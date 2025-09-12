<?php
    $page = 'reports';
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    include('header.php');
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
                <form action="" name="frmGenerateReport" method="POST" onsubmit="return confirm('Generate XML now?');">
                    <table border=0 style="margin-top: 20px;" align="center">
                        <tr>
                            <td colspan="2" align="center"><h4><u>Filter by Date Encoded the Record</u></h4></td>
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
                                <input type="submit" name="searchFirstEncounterRecordBtn" class="btn btn-success" id="search" value="Generate First Encounter Record" style="margin-top: 15px;">
                                <input type="submit" name="searchAll" class="btn btn-primary" id="search" value="Generate KONSULTA XML" style="margin-top: 15px;">

                            </td>
                        </tr>
                    </table>

                    <div id="wait_image" align="center" style="display:;margin: 30px 0px;">
                        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
                    </div>

                <?php
                if(isset($_POST['searchAll']) && !empty($_POST['searchAll'])) {
                ?>
                    <!--START DISPLAY SEARCH RESULTS-->
                    <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
                        <?php
                        if(isset($pSDate) && !empty($pSDate) AND isset($pEDate) && !empty($pEDate)) {
                            /*Registration*/
                            $displayResultEnlist = getReportResultEnlistment($pStartDate, $pEndDate);

                            /*Health Screening & Assessment*/
                            $displayResultProfile = getReportResultProfiling($pStartDate, $pEndDate);

                            // /*Start Other details of Profiling*/
                                $displayProfilingMedHist = getReportResultProfilingMedHist($pStartDate, $pEndDate);
                                $displayProfilingMHspecific = getReportResultProfilingMHspecific($pStartDate, $pEndDate);
                                $displayProfilingPemisc = getReportResultProfilingPEmisc($pStartDate, $pEndDate);
                                $displayProfilingSurghist = getReportResultProfilingSurghist($pStartDate, $pEndDate);
                                $displayProfilingFamhist = getReportResultProfilingFamhist($pStartDate, $pEndDate);
                                $displayProfilingFhspecific = getReportResultProfilingFHspecific($pStartDate, $pEndDate);
                                $displayProfilingImmunization = getReportResultProfilingImmunization($pStartDate, $pEndDate);
                                // $displayProfilingDiagnostic= "";
                                // $displayProfilingManagement= "";
                            // /*End Other details of Profiling*/

                            // /*Consultation*/
                            $displayResultMainConsult=getReportResultMainConsult($pStartDate, $pEndDate);
                            $displayResultConsultation=getReportResultConsultation($pStartDate, $pEndDate);
                            // /*Start Other details of Consultation*/
                                $displayResultConsultDiagnostic = getReportResultConsultationDiagnostic($pStartDate, $pEndDate);
                                $displayResultConsultIcd = getReportResultConsultationIcd($pStartDate, $pEndDate);
                                $displayResultConsultManagement = getReportResultConsultationManagement($pStartDate, $pEndDate);
                                $displayResultConsultPemisc = getReportResultConsultationPemisc($pStartDate, $pEndDate);
                            // /*End Other details of Consultation*/

                            // /*Medicine*/
                            $displayResultMedicine = getReportResultMedicine($pStartDate, $pEndDate);

                            // /*Course Ward*/

                            // /*Laboratory Results*/
                            $displayResultLabs=getReportResultLab($pStartDate, $pEndDate);
                            $displayResultLabsCbc=getReportResultLabCbc($pStartDate, $pEndDate);
                            $displayResultLabsUrine=getReportResultLabUrine($pStartDate, $pEndDate);
                            $displayResultLabsFecalysis=getReportResultLabFecalysis($pStartDate, $pEndDate);
                            $displayResultLabsChestXray=getReportResultLabChestXray($pStartDate, $pEndDate);
                            $displayResultLabsSputum=getReportResultLabSputum($pStartDate, $pEndDate);
                            $displayResultLabsLipidProf=getReportResultLabLipidProf($pStartDate, $pEndDate);
                            $displayResultLabsFbs=getReportResultLabFbs($pStartDate, $pEndDate);
                            $displayResultLabsEcg=getReportResultLabEcg($pStartDate, $pEndDate);
                            $displayResultLabsOgtt=getReportResultLabOgtt($pStartDate, $pEndDate);
                            $displayResultLabsPaps=getReportResultLabPapsSmear($pStartDate, $pEndDate);

                            $displayResultLabsFOBT=getReportResultLabFOBT($pStartDate, $pEndDate);
                            $displayResultLabsCreatinine=getReportResultLabCreatinine($pStartDate, $pEndDate);
                            $displayResultLabsPPD=getReportResultLabPDD($pStartDate, $pEndDate);
                            $displayResultLabsHbA1c=getReportResultLabHbA1c($pStartDate, $pEndDate);
                            $displayResultLabsOthDiag=getReportResultLabOthDiag($pStartDate, $pEndDate);
                            $displayResultLabsRbs=getReportResultLabRBS($pStartDate, $pEndDate); //v1.2

                            /*Count Results*/
                            $countEnlistXML = count($displayResultEnlist);
                            $countProfXML = count($displayResultProfile);
                            $countSoapXML = count($displayResultMainConsult);
                            
                        }

                        ?>
                    </div>
                    <div>
                        <?php
                        if(!empty($pSDate) && !empty($pEDate)){


                            // if($test > 0){
                                /*Update Transmittal Reference Number*/
                                echo "<p style='font-size: 15px;font-weight: bold;'>(".$countEnlistXML.") eKONSULTA Registration Record/s<br/>
                                        (".$countProfXML.") Health Screening & Assessment Record/s<br/>
                                        (".$countSoapXML.") Consultation Record/s</p>";
                                /*Get Passphrase/Key to generate xml report*/
                                    $getHCIinfo = getHciProfileInfo($pAccreNo, $pUserId);
                                    $hciKey = $getHCIinfo['CIPHER_KEY'];

                                if(!empty($hciKey)){
                                    /*Generate XML format*/
                                        $xmlResults = generateXml($displayResultEnlist, $displayResultProfile, $displayProfilingMedHist, $displayProfilingMHspecific, 
                                            $displayProfilingPemisc,
                                            $displayProfilingSurghist, $displayProfilingFamhist, $displayProfilingFhspecific, $displayProfilingImmunization,
                                            $displayResultMainConsult, $displayResultConsultation, $displayResultConsultDiagnostic, $displayResultConsultIcd, 
                                            $displayResultConsultManagement, $displayResultConsultPemisc,$displayResultMedicine,
                                            $displayResultLabs, $displayResultLabsCbc, $displayResultLabsUrine, $displayResultLabsFecalysis, $displayResultLabsChestXray, 
                                            $displayResultLabsSputum,
                                            $displayResultLabsLipidProf, $displayResultLabsFbs, $displayResultLabsEcg, $displayResultLabsOgtt, $displayResultLabsPaps,
                                            $pStartDate, $pEndDate,
                                            $displayResultLabsFOBT, $displayResultLabsCreatinine, $displayResultLabsPPD, $displayResultLabsHbA1c, $displayResultLabsOthDiag, $displayResultLabsRbs);

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
                                    
                                        if(file_exists($filepath) && $countEnlistXML > 0) {
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

                <!-- FIRST ENCOUNTER DATA -->
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

                            // /*Start Other details of Profiling*/
                                $displayProfilingMedHist = getReportResultProfilingMedHist($pStartDate, $pEndDate);
                                $displayProfilingMHspecific = getReportResultProfilingMHspecific($pStartDate, $pEndDate);
                                $displayProfilingPemisc = getReportResultProfilingPEmisc($pStartDate, $pEndDate);
                                $displayProfilingSurghist = getReportResultProfilingSurghist($pStartDate, $pEndDate);
                                $displayProfilingFamhist = getReportResultProfilingFamhist($pStartDate, $pEndDate);
                                $displayProfilingFhspecific = getReportResultProfilingFHspecific($pStartDate, $pEndDate);
                                $displayProfilingImmunization = getReportResultProfilingImmunization($pStartDate, $pEndDate);
                            // /*End Other details of Profiling*/

                            // /*Laboratory Results*/
                            $displayResultLabs=getReportResultLabFirstEncounter($pStartDate, $pEndDate);
                            $displayResultLabsFbs=getReportResultLabFbsFirstEncounter($pStartDate, $pEndDate);
                            $displayResultLabsRbs=getReportResultLabRBSFirstEncounter($pStartDate, $pEndDate); //v1.2

                            /*Count Results*/
                            $countEnlistXML = count($displayResultEnlist);
                            $countProfXML = count($displayResultProfile);

                        }

                        ?>
                    </div>
                    <div>
                        <?php
                        if(!empty($pSDate) && !empty($pEDate)){


                            // if($test > 0){
                                /*Update Transmittal Reference Number*/
                                echo "<p style='font-size: 15px;font-weight: bold;'>(".$countEnlistXML.") eKONSULTA Registration Record/s<br/>
                                        (".$countProfXML.") Health Screening & Assessment Record/s</p>";
                                /*Get Passphrase/Key to generate xml report*/
                                    $getHCIinfo = getHciProfileInfo($pAccreNo, $pUserId);
                                    $hciKey = $getHCIinfo['CIPHER_KEY'];

                                if(!empty($hciKey)){
                                    /*Generate XML format*/
                                        $xmlResults = generateXmlFirstEncounter($displayResultEnlist, $displayResultProfile, $displayProfilingMedHist, $displayProfilingMHspecific, 
                                            $displayProfilingPemisc,
                                            $displayProfilingSurghist, $displayProfilingFamhist, $displayProfilingFhspecific, $displayProfilingImmunization,
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
                <?php } ?>

                    <div class="alert alert-info" style="text-align: left;width: 95%;margin-top: 20px;" >
                       Note:<br/>
                        <ul>
                            <li>Generate First Encounter Record:</li>
                            <li>Generate First Encounter Record:</li>
                            <li>Generate Weekly Report:</li>
                        </ul>
                       Generate First Encounter Record - to generate encounter registration and health screening & assessment record per patient within the selected date only.<br/>
                       Generate KONSULTA XML - to generate whole transaction encoded per patient within the selected date.
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