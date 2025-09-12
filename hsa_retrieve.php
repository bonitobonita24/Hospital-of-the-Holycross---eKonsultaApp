<?php
    $page = 'pxRecord';
    include('header.php');
    checkLogin();
    include('menu.php');

    $pCurrentCaseNo = $_GET['caseno'];
    $pPrevFPECaseNo = $_GET['prev_caseno'];
    $pEffYear = $_GET['effyear'];

    $getPxPersonalDetails = getPrevPxRecordEnlist($pPrevFPECaseNo);
    $pPxName =  strReplaceEnye($getPxPersonalDetails['PX_LNAME']).', '.$getPxPersonalDetails['PX_FNAME'].' '.$getPxPersonalDetails['PX_MNAME'].' '.$getPxPersonalDetails['PX_EXTNAME'];
    $pPxPIN = $getPxPersonalDetails['PX_PIN'];
    $pPxSex = $getPxPersonalDetails['PX_SEX'];
    $pPxDoB = date('F d, Y', strtotime($getPxPersonalDetails['PX_DOB']));
    $pDateEnlisted = date('m/d/Y', strtotime($getPxPersonalDetails['ENLIST_DATE']));
    $pPxType = $getPxPersonalDetails['PX_TYPE'];
    $pCaseNo = $getPxPersonalDetails['CASE_NO'];
    $pTransNo = $getPxPersonalDetails['TRANS_NO'];
    $pMobileNo = $getPxPersonalDetails['PX_MOBILE_NO'];
    $pLandlineNo = $getPxPersonalDetails['PX_LANDLINE_NO'];
    switch($pPxType){
      case "MM": 
      $pxType =  "Member";
      break;
      case "DD": 
      $pxType =  "Dependent";
      break;
      default:
      $pxType =  "-";
    }
    switch($pPxSex){
      case "M": 
      $pxSex =  "Male";
      break;
      case "F": 
      $pxSex =  "Female";
      break;
      default:
      $pxSex =  "-";
    }
    //profiling
    $getPxRecordProfileDetails = getPxRecordProfile($pCaseNo);
    $getPXRecordFamHist = getProfilingFamhistPerIndividual($pCaseNo);
    $getPXRecordMedHist = getProfilingMedHistPerIndividual($pCaseNo);
    $getPXRecordSurgHist = getProfilingSurghistPerIndividual($pCaseNo);

    //consultation
    $getPXRecordConsultationDetails = getCarryOverConsultation($pCaseNo);

    //hci address
    $getProvince = describeProvinceAddress($_SESSION['pHospAddProv']);
    $province = $getProvince['PROV_NAME'];
    $getMunicipality = describeMunicipalityAddress($_SESSION['pHospAddMun'], $_SESSION['pHospAddProv']);
    $municipality  = $getMunicipality['MUN_NAME'];
    $getBarangay = describeBarangayAddress($_SESSION['pHospAddBrgy'],$_SESSION['pHospAddMun'], $_SESSION['pHospAddProv']);
    $barangay = $getBarangay['BRGY_NAME'];


    $hciAddress = $barangay.', '.$municipality.', '.$province;

    if(isset($_POST['retrieveFPE'])){
         //echo '<script>alert("test");</script>';
         saveCarryoverProfilingInfo($pCurrentCaseNo,$pPrevFPECaseNo,$pEffYear);
        
    }

?>

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

    <div id="content_div" style="margin: 0px 0px 20px 0px;">
        <div class="panel panel-primary">
            <div class="panel-heading" align="center">
                <h3 class="panel-title">PREVIEW ON THE HEALTH SCREENING & ASSESSMENT <br/> (CARRY-OVER TO THE CURRENT YEAR)</h3>
            </div>
            <div class="panel-body">
                <form action="" method="POST" onsubmit="return confirm('Are you sure you want to use the previous record and carry-over to the current year?');">
                  <div class="alert alert-success">PRIMARY CARE PROVIDER</div>
                   <table  style="width: 100%;">
                    <col width="70%">
                    <col width="30%">
                     <tr>
                       <td>
                         <div style="text-align: left;font-size: 16px;font-weight: bold;"><?php echo strReplaceEnye($_SESSION['pHospName']);?></div>
                         <div style="text-align: left;font-size: 12;"><?php echo strReplaceEnye(utf8_encode($hciAddress));?></div>
                         <div style="text-align: left;font-size: 12;">PAN: <?php echo $_SESSION['pAccreNum'];?></div>
                       </td>
                       <td class="table table-bordered">
                         <div style="text-align: center;font-size: 14px;font-weight: bold;">
                            <?php echo date('F d, Y');?>
                           <br/>
                           <?php echo date('h:i:sa');?>
                         </div>
                         <div style="text-align: left;">
                           Logged User:  <u>&emsp;<?php echo $_SESSION['pUserLname'].', '. $_SESSION['pUserFname'].' '.$_SESSION['pUserMname'];?>&emsp;</u>  
                           <br/>
                           Employee Number: <u>&emsp;<?php echo $_SESSION['pEmdID'];?>&emsp;</u>  
                         </div>
                       </td>
                     </tr>
                   </table>

                   <div class="alert alert-success" style="margin-top: 20px;">CLIENT RECORD</div>
                      <table class="table table-condensed table-bordered" style="width: 100%; text-align: left;">
                        <col width="20%">
                        <col width="80%">
                        <tbody>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Case No. (Previous)</td>
                            <td style="text-align: left;">
                              <?php echo $pCaseNo;?>
                              <input type="hidden" name="prevFPECaseNo" value="<?php echo $pCaseNo; ?>"/>
                            </td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Transaction No. (Previous)</td>
                            <td style="text-align: left;"><?php echo $pTransNo;?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Case No. (Current)</td>
                            <td style="text-align: left;">
                              <?php echo $pCurrentCaseNo;?>
                              <input type="hidden" name="currentCaseNo" value="<?php echo $pCurrentCaseNo; ?>" />
                            </td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Name </td>
                            <td style="text-align: left;"><?php echo $pPxName;?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">PIN </td>
                            <td style="text-align: left;"><?php echo $pPxPIN;?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Type </td>
                            <td style="text-align: left;"><?php echo $pPxType;?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Sex </td>
                            <td style="text-align: left;"><?php echo $pxSex;?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Birthdate </td>
                            <td style="text-align: left;"><?php echo $pPxDoB;?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Age </td>
                            <td style="text-align: left;"><?php $getAge = explode(' ', $getPxRecordProfileDetails["PX_AGE"]);echo $getAge[0];?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Blood Type </td>
                            <td style="text-align: left;"><?php echo $getPxRecordProfileDetails["BLOOD_TYPE"];?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left; font-weight: bold;">Contact No. </td>
                            <td style="text-align: left;"><?php echo $pMobileNo.' '.$pLandlineNo;?></td>
                          </tr>
                        </tbody>
                      </table>

                      <table class="table table-condensed table-bordered" style="width: 100%;">
                        <col width="70%">
                        <col width="30%">
                        <thead>
                          <th>Konsulta Service</th>
                          <th>Service Date</th>
                        </thead>
                        <tbody>
                          <tr>
                            <td style="text-align: left;">History and Physical Examination (vitals, anthorpometrics...) </td>
                            <td><?php echo date('m/d/Y', strtotime($getPxRecordProfileDetails["PROF_DATE"]));?></td>
                          </tr>
                          <tr>
                            <td style="text-align: left;">Consultation/s </td>
                            <td>
                              <?php 
                                foreach($getPXRecordConsultationDetails as $getPXRecordConsultationDetail) {
                                  echo date('m/d/Y', strtotime($getPXRecordConsultationDetail["SOAP_DATE"]));
                                }
                              ?>
                            </td>
                          </tr>
                        </tbody>
                      </table>

                      <table class="table table-condensed table-bordered" style="width: 100%;">
                        <thead>
                          <th>Family History</th>
                          <th>Personal/Social History</th>
                          <th>Past Medical History</th>
                          <th>Past Surgical History</th>
                          <th>OB-Gyne History <i>(if female)</i></th>
                        </thead>
                        <tbody>
                          <tr>
                            <td style="text-align: left;">
                              <?php
                              foreach($getPXRecordFamHist as $getPXRecordFamHists){
                                $medDiseaseDesc = describeMedDisease($getPXRecordFamHists['MDISEASE_CODE']);
                                echo $medDiseaseDesc['MDISEASE_DESC'];
                                echo "<br/>";
                              }
                              ?>
                            </td>
                            <td style="text-align: left;">
                              Smoking: <u>&emsp;<?php echo $getPxRecordProfileDetails["IS_SMOKER"];?>&emsp;</u>
                              <br/>
                              Alcohol: <u>&emsp;<?php echo $getPxRecordProfileDetails["IS_ADRINKER"];?>&emsp;</u>
                              <br/>
                              Illicit Drugs: <u>&emsp;<?php echo $getPxRecordProfileDetails["ILL_DRUG_USER"];?>&emsp;</u>
                              <br/>
                              Sexually Active: <u>&emsp;<?php echo $getPxRecordProfileDetails["IS_SEXUALLY_ACTIVE"];?>&emsp;</u>                              
                            </td>
                            <td style="text-align: left;">
                              <?php
                              foreach($getPXRecordMedHist as $getPXRecordMedHists){
                                $medDiseaseDesc = describeMedDisease($getPXRecordMedHists['MDISEASE_CODE']);
                                echo $medDiseaseDesc['MDISEASE_DESC'];
                                echo "<br/>";
                              }
                              ?>
                            </td>
                            <td style="text-align: left;">
                              <?php
                              foreach($getPXRecordSurgHist as $getPXRecordSurgHists){
                                $surgDesc = $getPXRecordSurgHists['SURG_DESC'];
                                $surgDate = date('m/d/y', strtotime($getPXRecordSurgHists['SURG_DATE']));
                                if ($getPXRecordSurgHists['SURG_DATE'] != "") {
                                  echo $surgDate.' - '.$surgDesc;
                                  echo "<br/>";
                                } else {
                                  echo " -";
                                }
                              }
                              ?>
                            </td>
                            <td style="text-align: left;">
                              <?php
                                if($getPxRecordProfileDetails["IS_APPLICABLE"] == 'N') {
                                  echo "Not Applicable<br/>";
                                } else {
                              ?>
                              Menarche: <u>&emsp;<?php echo $getPxRecordProfileDetails["MENARCHE_PERIOD"];?>&emsp;</u>
                              <br/>
                              LMP: <u>&emsp;<?php echo $getPxRecordProfileDetails["LAST_MENS_PERIOD"];?>&emsp;</u>
                              <br/>
                              Period Duration: <u>&emsp;<?php echo $getPxRecordProfileDetails["PERIOD_DURATION"];?>&emsp;</u>
                              <br/>
                              G: <u> <?php echo $getPxRecordProfileDetails["PREG_CNT"];?> </u>&emsp;P: <u> <?php echo $getPxRecordProfileDetails["DELIVERY_CNT"];?> </u>&emsp;
                              (<u> <?php echo $getPxRecordProfileDetails["FULL_TERM_CNT"];?> </u> - <u> <?php echo $getPxRecordProfileDetails["PREMATURE_CNT"];?> </u> - <u> <?php echo $getPxRecordProfileDetails["ABORTION_CNT"];?> </u> - <u> <?php echo $getPxRecordProfileDetails["LIV_CHILDREN_CNT"];?> </u>)
                              <?php } ?>
                            </td>
                          </tr>
                        </tbody>
                      </table>
               </div>
                <div style="text-align: center; margin: 10px 0px 25px 0px">
                    <input type="submit"
                        name="retrieveFPE"
                        id="retrieveFPE"
                        class="btn btn-primary"
                        value="Retrieve Assessment & Save"
                        title="Retrieve Health Screening & Assessment"
                    />
                </div> 
              </form>
            </div>

        </div>
    </div>


    <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
    </div>

</div>

<?php
include('footer.php');
?>
