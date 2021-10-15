<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<link href="styles.css" rel="stylesheet" type="text/css">
<title>Police Emergency Service System</title>
</head>

<body>
	<?php require_once 'nav.php';?>
	<?php
	if(isset($_POST["btnDispatch"])){
		require_once 'db.php';
		
		$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		
		if($mysqli->connect_errno){
			die("Failed to connect to MySQL: ".$sqli->connect_errno);
		}
		
		$patrolcarDispatched = $_POST["chkPatrolcar"];
		$numOfPatrolcarDispatched = count($patrolcarDispatched);
		
		$incidentStatus;
		
		if($numOfPatrolcarDispatched > 0){
			$incidentStatus='2';
		}else {
			$incidentStatus='1';
		}
		
		$sql = "INSERT INTO incident (callerName, phoneNumber, incidentTypeId, incidentLocation, incidentDesc, incidentStatusId) VALUES (?, ?, ?, ?, ?, ?)";
		
		if(!($stmt = $mysqli->prepare($sql))){
			die("Prepare failed: ".$mysqli->errno);
		}
		
		if(!$stmt->bind_param('ssssss', $_POST['callerName'], $_POST['contactNo'], $_POST['incidentType'], $_POST['location'], $_POST['incidentDesc'], $incidentStatus)){
			die("Binding parameters failed: ".$stmt->errno);
		}
		
		if(!$stmt->execute()){
			die("Insert incident table failed: ".$stmt->errno);
		}
		
		$incidentId=mysqli_insert_id($mysqli);;
		
		for($i=0; $i < $numOfPatrolcarDispatched; $i++){
			$sql = "UPDATE patrolcar SET patrolcarStatusId = '1' WHERE patrolcarId = ?";
			
			if(!($stmt = $mysqli->prepare($sql))){
				die("Prepare failed: ".$mysqli->errno);
			}
			
			if(!$stmt->bind_param('s', $patrolcarDispatched[$i])){
				die("Binding parameters failed: ".$stmt->errno);
			}
			
			if(!$stmt->execute()){
				die("Update patrolcar_status table failed: ".$stmt->errno);
			}
			
			$sql = "INSERT INTO dispatch (incidentId, patrolcarId, timeDispatched) VALUES (?, ?, NOW())";
			
			if(!($stmt = $mysqli->prepare($sql))){
				die("Prepare failed: ".$mysqli->errno);
			}
			
			if(!$stmt->bind_param('ss', $incidentId, $patrolcarDispatched[$i])){
				die("Binding parameters failed: ".$stmt->errno);
			}
			
			if(!$stmt->execute()){
				die("Insert dispatch table failed: ".$stmt->errno);
			}
		}
		
		$stmt->close();
		
		$mysqli->close();
	}
	?>
	<fieldset>
	<legend>Dispatch</legend>
	
	<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
		
		<table width="40%" border="1" align="center" cellpadding="4" cellspacing="4">
			<tr>
				<td colspan="2" class="row-title row_title">Incident Detail</td>
			</tr>
			
			<tr>
				<td width="50%" class="row-title row_title">Caller's Name:</td>
				<td width="50%" class="row-title row_title">
					<?php echo $_POST['callerName']?>
					<input type="hidden" name="callerName" id="callerName" value="<?php echo $_POST['callerName'] ?>">
				</td>
			</tr>
			
			<tr>
				<td width="50%" class="row-title row_title">Contact No:</td>
				<td width="50%" class="row-title row_title">
					<?php echo $_POST['contactNo'] ?>
					<input type="hidden" name="contactNo" id="contactNo" value="<?php echo $_POST['contactNo']?>">
				</td>
			</tr>
			
			<tr>
				<td width="50%" class="row-title row_title">Location:</td>
				<td width="50%" class="row-title row_title">
					<?php echo $_POST['location'] ?>
					<input type="hidden" name="location" id="location" value="<?php echo $_POST['location']?>">
				</td>
			</tr>
			
			<tr>
				<td width="50%" class="row-title row_title">Incident Type:</td>
				<td width="50%" class="row-title row_title">
					<?php echo $_POST['incidentType'] ?>
					<input type="hidden" name="incidentType" id="incidentType" value="<?php echo $_POST['incidentType']?>">
				</td>
			</tr>
			
			<tr>
				<td width="50%" class="row-title row_title">Description:</td>
				<td width="50%" height="150px">
					<textarea name="incidentDesc" cols="45" row="5" readonly id="incidentDesc">
						<?php echo $_POST['incidentDesc'] ?>
					</textarea>
					<input name="incidentDesc" type="hidden" id="incidentDesc" value="<?php echo $_POST['incidentDesc']?>">
				</td>
			</tr>
		</table>
		
		<?php
			require_once 'db.php';
			
			$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
			
			if($mysqli -> connect_errno){
				die("Failed to connect to MySQL: ".$mysqli->connect_errno);
			}
			
			$sql = "SELECT patrolcarId, statusDesc FROM patrolcar JOIN patrolcar_status ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarStatusId='3'";
					
			if(!($stmt = $mysqli->prepare($sql))){
				die("Prepare failed: ".$mysqli->errno);
			}
					
			if(!$stmt->execute()){
				die("Execute failed: ".$stmt->errno);
			}
					
			if(!($resultset = $stmt->get_result())){
				die("Getting result set failed: ".$stmt->errno);
			}
					
			$patrolcarArray;
					
			while($row = $resultset->fetch_assoc()){
				$patrolcarArray[$row['patrolcarId']] = $row['statusDesc'];
			}
					
			$stmt->close();
					
			$resultset->close();
					
			$mysqli->close();
		?>
		
		<br><br>
		<table width="40%" border="1" align="center">
			
			<tr>
				<td colspan="3" style="text-align: center; color:white;">Dispatch Patrolcar Panel</td>
			</tr>
			
			<?php
				foreach($patrolcarArray as $key=>$value){
			?>
			
			<tr>
				<td style="color:white;"><input type="checkbox" name="chkPatrolcar[]" value="<?php echo $key?>"></td>
				<td style="color:white;"><?php echo $key ?></td>
				<td style="color:white;"><?php echo $value ?></td>
			</tr>
			
			<?php
				}
			?>
			
			<tr>
				<td colspan="3">
					<input type="reset" name="btnCancel" id="btnCancel" value="Reset" class="gem-button gem-white center">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch" class="gem-button gem-white center">
				</td>
			</tr>
		
		</table>
	
	</form>
		
	</fieldset>
</body>
</html>