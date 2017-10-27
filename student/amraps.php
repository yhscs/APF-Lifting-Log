<?php
$STUDENT_PAGE = true;
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

$selectedWeek = $_SESSION["WEEK_GLOBAL"];
if(isset($_POST["WEEK_GLOBAL"])) {
	$_SESSION["WEEK_GLOBAL"] = $_POST["WEEK_GLOBAL"];
	$selectedWeek = $_SESSION["WEEK_GLOBAL"];
}
if(!is_numeric($selectedWeek) || $selectedWeek <= 0 || $selectedWeek > 12) {
	$selectedWeek = "1";
	$_SESSION["WEEK_GLOBAL"] = "1";
}
$selectedWeek = $_SESSION["WEEK_GLOBAL"];

$row = null;
if(isset($_SESSION['STUDENT_COACH']) && isset($_SESSION['STUDENT_SEMESTER'])) {
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE STUDENT_ID = :student_id AND COACH = :coach AND SEMESTER = :semester");
	$stmt->execute(array('student_id' => $_SESSION['login_user'],
						 'coach' => $_SESSION['STUDENT_COACH'],
						 'semester' => $_SESSION['STUDENT_SEMESTER']));
	$row = $stmt->fetch();
} else {
	header("location: /student/index.php");
	die();
}
if($row == null) {
	header("location: /student/index.php");
	die();
}

if(isset($_POST["DEADLIFT"]) && isset($_POST["BENCH"]) && isset($_POST["SQUAT"])) {
	if(is_numeric($_POST["DEADLIFT"]) && is_numeric($_POST["BENCH"]) && is_numeric($_POST["SQUAT"])) {
		$stmt = $conn->prepare("SELECT * FROM DATA WHERE WEEK = :week AND LINKED_ID = :link");
		$stmt->execute(array('week' => $selectedWeek,
							 'link' => $row["ID"]));
		$replace = $stmt->fetch();
		if(isset($replace["ID"])) {
			$stmt = $conn->prepare("UPDATE DATA SET BENCH = :bench, DEADLIFT = :dead, BACKSQUAT = :back WHERE ID = :replace");
			$stmt->execute(array('replace' => $replace["ID"],
								 'bench' => $_POST["BENCH"],
								 'dead' => $_POST["DEADLIFT"],
								 'back' => $_POST["SQUAT"]));
			$editSuccess = "Thanks for updating your information, " . $row["NAME"];
		} else {
			$stmt = $conn->prepare("INSERT INTO DATA (LINKED_ID, WEEK, BENCH, DEADLIFT, BACKSQUAT) VALUES (:link, :week, :bench, :dead, :back)");
			$stmt->execute(array('link' => $row["ID"],
								 'week' => $selectedWeek,
								 'bench' => $_POST["BENCH"],
								 'dead' => $_POST["DEADLIFT"],
								 'back' => $_POST["SQUAT"]));
			$editSuccess = "Thanks for entering your information, " . $row["NAME"];
		}
	} else {
		$editError = "Enter a number next time, please!";
	}
}

$weekBuilder = array();
for($week = 1; $week <= 12; $week++) {
	$weekBuilder[] = 
	'<option value="' . $week .
	'" style="width: 100%;">Week ' . $week . '</option>';
}
$key = array_search('<option value="' .
$selectedWeek . '" style="width: 100%;">Week ' .
$selectedWeek . '</option>', $weekBuilder);

if($key !== FALSE) {
	$weekBuilder[$key] = 
	'<option selected="selected" value="' .
	$selectedWeek . '" style="width: 100%;">Week ' .
	$selectedWeek . '</option>';
}

$stmt = $conn->prepare("SELECT * FROM DATA WHERE WEEK = :week AND LINKED_ID = :link");
$stmt->execute(array('week' => $selectedWeek,
					 'link' => $row["ID"]));
$existingData = $stmt->fetch();

$autofill = isset($existingData["ID"]);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit your AMRAPs</title>
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
	<div id="main">
		<div id="login">
			<h1>AMRAPs</h1>
			<form method="post">
				<select name='WEEK_GLOBAL' onchange='if(this.value != 0) {loading(); this.form.submit();}' style="width: 100%;">
<?php foreach($weekBuilder as $rows) {echo("\t\t\t" . $rows . "\n");} ?>
				</select>
			</form>
			<form method="post">
				<h3 class="titlepadding">Bench</h3>
				<input class="text" type="number" name="BENCH" 		placeholder="Enter Reps Here" 
				<?php if($autofill) {echo 'value="' . $existingData["BENCH"] . '"';} ?>><br>
				<h3 class="titlepadding">Dead Lift</h3>
				<input class="text" type="number" name="DEADLIFT" 	placeholder="Enter Reps Here" 
				<?php if($autofill) {echo 'value="' . $existingData["DEADLIFT"] . '"';} ?>><br>
				<h3 class="titlepadding">Squat</h3>
				<input class="text" type="number" name="SQUAT" 		placeholder="Enter Reps Here" 
				<?php if($autofill) {echo 'value="' . $existingData["BACKSQUAT"] . '"';} ?>><br>
				<div class="padding"><input class="goodbutton" type="submit" value="Save!" onClick="window.onbeforeunload = null;"></div>
				<div class="center">
					<?php
					if($error !== "") {echo("<span>$error</span>");}
					if($editError !== "") {echo("<span>$editError</span>");}
					if($editSuccess !== "") {echo("<p style=\"color:green;\">$editSuccess</p>");}
					?>
				</div>
			</form>
			<form action="/student/index.php">
				<div class="padding">
					<input type="submit" value="Return to Home">
				</div>
			</form>
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
</body>
</html>