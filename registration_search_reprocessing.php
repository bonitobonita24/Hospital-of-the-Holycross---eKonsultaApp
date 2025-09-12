<?php
    $page = 'enlistment';
    include('header.php');
    checkLogin();
    include('menu.php');

?>

<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"></div>
        <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
    </div>
</div>

<div id="content">

    <style>
        .table td,
        .table th {
            text-align: center;
        }

        legend {
            background-color: #FBFCC7;
        }
    </style>

    <div id="content_div" align="center" style="margin: 0px 0px 20px 0px;">        
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">eKONSULTA REGISTRATION</h3>
            </div>
            <div class="panel-body">
                <?php
                    $result = updateEnlistUsingAssignTbl();

                    if ($result > 0) {
                        echo '<div class="alert alert-info"> Successfully updated the '.$result.' encoded record/s in the eKonsulta Registration.</div>';
                    } else {
                         echo '<div class="alert alert-danger" style="text-align: left;">0 record has been updated. <br/><br/>Please be advised that the uploaded Konsulta Registration Masterlist should be updated to correct the membership record encoded in your eKonsulta Registration.</div>';
                    }
                ?>

                <p></p>
            </div>

        </div>
    </div>

</div>

<?php
    include('footer.php');
?>

