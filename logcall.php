<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<link href="styles.css" rel="stylesheet" type="text/css">
<title>Police Emergency Service System</title>
</head>

<body>
	<script>
	function submitCheck() {
		var x = document.forms["frmLogCall"]["callerName"].value;
		if (x==null || x==""){
			alert("Caller name is required.");
			return false;
		}
	}
	</script>
	
	<?php require 'nav.php';?>
	<?php require 'db.php';
	
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if($mysqli->connect_errno){
		die("Unable to connect to Database: ".$mysqli->connect_errno);
	}
	
	$sql = "SELECT * FROM incidenttype";
	
	if(!($stmt = $mysqli->prepare($sql))){
		die("Command error: ".$mysqli->errno);
	}
	
	if(!$stmt->execute()){
		die("Cannot run SQL command: ".$stmt->errno);
	}
	
	if(!($resultset = $stmt->get_result())){
		die("No data in resultset: ".$stmt->errno);
	}
	
	$incidentType;
	
	while($row = $resultset->fetch_assoc()) {
		$incidentType[$row['incidentTypeId']] = $row['incidentTypeDesc'];
	}
	
	$stmt->close();
	
	$resultset->close();
	
	$mysqli->close();
	?>
	
	<fieldset>
	
		<legend>Log Call</legend>
		<form name="frmLogCall" method="post" action="dispatch.php" onSubmit="return submitCheck();">
			<table width="40%" border="1" align="center" cellpadding="4" cellspacing="4">
			
				<tr>
					<td width="50%" class="row-title row_title">Caller's Name:</td>
					<td width="50%"><input type="text" name="callerName" id="callerName" class="text-input" placeholder="Caller's Name"></td>
				</tr>
				
				<tr>
					<td width="50%" class="row-title row_title">Contact No:</td>
					<td width="50%"><input type="text" name="contactNo" id="contactNo" class="text-input" placeholder="Contact No."></td>
				</tr>
				
				<tr>
					<td width="50%" class="row-title row_title">Location:</td>
					<td width="50%"><input type="text" name="location" id="location" class="text-input" placeholder="Location"></td>
				</tr>
				
				<tr>
					<td width="50%" class="row-title row_title">Incident Type:</td>
					<td width="50%">
						<select name="incidentType" id="incidentType">
							<?php foreach($incidentType as $key=> $value) {?>
							<option value="<?php echo $key ?> " >
							<?php echo $value ?> </option>
							<?php } ?>
						</select>
					</td>
				</tr>
				
				<tr>
					<td width="50%" class="row-title row_title">Description:</td>
					<td width="50%"><textarea name="incidentDesc" id="incidentDesc" cols="45" rows="5" class="desc-input" placeholder="Description here..."></textarea></td>
				</tr>
				
				<tr>
					<td colspan="2">
						<input type="reset" name="cancelProcess" id="cancelProcess" value="Reset" class="gem-button gem-white center">
						<input type="submit" name="btnProcessCall" id="btnProcessCall" value="Process Call" class="gem-button gem-white center">
					</td>
				</tr>
			
			</table>
		
		
		</form>
	
	</fieldset>
</body>
</html>