 
<?php

$datetime = '2016-08-01T15:00:00Z';
$datetime = str_replace('T', ' ', $datetime);
$datetime = str_replace('Z', '', $datetime);
$appdatetime = explode(" ",$datetime);
echo $datetime;
echo '<br/>';

$appdate = $appdatetime[0];
echo $appdate;
echo '<br/>';
$apptime = $appdatetime[1];
echo $apptime;
echo '<br/>';
$endTime = date("H:i:s", strtotime('+15 minutes', $apptime));
echo $endTime;

/*

$servername = "50.18.176.8";
$username = "bijay";
$password = "openi.,#";
$db = "care_collaboration_openemr";
$con=mysqli_connect($servername,$username,$password,$db);
$quantity =3;

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

	    echo $sql;
	   */
