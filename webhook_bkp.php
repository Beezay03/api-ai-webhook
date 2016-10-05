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

	if($response['result']['action'] == 'appointment-action'){
		$SSN = $response['result']['parameters']['SSN'];
	    $datetime = new DateTime('tomorrow 2pm');
		$appdate = $datetime->format('Y-m-d');
		$fb_user = $response['result']['parameters']['fb_user'];
		

		// Check if appointment is available or not
	    $sql = "SELECT count(*) as count FROM openemr_postcalendar_events WHERE pc_eventDate = '$appdate' AND '14:00:00' BETWEEN pc_startTime AND ADDTIME(pc_startTime, '00:15:00')";
	    $result = mysqli_query($con,$sql);
		$count = $result->fetch_array();

		// Check if use is valid fb user
	    $sql1 = "SELECT count(*) as count FROM user_appointment WHERE facebook_id = coalesce('$fb_user','''');";
	    $result1 = mysqli_query($con,$sql1);
		$count1 = $result1->fetch_array();

		//if($count1 == 1){
				if($count[0] == 0){
				$insertapp = "INSERT INTO openemr_postcalendar_events 
								(pc_pid, pc_title, pc_hometext , pc_time, pc_eventDate, pc_startTime, pc_endTime, pc_apptstatus, pc_catid, pc_aid, pc_facility, 
								pc_billing_location, pc_duration , pc_informant, pc_eventstatus, pc_sharing, pc_recurrspec, pc_location) 
								SELECT
									(SELECT user_id FROM user_appointment WHERE facebook_id = '$fb_user') AS pc_pid,
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
					$resptext = "Sorry, unable to add appointment. Type keyword 'Appointment' ";
					//$response['result']['fulfillment']['speech'] = "Sorry, unable to add appointment. Please reenter your SSN. ";
				}
			}
			else{
				$resptext = "Sorry, no appointment available.";
				//$response['result']['fulfillment']['speech'] = "Sorry, no appointment available.";
			}

		}
		//else{
		//	$resptext = "Invalid user.";
		//}
	//}
  
    //$response = json_decode((string) $query->getBody(), true);
   	//print_r( $response);
   //	curl_exec($response);

	//echo json_encode($response);

	//curl_exec($response);

   	//file_put_contents("data.json", $response);

} catch (\Exception $error) {
    echo $error->getMessage();
    mysqli_close($con);
}

ob_end_clean();
//echo json_encode($response);
echo json_encode("Headers:Content-type: application/json Body:{\"speech\":\"".$resptext."\",\"displayText\":\"".$resptext."\",\"source\":\"".$response['result']['source']."\"}");




