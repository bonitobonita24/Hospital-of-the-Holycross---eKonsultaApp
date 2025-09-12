<?php
    $page = 'consultation';
    include('header.php');
    checkLogin();
    include('menu.php');

    $pPIN = $_GET['pPIN'];
    $pLastName= $_GET['pLastName'];
    $pFirstName = $_GET['pFirstName'];
    $pMiddleName = $_GET['pMiddleName'];
    $pSuffix = $_GET['pSuffix'];
    $pDoB = $_GET['pDateOfBirth'];
    $pDateOfBirth = date('Y-m-d',strtotime($pDoB));
    $pModule = "K"; 
?>
<script>
    $(function() {
        $( ".datepicker" ).datepicker();
    });
    $("#pDateOfBirth").mask("99/99/9999");
    $("#pDateOfBirth").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });

</script>

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
                <h3 class="panel-title">CONSULTATION MODULE</h3>
            </div>
            <form action="consultation_search.php" name="search_consultation_form" method="GET">
                <div class="panel-body">
                    <div style="text-align: right;">
                        <input
                            type="button"
                            name="view"
                            class="btn btn-info btn-sm"
                            id="view"
                            value="Go to List of Consultation Records"
                            title="Go to List of Consultation Records"
                            style="margin-top: 15px;"
                            onclick="window.location='consultation_list_of_all_patients.php'"
                        />
                    </div>

                    <table style="margin-top: 20px; " align="center">
                        <tr>
                            <td colspan="5" align="center"><u><h4>Search Client Record</h4></u></td>
                        </tr>
                        <tr>
                            <td colspan="5" align="center">
                                <table>
                                    <tr>
                                        <td><label>PhilHealth Identification No:</label></td>
                                        <td><input type="text" name="pPIN" id="pPIN" style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;" class="form-control" value="<?php echo $pPIN;?>" onkeypress="return isNumberKey(event);" maxlength="12">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="height: 10px;">
                            <td colspan="5"></td>
                        </tr>
                        <tr>
                            <td><label>Last Name</label></td>
                            <td><label>First Name</label></td>
                            <td><label>Middle Name</label></td>
                            <td><label>Extension</label></td>
                            <td><label>Date of Birth</label></td>
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
                            <td>
                                <input
                                        type="text"
                                        name="pMiddleName"
                                        id="pMiddleName"
                                        class="form-control"
                                        style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                        maxlength="15"
                                        value="<?php echo $pMiddleName; ?>"
                                        autocomplete="off"
                                />
                            </td>
                            <td>
                                <input
                                        type="text"
                                        name="pSuffix"
                                        id="pSuffix"
                                        class="form-control"
                                        style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 70px;" maxlength="3"
                                        value="<?php echo $pSuffix; ?>"
                                        autocomplete="off"
                                />
                            </td>
                            <td>
                                <input
                                        type="text"
                                        name="pDateOfBirth"
                                        id="pDateOfBirth"
                                        class="datepicker form-control"
                                        value="<?php echo $pDoB; ?>"
                                        placeholder="mm/dd/yyyy"
                                        style="width: 100px;"
                                        autocomplete="off"
                                        onkeyup="formatDate('pDateOfBirth');"
                                />
                            </td>
                        </tr>
                        <tr style="height: 20px;">
                            <td colspan="5"></td>
                        </tr>
                        <tr>
                            <td colspan="5" align="center">
                                <input
                                        type="submit"
                                        name="search"
                                        class="btn btn-success"
                                        id="search"
                                        value="Search"
                                        title="Search"
                                        onclick="return validateSearch();"
                                />
                                <input
                                        type="button"
                                        name="clear"
                                        class="btn btn-default"
                                        id="clear"
                                        value="Clear"
                                        title="Clear"
                                        onclick="window.location='consultation_search.php'"
                                />
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
                if($_GET) {
                    $displayResult = searchClientResult($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth, $pModule);

                    if (count($displayResult) == 0) {
                        echo "<div class='alert alert-danger' style='text-align: center;font-weight: bold;font-size: 12px;width:50%'>No record found.</div>";
                    } else {
                        echo "<div class='alert alert-info' style='text-align: center;font-weight: bold;font-size: 12px;width:50%'>" . count($displayResult) . " Record/s Found.</div>";
                        ?>
                        <table class="table table-hover table-bordered" style="margin-top: 20px; margin-bottom: 20px; font-size: 11px; text-align: center; width: 95%;">
                            <thead>
                            <tr>
                                <th style="width: 5%; font-size: 11px; padding: 2px; vertical-align: middle"
                                    rowspan="2">No
                                </th>
                                <th style="width: 75%; font-size: 11px; padding: 2px;" colspan="9">Client Information
                                </th>
                            </tr>
                            <tr>
                                <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Case No</th>
                                <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">PIN</th>
                                <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Last Name</th>
                                <th style="width: 12%;  font-size: 11px; padding: 2px;vertical-align: middle">First Name</th>
                                <th style="width: 12%;  font-size: 11px; padding: 2px;vertical-align: middle">Middle Name</th>
                                <th style="width: 12%;  font-size: 11px; padding: 2px;vertical-align: middle">Extension</th>
                                <th style="width: 12%;  font-size: 11px; padding: 2px;vertical-align: middle">Date of Birth</th>
                                <th style="width: 12%;  font-size: 11px; padding: 2px;vertical-align: middle">Client Type</th>
                                <th style="width: 12%;  font-size: 11px; padding: 2px;vertical-align: middle">Effectivity Year</th>
                            </tr>
                            </thead>
                            <?php
                            for ($i = 0; $i < count($displayResult); $i++) {
                                $pxCaseNo = $displayResult[$i]['CASE_NO'];
                                $pxPin = $displayResult[$i]['PX_PIN'];
                                $pxLname = $displayResult[$i]['PX_LNAME'];
                                $pxFname = $displayResult[$i]['PX_FNAME'];
                                $pxMname = $displayResult[$i]['PX_MNAME'];
                                $pxExtName = $displayResult[$i]['PX_EXTNAME'];
                                $pxDob = $displayResult[$i]['PX_DOB'];
                                $pxType = $displayResult[$i]['PX_TYPE'];
                                $pEffYear = $displayResult[$i]['EFF_YEAR'];
                                ?>
                                <tbody>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><a href="consultation_list_of_patient.php?case_no=<?php echo $pxCaseNo; ?>"
                                           title="Go to Consultation Record"
                                           style="font-size:11px;font-weight: normal;"><?php echo $pxCaseNo; ?></a></td>
                                    <td><?php echo $pxPin; ?></td>
                                    <td><?php echo $pxLname; ?></td>
                                    <td><?php echo $pxFname; ?></td>
                                    <td><?php echo $pxMname; ?></td>
                                    <td><?php echo $pxExtName; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($pxDob)); ?></td>
                                    <td><?php echo getPatientType(false, $pxType); ?></td>
                                    <td><?php echo $pEffYear; ?></td>
                                </tr>
                                </tbody>
                            <?php } ?>
                        </table>
                    <?php }
                }?>

            </div>

            <div style="float: left; text-align: left; padding-top: 10px;font-weight: bold; font-size: 11px;color: #030303;">
                Note:<br>
                <li>Only encoded 'registered clients' can be searched in the system.</li>
                <li>You may search using the PhilHealth Identification Number or a combination of the name and birthday.</li>
                <li>To view the Client Record, select 'Go to List of Consultation Records'.</li>
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

    $("#pDateOfBirth").mask("99/99/9999");
</script>