<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 3/22/2018
 * Time: 11:06 AM
 */
    require("function.php");
    require("function_global.php");
    $drugs= getMeds($_GET["pMeds"]);
    //$drugs= getMedsPackage($_GET["pMeds"]);
?>
<!--    <option value="" selected="selected" disabled>Select Package</option>-->
<?php
    foreach($drugs as $drug){
        $descPackage = descMedsPackage($drug["PACKAGE_CODE"]);
?>
    <option value="<?php echo $descPackage["PACKAGE_CODE"]; ?>"><?php echo $descPackage["PACKAGE_DESC"]; ?></option>
<?php } ?>
