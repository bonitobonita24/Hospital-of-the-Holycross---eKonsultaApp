<?php
    require("function.php");
    $zipCode = getZipCode($_GET["pMunCode"], $_GET["pProvCode"]);

    foreach($zipCode as $zipcode){
        echo $zipcode["ZIP_CODE"];
    }
?>
<script type="text/javascript" language="javascript">
    $(function() {
        $('#pPatientZIPCode').val('<?php echo $zipcode["ZIP_CODE"]; ?>');
    });

    $(function() {
        $('#pHospZIPCode').val('<?php echo $zipcode["ZIP_CODE"]; ?>');
    });
</script>
