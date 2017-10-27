<?php
$error = "";
$editError = "";
$editSuccess = "";
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

if(isset($_POST["DESTROY_STUDENT_REAL"])) {
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE ID = :id AND COACH = :coach");
	$stmt->execute(array('id' => $_POST["DESTROY_STUDENT_REAL"],
						 'coach' => $_SESSION['login_user']));
	$row = $stmt->fetch();
	if(isset($row["NAME"])) {
		$stmt = $conn->prepare("DELETE FROM STUDENT$ WHERE ID = :id AND COACH = :coach");
		$stmt->execute(array('id' => $_POST["DESTROY_STUDENT_REAL"],
							 'coach' => $_SESSION['login_user']));
							 
		$stmt = $conn->prepare("DELETE FROM DATA WHERE LINKED_ID = :id");
		$stmt->execute(array('id' => $_POST["DESTROY_STUDENT_REAL"]));
		$editSuccess = 'Student "' . $row["NAME"] . '" deleted forever.';
	}
}

if(isset($_POST["DELETE"])) {
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE ID = :id AND COACH = :coach");
	$stmt->execute(array('id' => $_POST["DELETE"],
						 'coach' => $_SESSION['login_user']));
	$row = $stmt->fetch();
	if(isset($row["NAME"])) {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Destroy Student</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="../css/style.css">
	
</head>
<body>
<div id="navbar">
	<div id="exit" style="margin: 0;">
		<a href="students.php" class="headlink" style="border-radius: 0 0 30px 0;"><div class="textheadlink">No, take me back!</div></a>
	</div>
</div>
<div class="pseudobody">
	<h1>Are you sure?</h1>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
		<p>You are about to destroy the student "<?php echo($row["NAME"]);?>"!</p>
		<p>Are you REALLY sure you want to do this?</p>
		<form method="post">
			<input type="hidden" name="DESTROY_STUDENT_REAL" value="<?php echo($row["ID"]);?>">
			<div class="padding"><button id="yes" name="submit" type="submit" value="submit">Yes, delete <?php echo($row["NAME"]);?> forever (A long time!)</button></div>
		</form>
	</div>
</div>
</body>
</html>
<?php
		die();
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Edit Students</title>
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
<div id="body">
	<h1>Edit Students</h1>
	<?php
	if($error !== "") {echo("<span>$error</span>");}
	if($editError !== "") {echo("<span>$editError</span>");}
	if($editSuccess !== "") {echo("<p style=\"color:green; text-align: center;\">$editSuccess</p>");}
	?>
	<table>
		<style>
			button{margin-top:0}
		</style>
		<tr>
			<th>Name</th>
			<th>Student ID</th>
			<th>Gender</th>
			<th></th>
			<th>Bench</th>
			<th>Deadlift</th>
			<th>Backsquat</th>
			<th colspan="2" style="min-width: 150px !important;">Actions</th>
		</tr>
<?php
$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE COACH = :coach AND SEMESTER = :semester AND PERIOD = :period ORDER BY RIGHT(TRIM(NAME), LOCATE(' ', REVERSE(TRIM(NAME))) - 1)");
$stmt->execute(array('coach' => $_SESSION['login_user'],
					 'semester' => $_SESSION["SEMESTER_GLOBAL"],
					 'period' => $_SESSION["PERIOD_GLOBAL"]));
$all = $stmt->fetchAll();
foreach($all as $row) {
?>
		<tr>
			<td rowspan="2"><?php echo($row["NAME"]); ?></td>
			<td rowspan="2"><?php echo($row["STUDENT_ID"]); ?></td>
			<td rowspan="2"><?php echo($row["GENDER"] == "F" ? "Female" : "Male"); ?></td>
			<td style="background-color: black; color:white; border: 2px solid grey;">Pre:</td>
			<td><?php echo($row["BASE_BENCH"] == 0 ? "Not Entered" : $row["BASE_BENCH"]); ?></td>
			<td><?php echo($row["BASE_DEADLIFT"] == 0 ? "Not Entered" : $row["BASE_DEADLIFT"]); ?></td>
			<td><?php echo($row["BASE_BACKSQUAT"] == 0 ? "Not Entered" : $row["BASE_BACKSQUAT"]); ?></td>
			<td rowspan="2"><form method="post" action="edit.php"><button name="EDIT" type="submit" value="<?php echo($row["ID"]); ?>">Edit</button></form></td>
			<td rowspan="2"><form method="post"><button name="DELETE" type="submit" value="<?php echo($row["ID"]); ?>">Delete</button></form></td>
			
		</tr>
		<tr>
			<td style="background-color: black; color:white; border: 2px solid grey;">Post:</td>
			<td><?php echo($row["POST_BENCH"] == 0 ? "Not Entered" : $row["POST_BENCH"]); ?></td>
			<td><?php echo($row["POST_DEADLIFT"] == 0 ? "Not Entered" : $row["POST_DEADLIFT"]); ?></td>
			<td><?php echo($row["POST_BACKSQUAT"] == 0 ? "Not Entered" : $row["POST_BACKSQUAT"]); ?></td>
		</tr>
<?php
}
?>
		<tr>
			<td colspan="9">
				<a href="create.php" class="headlink" style="height: 52px;"><div class="textheadlink">Add Student</div></a>
			</td>
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