<body style="background-image:url(res/images/ekos_bg.png); background-repeat:no-repeat;">
<?php
    $page = 'login';
    include('header.php');
?>

<div id="content" align="center" style="margin-top: 40px;">

    <br />
    <div class="row">
        <div class="col-xs-12 col-md-offset-4 col-md-4">
            <img src="res/images/ekos_home2.png" class="featurette-image img-responsive" alt="">
        </div>
    </div>

	<div id="content_div" align="center" style="margin: 0px;">

        <form action="authenticate.php" name="loginForm" id="loginForm" role="form" class="form-signin" method="post" accept-charset="utf-8">
            <input type="text" name="pUserID" id="pUserID" maxlength="20" style="color: #000; margin: 10px 10px 0px 0px;" autocomplete="on" placeholder="USER ID" class="form-control">

            <input type="password" name="pUserPassword" id="pUserPassword" style="color: #000; margin: 5px 10px 0px 0px;" autocomplete="off" placeholder="PASSWORD" class="form-control">

            <input type="submit" name="login" value="Login" style="margin: 20px 10px 0px 0px;" class="btn btn-warning btn-block" title="Login">
            <ul class="nav" style="margin-top:5px;font-weight: bold;">
                <li><a href="hci_registration.php"><u>Create an Account</u></a></li>
            </ul>

        </form>

   	</div>

</div>

<?php
    include('footer.php');
?>
