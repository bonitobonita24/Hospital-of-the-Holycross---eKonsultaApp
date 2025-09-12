<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 5/2/2018
 * Time: 9:18 AM
 */
    $page = 'profiling';
    include('header.php');
    checkLogin();
    include('menu.php');

    if(isset($_GET['pHsaCaseNo'])){
        $pHsaCaseNum = $_GET['pHsaCaseNo'];
        $pEffYear = $_GET['effyear'];
        $pRefNo = $_GET['refno'];
    }
?>

<div style="margin: 5px;">
    <div class="row">
        <div class="col-sm-7 col-xs-8"></div>
        <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
    </div>
</div>

<div id="content">

    <style>
        .table td,
        .table th {
            text-align: center;
        }

        legend {
            background-color: #FBFCC7;
        }
    </style>

    <div id="content_div" align="center" style="margin: 0px 0px 20px 0px;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">LIST OF HEALTH SCREENING & ASSESSMENT</h3>
            </div>
            <div class="panel-body">
                <form action="" name="search_consultation_form" method="GET">
                    <?php
                    $getListHSA = getPatientHsaList($pHsaCaseNum, $pEffYear);

                    $vPrevEffYear = (int)$pEffYear - 1;

                    $getFPEConsultInfo = getPatientFPEConsultInfo($pRefNo, $vPrevEffYear);
                    $prevFPECaseNo = $getFPEConsultInfo["CASE_NO"];

                    if(count($getListHSA) == 0){
                        echo "<b>No record found.</b>";
                    }
                    else{
                    ?>
                    <div align="center">
                        <table class="table table-hover table-bordered" id="tbla" style="margin-top: 20px; margin-bottom: 20px; font-size: 10px; text-align: center; width: 100%;">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Transaction No</th>
                                <th>PIN</th>
                                <th>Transaction Date</th>
                                <th>Transaction By</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=0;
                            foreach ($getListHSA as $listHsa) {
                                $transNo = $listHsa['TRANS_NO'];
                                $profDate = $listHsa['PROF_DATE'];
                                $profBy = $listHsa['PROF_BY'];
                                $pxPin = $listHsa['PX_PIN'];
                                ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><a href="hsa_data_entry.php?pHsaTransNo=<?php echo $transNo; ?>" title="Go to Health Screening and Assessment to View/Edit/Update/Finalize Record" style="font-size: 11px;font-weight: normal"><?php echo $transNo; ?></a></td>
                                    <!-- <td><?php echo $transNo; ?></td> -->
                                    <td><?php echo $pxPin; ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($profDate))?></td>
                                    <td><?php echo $profBy;?></td>
                                </tr>
                            <?php
                                $i++;
                            } ?>
                            </tbody>
                        </table>
                        <?php } ?>
                    </div>
                    <div style="margin-top:25px;">
                        <input type="button"
                               name="addNewConsult"
                               id="addNewConsult"
                               class="btn btn-primary"
                               value="Add New Record"
                               title="Add New Record"
                               onclick="window.location='hsa_data_entry.php?case_no=<?php echo $_GET['pHsaCaseNo'];?>'"
                               <?php
                               if(count($getListHSA) > 0){ ?>
                                    disabled
                               <?php }
                               ?>
                        />
                        <?php  if(count($getFPEConsultInfo) > 1) { ?>
                        <input type="button"
                            name="retrieveFPE"
                            id="retrieveFPE"
                            class="btn btn-primary"
                            value="Retrieve Assessment"
                            title="Retrieve Health Screening & Assessment"
                            onclick="window.location='hsa_retrieve.php?caseno=<?php echo $pHsaCaseNum;?>&prev_caseno=<?php echo $prevFPECaseNo;?>&effyear=<?php echo $pEffYear;?>'"
                            <?php
                            if(count($getListHSA) > 0){ ?>
                            disabled
                            <?php }
                            ?>
                        />
                        <?php } ?>
                        <input type="button"
                               name="goBackSearch"
                               id="goBackSearch"
                               class="btn btn-primary"
                               style="background:#006dcc;"
                               value="Go Back to Search Module"
                               title="Go Back to Search Module"
                               onclick="window.history.back();"
                        />
                    </div>
                    <br/>

                    <!-- <div style="font-weight: normal;font-style: italic;color: #942a25;text-align: left;font-size: 11px;">
                        Note: Patient has an existing record for the current year. Go to Consultation Module to record a follow-up.
                    </div> -->

                    <br/>
                </form>
            </div>

        </div>
    </div>


    <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
    </div>

</div>

<?php
include('footer.php');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#tbla').dataTable({
        });
    });
</script>