<head>
    <link href="res/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="res/css/styles.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="res/js/jquery.js"></script>
    <script type="text/javascript" src="res/js/jquery.min.js"></script>
    <script type="text/javascript" src="res/js/jquery-1.11.1.js"></script>
    <script type="text/javascript" src="res/js/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="res/js/jquery-ui-1.11.4.js"></script>
    <script type="text/javascript" src="res/js/scripts.js"></script>
</head>
<?php
    include('function.php');
    include('function_global.php');
    include('fx_xml.php');
    checkLogin();
    session_start();
    $pUserId = $_SESSION['pUserID'];
    $pAccreNo = $_SESSION['pAccreNum'];
    $pCaseNo = $_GET['caseno'];
?>
<script type="text/javascript">
    $(window).load(function() {
        $("#wait_image").fadeOut("slow");
    });
</script>

<div id="wait_image" align="center" style="display:;margin: 30px 0px;">
    <img src="res/images/LoadingWait.gif" alt="Please Wait" />
</div>
  
<form action="" method="POST">
    <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
        <?php
        if(isset($pCaseNo) && !empty($pCaseNo)) {
            /*Registration*/
            $displayResultEnlist = getRegistration($pCaseNo);    

            /*Health Screening & Assessment*/
            $displayResultProfile = getProfiling($pCaseNo);

            // /*Consultation*/
            $displayResultConsultation= getIndividualConsultation($pCaseNo);

            /*Count Results*/
            $countEnlistXML = count($displayResultEnlist);
            $countProfXML = count($displayResultProfile);
            $countSoapXML = count($displayResultConsultation);
        }
        ?>
    </div>
    <div>
    <?php
        /*Update Transmittal Reference Number*/
        echo "<b>Case Number: ".$pCaseNo."</b>";
        echo "<br/>";
        echo "<b>Patient PIN: ".$displayResultEnlist['PX_PIN']."</b>";
        /*Get Passphrase/Key to generate xml report*/
            $getHCIinfo = getHciProfileKeyPerIndividual($pAccreNo, $pUserId);
            $hciKey = $getHCIinfo['CIPHER_KEY'];
            $pAccreNo = $getHCIinfo['ACCRE_NO'];

        if ($countProfXML > 0) {
            if(!empty($hciKey)){
            /*Generate XML format*/
                $xmlResults = generateXmlPerIndividual($pCaseNo);

            /*Encrypt XML String*/
                include_once('PhilHealthEClaimsEncryptor.php');
                $encryptor = new PhilHealthEClaimsEncryptor();
                $encryptor->setLoggingEnabled(TRUE);
                $encryptor->setPassword1UsingHexStr(null);
                $encryptor->setPassword2UsingHexStr(null);
                $encryptor->setIVUsingHexStr(null);
                $encryptedOutput = $encryptor->encryptXmlPayloadData($xmlResults, $hciKey);
                $logs = print_r($encryptor->getLogs(), true);

            /*Download as File*/
            if ($encryptedOutput == true) {
                
                /*Save as Encrypted File*/
                if ($countEnlistXML > 0 && $countProfXML > 0 && $countSoapXML > 0) {
                    $fileType = "2";
                } else {
                    $fileType = "1";
                }

                $fileName = $fileType.$pAccreNo."_".$pCaseNo."_".$displayResultEnlist['PX_PIN'].".xml.enc";
                $file = file_put_contents("files/Output/".$fileName, $encryptedOutput);
                $filepath = "files/Output/".$fileName;
                
                if(file_exists($filepath)) {
                    echo "<br/>Click the download link below for the encrypted XML file. <br/>File Name: AccreditationNumber_CaseNumber_PatientPIN<br/><br/>";
                    echo "<a href='".$filepath."' download='".$fileName."'>Download Individual Konsulta XML Report</a>";
                }

            }
        }
        } else {
            echo "<p style='color:red;'><br/><br/><b>Note: At least one Health Screening & Assessment and/or Consultation record is required to download the Konsulta XML per individual.</b></p>";
        }  
    ?>
    </div>
</form>