<?php
#echo "This feature is disabled."; die(); #Comment this out to generate a coach.
include '../config_iron.php';
$username = $_POST["COACH"];
$password = $_POST["PASSWORD"];

if(isset($_POST["ROOT"])) {
	if($_POST["ROOT"] === "@wwEc5OemY@%") {
		$hashword = password_hash($password, PASSWORD_BCRYPT);

		$stmt = $conn->prepare("INSERT INTO COACH (username, password) VALUES (:username, :hashword)");
		$stmt->execute(array('username' => $username,
							 'hashword' => $hashword));
		$error = "The account was created... Probably.";
	} else {
		$error = "Root password is wrong!";
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Coach Generator 2000</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
	<div id="main">
		<div id="login">
			<h1>Coach Generator 2000</h1>
			<form class="login" method="post">
				<h3>Coach Username: </h3><div class="padding"><input type="text" id="username" name="COACH"></div><br>
				<h3>Password: </h3><div class="padding"><input type="password" id="password" name="PASSWORD"></div><br>
				<h3>Root Password: </h3><div class="padding"><input type="password" id="password" name="ROOT"></div><br>
				<div class="padding"><input type="submit" value=" Create Account "></div>
				<span id="error"><?php echo $error?></span>
			</form>
		</div>
	</div>
</body>
</html>