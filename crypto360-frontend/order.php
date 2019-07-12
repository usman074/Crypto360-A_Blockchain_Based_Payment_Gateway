<?php
session_start();
$BASE_URL="http://localhost:7271";
$WEB_BASE_URL = "http://localhost/crypto360/";
$data_fetched = false;
if(isset($_GET['merchant']) && isset($_GET['api_key']) && isset($_GET['currency']) && isset($_GET['success_url']) && isset($_GET['cancel_url']) && isset($_GET['invoice']) && isset($_GET['email']) && isset($_GET['amountf']) && isset($_GET['taxf']) && isset($_GET['shippingf']))
{
	$total_amount = $_GET['amountf'] + $_GET['taxf'] + $_GET['shippingf'];
	$url=$BASE_URL."/address";
	$data_array = array('merchantId'=> $_GET['merchant'], 'invoiceId'=> $_GET['invoice'], 'amount'=> $total_amount, 'currency'=> $_GET['currency'], 'api_key'=> $_GET['api_key'], 'user_email'=> $_GET['email'], 'callback_url'=> $_GET['success_url']);
	$data_json = json_encode($data_array);
	$result_in_json = call("POST",$url,$data_json);
	$php_dictionary = json_decode($result_in_json, true);
	if($php_dictionary["error"] == null)
    {
	  $btc_address = $php_dictionary["address"];
	  $btc_amount = $php_dictionary["amount"];
	  $payment_id = $php_dictionary["paymentId"];
    }
}
else{
	header('Location: '.$WEB_BASE_URL.'error.php?error=Invalid Request');
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
    curl_setopt($curl,CURLOPT_HTTPHEADER,["Content-Type:application/json"]);
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
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Crypto360 - Payment Solution for Cryptocurrencies</title>
		<!-- <link rel="stylesheet" href="bootstrap-4.1.3-dist/css/bootstrap.min.css"> -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
		<link rel="stylesheet" href="style.css">
		<link rel="stylesheet" href="css/fixed.css">
		<script src="js/script.js"> </script>
	</head>
	<body>
		<div class="row">
			<div class="col-md-4">
				<h2 style="color:white;">Crypto360</h2>
			</div>
			<div class="col-md-8">
			</div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6" style="background-color:white;">
				<?php
					if(isset($btc_amount) && isset($btc_address))
					{
				?>
						<img src="https://chart.googleapis.com/chart?chs=221x221&cht=qr&chl=bitcoin%3A<?php echo $btc_address ?>?amount=<?php echo $btc_amount ?>&choe=UTF-8" title="Scan to Pay" style="display: block; margin-left: auto; margin-right: auto;"/>
				<?php
					}
				?>
				
			</div>
			<div class="col-md-3"></div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-2" style="background-color:white;">
				<p>Amount Remaining</p>
				<?php
					if(isset($btc_amount))
					{
				?>
						<p><a href="bitcoin:<?php echo $btc_address?>?amount=<?php echo $btc_amount?>">
				<?php
						echo $btc_amount
				?>
						</p></a>
				<?php
					}
				?>
			</div>
			<div class="col-md-4" style="background-color:white;">
				<p>Address</p>
				<?php
					if(isset($btc_address))
					{
				?>
						<p>
				<?php
						echo $btc_address
				?>
						</p>
				<?php
					}
				?>
			</div>
			<div class="col-md-3"></div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6" style="background-color:white;">
				<p>Make sure to send enough to cover any coin transaction fees!</p>
			</div>
			<div class="col-md-3"></div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6" style="background-color:white;">
				<p>Payment ID: 
					<?php
						if(isset($payment_id))
						{
							echo $payment_id;
						}
					?>
				</p>
			</div>
			<div class="col-md-3"></div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6" style="background-color:white;">
				<p>What to do next?</p>
			</div>
			<div class="col-md-3"></div>
		</div>
		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-6" style="background-color:white;">
				<p>
				1)  You will need to initiate the payment using your software or online wallet and copy/paste the address and payment amount into it. We will email you when all funds have been received.<br>

				2) Once the payment is confirmed several times in the block chain, the payment will be completed and the merchant will be notified. The confirmation process usually takes 10-45 minutes but varies based on the coin's target block time and number of block confirms required.
				</p>
			</div>
			<div class="col-md-3"></div>
		</div>
	
	</body>
</html>
