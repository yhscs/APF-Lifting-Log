<?php
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.
?>
<!DOCTYPE html>
<html>
<head>
	<title>Landing</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	<script>
	function loading() { 
		document.getElementById("loading").style.display="block";
	}
	</script>
	<style>
	@media screen and (max-width: 999px) {
    #dropdowns form {
		width: 100%;
	}
	</style>
</head>
<body>
<div id="navbar">
	<div id="header" style="margin: 0;">
		<a href="projector.php" class="headlink"><div class="textheadlink">Projector View</div></a>
		<a href="laptop.php" class="headlink"><div class="textheadlink">Laptop View</div></a>
		<a href="data.php" class="headlink"><div class="textheadlink">Download Data</div></a>
		<a href="students.php" class="headlink"><div class="textheadlink">Modify Students</div></a>
		<a href="classes.php" class="headlink"><div class="textheadlink">Modify Classes</div></a>
	</div>
	<?php include '../../require_select_iron.php';?>
</div>
<div class="center">
	<div style="margin: 20px 0 20px; height: 50px;">
		<a href="../logout.php" class="headlink"><div class="textheadlink">Log Out</div></a>
	</div>
</div>
<?php
$stmt = $conn->prepare("SELECT NAME, BASE_BENCH, BASE_DEADLIFT, BASE_BACKSQUAT, POST_BENCH, POST_DEADLIFT, POST_BACKSQUAT, GENDER FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester");
$stmt->execute(array('coach' => $_SESSION['login_user'],
					 'semester' => $_SESSION["SEMESTER_GLOBAL"]));
$all = $stmt->fetchAll();

$workouts = array();
	$workouts["BASE_BENCH"] = array();
		$workouts["BASE_BENCH"]["M"] = 0;
		$workouts["BASE_BENCH"]["F"] = 0;
	$workouts["BASE_DEADLIFT"] = array();
		$workouts["BASE_DEADLIFT"]["M"] = 0;
		$workouts["BASE_DEADLIFT"]["F"] = 0;
	$workouts["BASE_BACKSQUAT"] = array();
		$workouts["BASE_BACKSQUAT"]["M"] = 0;
		$workouts["BASE_BACKSQUAT"]["F"] = 0;
	$workouts["POST_BENCH"] = array();
		$workouts["POST_BENCH"]["M"] = 0;
		$workouts["POST_BENCH"]["F"] = 0;
	$workouts["POST_DEADLIFT"] = array();
		$workouts["POST_DEADLIFT"]["M"] = 0;
		$workouts["POST_DEADLIFT"]["F"] = 0;
	$workouts["POST_BACKSQUAT"] = array();
		$workouts["POST_BACKSQUAT"]["M"] = 0;
		$workouts["POST_BACKSQUAT"]["F"] = 0;
	
$averages = array();
	$averages["BASE_BENCH"] = array();
		$averages["BASE_BENCH"]["M_COUNT"] = 0;
		$averages["BASE_BENCH"]["F_COUNT"] = 0;
	$averages["BASE_DEADLIFT"] = array();
		$averages["BASE_DEADLIFT"]["M_COUNT"] = 0;
		$averages["BASE_DEADLIFT"]["F_COUNT"] = 0;
	$averages["BASE_BACKSQUAT"] = array();
		$averages["BASE_BACKSQUAT"]["M_COUNT"] = 0;
		$averages["BASE_BACKSQUAT"]["F_COUNT"] = 0;
	$averages["POST_BENCH"] = array();
		$averages["POST_BENCH"]["M_COUNT"] = 0;
		$averages["POST_BENCH"]["F_COUNT"] = 0;
	$averages["POST_DEADLIFT"] = array();
		$averages["POST_DEADLIFT"]["M_COUNT"] = 0;
		$averages["POST_DEADLIFT"]["F_COUNT"] = 0;
	$averages["POST_BACKSQUAT"] = array();
		$averages["POST_BACKSQUAT"]["M_COUNT"] = 0;
		$averages["POST_BACKSQUAT"]["F_COUNT"] = 0;

$rawPowerPre = 0;
$rawPowerPreNum = 0;
$rawPowerPreExcluded = 0;

$rawPowerPost = 0;
$rawPowerPostNum = 0;
$rawPowerPostExcluded = 0;
foreach($all as $row) {
	if($row["BASE_BENCH"] > 0 && $row["BASE_DEADLIFT"] > 0 && $row["BASE_BACKSQUAT"] > 0) {
		$rawPowerPre += $row["BASE_BENCH"] + $row["BASE_DEADLIFT"] + $row["BASE_BACKSQUAT"];
		$rawPowerPreNum++;
	} else {
		$rawPowerPreExcluded;
	}
	if($row["POST_BENCH"] > 0 && $row["POST_DEADLIFT"] > 0 && $row["POST_BACKSQUAT"] > 0) {
		$rawPowerPost += $row["POST_BENCH"] + $row["POST_DEADLIFT"] + $row["POST_BACKSQUAT"];
		$rawPowerPostNum++;
	} else {
		$rawPowerPostExcluded ++;
	}
	
	foreach($row as $workoutname => $lbs) {
		if($workoutname != "NAME" && $workoutname != "GENDER" && $lbs > 0) {
			$workouts[$workoutname][$row["GENDER"]]  += $lbs;
			$averages[$workoutname][$row["GENDER"] . "_COUNT"] += 1;
			$averages[$workoutname]["TOTAL_COUNT"] += 1;
		}
	}
}

foreach($workouts as $workoutname => $workout) {
	foreach($workout as $gender => $totallbs) {
		$averages[$workoutname][$gender . "_AVERAGE"] = ($averages[$workoutname][$gender . "_COUNT"] == 0) ? "Not Enough Data!" :
		round($totallbs/$averages[$workoutname][$gender . "_COUNT"],1,PHP_ROUND_HALF_UP);
	}
	$divisor = $averages[$workoutname]["TOTAL_COUNT"];
	if($divisor != 0) {
		$averages[$workoutname]["TOTAL_AVERAGE"] = round((($workout["M"] + $workout["F"]) / $divisor),1,PHP_ROUND_HALF_UP);
	} else {
		$averages[$workoutname]["TOTAL_AVERAGE"] = "Not Enough Data!";
	}
}
if($rawPowerPreNum != 0) {
	$rawPowerPre = $rawPowerPre/$rawPowerPreNum;
}
if($rawPowerPostNum != 0) {
	$rawPowerPost = $rawPowerPost/$rawPowerPostNum;
}
?>
<div id="body">
	<h1>Results for <?php echo($_SESSION["SEMESTER_GLOBAL"]); ?>, All Periods.</h1>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
<?php
if($rawPowerPreNum != $rawPowerPostNum) {
?>
		<span>Warning! Your data might be skewed because a different amount of students took the pre test and post test!</span>
<?php
}
?>
		<p>Across all periods from the semester "<?php echo($_SESSION["SEMESTER_GLOBAL"]); ?>", <?php echo($rawPowerPreNum . ($rawPowerPreNum == 1 ? " student" : " students")); ?> took the full pre test averaging a raw power of <?php echo(round($rawPowerPre,1,PHP_ROUND_HALF_UP)); ?>, and  <?php echo($rawPowerPostNum . ($rawPowerPostNum == 1 ? " student" : " students")); ?>  took the full post test averaging a raw power of <?php echo(round($rawPowerPost,1,PHP_ROUND_HALF_UP)); ?>. The change in percent is 
		<?php if($rawPowerPre == 0) {echo("a devide by 0 error");} else {echo(round(((($rawPowerPost - $rawPowerPre)/$rawPowerPre)*100),1,PHP_ROUND_HALF_UP) . "%");} ?>.<br> Here are the breakdowns:</p>
	</div>
	
<?php
$workoutnames = array();
	$workoutnames[] = "BENCH";
	$workoutnames[] = "DEADLIFT";
	$workoutnames[] = "BACKSQUAT";
	
$human = array();
	$human[] = "Bench Press";
	$human[] = "Deadlift";
	$human[] = "Backsquat";

$i = 0;
foreach($workoutnames as $wo) {
?>
	<table>
		<tr>
			<th colspan="7"><?php echo($human[$i]); ?></th>
		</tr><tr>
			<td rowspan="2"></td>
			<td colspan="2">Pre Test</td>
			<td colspan="2">Post Test</td>
			<td rowspan="2">Weight Change</td>
			<td rowspan="2">Percent Improvement</td>
		</tr><tr>
			<td>Count</td>
			<td>Average</td>
			<td>Count</td>
			<td>Average</td>
		</tr><tr><tr>
			<td>Male</td>
			<td><?php echo($averages["BASE_" . $wo]["M_COUNT"]); ?></td>
			<td><?php echo($averages["BASE_" . $wo]["M_AVERAGE"]); ?></td>
			<td><?php echo($averages["POST_" . $wo]["M_COUNT"]); ?></td>
			<td><?php echo($averages["POST_" . $wo]["M_AVERAGE"]); ?></td>
<?php 
if($averages["POST_" . $wo]["M_AVERAGE"] == 0 || $averages["BASE_" . $wo]["M_AVERAGE"] == 0) {
?> 
			<td colspan="2">Not Enough Data!</td>
<?php
}else{
?> 
			<td><?php echo($averages["POST_" . $wo]["M_AVERAGE"] - $averages["BASE_" . $wo]["M_AVERAGE"]); ?></td>
			<td><?php echo(round(($averages["POST_" . $wo]["M_AVERAGE"] - $averages["BASE_" . $wo]["M_AVERAGE"])/$averages["BASE_" . $wo]["M_AVERAGE"]*100,1,PHP_ROUND_HALF_UP) . "%"); ?></td>
<?php
}
?> 
		</tr><tr>
			<td>Female</td>
			<td><?php echo($averages["BASE_" . $wo]["F_COUNT"]); ?></td>
			<td><?php echo($averages["BASE_" . $wo]["F_AVERAGE"]); ?></td>
			<td><?php echo($averages["POST_" . $wo]["F_COUNT"]); ?></td>
			<td><?php echo($averages["POST_" . $wo]["F_AVERAGE"]); ?></td>
<?php 
if($averages["POST_" . $wo]["F_AVERAGE"] == 0 || $averages["BASE_" . $wo]["F_AVERAGE"] == 0) {
?> 
			<td colspan="2">Not Enough Data!</td>
<?php
}else{
?> 
			<td><?php echo($averages["POST_" . $wo]["F_AVERAGE"] - $averages["BASE_" . $wo]["F_AVERAGE"]); ?></td>
			<td><?php echo(round(($averages["POST_" . $wo]["F_AVERAGE"] - $averages["BASE_" . $wo]["F_AVERAGE"])/$averages["BASE_" . $wo]["F_AVERAGE"]*100,1,PHP_ROUND_HALF_UP) . "%"); ?></td>
<?php
}
?> 
		</tr><tr>
			<td>Total</td>
			<td><?php echo($averages["BASE_" . $wo]["TOTAL_COUNT"]); ?></td>
			<td><?php echo($averages["BASE_" . $wo]["TOTAL_AVERAGE"]); ?></td>
			<td><?php echo($averages["POST_" . $wo]["TOTAL_COUNT"]); ?></td>
			<td><?php echo($averages["POST_" . $wo]["TOTAL_AVERAGE"]); ?></td>
<?php 
if($averages["POST_" . $wo]["TOTAL_AVERAGE"] == 0 || $averages["BASE_" . $wo]["TOTAL_AVERAGE"] == 0) {
?> 
			<td colspan="2">Not Enough Data!</td>
<?php
}else{
?> 
			<td><?php echo($averages["POST_" . $wo]["TOTAL_AVERAGE"] - $averages["BASE_" . $wo]["TOTAL_AVERAGE"]); ?></td>
			<td><?php echo(round(($averages["POST_" . $wo]["TOTAL_AVERAGE"] - $averages["BASE_" . $wo]["TOTAL_AVERAGE"])/$averages["BASE_" . $wo]["TOTAL_AVERAGE"]*100,1,PHP_ROUND_HALF_UP) . "%"); ?></td>
<?php
}
?> 
		</tr>
	</table>
<?php
	$i++;
}
?>
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
