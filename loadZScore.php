<?php
    require("function.php");

    $length = $_GET["length"];
    $height = $_GET["weight"];
    $weight = $_GET["height"];
    $sex = $_GET["sex"];
    $yearAge = $_GET["year"];
    $monthAge = $_GET["month"];

    if(($yearAge >=0 && $yearAge <=1) && ($monthAge >=0 && $monthAge <= 11) && $sex == 'F'){
        $zscores = getZScoreG023($_GET["length"], $_GET["weight"]);
    }

    if(($yearAge >=0 && $yearAge <=1) && ($monthAge >=0 && $monthAge <= 11) && $sex == 'M'){
        $zscores = getZScoreB023($_GET["length"], $_GET["weight"]);
    }

    if(($yearAge >= 2 && $yearAge <= 4) && ($monthAge >=0 && $monthAge <= 11) && $sex == 'F'){
        $zscores = getZScoreG2460($_GET["height"], $_GET["weight"]);
    }

    if(($yearAge >= 2 && $yearAge <= 4) && ($monthAge >=0 && $monthAge <= 11) && $sex == 'M'){
        $zscores = getZScoreB2460($_GET["height"], $_GET["weight"]);
    }


    foreach($zscores as $zscore){
        $resultCode = $zscore["RESULT_CODE"];
        $resultDesc = $zscore["RESULT_DESC"];
    }
    if ($zscores == null) {
        $resultCode = "";
        $resultDesc = " No result found.";
    }
?>
<script type="text/javascript" language="javascript">
    $(function() {
        $('#txtPhExZscoreCm').val('<?php echo $resultCode; ?>');
    });

    $(function() {
        $('#zscoreDesc').text('<?php echo ' Result Description: '.$resultDesc; ?>');
    });
</script>
