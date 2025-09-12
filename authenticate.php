<?php
    require("function.php");
    if (isset($_POST['pUserID']) && isset($_POST['pUserPassword'])) {
        $pUserID = trim($_POST['pUserID']);
        $pUserPassword = trim($_POST['pUserPassword']);

        $authenticated = authenticateUser($pUserID,$pUserPassword);
        if ($authenticated) {
            header('Location: home.php');
        }
    }
    else{
        echo '<script type="text/javascript">alert("Fill up all the fields to login.");window.location="index.php"</script>';
    }
?>
