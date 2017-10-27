<?php 
	header("Location: student.php"); 
	die(); #Comment this line to get a button page. 
?>
<!DOCTYPE html>
<html>
<header>
	<title>Select</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="css/style.css">
</header>
<body>
	<div id="main">
		<div id="login">
			<h1>Yorkville SC</h1>
			<br>
			<h3>Are you a student or a coach?</h3>
			<form action="student.php">
				<div class="padding"><input type="submit" value=" Student "></div>
			</form>
			<form action="coach.php">
				<div class="padding"><input type="submit" value=" Coach "></div>
			</form>
		</div>
	</div>
</body>
</html>