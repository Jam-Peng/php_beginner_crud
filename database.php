<?php
		$homeUrl = 'index.php';

		$db_server = "localhost";
		$db_user = "root";
		$db_password = "";
		$db_name = "php_beginner_tutorial";

		try {
			$conn = new mysqli($db_server, $db_user, $db_password, $db_name);
	
			if ($conn->connect_error) {
					throw new Exception("資料庫連線失敗: " . $conn->connect_error);
			}
	
			//echo "資料庫連線成功，目前連線 {$db_name} 資料表";
		} catch (Exception $e) {
				echo $e->getMessage();
		}
?>
