<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en"><!-- savr 2015-11-11 -->
<?php
    $page = 'enlistment';
    include('header.php');
    checkLogin();
    include('menu.php');

    $pUserId = $_SESSION['pUserID'];
    $pHciNo = "0";
    $pAccreNo = $_SESSION['pAccreNum'];
    $pPmccNo = "0";
    $pHospName = $_SESSION['pHospName'];

    if(isset($_POST['submitEnlistClient'])){
        $_POST['pUpdCntEnlist'] = 0;
              
       
        if(isset($_POST['pWithConsent']) == 'Y'){
            $_POST['pWithConsentValue']='Y';
        } else{
            $_POST['pWithConsentValue']='N';
        }

        $age = getAge($_POST['pPatientDateOfBirth']);
        $yearAge = $age['y'];
        $pxAge =  $data['pPxAge'];
        
        savePatientRegistration($_POST);       
    }

    if(isset($_POST['updateEnlistClient'])){
       
        if(isset($_POST['pWithConsent']) == 'Y'){
            $_POST['pWithConsentValue']='Y';
        }
        else{
            $_POST['pWithConsentValue']='N';
        }

       
        /*Get Updated Count of Enlistment/Registration*/
        $getUpdCntEnlist = getUpdCntRegistration($_POST['pCaseNum']);
        $getUpdCnt = $getUpdCntEnlist['UPD_CNT'] + 1;
        $_POST['pUpdCntEnlist'] = $getUpdCnt;
        
        $age = getAge($_POST['pPatientDateOfBirth']);
        $yearAge = $age['y'];
        $pxAge =  $data['pPxAge'];
        
        updatePatientRegistration($_POST);
        
    }

    //If Update/Edit/View Patient Record
    $pCaseNo = $_GET['pCaseNo'];
    if(isset($_GET['pCaseNo']) && !empty($_GET['pCaseNo'])){
        $pHistoryPatientRecord = getPatientRecord($pCaseNo);
        $pPxCaseNo = $pHistoryPatientRecord['CASE_NO'];
        $pPxTransNo = $pHistoryPatientRecord['TRANS_NO'];
        $pPxTransDate = $pHistoryPatientRecord['TRANS_DATE'];
        $pPxEnlistType = $pHistoryPatientRecord['ENLIST_TYPE'];
        $pPxWithConsent = $pHistoryPatientRecord['WITH_CONSENT'];
        $pPxType = $pHistoryPatientRecord['PX_TYPE'];
        $pPxPin = $pHistoryPatientRecord['PX_PIN'];
        $pPxLname = $pHistoryPatientRecord['PX_LNAME'];
        $pPxFname = $pHistoryPatientRecord['PX_FNAME'];
        $pPxMname = $pHistoryPatientRecord['PX_MNAME'];
        $pPxExtname = $pHistoryPatientRecord['PX_EXTNAME'];
        $pPxDoB = $pHistoryPatientRecord['PX_DOB'];
        $pPxSex = $pHistoryPatientRecord['PX_SEX'];
        $pPxContactMobileNo = $recordResult['PX_MOBILE_NO'];      
        $pPxContactLandlineNo = $recordResult['PX_LANDLINE_NO'];  
        $pMemPin = $pHistoryPatientRecord['MEM_PIN'];
        $pMemLname= $pHistoryPatientRecord['MEM_LNAME'];
        $pMemFname = $pHistoryPatientRecord['MEM_FNAME'];
        $pMemMname = $pHistoryPatientRecord['MEM_MNAME'];
        $pMemExtName= $pHistoryPatientRecord['MEM_EXTNAME'];
        $pMemDoB= $pHistoryPatientRecord['MEM_DOB'];
        $pMemSex = $pHistoryPatientRecord['MEM_SEX'];

    }

    if(isset($_GET['pin']) && !empty($_GET['pin']) && isset($_GET['effyear']) && !empty($_GET['effyear'])){
        $recordResult = searchAssignedMember($_GET['pin'], $_GET['effyear']);

        $pPxType = $recordResult['ASSIGNED_TYPE'];     
          
          $pMemPin = $recordResult['PRIMARY_PIN'];
          $pMemLname = $recordResult['PRIMARY_LAST_NAME'];
          $pMemFname = $recordResult['PRIMARY_FIRST_NAME'];
          $pMemMname = $recordResult['PRIMARY_MIDDLE_NAME'];
          $pMemExtName = $recordResult['PRIMARY_EXT_NAME'];
          $pMemDoB = $recordResult['PRIMARY_DOB'];        
          $pMemSex = $recordResult['PRIMARY_SEX'];       
          
          $pPxPin = $recordResult['ASSIGNED_PIN'];
          $pPxLname = $recordResult['ASSIGNED_LAST_NAME'];
          $pPxFname = $recordResult['ASSIGNED_FIRST_NAME'];
          $pPxMname = $recordResult['ASSIGNED_MIDDLE_NAME'];
          $pPxExtName = $recordResult['ASSIGNED_EXT_NAME'];
          $pPxDoB = $recordResult['ASSIGNED_DOB']; 
          $pPxSex = $recordResult['ASSIGNED_SEX'];    
          $pMemPin = $recordResult['PRIMARY_PIN'];
          $pPxPin = $recordResult['ASSIGNED_PIN'];
          $pPxLname = $recordResult['ASSIGNED_LAST_NAME'];
          $pPxFname = $recordResult['ASSIGNED_FIRST_NAME'];
          $pPxMname = $recordResult['ASSIGNED_MIDDLE_NAME'];
          $pPxExtName = $recordResult['ASSIGNED_EXT_NAME'];
          $pPxDoB = $recordResult['ASSIGNED_DOB']; 
          $pPxSex = $recordResult['ASSIGNED_SEX'];         
          $pPxContactMobileNo = $recordResult['MOBILE_NUMBER'];      
          $pPxContactLandlineNo = $recordResult['LANDLINE_NUMBER'];

          $pAssignDate = $recordResult['ASSIGNED_DATE']; //added v01.08.00
      
        $pHistoryPatientRecord = getEnlistRecord($pPxPin, $_GET['effyear']);
        $pCaseNo = $pHistoryPatientRecord['CASE_NO'];
        if ($pCaseNo != NULL) {
            $pPxCaseNo = $pHistoryPatientRecord['CASE_NO'];
            $pPxTransNo = $pHistoryPatientRecord['TRANS_NO'];
            $pEnlistDate = $pHistoryPatientRecord['ENLIST_DATE'];
            $pPxContactMobileNo = $pHistoryPatientRecord['PX_MOBILE_NO'];      
            $pPxContactLandlineNo = $pHistoryPatientRecord['PX_LANDLINE_NO'];  
        } else {
            $pPxContactMobileNo = $recordResult['MOBILE_NUMBER'];      
            $pPxContactLandlineNo = $recordResult['LANDLINE_NUMBER'];  
        }
        
    }

?>
<body>
<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"><b>DATA ENTRY MODULE</b></div>
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
            <form action="" name="formRegisterClient" method="POST" onsubmit="return saveTransRegistration();">
                <div class="panel-body">
                    <div style="margin-top: 0px;" align="center" id="register_client">
                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                            <input type="hidden"
                                   name="pHCInum"
                                   class="form-control"
                                   id="pHCInum"
                                   maxlength="25"
                                   style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                   onkeypress="return isNumberKey(event);"
                                   autocomplete="off"
                                   value="<?php echo $pHciNo; ?>"
                                   readonly
                                   required
                            />
                            <input type="hidden"
                                   name="pAccreNum"
                                   class="form-control"
                                   id="pAccreNum"
                                   maxlength="25"
                                   style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                   autocomplete="off"
                                   value="<?php echo $pAccreNo; ?>"
                                   readonly
                                   required
                            />
                            <input type="hidden"
                                   name="pPMCCnum"
                                   class="form-control"
                                   id="pPMCCnum"
                                   maxlength="25"
                                   style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                   autocomplete="off"
                                   value="<?php echo $pPmccNo; ?>"
                                   readonly
                                   required
                            />
                            <?php if($pCaseNo != NULL){ ?>
                                <input type="hidden"
                                       name="pCaseNum"
                                       id="pCaseNum"
                                       autocomplete="off"
                                       value="<?php echo $pPxCaseNo; ?>"
                                       readonly
                                />
                                <input type="hidden"
                                       name="pTransNum"
                                       id="pTransNum"
                                       autocomplete="off"
                                       value="<?php echo $pPxTransNo; ?>"
                                       readonly
                                />
                            <?php } ?>

                            <table style="margin-top: 0px;width:100%">
                                <tr id="enlistment_info">
                                    <td>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td colspan="4"><b><u>PhilHealth Information</u></b></td>
                                            </tr>
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Registration Date:</label></td>
                                                <td><label style="color:red;">*</label><label>Package Type:</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="pEnlistmentDate"
                                                           id="pEnlistmentDate"
                                                           class="datepicker form-control"
                                                           id="pEnlistmentDate"
                                                           placeholder="mm/dd/yyyy"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px;text-transform:uppercase"
                                                           onclick="formatDate('pEnlistDate')"
                                                           value="<?php echo date('m/d/Y', strtotime(($pAssignDate))); ?>"
                                                           required
                                                           readonly
                                                    />
                                                </td>
                                                <td>
                                                    <select name="pEnlistType" id="pEnlistType" class="form-control" style="width: 160px; margin: 0px 10px 0px 0px;" required>
                                                          <option value="K" selected="selected">KONSULTA</option>                                                        
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="checkbox"
                                                           name="pWithConsent"
                                                           id="pWithConsent"
                                                           style="margin-left:20px;float: left;"
                                                           value="Y"
                                                           checked
                                                            <?php if(isset($_GET['pCaseNo']) && !empty($_GET['pCaseNo'])) {
                                                                if ($pPxWithConsent == 'Y') {
                                                                    ?> checked
                                                                <?php }
                                                            }?>
                                                    />
                                                    <label for="pWithConsent" style="margin:3px 20px 0px 5px;font-weight: bold;"><label style="color:red;">*</label>With Consent to share patient record?</label>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr id="patient_info">
                                    <td>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td colspan="5"><b><u>Client's Information</u></b></td>
                                            </tr>
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Client Type:</label></td>
                                                <td><label style="color:red;">*</label><label>PIN:</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <select name="pPatientType" id="pPatientType" class="form-control" style="width: 160px; margin: 0px 10px 0px 0px;" readonly required>
                                                        <?php if($pPxType != null){ ?>
                                                            <option value="<?php echo $pPxType;?>" selected="selected"><?php echo getPatientType(false, $pPxType);?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientPIN"
                                                           class="form-control"
                                                           id="pPatientPIN"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           autocomplete="off"
                                                           minlength="12"
                                                           maxlength="12"
                                                           onkeypress="return isNumberKey(event);"
                                                           value="<?php echo $pPxPin; ?>"
                                                           readonly
                                                           required
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Last Name:</label></td>
                                                <td><label style="color:red;">*</label><label>First Name:</label></td>
                                                <td><label>Middle Name:</label></td>
                                                <td><label>Extension (SR.JR):</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientLastName"
                                                           class="form-control"
                                                           id="pPatientLastName"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           maxlength="60"
                                                           autocomplete="off"
                                                           value="<?php echo strReplaceEnye($pPxLname); ?>"
                                                           required
                                                           readonly
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientFirstName"
                                                           class="form-control"
                                                           id="pPatientFirstName"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px;text-transform: uppercase"
                                                           maxlength="60"
                                                           autocomplete="off"
                                                           value="<?php echo strReplaceEnye($pPxFname); ?>"
                                                           required
                                                           readonly
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientMiddleName"
                                                           class="form-control"
                                                           id="pPatientMiddleName"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           maxlength="60"
                                                           autocomplete="off"
                                                           readonly
                                                           value="<?php echo strReplaceEnye($pPxMname); ?>"
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientSuffix"
                                                           class="form-control"
                                                           id="pPatientSuffix"
                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           maxlength="4"
                                                           readonly
                                                           autocomplete="off"
                                                           value="<?php echo strReplaceEnye($pPxExtname);?>"
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Date of Birth:</label></td>
                                                <td><label style="color:red;">*</label><label>Sex:</label></td>
                                                <td><label style="color:red;">*</label><label>Mobile Number:</label></td>
                                                <td><label>Landline Number:</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientDateOfBirth"
                                                           class="datepicker form-control"
                                                           placeholder="mm/dd/yyyy"
                                                           id="pPatientDateOfBirth"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           autocomplete="off"
                                                           onkeydown="computeAge('value');"
                                                           required
                                                           readonly
                                                           value="<?php if(isset($pPxDoB) && !empty($pPxDoB)){ echo date('m/d/Y', strtotime(($pPxDoB)));}?>"
                                                    />
                                                </td>
                                                <td>
                                                    <select name="pPatientSexX" id="pPatientSexX" class="form-control" style="width: 160px; margin: 0px 10px 0px 0px;" readonly required>
                                                        <?php if($pPxSex != null){ ?>
                                                            <option value="<?php echo $pPxSex;?>" selected="selected"><?php echo getSex(false, $pPxSex);?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientContactMobileNumber"
                                                           class="form-control"
                                                           id="pPatientContactMobileNumber"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           maxlength="11"
                                                           autocomplete="off"
                                                           onkeypress="return isNumberKey(event);"
                                                           value="<?php if($pPxContactMobileNo != null){ echo $pPxContactMobileNo; } ?>"
                                                           required
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pPatientLandlineNumber"
                                                           class="form-control"
                                                           id="pPatientLandlineNumber"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           maxlength="11"
                                                           autocomplete="off"
                                                           onkeypress="return isNumberKey(event);"
                                                           value="<?php if($pPxContactLandlineNo != null){ echo $pPxContactLandlineNo; } ?>"
                                                    />
                                                </td>
                                            </tr>
                                        </table>                                        
                                    </td>
                                </tr>  
                                <tr id="member_info">
                                    <td>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td colspan="5"><b><u>Member Information</u></b></td>
                                            </tr>
                                            <tr>
                                                <td><label style="color:red;">*</label><label for="pMemberPIN" style="">Member PIN:</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input name="pMemberPIN"
                                                           id="pMemberPIN"
                                                           class="form-control"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           autocomplete="off"
                                                           minlength="12"
                                                           maxlength="12"
                                                           onkeypress="return isNumberKey(event);"
                                                           value="<?php echo $pMemPin; ?>"
                                                           readonly
                                                           required
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Member's Last Name:</label></td>
                                                <td><label style="color:red;">*</label><label>Member's First Name:</label></td>
                                                <td><label>Member's Middle Name:</label></td>
                                                <td><label>Extension (SR.JR):</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="pMemberLastName"
                                                           class="form-control"
                                                           id="pMemberLastName"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase" 
                                                           autocomplete="off"
                                                           maxlength="60"
                                                           value="<?php echo strReplaceEnye($pMemLname);?>"
                                                           readonly
                                                           required
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pMemberFirstName"
                                                           class="form-control"
                                                           id="pMemberFirstName"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px;text-transform: uppercase" 
                                                           autocomplete="off"
                                                           maxlength="60"
                                                           value="<?php echo strReplaceEnye($pMemFname); ?>"
                                                           readonly
                                                           required
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pMemberMiddleName"
                                                           class="form-control"
                                                           id="pMemberMiddleName"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase" autocomplete="off"
                                                           value="<?php echo strReplaceEnye($pMemMname); ?>"
                                                           maxlength="60"
                                                           readonly
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pMemberSuffix"
                                                           class="form-control"
                                                           id="pMemberSuffix"
                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase" 
                                                           autocomplete="off"
                                                           maxlength="4"
                                                           value="<?php echo strReplaceEnye($pMemExtName);?>"
                                                           readonly
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                        <table style="margin-top: 15px;">
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Member's Date of Birth:</label></td>
                                                <td><label style="color:red;">*</label><label for="pMemberSex" style="">Member Sex:</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="pMemberDateOfBirth"
                                                           id="pMemberDateOfBirth"
                                                           class="datepicker form-control"
                                                           id="pCaseNo"
                                                           placeholder="mm/dd/yyyy"
                                                           style="width: 160px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           onclick="formatDate('pMemberDateOfBirth')"
                                                           value="<?php echo date('m/d/Y', strtotime(($pMemDoB))); ?>"
                                                           readonly
                                                           required
                                                    />
                                                </td>
                                                <td>
                                                    <select name="pMemberSex" id="pMemberSex" class="form-control" style="width: 160px; margin: 0px 10px 0px 0px;" readonly>
                                                        <?php if($pMemSex != null){ ?>
                                                            <option value="<?php echo $pMemSex;?>" selected="selected"><?php echo getSex(false, $pMemSex);?></option>
                                                        <?php } ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr>
                                    <td style="text-align: left;color:red;font-size: 10px; font-family: Verdana, Geneva, sans-serif;margin-top:15px;">
                                      <i>NOTE: All fields marked with asterisk (*) are required.<br/>
                                      </i>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                    </div>
                </div>
            </div>

            <div>
                <input type="button" class="btn btn-primary" style="background:#006dcc" name="back" id="back" value="Go Back to Search Module" onclick="window.location='registration_search.php'">
                <?php if($pCaseNo != NULL){ ?>
                    <input type="submit" class="btn btn-primary" name="updateEnlistClient" id="updateClient" value="Update">
                <?php } else { ?>
                    <input type="submit" class="btn btn-primary" name="submitEnlistClient" id="enlistClient" value="Save">
               <?php } ?>
            </div>
        </form>

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
<script>
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $("#pPatientDateOfBirth").mask("99/99/9999");
    $("#pEnlistmentDate").mask("99/99/9999");
    $("#pMemberDateOfBirth").mask("99/99/9999");

    $("#pEnlistmentDate").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#pPatientDateOfBirth").datepicker({ maxDate: new Date, minDate: new Date(1900, 6, 12) });
    $("#pMemberDateOfBirth").datepicker({ maxDate: new Date, minDate: new Date(1900, 6, 12) });

</script>