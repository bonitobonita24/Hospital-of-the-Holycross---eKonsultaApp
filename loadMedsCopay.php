<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 8/30/2018
 * Time: 11:07 AM
 */
require("function.php");
require("function_global.php");
$genCode = $_GET["mDrugCode"];
$copayMeds= getMedsCopay($genCode);

if($copayMeds != NULL) {
    foreach ($copayMeds as $copayMed) {
        session_start();
        if ($_SESSION['pHospSector'] == 'G') {
            $mCopay = $copayMed["GOVERNMENT_COPAY"];
            echo $mCopay;
        } else if ($_SESSION['pHospSector'] == 'P') {
            $mCopay = $copayMed["PRIVATE_COPAY"];
            echo $mCopay;
        } else {
            $mCopay = 0.00;
            echo $mCopay;
        }
    }
}else{
    $mCopay = 0.00;
}
?>
<script type="text/javascript" language="javascript">
    $(function() {
        $('#pCoPayment').val('<?php echo $mCopay; ?>');
    });
</script>
