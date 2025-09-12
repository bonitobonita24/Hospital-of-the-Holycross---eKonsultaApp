<?php
    $page = 'medicine';
    include('header.php');
    checkLogin();
    include('menu.php');

    if(isset($_GET['case_no'])){
        $pCaseNum = $_GET['case_no'];
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
                <h3 class="panel-title">LIST OF FOLLOW-UP MEDICINE</h3>
            </div>
            <div class="panel-body">
                <form action="" name="search_consultation_form" method="GET">
                    <?php
                        $getListConsultation = checkPatientConsultationRecordExist($pCaseNum);
                        if(count($getListConsultation) == 0){
                            echo "<b>No consultation record found</b>";
                        }
                        else{
                    ?>

                    <div align="center">
                        <table class="table table-hover table-bordered" id="tbla" style="margin-top: 20px; margin-bottom: 20px; font-size: 10px; text-align: center; width: 100%;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Case No</th>
                                    <th>Patient PIN</th>
                                    <th>Consultation Date</th>
                                    <th>Consultation By</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            for ($i = 0; $i < count($getListConsultation); $i++) {
                                $caseNo = $getListConsultation[$i]['CASE_NO'];
                                $transNo = $getListConsultation[$i]['TRANS_NO'];
                                $pxPin = $getListConsultation[$i]['PX_PIN'];
                                $soapDate = $getListConsultation[$i]['SOAP_DATE'];
                                $soapBy = $getListConsultation[$i]['SOAP_BY'];
                                if ($transNo != null) {
                                    ?>
                                    <tr>
                                        <td><?php echo $i + 1; ?></td>
                                        <td><?php echo $caseNo; ?></td>
                                        <td><?php echo $pxPin; ?></td>
                                        <td><?php echo date('m/d/Y', strtotime($soapDate)) ?></td>
                                        <td><?php echo $soapBy; ?></td>
                                    </tr>
                                <?php }
                            }?>
                            </tbody>
                        </table>
                        <?php
                        }?>
                    </div>
                    <div style="margin-top:25px;">
                        <input type="button"
                               <?php if(count($getListConsultation) != 0){ ?>
                                   name="addNew"
                                   id="addNew"
                                   class="btn btn-primary"
                                   value="Add New Follow-up Medicine"
                                   title="Add New Follow-up Medicine"
                                   onclick="window.location='followup_meds_data_entry.php?case_no=<?php echo $_GET['case_no'];?>'"
                               <?php } else{ ?>
                                   name="addRecord"
                                   id="addRecord"
                                   class="btn btn-primary"
                                   value="Go to Consultation Module"
                                   title="Go to Consultation Module"
                                   onclick="window.location='consultation_data_entry.php?case_no=<?php echo $_GET['pCaseNo'];?>'"
                               <?php } ?>
                        />
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
                    <?php if(count($getListConsultation) == 0){ ?>
                        <div style="font-weight: normal;font-style: italic;color: #942a25;text-align: left;font-size: 11px;margin: 0px 0px 15px 15px;">
                            Note:
                            <br/>
                                (1) Patient has NO existing Consultation record
                        </div>
                    <?php }else if($getListConsultation['IS_FINALIZE'] == 'N'){ ?>
                    <?php } ?>
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