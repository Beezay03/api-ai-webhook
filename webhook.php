<?php
header('Content-Type: application/json');

require_once __DIR__.'/vendor/autoload.php';
use ApiAi\Client;

ob_start();
$json = file_get_contents('php://input'); 
$response = json_decode($json, true);
//$query = json_decode($json, true);

//$client = new Client('a045be2b28414e2fb1280ca178c7bffa');
//$query = $client->get('query', ['query' => '154895',]);
//$response = json_decode((string) $query->getBody(), true);
//file_put_contents("data.json", $response);

$servername = "50.18.176.8";
$username = "bijay";
$password = "openi.,#";
$db = "care_collaboration_openemr";
/*
$servername = "localhost";
$username = "root";
$password = "root";
$db = "openemr";
*/

// Create connection
$con=mysqli_connect($servername,$username,$password,$db);
// Check connection
if (mysqli_connect_errno()){
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


$resptext = "";
try {
    //$client = new Client('a045be2b28414e2fb1280ca178c7bffa');

    //$query = $client->get('query', ['query' => '154895',]);

    //decode the response body
    //$response = json_decode((string) $query->getBody(), true);
    //print_r($response);
    //echo "<br/><br/>";


	//for appointment process
	if($response['result']['action'] == 'appointment-action'){
	    $datetime = $response['result']['parameters']['date-time'];
	    $datetime = str_replace('T', ' ', $datetime);
		$datetime = str_replace('Z', '', $datetime);
		$appdatetime = explode(" ",$datetime);
		$appdate = $appdatetime[0];
		$apptime = $appdatetime[1];

		$fb_user = $response['result']['parameters']['fb_user'];
		

		// Check if appointment is available or not
		
	    //$sql = "SELECT count(*) as count FROM openemr_postcalendar_events WHERE pc_eventDate = '$appdate' AND '14:00:00' BETWEEN pc_startTime AND ADDTIME(pc_startTime, '00:15:00')";
	    //$result = mysqli_query($con,$sql);
		//$count = $result->fetch_array();
	

	    $sql = "SELECT * FROM user_appointment WHERE facebook_id = coalesce($fb_user,'''');";
	    $result = mysqli_query($con,$sql);

		if($result->num_rows > 0){
			//remove all appointments
			$sql = "delete FROM openemr_postcalendar_events WHERE pc_eventDate = '$appdate' and pc_startTime = '$apptime'";
	    	$result1 = mysqli_query($con,$sql);

			$insertapp = "INSERT INTO openemr_postcalendar_events 
								(pc_pid, pc_title, pc_hometext , pc_time, pc_eventDate, pc_startTime, pc_endTime, pc_apptstatus, pc_catid, pc_aid, pc_facility, 
								pc_billing_location, pc_duration , pc_informant, pc_eventstatus, pc_sharing, pc_recurrspec, pc_location) 
								SELECT
									(SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user') AS pc_pid,
									'Office Visit' AS pc_title, -- pc_title
									'$count1' AS pc_hometext , -- comments
									NOW() AS pc_time, -- date('Y-m-d H:i:s')
									'$appdate' AS pc_eventDate, -- appointmentDate
									'$apptime' AS pc_startTime, -- appointmentTime
									'' AS pc_endTime, -- endTime
									'-' AS pc_apptstatus, -- app_status
									5 AS pc_catid, -- pc_catid
									7 AS pc_aid, -- admin_id Provider id
									0 AS pc_facility, -- facility
									NULL AS pc_billing_location, -- pc_billing_location
									900 AS pc_duration , -- pc_duration
									1 AS pc_informant, 
									1 AS pc_eventstatus, 
									1 AS pc_sharing, 
									'a:6:{s:17:\"event_repeat_freq\";s:1:\"0\";s:22:\"event_repeat_freq_type\";s:1:\"0\";s:19:\"event_repeat_on_num\";s:1:\"1\";s:19:\"event_repeat_on_day\";s:1:\"0\";s:20:\"event_repeat_on_freq\";s:1:\"0\";s:6:\"exdate\";s:0:\"\";}' AS pc_recurrspec, -- recurrspec
									'a:6:{s:14:\"event_location\";s:0:\"\";s:13:\"event_street1\";s:0:\"\";s:13:\"event_street2\";s:0:\"\";s:10:\"event_city\";s:0:\"\";s:11:\"event_state\";s:0:\"\";s:12:\"event_postal\";s:0:\"\";}' AS pc_location -- locationspec";

			$addapp = mysqli_query($con,$insertapp);

			if($addapp){
				$resptext = "Thank you!!! The appointment has been scheduled for $appdate $apptime with Dr. Gregory House.";
			}
			else{
				$resptext = "Sorry, unable to add appointment. Type keyword 'Appointment' ";
			}
		}else{
			$resptext = "Sorry, invalid patient.";
		}
	}

	//for medication refill process step 1
	if($response['result']['action'] == 'medicationrefill-action'){
		$fb_user = $response['result']['parameters']['fb_user'];

	    $sql2 = "SELECT * FROM user_appointment WHERE facebook_id = coalesce($fb_user,'''');";
	    $result2 = mysqli_query($con,$sql2);

	    if($result2->num_rows > 0){
	    	$resptext = "Please select one of the following medications to refill (like: med {Med_name}): \\n med Abatacept\\n med Amoxicillin\\n med Zocor";
	    }else{
			$resptext = "Sorry, invalid patient.";
		}
	 }


	//for medication refill process step 2
	if($response['result']['action'] == 'medication-select-action'){
		$fb_user = $response['result']['parameters']['fb_user'];
		$medlist = $response['result']['parameters']['medlist'];

	    $sql2 = "SELECT * FROM user_appointment WHERE facebook_id = coalesce($fb_user,'''');";
	    $result2 = mysqli_query($con,$sql2);

		if($result2->num_rows > 0){
			if($medlist == 'Abatacept' or $medlist == 'Amoxicillin' or $medlist == 'Zocor'){
				$updatepres = "UPDATE 
							prescriptions
							SET
							    provider_id = '1', 
							    start_date = DATE(NOW()), 
							    drug = '$medlist', 
							    dosage = '1', 
							    quantity = '10',  
							    refills = refills+1, 
							    medication = '1',
							    date_modified = DATE(NOW()),
							    note = 'Drug Zocor Refilled'
							WHERE patient_id = (SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user');";

				$pres = mysqli_query($con,$updatepres);

				if($pres){

					$select_medication = "SELECT * FROM  `lists` 
	                                    WHERE  `type` =  'medication'
	                                    AND  `title` =  '$medlist' 
	                                    AND  `pid` = (SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user');";
	            	$result1 = mysqli_query($con,$select_medication);

	            	if($result1->num_rows == 0){
	            		$insertlist = "INSERT INTO lists(DATE,begdate,TYPE,activity,pid,USER,groupname,title) 
										VALUES (NOW(),CAST(NOW() AS DATE),'medication',1,(SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user'),'','','$medlist')";
		    			$result2 = mysqli_query($con,$insertlist);
	            	}

					$resptext = "Thank you!!! Your medication $medlist has been refilled.";			
				}
				else{
					$resptext = "Sorry, unable to refill medication. \\n\\nPlease select one of the following medications to refill (like: med {Med_name}): \\n med Abatacept\\n med Amoxicillin\\n med Zocor ";
				}
			}else{
				$resptext = "Invalid medication selection.\\n\\nPlease select one of the following medications to refill (like: med {Med_name}): \\n med Abatacept\\n med Amoxicillin\\n med Zocor";
			}
	
		}else{
			$resptext = "Sorry, invalid patient.";
		}
	}
	

} catch (\Exception $error) {
    echo $error->getMessage(); 
    mysqli_close($con);
}

ob_end_clean();
//echo json_encode($response);
//echo json_encode("Headers:Content-type: application/json Body:{\"speech\":\"".$resptext."\",\"displayText\":\"".$resptext."\",\"source\":\"".$response['result']['source']."\"}");
echo "{\"speech\":\"".$resptext."\"}";