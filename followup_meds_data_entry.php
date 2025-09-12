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
        if($_POST['pDrugCodeMeds'] != NULL || $_POST['pGenericNameMeds'] != NULL) {
            saveFollowUpMedicine($_POST);
        } else{
            echo "<script>alert('Please input at least one medicine!');</script>";
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
        <form action="" name="consultationForm" method="POST">
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
                         <!--ATC Details-->       
                         <input type="button" name="hideShow" id="hideShowBtnATCInfo" class="btn btn-info btn-sm" value="- Hide Details" onclick="showHideBtn()" style="margin-bottom: 10px; " />     
                            <fieldset id="fsATCinfo" style="margin: 0px 0px 10px 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
                              <table border="0" style="width: 100%;" class="table-condensed">
                                <col width="50%">
                                <col width="50%">
                                <tr>
                                    <td colspan="2">                                                        
                                        <input type="radio"
                                             name="walkedInChecker"
                                             id="walkedInChecker_true"
                                             value="Y"   
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
                                               value="N"   
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
                          <!--Client Details-->
                            <fieldset id="fsClientInfo" style="margin: 0px; padding: 20px; background-color: #EDFCF0;text-align: left;">
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
                                                   value="<?php echo $descPatientInfo['PX_MOBILE_NO'].' '.$descPatientInfo['PX_LANDLINE_NO'];  ?>"
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
                                              <div style="float:right;">
                                                <span style="display:inline-block;">                                                                
                                                  <span>
                                                    <input type="radio"
                                                         name="medsStatus"
                                                         id="medsStatusYes"
                                                         style="cursor: pointer; float: left;"                                                         
                                                         value="Y"
                                                         checked="checked" 
                                                         onclick="enableMedicine()" 
                                                    />
                                                    <label for="medsStatusYes" style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer;font-weight: bold;">With prescribe drug/medicine</label>
                                                  </span>
                                                </span>
                                              </div>
                                        </div>
                                        <table style="margin: 15px 0px 0px 20px; text-align: left;width: 100%;">
                                          <col width="20%">
                                          <col width="80%">
                                          <tr>
                                            <td colspan="2">
                                              <table id="tblPrescribeMeds" class="table table-bordered table-condensed" style="width:90%">
                                                  <tr>
                                                      <td style="text-align: left;width: 100%">
                                                        <table style="width: 100%">                                                          
                                                          <col width="30%">
                                                          <col width="25%">
                                                          <col width="30%">
                                                          <col width="15%">
                                                          <tr>
                                                            <td>
                                                              <p style="font-size: 11px;font-weight: bold;text-align: left">Prescribing Physician:</p>
                                                              <input type="text"
                                                                       name="pPrescDoctor"
                                                                       id="pPrescDoctor"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 95%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase;"
                                                                       autocomplete="off"
                                                                       maxlength="100"
                                                                       placeholder="NAME OF PRESCRIBING PHYSICIAN"
                                                                />
                                                            </td>
                                                            <td>
                                                              <p style="font-size: 11px;font-weight: bold;text-align: left;">Is Drug/Medicine dispensed?:</p>
                                                              <input type="radio"
                                                                     name="radDispense"
                                                                     id="radDispenseY"
                                                                     value="Y"
                                                                     style="cursor: pointer; float: left;"
                                                                     onclick="setDisabled('<?php echo "pDispensedDate";?>', false);setDisabled('<?php echo "pDispensingPersonnel";?>', false);"
                                                                     checked
                                                              />
                                                              <label for="radDispenseY" style="cursor: pointer;float:left; font-weight: normal;margin:2px 15px 0px 2px;">Yes</label>

                                                              <input type="radio"
                                                                     name="radDispense"
                                                                     id="radDispenseN"
                                                                     value="N"
                                                                     style="cursor: pointer; float: left;"
                                                                     onclick="setDisabled('<?php echo "pDispensedDate";?>', true);setDisabled('<?php echo "pDispensingPersonnel";?>', true);"    
                                                              />
                                                              <label for="radDispenseN" style="cursor: pointer;float:left; font-weight: normal;margin:2px 0px 0px 2px;">No</label>                                                
                                                            </td>                                                            
                                                            <td>
                                                              <p style="font-size: 11px;font-weight: bold;text-align: left">Dispensing Personnel:</p>
                                                              <input type="text"
                                                                       name="pDispensingPersonnel"
                                                                       id="pDispensingPersonnel"
                                                                       class="form-control"
                                                                       value=""
                                                                       style="width: 95%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase;"
                                                                       autocomplete="off"
                                                                       maxlength="100"
                                                                       placeholder="NAME OF DISPENSING PERSONNEL"
                                                                />
                                                            </td>
                                                            <td>
                                                              <p style="font-size: 11px;font-weight: bold;text-align: left;">Dispense Date:</p>
                                                              <input type="text"
                                                                   name="pDispensedDate"
                                                                   id="pDispensedDate"
                                                                   class="datepicker form-control"
                                                                   value=""
                                                                   style="width: 95%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                   autocomplete="off"
                                                                   maxlength="100"
                                                                   placeholder="MM/DD/YYYY"
                                                              />
                                                            </td>
                                                          </tr>
                                                        </table>
                                                      </td>  
                                                  </tr>
                                                  <tr>
                                                      <td>
                                                          <table>
                                                              <tr>
                                                                  <td><label style="text-decoration: underline;">DRUG/MEDICINE</label></td>
                                                              </tr>
                                                          </table>
                                                          <table style="margin-top: 5px; text-align: left;">
                                                              <tr>
                                                                  <th><label style="font-size:13px;">Drug/Medicine [Complete Details]</label></th>
                                                              </tr>
                                                              <tr>
                                                                  <td style="text-align: left;">
                                                                      <select name="pDrugCode" id="pDrugCode" class="chosen-select form-control" style="width:450px;margin:0px 10px 0px 0px;" onChange="loadMedsGeneric(this.value);loadMedsStrength(this.value);loadMedsForm(this.value);loadMedsPackage(this.value);loadMedsCopay();loadMedsInsStrength(this.value);loadMedsSalt(this.value);loadMedsUnit(this.value);">  
                                                                      <option value="" selected disabled>Select Drug/Medicine</option>                             
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
                                                                  <!-- <td>
                                                                      <input type="text"
                                                                             name="pGenericFreeText"
                                                                             id="pGenericFreeText"
                                                                             class="form-control"
                                                                             value=""
                                                                             style="width: 450px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                             autocomplete="off"
                                                                             maxlength="500"
                                                                             placeholder="Generic Name/ Salt/ Strength/ Form/ Unit/ Package"
                                                                      />
                                                                  </td> -->
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
                                                                      <select name="pGeneric" id="pGeneric" class="form-control" style="width:220px;margin:0px 10px 0px 0px;" readonly>
                                                                          <!-- <option value></option> -->
                                                                      </select>
                                                                  </td>
                                                                  <td>
                                                                      <select name="pSalt" id="pSalt" class="form-control" style="width:100px;margin:0px 10px 0px 0px;" readonly>
                                                                          <!-- <option value></option> -->
                                                                      </select>
                                                                  </td>
                                                                  <td>
                                                                      <select name="pStrength" id="pStrength" class="form-control" style="width:140px;margin:0px 10px 0px 0px;" readonly>
                                                                          <!-- <option value></option> -->
                                                                      </select>
                                                                  </td>
                                                                  <td>
                                                                      <select name="pForm" id="pForm" class="form-control" style="width:150px; margin:0px 10px 0px 0px;" readonly>
                                                                          <!-- <option value></option> -->
                                                                      </select>
                                                                  </td>
                                                                  <td>
                                                                      <select name="pUnit" id="pUnit" class="form-control" style="width:100px;margin:0px 10px 0px 0px;" readonly>
                                                                          <!-- <option value></option> -->
                                                                      </select>
                                                                  </td>
                                                                  <td>
                                                                      <select name="pPackage" id="pPackage" class="form-control" style="width:150px; margin:0px 10px 0px 0px;" readonly>
                                                                          <!-- <option value></option> -->
                                                                      </select>
                                                                  </td>
                                                              </tr>
                                                          </table>
                                                          <table style="margin-top: 5px; text-align: left;">
                                                              <tr>
                                                                <td>
                                                                    <input type="checkbox"
                                                                           name="chkOthMeds"
                                                                           id="chkOthMeds"
                                                                           value="Y"
                                                                           style="cursor: pointer; float: left;"
                                                                           onchange ="enableDisableOthMeds();" 
                                                                    />
                                                                    <label for="chkOthMeds" style="margin: 4px 0px 0px 2px; font-weight: bold; cursor: pointer;font-size: 13px;">Other Drug/Medicine [If not available in the list of library]</label>
                                                                </td>
                                                                <td>
                                                                  <!-- <th><label style="font-size:13px;">Other Drug/Medicine [If not available in the list of library]</label></th> -->
                                                                    <label style="font-size:13px;">Drug Grouping</label>
                                                                </td>
                                                              </tr>
                                                              <tr>
                                                                  <td>
                                                                      <input type="text"
                                                                             name="pGenericFreeText"
                                                                             id="pGenericFreeText"
                                                                             class="form-control"
                                                                             value=""
                                                                             style="width: 450px; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                             autocomplete="off"
                                                                             maxlength="500"
                                                                             placeholder="Generic Name/ Salt/ Strength/ Form/ Unit/ Package"
                                                                             disabled
                                                                      />
                                                                  </td>
                                                                  <td>
                                                                      <select name="pOthMedDrugGrouping" id="pOthMedDrugGrouping" class="form-control" style="width:250px; margin:0px 10px 0px 0px;" disabled>
                                                                          <option value selected="selected" disabled>SELECT DRUG GROUPING</option>
                                                                          <option value="NCD">NCD</option>
                                                                          <option value="ANTIBIOTIC">ANTIBIOTIC</option>
                                                                          <option value="OTHERS">OTHERS</option>
                                                                      </select>
                                                                  </td>
                                                              </tr>
                                                          </table>
                                                          <table style="margin-top: 15px; text-align: left;">
                                                              <tr>
                                                                  <td><label style="font-size:13px;">Quantity</label></td>
                                                                  <td><label style="font-size:13px;">Actual Unit Price</label></td>
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
                                                                      Php
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
                                                              </tr>
                                                          </table>
                                                      </td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                          </table>

                                              <table style="margin: 15px 0px 0px 20px; text-align: left;width: 100%">
                                                  <tr style="font-weight: bold;">
                                                      <td>Advise</td>
                                                  </tr>
                                                  <tr>
                                                    <td>
                                                      <table id="tblPrescribeMeds" class="table table-bordered table-condensed" style="width:90%">
                                                      <tr>
                                                          <td colspan="3"><label>Medicine Instruction</label></td>
                                                      </tr>
                                                      <tr>
                                                          <td><label style="font-size:13px;">Quantity</label></td>
                                                          <td><label style="font-size:13px;">Strength</label></td>
                                                          <td><label style="font-size:13px;">Frequency</label></td>
                                                      </tr>
                                                      <tr>
                                                          <td>
                                                            <input type="text"
                                                                     name="pQtyInstruction"
                                                                     id="pQtyInstruction"
                                                                     class="form-control"
                                                                     value=""
                                                                     style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                     autocomplete="off"
                                                                     maxlength="50"
                                                            />
                                                             <!--  -->
                                                          </td>
                                                          <td>
                                                              <input type="text"
                                                                     name="pStrengthInstruction"
                                                                     id="pStrengthInstruction"
                                                                     class="form-control"
                                                                     value=""
                                                                     style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                     autocomplete="off"
                                                                     maxlength="100"
                                                              />
                                                          </td>
                                                          <td>
                                                             <input type="text"
                                                                     name="pFrequencyInstruction"
                                                                     id="pFrequencyInstruction"
                                                                     class="form-control"
                                                                     value=""
                                                                     style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase"
                                                                     autocomplete="off"
                                                                     maxlength="50"
                                                            />
                                                          </td>
                                                        </tr>                                                                
                                                        <tr>
                                                            <td colspan="3"><label for="advice_remarks">Remarks:</label></td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="3">
                                                                <textarea name="advice_remarks"
                                                                          id="advice_remarks"
                                                                          class="form-control"
                                                                          style="width: 100%; color: #000; margin: 0px 10px 0px 0px; text-transform: uppercase; resize: none;"
                                                                          autocomplete="off"
                                                                          rows="2"
                                                                ><?php
                                                                    if($pSoapTransNo != null) {
                                                                        echo $descPatientInfo['REMARKS'];
                                                                    } ?></textarea>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                  </td>
                                                </tr>
                                              </table>
                                              <div>
                                                <input type="button"
                                                       name="btnAddMeds"
                                                       id="btnAddMeds"
                                                       class="btn btn-warning"
                                                       style="color:#000000;margin: 10px 0px 10px 0px;"
                                                       onclick="addMedicineFollowups();"
                                                       value="Add Medicine"
                                                       title="Add Medicine"
                                                />
                                              </div>
                                              <!--START DISPLAY ADDED MEDICINE-->
                                              <div style="font-weight: normal;font-style: italic;font-size:11px;color:#8b0000">
                                                Click 'Add Medicine' button to add drug/medicine on the list.
                                              </div>
                                              <table id="tblResultsMeds" class="table table-bordered table-hover" style="font-weight: normal;font-size:11px;width: 90%;margin-left: 20px;">
                                                  <thead>
                                                  <tr>
                                                      <th colspan="5">List of Drug/Medicine</th>
                                                      <th colspan="3">Instruction</th>
                                                      <th colspan="2">Dispensing Section</th>
                                                      <th rowspan="2"></th>
                                                  </tr>
                                                  <tr>
                                                      <th style='vertical-align: middle;'>Medicine <br/>Strength/ Form/ Volume</th>
                                                      <th style='vertical-align: middle;'>Drug Grouping <br/>(For Other Medicine)</th>
                                                      <th style='vertical-align: middle;'>Quantity</th>
                                                      <th style='vertical-align: middle;'>Actual Unit Price</th>
                                                      <th style='vertical-align: middle;'>Total Amount Price</th>
                                                      <th style='vertical-align: middle;'>Quantity</th>
                                                      <th style='vertical-align: middle;'>Strength</th>
                                                      <th style='vertical-align: middle;'>Frequency</th>                                                      
                                                      <th style='vertical-align: middle;'>Is Drug/Medicine dispensed?</th>
                                                      <th style='vertical-align: middle;'>Dispensed Date</th>
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
                                                          $pOthDrugGrouping = $descMedicine[$i]['DRUG_GROUPING'];
                                                          // $pCoPayment = $descMedicine[$i]['CO_PAYMENT'];

                                                          if ($i % 2 != 1) {
                                                              echo '<tr style="background-color: #FBFCC7;">';
                                                          } else {
                                                              echo '<tr>';
                                                          }
                                                          echo '<td>' . $pGenCode . '</td>';
                                                          echo '<td>' . $pOthDrugGrouping . '</td>';
                                                          echo '<td>' . $pQty . '</td>';
                                                          echo '<td>' . $pActualPrice . '</td>';
                                                          // echo '<td>' . $pCoPayment . '</td>';
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
                                           value="Go Back to Search Module"
                                    />

                                    <input type="submit"
                                           name="submit"
                                           id="submit"
                                           class="btn btn-success"
                                           style="margin-left: 10px;"
                                           value="Save"
                                           onclick="return validateFollowupMeds();"
                                    />
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
    $(".chosen-select").chosen();

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
