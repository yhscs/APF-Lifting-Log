<?php
$weekNeeded = TRUE;
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.
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
	<?php include '../../require_select_iron.php';?>
</div>
<?php
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
?>
<div style="display: block; overflow: auto;" >
<table style="margin-bottom: 0;">
	<tr>
		<th>Name</th>
		<th colspan="4">Bench</th>
		<th colspan="4">Dead Lift</th>
		<th colspan="4">Back Squat</th>
	</tr><tr>
		<th>Reps</th>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
		<th colspan="2">3x<?php echo($reps); ?></th>
		<th>MAX</th>
		<th>AMRAP</th>
	</tr>
<?php
$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period");
$stmt->execute(array('coach' => $_SESSION['login_user'],
					 'semester' => $_SESSION["SEMESTER_GLOBAL"],
					 'period' => $_SESSION["PERIOD_GLOBAL"]));
$all = $stmt->fetchAll();

if($stmt->rowCount() == 0) {
?>
	<tr>
		<td colspan="13" style="text-align: center;">There is nobody here!</td>
	</tr>
</table>
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
<?php	
	die();
}

$quarry = "SELECT * FROM DATA WHERE (WEEK = :week";
$params = array();
$params["week"] = $_SESSION["WEEK_GLOBAL"];
for($i = 1; $i <= 3; $i++) {
	if($_SESSION["WEEK_GLOBAL"] > $i * 3) {
		$quarry = $quarry . " OR WEEK = :week$i";
		$params["week$i"] = ($i * 3 - 2) . "";
	}
}

$i = 0;
$quarry = $quarry . ") AND (";
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

foreach($all as $row) {
	$repsLargerThanAdderForDeadlift = 0;
	$repsLargerThanAdderForBench = 0;
	$repsLargerThanAdderForBacksquat = 0;
	
	$canCompute = 0;
	$canComputeNeedsToBe = 0;
	for($i = 1; $i <=3; $i++) {
		if($_SESSION["WEEK_GLOBAL"] > $i * 3) {
			$canComputeNeedsToBe++;
			foreach($alreadyHasData as $dataRow) {
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
	foreach($alreadyHasData as $dataRow) {
		if($dataRow["LINKED_ID"] === $row["ID"] && $dataRow["WEEK"] == $_SESSION["WEEK_GLOBAL"]) {
			$echoed = TRUE;
			$benchcolor = ($dataRow["BENCH"] == 0 ? "#FF6" : "#9F9");
			$deadcolor = ($dataRow["DEADLIFT"] == 0 ? "#FF6" : "#9F9");
			$squatcolor = ($dataRow["BACKSQUAT"] == 0 ? "#FF6" : "#9F9");
			if($canComputeNeedsToBe === $canCompute) { #We have data from this week and the weeks prior
?>
	<tr>
		<td><?php echo($row["NAME"]); ?></td>

		<td><?php echo((int)(($row["BASE_BENCH"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td style="background-color: <?php echo $benchcolor ?>;"><?php echo($dataRow["BENCH"]); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td style="background-color: <?php echo $deadcolor ?>;"><?php echo($dataRow["DEADLIFT"]); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td style="background-color: <?php echo $squatcolor ?>;"><?php echo($dataRow["BACKSQUAT"]); ?></td>
	</tr>
<?php
			} else { #We have data from this week but one or more of the weeks prior are missing. Usually should not happen.
?>
	<tr>
		<td><?php echo($row["NAME"]); ?></td>
		
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: <?php echo $benchcolor ?>;"><?php echo($dataRow["BENCH"]); ?></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: <?php echo $deadcolor ?>;"><?php echo($dataRow["DEADLIFT"]); ?></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: red;"></td>
		<td style="background-color: <?php echo $squatcolor ?>;"><?php echo($dataRow["BACKSQUAT"]); ?></td>
	</tr>
<?php
			}
			break;
		}
	}
	if($echoed == FALSE) {
		if($canComputeNeedsToBe === $canCompute) { #We don't have data from this week, but we have data from the weeks prior.
?>
	<tr>
		<td><?php echo($row["NAME"]); ?></td>

		<td><?php echo((int)(($row["BASE_BENCH"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td><?php echo((int)(($row["BASE_BENCH"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBench); ?></td>
		<td style="background-color: red;">Missing!</td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td><?php echo((int)(($row["BASE_DEADLIFT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForDeadlift); ?></td>
		<td style="background-color: red;">Missing!</td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.55 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.65 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td><?php echo((int)(($row["BASE_BACKSQUAT"] * (0.75 + $adder))/5 + 0.5)*5 + $repsLargerThanAdderForBacksquat); ?></td>
		<td style="background-color: red;">Missing!</td>
	</tr>
<?php
		} else { #We don't have any data.
?>
	<tr>
		<td><?php echo($row["NAME"]); ?></td>
		<td colspan="12">Please enter your "As Many Reps  As Possible" from the previous week(s) to obtain lift data!</td>
	</tr>
<?php
		}
	}
}
?>
</table>
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