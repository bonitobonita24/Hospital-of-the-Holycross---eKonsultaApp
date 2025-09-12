<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 01/28/2020
 * Time: 10:05 AM
 */
?>

<?php
$page = 'reports';
include('header.php');
checkLogin();
include('menu.php');


?>

<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"><b>ASSIGNMENT MASTERLIST</b></div>
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
                <h3 class="panel-title">ASSIGNMENT MASTERLIST</h3>
            </div>
            <div class="panel-body">
                <div>
                    <?php
                    $getReportAssignment = getReportMemberAssignment();

                    if(count($getReportAssignment) == 0){
                        echo "<b>No uploaded Member Assignment yet.</b>";
                    }
                    else{
                        ?>
						<div style="overflow-x: auto">
                        <table class="table table-hover table-bordered table-responsive" id="listRecords" style="margin-top: 20px; margin-bottom: 20px; font-size: 11px; text-align: center; width: 98%;">
                            <thead>
                            <tr>
                                <th style="vertical-align: middle;width: 1%;">No</th>
                                <th style="vertical-align: middle;width: 10%;">PRIMARY PIN</th>
                                <th style="vertical-align: middle;width: 10%;">PIN</th>
                                <th style="vertical-align: middle;width: 10%;">Last Name</th>
                                <th style="vertical-align: middle;width: 10%;">First Name</th>
                                <th style="vertical-align: middle;width: 10%;">Middle Name</th>
                                <th style="vertical-align: middle;width: 10%;">Suffix</th>
                                <th style="vertical-align: middle;width: 10%;">Sex</th>
                                <th style="vertical-align: middle;width: 10%;">Date of Birth</th>
                                <th style="vertical-align: middle;width: 5%;">Membership Type</th>
                                <th style="vertical-align: middle;width: 5%;">Contact Number</th>
                                <th style="vertical-align: middle;width: 10%;">Assigned Date</th>
                                <th style="vertical-align: middle;width: 5%;">Effectivity Year</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            for ($i = 0; $i < count($getReportAssignment); $i++) {
                                $pPrimaryPin = $getReportAssignment[$i]['PRIMARY_PIN'];
                                $pPin = $getReportAssignment[$i]['PIN'];
                                $lastName = $getReportAssignment[$i]['LAST_NAME'];
                                $firstName = $getReportAssignment[$i]['FIRST_NAME'];
                                $middleName = $getReportAssignment[$i]['MIDDLE_NAME'];
                                $extname = $getReportAssignment[$i]['EXT_NAME'];
                                $dateOfBirth= $getReportAssignment[$i]['DOB'];
                                $memType = $getReportAssignment[$i]['MEM_TYPE'];
                                $assignDate = $getReportAssignment[$i]['ASSIGN_DATE'];
                                $effYear = $getReportAssignment[$i]['EFF_YEAR'];
                                $sex = $getReportAssignment[$i]['SEX'];
                                $contactNo = $getReportAssignment[$i]['CONTACT_NUMBER'];
                            ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo $pPrimaryPin; ?></td>
                                    <td><?php echo $pPin; ?></td>
                                    <td><?php echo $lastName; ?></td>
                                    <td><?php echo $firstName; ?></td>
                                    <td><?php echo $middleName; ?></td>
                                    <td><?php echo $extname; ?></td>
                                    <td><?php echo $sex; ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($dateOfBirth)); ?></td>
                                    <td><?php echo $memType; ?></td>
                                    <td><?php echo $contactNo; ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($assignDate))?></td>
                                    <td><?php echo $effYear; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
						</div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>

    <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
    </div>

</div>

<?php
include('footer.php');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#listRecords').dataTable({
        });
    });
</script>