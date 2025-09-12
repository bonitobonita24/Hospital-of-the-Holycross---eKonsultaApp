<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 8/30/2018
 * Time: 10:38 AM
 */
require("function.php");
require("function_global.php");
$drugs= getMeds($_GET["pMeds"]);
foreach($drugs as $drug){
    $drugs = $drug["UNIT_CODE"];
    $descUnit = descMedsUnit($drug["UNIT_CODE"]);
    ?>
    <option value="<?php echo $descUnit["UNIT_CODE"]; ?>"><?php echo $descUnit["UNIT_DESC"]; ?></option>
<?php } ?>

