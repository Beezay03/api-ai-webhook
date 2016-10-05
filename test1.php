<?php
//$challenge = $REQUEST['hubchallenge'];
//$verify_token = $REQUEST['hubverify_token'];
$access_token = 'EAAE2qmeKjIIBADuyLTfc2fqHh8vKKrufHEM7ZABaQ60UxR1tqxMR1R8mQd5CkAbaZA9xTdcnZBiDnTc3hi8ZBZApO73o3DxXyAOF1v1JjviJZBNpMdZCZAIvkt5gJVt6u41q8DLVWdluy9i5yRffuyfPIP9IVWlAOTHWuoAeG5amZAgZDZD';

/*
if ($verify_token === 'EAAE2qmeKjIIBADuyLTfc2fqHh8vKKrufHEM7ZABaQ60UxR1tqxMR1R8mQd5CkAbaZA9xTdcnZBiDnTc3hi8ZBZApO73o3DxXyAOF1v1JjviJZBNpMdZCZAIvkt5gJVt6u41q8DLVWdluy9i5yRffuyfPIP9IVWlAOTHWuoAeG5amZAgZDZD') {
	echo $challenge;
}
*/

$input = json_decode(file_get_contents('php://input'), true);
$sender = $input['entry'][0]['messaging'][0]['sender']['id'];
$inmessage = $input['entry'][0]['messaging'][0]['message']['text'];
echo "<br/>input: ".$input;
echo "<br/>send: ".$sender;
echo "<br/>inmessage: ".$inmessage;


// do whatever to set the output message back to facebook
//Within the Facebook Webhook above (where it says "// do whatever.."), you can call api.ai to do the query and get the response:
/*
$data = array("v" => "20150821", "query" => " . $inmessage . ", "lang" => "EN", "sessionId" => "1234567890"); 
$data_string = json_encode($data);

$ch2 = curl_init('https://api.api.ai/v1/query');
curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch2, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
	'Content-Type: application/json',
	// 'Content-Length: ' . strlen($data_string),
	'Content-type: application/json',
	'Authorization: Bearer [CLIENT ACCESS TOKEN]')
	);

$result = curl_exec($ch2);
*/

$outmessage = "Test";

$url = 'https://graph.facebook.com/v2.6/me/messages?access_token='.$access_token;
$ch = curl_init($url);
$jsonData = '{
"recipient":{
"id":"' . $sender . '"
}, 
"message":{
"text":"' . $outmessage . '"
}
}';
echo $jsonData;
file_put_contents("data.json", $jsonData);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
if(!empty($input['entry'][0]['messaging'][0]['message'])){
	$result = curl_exec($ch);
}

?>