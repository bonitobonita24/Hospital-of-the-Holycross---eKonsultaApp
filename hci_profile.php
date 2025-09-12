<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 7/9/2018
 * Time: 1:47 PM
 */
    $page = 'account';
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

    <div class="row" align="center">
        <div class="col-xs-12 col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">PROFILE</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <?php
                        $pAccreNo = $_SESSION['pAccreNum'];
                        $pUserId = $_SESSION['pUserID'];
                        $getUserProfile = getHciProfileInfo($pAccreNo,$pUserId);
                        ?>

                        <table class="table table-hover table-bordered table-condensed">
                            <col width="15%">
                            <col width="85%">
                            <thead>
                                <tr>
                                    <th colspan="2" class="alert alert-success" style="font-style: normal;text-align: left;">User Profile</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">USER ID:</td>
                                    <td style="text-align: left;"><?php echo $getUserProfile['USER_ID'];?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">NAME:</td>
                                    <td style="text-align: left;"><?php echo $getUserProfile['USER_LNAME']." ".$getUserProfile['USER_EXTNAME'].", ".$getUserProfile['USER_FNAME']." ".$getUserProfile['USER_MNAME'];?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">SEX:</td>
                                    <td style="text-align: left;"><?php echo getSex(false, $getUserProfile['USER_SEX']);?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">DATE OF BIRTH:</td>
                                    <td style="text-align: left;"><?php echo date("m/d/Y", strtotime($getUserProfile['USER_DOB']));?></td>
                                </tr>
                            </tbody>
                        </table>
                        <table class="table table-hover table-bordered table-condensed">
                            <col width="15%">
                            <col width="85%">
                            <thead>
                                <tr>
                                    <th colspan="2" class="alert alert-success" style="font-style: normal;text-align: left;">HCI Profile</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">ACCREDITATION NO:</td>
                                    <td style="text-align: left;"><?php echo $getUserProfile['ACCRE_NO'];?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">HOSPITAL NAME:</td>
                                    <td style="text-align: left;"><?php echo utf8_decode($getUserProfile['HOSP_NAME']);?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">SECTOR:</td>
                                    <td style="text-align: left;"><?php echo getTypeOfSector(false, $getUserProfile['SECTOR']);?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;font-weight: bold;">CIPHER KEY:</td>
                                    <td style="text-align: left;font-family: Georgia, Times, serif"><?php echo $getUserProfile['CIPHER_KEY'];?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="result" style="margin: 30px 0px 30px 0px; display: none;" align="center">

    </div>

    <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
    </div>

</div>

<?php
include('footer.php');
?>
