<?php

$street = $_GET['street'];
$postName = $_GET['postName'];

$payload = array();

//
// STEP 1
// 

$query = urlencode($postName . ' ' . $street);
$stepOneUrl = sprintf('http://gea.xlab.si/ses/search?s=location&addresssearch=true&proj=gkr&format=json&count=5&start=0&query=%s', $query);

$stepOneData = get($stepOneUrl);
$stepOneData = json_decode($stepOneData);

if (is_null($stepOneData)) {
	echo json_encode(array("error" => "Could not parse response"));
	die;
}

if (count($stepOneData) > 1) {
	echo json_encode(array("error" => "More than one result", 'results' => $stepOneData));
	die;
}

$payload['address'] = $stepOneData[0]->address;
$payload['id']      = $stepOneData[0]->ul_mid;

//
// STEP 2
//

$stepTwoUrl = sprintf('http://gea.xlab.si/dws/call/GetVolisceDrzavnozborsko?ul_mid=%s&hs=3&bbox=28250,-12750,961750,226750&jsoncallback=', $payload['id']);

$stepTwoData = get($stepTwoUrl);
$stepTwoData = substr($stepTwoData, 0, -2);
$stepTwoData = substr($stepTwoData, 1);
$stepTwoData = json_decode($stepTwoData);

if (is_null($stepTwoData)) {
	echo json_encode(array("error" => "Could not parse step two response"));
	die;
}

$enota = $stepTwoData->features[0]->properties->volilna_eonta_uime;
$numEnota = substr($enota, -1);

$payload['enota_label'] = $enota;
$payload['enota_num'] = $numEnota;

echo json_encode($payload); die;


function get($url) {
	// create curl resource 
    $ch = curl_init(); 

    // set url 
    curl_setopt($ch, CURLOPT_URL, $url); 

    //return the transfer as a string 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

    // $output contains the output string 
    $output = curl_exec($ch); 

    // close curl resource to free up system resources 
    curl_close($ch);    

    return $output;
}