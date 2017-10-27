<?php
$conn = null;
$error = "";
$editError = "";
$editSuccess = "";
$semesterBuilder = array();
$periodBuilder = array();

include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

$stmt = $conn->prepare("SELECT DISTINCT SEMESTER FROM CLASS WHERE COACH = :coach");
$stmt->execute(array('coach' => $_SESSION['login_user']));
$all = $stmt->fetchAll();
foreach($all as $row) {
	$semesterBuilder[] = 
	'<option value="' . $row['SEMESTER'] .
	'" style="width: 100%;">' . $row['SEMESTER'] . '</option>';
}

do {
if(isset($_POST["DESTROY_SEMESTER_REAL"]) && isset($_POST["password"])) {
	$stmt = $conn->prepare("SELECT USERNAME, PASSWORD FROM COACH WHERE USERNAME = :username");
	$stmt->execute(array('username' => $_SESSION['login_user']));
	$row = $stmt->fetch();
	
	if(!(password_verify($_POST["password"], $row["PASSWORD"]))) {
		$error = "When you tried to destroy the semester, the password was incorrect!"; 
		break;
	}
	$stmt = $conn->prepare("DELETE FROM CLASS WHERE COACH = :coach AND SEMESTER = :semester");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["DESTROY_SEMESTER_REAL"]));
	
	$stmt = $conn->prepare("SELECT ID FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["DESTROY_SEMESTER_REAL"]));
	$all = $stmt->fetchAll();
	
	if($stmt->rowCount() > 0) {
		$quarry = "DELETE FROM DATA WHERE ";
		$params =  array();
		$i = 0;
		foreach($all as $row) {
			if ($row !== end($all)) {
				$quarry = $quarry . "LINKED_ID = :p$i OR ";
				$params["p$i"] = $row["ID"];
			} else {
				$quarry = $quarry . "LINKED_ID = :p$i";
				$params["p$i"] = $row["ID"];
			}
			$i++;
		}
		$stmt = $conn->prepare($quarry);
		$stmt->execute($params);
	}
	
	$stmt = $conn->prepare("DELETE FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["DESTROY_SEMESTER_REAL"]));
						 
	$editSuccess = "Done, the semester " . $_POST["DESTROY_SEMESTER_REAL"] . " is gone forever.";
	unset($_SESSION["SEMESTER_GLOBAL"]);
	unset($_SESSION["PERIOD_GLOBAL"]);
}

if(isset($_POST["DESTROY_PERIOD_FROM_SEMESTER_REAL"]) && isset($_POST["DESTROY_PERIOD_REAL"])  && isset($_POST["password"])) {
	$stmt = $conn->prepare("SELECT USERNAME, PASSWORD FROM COACH WHERE USERNAME = :username");
	$stmt->execute(array('username' => $_SESSION['login_user']));
	$row = $stmt->fetch();
	
	if(!(password_verify($_POST["password"], $row["PASSWORD"]))) {
		$error = "When you tried to destroy the period, the password was incorrect!"; 
		break;
	}
	$stmt = $conn->prepare("DELETE FROM CLASS WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["DESTROY_PERIOD_FROM_SEMESTER_REAL"],
						 'period' => $_POST["DESTROY_PERIOD_REAL"]));
	
	$stmt = $conn->prepare("SELECT ID FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["DESTROY_PERIOD_FROM_SEMESTER_REAL"],
						 'period' => $_POST["DESTROY_PERIOD_REAL"]));
	$all = $stmt->fetchAll();
	
	if($stmt->rowCount() > 0) {
		$quarry = "DELETE FROM DATA WHERE ";
		$params =  array();
		$i = 0;
		foreach($all as $row) {
			if ($row !== end($all)) {
				$quarry = $quarry . "LINKED_ID = :p$i OR ";
				$params["p$i"] = $row["ID"];
			} else {
				$quarry = $quarry . "LINKED_ID = :p$i";
				$params["p$i"] = $row["ID"];
			}
			$i++;
		}
		$stmt = $conn->prepare($quarry);
		$stmt->execute($params);
	}
	
	$stmt = $conn->prepare("DELETE FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["DESTROY_PERIOD_FROM_SEMESTER_REAL"],
						 'period' => $_POST["DESTROY_PERIOD_REAL"]));
						 
						 
	$editSuccess = "Done, the period " . $_POST["DESTROY_PERIOD_REAL"] . " is gone forever.";
	unset($_SESSION["SEMESTER_GLOBAL"]);
	unset($_SESSION["PERIOD_GLOBAL"]);
}

if((isset($_POST["CREATE_SEMESTER_NAME"]) || isset($_POST["CREATE_SEMESTER_SELECT"])) && isset($_POST["CREATE_SEMESTER_PERIOD"])) {
	$stmt = $conn->prepare("SELECT SEMESTER, PERIOD FROM CLASS WHERE COACH = :coach");
	$stmt->execute(array('coach' => $_SESSION['login_user']));
	$all = $stmt->fetchAll();
	
	if($_POST["CREATE_SEMESTER_SELECT"] !== "NOTHING") {
		$_POST["CREATE_SEMESTER_NAME"] = $_POST["CREATE_SEMESTER_SELECT"];
	}
	foreach($all as $row) {
		if(strtolower($_POST["CREATE_SEMESTER_NAME"]) === strtolower($row["SEMESTER"]) ||
		   strtolower($_POST["CREATE_SEMESTER_SELECT"]) === strtolower($row["SEMESTER"])) {
			if(strtolower($_POST["CREATE_SEMESTER_PERIOD"]) === strtolower($row["PERIOD"])) {
				$editError = "That semester and period combination already exists!";
				break 2;
			}
		}
	}
	
	if(trim($_POST["CREATE_SEMESTER_NAME"]) === "" || trim($_POST["CREATE_SEMESTER_PERIOD"]) === "" ||
	   trim($_POST["CREATE_SEMESTER_NAME"]) === "NOTHING" || trim($_POST["CREATE_SEMESTER_PERIOD"]) === "NOTHING") {
		$editError = "The name has to at least be 1 character.";
		break;
	}
	
	$stmt = $conn->prepare("INSERT INTO CLASS (COACH, SEMESTER, PERIOD) VALUES (:coach, :semester, :period)");
	$stmt->execute(array('coach' => $_SESSION['login_user'],
						 'semester' => $_POST["CREATE_SEMESTER_NAME"],
						 'period' => $_POST["CREATE_SEMESTER_PERIOD"]));
						 
	$editSuccess = 'Whoo, you created the period "' . $_POST["CREATE_SEMESTER_PERIOD"] . '" under the semester "' . $_POST["CREATE_SEMESTER_NAME"] . '" successfully!';
}

if(isset($_POST["DESTROY_SEMESTER"]) && $_POST["DESTROY_SEMESTER"] !== "NOTHING") {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Destroy Semester</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
<div id="navbar">
	<div id="exit" style="margin: 0;">
		<a href="classes.php" class="headlink" style="border-radius: 0 0 30px 0;"><div class="textheadlink">No, take me back!</div></a>
	</div>
</div>
<div class="pseudobody">
	<h1>Are you sure?</h1>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
		<p>You are about to destroy the semester "<?php echo($_POST["DESTROY_SEMESTER"]);?>" FOREVER!</p>
		<p>This will delete all the students and their data in this semester as well.</p><br>
		<p>Are you REALLY sure you want to do this?</p>
		<form method="post">
			<input type="hidden" name="DESTROY_SEMESTER_REAL" value="<?php echo($_POST["DESTROY_SEMESTER"]);?>">
			<h3>Password: </h3><div class="padding"><input type="password" id="password" name="password" autocomplete="off"></div><br>
			<div class="padding"><button id="yes" type="submit">Yes, delete <?php echo($_POST["DESTROY_SEMESTER"]);?> forever (A long time!)</button></div>
		</form>
		<span id="hide" style="font-size:40px;">Wait 5 more seconds...</span>
	</div>
</div>
<script>
document.getElementById("yes").style.display = "none";
var time = 5;
function countDown() {
	if(time > 1) {
		document.getElementById("hide").innerHTML = "Wait "  + time + " more seconds...";
		time = time - 1;
		setTimeout(countDown,1000);
	} else if(time == 1) {
		document.getElementById("hide").innerHTML = "Wait 1 more second...";
		time = time - 1;
		setTimeout(countDown,1000);
	} else {
		document.getElementById("yes").style.display = "block"; 
		document.getElementById("hide").style.display = "none";
	}
}
countDown();
</script>
</body>
<?php
die();
}

if(isset($_POST["DESTROY_PERIOD_FROM_SEMESTER"]) && $_POST["DESTROY_PERIOD_FROM_SEMESTER"] !== "NOTHING" &&
   isset($_POST["DESTROY_PERIOD"]) && $_POST["DESTROY_PERIOD"] !== "NOTHING" &&
   isset($_POST["DELETE"]) && $_POST["DELETE"] === "USER") {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Destroy Period</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
<div id="navbar">
	<div id="exit" style="margin: 0;">
		<a href="classes.php" class="headlink" style="border-radius: 0 0 30px 0;"><div class="textheadlink">No, take me back!</div></a>
	</div>
</div>
<div class="pseudobody">
	<h1>Are you sure?</h1>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
		<p>You are about to destroy the period "<?php echo($_POST["DESTROY_PERIOD"]);?>" from "<?php echo($_POST["DESTROY_PERIOD_FROM_SEMESTER"]);?>" FOREVER!</p>
		<p>This will delete all the students and their data in this period as well.</p><br>
		<p>Are you REALLY sure you want to do this?</p>
		<form method="post">
			<input type="hidden" name="DESTROY_PERIOD_FROM_SEMESTER_REAL" value="<?php echo($_POST["DESTROY_PERIOD_FROM_SEMESTER"]);?>">
			<input type="hidden" name="DESTROY_PERIOD_REAL" value="<?php echo($_POST["DESTROY_PERIOD"]);?>">
			<h3>Password: </h3><div class="padding"><input type="password" id="password" name="password" autocomplete="off"></div><br>
			<div class="padding"><button id="yes" type="submit">Yes, delete "<?php echo($_POST["DESTROY_PERIOD"]);?>" from "<?php echo($_POST["DESTROY_PERIOD_FROM_SEMESTER"]);?>" forever (A long time!)</button></div>
		</form>
		<span id="hide" style="font-size:40px;">Wait 5 more seconds...</span>
	</div>
</div>
<script>
document.getElementById("yes").style.display = "none";
var time = 5;
function countDown() {
	if(time > 1) {
		document.getElementById("hide").innerHTML = "Wait "  + time + " more seconds...";
		time = time - 1;
		setTimeout(countDown,1000);
	} else if(time == 1) {
		document.getElementById("hide").innerHTML = "Wait 1 more second...";
		time = time - 1;
		setTimeout(countDown,1000);
	} else {
		document.getElementById("yes").style.display = "block"; 
		document.getElementById("hide").style.display = "none";
	}
}
countDown();
</script>
</body>
<?php
die();
}

}while(0);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Classes</title>
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
		<a href="index.php" class="headlink" style="border-radius: 0 0 30px 0;"><div class="textheadlink">Exit</div></a>
	</div>
</div>

<div class="pseudobody">
	<h1>Create</h1>
	<div class="center">
		<div class="padding">
			<form method="post">
				<?php
				if($error !== "") {echo("<span>$error</span>");}
				if($editError !== "") {echo("<span>$editError</span>");}
				if($editSuccess !== "") {echo("<p style=\"color:green;\">$editSuccess</p>");}
				?>
				<h3 class="titlepadding">Create new semester with the name...</h3>
				<input class="text" 		
					   type="text" 		
					   name="CREATE_SEMESTER_NAME" 
					   placeholder="ex: Spring 2016"><br>
				
				<h3 class="titlepadding">...OR select an existing semester from this drop-down list...</h3>
				<select name='CREATE_SEMESTER_SELECT' class="classestext">
					<option value='NOTHING' style="width: 100%;">Click to select an existing semester</option>
					<?php
					foreach($semesterBuilder as $row) {echo($row);}
					?> 
				</select>
				<br><br><br>			
				<h3 class="titlepadding">...and then add the period to it...</h3>
				<input class="text"
					   type="text"
					   name="CREATE_SEMESTER_PERIOD"
					   placeholder="ex: Period 1"><br>

				<div class="padding"><input type="submit"	value="Create Period!" class="goodbutton"></div>
			</form><br>
		</div>
	</div>
</div>

<div class="pseudobody">
	<h1 id="destroy">Destroy</h1>
	<div class="center">
		<div class="padding">
			<form method="post">
				<h3 class="titlepadding">Destroy semester</h3>
				<select name='DESTROY_SEMESTER' class="classestext">
					<option value='NOTHING' style="width: 100%;">Click to select an existing semester</option>
					<?php
					foreach($semesterBuilder as $row) {echo($row);}
					?> 
				</select>
				<div class="padding"><button type="submit">Delete (Requires confirmation)</button></div>
			</form>
		</div>
	</div><br><br><br>
<?php
if(isset($_POST["DESTROY_PERIOD_FROM_SEMESTER"])) {
	$stmt = $conn->prepare("SELECT PERIOD FROM CLASS WHERE SEMESTER = :semester AND COACH = :coach");
	$stmt->execute(array('semester' => $_POST["DESTROY_PERIOD_FROM_SEMESTER"],
						 'coach' => $_SESSION['login_user']));
	$all = $stmt->fetchAll();
	foreach($all as $row) {
		$periodBuilder[] = '<option value="' . $row['PERIOD'] . '" style="width: 100%;">' . $row['PERIOD'] . '</option>';
	}
	
	$key = array_search('<option value="' .
	$_POST['DESTROY_PERIOD_FROM_SEMESTER'] . '" style="width: 100%;">' .
	$_POST['DESTROY_PERIOD_FROM_SEMESTER'] . '</option>', $semesterBuilder);
	
	if($key !== FALSE) {
		$semesterBuilder[$key] = 
		'<option selected="selected" value="' .
		$_POST['DESTROY_PERIOD_FROM_SEMESTER'] . '" style="width: 100%;">' .
		$_POST['DESTROY_PERIOD_FROM_SEMESTER'] . '</option>';
	}
}
?>	
	<div class="center">
		<div class="padding">
			<form method="post">
				<h3 class="titlepadding">Destroy period from semester...</h3>
				<select name='DESTROY_PERIOD_FROM_SEMESTER' class="classestext" onchange='if(this.value != 0) {loading(); this.form.submit();}'>
					<option value='NOTHING' style="width: 100%;">Select semester first...</option>
					<?php
					foreach($semesterBuilder as $row) {echo($row);}
					?>				
				</select>
<?php
if(isset($_POST["DESTROY_PERIOD_FROM_SEMESTER"])) {
?> 
				<select name='DESTROY_PERIOD' class="classestext">
					<option value='NOTHING' style="width: 100%;">Select a period from <?php echo($_POST["DESTROY_PERIOD_FROM_SEMESTER"]);?></option>
					<?php 
					foreach($periodBuilder as $row) {echo($row);} 
					?> 
				</select>

				<div class="padding"><button name="DELETE" type="submit" value="USER">Delete (Requires confirmation)</button></div>
<?php 
}
?>
			</form><br>
		</div>
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