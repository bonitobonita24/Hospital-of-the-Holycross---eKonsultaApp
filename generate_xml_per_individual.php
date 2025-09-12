<head>
<link href="res/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="res/css/styles.css" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="res/js/jquery.js"></script>
<script type="text/javascript" src="res/js/jquery.min.js"></script>
<script type="text/javascript" src="res/js/jquery-1.11.1.js"></script>
<script type="text/javascript" src="res/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="res/js/jquery-ui-1.11.4.js"></script>
<script type="text/javascript" src="res/js/scripts.js"></script>
</head>
<?php
    $page = 'reports';
    include('header.php');
    checkLogin();
    include('menu.php');

    $pPIN = $_GET['pPIN'];
    $pLastName= $_GET['pLastName'];
    $pFirstName = $_GET['pFirstName'];
    $pMiddleName = $_GET['pMiddleName'];
    $pSuffix = $_GET['pSuffix'];
    $pDoB = $_GET['pDateOfBirth'];
    $pDateOfBirth = date('Y-m-d',strtotime($pDoB));
    $pModule = "K";
?>
<script>
    $(function() {
        $( ".datepicker" ).datepicker();
    });
    $("#pDateOfBirth").mask("99/99/9999");
    $("#pDateOfBirth").datepicker({ maxDate: new Date, minDate: new Date(2007, 6, 12) });

</script>
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
                <h3 class="panel-title">GENERATION OF KONSULTA XML PER INDIVIDUAL REPORT MODULE</h3>
            </div>
            <div class="panel-body">
                <form action="generate_xml_per_individual.php" name="search_enlistment_form" method="GET">
                    <table style="margin-top: 20px; " align="center">
                        <tr>
                            <td colspan="5" align="center"><u><h4></h4></u></td>
                        </tr>
                        <tr>
                            <td colspan="5" align="center">
                                <table>
                                    <tr>
                                        <td><label>PhilHealth Identification No:</label></td>
                                        <td>
                                            <input type="text"
                                                   name="pPIN"
                                                   id="pPIN"
                                                   style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                                   class="form-control"
                                                   value="<?php echo $pPIN;?>"
                                                   onkeypress="return isNumberKey(event);"
                                                   maxlength="12"
                                            />
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="height: 10px;">
                            <td colspan="5"></td>
                        </tr>
                        <tr>
                            <td><label>Last Name</label></td>
                            <td><label>First Name</label></td>
                            <td><label>Middle Name</label></td>
                            <td><label>Extension</label></td>
                            <td><label>Date of Birth</label></td>
                        </tr>
                        <tr>
                            <td>
                                <input type="text"
                                       name="pLastName"
                                       id="pLastName"
                                       class="form-control"
                                       style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                       maxlength="20"
                                       value="<?php echo $pLastName; ?>"
                                       autocomplete="off"
                                />
                            </td>
                            <td>
                                <input type="text"
                                       name="pFirstName"
                                       id="pFirstName"
                                       class="form-control"
                                       style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                       maxlength="20"
                                       value="<?php echo $pFirstName; ?>"
                                       autocomplete="off"
                                />
                            </td>
                            <td>
                                <input type="text"
                                       name="pMiddleName"
                                       id="pMiddleName"
                                       class="form-control"
                                       style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 150px;"
                                       maxlength="15"
                                       value="<?php echo $pMiddleName; ?>"
                                       autocomplete="off"
                                />
                            </td>
                            <td>
                                <input type="text"
                                       name="pSuffix"
                                       id="pSuffix"
                                       class="form-control"
                                       style=" margin: 0px 10px 0px 0px;text-transform: uppercase;width: 70px;"
                                       maxlength="3"
                                       value="<?php echo $pSuffix; ?>"
                                       autocomplete="off"
                                />
                            </td>
                             <td>
                                <input type="text"
                                       name="pDateOfBirth"
                                       id="pDateOfBirth"
                                       class="datepicker form-control"
                                       value="<?php echo $pDoB; ?>"
                                       placeholder="mm/dd/yyyy"
                                       style="width: 100px;"
                                       autocomplete="off"
                                       onkeyup="formatDate('pDateOfBirth');"
                                />
                            </td>
                        </tr>
                        <tr style="height: 20px;">
                            <td colspan="5"></td>
                        </tr>
                        <tr>
                            <td colspan="5" align="center">
                                <input type="submit"
                                       name="search"
                                       class="btn btn-success"
                                       id="search"
                                       value="Search"
                                       title="Search"
                                       onclick="return validateSearch();"
                                />
                                <input type="button"
                                       name="clear"
                                       class="btn btn-default"
                                       id="clear"
                                       value="Clear"
                                       title="Clear"
                                       onclick="window.location='generate_xml_per_individual.php'"
                                />
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="wait_image" align="center" style="display: none; margin: 30px 0px;">
                <img src="res/images/LoadingWait.gif" alt="Please Wait" />
            </div>

            <div id="result" style="margin: 30px 0px 30px 0px;" align="center">
                <?php
                    $displayResult = searchBeneficiaryForXMLGeneration($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth);

                    if(count($displayResult) == 0){
                        echo "<div class='alert alert-warning' style='text-align: left;font-weight: bold;font-size: 12px; margin-left: 20px;'>No record found in the Uploaded Registration Masterlist, or the Uploaded Registration Masterlist is not yet available.</div>";
                    }
                    else{
                        echo "<div class='alert alert-info' style='text-align: center;font-weight: bold;font-size: 12px;width:50%'>".count($displayResult)." Record/s Found.</div>";                       
                    
                ?>
                        <table class="table table-hover table-bordered" style="margin-top: 5px; margin-bottom: 20px; font-size: 10px; text-align: center;width: 95%">
                            <thead>
                                <tr>
                                    <th style="width: 5%; font-size: 11px; padding: 2px; vertical-align: middle" rowspan="2">No</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;" colspan="8">Client Information</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;" rowspan="2"></th>
                                </tr>
                                <tr>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">PIN</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Last Name</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">First Name</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Middle Name</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Extension</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Client Type</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Date of Birth</th>
                                    <th style="width: 12%; font-size: 11px; padding: 2px;vertical-align: middle">Effectivity Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                for ($i = 0; $i < count($displayResult); $i++) {
                                    $pxPin = $displayResult[$i]['PX_PIN'];
                                    $pxLname = $displayResult[$i]['PX_LNAME'];
                                    $pxFname = $displayResult[$i]['PX_FNAME'];
                                    $pxMname = $displayResult[$i]['PX_MNAME'];
                                    $pxExtName = $displayResult[$i]['PX_EXTNAME'];
                                    $pxType = $displayResult[$i]['PX_TYPE'];
                                    $pxDob = $displayResult[$i]['PX_DOB'];
                                    $pEnlistCaseNo = $displayResult[$i]['CASE_NO'];
                                    $pEffYear = $displayResult[$i]['EFF_YEAR'];

                                    $for_generation_xml = "modal_generate_xml_per_individual.php?caseno=".$pEnlistCaseNo;
                                ?>
                                <tr>
                                    <td><?php echo $i+1; ?></td>
                                    <td><?php echo $pxPin;?></td>
                                    <td><?php echo strReplaceEnye($pxLname);?></td>
                                    <td><?php echo strReplaceEnye($pxFname);?></td>
                                    <td><?php echo strReplaceEnye($pxMname);?></td>
                                    <td><?php echo $pxExtName;?></td>                                    
                                    <td><?php echo getPatientType(false, $pxType);?></td>                                    
                                    <td><?php echo $pxDob;?></td>                                    
                                    <td><?php echo $pEffYear;?></td> 
                                    <td>
                                         <button type="button"
                                                class="btn btn-primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#modal_xml"
                                                onclick="$('#xml_myIframe').attr('src', '<?php echo $for_generation_xml; ?>');">
                                            Generate XML
                                        </button>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
            </div>

        </div>
    </div>

</div>

<!-- START MODAL GENERATION/DOWNLOAD XML FILE -->
<div class="modal fade" id="modal_xml" role="dialog" tabindex='-1'>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" style="text-align:left;">Download XML File</h4>
            </div>
            <div class="modal-body">

                <iframe id="xml_myIframe" src="" width="100%" frameborder="0" style="height:300px;">
                    
                </iframe>
            </div>
            <div class="modal-footer">
                 <button type="button" class="btn btn-default" data-dismiss="modal" style="width:90px;" onClick="history.go(0);">Close</button>
            </div> 
        </div>
    </div>
</div>

<?php
    include('footer.php');
?>

<script>
    $(function() {
        $( ".datepicker" ).datepicker();
    });

    $(document).ready(function () {
        $('.dropdown-toggle').dropdown();
    });

    $("#pDateOfBirth").mask("99/99/9999");
</script>
