<?php
$STUDENT_PAGE = true;
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

#Phase 1, need the coach
if(isset($_POST['STUDENT_COACH']) && !isset($_SESSION['STUDENT_COACH'])) {
	$correct = false;
	$stmt = $conn->prepare("SELECT DISTINCT COACH FROM STUDENT$ WHERE STUDENT_ID = :student_id");
	$stmt->execute(array('student_id' => $_SESSION['login_user']));
	$all = $stmt->fetchAll();
	foreach($all as $row) {
		if($row["COACH"] === $_POST['STUDENT_COACH']) {
			$correct = true;
			break;
		}
	}
	if($correct) {
		$_SESSION['STUDENT_COACH'] = $_POST['STUDENT_COACH'];
	} else {
		$error = "You seemed to modify the choices after I sent them to you. Try not to do that please!";
	}
}


#Phase 2, have the coach, need the semester
if(isset($_SESSION['STUDENT_COACH']) && isset($_POST['STUDENT_SEMESTER']) && !isset($_SESSION['STUDENT_SEMESTER'])) {
	$correct = false;
	$stmt = $conn->prepare("SELECT DISTINCT SEMESTER FROM STUDENT$ WHERE STUDENT_ID = :student_id AND COACH = :coach");
	$stmt->execute(array('student_id' => $_SESSION['login_user'],
						 'coach' => $_SESSION['STUDENT_COACH']));
	$all = $stmt->fetchAll();
	foreach($all as $row) {
		if($row["SEMESTER"] === $_POST['STUDENT_SEMESTER']) {
			$correct = true;
			break;
		}
	}
	if($correct) {
		$_SESSION['STUDENT_SEMESTER'] = $_POST['STUDENT_SEMESTER'];
	} else {
		$error = "You seemed to modify the choices after I sent them to you. Try not to do that please!";
	}
}

#Phase 3, have the coach, have the semester, need to know what to actually do.
if(isset($_SESSION['STUDENT_COACH']) && isset($_SESSION['STUDENT_SEMESTER'])) {
	if(isset($_POST['DO'])) {
		if($_POST['DO'] == "Logout") {
			logout();
		}
		if($_POST['DO'] == "View historical data") {
			header("location: /student/history.php");
			die();
		}
				if($_POST['DO'] == "View your workout") {
			header("location: /student/workout.php");
			die();
		}
				if($_POST['DO'] == "Edit your AMRAPs") {
			header("location: /student/amraps.php");
			die();
		}
	}
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE STUDENT_ID = :student_id AND COACH = :coach AND SEMESTER = :semester");
	$stmt->execute(array('student_id' => $_SESSION['login_user'],
						 'coach' => $_SESSION['STUDENT_COACH'],
						 'semester' => $_SESSION['STUDENT_SEMESTER']));
	$row = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Welcome, <?php echo $row["NAME"];?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
	<div id="main">
		<div id="login">
			<h1>Welcome, <?php echo $row["NAME"];?></h1>
			<form class="login" method="post">
				<h3>What would you like to do?</h3>
				<div class="padding">
					<input class="goodbutton" type="submit" name="DO" value="View your workout">
					<input class="goodbutton" type="submit" name="DO" value="Edit your AMRAPs">
				</div>
				<span id="error"><?php echo $error?></span>
				<br>
				<h3>General Information: </h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Coach: <?php echo $row["COACH"];?></h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Semester: <?php echo $row["SEMESTER"];?></h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Period: <?php echo $row["PERIOD"];?></h3>
				<br>
				<h3>Your Pre Test: </h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Bench Press: <?php echo $row["BASE_BENCH"];?>lbs</h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Deadlift: <?php echo $row["BASE_DEADLIFT"];?>lbs</h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Backsquat: <?php echo $row["BASE_BACKSQUAT"];?>lbs</h3>
				<br>
				<h3>Your Post Test: </h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Bench Press: <?php echo ($row["POST_BENCH"] == 0 ? "None." : $row["POST_BENCH"] . "lbs");?></h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Deadlift: <?php echo ($row["POST_DEADLIFT"] == 0 ? "None." : $row["POST_DEADLIFT"] . "lbs");?></h3>
				<h3>&nbsp;&nbsp;&nbsp;&nbsp;Backsquat: <?php echo ($row["POST_BACKSQUAT"] == 0 ? "None." : $row["POST_BACKSQUAT"] . "lbs");?></h3>
				<br>
				<div class="padding">
					<input type="submit" name="DO" value="Logout">
				</div>
			</form>
		</div>
	</div>
</body>
</html>
<?php
	die();
}

#Phase 1 and phase 2 selection below
?>
<!DOCTYPE html>
<html>
<head>
	<title>Hold up!</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
	<div id="main">
		<div id="login">
			<h1>We need to know a little bit more information!</h1>
			<form class="login" method="post">
<?php

#Phase 1, need the coach
if(!isset($_SESSION['STUDENT_COACH'])) {
?> 
				<h3>Select the coach you want to use: </h3>
				<div class="padding">
<?php
	$stmt = $conn->prepare("SELECT DISTINCT COACH FROM STUDENT$ WHERE STUDENT_ID = :student_id");
	$stmt->execute(array('student_id' => $_SESSION['login_user']));
	$all = $stmt->fetchAll();
	foreach($all as $row) {
?>
					<input class="goodbutton" type="submit" name="STUDENT_COACH" value="<?php echo $row["COACH"]; ?>">
<?php
	}
?> 
				</div>
<?php
}

#Phase 2, have the coach, need the semester
if(isset($_SESSION['STUDENT_COACH']) && !isset($_SESSION['STUDENT_SEMESTER'])) {
?> 
				<h3>Select the semester you want to use: </h3>
				<div class="padding">
<?php
	$stmt = $conn->prepare("SELECT DISTINCT SEMESTER FROM STUDENT$ WHERE STUDENT_ID = :student_id AND COACH = :coach");
	$stmt->execute(array('student_id' => $_SESSION['login_user'],
						 'coach' => $_SESSION['STUDENT_COACH']));
	$all = $stmt->fetchAll();
	foreach($all as $row) {
?>
					<input class="goodbutton" type="submit" name="STUDENT_SEMESTER" value="<?php echo $row["SEMESTER"]; ?>">
<?php
	}
?> 
				</div>
<?php
}
?> 
				<span id="error"><?php echo $error?></span>
			</form>
		</div>
	</div>
</body>
</html>