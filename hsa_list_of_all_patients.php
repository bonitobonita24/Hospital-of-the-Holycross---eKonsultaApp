<?php
    // Increase timeout for large dataset
    set_time_limit(300);
    ini_set('max_execution_time', 300);
    ini_set('memory_limit', '256M');
    
    $page = 'profiling';
    include('header.php');
    checkLogin();
    include('menu.php');
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


            <div style="float: left; text-align: left; padding-top: 10px;padding-bottom: 10px;">
                <em style="font-weight: bold; font-size: 11px;color: #942a25;">
                    <li>To view the Client Record, click the 'Patient PIN'</li>
                    <li>To view, edit and save/finalize Health Screening & Assessment record, click the 'Transaction No.' of the client.</li>
                </em>
            </div>

                <div>
                   <?php $getList = getListEnlistedWithScreening(); ?>

                   <table class="table table-hover table-bordered table-responsive" id="listRecord" style="margin-top: 20px; margin-bottom: 20px; font-size: 11px; text-align: center; width: 95%;">
                       <thead>
                           <tr>
                               <th>No</th>
                               <th>PIN</th>
                               <th>Transaction No.</th>
                               <th>Last Name</th>
                               <th>First Name</th>
                               <th>Middle Name</th>
                               <th>Extension</th>
                               <th>Date of Birth</th>
                               <th>Registered Type</th>
                               <th>Screening Date</th>
                               <th>Effectivity Year</th>
                           </tr>
                       </thead>
                       <tbody>
                       <?php
                       for ($i = 0; $i < count($getList); $i++) {
                           $pxPin = $getList[$i]['PX_PIN'];
                           $transNo = $getList[$i]['TRANS_NO'];
                           $pxLName = $getList[$i]['PX_LNAME'];
                           $pxFName = $getList[$i]['PX_FNAME'];
                           $pxMName = $getList[$i]['PX_MNAME'];
                           $pxExtName= $getList[$i]['PX_EXTNAME'];
                           $pxDob = $getList[$i]['PX_DOB'];
                           $pxType = $getList[$i]['PX_TYPE'];
                           $profDate = $getList[$i]['PROF_DATE'];
                           $effYear = $getList[$i]['EFF_YEAR'];
                           ?>
                           <tr>
                               <td><?php echo $i+1; ?></td>
                               <td><a href="px_record.php?pin=<?php echo $pxPin;?>&effyear=<?php echo $effYear;?>"><?php echo $pxPin; ?></a></td>
                               <td><a href="hsa_data_entry.php?pHsaTransNo=<?php echo $transNo;?>"><?php echo $transNo; ?></a></td>
                               <td><?php echo strReplaceEnye($pxLName); ?></td>
                               <td><?php echo strReplaceEnye($pxFName); ?></td>
                               <td><?php echo strReplaceEnye($pxMName); ?></td>
                               <td><?php echo $pxExtName; ?></td>
                               <td><?php echo date('m/d/Y', strtotime($pxDob)); ?></td>
                               <td><?php echo $pxType; ?></td>
                               <td><?php echo date('m/d/Y', strtotime($profDate))?></td>
                               <td><?php echo $effYear; ?></td>
                           </tr>
                       <?php } ?>
                       </tbody>
                   </table>
                   <div style="margin-top:25px;">
                       <input type="button" name="goBackSearch" id="goBackSearch" class="btn btn-primary" style="background:#006dcc;" value="Go Back to Search Module" title="Go Back to Search Module" onclick="window.history.back();">
                   </div>
               </div>
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
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $(document).ready(function() {
        $('#listRecord').dataTable({
        });
    });
</script>