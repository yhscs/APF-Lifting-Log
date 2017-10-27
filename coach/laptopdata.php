<?php
$skip = true; 
$error = "";
$editError = "";
$editSuccess = "";
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.
if($_SESSION['valid'] === "Coach" || $_SESSION['valid'] === "Laptop") {
	if(!isset($_SESSION["SEMESTER_GLOBAL"]) || !isset($_SESSION["PERIOD_GLOBAL"]) || !isset($_SESSION["WEEK_GLOBAL"])) {
		header("Location: laptop.php");
		die();
	}
} else {
	logout();
	die();	
}
$_SESSION['valid'] = "Laptop";
$selectedWeek = $_SESSION["WEEK_GLOBAL"];

$usingTemp = "NO";
if(isset($_POST["WEEK_LOCAL"]) && is_numeric($_POST["WEEK_LOCAL"]) && $_POST["WEEK_LOCAL"] > 0 && $_POST["WEEK_LOCAL"] <= 12 &&  $_POST["WEEK_LOCAL"] !== $_SESSION["WEEK_GLOBAL"]) {
	$selectedWeek = (int)$_POST["WEEK_LOCAL"];
	$usingTemp = "YES";
}

if(isset($_POST["WEEK_LOCAL_PAGE"]) && is_numeric($_POST["WEEK_LOCAL_PAGE"]) && $_POST["WEEK_LOCAL_PAGE"] > 0 && $_POST["WEEK_LOCAL_PAGE"] <= 12 &&  $_POST["WEEK_LOCAL_PAGE"] !== $_SESSION["WEEK_GLOBAL"]) {
	$selectedWeek = (int)$_POST["WEEK_LOCAL_PAGE"];
	$usingTemp = "ULTRA_TEMP";
}

if(isset($_POST["DEADLIFT"]) && isset($_POST["BENCH"]) && isset($_POST["SQUAT"]) && isset($_POST["STUDENT_ID"]) && isset($_POST["NAME"])) {
	if(is_numeric($_POST["DEADLIFT"]) && is_numeric($_POST["BENCH"]) && is_numeric($_POST["SQUAT"])) {
		$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period AND STUDENT_ID = :student_id");
		$stmt->execute(array('coach' => $_SESSION['login_user'],
							 'semester' => $_SESSION["SEMESTER_GLOBAL"],
							 'period' => $_SESSION["PERIOD_GLOBAL"],
							 'student_id' => $_POST["STUDENT_ID"]));
		$row = $stmt->fetch();
		if(isset($row["ID"])) {
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
			$editError = "Either you don't exist or you entered your student ID incorrectly!";
		}
	} else {
		$editError = "Enter a number next time, please!";
	}
}

if($usingTemp === "ULTRA_TEMP") {
	$selectedWeek = $_SESSION["WEEK_GLOBAL"];
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

$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period ORDER BY RIGHT(TRIM(NAME), LOCATE(' ', REVERSE(TRIM(NAME))) - 1)");
$stmt->execute(array('coach' => $_SESSION['login_user'],
					 'semester' => $_SESSION["SEMESTER_GLOBAL"],
					 'period' => $_SESSION["PERIOD_GLOBAL"]));
$all = $stmt->fetchAll();

if($stmt->rowCount() == 0) {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Aww...</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
<div id="body">
	<h1>There's nobody here!</h1>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
		<p>Sorry, but you never added anyone to this period. Please login and add some students first!</p> 
	</div>
	<div class="center">
		<div style="margin: 20px; height: 100px;">
			<a href="../logout.php" class="headlink"><div class="textheadlink">Leave this page and return to the login screen.</div></a>
		</div>
	</div>
</div>
</body>
<?php
die();
}

$quarry = "SELECT * FROM DATA WHERE WEEK = :week AND (";
$params = array();
$params["week"] = $selectedWeek;
$i = 0;
foreach($all as $row) {
	if ($row !== end($all)) {
		$quarry = $quarry . "LINKED_ID = :p$i OR ";
		$params["p$i"] = $row["ID"];
	} else {
		$quarry = $quarry . "LINKED_ID = :p$i);";
		$params["p$i"] = $row["ID"];
	}
	$i++;
}
$stmt = $conn->prepare($quarry);
$stmt->execute($params);
$alreadyHasData = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Laptop</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<script>
	var confirmOnPageExit = function (e) {
		e = e || window.event; // If we haven't been passed the event get the window.event
		var message = 'Leaving this page will require the Coach to login again!';
		if (e) { // For IE6-8 and Firefox prior to version 4
			e.returnValue = message;
		}
		return message; // For Chrome, Safari, IE8+ and Opera 12+
	};
	window.onbeforeunload = confirmOnPageExit;
	function loading() { 
		document.getElementById("loading").style.display="block";
	}
	</script>
</head>
<body>
<div id="body">
	<h1><?php
echo($_SESSION["SEMESTER_GLOBAL"] . ", " . $_SESSION["PERIOD_GLOBAL"] . "<br>Suggested week: " . $_SESSION["WEEK_GLOBAL"]);
if($usingTemp === "YES") {
	echo("<br>Selected  week: " . $selectedWeek);			
} ?></h1>
	<div class="center">
		<?php
		if($error !== "") {echo("<span>$error</span>");}
		if($editError !== "") {echo("<span>$editError</span>");}
		if($editSuccess !== "") {echo("<p style=\"color:green;\">$editSuccess</p>");}
		?>
	</div>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
		<span>Leaving this page will require the Coach to login again!</span>
	</div>
	<div class="center">
		<h3 class="titlepadding">If needed, you can change the week here.</h3>
		<form method="post">
			<select name='WEEK_LOCAL'  class="classestext" onchange='if(this.value != 0) {loading(); window.onbeforeunload = null; this.form.submit();}'>
<?php foreach($weekBuilder as $row) {echo("\t\t\t\t" . $row . "\n");} ?>
			</select>
		</form>
	</div>
	<form method="post" autocomplete="off">
		<table>
			<tr>
				<th>Name</th>
				<th>Workout</th>
			</tr><tr>
				<td>
<?php
					foreach($all as $row) {
						$echoed = FALSE;
						foreach($alreadyHasData as $rowDone) {
							if($rowDone["LINKED_ID"] === $row["ID"]) {
								$echoed = TRUE; ?>
					<label style="background-color: lime; width: 200px; display: block; padding: 3px;"><input type="radio" name="NAME" value="<?php echo($row["ID"]); ?>"><?php echo($row["NAME"]); ?></label>
<?php
							}
						}
						if($echoed === FALSE) { ?>
					<label style="width: 200px; display: block; padding: 3px;"><input type="radio" name="NAME" value="<?php echo($row["ID"]); ?>"><?php echo($row["NAME"]); ?></label>
<?php
						}
					}
					?>
				</td>
				<td>
					<h3 class="titlepadding">Bench</h3>
					<input class="text" 		type="number" 		name="BENCH" 			placeholder="Enter Reps Here"><br>
					<h3 class="titlepadding">Dead Lift</h3>
					<input class="text" 		type="number" 		name="DEADLIFT" 		placeholder="Enter Reps Here"><br>
					<h3 class="titlepadding">Squat</h3>
					<input class="text" 		type="number" 		name="SQUAT" 			placeholder="Enter Reps Here"><br>
					<h3 class="titlepadding">Student ID</h3>
					<input class="text" 		type="password" 	name="STUDENT_ID" 		placeholder="Student ID" ><br>
<?php
if($usingTemp === "YES") {
?>					<input class="text" 		type="hidden" 		name="WEEK_LOCAL_PAGE" 	value="<?php echo $selectedWeek; ?>"><br>
<?php } ?>
					<div class="padding"><input type="submit" value="Save!" onClick="window.onbeforeunload = null;"></div>
				</td>
			</tr>
		</table>
	</form> 
</div>
<div class="center">
	<div style="margin: 80px 0 20px; height: 100px;">
		<a href="../logout.php" class="headlink"><div class="textheadlink">Leave this page and return to the login screen.</div></a>
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