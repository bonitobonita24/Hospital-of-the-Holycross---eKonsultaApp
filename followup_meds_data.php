<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 1/18/2018
 * Time: 4:32 PM
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    $page = 'medicine';
    include('header.php');
    checkLogin();
    include('menu.php');

    /* START GET PATIENT RECORD BEFORE CONSULTATION*/
    if(isset($_GET['case_no'])){
        $pCaseNo = $_GET['case_no'];
        $descPatientInfo = getPatientInfoConsultation($pCaseNo);

        if(!$descPatientInfo) {
            echo "<script>alert('Invalid Case Number!'); window.location='followup_meds_search.php';</script>";
        }
    }
    /* END GET PATIENT RECORD BEFORE CONSULTATION*/
    /* START GET COMPUTATION OF AGE */
    $age = getAge($descPatientInfo['PX_DOB']);
    $yearAge = $age['y'];
    $monAge = $age['m'];
    $dayAge = $age['d'];
    $pxAge = $yearAge.' yrs, '.$monAge.' mos, '.$dayAge.' days';

    if (($yearAge == 0 && $monAge <= 11) || ($yearAge == 1 && $monAge == 0)) {
        $pAgeBracket = 'pedia-one';
    }
    else if (($yearAge == 1 && $monAge <= 23) || ($yearAge == 2 && $monAge == 0)) {
        $pAgeBracket = 'pedia-two';
    }
    else {
        $pAgeBracket = 'non-pedia';
    }
    /* END GET COMPUTATION OF AGE */

    /*INSERT INITIAL DATA OF CONSULTATION*/
    if(isset($_POST['submit'])) {
        saveFollowUpMedicine($_POST);
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
        <form action="" name="consultationForm" method="POST" onsubmit="return validateFollowupMeds();">
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
                            <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
                                <label style="color:red;">*</label><label for="pSoapOTP">Authorization Transaction Code:</label>
                                <br/>
                                <input type="text"
                                       name="pSoapOTP"
                                       id="pSoapOTP"
                                       class="form-control"
                                       style="width: 15%; color: #000; margin: 0px 10px 0px 0px;"
                                       minlength="4"
                                       maxlength="10"
                                       autocomplete="off"
                                       <?php if($pSoapTransNo != null){ ?>
                                       value="<?php echo $descPatientInfo["SOAP_OTP"]; ?>"
                                       readonly
                                       <?php } else{?>
                                       value=""
                                       autofocus
                                       <?php } ?>
                                />
                                <br/>
                                <label style="margin-top:15px;font-size: 11px;font-style: italic;color:red;">Note: ATC should be used within the scheduled date.</label>
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
                                        <td><label>Patient PIN:</label></td>
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
                                                   value="<?php echo $descPatientInfo['PX_LNAME']; ?>"
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
                                                   value="<?php echo $descPatientInfo['PX_FNAME']; ?>"
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
                                                   value="<?php echo $descPatientInfo['PX_MNAME']; ?>"
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
                                                   value="<?php echo $descPatientInfo['PX_EXTNAME']; ?>"
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
                                                   value="<?php echo $descPatientInfo['PX_CONTACTNO']; ?>"
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
                                                   value="<?php echo $pxAge; ?>"
                                                   style="width: 200px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                   autocomplete="off"
                                                   readonly
                                            />
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
<!--                    <div class="tabbable">-->
<!--                        <ul class="nav nav-pills nav-justified" data-tabs="tabs" style="margin-top: 1px; margin-bottom: 5px;">-->
<!--                            <li class="active"><a href="#tab1" data-toggle="tab" class="" style="text-align: center;">Medicine</a></li>-->
<!--                        </ul>-->
<!--                    </div>-->
                        <!--START TAB MEDICINE-->
                        <div class="tab-pane fade in active" id="tab1">
                            <div class="panel panel-primary">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Medicine</h3>
                                </div>
                                <div class="panel-body">
                                    <fieldset style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;">
                                        <div class="alert alert-success" style="margin-bottom: 0px">
                                            <strong style="font-size: 16px">DRUG PRESCRIPTION</strong>
                                        </div>
                                        <table style="width:20%;margin:15px 0px 20px 0px;">
                                            <tr>
                                                <td><label style="color: red">*</label><label>Prescribing Physician:</label></td>
                                                <td><label style="color: red">*</label><label>Consultation Date</label></td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <input type="text"
                                                           name="pPrescDoctor"
                                                           id="pPrescDoctor"
                                                           class="form-control"
                                                           value=""
                                                           style="width: 250px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                           autocomplete="off"
                                                           maxlength="100"
                                                           placeholder="NAME OF PHYSICIAN"
                                                    />
                                                </td>
                                                <td>
                                                    <input type="text"
                                                           name="pSOAPDate"
                                                           id="pSOAPDate"
                                                           class="datepicker form-control"
                                                           value=""
                                                           style="width: 150px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase;text-align:center"
                                                           autocomplete="off"
                                                           placeholder="mm/dd/yyyy"
                                                           onkeypress="formatDate('pSOAPDate')"
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                        <table id="tblPrescribeMeds" class="table table-bordered table-condensed" style="width:100%">
                                            <tr>
                                                <td>
                                                    <table>
                                                        <tr>
                                                            <td><label style="color: red">*</label><label style="text-decoration: underline;">MEDICINE</label></td>
                                                        </tr>
                                                    </table>
                                                    <table style="margin-top: 5px; text-align: left;">
                                                        <tr>
                                                            <th><label style="font-size:13px;">Complete Drug Description</label></th>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <select name="pDrugCode" id="pDrugCode" class="form-control" style="width:300px;margin:0px 10px 0px 0px;" onChange="loadMedsGeneric(this.value);loadMedsStrength(this.value);loadMedsForm(this.value);loadMedsPackage(this.value);loadMedsCopay();loadMedsInsStrength(this.value);loadMedsSalt(this.value);loadMedsUnit(this.value);">
                                                                    <option value selected="selected" disabled>Select Drug Name</option>
                                                                    <?php
                                                                    $pLibDrugs = listDrugsDesc();
                                                                    foreach ($pLibDrugs as $pLibDrug){
                                                                        $drugCode= $pLibDrug['DRUG_CODE'];
                                                                        $drugDesc= $pLibDrug['DRUG_DESC'];
                                                                        ?>
                                                                        <option value="<?php echo $drugCode;?>"><?php echo $drugDesc;?></option>
                                                                    <?php } ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <table style="margin-top: 15px; text-align: left;">
                                                        <tr>
                                                            <th><label style="font-style: italic;font-weight: normal;">Generic Name</label></th>
                                                            <th><label style="font-style: italic;font-weight: normal;">Salt</label></th>
                                                            <th><label style="font-style: italic;font-weight: normal;">Strength</label></th>
                                                            <th><label style="font-style: italic;font-weight: normal;">Form</label></th>
                                                            <th><label style="font-style: italic;font-weight: normal;">Unit</label></th>
                                                            <th><label style="font-style: italic;font-weight: normal;">Package</label></th>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <select name="pGeneric" id="pGeneric" class="form-control" style="width:220px;margin:0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="pSalt" id="pSalt" class="form-control" style="width:100px;margin:0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="pStrength" id="pStrength" class="form-control" style="width:140px;margin:0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="pForm" id="pForm" class="form-control" style="width:150px; margin:0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="pUnit" id="pUnit" class="form-control" style="width:100px;margin:0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="pPackage" id="pPackage" class="form-control" style="width:100px; margin:0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <table style="margin-top: 15px; text-align: left;">
                                                        <tbody>
                                                        <tr>
                                                            <td>
                                                                <label style="font-size:11px;color: red;"><i>Note: If Medicine is not available in the list, kindly input  the drug description below as required:</i></label><br/>
                                                                <label style="font-size:13px;">Generic Name/Salt/Strength/Form/Unit/Package</label>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                       name="pGenericFreeText"
                                                                       id="pGenericFreeText"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                       autocomplete="off"
                                                                       maxlength="500"
                                                                />
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="margin-top: 15px; text-align: left;">
                                                        <tr>
                                                            <td><label style="font-size:13px;">Quantity</label></td>
                                                            <td><label style="font-size:13px;">Actual Unit Price</label></td>
                                                            <td><label style="font-style: italic;font-weight: normal;">Co-Payment</label></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <input type="text"
                                                                       name="pQuantity"
                                                                       id="pQuantity"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 80px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                       autocomplete="off"
                                                                       maxlength="5"
                                                                       onkeypress="return isNumberKey(event);"
                                                                />
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                       name="pUnitPrice"
                                                                       id="pUnitPrice"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 100px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                       autocomplete="off"
                                                                       maxlength="6"
                                                                       onkeypress="return isNumberWithDecimalKey(event);"
                                                                />
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                       name="pCoPayment"
                                                                       id="pCoPayment"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 80px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                       autocomplete="off"
                                                                       maxlength="6"
                                                                       readonly
                                                                />
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <table style="margin-top: 15px; text-align: left;">
                                                        <thead>
                                                        <tr>
                                                            <th colspan="3"><label style="color: red">*</label><label><u>INSTRUCTION</u></label></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <tr>
                                                            <td><label style="font-size:13px;">Quantity</label></td>
                                                            <td><label style="font-size:13px;">Strength</label></td>
                                                            <td><label style="font-size:13px;">Frequency</label></td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <select name="pQtyInstruction" id="pQtyInstruction" class="form-control" style="width:100px;margin: 0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                    <?php
                                                                    $quantity = getQuantity(true, '');
                                                                    foreach($quantity as $key => $value) {
                                                                        ?>
                                                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                       name="pStrengthInstruction"
                                                                       id="pStrengthInstruction"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 120px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                       autocomplete="off"
                                                                       maxlength="100"
                                                                />
                                                            </td>
                                                            <td>
                                                                <select name="pFrequencyInstruction" id="pFrequencyInstruction" class="form-control" style="width:150px;margin: 0px 10px 0px 0px;">
                                                                    <option value selected="selected"></option>
                                                                    <?php
                                                                    $frequency = getFrequency(true, '');
                                                                    foreach($frequency as $key => $value) {
                                                                        ?>
                                                                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                                <td>
                                                    <input type="button"
                                                           name="btnAddMeds"
                                                           id="btnAddMeds"
                                                           class="btn btn-warning"
                                                           style="color:#000000;margin: 100px 0px 0px 0px;"
                                                           onclick="addMedicine();"
                                                           value="Add Medicine"
                                                           title="Add Medicine"
                                                    />
                                                </td>
                                            </tr>
                                        </table>
                                        <!--START DISPLAY ADDED MEDICINE-->
                                        <div style="font-weight: normal;font-style: italic;font-size:11px;color:#8b0000">Click 'Add Medicine' button to add medicine in the list.</div>
                                        <table id="tblResultsMeds" class="table table-bordered table-hover" style="font-weight: normal;font-size:11px;">
                                            <thead>
                                            <tr>
                                                <th colspan="5">List of Medicine</th>
                                                <th colspan="3">Instruction</th>
                                                <th rowspan="2"></th>
                                            </tr>
                                            <tr>
                                                <th style='vertical-align: middle;'>Complete Drug Description</th>
                                                <th style='vertical-align: middle;'>Quantity</th>
                                                <th style='vertical-align: middle;'>Actual Unit Price</th>
                                                <th style='vertical-align: middle;'>Copayment</th>
                                                <th style='vertical-align: middle;'>Total Amount Price</th>
                                                <th style='vertical-align: middle;'>Quantity</th>
                                                <th style='vertical-align: middle;'>Strength</th>
                                                <th style='vertical-align: middle;'>Frequency</th>
                                            </tr>
                                            </thead>
                                            <tbody id="tblBodyMeds">
                                            <?php
                                            if($hsa_transNo != null) {
                                                for ($i = 0; $i < count($descMedicine); $i++) {
                                                    $pDrugCode = $descMedicine[$i]['DRUG_CODE'];
                                                    $pGenCode = $descMedicine[$i]['GEN_CODE'];
                                                    $pSaltCode = $descMedicine[$i]['SALT_CODE'];
                                                    $pStreCode = $descMedicine[$i]['STRENGTH_CODE'];
                                                    $pFormCode = $descMedicine[$i]['FORM_CODE'];
                                                    $pUnitCode = $descMedicine[$i]['UNIT_CODE'];
                                                    $pPackCode = $descMedicine[$i]['PACKAGE_CODE'];
                                                    $pQty = $descMedicine[$i]['QUANTITY'];
                                                    $pActualPrice = $descMedicine[$i]['DRUG_ACTUAL_PRICE'];
                                                    $pTotalPrice = $descMedicine[$i]['AMT_PRICE'];
                                                    $pInsQty = $descMedicine[$i]['INS_QUANTITY'];
                                                    $pInsStre = $descMedicine[$i]['INS_STRENGTH'];
                                                    $pInsFreq = $descMedicine[$i]['INS_FREQUENCY'];
                                                    $pCoPayment = $descMedicine[$i]['CO_PAYMENT'];

                                                    if ($i % 2 != 1) {
                                                        echo '<tr style="background-color: #FBFCC7;">';
                                                    } else {
                                                        echo '<tr>';
                                                    }
                                                    echo '<td>' . $pGenCode . '</td>';
                                                    echo '<td>' . $pQty . '</td>';
                                                    echo '<td>' . $pActualPrice . '</td>';
                                                    echo '<td>' . $pCoPayment . '</td>';
                                                    echo '<td>' . $pTotalPrice . '</td>';
                                                    echo '<td>' . $pInsQty . '</td>';
                                                    echo '<td>' . $pInsStre . '</td>';
                                                    echo '<td>' . $pInsFreq . '</td>';
                                                    echo '<td></td>';
                                                    echo '</tr>';
                                                }
                                            }
                                            ?>
                                            </tbody>
                                        </table> <!--END DISPLAY ADDED MEDICINE-->
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                        <!--END TAB MEDICINE-->

                    <div align="left"><font color="red" style="font-size: 10px; font-family: Verdana, Geneva, sans-serif;"><i>NOTE: All fields marked with asterisk (*) are required.</i></font></div>
                    <div align="center">
                        <table>
                            <tr style="height: 10px;">
                                <td></td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <input type="button"
                                           name="goBackSearch"
                                           id="goBackSearch"
                                           class="btn btn-primary"
                                           style="margin-left: 10px;background:#006dcc"
                                           onclick="window.location='followup_meds_search.php'"
                                           value="Go Back to Search Module">

                                    <input type="submit"
                                           name="submit"
                                           id="submit"
                                           class="btn btn-success"
                                           style="margin-left: 10px;"
                                           value="Save Patient Record">
                                </td>
                            </tr>
                        </table>
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

    $(function() {
        if($("#pPatientSexX").val() == 'FEMALE'){
            $("#obligated_service_4_yes").attr("disabled",true);
            $("#obligated_service_4_no").attr("disabled",true);
            $("#obligated_service_4_w").attr("disabled",true);
        }
        else{
            $("#obligated_service_2_yes").attr("disabled",true);
            $("#obligated_service_2_no").attr("disabled",true);
            $("#obligated_service_2_w").attr("disabled",true);
            $("#obligated_service_3_yes").attr("disabled",true);
            $("#obligated_service_3_no").attr("disabled",true);
            $("#obligated_service_3_w").attr("disabled",true);
            $("#obligated_service_4_yes").attr("disabled",false);
            $("#obligated_service_4_no").attr("disabled",false);
            $("#obligated_service_4_w").attr("disabled",false);
        }
    });
</script>
