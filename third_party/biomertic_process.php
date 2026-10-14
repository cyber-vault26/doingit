<?php
date_default_timezone_set("Africa/Dar_es_Salaam");

    require_once __DIR__ . "/../functions/attendance_functions.php";
    require_once __DIR__ . "/../configs.php";
      $conn = openConn();
	$rawdata = file_get_contents("php://input");
	$stgid= $_GET["stgid"];
	error_log(print_r($rawdata, true));
	error_log(print_r($rawdata, true));
// 		file_put_contents("biometric_data.txt", $rawdata, FILE_APPEND, LOCK_EX);
	// $file = "test_data.txt";
	// 
	// file_put_contents($file, $rawdata, FILE_APPEND, LOCK_EX);
	// We capture all the incoming data from CAMS into file: cams-attendance-record.txt.
	// If you need to maintain it in your own database, you need to impletement the same here.
	$raw = json_decode($rawdata);

	$ret = "done";
	$data = json_decode($rawdata, true);

	if( isset( $data["RealTime"] ))
	{
		$ret = handle_attendance_log($stgid, $rawdata, $conn);
	}
	else
		$ret = "Else";

	$response = new StdClass();
	$response->status="done";

	header("Content-Type: application/text;charset=utf-8");
	http_response_code(200);
	echo json_encode($response);


// You can test url of this file through postman with POST->body->x-www-form-urlencoded parameter rawdata
// the results will be avilable in http://<domain:port>/cams-attendance-record.txt
// ------------------------------------------------------------------------------------------------------------


function handle_attendance_log($stgid, $rawdata, mysqli  $conn)
{

	$ret = "done";
		// Sample rawdata
		// {
		//      "RealTime": {
		//           "OperationID": "123123123",
		//           "PunchLog": {
		//                "Type": "CheckOut",
		//                "Temperature": "36.8",
		//                "FaceMask": false,
		//                "InputType": "Fingerprint",
		//                "UserId": "2",
		//                "LogTime": "2020-09-17 07:48:22 GMT +0530"
		//           },
		//           "AuthToken": "COJJ7eiiPBGUfmIQPvh2PJWWDLX7OuKs",
		//           "Time": "2020-09-17 04:19:03 GMT +0000"
		//      }
		// }

	$request = new StdClass();
	$request->RealTime = new StdClass();
	$request->RealTime->AuthToken="";
	$request->RealTime->Time="";
	$request->RealTime->OperationID="";
	$request->RealTime->PunchLog = new StdClass();
	$request->RealTime->PunchLog->UserId="";
	$request->RealTime->PunchLog->LogTime="";
	$request->RealTime->PunchLog->Temperature="";
	$request->RealTime->PunchLog->FaceMask="";
	$request->RealTime->PunchLog->InputType="";
	$request->RealTime->PunchLog->Type= "";

	$request = json_decode($rawdata);

	$content = 'ServiceTagId:' . $stgid . ",\t";
	$content = $content . 'UserId:' . $request->RealTime->PunchLog->UserId . ",\t";
	$content = $content . 'AttendanceTime:' . $request->RealTime->PunchLog->LogTime . ",\t";
	$content = $content . 'AttendanceType:' . $request->RealTime->PunchLog->Type . ",\t";
	$content = $content . 'InputType:' . $request->RealTime->PunchLog->InputType . ",\t";
	$content = $content . 'Operation: RealTime->PunchLog' . ",\t";
	$content = $content . 'AuthToken:' . $request->RealTime->AuthToken . "\n";

	$file = fopen("cams-attendance-record.txt","a");
	fwrite($file, $content);

    $time = $request->RealTime->PunchLog->LogTime;
    	error_log(print_r( "time:" . $time, true));
    $userId = $request->RealTime->PunchLog->UserId;  
    $dt = new DateTime($time);
        $checkTime = $dt->format("H:i:s");

    	error_log(print_r( " check time:" . $checkTime, true));
    $checkStatus = '';
    if($checkTime >= "06:00:00" && $checkTime <= "15:59:59") {
    $checkStatus = "check_in";

    } elseif($checkTime >= "16:00:00" && $checkTime <= "23:59:59") {
        $checkStatus = "check_out";
    }
    recordAttendance($conn, $userId,  $checkTime, $checkStatus);
	// Uncomment this block to write the values in your DB. Make sure that following table is created and $servername, $username , $password and $dbname are filled with its actual values
	//DB Script for table name CamsBiometricAttendance
	// Create Table CamsBiometricAttendance ( ID int NOT NULL AUTO_INCREMENT,    ServiceTagId varchar(20), UserID varchar(9), AttendanceTime datetime, AttendanceType varchar(16), PRIMARY KEY (ID));
	// After success execution of the script run the following query at your sql server
	//select ServiceTagId, UserId, AttendanceTime, AttendanceType  from CamsBiometricAttendance order by id desc

/*
	$ServiceTagId = $stgid;
	$UserId = $request->RealTime->PunchLog->UserId;
	$AttendanceTime = $request->RealTime->PunchLog->LogTime;
	$AttendanceType = $request->RealTime->PunchLog->Type;

	$servername = "localhost";
	$username = "root";
	$password = "password";
	$dbname = "test";


	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
		fwrite($file,"Connection failed: " . $conn->connect_error);
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "INSERT INTO CamsBiometricAttendance (ServiceTagId, UserId, AttendanceTime, AttendanceType)
				VALUES ('". $ServiceTagId ."', '". $UserId ."', '" . $AttendanceTime . "', '" . $AttendanceType . "')";

	fwrite($file,$sql);
	if ($conn->query($sql) === TRUE) {
		fwrite($file, " -- inserted in db");
	} else {
		fwrite($file, " -- DB Error: " . $sql . "<br>" . $conn->error);
	}

	$conn->close();
	*/

	fclose($file);

	return $ret;

}
?>
