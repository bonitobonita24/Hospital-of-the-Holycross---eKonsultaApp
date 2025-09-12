<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 3/22/2018
 * Time: 11:06 AM
 */
    require("function.php");
    require("function_global.php");
    //$drugs= getMedsForm($_GET["pMeds"]);
    $drugs= getMeds($_GET["pMeds"]);
?>
<!--    <option value="" selected="selected" disabled>Select Form</option>-->
<?php
    foreach($drugs as $drug){
        $descForm = descMedsForm($drug["FORM_CODE"]);
?>
    <option value="<?php echo $descForm["FORM_CODE"]; ?>"><?php echo $descForm["FORM_DESC"]; ?></option>
<?php } ?>
