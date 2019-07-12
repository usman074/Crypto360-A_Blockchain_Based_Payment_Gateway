<?php
session_start();

$WEB_BASE_URL = "http://localhost/crypto360/";
if(isset($_SESSION["firstName"]))
{
  $firstName = $_SESSION["firstName"];
}
else{
  header('Location: '.$WEB_BASE_URL.'index.php');
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
   
  </head>

  <body>

    <nav class="navbar navbar-default">
      <div class="container-fluid">
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
          <h1><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Dashboard</h1>
        </div>
      </div>
    </header>

    <section id="breadcrumb">
      <div class="container">
        <ol class="breadcrumb">
          <li class="active">Dashboard</li>
        </ol>
      </div>
    </section>

    <section id="main">
        <div class="container">
          <div class="row">
            <div class="col-md-3">
                <div class="list-group">
                    <a href="dashboard.php?coin=BTC" class="list-group-item active main-color-bg"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> Dashboard</a>
                    <a href="payments.php?payment=all" class="list-group-item"><i class="fas fa-file-invoice-dollar"></i> Payments</a>
                    <a href="MerchantTools.php" class="list-group-item"><i class="fas fa-briefcase"></i> Merchant Tools</a>
                    <a href="settings.php?action=profile" class="list-group-item"><span class="glyphicon glyphicon-cog" aria-hidden="true" ></span>  Settings</a>
                  </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-10"></div>
                    <div class="col-md-2">
                        <div class="form-group">
                          <select class="form-control" id="sel1">
                            <option>BTC</option>
                          </select>
                        </div>
                      </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div id="container">
                          <div>
                            <canvas id="myChart" width="150" height="150" style= "background-color:transparent;"></canvas>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="container">
                          <div>
                            <canvas id="pieChart" width="200" height="200"></canvas>
                          </div>
                        </div>
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
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"> </script>
    <script src="js/script.js"> </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.js"> </script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            pieChartDataCall();
            LineGraph(this.responseText);
          }
        };
        xmlhttp.open("GET", "http://localhost/crypto360/lineGraph.php?coin=BTC", true);
        xmlhttp.send();        

       function LineGraph(response)
       {
          var amount = new Array();
          var sumAmountByDate = new Array();
          var dataset = new Array();
          var dates = new Array();
          var responseText = response;
          var today = new Date();
          var labelArray=new Array(new Date(today).toLocaleDateString());
          var obj = JSON.parse(responseText);
          if(obj.error == null)
          {
            for(var i =0; i < obj.response.length; i++)
            {
              amount.push(obj.response[i].amount);
              dates.push(new Date(obj.response[i].Date).toLocaleDateString());
            }
            var uniqueDates = getUnique(dates);
            uniqueDates.sort();
            for(var i=0; i < uniqueDates.length; i++)
            {
              var sum = 0;
              for(var j=0; j < obj.response.length; j++)
              {
                if(uniqueDates[i] == dates[j])
                {
                  sum = sum + amount[j];
                } 
              }
              sumAmountByDate.push(sum);
            }
            for(var i =0; i < 30; i++)
              {
                labelArray.push(new Date(today.setDate(today.getDate() - 1)).toLocaleDateString());
              }
              labelArray.reverse();
              for(var i = 0; i < labelArray.length; i++)
              {
                var matched = false;
                for(var j=0; j < uniqueDates.length; j++)
                {
                  if(labelArray[i] == uniqueDates[j])
                  {
                    matched=true;
                    dataset.push({x:uniqueDates[j].toString(),y: sumAmountByDate[j].toString() });
                    j = uniqueDates.length;
                  }
                }
                if(matched == false)
                {
                    dataset.push({x:labelArray[i].toString(),y: 0 });
                }
                
              }            
              var ctx = document.getElementById('myChart').getContext('2d');
              var myChart = new Chart(ctx, {
                  type: 'line',
                  data: {
                      labels: labelArray,
                      datasets: [{
                          label: 'Last 30 days Revenue',
                          data: dataset,
                          borderColor: [
                            '#034ca0'
                          ],
                          borderWidth: 3,
                          pointBorderColor: '#002754',
                          pointBorderWidth: 5,
                          pointHitRadius:5,
                          pointHoverBackgroundColor: 'white',
                          pointHoverBorderColor: '#002754',
                          pointRadius: 2,
                      }]
                  },
                  options: {
                    scales: {
                          yAxes: [{
                              ticks: {
                                  beginAtZero: true
                                  
                              }
                          }]
                      }
                  }
              });
          }
          else{
            if(obj.error == "Authentication Failed")
            {
              swal("Error", "Authentication Failed. Please Login Again.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/logout.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
            }
            else
            {
              swal("Error", obj.error, "error");
            }
            
          }
        }

        function getUnique(array){
        var uniqueArray = [];
        
        // Loop through array values
        for(var value of array){
            if(uniqueArray.indexOf(value) === -1){
                uniqueArray.push(value);
            }
        }
        return uniqueArray;
    }

    function pieChartDataCall()
    {
      var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 200) {
            //console.log(this.responseText);
            //pieChartDataCall();
            //LineGraph(this.responseText);
            pieChart(this.responseText);
          }
        };
        xmlhttp.open("GET", "http://localhost/crypto360/PieGraph.php?payment=all", true);
        xmlhttp.send(); 
    }

    function pieChart(response)
    {
      var responseText = response;
          var paid = 0;
          var unpaid = 0;
          var obj = JSON.parse(responseText);
          if(obj.error == null)
          { 
            for(var i =0; i < obj.response.length; i++)
            {
              if(obj.response[i].Unused_Addresses == "true")
              {
                unpaid = unpaid +1;
              }
              else if(obj.response[i].Unused_Addresses == "false")
              {
                paid =paid +1;
              }
            }
            data = {
                datasets: [{
                    data: [paid, unpaid],
                    backgroundColor: ["#214497","#4f6ef7"]
                }],

                // These labels appear in the legend and in the tooltips when hovering different arcs
                labels: [
                    'Paid Payments '+paid,
                    'Un Paid Paymens '+unpaid
                ]
            };
            var ctxx = document.getElementById("pieChart").getContext('2d');
            //Chart.defaults.sale.ticks.beginAtZero = true;
            var myDoughnutChart = new Chart(ctxx, {
                type: 'doughnut',
                data: data,
                options: {
                  // Here is where we enable the 'radiusBackground'
                  radiusBackground: {
                    color: '#d1d1d1' // Set your color per instance if you like
                  },
                }

            });
          }
          else
          {
            if(obj.error == "Authentication Failed")
            {
              swal("Error", "Authentication Failed. Please Login Again.", "error").then((value)=>{
                    window.location.href='http://localhost/crypto360/logout.php';
                  }).catch((error)=>{
                    swal("Error", error, "error");
                  });
            }
            else
            {
              swal("Error", obj.error, "error");
            }
          }
    }
        
        
       
        
</script>
</body>

 </html>
