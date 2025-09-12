<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 3/9/2018
 * Time: 9:00 AM
 */
?>
<?php
require_once('../res/tcpdf/tcpdf.php');
include('../function.php');
error_reporting(E_ALL);
//include('../function_global.php');

// create new PDF document
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('PhilHealth eKONSULTA Stand-alone System');
$pdf->SetTitle('eKONSULTA: eKonSulTa Availment Slip');
$pdf->SetSubject('eKONSULTA: eKonSulTa Availment Slip');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 8);

// add a page
$pdf->AddPage();
session_start();
//hci details
$accreno = $_SESSION['pAccreNum'];
$hciname = $_SESSION['pHospName'];
$userid = $_SESSION['pUserID'];
$usename = $_SESSION['pUserLname'].' '.$_SESSION['pUserFname'].' '.$_SESSION['pUserMname'].' '.$_SESSION['pUserSuffix'];

//patients info
$transno=$_GET['transno'];
$module=$_GET['module'];

// if($module == 'HSA'){
//     $getResult = getProfilingTransactionForSlip($transno);    
//     $pxATC = $getResult['PROFILE_OTP'];  
// } 

if($module == 'SOAP') {
    $getResult = getConsultationTransactionForSlip($transno); 
    $pxATC = $getResult['SOAP_OTP'];    
}

$caseNo = $getResult["CASE_NO"];
$transNo = $getResult["TRANS_NO"];
$patientName = $getResult['PX_LNAME'].', '.$getResult['PX_FNAME'].' '.$getResult['PX_MNAME'].' '.$getResult['PX_EXTNAME'];
$pxType = $getResult['PX_TYPE'];    
$pxPin = $getResult['PX_PIN'];    
$pxContactNo = $getResult['PX_CONTACTNO'];    
  
//age
$px_RegisteredDate = date("m/d/Y",strtotime($getResult["ENLIST_DATE"]));
$dateRegister = new DateTime($px_RegisteredDate, new DateTimeZone('Asia/Manila'));
$pat_birthday = date("m/d/Y",strtotime($getResult["PX_DOB"]));
$datePxDoB = new DateTime($pat_birthday, new DateTimeZone('Asia/Manila'));
$getAgeServ = date_diff($dateRegister,$datePxDoB);
$descAgeServ = $getAgeServ->y." yr(s), ".$getAgeServ->m." mo(s), ".$getAgeServ->d." day(s)";

// create some HTML content
$report = '
        <div style="margin-bottom: 30px;font-weight: bold;font-size: 16px;text-align:left">ELECTRONIC KONSULTA AVAILMENT SLIP (eKAS)</div>            
                <br/>
                <table border="0" style="width: 100%;margi-top:20px;">
                    <col width="15%">
                    <col width="35%">
                    <col width="15%">
                    <col width="35%">
                    <tr>
                        <td style="text-align: left;">HCI Name:</td>
                        <td style="text-align: left;"><u>'.$hciname.'</u></td>
                        <td style="text-align: left;">Case No.:</td>
                        <td style="text-align: left;"><u>'.$caseNo.'</u></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">HCI Accreditation No.</td>
                        <td style="text-align: left;"><u>'.$accreno.'</u></td>
                        <td style="text-align: left;">Transaction No.:</td>
                        <td style="text-align: left;"><u>'.$transNo.'</u></td>
                    </tr>
                    <tr><td colspan="4">&nbsp;</td></tr>
                    <tr>
                        <td style="text-align: left;">Patient Name <i>(pangalan ng pasyente)</i>:</td>
                        <td style="text-align: left;"><u>'.$patientName.'</u></td>
                        <td style="text-align: left;">Age <i>(edad)</i>:</td>
                        <td style="text-align: left;"><u>'.$getAgeServ->y.' yr(s) old</u></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">PIN (PhilHealth Identification Number):</td>
                        <td style="text-align: left;"><u>'.$pxPin.'</u></td>
                        <td style="text-align: left;">Membership Category:</td>
                        <td style="text-align: left;">__________</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Contact No.:</td>
                        <td style="text-align: left;"><u>'.$pxContactNo.'</u></td>
                        <td style="text-align: left;">Membership Type:</td>
                        <td style="text-align: left;"><u>'.$pxType.'</u></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;" colspan="4">Authorization Transaction Code (ATC):<u> '.$pxATC.'   </u></td>
                    </tr>
                </table>
                <br/>
                <br/>
                <!--Essential Services Info-->
                <table border="1" class="table" style="width: 100%;">
                    <tr style="text-align: center;font-weight: bold;">
                        <td>Essential Service</td>
                        <td> (&#10004;) if Performed<br/>(X) if not performed</td>
                        <td>Date Performed</td>
                        <td>Initial of Personnel <br/>Performed</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">History and Physical Examination (vitals, anthorpometrics...)</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr> ';
                    if($getAgeServ->y >= 1){ 
             $report .= '       <tr>
                        <td style="text-align: left;">CBC*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>'; 
                    } 
                    if($getAgeServ->y >= 1 && $getAgeServ->y <= 19){
            $report .= '        <tr>
                        <td style="text-align: left;">Fecalysis*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';  
                    }   
                    if($getAgeServ->y >= 1){ 
            $report .= '        <tr>
                        <td style="text-align: left;">Urinalysis*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>'; 
                    } 
                    if($getAgeServ->y >= 10 && $getResult['PX_SEX'] == 'F'){ 
            $report .= '        <tr>
                        <td style="text-align: left;">Pap Smear*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                    } 
                    if($getAgeServ->y >= 10){
            $report .= '        <tr>
                        <td style="text-align: left;">Chest X-ray*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';  
                    }    
                    if($getAgeServ->y >= 40){ 
            $report .= '        <tr>
                        <td style="text-align: left;">Lipid Profile*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>   
                    
                    <tr>
                        <td style="text-align: left;">Oral Glucose Tolerance Test* </td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    
                    <tr>
                        <td style="text-align: left;">Sputum Microscopy*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';   
                    }  
                    if($getAgeServ->y >= 30){
            $report .= '        <tr>
                        <td style="text-align: left;">ECG*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';  
                    }   
                    if($getAgeServ->y >= 40){
            $report .= '        <tr>
                        <td style="text-align: left;">FBS*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>'; 
                    }
                    if($getAgeServ->y >= 50){
            $report .= '        <tr>
                        <td style="text-align: left;">Fecal Occult Blood Test* </td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';   
                    }
                    if($getAgeServ->y >= 5 && $getAgeServ->y <= 9){
            $report .= '        <tr>
                        <td style="text-align: left;">PPD Test*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                    }
                    if($getAgeServ->y >= 40){
            $report .= '        <tr>
                        <td style="text-align: left;">Creatinine*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';    
                    }             
            $report .= '    </table>
                
                <div style="font-style: italic;text-align: left;">*as applicable</div>
                <br/>
                <table>
                    <tr>
                        <td colspan="2">
                            <div style="text-align: left;">
                                Have you received the above-mentioned essential services? ____ YES ____ NO
                                <br/>
                                <i>   (Natanggap mo ba ang mga essential services na nabanggit?)</i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="text-align: left;">
                                How do you feel about the services provided?
                                <br/>
                                <i>   (Ano ang iyong pakiramdam tungkol sa mga serbisyong ibinigay?)</i>                                
                            </div>
                        </td>
                        <td>                            
                            <input type="checkbox" name="emoticon" id="happy" value="happy" style="margin: 10px 5px 0px 20px;"/>
                            <img src="../res/images/happy.png" width="35px" height="35px"/>

                            <input type="checkbox" name="emoticon" id="neutral" value="neutral" style="margin: 10px 5px 0px 20px"/>
                            <img src="../res/images/neutral.png" width="35px" height="35px"/>

                            <input type="checkbox" name="emoticon" id="sad" value="sad" style="margin: 10px 5px 0px 20px"/>
                            <img src="../res/images/sad.png" width="35px" height="35px"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="text-align: left;">
                                Under the penalty of law, I attest that the information I provided in this slip are true and accurate.
                                <br/>
                                <i>   (Sa ilalim ng batas, pinatutunayan ko na ang impormasyong ibinigay ko ay totoo at tama)</i>
                            </div>
                        </td>
                    </tr>
                </table>          
                <br/><br/><br/>
                <table style="margin-top:20px;">
                    <tr>
                        <td style="width:60%;">_________________________________________________</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            Parents/Guardians Signature over Printed Name
                            <br/>
                            <i>(Lagda ng magulang/Tagapangalaga sa itaas ng isinulat na pangalan)</i>
                        </td>                        
                        <td>
                            Next Consultation Date: _________________________
                            <br/>
                            <i>(Petsa ng susunod na konsultasyon)</i>
                        </td>
                    </tr>
                    <tr><td></td></tr>
                    <tr>
                        <td>
                            Note: <br/>
                            Accomplised form shall be submitted to PhilHealth.
                            <br/>
                            <i>(Ang kumpletong form ay dapat isumite sa PhilHealth.)</i>
                        </td>
                    </tr>
                </table>
                <div style="text-align: left;">
                
                    <br/><br/><br/>
                    
                </div>
                <div style="text-align:right;font-style: italic;font-size:9px;">Prepared Date:'.date('m/d/Y h:i:sa').' Prepared By: '.$usename.'</div>
                <div style="text-align:right;">HCIs Copy</div>
            ';

// output the HTML content
$pdf->writeHTML($report, true, false, true, false, '');


// add a page
$pdf->AddPage();
// create some HTML content
$report = '
        <div style="margin-bottom: 30px;font-weight: bold;font-size: 16px;text-align:left">ELECTRONIC KONSULTA AVAILMENT SLIP (eKAS)</div>            
                <br/>
                <table border="0" style="width: 100%;margi-top:20px;">
                    <col width="10%">
                    <col width="40%">
                    <col width="10%">
                    <col width="40%">
                    <tr>
                        <td style="text-align: left;">HCI Name:</td>
                        <td style="text-align: left;"><u>'.$hciname.'</u></td>
                        <td style="text-align: left;">Case No.:</td>
                        <td style="text-align: left;"><u>'.$caseNo.'</u></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">HCI Accreditation No.</td>
                        <td style="text-align: left;"><u>'.$accreno.'</u></td>
                        <td style="text-align: left;">Transaction No.:</td>
                        <td style="text-align: left;"><u>'.$transNo.'</u></td>
                    </tr>
                    <tr><td colspan="4">&nbsp;</td></tr>
                    <tr>
                        <td style="text-align: left;">Patient Name <i>(Pangalan ng Pasyente)</i>:</td>
                        <td style="text-align: left;"><u>'.$patientName.'</u></td>
                        <td style="text-align: left;">Age <i>(edad)</i>:</td>
                        <td style="text-align: left;"><u></u></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Patient PIN:</td>
                        <td style="text-align: left;"><u>'.$pxPin.'</u></td>
                        <td style="text-align: left;">Membership Category:</td>
                        <td style="text-align: left;">__________</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Contact No.:</td>
                        <td style="text-align: left;"><u>'.$pxContactNo.'</u></td>
                        <td style="text-align: left;">Membership Type:</td>
                        <td style="text-align: left;"><u>'.$pxType.'</u></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;" colspan="4">Authorization Transaction Code (ATC):<u> '.$pxATC.'   </u></td>
                    </tr>
                </table>
                <br/>
                <br/>
                <!--Essential Services Info-->
                <table border="1" class="table" style="width: 100%;">
                    <tr style="text-align: center;font-weight: bold;">
                        <td>Essential Service</td>
                        <td> (&#10004;) if Performed<br/>(X) if not performed</td>
                        <td>Date Performed</td>
                        <td>Initial of Personnel <br/>Performed</td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">History and Physical Examination (vitals, anthorpometrics...)</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr> ';
                    if($getAgeServ->y >= 0 && $getAgeServ->y <= 19){ 
             $report .= '       <tr>
                        <td style="text-align: left;">CBC*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>'; 
                    } 
                    if($getAgeServ->y >= 1 && $getAgeServ->y <= 19){
            $report .= '        <tr>
                        <td style="text-align: left;">Fecalysis*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';  
                    }   
                    if($getAgeServ->y >= 1){ 
            $report .= '        <tr>
                        <td style="text-align: left;">Urinalysis*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>'; 
                    } 
                    if($getAgeServ->y >= 10 && $getResult['PX_SEX'] == 'F'){ 
            $report .= '        <tr>
                        <td style="text-align: left;">Paps Smear*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                    } 
                    if($getAgeServ->y >= 10){
            $report .= '        <tr>
                        <td style="text-align: left;">Chest X-ray*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';  
                    }    
                    if($getAgeServ->y >= 20){ 
            $report .= '        <tr>
                        <td style="text-align: left;">Lipid Profile*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>   
                    
                    <tr>
                        <td style="text-align: left;">Oral Glucose Tolerance Test* </td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    
                    <tr>
                        <td style="text-align: left;">Sputum Microscopy*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';   
                    }  
                    if($getAgeServ->y >= 30){
            $report .= '        <tr>
                        <td style="text-align: left;">ECG*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';  
                    }   
                    if($getAgeServ->y >= 40){
            $report .= '        <tr>
                        <td style="text-align: left;">FBS*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>'; 
                    }
                    if($getAgeServ->y >= 50){
            $report .= '        <tr>
                        <td style="text-align: left;">Fecal Occult Blood Test* </td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';   
                    }
                    if($getAgeServ->y >= 5 && $getAgeServ->y <= 50){
            $report .= '        <tr>
                        <td style="text-align: left;">PPD Test*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                    }
                    if($getAgeServ->y >= 40){
            $report .= '        <tr>
                        <td style="text-align: left;">Creatinine*</td>
                        <td style="text-align: left;">&nbsp;</td>                    
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';    
                    }             
            $report .= '    </table>
                
                <div style="font-style: italic;text-align: left;">*as applicable</div>
                <br/>
                <br/>
                <table>
                    <tr>
                        <td colspan="2">
                            <div style="text-align: left;">
                                Have you received the above-mentioned essential services? ____ YES ____ NO
                                <br/>
                                <i>   (Natanggap mo ba ang mga essential services na nabanggit?)</i>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="text-align: left;">
                                How do you feel about the services provided?
                                <br/>
                                <i>   (Ano ang iyong pakiramdam tungkol sa mga serbisyong ibinigay?)</i>                                
                            </div>
                        </td>
                        <td>                            
                            <input type="checkbox" name="emoticon" id="happy" value="happy" style="margin: 10px 5px 0px 20px;"/>
                            <img src="../res/images/happy.png" width="35px" height="35px"/>

                            <input type="checkbox" name="emoticon" id="neutral" value="neutral" style="margin: 10px 5px 0px 20px"/>
                            <img src="../res/images/neutral.png" width="35px" height="35px"/>

                            <input type="checkbox" name="emoticon" id="sad" value="sad" style="margin: 10px 5px 0px 20px"/>
                            <img src="../res/images/sad.png" width="35px" height="35px"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div style="text-align: left;">
                                Under the penalty of law, I attest that the information I provided in this slip are true and accurate.
                                <br/>
                                <i>   (Sa ilalim ng batas, pinatutunayan ko na ang impormasyong ibinigay ko ay totoo at tama)</i>
                            </div>
                        </td>
                    </tr>
                </table>          
                <br/><br/><br/>
                <table style="margin-top:20px;">
                    <tr>
                        <td style="width:60%;">_________________________________________________</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            Parents/Guardians Signature over Printed Name
                            <br/>
                            <i>(Lagda ng magulang/Tagapangalaga sa itaas ng isinulat na pangalan)</i>
                        </td>                        
                        <td>
                            Next Consultation Date: _________________________
                            <br/>
                            <i>(Petsa ng susunod na konsultasyon)</i>
                        </td>
                    </tr>
                    <tr><td></td></tr>
                    <tr>
                        <td>
                            Note: <br/>
                            Accomplised form shall be submitted to PhilHealth.
                            <br/>
                            <i>(Ang kumpletong form ay dapat isumite sa PhilHealth.)</i>
                        </td>
                    </tr>
                </table>
                <div style="text-align:right;font-style: italic;font-size:9px;">Prepared Date:'.date('m/d/Y h:i:sa').' Prepared By: '.$usename.'</div>
                <div style="text-align:right;">Patients Copy</div>
            ';

// output the HTML content
$pdf->writeHTML($report, true, false, true, false, '');

// reset pointer to the last page
$pdf->lastPage();
// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('print_ekas.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

