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
    $drugs = $drug["GEN_CODE"];
    $descStrength = descMedsGeneric($drug["GEN_CODE"]);
    ?>
    <option value="<?php echo $descStrength["GEN_CODE"]; ?>"><?php echo $descStrength["GEN_DESC"]; ?></option>
<?php } ?>
