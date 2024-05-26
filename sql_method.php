<?php
		// 設定傳遞 JSON 格式
		header('Content-Type: application/json');

		include("database.php");

		// 檢查請求方法
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				if (isset($_POST['_method']) && $_POST['_method'] == 'DELETE') {
						// 處理刪除
						if (isset($_POST['userId'])) {
								$userId = $_POST['userId'];

								// $sql = "DELETE FROM user_members WHERE id = '$userId'";
								$sql = "UPDATE user_members SET is_delete = '1' WHERE id = '$userId'";
								if (mysqli_query($conn, $sql)) {
										// 返回成功信息
										echo json_encode(['status' => 'success', 'msg' => '刪除成功']);
										http_response_code(200);
								} else {
										
										echo json_encode(['status' => 'error', 'msg' => '此帳號不存在']);
										http_response_code(500);
								}
						} else {
								// 如果未提供用户 ID
								echo json_encode(['status' => 'error', 'msg' => '缺少參數']);
								http_response_code(400);
						}
				} 
				elseif (isset($_POST['_method']) && $_POST['_method'] == 'UPDATE') {
						// 可以在這裡處理更新資料庫的
				} 
				else {
						// 如果未提供有效的操作类型，返回错误信息给前端
						echo json_encode(['status' => 'error',  'msg' => '無效的操作']);
						http_response_code(400);
				}
		}
?>