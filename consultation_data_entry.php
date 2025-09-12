<?php
/**
 * Created by PhpStorm.
 * User: ZNL
 * Date: 1/18/2018
 * Time: 4:32 PM
 */
    $page = 'consultation';
    include('header.php');
    include('menu.php');
    checkLogin();


    /* START GET PATIENT RECORD BEFORE CONSULTATION*/
    if(isset($_GET['case_no'])){
        $pCaseNo = $_GET['case_no'];
        $descPatientInfo = getPatientInfoConsultation($pCaseNo);

        if(!$descPatientInfo) {
            echo "<script>alert('Invalid Case Number!'); window.location='consultation_search.php';</script>";
        }

        // Get Consultation Record
        $getPrevTransNo = getPrevConsultationTransNo($pCaseNo);
        $prevTransNo = $getPrevTransNo["TRANS_NO"];
        $getPrevConsultation = getPrevConsultationRecord($prevTransNo);
        $chiefComplaintList = explode(";", $getPrevConsultation["SIGNS_SYMPTOMS"]); 
        // $descPemiscInfo = getPatientPemiscRecord($prevTransNo,$getUpdCnt);
        // $descAssessmentICDDiagnosis =getPatientAssessmentRecord($prevTransNo,$getUpdCnt);
        $descDiagnostic = getPatientDiagnosticRecord($prevTransNo,$getUpdCnt);
        // $descManagement = getPatientManagementRecord($pSoapTransNo,$getUpdCnt);
        // $descMedicine = getPatientSoapMedicine($hsa_transNo, $getUpdCnt);

        /*GET LABS RESULT BASED ON ESSENTIAL SERVICES*/
        // latest lab results
        $getPXRecordCBCResults = getLabCbc($pCaseNo);
        $getPXRecordCreatineResults = getLabCreatinine($pCaseNo);
        $getPXRecordUrinalysisResults = getLabUrinalysis($pCaseNo);
        $getPXRecordFecalysisResults = getLabFecalysis($pCaseNo);
        $getPXRecordChestXrayResults = getLabChestXray($pCaseNo);
        $getPXRecordSputumResults = getLabSputum($pCaseNo);
        $getPXRecordLipidResults = getLabLipidProfile($pCaseNo);
        $getPXRecordFbsResults = getLabFbs($pCaseNo);
        $getPXRecordEcgResults = getLabEcg($pCaseNo);
        $getPXRecordOgttResults = getLabOgtt($pCaseNo);
        $getPXRecordPapSmearResults = getLabPapSmear($pCaseNo);
        $getPXRecordFOBResults = getLabFecalOccultBlood($pCaseNo);
        $getPXRecordHbA1cResults = getLabHba1c($pCaseNo);
        $getPXRecordPPDTestResults = getLabPPDTest($pCaseNo);
        $getPXRecordRbsResults = getLabRbs($pCaseNo); //v1.2

    } 
 
    /* END GET PATIENT RECORD BEFORE CONSULTATION*/

    /*START GET CONSULTATION TRANSACTION NUMBER TO VIEW/EDIT/UPDATE RECORD*/
    if($prevTransNo){
        // $pSoapTransNo = $_GET['pSoapTransNo'];
        $getUpdCntSoap = getUpdCntConsultation($prevTransNo);
        $getUpdCnt = $getUpdCntSoap['UPD_CNT'];
        // $descPatientInfo = getPatientConsultationRecord($prevTransNo,$getUpdCnt);
        $descPemiscInfo = getPatientPemiscRecord($prevTransNo,$getUpdCnt);
        $descAssessmentICDDiagnosis =getPatientAssessmentRecord($prevTransNo,$getUpdCnt);
        $descDiagnostic = getPatientDiagnosticRecord($prevTransNo,$getUpdCnt);
        $descManagement = getPatientManagementRecord($prevTransNo,$getUpdCnt);
        $descMedicine = getPatientSoapMedicine($prevTransNo, $getUpdCnt);

        //get physical exam
        $px_data = getPatientPepertPrevTransNo($prevTransNo);
        $descPemiscInfo = getPatientPemicsPrevTransNo($prevTransNo);

        
        if(!$descPatientInfo) {
            echo "<script>alert('Invalid Transaction Number!'); window.location='consultation_search.php';</script>";
        }
    }/* END GET CONSULTATION TRANSACTION NUMBER TO VIEW/EDIT/UPDATE RECORD*/
    else {
        //get physical exam
        $px_data = getPatientHsaRecordForSOAP($pCaseNo);
        $descPemiscInfo = getPatientHSAPemiscRecorForSOAP($pCaseNo);
    }
    
    /* END GET COMPUTATION OF AGE */

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


    /*INSERT INITIAL DATA OF CONSULTATION*/
    if(isset($_POST['saveClientSoap'])){
        $cntChiefComplaint = count($_POST["complaint"]);
        $x=1;
        foreach($_POST["complaint"] as $complaints) {
            if($x==$cntChiefComplaint) {
                $complaint .= $complaints;
            }
            else {
                $complaint .= $complaints.";";
            }
            $x++;
        }
        $_POST["pChiefComplaint"] = $complaint;

        $cntObservation = count($_POST["observation"]);
        $x=1;
        foreach($_POST["observation"] as $observations) {
            if($x==$cntObservation) {
                $observation .= $observations;
            }
            else {
                $observation .= $observations.";";
            }
            $x++;
        }
        $_POST["pObservation"] = $observation;

        $_POST['pUpdCntSoap'] = 0;
        saveConsultationInfo($_POST);

    }

    /*UPDATE PREVIOUS DATA OF CONSULTATION */
    if(isset($_POST['updateClientSoap'])){
        $_POST['pSoapTransNum'] = $_GET["pSoapTransNo"];

        $cntChiefComplaint = count($_POST["complaint"]);
        $x=1;
        foreach($_POST["complaint"] as $complaints) {
            if($x==$cntChiefComplaint) {
                $complaint .= $complaints;
            }
            else {
                $complaint .= $complaints.";";
            }
            $x++;
        }
        $_POST["pChiefComplaint"] = $complaint;

        $cntObservation = count($_POST["observation"]);
        $x=1;
        foreach($_POST["observation"] as $observations) {
            if($x==$cntObservation) {
                $observation .= $observations;
            }
            else {
                $observation .= $observations.";";
            }
            $x++;
        }
        $_POST["pObservation"] = $observation;
        /*Get Updated Count in Profiling/HSA*/
        $getUpdCntSoap = getUpdCntConsultation($pSoapTransNo);
        $getUpdCnt = $getUpdCntSoap['UPD_CNT'] + 1;
        $_POST['pUpdCntSoap'] = $getUpdCnt;

        if(true){
            if(!isset($_POST["pSoapOTP"]) && !empty($_POST["pSoapOTP"])) {
                saveConsultationInfo($_POST);
            }
        }
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
        <form action="" name="consultationForm" method="POST" onsubmit="return validateSoapForm();">
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
                            <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
                              <table border="0" style="width: 100%;" class="table-condensed">
                                <col width="50%">
                                <col width="50%">
                                <tr>
                                    <td colspan="2">                                                        
                                        <input type="radio"
                                             name="walkedInChecker"
                                             id="walkedInChecker_true"
                                             value="N"   
                                             style="cursor: pointer; float: left;"
                                             onclick="setDisabled('<?php echo "pSoapOTP";?>', false);"
                                             checked="checked"                                                         
                                        />
                                        <label for="walkedInChecker_true" style="font-size:14px;font-weight: bold; cursor: pointer; float: left; margin: 0px 10px 0px 5px; ">
                                        Walk-in clients with Authorization Transaction Code (ATC)
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                <td>
                                  <label style="color:red;">*</label><label for="pSoapOTP">Authorization Transaction Code:</label>
                                  <br/>
                                  <input type="text"
                                         name="pSoapOTP"
                                         id="pSoapOTP"
                                         class="form-control"
                                         style="width: 15%; color: #000; margin: 0px 10px 0px 0px;width: 100%;"
                                         minlength="4"
                                         maxlength="10"
                                         autocomplete="off"
                                         placeholder="Authorization Transaction Code (ATC)"
                                         <?php if($pSoapTransNo != null){ ?>
                                         value="<?php echo $descPatientInfo["SOAP_OTP"]; ?>"
                                         readonly
                                         <?php } else{?>
                                         value=""
                                         autofocus
                                         <?php } ?>
                                  />
                                  </td>
                                  </tr>
                                  <tr><td colspan="2"><label style="margin-top:15px;font-size: 11px;font-style: italic;color:red;">Note: ATC should be used within the Consultation Date.</label></td></tr>
                                  <tr>
                                      <td colspan="2">                                                        
                                          <input type="radio"
                                               name="walkedInChecker"
                                               id="walkedInChecker_false"
                                               value="Y"   
                                               style="cursor: pointer; float: left;"
                                               onclick="setDisabled('<?php echo "pSoapOTP";?>', true);"                                                         
                                          />
                                          <label for="walkedInChecker_false" style="font-size:14px;font-weight: bold; cursor: pointer; float: left; margin: 0px 10px 0px 5px; ">
                                          Walk-in clients without ATC
                                          </label>
                                      </td>
                                  </tr>
                                </table>
                            </fieldset>

                            <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
                              <table border="0" style="width: 100%;" class="table-condensed">
                                <tr>
                                    <td>
                                        <label style="color:red;">*</label><label for="pEffYear">Effectivity Year:</label>
                                        <br/>
                                        <input type="text"
                                               name="pEffYear"
                                               id="pEffYear"
                                               class="form-control"
                                                style="width: 50%"
                                               value="<?php echo $descPatientInfo['EFF_YEAR']; ?>"
                                               readonly
                                        />
                                  </td>
                                </tr>
                                <tr>                                  
                                  <td>
                                      <label style="color:red;">*</label><label for="pCoPayment">Co-payment:</label>
                                      <br/>
                                      Php   <input type="text"
                                             id="pCoPayment"
                                             name="pCoPayment"
                                             value=""
                                             class="form-control"
                                             style="width: 47%"
                                             placeholder="Co-payment"
                                             maxlength="12" 
                                             onkeypress="return isNumberKeyWithTwoDecimalKey(event,'<?php echo "pCoPayment";?>');"
                                      />                                                    
                                  </td>
                                </tr>
                                <tr>
                                    <td><label style="color: red">*</label><label for="pSOAPDate">Consultation Date</label></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="text"
                                               name="pSOAPDate"
                                               id="pSOAPDate"
                                               class="datepicker form-control"
                                               value="<?php if($pSoapTransNo != null){ echo date('m/d/Y',strtotime($descPatientInfo['SOAP_DATE']));} ?>"
                                               style="width: 50%;"
                                               autocomplete="off"
                                               placeholder="mm/dd/yyyy"
                                        />
                                    </td>
                                </tr>
                              </table>
                            </fieldset>

                            <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
                                <table style="margin-top: 5px; text-align: left;width:100%">
                                    <tr>
                                        <td colspan="4" class="alert alert-success"><b><u>Client Information</u></b></td>
                                    </tr>
                                </table>
                                <table  style="margin-top: 5px; text-align: left;">
                                    <tr>
                                        <td><label>Case No:</label></td>
                                        <td><label>Date of Registration:</label></td>
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
                                                   name="pEnlistmentDate"
                                                   id="pEnlistmentDate"
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
                            <li class="active" id="list1"><a href="#tab1" data-toggle="tab" class="" style="text-align: center;">1<br>Subjective/History of Illness</a></li>
                            <li class="" id="list2"><a href="#tab2" data-toggle="tab" class="" style="text-align: center;">2<br>Objective/Physical Examination</a></li>
                            <li class="" id="list3"><a href="#tab3" data-toggle="tab" class="" style="text-align: center;">3<br>Assessment/Diagnosis</a></li>
                            <li class="" id="list4"><a href="#tab4" data-toggle="tab" class="" style="text-align: center;">4<br>Plan/Management</a></li>
                            <!-- <li class="" id="list5"><a href="#tab5" data-toggle="tab" class="" style="text-align: center;">5<br>Laboratory/Imaging Results</a></li>                           
                            <li class="" id="list6"><a href="#tab6" data-toggle="tab" class="" style="text-align: center;">6<br>Dispensing Medicine</a></li> -->
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="tab1">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Subjective/History of Illness</h3>
                                </div>
                                <div class="panel-body">
                                    <div class="tab-content">
                                        <div class="tab-pane fade in active" id="subjectiveTab">
                                            <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                                <table width="100%">
                                                    <tr>
                                                        <td class="alert alert-success"><b>SUBJECTIVE/HISTORY OF ILLNESS</b></td>
                                                    </tr>
                                                </table>

                                                <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                    <tr style="height: 5px;">
                                                        <td colspan="2"></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2"><label style="color:red">*</label><strong><u>A. Chief Complaint</u></strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <?php
                                                            $pLibComplaint = listComplaint();
                                                            for ($i = 0; $i < count($pLibComplaint); $i++) {
                                                            ?>
                                                            <input type="checkbox"
                                                                   name="complaint[]"
                                                                   id="<?php echo 'symptom_'.$pLibComplaint[$i]['SYMPTOMS_ID'];?>"
                                                                   value="<?php echo $pLibComplaint[$i]['SYMPTOMS_ID'];?>"
                                                                   style="cursor: pointer; float: left;"
                                                                   onclick="checkPainChiefComplaint();"
                                                                   <?php 
                                                                   foreach ($chiefComplaintList as $chiefComplaint) {
                                                                       if ($chiefComplaint == $pLibComplaint[$i]['SYMPTOMS_ID']) { 
                                                                        echo "checked='checked'";
                                                                       } 
                                                                   }
                                                                   ?>
                                                            />
                                                            <label for="<?php echo 'symptom_'.$pLibComplaint[$i]['SYMPTOMS_ID'];?>" style="cursor: pointer;float:left; font-weight: normal;margin: 4px 0px 0px 2px;"><?php echo $pLibComplaint[$i]['SYMPTOMS_DESC'];?></label>

                                                            <?php
                                                                if($pLibComplaint[$i]['SYMPTOMS_ID'] == '38'){ ?>
                                                                    <input type="text"
                                                                           name="pPainSite"
                                                                           id="pPainSite"
                                                                           class="form-control"
                                                                           style="width: 465px; color: #000; margin: 0px 10px 0px 10px; text-transform: uppercase; resize: none;display:none"
                                                                           placeholder="Site of Pain"
                                                                           autocomplete="off"
                                                                           value="<?php echo $getPrevConsultation['PAIN_SITE']; ?>"
                                                                           maxlength="500"
                                                                    />
                                                                    <br/>
                                                        <?php
                                                                }
                                                                else{
                                                                   echo '<br/>';
                                                                }
                                                            }
                                                        ?>
                                                            <input type="checkbox"
                                                                   name="complaint[]"
                                                                   id="symptom_X"
                                                                   value="X"
                                                                   style="cursor: pointer; float: left;"
                                                                   onclick="checkOtherChiefComplaint();"
                                                                   <?php 
                                                                   foreach ($chiefComplaintList as $chiefComplaint) {
                                                                       if ($chiefComplaint == "X") { 
                                                                        echo "checked='checked'";
                                                                       } 
                                                                   }
                                                                   ?>
                                                            />
                                                            <label for="symptom_X" style="cursor: pointer;float:left;margin: 4px 0px 0px 2px; font-weight: normal;">OTHERS</label><br/>
                                                            <input type="text"
                                                                   name="pOtherChiefComplaint"
                                                                   id="pOtherChiefComplaint"
                                                                   class="form-control"
                                                                   style="width: 500px; color: #000; margin: 0px 10px 0px 10px; text-transform: uppercase; resize: none;"
                                                                   placeholder="OTHERS"
                                                                   autocomplete="off"
                                                                   value="<?php echo $getPrevConsultation['OTHER_COMPLAINT']; ?>"
                                                                   disabled
                                                            />
                                                        </td>
                                                    </tr>
                                                    <tr style="height: 5px;">
                                                        <td colspan="2"></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2"><strong><u>B. History of Illness</u></strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2">
                                                            <textarea name="pIllnessHistory"
                                                                      id="pIllnessHistory"
                                                                      class="form-control"
                                                                      style="width: 500px; color: #000; margin: 0px 10px 0px 10px; text-transform: uppercase; resize: none;"
                                                                      autocomplete="off"
                                                                      placeholder="History of Illness"
                                                                      rows="3"><?php echo $getPrevConsultation['ILLNESS_HISTORY']; ?></textarea>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                            <div style="text-align: center;">
                                              <input type="button"
                                                     class="btn btn-primary"
                                                     name="nxtTab2"
                                                     id="nxtTab2"
                                                     value="Next"
                                                     title="Go to Objective/Physical Examination"
                                                     style="margin: 10px 0px 0px 0px;"
                                                     onclick="showTabConsultation('tab2');"
                                              />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab2">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Objective/Physical Examination</h3>
                                </div>
                                <div class="panel-body">
                                    <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                        <table width="100%">
                                            <tr>
                                                <td class="alert alert-success"><b>OBJECTIVE/PHYSICAL EXAMINATION</td>
                                            </tr>
                                        </table>

                                        <table style="margin: 5px 0px 0px 20px; text-align: left;" id="non_pedia">
                                            <tr>
                                                <td style="width: 100px;"><label style="color:red;">*</label><label>Blood Pressure</label></td>
                                                <td style="width:100px">
                                                    <table>
                                                        <tr>
                                                            <td>
                                                              <label class="form-inline">
                                                                <input name="pe_bp_u"
                                                                       id="pe_bp_u"
                                                                       class="form-control"
                                                                       value="<?php echo $px_data['SYSTOLIC']; ?>"
                                                                       style="width: 85px; color: #000; margin-bottom: 5px; "
                                                                       autocomplete="off"
                                                                       maxlength="4"
                                                                       placeholder="Systolic"
                                                                       onkeypress="return isNumberKey(event);"
                                                               />
                                                             </label>
                                                             </td>
                                                            <td>
                                                                &nbsp;&nbsp;/&nbsp;
                                                            </td>
                                                            <td>
                                                              <label class="form-inline">
                                                                <input name="pe_bp_l"
                                                                       id="pe_bp_l"
                                                                       class="form-control"
                                                                       value="<?php echo $px_data['DIASTOLIC']; ?>"
                                                                       style="width: 85px; color: #000; margin-bottom: 5px; "
                                                                       autocomplete="off"
                                                                       maxlength="4"
                                                                       placeholder="Diastolic"
                                                                       onkeypress="return isNumberKey(event);"
                                                                />
                                                              </label>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                </td>
                                                <td style="width: 150px;"><label>(mmHg)</label></td>
                                                <td style="width: 130px;"><?php if ($yearAge >= 2) { ?><label style="color:red;">*</label><?php } ?><label>Height</label></td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input name="txtPhExHeightCm"
                                                           id="txtPhExHeightCm"
                                                           class="form-control"
                                                           value="<?php echo $px_data['HEIGHT']; ?>"
                                                           style="width: 100px; color: #000; margin: 0px 10px 5px 0px; float: left;"
                                                           autocomplete="off"
                                                           maxlength="6"
                                                           placeholder="Height"
                                                           onkeypress="setValue('pe_height_inch', CmtoInch(this));"
                                                           onkeyup="setValue('pe_height_inch', CmtoInch(this));"
                                                           <?php if ($yearAge < 2) { ?>
                                                              disabled
                                                              value="0"
                                                          <?php } else { ?>
                                                              value=""
                                                          <?php } ?>
                                                    /> (cm)
                                                  </label>
                                                </td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input name="txtPhExHeightIn"
                                                           id="pe_height_inch"
                                                           class="form-control"
                                                           style="width: 100px; color: #000; margin: 0px 10px 5px 10px;"
                                                           autocomplete="off"
                                                           maxlength="6"
                                                           onkeypress="setValue('txtPhExHeightCm', InchToCm(this));"
                                                           onkeyup="setValue('txtPhExHeightCm', InchToCm(this));"
                                                           <?php if ($yearAge < 2) { ?>
                                                              disabled
                                                              value="0"
                                                          <?php } else { ?>
                                                              value=""
                                                          <?php } ?>
                                                    />(inch)
                                                  </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Heart Rate</label></td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input name="pe_hr"
                                                           id="pe_hr"
                                                           class="form-control"
                                                           value="<?php echo $px_data['HR']; ?>"
                                                           style="width: 185px; color: #000; margin: 0px 10px 5px 0px; "
                                                           autocomplete=""
                                                           maxlength="6"
                                                           placeholder="Heart Rate"
                                                           onkeypress="return isNumberKey(event);"
                                                    />
                                                  </label>
                                                </td>
                                                <td><label>(/min)</label></td>
                                                <td ><label style="color:red;">*</label><label>Weight</label></td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input name="txtPhExWeightKg"
                                                           id="txtPhExWeightKg"
                                                           class="form-control"
                                                           value="<?php echo $px_data['WEIGHT']; ?>"
                                                           style="width: 100px; color: #000; margin: 0px 10px 5px 0px; "
                                                           autocomplete="off"
                                                           maxlength="6"
                                                           placeholder="Weight"
                                                           onkeypress="setValue('pe_weight_lb', KgToLb(this));"
                                                           onkeyup="setValue('pe_weight_lb', KgToLb(this));"
                                                    />(kg)
                                                  </label>
                                                </td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input name="txtPhExWeightLb"
                                                           id="pe_weight_lb"
                                                           class="form-control"
                                                           value=""
                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 10px; "
                                                           maxlength="6"
                                                           autocomplete="off"
                                                           onkeypress="setValue('txtPhExWeightKg', LbToKg(this));"
                                                           onkeyup="setValue('txtPhExWeightKg', LbToKg(this));"
                                                    />(lb)
                                                  </label>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><label style="color:red;">*</label><label>Respiratoy Rate</label></td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input name="pe_rr"
                                                           id="pe_rr"
                                                           class="form-control"
                                                           value="<?php echo $px_data['RR']; ?>"
                                                           style="width: 185px; color: #000; margin: 0px 10px 0px 0px; "
                                                           autocomplete="off"
                                                           maxlength="6"
                                                           placeholder="Respiratoy Rate"
                                                           onkeypress="return isNumberKey(event);"
                                                    />
                                                  </label>
                                                </td>
                                                <td><label>(/min)</label></td>

                                                            
                                                <td><label style="color:red;">*</label><label for="pe_bmi">BMI:</label></td>
                                                <td>
                                                  <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExBMI'
                                                           id="txtPhExBMI"
                                                           maxlength="6"
                                                           style="width: 100px"
                                                           class='form-control'  
                                                           placeholder="BMI"   
                                                           readonly
                                                          <?php if ($yearAge <= 4) { ?>
                                                            value="<?php echo '0'; ?>"
                                                          <?php } else { ?>
                                                            value="<?php echo $px_data['BMI']; ?>"
                                                          <?php } ?>
                                                    />
                                                  </label>
                                                  <input type="button" 
                                                         class="btn btn-info btn-sm" 
                                                         value="Get BMI" 
                                                         onclick="computeBMI(this.form)" 
                                                         style="margin: 2px 0px 0px 2px;"
                                                         <?php if ($yearAge <= 4) { ?>                                                          
                                                          title="Not Applicable"
                                                          disabled
                                                         <?php } ?>
                                                   />
                                                </td>
                                                <td colspan="2">
                                                   <div id="bmiDescription" name="bmiDescription"></div>
                                                </td>
                                            </tr>
                                            <tr>                                                
                                                <td><label>Visual Acuity</label></td>
                                                <td colspan="2"> 
                                                  <label class="form-inline">                                                   
                                                    <input name="pe_visual_acuityL"
                                                           id="pe_visual_acuityL"
                                                           class="form-control"
                                                           value="<?php echo $px_data['LEFT_VISUAL_ACUITY']; ?>"
                                                           style="width: 88px; color: #000; margin: 5px 2px 0px 0px; "
                                                           maxlength="12"
                                                           autocomplete="off"
                                                           placeholder="Left Eye"
                                                    />/
                                                    <input name="pe_visual_acuityR"
                                                           id="pe_visual_acuityR"
                                                           class="form-control"
                                                           value="<?php echo $px_data['RIGHT_VISUAL_ACUITY']; ?>"
                                                           style="width: 88px; color: #000; margin: 5px 10px 0px 0px; "
                                                           maxlength="12"
                                                           autocomplete="off"
                                                           placeholder="Right Eye"
                                                    />
                                                  </label>
                                                </td>
                                                
                                                           
                                                <td><label style="color:red;">*</label><label for="pe_temp">Temperature:</label></td>
                                                <td>
                                                    <label class="form-inline">
                                                        <input type='text'
                                                               name='pe_temp'
                                                               id="pe_temp"
                                                               maxlength="6"
                                                               class='form-control'
                                                               placeholder="Temperature"
                                                               onkeypress="return isNumberWithDecimalKey(event);"
                                                               style="width: 100px;margin: 0px 10px 0px 0px;"
                                                               value="<?php echo $px_data['TEMPERATURE']; ?>"
                                                        /> (&#176;C)
                                                      </label>
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="margin: 5px 0px 0px 20px; text-align: left;" id="pedia_one">
                                            <tr>
                                                <td colspan="5" style="font-weight: bold; text-decoration: underline;">Pediatric Client aged 0-24 months</td>
                                            </tr>
                                            <tr>
                                                <td><label><?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?><font color="red">*</font><?php } ?>Length:</label></td>
                                                <td>&nbsp;&nbsp;</td>
                                                <td><label>Head Circumference:</label></td>
                                                <td>&nbsp;&nbsp;</td>
                                                <td><label>Skinfold Thickness:</label></td>
                                            </tr>
                                            <tr>
                                            <td>
                                                <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExLengthCm'
                                                           id="txtPhExLengthCm"
                                                           maxlength="6"
                                                           class='form-control'
                                                           placeholder="Length"
                                                           value="<?php echo $px_data['LENGTH']; ?>"
                                                          onkeypress="return isNumberWithDecimalKey(event);"
                                                        <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                    /> (cm)
                                                </label>
                                            </td>
                                            <td>&nbsp;&nbsp;</td>
                                            <td>
                                                <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExHeadCircCm'
                                                           id="txtPhExHeadCircCm"
                                                           maxlength="6"
                                                           class='form-control'
                                                           placeholder="Head Circumference"
                                                            onkeypress="return isNumberWithDecimalKey(event);"
                                                           value="<?php echo $px_data['HEAD_CIRC']; ?>"
                                                       <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                    /> (cm)
                                                </label>
                                            </td>
                                            <td>&nbsp;&nbsp;</td>
                                            <td>
                                                <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExSkinfoldCm'
                                                           id="txtPhExSkinfoldCm"
                                                           maxlength="6"
                                                           class='form-control'
                                                           placeholder="Skinfold Thickness"                 
                                                            value="<?php echo $px_data['SKIN_THICKNESS']; ?>"
                                                            onkeypress="return isNumberWithDecimalKey(event);"
                                                        <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                    /> (cm)
                                                </label>
                                            </td>
                                        </tr>                                                                        
                                        <tr>
                                            <td><label>Body Circumference:</label></td>
                                        </tr>
                                        <tr>
                                            <td><label>Waist</label></td>
                                            <td>&nbsp;&nbsp;</td>
                                            <td><label>Hip</label></td>
                                            <td>&nbsp;&nbsp;</td>
                                            <td><label>Limbs</label></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExBodyCircWaistCm'
                                                           id="txtPhExBodyCircWaistCm"
                                                           maxlength="6"
                                                           class='form-control'
                                                           placeholder="Waist"                           
                                                           value="<?php echo $px_data['WAIST']; ?>"
                                                            onkeypress="return isNumberWithDecimalKey(event);"
                                                        <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                    /> (cm)
                                                </label>
                                            </td>
                                            <td>&nbsp;&nbsp;</td>
                                            <td>
                                                <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExBodyCircHipsCm'
                                                           id="txtPhExBodyCircHipsCm"
                                                           maxlength="6"
                                                           class='form-control'
                                                            placeholder="Hip"                                       
                                                            value="<?php echo $px_data['HIP']; ?>"
                                                            onkeypress="return isNumberWithDecimalKey(event);"
                                                        <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                    /> (cm)
                                                </label>
                                            </td>
                                            <td>&nbsp;&nbsp;</td>
                                            <td>
                                                <label class="form-inline">
                                                    <input type='text'
                                                           name='txtPhExBodyCircLimbsCm'
                                                           id="txtPhExBodyCircLimbsCm"
                                                           maxlength="6"
                                                           class='form-control'
                                                            placeholder="Limbs"                                                 
                                                           value="<?php echo $px_data['LIMBS']; ?>"
                                                            onkeypress="return isNumberWithDecimalKey(event);"
                                                        <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                    /> (cm)
                                                </label>
                                            </td>                                                                            
                                          </tr>

                                          <tr>
                                            <td colspan="5"><label><?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?><font color="red">*</font><?php } ?>Middle and Upper Arm Circumference</label>
                                            </td>
                                          </tr>
                                          <tr>
                                              <td>
                                                  <label class="form-inline">
                                                      <input type='text'
                                                             name='txtPhExMidUpperArmCirc'
                                                             id="txtPhExMidUpperArmCirc"
                                                             maxlength="6"
                                                             class='form-control' 
                                                             placeholder='Middle and Upper Arm'   
                                                             onkeypress="return isNumberWithDecimalKey(event);"
                                                             value="<?php echo $px_data['WAIST']; ?>"
                                                          <?php if ($pAgeBracket == 'pedia-one' || $pAgeBracket == 'pedia-two') { ?>
                                                            enabled
                                                        <?php } else { ?>
                                                            disabled
                                                        <?php } ?>
                                                      /> (cm)
                                                  </label>
                                              </td>
                                          </tr>
                                        </table>

                                        <table style="margin-top: 15px;width:100%">
                                            <tr>
                                                <td class="alert alert-success"><b>PERTINENT FINDINGS PER SYSTEM</b></td>
                                            </tr>
                                        </table>

                                        <table>
                                            <tr id="heent_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">A. HEENT</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listHeents = listHeent();
                                                        foreach ($listHeents as $pLibHEENT) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="heent[]"
                                                                           id="<?php echo 'heent_'.$pLibHEENT['HEENT_ID'];?>"
                                                                           value="<?php echo $pLibHEENT['HEENT_ID'];?>"
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkHeent();"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['HEENT_ID'] == $pLibHEENT['HEENT_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    >
                                                                    <label for="<?php echo 'heent_'.$pLibHEENT['HEENT_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibHEENT['HEENT_DESC'];?></label><br/>
                                                                   <?php
                                                                      if($pLibHEENT['HEENT_ID'] == '99'){ ?>
                                                                      <textarea name="heent_remarks"
                                                                                id="heent_remarks"
                                                                                class="form-control"
                                                                                style="width: 100%; color: #000; margin: 0px 0px 0px 0px; text-transform: uppercase; resize: none;"
                                                                                autocomplete="off"
                                                                                maxlength="500"
                                                                                disabled
                                                                                rows="2"><?php echo $px_data['HEENT_REM']; ?></textarea>
                                                                      <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr id="chest_lungs_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">B. Chest/Breast/Lungs</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listChests = listChest();
                                                        foreach ($listChests as $pLibChest) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="chest[]"
                                                                           id="<?php echo 'chest_'.$pLibChest['CHEST_ID'];?>"
                                                                           value="<?php echo $pLibChest['CHEST_ID'];?>"
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkChestLungs();" 
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['CHEST_ID'] == $pLibChest['CHEST_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'chest_'.$pLibChest['CHEST_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibChest['CHEST_DESC'];?></label><br/>
                                                                    <?php
                                                                        if($pLibChest['CHEST_ID'] == '99'){ ?>
                                                                        <textarea name="chest_lungs_remarks" id="chest_lungs_remarks" class="form-control" style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['CHEST_REM']; ?></textarea>
                                                                        <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr id="heart_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">C. Heart</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listHearts = listHeart();
                                                        foreach ($listHearts as $pLibHeart) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="heart[]"
                                                                           id="<?php echo 'heart_'.$pLibHeart['HEART_ID'];?>"
                                                                           value="<?php echo $pLibHeart['HEART_ID'];?>"
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkHeart();"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['HEART_ID'] == $pLibHeart['HEART_ID']) { ?>
                                                                                            checked
                                                                                        <?php }
                                                                                    }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'heart_'.$pLibHeart['HEART_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibHeart['HEART_DESC'];?></label><br/>
                                                                    <?php
                                                                    if($pLibHeart['HEART_ID'] == '99'){ ?>
                                                                    <textarea name="heart_remarks" id="heart_remarks" class="form-control" style="width:100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['HEART_REM']; ?></textarea>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr id="abdomen_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">D. Abdomen</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listAbs = listAbdomen();
                                                        foreach ($listAbs as $pLibAbdomen) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="abdomen[]"
                                                                           id="<?php echo 'abdomen_'.$pLibAbdomen['ABDOMEN_ID'];?>"
                                                                           value="<?php echo $pLibAbdomen['ABDOMEN_ID'];?>"
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkAbdomen();"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['ABDOMEN_ID'] == $pLibAbdomen['ABDOMEN_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'abdomen_'.$pLibAbdomen['ABDOMEN_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibAbdomen['ABDOMEN_DESC'];?></label><br/>
                                                                    <?php
                                                                    if($pLibAbdomen['ABDOMEN_ID'] == '99'){ ?>
                                                                        <textarea name="abdomen_remarks" id="abdomen_remarks" class="form-control" style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['ABDOMEN_REM']; ?></textarea>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr id="gu_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">E. Genitourinary</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listGenitourinary = listGenitourinary();
                                                        foreach ($listGenitourinary  as $pLibGU) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="genitourinary[]"
                                                                           id="<?php echo 'gu_'.$pLibGU['GU_ID'];?>"
                                                                           value="<?php echo $pLibGU['GU_ID'];?>" 
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkGU();"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['GU_ID'] == $pLibGU['GU_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'gu_'.$pLibGU['GU_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibGU['GU_DESC'];?></label><br/>
                                                                    <?php
                                                                    if($pLibGU['GU_ID'] == '99'){ ?>
                                                                        <textarea name="gu_remarks" id="gu_remarks" class="form-control" style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['GU_REMARKS']; ?></textarea>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr id="rectal_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">F. Digital Rectal Examination</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listRectal = listDigitalRectal();
                                                        foreach ($listRectal as $pLibRectal) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="rectal[]"
                                                                           id="<?php echo 'rectal_'.$pLibRectal['RECTAL_ID'];?>"
                                                                           value="<?php echo $pLibRectal['RECTAL_ID'];?>" style="cursor: pointer; float: left;"
                                                                           onclick="checkRectal();disDigitalRectal(this.value);"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['RECTAL_ID'] == $pLibRectal['RECTAL_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'rectal_'.$pLibRectal['RECTAL_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibRectal['RECTAL_DESC'];?></label><br/>
                                                                   <?php
                                                                      if($pLibRectal['RECTAL_ID'] == '99'){ ?>
                                                                      <textarea name="rectal_remarks" id="rectal_remarks" class="form-control" style="width:100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['RECTAL_DESC']; ?></textarea>
                                                                      <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr id="skin_extremities_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">G. Skin/Extremities</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listSkinExtremities = listSkinExtremities();
                                                        foreach ($listSkinExtremities as $pLibExtremities) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="skinExtremities[]"
                                                                           id="<?php echo 'extremities_'.$pLibExtremities['SKIN_ID'];?>"
                                                                           value="<?php echo $pLibExtremities['SKIN_ID'];?>"
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkSkinExtrem();"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['SKIN_ID'] == $pLibExtremities['SKIN_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'extremities_'.$pLibExtremities['SKIN_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibExtremities['SKIN_DESC'];?></label><br/>
                                                                    <?php
                                                                    if($pLibExtremities['SKIN_ID'] == '99'){ ?>
                                                                    <textarea name="skinExtremities_remarks" id="extremities_remarks" class="form-control" style="width:100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['SKIN_REM']; ?></textarea>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr id="neuro_info">
                                                <td>
                                                    <h5><label style="color:red;">*</label><u style="font-weight: bold;">H. Neurological Examination</u></h5>
                                                    <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                        <?php
                                                        $listNeuro = listNeuro();
                                                        foreach ($listNeuro as $pLibNeuro) { ?>
                                                            <tr>
                                                                <td style="width: 250px;">
                                                                    <input type="checkbox"
                                                                           name="neuro[]"
                                                                           id="<?php echo 'neuro_'.$pLibNeuro['NEURO_ID'];?>"
                                                                           value="<?php echo $pLibNeuro['NEURO_ID'];?>"
                                                                           style="cursor: pointer; float: left;"
                                                                           onclick="checkNeuro();"
                                                                            <?php if($descPemiscInfo != null) {
                                                                                foreach($descPemiscInfo as $descPemiscInfos) {
                                                                                    if ($descPemiscInfos['NEURO_ID'] == $pLibNeuro['NEURO_ID']) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            }
                                                                            ?>
                                                                    />
                                                                    <label for="<?php echo 'neuro_'.$pLibNeuro['NEURO_ID'];?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;"><?php echo $pLibNeuro['NEURO_DESC'];?></label><br/>
                                                                    <?php
                                                                    if($pLibNeuro['NEURO_ID'] == '99'){ ?>
                                                                    <textarea name="neuro_remarks" id="neuro_remarks" class="form-control" style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;" autocomplete="off" rows="2" maxlength="500" disabled><?php echo $px_data['NEURO_REM']; ?></textarea>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </fieldset>
                                    <div style="text-align: center;">
                                      <input type="button"
                                             class="btn btn-primary"
                                             name="nxtTab3"
                                             id="nxtTab3"
                                             value="Next"
                                             title="Go to Assessment/Diagnosis"
                                             style="margin: 10px 0px 0px 0px;"
                                             onclick="showTabConsultation('tab3');"
                                      />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab3">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Assessment/Diagnosis</h3>
                                </div>
                                <div class="panel-body">
                                    <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                        <table width="100%">
                                            <tr>
                                                <td class="alert alert-success"><b>ASSESSMENT/DIAGNOSIS</b></td>
                                            </tr>
                                        </table>

                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                            <tr>
                                                <td style="width: 100px;"><label style="color: red">*</label><label>Diagnosis</label></td>
                                                <td>
                                                    <select class="chosen-select form-control" name="pICD" id="pICD" style="width: 500px; margin: 0px 10px 0px 0px;text-transform: uppercase">
                                                        <option value selected="selected"></option>
                                                        <?php
                                                        $pLibICD = listICD();
                                                        foreach ($pLibICD as $pLibraryICD) {
                                                            $icdCode = $pLibraryICD['ICD_CODE'];
                                                            $icdDesc = $pLibraryICD['ICD_DESC'];
                                                        ?>
                                                        <option value="<?php echo $icdCode; ?>"><?php echo $icdDesc.' - '.$icdCode; ?></option>
                                                        <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input name="add" class="btn btn-primary" style="width:120px;margin-left: 5px;" onclick="addDiagnosis('res/images/delete.png');" value="Add Diagnosis" id="add" title = "Add Diagnosis">
                                                </td>
                                            </tr>
                                        </table>

                                        <table style="margin: 5px 0px 0px 20px; width: 750px;" id="diagnosis_table" class="table">
                                            <tr>
                                                <td style="width: 20px; text-align: center;"><label>No.</label></td>
                                                <td style="width: 700px; text-align: left;"><label>Diagnosis Name</label></td>
                                                <td style="width: 30px; text-align: center;"></td>
                                            </tr>
                                            <?php
                                            if($prevTransNo != null) {
                                                for ($i = 0; $i < count($descAssessmentICDDiagnosis); $i++) {
                                                    $pSeqNo = $descAssessmentICDDiagnosis[$i]['SEQ_NO'];
                                                    $pICDCode = $descAssessmentICDDiagnosis[$i]['ICD_CODE'];
                                                    $pICDDesc = $descAssessmentICDDiagnosis[$i]['ICD_DESC'];
                                                    $icd = $pICDDesc .' - '. $pICDCode;
                                                    if ($i % 2 != 1) {
                                                        echo '<tr style="background-color: #FBFCC7;">';
                                                    } else {
                                                        echo '<tr>';
                                                    }
                                                    echo '<td>' . $pSeqNo . '</td>';
                                                    echo '<td style="text-align: left;">' . $pICDDesc . ' - ' . $pICDCode . '
                                                    <input type="hidden" value="' .$pICDCode. '" id="diagnosis_' .$pICDCode. '" name="diagnosis[]">
                                                    </td>';
                                                    echo '<td>';
                                                    echo ' <img src="res/images/delete.png" 
                                                            onclick="deleteRow(this)"
                                                            alt="Remove Diagnosis"
                                                            title="Remove Diagnosis"
                                                            style="width: 20px; height: 20px; cursor: pointer;">';
                                                    echo '</td>';
                                                    echo '</tr>';
                                                }
                                            }
                                          ?>
                                        </table>
                                    </fieldset>
                                    <div style="text-align: center;">
                                      <input type="button"
                                             class="btn btn-primary"
                                             name="nxtTab4"
                                             id="nxtTab4"
                                             value="Next"
                                             title="Go to Plan/Management"
                                             style="margin: 10px 0px 0px 0px;"
                                             onclick="showTabConsultation('tab4');"
                                      />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab4">
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
                                                  <col width="43%">
                                                  <col width="25%">
                                                  <col width="32%">
                                                  <tr style="text-align: center;font-size: 10px;">
                                                    <td style="font-weight: bold;">Laboratory/Imaging</td>
                                                    <td style="font-weight: bold;">Doctor Recommendation</td>
                                                    <td style="font-weight: bold;">Client</td>
                                                  </tr>
                                                  <?php
                                                  $pLibDiagnostic = listDiagnosisConsultation();
                                                  for ($i = 0; $i < count($pLibDiagnostic); $i++) {
                                                          $pDiagnosticDesc = $pLibDiagnostic[$i]['DIAGNOSTIC_DESC'];
                                                          $pDiagnosticID = $pLibDiagnostic[$i]['DIAGNOSTIC_ID'];
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
                                                        <?php 
                                                        //cbc
                                                        if ($pDiagnosticID == '1' &&  $yearAge <= 60) {
                                                          echo "&emsp;*";
                                                        }  

                                                        //fecalysis
                                                        if ($pDiagnosticID == '3' &&  ($yearAge >= 5 && $yearAge <= 19)) {
                                                          echo "&emsp;*";
                                                        }  

                                                        //urinalysis
                                                        if ($pDiagnosticID == '2' &&  $yearAge >= 10) {
                                                          echo "&emsp;*";
                                                        }  

                                                        //pap smear
                                                        if ($pDiagnosticID == '13' && ($yearAge >= 18 && $descPatientInfo['PX_SEX'] == 'F')) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //chest x-ray
                                                        if ($pDiagnosticID == '4' && $yearAge >= 10) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //fbs
                                                        if ($pDiagnosticID == '7' && $yearAge >= 40) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //lipid profile
                                                        if ($pDiagnosticID == '6' && $yearAge >= 40) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //fecal occult blood
                                                        if ($pDiagnosticID == '15' && $yearAge >= 50) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //creatinine
                                                        if ($pDiagnosticID == '8' && $yearAge >= 40) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //sputum
                                                        if ($pDiagnosticID == '5' && $yearAge > 60) {
                                                          echo "&emsp;*";
                                                        } 

                                                        //ogtt
                                                        if ($pDiagnosticID == '14' && $yearAge > 60) {
                                                          echo "&emsp;*";
                                                        } 
                                                        ?>
                                                        </label>
                                                    </td>
                                                    <td>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctor';?>"
                                                             value="Y"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorYes'; ?>"
                                                             style="cursor: pointer; float: left;margin-left: 10px"    
                                                             onclick="checkOtherDiagExam();"                            
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorYes'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">Yes</label>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctor';?>"
                                                             value="N"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorNo'; ?>"
                                                             style="float: left;"        
                                                             onclick="checkOtherDiagExam();"                                                            
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorNo'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">No</label>

                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctor';?>"
                                                             value="N"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorUnselect'; ?>"
                                                             style="float: left;"            
                                                             onclick="checkOtherDiagExam();"                                                       
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_doctorUnselect'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">Deselect</label>
                                                    </td>
                                                    <td>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_patient';?>"
                                                             value="RQ"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRQ'; ?>"
                                                             style="cursor: pointer; float: left;"
                                                             onclick="checkOtherDiagExam();"       
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRQ'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px; ">Request</label>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_patient';?>"
                                                             value="RF"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRF'; ?>"
                                                             style="cursor: pointer; float: left;"
                                                             onclick="checkOtherDiagExam();"       
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientRF'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px;">Refuse</label>
                                                      <input type="radio"
                                                             name="<?php echo 'diagnostic_'.$pDiagnosticID.'_patient';?>"
                                                             value="RF"
                                                             id="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientUnselect'; ?>"
                                                             style="cursor: pointer; float: left;"
                                                             onclick="checkOtherDiagExam();"       
                                                      />
                                                      <label for="<?php echo 'diagnostic_'.$pDiagnosticID.'_patientUnselect'; ?>" style="font-weight: normal; cursor: pointer; float: left; margin: 3px 20px 0px 5px;">Deselect</label>
                                                    </td>    
                                                  </tr>
                                                  <?php } ?>
                                                  <tr>
                                                    <td colspan="3" style="text-align: left;">
                                                        <input type="text"
                                                               name="diagnostic_oth_remarks"
                                                               id="diagnostic_oth_remarks1"
                                                               class="form-control"
                                                               style="width: 250px; color: #000; margin: 0px 0px 0px 5px; text-transform: uppercase;"
                                                               autocomplete="off"
                                                               disabled
                                                               placeholder="Other Diagnostic Exam"
                                                        />
                                                        <br/>
                                                        <div style="font-size: 10px;font-style: italic;margin-top: 20px;">
                                                          Asterisk (*) refers to the services recommended by the Guidelines <br/>(AO No. 2017-0012: Guidelines on the Adoptions of Baseline Primary Health Care Guarantees for All Filipinos)
                                                        </div>
                                                        <div style="font-size: 10px;font-style: italic;margin-top: 20px;">
                                                          "Deselect" option is added to unselect/uncheck the checked option in Doctor Recommendation and Client Request or Refuse to avoid reloading of the page
                                                        </div>
                                                      </td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>     
                                        </table>

                                        <table style="margin: 15px 0px 0px 20px; text-align: left;">
                                            <tr style="font-weight: bold;">
                                                <td width="225px;">B. <font color="red" style="font-size: 15px;">*</font>Management (check if done)</td>
                                                <td>
                                                    <input type="checkbox"
                                                           name="management[]"
                                                           id="management_NA"
                                                           style="cursor: pointer; float: left;"
                                                           value="0"
                                                           onclick="enableDisableManagement();"

                                                    />
                                                    <label for="management_NA" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer;">Not Applicable</label>
                                                </td>
                                            </tr>
                                            <?php
                                            $pLibManagement = listManagement();
                                            foreach ($pLibManagement as $pManagementLib) {
                                                $managementID = $pManagementLib['MANAGEMENT_ID'];
                                                $managementDesc = $pManagementLib['MANAGEMENT_DESC'];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox"
                                                               name="management[]"
                                                               id="<?php echo 'management_'.$managementID; ?>"
                                                               value="<?php echo $managementID;?>"
                                                               style="cursor: pointer; float: left;"
                                                                <?php if($pSoapTransNo != null) {
                                                                    for($z = 0; $z < count($descManagement); $z++) {
                                                                        if ($descManagement[$z]['MANAGEMENT_ID'] == $managementID) { ?>
                                                                            checked
                                                                        <?php }
                                                                    }
                                                                }
                                                                ?>
                                                        />
                                                        <label for="<?php echo 'management_'.$managementID; ?>" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer;"><?php echo $managementDesc;?></label><br/>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            <tr>
                                                <td colspan="2">
                                                    <input type="checkbox"
                                                           name="management[]"
                                                           id="management_oth"
                                                           value="X"
                                                           style="cursor: pointer; float: left;"
                                                           onclick="checkOtherManagement();"
                                                    />
                                                    <label for="management_oth" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer; float: left;">Others</label>
                                                    <input type="text"
                                                           name="management_oth_remarks"
                                                           id="management_oth_remarks"
                                                           class="form-control"
                                                           style="width: 300px; color: #000; margin: 0px 0px 0px 5px; text-transform: uppercase;"
                                                           autocomplete="off"
                                                           disabled
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                    </fieldset>
                                    <!-- <div style="text-align: center;">
                                      <input type="button"
                                             class="btn btn-primary"
                                             name="nxtTab5"
                                             id="nxtTab5"
                                             value="Next"
                                             title="Go to Laboratory/Imaging Results"
                                             style="margin: 10px 0px 0px 0px;"
                                             onclick="showTabConsultation('tab5');"
                                      />
                                    </div> -->

                                    <br/>
                                    <div align="center">
                                      <?php if($pSoapTransNo != null){ ?>
                                      <input type="submit"
                                             name="updateClientSoap"
                                             id="updateClientSoap"
                                             class="btn btn-success"
                                             style="margin-left: 10px;"
                                             value="Update Patient Record">
                                      <?php } else{ ?>
                                      <input type="submit"
                                             name="saveClientSoap"
                                             id="saveClientSoap"
                                             class="btn btn-success"
                                             style="margin-left: 10px;"
                                             value="Save Record">
                                      <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div align="left">
                      <font color="red" style="font-size: 10px; font-family: Verdana, Geneva, sans-serif;">
                        <i>NOTE: All fields marked with asterisk (*) are required.</i>
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
    $(".chosen-select").chosen();

    function deleteRow(r) {
        var i = r.parentNode.parentNode.rowIndex;
        document.getElementById("diagnosis_table").deleteRow(i);
    }
      $(document).ready(function(){
          $(".chosen-container").css({"width": "450px"});
          $(".chosen-container").css({"text-transform": "uppercase"});

      });

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

</script>
