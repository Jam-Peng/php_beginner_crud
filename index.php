<?php
		include("language.php");
		include("database.php");

		$error = '';
		$success = '';

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
				// $username = $_POST['username'];
				// $password = $_POST['password'];
				$username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
				$password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

				if(empty($username)){
						$error = "請輸入帳號";
				}
				elseif(empty($password)){
						$error = "請輸入密碼";
				}
				else{
						// 連接資料庫
						include("database.php");

						// 檢查用戶是否已存在
						$stmt = $conn -> prepare("SELECT * FROM user_members WHERE username = ?");
						$stmt->bind_param("s", $username);
						$stmt->execute();
						$result = $stmt->get_result();

						if($result->num_rows > 0) {
								$error = "帳號已存在";
						} 
						else {
								$uid = generateUID();
								$hashed_password = password_hash($password, PASSWORD_DEFAULT);
								$create_time = date('Y-m-d H:i:s'); // 獲取當前時間

								$stmt = $conn->prepare("INSERT INTO user_members (uid, username, password, create_time) VALUES (?, ?, ?, ?)");
								$stmt->bind_param("ssss", $uid, $username, $hashed_password, $create_time);
								if ($stmt->execute()) {
										$success = "註冊成功";
								} else {
										$error = "註冊失敗，請稍後再試";
								}
						}

						$stmt->close();
						$conn->close();
				}
		}
?>

<!DOCTYPE html>
<html lang="en">
<head>
		<meta charset="UTF-8">
		<title><?php echo db_basename?></title>
		<style>
				html, body {
						box-sizing: border-box;
            height: 100%;
						width: 100%;
            margin: 0;
						padding: 0;
        }
				.shared-style{
					text-decoration: none; 
					color: inherit;
					border: 1px solid black;
					border-radius: .2rem;
					padding: .2rem .5rem;
					font-size: 1rem;
					cursor: pointer;
				}
				td{
					padding: .4rem .5rem;
					text-align: center;
				}
				.container{
					box-sizing: border-box;
					height: 100vh;
          width: 100vw;
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: center;
					margin: 0;
					padding: 2rem;
				}
				#deleteConfirmation{
					display: none;
					width: 50vw;
					position: fixed;
					padding: 1rem;
					border: 1px solid black;
					border-radius: 10px;
					background-color: white;
					z-index: 999;
					box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
					transition: transform 0.5s ease; 
				}
		</style>
</head>
<body>
	<div class="container">
		<?php 
				if (!empty($error)){
						echo "<p id='error' style='color:red;'>$error</p>";
						echo '<script>
								setTimeout(function() {
										let error = document.getElementById("error");
										if (error) {
											error.style.display = "none";
										}
								}, 2000);
						</script>';
				}  
		?>	
    <?php 
				if (!empty($success)){
						echo "<p id='success' style='color:green;'>$success</p>"; 
						echo '<script>
								setTimeout(function() {
										let success = document.getElementById("success");
										if (success) {
											success.style.display = "none";
										}
								}, 2000);
						</script>';
				} 
		?>

    <form action="index.php" method="post">
				<input type="hidden" name="_method" value="POST">
				<label for="username">帳號: <input type="text" id="username" name="username"></label>
				<br><br>
				<label for="password">密碼: <input type="password" id="password" name="password"></label>
				<br><br>
				<div style="text-align: center;">
					<input type="submit" class="shared-style"  name="register" value="註冊">
				</div>
    </form>

		<!-- 顯示所有 user_members 資料表中的所有資訊 -->
		<?php
			include("database.php");
			// $sql = "SELECT * FROM user_members WHERE is_delete != 1 ";
			$sql = "SELECT * FROM user_members";
			$result = mysqli_query($conn, $sql);

			if (mysqli_num_rows($result) > 0) {
				echo "<br>";
				echo "<div style='width: 100%; border: 1px solid black; border-radius: 10px; padding: 1.5rem 0;
							display:flex; flex-direction: column; align-items: center; justify-content: center;'>";
				echo "<span style='font-size: 1.5rem'>顯示所有使用者資訊方式</span>";
				echo "<br>";
				echo "<table style='border-collapse: collapse; width: 80%;' border='1'>";
				echo "<tr><th>ID</th><th>UID</th><th>Username</th><th>Password</th><th>Create Time</th><th>Is Delete</th><th>更新</th><th>刪除</th></tr>";
				$count = 0; // 計算單數行背景要換色
				while ($row = mysqli_fetch_assoc($result)) {
					$count++;
					echo "<tr style='background-color: ".($count % 2 == 1 ? '#ddd' : 'white')."'>"; // 判斷奇偶行
					echo "<td>".$row['id']."</td>";
					echo "<td>".$row['uid']."</td>";
					echo "<td>".$row['username']."</td>";
					echo "<td>".$row['password']."</td>";
					echo "<td>".$row['create_time']."</td>";
					echo "<td>".$row['is_delete']."</td>";
					echo "<td><a class='shared-style' href='update.php?id=".$row['id']."'>更新</a></td>"; // 更新連結
					echo "<td><button class='shared-style' onclick='showDeleteConfirmation(".$row['id'].")'>刪除</button></td>";
					echo "</tr>";
				}
				echo "</table>";
				echo "</div>";
			} else {
				echo "<p>目前資料表尚未有資料</p>";
			}
			mysqli_close($conn);
		?>

		<div id="deleteConfirmation">
				<p>確定要刪除此用戶嗎？</p>
				<p id="deleteUserInfo"></p>
				<button class="shared-style" onclick="confirmDelete()">確定</button>
				<button class="shared-style" onclick="hideDeleteConfirmation()">取消</button>
		</div>
	</div>

		<script>
				function showDeleteConfirmation(id) {
						const deleteConfirmation = document.getElementById('deleteConfirmation');
						deleteConfirmation.style.display = 'block';
						
						// 在這裡將用戶的 ID 存儲起來，以便於後續確認刪除時使用
						deleteConfirmation.dataset.userId = id;
						deleteConfirmation.style.transform = 'scale(0)';

						setTimeout(() => {
								deleteConfirmation.style.transform = 'scale(1)';
						}, 0)
						// 在此處獲取用戶的所有資料，並顯示在確認框中
						let userInfo = "用戶 ID：" + id + "<br>";
						userInfo += "UID：" + document.querySelector("td:nth-child(2)").innerText + "<br>";
						userInfo += "帳號：" + document.querySelector("td:nth-child(3)").innerText + "<br>";
						userInfo += "密碼：" + document.querySelector("td:nth-child(4)").innerText + "<br>";
						userInfo += "建立時間：" + document.querySelector("td:nth-child(5)").innerText + "<br>";
						userInfo += "是否刪除：" + document.querySelector("td:nth-child(6)").innerText;
						
						document.getElementById('deleteUserInfo').innerHTML = userInfo;
				}

				function hideDeleteConfirmation() {
						const deleteConfirmation = document.getElementById('deleteConfirmation');
						// 控制結構的縮小
						deleteConfirmation.style.transform = 'scale(0)';
						
						// 等待縮小動畫完成後隱藏結構
						setTimeout(function() {
								deleteConfirmation.style.display = 'none';
						}, 500); // 與 CSS 中過渡時間保持一致
				}

				function confirmDelete() {
						let userId = document.getElementById('deleteConfirmation').dataset.userId;
						console.log('確認刪除用戶 ID:', userId);

						let formData = new FormData();
						formData.append('userId', userId);
						formData.append('_method', 'DELETE');

						// 发送 POST 请求到后端 PHP 文件
						fetch('sql_method.php', {
								method: 'POST',
								body: formData
						})
						.then(response => response.json())
						.then(result => {
								console.log(result)
								if(result.status === 'success') {
										console.log("刪除成功")
								} else {
										console.log('删除失败。');
								}
						})
						.catch(error => {
								console.error('Error:', error);
								console.log('操作失敗。');
						});

						// 改由放大縮小效果
						hideDeleteConfirmation()
						setTimeout(()=>{ 
							location.reload(); 
						} ,1000)
				}

				// 直接隱藏確認框效果
				function cancelDelete() {
						document.getElementById('deleteConfirmation').style.display = 'none';
				}
		</script>

</body>
</html>

<?php
		function generateUID() {
				// 生成前兩位的隨機英文字母
				$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$firstTwoLetters = $letters[rand(0, strlen($letters) - 1)] . $letters[rand(0, strlen($letters) - 1)];

				// 生成後面 8 位的隨機字母和數字組合
				//$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				// 生成後面 8 位的隨機數字組合
				$characters = '0123456789';
				$randomString = '';
				for ($i = 0; $i < 8; $i++) {
						$randomString .= $characters[rand(0, strlen($characters) - 1)];
				}

				return $firstTwoLetters . $randomString;
		}
?>
