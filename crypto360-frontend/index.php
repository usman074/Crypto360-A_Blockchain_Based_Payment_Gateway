<?php
session_start();
$BASE_URL="http://localhost:7270";
$WEB_BASE_URL = "http://localhost/crypto360/";
$error= false; //to chek if api sends an error
$unregister_email = false; //to check user is a member of site or not
$incorrct_email_pass = false; //to check email is register with system or not
$email_sent_success = false;
$email_sent_fail = false;
$account_verifed = false;
$account_already_verified = false;
$forget_email_send_successfully = false;
$forget_email_send_failed = false;
$resendLink = false;
$resendLinkSent = false;
// ==> Normally you have to send a jSON string to the jSON URL and you will get a jSON string back
// ==> In the following program we feed this function a JSON string in form of $data_json_string and it outputs a JSON string in form of $result

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
//JSON Decode to transform the jSON string to PHP array
//JSON Encode to create a json string from your PHP array

//We will now deocde the JSON string which is  $result to a php array

if(isset($_POST['submit']))
{
	$url=$BASE_URL."/login";
	$data_array = array('email'=> $_POST['email'], 'password'=> $_POST['psw']);
	
	$data_json = json_encode($data_array);
	
	$result_in_json = call("POST",$url,$data_json);
	$php_dictionary = json_decode($result_in_json, true);
	if($php_dictionary["error"] == null)
	{
		
		$php_dictionary = json_decode($php_dictionary["response"], true); 
		$_SESSION["firstName"]= $php_dictionary["firstName"];
		$_SESSION["x-acess-token"] = $php_dictionary["token"];
		header('Location: '.$WEB_BASE_URL.'dashboard.php');
	}
	else{
		if($php_dictionary["resendLink"])
		{
			header('Location: '.$WEB_BASE_URL.'error.php?error='.$php_dictionary["error"].'&link='.$php_dictionary["resendLink"]);
		}
		else if($php_dictionary["error"]=="Email doesn't register with our system"){
			$unregister_email = true;
		}
		else if ($php_dictionary["error"]=="Email or Password is incorrect")
		{
			$incorrct_email_pass = true;
		}
		else{
			$error = true;
		}

	}
	
}

if(isset($_POST['emailSubmit']))
{
	$url=$BASE_URL."/contactUs";
	$data_array = array('email'=> $_POST['email'], 'subject'=> $_POST['subject'], 'message'=> $_POST['message']);
	
	$data_json = json_encode($data_array);
	
	$result_in_json = call("POST",$url,$data_json);
	$php_dictionary = json_decode($result_in_json, true);
	if($php_dictionary["error"] == null)
	{
		$email_sent_success = true;
	}
	else{
		$email_sent_fail = true;
	}
	
}

if(isset($_POST['forgetPass']))
{
	$url=$BASE_URL."/forgetPass";
	$data_array = array('email'=> $_POST['email']);
	
	$data_json = json_encode($data_array);
	
	$result_in_json = call("POST",$url,$data_json);
	$php_dictionary = json_decode($result_in_json, true);
	if($php_dictionary["error"] == null)
	{
		$forget_email_send_successfully = true;
	}
	else{
		$forget_email_send_failed = true;
	}
	
}

if(isset($_GET['account']))
{
	if($_GET['account'] == "verified")
	{
		$account_verifed = true;
	}
}

if(isset($_GET['resendEmail']))
{
	if($_GET['resendEmail'] == "sent")
	{
		$resendLinkSent = true;
	}
}

if(isset($_GET['link']))
{
	if($_GET['link'] == "expired")
	{
		$account_already_verified = true;
	}
}




?>




<!DOCTYPE html>
<html lang="en">
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
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>

<body data-spy="scroll" data-target="#navbarResponsive">

    <?php
  	  if($error == true)
      {
        ?>
              <script>swal("Error", "Unexpected Error", "error");</script>
        <?php      
			}
			if($unregister_email == true)
			{
		?>
			 <script>
                  swal("Error", "Email is not register with our system.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($incorrct_email_pass == true)
			{
		?>
				<script>
                  swal("Error", "Email or Password is incorrect.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($email_sent_success == true)
			{
		?>
				
				<script>
                  swal("Success", "Email Sent Successfully", "success").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($email_sent_fail == true)
			{
		?>
				
				<script>
                  swal("Error", "Error Sending Email", "erorr").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($account_verifed == true)
			{
		?>
				<script>
                  swal("Success", "Account Verified Successfully", "success").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($account_already_verified == true)
			{
		?>
				<script>
                  swal("Error", "Link is expired or Already used for verification.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($forget_email_send_successfully == true)
			{
		?>
				<script>
                  swal("Success", "Password reset link sent successfully. please check your email.", "success").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
			if($forget_email_send_failed == true)
			{
		?>
				<script>
                  swal("Error", "Error Sending Email.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
                </script>
		<?php
			}
		if($resendLinkSent == true)
		{
	?>
			<script>
								swal("Success", "Email for Account verification sent succesfully", "success").then((value)=>{
								window.location.href='http://localhost/crypto360/index.php';
							}).catch((error)=>{
								swal("Error", error, "error");
							});
			</script>
	<?php
			}
		?>
	<!--Start Home Section-->
	<div id="home">

		<!--Navigation-->
		<nav class="navbar navbar-expand-md navbar-dark fixed-top ">
			<a class="navbar-brand" href="#"><h3>Crypto360</h3></a>
			<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarResponsive">
				<ul class="navbar-nav ml-auto">
					<li class="nav-item">
						<a class="nav-link" href="#home">Home</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#merchant">Merchant Tools</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#coins">Supported Coins</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#features">Features</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#contact">Contact Us</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="#" data-toggle="modal" data-target="#id01">Login</a>
						<div class="modal fade" id="id01" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Login</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
									<form action="index.php" method="post">
									<label><b>Email</b></label><br>
											<input id="email" type="email" placeholder="Email" name="email" required><br>
										
											<label><b>Password</b></label><br>
											<input id="psw" type="password" placeholder="Password" name="psw" required><br>
											<button	type="submit" name="submit">Login</button><br>
											<span class="psw"> <a href="#" data-toggle="modal" data-target="#id02" data-dismiss="modal" style="color:black; width:auto;">Forgot password?</a></span>
									</form>
												
									</div>
								</div>
							</div>
						</div>

						<div class="modal fade" id="id02" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Password Reset</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<form action="index.php" method="post">
											<label><b>Email</b></label><br>
											<input type="email" placeholder="Email" name="email" required><br>
											<button type="submit" name="forgetPass">Reset</button><br>
										</form>
									</div>
								</div>
							</div>
						</div>
					</li>
					<li class="nav-item">

					<a class="nav-link" href="#" data-toggle="modal" data-target="#id03">Sign Up</a>
						<div class="modal fade" id="id03" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Sign Up</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
											<label><b>First Name</b></label><br>
											<input id="fname" type="text" placeholder="First Name" name="fname" required><br>
											<label><b>Last Name</b></label><br>
											<input id ="lname" type="text" placeholder="Last Name" name="lname" required><br>
											<label><b>Organization Name</b></label><br>
											<input id="orgName" type="text" placeholder="Organization Name" name="org_name" required><br>
											<label><b>Email</b></label><br>
											<input id ="signEmail" type="email" placeholder="Email" name="email" required><br>
											<label><b>Password</b></label><br>
											<input id ="signPass" type="password" placeholder="Password" name="psw" required><br>
											<button onclick=user_sign_up()>Register</button><br>
									</div>
								</div>
							</div>
						</div> 
					</li>
				</ul>

			</div>
		</nav>
		<!--End Navigation-->

		<!--Start Landing Page Section-->
		<div class="landing">
			<div class="home-wrap">
				<div class="home-inner">

				</div>
			</div>
		</div>

		<div class="caption text-center">
			<h1>Welcome to Crypto360</h1>
			<h3>We provide solutions to your payments with Cryptocurrency</h3>
			<a class="btn btn-outline-light btn-lg" href="#" style="width:auto;" data-toggle="modal" data-target="#id03">Get Started</a>
		</div>
		<!--End Landing Page Section-->

	</div>
	<!--End Home Section-->

	<!--Start Merchant Tools Section-->
	<div id="merchant" class="offset" >
		<div class="row" style="background-color:white;">
			<div class="col-md-4 nopadding"></div>
			<div class="col-md-4 nopadding" >
				<h3 class="merchant-tools">Merchant Tools</h3>
			</div>
			<div class="col-md-4 nopadding"></div>
		</div>
		<div class="row" style="background-color:white;">
			<div class="col-md-2 nopadding"></div>
			<div class="col-md-4 nopadding">
				<div class="row">
					<div class="col-md-12 nopadding">
						<img class="mx-auto d-block" src="images/cart.png" style="width:100px; height:100px">
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 nopadding">
						<p style="text-align: center">Crypto360 Plugin</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 nopadding">
						<p style="text-align: center">Crypto360 plugin is a woocommerce plugin which can be easily integrated with woocommerce to accept cryptocurrency payments.</p>
					</div>
				</div>
			</div>
			<div class="col-md-4 nopadding">
				<div class="row">
					<div class="col-md-12 nopadding">
						<img class="mx-auto d-block" src="images/api.png" style="width:100px; height:100px">
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 nopadding">
						<p style="text-align: center">Crypto360 Restful API</p>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12 nopadding">
						<p style="text-align: center">Crypto360 Api is a Restful Api which can be easily integrated in your site to accept cryptocurrency payments.<br> See our <a href="#">docs</a></p>
					</div>
				</div>
			</div>
			<div class="col-md-2 nopadding"></div>
		</div>
	</div>
	<!--End Merchant Tools Section-->
	<!--Start Supported Coin Section-->
	<div id="coins" class="offset" style="background-color:white;">
		<div class="row">
			<div class="col-md-4 nopadding" ></div>
			<div class="col-md-4 nopadding" >
				<h3 class="merchant-tools">Supported Coins</h3>
			</div>
			<div class="col-md-4 nopadding"></div>
		</div>
		<div class="narrow">
				

				<div class="row text-center">
					<div class="col-md-4"></div>
					<div class="col-md-4">
							<div class="feature">
									<i class="fab fa-bitcoin fa-4x" data-fa-transform="shrink-3 up-5"></i>
									<h3>Bitcoin</h3>
									<p>We are currently accepting bitcoin only.<br> But we are looking forward to work with Ripple and Ethereum.</p>
							</div>
					</div>
					
					<div class="col-md-4"></div>

				</div>

			</div>

	</div>
	<!--End Supported Coin Section-->


	<!--Start Features Section-->
	<div id="features" class="offset">
		<!--Start Jumotron-->
		<div class="jumbotron">
			<div class="narrow">
				<div class="col-12 text-center">
					<h3 class="heading">Features</h3>
					<div class="heading-underline"></div>
				</div>

				<div class="row text-center">
					<div class="col-md-2"></div>
					<div class="col-md-4">
							<div class="feature">
									<i class="fab fa-bitcoin fa-4x" data-fa-transform="shrink-3 up-5"></i>
									<h3>Decentralized Settlement</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
							</div>
					</div>
					
					<div class="col-md-4">
							<div class="feature">
									<i class="fas fa-globe fa-4x" data-fa-transform="shrink-3 up-5"></i>
									<h3>Cross Border Payments</h3>
									<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
							</div>
					</div>
					<div class="col-md-2"></div>

				</div>

			</div>

		</div>
		<!--End Jumbotron-->
		
	</div>
	<!--End Features Section-->

	

	<!--Start Contact Us Section-->
	<div id="contact" class="offset">
		<div class="fixed-background">
			<div class="row dark text-center" style="padding-top: 4rem;">
				<div class="col-12"> 
					<h3 class="heading">Contact Us</h3>
				</div>
			</div>
			<div class="row dark text-center">
				<div class="col-md-3"></div>
				<div class="col-md-6">
					<div class="contact-form">
						<form id="contact-form" method="post" action="index.php">
							<label>Your Email</label>
							<input name="email" type="email" class="form-control" required><br>
							<label>Subject</label>
							<input name="subject" type="text" class="form-control" required><br>
							<label>Your Message</label>
							<textarea name="message" rows="5" cols="40"  class="form-control" required></textarea><br>
							<input type="submit" name="emailSubmit" class="form-control submit" value="SEND MESSAGE">
						</form>
					</div>
				</div>
				<div class="col-md-3"></div>
			</div>
			<div class="fixed-wrap">
				<div class="fixed">
		
				</div>
			</div>

		</div>
	</div>
	<!--End Contact Us Section-->
</div>

<!--- Script Source Files -->

<script src="https://use.fontawesome.com/releases/v5.5.0/js/all.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<!--- End of Script Source Files -->
<script>

//sign up
var BASE_URL = "http://localhost:7270/";
var BASE_URL_WEB = "http://localhost/crypto360/";
function user_sign_up() {
	var fname = document.getElementById('fname').value;
	var lname = document.getElementById('lname').value;
	var orgName = document.getElementById('orgName').value;
	var email = document.getElementById('signEmail').value;
	var pass = document.getElementById('signPass').value;
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
			signUpResponse(this.responseText);
    }
  };
  xhttp.open("POST", BASE_URL+"signup/", true);
  xhttp.setRequestHeader('Content-Type', 'application/json');
xhttp.send(JSON.stringify({'firstname':fname,'lastname':lname,'email':email, 'password':pass,'organizationName':orgName}));
}

function signUpResponse(response){
	var responseText = response;
	var obj = JSON.parse(responseText);
	if(obj.error == null)
	{
		swal("Success", obj.response, "success").then((value)=>{
                    window.location.href='http://localhost/crypto360/index.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
	}
	else{
		swal("Error", obj.error, "error").then((value)=>{
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
	}
}




</script>

</body>
</html>
