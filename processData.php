<?php

if (!isset($errors)){
						$errors = array();
					}
$fields = array("1" => "firstName", "2" => "lastName", "3" => "username", "4" => "password", "5" => "passwordConfirm", "6" => "dateofBirth", "7" => "postCode");

$a = 0;
//checks that all the fields are populated. 
foreach ($fields as $value){
	if (isset($_POST[$value])){
		$a = 1;
	}
}

if ($a==1){
	require 'validate.php';
	validateEmail($errors, $_POST, 'email');
	checkMatch($errors);
	checkPostcode($errors);
	foreach ($fields as $value){
		checkEmpty($errors, $_POST, $value);
	}
	
	if($errors){
		include 'Registration.php';
	} else {
		//connecting to the database
		$pdo = new PDO('mysql:host=127.0.0.1:57342;dbname=localdb', 'azure', '6#vWHD_$');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		try {
			//checks if the given username is already in use
			$result = $pdo->prepare('SELECT username FROM users WHERE username = :username');
			$result->execute(['username' => $_POST['username']]);
			$rows = $result->rowCount();
			//if the username is taken, provide error feedback to the user
			if ($rows == 1){
				$errors['username'] = 'Username taken';
				include 'Registration.php';
			}
			//if the given username is not taken, insert the username and password into the database
			if ($rows == 0){
				$salt = '4b3403665fea6';
				//prepare the PDO statement
				$entry = $pdo->prepare("INSERT INTO users (username, salt, password)VALUES(:username, :salt, SHA2(CONCAT(:password, :salt), 0))");
				$entry->execute(array(
					'username' => $_POST['username'],
					'salt'  => $salt,
					'password' => $_POST['password']
				));
 ;
				header('Location: Login.php');
			}

		} catch (PDOException $e) {
			echo $e->getMessage();
	}
		
	}
} else {
	include 'Registration.php';
}
?>

