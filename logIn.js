// JavaScript Document

$("#logIn").click(function(){sendData($("#name").val(), $("#id").val())})

function sendData(name, id){
	$.post("logIn.php",{name:name,id:id},function(data){
		$(".mainContent").html(data);
		//$("#logIn").click(function(){sendData($("#name").val(), $("#id").val())})
		$("#submitButton").click(function(){sendMoreData(id)})
		$("#getButton").click(function(){getData(id)})
		$("#adminSubmitButton").click(function(){getAdminData()})
										  })
}

function getAdminData(){
	var lift = $("#liftType").val();
	var id = $("#studentID").val();
	$.post("getAdminData.php",{id:id,lift:lift},function(adminData){
		$("#viewScreen").html(adminData);
														 })
}

function sendMoreData(id){
	var lift = $("#liftType").val();
	var weight = $("#Weight").val();
	var reps = $("#Reps").val();
	var sets = $("#Sets").val();
	$.post("sendData.php",{id:id,lift:lift,weight:weight,reps:reps,sets:sets},function(moreData){
		alert(moreData)
																							   })
}

function getData(id){
	$.post("getData.php",{id:id},function(evenMoreData){
		$("#viewScreen").html(evenMoreData);
								  		})
}