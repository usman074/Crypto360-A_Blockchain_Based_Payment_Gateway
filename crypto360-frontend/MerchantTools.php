<?php
session_start();
$BASE_URL="http://localhost:7270";
$WEB_BASE_URL = "http://localhost/crypto360/";
$data_fetched = false;
$error = false; //to check if api sends any error
$key_generated_success = false; //to check if api key has generated
$key_generated_already = false; // to check if user already had a key (max allow 1)
$key_deleted_success = false; //to check key deleted
$no_key_to_del = false; //to check if there is some key to be deleted or not
$auth_error = false;
if(isset($_SESSION["firstName"]))
{
  $firstName = $_SESSION["firstName"];
  if($_GET['api'] == "generate")
  {
    $url=$BASE_URL."/api/apiadd";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
      $key_generated_success = true;
    }
    else
    {
      if($php_dictionary["error"] == "You have already generated Api key")
      {
        $key_generated_already = true;
      }
      else if($php_dictionary["error"] == "Authentication Failed")
      {
        $auth_error = true;
      }
      else
      {
        $error=true;
      }
    }
  }
  else if($_GET['api'] == "delete")
  {
    $url=$BASE_URL."/api/delapi";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
      $key_deleted_success = true;
    }
    else
    {
      if($php_dictionary["error"] == "No Api key to be deleted.")
      {
        $no_key_to_del = true;
      }
      else if($php_dictionary["error"] == "Authentication Failed")
      {
        $auth_error = true;
      }
      else{
        $error = true;
      }
    }
  }
  else
  {
    $url=$BASE_URL."/api";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
      if($php_dictionary["response"] == "You have not generated any Api key.")
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
            if($key_generated_success == true)
            {
        ?>
              <script>
                  swal("Success", "Api Key Generated Successfully", "success").then((value)=>{
                    window.location.href='http://localhost/crypto360/MerchantTools.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
        <?php      
            }
            if($key_generated_already == true)
            {
        ?>
                <script>
                  swal("Error", "You have already generated Api key.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/MerchantTools.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
        <?php        
            }
            if($key_deleted_success == true)
            {
        ?>
                <script>
                  swal("Success", "Api Key Deleted Successfully", "success").then((value)=>{
                    window.location.href='http://localhost/crypto360/MerchantTools.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
        <?php
            }
            if($no_key_to_del == true)
            {
        ?>
                <script>
                  swal("Error", "You dont have any Api Key to be deleted.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/MerchantTools.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
        <?php
            }
            if($auth_error == true)
            {
        ?>
               <script>
                  swal("Error", "Authentication Failed. Please Login Again.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/logout.php';
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
          <h1><i class="fas fa-briefcase"></i> Merchant Tools</h1>
        </div>
      </div>
    </header>

    <section id="breadcrumb">
      <div class="container">
        <ol class="breadcrumb">
          <li class="active">Merchant Tools</li>
        </ol>
      </div>
    </section>

    <section id="main">
        <div class="container">
          <div class="row">
              <div class="col-md-3">
                  <div class="list-group">
                      <a href="dashboard.php" class="list-group-item"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Dashboard</a>
                      <a href="payments.php?payment=all" class="list-group-item"><i class="fas fa-file-invoice-dollar"></i> Payments</a>
                      <a href="MerchantTools.php" class="list-group-item active main-color-bg"><i class="fas fa-briefcase"></i></span> Merchant Tools</a>
                      <a href="settings.php?action=profile" class="list-group-item"><span class="glyphicon glyphicon-cog" aria-hidden="true" ></span>  Settings</a>
                                
                    </div>
              </div>
              <div class="col-md-3">
                <div class="row">
                  <div class="col-md-12">
                      <img src="images/cart.png" style="width:100px; height:100px">
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <p style="text-align: left">Crypto360 Plugin</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <a href="https://www.dropbox.com/s/fhkqq7xmuacms86/Crypto360.zip?dl=0" target="_blank">Download Plugin</a>
                    </div>
                    
                </div>
              </div>
              <div class="col-md-3">
                  <div class="row">
                      <div class="col-md-12">
                          <img src="images/api.png" style="width:100px; height:100px">
                      </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <p style="text-align: left">Crypto360 Restful API</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <a href="MerchantTools.php?api=generate">Get API Key</a>
                        </div>
                        
                    </div>
              </div>
              <div class="col-md-3">
                
              </div>
          </div>

          <div class="row">
              <div class="col-md-3">
                </div>
            <div class="col-md-3">
              <label>API Key</label><br>
              <label>
                <?php
                  if($data_fetched == true)
                  {
                    echo $php_dictionary["response"];
                  }
                  else if($data_fetched == false)
                  {
                    echo "No Api Key";
                  }
                ?>
              </label>
            </div>
            <div class="col-md-3">
            </div>
            <div class="col-md-3">
                <label>Delete?</label><br>
                <button type="button" class="btn btn-primary" style="background-color : #002754; border-color: #002754" onclick=apiDel()>Delete Key</button>
            </div>
            
          </div>
        </div>
      </section>

    


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"> </script>
    <script src="js/script.js"> </script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
      var BASE_URL_WEB = "http://localhost/crypto360/";
      function apiDel()
      {
        window.location.href = BASE_URL_WEB+"MerchantTools.php?api=delete";
      }
    </script>
    
  </body>
</html>
