<?php
    $page = 'enlistment';
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
                <h3 class="panel-title">eKONSULTA REGISTRATION MODULE</h3>
            </div>
            <div class="panel-body">
                <form action="registration_search.php" name="search_enlistment_form" method="GET">
                    <table style="margin-top: 20px; " align="center">
                        <tr>
                            <td colspan="5" align="center"><u><h4>Search Based on Uploaded Registration Masterlist</h4></u></td>
                        </tr>
                        <tr>
                            <td colspan="5" align="center">
                                <table>
                                    <tr>
                                        <td><label>PhilHealth Identification No:</label></td>
                                        <td>
                                            <input type="text"
                                                   name="pPIN"
                                                   id="pPIN"
                                                   style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
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
                                <input type="text"
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
                                <input type="text"
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
                                <input type="text"
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
                                <input type="text"
                                       name="pSuffix"
                                       id="pSuffix"
                                       class="form-control"
                                       style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 70px;"
                                       maxlength="3"
                                       value="<?php echo $pSuffix; ?>"
                                       autocomplete="off"
                                />
                            </td>
                             <td>
                                <input type="text"
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
                                <input type="submit"
                                       name="search"
                                       class="btn btn-success"
                                       id="search"
                                       value="Search"
                                       title="Search"
                                       onclick="return validateSearch();"
                                />
                                <input type="button"
                                       name="clear"
                                       class="btn btn-default"
                                       id="clear"
                                       value="Clear"
                                       title="Clear"
                                       onclick="window.location='registration_search.php'"
                                />
                            </td>
                        </tr>
                        <tr>
                          <td colspan="5" align="center"> 
                                <input type="button"
                                       name="view"
                                       class="btn btn-info btn-sm"
                                       id="btnMasterlist"
                                       value="Go to Uploaded Registration Masterlist"
                                       title="Uploaded Registration Masterlist"
                                       onclick="window.location='assignment_masterlist.php'"
                                       style="margin-top:15px;" 
                                />   
                                <input type="button"
                                       name="view"
                                       class="btn btn-info btn-sm"
                                       id="btnReg"
                                       value="View eKonsulta Registered Clients"
                                       title="Registered Clients that encoded in the system"
                                       onclick="window.location='registered_view_list.php'"
                                       style="margin-top:15px;background-color: #2E86C1" 
                                />
                              
                                <input type="button"
                                       name="view"
                                       class="btn btn-warning btn-sm"
                                       id="btnUpdate"
                                       value="Update Encoded eKonsulta Registration"
                                       title="Update encoded eKonsulta Registration using the uploaded Konsulta Registration Masterlist"
                                       style="margin-top:15px;" 
                                       onclick="window.confirm('The uploaded Konsulta Registration Masterlist should be updated prior updating the encoded Registration in the eKonsulta System.');window.location='registration_search_reprocessing.php'"> 
                                
                          </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
                <img src="res/images/LoadingWait.gif" alt="Please Wait" />
            </div>
            
            <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
                <?php
                    $displayResult = searchBasedOnAssignmentMasterlist($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth);

                    if(count($displayResult) == 0){
                        echo "<div class='alert alert-warning' style='text-align: left;font-weight: bold;font-size: 12px; margin-left: 20px;'>No record found on the Uploaded Registration Masterlist or no Uploaded Registration Masterlist yet.</div>";
                    }
                    else{
                        echo "<div class='alert alert-info' style='text-align: center;font-weight: bold;font-size: 12px;width:50%'>".count($displayResult)." Record/s Found.</div>";         
                   
                ?>
                        <table class="table table-hover table-bordered" style="margin-top: 5px; margin-bottom: 20px; font-size: 10px; text-align: center;width: 95%">
                            <thead>
                                <tr>
                                    <th style="width: 5%; font-size: 11px; padding: 2px; vertical-align: middle" rowspan="2">No</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;" colspan="8">Client Information</th>
                                </tr>
                                <tr>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">PIN</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Last Name</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">First Name</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Middle Name</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Extension</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Client Type</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Date of Birth</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Effectivity Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                 for ($i = 0; $i < count($displayResult); $i++) {
                                    $pxPin = $displayResult[$i]['ASSIGNED_PIN'];
                                    $pxLname = $displayResult[$i]['ASSIGNED_LAST_NAME'];
                                    $pxFname = $displayResult[$i]['ASSIGNED_FIRST_NAME'];
                                    $pxMname = $displayResult[$i]['ASSIGNED_MIDDLE_NAME'];
                                    $pxExtName = $displayResult[$i]['ASSIGNED_EXT_NAME'];
                                    $pxType = $displayResult[$i]['ASSIGNED_TYPE'];
                                    $pxDob = $displayResult[$i]['ASSIGNED_DOB'];
                                    // $pEnlistCaseNo = $displayResult[$i]['CASE_NO'];
                                    $pEffYear = $displayResult[$i]['EFF_YEAR'];
                                ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><a href="registration_data_entry.php?pin=<?php echo $pxPin; ?>&effyear=<?php echo $pEffYear; ?>" style="font-size:11px;font-weight: normal;"><?php echo $pxPin;?></a></td>
                                    <td><?php echo $pxLname;?></td>
                                    <td><?php echo $pxFname;?></td>
                                    <td><?php echo $pxMname;?></td>
                                    <td><?php echo $pxExtName;?></td>                                    
                                    <td><?php echo getPatientType(false, $pxType);?></td>                                    
                                    <td><?php echo $pxDob;?></td>                                    
                                    <td><?php echo $pEffYear;?></td> 
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
            </div>

            <div style="float: left; text-align: left; padding-top: 35px;font-weight: bold; font-size: 11px;color: #030303;">
                Note:<br>
                <li>Use the Searching feature to search for each beneficiary/individual in the uploaded Konsulta Registration Masterlist.</li>
                <li>Use the <u>View eKonsulta Registered Clients</u> feature to view the encoded beneficiaries in the eKonsulta System.</li>
                <li>Use the <u>Update Encoded eKonsulta Registration</u> button to update the encoded eKonsulta Registration Masterlist using the records from the uploaded Konsulta Registration Masterlist.</li>
            </div>

        </div>
    </div>
</div>

<?php
    include('footer.php');
?>

<script>
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
    });

    $("#pDateOfBirth").mask("99/99/9999");
</script>
