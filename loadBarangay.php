<?php
    require("function.php");
    $barangay= getBarangay($_GET["pMunCode"], $_GET["pProvCode"]);
?>
    <option value="" selected="selected" disabled>Select Barangay</option>
<?php
foreach($barangay as $pBrgy) {
    ?>
    <option value="<?php echo $pBrgy["BARANGAY"]; ?>"><?php echo utf8_encode($pBrgy["BRGY_NAME"]); ?></option>
    <?php
}
?>
