<?php

//CHECK if user has a password to create a user
if(file_exists('./env.php')) {
    include './env.php';
}
else {
	die("there was no env file, please create ./env.php");
}

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL); 
$loginUser = "admin";
$password = getenv('LOGIN_PASSWORD');
$newUser = "testUser2";
$newUserPassword = "GenerateThisPasswordLater";
define('HOST',"http://metrics.ekitabu.com:5984/_users/org.couchdb.user:$newUser");

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => HOST ,
  CURLOPT_USERPWD => "$loginUser:$password",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PUT",
  CURLOPT_POSTFIELDS => '{"name" :  "'.$newUser.'" ,"password" : "'.$newUserPassword.'",  "roles" : [], "type" : "user"}',
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/json",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

