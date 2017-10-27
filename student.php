<?php
$error=""; #Variable To Store Error Message
include '../config_iron.php'; #Define $servername $username $password $dbname and $configready here.
session_start(); #Starting Session

do {
if($error !== "") {
	break;
}
if (!empty($_POST)) {
	if(!(array_key_exists("student_id",$_POST))) {
		$error = "You need to enter a Student ID.";
		break;
	}
	
	if($_POST["student_id"] === '') {
		$error = "You need to enter a Student ID.";
		break;
	}
	
	$stmt = $conn->prepare("SELECT STUDENT_ID FROM STUDENT$ WHERE STUDENT_ID = :student_id");
	$stmt->execute(array('student_id' => $_POST["student_id"]));
	$row = $stmt->fetch();
	
	
	
	if($stmt->rowCount() > 0) {
		$_SESSION['login_user'] = $row["STUDENT_ID"]; #Initializing Session
		$_SESSION['timestamp'] = date("Y-m-d H:i:s");
		$_SESSION['valid'] = "Student";	
	} else {
		$error = "It appears you don't have an account. Check with your coach to see if you are added to the database!";
	}
}

if(isset($_SESSION['login_user']) && $_SESSION['valid'] === "Student"){
	header("location: /student/index.php");
	die();
}
} while (0); #but it works!
?>
<!DOCTYPE html>
<html>
<head>
	<title>Student Login</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
	<div id="main">
		<div id="login">
			<h1>Student Login</h1>
			<form class="login"  method="post">
				<h3>Student ID: </h3><div class="padding"><input type="password" id="username" name="student_id"></div><br>
				<div class="padding"><input type="submit" value=" Enter "></div>
				<span id="error"><?php echo $error?></span>
			</form>
			<form class="login" action="coach.php">
				<div class="padding"><input type="submit" value=" I'm actually a coach "></div>
			</form>
		</div>
	</div>
</body>
</html>