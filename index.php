<?php

require_once "vendor/autoload.php";

/*

$guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));

$client = new Google_Client();
$client->setHttpClient($guzzleClient);
$client->setApplicationName("PlayStore Dashboard");
//$client->setDeveloperKey("AIzaSyCZnrRSJRFndjKxkqqDl5lu2-38-rYI4tk"); // In API mode
$client->setAuthConfig('client_id.json'); // In oAuth Mode

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']; // Determining current URI
$client->setRedirectUri($redirect_uri);

$client->addScope("https://www.googleapis.com/auth/androidpublisher");




// add "?logout" to the URL to remove a token from the session
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['upload_token']);
}




if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);
  // store in the session also
  $_SESSION['upload_token'] = $token;
  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
// set the access token as part of the client
if (!empty($_SESSION['upload_token'])) {
  $client->setAccessToken($_SESSION['upload_token']);
  if ($client->isAccessTokenExpired()) {
    unset($_SESSION['upload_token']);
  }
} else {
  $authUrl = $client->createAuthUrl();
}

*/


/*
POST https://datastore.googleapis.com/v1beta3/projects/YOUR_PROJECT_ID:runQuery?key=YOUR_API_KEY

{
    "query": {
        "kind": [{
            "name": "Book"
        }],
        "order": [{
            "property": {
                "name": "title"
            },
            "direction": "descending"
        }],
        "limit": 10
    }
}


$datastore = new Google_Service_Datastore($client);

// build the query - this maps directly to the JSON
$query = new Google_Service_Datastore_Query([
    'kind' => [
        [
            'name' => 'Book',
        ],
    ],
    'order' => [
        'property' => [
            'name' => 'title',
        ],
        'direction' => 'descending',
    ],
    'limit' => 10,
]);

// build the request and response
$request = new Google_Service_Datastore_RunQueryRequest(['query' => $query]);
$response = $datastore->projects->runQuery('YOUR_DATASET_ID', $request);

*/


/*

GET https://www.googleapis.com/androidpublisher/v2/applications/{packageName}/edits/{editId}/apks/{apkVersionCode}/listings

*/


// $service = new Google_Service_AndroidPublisher($client);
// $response = $service->applications->list();




//$httpClient = $client->authorize();










/*


$packageName = "com.facebook.something";


$androidpublisherService = new Google_Service_AndroidPublisher($client);




$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }
  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}
if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}
// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  try {
    
$appEdit = new Google_Service_AndroidPublisher_AppEdit();
$appEdit->setId(45632);
$appEdit->setExpiryTimeSeconds(1500000000);
//$apklistings = $androidpublisherService->apklistings;
$edits = $androidpublisherService->edits->insert($packageName, $appEdit);


  } catch (Google_Service_Exception $e) {
    $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == '783777458275-rf51b8gldo37ohq666ji0gc42s7lobfh.apps.googleusercontent.com') {
  $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;
  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}







*/




require_once "header.php";
require_once "navigation.php";


//$page = $_GET['page'];
//require_once $page.".php";



require_once "footer.php";


?>