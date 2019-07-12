<?php
session_start();
$BASE_URL="http://localhost:7270";
$WEB_BASE_URL = "http://localhost/crypto360/";
$data_fetched = false; //to check if data is received from api
$error = false; //to check if api send an error
$auth_error = false;

if(isset($_SESSION["firstName"]))
{
  $firstName = $_SESSION["firstName"];
  if($_GET['payment'] == "all")
  {
    $url=$BASE_URL."/payments/all";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
      if($php_dictionary["response"] == "No Payment Records found")
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
  else if($_GET['payment'] == "paid")
  {
    $url=$BASE_URL."/payments/paid";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
      if($php_dictionary["response"] == "No Payment Records found")
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
  else if($_GET['payment'] == "unpaid")
  {
    $url=$BASE_URL."/payments/unresolved";
    $result_in_json = call("GET",$url);
    $php_dictionary = json_decode($result_in_json, true);
    if($php_dictionary["error"] == null)
    {
      if($php_dictionary["response"] == "No Payment Records found")
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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
  </head>

  <body>
      <?php
        if($error == true)
          {
      ?>
            <script>swal("Error", "Unexpected Error", "error");</script>
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
          <h1><i class="fas fa-file-invoice-dollar"></i></span> Payments</h1>
        </div>
      </div>
    </header>

    <section id="breadcrumb">
      <div class="container">
        <ol class="breadcrumb">
          <li class="active">Payments</li>
        </ol>
      </div>
    </section>

    <section id="main">
        <div class="container">
          <div class="row">
              <div class="col-md-3">
                  <div class="list-group">
                      <a href="dashboard.php" class="list-group-item"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Dashboard</a>
                      <a href="payments.php?payment=all" class="list-group-item active main-color-bg"><i class="fas fa-file-invoice-dollar"></i> Payments</a>
                      <a href="MerchantTools.php" class="list-group-item"><i class="fas fa-briefcase"></i> Merchant Tools</a>
                      <a href="settings.php?action=profile" class="list-group-item"><span class="glyphicon glyphicon-cog" aria-hidden="true" ></span>  Settings</a>
                                
                    </div>
              </div>
            <div class="col-md-9">
              <div class="row">
                  <div class="col-md-10"></div>
                  <div class="col-md-2">
                      <div class="form-group">
                        <select class="form-control" id="sel1" onclick=PaymentValue()>
                          <option value="all">All</option>
                          <option value="paid">Paid</option>
                          <option value="unpaid">Unresolved</option>
                        </select>
                      </div>
                    </div>
              </div>
              <div class="row">
                  <div class="col-md-12">
                      <table id="datatable" class="display">
                          <thead>
                            <tr>
                              <th>Invoice Id</th>
                              <th>Time</th>
                              <th>Coin</th>
                              <th>Amount</th>
                              <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                              <?php
                                if($data_fetched == true)
                                {
                                  foreach($php_dictionary["response"] as $temp)
                                  {
                              ?>
                                    <tr>
                                      <td>
                                        <?php echo $temp["invoice_id"]?>
                                      </td>
                                      <td>
                                        <?php 
                                          $date=str_split($temp["Date"],10);
                                          echo $date[0];
                                        ?>
                                      </td>
                                      <td>
                                        <?php echo $temp["Coin"]?>
                                      </td>
                                      <td>
                                        <?php echo $temp["amount"]?>
                                      </td>
                                      <td>
                                        <?php 
                                          if($temp["Unused_Addresses"] == "true")
                                          {
                                            echo "Pending";
                                          }
                                          else if($temp["Unused_Addresses"] == "false")
                                          {
                                            echo "Paid";
                                          }
                                          
                                        ?>
                                      </td>
                                    </tr>
                              <?php  
                                  }
                                }
                              ?>
                            </tbody>
                        </table>
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
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="js/script.js"> </script>
    <script>
      var BASE_URL_WEB = "http://localhost/crypto360/";
      function PaymentValue()
      {
        var value = document.getElementById("sel1").value;
        window.location.href = BASE_URL_WEB+"payments.php?payment="+value;
      }
      function myFn(){
        swal("helo");
}
    </script>
  </body>
</html>
