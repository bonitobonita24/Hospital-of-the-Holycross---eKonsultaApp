<?php
$page = 'facilityProfileRegistration';
include('function.php');
include('function_global.php');
if(isset($_POST['saveRegistration'])){
    $pProvCode = $_POST['pHospAddProv'];
    $getData = getRegionLhio($pProvCode);

    $_POST['pHospRegion'] = $getData['PROCODE']; //REGION CODE
    $_POST['pHospLhio'] = $getData['LHIO']; //LHIO CODE

    if(!empty($_POST['pHospRegion']) AND !empty($_POST['pHospLhio'])){
        if($_POST['pUserPassword'] != $_POST['pUserConfirmPassword']){

        }
        saveHciProfileRegistration($_POST);
    }
    else{
        echo '<script>alert("Cannot Process Registration!");</script>';
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en"><!-- savr 2015-11-11 -->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, initial-scale=1"><!-- savr 2015-11-11 -->
    <title>eKONSULTA</title>

    <link href="res/ico/favicon.png" rel="shortcut icon" type="image/x-icon" />
    <link href="res/css/normalize.css" rel="stylesheet" type="text/css" />
    <link href="res/css/omis.css" rel="stylesheet" type="text/css" />
    <link href="res/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="res/css/styles.css" rel="stylesheet" type="text/css" />
    <link href="res/css/jquery-ui-1.11.4.css" rel="stylesheet"><!--added by marv 01302018-->

    <script type="text/javascript" src="res/js/jquery.js"></script>
    <script type="text/javascript" src="res/js/jquery.min.js"></script>
    <script type="text/javascript" src="res/js/jquery-1.11.1.js"></script>
    <script type="text/javascript" src="res/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="res/js/jquery-ui-1.11.4.js"></script>
    <script type="text/javascript" src="res/js/scripts.js"></script>

    <script type="text/javascript" src="res/datatable/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="res/datatable/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript" src="res/datatable/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="res/datatable/buttons.flash.min.js"></script>
    <script type="text/javascript" src="res/datatable/jszip.min.js"></script>
    <script type="text/javascript" src="res/datatable/pdfmake.min.js"></script>
    <script type="text/javascript" src="res/datatable/vfs_fonts.js"></script>
    <script type="text/javascript" src="res/datatable/buttons.html5.min.js"></script>
    <script type="text/javascript" src="res/datatable/buttons.print.min.js"></script>
    <script type="text/javascript" src="res/datatable/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="res/js/jquery.maskedinput-1.3.min.js"></script>

<body>
<div id="content" align="center" class="col-lg-12 col-md-12" >
    <style>
        .table td,
        .table th {
            text-align: center;
        }

        legend {
            background-color: #FBFCC7;
        }
    </style>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <p class="systtl" style="">eKONSULTA</p>
                <div align="right"><a class="brand" href="index.php"><img src="res/img/ph_logo.png" alt="PhilHealth Logo"></a></div>
            </div>
        </div>
    </div>
    <div id="content_div" style="margin: 30px 0px 20px 0px;width:75%;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">FACILITY PROFILE REGISTRATION</h3>
            </div>
            <div class="panel-body">
                <form action="" name="facilityRegistrationForm" id="facilityRegistrationForm" method="POST"  onsubmit="return validateHciForm();">
                    <table border="0" name="tblFacilityRegistration" id="tblFacilityRegistration" style="width: 70%;">
                        <tr id="phic_info">
                            <td>
                                <table>
                                    <tr>
                                        <td>
                                            <label><label style="color:red;">*</label>Accreditation No:</label>
                                        </td>
                                        <td>
                                            <label><label style="color:red;">*</label>Cipher Key:</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pAccreNo"
                                                   id="pAccreNo"
                                                   class="form-control"
                                                   style="width:180px;margin: 0px 10px 0px 0px;"
                                                   autocomplete="off"
                                                   required
                                                   minlength="9"
                                                   maxlength="9"
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pHciKey"
                                                   id="pHciKey"
                                                   class="form-control"
                                                   style="width:370px;margin: 0px 10px 0px 0px;"
                                                   autocomplete="off"
                                                   minlength="1"
                                                   maxlength="25"
                                                   required
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr id="hospital_info1">
                            <td>
                                <table style="margin-top: 15px;">
                                    <tr>
                                        <td colspan="3"><b><u>HCI INFORMATION</u></b></td>
                                    </tr>

                                    <tr>
                                        <td><label><label style="color:red;">*</label>HCI Name:</label></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">
                                            <input type="text"
                                                   name="pHospName"
                                                   id="pHospName"
                                                   class="form-control"
                                                   style="width: 560px;text-transform: uppercase"
                                                   autocomplete="off"
                                                   maxlength="50"
                                                   required
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr id="other_info1">
                            <td>
                                <table style="margin-top: 15px;">
                                    <tr>
                                        <td><label>Email Address:</label></td>
                                        <td><label>Contact No:</label></td>
                                        <td><label style="color:red;">*</label><label>Sector:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pHospEmailAdd"
                                                   id="pHospEmailAdd"
                                                   class="form-control"
                                                   style="width: 150px;margin: 0px 10px 0px 0px;"
                                                   autocomplete="off"
                                                   maxlength="50"
                                                   onblur="validateEmail(this);"
                                            />
                                            <br/><span id="errmsg1" style="color:red;font-size:12px;"></span>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pHospTelNo"
                                                   id="pHospTelNo"
                                                   class="form-control"
                                                   style="width: 150px;margin: 0px 10px 0px 0px;"
                                                   minlength="8"
                                                   maxlength="18"
                                                   autocomplete="off"
                                            />
                                        </td>
                                        <td>
                                            <select class="form-control" name="pHospSector" id="pHospSector" style="width: 160px;margin: 0px 10px 0px 0px;text-transform: uppercase" autocomplete="off" required>
                                                <?php
                                                $typeSector = getTypeOfSector(true, '');?>
                                                <option value="" selected disabled></option>
                                                <?php
                                                foreach($typeSector as $key => $value) {
                                                ?>
                                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                <?php
                                                } ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr id="hospital_info2">
                            <td>
                                <table style="margin-top: 15px;">
                                    <tr>
                                        <td colspan="3"><b><u>HCI ADDRESS</u></b></td>
                                    </tr>
                                    <tr>

                                        <td><label style="color:red;">*</label><label>Province:</label></td>
                                        <td><label style="color:red;">*</label><label>Municipality:</label></td>
                                        <td><label style="color:red;">*</label><label>Barangay:</label></td>
                                        <td><label style="color:red;">*</label><label>ZIP Code:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select class="form-control" name="pHospAddProv" id="pHospAddProv" style="width: 200px;margin: 0px 10px 0px 0px;text-transform: uppercase" autocomplete="off" onchange="loadMunicipality(this.value);" required>
                                                <option value="0" selected="selected"></option>
                                                <?php
                                                $pLibProvince = listProvince();
                                                foreach($pLibProvince as $pLibProv){
                                                    $lProvinceCode = $pLibProv['PROVINCE'];
                                                    $lProvinceName = $pLibProv['PROV_NAME'];
                                                    $lProcode = $pLibProv['PROCODE'];
                                                    $lLhio = $pLibProv['LHIO'];
                                                    ?>
                                                    <option value="<?php echo $lProvinceCode; ?>"><?php echo $lProvinceName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="pHospAddMun" id="pHospAddMun" style="width: 200px;margin: 0px 10px 0px 0px;text-transform: uppercase" autocomplete="off" onchange="loadBarangay();" required>
                                                <option value=""></option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-control" name="pHospAddBrgy" id="pHospAddBrgy" style="width: 200px;margin: 0px 10px 0px 0px;text-transform: uppercase" onchange="loadZipCode();" autocomplete="off" required>
                                                <option value=""></option>
                                            </select>
                                        </td>
                                        <td>
<!--                                            <select class="form-control" name="pHospZipCode" id="pHospZIPCode" style="width: 100px;margin: 0px 10px 0px 0px;text-transform: uppercase" autocomplete="off" required>-->
<!--                                                <option value=""></option>-->
<!--                                            </select>-->
                                            <input type="text"
                                                   class="form-control"
                                                   id="pHospZIPCode"
                                                   name="pHospZIPCode"
                                                   maxlength="5"
                                                   minlength="4"
                                                   style="width: 100px;margin: 0px 10px 0px 0px;"
                                                   value=""
                                                   autocomplete="off"
                                                   required
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr id="user_info">
                            <td>
                                <table style="margin-top: 15px;">
                                    <tr>
                                        <td colspan="3"><b><u>USER ACCOUNT</u></b></td>
                                    </tr>
                                    <tr>
                                        <td><label style="color:red;">*</label><label>User ID:</label></td>
                                        <td><label style="color:red;">*</label><label>Password:</label></td>
                                        <td><label style="color:red;">*</label><label>Confirm Password:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pUserId"
                                                   id="pUserId"
                                                   class="form-control"
                                                   style="width: 170px;margin: 0px 10px 0px 0px;"
                                                   minlength="6"
                                                   maxlength="10"
                                                   autocomplete="off"
                                                   required
                                            />
                                        </td>
                                        <td>
                                            <input type="password"
                                                   name="pUserPassword"
                                                   id="pUserPassword"
                                                   class="form-control"
                                                   style="width: 170px;margin: 0px 10px 0px 0px;"
                                                   minlength="6"
                                                   maxlength="20"
                                                   autocomplete="off"
                                                   required
                                            />
                                        </td>
                                        <td>
                                            <input type="password"
                                                   name="pUserConfirmPassword"
                                                   id="pUserConfirmPassword"
                                                   class="form-control"
                                                   style="width: 170px;margin: 0px 10px 0px 0px;"
                                                   minlength="6"
                                                   maxlength="20"
                                                   autocomplete="off"
                                                   required
                                            />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label style="color:red;">*</label><label>Employee Number:</label></td>
                                        <td><label style="color:red;">*</label><label>Last Name:</label></td>
                                        <td><label style="color:red;">*</label><label>First Name:</label></td>
                                        <td><label>Middle Name:</label></td>
                                        <td><label>Extension(Jr./Sr.):</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pUserEmpID"
                                                   id="pUserEmpID"
                                                   class="form-control"
                                                   minlength="1"
                                                   maxlength="20" style="width: 170px;margin: 0px 10px 0px 0px;"
                                                   onkeypress="return isNumberKey(event);"
                                                   autocomplete="off"
                                                   required
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pUserLname"
                                                   id="pUserLname"
                                                   class="form-control"
                                                   style="width: 170px;margin: 0px 10px 0px 0px;text-transform: uppercase"
                                                   autocomplete="off"
                                                   maxlength="60"
                                                   required
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pUserFname"
                                                   id="pUserFname"
                                                   class="form-control"
                                                   style="width: 170px;margin: 0px 10px 0px 0px;text-transform: uppercase"
                                                   autocomplete="off"
                                                   maxlength="60"
                                                   required
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pUserMname"
                                                   id="pUserMname"
                                                   class="form-control"
                                                   style="width: 130px;margin: 0px 10px 0px 0px;text-transform: uppercase"
                                                   autocomplete="off"
                                                   maxlength="60"
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pUserExtName"
                                                   id="pUserExtName"
                                                   class="form-control"
                                                   style="width: 70px;margin: 0px 10px 0px 0px;text-transform: uppercase"
                                                   autocomplete="off"
                                                   maxlength="60"
                                            />
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label style="color:red;">*</label><label>Sex:</label></td>
                                        <td><label style="color:red;">*</label><label>Date of Birth:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select name="pUserSex" id="pUserSex" class="form-control" style="width: 170px; margin: 0px 10px 0px 0px;" required>
                                                <option selected="selected"></option>
                                                <?php
                                                $sexX = getSex(true, '');
                                                foreach($sexX as $key => $value) {
                                                    ?>
                                                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pUserDoB"
                                                   id="pUserDoB"
                                                   class="datepicker form-control"
                                                   style="width: 170px;margin: 0px 10px 0px 0px;"
                                                   maxlength="10"
                                                   required
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr><td>&nbsp;</td></tr>
                        <tr id="note_info">
                            <td><div align="left" style="margin: 0px 0px 10px 0px;color:red;font-size: 10px; font-family: Verdana, Geneva, sans-serif;"><i>NOTE: All fields marked with asterisk (*) are required.</i></div></td>
                        </tr>
                    </table>
                    <div style="margin-top:20px">
                        <input type="button" class="btn btn-primary" style="background:#006dcc" name="back" id="back" value="Go Back to Log in Page" onclick="window.location='index.php'">
                        <input type="submit" class="btn btn-primary" name="saveRegistration" id="saveRegistration" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<script type="text/javascript">
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $("#pUserDoB").mask("99/99/9999");
    $("#pUserDoB").datepicker({ maxDate: new Date, minDate: new Date(1900, 6, 12) });

</script>