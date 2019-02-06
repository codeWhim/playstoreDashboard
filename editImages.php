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
  if(empty($editId)){
    $editId = mt_rand();
  }

  if(isset($_POST['submit'])){

    if(isset($_POST['language'])){$language = $_POST['language'];}
    if(isset($_POST['imageType'])){$imageType = $_POST['imageType'];}
    if(isset($_POST['imageId'])){$imageId = $_POST['imageId'];}

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

              $s = $androidpublisherService->edits_images->delete($packageName, $editId, $language, $imageType, $imageId);
              $htmlBody .= "Deleted the Image.";

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= $edits;

    }elseif($_POST['submit']=="Delete All Localized images"){

              $s = $androidpublisherService->edits_images->deleteall($packageName, $editId, $language, $imageType);
              $htmlBody .= "Deletec all the specified Images.";

              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= $edits;

    }elseif($_POST['submit']=="Get Localized Listing"){

              $s = $androidpublisherService->edits_images->listEditsImages($packageName, $editId, $language, $imageType);
              //$htmlBody .= $s;

              $htmlBody.= ' All images for the specified language and image type: <br /> <table class="table table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Image ID</th>
                    <th scope="col">Image (and URL)</th>
                    <th scope="col">SHA1</th>
                  </tr>
                </thead>
                <tbody>';
              $number = 0;

              foreach($s->images as $x){

                $number++;

                $htmlBody.='<tr>
                  <th scope="row">'.$number.'</th>
                  <td>'.$x['id'].'</td>
                  <td><a href="'.$x['url'].'" target="_blank"><img src="'.$x['url'].'"><br />'.$x['url'].'</a></td>
                  <td>'.$x['sha1'].'</td>
                </tr>';

              }

              $htmlBody .='</tbody></table>';


              $edits = $androidpublisherService->edits->commit($packageName, $editId);
              //$htmlBody .= $edits;

    }elseif($_POST['submit']=="Upload"){

              $target_dir = "uploads/images/";
              $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
              $uploadOk = 1;
              $fileType = pathinfo($target_file,PATHINFO_EXTENSION);
              // Check if file already exists
              $number = 0;
              while(file_exists($target_file)) {
                $number++;
                  $target_file = $target_dir .$number. basename($_FILES["fileToUpload"]["name"]);
              }
              // Check file size
              if ($_FILES["fileToUpload"]["size"] > (15*1024*1024)) { // 15 MB
                  $htmlBody .= "Sorry, your file is too large.";
                  $uploadOk = 0;
              }
              // Allow certain file formats
              if($fileType != "jpeg" && $fileType != "png" && $fileType != "jpg") {
                  $htmlBody .= "Sorry, only .jpeg and .png files are allowed.";
                  $uploadOk = 0;
              }
              // Check if $uploadOk is set to 0 by an error
              if ($uploadOk == 0) {
                  $htmlBody .= "Sorry, your file was not uploaded.";
              // if everything is ok, try to upload file
              } else {
                  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

                      $htmlBody .= "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";

                      $s = $androidpublisherService->edits_images->upload($packageName, $editId, $language, $imageType, array(
                        'mimeType' => 'image/*',
                        'uploadType' => 'multipart',
                        'data' => file_get_contents($target_file)
                      ));
                      //$htmlBody .= $s;

                      $edits = $androidpublisherService->edits->commit($packageName, $editId);
                      //$htmlBody .= $edits;



                  } else {
                      $htmlBody .= "Sorry, there was an error uploading your file.";
                  }
              }

              

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

<h3>Edit  images</h3>
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
        <input type="text" name="imageId" placeholder="Image ID" class="form-control" required>
        <small id="helpText" class="form-text text-muted">Unique identifier an image within the set of images attached to this edit.</small>
      </div>

      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>

      <div class="form-group">
      <select class="form-control" name="imageType" placeholder="Image Type" required>
        <option disabled selected>Image Type</option>
        <option value="featureGraphic">Featured Graphics</option>
        <option value="icon">Icon</option>
        <option value="phoneScreenshots">Phone Screenshots</option>
        <option value="promoGraphic">Promotional Graphics</option>
        <option value="sevenInchScreenshots">Seven Inch Screenshots</option>
        <option value="tenInchScreenshots">Ten Inch Screenshots</option>
        <option value="tvBanner">TV Banner</option>
        <option value="tvScreenshots">TV Screenshots</option>
        <option value="wearScreenshots">Wear Screenshots</option>
      </select>
      </div>
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <input type="submit" name="submit" value="Delete" class="btn btn-danger" />
    </form>

    <br /><br /><br />

    <form method="post">
      
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <div class="form-group">
      <select class="form-control" name="imageType" placeholder="Image Type" required>
        <option disabled selected>Image Type</option>
        <option value="featureGraphic">Featured Graphics</option>
        <option value="icon">Icon</option>
        <option value="phoneScreenshots">Phone Screenshots</option>
        <option value="promoGraphic">Promotional Graphics</option>
        <option value="sevenInchScreenshots">Seven Inch Screenshots</option>
        <option value="tenInchScreenshots">Ten Inch Screenshots</option>
        <option value="tvBanner">TV Banner</option>
        <option value="tvScreenshots">TV Screenshots</option>
        <option value="wearScreenshots">Wear Screenshots</option>
      </select>
      </div>
      <input type="submit" name="submit" value="Delete All Localized images" class="btn btn-danger" />
    </form>

    <br /><br /><br />

    <form method="post">
      
      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>
      <input type="hidden" name="editId" value="<?php echo $editId; ?>">
      <div class="form-group">
      <select class="form-control" name="imageType" placeholder="Image Type" required>
        <option disabled selected>Image Type</option>
        <option value="featureGraphic">Featured Graphics</option>
        <option value="icon">Icon</option>
        <option value="phoneScreenshots">Phone Screenshots</option>
        <option value="promoGraphic">Promotional Graphics</option>
        <option value="sevenInchScreenshots">Seven Inch Screenshots</option>
        <option value="tenInchScreenshots">Ten Inch Screenshots</option>
        <option value="tvBanner">TV Banner</option>
        <option value="tvScreenshots">TV Screenshots</option>
        <option value="wearScreenshots">Wear Screenshots</option>
      </select>
      </div>
      <input type="submit" name="submit" value="Get Localized Listing" class="btn btn-success" />
    </form>

    <br /><br /><br />

    <form method="post" enctype="multipart/form-data">
      <div class="form-group">
        <input type="hidden" name="editId" value="<?php echo $editId; ?>">
        <label for="fileToUpload">Select an Image to upload</label>
        <input type="file" name="fileToUpload" id="fileToUpload" required>
      </div>
      <div class="form-group">
        <input type="text" name="language" placeholder="The Language Code" class="form-control" required>
        <small id="helpText" class="form-text text-muted">The language code (a BCP-47 language tag) of the -specific localized listing to read or modify. For example, to select Austrian German, pass "de-AT"</small>
      </div>
      <div class="form-group">
      <select class="form-control" name="imageType" placeholder="Image Type" required>
        <option disabled selected>Image Type</option>
        <option value="featureGraphic">Featured Graphics</option>
        <option value="icon">Icon</option>
        <option value="phoneScreenshots">Phone Screenshots</option>
        <option value="promoGraphic">Promotional Graphics</option>
        <option value="sevenInchScreenshots">Seven Inch Screenshots</option>
        <option value="tenInchScreenshots">Ten Inch Screenshots</option>
        <option value="tvBanner">TV Banner</option>
        <option value="tvScreenshots">TV Screenshots</option>
        <option value="wearScreenshots">Wear Screenshots</option>
      </select>
      </div>
      <input type="submit" name="submit" value="Upload" class="btn btn-info" />
    </form>

  	<br /><br /><br />

    <a class='btn btn-warning login' href="?logout">Logout</a>

    <br /><br />

    <!--<p>Here is the data from your Id Token:</p>-->
    <!--<pre><?php var_export($token_data) ?></pre>-->
  </div>
<?php endif ?>
</div>

