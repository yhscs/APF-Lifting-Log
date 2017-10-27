<?php
include '../../verify_iron.php';
include '../../config_iron.php'; #Connect to db.
?>
<!DOCTYPE html>
<html>
<head>
	<title>Links</title>
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
	<h1>Current Links</h1>
	<form method="post">
		<table>
			<tr>
				<th>Link</th>
				<th>Description</th>
				<th>Status</th>
				<th>Select</th>
			</tr><tr>
				<td><a href="example.com">example.com</a></td>
				<td>example</td>
				<td style="background-color:#C00; color: white;">disabled</td>
				<td><label><input type="checkbox" name="enabled" value="1">Select link</label></td>
			</tr>
		</table>
		<div class="center">
			<button name="edit" value="toggle" type="submit" class="goodbutton">Toggle Selected Links</button>
			<button name="edit" value="destroy" type="submit">Destroy Selected Links</button>
		</div>
	</form>
	<br>
</div>
<div class="pseudobody">
	<h1>Edit Links</h1>
	<div class="center">
		<form method="post">
			<h3 class="titlepadding">URL</h3>
				<input class="text" 		
					   type="text" 		
					   name="url" 
					   placeholder="ex: https://www.google.com/"><br>
			
			<h3 class="titlepadding">Description</h3>
				<input class="text"
					   type="text"
					   name="description"
					   placeholder="ex: Discover websites and information with Google"><br><br>
					   
			<label><input type="checkbox" name="enabled" value="true">Mark this link as enabled</label><br><br>
			<div class="padding"><input type="submit"	value="Create link!" class="goodbutton"></div>
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