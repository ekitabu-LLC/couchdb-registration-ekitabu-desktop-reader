<?php

//CHECK if user has a password to create a user
if(file_exists('./env.php')) {
    include './env.php';
}
else {
	die("there was no env file, please create ./env.php");
}

$output = [
    "success" => true,
    "extraInfo" => "",
];

$loginUser = getenv('LOGIN_USERNAME');
$password = getenv('LOGIN_PASSWORD');
$newUser = $_GET['dvuuid'];
$newUserPassword = random_str(30);

$databaseName =  "analyticsdb_" . $newUser;

$usersUrl = "_users/org.couchdb.user:";

define('CREATE_USER_URL' ,getenv('HOST') . $usersUrl . $newUser);
define('DATABASE_URL' ,getenv('HOST') . $databaseName);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => CREATE_USER_URL ,
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
  //echo "cURL Error #:" . $err;
  $output["success"] = false;
  $output["message"] = "Failed to create a new user. ERROR: " . $err;
} else {
  //echo $response;
  $output["extraInfo"] .= $response;
}

if ($output["success"]) 
{
    $output["password"] = $newUserPassword;
}

$createDatabaseCurl = curl_init();

curl_setopt_array($createDatabaseCurl, array(
  CURLOPT_URL => DATABASE_URL ,
  CURLOPT_USERPWD => "$loginUser:$password",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PUT"
  )
);

$response = curl_exec($createDatabaseCurl);
$err = curl_error($createDatabaseCurl);

curl_close($createDatabaseCurl);

if ($err) {
  //echo "cURL Error #:" . $err;
  $output["success"] = false;
  $output["message"] .= "Failed to create a database for the user. ERROR: " . $err .".  " ;
} else {
  //echo $response;
  $output["extraInfo"] .= $response;
}


//UPDATE DATABASE WITH PERMISSIONS TO ALLOW USER TO EDIT

$updatePermissionCurl = curl_init();

curl_setopt_array($updatePermissionCurl, array(
  CURLOPT_URL => DATABASE_URL . "/_security" ,
  CURLOPT_USERPWD => "$loginUser:$password",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PUT",
  CURLOPT_POSTFIELDS => '{"admins": { "names": ["'.$newUser.'"], "roles": [] }, "members": { "names": ["'.$newUser.'"], "roles": [] } }',
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache",
    "content-type: application/json",
  ),
));

$response = curl_exec($updatePermissionCurl);
$err = curl_error($updatePermissionCurl);

curl_close($updatePermissionCurl);

if ($err) {
  //echo "cURL Error #:" . $err;
  $output["success"] = false;
  $output["message"] .= "Failed to set permissions on the database for the user. ERROR: " . $err .".  " ;
} else {
  //echo $response;
  $output["extraInfo"] .= $response;
}

echo(json_encode($output));

function random_str(
    $length,
    $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
) 
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    if ($max < 1) {
        throw new Exception('$keyspace must be at least two characters long');
    }
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}
