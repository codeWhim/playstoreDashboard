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
      //$apklistings = $androidpublisherService->apklistings;
      $edits = $androidpublisherService->edits->insert($packageName, $appEdit);
      $editsArray = (array) $edits;
      //$editId = $edits.getId();
      $editId = $editsArray['id'];


      

    }


    if($_POST['submit']=="Delete"){

              $s = $androidpublisherService->edits_listings->delete($packageName, $editId, $language);
              $htmlBody .= "Listing Deleted";

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }elseif($_POST['submit']=="Delete All Localized Listings"){

              $s = $androidpublisherService->edits_listings->deleteall($packageName, $editId);
              $htmlBody .= "All Localized Listings Deleted";

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }elseif($_POST['submit']=="Get Listing"){

              $s = $androidpublisherService->edits_listings->get($packageName, $editId, $language);
              
              $htmlBody.= ' Information about a localized store listing: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Language</th>
                    <th scope="col">Title</th>
                    <th scope="col">Full Description</th>
                    <th scope="col">Short Description</th>
                    <th scope="col">Video</th>
                  </tr>
                </thead>
                <tbody>';

                $htmlBody.='<tr>
                  <td>'.$s['language'].'</td>
                  <td>'.$s['title'].'</td>
                  <td>'.$s['fullDescription'].'</td>
                  <td>'.$s['shortDescription'].'</td>
                  <td>'.$s['video'].'</td>
                </tr>';

              $htmlBody .='</tbody></table>';

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }elseif($_POST['submit']=="All Localized Store Listing"){

              $s = $androidpublisherService->edits_listings->listEditslistings($packageName, $editId);
              
              $htmlBody.= ' All Localized Store Listings: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Language</th>
                    <th scope="col">Title</th>
                    <th scope="col">Full Description</th>
                    <th scope="col">Short Description</th>
                    <th scope="col">Video</th>
                  </tr>
                </thead>
                <tbody>';
              $number = 0;

              foreach($s->listings as $x){

                $number++;

                $htmlBody.='<tr>
                  <th scope="row">'.$number.'</th>
                  <td>'.$x['language'].'</td>
                  <td>'.$x['title'].'</td>
                  <td>'.$x['fullDescription'].'</td>
                  <td>'.$x['shortDescription'].'</td>
                  <td>'.$x['video'].'</td>
                </tr>';

              }

              $htmlBody .='</tbody></table>';

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }elseif($_POST['submit']=="Patch"){

              $title = $_POST['title'];
              $fullDescription = $_POST['fullDescription'];
              $shortDescription = $_POST['shortDescription'];
              $video = $_POST['video'];

              $postBody = new Google_Service_AndroidPublisher_Listing();
              $postBody->setFullDescription($fullDescription);
              $postBody->setLanguage($language);
              $postBody->setShortDescription($shortDescription);
              $postBody->setTitle($title);
              $postBody->setVideo($video);

              $s = $androidpublisherService->edits_listings->patch($packageName, $editId, $language, $postBody);
              
              $htmlBody.= ' Localized store listing updated: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Language</th>
                    <th scope="col">Title</th>
                    <th scope="col">Full Description</th>
                    <th scope="col">Short Description</th>
                    <th scope="col">Video</th>
                  </tr>
                </thead>
                <tbody>';

                $htmlBody.='<tr>
                  <td>'.$s['language'].'</td>
                  <td>'.$s['title'].'</td>
                  <td>'.$s['fullDescription'].'</td>
                  <td>'.$s['shortDescription'].'</td>
                  <td>'.$s['video'].'</td>
                </tr>';

              $htmlBody .='</tbody></table>';

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= json_encode($edits);

    }elseif($_POST['submit']=="Update"){


              $title = $_POST['title'];
              $fullDescription = $_POST['fullDescription'];
              $shortDescription = $_POST['shortDescription'];
              $video = $_POST['video'];

              $postBody = new Google_Service_AndroidPublisher_Listing();
              $postBody->setFullDescription($fullDescription);
              $postBody->setLanguage($language);
              $postBody->setShortDescription($shortDescription);
              $postBody->setTitle($title);
              $postBody->setVideo($video);

              $s = $androidpublisherService->edits_listings->update($packageName, $editId, $language, $postBody);
              
              $htmlBody.= ' Localized store listing updated: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">Language</th>
                    <th scope="col">Title</th>
                    <th scope="col">Full Description</th>
                    <th scope="col">Short Description</th>
                    <th scope="col">Video</th>
                  </tr>
                </thead>
                <tbody>';

                $htmlBody.='<tr>
                  <td>'.$s['language'].'</td>
                  <td>'.$s['title'].'</td>
                  <td>'.$s['fullDescription'].'</td>
                  <td>'.$s['shortDescription'].'</td>
                  <td>'.$s['video'].'</td>
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

<h3>Edit  Listings</h3>
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
      
      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Delete" class="btn btn-danger" />
    </form>

    <br /><br /><br />

    <form method="post">
      
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Delete All Localized Listings" class="btn btn-danger" />
    </form>

    <br /><br /><br />

    <form method="post">
      
      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Get Listing" class="btn btn-success" />
    </form>

    <br /><br /><br />

    <form method="post">
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="All Localized Store Listing" class="btn btn-success" />
    </form>

    <br /><br /><br />

    <!--<form method="post">
      
      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Patch" class="btn btn-primary" />
    </form>

    <br /><br /><br />-->

    <form method="post">
      
      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>

      <div class="form-group">
        <textarea name="fullDescription" placeholder="Full Description" class="form-control" required rows="5"></textarea>
        <small id="helpText" class="form-text text-muted">Full description of the app; this may be up to 4000 characters in length.</small>
      </div>

      <div class="form-group">
        <input type="text" name="shortDescription" maxlength="80" placeholder="Short Description" class="form-control" required>
        <small id="helpText" class="form-text text-muted">Short description of the app (previously known as promo text); this may be up to 80 characters in length</small>
      </div>

      <div class="form-group">
        <input type="text" name="title" placeholder="Title" class="form-control" required>
        <small id="helpText" class="form-text text-muted">App's Localized Title</small>
      </div>

      <div class="form-group">
        <input type="text" name="video" placeholder="Video URL" class="form-control">
        <small id="helpText" class="form-text text-muted">URL of a promotional YouTube video for the app. </small>
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

