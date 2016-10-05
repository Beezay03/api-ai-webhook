<?php

    $rsp = "Array ( [id] => e4927b2a-83c2-47a9-9308-d05e796b9a44 [timestamp] => 2016-09-22T10:39:43.643Z [result] => Array ( [source] => agent [resolvedQuery] => tomorrow 2pm [action] => [actionIncomplete] => [parameters] => Array ( [appointment] => [date] => 2016-09-23 [time] => 14:00:00 ) [contexts] => Array ( ) [metadata] => Array ( [intentId] => cf59be4e-36e5-4435-ad3a-cf1252c05b5a [webhookUsed] => false [intentName] => date-select ) [fulfillment] => Array ( [speech] => Thank you!!! The appointment has been scheduled for 2016-09-23 14:00:00. ) [score] => 1 ) [status] => Array ( [code] => 200 [errorType] => success ) [sessionId] => 00000000-0000-0000-0000-000000000000 [speech] => Response edited )";

    
    $rsp['result']['fulfillment']['speech'] = "Response edited";
    print_r($obj);

    //print_r( $rsp);
