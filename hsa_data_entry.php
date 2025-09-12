<?php
$page = 'profiling';
include('header.php');
checkLogin();
include('menu.php');

$case_no = $_GET["case_no"];
if(isset($_GET['case_no'])){
    $px_data = getEnlistData($case_no);
    if(!$px_data) {
        echo "<script>alert('Invalid Case Number!'); window.location='hsa_search.php';</script>";
    }
}

$hsa_transNo = $_GET["pHsaTransNo"];
$getUpdCntProfile = getUpdCntProfiling($hsa_transNo);
if ($getUpdCntProfile["UPD_CNT"] != NULL) {
    $getUpdCnt = $getUpdCntProfile["UPD_CNT"];
} else {
    $getUpdCnt = "0";
}

if(isset($_GET['pHsaTransNo'])){
    $px_data = getPatientHsaRecord($hsa_transNo, $getUpdCnt);
    $descPastMedHist = getPatientHsaPastMedicalHistory($hsa_transNo, $getUpdCnt);
    $descPastMedRemarks = getPatientHsaPastMedicalRemarks($hsa_transNo, $getUpdCnt);
    $descSurgicalHistory = getPatientHsaSurgicalHistory($hsa_transNo, $getUpdCnt);
    $descFamMedHistory = getPatientHsaFamilyHistory($hsa_transNo, $getUpdCnt);
    $descFamMedRemarks = getPatientHsaFamilyRemarks($hsa_transNo, $getUpdCnt);
    $descImmunization = getPatientHsaImmunization($hsa_transNo, $getUpdCnt);
    $descPertinentMisc = getPatientHsaPertinentMisc($hsa_transNo, $getUpdCnt);
    $px_labsFBS = getPatientHsaLabsFbs($hsa_transNo, $getUpdCnt);
    $px_labsRBS = getPatientHsaLabsRbs($hsa_transNo, $getUpdCnt);

    //$descMedicine = getPatientHsaMedicine($hsa_transNo, $getUpdCnt);

    $pPoB = wordwrap($px_data['PX_POB'], 2, " ", true);
    $pGetPoB = explode(" ",$pPoB);
    $descProvAdd = describeProvinceAddress($pGetPoB[0]);
    $descProvMun = describeMunicipalityAddress($pGetPoB[1], $pGetPoB[0]);

    if(!$px_data) {
        echo "<script>alert('Invalid Transaction Number!'); window.location='hsa_search.php';</script>";
    }
   
}

$pat_birthday = date("m/d/Y",strtotime($px_data["PX_DOB"]));
$mem_birthday = date("m/d/Y",strtotime($px_data["MEM_DOB"]));

/*Start Compute Age for availment of Essential Services*/
$px_RegisteredDate = date("m/d/Y",strtotime($px_data["ENLIST_DATE"]));
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

/*End Compute Age for availment of Essential Services*/


$provinces = listProvince();
//$listSkins = listSkin();
$listHeents = listHeent();
$listChests = listChest();
$listHearts = listHeart();
$listAbs = listAbdomen();
//$listExtremities = listExtremities();
$listNeuro = listNeuro();
$listGenitourinary = listGenitourinary();
$listRectal = listDigitalRectal();
$listSkinExtremities = listSkinExtremities();

if(isset($_POST['saveFinalizeHSA']) || isset($_POST['saveRecord'])){
    if($_POST['saveRecord']){
        $_POST['pFinalize'] = "N";
    }

    if($_POST['saveFinalizeHSA']){
        $_POST['pFinalize'] = "Y";
    }

    // $_POST['pFinalize'] = "Y";
    $_POST['pUpdCntProfile'] = $getUpdCnt;
    $_POST['pHsaTransNo'] = $hsa_transNo;

    if(isset($_POST['chkOBHistPreEclampsia']) == 'Y'){
        $_POST['chkOBHistPreEclampsiaValue']='Y';
    }else{
        $_POST['chkOBHistPreEclampsiaValue']='N';
    }

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


    if(true){
        saveProfilingInfo($_POST);
    } else{
        echo "<script>alert('Error Saving Health Screening & Assessment.'); </script>";
    }
}

?>

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
        <form action="" name="formProfiling" method="POST">
            <table border="0" style="margin-top: 0px;" align="center">
                <tr id="soap_info">
                    <td>
                        <div class="tabbable">
                            <ul class="nav nav-pills nav-justified" data-tabs="tabs" style="margin-top: 1px; margin-bottom: 5px;">
                                <li class="active" id="list1"><a href="#tab1" data-toggle="tab" style="text-align: center; height: 71px;" onclick="" id="">1<br>Client Profile</a></li>
                                <li class="" id="list2"><a href="#tab2" data-toggle="tab" style="text-align: center;" onclick="" id="">2<br>Medical & Surgical History</a></li>
                                <li class="" id="list3"><a href="#tab3" data-toggle="tab" style="text-align: center;" onclick="" id="">3<br>Family & Personal History</a></li>
                                <li class="" id="list3_1" style="display: none;"><a href="#tab3_1" data-toggle="tab" style="text-align: center;" onclick="" id="">3.1<br>Laboratory/Imaging Results</a></li>
                                <li class="" id="list4"><a href="#tab4" data-toggle="tab" style="text-align: center; height: 71px;" onclick="" id="">4<br>Immunizations</a></li>
                                <li class="" id="list5"><a href="#tab5" data-toggle="tab" style="text-align: center; height: 71px;" onclick="" id="">5<br>OB-Gyne History</a></li>
                                <li class="" id="list6"><a href="#tab6" data-toggle="tab" style="text-align: center;" onclick="" id="">6<br>Pertinent Physical Examination Findings</a></li>                                
                                <li class="" id="list7"><a href="#tab7" data-toggle="tab" style="text-align: center;" onclick="" id="">7<br>NCD High-Risk<br/> Assessment</a></li>
                            </ul>
                        </div>
                        <div class="tab-content">
                            <!--TAB 1 - FAMILY AND PERSONAL HISTORY START-->
                            <div class="tab-pane fade in active" id="tab1">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Client Profile</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;">
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
                                                             onclick="setDisabled('<?php echo "txtPerHistOTP";?>', false);"
                                                            <?php if($hsa_transNo != null && $px_data["WITH_ATC"] == 'N'){ ?>
                                                                 checked="checked"
                                                            <?php } else if($hsa_transNo == null) {?>     
                                                                checked="checked"     
                                                            <?php } ?>                               
                                                        />
                                                        <label for="walkedInChecker_true" style="font-size:14px;font-weight: bold; cursor: pointer; float: left; margin: 0px 10px 0px 5px; ">
                                                        Walk-in clients with Authorization Transaction Code (ATC) 
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                <td>
                                                    <!--Authorization Number/ PROFILE_OTP-->
                                                    <label style="color:red;">*</label><label for="txtPerHistOTP">Authorization Transaction Code:</label>
                                                    <br/>
                                                    <input type="text"
                                                           id="txtPerHistOTP"
                                                           name="txtPerHistOTP"
                                                           minlength="4"
                                                           maxlength="10"
                                                           class="form-control"
                                                           style="width: 100%"
                                                        <?php if($hsa_transNo != null && $px_data["WITH_ATC"] == 'N'){ ?>
                                                            value="<?php echo $px_data["PROFILE_OTP"]; ?>"
                                                        <?php } else{  ?>
                                                            value=""
                                                            placeholder="Authorization Transaction Code"
                                                        <?php } ?>
                                                    />                                                    
                                                </td>                                                
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><label style="margin-top:15px;font-size: 11px;font-style: italic;color:red;">Note: ATC should be used within the Screening & Assessment Date.</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">                                                        
                                                        <input type="radio"
                                                             name="walkedInChecker"
                                                             id="walkedInChecker_false"
                                                             value="Y"   
                                                             style="cursor: pointer; float: left;"
                                                             onclick="setDisabled('<?php echo "txtPerHistOTP";?>', true);"
                                                             <?php if($hsa_transNo != null && $px_data["WITH_ATC"] == 'Y'){ ?>
                                                                 checked="checked"
                                                            <?php }  ?>                                                                       
                                                        />
                                                        <label for="walkedInChecker_false" style="font-size:14px;font-weight: bold; cursor: pointer; float: left; margin: 0px 10px 0px 5px; ">
                                                        Walk-in clients without ATC
                                                        </label>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>

                                        <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;">
                                            <table border="0" style="width: 100%;" class="table-condensed">
                                                <tr>
                                                    <td>
                                                    <label style="color:red;">*</label><label for="txtPerHistProfDate">Health Screening & Assessment Date:</label>
                                                    <br/>
                                                    <input type="text"
                                                           id="txtPerHistProfDate"
                                                           name="txtPerHistProfDate"
                                                           value="<?php if($hsa_transNo != null){ echo date('m/d/Y', strtotime($px_data["PROF_DATE"])); }?>"
                                                           class="datepicker form-control"
                                                           style="width: 50%"
                                                           placeholder="mm/dd/yyyy"
                                                           onkeypress="formatDate('txtPerHistProfDate')"
                                                    />                                                    
                                                </td>
                                                </tr>
                                            </table>
                                        </fieldset>

                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                            <table border="0" style="width: 100%;" class="table-condensed">
                                                <tr>
                                                    <td colspan="5">
                                                        <div class="alert alert-success" style="margin-bottom: 0px;font-weight: bold;font-size:16px;">
                                                            INDIVIDUAL HEALTH PROFILE
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 20%;">
                                                        <label for="txtPerHistCaseNo">Case Number:</label>
                                                        <input type="text"
                                                               id="txtPerHistCaseNo"
                                                               name="txtPerHistCaseNo"
                                                               value="<?php echo $px_data["CASE_NO"]; ?>"
                                                               class="form-control"
                                                               style="width: 95%"
                                                               readonly
                                                        />
                                                    </td>
                                                    <td style="width: 20%;">
                                                        <label>PhilHealth Identification Number:</label>
                                                        <!--Patient PIN-->
                                                        <input type="text"
                                                               id="txtPerHistPxPIN"
                                                               name="txtPerHistPxPIN"
                                                               value="<?php echo $px_data["PX_PIN"]; ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                        <!--Member PIN-->
                                                        <input type="hidden"
                                                               id="txtPerHistMemPIN"
                                                               name="txtPerHistMemPIN"
                                                               value="<?php echo $px_data["MEM_PIN"]; ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>  
                                                    <td>
                                                        <!--Effectivty year-->
                                                        <label>Effectivty year:</label>
                                                        <input type="text"
                                                               id="txtPerHistEffYEar"
                                                               name="txtPerHistEffYear"
                                                               value="<?php echo $px_data["EFF_YEAR"]; ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>                                                  
                                                </tr>
                                                <tr><td colspan="5"><h4><u>Client Details</u></h4></td></tr>
                                                <tr>
                                                    <td style="width: 20%">
                                                        <label>Last Name:</label>
                                                        <input type="text"
                                                               id="txtPerHistPatLname"
                                                               name="txtPerHistPatLname"
                                                               value="<?php echo strReplaceEnye($px_data["PX_LNAME"]); ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>
                                                    <td style="width: 20%">
                                                        <label>First Name:</label>
                                                        <input type="text"
                                                               id="txtPerHistPatFname"
                                                               name="txtPerHistPatFname"
                                                               value="<?php echo strReplaceEnye($px_data["PX_FNAME"]); ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>
                                                    <td style="width: 20%">
                                                        <label>Middle Name:</label>
                                                        <input type="text"
                                                               id="txtPerHistPatMname"
                                                               name="txtPerHistPatMname"
                                                               value="<?php echo strReplaceEnye($px_data["PX_MNAME"]); ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>
                                                    <td style="width: 10%">
                                                        <label>Extension Name:</label>
                                                        <input type="text"
                                                               id="txtPerHistPatExtName"
                                                               name="txtPerHistPatExtName"
                                                               value="<?php echo strReplaceEnye($px_data["PX_EXTNAME"]); ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>
                                                </tr>                                                
                                                <tr>
                                                    <td style="width: 20%">
                                                        <label>Age:</label>
                                                        <input type="text"
                                                               id="txtPerHistPatAge"
                                                               name="txtPerHistPatAge"
                                                               value="<?php echo $descAgeServ; ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                        <input type="hidden" id="valtxtPerHistPatAge" name="valtxtPerHistPatAge" value="<?php echo $getAgeServ->y; ?>" />
                                                        <input type="hidden" id="valtxtPerHistPatMonths" name="valtxtPerHistPatMonths" value="<?php echo $getAgeServ->m; ?>" />
                                                        <input type="hidden" id="valtxtPerHistPatDays" name="valtxtPerHistPatDays" value="<?php echo $getAgeServ->d; ?>" />
                                                    </td>
                                                    <td style="width: 20%">
                                                        <label>Date of Birth (mm/dd/yyyy):</label>
                                                        <input type="text"
                                                               id="txtPerHistPatBirthday"
                                                               name="txtPerHistPatBirthday"
                                                               value="<?php echo $pat_birthday;?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>
                                                    <td style="width: 20%">
                                                        <label>Sex:</label>
                                                        <input type="text"
                                                               id="txtPerHistPatSex"
                                                               name="txtPerHistPatSex"
                                                               value="<?php echo getSex(false, $px_data["PX_SEX"]); ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                        <input type="hidden"
                                                               id="txtPerHistPatSexValue"
                                                               name="txtPerHistPatSexValue"
                                                               value="<?php echo $px_data["PX_SEX"]; ?>"
                                                        />
                                                    </td>    
                                                    <td style="width: 20%">
                                                        <label>Client Type:</label>
                                                        <input type="text"
                                                               id=""
                                                               name=""
                                                               value="<?php echo getPatientType(false, $px_data["PX_TYPE"]); ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                        <input type="hidden"
                                                               id="txtPerHistPatType"
                                                               name="txtPerHistPatType"
                                                               value="<?php echo $px_data["PX_TYPE"]; ?>"
                                                               class="form-control"
                                                               style="width: 95%;"
                                                               readonly
                                                        />
                                                    </td>                                               
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="button"
                                                   class="btn btn-primary"
                                                   name="nextTab2"
                                                   id="nextTab2"
                                                   value="Next"
                                                   title="Go to Medical & Surgical History"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab1');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--TAB 1 - FAMILY AND PERSONAL HISTORY END-->

                            <!--TAB 2 - MEDICAL AND SURGICAL HISTORY TAB START-->
                            <div class="tab-pane fade" id="tab2">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Medical & Surgical History</h3>
                                    </div>
                                    <div class="panel-body" id="obliSerTab">
                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">

                                            <table border="0" width="100%" class="table-condensed">
                                                <col width="50%">
                                                <col width="50%">
                                                <tr> <!--PAST MEDICAL HISTORY-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <label style="color:red;">*</label><strong style="font-size: 16px">PAST MEDICAL HISTORY</strong>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0">
                                                            <?php
                                                            $mDiseases = listMedicalDiseases();
                                                            for ($i=0; $i < count($mDiseases); $i++) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                               id="chkMedHistDiseases_<?php echo $mDiseases[$i]["MDISEASE_CODE"]; ?>"
                                                                               name="chkMedHistDiseases[]"
                                                                               value="<?php echo $mDiseases[$i]["MDISEASE_CODE"]; ?>"
                                                                               onclick="enDisSpecificMedHist(this.value);"
                                                                            <?php if($hsa_transNo != null){
                                                                                for($z = 0; $z < count($descPastMedHist); $z++) {
                                                                                    if ($descPastMedHist[$z]["MDISEASE_CODE"] == $mDiseases[$i]["MDISEASE_CODE"]) { ?>
                                                                                        checked
                                                                                    <?php }
                                                                                }
                                                                            } ?>
                                                                        />
                                                                    </td>
                                                                    <td>&nbsp;</td>
                                                                    <td>
                                                                        <label for="chkMedHistDiseases_<?php echo $mDiseases[$i]["MDISEASE_CODE"]; ?>" style="margin-top: 5px; cursor: pointer; font-size: 12px;">
                                                                            <?php echo $mDiseases[$i]["MDISEASE_DESC"]; ?>
                                                                        </label>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                    <td style="vertical-align: top">
                                                        <table border="0" style="width: 100%" class="table-condensed">
                                                            <tr>
                                                                <td>
                                                                    <label for="txtMedHistAllergy">Specify Allergy:</label><br>
                                                                    <input type='text'
                                                                           name='txtMedHistAllergy'
                                                                           id="txtMedHistAllergy"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase;'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                if ($descPastMedRem["MDISEASE_CODE"] == "001") {
                                                                                    ?>
                                                                                    value="<?php echo $descPastMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtMedHistCancer">Specify organ with cancer:</label><br>
                                                                    <input type='text'
                                                                           name='txtMedHistCancer'
                                                                           id="txtMedHistCancer"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase;'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                if ($descPastMedRem["MDISEASE_CODE"] == "003") {
                                                                                    ?>
                                                                                    value="<?php echo $descPastMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtMedHistHepatitis">Specify hepatitis type:</label><br>
                                                                    <input type='text'
                                                                           name='txtMedHistHepatitis'
                                                                           id="txtMedHistHepatitis"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase;'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                if ($descPastMedRem["MDISEASE_CODE"] == "009") {
                                                                                    ?>
                                                                                    value="<?php echo $descPastMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtMedHistBPSystolic">Highest blood pressure:</label><br>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtMedHistBPSystolic'
                                                                               id="txtMedHistBPSystolic"
                                                                               maxlength="4"
                                                                               class='form-control'
                                                                               style="width: 50px;text-transform: uppercase;"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               disabled
                                                                            <?php
                                                                            if($hsa_transNo != null) {
                                                                                foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                    if ($descPastMedRem["MDISEASE_CODE"] == "011") {
                                                                                        $descBP = explode(" ", $descPastMedRem['SPECIFIC_DESC']);
                                                                                        ?>
                                                                                        value="<?php echo $descBP[0];?>"
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        /> /
                                                                        <input type='text'
                                                                               name='txtMedHistBPDiastolic'
                                                                               id="txtMedHistBPDiastolic"
                                                                               maxlength="4"
                                                                               class='form-control'
                                                                               style="width: 50px;text-transform: uppercase"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               disabled
                                                                            <?php
                                                                            if($hsa_transNo != null) {
                                                                                foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                    if ($descPastMedRem["MDISEASE_CODE"] == "011") {
                                                                                        $descBP = explode(" ", $descPastMedRem['SPECIFIC_DESC']);
                                                                                        ?>
                                                                                        value="<?php echo $descBP[2];?>"
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        /> mmHg
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtMedHistPTB">Specify Pulmonary Tuberculosis category:</label><br>
                                                                    <input type='text'
                                                                           name='txtMedHistPTB'
                                                                           id="txtMedHistPTB"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase;'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                if ($descPastMedRem["MDISEASE_CODE"] == "015") {
                                                                                    ?>
                                                                                    value="<?php echo $descPastMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtMedHistExPTB">Specify Extrapulmonary Tuberculosis category:</label><br>
                                                                    <input type='text'
                                                                           name='txtMedHistExPTB'
                                                                           id="txtMedHistExPTB"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase;'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descPastMedRemarks as $descPastMedRem){
                                                                                if ($descPastMedRem["MDISEASE_CODE"] == "016") {
                                                                                    ?>
                                                                                    value="<?php echo $descPastMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txaMedHistOthers">Others, please specify:</label><br>
                                                                    <textarea name="txaMedHistOthers" id="txaMedHistOthers" class='form-control' rows="5" maxlength="2000" style="text-transform: uppercase; resize: none; width: 100%;" disabled>
                                                                    <?php
                                                                    if($hsa_transNo != null) {
                                                                        foreach ($descPastMedRemarks as $descPastMedRem){
                                                                            if ($descPastMedRem["MDISEASE_CODE"] == "998") {
                                                                                ?>
                                                                                value="<?php echo $descPastMedRem['SPECIFIC_DESC'];?>"
                                                                                <?php
                                                                            }
                                                                        }
                                                                    }
                                                                    ?></textarea>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr> <!--PAST MEDICAL HISTORY END-->
                                                <tr>  <!--PAST SURGICAL HISTORY-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 2px">
                                                            <strong style="font-size: 16px">PAST SURGICAL HISTORY</strong>
                                                        </div>

                                                        <table id="tblMedHistOpHist" class="table table-condensed table-bordered">

                                                            <col width="70%">
                                                            <col width="20%">
                                                            <col width="10%">
                                                            <thead>
                                                            <tr>
                                                                <th style="text-align: left;">OPERATION</th>
                                                                <th>DATE</th>
                                                                <th>&nbsp;</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php
                                                            for($i=0; $i < count($descSurgicalHistory); $i++){
                                                                $pOperation = $descSurgicalHistory[$i]['SURG_DESC'];
                                                                $pOptDate = $descSurgicalHistory[$i]['SURG_DATE'];

                                                                if ($i % 2 != 1) {
                                                                    echo '<tr style="background-color: #FBFCC7;">';
                                                                }
                                                                else {
                                                                    echo '<tr>';
                                                                }

                                                                echo '<td style="text-align: left;">'.$pOperation.'</td>';
                                                                echo '<td>'.$pOptDate.'</td>';
                                                                echo '<td></td>';
                                                                echo '</tr>';
                                                            }
                                                            ?>

                                                            <tr>
                                                                <td>
                                                                    <textarea id="txaMedHistOpHist" onkeyup="resizeTextArea();" class='form-control' rows="1" style="resize: none; width: 100%;text-transform: uppercase;"></textarea>
                                                                </td>
                                                                <td style="vertical-align: middle">
                                                                    <input type="text"
                                                                           id="txtMedHistOpDate"
                                                                           placeholder='mm/dd/yyyy'
                                                                           class="datepicker form-control"
                                                                           maxlength="10"
                                                                           autocomplete="off"
                                                                    />
                                                                </td>
                                                                <td style="vertical-align: middle">
                                                                    <button type="button" class="btn btn-success" style="width: 100%" onclick="addOperationHist();">Add</button>
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr> <!--PAST SURGICAL HISTORY END-->
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="button"
                                                   class="btn btn-primary"
                                                   name="nextTab3"
                                                   id="nextTab3"
                                                   value="Next"
                                                   title="Go to Family & Personal History"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab2');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--TAB 2 - MEDICAL AND SURGICAL HISTORY TAB START END-->

                            <!--TAB 3 - FAMILY AND PERSONAL HISTORY START-->
                            <div class="tab-pane fade" id="tab3">
                                <div class="panel panel-primary ">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Family & Personal History</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">

                                            <table border="0" width="100%" class="table-condensed">
                                                <col width="50%">
                                                <col width="50%">
                                                <tr> <!--FAMILY HISTORY-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <label style="color:red;">*</label><strong style="font-size: 16px">FAMILY HISTORY</strong>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0">
                                                            <?php
                                                            $mDisease = listMedicalDiseases();
                                                            for ($i=0; $i < count($mDisease); $i++) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                               id="chkFamHistDiseases_<?php echo $mDisease[$i]["MDISEASE_CODE"]; ?>"
                                                                               name="chkFamHistDiseases[]"
                                                                               value="<?php echo $mDisease[$i]["MDISEASE_CODE"]; ?>"
                                                                               onclick="enDisSpecificFamHist(this.value);"
                                                                            <?php if($hsa_transNo != null) {
                                                                            for($z = 0; $z < count($descFamMedHistory); $z++) {
                                                                            if ($mDisease[$i]['MDISEASE_CODE'] == $descFamMedHistory[$z]['MDISEASE_CODE']) { ?>
                                                                               checked
                                                                            <?php
                                                                                    }
                                                                                }
                                                                            } ?>/>
                                                                    </td>
                                                                    <td>&nbsp;</td>
                                                                    <td>
                                                                        <label for="chkFamHistDiseases_<?php echo $mDisease[$i]["MDISEASE_CODE"]; ?>" style="margin-top: 5px; cursor: pointer; font-size: 12px;">
                                                                            <?php echo $mDisease[$i]["MDISEASE_DESC"]; ?>
                                                                        </label>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                    <td style="vertical-align: top">
                                                        <table border="0" style="width: 100%" class="table-condensed">
                                                            <tr>
                                                                <td>
                                                                    <label for="txtFamHistAllergy">Specify Allergy:</label><br>
                                                                    <input type='text'
                                                                           name='txtFamHistAllergy'
                                                                           id="txtFamHistAllergy"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                if ($descFamMedRem["MDISEASE_CODE"] == "001") {
                                                                                    ?>
                                                                                    value="<?php echo $descFamMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtFamHistCancer">Specify organ with cancer:</label><br>
                                                                    <input type='text'
                                                                           name='txtFamHistCancer'
                                                                           id="txtFamHistCancer"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                if ($descFamMedRem["MDISEASE_CODE"] == "003") {
                                                                                    ?>
                                                                                    value="<?php echo $descFamMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtFamHistHepatitis">Specify hepatitis type:</label><br>
                                                                    <input type='text'
                                                                           name='txtFamHistHepatitis'
                                                                           id="txtFamHistHepatitis"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                if ($descFamMedRem["MDISEASE_CODE"] == "009") {
                                                                                    ?>
                                                                                    value="<?php echo $descFamMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtFamHistBPSystolic">Highest blood pressure:</label><br>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtFamHistBPSystolic'
                                                                               id="txtFamHistBPSystolic"
                                                                               maxlength="4"
                                                                               class='form-control'
                                                                               style="width: 50px;text-transform: uppercase"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               disabled
                                                                            <?php
                                                                            if($hsa_transNo != null) {
                                                                                foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                    if ($descFamMedRem["MDISEASE_CODE"] == "011") {
                                                                                        $descFamBP = explode("/", $descFamMedRem['SPECIFIC_DESC']);
                                                                                        ?>
                                                                                        value="<?php echo $descFamBP[0];?>"
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        /> /
                                                                        
                                                                        <input type='text'
                                                                               name='txtFamHistBPDiastolic'
                                                                               id="txtFamHistBPDiastolic"
                                                                               maxlength="4"
                                                                               class='form-control'
                                                                               style="width: 50px;"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               disabled
                                                                            <?php
                                                                            if($hsa_transNo != null) {
                                                                                foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                    if ($descFamMedRem["MDISEASE_CODE"] == "011") {
                                                                                        $descFamBP = explode("/", $descFamMedRem['SPECIFIC_DESC']);
                                                                                        $descFamBP2 = explode(" ", $descFamBP[1]);
                                                                                        ?>
                                                                                        value="<?php echo $descFamBP2[0];?>"
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            }
                                                                            ?>
                                                                        /> mmHg
                                                                        
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtFamHistPTB">Specify Pulmonary Tuberculosis category:</label><br>
                                                                    <input type='text'
                                                                           name='txtFamHistPTB'
                                                                           id="txtFamHistPTB"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                if ($descFamMedRem["MDISEASE_CODE"] == "015") {
                                                                                    ?>
                                                                                    value="<?php echo $descFamMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txtFamHistExPTB">Specify Extrapulmonary Tuberculosis category:</label><br>
                                                                    <input type='text'
                                                                           name='txtFamHistExPTB'
                                                                           id="txtFamHistExPTB"
                                                                           maxlength="2000"
                                                                           class='form-control'
                                                                           style='text-transform: uppercase'
                                                                           disabled
                                                                        <?php
                                                                        if($hsa_transNo != null) {
                                                                            foreach ($descFamMedRemarks as $descFamMedRem){
                                                                                if ($descFamMedRem["MDISEASE_CODE"] == "016") {
                                                                                    ?>
                                                                                    value="<?php echo $descFamMedRem['SPECIFIC_DESC'];?>"
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label for="txaFamHistOthers">Others, please specify:</label><br>
                                                                    <textarea name="txaFamHistOthers" 
                                                                    id="txaFamHistOthers" class='form-control' 
                                                                    rows="5" maxlength="2000" 
                                                                    style="resize: none; width: 100%;text-transform: uppercase" 
                                                                    disabled
                                                                    ><?php if($hsa_transNo != null) {
                                                                            foreach ($descFamMedRemarks as $descFamMedRem) {
                                                                                if ($descFamMedRem["MDISEASE_CODE"] == "998") { ?>
                                                                                    <?php echo $descFamMedRem['SPECIFIC_DESC'];?>
                                                                                    <?php
                                                                                }
                                                                            }
                                                                        }
                                                                        ?></textarea>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr> <!--FAMILY HISTORY END-->
                                                <tr>  <!--PERSONAL/SOCIAL HISTORY-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 2px">
                                                            <strong style="font-size: 16px">PERSONAL/SOCIAL HISTORY</strong>
                                                        </div>

                                                        <table border="0">
                                                            <tr>
                                                                <td><h5><u><b><label style="color:red;">*</label>Smoking</b></u></h5></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input id="radFamHistSmokeY"
                                                                                       type="radio"
                                                                                       name="radFamHistSmoke"
                                                                                       value="Y"
                                                                                       onclick="enDisFamHistSmoking(this.value);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_SMOKER'] == "Y") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistSmokeY">Yes</label></td>
                                                                            <td>
                                                                                <input id="radFamHistSmokeN"
                                                                                       type="radio"
                                                                                       name="radFamHistSmoke"
                                                                                       value="N"
                                                                                       onclick="enDisFamHistSmoking(this.value);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_SMOKER'] == "N") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistSmokeN">No</label></td>
                                                                            <td>
                                                                                <input id="radFamHistSmokeX"
                                                                                       type="radio"
                                                                                       name="radFamHistSmoke"
                                                                                       value="X"
                                                                                       onclick="enDisFamHistSmoking(this.value);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_SMOKER'] == "X") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistSmokeX">Quit</label></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <label>No. of packs/year?</label>
                                                                    <input type='text'
                                                                           name='txtFamHistCigPk'
                                                                           id="txtFamHistCigPk"
                                                                           maxlength="5"
                                                                           class='form-control'
                                                                           onkeypress="return acceptNumOnly(event);"
                                                                        <?php if($hsa_transNo != null) {
                                                                            if ($px_data['IS_SMOKER'] == "Y" || $px_data['IS_SMOKER'] == "X") { ?>
                                                                                value="<?php echo $px_data['NO_CIGPK'];?>"
                                                                            <?php   }
                                                                        } else{ ?>
                                                                            disabled
                                                                        <?php } ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><h5><u><b><label style="color:red;">*</label>Alcohol</b></u></h5></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input id="radFamHistAlcoholY"
                                                                                       type="radio"
                                                                                       name="radFamHistAlcohol"
                                                                                       value="Y"
                                                                                       onclick="enDisFamHistAlcohol(this.value);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_ADRINKER'] == "Y") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistAlcoholY">Yes</label></td>
                                                                            <td>
                                                                                <input id="radFamHistAlcoholN"
                                                                                       type="radio"
                                                                                       name="radFamHistAlcohol"
                                                                                       value="N"
                                                                                       onclick="enDisFamHistAlcohol(this.value);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_ADRINKER'] == "N") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistAlcoholN">No</label></td>
                                                                            <td>
                                                                                <input id="radFamHistAlcoholX"
                                                                                       type="radio"
                                                                                       name="radFamHistAlcohol"
                                                                                       value="X"
                                                                                       onclick="enDisFamHistAlcohol(this.value);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_ADRINKER'] == "X") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistAlcoholX">Quit</label></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <label>No. of bottles/day?</label>
                                                                    <input type='text'
                                                                           name='txtFamHistBottles'
                                                                           id="txtFamHistBottles"
                                                                           maxlength="5"
                                                                           class='form-control'
                                                                           onkeypress="return acceptNumOnly(event);"
                                                                        <?php if($hsa_transNo != null) {
                                                                            if ($px_data['IS_ADRINKER'] == "Y" || $px_data['IS_ADRINKER'] == "X") { ?>
                                                                                value="<?php echo $px_data['NO_BOTTLES'];?>"
                                                                            <?php   }
                                                                        } else{ ?>
                                                                            disabled
                                                                        <?php } ?>
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><h5><u><b><label style="color:red;">*</label>Illicit Drugs</b></u></h5></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input id="radFamHistDrugsY"
                                                                                       type="radio"
                                                                                       name="radFamHistDrugs"
                                                                                       value="Y"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['ILL_DRUG_USER'] == "Y") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistDrugsY">Yes</label></td>
                                                                            <td>
                                                                                <input id="radFamHistDrugsN"
                                                                                       type="radio"
                                                                                       name="radFamHistDrugs"
                                                                                       value="N"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['ILL_DRUG_USER'] == "N") { ?>
                                                                                            checked
                                                                                        <?php   }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistDrugsN">No</label></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td><h5><u><b><label style="color:red;">*</label>Sexual History Screening</b></u></h5></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Sexually Active</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>                       
                                                                                <input id="radFamHistSexualHistY"
                                                                                       type="radio"
                                                                                       name="radFamHistSexualHist"
                                                                                       value="Y"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_SEXUALLY_ACTIVE'] == "Y") { ?>
                                                                                            checked="checked"
                                                                                        <?php   }
                                                                                    } 
                                                                                    if ($getAgeServ->y <= 17) {
                                                                                     ?>
                                                                                     disabled="disabled"
                                                                                    <?php } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistSexualHistY">Yes</label></td>
                                                                            <td>
                                                                                <input id="radFamHistSexualHistN"
                                                                                       type="radio"
                                                                                       name="radFamHistSexualHist"
                                                                                       value="N"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_SEXUALLY_ACTIVE'] == "N") { ?>
                                                                                            checked="checked"
                                                                                        <?php   }
                                                                                    }
                                                                                    if ($getAgeServ->y <= 17) {
                                                                                     ?>
                                                                                     disabled="disabled"
                                                                                     checked="checked"
                                                                                    <?php } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radFamHistSexualHistN">No</label></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr> <!--PERSONAL/SOCIAL HISTORY END-->
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="button"
                                                   class="btn btn-primary"
                                                   name="nextTab4"
                                                   id="nextTab4"
                                                   value="Next"
                                                   title="Go to Immunizations"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab3');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--TAB 3 - FAMILY AND PERSONAL HISTORY END-->

                            <!--TAB 4 - IMMUNIZATIONS TAB START-->
                            <div class="tab-pane fade" id="tab4">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Immunizations</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">

                                            <table border="0" width="100%" class="table-condensed">
                                                <col width="50%">
                                                <col width="50%">
                                                <tr>
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <strong style="font-size: 16px">IMMUNIZATIONS</strong>

                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr><!-- CHILDREN START-->
                                                    <td style="font-weight: bold;text-decoration: underline;">
                                                        FOR CHILDREN
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0">
                                                            <?php
                                                            $immunization = listChildImmunizations();
                                                            for ($i=0; $i < count($immunization); $i++) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                               id="chkImmChild_<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                               name="chkImmChild[]"
                                                                               value="<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                               onclick="enDisImmuneChild(this.value);"
                                                                            <?php if($hsa_transNo != null) {
                                                                                for($z = 0; $z < count($descImmunization); $z++) {
                                                                                    if ($descImmunization[$z]['CHILD_IMMCODE'] == $immunization[$i]['IMM_CODE']) { ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            } ?>
                                                                        />
                                                                    </td>
                                                                    <td>&nbsp;</td>
                                                                    <td>
                                                                        <label for="chkImmChild_<?php echo $immunization[$i]["IMM_CODE"]; ?>" style="margin-top: 5px; cursor: pointer; font-size: 12px;">
                                                                            <?php echo $immunization[$i]["IMM_DESC"]; ?>
                                                                        </label>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                </tr> <!--FOR CHILDREN END-->
                                                <tr> <!--FOR ADULT-->
                                                    <td colspan="2" style="font-weight: bold;text-decoration: underline;">
                                                        FOR ADULT
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0">
                                                            <?php
                                                            $immunization = listAdultImmunizations();
                                                            for ($i=0; $i < count($immunization); $i++) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                               id="chkImmAdult_<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                               name="chkImmAdult[]"
                                                                               value="<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                               onclick="enDisImmuneAdult(this.value);"
                                                                            <?php if($hsa_transNo != null) {
                                                                                for($z = 0; $z < count($descImmunization); $z++) {
                                                                                    if ($descImmunization[$z]['YOUNGW_IMMCODE'] == $immunization[$i]['IMM_CODE']) { ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            } ?>
                                                                        />
                                                                    </td>
                                                                    <td>&nbsp;</td>
                                                                    <td>
                                                                        <label for="chkImmAdult_<?php echo $immunization[$i]["IMM_CODE"]; ?>" style="margin-top: 5px; cursor: pointer; font-size: 12px;">
                                                                            <?php echo $immunization[$i]["IMM_DESC"]; ?>
                                                                        </label>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                                // $index++;
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                </tr> <!--FOR ADULT END-->
                                                <tr> <!--FOR PREGNANT WOMEN-->
                                                    <td colspan="2" style="font-weight: bold;text-decoration: underline;">FOR PREGNANT WOMEN</td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0">
                                                            <?php
                                                            $immunization = listPregnantImmunizations();
                                                            for ($i=0; $i < count($immunization); $i++) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                               id="chkImmPregnant_<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                               name="chkImmPregnant[]"
                                                                               onclick="enDisImmunePreg(this.value);"
                                                                               value="<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                            <?php if($hsa_transNo != null) {
                                                                                for($z = 0; $z < count($descImmunization); $z++) {
                                                                                    if ($descImmunization[$z]['PREGW_IMMCODE'] == $immunization[$i]['IMM_CODE']) { ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            }                                                                           
                                                                            ?>

                                                                        />
                                                                    </td>
                                                                    <td>&nbsp;</td>
                                                                    <td>
                                                                        <label for="chkImmPregnant_<?php echo $immunization[$i]["IMM_CODE"]; ?>" style="margin-top: 5px; cursor: pointer; font-size: 12px;">
                                                                            <?php echo $immunization[$i]["IMM_DESC"]; ?>
                                                                        </label>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                </tr> <!--FOR PREGNANT WOMEN END-->
                                                <tr> <!--FOR ELDERLY AND IMMUNOCOMPROMISED-->
                                                    <td colspan="2" style="font-weight: bold;text-decoration: underline;">FOR ELDERLY AND IMMUNOCOMPROMISED</td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0">
                                                            <?php
                                                            $immunization = listElderlyImmunizations();
                                                            for ($i=0; $i < count($immunization); $i++) {
                                                                ?>
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox"
                                                                               id="chkImmElderly_<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                               name="chkImmElderly[]"
                                                                               onclick="enDisImmuneElder(this.value);"
                                                                               value="<?php echo $immunization[$i]["IMM_CODE"]; ?>"
                                                                            <?php if($hsa_transNo != null) {
                                                                                for($z = 0; $z < count($descImmunization); $z++) {
                                                                                    if ($descImmunization[$z]['ELDERLY_IMMCODE'] == $immunization[$i]['IMM_CODE']) { ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                }
                                                                            } ?>
                                                                        />
                                                                    </td>
                                                                    <td>&nbsp;</td>
                                                                    <td>
                                                                        <label for="chkImmElderly_<?php echo $immunization[$i]["IMM_CODE"]; ?>" style="margin-top: 5px; cursor: pointer; font-size: 12px;">
                                                                            <?php echo $immunization[$i]["IMM_DESC"]; ?>
                                                                        </label>
                                                                    </td>

                                                                </tr>
                                                                <?php
                                                                // $index++;
                                                            }
                                                            ?>
                                                        </table>
                                                    </td>
                                                </tr> <!--FOR ELDERLY AND IMMUNOCOMPROMISED END-->
                                                <tr> <!--FOR OTHERS-->
                                                    <td colspan="2" style="font-weight: bold;text-decoration: underline;">OTHERS, PLEASE SPECIFY</td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top; padding-left: 10px;">
                                                        <table border="0" width="100%">
                                                            <tr>
                                                                <td>
                                                                    <textarea name="txaImm" id="txaImmOthers" class='form-control' rows="5" maxlength="2000" style="resize: none; width: 100%;text-transform: uppercase"><?php echo $px_data["OTHER_IMM"];?></textarea>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr> <!--FOR OTHERS END-->
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="button"
                                                   class="btn btn-primary"
                                                   name="nextTab5"
                                                   id="nextTab5"
                                                   value="Next"
                                                   title="Go to OB-Gyne History"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab4');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--TAB 4 IMMUNIZATIONS TAB END-->

                            <!--TAB 5 - OB-GYNE HISTORY TAB START-->
                            <div class="tab-pane fade" id="tab5">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">OB-Gyne History</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;<?php if ($px_data["PX_SEX"] == 'M') { ?>display:none;<?php } ?>" >
                                            <table border="0" width="100%" class="table-condensed">
                                                <col width="50%">
                                                <col width="50%">
                                                <tr> <!--FAMILY PLANNING-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <strong style="font-size: 16px">FAMILY PLANNING</strong>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><label>With access to family planning counseling?</label></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <table>
                                                            <tr>
                                                                <td>
                                                                    <input id="radOBHistWFamPlanY"
                                                                           type="radio"
                                                                           name="radOBHistWFamPlan"
                                                                           value="Y"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['W_FAM_PLAN'] == "Y"){
                                                                                ?>
                                                                                checked
                                                                                <?php
                                                                            }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td style="padding-right: 15px;"><label for="radOBHistWFamPlanY">Yes</label></td>
                                                                <td>
                                                                    <input id="radOBHistWFamPlanN"
                                                                           type="radio"
                                                                           name="radOBHistWFamPlan"
                                                                           value="N"
                                                                           checked
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['W_FAM_PLAN'] == "N"){
                                                                                ?>
                                                                                checked
                                                                                <?php
                                                                            }

                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td style="padding-right: 15px;"><label for="radOBHistWFamPlanN">No</label></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr> <!--MENSTRUAL HISTORY-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <strong style="font-size: 16px">MENSTRUAL HISTORY</strong>

                                                            <div style="float:right;">
                                                            <span id="mhDone" style="display:inline-block;">
                                                                <span>
                                                                    <input id="mhDone_Y"
                                                                           type="radio"
                                                                           name="mhDone"
                                                                           value="Y"
                                                                           style="margin-left:20px;float: left;"
                                                                           onclick="enMenstrualHist();"
                                                                           <?php if ($px_data["PX_SEX"] == 'F') { ?>
                                                                            checked
                                                                           <?php } ?>
                                                                    />
                                                                    <label for="mhDone_Y" style="margin: 3px 20px 0px 5px;">Applicable</label> <!--done--->
                                                                </span>
                                                                <span>
                                                                    <input id="mhDone_N"
                                                                           type="radio"
                                                                           name="mhDone"
                                                                           value="N"
                                                                           style="float: left;"
                                                                        <?php if($px_data["PX_SEX"] == 'M'){ ?>
                                                                            checked="checked"
                                                                        <?php } ?>
                                                                           onclick="disMenstrualHist();"
                                                                    />
                                                                    <label for="mhDone_N" style="margin: 3px 20px 0px 5px;">Not Applicable</label>
                                                                </span>                                                               
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <table border="0" style="width: 100%;">
                                                            <col style="width: 25%;">
                                                            <col style="width: 25%;">
                                                            <col style="width: 15%;">
                                                            <col style="width: 35%;">
                                                            <tr>
                                                                <td><label>Menarche:</label></td>
                                                                <td><label>Onset of sexual intercourse:</label></td>
                                                                <td><label>Menopause?</label></td>
                                                                <td><label>If yes, what age?</label></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistMenarche'
                                                                                       id="txtOBHistMenarche"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['MENARCHE_PERIOD'];?>"
                                                                                />
                                                                            </td>
                                                                            <td>
                                                                                <label style="float: right;">&nbsp;yrs. old</label>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistOnsetSexInt'
                                                                                       id="txtOBHistOnsetSexInt"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['ONSET_SEX_IC'] ?>"
                                                                                />
                                                                            </td>
                                                                            <td>
                                                                                <label style="float: right;">&nbsp;yrs. old</label>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input id="radOBHistMenopauseY"
                                                                                       type="radio"
                                                                                       name="radOBHistMenopause"
                                                                                       value="Y"
                                                                                       onclick="enDisOBHistMenopause(this.value);"
                                                                                    <?php if($hsa_transNo != null){
                                                                                        if($px_data['IS_MENOPAUSE'] == "Y"){
                                                                                            ?>
                                                                                            checked
                                                                                            <?php
                                                                                        }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radOBHistMenopauseY">Yes</label></td>
                                                                            <td>
                                                                                <input id="radOBHistMenopauseN"
                                                                                       type="radio"
                                                                                       name="radOBHistMenopause"
                                                                                       value="N" onclick="enDisOBHistMenopause(this.value);"
                                                                                    <?php if($hsa_transNo != null){
                                                                                        if($px_data['IS_MENOPAUSE'] == "N"){
                                                                                            ?>
                                                                                            checked
                                                                                            <?php
                                                                                        }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-right: 15px;"><label for="radOBHistMenopauseN">No</label></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistMenopauseAge'
                                                                                       id="txtOBHistMenopauseAge"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                    <?php if($hsa_transNo != null) {
                                                                                        if ($px_data['IS_MENOPAUSE'] == "Y") { ?>
                                                                                            value="<?php echo $px_data['MENOPAUSE_AGE']; ?>"
                                                                                        <?php } else{ ?>
                                                                                            disabled
                                                                                        <?php }
                                                                                    }else{
                                                                                        ?>
                                                                                        disabled
                                                                                    <?php } ?>
                                                                                />
                                                                            </td>
                                                                            <td>
                                                                                <label style="float: right;">&nbsp;yrs. old</label>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label>Last menstrual period:</label></td>
                                                                <td><label>Birth control method:</label></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistLastMens'
                                                                                       id="txtOBHistLastMens"
                                                                                       maxlength="10"
                                                                                       class='form-control datepicker'
                                                                                       placeholder="mm/dd/yyyy"
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php if($px_data['LAST_MENS_PERIOD'] != null || $px_data['LAST_MENS_PERIOD'] != ""){
                                                                                       echo date('m/d/Y', strtotime($px_data['LAST_MENS_PERIOD'])); } else { echo ""; } ?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistBirthControl'
                                                                                       id="txtOBHistBirthControl"
                                                                                       class='form-control'
                                                                                       value="<?php echo $px_data['BIRTH_CTRL_METHOD'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label>Period duration:</label></td>
                                                                <td><label>Interval cycle:</label></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistPeriodDuration'
                                                                                       id="txtOBHistPeriodDuration"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['PERIOD_DURATION'];?>"
                                                                                />
                                                                            </td>
                                                                            <td>
                                                                                <label style="float: right;">&nbsp;days</label>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistInterval'
                                                                                       id="txtOBHistInterval"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['MENS_INTERVAL'];?>"
                                                                                />
                                                                            </td>
                                                                            <td>
                                                                                <label style="float: right;">&nbsp;days</label>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label>No. of pads/day during menstruation:</label></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistPadsPerDay'
                                                                                       id="txtOBHistPadsPerDay"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['PADS_PER_DAY'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr> <!--PREGNANCY HISTORY-->
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <strong style="font-size: 16px">PREGNANCY HISTORY</strong>

                                                            <div style="float:right;">
                                                            <span id="pregDone" style="display:inline-block;">
                                                                <span>
                                                                    <input id="pregDone_Y"
                                                                           type="radio"
                                                                           name="pregDone"
                                                                           value="Y"
                                                                           style="margin-left:20px;float: left;"
                                                                           onclick="enPregHist();"
                                                                           <?php if ($px_data["PX_SEX"] == 'F') { ?>
                                                                            checked
                                                                           <?php } ?>
                                                                    />
                                                                    <label for="pregDone_Y" style="margin: 3px 20px 0px 5px;">Applicable</label>
                                                                </span>
                                                                <span>
                                                                    <input id="pregDone_N"
                                                                           type="radio"
                                                                           name="pregDone"
                                                                           value="N"
                                                                           style="float: left;"
                                                                        <?php if($px_data["PX_SEX"] == 'M'){ ?>
                                                                            checked="checked"
                                                                        <?php } ?>
                                                                           onclick="disPregHist();"
                                                                    />
                                                                    <label for="pregDone_N" style="margin: 3px 20px 0px 5px;">Not Applicable</label>
                                                                </span>
                                                            </span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <table border="0" style="width: 100%;">
                                                            <col style="width: 25%;">
                                                            <col style="width: 25%;">
                                                            <col style="width: 25%;">
                                                            <col style="width: 25%;">
                                                            <tr>
                                                                <td><label>Gravidity (no. of pregnancy):</label></td>
                                                                <td><label>Parity (no. of delivery):</label></td>
                                                                <td><label>Type of delivery:</label></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistGravity'
                                                                                       id="txtOBHistGravity"
                                                                                       maxlength="2"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['PREG_CNT'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistParity'
                                                                                       id="txtOBHistParity"
                                                                                       maxlength="2"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['DELIVERY_CNT'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td colspan="2">
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <select name='optOBHistDelivery' id="optOBHistDelivery" class='form-control' style="width: 100%">
                                                                                    <?php
                                                                                    $deliveryTypes = getDeliveryType(true,"");

                                                                                    foreach ($deliveryTypes as $deliveryCode => $deliveryDesc) {
                                                                                        echo "<option value='".$deliveryCode."'>".$deliveryDesc."</option>";
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label>No. of full term:</label></td>
                                                                <td><label>No. of premature:</label></td>
                                                                <td><label>No. of abortion:</label></td>
                                                                <td><label>No. of living children:</label></td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistFullTerm'
                                                                                       id="txtOBHistFullTerm"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['FULL_TERM_CNT'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistPremature'
                                                                                       id="txtOBHistPremature"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['PREMATURE_CNT'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistAbortion'
                                                                                       id="txtOBHistAbortion"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['ABORTION_CNT'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                                <td>
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type='text'
                                                                                       name='txtOBHistLivingChildren'
                                                                                       id="txtOBHistLivingChildren"
                                                                                       maxlength="3"
                                                                                       class='form-control'
                                                                                       onkeypress="return acceptNumOnly(event);"
                                                                                       value="<?php echo $px_data['LIV_CHILDREN_CNT'];?>"
                                                                                />
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <table>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="checkbox"
                                                                                       name="chkOBHistPreEclampsia"
                                                                                       id="chkOBHistPreEclampsia"
                                                                                       value="Y"
                                                                                    <?php if($hsa_transNo != null){
                                                                                        if($px_data['W_PREG_INDHYP'] == "Y"){ ?>
                                                                                            checked
                                                                                            <?php
                                                                                        }
                                                                                    } ?>
                                                                                />
                                                                            </td>
                                                                            <td>&nbsp;</td>
                                                                            <td style="padding-top: 4px"><label for="chkOBHistPreEclampsia">Pregnancy-induced hypertension (Pre-eclampsia)</label></td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="button"
                                                   class="btn btn-primary"
                                                   name="nextTab6"
                                                   id="nextTab6"
                                                   value="Next"
                                                   title="Go to Pertinent Physical Examination Findings"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab5');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--TAB 5 OB-GYNE HISTORY TAB END-->

                            <!--TAB 6 - PERTINENT PHYSICAL EXAMINATION FINDINGS TAB START-->
                            <div class="tab-pane fade" id="tab6">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Pertinent Physical Examination Findings</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;">
                                            <!--PERTINENT PHYSICAL EXAMINATION FINDINGS-->
                                            <table border="0" style="width: 100%" class="table-condensed">
                                                <col style="width: 35%;">
                                                <col style="width: 65%;">
                                                <tr>
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <strong style="font-size: 16px">PERTINENT PHYSICAL EXAMINATION FINDINGS</strong>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="vertical-align: top;">
                                                        <table>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label for="txtMedHistBPSystolic" title="Blood Pressure">Blood Pressure:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExSystolic'
                                                                               id="txtPhExSystolic"
                                                                               maxlength="3"
                                                                               class='form-control'
                                                                               style="width: 97px;"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               value="<?php echo $px_data['SYSTOLIC']; ?>"
                                                                               placeholder="Systolic"
                                                                        /> /

                                                                        <input type='text'
                                                                               name='txtPhExBPDiastolic'
                                                                               id="txtPhExBPDiastolic"
                                                                               maxlength="3"
                                                                               class='form-control'
                                                                               style="width: 97px;"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               value="<?php echo $px_data['DIASTOLIC']; ?>"
                                                                               placeholder="Diastolic"
                                                                        /> mmHg
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label for="txtPhExHeartRate" title="Heart Rate">Heart Rate:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExHeartRate'
                                                                               id="txtPhExHeartRate"
                                                                               maxlength="6"
                                                                               class='form-control'
                                                                               placeholder="Heart Rate"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               value="<?php echo $px_data['HR']; ?>"
                                                                        /> /min
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label for="txtPhExRespiratoryRate" title="Respiratory Rate">Respiratoy Rate:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExRespiratoryRate'
                                                                               id="txtPhExRespiratoryRate"
                                                                               maxlength="6"
                                                                               class='form-control'
                                                                               placeholder="Respiratory Rate"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               value="<?php echo $px_data['RR']; ?>"
                                                                        /> /min
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label>Visual Acuity:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td colspan="3">                                                                    
                                                                    <label class="form-inline">                                                                        
                                                                        <input type='text'
                                                                               name='txtPhExVisualAcuityL'
                                                                               id="txtPhExVisualAcuityL"
                                                                               maxlength="12"
                                                                               class='form-control'
                                                                               value="<?php echo $px_data['LEFT_VISUAL_ACUITY']; ?>"
                                                                               style="width:100px;"      
                                                                               placeholder="Left Eye"
                                                                        />
                                                                        
                                                                        <input type='text'
                                                                               name='txtPhExVisualAcuityR'
                                                                               id="txtPhExVisualAcuityR"
                                                                               maxlength="12"
                                                                               class='form-control'
                                                                               value="<?php echo $px_data['RIGHT_VISUAL_ACUITY']; ?>"
                                                                               style="width:100px;"       
                                                                               placeholder="Right Eye"
                                                                        />
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                    <td>
                                                        <table>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label>Height:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExHeightCm'
                                                                               id="txtPhExHeightCm"
                                                                               maxlength="6"
                                                                               placeholder="Height"
                                                                               class='form-control'
                                                                               onkeypress="setValue('txtPhExHeightIn', CmtoInch(this));" onkeyup="setValue('txtPhExHeightIn', CmtoInch(this));"
                                                                               onFocus="this.form.txtPhExHeightCm.value=''"
                                                                               <?php if ($getAgeServ->y < 2) { ?>
                                                                                  disabled
                                                                                  value="0"
                                                                              <?php } else { ?>
                                                                                  value="<?php echo $px_data['HEIGHT']; ?>"
                                                                              <?php } ?>
                                                                        /> (cm)
                                                                    </label>
                                                                </td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExHeightIn'
                                                                               id="txtPhExHeightIn"
                                                                               maxlength="6"
                                                                               class='form-control'
                                                                               onkeypress="setValue('txtPhExHeightCm', InchToCm(this));" onkeyup="setValue('txtPhExHeightCm', InchToCm(this));"
                                                                                <?php if ($getAgeServ->y < 2) { ?>
                                                                                  disabled
                                                                                  value="0"
                                                                              <?php } else { ?>
                                                                                  value=""
                                                                              <?php } ?>
                                                                        /> (in)
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label>Weight:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExWeightKg'
                                                                               id="txtPhExWeightKg"
                                                                               maxlength="6"
                                                                               placeholder="Weight"
                                                                               class='form-control'
                                                                               onkeypress="setValue('txtPhExWeightLb', KgToLb(this));" onkeyup="setValue('txtPhExWeightLb', KgToLb(this));"
                                                                               value="<?php echo $px_data['WEIGHT']; ?>"
                                                                               onFocus="this.form.txtPhExWeightKg.value=''"
                                                                        /> (kg)
                                                                    </label>                                                                    
                                                                </td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExWeightLb'
                                                                               id="txtPhExWeightLb"
                                                                               maxlength="6"
                                                                               class='form-control'
                                                                               onkeypress="setValue('txtPhExWeightKg', LbToKg(this));" onkeyup="setValue('txtPhExWeightKg', LbToKg(this));"
                                                                        /> (lb)
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label for="txtPhExBMI">BMI:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td>
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExBMI'
                                                                               id="txtPhExBMI"
                                                                               maxlength="6"
                                                                               placeholder="Body Mass Index"
                                                                               class='form-control' 
                                                                               readonly
                                                                              <?php if ($getAgeServ->y <= 4) { ?>
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
                                                                           <?php if ($getAgeServ->y <= 4) { ?>
                                                                            disabled
                                                                           <?php } ?>
                                                                   />
                                                                </td>
                                                                <td colspan="2">
                                                                    <!-- <input type="text" class="form-control" name="bmiDescription" style="width:95%;margin: 2px 0px 0px 2px;" readonly/> -->
                                                                    <div id="bmiDescription" name="bmiDescription"></div>
                                                                </td>
                                                            </tr>
                                                            <tr><td>&nbsp;</td></tr>
                                                            <tr>
                                                                <td><label style="color:red;">*</label><label for="txtPhExTemp" title="Temperature">Temperature:</label></td>
                                                                <td>&nbsp;&nbsp;</td>
                                                                <td colspan="2">
                                                                    <label class="form-inline">
                                                                        <input type='text'
                                                                               name='txtPhExTemp'
                                                                               id="txtPhExTemp"
                                                                               maxlength="6"
                                                                               class='form-control'
                                                                               placeholder="Temperature"
                                                                               onkeypress="return isNumberWithDecimalKey(event);"
                                                                               value="<?php echo $px_data['TEMPERATURE']; ?>"
                                                                        /> &#176;C
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <table border="0">
                                                            <tr>
                                                                <td>
                                                                    <table border="0">
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
                                                                                           value="<?php echo $px_data['LENGTH']; ?>"
                                                                                           placeholder='Length'
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
                                                                                           value="<?php echo $px_data['HEAD_CIRC']; ?>"
                                                                                           onkeypress="return isNumberWithDecimalKey(event);"
                                                                                           placeholder='Head Circumference'
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
                                                                                           placeholder='Skinfold Thickness'
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
                                                                                           placeholder='Waist'   
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
                                                                            <td>&nbsp;&nbsp;</td>
                                                                            <td>
                                                                                <label class="form-inline">
                                                                                    <input type='text'
                                                                                           name='txtPhExBodyCircHipsCm'
                                                                                           id="txtPhExBodyCircHipsCm"
                                                                                           maxlength="6"
                                                                                           class='form-control' 
                                                                                           placeholder='Hip' 
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
                                                                                           placeholder='Limbs'
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
                                                                                           value="<?php echo $px_data['MID_UPPER_ARM']; ?>"
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
                                                                            <td colspan="5">&nbsp;</td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <h5><u style="font-weight: bold;">Blood Type</u></h5>
                                                        <table border="0">
                                                            <tr class="form-inline">                                                                
                                                                <td>
                                                                    <input id="radPhExBloodTypeA+"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="A+"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "A+"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeA+" style="margin-top: 3px; padding-right: 15px;">A+</label></td>
                                                                <td>
                                                                    <input id="radPhExBloodTypeB+"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="B+"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "B+"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeB+" style="margin-top: 3px; padding-right: 15px;">B+</label></td>
                                                                <td><input id="radPhExBloodTypeAB+"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="AB+"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "AB+"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeAB+" style="margin-top: 3px; padding-right: 15px;">AB+</label></td>
                                                                <td>
                                                                    <input id="radPhExBloodTypeO+"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="O+"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "O+"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeO+" style="margin-top: 3px; padding-right: 15px;">O+</label></td>
                                                            

                                                                <td>
                                                                    <input id="radPhExBloodTypeA-"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="A-"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "A-"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeA-" style="margin-top: 3px; padding-right: 15px;">A-</label></td>
                                                                <td>
                                                                    <input id="radPhExBloodTypeB-"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="B-"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "B-"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeB-" style="margin-top: 3px; padding-right: 15px;">B-</label></td>
                                                                <td><input id="radPhExBloodTypeAB-"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="AB-"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "AB-"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeAB-" style="margin-top: 3px; padding-right: 15px;">AB-</label></td>
                                                                <td>
                                                                    <input id="radPhExBloodTypeO-"
                                                                           type="radio"
                                                                           name="radPhExBloodType"
                                                                           value="O-"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['BLOOD_TYPE'] == "O-"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                </td>
                                                                <td>&nbsp;</td>
                                                                <td><label for="radPhExBloodTypeO-" style="margin-top: 3px; padding-right: 15px;">O-</label></td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <table>
                                                            <tr>
                                                                <td><label for="txtGenSurvey" title="General Survey" style="margin-top:20px;">General Survey:</label></td>
                                                                <td>
                                                                    <input type="radio"
                                                                           name="pGenSurvey"
                                                                           value="1"
                                                                           id="pGenSurvey_1"
                                                                           style="cursor: pointer; float: left;margin:20px 0px 0px 5px;"
                                                                           onclick="setDisabled('<?php echo "pGenSurveyRem";?>',true)"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['GENSURVEY_ID'] == "1"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                    <label for="pGenSurvey_1" style="font-weight: normal; cursor: pointer; float: left; margin: 20px 5px 0px 5px; ">Awake and alert</label>
                                                                </td>
                                                                <td>
                                                                    <input type="radio"
                                                                           name="pGenSurvey"
                                                                           value="2"
                                                                           id="pGenSurvey_2"
                                                                           style="cursor: pointer; float: left;margin:20px 0px 0px 10px;"
                                                                           onclick="setDisabled('<?php echo "pGenSurveyRem";?>',false)"
                                                                        <?php if($hsa_transNo != null){
                                                                            if($px_data['GENSURVEY_ID'] == "2"){ ?>
                                                                                checked
                                                                            <?php }
                                                                        } ?>
                                                                    />
                                                                    <label for="pGenSurvey_2" style="font-weight: normal; cursor: pointer; float: left; margin: 20px 5px 0px 5px; ">Altered Sensorium</label>
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                           name="pGenSurveyRemarks"
                                                                           value=""
                                                                           placeholder="Altered Sensorium Remarks" 
                                                                           id="pGenSurveyRem"
                                                                           class="form-control"
                                                                           style="width:250px;margin:20px 0px 0px 5px;text-transform: uppercase;"
                                                                           disabled
                                                                    />
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                                            <strong style="font-size: 16px">PERTINENT FINDINGS PER SYSTEM</strong>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr id="heent_info">
                                                    <td>
                                                        <h5><u style="font-weight: bold;">A. HEENT</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listHeents as $pLibHEENT) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="heent[]"
                                                                               id="<?php echo 'heent_'.$pLibHEENT['HEENT_ID'];?>"
                                                                               value="<?php echo $pLibHEENT['HEENT_ID'];?>"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="checkHeent();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['HEENT_ID'] == $pLibHEENT['HEENT_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">B. Chest/Breast/Lungs</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listChests as $pLibChest) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="chest[]"
                                                                               id="<?php echo 'chest_'.$pLibChest['CHEST_ID'];?>"
                                                                               value="<?php echo $pLibChest['CHEST_ID'];?>"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="checkChestLungs();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['CHEST_ID'] == $pLibChest['CHEST_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">C. Heart</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listHearts as $pLibHeart) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="heart[]"
                                                                               id="<?php echo 'heart_'.$pLibHeart['HEART_ID'];?>"
                                                                               value="<?php echo $pLibHeart['HEART_ID'];?>"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="checkHeart();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['HEART_ID'] == $pLibHeart['HEART_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">D. Abdomen</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listAbs as $pLibAbdomen) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="abdomen[]"
                                                                               id="<?php echo 'abdomen_'.$pLibAbdomen['ABDOMEN_ID'];?>"
                                                                               value="<?php echo $pLibAbdomen['ABDOMEN_ID'];?>" style="cursor: pointer; float: left;"
                                                                               onclick="checkAbdomen();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['ABDOMEN_ID'] == $pLibAbdomen['ABDOMEN_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">E. Genitourinary</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listGenitourinary  as $pLibGU) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="genitourinary[]"
                                                                               id="<?php echo 'gu_'.$pLibGU['GU_ID'];?>"
                                                                               value="<?php echo $pLibGU['GU_ID'];?>" style="cursor: pointer; float: left;"
                                                                               onclick="checkGU();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['GU_ID'] == $pLibGU['GU_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">F. Digital Rectal Examination</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listRectal as $pLibRectal) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="rectal[]"
                                                                               id="<?php echo 'rectal_'.$pLibRectal['RECTAL_ID'];?>"
                                                                               value="<?php echo $pLibRectal['RECTAL_ID'];?>" style="cursor: pointer; float: left;"
                                                                               onclick="checkRectal();disDigitalRectal(this.value);"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['RECTAL_ID'] == $pLibRectal['RECTAL_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">G. Skin/Extremities</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listSkinExtremities as $pLibExtremities) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="skinExtremities[]"
                                                                               id="<?php echo 'extremities_'.$pLibExtremities['SKIN_ID'];?>"
                                                                               value="<?php echo $pLibExtremities['SKIN_ID'];?>"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="checkSkinExtrem();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['SKIN_ID'] == $pLibExtremities['SKIN_ID']) { ?>
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
                                                        <h5><u style="font-weight: bold;">H. Neurological Examination</u></h5>
                                                        <table style="margin: 5px 0px 0px 20px; text-align: left;">
                                                            <?php foreach ($listNeuro as $pLibNeuro) { ?>
                                                                <tr>
                                                                    <td style="width: 250px;">
                                                                        <input type="checkbox"
                                                                               name="neuro[]"
                                                                               id="<?php echo 'neuro_'.$pLibNeuro['NEURO_ID'];?>"
                                                                               value="<?php echo $pLibNeuro['NEURO_ID'];?>"
                                                                               style="cursor: pointer; float: left;"
                                                                               onclick="checkNeuro();"
                                                                            <?php if($hsa_transNo != null) {
                                                                                foreach($descPertinentMisc as $descPertinent) {
                                                                                    if ($descPertinent['NEURO_ID'] == $pLibNeuro['NEURO_ID']) { ?>
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
                                                   name="nextTab7"
                                                   id="nextTab7"
                                                   value="Next"
                                                   title="Go to NCD High-Risk Assessment"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab6');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--TAB 6 PERTINENT PHYSICAL EXAMINATION FINDINGS TAB END-->

                           

                            <!--TAB 7 - NCD HIGH-RISK ASSESSMENT TAB START-->
                            <div class="tab-pane fade" id="tab7">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">NCD High-Risk Assessment</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px; padding: 20px; background-color: #EDFCF0;" class="fieldsetNcd">
                                            <table border="0" width="100%" class="table-condensed">
                                                <tr>
                                                    <td class="alert alert-success" colspan="2">
                                                        <strong style="font-size: 16px">NCD HIGH-RISK ASSESSMENT (for 25 years old and above)</strong>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <strong style="font-size: 14px;font-style:italic;">High Fat/High Salt Food Intake</strong>
                                                        <br/>
                                                        Eats processed/fast foods (e..g. instant noodles, hamburgers, fries, fried chicken skin etc.) and ihaw-ihaw (e.g. isaw, adidas, etc.) weekly
                                                        <br/>
                                                        <span id="Q1">
                                                        <span>
                                                            <input id="Q1_0"
                                                                   type="radio"
                                                                   name="Q1"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID1_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q1_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q1_1"
                                                                   type="radio"
                                                                   name="Q1"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID1_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q1_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <strong style="font-size: 14px;font-style:italic;">Dietary Fiber Intake</strong>
                                                        <br/>
                                                        <div>3 Servings vegetables daily</div>
                                                        <span id="Q2">
                                                        <span>
                                                            <input id="Q2_0"
                                                                   type="radio"
                                                                   name="Q2"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID2_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q2_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q2_1"
                                                                   type="radio"
                                                                   name="Q2"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID2_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q2_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                        <br/>
                                                        <div>2-3 servings of fruits daily</div>
                                                        <span id="Q3">
                                                        <span>
                                                            <input id="Q3_0"
                                                                   type="radio"
                                                                   name="Q3"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID3_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q3_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q3_1"
                                                                   type="radio"
                                                                   name="Q3"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID3_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q3_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <strong style="font-size: 14px;font-style:italic;">Physical Activities</strong>
                                                        <br/>
                                                        <div>Does at least 2.5 hours a week of moderate-intensity physical activity</div>
                                                        <span id="Q4">
                                                        <span>
                                                            <input id="Q4_0"
                                                                   type="radio"
                                                                   name="Q4"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID4_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q4_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q4_1"
                                                                   type="radio"
                                                                   name="Q4"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID4_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q4_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <strong style="font-size: 14px;font-style:italic;">Presence or absence of Diabetes</strong>
                                                        <br/>
                                                        <div>1. Was patient diagnosed as having diabetes?</div>
                                                        <span id="Q5">
                                                        <span>
                                                            <input id="Q5_0"
                                                                   type="radio"
                                                                   name="Q5"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID5_YNX'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q5_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q5_1"
                                                                   type="radio"
                                                                   name="Q5"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID5_YNX'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q5_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q5_2"
                                                                   type="radio"
                                                                   name="Q5"
                                                                   value="X"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID5_YNX'] == "X"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q5_2" style="margin: 3px 20px 0px 5px;">Do Not Know</label>
                                                        </span>
                                                    </span>
                                                        <br/>
                                                        <div>If Yes,</div>
                                                        <div id="upDiabetes">
                                                        <span id="Q5_1_1">
                                                            <span>
                                                                <input id="Q5_1_1_0"
                                                                       type="radio"
                                                                       name="Q5_1_1"
                                                                       value="Y"
                                                                       disabled="disabled"
                                                                       style="margin-left:20px;float: left;"
                                                                    <?php if($hsa_transNo != null){
                                                                        if($px_data['QID18_YN'] == "Y"){ ?>
                                                                            checked
                                                                            <?php
                                                                        }
                                                                    } ?>
                                                                />
                                                                <label for="Q5_1_1_0" style="margin: 3px 20px 0px 5px;">With Medication</label>
                                                            </span>
                                                            <br/>
                                                            <span>
                                                                <input id="Q5_1_1_1"
                                                                       type="radio"
                                                                       name="Q5_1_1"
                                                                       value="N"
                                                                       disabled="disabled"
                                                                       style="margin-left:20px;float: left;"
                                                                    <?php if($hsa_transNo != null){
                                                                        if($px_data['QID18_YN'] == "N"){ ?>
                                                                            checked
                                                                            <?php
                                                                        }
                                                                    } ?>
                                                                />
                                                                <label for="Q5_1_1_1" style="margin: 3px 20px 0px 5px;">Without Medication</label>
                                                            </span>
                                                        </span>
                                                        </div>
                                                        <div>
                                                            <br/>and perform Urine Test for Ketones. <br/>
                                                            If No or Do not know, proceed to question 2.<br/>
                                                            2. Does patient have the following symptoms? <br/>
                                                            Polyphagia
                                                        </div>
                                                        <span id="Q6">
                                                        <span>
                                                            <input id="Q6_0"
                                                                   type="radio"
                                                                   name="Q6"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID6_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q6_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q6_1"
                                                                   type="radio"
                                                                   name="Q6"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID6_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q6_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                        <br/>
                                                        <div>Polydipsia</div>
                                                        <span id="Q7">
                                                        <span>
                                                            <input id="Q7_0"
                                                                   type="radio"
                                                                   name="Q7"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID7_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q7_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q7_1"
                                                                   type="radio"
                                                                   name="Q7"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID7_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q7_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                        <br/>
                                                        <div>Polyuria</div>
                                                        <span id="Q8">
                                                        <span>
                                                            <input id="Q8_0"
                                                                   type="radio"
                                                                   name="Q8"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID8_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q8_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q8_1"
                                                                   type="radio"
                                                                   name="Q8"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID8_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q8_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                        <br/>
                                                        <div>If two or more of the above symptoms are present, perform a blood glucose test.</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <table width="600px">
                                                            <tbody>
                                                            <tr>
                                                                <td colspan="2">
                                                                    <div><strong style="font-size: 14px;font-style:italic;">Raised Blood Glucose</strong></div>
                                                                    <span id="Q8">
                                                                        <span>
                                                                            <input id="Q678_1_1_0"
                                                                                   type="radio"
                                                                                   name="Q678_1_1"
                                                                                   value="Y"
                                                                                   disabled="disabled"
                                                                                   style="margin-left:20px;float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID19_YN'] == "Y"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_1_1_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                                        </span>
                                                                        <span>
                                                                            <input id="Q678_1_1_1"
                                                                                   type="radio"
                                                                                   name="Q678_1_1"
                                                                                   value="N"
                                                                                   disabled="disabled"
                                                                                   style="float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID19_YN'] == "N"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_1_1_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                                        </span>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>FBS/RBS</td>
                                                                <td>Date Taken</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label>
                                                                        <input name="Q678_1_2"
                                                                               type="text"
                                                                               id="Q678_1_2"
                                                                               class="form-control"
                                                                               style="width:160px;"
                                                                               onkeypress=""
                                                                               maxlength="3"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               disabled="disabled"
                                                                               value="<?php echo $px_data['QID19_FBSMG'];?>"
                                                                        />
                                                                        mg/dL
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <input name="ncdRbgDate"
                                                                           type="text"
                                                                           disabled="disabled"
                                                                           id="ncdRbgDate"
                                                                           class="datepicker form-control"
                                                                           placeholder="mm/dd/yyyy"
                                                                           style="width:140px;"
                                                                           value=""
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <label>
                                                                        <input name="Q678_1_3"
                                                                               type="text"
                                                                               maxlength="3"
                                                                               disabled="disabled"
                                                                               id="Q678_1_3"
                                                                               class="form-control"
                                                                               onkeypress="return isNumberKey(event);"
                                                                               style="margin-top:5px;width:160px;"
                                                                               value="<?php echo $px_data['QID19_FBSMMOL'];?>"
                                                                        />
                                                                        mmol/L
                                                                    </label>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <div><strong style="font-size: 14px;font-style:italic;margin-top:10px">Raised Blood Lipids</strong></div>
                                                                    <span id="Q678_2_1">
                                                                        <span>
                                                                            <input id="Q678_2_1_0"
                                                                                   type="radio"
                                                                                   name="Q678_2_1"
                                                                                   value="Y"
                                                                                   disabled="disabled"
                                                                                   style="margin-left:20px;float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID20_YN'] == "Y"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_2_1_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                                        </span>
                                                                        <span>
                                                                            <input id="Q678_2_1_1"
                                                                                   type="radio"
                                                                                   name="Q678_2_1"
                                                                                   value="N"
                                                                                   disabled="disabled"
                                                                                   style="float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID20_YN'] == "N"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_2_1_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                                        </span>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Total Cholesterol</td>
                                                                <td>Date Taken</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <input name="Q678_2_2"
                                                                           type="text"
                                                                           maxlength="3"
                                                                           disabled="disabled"
                                                                           id="Q678_2_2"
                                                                           class="form-control"
                                                                           onkeypress="return isNumberKey(event);"
                                                                           style="width:160px;"
                                                                           value="<?php echo $px_data['QID20_CHOLEVAL'];?>"
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <input name="ncdRblDate"
                                                                           type="text"
                                                                           disabled="disabled"
                                                                           id="ncdRblDate"
                                                                           class="datepicker form-control"
                                                                           placeholder="mm/dd/yyyy"
                                                                           style="width:140px;"
                                                                           value=""
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <div><strong style="font-size: 14px;font-style:italic;">Presence of Urine Ketones</strong></div>
                                                                    <span id="Q678_3_1">
                                                                        <span>
                                                                            <input id="Q678_3_1_0"
                                                                                   type="radio"
                                                                                   name="Q678_3_1"
                                                                                   value="Y"
                                                                                   disabled="disabled"
                                                                                   style="margin-left:20px;float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID21_YN'] == "Y"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_3_1_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                                        </span>
                                                                        <span>
                                                                            <input id="Q678_3_1_1"
                                                                                   type="radio"
                                                                                   name="Q678_3_1"
                                                                                   value="N"
                                                                                   disabled="disabled"
                                                                                   style="float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID21_YN'] == "N"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_2_1_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                                        </span>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Urine Ketone</td>
                                                                <td>Date taken</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <input name="Q678_3_2"
                                                                           type="text"
                                                                           maxlength="3"
                                                                           disabled="disabled"
                                                                           id="Q678_3_2"
                                                                           class="form-control"
                                                                           onkeypress="return isNumberKey(event);"
                                                                           style="width:160px;"
                                                                           value="<?php echo $px_data['QID21_KETONVAL'];?>"
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <input name="ncdUkDate"
                                                                           type="text"
                                                                           disabled="disabled"
                                                                           id="ncdUkDate"
                                                                           class="datepicker form-control"
                                                                           placeholder="mm/dd/yyyy"
                                                                           style="width:140px;"
                                                                           value=""
                                                                    />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <div><strong style="font-size: 14px;font-style:italic;">Presence of Urine Protein</strong></div>
                                                                    <span id="Q678_4_1">
                                                                        <span>
                                                                            <input id="Q678_4_1_0"
                                                                                   type="radio"
                                                                                   name="Q678_4_1"
                                                                                   value="Y"
                                                                                   disabled="disabled"
                                                                                   style="margin-left:20px;float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID22_YN'] == "Y"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_4_1_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                                        </span>
                                                                        <span>
                                                                            <input id="Q678_4_1_1"
                                                                                   type="radio"
                                                                                   name="Q678_4_1"
                                                                                   value="N"
                                                                                   disabled="disabled"
                                                                                   style="float: left;"
                                                                                <?php if($hsa_transNo != null){
                                                                                    if($px_data['QID22_YN'] == "N"){ ?>
                                                                                        checked
                                                                                        <?php
                                                                                    }
                                                                                } ?>
                                                                            />
                                                                            <label for="Q678_4_1_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                                        </span>
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Urine Protein</td>
                                                                <td>Date taken</td>
                                                            </tr>
                                                            <tr>
                                                                <td>
                                                                    <input name="Q678_4_2"
                                                                           type="text"
                                                                           maxlength="3"
                                                                           disabled="disabled"
                                                                           id="Q678_4_2"
                                                                           class="form-control"
                                                                           onkeypress="return isNumberKey(event);"
                                                                           style="width:160px;"
                                                                           value="<?php echo $px_data['QID22_PROTEINVAL'];?>"
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <input name="ncdUpDate"
                                                                           type="text"
                                                                           disabled="disabled"
                                                                           id="ncdUpDate"
                                                                           class="datepicker form-control"
                                                                           placeholder="mm/dd/yyyy"
                                                                           style="width:140px;"
                                                                           value=""
                                                                    />
                                                                </td>
                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div style="font-size:14px;font-weight: bold">Questionnaire to Determine Probable Angina, Heart Attack, Stroke or Transient Ischemic Attack</div>
                                                        <div style="font-size:14px;font-weight: bold;font-style: italic;margin-top:10px">Angina or Heart Attack</div>
                                                        <span id="Q23">
                                                        <span>
                                                            <input id="Q23_0"
                                                                   type="radio"
                                                                   name="Q23"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID23_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q23_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q23_1"
                                                                   type="radio"
                                                                   name="Q23"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID23_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q23_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/><br/>
                                                        <div>1. Have you had any pain or discomfort or any pressure or heaviness in your chest?</div>
                                                        <span id="Q9">
                                                        <span>
                                                            <input id="Q9_0"
                                                                   type="radio"
                                                                   name="Q9"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID9_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q9_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q9_1"
                                                                   type="radio"
                                                                   name="Q9"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID9_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q9_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                        If NO, go to question 8

                                                        <br/>
                                                        <div style="margin-top:20px;">2. Do you get the pain in the center of the chest or left arm?</div>
                                                        <span id="Q10">
                                                        <span>
                                                            <input id="Q10_0"
                                                                   type="radio"
                                                                   name="Q10"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID10_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q10_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q10_1"
                                                                   type="radio"
                                                                   name="Q10"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID10_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q10_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>
                                                        If NO, go to question 8

                                                        <br/>
                                                        <div style="margin-top:20px;">3. Do you get it when you walk uphill or hurry?</div>
                                                        <span id="Q11">
                                                        <span>
                                                            <input id="Q11_0"
                                                                   type="radio"
                                                                   name="Q11"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID11_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q11_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q11_1"
                                                                   type="radio"
                                                                   name="Q11"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID11_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q11_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:20px;">4. Do you slowdown if you get the pain while walking?</div>
                                                        <span id="Q12">
                                                        <span>
                                                            <input id="Q12_0"
                                                                   type="radio"
                                                                   name="Q12"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID12_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q12_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q12_1"
                                                                   type="radio"
                                                                   name="Q12"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID12_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q12_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:20px;">5. Does the pain go away if you stand still or if you take a tablet under the tongue?</div>
                                                        <span id="Q13">
                                                        <span>
                                                            <input id="Q13_0"
                                                                   type="radio"
                                                                   name="Q13"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID13_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q13_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q13_1"
                                                                   type="radio"
                                                                   name="Q13"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID13_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q13_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:20px;">6. Does the pain away in less than 10 minutes?</div>
                                                        <span id="Q14">
                                                        <span>
                                                            <input id="Q14_0"
                                                                   type="radio"
                                                                   name="Q14"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID14_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q14_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q14_1"
                                                                   type="radio"
                                                                   name="Q14"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID14_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q14_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:20px;">7. Have you ever had a severe chest pain across the front of your chest lasting for half an hour or more?</div>
                                                        <span id="Q15">
                                                        <span>
                                                            <input id="Q15_0"
                                                                   type="radio"
                                                                   name="Q15"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID15_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q15_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q15_1"
                                                                   type="radio"
                                                                   name="Q15"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID15_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q15_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:20px">If the answer to Question 3 or 4 or 5 or 6 or 7 is Yes, patient have angina or heart attack and needs to see the doctor</div>
                                                        <div style="margin-top:20px;font-weight: bold;font-style: italic;">Stroke and TIA (Transient Ischemic Attack)</div>
                                                        <span id="Q24">
                                                        <span>
                                                            <input id="Q24_0"
                                                                   type="radio"
                                                                   name="Q24"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID24_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q24_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q24_1"
                                                                   type="radio"
                                                                   name="Q24"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID24_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q24_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:20px;">8. Have you ever had any of the following: difficulty in talking, weakness of arm and/or leg on one side of the body or numbness on one side of the body?</div>
                                                        <span id="Q16">
                                                        <span>
                                                            <input id="Q16_0"
                                                                   type="radio"
                                                                   name="Q16"
                                                                   value="Y"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID16_YN'] == "Y"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q16_0" style="margin: 3px 20px 0px 5px;">Yes</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q16_1"
                                                                   type="radio"
                                                                   name="Q16"
                                                                   value="N"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID16_YN'] == "N"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q16_1" style="margin: 3px 20px 0px 5px;">No</label>
                                                        </span>
                                                    </span>

                                                        <br/>
                                                        <div style="margin-top:10px;">If the answer to question 8 is YES, the patient may have had a TIA or stroke and needs to see the doctor.</div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <div style="margin-top:10px;font-weight: bold;font-style: italic;">RISK LEVEL</div>
                                                        <span id="Q17">
                                                        <span>
                                                            <input id="Q17_0"
                                                                   type="radio"
                                                                   name="Q17"
                                                                   value="A"
                                                                   disabled="disabled"
                                                                   style="margin-left:20px;float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID17_ABCDE'] == "A"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q17_0" style="margin: 3px 20px 0px 5px;"><10%</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q17_1"
                                                                   type="radio"
                                                                   name="Q17"
                                                                   value="B"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID17_ABCDE'] == "B"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q17_1" style="margin: 3px 20px 0px 5px;">10% to <20%</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q17_2"
                                                                   type="radio"
                                                                   name="Q17"
                                                                   value="C"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID17_ABCDE'] == "C"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q17_2" style="margin: 3px 20px 0px 5px;">20% to <30%</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q17_3"
                                                                   type="radio"
                                                                   name="Q17"
                                                                   value="D"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID17_ABCDE'] == "D"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q17_3" style="margin: 3px 20px 0px 5px;">30% to <40%</label>
                                                        </span>
                                                        <span>
                                                            <input id="Q17_4"
                                                                   type="radio"
                                                                   name="Q17"
                                                                   value="E"
                                                                   disabled="disabled"
                                                                   style="float: left;"
                                                                <?php if($hsa_transNo != null){
                                                                    if($px_data['QID17_ABCDE'] == "E"){ ?>
                                                                        checked
                                                                        <?php
                                                                    }
                                                                } ?>
                                                            />
                                                            <label for="Q17_4" style="margin: 3px 20px 0px 5px;">≥ 40%</label>
                                                        </span>
                                                    </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="submit"
                                                    class="btn btn-info"                                               
                                                    name="saveRecord"
                                                    id="save"
                                                    value="Save Record"
                                                    title="Save Record"
                                                    onclick="return saveHSAWithOutValidation();"  
                                                    <?php if($hsa_transNo != null && $px_data["IS_FINALIZE"] == 'Y'){ ?>
                                                        disabled
                                                    <?php } ?>
                                                    style="text-align: center;margin: 10px 0px 0px 0px;" 
                                                />
                                            <input type="submit"
                                                    class="btn btn-primary"                                               
                                                    name="saveFinalizeHSA"
                                                    id="save"
                                                    value="Save & Finalize"
                                                    title="Save & Finalize"
                                                    onclick="return saveFinalHsaTransaction();"
                                                    <?php if($hsa_transNo != null && $px_data["IS_FINALIZE"] == 'Y'){ ?>
                                                        disabled
                                                    <?php } ?>
                                                    style="text-align: center;margin: 10px 0px 0px 0px;"                                                
                                                />
                                            <?php if($hsa_transNo != null && $px_data["IS_FINALIZE"] == 'Y'){ 
                                                echo "<br/><p style='color:red;font-size:10px; font-style:italic;'>This record has been finalized and cannot be edited.</p>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--NCD HIGH-RISK ASSESSMENT TAB END-->

                            <!--TAB 1 - FAMILY AND PERSONAL HISTORY START-->
                            <div class="tab-pane fade" id="tab3_1">
                                <div class="panel panel-primary">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Laboratory/Imaging Results</h3>
                                    </div>
                                    <div class="panel-body">
                                        <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;">
                                            <p style="font-style: italic;color: red;font-size: 10px;">Note: FBS or RBS should be filled-out</p>
                                             <table style="width:100%; margin: 5px 20px 0px 20px; text-align: left;" cellpadding="2" align="left">
                                                <tr>
                                                    <td class="alert alert-success">
                                                        <label style="color:red;">*</label><strong>Fasting Blood Sugar (FBS)</strong>
                                                    </td>                                                
                                                </tr>
                                                <tr>
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
                                                                                       name="diagnostic_7_lab_exam"
                                                                                       value="1"
                                                                                       id="diagnostic_7_lab_exam_in"
                                                                                       style="cursor: pointer; float: left;"
                                                                                       onclick="setDisabled('diagnostic_7_accre_diag_fac', true);"
                                                                                       checked="checked"
                                                                                />
                                                                                <label for="diagnostic_7_lab_exam_in" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">within the facility</label>
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="radio"
                                                                                       name="diagnostic_7_lab_exam"
                                                                                       id="diagnostic_7_lab_exam_out"
                                                                                       value="0"
                                                                                       style="cursor: pointer; float: left;"
                                                                                       onclick="setDisabled('diagnostic_7_accre_diag_fac', false); setFocus('diagnostic_7_accre_diag_fac')"
                                                                                />
                                                                                <label for="<?php echo 'diagnostic_7_lab_exam_out';?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px;">Partner Facility</label>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                       name="diagnostic_7_accre_diag_fac"
                                                                                       id="diagnostic_7_accre_diag_fac"
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
                                                                    <label for="diagnostic_7_lab_exam_date" style="font-style: italic; font-weight: bold;">Date of Lab/Image Exam</label>
                                                                <td colspan="2">
                                                                    <input type="text"
                                                                           name="diagnostic_7_lab_exam_date"
                                                                           id="diagnostic_7_lab_exam_date"
                                                                           class="datepicker form-control"
                                                                           style="width: 110px; color: #000; margin: 5px 10px 0px 28px; text-transform: uppercase"
                                                                           onkeypress="formatDate('diagnostic_7_lab_exam_date')"
                                                                           autocomplete="off"
                                                                           placeholder="mm/dd/yyyy"
                                                                           maxlength="10"
                                                                           <?php if($hsa_transNo != null && $px_labsFBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo date('m/d/Y', strtotime($px_labsFBS["LAB_DATE"])); ?>"
                                                                            <?php } ?>
                                                                           />
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>
                                                                    <label for="diagnostic_7_lab_fee" style="font-style: italic; font-weight: bold;">Laboratory/Imaging Fee</label>
                                                                </td>
                                                                <td colspan="2">
                                                                  Php
                                                                    <input type="text"
                                                                           name="diagnostic_7_lab_fee"
                                                                           id="diagnostic_7_lab_fee"
                                                                           class="form-control"
                                                                           style="width: 110px; color: #000; margin: 5px 10px 0px 0px; text-transform: uppercase"
                                                                           onkeypress="return isNumberKeyWithTwoDecimalKey(event,'<?php echo "diagnostic_7_lab_fee";?>');"
                                                                           autocomplete="off"
                                                                            <?php if($hsa_transNo != null && $px_labsFBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo $px_labsFBS["DIAGNOSTIC_FEE"]; ?>"
                                                                            <?php } else {?>
                                                                                value=""
                                                                            <?php } ?>
                                                                           />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <!-- /*START FASTING BLOOD SUGAR (FBS)*/ -->
                                                            <tr>
                                                                <td><label for="diagnostic_7_glucose_mgdL" style="font-style: normal; font-weight: normal;">Glucose</label></td>
                                                                <td>
                                                                    <input type="text"
                                                                           name="diagnostic_7_glucose_mgdL"
                                                                           id="diagnostic_7_glucose_mgdL"
                                                                           class="form-control"
                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                           autocomplete="off" 
                                                                           maxlength="15" 
                                                                            <?php if($hsa_transNo != null && $px_labsFBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo $px_labsFBS["GLUCOSE_MG"]; ?>"
                                                                            <?php } else {?>
                                                                                value=""
                                                                            <?php } ?>>mg/dL
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                           name="diagnostic_7_glucose_mmolL"
                                                                           id="diagnostic_7_glucose_mmolL"
                                                                           class="form-control"
                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                           autocomplete="off"
                                                                           maxlength="15"      
                                                                            <?php if($hsa_transNo != null && $px_labsFBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo $px_labsFBS["GLUCOSE_MMOL"]; ?>"
                                                                            <?php } else {?>
                                                                                value=""
                                                                            <?php } ?>>mmol/L
                                                                </td>
                                                            </tr>
                                                            <!-- /*END FASTING BLOOD SUGAR (FBS)*/ -->
                                                        </table>
                                                    </fieldset>
                                                </td>
                                                </tr>
                                            </table>
                                        </fieldset>


                                        <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;">
                                             <table style="width:100%; margin: 5px 20px 0px 20px; text-align: left;" cellpadding="2" align="left">
                                                <tr>
                                                    <td class="alert alert-success">
                                                        <label style="color:red;">*</label><strong>Random Blood Sugar (RBS)</strong>
                                                    </td>                                                
                                                </tr>
                                                <tr>
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
                                                                                       name="diagnostic_19_lab_exam"
                                                                                       value="1"
                                                                                       id="diagnostic_19_lab_exam_in"
                                                                                       style="cursor: pointer; float: left;"
                                                                                       onclick="setDisabled('diagnostic_19_accre_diag_fac', true);"
                                                                                       checked="checked"
                                                                                />
                                                                                <label for="diagnostic_19_lab_exam_in" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px; ">within the facility</label>
                                                                            </td>
                                                                            <td></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>
                                                                                <input type="radio"
                                                                                       name="diagnostic_19_lab_exam"
                                                                                       id="diagnostic_19_lab_exam_out"
                                                                                       value="0"
                                                                                       style="cursor: pointer; float: left;"
                                                                                       onclick="setDisabled('diagnostic_19_accre_diag_fac', false); setFocus('diagnostic_19_accre_diag_fac')"
                                                                                />
                                                                                <label for="<?php echo 'diagnostic_19_lab_exam_out';?>" style="font-weight: normal; cursor: pointer; float: left; margin: 0px 5px 0px 5px;">Partner Facility</label>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                       name="diagnostic_19_accre_diag_fac"
                                                                                       id="diagnostic_19_accre_diag_fac"
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
                                                                    <label for="<?php echo 'diagnostic_19_lab_exam_date'; ?>" style="font-style: italic; font-weight: bold;">Date of Lab/Image Exam</label>
                                                                <td colspan="2">
                                                                    <input type="text"
                                                                           name="diagnostic_19_lab_exam_date"
                                                                           id="diagnostic_19_lab_exam_date"
                                                                           class="datepicker form-control"
                                                                           style="width: 110px; color: #000; margin: 5px 10px 0px 28px; text-transform: uppercase"
                                                                           onkeypress="formatDate('diagnostic_19_lab_exam_date')"
                                                                           autocomplete="off"
                                                                           placeholder="mm/dd/yyyy"
                                                                           maxlength="10"
                                                                           <?php if($hsa_transNo != null && $px_labsRBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo date('m/d/Y', strtotime($px_labsRBS["LAB_DATE"])); ?>"
                                                                            <?php } ?>
                                                                           />
                                                                </td>
                                                            </tr>

                                                            <tr>
                                                                <td>
                                                                    <label for="diagnostic_19_lab_fee" style="font-style: italic; font-weight: bold;">Laboratory/Imaging Fee</label>
                                                                </td>
                                                                <td colspan="2">
                                                                  Php
                                                                    <input type="text"
                                                                           name="diagnostic_19_lab_fee"
                                                                           id="diagnostic_19_lab_fee"
                                                                           class="form-control"
                                                                           style="width: 110px; color: #000; margin: 5px 10px 0px 0px; text-transform: uppercase"
                                                                           onkeypress="return isNumberKeyWithTwoDecimalKey(event,'<?php echo "diagnostic_19_lab_fee";?>');"
                                                                           autocomplete="off"
                                                                           <?php if($hsa_transNo != null && $px_labsRBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo $px_labsRBS["DIAGNOSTIC_FEE"]; ?>"
                                                                            <?php } else {?>
                                                                                value=""
                                                                            <?php } ?>
                                                                           />
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">&nbsp;</td>
                                                            </tr>
                                                            <!-- /*START RANDOM BLOOD SUGAR (RBS)*/ -->
                                                            <tr>
                                                                <td><label for="diagnostic_19_glucose_mgdL" style="font-style: normal; font-weight: normal;">Glucose</label></td>
                                                                <td>
                                                                    <input type="text"
                                                                           name="diagnostic_19_glucose_mgdL"
                                                                           id="diagnostic_19_glucose_mgdL"
                                                                           class="form-control"
                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                           autocomplete="off"
                                                                           maxlength="15"  
                                                                           <?php if($hsa_transNo != null && $px_labsRBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo $px_labsRBS["GLUCOSE_MG"]; ?>"
                                                                            <?php } else {?>
                                                                                value=""
                                                                            <?php } ?>>mg/dL
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                           name="diagnostic_19_glucose_mmolL"
                                                                           id="diagnostic_19_glucose_mmolL"
                                                                           class="form-control"
                                                                           style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                           autocomplete="off"
                                                                           maxlength="15"
                                                                           <?php if($hsa_transNo != null && $px_labsRBS["IS_APPLICABLE"] == 'D'){ ?>
                                                                                value="<?php echo $px_labsRBS["GLUCOSE_MMOL"]; ?>"
                                                                            <?php } else {?>
                                                                                value=""
                                                                            <?php } ?>
                                                                                >mmol/L
                                                                </td>
                                                            </tr>
                                                            <!-- /*END RANDOM BLOOD SUGAR (RBS)*/ -->
                                                        </table>
                                                    </fieldset>
                                                </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <div style="text-align: center;">
                                            <input type="button"
                                                   class="btn btn-primary"
                                                   name="nextTab3_1"
                                                   id="nextTab3_1"
                                                   value="Next"
                                                   title="Go to Immunizations"
                                                   style="margin: 10px 0px 0px 0px;"
                                                   onclick="showTabHSA('tab3_1');"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr style="">
                    <td><div align="left" style="color:red;font-size: 10px; font-family: Verdana, Geneva, sans-serif;font-style: italic;">NOTE: <br/>All fields marked with asterisk (*) are required for <i>"First Patient Encounter Data"</i>.
                    </div></td>
                </tr>
                <tr style="height: 10px;">
                    <td></td>
                </tr>               
            </table>
        </form>
        <br/><br/><br/>

        <div id="result" style="margin: 30px 0px 30px 0px; display: none;" align="center">
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

    $("#txtPerHistProfDate").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#txtOBHistLastMens").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#txtMedHistOpDate").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_7_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });
    $("#diagnostic_19_lab_exam_date").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });

    $("#txtPerHistProfDate").mask("99/99/9999");
    $("#txtPerHistMomBirthday").mask("99/99/9999");
    $("#txtPerHistDadBirthday").mask("99/99/9999");
    $("#txtMedHistOpDate").mask("99/99/9999");
    $("#txtOBHistLastMens").mask("99/99/9999");
    //
    //JS Functions
    $(function() {
        if($("#heent_9").is(":checked")){
            $("#palpable_mass").attr("disabled", false);
        } else{
            $("#palpable_mass").attr("disabled", true);
        }

        if($("#extremities_4").is(":checked")){
            $("#extreme_deform").attr("disabled", false);
        } else{
            $("#extreme_deform").attr("disabled", true);
        }

    });

    $(function() {
        /*Past Medical History*/
        if($("#chkMedHistDiseases_001").is(":checked")){
            $("#txtMedHistAllergy").attr("disabled", false);
        } else{
            $("#txtMedHistAllergy").attr("disabled", true);
        }

        if($("#chkMedHistDiseases_003").is(":checked")){
            $("#txtMedHistCancer").attr("disabled", false);
        } else{
            $("#txtMedHistCancer").attr("disabled", true);
        }

        if($("#chkMedHistDiseases_009").is(":checked")){
            $("#txtMedHistHepatitis").attr("disabled", false);
        } else{
            $("#txtMedHistHepatitis").attr("disabled", true);
        }

        if($("#chkMedHistDiseases_011").is(":checked")){
            $("#txtMedHistBPSystolic").attr("disabled", false);
            $("#txtMedHistBPDiastolic").attr("disabled", false);
        } else{
            $("#txtMedHistBPSystolic").attr("disabled", true);
            $("#txtMedHistBPDiastolic").attr("disabled", true);
        }

        if($("#chkMedHistDiseases_015").is(":checked")){
            $("#txtMedHistPTB").attr("disabled", false);
        } else{
            $("#txtMedHistPTB").attr("disabled", true);
        }

        if($("#chkMedHistDiseases_016").is(":checked")){
            $("#txtMedHistExPTB").attr("disabled", false);
        } else{
            $("#txtMedHistExPTB").attr("disabled", true);
        }

        if($("#chkMedHistDiseases_998").is(":checked")){
            $("#txaMedHistOthers").attr("disabled", false);
        } else{
            $("#txaMedHistOthers").attr("disabled", true);
        }

        /*Family History*/
        if($("#chkFamHistDiseases_001").is(":checked")){
            $("#txtFamHistAllergy").attr("disabled", false);
        } else{
            $("#txtFamHistAllergy").attr("disabled", true);
        }

        if($("#chkFamHistDiseases_003").is(":checked")){
            $("#txtFamHistCancer").attr("disabled", false);
        } else{
            $("#txtFamHistCancer").attr("disabled", true);
        }

        if($("#chkFamHistDiseases_009").is(":checked")){
            $("#txtFamHistHepatitis").attr("disabled", false);
        } else{
            $("#txtFamHistHepatitis").attr("disabled", true);
        }

        if($("#chkFamHistDiseases_011").is(":checked")){
            $("#txtFamHistBPSystolic").attr("disabled", false);
            $("#txtFamHistBPDiastolic").attr("disabled", false);
        } else{
            $("#txtFamHistBPSystolic").attr("disabled", true);
            $("#txtFamHistBPDiastolic").attr("disabled", true);
        }

        if($("#chkFamHistDiseases_015").is(":checked")){
            $("#txtMedHistPTB").attr("disabled", false);
        } else{
            $("#txtMedHistPTB").attr("disabled", true);
        }

        if($("#chkFamHistDiseases_016").is(":checked")){
            $("#txtFamHistExPTB").attr("disabled", false);
        } else{
            $("#txtFamHistExPTB").attr("disabled", true);
        }

        if($("#chkFamHistDiseases_998").is(":checked")){
            $("#txaFamHistOthers").attr("disabled", false);
        } else{
            $("#txaFamHistOthers").attr("disabled", true);
        }

        if ($("#chkFamHistDiseases_006").is(":checked")) {
            // $("#list3").removeClass("active");
            // $("#tab3").removeClass("active");
            $("#list3_1").show();
            // $("#tab3_1").addClass("active in");
        } 
    });

    $(function() {
        if($("#valtxtPerHistPatAge").val() >= 25){
            $('[name="ncdDone"]').attr("disabled",false);
            $('[name="Q1"]').attr("disabled",false);
            $('[name="Q2"]').attr("disabled",false);
            $('[name="Q3"]').attr("disabled",false);
            $('[name="Q4"]').attr("disabled",false);
            $('[name="Q5"]').attr("disabled",false);
            $('[name="Q5_1_1"]').attr("disabled",false);
            $('[name="Q6"]').attr("disabled",false);
            $('[name="Q7"]').attr("disabled",false);
            $('[name="Q8"]').attr("disabled",false);
            $('[name="Q678_1_1"]').attr("disabled",false);
            $('[name="Q678_1_2"]').attr("disabled",false);
            $('[name="Q678_1_3"]').attr("disabled",false);
            $('[name="ncdRbgDate"]').attr("disabled",false);
            $('[name="Q678_2_1"]').attr("disabled",false);
            $('[name="Q678_2_2"]').attr("disabled",false);
            $('[name="ncdRblDate"]').attr("disabled",false);
            $('[name="Q678_3_1"]').attr("disabled",false);
            $('[name="Q678_3_2"]').attr("disabled",false);
            $('[name="ncdUkDate"]').attr("disabled",false);
            $('[name="Q678_4_1"]').attr("disabled",false);
            $('[name="Q678_4_2"]').attr("disabled",false);
            $('[name="ncdUpDate"]').attr("disabled",false);
            $('[name="Q23"]').attr("disabled",false);
            $('[name="Q9"]').attr("disabled",false);
            $('[name="Q10"]').attr("disabled",false);
            $('[name="Q11"]').attr("disabled",false);
            $('[name="Q12"]').attr("disabled",false);
            $('[name="Q13"]').attr("disabled",false);
            $('[name="Q14"]').attr("disabled",false);
            $('[name="Q15"]').attr("disabled",false);
            $('[name="Q24"]').attr("disabled",false);
            $('[name="Q16"]').attr("disabled",false);
            $('[name="Q17"]').attr("disabled",false);
        }
    });

</script>
