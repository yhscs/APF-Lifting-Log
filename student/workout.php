<?php
$STUDENT_PAGE = true;
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

$selectedWeek = $_POST["WEEK_GLOBAL"];
$_SESSION["WEEK_GLOBAL"] = $_POST["WEEK_GLOBAL"];
if(!is_numeric($selectedWeek) || $selectedWeek <= 0 || $selectedWeek > 12) {
	$selectedWeek = "1";
	$_SESSION["WEEK_GLOBAL"] = "1";
}

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

$reps = 0;
$adder = 0;

if((int)$_SESSION["WEEK_GLOBAL"] % 3 == "1") {
	$reps = 8;
}
if((int)$_SESSION["WEEK_GLOBAL"] % 3 == "2") {
	$reps = 6;
	$adder = 0.05;
}
if((int)$_SESSION["WEEK_GLOBAL"] % 3 == "0") {
	$reps = 4;
	$adder = 0.1;
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
?>
<!DOCTYPE html>
<html>
<head>
	<title>Projector</title>
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
	<div id="dropdowns" style="margin: 0;">
		<form method="post" style="float: right;">
		<select name='WEEK_GLOBAL' onchange='if(this.value != 0) {loading(); this.form.submit();}'>
<?php foreach($weekBuilder as $rows) {echo("\t\t\t" . $rows . "\n");} ?>
		</select>
	</form>
</div>
</div>
<span style="color: white; font-size: 24px;"><br>Units are in pounds to the nearest 5<br>3x<?php echo($reps); ?>: 3 sets of <?php echo($reps); ?><br>MAX: Do as many lifts as possible. The number of lifts that you do is your "AMRAP"<br>AMRAP: "As many repetitions as possible"</span>
<?php
$quarry = "SELECT * FROM DATA WHERE (WEEK = :week";
$params = array();
$params["week"] = $_SESSION["WEEK_GLOBAL"];
for($i = 1; $i <= 3; $i++) {
	if($_SESSION["WEEK_GLOBAL"] > $i * 3) {
		$quarry = $quarry . " OR WEEK = :week$i";
		$params["week$i"] = ($i * 3 - 2) . "";
	}
}
$quarry = $quarry . ") AND (LINKED_ID = :student);";
$params["student"] = $row["ID"];
$stmt = $conn->prepare($quarry);
$stmt->execute($params);
$data = $stmt->fetchAll();

$repsLargerThanAdderForDeadlift = 0;
$repsLargerThanAdderForBench = 0;
$repsLargerThanAdderForBacksquat = 0;

$canCompute = 0;
$canComputeNeedsToBe = 0;
for($i = 1; $i <=3; $i++) {
	if($_SESSION["WEEK_GLOBAL"] > $i * 3) {
		$canComputeNeedsToBe++;
		foreach($data as $dataRow) {
			if($dataRow["LINKED_ID"] === $row["ID"] && $dataRow["WEEK"] == ($i * 3 - 2) . "") {
				$canCompute++;
				if($dataRow["BENCH"] > 7) {
					$repsLargerThanAdderForBench += 10;
				}
				if($dataRow["DEADLIFT"] > 7) {
					$repsLargerThanAdderForDeadlift += 10;
				}
				if($dataRow["BACKSQUAT"] > 7) {
					$repsLargerThanAdderForBacksquat += 10;
				}
				break;
			}
		}
	} else {
		break;
	}
}
$echoed = FALSE;
foreach($data as $dataRow) {
	if($dataRow["LINKED_ID"] === $row["ID"] && $dataRow["WEEK"] == $_SESSION["WEEK_GLOBAL"]) {
		$echoed = TRUE;
		$benchcolor = ($dataRow["BENCH"] == 0 ? "#FF6" : "#9F9");
		$deadcolor = ($dataRow["DEADLIFT"] == 0 ? "#FF6" : "#9F9");
		$squatcolor = ($dataRow["BACKSQUAT"] == 0 ? "#FF6" : "#9F9");
		if($canComputeNeedsToBe === $canCompute) { #We have data from this week and the weeks prior
?>
<table class="huge">
	<tr>
		<th colspan="4">Bench</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td style="background-color: <?php echo $benchcolor ?>;"><?php echo($dataRow["BENCH"]); ?></td>
	</tr>
</table>

<table class="huge">
	<tr>
		<th colspan="4">Deadlift</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td style="background-color: <?php echo $deadcolor ?>;"><?php echo($dataRow["DEADLIFT"]); ?></td>
	</tr>
</table>

<table class="huge">
	<tr>
		<th colspan="4">Backsquat</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td style="background-color: <?php echo $squatcolor ?>;"><?php echo($dataRow["BACKSQUAT"]); ?></td>
	</tr>
</table>
<?php
		} else { #We have data from this week but one or more of the weeks prior are missing. Usually should not happen.
?>
<table class="huge">
	<tr>
		<th colspan="4">Bench</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>	
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: <?php echo $benchcolor ?>;"><?php echo($dataRow["BENCH"]); ?></td>
	</tr>
</table>

<table class="huge">
	<tr>
		<th colspan="4">Deadlift</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: <?php echo $deadcolor ?>;"><?php echo($dataRow["DEADLIFT"]); ?></td>
	</tr>
</table>

<table class="huge">
	<tr>
		<th colspan="4">Backsquat</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: <?php echo $squatcolor ?>"><?php echo($dataRow["BACKSQUAT"]); ?></td>
	</tr>
</table>
<?php
		}
		break;
	}
}
if($echoed == FALSE) {
	if($canComputeNeedsToBe === $canCompute) { #We don't have data from this week, but we have data from the weeks prior.
?>
<table class="huge">
	<tr>
		<th colspan="4">Bench</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>	
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td style="background-color: red;">Missing!</td>
	</tr>
</table>

<table class="huge">
	<tr>
		<th colspan="4">Deadlift</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td style="background-color: red;">Missing!</td>
	</tr>
</table>

<table class="huge">
	<tr>
		<th colspan="4">Backsquat</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td style="background-color: red;">Missing!</td>
	</tr>
</table>
<?php
	} else { #We don't have any data.
?>
<table class="huge">
	<tr>
		<th colspan="4">All Workouts</th>
	</tr>
	<tr>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
	<tr>
		<td colspan="4">Please enter your "As Many Reps  As Possible" from the previous week(s) to obtain lift data!</td>
	</tr>
</table>
<?php
	}
}
?>


	<tr>
		<td>65</td>
		<td>75</td>
		<td>85</td>
		<td style="background-color: red;">Missing!</td>
	</tr>
</table>
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