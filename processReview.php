<?php
session_start();

//check if the user is logged in
if (isset($_SESSION["username"])){
	$pdo = new PDO('mysql:host=127.0.0.1:57342;dbname=localdb', 'azure', '6#vWHD_$');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try {
			$entry = $pdo->prepare('INSERT INTO reviews VALUES(:username, :review, :itemid, :rating)');
			$entry->execute(array(
					'username' => $_SESSION['username'],
					//filter used to prevent cross site scripting. This filter escapes special characters
					'review'  => filter_var($_GET['comment'], FILTER_SANITIZE_SPECIAL_CHARS),
					'itemid' => $_SESSION['park id'],
					'rating' => $_GET['rate']
				));
	}	catch (PDOException $e) {
			echo $e->getMessage();
			
		}
	
	// If the user has logged in then return the item page with refreshing
	echo "<script>location.href='".$_SERVER["HTTP_REFERER"]."';</script>";
} else {
	//if the user is not logged in, link them to the login page
	header('Location: Login.php');
}

$_SESSION['park id'] = null;

?>








