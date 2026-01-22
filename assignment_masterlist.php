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

    if(isset($_POST['submit']) && !empty($_POST['submit'])) {
        $getReportAssignment = getReportMemberAssignment($_POST['pEffYear']);
    }
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
                <h3 class="panel-title">REGISTRATION MASTERLIST</h3>
            </div>
            <div class="panel-body">
                
                <form action="" method="POST">
                    <div id="" align="center" style="">
                        <b>Effectivity Year: </b>
                        <select name="pEffYear" id="pEffYear" class="form-control" style="width:220px;margin:0px 10px 0px 0px;" required>
                            <option value="">Select Effectivity Year</option>
							<option value="2026" <?php if ($_POST['pEffYear'] == "2026") { echo "selected"; } ?>>2026</option>
                            <option value="2025" <?php if ($_POST['pEffYear'] == "2025") { echo "selected"; } ?>>2025</option>
                            <option value="2024" <?php if ($_POST['pEffYear'] == "2024") { echo "selected"; } ?>>2024</option>
                            <option value="2023" <?php if ($_POST['pEffYear'] == "2023") { echo "selected"; } ?>>2023</option>
                            <option value="2022" <?php if ($_POST['pEffYear'] == "2022") { echo "selected"; } ?>>2022</option>
                            <option value="2021" <?php if ($_POST['pEffYear'] == "2021") { echo "selected"; } ?>>2021</option>
                        </select>
                        <input type="submit"
                                name="submit"
                                id="submit"
                                class="btn btn-success"
                                style="margin-left: 10px;"
                                value="Search"
                        />
                    </div>
                </form>

                <div id="wait_image" align="center" style="margin: 30px 0px;">
                    <img src="res/images/LoadingWait.gif" alt="Please Wait" />
                </div>

                <br/><br/>
                <div style="overflow-x: auto">
                    <?php 
                        if (count($getReportAssignment) > 0) {
                    ?>
                    <table class="table table-hover table-bordered table-responsive" id="listRecords" style="margin-top: 50px; margin-bottom: 20px; font-size: 11px; text-align: center; width: 98%;">
                        <thead>
                            <tr>
                                <th style="vertical-align: middle;width: 1%;">No</th>
                                <th style="vertical-align: middle;width: 10%;">Registered PIN</th>
                                <th style="vertical-align: middle;width: 10%;">Primary PIN</th>
                                <th style="vertical-align: middle;width: 10%;">Last Name</th>
                                <th style="vertical-align: middle;width: 10%;">First Name</th>
                                <th style="vertical-align: middle;width: 10%;">Middle Name</th>
                                <th style="vertical-align: middle;width: 10%;">Extension</th>
                                <th style="vertical-align: middle;width: 10%;">Sex</th>
                                <th style="vertical-align: middle;width: 10%;">Date of Birth</th>
                                <th style="vertical-align: middle;width: 5%;">Client Type</th>
                                <th style="vertical-align: middle;width: 5%;">Mobile Number</th>
                                <th style="vertical-align: middle;width: 5%;">Landline Number</th>
                                <th style="vertical-align: middle;width: 10%;">Registration Date</th>
                                <th style="vertical-align: middle;width: 5%;">Effectivity Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            for ($i = 0; $i < count($getReportAssignment); $i++) {
                                $pPrimaryPin = $getReportAssignment[$i]['PRIMARY_PIN'];
                                $pAssignedPin = $getReportAssignment[$i]['ASSIGNED_PIN'];
                                $lastName = $getReportAssignment[$i]['ASSIGNED_LAST_NAME'];
                                $firstName = $getReportAssignment[$i]['ASSIGNED_FIRST_NAME'];
                                $middleName = $getReportAssignment[$i]['ASSIGNED_MIDDLE_NAME'];
                                $extname = $getReportAssignment[$i]['ASSIGNED_EXT_NAME'];
                                $dateOfBirth= $getReportAssignment[$i]['ASSIGNED_DOB'];
                                $memType = $getReportAssignment[$i]['ASSIGNED_TYPE'];
                                $assignDate = $getReportAssignment[$i]['ASSIGNED_DATE'];
                                $effYear = $getReportAssignment[$i]['EFF_YEAR'];
                                $sex = $getReportAssignment[$i]['ASSIGNED_SEX'];
                                $contactMobileNo = $getReportAssignment[$i]['MOBILE_NUMBER'];
                                $contactLandlineNo = $getReportAssignment[$i]['LANDLINE_NUMBER'];
                                $effYear = $getReportAssignment[$i]['EFF_YEAR'];

                                if ($memType == "MM") {
                                    $memTypeDesc = "MEMBER";
                                } else {
                                    $memTypeDesc = "DEPENDENT";
                                }
                            ?>
                        <tr>
                            <td><?php echo $i+1; ?></td>
                            <td><a href="registration_data_entry.php?pin=<?php echo $pAssignedPin; ?>&effyear=<?php echo $effYear; ?>" style="font-size:11px;font-weight: normal;"><?php echo $pAssignedPin; ?></a></td>
                            <td><?php echo $pPrimaryPin; ?></td>
                            <td><?php echo strReplaceEnye($lastName); ?></td> 
                            <td><?php echo strReplaceEnye($firstName); ?></td>
                            <td><?php echo strReplaceEnye($middleName); ?></td>
                            <td><?php echo $extname; ?></td>
                            <td><?php echo $sex; ?></td>
                            <td><?php echo date('m/d/Y', strtotime($dateOfBirth)); ?></td>
                            <td><?php echo $memTypeDesc; ?></td>
                            <td><?php echo $contactMobileNo; ?></td>
                            <td><?php echo $contactLandlineNo; ?></td>
                            <td><?php echo date('m/d/Y', strtotime($assignDate))?></td>
                            <td><?php echo $effYear; ?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>
            </div>

        </div>
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

    $(window).load(function() {
        $("#wait_image").fadeOut("slow");
    });
</script>