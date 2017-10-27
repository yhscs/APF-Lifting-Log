<?php
$error = "";
$editError = "";
$editSuccess = "";
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.

	// Future Note: Consider PHPExcel for multiple sheets

	$stmt = $conn->prepare("SELECT DISTINCT SEMESTER FROM STUDENT$");
	$stmt->execute();
	$semesters = $stmt->fetchAll();
	
	$files = array();
	$currentFile = 0;
	
	foreach ($semesters as $semester)
	{
		$current = $semester['SEMESTER'];
		
		$stmt = $conn->prepare("SELECT PERIOD, COACH, NAME, GENDER, BASE_BACKSQUAT, BASE_BENCH, BASE_DEADLIFT, POST_BACKSQUAT, POST_BENCH, POST_DEADLIFT FROM STUDENT$ WHERE SEMESTER = :semester");
		$stmt->execute(array('semester' => $current));
		$results = $stmt->fetchAll(PDO::FETCH_NUM);
		
		// Open csv file and write column headers to csv file
		$files[$currentFile] = $current.'.csv';
		$currentFile++;
		$filePointer = fopen($current.'.csv', 'w');
		$headers = array("Period", "Teacher", "Student", "Gender", "Pre Backsquat", "Pre Bench", "Pre Deadlift", "Post Backsquat", "Post Bench", "Post Deadlift", "Gains Backsquat", "Gains Bench", "Gains Deadlift");
		fputcsv($filePointer, $headers);
		
		// Write all semester results to csv file
		foreach ($results as $result)
		{
			// Determine gains by subtrating post lift from pre lift	
			$result[10] = $result[7] - $result[4];
			$result[11] = $result[8] - $result[5];
			$result[12] = $result[9] - $result[6];
			
			fputcsv($filePointer, $result);
		}
		fclose($filePointer);
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Download Data</title>
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
</div>
<div class="center">
	<div style="margin: 20px 0 20px; height: 50px;">
		<a href="../logout.php" class="headlink"><div class="textheadlink">Log Out</div></a>
	</div>
</div>

<div id="body">
	<h1>Download Semester Spreadsheets</h1>
	<div class="center" style="font-size: 22px; margin-bottom: 20px;">
		<?php
			foreach ($files as $file)
				echo "<p><a href='$file'>$file</a></p>";
		?>
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