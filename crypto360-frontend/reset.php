<?php
$BASE_URL="http://localhost:7270";
$WEB_BASE_URL = "http://localhost/crypto360/";
$link_validation = false;
$check_new_pass = false;
$pass_length = false;
$pass_change_success = false;
$error = false;
if(isset($_GET["mId"]) && isset($_GET["reset"]))
{
    if(isset($_GET["action"]))
    {
        $pass= $_POST["NewPass"];
        $confirm_pass = $_POST["ConfNewPass"];
        if($pass == $confirm_pass)
        {
            if(strlen($pass) >=8)
            {
                $url=$BASE_URL."/resetPass"."/".$_GET["mId"];
                $data_array = array('newpassword'=> $_POST["NewPass"],'confirmpassword'=> $_POST["ConfNewPass"]);
                
                $data_json = json_encode($data_array);
                
                $result_in_json = call("POST",$url,$data_json);
                $php_dictionary = json_decode($result_in_json, true);
                if($php_dictionary["error"] == null)
                {
                    $pass_change_success = true;
                }
                else{
                    if($php_dictionary["error"] == "New password and Confirm Password fields did not match")
                    {
                        $url=$BASE_URL.'/resetPass'."/".$_GET["mId"]."/".$_GET["reset"];
                        $result_in_json = call("GET",$url);
                        $php_dictionary = json_decode($result_in_json, true);
                        if($php_dictionary["error"] == null)
                        {
                            $check_new_pass = true;
                            $link_validation = true;
                        }
                        else
                        {
                            header('Location: '.$WEB_BASE_URL.'index.php');
                        }
                    }
                    else{
                        $url=$BASE_URL.'/resetPass'."/".$_GET["mId"]."/".$_GET["reset"];
                        $result_in_json = call("GET",$url);
                        $php_dictionary = json_decode($result_in_json, true);
                        if($php_dictionary["error"] == null)
                        {
                            $error = true;
                            $link_validation = true;
                        }
                        else
                        {
                            header('Location: '.$WEB_BASE_URL.'index.php');
                        }
                    }
                
                }
            }
            else{
                $url=$BASE_URL.'/resetPass'."/".$_GET["mId"]."/".$_GET["reset"];
                $result_in_json = call("GET",$url);
                $php_dictionary = json_decode($result_in_json, true);
                if($php_dictionary["error"] == null)
                {
                    $pass_length = true;
                    $link_validation = true;
                }
                else
                {
                    header('Location: '.$WEB_BASE_URL.'index.php');
                }
            }
        }
        else{
            $url=$BASE_URL.'/resetPass'."/".$_GET["mId"]."/".$_GET["reset"];
            $result_in_json = call("GET",$url);
            $php_dictionary = json_decode($result_in_json, true);
            if($php_dictionary["error"] == null)
            {
                $check_new_pass = true;
                $link_validation = true;
            }
            else
            {
                header('Location: '.$WEB_BASE_URL.'index.php');
            }
        }
    }
    else
    {
        $url=$BASE_URL.'/resetPass'."/".$_GET["mId"]."/".$_GET["reset"];
        $result_in_json = call("GET",$url);
        $php_dictionary = json_decode($result_in_json, true);
        if($php_dictionary["error"] == null)
        {
            $link_validation = true;
        }
        else
        {
            header('Location: '.$WEB_BASE_URL.'index.php');
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
    <title>Crypto360 - Payment Solution for Cryptocurrencies</title>
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
        if($check_new_pass == true)
        {
    ?>
            <script>
                swal("Error", "New and confirm new password didn't match", "error");</script>
    <?php
        }
        if($pass_length == true)
        {
    ?>
            <script>swal("Info", "Password must be of 8 chracters", "info")</script>
    <?php
        }
        if($pass_change_success == true)
        {
    ?>
            <script>swal("Success", "Password changed successfully", "success").then(()=>{
                    window.location.href='http://localhost/crypto360/index.php';
            }).catch(()=>{
                swal("Error", error, "error");
            })
            </script>
    <?php
        }
    ?>
    <?php
        if($link_validation == true)
        {
    ?>
            <div class="row" style="margin:0%">
                <div class="col-md-12" style="margin-top:8%">
                </div>
            </div>
            <div class="row" style="margin:0%">
                <div class="col-md-4"></div>
                <div class="col-md-4" style="background-color:white; text-align:center;">
                    <h1><b>Crypto360</b></h1><br>
                    <h3>Password Reset</h3><br>
                    <form action="reset.php?action=passUpdate&mId=<?php echo $_GET["mId"]?>&reset=<?php echo $_GET["reset"]?>" method="post">
                        <label>New Password</label>
                        <input class="form-control" name="NewPass" type="password"><br>
                        <label>Confirm New Password</label>
                        <input class="form-control" name="ConfNewPass" type="password"><br>
                        <button type="submit" class="btn btn-primary" style="background-color : #002754; border-color: #002754">Reset</button>
                    </form>
                </div>
                <div class="col-md-4"></div>
            </div>
    <?php
        }
    ?>
  </body>
</html>
