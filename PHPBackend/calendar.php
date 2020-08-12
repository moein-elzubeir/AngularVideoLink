<?php

//Circumvent CORS
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Kama');
define('CREDENTIALS_PATH', '~/.credentials/token.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');

date_default_timezone_set('America/New_York'); // Prevent DateTime tz exception

try{
  $postdata = file_get_contents("php://input");

  if(isset($postdata) && !empty($postdata)){
    // Extract the data.
    $request = json_decode($postdata);
    $userA = $request->userA;
    $userB = $request->userB;
    $date = $request->date;
    $time = $request->time;
  }


  /**
   * Returns an authorized API client.
   * @return Google_Client the authorized client object
  */
  function getClient() {
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');

    //for local environment
    $guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false)));
          $client->setHttpClient($guzzleClient);

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
    if (file_exists($credentialsPath)) {
      $accessToken = json_decode(file_get_contents($credentialsPath), true);
    }
    // else {
      // can't request authorization directly - backend
      // create .credentials file
    //   if(!credentials_in_browser()) {
    //       // Request authorization from the user.
    //       $authUrl = $client->createAuthUrl();
    //       return "<a href='$authUrl'>Click Here To Link Your Google Data</a>";
    //   }
    //
    //   //Get verification code
    //   $authCode = $_GET['code'];
    //
    //   // Exchange authorization code for an access token.
    //   $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    //
    //   // Store the credentials to disk.
    //   if(!file_exists(dirname($credentialsPath))) {
    //     mkdir(dirname($credentialsPath), 0700, true);
    //   }
    //   file_put_contents($credentialsPath, json_encode($accessToken));
    // }

    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
      $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
  }

  function expandHomeDirectory($path) {
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
      $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
  }

  // function credentials_in_browser() {
  //     if(isset($_GET['code'])) return true;
  //
  //     return false;
  // }

  $date_time = new DateTime($date.'T'.$time);
  $end_date_time = new DateTime($date.'T'.$time);;
  $end_date_time->add(new DateInterval('PT1H'));

  // Get the API client and construct the service object.
  $client = getClient();
  $service = new Google_Service_Calendar($client);
  $event = new Google_Service_Calendar_Event(array(
    'summary' => "Kama Test",
    'description' => "Description of the event",
    'colorId' => 11,
    'sendUpdates' => 'all',
    'start' => array(
        'dateTime' => date('c', $date_time->getTimestamp()),
        'timeZone' => 'America/New_York',
    ),
    'end' => array(
      'dateTime' => date('c', $end_date_time->getTimestamp()),
      'timeZone' => 'America/New_York',
    ),

    'conferenceData' => array(
      'createRequest' => array(
        'requestId'=> $userA.'x'.$userB.rand(),
        'conferenceSolutionKey'=> array(
          'type'=> "eventHangout",
        ),
        'status' => array(
          'statusCode' => 'success'
        )
      ),
    ),

    'attendees' => array(
      array('email' => $userA),
      array('email' => $userB)
    ),

    'guestsCanInviteOthers' => FALSE,
    'visibility' => "private",

    'reminders' => array(
      'useDefault' => FALSE,
      'overrides' => array(
        array('method' => 'email', 'minutes' => 24 * 60),
        array('method' => 'popup', 'minutes' => 10),
      ),
    ),
  ));

  $params = ['conferenceDataVersion' => 1, "sendNotifications" => "true" ];
  $calendarId = 'primary';
  $event = $service->events->insert($calendarId, $event, $params);

  header('Content-Type: application/json');
  $json = json_encode(['data'=>$event->hangoutLink]);
  if ($json === false) {
      // JSONify the error message instead:
      $json = json_encode(["jsonError" => json_last_error_msg()]);
      if ($json === false) {
          // This should not happen, but we go all the way now:
          $json = '{"jsonError":"unknown"}';
      }
      // Set HTTP response status code to: 500 - Internal Server Error
      http_response_code(500);
  }
  echo $json;
}
catch(Google_Service_Exception $gse){
  echo json_encode(['data'=>$gse->getMessage()]);
}
catch(Exception $ex){
  echo json_encode(['data'=>$ex->getMessage()]);
}
?>
