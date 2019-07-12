<?php
session_start();
$BASE_URL="http://localhost:7270";
$WEB_BASE_URL = "http://localhost/crypto360/";
$data_fetched = false;
$data="";
if(isset($_SESSION["firstName"]))
{
  $firstName = $_SESSION["firstName"];
  if($_GET['payment'] == "all")
  {
    $url=$BASE_URL."/payments/all";
    $result_in_json = call("GET",$url);
    $data = $result_in_json;
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
echo $data;
?>