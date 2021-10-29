<!doctype html>
<html>
<head>
<meta charset="utf-8">
<link href="styles.css" rel="stylesheet" type="text/css">
<title>Police Emergency Service System</title>
<?php
if(isset($_POST["btnUpdate"])){
	require_once 'db.php';
	
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if($mysqli->connect_errno){
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
	
	$sql = "UPDATE patrolcar SET patrolcarStatusId = ? WHERE patrolcarId = ? ";
	
	if(!($stmt = $mysqli->prepare($sql))){
		die("Prepare failed: ".$mysqli->errno);
	}
	
	if(!$stmt->bind_param('ss', $_POST['patrolCarStatus'], $_POST['patrolCarId'])){
		die("Binding parameters failed: ".$stmt->errno);
	}
	
	if(!$stmt->execute()){
		die("Update patrolcar table failed: ".$stmt->errno);
	}
	
	if($_POST["patrolCarStatus"] == '4'){
		$sql = "UPDATE dispatch SET timeArrived = NOW() WHERE timeArrived is NULL and patrolcarId = ?";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('s', $_POST['patrolCarId'])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute()){
			die("Update dispatch table failed: ".$stmt->errno);
		}
		
		
	} else if($_POST["patrolCarStatus"] == '3'){
		$sql = "SELECT incidentId FROM dispatch WHERE timeCompleted is NULL AND patrolcarId = ?";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('s', $_POST['patrolCarId'])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute()){
			die("Execute failed: ".$stmt->errno);
		}
		
		if(!($resultset = $stmt->get_result())){
			die("Getting result set failed: ".$stmt->errno);
		}
		
		$incidentId;
		
		while($row = $resultset->fetch_assoc()){
			$incidentId = $row['incidentId'];
		}
		
		$sql = "UPDATE dispatch SET timeCompleted = NOW() WHERE timeCompleted is NULL AND patrolcarId = ?";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('s', $_POST['patrolCarId'])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute()){
			die("Update dispatch table failed: ".$stmt->errno);
		}
		
		$sql = "UPDATE incident SET incidentStatusId = '3' WHERE incidentId = '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch WHERE timeCompleted IS NULL AND incidentId = '$incidentId')";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed 11: ".$mysqli->errno);
		}
		
		if(!$stmt->execute()){
			die("Update dispatch table failed: ".$stmt->errno);
		}
		
		$resultset->close();
	}
	
	$stmt->close();
	
	$mysqli->close();

?>
	<script>window.location="logcall.php";</script>
<?php } ?>
</head>

<body>
	<?php require_once 'nav.php';?>
	<br><br>
	<?php
	if(!isset($_POST["btnSearch"])){
	?>
	<fieldset>
	<legend>Update</legend>
	<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<table width="50%" border="1" align="center" cellpadding="4" cellspacing="4">
			<tr></tr>
			<tr>
				<td class="row-title row_title">Patrol Car ID:</td>
				<td>
					<input type="text" name="patrolCarId" id="patrolCarId" class="text-input">
				</td>
				
				<td>
					<input type="submit" name="btnSearch" id="btnSearch" value="Search" class="gem-button gem-white center">
				</td>
			</tr>
		</table>
	</form>
	</fieldset>
	
	<?php
	}else{
		require_once 'db.php';
		
		$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		
		if($mysqli->connect_errno){
			die("Failed to connect to MySQL: ".$mysqli->connect_errno);
		}
		
		$sql = "SELECT * FROM patrolcar WHERE patrolcarId = ?";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('s', $_POST['patrolCarId'])){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute()){
			die("Execute failed: ".$stmt->errno);
		}
		
		if(!($resultset = $stmt->get_result())){
			die("Getting result set failed: ".$stmt->errno);
		}
		
		if($resultset->num_rows == 0){
			?>
				<script>window.location="update.php";</script>
			<?php }
		$patrolCarId;
		$patrolCarStatusId;
		
		while($row = $resultset->fetch_assoc()){
			$patrolCarId = $row['patrolcarId'];
			$patrolCarStatusId = $row['patrolcarStatusId'];
		}
		
		$sql = "SELECT * FROM patrolcar_status";
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->execute()){
			die("Execute failed: ".$stmt->errno);
		}
		
		if(!($resultset = $stmt->get_result())){
			die("Getting result set failed: ".$stmt->errno);
		}
		
		$patrolCarStatusArray;;
		
		while($row = $resultset->fetch_assoc()){
			$patrolCarStatusArray[$row['statusId']] = $row['statusDesc'];
		}
		
		$stmt->close();
		
		$resultset->close();
		
		$mysqli->close();
	?>
	
	<fieldset>
	<legend>Update</legend>
	<form name="form2" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		<table width="50%" border="1" align="center" cellpadding="4" cellspacing="4">
			<tr></tr>
			<tr>
				<td class="row-title row_title">ID:</td>
				<td class="row-title row_title">
					<?php echo $patrolCarId?>
					<input type="hidden" name="patrolCarId" id="patrolCarId" value="<?php echo $patrolCarId?>">
				</td>
			</tr>
			
			<tr>
				<td class="row-title row_title">Status:</td>
				<td class="row-title row_title">
					<select name="patrolCarStatus" id="patrolCarStatus">
						<?php foreach($patrolCarStatusArray as $key => $value) { ?>
						<option value="<?php echo $key ?>"
								<?php if($key==$patrolCarStatusId) {?> selected="selected"
								<?php } ?>
								>
							<?php echo $value ?>
						</option>
						<?php } ?>
					</select>
				</td>
			</tr>
			
			<tr>
				<td colspan="2" align="center">
					<input type="reset" name="btnCancel" id="btnCancel" value="Reset" class="gem-button gem-white center">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="gem-button gem-white center">
				</td>
			</tr>
		</table>
	</form>
	</fieldset>
	<?php } ?>
</body>
</html>