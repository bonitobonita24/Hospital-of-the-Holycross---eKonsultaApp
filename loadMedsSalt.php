<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 8/30/2018
 * Time: 10:35 AM
 */
require("function.php");
require("function_global.php");
$drugs= getMeds($_GET["pMeds"]);

foreach($drugs as $drug){
    $drugs = $drug;["SALT_CODE"];
    $descSalt = descMedsSalt($drug["SALT_CODE"]);
    ?>
    <option value="<?php echo $descSalt["SALT_CODE"]; ?>"><?php echo $descSalt["SALT_DESC"]; ?></option>
<?php } ?>

