<?php
    $page = 'reports';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(0);
    include('header.php');
    include('fx_xml.php');
    checkLogin();
    include('menu.php');

    $pSDate=$_POST['pStartDate'];
    $pEDate=$_POST['pEndDate'];
    $pStartDate = date('Y-m-d',strtotime($pSDate));
    $pEndDate = date('Y-m-d',strtotime($pEDate));

?>

<style>
    .table td,
    .table th {
        text-align: center;
    }
    legend {
        background-color: #FBFCC7;
    }
</style>

<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"></div>
        <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
    </div>
</div>

<div id="content">
    <div id="content_div" align="center" style="margin: 0px 0px 20px 0px;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">GENERATE KONSULTA XML REPORT MODULE</h3>
            </div>
            <div class="panel-body">
                <form action="" name="frmGenerateReport" method="POST" onsubmit="compareYears('pStartDate', 'pEndDate')">
                    <table border=0 style="margin-top: 20px;" align="center">
                        <tr>
                            <td colspan="2" align="center"><h4><u>Search by FPE or Consultation Date</u></h4></td>
                        </tr>
                        <tr style="height: 10px;">
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td><label>Start Date:</label></td>
                            <td><label>End Date:</label></td>
                        </tr>
                        <tr>
                            <td><input type="text" name="pStartDate" id="pStartDate" class="datepicker form-control" style=" margin: 0px 10px 0px 0px;width: 98%;" value="<?php echo $pSDate;?>" autocomplete="off" placeholder="mm/dd/yyyy" onkeyup="formatDate('pStartDate');" required></td>
                            <td><input type="text" name="pEndDate" id="pEndDate" class="datepicker form-control" style="margin: 0px 10px 0px 0px;width: 98%;" value="<?php echo $pEDate;?>" placeholder="mm/dd/yyyy" autocomplete="off" onkeyup="formatDate('pEndDate');" required></td>
                        </tr>
                        <tr style="height: 10px;">
                            <td colspan="2"></td>
                        </tr>
                        <tr>                            
                            <td colspan="2" align="center">
                                <input type="submit" name="searchFirstEncounterRecordBtn" class="btn btn-primary" id="search" value="Generate First Encounter Record" style="margin-top: 15px;">
                                <input type="submit" name="searchAll" class="btn btn-primary" id="search" value="Generate KONSULTA XML" style="margin-top: 15px;">

                            </td>
                        </tr>
                    </table>

                    <div id="wait_image" align="center" style="display:;margin: 30px 0px;">
                        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
                    </div>

                <!--
                GENERATION OF XML WITH CONSULTATION/S
                -->
                <?php
                if(isset($_POST['searchAll']) && !empty($_POST['searchAll'])) {
                ?>
                    <!--START DISPLAY SEARCH RESULTS-->
                    <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
                        <?php
                        if(isset($pSDate) && !empty($pSDate) AND isset($pEDate) && !empty($pEDate)) {
                            /* Consultation **/
                            $vGetCaseNo = getCaseConsultation($pStartDate, $pEndDate);
                            $vGetConsultation = getConsultation($pStartDate, $pEndDate);
                            $vCntTotalConsultation = count($vGetConsultation);

                            /*Registration*/
                            $vCntRegistration = count($vGetCaseNo);
                            $displayResultEnlist = getReportResultEnlistment($pStartDate, $pEndDate);

                            /*Health Screening & Assessment*/
                            $vCntProfile = count($vGetCaseNo);
                            $displayResultProfile = getReportResultProfiling($pStartDate, $pEndDate);
                        }
                        ?>
                    </div>

                    <div>
                        <?php
                        if(!empty($pSDate) && !empty($pEDate)){
                            /*Update Transmittal Reference Number*/
                            echo "
                                <p style='font-size: 15px;font-weight: bold;'>
                                    (".count($vGetCaseNo).") Health Screening & Assessment Record/s <br/>
                                    (".$vCntTotalConsultation.") Consultation Record/s
                                </p>
                                ";
                                
                            /*Get Passphrase/Key to generate xml report*/
                                $getHCIinfo = getHciProfileInfo($pAccreNo, $pUserId);
                                $hciKey = $getHCIinfo['CIPHER_KEY'];

                            if(!empty($hciKey)){
                                /*Generate XML format*/
                                    $xmlResults = generateKonsultaBatchXML($vGetCaseNo, $vGetConsultation);

                                /*Encrypt XML String*/
                                    include_once('PhilHealthEClaimsEncryptor.php');
                                    $encryptor = new PhilHealthEClaimsEncryptor();
                                    $encryptor->setLoggingEnabled(TRUE);
                                    $encryptor->setPassword1UsingHexStr(null);
                                    $encryptor->setPassword2UsingHexStr(null);
                                    $encryptor->setIVUsingHexStr(null);
                                    $encryptedOutput = $encryptor->encryptXmlPayloadData($xmlResults, $hciKey);
                                    $logs = print_r($encryptor->getLogs(), true);

                                /*Success notification*/
                                    
                                /*Download as File*/
                                if ($encryptedOutput == true) {
                                    $pBeginDate=date('Ymd',strtotime($_POST['pStartDate']));
                                    $pLastDate=date('Ymd',strtotime($_POST['pEndDate']));
                                    
                                /*Save as Encrypted File*/
                                $fileName = "2".$pAccreNo."_".$pBeginDate."_".$pLastDate.".xml.enc";
                                $file = file_put_contents("files/Output/".$fileName, $encryptedOutput);
                                $filepath = "files/Output/".$fileName;
                                
                                    if(file_exists($filepath) && $vCntTotalConsultation > 0) {
                                        echo "<a href='".$filepath."' download='".$fileName."'>Download Konsulta XML Report</a>";
                                    } else {
                                        echo '<pre>'; print_r($filepath); echo '</pre>';
                                    }

                                }

                            }
                        }
                        ?>
                    </div>
                    <!--END DISPLAY SEARCH RESULTS-->
                <?php } ?>

                <!--
                GENERATION OF XML FOR FPE ONLY
                -->
                <?php
                if(isset($_POST['searchFirstEncounterRecordBtn']) && !empty($_POST['searchFirstEncounterRecordBtn'])) {
                ?>
                    <!--START DISPLAY SEARCH RESULTS-->
                    <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
                        <?php

                        if(isset($pSDate) && !empty($pSDate) AND isset($pEDate) && !empty($pEDate)) {
                            /*Registration*/
                            $displayResultEnlist = getReportResultEnlistmentFirstEnctr($pStartDate, $pEndDate);

                            /*Health Screening & Assessment*/
                            $displayResultProfile = getReportResultProfiling($pStartDate, $pEndDate);

                            /*Start Other details of Profiling*/
                                $displayProfilingMedHist = getReportResultProfilingMedHist($pStartDate, $pEndDate);
                                $displayProfilingMHspecific = getReportResultProfilingMHspecific($pStartDate, $pEndDate);
                                $displayProfilingPemisc = getReportResultProfilingPEmisc($pStartDate, $pEndDate);
                                $displayProfilingSurghist = getReportResultProfilingSurghist($pStartDate, $pEndDate);
                                $displayProfilingFamhist = getReportResultProfilingFamhist($pStartDate, $pEndDate);
                                $displayProfilingFhspecific = getReportResultProfilingFHspecific($pStartDate, $pEndDate);
                                $displayProfilingImmunization = getReportResultProfilingImmunization($pStartDate, $pEndDate);
                            /*End Other details of Profiling*/

                            /*Laboratory Results*/
                            $displayResultLabs=getReportResultLabFirstEncounter($pStartDate, $pEndDate);
                            $displayResultLabsFbs=getReportResultLabFbsFirstEncounter($pStartDate, $pEndDate);
                            $displayResultLabsRbs=getReportResultLabRBSFirstEncounter($pStartDate, $pEndDate); // v1.2

                            /*Count Results*/
                            $countEnlistXML = count($displayResultEnlist);
                            $countProfXML = count($displayResultProfile);
                        }

                        ?>
                    </div>
                    <div>
                        <?php
                        if(!empty($pSDate) && !empty($pEDate)){
                                /*Update Transmittal Reference Number*/
                                echo "
                                    <p style='font-size: 15px;font-weight: bold;'>
                                        (".$countProfXML.") Health Screening & Assessment Record/s
                                    </p>
                                    ";
                                /*Get Passphrase/Key to generate xml report*/
                                    $getHCIinfo = getHciProfileInfo($pAccreNo, $pUserId);
                                    $hciKey = $getHCIinfo['CIPHER_KEY'];

                                if(!empty($hciKey)){
                                    /*Generate XML format*/
                                        $xmlResults = generateXmlFirstEncounter($displayResultEnlist, $displayResultProfile, $displayProfilingMedHist, $displayProfilingMHspecific, 
                                            $displayProfilingPemisc,$displayProfilingSurghist, $displayProfilingFamhist, $displayProfilingFhspecific, $displayProfilingImmunization,
                                            $pStartDate, $pEndDate, $displayResultLabs, $displayResultLabsFbs, $displayResultLabsRbs);

                                    /*Encrypt XML String*/
                                        include_once('PhilHealthEClaimsEncryptor.php');
                                        $encryptor = new PhilHealthEClaimsEncryptor();
                                        $encryptor->setLoggingEnabled(TRUE);
                                        $encryptor->setPassword1UsingHexStr(null);
                                        $encryptor->setPassword2UsingHexStr(null);
                                        $encryptor->setIVUsingHexStr(null);
                                        $encryptedOutput = $encryptor->encryptXmlPayloadData($xmlResults, $hciKey);
                                        $logs = print_r($encryptor->getLogs(), true);

                                    /*Success notification*/
                                      
                                    /*Download as File*/
                                    if ($encryptedOutput == true) {
                                        $pBeginDate=date('Ymd',strtotime($_POST['pStartDate']));
                                        $pLastDate=date('Ymd',strtotime($_POST['pEndDate']));
                                       
                                        /*Save as Encrypted File*/
                                        $fileName = "1".$pAccreNo."_".$pBeginDate."_".$pLastDate.".xml.enc";
                                        $file = file_put_contents("files/Output/".$fileName, $encryptedOutput);
                                        $filepath = "files/Output/".$fileName;
                                    
                                        if(file_exists($filepath) && $countEnlistXML > 0) {
                                            echo "<a href='".$filepath."' download='".$fileName."'>Download Konsulta XML Report</a>";
                                        }

                                    }

                                }
                        }
                        ?>
                    </div>
                    <!--END DISPLAY SEARCH RESULTS-->
                <?php 
                } 
                ?>

                    <div class="alert alert-info" style="text-align: left;width: 95%;margin-top: 20px;" >
                        <b>Note:</b><br/>
                        <ul style="margin: 0px 0px 0px 25px;">
                            <li>
                                <b>Generate First Encounter Record: </b><br/>
                                Generate the encounter registration and health screening & assessment record for each patient based on the selected date range. This includes capturing all initial encounters within the specified period.
                            </li>
                            <li>
                                <b>Generate KONSULTA XML: </b><br/>
                                Generate a complete XML file of all transactions encoded for each patient within the selected date range. This should include detailed records of all interactions and services provided.
                            </li>
                            <li>
                                <b>Generate Daily/ Weekly Report: </b><br/>
                                It is recommended to generate reports on a daily or weekly basis for better management of file size.
                            </li>
                        </ul>
                    </div>
                </form>
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
</script>