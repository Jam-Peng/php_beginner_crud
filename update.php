<?php
		include("database.php");
		$error = '';
		$success = '';
		$userId = null;

		if (isset($_GET['id'])) {

				$userId = $_GET['id'];
				$sql = "SELECT * FROM user_members WHERE id = '$userId'";
				$result = mysqli_query($conn, $sql);
				$user = mysqli_fetch_assoc($result);
				if (!$user) {
						$error = "未找到該用戶。";
						exit;
				}
		} 
		else {
				$error = "缺少參數";
				exit;
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
				if (!empty($_POST['username']) && isset($_POST['is_delete'])) {
						$username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_SPECIAL_CHARS);
						$is_delete = filter_input(INPUT_POST, "is_delete", FILTER_SANITIZE_SPECIAL_CHARS);

						if(empty($username)){
								$error = "請輸入要更新的帳號";
						}
						else{
								$updateSql = "UPDATE user_members SET username = '$username', is_delete = '$is_delete' WHERE id = '$userId'";
								if (mysqli_query($conn, $updateSql)) {
										$success = "更新成功。";
										header("Location: index.php");
										exit;
								} 
								else {
										$error = "更新失敗。";
								}
						}
				} 
				else {
						$error = "請正確填寫欄位。";
				}
		}

		mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>更新用戶</title>
    <style>
        .shared-style {
						text-decoration: none; 
						color: inherit;
						border: 1px solid black;
						border-radius: .2rem;
						padding: .2rem .5rem;
						font-size: 1rem;
						cursor: pointer;
        }
        .container {
            width: 80%;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid #000;
            border-radius: 10px;
        }
				/* 去除input預設樣式 */
        select,
				#username {
            border: 1px solid black;
            border-radius: 5px; 
            padding: 0.2rem;
            box-shadow: none; 
            background-color: #f0f0f0; 
            outline: none; 
        }

				.cancel_style,
        input[type="datetime-local"]
				{
					border: none;
				}
    </style>
		
</head>
<body>
    <div class="container">
				<div style="padding: 0 0 1rem 0 ;">
						<a class='shared-style' href='index.php'>回註冊頁</a>
				</div>

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

        <form action="update.php?id=<?php echo $userId; ?>" method="post" onsubmit="handleSubmit()">
            <label for="uid">會員編號: <input type="text" id="uid" class="cancel_style" name="uid" value="<?php echo $user['uid']; ?>" readonly></label>
            <br><br>
            <label for="username">會員帳號: <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>"></label>
            <br><br>
            <label for="password">會員密碼: <input type="text" id="password" class="cancel_style" name="password" value="<?php echo $user['password']; ?>" readonly></label>
            <br><br>
            <label for="createTime">建立時間: <input type="datetime-local" id="createTime" class="cancel_style" name="createTime" value="<?php echo $user['create_time']; ?>" readonly></label>
            <br><br>
            <label for="is_delete">是否刪除: 
                <select id="is_delete" name="is_delete" style="width: 50px;">
                    <option value="0" <?php echo $user['is_delete'] == '0' ? 'selected' : ''; ?>>否</option>
                    <option value="1" <?php echo $user['is_delete'] == '1' ? 'selected' : ''; ?>>是</option>
                </select>
            </label>
            <br><br>
            <div style="text-align: center;">
                <input type="submit" class="shared-style" name="update" value="更新">
            </div>
        </form>
    </div>
		
		<script>
				// 處理text、password input 前後空白
        function handleSubmit() {
						const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(input => {
                input.value = input.value.trim();
            });
        };
    </script>
</body>
</html>
