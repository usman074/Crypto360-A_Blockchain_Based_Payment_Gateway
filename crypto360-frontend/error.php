<html>
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
    </body>

        
        <div class="row" style="margin:0%">
                <div class="col-md-12" style="margin-top:8%">
                </div>
            </div>
            <div class="row" style="margin:0%">
                <div class="col-md-2"></div>
                <div class="col-md-8" style="background-color:white; text-align:center;border: 1px solid black;">
                    <h3>
                        <?php 
                            echo $_GET['error'];
                            if(isset($_GET['link']))
                            {
                        ?>
                                or click on the given link to receive email again.<br><a href = "<?php echo $_GET['link'] ?>"><?php echo $_GET['link'] ?></a>
                        <?php
                            }
                        ?>
                        
                    </h3>
                </div>
                <div class="col-md-2"></div>
            </div>
    </body>
</html>