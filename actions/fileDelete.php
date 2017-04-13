<?php

// SET TIMEZONE TO READ FROM NEW YORK
date_default_timezone_set('America/New_York');

header('Access-Control-Allow-Methods: POST, PUT');
header("Access-Control-Allow-Headers: X-Requested-With");

// THESE ARE NOT TRACKED IN GIT, SO YOU MUST HAVE BEEN PROVIDED THESE BY AN ADMIN OR CREATE YOUR OWN
// SERVICE TOKEN REQUIRES ADMIN PERMISSION

require_once "../auth/slack_keys.php";
global $token;

// DEFINE THE SLACK RESOURCE URIS AND FILE LIST LIMIT
$fileListResource = "https://slack.com/api/files.list";
$fileDeleteResource = "https://slack.com/api/files.delete";
$filesPerPage = "100";

// ASK FOR THE DATE LIMIT (VALUE ENTERED WILL LIST ALL FILES UP TO THAT DATE)
$humanDateLimit = readline("Enter Date Limit (YYYY-MM-DD): ");
$dateLimit = strtotime($humanDateLimit);


// DETERMINE HOW MANY PAGES EXIST BASED ON RECORD LIMIT AND DATE LIMIT

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "$fileListResource?count=$filesPerPage&ts_to=$dateLimit&token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

$response = curl_exec($ch);
curl_close($ch);

// GET THE JSON INTO AN ARRAY SO WE CAN ACCESS VARIABLES
$pagingInfo = json_decode($response, true);
// print_r($pagingInfo);


// FIND OUT HOW MANY PAGES EXIST BASED ON THE IMPOSED LIMITS (TIME / NUMBER OF FILES)
$numberOfPages = $pagingInfo['paging']['pages'];
// print_r("$numberOfPages\r\n");


// START WITH THE FIRST PAGE AND CONTINUE UNTIL WE REACH THE LAST ONE
for ($pageCount = 1; $pageCount <= $numberOfPages; $pageCount++) {
  $currentPage = $pageCount;
  // print_r("$currentPage\r\n");


  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "$fileListResource?page=$currentPage&count=$filesPerPage&ts_to=$dateLimit&token=$token");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, FALSE);

  $response = curl_exec($ch);
  curl_close($ch);

// GET THE JSON INTO AN ARRAY SO WE CAN ACCESS VARIABLES
$userFileList = json_decode($response, true);

// LOOP THROUGH THE FILES ARRAY AND EXTRACT EACH ONE
  foreach($userFileList as $userFile) {
    $userFile = $userFileList['files'];
  }

  // LOOP THROUGH EACH FILE AND EXTRACT FILE ID
  foreach($userFile as $fileDetails) {
    $fileId = $fileDetails['id'];

    // DELETE EACH FILE

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$fileDeleteResource?file=$fileId&token=$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    print_r($response);
  }
}
?>
