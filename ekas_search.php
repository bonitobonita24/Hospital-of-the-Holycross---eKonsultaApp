<?php
$page = 'reports';
include('header.php');
checkLogin();
include('menu.php');

$pPIN = $_GET['pPIN'];
$pLastName= $_GET['pLastName'];
$pFirstName = $_GET['pFirstName'];
$pMiddleName = $_GET['pMiddleName'];
?>

<style>
    .table td,
    .table th {
        text-align: center;
    }

    legend {
        background-color: #FBFCC7;
    }
</style>
    
<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"></div>
        <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
    </div>
</div>

<div id="content">
    <div id="content_div" align="center" style="margin: 0px 0px 20px 0px;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">eKONSULTA Availment Slip (eKAS)</h3>
            </div>
            <form action="ekas_search.php" name="search_profile_form" method="GET">
                <div class="panel-body">
                    <table style="margin-top: 20px; " align="center" border="0">
                        <tr>
                            <td colspan="5" align="center"><u><h4>Search Client Record</h4></u></td>
                        </tr>
                        <tr>
                            <td colspan="5" align="center">
                                <table border="0">
                                    <tr>
                                        <td style="text-align: center"><b style="font-size: 11px">PhilHealth Identification No:</b></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input
                                                type="text"
                                                name="pPIN"
                                                id="pPIN"
                                                class="form-control"
                                                value="<?php echo $pPIN;?>"
                                                onkeypress="return isNumberKey(event);"
                                                maxlength="12"
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td><label>Last Name:</label></td>
                            <td><label>First Name:</label></td>
                        </tr>
                        <tr>
                            <td>
                                <input
                                    type="text"
                                    name="pLastName"
                                    id="pLastName"
                                    class="form-control"
                                    style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                    maxlength="20"
                                    value="<?php echo $pLastName; ?>"
                                    autocomplete="off"
                                />
                            </td>
                            <td>
                                <input
                                    type="text"
                                    name="pFirstName"
                                    id="pFirstName"
                                    class="form-control"
                                    style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                    maxlength="20"
                                    value="<?php echo $pFirstName; ?>"
                                    autocomplete="off"
                                />
                            </td>                       
                        </tr>
                        <tr><td colspan="5">&nbsp;</td></tr>
                        <tr>
                            <td colspan="5" align="center">
                                <input type="submit" name="search" class="btn btn-success" id="search" value="Search" title="Search" onclick="return validateSearch()">
                                <input type="button" name="clear" class="btn btn-default" id="clear" value="Clear" title="Clear" onclick="window.location='ekas_search.php'">
                            </td>
                        </tr>
                    </table>
                </div>
            </form>

            <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
                <img src="res/images/LoadingWait.gif" alt="Please Wait" />
            </div>

            <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
                <?php
                    
                    $displayResultAssessment = searchTransactionPerScreening($pPIN, $pLastName, $pFirstName);
                    $displayResultConsultation = searchTransactionPerConsultation($pPIN, $pLastName, $pFirstName);
                    $getTotalCnt = $displayResultAssessment + $displayResultConsultation;
                    if (count($getTotalCnt) == 0) {
                        echo "<div class='alert alert-danger' style='text-align: center;font-weight: bold;font-size: 12px;width:50%'>No record found.</div>";
                    } else {
                        echo "<div class='alert alert-info' style='text-align: center;font-weight: bold;font-size: 12px;width:50%'>" . count($getTotalCnt) . " Record(s) Found.</div>";
                        ?>
                        <table id="tbl_ekas" class="table table-hover table-bordered" style="margin: 20px 10px 0px 10px; text-align: center; width: 95%;">
                            <thead>
                                <tr>
                                    <th>Case No.</th>                                   
                                    <th>Trans No.</th>
                                    <th>PIN</th>
                                    <th>Name</th>
                                    <th>Date of Birth</th>
                                    <th>Patient Type</th>
                                    <th>Service Type</th>
                                    <th>Effectivity Year</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            for ($i = 0; $i < count($displayResultAssessment); $i++) {
                                $pxCaseNo = $displayResultAssessment[$i]['CASE_NO'];
                                $pxTransNo = $displayResultAssessment[$i]['TRANS_NO'];
                                $pxPin = $displayResultAssessment[$i]['PX_PIN'];
                                $pxLname = $displayResultAssessment[$i]['PX_LNAME'];
                                $pxFname = $displayResultAssessment[$i]['PX_FNAME'];
                                $pxMname = $displayResultAssessment[$i]['PX_MNAME'];
                                $pxExtName = $displayResultAssessment[$i]['PX_EXTNAME'];
                                $pxDob = $displayResultAssessment[$i]['PX_DOB'];
                                $pxType = $displayResultAssessment[$i]['PX_TYPE'];
                                $pTransDate = $displayResultAssessment[$i]['TRANS_DATE'];
                                $pEffYear = $displayResultAssessment[$i]['EFF_YEAR'];
                                
                                $pxName = $pxFname . " " . $pxMname . " " . $pxLname . " " . $pxExtName;
                                
                                ?>
                               
                                    <tr>                                       
                                        <td><?php echo $pxCaseNo; ?></td>
                                        <td><a href="print/print_ekas.php?transno=<?php echo $pxTransNo;?>" target="_blank" style="font-size: 11px;">
                                            <?php echo $pxTransNo; ?></a></td>
                                        <td><?php echo $pxPin; ?></td>
                                        <td><?php echo $pxName; ?></td>
                                        <td><?php echo date('m/d/Y', strtotime($pxDob)); ?></td>
                                        <td><?php echo getPatientType(false,$pxType); ?></td>
                                        <td><?php echo "Health Screening & Assessment"; ?></td>
                                        <td><?php echo $pEffYear; ?></td>
                                    </tr>
                                <?php
                            }?>
                            <?php
                            for ($i = 0; $i < count($displayResultConsultation); $i++) {
                                $pxCaseNo = $displayResultConsultation[$i]['CASE_NO'];
                                $pxTransNo = $displayResultConsultation[$i]['TRANS_NO'];
                                $pxPin = $displayResultConsultation[$i]['PX_PIN'];
                                $pxLname = $displayResultConsultation[$i]['PX_LNAME'];
                                $pxFname = $displayResultConsultation[$i]['PX_FNAME'];
                                $pxMname = $displayResultConsultation[$i]['PX_MNAME'];
                                $pxExtName = $displayResultConsultation[$i]['PX_EXTNAME'];
                                $pxDob = $displayResultConsultation[$i]['PX_DOB'];
                                $pxType = $displayResultConsultation[$i]['PX_TYPE'];
                                $pTransDate = $displayResultConsultation[$i]['TRANS_DATE'];
                                $pEffYear = $displayResultConsultation[$i]['EFF_YEAR'];
                                
                                $pxName = $pxFname . " " . $pxMname . " " . $pxLname . " " . $pxExtName;
                                
                                ?>
                               
                                    <tr>                                       
                                        <td><?php echo $pxCaseNo; ?></td>
                                        <td><a href="print/print_ekas.php?transno=<?php echo $pxTransNo;?>" target="_blank" style="font-size: 11px;">
                                            <?php echo $pxTransNo; ?></a></td>
                                        <td><?php echo $pxPin; ?></td>
                                        <td><?php echo $pxName; ?></td>
                                        <td><?php echo date('m/d/Y', strtotime($pxDob)); ?></td>
                                        <td><?php echo getPatientType(false,$pxType); ?></td>
                                        <td><?php echo "Consultation"; ?></td>
                                        <td><?php echo $pEffYear; ?></td>
                                    </tr>
                                <?php
                            }?>
                            </tbody>
                        </table>
                        <?php
                    }
                ?>
            </div> 

            <div style="float: left; text-align: left;font-weight: bold; font-size: 11px;color: #030303;">
                Note:<br>
                <li>Only encoded 'registered clients' can be searched in the system.</li>
                <li>You may search using the PhilHealth Identification Number or name.</li>
            </div>

        </div>
    </div>
</div>

<?php
include('footer.php');
?>
<script>
    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
    });


    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $(document).ready(function() {
    $('#tbl_ekas').dataTable({
    });
    });

    $("#pDateOfBirth").mask("99/99/9999");

</script>