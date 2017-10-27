<?php
$error = "";
$editError = "";
$editSuccess = "";
$editErrorWeekly = "";
$editSuccessWeekly = "";
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.
if(isset($_POST["EDIT"])) {
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE ID = :id AND COACH = :coach");
	$stmt->execute(array('id' => $_POST["EDIT"],
						 'coach' => $_SESSION['login_user']));
	$row = $stmt->fetch();
	if($stmt->rowCount() == 0) {
		header("Location: students.php");
		die();
	}
} else {
	header("Location: students.php");
	die();
}
do{
if(isset($_POST["WEEK_LOCAL"]) && isset($_POST["BENCH"]) && isset($_POST["DEADLIFT"]) && isset($_POST["BACKSQUAT"]) && isset($_POST["UPDATE_WEEK"])) {
	if(!is_numeric($_POST["BENCH"]) || !is_numeric($_POST["DEADLIFT"]) || !is_numeric($_POST["BACKSQUAT"]) || !is_numeric($_POST["WEEK_LOCAL"])) {
		$editErrorWeekly = "You somehow submitted text that should be a number. Try again with a number!";
		break;
	}
	if($_POST["WEEK_LOCAL"] > 12 || $_POST["WEEK_LOCAL"] < 1) {
		$editErrorWeekly = "The week should be a number 1-12";
		break;
	}
	$stmt = $conn->prepare("SELECT * FROM DATA WHERE WEEK = :week AND LINKED_ID = :link");
	$stmt->execute(array('week' => $_POST["WEEK_LOCAL"],
						 'link' => $row["ID"]));
	$replace = $stmt->fetch();
	if(isset($replace["ID"])) {
		$stmt = $conn->prepare("UPDATE DATA SET BENCH = :bench, DEADLIFT = :dead, BACKSQUAT = :back WHERE ID = :replace");
		$stmt->execute(array('replace' => $replace["ID"],
							 'bench' => $_POST["BENCH"],
							 'dead' => $_POST["DEADLIFT"],
							 'back' => $_POST["BACKSQUAT"]));
		$editSuccessWeekly = "You manually updated " . $row["NAME"] . "'s information for week " . $_POST["WEEK_LOCAL"];
	} else {
		$stmt = $conn->prepare("INSERT INTO DATA (LINKED_ID, WEEK, BENCH, DEADLIFT, BACKSQUAT) VALUES (:link, :week, :bench, :dead, :back)");
		$stmt->execute(array('link' => $row["ID"],
							 'week' => $_POST["WEEK_LOCAL"],
							 'bench' => $_POST["BENCH"],
							 'dead' => $_POST["DEADLIFT"],
							 'back' => $_POST["BACKSQUAT"]));
		$editSuccessWeekly = "You manually entered " . $row["NAME"] . "'s information for week " . $_POST["WEEK_LOCAL"];
	}
}
	
if(isset($_POST["NAME"]) && isset($_POST["STUDENT_ID"]) && isset($_POST["GENDER"]) &&
   isset($_POST["BASE_DEADLIFT"]) && isset($_POST["BASE_BACKSQUAT"]) && isset($_POST["BASE_BENCH"]) &&
   isset($_POST["POST_DEADLIFT"]) && isset($_POST["POST_BACKSQUAT"]) && isset($_POST["POST_BENCH"])) {
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE STUDENT_ID = :id AND COACH = :coach  AND SEMESTER = :semester");
	$stmt->execute(array('id' => $_POST["STUDENT_ID"],
						 'coach' => $_SESSION['login_user'],
						 'semester' => $_SESSION["SEMESTER_GLOBAL"]));
	$exists = $stmt->fetch();
	if(isset($exists["NAME"]) && $exists["ID"] != $row["ID"]) {
		$editError = $exists["NAME"] . " already uses the Student ID " . $exists["STUDENT_ID"] . " in period " . $exists["PERIOD"] . "! Try switching to a new semester instead.";
		break;
	}
	if(!is_numeric($_POST["BASE_BENCH"]) || !is_numeric($_POST["BASE_BACKSQUAT"]) || !is_numeric($_POST["BASE_DEADLIFT"]) ||
	   !is_numeric($_POST["POST_BENCH"]) || !is_numeric($_POST["POST_BACKSQUAT"]) || !is_numeric($_POST["POST_DEADLIFT"])) {
		$editError = "You somehow submitted text that should be a number. Try again with a number!";
		break;
	}
	if(trim($_POST["NAME"]) == "" || trim($_POST["STUDENT_ID"]) == "" || trim($_POST["GENDER"] == "")) {
		$editError = "Please enter data in all of the fields!";
		break;
	}
	$stmt = $conn->prepare("UPDATE STUDENT$ SET BASE_BENCH = :basebench, BASE_DEADLIFT = :basedead, BASE_BACKSQUAT = :baseback, " . 
	                                           "POST_BENCH = :postbench, POST_DEADLIFT = :postdead, POST_BACKSQUAT = :postback, " . 
	                                           "NAME = :name, STUDENT_ID = :studid, GENDER = :gender WHERE ID = :replace");
	$stmt->execute(array('replace' => $row["ID"],
						 'basebench' => $_POST["BASE_BENCH"],
						 'basedead' => $_POST["BASE_DEADLIFT"],
						 'baseback' => $_POST["BASE_BACKSQUAT"],
						 'postbench' => $_POST["POST_BENCH"],
						 'postdead' => $_POST["POST_DEADLIFT"],
						 'postback' => $_POST["POST_BACKSQUAT"],
						 'name' => $_POST["NAME"],
						 'studid' => $_POST["STUDENT_ID"],
						 'gender' => $_POST["GENDER"]));
	$editSuccess = "Hurray, you updated the information successfully!";
	
	#refetch information because we just updated it.
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE ID = :id AND COACH = :coach");
	$stmt->execute(array('id' => $_POST["EDIT"],
						 'coach' => $_SESSION['login_user']));
	$row = $stmt->fetch();
}
} while(0);

$weekBuilder = array();
$stmt = $conn->prepare("SELECT * FROM DATA WHERE LINKED_ID = :link");
$stmt->execute(array('link' => $row["ID"]));
$weekData = $stmt->fetchAll();

for($week = 1; $week <= 12; $week++) {
	$echoed = FALSE;
	foreach($weekData as $weekDataRow) {
		if($weekDataRow["WEEK"] == $week) {
			$echoed = TRUE;
			$weekBuilder[] = 
			'<option value="' . $week .
			'" style="width: 100%; background-color: lime;">* Week ' . $week . '</option>';
			break;
		}
	}
	if($echoed == FALSE) {
		$weekBuilder[] = 
		'<option value="' . $week .
		'" style="width: 100%;">Week ' . $week . '</option>';
	}
}
$selectedWeek = 1;
if(isset($_POST["WEEK_LOCAL"])) {
	$selectedWeek = $_POST["WEEK_LOCAL"];
	$key = array_search('<option value="' .
	$selectedWeek  . '" style="width: 100%;">Week ' .
	$selectedWeek  . '</option>', $weekBuilder);
	if($key !== FALSE) {
		$weekBuilder[$key] = 
		'<option selected="selected" value="' .
		$selectedWeek  . '" style="width: 100%;">Week ' .
		$selectedWeek  . '</option>';
	} else {
		$key = array_search('<option value="' .
		$selectedWeek  . '" style="width: 100%; background-color: lime;">* Week ' .
		$selectedWeek  . '</option>', $weekBuilder);
		if($key !== FALSE) {
			$weekBuilder[$key] = 
			'<option selected="selected" value="' .
			$selectedWeek  . '" style="width: 100%; background-color: lime;">* Week ' .
			$selectedWeek  . '</option>';
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Student</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<script>
	function loading() { 
		document.getElementById("loading").style.display="block";
	}
	</script>
</head>
<body>
<div id="navbar">
	<div id="exit" style="margin: 0;">
		<a href="students.php" class="headlink" style="border-radius: 0 0 30px 0;"><div class="textheadlink">Exit</div></a>
	</div>
</div>
<div class="pseudobody">
	<h1>Weekly data for <?php echo($row["NAME"]); ?></h1>
	<div class="center">
		<form method="post">
			<input type="hidden"
				   name="EDIT"
				   value="<?php echo($_POST["EDIT"]); ?>"><br>

			<?php
			if($error !== "") {echo("<span>$error</span>");}
			if($editErrorWeekly !== "") {echo("<span>$editErrorWeekly</span>");}
			if($editSuccessWeekly !== "") {echo("<p style=\"color:green;\">$editSuccessWeekly</p>");}
			?> 

			<h3 class="titlepadding">WARNING: Switching this field will clear any unsaved data below!</h3>
			<h3 class="titlepadding">A star/green color represents saved data!</h3>
				<select class="classestext" name='WEEK_LOCAL' onchange='if(this.value != 0) {loading(); this.form.submit();}'>
<?php foreach($weekBuilder as $weeks) {echo($weeks);} ?> 
				</select>
				
<?php
$stmt = $conn->prepare("SELECT * FROM DATA WHERE WEEK = :week AND LINKED_ID = :link");
$stmt->execute(array('week' => $selectedWeek,
					 'link' => $row["ID"]));
$replace = $stmt->fetch();
?>
			<h3 class="titlepadding">Bench Press (AMRAP)</h3>
				<input class="text"
					   type="number"
					   name="BENCH"
					   placeholder="<?php echo($replace["BENCH"]); ?>" 
					   value="<?php echo($replace["BENCH"]); ?>"><br>
					   
			<h3 class="titlepadding">Dead Lift (AMRAP)</h3>
				<input class="text"
					   type="number"
					   name="DEADLIFT"
					   placeholder="<?php echo($replace["DEADLIFT"]); ?>" 
					   value="<?php echo($replace["DEADLIFT"]); ?>"><br>
					   
			<h3 class="titlepadding">Backsquat (AMRAP)</h3>
				<input class="text"
					   type="number"
					   name="BACKSQUAT"
					   placeholder="<?php echo($replace["BACKSQUAT"]); ?>" 
					   value="<?php echo($replace["BACKSQUAT"]); ?>"><br>
					   
			<button name="UPDATE_WEEK" type="submit" value="REAL" class="goodbutton">Update Week <?php echo($selectedWeek); ?></button>
		</form><br>
	</div>
</div>
<div class="pseudobody">
	<h1>General data for <?php echo($row["NAME"]); ?></h1>
	<div class="center">
		<form method="post">
			<input type="hidden"
				   name="EDIT"
				   value="<?php echo($_POST["EDIT"]); ?>">
			<?php
			if($editError !== "") {echo("<span>$editError</span>");}
			if($editSuccess !== "") {echo("<p style=\"color:green;\">$editSuccess</p>");}
			?> 
			<h3 class="titlepadding">Name</h3>
				<input class="text" 		
					   type="text" 		
					   name="NAME" 
					   placeholder="<?php echo($row["NAME"]); ?>" 
					   value="<?php echo($row["NAME"]); ?>"><br>
			
			<h3 class="titlepadding">Student ID</h3>
				<input class="text"
					   type="text"
					   name="STUDENT_ID"
					   placeholder="<?php echo($row["STUDENT_ID"]); ?>" 
					   value="<?php echo($row["STUDENT_ID"]); ?>"><br>
					   
			<h3 class="titlepadding">Gender</h3>
			<select name='GENDER' class="classestext">
<?php
if($row["GENDER"] == "F") {
?> 
				<option value='M' style="width: 100%;">Male</option>
				<option value='F' style="width: 100%;" selected>Female</option>
<?php
} else {
?> 
				<option value='M' style="width: 100%;" selected>Male</option>
				<option value='F' style="width: 100%;">Female</option>
<?php
}
?>
			</select>
			<br><br><br>
			<h3 class="titlepadding">Set any of the fields below to 0 for "Not Entered"</h3>
			<h3 class="titlepadding">Pre Test Bench MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="BASE_BENCH"
					   placeholder="<?php echo($row["BASE_BENCH"]); ?>" 
					   value="<?php echo($row["BASE_BENCH"]); ?>"><br>
					   
			<h3 class="titlepadding">Pre Test Deadlift MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="BASE_DEADLIFT"
					   placeholder="<?php echo($row["BASE_DEADLIFT"]); ?>" 
					   value="<?php echo($row["BASE_DEADLIFT"]); ?>"><br>
					   
			<h3 class="titlepadding">Pre Test Squat MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="BASE_BACKSQUAT"
					   placeholder="<?php echo($row["BASE_BACKSQUAT"]); ?>" 
					   value="<?php echo($row["BASE_BACKSQUAT"]); ?>"><br><br><br>
					   
			<h3 class="titlepadding">Post Test Bench MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="POST_BENCH"
					   placeholder="<?php echo($row["POST_BENCH"]); ?>" 
					   value="<?php echo($row["POST_BENCH"]); ?>"><br>
					   
			<h3 class="titlepadding">Post Test Deadlift MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="POST_DEADLIFT"
					   placeholder="<?php echo($row["POST_DEADLIFT"]); ?>" 
					   value="<?php echo($row["POST_DEADLIFT"]); ?>"><br>
					   
			<h3 class="titlepadding">Post Test Squat MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="POST_BACKSQUAT"
					   placeholder="<?php echo($row["POST_BACKSQUAT"]); ?>" 
					   value="<?php echo($row["POST_BACKSQUAT"]); ?>"><br>

			<div class="padding"><input type="submit"	value="Edit Student!" class="goodbutton"></div>
		</form><br>
	</div>
</div>
<div id="loading" style="width:100%; height:100%; position:fixed; top:0; left:0; background: rgba(0, 0, 0, 0.4); display:none">
	<img style="margin: auto; display:block; padding-top:100px; width:60%; max-width: 400px;" src="../images/loading.gif"/>
</div>
<script> 
var forms = document.getElementsByTagName('form');
for(i=0;i<forms.length;i++) {
	forms[i].addEventListener("submit", loading, false);
}
</script>