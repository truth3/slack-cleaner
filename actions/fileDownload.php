<?php
// RAISE MEMORY LIMIT TO 1GB TO HANDLE VIDEO FILES
ini_set('memory_limit', '1024M');

// SET TIMEZONE TO READ FROM NEW YORK
date_default_timezone_set('America/New_York');

header('Access-Control-Allow-Methods: POST, PUT');
header("Access-Control-Allow-Headers: X-Requested-With");

// THESE ARE NOT TRACKED IN GIT, SO YOU MUST HAVE BEEN PROVIDED THESE BY AN ADMIN OR CREATE YOUR OWN
// SERVICE REQUIRES READ PERMISSION ON CHANNELS AND FILES RESOURCE

require_once "../auth/slack_keys.php";
global $token;

// DEFINE THE SLACK RESOURCE URIS AND FILE LIST LIMIT
$channelListResource = "https://slack.com/api/channels.list";
$fileListResource = "https://slack.com/api/files.list";
$filesPerPage = "100";

// ASK FOR THE DATE LIMIT (VALUE ENTERED WILL LIST ALL FILES UP TO THAT DATE)
$humanDateLimit = readline("Enter Date Limit (YYYY-MM-DD): ");
$dateLimit = strtotime($humanDateLimit);

// CREATE A NEW FOLDER WHICH MATCHES THE DATE THE USER ENTERED
mkdir($humanDateLimit);

// GRAB ALL THE CHANNELS

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "$channelListResource?token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

$response = curl_exec($ch);
curl_close($ch);

// GET THE JSON INTO AN ARRAY SO WE CAN ACCESS VARIABLES
$channelList = json_decode($response, true);

// LOOP THROUGH THE CHANNELS ARRAY AND EXTRACT EACH ONE
foreach($channelList as $channel) {
  $channel = $channelList['channels'];
}

// LOOP THROUGH EACH CHANNEL AND EXTRACT THE NAME AND ID
foreach($channel as $channelName) {
  $name = $channelName['name'];
  $id = $channelName['id'];

  // CREATE A NEW FOLDER WHICH MATCHES THE CHANNEL ID SO WE CAN ADD FILES MATCHING THE CHANNEL ID
  mkdir("$humanDateLimit/$id");
}

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

  $userFileList = json_decode($response, true);
  // print_r($pageCount);

// LOOP THROUGH THE FILES ARRAY AND EXTRACT EACH ONE
  foreach($userFileList as $userFile) {
    $userFile = $userFileList['files'];
    // print_r($userFile);
  }

  // LOOP THROUGH EACH FILE AND EXTRACT ID, DOWNLOAD LINK, TITLE, TYPE, AND RELATED CHANNEL
  foreach($userFile as $fileDetails) {
    $fileId = $fileDetails['id'];
    $privateUrl = $fileDetails['url_private'];
    $title = $fileDetails['title'];
    $fileType = $fileDetails['filetype'];
    $channelFile = $fileDetails['channels'][0];
    print_r($fileId);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $privateUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      "Authorization: Bearer $token")
    );

    $response = curl_exec($ch);

// SAVE EACH FILE INTO THE MATCHING CHANNEL (NEED TO SEE IF WE CAN CONVERT ID TO CHANNEL NAME)
    file_put_contents ("$humanDateLimit/$channelFile/$title.$fileType" ,$response);
    curl_close($ch);
  }
}

// // FETCH CHANNELS ONE MORE TIME SO WE CAN RENAME THE CHANNELS TO HUMAN FRIENDLY VERSION

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "$channelListResource?token=$token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

$response = curl_exec($ch);
curl_close($ch);

// GET THE JSON INTO AN ARRAY SO WE CAN ACCESS VARIABLES
$channelList = json_decode($response, true);

// LOOP THROUGH THE CHANNELS ARRAY AND EXTRACT EACH ONE
foreach($channelList as $channel) {
  $channel = $channelList['channels'];
}

// LOOP THROUGH EACH CHANNEL AND EXTRACT THE NAME AND ID
foreach($channel as $channelName) {
  $name = $channelName['name'];
  $id = $channelName['id'];

  // RENAME THE CHANNELS TO HUMAN FRIENDLY VERSION
  rename ("$humanDateLimit/$id", "$humanDateLimit/$name");
}
?>
