<?php
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

do {
if(isset($_POST["BASE_BENCH"]) && isset($_POST["BASE_BACKSQUAT"]) && isset($_POST["BASE_DEADLIFT"]) && isset($_POST["NAME"]) && isset($_POST["STUDENT_ID"]) && isset($_POST["GENDER"])) {
	if(!is_numeric($_POST["BASE_BENCH"]) || !is_numeric($_POST["BASE_BACKSQUAT"]) || !is_numeric($_POST["BASE_DEADLIFT"])) {
		$editError = "You somehow submitted text that should be a number. Try again with a number!";
		break;
	}
	if(trim($_POST["NAME"]) == "" || trim($_POST["STUDENT_ID"]) == "" || trim($_POST["GENDER"]) == "") {
		$editError = "Some fields were left empty!";
		break;
	}
	
	$stmt = $conn->prepare("SELECT * FROM STUDENT$ WHERE STUDENT_ID = :id AND COACH = :coach AND SEMESTER = :semester");
	$stmt->execute(array('id' => $_POST["STUDENT_ID"],
						 'coach' => $_SESSION['login_user'],
						 'semester' => $_SESSION["SEMESTER_GLOBAL"]));
	$row = $stmt->fetch();
	if(isset($row["NAME"])) {
		$editError = $row["NAME"] . " already uses the Student ID " . $row["STUDENT_ID"] . " in period " . $row["PERIOD"] . "! Try switching to a new semester instead.";
		break;
	}
	
	$stmt = $conn->prepare("INSERT INTO STUDENT$ (STUDENT_ID, GENDER, NAME, PERIOD, SEMESTER, COACH, BASE_BENCH, BASE_BACKSQUAT, BASE_DEADLIFT) VALUES (:studentid, :gender, :name, :period, :semester, :coach, :bench, :backsquat, :deadlift)");
	$stmt->execute(array('studentid' => $_POST["STUDENT_ID"],
					 'name' => $_POST["NAME"],
					 'gender' => trim($_POST["GENDER"]),
					 'period' => $_SESSION["PERIOD_GLOBAL"],
					 'semester' => $_SESSION["SEMESTER_GLOBAL"],
					 'coach' => $_SESSION['login_user'],
					 'bench' => $_POST["BASE_BENCH"],
					 'backsquat' => $_POST["BASE_BACKSQUAT"],
					 'deadlift' => $_POST["BASE_DEADLIFT"]));
					 
	$editSuccess = 'You created the student "' . $_POST["NAME"] . '" in semester "' . $_SESSION["SEMESTER_GLOBAL"] . '" under the period "' . $_SESSION["PERIOD_GLOBAL"] . '" successfully!';
}
}while(0)
?>
<!DOCTYPE html>
<html>
<head>
	<title>Student Creation</title>
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
	<?php include '../../require_select_iron.php';?>
</div>

<div class="pseudobody">
	<h1>Create</h1>
	<div class="center">
		<form method="post">
			<?php
			if($error !== "") {echo("<span>$error</span>");}
			if($editError !== "") {echo("<span>$editError</span>");}
			if($editSuccess !== "") {echo("<p style=\"color:green;\">$editSuccess</p>");}
			?> 
			<h3 class="titlepadding">Name</h3>
				<input class="text" 		
					   type="text" 		
					   name="NAME" 
					   placeholder="ex: Bob Joe" autofocus><br>
			
			<h3 class="titlepadding">Student ID</h3>
				<input class="text"
					   type="text"
					   name="STUDENT_ID"
					   placeholder="ex: 1714057"><br>
					   
			<h3 class="titlepadding">Gender</h3>
			<select name='GENDER' class="classestext">
				<option value='M' style="width: 100%;">Male</option>
				<option value='F' style="width: 100%;">Female</option>
			</select>
					   
			<h3 class="titlepadding">Pre Test Bench MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="BASE_BENCH"
					   placeholder="ex: 100"><br>
					   

			<h3 class="titlepadding">Pre Test Dead Lift MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="BASE_DEADLIFT"
					   placeholder="ex: 100"><br>
					   
			<h3 class="titlepadding">Pre Test Squat MAX (lbs)</h3>
				<input class="text"
					   type="number"
					   name="BASE_BACKSQUAT"
					   placeholder="ex: 100"><br>

			<div class="padding"><input type="submit"	value="Create Student!" class="goodbutton"></div>
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
</body>