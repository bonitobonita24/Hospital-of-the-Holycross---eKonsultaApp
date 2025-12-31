<?php
    $page = 'home';
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

    <div class="row" align="center">
        <div class="col-xs-12 col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">WELCOME</h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <div class="alert alert-info" style="text-align: justify;">
                        The <b>eKONSULTA System</b> is an interim, stand-alone system designed to record Health Screening & Assessment, Consultations, Laboratory/Imaging Examination Results, and Medication for the facility's clients. This system also generates feedback slips known as eKAS (electronic Konsulta Slip) and ePresS (electronic Prescription Slip) to assess client satisfaction with the facility's services. Additionally, it features the ability to generate Konsulta data in an encrypted XML format, which is submitted to the Philippine Health Insurance Corporation (PhilHealth) as required by the Konsulta Package benefits for all Filipinos. This data submission supports performance analysis.    
                        <br/><br/>
                            See the <i>version history</i> below for the system updates/ enhancements.

                        </div>
                        <table class="table table-hover table-condensed table-bordered">
                            <thead>
                                <tr class="alert alert-warning">
                                    <th>Version</th>
                                    <th style="text-align: left;">Features</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">1.11.0<br/>[Released Date: 2025-10-06]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Disabled the membership-related fields in the eKonsulta Registration module.</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">1.10.0<br/>[Accepted Date: 2025-04-23]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Removed the Authorization Transaction Code (ATC) requirement when encoding and saving the First Patient Encounter (FPE) and Consultation data, in accordance with PhilHealth Circular No. 2024-0013, effective immediately starting May 16, 2024.</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.09.00.202501<br/>[Released Date: 2025-02-20]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Removed the FBS/RBS as a requirement if the beneficiary has a Family History of Diabetes Mellitus</li>
                                            <li>Fixed bugs and improved user experience</li>
                                            1. Fixed issues with viewing the 2025 Uploaded Registration Masterlist. <br/>
                                            2. Resolved the "There is no active transaction" error message in MySQL when saving a transaction. Note: This issue affected the network setup settings and multiple users.
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.08.00.202401<br/>[Released Date: 2024-08-05]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Implemented the carry-over of encoded First Patient Encounter (FPE) data from the previous year, provided there is at least one consultation from that year.</li>
                                            <li>Implemented validation in the Consultation module to ensure that encoding of consultation data is within the applicable year of the client record. </li>
                                            <li>Enhanced the generation of the Konsulta XML Report per Group to implement the following changes: <br/>
                                            1. Ensure the generation of XML reports within the same calendar year.
                                            <br/>
                                            2. Resolve the encountered document type definition (DTD) related issue upon uploading.
                                            </li>
                                            <li>Enhanced the generation of the Konsulta XML Report per Individual to implement the following changes: <br/>
                                            1. Ensure the generation of XML report is per applicable year.
                                            <br/>
                                            2. Resolve the encountered document type definition (DTD) issue upon uploading.</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.07.00.202302<br/>[Released Date: 2023-11-06]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Fixed the generation of XML per individual and group.</li>
                                            <li>Enhanced the convention of case, transaction and transamittal number to use seconds instead of minutes.</li>
                                            <li>Enhanced the Consultation to remove the Laboratory/Imaging Results and Dispensing Medicine sub-modules</li>
                                            <li>Enhanced the menu tab to add Laboratory/Imaging Results;and rename Follow-ups Medicine to Medicine<br/>
                                                1. Allow user to add laboratory/imaging results if results are available after the consultation<br/>
                                                2. Allow user to add prescribe and dispensed medicine if with consultation
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.06.02.202301<br/>[Released Date: 2023-06-23]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Fixed the generation of the pSignsSymptoms, pPainSite, pOtherComplaint, and pMidUpperArmCirc in individual and group XML.</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.06.01.202205<br/>[Released Date: 2022-12-14]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Fixed the generation of XML per individual wherein the pSignsSymptoms and pPainSite values have been resolved, and encrypt the XML using the cipher key of the currently logged user account in the system.</li>
                                            <li>Fixed the Ñ character upon generation of XML</li>
                                            <li>Fixed the membership record through the uploaded Konsulta Registration Masterlist wherein the <i>Update the encoded eKonsulta Registration</i> button is provided; and enabled to update the encoded eKonsulta Registration</li>
                                            <li>Enhanced the eKonsulta Registration wherein the user will automatically proceed to the Health Screening and Assessment once successfully saved the record.</li>
                                            <li>Enhanced the Generation of XML per Individual to include the search feature per beneficiary</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.06.00.202204<br/>[Released Date: 2022-10-10]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Removed the Z-Score as a requirement for Pediatric beneficiaries aged 0-60 months old in Health Screening & Assessment, and Consultation module.</li>
                                            <li>Removed the Register New Client sub-module</li>
                                            <li>Renamed the Konsulta Registration to eKonsulta Registration</li>
                                            <li>Enhanced the generation of the transmittal number upon generation of XML to facilitate multiple databases installed per facility</li>
                                            <li>Fixed the display of special character such as Ñ</li>
                                            <li>Debugged the generaton of XML. This fixed the generation of the First Patient Encounter Data with FBS/RBS</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.05.00.202203<br/>[Released Date: 2022-06-06]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Enhanced the data entry for consultation to retrieve previous consultation data if available in Subjective/History of Illness, Objective/Physical Examination, and Assessment/Diagnosis</li>
                                            <li>Fixed the XML format error encounterd upon uploading of the generated XML in the Konsulta Uploading utility</li>
                                        </ul>
                                    </td>
                                </tr>
                                 <tr>
                                    <td style="font-weight: bold;width:25%;">01.04.00.202202<br/>[Released Date: 2022-04-06]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Enhanced <i>View Konsulta Registered Clients</i> in Konsulta Registration<br/>
                                                1. Enhanced list of registered beneficiaries by retrieving based on registration date on the eKONSULTA System on descending order as default<br/>
                                            </li>
                                            <li>Enhanced Health Screening and Assessment module<br/>
                                                1. Enhanced list of health screening and assessment records by retrieving based on the date of health screening and assessment date on descending order as default<br/>
                                                2. Removed question mark (?) in the label of Walk-in Client with and without Authorization Transaction Code (ATC) <br/>
                                            </li>
                                            <li>Enhanced Consultation module<br/>
                                                1. Removed question mark (?) in the label of Walk-in Client with and without Authorization Transaction Code (ATC) <br/>
                                                2. Enhanced searching of ICD10 in Assessment/Diagnosis by using keywords<br/>
                                                3. Added deselect option in Laboratory/ Imaging Examination<br/>
                                                4. Enhanced searching of Drug/Medicine by using keywords<br/>
                                                5. Added <i>Drug Grouping</i> field for Other Drug/Medicine input to tag if NCD, Antibiotic or Others<br/>
                                                6. Enhanced Quantity and Frequency to accept alphanumeric characters and removed the predefined library under Advise<br/>
                                                7. Enhanced Laboratory/ Imaging (CBC w/ platelet count, Urinalysis, Lipid Profile, RBS, FBS, OGTT) results to accept alphanumeric and special characters, e.g. 1-3<br/>
                                                8. Changed label name of MHC to MCH and MHCH to MCHC in CBC w/ platelet count<br/>
                                            </li>
                                            <li>Enhanced Follow-ups Medicine<br/>                                                
                                                1. Enhanced searching of Drug/Medicine by using keywords<br/>
                                                2. Added <i>Drug Grouping</i> field for Other Drug/Medicine input to tag if NCD, Antibiotic or Others<br/>
                                                3. Enhanced Quantity and Frequency to accept alphanumeric characters and removed the predefined library under Advise<br/>
                                            </li>
                                            <li>Enhanced Generation of eKAS<br/>                                                
                                                1. Added Consultation records<br/>
                                                2. Added Other Laboratory/ Imaging result<br/>
                                            </li>
                                            <li>Enhanced Generation of XML per Individual<br/>                                               
                                                1. Enhanced generation of individual XML by generating only with at least one Consultation record<br/>
                                            </li>
                                            <li>Enhanced Generation of XML<br/>                                               
                                                1. Added Drug Grouping upon generation of XML<br/>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.03.01.202103<br/>[Released Date: 2021-10-06]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Bug fixed:<br/>
                                                1. Fixed the processing of Z-Score for children 0-24 months old<br/>
                                                2. Retrieving of Pertinent Findings per System from Health Screening and Assessment to Consultation module<br/>
                                                3. Enabled of Other Diagnostic Exam text field for 'Others' option in Consultation - Plan/Management<br/>
                                                3. Enhanced Generation of XML<br/>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">01.03.00.202102<br/>[Released Date: 2021-07-09]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Database Update<br/>
                                                1. Update ICD10 Library based on Philippine ICD-10 Modification 2nd Edition<br/>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">1.2.1.1<br/>[Released Date: 2021-03-16]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Fixed the Generatio of Konsulta XML</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">1.2.0<br/>[Released Date: 2021-01-21]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Enhanced Health Screening & Assessment<br/>
                                                1. Made fields required for the First Patient Encounter <br/>
                                                2. Added Middle and Upper Arm Circumference in Pertinent Physical Examination Findings under Pediatric Client aged 0-24 months<br/>
                                                3. Added a sub-module of Laboratory/Imaging Results – enabled only if ‘Diabetes Mellitus’ under Family History is selected <br/>
                                                3.1. Only FBS and RBS are displayed <br/>
                                                4. Added 'Save and Finalize' button
                                            </li>
                                            <li>Enhanced Consultation Module<br/>
                                                1. Only client with finalized health screening and assessment record shall be allowed to add new consultation record<br/>
                                                2. Added Middle and Upper Arm Circumference in Objective/Physical Examination under Pediatric Client aged 0-24 months <br/>
                                                3. Added ‘Random Blood Sugar’ in the list of Laboratory/Imaging Examination
                                            </li>
                                            <li>Enhanced Generation of Konsulta XML</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">1.1.0<br/>[Accepted Date: 2020-09-02]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>Enhanced eKONSULTA Registration List (List of All Registered Clients)<br/>
                                                1. Include additional button to link the registered client to Health Screening & Assessment
                                            </li>
                                            <li>Enhanced Health Screening & Assessment Module<br/>
                                                1. Adjustments of required data requirements to save the record<br/>
                                            </li>
                                            <li>Enhanced Consultation Module<br/>
                                                1. Remove auto-checked of "YES" in Doctor's Recommendation based on age group category<br/>
                                                2. Remove auto-disabled of "NO" in Doctor's Recommendation based on age group category
                                            </li>
                                            <li>Enhanced Generation of eKAS to reflect status based on Laboratory/Imaging Examination</li>
                                            <li>Enhanced Generation of Konsulta XML</li>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;width:25%;">1.0.0<br/>[Accepted Date: 2020-08-03]</td>
                                    <td>
                                        <ul style="margin-left: 10px;text-align: left">
                                            <li>User Registration on the system</li>
                                            <li>Uploading of encrypted Konsulta Registration Masterlist</li>
                                            <li>Data Entry of Client's Information in Konsulta Registration module not included in Registration Masterlist</li>
                                            <li>Data Entry of Client's Information in Konsulta Registration module based on Uploaded Registration Masterlist</li>
                                            <li>Data Entry of Health Screening & Assessment</li>
                                            <li>Data Entry of Consultation</li>
                                            <li>Data Entry of Laboratory/Imaging Results in Consultation module</li>
                                            <li>Data Entry of Medicine in Consultation module</li>
                                            <li>Generation of encrypted Konsulta XML Report per Group</li>
                                            <li>Generation of encyrpted Konsulta XML Report per Individual</li>
                                            <li>Generation of eKAS (electronic Konsulta Availment Slip)</li>
                                            <li>Generation of ePresS (electronic Prescription Slip)</li>
                                            <li>Viewing of Client's Record module</li>
                                        </ul>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
