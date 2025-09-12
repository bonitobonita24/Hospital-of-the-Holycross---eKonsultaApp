<?php
    $page = 'consultation';
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
                <h3 class="panel-title">LIST OF CONSULTATIONS</h3>
            </div>
            <div class="panel-body">


            <div style="float: left; text-align: left; padding-top: 10px;padding-bottom: 10px;">
                <em style="font-weight: bold; font-size: 11px;color: #942a25;">
                    <li>To view the Client Record, click the 'Patient PIN'</li>
                </em>
            </div>

            <br/> <br/>

                <div>
                   <?php $getListConsultation = getEnlistedConsultationList(); ?>

                   <table class="table table-hover table-bordered table-responsive" id="listRecord" style="margin-top: 20px; margin-bottom: 20px; font-size: 11px; text-align: center; width: 95%;">
                       <thead>
                           <tr>
                               <th>No</th>
                               <th>PIN</th>
                               <th>Transaction No</th>
                               <th>Last Name</th>
                               <th>First Name</th>
                               <th>Middle Name</th>
                               <th>Suffix</th>
                               <th>Date of Birth</th>
                               <th>Type</th>
                               <th>Effectivity Year</th>
                               <th>Consultation Date</th>
                           </tr>
                       </thead>
                       <tbody>
                       <?php
                       for ($i = 0; $i < count($getListConsultation); $i++) {
                           $pxPin = $getListConsultation[$i]['PX_PIN'];
                           $transNo = $getListConsultation[$i]['TRANS_NO'];
                           $pxLName = $getListConsultation[$i]['PX_LNAME'];
                           $pxFName = $getListConsultation[$i]['PX_FNAME'];
                           $pxMName = $getListConsultation[$i]['PX_MNAME'];
                           $pxExtName= $getListConsultation[$i]['PX_EXTNAME'];
                           $pxDob = $getListConsultation[$i]['PX_DOB'];
                           $pxType = $getListConsultation[$i]['PX_TYPE'];
                           $soapDate = $getListConsultation[$i]['SOAP_DATE'];
                           $soapBy = $getListConsultation[$i]['SOAP_BY'];
                           $effYear = $getListConsultation[$i]['EFF_YEAR'];
                           ?>
                           <tr>
                               <td><?php echo $i+1; ?></td>
                               <td><a href="px_record.php?pin=<?php echo $pxPin;?>&effyear=<?php echo $effYear ; ?>"><?php echo $pxPin; ?></a></td>
                               <td><?php echo $transNo; ?></td>
                               <td><?php echo $pxLName; ?></td>
                               <td><?php echo $pxFName; ?></td>
                               <td><?php echo $pxMName; ?></td>
                               <td><?php echo $pxExtName; ?></td>
                               <td><?php echo date('m/d/Y', strtotime($pxDob)); ?></td>
                               <td><?php echo $pxType; ?></td>
                               <td><?php echo $effYear; ?></td>
                               <td><?php echo date('m/d/Y', strtotime($soapDate))?></td>
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