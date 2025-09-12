<?php
    $page = 'enlistment';
    include('header.php');
    checkLogin();
    include('menu.php');
        
    $pSDate=$_GET['pStartDate'];
    $pEDate=$_GET['pEndDate'];
    $pStartDate = date('Y-m-d',strtotime($pSDate));
    $pEndDate = date('Y-m-d',strtotime($pEDate));

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
                <h3 class="panel-title">eKONSULTA REGISTRATION MODULE</h3>
            </div>
            <div class="panel-body">
                <form action="" name="frmEnlistList" method="GET">
                    <table style="margin-top: 20px; " align="center">
                        <tr>
                            <td colspan="5" align="center"><u><h4>List of All Registered Clients</h4></u></td>
                        </tr>                        
                    </table>
                <br/>
                <div style="margin-top: 0px;" align="center" id="results_list_tbl">
                    <?php
                    $getListEnlisted= getRegistrationRecord();

                    ?>
                        <table class="table table-hover table-bordered" id="listRecord" style="margin-top: 20px; margin-bottom: 20px; font-size: 11px; text-align: center; width: 98%;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>PIN</th>
                                    <th>Case No</th>
                                    <th>Name</th>
                                    <th>Date of Birth</th>
                                    <th>Registered Type</th>
                                    <th>Date of Encounter</th>
                                    <th>Effectivity Year</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            for ($i = 0; $i < count($getListEnlisted); $i++) {
                                $pxPin = $getListEnlisted[$i]['PX_PIN'];
                                $caseNo = $getListEnlisted[$i]['CASE_NO'];
                                $pxLName = $getListEnlisted[$i]['PX_LNAME'];
                                $pxFName = $getListEnlisted[$i]['PX_FNAME'];
                                $pxMName = $getListEnlisted[$i]['PX_MNAME'];
                                $pxExtName= $getListEnlisted[$i]['PX_EXTNAME'];
                                $pxDob = $getListEnlisted[$i]['PX_DOB'];
                                $pxType = $getListEnlisted[$i]['PX_TYPE'];
                                $encounterDate = $getListEnlisted[$i]['ENLIST_DATE'];
                                $createdBy = $getListEnlisted[$i]['CREATED_BY'];

                                $effYear = $getListEnlisted[$i]['EFF_YEAR'];
                                $vPrevEffYear = (int)$effYear - 1;

                                $getListHSA = getPatientHsaList($caseNo);

                                $getFPEConsultInfo = getPatientFPEConsultInfo($pxPin, $vPrevEffYear);
                                $prevFPECaseNo = $getFPEConsultInfo["CASE_NO"];
                                ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo $pxPin; ?></td>
                                    <td><?php echo $caseNo; ?>
                                        <input type="hidden" name="currentCaseNo" value="<?php echo $caseNo; ?>" />
                                        <input type="hidden" name="prevFPECaseNo" value="<?php echo $prevFPECaseNo; ?>" />
                                    </td>
                                    <td style="text-align: left;"><?php echo strReplaceEnye($pxLName).', '.strReplaceEnye($pxFName).' '.strReplaceEnye($pxExtName); ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($pxDob)); ?></td>
                                    <td style="text-align: left;"><?php echo getPatientType(false,$pxType); ?></td>
                                    <td><?php echo date('m/d/Y', strtotime($encounterDate))?></td>
                                    <td><?php echo $effYear?></td>
                                    <!-- <td style="text-align: left;">
                                        <?php 
                                        if(count($getFPEConsultInfo) > 1) { ?>
                                            <input type="button"
                                               name="retrieveFPE"
                                               id="retrieveFPE"
                                               class="btn btn-success btn-sm"
                                               value="Retrieve FPE"
                                               title="Retrieve Health Screening & Assessment"
                                               onclick="window.location='hsa_retrieve.php?caseno=<?php echo $caseNo;?>&prev_caseno=<?php echo $prevFPECaseNo;?>&effyear=<?php echo $effYear;?>'"
                                               <?php
                                               if(count($getListHSA) > 0){ ?>
                                                    disabled
                                               <?php }
                                               ?>
                                            />
                                        <br/> <br/>
                                        <?php } ?>
                                            <input type="button"
                                               name="addNewConsult"
                                               id="addNewConsult"
                                               class="btn btn-success btn-sm"
                                               value="Add Assessment"
                                               title="Add Health Screening & Assessment"
                                               onclick="window.location='hsa_data_entry.php?case_no=<?php echo $caseNo;?>'"
                                               <?php
                                               if(count($getListHSA) > 0){ ?>
                                                    disabled
                                               <?php }
                                               ?>
                                            />
                                       
                                    </td>                                     -->
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                </div>

                </form>

                <br/>

                <div>
                    <input type="button"
                           class="btn btn-primary"
                           style="background:#006dcc"
                           name="back"
                           id="back"
                           value="Go Back to Search Module"
                           onclick="window.location='registration_search.php'">
                </div>
            </div>
        </div>

    </div>

    <div id="result" style="margin: 30px 0px 30px 0px; display: none;" align="center">

    </div>

    <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
    </div>

</div>

<?php
include('footer.php');
?>
<script type="text/javascript">
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $(document).ready(function() {
        $('#listRecord').dataTable({
        });
    });
</script>