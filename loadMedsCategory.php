<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 9/26/2018
 * Time: 3:50 PM
 */

    require("function.php");
    require("function_global.php");
    $drugs= getMeds($_GET["pMeds"]);

foreach($drugs as $drug){

    echo $drug["CATEGORY"];
}

?>
<script type="text/javascript" language="javascript">
    $(function() {
        $('#pCategory').val('<?php echo $drug["CATEGORY"]; ?>');
    });
</script>