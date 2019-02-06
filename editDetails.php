<?php

error_reporting(E_ALL);

require_once "includes.php";

$htmlBody = "";
$editId = "";
$packageName = $config['packageName'];

$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
$client = new Google_Client();
//$guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
//$client->setHttpClient($guzzleClient);
$client->setAuthConfig('client_id.json');
$client->setRedirectUri($redirect_uri);
$client->setScopes('email');
$client->addScope("https://www.googleapis.com/auth/androidpublisher");
$client->setAccessType("offline");
/************************************************
 * If we're logging out we just need to clear our
 * local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['id_token_token']);
}
/************************************************
 * If we have a code back from the OAuth 2.0 flow,
 * we need to exchange that with the
 * Google_Client::fetchAccessTokenWithAuthCode()
 * function. We store the resultant access token
 * bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  $client->setAccessToken($token);
  // store in the session also
  $_SESSION['id_token_token'] = $token;
  // redirect back to the example
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
  //var_dump($_SESSION);
if (
  !empty($_SESSION['id_token_token'])
  && isset($_SESSION['id_token_token']['id_token'])
) {
  $client->setAccessToken($_SESSION['id_token_token']);
} else {
  $authUrl = $client->createAuthUrl();
}
/************************************************
  If we're signed in we can go ahead and retrieve
  the ID token, which is part of the bundle of
  data that is exchange in the authenticate step
  - we only need to do a network call if we have
  to retrieve the Google certificate to verify it,
  and that can be cached.
 ************************************************/
if ($client->getAccessToken()) {
  $token_data = $client->verifyIdToken();
}




if (!isset($authUrl)) {

  if(isset($_POST['editId'])){$editId = $_POST['editId'];}

  if(isset($_POST['submit'])){

    if(isset($_POST['language'])){$language = $_POST['language'];}

    if($_POST['submit']=='Update Package'){

      $packageName = $_POST['packageName'];
      setConfig('packageName',$packageName);


    }

    try{

    if(!empty($packageName)){

      $androidpublisherService = new Google_Service_AndroidPublisher($client);

      $appEdit = new Google_Service_AndroidPublisher_AppEdit();
      $appEdit->setId($editId);
      //$editId = $appEdit->getId();
      $appEdit->setExpiryTimeSeconds(1500000000);
      //$apkdetails = $androidpublisherService->apkdetails;
      $edits = $androidpublisherService->edits->insert($packageName, $appEdit);
      $editsArray = (array) $edits;
      //$editId = $edits.getId();
      $editId = $editsArray['id'];


      

    }


    if($_POST['submit']=="Get Details"){

              $s = $androidpublisherService->edits_details->get($packageName, $editId);
              
              $htmlBody.= ' App Details: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Default Language</th>
                    <th scope="col">Contact Website</th>
                    <th scope="col">Contact Email</th>
                    <th scope="col">Contact Phone</th>
                  </tr>
                </thead>
                <tbody>';

                $htmlBody.='<tr>
                  <td>'.$s['defaultLanguage'].'</td>
                  <td>'.$s['contactWebsite'].'</td>
                  <td>'.$s['contactEmail'].'</td>
                  <td>'.$s['contactPhone'].'</td>
                </tr>';

              $htmlBody .='</tbody></table>';

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }elseif($_POST['submit']=="Update"){


              $defaultLanguage = $_POST['defaultLanguage'];
              $contactWebsite = $_POST['contactWebsite'];
              $contactEmail = $_POST['contactEmail'];
              $contactPhone = $_POST['contactPhone'];

              $postBody = new Google_Service_AndroidPublisher_AppDetails();
              $postBody->setDefaultLanguage($defaultLanguage);
              $postBody->setContactWebsite($contactWebsite);
              $postBody->setContactEmail($contactEmail);
              $postBody->setContactPhone($contactPhone);

              $s = $androidpublisherService->edits_details->update($packageName, $editId, $postBody);
              
              $htmlBody.= ' Localized store listing updated: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Default Language</th>
                    <th scope="col">Contact Website</th>
                    <th scope="col">Contact Email</th>
                    <th scope="col">Contact Phone</th>
                  </tr>
                </thead>
                <tbody>';

                $htmlBody.='<tr>
                  <td>'.$s['defaultLanguage'].'</td>
                  <td>'.$s['contactWebsite'].'</td>
                  <td>'.$s['contactEmail'].'</td>
                  <td>'.$s['contactPhone'].'</td>
                </tr>';

              $htmlBody .='</tbody></table>';

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }

  } catch (Google_Service_Exception $e) {
  $htmlBody = sprintf('<p>A service error occurred: <code>%s</code></p>',
  htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
  $htmlBody = sprintf('<p>An client error occurred: <code>%s</code></p>',
  htmlspecialchars($e->getMessage()));
  }

  }

 

}

require_once "header.php";
require_once "navigation.php";

?>



<div class="box">
<?php if (isset($authUrl)): ?>
  <div class="request">
    <a class='btn btn-warning login' href='<?= $authUrl ?>'>Authenticate</a>
  </div>
<?php else: ?>
  <div class="data">

<h3>Edit  details</h3>
<br />
  	<?php 
    if($htmlBody!=""){
      echo '<div class="alert alert-info"><strong>Response:</strong> '.$htmlBody.'</div>';
    }
     ?>
<br />
<br />
    <form method="POST">
    <div class="row">
    <div class="col-md-6">
    <div class="input-group">
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="text" class="form-control" placeholder="Application Package Name" aria-label="Package name" name="packageName" value="<?php echo $packageName; ?>" required>
      <span class="input-group-btn">
        <input type="submit" name="submit" class="btn btn-secondary" value="Update Package" />
      </span>
    </div>
    </div>
    </div>
    </form>

    <br /><br /><br />





    
    <form method="post">
      
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Get Details" class="btn btn-success" />
    </form>


    <br /><br /><br />

    <form method="post">
      
      <div class="form-group">
        <input type="text" name="defaultLanguage" placeholder="Default Language" class="form-control">
        <small id="helpText" class="form-text text-muted">Default language code, in BCP 47 format (eg "en-US").</small>
      </div>

      <div class="form-group">
        <input type="text" name="contactWebsite" placeholder="Contact Website" class="form-control">
        <small id="helpText" class="form-text text-muted">The user-visible website for this app.</small>
      </div>

      <div class="form-group">
        <input type="text" name="contactPhone" placeholder="Contact Phone" class="form-control">
        <small id="helpText" class="form-text text-muted">The user-visible support telephone number for this app.</small>
      </div>

      <div class="form-group">
        <input type="text" name="contactEmail" placeholder="Contact Email" class="form-control">
        <small id="helpText" class="form-text text-muted">The user-visible support email for this app.</small>
      </div>

      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Update" class="btn btn-secondary" />
    </form>

  	<br /><br /><br />

    <a class='btn btn-warning login' href="?logout">Logout</a>

    <br /><br />

    <!--<p>Here is the data from your Id Token:</p>-->
    <!--<pre><?php var_export($token_data) ?></pre>-->
  </div>
<?php endif ?>
</div>

