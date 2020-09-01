<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require __DIR__ . '/vendor/autoload.php';

try{
  $postdata = file_get_contents("php://input");

  if(isset($postdata) && !empty($postdata)){
    // Extract the data.
    $request = json_decode($postdata);
    $userA = $request->userA;
    $userB = $request->userB;
    $date = $request->time;
    $time = $request->date;
  }

  function getClient() {
  	$KEY_FILE_LOCATION = __DIR__ . '/service-account.json'; //location of service account key
  	$client = new Google_Client();
  	$client->setApplicationName('Name');
  	$client->setAuthConfig($KEY_FILE_LOCATION);
    $client->setScopes(Google_Service_Calendar::CALENDAR);
    return $client;
  }
  // Get the API client and construct the service object.

  $client = getClient();
  $service = new Google_Service_Calendar($client);
  $calendarId = 'xxxxx@example.com';

  //insert
  $event = new Google_Service_Calendar_Event(array(
    'summary' => 'Test',
    'description' => 'Somthing in the description',
    'start' => array(
        'dateTime' => date('c', $date_time->getTimestamp()),
        'timeZone' => 'America/New_York',
    ),
    'end' => array(
      'dateTime' => date('c', $end_date_time->getTimestamp()),
      'timeZone' => 'America/New_York',
    ),
    'attendees' => array(
      array('email' => $userA),
      array('email' => $userB)
    ),
    'colorId' => 11,
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
    'sendUpdates' => 'all',
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

  $event = $service->events->insert($calendarId,$event,$params = ['conferenceDataVersion' => 1, "sendNotifications" => "true" ];);


  header('Content-Type: application/json');
  $json = json_encode('data'=>$event->htmlLink);
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
  echo json_encode(['data'=>$gse]);
  //$gse->getMessage()
}
catch(Exception $ex){
  echo json_encode(['data'=>$ex)];
  //$ex->getMessage()]
}
?>
