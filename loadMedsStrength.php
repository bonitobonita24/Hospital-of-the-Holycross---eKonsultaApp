<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 3/19/2018
 * Time: 9:49 AM
 */
    require("function.php");
    require("function_global.php");
    $drugs= getMeds($_GET["pMeds"]);
    //$drugs= getMedsStrength($_GET["pMeds"]);
?>
<!--    <option value="" selected="selected" disabled>Select Strength</option>-->
<?php
    foreach($drugs as $drug){
        $drugs = $drug["STRENGTH_CODE"];
        $descStrength = descMedsStrength($drug["STRENGTH_CODE"]);
?>
    <option value="<?php echo $descStrength["STRENGTH_CODE"]; ?>"><?php echo $descStrength["STRENGTH_DESC"]; ?></option>
<?php } ?>
