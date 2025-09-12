<?php
    require("function.php");
    $municipality = getMunicipality($_GET["pProvCode"]);
?>
    <option value="" selected="selected" disabled>Select Municipality</option>
<?php
    foreach($municipality as $pMun) {
    ?>
    <option value="<?php echo $pMun["MUNICIPALITY"]; ?>"><?php echo utf8_encode($pMun["MUN_NAME"]); ?></option>
    <?php
    }
?>
