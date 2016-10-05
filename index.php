<?php
require_once __DIR__.'/vendor/autoload.php';

use ApiAi\Client;
/*
$servername = "localhost";
$username = "root";
$password = "root";
$db = "openemr";
*/

$servername = "50.18.176.8";
$username = "bijay";
$password = "openi.,#";
$db = "care_collaboration_openemr";

// Create connection
$con=mysqli_connect($servername,$username,$password,$db);
// Check connection
if (mysqli_connect_errno()){
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$resptext = "";
try {
    $client = new Client('a045be2b28414e2fb1280ca178c7bffa');

    $query = $client->get('query', ['query' => 'med cetamol',]);

    //decode the response body
    $response = json_decode((string) $query->getBody(), true);
    print_r($response);
    echo "<br/><br/>";


	if($response['result']['action'] == 'patientid-action'){
		$SSN = $response['result']['parameters']['SSN'];
	    $datetime = new DateTime('tomorrow 2pm');
		$appdate = $datetime->format('Y-m-d');

		// Check if appointment is available or not
	    $sql = "SELECT count(*) as count FROM openemr_postcalendar_events WHERE pc_eventDate = '$appdate' AND '14:00:00' BETWEEN pc_startTime AND ADDTIME(pc_startTime, '00:15:00')";

	    $result = mysqli_query($con,$sql);
		$count = $result->fetch_array();
		echo "<br/>";
		//print_r($count) ;
		echo "<br/>";

		if($count[0] == 0){

			$insertapp = "INSERT INTO openemr_postcalendar_events 
							(pc_pid, pc_title, pc_hometext , pc_time, pc_eventDate, pc_startTime, pc_endTime, pc_apptstatus, pc_catid, pc_aid, pc_facility, 
							pc_billing_location, pc_duration , pc_informant, pc_eventstatus, pc_sharing, pc_recurrspec, pc_location) 
							SELECT
								(SELECT pid FROM patient_data WHERE ss = '$SSN') AS pc_pid, -- patientId
								'Office Visit' AS pc_title, -- pc_title
								'' AS pc_hometext , -- comments
								NOW() AS pc_time, -- date('Y-m-d H:i:s')
								'$appdate' AS pc_eventDate, -- appointmentDate
								'14:00:00' AS pc_startTime, -- appointmentTime
								'14:15:00' AS pc_endTime, -- endTime
								'-' AS pc_apptstatus, -- app_status
								5 AS pc_catid, -- pc_catid
								1 AS pc_aid, -- admin_id
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
				$resptext = "Thank you!!! The appointment for patient SSN $SSN has been scheduled for $appdate 2pm.";
				//$response['result']['fulfillment']['speech'] = "Thank you!!! The appointment for patient SSN $SSN has been scheduled for $appdate 2pm.";
			}
			else{
				$resptext = "Sorry, unable to add appointment. Please reenter your SSN. ";
				//$response['result']['fulfillment']['speech'] = "Sorry, unable to add appointment. Please reenter your SSN. ";
			}
		}
		else{
			$resptext = "Sorry, no appointment available.";
			//$response['result']['fulfillment']['speech'] = "Sorry, no appointment available.";
		}
	}

	//for medication refill process
	if($response['result']['action'] == 'medicationrefill-action'){
		$quantity = $response['result']['parameters']['quantity'];
		$fb_user = $response['result']['parameters']['fb_user'];
		echo "<br/>quantity: ".$quantity;

	    $sql2 = "SELECT * FROM user_appointment WHERE facebook_id = coalesce($fb_user,'''');";
	    $result2 = mysqli_query($con,$sql2);

		if($result2->num_rows > 0){

			$updatepres = "UPDATE 
							prescriptions
							SET
							    provider_id = '1', 
							    start_date = DATE(NOW()), 
							    drug = 'Zocor', 
							    dosage = '1', 
							    quantity = $quantity,  
							    refills = refills+1, 
							    medication = '1',
							    date_modified = DATE(NOW()),
							    note = 'Drug Zocor Refilled Qty $quantity'
							WHERE patient_id = (SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user');";

			$pres = mysqli_query($con,$updatepres);



			if($pres){
				$insertlist = "INSERT INTO lists(DATE,begdate,TYPE,activity,pid,USER,groupname,title) 
						VALUES (NOW(),CAST(NOW() AS DATE),'medication',1,(SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user'),'','','Zocor')";
	    		$result2 = mysqli_query($con,$insertlist);

				$resptext = "Thank you!!! Your medication for Zocor has been refilled with quantity $quantity.";
				//$response['result']['fulfillment']['speech'] = "Thank you!!! The appointment for patient SSN $SSN has been scheduled for $appdate 2pm.";				
			}
			else{
				$resptext = "Sorry, unable to refill medication. Type keyword 'refill medication'. ";
				//$response['result']['fulfillment']['speech'] = "Sorry, unable to add appointment. Please reenter your SSN. ";
			}
		}else{
			$resptext = "Sorry, unable to find the patient.";
			//$response['result']['fulfillment']['speech'] = "Sorry, unable to add appointment. Please reenter your SSN. ";
		}
	}
  

  //for medication refill process step 2
	if($response['result']['action'] == 'medication-select-action'){
		//$fb_user = $response['result']['parameters']['fb_user'];
		$medlist = $response['result']['parameters']['medlist'];
		$fb_user = '1381772761868204';

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
					$resptext = "Sorry, unable to refill medication. Type keyword 'refill medication'. ";
				}
			}else{
				$resptext = "Invalid medication selection.\\n\\nPlease select one of the following medications to refill: \\nmed Abatacept\\nmed Amoxicillin\\nmed Zocor";
			}
	
		}else{
			$resptext = "Sorry, invalid patient.";
		}
	}
    //$response = json_decode((string) $query->getBody(), true);
   	//print_r( $response);

} catch (\Exception $error) {
    echo $error->getMessage();
    mysqli_close($con);
}


echo "{\"speech\":\"".$resptext."}";
//print_r($response);