<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="shortcut icon" href="https://2tjosk2rxzc21medji3nfn1g-wpengine.netdna-ssl.com/wp-content/themes/konghq/assets/img/favicon.ico" type="image/x-icon">
        <link rel="icon" href="https://2tjosk2rxzc21medji3nfn1g-wpengine.netdna-ssl.com/wp-content/themes/konghq/assets/img/favicon.ico" type="image/x-icon">
        <title>KongMap - API Gateway Visualizer</title>
        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <!-- Custom styles for this template -->
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
      <script type="text/javascript" src="https://unpkg.com/vis-network@8.5.6/standalone/umd/vis-network.min.js"></script>



    <![endif]-->
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>

         <style css>.navbar-custom {
    background-color: #dddddd;
                box-shadow: 0px 1px 5px #888888;
}

.demo {
    //: absolute;
    //: 0;
}

            #mynetwork .vis-network:focus{
                outline: none;
            }

.demorow {
    margin-bottom: 25px;
    //: 200px;
}
            .mapdetails{
                background-color: #f1f1f1;
            }
      
          @media screen and (min-width:1024px) {

}
            
      
      </style>
    </head>
    <body>
    <script type="text/javascript" src="https://unpkg.com/vis-network@8.5.6/standalone/umd/vis-network.min.js"></script>
    <nav class="navbar navbar-light navbar-custom " style="">
            <a class="navbar-brand" href="<?php echo $app_url?>">

                <span class="navbar-text" style=""></span>
            </a>


        <?php echo $navbar; ?>

            <span class="navbar-text" style="position: absolute; right: 10px;">
                <a href="https://github.com/yesinteractive/kong-map/blob/main/CHANGELOG.md" target="_blank">KongMap ver <?php echo option('kongmap_ver'); ?> </a></span>


        </nav>         
        <!-- Main jumbotron for a primary marketing message or call to action -->


        <div class="" style="    margin: 0px;width: 100%;padding: 25px;white-space: normal;">


            <?php  echo $content; ?>

            <footer class="footer mt-auto py-3">
                <hr>
                    Visit <a href="https://github.com/yesinteractive/kong-map" target="_blank">Project on GitHub</a> for documentation and to submit questions and issues.
                    <BR>
                    The Kong trademark, product name and logo are property of <a href="https://konghq.com" target="_blank">Kong Inc.</a>

            </footer>
        </div>         
        <!-- /container -->
        <!-- Bootstrap core JavaScript
    ================================================== -->
        <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js"></script>

    </body>
</html>
