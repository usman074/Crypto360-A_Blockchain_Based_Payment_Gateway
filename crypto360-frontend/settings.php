<?php
session_start();
$BASE_URL="http://localhost:7270";
$WEB_BASE_URL = "http://localhost/crypto360/";
$data_fetched = false; //to check xpubkey is empty or not
$error = false; //to check if api sends any error
$profile_update_success = false; //to check if profile updated
$profile_update_fail = false; //to check if profile did not update => error from api
$pass_update_success = false; //to check if password updated
$pass_update_fail = false; //to check if password did not update => error from api
$check_new_pass = false; //to check new and confirm new passwords are same
$pass_length = false; //to check length of new password
$old_pass_mismatch = false; //to check if password did not update bcz of old password mismatch => error from api
$xpub_empty = false; //to check xpubkey field is empty or not
$xpub_added_success = false; //to check if xpubkey added
$xpub_added_fail = false; //to check if xpubkey did not added => error from api
$auth_error = false;
if(isset($_SESSION["firstName"]))
{
  $firstName = $_SESSION["firstName"];
  if($_GET['action'] == "profile")
  {
    $url=$BASE_URL."/settings/profile";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
        foreach($php_dictionary["response"] as $x => $x_value) {
          $_GET[$x] = $x_value;
      }
      $url=$BASE_URL."/settings/CoinSettings";
      $result_in_json = call("GET",$url);
      $php_dictionary = json_decode($result_in_json, true);
      if($php_dictionary["error"] == null)
      {
        if($php_dictionary["response"] == "No key found")
        {
          $data_fetched = false;
        }
        else{
          $data_fetched = true;
        } 
      }
      else{
        if($php_dictionary["error"] == "Authentication Failed")
        {
          $auth_error = true;
        }
        else{
          $error = true;
        }
      }
    }
    else{
      if($php_dictionary["error"] == "Authentication Failed")
      {
        $auth_error = true;
      }
      else{
        $error = true;
      }
    }
  }
  else if($_GET['action'] == "profileUpdate")
  {
    $url=$BASE_URL."/settings/profile/update";
    $data_array = array('firstname'=> $_GET['fName'], 'lastname'=> $_GET['lName'], 'orgName'=> $_GET['orgName']);
    
    $data_json = json_encode($data_array);
    
    $result_in_json = call("POST",$url,$data_json);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
        $profile_update_success = true;
    }
    else{
      if($php_dictionary["error"] == "Authentication Failed")
      {
        $auth_error = true;
      }
      else{
        $profile_update_fail = true;
      }
    }
  }
  else if($_GET['action'] == "passUpdate")
  {
    
    if($_POST['NewPass'] === $_POST['ConfNewPass'])
    {
      if(strlen($_POST['NewPass']) >=8)
      {
        $url=$BASE_URL."/settings/security";
        $data_array = array('oldpassword'=> $_POST['CurrPass'], 'newpassword'=> $_POST['NewPass']);
        
        $data_json = json_encode($data_array);
        
        $result_in_json = call("POST",$url,$data_json);
        $php_dictionary = json_decode($result_in_json, true);
        if($php_dictionary["error"] == null)
        {
          $pass_update_success = true;
        }
        else
        {
          if($php_dictionary["error"]=="Old Password mismatch")
          {
            $old_pass_mismatch = true;
          }
          else if($php_dictionary["error"] == "Authentication Failed")
          {
            $auth_error = true;
          }
          else{
            $pass_update_fail=true;
          }
        }
      }
      else{
        $pass_length = true;
      }
        
    }
    else
    {
      $check_new_pass = true;
    }
  
  }
  else if($_GET['action'] == "addXpub")
  {
    if(strlen($_POST['xPubBtc']) >1)
    {
        $url=$BASE_URL."/settings/CoinSettings/addXpub";
        $data_array = array('xpubkey'=> $_POST['xPubBtc']);
        
        $data_json = json_encode($data_array);
        
        $result_in_json = call("POST",$url,$data_json);
        $php_dictionary = json_decode($result_in_json, true);
        if($php_dictionary["error"] == null)
        {
          $xpub_added_success = true;
        }
        else{
          if($php_dictionary["error"] == "Authentication Failed")
          {
            $auth_error = true;
          }
          else{
            $xpub_added_fail = true;
          }
          
        }
    }
    else
    {
      $xpub_empty = true;
    }
  
  }
  
}
else{
  header('Location: '.$WEB_BASE_URL.'index.php');
}

function call($method, $url, $data_json_string  = false)
{
	
    // 1. initialize
    $curl = curl_init();

    // 2. set the options, including the url

    if ($method == "POST") {
        curl_setopt($curl,CURLOPT_POST,1);

        if($data_json_string){
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json_string );
        }
    }
    else
    {
        if ($data_json_string){
            $url = sprintf("%s%s", $url, http_build_query($data_json_string ));
        }
    }

    curl_setopt($curl,CURLOPT_URL,$url);
    curl_setopt($curl,CURLOPT_HTTPHEADER,["Content-Type:application/json","x-access-token:".$_SESSION["x-acess-token"]]);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);

    // 3. Execute and Fetch Result

    $result = curl_exec($curl);  // Getting jSON result string
		//echo $result;
    //check if there is some error in output

    if ($result === FALSE) {
        echo "cURL Error: " . curl_error($curl);
    }
//
// 4. free up the curl handle

    curl_close($curl);

    return $result;
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crypto360</title>
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  </head>

  <body>
    <?php
        if($error == true)
        {
    ?>
          <script>swal("Error", "Unexpected Error", "error");</script>
    <?php      
        }
        if($profile_update_success == true)
        {
    ?>
           <script>
              swal("Success", "Profile Updated Successfully", "success").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php      
        }
        if($profile_update_fail == true)
        {
    ?>
            <script>
              swal("Error", "Profile Not Updated Successfully", "error").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php      
        }
        if($check_new_pass == true)
        {
    ?>
            <script>
              swal("Error", "New and confirm new password didn't match", "error").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($pass_length == true)
        {
    ?>
            <script>
              swal("Info", "Password must be of 8 chracters", "info").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($pass_update_success == true)
        {
    ?>
            <script>
              swal("Success", "Password Updated Successfully", "success").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($pass_update_fail == true)
        {
    ?>
            <script>
              swal("Error", "Unexpected Error", "error").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($old_pass_mismatch == true)
        {
    ?>
            <script>
              swal("Error", "Old Password Mismatch", "error").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($xpub_empty == true)
        {
    ?>
            <script>
              swal("Error", "Xpublic Key can't be empty", "error").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($xpub_added_success == true)
        {
    ?>
            <script>
              swal("Success", "Xpublic Key Added Successfully", "success").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
        if($xpub_added_fail == true)
        {
    ?>
            <script>
              swal("Error", "Unexpected Error", "error").then((value)=>{
                window.location.href='http://localhost/crypto360/settings.php?action=profile';
              }).catch((error)=>{
                swal("Error", error, "error");
              });
            </script>
    <?php
        }
    ?>

    <nav class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="dashboard.php?coin=BTC">Crypto360</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="dashboard.php?coin=BTC">Welcome, <?php echo $firstName ?></a></li>
            <li><a href="logout.php">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <header id="header">
      <div class="container">
        <div class="col-md-12">
          <h1><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Settings</h1>
        </div>
      </div>
    </header>

    <section id="breadcrumb">
      <div class="container">
        <ol class="breadcrumb">
          <li class="active">Settings</li>
        </ol>
      </div>
    </section>

    <section id="main">
        <div class="container">
          <div class="row">
              <div class="col-md-3">
                  <div class="list-group">
                      <a href="dashboard.php?coin=BTC" class="list-group-item"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Dashboard</a>
                      <a href="payments.php?payment=all" class="list-group-item"><i class="fas fa-file-invoice-dollar"></i> Payments</a>
                      <a href="MerchantTools.php" class="list-group-item"><i class="fas fa-briefcase"></i> Merchant Tools</a>
                      <a href="settings.php?action=profile" class="list-group-item active main-color-bg"><span class="glyphicon glyphicon-cog" aria-hidden="true" ></span>  Settings</a>
                                
                    </div>
  
              </div>
            <div class="col-md-9">
            
                <div>

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs">
                        <li class="active"><a data-toggle="tab" href="#profile">Profile</a></li>
                        <li><a data-toggle="tab" href="#passChng">Login & Security</a></li>
                        <li><a data-toggle="tab" href="#coinSettings">Coin Settings</a></li>
                    </ul>
                  
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div id="profile" class="tab-pane fade in active">
                          <div class="row">
                              <div class="col-md-12">
                                  <h3>Profile</h3>
                              </div>
                          </div>
                          <div class="row">
                              <div class="col-md-3">
                                  <label>First Name</label>
                              </div>
                              <div class="col-md-9">
                                  <div class="col-xs-3">
                                      <input class="form-control" id="fName" type="text" value="<?php echo $_GET['firstName'] ?>">
                                    </div>
                              </div>
                          </div>
                          <br>
                          <div class="row">
                              <div class="col-md-3">
                                  <label>Last Name</label>
                              </div>
                              <div class="col-md-9">
                                  <div class="col-xs-3">
                                      <input class="form-control" id="lName" type="text" value="<?php echo $_GET['lastName'] ?>">
                                    </div>
                                </div>
                          </div>
                          <br>
                          <div class="row">
                              <div class="col-md-3">
                                  <label>Oranization Name</label>
                              </div>
                              <div class="col-md-9">
                                  <div class="col-xs-3">
                                      <input class="form-control" id="OrgName" type="text" value="<?php echo $_GET['organizationName'] ?>">
                                    </div>
                                </div>
                          </div>
                          <br>
                          <div class="row">
                              <div class="col-md-3">
                                  <label>Email</label>
                              </div>
                              <div class="col-md-9">
                                  <p>
                                    <?php
                                      echo $_GET["email"];
                                    ?>
                                  </p>
                              </div>
                          </div>
                          <br>
                          <div class="row">
                              <div class="col-md-3">
                                  <label>Merchant Id</label>
                              </div>
                              <div class="col-md-9">
                                  <p>
                                    <?php
                                      echo $_GET['merchant_id']
                                    ?>
                                  </p>
                                </div>
                          </div>
                          <br>
                          <div class="row">
                              <div class="col-md-3">
                                  <button type="button" class="btn btn-primary" style="background-color : #002754; border-color: #002754" onclick=profileUpdate()>Update</button>
                              </div>
                              <div class="col-md-9">
                                </div>
                          </div>
                        </div>
                        <div id="passChng" class="tab-pane fade">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Login & Security</h3>
                                </div>
                            </div>
                            <form action="settings.php?action=passUpdate" method="post">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Current Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="col-xs-6">
                                            <input class="form-control" name="CurrPass" type="password">
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>New Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="col-xs-6">
                                            <input class="form-control" name="NewPass" type="password">
                                        </div>
                                      </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Confirm New Password</label>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="col-xs-6">
                                            <input class="form-control" name="ConfNewPass" type="password">
                                        </div>
                                      </div>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary" style="background-color : #002754; border-color: #002754" onclick=passUpdate()>Update</button>
                                    </div>
                                    <div class="col-md-9">
                                    </div>
                                </div>
                            </form>
                            
                        </div>
                        
                        <div id="coinSettings" class="tab-pane fade">
                          <form action="settings.php?action=addXpub" method="post">
                              <div class="row">
                                  <div class="col-md-12">
                                      <h3>Coin Settings</h3>
                                  </div>
                              </div>
                              <div class="row">
                                  <div class="col-md-2">
                                    <label>Coin Name</label><br>
                                    <p>Bitcoin</p>
                                  </div>
                                  <div class="col-md-2">
                                    <label>Coin Code</label><br>
                                    <p>BTC<p>
                                  </div>
                                  <div class="col-md-5">
                                          <label>Your Bitcoin Xpubkey</label><br>
                                          <input class="form-control" name="xPubBtc" type="text" value="<?php if($data_fetched) { echo $php_dictionary['response']; } else { echo ' '; }?>">
                                  </div>
                                  <div class="col-md-3">
                                    </label></label><br>
                                    <button type="submit" class="btn btn-primary" style="background-color : #002754; border-color: #002754">Add</button>
                                  </div>
                              </div>
                          </form>
                        </div>
                        
                      </div>
                  
                </div>
            
          </div>
        </div>
      </section>

    


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script> src="js/bootstrap.min.js">s</script>
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"> </script>
    <script src="js/script.js"> </script>
    <script>
      var BASE_URL = "http://localhost:7270/";
      var BASE_URL_WEB = "http://localhost/crypto360/";
      function profileUpdate() {
        var fname = document.getElementById('fName').value;
        var lname = document.getElementById('lName').value;
        var orgName = document.getElementById('OrgName').value;
        window.location.href = BASE_URL_WEB+"settings.php?action=profileUpdate&fName="+fname+"&lName="+lname+"&orgName="+orgName;
      }
    </script>
  </body>
</html>
