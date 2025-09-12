<?php
/**
 * Created by PhpStorm.
 * User: ZNL
 * Date: 1/18/2018
 * Time: 4:32 PM
 */
    $page = 'labs';
    include('header.php');
    include('menu.php');
    checkLogin();

    /* START GET PATIENT RECORD BEFORE CONSULTATION*/
    if(isset($_GET['transno'])){
        $vTransNo = $_GET['transno'];
        $descPatientInfo = getConsultationEnlistInfo($vTransNo);

        if(!$descPatientInfo) {
            echo "<script>alert('Invalid Case Number!'); window.location='consultation_search.php';</script>";
        }

        /*GET LABS RESULT BASED ON ESSENTIAL SERVICES*/
        $getDiagnosticList = getConsultationDiagnosticInfo($vTransNo);
        $vListDiagnostic = listDiagnosisConsultation();
        $getOtherDiagnostic = getDiagnosticOthers($vTransNo);
    } 
    /* END GET PATIENT RECORD BEFORE CONSULTATION*/

    /*Start Compute Age for availment of Essential Services*/
    $pat_birthday = date("m/d/Y",strtotime($descPatientInfo["PX_DOB"]));
    $mem_birthday = date("m/d/Y",strtotime($descPatientInfo["MEM_DOB"]));
    $px_RegisteredDate = date("m/d/Y",strtotime($descPatientInfo["ENLIST_DATE"]));
    $dateRegister = new DateTime($px_RegisteredDate, new DateTimeZone('Asia/Manila'));
    $datePxDoB = new DateTime($pat_birthday, new DateTimeZone('Asia/Manila'));
    $getAgeServ = date_diff($dateRegister,$datePxDoB);
    $descAgeServ = $getAgeServ->y." yr(s), ".$getAgeServ->m." mo(s), ".$getAgeServ->d." day(s)";

        if (($getAgeServ->y >= 0 && $getAgeServ->y <= 1) && ($getAgeServ->m >= 0 && $getAgeServ->m <= 11)) {
            $pAgeBracket = 'pedia-one';
        }
        else if ($getAgeServ->y == 2 && $getAgeServ->m == 0 && $getAgeServ->d == 0) {
            $pAgeBracket = 'pedia-two';
        }
        else if (($getAgeServ->y >= 3 && $getAgeServ->y <= 4) && ($getAgeServ->m >= 0 && $getAgeServ->m <= 11)) {
            $pAgeBracket = 'pedia-three';
        }
        else if (($getAgeServ->y >= 4 && $getAgeServ->y <= 5) && ($getAgeServ->m >= 0 && $getAgeServ->m <= 11)) { 
            $pAgeBracket = 'pedia-four';
        }
        else if ($getAgeServ->y == 5 && $getAgeServ->m == 0 && $getAgeServ->d == 0) {
            $pAgeBracket = 'pedia-five';
        }
        else {
            $pAgeBracket = 'non-pedia';
        }

        $yearAge = $getAgeServ->y;
        $monAge = $getAgeServ->m;
        $dayAge = $getAgeServ->d;
        $pxAge = $yearAge.' yrs, '.$monAge.' mos, '.$dayAge.' days';
    /*End Compute Age for availment of Essential Services*/

    /*Save Record*/
    if(isset($_POST['btnSave'])){
        saveLaboratoryResults($_POST);

    }

?>

<!--suppress Annotator -->
<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"><b>DATA ENTRY MODULE</b></div>
        <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
    </div>
</div>

<div id="wait_image" align="center" style="display: ; margin: 30px 0px;">
    <img src="res/images/LoadingWait.gif" alt="Please Wait" />
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

    <div id="content_div" align="center" style="margin: 0px 0px 0px 0px;">
        <form action="" name="labForm" method="POST" onsubmit="return validateLabResultsForm();">
            <input type="hidden"
                   name="pEffYear"
                   id="pEffYear"
                   value="<?php echo $descPatientInfo['EFF_YEAR']; ?>"
            />
            <input type="hidden"
                   name="pMemPin"
                   id="pMemPin"
                   value="<?php echo $descPatientInfo['MEM_PIN']; ?>"
            />

          <table style="margin-top: 0px;width:100%" align="center">
            <tr id="information">
                <td>
                    <div class="panel panel-primary">
                        <div class="panel-body">
                            <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
                                <table style="margin-top: 5px; text-align: left;width:100%">
                                    <tr>
                                        <td colspan="4" class="alert alert-success"><b><u>Client Information</u></b></td>
                                    </tr>
                                </table>
                                <table  style="margin-top: 5px; text-align: left;">
                                    <tr>
                                        <td><label>Case No:</label></td>
                                        <td><label>Transaction No:</label></td>
                                        <td><label>Consultation Date:</label></td>
                                        <td><label>Client PIN:</label></td>
                                        <td><label>Type:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pCaseNo"
                                                   id="pCaseNo"
                                                   class="form-control"
                                                   value="<?php echo $descPatientInfo['CASE_NO']; ?>"
                                                   style="width: 180px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pConsultTransNo"
                                                   id="pConsultTransNo"
                                                   class="form-control"
                                                   value="<?php echo $descPatientInfo['TRANS_NO']; ?>"
                                                   style="width: 180px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pConsultationDate"
                                                   id="pConsultationDate"
                                                   class="form-control"
                                                   value="<?php echo date('m/d/Y', strtotime($descPatientInfo['ENLIST_DATE'])); ?>"
                                                   style="width: 150px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientPIN"
                                                   id="pPatientPIN"
                                                   class="form-control"
                                                   value="<?php echo $descPatientInfo['PX_PIN']; ?>"
                                                   style="width: 130px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name=""
                                                   id=""
                                                   class="form-control"
                                                   value="<?php echo getPatientType(false,$descPatientInfo['PX_TYPE']); ?>"
                                                   style="width: 150px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                            <input type="hidden"
                                                   name="pPatientType"
                                                   id="pPatientType"
                                                   value="<?php echo $descPatientInfo['PX_TYPE']; ?>"
                                                   readonly
                                            />
                                        </td>
                                    </tr>
                                </table>
                                <table style="margin-top: 5px; text-align: left;">
                                    <tr>
                                        <td><label>Last Name:</label></td>
                                        <td><label>First Name:</label></td>
                                        <td><label>Middle Name:</label></td>
                                        <td><label>Suffix:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pPatientLastName"
                                                   id="pPatientLastName"
                                                   class="form-control"
                                                   value="<?php echo strReplaceEnye($descPatientInfo['PX_LNAME']); ?>"
                                                   style="width: 170px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientFirstName"
                                                   id="pPatientFirstName"
                                                   class="form-control"
                                                   value="<?php echo strReplaceEnye($descPatientInfo['PX_FNAME']); ?>"
                                                   style="width: 170px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientMiddleName"
                                                   id="pPatientMiddleName"
                                                   class="form-control"
                                                   value="<?php echo strReplaceEnye($descPatientInfo['PX_MNAME']); ?>"
                                                   style="width: 170px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientSuffix"
                                                   id="pPatientSuffix"
                                                   class="form-control"
                                                   value="<?php echo strReplaceEnye($descPatientInfo['PX_EXTNAME']); ?>"
                                                   style="width: 70px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                    </tr>
                                </table>

                                <table style="margin-top: 5px; text-align: left;">
                                    <tr>
                                        <td><label>Contact No.:</label></td>
                                        <td><label>Sex:</label></td>
                                        <td><label>Date of Birth:</label></td>
                                        <td><label>Age:</label></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="text"
                                                   name="pPatientContactNo"
                                                   id="pPatientContactNo"
                                                   class="form-control"
                                                   value="<?php echo $descPatientInfo['PX_MOBILE_NO'].' '.$descPatientInfo['PX_LANDLINE_NO']; ?>"
                                                   style="width: 120px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientSexX"
                                                   id="pPatientSexX"
                                                   class="form-control"
                                                   value="<?php echo getSex(false, $descPatientInfo['PX_SEX']); ?>"
                                                   style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                            <input type="hidden"
                                                   id="txtPerHistPatSexValue"
                                                   name="txtPerHistPatSexValue"
                                                   value="<?php echo $descPatientInfo["PX_SEX"]; ?>"
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientDateOfBirth"
                                                   id="pPatientDateOfBirth"
                                                   class="form-control"
                                                   value="<?php echo date('m/d/Y', strtotime($descPatientInfo['PX_DOB'])); ?>"
                                                   style="width: 90px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                        </td>
                                        <td>
                                            <input type="text"
                                                   name="pPatientAge"
                                                   id="pPatientAge"
                                                   class="form-control"
                                                   value="<?php echo $descAgeServ; ?>"
                                                   style="width: 200px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
                                            <input type="hidden" id="valtxtPerHistPatAge" name="valtxtPerHistPatAge" value="<?php echo $yearAge; ?>" />
                                            <input type="hidden" id="valtxtPerHistPatMonths" name="valtxtPerHistPatMonths" value="<?php echo $monAge; ?>" />
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </div>
                    </div>
                </td>
            </tr>
            <tr id="soap_info">
                <td>
                    <div class="tabbable">
                        <ul class="nav nav-pills nav-justified" data-tabs="tabs" style="margin-top: 1px; margin-bottom: 5px;">
                            <li class="active" id="list4"><a href="#tab4" data-toggle="tab" class="" style="text-align: center;">1<br>Plan/Management</a></li>
                            <li class="" id="list5"><a href="#tab5" data-toggle="tab" class="" style="text-align: center;">2<br>Laboratory/Imaging Results</a></li>       
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab4">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Plan/Management</h3>
                                </div>
                                <div class="panel-body">
                                    <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                        <table width="100%">
                                            <tr>
                                                <td class="alert alert-success"><b>PLAN/MANAGEMENT</b></td>
                                            </tr>
                                        </table>

                                        <table style="margin: 5px 0px 0px 20px; text-align: left;width: 100%;" id="diagnostic_examination">
                                            <tr style="font-weight: bold;">
                                                <td width="500px">A. <font color="red" style="font-size: 15px;">*</font>Laboratory/Imaging Examination</td>
                                            </tr>
                                            <tr>
                                              <td>
                                                <table border="1" style="width: 90%;" class="table table-condensed table-bordered">
                                                  <col width="35%">
                                                  <col width="20%">
                                                  <col width="20%">
                                                  <col width="15%">
                                                  <tr style="text-align: center;font-size: 10px;">
                                                    <td style="font-weight: bold;">Laboratory/Imaging</td>
                                                    <td style="font-weight: bold;">Is Doctor Recommended?</td>
                                                    <td style="font-weight: bold;">Is Client Requested?</td>
                                                    <td style="font-weight: bold;">Status<br><i>(D - Done<br/>N - Not yet done<br/>W - Waived<br/>X - Deferred)</i></td>
                                                  </tr>
                                                  <?php
                                                  for ($i = 0; $i < count($vListDiagnostic); $i++) {
                                                        $pDiagnosticDesc = $vListDiagnostic[$i]['DIAGNOSTIC_DESC'];
                                                        $pDiagnosticID = $vListDiagnostic[$i]['DIAGNOSTIC_ID'];
                                                  ?>
                                                  <tr>
                                                    <td width="20%">
                                                        <input type="hidden"
                                                               name="diagnostic[]"
                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID;?>"
                                                               value="<?php echo $pDiagnosticID;?>"
                                                               style="cursor: pointer; float: left;"    
                                                               readonly
                                                        />
                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID;?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pDiagnosticDesc; ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctor';?>"
                                                             value="Y"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorYes'; ?>"
                                                             style="cursor: pointer; float: left;margin-left: 10px"
                                                             disabled
                                                            <?php 
                                                            foreach($getDiagnosticList as $diagListStatus) {
                                                                if(($vListDiagnostic[$i]['DIAGNOSTIC_ID'] == $diagListStatus['DIAGNOSTIC_ID']) && ($diagListStatus['IS_DR_RECOMMENDED'] == "Y")) {
                                                                echo "checked";   
                                                                } 
                                                            }?>    
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorYes'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">Yes</label>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctor';?>"
                                                             value="N"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorNo'; ?>"
                                                             style="float: left;"
                                                             disabled
                                                            <?php 
                                                            foreach($getDiagnosticList as $diagListStatus) {
                                                                if(($vListDiagnostic[$i]['DIAGNOSTIC_ID'] == $diagListStatus['DIAGNOSTIC_ID']) && ($diagListStatus['IS_DR_RECOMMENDED'] == "N")) {
                                                                echo "checked";   
                                                                } 
                                                            }?>                                                                 
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorNo'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">No</label>

                                                    </td>
                                                    <td>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_patient';?>"
                                                             value="RQ"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRQ'; ?>"
                                                             style="cursor: pointer; float: left;"
                                                             disabled
                                                            <?php 
                                                            foreach($getDiagnosticList as $diagListStatus) {
                                                                if(($vListDiagnostic[$i]['DIAGNOSTIC_ID'] == $diagListStatus['DIAGNOSTIC_ID']) && ($diagListStatus['PX_REMARKS'] == "RQ")) {
                                                                echo "checked";   
                                                                } 
                                                            }?>  
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRQ'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">Request</label>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_patient';?>"
                                                             value="RF"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRF'; ?>"
                                                             style="cursor: pointer; float: left;"
                                                             disabled
                                                            <?php 
                                                            foreach($getDiagnosticList as $diagListStatus) {
                                                                if(($vListDiagnostic[$i]['DIAGNOSTIC_ID'] == $diagListStatus['DIAGNOSTIC_ID']) && ($diagListStatus['PX_REMARKS'] == "RF")) {
                                                                echo "checked";   
                                                                } 
                                                            }?>  
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRF'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px;">Refuse</label>
                                                    </td>    
                                                    <td>
                                                        <?php 
                                                        foreach($getDiagnosticList as $diagListStatus) {

                                                            if(($vListDiagnostic[$i]['DIAGNOSTIC_ID'] == $diagListStatus['DIAGNOSTIC_ID']) && ($diagListStatus['IS_DR_RECOMMENDED'] == "Y" || $diagListStatus['PX_REMARKS'] == "RQ")) { 
                                                                

                                                            $vLaboratoryStatus = getLaboratoryStatus($vListDiagnostic[$i]['DIAGNOSTIC_ID'], $vTransNo);
                                                            if($vLaboratoryStatus) {
                                                                $vLabStatus = $vLaboratoryStatus["LAB_STATUS"];
                                                            } else {
                                                                $vLabStatus = "N";
                                                            }
                                                                ?>
                                                                <input type="text"
                                                                       name="pDiagStatus"
                                                                       id="<?php echo 'diagnostic_'.$pDiagnosticID.'_status'; ?>"
                                                                       class="form-control"
                                                                       value="<?php echo $vLabStatus; ?>"
                                                                       style="width: 60px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; text-align: center;"
                                                                       autocomplete="off"
                                                                       readonly
                                                                />
                                                           <?php  } 
                                                        }
                                                        ?>
                                                    </td>
                                                  </tr>
                                                  <?php }  ?>
                                                  <tr>
                                                    <td colspan="3" style="text-align: left;">
                                                        <input type="text"
                                                               name="diagnostic_oth_remarks"
                                                               id="diagnostic_oth_remarks1"
                                                               class="form-control"
                                                               style="width: 250px; color: #000; margin: 0px 0px 0px 5px; text-transform: uppercase;"
                                                               autocomplete="off"
                                                               value="<?php echo $getOtherDiagnostic['OTH_REMARKS'];?>"
                                                               readonly
                                                               placeholder="Other Diagnostic Exam"
                                                        />
                                                        <br/>
                                                      </td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>     
                                        </table>
                                    </fieldset>  
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab5">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Laboratory/Imaging Results</h3>
                                </div>
                                <div class="panel-body" id="obliSerTab">
                                    <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                        <table style="width:100%; margin: 5px 20px 0px 20px; text-align: left;" cellpadding="2" align="left">
                                            <!--START CHECK DIAGNOSTIC EXAMINATION-->
                                            <?php
                                                foreach ($vListDiagnostic as $diagnostic) {
                                                    $pDiagnosticDesc = $diagnostic['DIAGNOSTIC_DESC'];
                                                    $pDiagnosticID = $diagnostic['DIAGNOSTIC_ID'];
                                            ?>
                                            <tr id="<?php echo 'div_diagnostic_'.$pDiagnosticID.'_header'; ?>" <?php 
                                                    $vLaboratoryStatus = getLaboratoryStatus($pDiagnosticID, $vTransNo);
                                                            foreach($getDiagnosticList as $diagListStatus) {
                                                                if($pDiagnosticID == $diagListStatus['DIAGNOSTIC_ID']) {
                                                                    if ($diagListStatus['IS_DR_RECOMMENDED'] == "Y" || $diagListStatus['PX_REMARKS'] == "RQ") {
                                                                        if ($vLaboratoryStatus["LAB_STATUS"] == "D" || $vLaboratoryStatus["LAB_STATUS"] == "W" || $vLaboratoryStatus["LAB_STATUS"] == "X") {
                                                                            echo ' style="display: none;"';
                                                                        }
                                                                    } else {
                                                                        echo ' style="display: none;"';
                                                                    }
                                                                } 
                                                            }
                                                    ?>
                                            > 
                                                <td class="alert alert-success">
                                                  <label style="color:red;">*</label><strong><?php echo $pDiagnosticDesc;?></strong>
                                                  <div style="float:right;">
                                                            <span style="display:inline-block;">                                                                
                                                                <span>
                                                                    <input id="<?php echo 'diagnostic_'.$pDiagnosticID.'_done'; ?>"
                                                                           type="radio"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_status'; ?>"
                                                                           value="D"
                                                                           style="margin-left:20px;float: left;"/>
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_done'; ?>" style="margin: 3px 20px 0px 5px;">Done</label>
                                                                </span>
                                                                <span>
                                                                    <input id="<?php echo 'diagnostic_'.$pDiagnosticID.'_notYetDone'; ?>"
                                                                           type="radio"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_status'; ?>"
                                                                           value="N"
                                                                           style="margin-left:20px;float: left;"
                                                                           checked="checked"/>
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_notYetDone'; ?>" style="margin: 3px 20px 0px 5px;">Not yet done</label>
                                                                </span>
                                                                <span>
                                                                    <input id="<?php echo 'diagnostic_'.$pDiagnosticID.'_deferred'; ?>"
                                                                           type="radio"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_status'; ?>"
                                                                           value="X"
                                                                           style="margin-left:20px;float: left;"/>
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_deferred'; ?>" style="margin: 3px 20px 0px 5px;">Deferred</label>
                                                                </span>
                                                                <span>
                                                                    <input id="<?php echo 'diagnostic_'.$pDiagnosticID.'_waived'; ?>"
                                                                           type="radio"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_status'; ?>"
                                                                           value="W"
                                                                           style="margin-left:20px;float: left;"/>
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_waived'; ?>" style="margin: 3px 20px 0px 5px;">Waived</label>
                                                                </span>
                                                            </span>
                                                        </div>
                                                </td>                                                
                                            </tr>
                                            <tr id="<?php echo 'div_diagnostic_'.$pDiagnosticID.'_form'; ?>" <?php 
                                                    $vLaboratoryStatus = getLaboratoryStatus($pDiagnosticID, $vTransNo);
                                                            foreach($getDiagnosticList as $diagListStatus) {
                                                                if($pDiagnosticID == $diagListStatus['DIAGNOSTIC_ID']) {
                                                                    if ($diagListStatus['IS_DR_RECOMMENDED'] == "Y" || $diagListStatus['PX_REMARKS'] == "RQ") {
                                                                        if ($vLaboratoryStatus["LAB_STATUS"] == "D" || $vLaboratoryStatus["LAB_STATUS"] == "W" || $vLaboratoryStatus["LAB_STATUS"] == "X") {
                                                                            echo ' style="display: none;"';
                                                                        }
                                                                    } else {
                                                                        echo ' style="display: none;"';
                                                                    }
                                                                } 
                                                            }
                                                    ?>
                                            >
                                                <td>
                                                    <fieldset style="margin-top: 10px; padding: 20px;">
                                                        <table>
                                                            <tr>
                                                                <td style="width: 200px; vertical-align: text-top"><label style="font-style: italic; font-weight: bold;">Laboratory/Image Done</label></td>
                                                                <td colspan="2">
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="radio"
                                                                                       name="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam';?>"
                                                                                       value="1"
                                                                                       id="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_in'; ?>"
                                                                                       style="cursor: pointer; float: left;"
                                                                                       onclick="setDisabled('<?php echo "diagnostic_".$pDiagnosticID."_accre_diag_fac";?>', true);"
                                                                                       checked="checked"
                                                                                />
                                                                                <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_in'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">within the facility</label>
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="radio"
                                                                                       name="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam';?>"
                                                                                       id="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_out';?>"
                                                                                       value="0"
                                                                                       style="cursor: pointer; float: left;"
                                                                                       onclick="setDisabled('<?php echo "diagnostic_".$pDiagnosticID."_accre_diag_fac";?>', false); setFocus('<?php echo "diagnostic_".$pDiagnosticID."_accre_diag_fac";?>')"
                                                                                />
                                                                                <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_out';?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px;">Partner Facility</label>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                       name="<?php echo 'diagnostic_'.$pDiagnosticID.'_accre_diag_fac'; ?>"
                                                                                       id="<?php echo 'diagnostic_'.$pDiagnosticID.'_accre_diag_fac'; ?>"
                                                                                       class="form-control"
                                                                                       style="width: 300px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                                       autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION" required disabled>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>

                                                            <tr>
                                                                <td>
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_date'; ?>" style="font-style: italic; font-weight: bold;">Date of Lab/Image Exam</label>
                                                                <td colspan="2">
                                                                    <input type="text"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_date'; ?>"
                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_exam_date'; ?>"
                                                                           class="datepicker form-control"
                                                                           style="width: 110px; color: #000; margin: 5px 10px 0px 28px; text-transform: uppercase"
                                                                           onkeypress="formatDate('<?php echo "diagnostic_".$pDiagnosticID."_lab_exam_date"; ?>')"
                                                                           autocomplete="off"
                                                                           placeholder="mm/dd/yyyy"
                                                                           maxlength="10"
                                                                           />
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_fee'; ?>" style="font-style: italic; font-weight: bold;">Laboratory/Imaging Fee</label>
                                                                </td>
                                                                <td colspan="2">
                                                                  Php
                                                                    <input type="text"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_fee'; ?>"
                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_fee'; ?>"
                                                                           class="form-control"
                                                                           style="width: 110px; color: #000; margin: 5px 10px 0px 0px; text-transform: uppercase"
                                                                            onkeypress="return isNumberKeyWithTwoDecimalKey(event,'<?php echo 'diagnostic_'.$pDiagnosticID.'_lab_fee';?>');"
                                                                           autocomplete="off"
                                                                           />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <?php
                                                            /*START COMPLETE BLOOD COUNT (CBC)*/
                                                            if ($pDiagnosticID == '1') {
                                                            ?>
                                                                <tr>
                                                                    <td>
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_hematocrit'; ?>" style="font-style: normal; font-weight: normal;">Hematocrit</label>
                                                                    </td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_hematocrit';?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_hematocrit'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15">%
                                                                        <span id="normalHct" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveHct" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowHct" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_hemoglobin_gdL'; ?>" style="font-style: normal; font-weight: normal;">Hemoglobin</label>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_hemoglobin_gdL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_hemoglobin_gdL';?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" 
                                                                               value="">g/dL
                                                                        <span id="normalHgb" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveHgb" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowHgb" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_hemoglobin_mmolL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_hemoglobin_mmolL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"  
                                                                               maxlength="15" 
                                                                               value="">mmol/L
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_mhc_pgcell'; ?>" style="font-style: normal; font-weight: normal;">MCH</label>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_mhc_pgcell';?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_mhc_pgcell';?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase" 
                                                                               value=""
                                                                               autocomplete="off"   
                                                                               maxlength="15">pg/cell
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_mhc_fmolcell'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_mhc_fmolcell'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15"
                                                                               value="">fmol/cell
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_mchc_gHbdL'; ?>" style="font-style: normal; font-weight: normal;">MCHC</label>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_mchc_gHbdL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_mchc_gHbdL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">g Hb/dL
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_mchc_mmolHbL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_mchc_mmolHbL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">mmol Hb/L
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_mcv_um'; ?>" style="font-style: normal; font-weight: normal;">MCV</label>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_mcv_um'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_mcv_um'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15"
                                                                               value="">um^3
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_mcv_fL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_mcv_fL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15"
                                                                               value="">fL
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td>
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc_cellsmmuL'; ?>" style="font-style: normal; font-weight: normal;">WBC</label>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc_cellsmmuL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc_cellsmmuL'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15"
                                                                               value="">x1,000 cells/mm^3uL
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc_cellsL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc_cellsL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15"
                                                                               value="">x10^9 cells/L
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="3">&nbsp;</td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="3"><label style="font-weight: bold;">Leukocyte differential</label></td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_myelocyte'; ?>" style="font-style: normal; font-weight: normal;">Myelocyte</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_myelocyte'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_myelocyte'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">%
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_neutrophils_bands'; ?>" style="font-style: normal; font-weight: normal;">Neutrophils (bands)</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_neutrophils_bands'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_neutrophils_bands'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">%
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_neutrophils_segmenters';?>" style="font-style: normal; font-weight: normal;">Neutrophils (segmenters)</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_neutrophils_segmenters'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_neutrophils_segmenters'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">%
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_lymphocytes'; ?>" style="font-style: normal; font-weight: normal;">Lymphocytes</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_lymphocytes'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_lymphocytes'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">%
                                                                        <span id="normalLymp" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveLymp" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowLymp" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_monocytes'; ?>" style="font-style: normal; font-weight: normal;">Monocytes</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_monocytes'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_monocytes'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">%
                                                                        <span id="normalMono" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveMono" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowMono" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_eosinophils'; ?>" style="font-style: normal; font-weight: normal;">Eosinophils</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_eosinophils'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_eosinophils'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value=""
                                                                               >%
                                                                        <span id="normalEosi" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveEosi" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowEosi" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_basophils'; ?>" style="font-style: normal; font-weight: normal;">Basophils</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_basophils'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_basophils'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">%
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_platelet'; ?>" style="font-style: normal; font-weight: normal;">Platelet</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_platelet'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_platelet'; ?>"
                                                                               class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">Platelets/mcL
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END COMPLETE BLOOD COUNT (CBC)*/

                                                            /*START URINALYSIS*/
                                                            if($pDiagnosticID == '2'){ ?>
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <table width="100%">
                                                                            <col style="width:20%">
                                                                            <col style="width:30%">
                                                                            <col style="width:20%">
                                                                            <col style="width:30%">
                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_sg'; ?>" style="font-style: normal; font-weight: normal;">Specific gravity</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_sg'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_sg'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_crystals'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Crystals</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_crystals'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_crystals'; ?>"
                                                                                           class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_appearance'; ?>" style="font-style: normal; font-weight: normal;">Appearance</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_appearance'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_appearance'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off" 
                                                                                           maxlength="50"
                                                                                           value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_bladder_cells'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Bladder cells</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_bladder_cells'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_bladder_cells'; ?>"
                                                                                           class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_color'; ?>" style="font-style: normal; font-weight: normal;">Color</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_color'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_color'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off" maxlength="50" value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_squamous_cells'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Squamous cells</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_squamous_cells'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_squamous_cells'; ?>"
                                                                                           class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose'; ?>" style="font-style: normal; font-weight: normal;">Glucose</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off" maxlength="50" value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_tubular_cells'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Tubular cells</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_tubular_cells'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_tubular_cells'; ?>"
                                                                                           class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_proteins'; ?>" style="font-style: normal; font-weight: normal;">Proteins</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_proteins'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_proteins'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off" maxlength="50" value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_broad_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Broad casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_broad_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_broad_casts'; ?>"
                                                                                           class="form-control" style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_ketones'; ?>" style="font-style: normal; font-weight: normal;">Ketones</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ketones'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_ketones'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off" maxlength="50" value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_epithelial_cell_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Epithelial cell casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_epithelial_cell_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_epithelial_cell_casts'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_pH'; ?>" style="font-style: normal; font-weight: normal;">pH</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_pH'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_pH'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_granular_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Granular casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_granular_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_granular_casts'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_pus'; ?>" style="font-style: normal; font-weight: normal;">Pus cells</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_pus'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_pus'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">
                                                                                    <span id="normalUrinePus" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                                    <span id="aboveUrinePus" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_hyaline_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Hyaline casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_hyaline_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_hyaline_casts'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_alb'; ?>" style="font-style: normal; font-weight: normal;">Albumin</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_alb'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_alb'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">mg/dl
                                                                                    <span id="normalUrineAlb" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                                    <span id="aboveUrineAlb" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Red blood cell casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc_casts'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc'; ?>" style="font-style: normal; font-weight: normal;">Red blood cells</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                    <span id="normalUrineRbc" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                                    <span id="aboveUrineRbc" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_waxy_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">Waxy casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_waxy_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_waxy_casts'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc'; ?>" style="font-style: normal; font-weight: normal;">White blood cells</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>

                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_wc_casts'; ?>" style="font-style: normal; font-weight: normal;margin-left:40px;">White cell casts</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_wc_casts'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_wc_casts'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_bacteria'; ?>" style="font-style: normal; font-weight: normal;">Bacteria</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_bacteria'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_bacteria'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="50" value="">/hpf
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                            <?php } /*END URINALYSIS*/

                                                            /*START FECALYSIS*/
                                                            if ($pDiagnosticID == '3'){ ?>
                                                                <tr>
                                                                    <td colspan="3"><label style="font-weight: bold">Appearance:</label></td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_blood'; ?>" style="font-style: normal; font-weight: normal;">Color</label></td>
                                                                    <td colspan="2">
                                                                        <select name="<?php echo 'diagnostic_'.$pDiagnosticID.'_color'; ?>" id="<?php echo 'diagnostic_'.$pDiagnosticID.'_color'; ?>" class="form-control" style="width: 150px; margin: 0px 10px 0px 0px;">
                                                                            <?php
                                                                            $diagnostic_color_options = getDiagnosticColor(true, '');
                                                                            foreach($diagnostic_color_options as $key => $value) {
                                                                                ?>
                                                                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_consistency'; ?>" style="font-style: normal; font-weight: normal;">Consistency</label></td>
                                                                    <td colspan="2">
                                                                        <select name="<?php echo 'diagnostic_'.$pDiagnosticID.'_consistency'; ?>"
                                                                                id="<?php echo 'diagnostic_'.$pDiagnosticID.'_consistency'; ?>"
                                                                                class="form-control"
                                                                                style="width: 150px; margin: 3px 10px 0px 0px;">
                                                                            <?php
                                                                            $diagnostic_consistency_options = getDiagnosticConsistency(true, '');
                                                                            foreach($diagnostic_consistency_options as $key => $value) {
                                                                                ?>
                                                                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_pus'; ?>" style="font-style: normal; font-weight: normal;">Pus Cells</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_pus'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_pus'; ?>"
                                                                               class="form-control"
                                                                               style="width: 150px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="4"
                                                                               value="">
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="3">&nbsp;</td>
                                                                </tr>

                                                                <tr>
                                                                    <td colspan="3"><label style="font-weight: ">Microscopic:</label></td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc'; ?>" style="font-style: normal; font-weight: normal;">RBC</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_rbc'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" value="">/hpf
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc'; ?>" style="font-style: normal; font-weight: normal;">WBC</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_wbc'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" value="">/hpf
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_ova'; ?>" style="font-style: normal; font-weight: normal;">Ova</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ova'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_ova'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" value="">=/-
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_parasite'; ?>" style="font-style: normal; font-weight: normal;">Parasite</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_parasite'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_parasite'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" value="">=/-
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_blood'; ?>" style="font-style: normal; font-weight: normal;">Blood</label></td>
                                                                    <td colspan="2">
                                                                        <select name="<?php echo 'diagnostic_'.$pDiagnosticID.'_blood'; ?>"
                                                                                id="<?php echo 'diagnostic_'.$pDiagnosticID.'_blood'; ?>"
                                                                                class="form-control"
                                                                                style="width: 100px; margin: 3px 10px 0px 0px;">
                                                                            <?php
                                                                            $diagnostic_blood_options = getDiagnosticBlood(true, '');
                                                                            foreach($diagnostic_blood_options as $key => $value) {
                                                                                ?>
                                                                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                                <?php
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_occult_blood'; ?>" style="font-style: normal; font-weight: normal;">Occult Blood</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_occult_blood'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_occult_blood'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" value="">
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END FECALYSIS*/

                                                            /*START CHEST X-RAY*/
                                                            if ($pDiagnosticID == '4') { ?>
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <div>
                                                                            <label style="font-style: normal;">Results</label>
                                                                        </div>
                                                                        <table id="tblChestObservation" class="table table-condensed table-bordered" style="width: 100%">
                                                                            <col width="50%">
                                                                            <col width="40%">
                                                                            <col width="10%">
                                                                            <thead>
                                                                            <tr>
                                                                                <th style="vertical-align: text-top;text-align:center;font-weight: bold;font-size:11px;">Observation</th>
                                                                                <th style="vertical-align: text-top;text-align:center;font-weight: bold;font-size:11px;"></th>
                                                                                <th></th>
                                                                            </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select name="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_observe';?>"
                                                                                            id="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_observe';?>"
                                                                                            class="form-control"
                                                                                            style="width: 100%; margin: 3px 10px 0px 0px;"
                                                                                            onChange="onChangeObservation('<?php echo "diagnostic_".$pDiagnosticID."_chest_observe";?>', '<?php echo "diagnostic_".$pDiagnosticID."_chest_observe_remarks"; ?>');">
                                                                                        <option value selected="selected" disabled></option>
                                                                                        <?php
                                                                                        $listXrayObservation = listChestXrayObservation();
                                                                                        foreach ($listXrayObservation as $observation) {
                                                                                            echo "<option value='".$observation["OBSERVE_ID"]."'>".$observation["OBSERVE_DESC"]."</option>";
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_observe_remarks'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_observe_remarks'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           disabled>
                                                                                </td>
                                                                                <td  style="vertical-align: middle">
                                                                                    <button type="button" class="btn btn-success" style="width: 100%" onclick="addXrayObservation();">Add</button>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                        <table style="margin-top:20px;width: 100%">
                                                                            <col width="50%">
                                                                            <col width="50%">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th style="font-weight: bold;vertical-align: text-top"><label>Findings</label></th>
                                                                                    <th style="font-weight: normal;vertical-align: text-top"><label>Remarks</label></th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                            <tr>
                                                                                <td>
                                                                                    <select name="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_findings';?>"
                                                                                            id="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_findings';?>"
                                                                                            class="form-control"
                                                                                            style="width: 100%; margin: 3px 10px 0px 0px;"
                                                                                            onChange="onChangeFindings('<?php echo "diagnostic_".$pDiagnosticID."_chest_findings";?>', '<?php echo "diagnostic_".$pDiagnosticID."_chest_findings_remarks"; ?>');">>
                                                                                        <option value="" selected="selected" disabled></option>
                                                                                        <?php
                                                                                        $listXrayFindings = listChestXrayFindings();
                                                                                        foreach ($listXrayFindings as $finding) {
                                                                                            echo "<option value='".$finding["FINDING_ID"]."'>".$finding["FINDING_DESC"]."</option>";
                                                                                        }
                                                                                        ?>
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_findings_remarks'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_chest_findings_remarks'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100%; color: #000; margin: 3px 10px 0px 10px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           disabled>
                                                                                </td>
                                                                            </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END CHEST X-RAY*/

                                                            /*START SPUTUM MICROSCOPY*/
                                                            if ($pDiagnosticID == '5') { ?>
                                                                <tr>
                                                                    <td style="vertical-align: text-top"><label style="font-style: italic; font-weight: normal;">Lab Results</label></td>
                                                                    <td colspan="2">
                                                                        <input type="radio"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_sputum';?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>"
                                                                               value="1"
                                                                               style="cursor: pointer; float: left;"
                                                                               autocomplete="off"
                                                                               onclick="disableID('<?php echo "diagnostic_".$pDiagnosticID."_sputum_remarks"; ?>');">
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px;">Essentially Normal</label>
                                                                        <input type="radio"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_sputum';?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>"
                                                                               value="2"
                                                                               style="cursor: pointer; float: left;"
                                                                               autocomplete="off"
                                                                               onclick="enableID('<?php echo "diagnostic_".$pDiagnosticID."_sputum_remarks" ; ?>'); setFocus('<?php echo "diagnostic_".$pDiagnosticID."_sputum_remarks" ;?>');">
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">With Findings</label>

                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_sputum_remarks'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_sputum_remarks'; ?>"
                                                                               class="form-control"
                                                                               style="width: 200px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" required disabled>
                                                                    </td>
                                                                <tr>
                                                                    <td style="font-weight: normal;font-size:11px;">Number of Plusses
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_plusses';?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_plusses';?>"
                                                                               class="form-control"
                                                                               style="width:100px;margin-top:10px;"
                                                                        />
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END SPUTUM MICROSCOPY*/

                                                            /*START LIPID PROFILE*/
                                                            if ($pDiagnosticID == '6') { ?>
                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_ldl'; ?>" style="font-style: normal; font-weight: normal;">LDL Cholesterol</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ldl'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_ldl'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="50" value="">mg/dL
                                                                        <span id="normalLdl" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveLdl" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowLdl" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_hdl'; ?>" style="font-style: normal; font-weight: normal;">HDL Cholesterol</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_hdl'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_hdl'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="50" value="">mg/dL
                                                                        <span id="normalHdl" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveHdl" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowHdl" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_cholesterol'; ?>" style="font-style: normal; font-weight: normal;">Total Cholesterol</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_cholesterol'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_cholesterol'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="50" value="">mg/dL
                                                                        <span id="normalChol" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveChol" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_triglycerides'; ?>" style="font-style: normal; font-weight: normal;">Triglycerides</label></td>
                                                                    <td colspan="2">
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_triglycerides'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_triglycerides'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="50" value="">mg/dL
                                                                        <span id="normalTrigly" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveTrigly" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END LIPID PROFILE*/

                                                            /*START FASTING BLOOD SUGAR (FBS)*/
                                                            if ($pDiagnosticID == '7') { ?>
                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mgdL'; ?>" style="font-style: normal; font-weight: normal;">Glucose</label></td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mgdL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mgdL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"    
                                                                               maxlength="15" 
                                                                               value="">mg/dL
                                                                        <span id="normalGlucose" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveGlucose" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowGlucose" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mmolL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mmolL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" 
                                                                               value="">mmol/L
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END FASTING BLOOD SUGAR (FBS)*/

                                                            /*START RANDOM BLOOD SUGAR (RBS)*/
                                                            if ($pDiagnosticID == '19') { ?>
                                                                <tr>
                                                                    <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mgdL'; ?>" style="font-style: normal; font-weight: normal;">Glucose</label></td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mgdL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mgdL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" 
                                                                               value="">mg/dL
                                                                        <span id="normalGlucose" style="color:red;display:none;font-size:11px;">Normal</span>
                                                                        <span id="aboveGlucose" style="color:red;display:none;font-size:11px;">Above Normal</span>
                                                                        <span id="belowGlucose" style="color:red;display:none;font-size:11px;">Below Normal</span>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mmolL'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_glucose_mmolL'; ?>"
                                                                               class="form-control"
                                                                               style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off"
                                                                               maxlength="15" value="">mmol/L
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END RAnDOM BLOOD SUGAR (RBS)*/

                                                            /*START ELECTROCARDIOGRAM (ECG)*/
                                                            if ($pDiagnosticID == '9') { ?>
                                                                <tr>
                                                                    <td style="vertical-align: text-top"><label style="font-style: italic; font-weight: normal;">Lab Results</label></td>
                                                                    <td colspan="2">
                                                                        <input type="radio"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ecg'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>" value="1"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="disableID('<?php echo "diagnostic_".$pDiagnosticID."_ecg_remarks"; ?>');" >
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">Essentially Normal</label>

                                                                        <input type="radio"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ecg'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>"
                                                                               value="2"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="enableID('<?php echo "diagnostic_".$pDiagnosticID."_ecg_remarks"; ?>');setFocus('<?php echo "diagnostic_".$pDiagnosticID."_ecg_remarks"; ?>');" >
                                                                        <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">With Findings</label>

                                                                        <input type="text"
                                                                               name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ecg_remarks'; ?>"
                                                                               id="<?php echo 'diagnostic_'.$pDiagnosticID.'_ecg_remarks'; ?>"
                                                                               class="form-control"
                                                                               style="width: 200px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                               autocomplete="off" value="" required disabled>
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END ELECTROCARDIOGRAM (ECG)*/

                                                            /*START PAP SMEAR*/
                                                            if ($pDiagnosticID == '13') { ?>
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <table>
                                                                            <tr>
                                                                                <td><label for="advice_remarks">Findings:</label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                        <textarea name="<?php echo 'diagnostic_'.$pDiagnosticID.'_papsSmearFindings'; ?>"
                                                                                  id="<?php echo 'diagnostic_'.$pDiagnosticID.'_papsSmearFindings'; ?>"
                                                                                  class="form-control"
                                                                                  style="width: 500px; color: #000; margin: 0px 10px 20px 0px; text-transform: uppercase; resize: none;"
                                                                                  autocomplete="off"
                                                                                  rows="3"
                                                                        ></textarea>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="impression_remarks">Impression:</label></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>
                                                                        <textarea name="<?php echo 'diagnostic_'.$pDiagnosticID.'_papsSmearImpression'; ?>"
                                                                                  id="<?php echo 'diagnostic_'.$pDiagnosticID.'_papsSmearImpression'; ?>"
                                                                                  class="form-control"
                                                                                  style="width: 500px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;"
                                                                                  autocomplete="off"
                                                                                  rows="3"
                                                                        ></textarea>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END PAP SMEAR*/

                                                            /*START ORAL GLUCOSE TOLERANCE*/
                                                            if ($pDiagnosticID == '14') { ?>
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <table>
                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_examination'; ?>" style="font-style: normal; font-weight: bold;">Examination</label>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_fasting_mg'; ?>" style="font-style: normal; font-weight: normal;">Fasting</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_fasting_mg'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_fasting_mg'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="15"
                                                                                           value=""
                                                                                    />mg/dL
                                                                                </td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_fasting_mmol'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_fasting_mmol'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 20px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="15"
                                                                                           value=""
                                                                                    />mmol/L
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_oneHr_mg'; ?>" style="font-style: normal; font-weight: normal;">OGTT (1 Hour)</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_oneHr_mg'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_oneHr_mg'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"   
                                                                                           maxlength="15"
                                                                                           value=""
                                                                                    />mg/dL
                                                                                </td><td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_oneHr_mmol'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_oneHr_mmol'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 20px; text-transform: uppercase"
                                                                                           autocomplete="off"  
                                                                                           maxlength="15"
                                                                                           value=""
                                                                                    />mmol/L
                                                                                </td>
                                                                            </tr>

                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_twoHr_mg'; ?>" style="font-style: normal; font-weight: normal;">OGTT (2 Hours)</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_twoHr_mg'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_twoHr_mg'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="15"
                                                                                           value=""
                                                                                    />mg/dL
                                                                                </td><td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_twoHr_mmol'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_twoHr_mmol'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100px; color: #000; margin: 3px 10px 0px 20px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           maxlength="15"
                                                                                           value=""
                                                                                    />mmol/L
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END ORAL GLUCOSE TOLERANCE*/ ?>

                                                            <?php
                                                            /* START CREATININE */
                                                            if ($pDiagnosticID == '8') {
                                                            ?>
                                                              <tr>
                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_creatinine_mgdl'; ?>" style="font-style: normal; font-weight: normal;">Result</label></td>
                                                                <td colspan="2">
                                                                    <input type="text"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_creatinine_mgdl'; ?>"
                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_creatinine_mgdl'; ?>"
                                                                           class="form-control"
                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                           autocomplete="off"
                                                                           maxlength="5"
                                                                           value=""
                                                                    />mg/dL
                                                                </td>                                                            
                                                              </tr>
                                                           <?php } /*END CREATININE */ ?>

                                                           <?php
                                                            /* START FECAL OCCULT BLOOD */
                                                            if ($pDiagnosticID == '15') {
                                                            ?>
                                                              <tr>
                                                                  <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_fobt'; ?>" style="font-style: normal; font-weight: normal;">Result</label></td>
                                                                  <td>
                                                                      <input type="radio"
                                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_fobt'; ?>"
                                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>" value="P"
                                                                             style="cursor: pointer; float: left;"/>                                                                       
                                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">Positive</label>

                                                                      <input type="radio"
                                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_fobt'; ?>"
                                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>"
                                                                             value="N"
                                                                             style="cursor: pointer; float: left;"/>                                                                       
                                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">Negative</label>
                                                                 </td>    
                                                              </tr>
                                                           <?php } /*END FECAL OCCULT BLOOD */ ?>

                                                           <?php
                                                            /* START HbA1c */
                                                            if ($pDiagnosticID == '18') {
                                                            ?>
                                                              <tr>
                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_hba1c_mmol'; ?>" style="font-style: normal; font-weight: normal;">Result</label></td>
                                                                <td colspan="2">
                                                                    <input type="text"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_hba1c_mmol'; ?>"
                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_hba1c_mmol'; ?>"
                                                                           class="form-control"
                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                           autocomplete="off"
                                                                           maxlength="5"
                                                                           value=""
                                                                    />mmol/mol
                                                                </td>                                                            
                                                              </tr>
                                                           <?php } /*END HbA1c */ ?>

                                                           <?php
                                                            /* START PPD Test */
                                                            if ($pDiagnosticID == '17') {
                                                            ?>
                                                              <tr>
                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_ppdt'; ?>" style="font-style: normal; font-weight: normal;">Result</label></td>
                                                                <td>
                                                                    <input type="radio"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ppdt'; ?>"
                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>" value="P"
                                                                           style="cursor: pointer; float: left;margin-left: 10px"/>                                                                       
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_yes'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 4px 0px 0px 2px ">Positive</label>

                                                                    <input type="radio"
                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_ppdt'; ?>"
                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>"
                                                                           value="N"
                                                                           style="cursor: pointer; float: left;margin-left: 10px;"/>                                                                       
                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_no'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 4px 0px 0px 2px">Negative</label>
                                                               </td>    
                                                            </tr>
                                                           <?php } /*END PPD Test */ 

                                                           /*START OTHERS 99*/
                                                            if ($pDiagnosticID == '99') { ?>
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <table style="width: 100%">
                                                                            <tr>
                                                                                <td colspan="3">
                                                                                    <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_examination'; ?>" style="font-style: normal; font-weight: bold;">Examination</label>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td><label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_fasting_mg'; ?>" style="font-style: normal; font-weight: normal;">Result</label></td>
                                                                                <td colspan="2">
                                                                                    <input type="text"
                                                                                           name="<?php echo 'diagnostic_'.$pDiagnosticID.'_oth1'; ?>"
                                                                                           id="<?php echo 'diagnostic_'.$pDiagnosticID.'_oth1'; ?>"
                                                                                           class="form-control"
                                                                                           style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                                           autocomplete="off"
                                                                                           value=""
                                                                                    />
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            <?php } /*END OTHERS 1*/  ?>
                                                        </table>
                                                    </fieldset>
                                                </td>
                                            </tr>
                                            <?php  } ?> <!--END CHECK DIAGNOSTIC EXAMINATION-->
                                        </table>
                                    </fieldset>
                                    <div style="text-align: center;margin-top: 25px;">
                                        <input type="submit"
                                             name="btnSave"
                                             id="btnSave"
                                             class="btn btn-success"
                                             style="margin-left: 10px;"
                                             value="Save Record"
                                         >
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div align="left">
                      <font color="red" style="font-size: 10px; font-family: Verdana, Geneva, sans-serif;">
                        NOTE: In generation of the Konsulta XML Report for the encoded laboratory/imaging results, always based in the Consultation Date of the applicable transaction.
                      </font>
                    </div>
                </td>
            </tr>
        </table>
        <br/><br/>

        </form>

    </div>
</div>
<?php
    include('footer.php');
?>
<script>
    $(window).load(function() {
        $("#wait_image").fadeOut("slow");
    });

    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $("#pSOAPDate").mask("99/99/9999");

    $("#pSOAPDate").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#pDispensedDate").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_1_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_2_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_3_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_4_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_5_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_6_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_7_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_8_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_9_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_13_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_14_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_15_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_17_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_18_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });

    //plan and management
    var chkCBC = $('#diagnostic_1_doctorYes').is(":checked");
    var chkUrinalysis = $('#diagnostic_2_doctorYes').is(":checked");
    var chkFecalysis = $('#diagnostic_3_doctorYes').is(":checked");
    var chkXray = $('#diagnostic_4_doctorYes').is(":checked");
    var chkSputum = $('#diagnostic_5_doctorYes').is(":checked");
    var chkLipid = $('#diagnostic_6_doctorYes').is(":checked");
    var chkFBS = $('#diagnostic_7_doctorYes').is(":checked");
    var chkCreatinine = $('#diagnostic_8_doctorYes').is(":checked");
    var chkECG = $('#diagnostic_9_doctorYes').is(":checked");
    var chkPaps = $('#diagnostic_13_doctorYes').is(":checked");
    var chkOTGG = $('#diagnostic_14_doctorYes').is(":checked");
    var chkFOBT = $('#diagnostic_15_doctorYes').is(":checked");
    var chkPPDT = $('#diagnostic_17_doctorYes').is(":checked");
    var chkHbA1C = $('#diagnostic_18_doctorYes').is(":checked");
    var chkRBS = $('#diagnostic_19_doctorYes').is(":checked");

    var chkCBCPxRq = $('#diagnostic_1_patientRQ').is(":checked");
    var chkUrinalysisPxRq = $('#diagnostic_2_patientRQ').is(":checked");
    var chkFecalysisPxRq = $('#diagnostic_3_patientRQ').is(":checked");
    var chkXrayPxRq = $('#diagnostic_4_patientRQ').is(":checked");
    var chkSputumPxRq = $('#diagnostic_5_patientRQ').is(":checked");
    var chkLipidPxRq = $('#diagnostic_6_patientRQ').is(":checked");
    var chkFBSPxRq = $('#diagnostic_7_patientRQ').is(":checked");
    var chkCreatininePxRq = $('#diagnostic_8_patientRQ').is(":checked");
    var chkECGPxRq = $('#diagnostic_9_patientRQ').is(":checked");
    var chkPapsPxRq = $('#diagnostic_13_patientRQ').is(":checked");
    var chkOTGGPxRq = $('#diagnostic_14_patientRQ').is(":checked");
    var chkFOBTPxRq = $('#diagnostic_15_patientRQ').is(":checked");
    var chkPPDTPxRq = $('#diagnostic_17_patientRQ').is(":checked");
    var chkHbA1CPxRq = $('#diagnostic_18_patientRQ').is(":checked");
    var chkRBSPxRq = $('#diagnostic_19_patientRQ').is(":checked");

    var statusCBC = $('#diagnostic_1_status').val();
    var statusUrinalysis = $('#diagnostic_2_status').val();
    var statusFecalysis = $('#diagnostic_3_status').val();
    var statusXray = $('#diagnostic_4_status').val();
    var statusSputum = $('#diagnostic_5_status').val();
    var statusLipid = $('#diagnostic_6_status').val();
    var statusFBS = $('#diagnostic_7_status').val();
    var statusCreatinine = $('#diagnostic_8_status').val();
    var statusECG = $('#diagnostic_9_status').val();
    var statusPaps = $('#diagnostic_13_status').val();
    var statusOTGG = $('#diagnostic_14_status').val();
    var statusFOBT = $('#diagnostic_15_status').val();
    var statusPPDT = $('#diagnostic_17_status').val();
    var statusHbA1C = $('#diagnostic_18_status').val();
    var statusRBS = $('#diagnostic_19_status').val();

    if((chkCBC == true || chkCBCPxRq == true) && statusCBC != "D"){
      document.getElementById('div_diagnostic_1_header').style.display = '';
      document.getElementById('div_diagnostic_1_form').style.display = '';
    }

    if((chkUrinalysis == true || chkUrinalysisPxRq == true) && statusUrinalysis != "D"){
      document.getElementById('div_diagnostic_2_header').style.display = '';
      document.getElementById('div_diagnostic_2_form').style.display = '';
    }

    if((chkFecalysis == true || chkFecalysisPxRq == true) && statusFecalysis != "D"){
      document.getElementById('div_diagnostic_3_header').style.display = '';
      document.getElementById('div_diagnostic_3_form').style.display = '';
    }

    if((chkXray == true || chkXrayPxRq == true) && statusXray != "D"){
      document.getElementById('div_diagnostic_4_header').style.display = '';
      document.getElementById('div_diagnostic_4_form').style.display = '';
    }

    if((chkSputum == true || chkSputumPxRq == true) && statusSputum != "D"){
      document.getElementById('div_diagnostic_5_header').style.display = '';
      document.getElementById('div_diagnostic_5_form').style.display = '';
    }

    if((chkLipid == true || chkLipidPxRq == true) && statusLipid != "D"){
      document.getElementById('div_diagnostic_6_header').style.display = '';
      document.getElementById('div_diagnostic_6_form').stle.display = '';
    }

    if((chkFBS == true || chkFBSPxRq == true) && statusFBS != "D"){
      document.getElementById('div_diagnostic_7_header').style.display = '';
      document.getElementById('div_diagnostic_7_form').style.display = '';
    }

    if((chkCreatinine == true || chkCreatininePxRq == true) && statusCreatinine != "D"){
      document.getElementById('div_diagnostic_8_header').style.display = '';
      document.getElementById('div_diagnostic_8_form').style.display = '';
    }

    if((chkECG == true || chkECGPxRq == true) && statusECG != "D"){
      document.getElementById('div_diagnostic_9_header').style.display = '';
      document.getElementById('div_diagnostic_9_form').style.display = '';
    }

    if((chkPaps == true || chkPapsPxRq == true) && statusPaps != "D"){
      document.getElementById('div_diagnostic_13_header').style.display = '';
      document.getElementById('div_diagnostic_13_form').style.display = '';
    }

    if((chkOTGG == true || chkOTGGPxRq == true) && statusOTGG != "D"){
      document.getElementById('div_diagnostic_14_header').style.display = '';
      document.getElementById('div_diagnostic_14_form').style.display = '';
    }

    if((chkFOBT == true || chkFOBTPxRq == true) && statusPPDT != "D"){
      document.getElementById('div_diagnostic_15_header').style.display = '';
      document.getElementById('div_diagnostic_15_form').style.display = '';
    }

    if((chkPPDT == true || chkPPDTPxRq == true) && statusUrinalysis != "D"){
      document.getElementById('div_diagnostic_17_header').style.display = '';
      document.getElementById('div_diagnostic_17_form').style.display = '';
    }

    if((chkHbA1C == true || chkHbA1CPxRq == true) && statusHbA1C != "D"){
      document.getElementById('div_diagnostic_18_header').style.display = '';
      document.getElementById('div_diagnostic_18_form').style.display = '';
    }

    if((chkRBS == true || chkRBSPxRq == true) && statusRBS != "D"){
      document.getElementById('div_diagnostic_19_header').style.display = '';
      document.getElementById('div_diagnostic_19_form').style.display = '';
    } 

</script>
