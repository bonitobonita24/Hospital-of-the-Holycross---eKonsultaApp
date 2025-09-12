<?php
error_reporting(0);
date_default_timezone_set('Asia/Manila');

function getReportResultLabFbsPerCaseNo($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS 
                                    WHERE TRANS_NO = :transNo
                                    AND IS_APPLICABLE IN ('D', 'W', 'X') 
                                    ");

        $stmt->bindParam(':transNo', $pTransNo);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    
    return $result;
}

function getReportResultLabRbsPerCaseNo($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS 
                                    WHERE TRANS_NO = :transNo
                                    AND IS_APPLICABLE IN ('D', 'W', 'X') 
                                    ");

        $stmt->bindParam(':transNo', $pTransNo);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    
    return $result;
}

/* */
function strReplaceEnye ($str) {
      // Array containing search string
      $searchVal = array("�", "Ã‘", "ÃƒÂ‘","¿", "Ã?", "ï¿½", "Ã", "Ñ", "&#xD1;");
     
      // Array containing replace string from  search string
      $replaceVal = array("Ñ", "Ñ", "Ñ", "Ñ", "Ñ", "Ñ", "Ñ", "Ñ", "Ñ");
     
      // Function to replace string
      $res = str_replace($searchVal, $replaceVal, $str);

      return $res;
}

/* String Replace */
function strReplace($str) {
    $str = str_replace('�', 'Ñ', $str);
    return $str;
}

/* Handle Ã‘ */
function strReplace2($str) {
    $str = str_replace('Ã‘', 'Ñ', $str);
    return $str;
}


/* Handle Ã‘ */
function strReplace3($str) {
    $str = str_replace('ÃƒÂ‘', 'Ñ', $str);
    return $str;
}


/*Library Functions*/
/* Get Diagnostic Color */
function getDiagnosticColor($list, $str) {
    $pDiagnosticColorLib = array(
        '1' => 'BROWN',
        '2' => 'BLACK',
        '3' => 'RED',
        '4' => 'WHITE/GREY',
        '5' => 'YELLOW',
        '6' => 'GREEN');

    if ($list == TRUE) {
        return $pDiagnosticColorLib;
    }
    else {
        foreach($pDiagnosticColorLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}
/* Get Diagnostic Consistency */
function getDiagnosticConsistency($list, $str) {
    $pDiagnosticConsistencyLib = array(
        '1' => 'SOFT',
        '2' => 'WELL-FORMED',
        '3' => 'SEMI-FORMED',
        '4' => 'WATERY',
        '5' => 'MUCOID',
        '6' => 'HARD');

    if ($list == TRUE) {
        return $pDiagnosticConsistencyLib;
    }
    else {
        foreach($pDiagnosticConsistencyLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Diagnostic Blood */
function getDiagnosticBlood($list, $str) {
    $pDiagnosticBloodLib = array(
        'P' => 'PRESENT',
        'A' => 'ABSENT');

    if ($list == TRUE) {
        return $pDiagnosticBloodLib;
    }
    else {
        foreach($pDiagnosticBloodLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}


/* Get Civil Status */
function getCivilStatus($list, $str) {
    $pCivilStatusLib = array(
        'S' => 'SINGLE',
        'M' => 'MARRIED',
        'W' => 'WIDOWED',
        'X' => 'SEPARATED',
        'A' => 'ANNULLED');

    if ($list == TRUE) {
        return $pCivilStatusLib;
    }
    else {
        foreach($pCivilStatusLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Sex */
function getSex($list, $str) {
    $pSexLib = array(
        'M' => 'MALE',
        'F' => 'FEMALE');

    if ($list == TRUE) {
        return $pSexLib;
    }
    else {
        $pSexLib['0'] = 'MALE'; //savr 2016-04-08: added this part to handle 0 value of sex
        $pSexLib['1'] = 'FEMALE'; //savr 2016-04-08: added this part to handle 1 value of sex

        foreach($pSexLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Patient Type */
function getPatientType($list, $str) {
    $pPatientTypeLib = array(
        'MM' => 'MEMBER',
        'DD' => 'DEPENDENT');

    if ($list == TRUE) {
        return $pPatientTypeLib;
    }
    else {
        foreach($pPatientTypeLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Patient Type -added by ZIA*/
function getDependentType($list, $str) {
    $pDependentTypeLib = array(
        'S' => 'SPOUSE',
        'C' => 'CHILD',
        'P' => 'PARENT');

    if ($list == TRUE) {
        return $pDependentTypeLib;
    }
    else {
        foreach($pDependentTypeLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}
/* Get Enlist Status*/
function getEnlistStatus($list, $str) {
    $pEnlistStatusLib = array(
        '' => '',
        '1' => 'ACTIVE',
        '2' => 'CANCELLED',
        '3' => 'TRANSFERRED');

    if ($list == TRUE) {
        return $pEnlistStatusLib;
    }
    else {
        foreach($pEnlistStatusLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Enlist Type*/
function getEnlistType($list, $str) {
    $pEnlistTypeLib = array(
        'E' => 'EPCB',
        'P' => 'PCB1');

    if ($list == TRUE) {
        return $pEnlistTypeLib;
    }
    else {
        foreach($pEnlistTypeLib as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Consent Value; Get With Disability Value*/
function getYNValue($list, $str) {
    $pYNVal= array(
        'Y' => 'YES',
        'N' => 'NO');

    if ($list == TRUE) {
        return $pYNVal;
    }
    else {
        foreach($pYNVal as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Enrollment*/
function getEnrollmentTag($list, $str) {
    $pEnrollment = array(
        '1' => 'WATGB',
        '2' => '4Ps',
        '3' => 'SENIOR CITIZEN',
        '4' => 'POINT OF CARE');

    if ($list == TRUE) {
        return $pEnrollment;
    }
    else {
        foreach($pEnrollment as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get type of delivery */
function getDeliveryType($list, $str) {
    $deliveryType = array(
        'X' => 'NOT APPLICABLE',
        'N' => 'NORMAL (NSD)',
        'O' => 'OPERATIVE (CSD)',
        'B' => 'BOTH NORMAL AND OPERATIVE (NSD & CSD)');

    if ($list == TRUE) {
        return $deliveryType;
    }
    else {
        foreach($deliveryType as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Day, Month & Year Age */
function getAge($birthday) {
    $date = new DateTime($birthday);
    $now = new DateTime();
    $interval = $now->diff($date);
    $ageD = $interval->d;
    $ageM = $interval->m;
    $ageY = $interval->y;
    return array('d' => $ageD, 'm' => $ageM, 'y' => $ageY);
}

/* Get Quantity for Prescribe Medicine Tab*/
function getQuantity($list, $str) {
    $pQty = array(
        '1' => '1',
        '2' => '2',
        '4' => '4',
        '6' => '6',
        '12' => '12',
        '0' => 'AS NEEDED');

    if ($list == TRUE) {
        return $pQty;
    }
    else {
        foreach($pQty as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Quantity for Frequency Instruction Medicine Tab*/
function getFrequency($list, $str) {
    $pFrequency = array(
        'A day' => 'Once a day',
        'Every 4 hours' => 'Every 4 hours',
        'Every 6 hours' => 'Every 6 hours',
        'Every 8 hours' => 'Every 8 hours',
        'Every 12 hours' => 'Every 12 hours');

    if ($list == TRUE) {
        return $pFrequency;
    }
    else {
        foreach($pFrequency as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Type of Generation Report*/
function getTypeOfReport($list, $str) {
    $pGenerationType = array(
        '1' => 'Registered',
        '2' => 'Screened and Assessed',
        '3' => 'Consulted',
        '4' => 'Services Provided',
        '5' => 'Laboratories',
        '6' => 'Medicine');

    if ($list == TRUE) {
        return $pGenerationType;
    }
    else {
        foreach($pGenerationType as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Type of Generation*/
function getTypeOfSearch($list, $str) {
    $pSearchType = array(
        'All' => 'All',
        'MM' => 'Member',
        'DD' => 'Dependent');

    if ($list == TRUE) {
        return $pSearchType;
    }
    else {
        foreach($pSearchType as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/* Get Type of Generation*/
function getTypeOfSector($list, $str) {
    $pSector = array(
        'G' => 'GOVERNMENT',
        'P' => 'PRIVATE');

    if ($list == TRUE) {
        return $pSector;
    }
    else {
        foreach($pSector as $id => $value) {
            if ($id == $str) {
                return $value;
                break;
            }
        }
    }
}

/*Generate XML Report*/
function generateXml($genResultEnlist, $genResultProfiling, $genProfilingMedHist, $genProfilingMHspecific, $genProfilingPemisc,
                         $genProfilingSurghist, $genProfilingFamhist, $genProfilingFhspecific, $genProfilingImmunization, 
                         $genResultMainConsult, $genResultConsultation, $genConsultationDiagnostic, $genConsultIcd, $genConsultManagement, $genConsultPemisc, 
                         $genResultMedicine,
                         $genResultLabs, $genResultLabsCbc, $genResultLabsUrine, $genResultLabsFecalysis, $genResultLabsChestXray, $genResultLabsSputum,
                         $genResultLabsLipidProf, $genResultLabsFbs, $genResultLabsEcg, $genResultLabsOgtt, $genResultLabsPaps,
                         $pStartDate, $pEndDate,
                         $getResultLabsFOBT,$getResultLabsCreatinine,$getResultLabsPDD,$getResultLabsHbA1c,$getResultLabsOthDiag, $genResultLabsRbs) {
    $konsulta = new SimpleXMLElement("<PCB></PCB>");

    $pReportTransNo = generateTransNo('REPORT_TRANS_NO');
    $pDateRange = $pStartDate." TO ".$pEndDate;

    if (count($genResultConsultation) > 0) {
        $cntSOAP = count($genResultConsultation);
    } else {
        $cntSOAP = 1;
    }

    $konsulta->addAttribute("pUsername", "");
    $konsulta->addAttribute("pPassword", "");
    $konsulta->addAttribute("pHciAccreNo", $_SESSION['pAccreNum']);
    $konsulta->addAttribute("pPMCCNo", "");
    $konsulta->addAttribute("pEnlistTotalCnt", count($genResultEnlist));
    $konsulta->addAttribute("pProfileTotalCnt", count($genResultProfiling));
    $konsulta->addAttribute("pSoapTotalCnt", $cntSOAP);
    $konsulta->addAttribute("pCertificationId", "EKON-00-06-2020-00001");
    $konsulta->addAttribute("pHciTransmittalNumber", $pReportTransNo);

    /*ENLISTMENT XML GENERATION*/
    $enlistments = $konsulta->addChild("ENLISTMENTS");

        foreach ($genResultEnlist as $genResultEnlists) {
            $enlistment = $enlistments->addChild("ENLISTMENT");
            $enlistment->addAttribute("pHciCaseNo", $genResultEnlists['CASE_NO']);
            $enlistment->addAttribute("pHciTransNo", $genResultEnlists['TRANS_NO']);
            $enlistment->addAttribute("pEffYear", $genResultEnlists['EFF_YEAR']);
            $enlistment->addAttribute("pEnlistStat", $genResultEnlists['ENLIST_STAT']);
            $enlistment->addAttribute("pEnlistDate", $genResultEnlists['ENLIST_DATE']);
            $enlistment->addAttribute("pPackageType", $genResultEnlists['PACKAGE_TYPE']);
            $enlistment->addAttribute("pMemPin", $genResultEnlists['MEM_PIN']);
            $enlistment->addAttribute("pMemFname", utf8_encode($genResultEnlists['MEM_FNAME']));
            $enlistment->addAttribute("pMemMname", utf8_encode($genResultEnlists['MEM_MNAME']));
            $enlistment->addAttribute("pMemLname", utf8_encode($genResultEnlists['MEM_LNAME']));
            $enlistment->addAttribute("pMemExtname", $genResultEnlists['MEM_EXTNAME']);
            $enlistment->addAttribute("pMemDob", $genResultEnlists['MEM_DOB']);
            $enlistment->addAttribute("pPatientPin", $genResultEnlists['PX_PIN']);
            $enlistment->addAttribute("pPatientFname", utf8_encode($genResultEnlists['PX_FNAME']));
            $enlistment->addAttribute("pPatientMname", utf8_encode($genResultEnlists['PX_MNAME']));
            $enlistment->addAttribute("pPatientLname", utf8_encode($genResultEnlists['PX_LNAME']));
            $enlistment->addAttribute("pPatientExtname", $genResultEnlists['PX_EXTNAME']);
            $enlistment->addAttribute("pPatientSex", $genResultEnlists['PX_SEX']);
            $enlistment->addAttribute("pPatientDob", $genResultEnlists['PX_DOB']);
            $enlistment->addAttribute("pPatientType", $genResultEnlists['PX_TYPE']);
            $enlistment->addAttribute("pPatientMobileNo", $genResultEnlists['PX_MOBILE_NO']);
            $enlistment->addAttribute("pPatientLandlineNo", $genResultEnlists['PX_LANDLINE_NO']);
            $enlistment->addAttribute("pWithConsent", $genResultEnlists['WITH_CONSENT']);
            $enlistment->addAttribute("pTransDate", $genResultEnlists['TRANS_DATE']);
            $enlistment->addAttribute("pCreatedBy", $genResultEnlists['CREATED_BY']);
            $enlistment->addAttribute("pReportStatus", "U");
            $enlistment->addAttribute("pDeficiencyRemarks", $genResultEnlists['DEFICIENCY_REMARKS']);
        }
   

    /*PROFILING XML GENERATION*/
    $profiling = $konsulta->addChild("PROFILING");
    
        foreach ($genResultProfiling as $genResultProfilings) {
            $profile = $profiling->addChild("PROFILE");
            $profile->addAttribute("pHciTransNo", $genResultProfilings['TRANS_NO']);
            $profile->addAttribute("pHciCaseNo", $genResultProfilings['CASE_NO']);
            $profile->addAttribute("pProfDate", $genResultProfilings['PROF_DATE']);
            $profile->addAttribute("pPatientPin", $genResultProfilings['PX_PIN']);
            $profile->addAttribute("pPatientType", $genResultProfilings['PX_TYPE']);
            $profile->addAttribute("pPatientAge", $genResultProfilings['PX_AGE']);
            $profile->addAttribute("pMemPin", $genResultProfilings['MEM_PIN']);
            $profile->addAttribute("pEffYear", $genResultProfilings['EFF_YEAR']);
            $profile->addAttribute("pATC", $genResultProfilings['PROFILE_OTP']);
            $profile->addAttribute("pIsWalkedIn", $genResultProfilings['WITH_ATC']);
            $profile->addAttribute("pTransDate", $genResultProfilings['DATE_ADDED']);
            $profile->addAttribute("pReportStatus", "U");
            $profile->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            if($genProfilingMedHist != NULL) {
                        $medhists = $profile->addChild("MEDHISTS");
                foreach ($genProfilingMedHist as $genProfilingMedHists) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingMedHists['TRANS_NO']) {
                        $medhist = $medhists->addChild("MEDHIST");
                        if ($genProfilingMedHists['MDISEASE_CODE'] == null || $genProfilingMedHists['MDISEASE_CODE'] == "") {
                            $medhist->addAttribute("pMdiseaseCode", "999");
                        } else {
                            $medhist->addAttribute("pMdiseaseCode", $genProfilingMedHists['MDISEASE_CODE']);
                        }
                        
                        $medhist->addAttribute("pReportStatus", "U");
                        $medhist->addAttribute("pDeficiencyRemarks", $genProfilingMedHists['DEFICIENCY_REMARKS']);
                    } 
                }

                foreach ($genProfilingMedHist as $genProfilingMedHists) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingMedHists['TRANS_NO']) {
                        $medhist = $medhists->addChild("MEDHIST");
                        $medhist->addAttribute("pMdiseaseCode", "999");
                        $medhist->addAttribute("pReportStatus", "U");
                        $medhist->addAttribute("pDeficiencyRemarks", $genProfilingMedHists['DEFICIENCY_REMARKS']);
                    } 
                    break;
                }
            } else {                
                    $medhists = $profile->addChild("MEDHISTS");
                    $medhist = $medhists->addChild("MEDHIST");
                    $medhist->addAttribute("pMdiseaseCode", "999");
                    $medhist->addAttribute("pReportStatus", "U");
                    $medhist->addAttribute("pDeficiencyRemarks", "");
            }

            if($genProfilingMHspecific != NULL) {
                        $mhspecifics = $profile->addChild("MHSPECIFICS");
                foreach ($genProfilingMHspecific as $genProfilingMHspecifics) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingMHspecifics['TRANS_NO']) {
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                        $mhspecific->addAttribute("pMdiseaseCode", $genProfilingMHspecifics['MDISEASE_CODE']);
                        $mhspecific->addAttribute("pSpecificDesc", $genProfilingMHspecifics['SPECIFIC_DESC']);
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks", $genProfilingMHspecifics['DEFICIENCY_REMARKS']);
                    }
                }

                foreach ($genProfilingMHspecific as $genProfilingMHspecifics) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingMHspecifics['TRANS_NO']) {
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                        $mhspecific->addAttribute("pMdiseaseCode", $genProfilingMHspecifics['MDISEASE_CODE']);
                        $mhspecific->addAttribute("pSpecificDesc", $genProfilingMHspecifics['SPECIFIC_DESC']);
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks", $genProfilingMHspecifics['DEFICIENCY_REMARKS']);
                    break;
                    }
                }
            } else{
                        $mhspecifics = $profile->addChild("MHSPECIFICS");
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                        $mhspecific->addAttribute("pMdiseaseCode", "");
                        $mhspecific->addAttribute("pSpecificDesc", "");
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks", "");
            }

            if($genProfilingSurghist != NULL) {
                        $surghists = $profile->addChild("SURGHISTS");
                foreach ($genProfilingSurghist as $genProfilingSurghists) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingSurghists['TRANS_NO']) {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", $genProfilingSurghists['SURG_DESC']);
                        $surghist->addAttribute("pSurgDate", $genProfilingSurghists['SURG_DATE']);
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", $genProfilingSurghists['DEFICIENCY_REMARKS']);
                    } else {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", "");
                        $surghist->addAttribute("pSurgDate", "");
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", "");
                    }
                }

                foreach ($genProfilingSurghist as $genProfilingSurghists) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingSurghists['TRANS_NO']) {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", "");
                        $surghist->addAttribute("pSurgDate", "");
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", "");
                    } 
                }
            } else{
                        $surghists = $profile->addChild("SURGHISTS");
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", "");
                        $surghist->addAttribute("pSurgDate", "");
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", "");
            }

            if($genProfilingFamhist != NULL) {
                        $famhists = $profile->addChild("FAMHISTS");
                foreach ($genProfilingFamhist as $genProfilingFamhists) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingFamhists['TRANS_NO']) {
                        $famhist = $famhists->addChild("FAMHIST");
                        if ($genProfilingFamhists['MDISEASE_CODE'] == null || $genProfilingFamhists['MDISEASE_CODE'] == "") {
                            $famhist->addAttribute("pMdiseaseCode", "");
                        } else {
                            $famhist->addAttribute("pMdiseaseCode", $genProfilingFamhists['MDISEASE_CODE']);
                        }
                       
                        $famhist->addAttribute("pReportStatus", "U");
                        $famhist->addAttribute("pDeficiencyRemarks", $genProfilingFamhists['DEFICIENCY_REMARKS']);
                    } 
                }
            } else {
                        $famhists = $profile->addChild("FAMHISTS");
                        $famhist = $famhists->addChild("FAMHIST");
                        $famhist->addAttribute("pMdiseaseCode", "");
                        $famhist->addAttribute("pReportStatus", "U");
                        $famhist->addAttribute("pDeficiencyRemarks", "");
            }

            if($genProfilingFhspecific != NULL) {
                        $fhspecifics = $profile->addChild("FHSPECIFICS");
                foreach ($genProfilingFhspecific as $genProfilingFhspecifics) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingFhspecifics['TRANS_NO']) {
                        $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                        $fhspecific->addAttribute("pMdiseaseCode", $genProfilingFhspecifics['MDISEASE_CODE']);
                        $fhspecific->addAttribute("pSpecificDesc", $genProfilingFhspecifics['SPECIFIC_DESC']);
                        $fhspecific->addAttribute("pReportStatus", "U");
                        $fhspecific->addAttribute("pDeficiencyRemarks", $genProfilingFhspecifics['DEFICIENCY_REMARKS']);
                    } 
                }
                foreach ($genProfilingFhspecific as $genProfilingFhspecifics) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingFhspecifics['TRANS_NO']) {
                        $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                        $fhspecific->addAttribute("pMdiseaseCode", "");
                        $fhspecific->addAttribute("pSpecificDesc", "");
                        $fhspecific->addAttribute("pReportStatus", "U");
                        $fhspecific->addAttribute("pDeficiencyRemarks", $genProfilingFhspecifics['DEFICIENCY_REMARKS']);
                        break;
                    } 
                }
            } else{
                        $fhspecifics = $profile->addChild("FHSPECIFICS");
                        $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                        $fhspecific->addAttribute("pMdiseaseCode", "");
                        $fhspecific->addAttribute("pSpecificDesc", "");
                        $fhspecific->addAttribute("pReportStatus", "U");
                        $fhspecific->addAttribute("pDeficiencyRemarks", "");
            }

            $sochist = $profile->addChild("SOCHIST");
            $sochist->addAttribute("pIsSmoker", $genResultProfilings['IS_SMOKER']);
            $sochist->addAttribute("pNoCigpk", $genResultProfilings['NO_CIGPK']);
            $sochist->addAttribute("pIsAdrinker", $genResultProfilings['IS_ADRINKER']);
            $sochist->addAttribute("pNoBottles", $genResultProfilings['NO_BOTTLES']);
            $sochist->addAttribute("pIllDrugUser", $genResultProfilings['ILL_DRUG_USER']);
            $sochist->addAttribute("pIsSexuallyActive", $genResultProfilings['IS_SEXUALLY_ACTIVE']);
            $sochist->addAttribute("pReportStatus", "U");
            $sochist->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            if ($genProfilingImmunization != null) {
                $immunizations = $profile->addChild("IMMUNIZATIONS");
                    foreach ($genProfilingImmunization as $genProfilingImmunizations) {
                        if ($genResultProfilings['TRANS_NO'] == $genProfilingImmunizations['TRANS_NO']) {
                            $immunization = $immunizations->addChild("IMMUNIZATION");

                            if ($genProfilingImmunizations['CHILD_IMMCODE'] == null || $genProfilingImmunizations['CHILD_IMMCODE'] == "") {
                                $immunization->addAttribute("pChildImmcode", "");
                            } else {
                                $immunization->addAttribute("pChildImmcode", $genProfilingImmunizations['CHILD_IMMCODE']);
                            }

                            if ($genProfilingImmunizations['YOUNGW_IMMCODE'] == null || $genProfilingImmunizations['YOUNGW_IMMCODE'] == "") {
                                $immunization->addAttribute("pYoungwImmcode", "");
                            } else {
                                $immunization->addAttribute("pYoungwImmcode", $genProfilingImmunizations['YOUNGW_IMMCODE']);
                            }

                            if ($genProfilingImmunizations['PREGW_IMMCODE'] == null || $genProfilingImmunizations['PREGW_IMMCODE'] == "") {
                                $immunization->addAttribute("pPregwImmcode", "");
                            } else {
                                $immunization->addAttribute("pPregwImmcode", $genProfilingImmunizations['PREGW_IMMCODE']);
                            }
                            
                            if ($genProfilingImmunizations['ELDERLY_IMMCODE'] == null || $genProfilingImmunizations['ELDERLY_IMMCODE'] == "") {
                                $immunization->addAttribute("pElderlyImmcode", "");
                            } else {
                                $immunization->addAttribute("pElderlyImmcode", $genProfilingImmunizations['ELDERLY_IMMCODE']);
                            }
                            
                            
                            $immunization->addAttribute("pOtherImm", $genProfilingImmunizations['OTHER_IMM']);
                            $immunization->addAttribute("pReportStatus", "U");
                            $immunization->addAttribute("pDeficiencyRemarks", $genProfilingImmunizations['DEFICIENCY_REMARKS']);
                        }
                    }

                    foreach ($genProfilingImmunization as $genProfilingImmunizations) {
                            if ($genResultProfilings['TRANS_NO'] != $genProfilingImmunizations['TRANS_NO']) {
                                $immunization = $immunizations->addChild("IMMUNIZATION");
                                $immunization->addAttribute("pChildImmcode", "");
                                $immunization->addAttribute("pYoungwImmcode", "");
                                $immunization->addAttribute("pPregwImmcode", "");
                                $immunization->addAttribute("pElderlyImmcode", "");
                                $immunization->addAttribute("pOtherImm", "");
                                $immunization->addAttribute("pReportStatus", "U");
                                $immunization->addAttribute("pDeficiencyRemarks", "");
                            break;
                        }
                    }
            } else {
                $immunizations = $profile->addChild("IMMUNIZATIONS");
                $immunization = $immunizations->addChild("IMMUNIZATION");
                $immunization->addAttribute("pChildImmcode", "999");
                $immunization->addAttribute("pYoungwImmcode", "999");
                $immunization->addAttribute("pPregwImmcode", "999");
                $immunization->addAttribute("pElderlyImmcode", "999");
                $immunization->addAttribute("pOtherImm", "");
                $immunization->addAttribute("pReportStatus", "U");
                $immunization->addAttribute("pDeficiencyRemarks", "");
            }

            $menshist = $profile->addChild("MENSHIST");
            $menshist->addAttribute("pMenarchePeriod", $genResultProfilings['MENARCHE_PERIOD']);
            $menshist->addAttribute("pLastMensPeriod", $genResultProfilings['LAST_MENS_PERIOD']);
            $menshist->addAttribute("pPeriodDuration", $genResultProfilings['PERIOD_DURATION']);
            $menshist->addAttribute("pMensInterval", $genResultProfilings['MENS_INTERVAL']);
            $menshist->addAttribute("pPadsPerDay", $genResultProfilings['PADS_PER_DAY']);
            $menshist->addAttribute("pOnsetSexIc", $genResultProfilings['ONSET_SEX_IC']);
            $menshist->addAttribute("pBirthCtrlMethod", $genResultProfilings['BIRTH_CTRL_METHOD']);
            $menshist->addAttribute("pIsMenopause", $genResultProfilings['IS_MENOPAUSE']);
            $menshist->addAttribute("pMenopauseAge", $genResultProfilings['MENOPAUSE_AGE']);
            $menshist->addAttribute("pIsApplicable", $genResultProfilings['IS_APPLICABLE']);
            $menshist->addAttribute("pReportStatus", "U");
            $menshist->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $preghist = $profile->addChild("PREGHIST");
            $preghist->addAttribute("pPregCnt", $genResultProfilings['PREG_CNT']);
            $preghist->addAttribute("pDeliveryCnt", $genResultProfilings['DELIVERY_CNT']);
            $preghist->addAttribute("pDeliveryTyp", $genResultProfilings['DELIVERY_TYP']);
            $preghist->addAttribute("pFullTermCnt", $genResultProfilings['FULL_TERM_CNT']);
            $preghist->addAttribute("pPrematureCnt", $genResultProfilings['PREMATURE_CNT']);
            $preghist->addAttribute("pAbortionCnt", $genResultProfilings['ABORTION_CNT']);
            $preghist->addAttribute("pLivChildrenCnt", $genResultProfilings['LIV_CHILDREN_CNT']);
            $preghist->addAttribute("pWPregIndhyp", $genResultProfilings['W_PREG_INDHYP']);
            $preghist->addAttribute("pWFamPlan", $genResultProfilings['W_FAM_PLAN']);
            $preghist->addAttribute("pIsApplicable", $genResultProfilings['IS_APPLICABLE']);
            $preghist->addAttribute("pReportStatus", "U");
            $preghist->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $pepert = $profile->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $genResultProfilings['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $genResultProfilings['DIASTOLIC']);
            $pepert->addAttribute("pHr", $genResultProfilings['HR']);
            $pepert->addAttribute("pRr", $genResultProfilings['RR']);
            $pepert->addAttribute("pTemp", $genResultProfilings['TEMPERATURE']);
            $pepert->addAttribute("pHeight", $genResultProfilings['HEIGHT']);
            $pepert->addAttribute("pWeight", $genResultProfilings['WEIGHT']);
            $pepert->addAttribute("pBMI", $genResultProfilings['BMI']);
            $pepert->addAttribute("pZScore", utf8_decode($genResultProfilings['Z_SCORE']));
            $pepert->addAttribute("pLeftVision", $genResultProfilings['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $genResultProfilings['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $genResultProfilings['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $genResultProfilings['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $genResultProfilings['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $genResultProfilings['WAIST']);
            $pepert->addAttribute("pHip", $genResultProfilings['HIP']);
            $pepert->addAttribute("pLimbs", $genResultProfilings['LIMBS']);
            $pepert->addAttribute("pMidUpperArmCirc", $genResultProfilings['MID_UPPER_ARM']);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);


            $bloodtype = $profile->addChild("BLOODTYPE");
            $bloodtype->addAttribute("pBloodType", $genResultProfilings['blood_type']);
            $bloodtype->addAttribute("pReportStatus", "U");
            $bloodtype->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $peadmin = $profile->addChild("PEGENSURVEY");
            $peadmin->addAttribute("pGenSurveyId", $genResultProfilings['GENSURVEY_ID']);
            $peadmin->addAttribute("pGenSurveyRem", $genResultProfilings['GENSURVEY_REM']);
            $peadmin->addAttribute("pReportStatus", "U");
            $peadmin->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);


            if ($genProfilingPemisc != null) {
                $pemiscs = $profile->addChild("PEMISCS");
                foreach ($genProfilingPemisc as $genProfilingPemiscs) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingPemiscs['TRANS_NO']) {
                        $pemisc = $pemiscs->addChild("PEMISC");
                        $pemisc->addAttribute("pSkinId", $genProfilingPemiscs['SKIN_ID']);
                        $pemisc->addAttribute("pHeentId", $genProfilingPemiscs['HEENT_ID']);
                        $pemisc->addAttribute("pChestId", $genProfilingPemiscs['CHEST_ID']);
                        $pemisc->addAttribute("pHeartId", $genProfilingPemiscs['HEART_ID']);
                        $pemisc->addAttribute("pAbdomenId", $genProfilingPemiscs['ABDOMEN_ID']);
                        $pemisc->addAttribute("pNeuroId", $genProfilingPemiscs['NEURO_ID']);
                        $pemisc->addAttribute("pRectalId", $genProfilingPemiscs['RECTAL_ID']);
                        $pemisc->addAttribute("pGuId", $genProfilingPemiscs['GU_ID']);
                        $pemisc->addAttribute("pReportStatus", "U");
                        $pemisc->addAttribute("pDeficiencyRemarks", $genProfilingPemiscs['DEFICIENCY_REMARKS']);
                    }
                }

                foreach ($genProfilingPemisc as $genProfilingPemiscs) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingPemiscs['TRANS_NO']) {
                        $pemisc = $pemiscs->addChild("PEMISC");
                        $pemisc->addAttribute("pSkinId", "");
                        $pemisc->addAttribute("pHeentId", "");
                        $pemisc->addAttribute("pChestId", "");
                        $pemisc->addAttribute("pHeartId", "");
                        $pemisc->addAttribute("pAbdomenId", "");
                        $pemisc->addAttribute("pNeuroId", "");
                        $pemisc->addAttribute("pRectalId", "");
                        $pemisc->addAttribute("pGuId", "");
                        $pemisc->addAttribute("pReportStatus", "U");
                        $pemisc->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            } else {
                    $pemiscs = $profile->addChild("PEMISCS");
                    $pemisc = $pemiscs->addChild("PEMISC");
                    $pemisc->addAttribute("pSkinId", "");
                    $pemisc->addAttribute("pHeentId", "");
                    $pemisc->addAttribute("pChestId", "");
                    $pemisc->addAttribute("pHeartId", "");
                    $pemisc->addAttribute("pAbdomenId", "");
                    $pemisc->addAttribute("pNeuroId", "");
                    $pemisc->addAttribute("pRectalId", "");
                    $pemisc->addAttribute("pGuId", "");
                    $pemisc->addAttribute("pReportStatus", "U");
                    $pemisc->addAttribute("pDeficiencyRemarks", "");
            }

            $pespecific = $profile->addChild("PESPECIFIC");
            $pespecific->addAttribute("pSkinRem", $genResultProfilings['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $genResultProfilings['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $genResultProfilings['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $genResultProfilings['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $genResultProfilings['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $genResultProfilings['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $genResultProfilings['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $genResultProfilings['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $ncdqans = $profile->addChild("NCDQANS");
            $ncdqans->addAttribute("pQid1_Yn", $genResultProfilings['QID1_YN']);
            $ncdqans->addAttribute("pQid2_Yn", $genResultProfilings['QID2_YN']);
            $ncdqans->addAttribute("pQid3_Yn", $genResultProfilings['QID3_YN']);
            $ncdqans->addAttribute("pQid4_Yn", $genResultProfilings['QID4_YN']);
            $ncdqans->addAttribute("pQid5_Ynx", $genResultProfilings['QID5_YNX']);
            $ncdqans->addAttribute("pQid6_Yn", $genResultProfilings['QID6_YN']);
            $ncdqans->addAttribute("pQid7_Yn", $genResultProfilings['QID7_YN']);
            $ncdqans->addAttribute("pQid8_Yn", $genResultProfilings['QID8_YN']);
            $ncdqans->addAttribute("pQid9_Yn", $genResultProfilings['QID9_YN']);
            $ncdqans->addAttribute("pQid10_Yn", $genResultProfilings['QID10_YN']);
            $ncdqans->addAttribute("pQid11_Yn", $genResultProfilings['QID11_YN']);
            $ncdqans->addAttribute("pQid12_Yn", $genResultProfilings['QID12_YN']);
            $ncdqans->addAttribute("pQid13_Yn", $genResultProfilings['QID13_YN']);
            $ncdqans->addAttribute("pQid14_Yn", $genResultProfilings['QID14_YN']);
            $ncdqans->addAttribute("pQid15_Yn", $genResultProfilings['QID15_YN']);
            $ncdqans->addAttribute("pQid16_Yn", $genResultProfilings['QID16_YN']);
            $ncdqans->addAttribute("pQid17_Abcde", $genResultProfilings['QID17_ABCDE']);
            $ncdqans->addAttribute("pQid18_Yn", $genResultProfilings['QID18_YN']);
            $ncdqans->addAttribute("pQid19_Yn", $genResultProfilings['QID19_YN']);
            $ncdqans->addAttribute("pQid19_Fbsmg", $genResultProfilings['QID19_FBSMG']);
            $ncdqans->addAttribute("pQid19_Fbsmmol", $genResultProfilings['QID19_FBSMMOL']);
            $ncdqans->addAttribute("pQid19_Fbsdate", $genResultProfilings['QID19_FBSDATE']);
            $ncdqans->addAttribute("pQid20_Yn", $genResultProfilings['QID20_YN']);
            $ncdqans->addAttribute("pQid20_Choleval", $genResultProfilings['QID20_CHOLEVAL']);
            $ncdqans->addAttribute("pQid20_Choledate", $genResultProfilings['QID20_CHOLEDATE']);
            $ncdqans->addAttribute("pQid21_Yn", $genResultProfilings['QID21_YN']);
            $ncdqans->addAttribute("pQid21_Ketonval", $genResultProfilings['QID21_KETONVAL']);
            $ncdqans->addAttribute("pQid21_Ketondate", $genResultProfilings['QID21_KETONDATE']);
            $ncdqans->addAttribute("pQid22_Yn", $genResultProfilings['QID22_YN']);
            $ncdqans->addAttribute("pQid22_Proteinval", $genResultProfilings['QID22_PROTEINVAL']);
            $ncdqans->addAttribute("pQid22_Proteindate", $genResultProfilings['QID22_PROTEINDATE']);
            $ncdqans->addAttribute("pQid23_Yn", $genResultProfilings['QID23_YN']);
            $ncdqans->addAttribute("pQid24_Yn", $genResultProfilings['QID24_YN']);
            $ncdqans->addAttribute("pReportStatus", "U");
            $ncdqans->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);
        }


    /*CONSULTATION XML GENERATION*/
    $consultations = $konsulta->addChild("SOAPS");

    if($genResultConsultation == NULL) {
        $consultation = $consultations->addChild("SOAP");
        $consultation->addAttribute("pHciCaseNo", "");
        $consultation->addAttribute("pHciTransNo", "");
        $consultation->addAttribute("pSoapDate", "");
        $consultation->addAttribute("pPatientPin", "");
        $consultation->addAttribute("pPatientType", "");
        $consultation->addAttribute("pMemPin", "");
        $consultation->addAttribute("pEffYear", "");
        $consultation->addAttribute("pATC", "");
        $consultation->addAttribute("pIsWalkedIn", "");
        $consultation->addAttribute("pCoPay", "");
        $consultation->addAttribute("pTransDate", "");
        $consultation->addAttribute("pReportStatus", "U");
        $consultation->addAttribute("pDeficiencyRemarks", "");

        $subjective = $consultation->addChild("SUBJECTIVE");
        $subjective->addAttribute("pIllnessHistory", "");
        $subjective->addAttribute("pSignsSymptoms", "");
        $subjective->addAttribute("pOtherComplaint", "");
        $subjective->addAttribute("pPainSite", "");
        $subjective->addAttribute("pReportStatus", "U");
        $subjective->addAttribute("pDeficiencyRemarks", "");

        $pepert = $consultation->addChild("PEPERT");
        $pepert->addAttribute("pSystolic", "");
        $pepert->addAttribute("pDiastolic", "");
        $pepert->addAttribute("pHr", "");
        $pepert->addAttribute("pRr", "");
        $pepert->addAttribute("pTemp", "");
        $pepert->addAttribute("pHeight", "");
        $pepert->addAttribute("pWeight", "");
        $pepert->addAttribute("pBMI", "");
        $pepert->addAttribute("pZScore", "");
        $pepert->addAttribute("pLeftVision", "");
        $pepert->addAttribute("pRightVision", "");
        $pepert->addAttribute("pLength", "");
        $pepert->addAttribute("pHeadCirc", "");
        $pepert->addAttribute("pSkinfoldThickness", "");
        $pepert->addAttribute("pWaist", "");
        $pepert->addAttribute("pHip", "");
        $pepert->addAttribute("pLimbs", "");
        $pepert->addAttribute("pMidUpperArmCirc", "");
        $pepert->addAttribute("pReportStatus", "U");
        $pepert->addAttribute("pDeficiencyRemarks", "");

        $pemiscs = $consultation->addChild("PEMISCS");
        $pemisc = $pemiscs->addChild("PEMISC");
        $pemisc->addAttribute("pSkinId","");
        $pemisc->addAttribute("pHeentId", "");
        $pemisc->addAttribute("pChestId", "");
        $pemisc->addAttribute("pHeartId", "");
        $pemisc->addAttribute("pAbdomenId", "");
        $pemisc->addAttribute("pNeuroId", "");
        $pemisc->addAttribute("pGuId", "");
        $pemisc->addAttribute("pRectalId", "");
        $pemisc->addAttribute("pReportStatus", "U");
        $pemisc->addAttribute("pDeficiencyRemarks", "");

        $pespecific = $consultation->addChild("PESPECIFIC");
        $pespecific->addAttribute("pSkinRem", "");
        $pespecific->addAttribute("pHeentRem", "");
        $pespecific->addAttribute("pChestRem","");
        $pespecific->addAttribute("pHeartRem", "");
        $pespecific->addAttribute("pAbdomenRem", "");
        $pespecific->addAttribute("pNeuroRem","");
        $pespecific->addAttribute("pRectalRem", "");
        $pespecific->addAttribute("pGuRem", "");
        $pespecific->addAttribute("pReportStatus", "U");
        $pespecific->addAttribute("pDeficiencyRemarks", "");

        $icds = $consultation->addChild("ICDS");
        $icd = $icds->addChild("ICD");
        $icd->addAttribute("pIcdCode", "");
        $icd->addAttribute("pReportStatus", "U");
        $icd->addAttribute("pDeficiencyRemarks", "");

        $diagnostics = $consultation->addChild("DIAGNOSTICS");
        $diagnostic = $diagnostics->addChild("DIAGNOSTIC");
        $diagnostic->addAttribute("pDiagnosticId", "");
        $diagnostic->addAttribute("pOthRemarks", "");
        $diagnostic->addAttribute("pIsPhysicianRecommendation", "");
        $diagnostic->addAttribute("pPatientRemarks", "");
        $diagnostic->addAttribute("pReportStatus", "U");
        $diagnostic->addAttribute("pDeficiencyRemarks", "");

        $managements = $consultation->addChild("MANAGEMENTS");
        $management = $managements->addChild("MANAGEMENT");
        $management->addAttribute("pManagementId", "");
        $management->addAttribute("pOthRemarks", "");
        $management->addAttribute("pReportStatus", "U");
        $management->addAttribute("pDeficiencyRemarks", "");

        $advice = $consultation->addChild("ADVICE");
        $advice->addAttribute("pRemarks", "");
        $advice->addAttribute("pReportStatus", "U");
        $advice->addAttribute("pDeficiencyRemarks", "");

    } else {
        foreach ($genResultConsultation as $genResultConsultations) {
            $consultation = $consultations->addChild("SOAP");
            $consultation->addAttribute("pHciCaseNo", $genResultConsultations['CASE_NO']);
            $consultation->addAttribute("pHciTransNo", $genResultConsultations['TRANS_NO']);
            $consultation->addAttribute("pSoapDate", $genResultConsultations['SOAP_DATE']);
            $consultation->addAttribute("pPatientPin", $genResultConsultations['PX_PIN']);
            $consultation->addAttribute("pPatientType", $genResultConsultations['PX_TYPE']);
            $consultation->addAttribute("pMemPin", $genResultConsultations['MEM_PIN']);
            $consultation->addAttribute("pEffYear", $genResultConsultations['EFF_YEAR']);
            $consultation->addAttribute("pATC", $genResultConsultations['SOAP_OTP']);
            $consultation->addAttribute("pIsWalkedIn", $genResultConsultations['WITH_ATC']);
            $consultation->addAttribute("pCoPay", $genResultConsultations['CO_PAY']);
            $consultation->addAttribute("pTransDate", $genResultConsultations['DATE_ADDED']);
            $consultation->addAttribute("pReportStatus", "U");
            $consultation->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);

            if ($genResultConsultations['ILLNESS_HISTORY'] != NULL) {
                $subjective = $consultation->addChild("SUBJECTIVE");
                $subjective->addAttribute("pIllnessHistory", $genResultConsultations['ILLNESS_HISTORY']);
                $subjective->addAttribute("pSignsSymptoms", $genResultConsultations['SIGNS_SYMPTOMS']);

                $chiefComplaintList = explode (";", $genResultConsultations['SIGNS_SYMPTOMS']);
                    foreach ($chiefComplaintList as $chiefComplaint) {
                       if ($chiefComplaint == "X") {
                            if ($genResultConsultations['OTHER_COMPLAINT'] != null) {
                                $vOtherComplaintStr = $genResultConsultations['OTHER_COMPLAINT'];
                            } else {
                                $vOtherComplaintStr = "NOT APPLICABLE";
                            }
                       }
                       break;
                    }

                if ($genResultConsultations['SIGNS_SYMPTOMS'] == "X") {
                    if ($genResultConsultations['OTHER_COMPLAINT'] != null) {
                        $vOtherComplaintStr = $genResultConsultations['OTHER_COMPLAINT'];
                    } else {
                        $vOtherComplaintStr = "NOT APPLICABLE";
                    }
                }

                $subjective->addAttribute("pOtherComplaint", $vOtherComplaintStr);
                $subjective->addAttribute("pPainSite", $genResultConsultations['PAIN_SITE']);
                $subjective->addAttribute("pReportStatus", "U");
                $subjective->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);
            } else {
                $subjective = $consultation->addChild("SUBJECTIVE");
                $subjective->addAttribute("pIllnessHistory", "NOT APPLICABLE");
                $subjective->addAttribute("pSignsSymptoms", $genResultConsultations['SIGNS_SYMPTOMS']);

                $chiefComplaintList = explode (";", $genResultConsultations['SIGNS_SYMPTOMS']);
                    foreach ($chiefComplaintList as $chiefComplaint) {
                       if ($chiefComplaint == "X") {
                            if ($genResultConsultations['OTHER_COMPLAINT'] != null) {
                                $vOtherComplaintStr = $genResultConsultations['OTHER_COMPLAINT'];
                            } else {
                                $vOtherComplaintStr = "NOT APPLICABLE";
                            }
                       }
                    }

                if ($genResultConsultations['SIGNS_SYMPTOMS'] == "X") {
                    if ($genResultConsultations['OTHER_COMPLAINT'] != null) {
                        $vOtherComplaintStr = $genResultConsultations['OTHER_COMPLAINT'];
                    } else {
                        $vOtherComplaintStr = "NOT APPLICABLE";
                    }
                }

                $subjective->addAttribute("pOtherComplaint", $vOtherComplaintStr);
                $subjective->addAttribute("pPainSite", $genResultConsultations['PAIN_SITE']);
                $subjective->addAttribute("pReportStatus", "U");
                $subjective->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);
            }
           

            $pepert = $consultation->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $genResultConsultations['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $genResultConsultations['DIASTOLIC']);
            $pepert->addAttribute("pHr", $genResultConsultations['HR']);
            $pepert->addAttribute("pRr", $genResultConsultations['RR']);
            $pepert->addAttribute("pTemp", $genResultConsultations['TEMPERATURE']);
            if ($genResultConsultations['HEIGHT'] != null) {
                $vHeight = $genResultConsultations['HEIGHT'];
            } else {
                $vHeight = 0;
            }
            $pepert->addAttribute("pHeight", $vHeight);
            $pepert->addAttribute("pWeight", $genResultConsultations['WEIGHT']);
            $pepert->addAttribute("pBMI", $genResultConsultations['BMI']);
            $pepert->addAttribute("pZScore", $genResultConsultations['Z_SCORE']);
            $pepert->addAttribute("pLeftVision", $genResultConsultations['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $genResultConsultations['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $genResultConsultations['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $genResultConsultations['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $genResultConsultations['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $genResultConsultations['WAIST']);
            $pepert->addAttribute("pHip", $genResultConsultations['HIP']);
            $pepert->addAttribute("pLimbs", $genResultConsultations['LIMBS']);
            $pepert->addAttribute("pMidUpperArmCirc", $genResultConsultations['MID_UPPER_ARM']);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);

            if($genConsultPemisc != NULL) {
                        $pemiscs = $consultation->addChild("PEMISCS");
                foreach ($genConsultPemisc as $genConsultPemiscs) {
                    if ($genResultConsultations['TRANS_NO'] == $genConsultPemiscs['TRANS_NO']) {
                        $pemisc = $pemiscs->addChild("PEMISC");
                        $pemisc->addAttribute("pSkinId", $genConsultPemiscs['SKIN_ID']);
                        $pemisc->addAttribute("pHeentId", $genConsultPemiscs['HEENT_ID']);
                        $pemisc->addAttribute("pChestId", $genConsultPemiscs['CHEST_ID']);
                        $pemisc->addAttribute("pHeartId", $genConsultPemiscs['HEART_ID']);
                        $pemisc->addAttribute("pAbdomenId", $genConsultPemiscs['ABDOMEN_ID']);
                        $pemisc->addAttribute("pNeuroId", $genConsultPemiscs['NEURO_ID']);
                        $pemisc->addAttribute("pGuId", $genConsultPemiscs['GU_ID']);
                        $pemisc->addAttribute("pRectalId", $genConsultPemiscs['RECTAL_ID']);
                        $pemisc->addAttribute("pReportStatus", "U");
                        $pemisc->addAttribute("pDeficiencyRemarks", $genConsultPemiscs['DEFICIENCY_REMARKS']);
                    }
                }
            } else{
               
                        $pemiscs = $consultation->addChild("PEMISCS");
                        $pemisc = $pemiscs->addChild("PEMISC");
                        $pemisc->addAttribute("pSkinId", "");
                        $pemisc->addAttribute("pHeentId", "");
                        $pemisc->addAttribute("pChestId", "");
                        $pemisc->addAttribute("pHeartId", "");
                        $pemisc->addAttribute("pAbdomenId", "");
                        $pemisc->addAttribute("pNeuroId", "");
                        $pemisc->addAttribute("pGuId", "");
                        $pemisc->addAttribute("pRectalId", "");
                        $pemisc->addAttribute("pReportStatus", "U");
                        $pemisc->addAttribute("pDeficiencyRemarks", "");
            }

            $pespecific = $consultation->addChild("PESPECIFIC");
            $pespecific->addAttribute("pSkinRem", $genResultConsultations['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $genResultConsultations['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $genResultConsultations['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $genResultConsultations['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $genResultConsultations['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $genResultConsultations['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $genResultConsultations['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $genResultConsultations['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);

            if($genConsultIcd != NULL) {
                        $icds = $consultation->addChild("ICDS");
                foreach ($genConsultIcd as $genConsultIcds) {
                    if ($genResultConsultations['TRANS_NO'] == $genConsultIcds['TRANS_NO']) {
                        $icd = $icds->addChild("ICD");
                        if ($genConsultIcds['ICD_CODE'] != null || $genConsultIcds['ICD_CODE'] != "") {
                            $icd->addAttribute("pIcdCode", $genConsultIcds['ICD_CODE']);
                        } else {
                            $icd->addAttribute("pIcdCode", "000");
                        }
                       
                        $icd->addAttribute("pReportStatus", "U");
                        $icd->addAttribute("pDeficiencyRemarks", $genConsultIcds['DEFICIENCY_REMARKS']);
                    } 
                }
            } else{
                        $icds = $consultation->addChild("ICDS");
                        $icd = $icds->addChild("ICD");
                        $icd->addAttribute("pIcdCode", "000");
                        $icd->addAttribute("pReportStatus", "U");
                        $icd->addAttribute("pDeficiencyRemarks", "");
            }

            if($genConsultationDiagnostic != NULL) {
                        $diagnostics = $consultation->addChild("DIAGNOSTICS");
                foreach ($genConsultationDiagnostic as $genResultConsultationDiagnostics) {
                    if ($genResultConsultations['TRANS_NO'] == $genResultConsultationDiagnostics['TRANS_NO']) {
                        $diagnostic = $diagnostics->addChild("DIAGNOSTIC");
                        $diagnostic->addAttribute("pDiagnosticId", $genResultConsultationDiagnostics['DIAGNOSTIC_ID']);
                        $diagnostic->addAttribute("pOthRemarks", $genResultConsultationDiagnostics['OTH_REMARKS']);
                        $diagnostic->addAttribute("pIsPhysicianRecommendation", $genResultConsultationDiagnostics['IS_DR_RECOMMENDED']);
                        $diagnostic->addAttribute("pPatientRemarks", $genResultConsultationDiagnostics['PX_REMARKS']);
                        $diagnostic->addAttribute("pReportStatus", "U");
                        $diagnostic->addAttribute("pDeficiencyRemarks", $genResultConsultationDiagnostics['DEFICIENCY_REMARKS']);
                    }
                }
            } else{
                $diagnostics = $consultation->addChild("DIAGNOSTICS");
                $diagnostic = $diagnostics->addChild("DIAGNOSTIC");
                $diagnostic->addAttribute("pDiagnosticId", "");
                $diagnostic->addAttribute("pOthRemarks", "");
                $diagnostic->addAttribute("pIsPhysicianRecommendation", "");
                $diagnostic->addAttribute("pPatientRemarks", "");
                $diagnostic->addAttribute("pReportStatus", "U");
                $diagnostic->addAttribute("pDeficiencyRemarks", "");
            }

            if($genConsultManagement != NULL) {
                        $managements = $consultation->addChild("MANAGEMENTS");
                foreach ($genConsultManagement as $genConsultManagements) {
                    if ($genResultConsultations['TRANS_NO'] == $genConsultManagements['TRANS_NO']) {
                        $management = $managements->addChild("MANAGEMENT");
                        $management->addAttribute("pManagementId", $genConsultManagements['MANAGEMENT_ID']);
                        if ($genConsultManagements['MANAGEMENT_ID'] == "X") {
                            $management->addAttribute("pOthRemarks", $genConsultManagements['OTH_REMARKS']);
                        } else {
                            $management->addAttribute("pOthRemarks", "");
                        }                       
                        $management->addAttribute("pReportStatus", "U");
                        $management->addAttribute("pDeficiencyRemarks", $genConsultManagements['DEFICIENCY_REMARKS']);
                    } else {
                        $management = $managements->addChild("MANAGEMENT");
                        $management->addAttribute("pManagementId", "0");
                        $management->addAttribute("pOthRemarks", "");
                        $management->addAttribute("pReportStatus", "U");
                        $management->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            } else{
                        $managements = $consultation->addChild("MANAGEMENTS");
                        $management = $managements->addChild("MANAGEMENT");
                        $management->addAttribute("pManagementId", "0");
                        $management->addAttribute("pOthRemarks", "");
                        $management->addAttribute("pReportStatus", "U");
                        $management->addAttribute("pDeficiencyRemarks", "");
            }

            if ($genResultConsultations['REMARKS'] != NULL) {
                $advice = $consultation->addChild("ADVICE");
                $advice->addAttribute("pRemarks", $genResultConsultations['REMARKS']);
                $advice->addAttribute("pReportStatus", "U");
                $advice->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);
            } else {
                $advice = $consultation->addChild("ADVICE");
                $advice->addAttribute("pRemarks", "NOT APPLICABLE");
                $advice->addAttribute("pReportStatus", "U");
                $advice->addAttribute("pDeficiencyRemarks", $genResultConsultations['DEFICIENCY_REMARKS']);
            }

        }
    }

    /*LABORATORY RESULTS XML GENERATION*/    
    if($genResultLabs != NULL){
            $labresults = $konsulta->addChild("DIAGNOSTICEXAMRESULTS");

            // fpe diagnostic results
            foreach ($genResultProfiling as $genResultProfilings) {
               if($genProfilingFamhist != NULL) {
                    foreach ($genProfilingFamhist as $genProfilingFamhists) {
                        if ($genResultProfilings['TRANS_NO'] == $genProfilingFamhists['TRANS_NO']) {
                           
                            if ($genProfilingFamhists['MDISEASE_CODE'] == "006") {

                                    $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
                                    $labresult->addAttribute("pHciCaseNo", $genResultProfilings['CASE_NO']);
                                    $labresult->addAttribute("pHciTransNo", $genResultProfilings['TRANS_NO']);
                                    $labresult->addAttribute("pPatientPin", $genResultProfilings['PX_PIN']);
                                    $labresult->addAttribute("pPatientType", $genResultProfilings['PX_TYPE']);
                                    $labresult->addAttribute("pMemPin", $genResultProfilings['MEM_PIN']);
                                    $labresult->addAttribute("pEffYear", $genResultProfilings['EFF_YEAR']);

                                        //
                                        // FBS
                                        //
                                            $getFBSRecordPerCaseNo = getReportResultLabFbsPerCaseNo($genProfilingFamhists['TRANS_NO']);
                                           
                                                $fbss = $labresult->addChild("FBSS");
                                                $fbs = $fbss->addChild("FBS");
                                                $fbs->addAttribute("pReferralFacility", $getFBSRecordPerCaseNo['REFERRAL_FACILITY']);
                                                $fbs->addAttribute("pLabDate", $getFBSRecordPerCaseNo['LAB_DATE']);
                                                $fbs->addAttribute("pGlucoseMg", $getFBSRecordPerCaseNo['GLUCOSE_MG']);
                                                $fbs->addAttribute("pGlucoseMmol", $getFBSRecordPerCaseNo['GLUCOSE_MMOL']);
                                                $fbs->addAttribute("pDateAdded", $getFBSRecordPerCaseNo['DATE_ADDED']);
                                                $fbs->addAttribute("pStatus", $getFBSRecordPerCaseNo['IS_APPLICABLE']);
                                                $fbs->addAttribute("pDiagnosticLabFee", $getFBSRecordPerCaseNo['DIAGNOSTIC_FEE']);
                                                $fbs->addAttribute("pReportStatus", "U");
                                                $fbs->addAttribute("pDeficiencyRemarks", "");
                                            

                                        //
                                        // RBS
                                        //
                                            $getRBSRecordPerCaseNo = getReportResultLabRbsPerCaseNo($genProfilingFamhists['TRANS_NO']);
                                           
                                                $rbss = $labresult->addChild("RBSS");
                                                $rbs = $rbss->addChild("RBS");
                                                $rbs->addAttribute("pReferralFacility", $getRBSRecordPerCaseNo['REFERRAL_FACILITY']);
                                                $rbs->addAttribute("pLabDate", $getRBSRecordPerCaseNo['LAB_DATE']);
                                                $rbs->addAttribute("pGlucoseMg", $getRBSRecordPerCaseNo['GLUCOSE_MG']);
                                                $rbs->addAttribute("pGlucoseMmol", $getRBSRecordPerCaseNo['GLUCOSE_MMOL']);
                                                $rbs->addAttribute("pDateAdded", $getRBSRecordPerCaseNo['DATE_ADDED']);
                                                $rbs->addAttribute("pStatus", $getRBSRecordPerCaseNo['IS_APPLICABLE']);
                                                $rbs->addAttribute("pDiagnosticLabFee", $getRBSRecordPerCaseNo['DIAGNOSTIC_FEE']);
                                                $rbs->addAttribute("pReportStatus", "U");
                                                $rbs->addAttribute("pDeficiencyRemarks", "");
                                            
                                        
                            }
                           
                        } 
                    }
                } 
            }
            
        // consultation diagnostic results
        foreach ($genResultLabs as $genResultLab) {
            $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
            $labresult->addAttribute("pHciCaseNo", $genResultLab['CASE_NO']);
            $labresult->addAttribute("pHciTransNo", $genResultLab['TRANS_NO']);
            $labresult->addAttribute("pPatientPin", $genResultLab['PX_PIN']);
            $labresult->addAttribute("pPatientType", $genResultLab['PX_TYPE']);
            $labresult->addAttribute("pMemPin", $genResultLab['MEM_PIN']);
            $labresult->addAttribute("pEffYear", $genResultLab['EFF_YEAR']);

            if($genResultLabsCbc != NULL){
                    // $cbcs = $labresult->addChild("CBCS");
                foreach ($genResultLabsCbc as $genResultLabsCbcs) {
                    if ($genResultLab['TRANS_NO'] == $genResultLabsCbcs['TRANS_NO']) {
                        $cbcs = $labresult->addChild("CBCS");
                        $cbc = $cbcs->addChild("CBC");
                        $cbc->addAttribute("pReferralFacility", $genResultLabsCbcs['REFERRAL_FACILITY']);
                        $cbc->addAttribute("pLabDate", $genResultLabsCbcs['LAB_DATE']);
                        $cbc->addAttribute("pHematocrit", $genResultLabsCbcs['HEMATOCRIT']);
                        $cbc->addAttribute("pHemoglobinG", $genResultLabsCbcs['HEMOGLOBIN_G']);
                        $cbc->addAttribute("pHemoglobinMmol", $genResultLabsCbcs['HEMOGLOBIN_MMOL']);
                        $cbc->addAttribute("pMhcPg", $genResultLabsCbcs['MHC_PG']);
                        $cbc->addAttribute("pMhcFmol", $genResultLabsCbcs['MHC_FMOL']);
                        $cbc->addAttribute("pMchcGhb", $genResultLabsCbcs['MCHC_GHB']);
                        $cbc->addAttribute("pMchcMmol", $genResultLabsCbcs['MCHC_MMOL']);
                        $cbc->addAttribute("pMcvUm", $genResultLabsCbcs['MCV_UM']);
                        $cbc->addAttribute("pMcvFl", $genResultLabsCbcs['MCV_FL']);
                        $cbc->addAttribute("pWbc1000", $genResultLabsCbcs['WBC_1000']);
                        $cbc->addAttribute("pWbc10", $genResultLabsCbcs['WBC_10']);
                        $cbc->addAttribute("pMyelocyte", $genResultLabsCbcs['MYELOCYTE']);
                        $cbc->addAttribute("pNeutrophilsBnd", $genResultLabsCbcs['NEUTROPHILS_BND']);
                        $cbc->addAttribute("pNeutrophilsSeg", $genResultLabsCbcs['NEUTROPHILS_SEG']);
                        $cbc->addAttribute("pLymphocytes", $genResultLabsCbcs['LYMPHOCYTES']);
                        $cbc->addAttribute("pMonocytes", $genResultLabsCbcs['MONOCYTES']);
                        $cbc->addAttribute("pEosinophils", $genResultLabsCbcs['EOSINOPHILS']);
                        $cbc->addAttribute("pBasophils", $genResultLabsCbcs['BASOPHILS']);
                        $cbc->addAttribute("pPlatelet", $genResultLabsCbcs['PLATELET']);
                        $cbc->addAttribute("pDateAdded", $genResultLabsCbcs['DATE_ADDED']);
                        $cbc->addAttribute("pStatus", $genResultLabsCbcs['IS_APPLICABLE']);
                        $cbc->addAttribute("pDiagnosticLabFee", $genResultLabsCbcs['DIAGNOSTIC_FEE']);
                        $cbc->addAttribute("pReportStatus", "U");
                        $cbc->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            }

            if($genResultLabsUrine != NULL) {
                    // $urinalysiss = $labresult->addChild("URINALYSISS");
                foreach ($genResultLabsUrine as $genResultLabsUrines) {
                    if ($genResultLab['TRANS_NO'] == $genResultLabsCbcs['TRANS_NO']) {
                        $urinalysiss = $labresult->addChild("URINALYSISS");
                        $urinalysis = $urinalysiss->addChild("URINALYSIS");
                        $urinalysis->addAttribute("pReferralFacility", $genResultLabsUrines['REFERRAL_FACILITY']);
                        $urinalysis->addAttribute("pLabDate", $genResultLabsUrines['LAB_DATE']);
                        $urinalysis->addAttribute("pGravity", $genResultLabsUrines['GRAVITY']);
                        $urinalysis->addAttribute("pAppearance", $genResultLabsUrines['APPEARANCE']);
                        $urinalysis->addAttribute("pColor", $genResultLabsUrines['COLOR']);
                        $urinalysis->addAttribute("pGlucose", $genResultLabsUrines['GLUCOSE']);
                        $urinalysis->addAttribute("pProteins", $genResultLabsUrines['PROTEINS']);
                        $urinalysis->addAttribute("pKetones", $genResultLabsUrines['KETONES']);
                        $urinalysis->addAttribute("pPh", $genResultLabsUrines['PH']);
                        $urinalysis->addAttribute("pRbCells", $genResultLabsUrines['RB_CELLS']);
                        $urinalysis->addAttribute("pWbCells", $genResultLabsUrines['WB_CELLS']);
                        $urinalysis->addAttribute("pBacteria", $genResultLabsUrines['BACTERIA']);
                        $urinalysis->addAttribute("pCrystals", $genResultLabsUrines['CRYSTALS']);
                        $urinalysis->addAttribute("pBladderCell", $genResultLabsUrines['BLADDER_CELL']);
                        $urinalysis->addAttribute("pSquamousCell", $genResultLabsUrines['SQUAMOUS_CELL']);
                        $urinalysis->addAttribute("pTubularCell", $genResultLabsUrines['TUBULAR_CELL']);
                        $urinalysis->addAttribute("pBroadCasts", $genResultLabsUrines['BROAD_CASTS']);
                        $urinalysis->addAttribute("pEpithelialCast", $genResultLabsUrines['EPITHELIAL_CAST']);
                        $urinalysis->addAttribute("pGranularCast", $genResultLabsUrines['GRANULAR_CAST']);
                        $urinalysis->addAttribute("pHyalineCast", $genResultLabsUrines['HYALINE_CAST']);
                        $urinalysis->addAttribute("pRbcCast", $genResultLabsUrines['RBC_CAST']);
                        $urinalysis->addAttribute("pWaxyCast", $genResultLabsUrines['WAXY_CAST']);
                        $urinalysis->addAttribute("pWcCast", $genResultLabsUrines['WC_CAST']);
                        $urinalysis->addAttribute("pAlbumin", $genResultLabsUrines['ALBUMIN']);
                        $urinalysis->addAttribute("pPusCells", $genResultLabsUrines['PUS_CELLS']);
                        $urinalysis->addAttribute("pDateAdded", $genResultLabsUrines['DATE_ADDED']);
                        $urinalysis->addAttribute("pStatus", $genResultLabsUrines['IS_APPLICABLE']);
                        $urinalysis->addAttribute("pDiagnosticLabFee", $genResultLabsUrines['DIAGNOSTIC_FEE']);
                        $urinalysis->addAttribute("pReportStatus", "U");
                        $urinalysis->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            }

            if($genResultLabsChestXray != NULL) {
                    // $chestxrays = $labresult->addChild("CHESTXRAYS");
                foreach ($genResultLabsChestXray as $genResultLabsChest) {
                    if ($genResultLab['TRANS_NO'] == $genResultLabsChest['TRANS_NO']) {
                        $chestxrays = $labresult->addChild("CHESTXRAYS");
                        $chestxray = $chestxrays->addChild("CHESTXRAY");
                        $chestxray->addAttribute("pReferralFacility", $genResultLabsChest['REFERRAL_FACILITY']);
                        $chestxray->addAttribute("pLabDate", $genResultLabsChest['LAB_DATE']);
                        $chestxray->addAttribute("pFindings", $genResultLabsChest['FINDINGS']);
                        $chestxray->addAttribute("pRemarksFindings", $genResultLabsChest['REMARKS_FINDINGS']);
                        $chestxray->addAttribute("pObservation", $genResultLabsChest['OBSERVATION']);
                        $chestxray->addAttribute("pRemarksObservation", $genResultLabsChest['REMARKS_OBSERVATION']);
                        $chestxray->addAttribute("pDateAdded", $genResultLabsChest['DATE_ADDED']);
                        $chestxray->addAttribute("pStatus", $genResultLabsChest['IS_APPLICABLE']);
                        $chestxray->addAttribute("pDiagnosticLabFee", $genResultLabsChest['DIAGNOSTIC_FEE']);
                        $chestxray->addAttribute("pReportStatus", "U");
                        $chestxray->addAttribute("pDeficiencyRemarks", $genResultLabsChest['DEFICIENCY_REMARKS']);
                    }
                }
            }

             if($genResultLabsSputum != NULL) {
                    // $sputums = $labresult->addChild("SPUTUMS");
                foreach ($genResultLabsSputum as $genResultLabsSputums) {
                    if ($genResultLab['TRANS_NO'] == $genResultLabsSputums['TRANS_NO']) {
                        $sputums = $labresult->addChild("SPUTUMS");
                        $sputum = $sputums->addChild("SPUTUM");
                        $sputum->addAttribute("pReferralFacility", $genResultLabsSputums['REFERRAL_FACILITY']);
                        $sputum->addAttribute("pLabDate", $genResultLabsSputums['LAB_DATE']);
                        $sputum->addAttribute("pDataCollection", $genResultLabsSputums['DATA_COLLECTION']);
                        $sputum->addAttribute("pFindings", $genResultLabsSputums['FINDINGS']);
                        $sputum->addAttribute("pRemarks", $genResultLabsSputums['REMARKS']);
                        $sputum->addAttribute("pNoPlusses", $genResultLabsSputums['NO_PLUSSES']);
                        $sputum->addAttribute("pDateAdded", $genResultLabsSputums['DATE_ADDED']);
                        $sputum->addAttribute("pStatus", $genResultLabsSputums['IS_APPLICABLE']);
                        $sputum->addAttribute("pDiagnosticLabFee", $genResultLabsSputums['DIAGNOSTIC_FEE']);
                        $sputum->addAttribute("pReportStatus", "U");
                        $sputum->addAttribute("pDeficiencyRemarks", $genResultLabsSputums['DEFICIENCY_REMARKS']);
                    }
                }
            }

           
            if($genResultLabsLipidProf != NULL) {
                    // $lipidprofs = $labresult->addChild("LIPIDPROFILES");
                foreach ($genResultLabsLipidProf as $genResultLabsLipidProfs) {
                    if ($genResultLab['TRANS_NO'] == $genResultLabsLipidProfs['TRANS_NO']) {
                        $lipidprofs = $labresult->addChild("LIPIDPROFILES");
                        $lipidprof = $lipidprofs->addChild("LIPIDPROFILE");
                        $lipidprof->addAttribute("pReferralFacility", $genResultLabsLipidProfs['REFERRAL_FACILITY']);
                        $lipidprof->addAttribute("pLabDate", $genResultLabsLipidProfs['LAB_DATE']);
                        $lipidprof->addAttribute("pLdl", $genResultLabsLipidProfs['LDL']);
                        $lipidprof->addAttribute("pHdl", $genResultLabsLipidProfs['HDL']);
                        $lipidprof->addAttribute("pTotal", $genResultLabsLipidProfs['TOTAL']);
                        $lipidprof->addAttribute("pCholesterol", $genResultLabsLipidProfs['CHOLESTEROL']);
                        $lipidprof->addAttribute("pTriglycerides", $genResultLabsLipidProfs['TRIGLYCERIDES']);
                        $lipidprof->addAttribute("pDateAdded", $genResultLabsLipidProfs['DATE_ADDED']);
                        $lipidprof->addAttribute("pStatus", $genResultLabsLipidProfs['IS_APPLICABLE']);
                        $lipidprof->addAttribute("pDiagnosticLabFee", $genResultLabsLipidProfs['DIAGNOSTIC_FEE']);
                        $lipidprof->addAttribute("pReportStatus", "U");
                        $lipidprof->addAttribute("pDeficiencyRemarks", $genResultLabsLipidProfs['DEFICIENCY_REMARKS']);
                    }
                }
            }

            
            if($genResultLabsFbs != NULL) {
                    //$fbss = $labresult->addChild("FBSS");
                foreach ($genResultLabsFbs as $genResultFbs) {
                    if ($genResultLab['TRANS_NO'] == $genResultFbs['TRANS_NO']) {
                        $fbss = $labresult->addChild("FBSS");
                        $fbs = $fbss->addChild("FBS");
                        $fbs->addAttribute("pReferralFacility", $genResultFbs['REFERRAL_FACILITY']);
                        $fbs->addAttribute("pLabDate", $genResultFbs['LAB_DATE']);
                        $fbs->addAttribute("pGlucoseMg", $genResultFbs['GLUCOSE_MG']);
                        $fbs->addAttribute("pGlucoseMmol", $genResultFbs['GLUCOSE_MMOL']);
                        $fbs->addAttribute("pDateAdded", $genResultFbs['DATE_ADDED']);
                        $fbs->addAttribute("pStatus", $genResultFbs['IS_APPLICABLE']);
                        $fbs->addAttribute("pDiagnosticLabFee", $genResultFbs['DIAGNOSTIC_FEE']);
                        $fbs->addAttribute("pReportStatus", $genResultFbs['REPORT_STATUS']);
                        $fbs->addAttribute("pDeficiencyRemarks", $genResultFbs['DEFICIENCY_REMARKS']);
                    }
                }
            }

           
            if($genResultLabsRbs != NULL) {
                foreach ($genResultLabsRbs as $genResultRbs) {
                    if ($genResultLab['TRANS_NO'] == $genResultRbs['TRANS_NO']) {
                        $rbss = $labresult->addChild("RBSS");
                        $rbs = $rbss->addChild("RBS");
                        $rbs->addAttribute("pReferralFacility", $genResultRbs['REFERRAL_FACILITY']);
                        $rbs->addAttribute("pLabDate", $genResultRbs['LAB_DATE']);
                        $rbs->addAttribute("pGlucoseMg", $genResultRbs['GLUCOSE_MG']);
                        $rbs->addAttribute("pGlucoseMmol", $genResultRbs['GLUCOSE_MMOL']);
                        $rbs->addAttribute("pDateAdded", $genResultRbs['DATE_ADDED']);
                        $rbs->addAttribute("pStatus", $genResultRbs['IS_APPLICABLE']);
                        $rbs->addAttribute("pDiagnosticLabFee", $genResultRbs['DIAGNOSTIC_FEE']);
                        $rbs->addAttribute("pReportStatus", "U");
                        $rbs->addAttribute("pDeficiencyRemarks", $genResultRbs['DEFICIENCY_REMARKS']);
                    }
                }
            }

           
            if($genResultLabsEcg != NULL) {
                    // $ecgs = $labresult->addChild("ECGS");
                foreach ($genResultLabsEcg as $genResultEcg) {                    
                    if ($genResultLab['TRANS_NO'] == $genResultEcg['TRANS_NO']) {
                        $ecgs = $labresult->addChild("ECGS");
                        $ecg = $ecgs->addChild("ECG");
                        $ecg->addAttribute("pReferralFacility", $genResultEcg['REFERRAL_FACILITY']);
                        $ecg->addAttribute("pLabDate", $genResultEcg['LAB_DATE']);
                        $ecg->addAttribute("pFindings", $genResultEcg['FINDINGS']);
                        $ecg->addAttribute("pRemarks", strtoupper($genResultEcg['REMARKS']));
                        $ecg->addAttribute("pDateAdded", $genResultEcg['DATE_ADDED']);
                        $ecg->addAttribute("pStatus", $genResultEcg['IS_APPLICABLE']);
                        $ecg->addAttribute("pDiagnosticLabFee", $genResultEcg['DIAGNOSTIC_FEE']);
                        $ecg->addAttribute("pReportStatus", "U");
                        $ecg->addAttribute("pDeficiencyRemarks", $genResultEcg['DEFICIENCY_REMARKS']);
                    }
                }
            }

            

            if($genResultLabsFecalysis != NULL) {
                    // $fecalysiss = $labresult->addChild("FECALYSISS");
                foreach ($genResultLabsFecalysis as $genResultFecalysis) {
                    if ($genResultLab['TRANS_NO'] == $genResultFecalysis['TRANS_NO']) {
                        $fecalysiss = $labresult->addChild("FECALYSISS");
                        $fecalysis = $fecalysiss->addChild("FECALYSIS");
                        $fecalysis->addAttribute("pReferralFacility", $genResultFecalysis['REFERRAL_FACILITY']);
                        $fecalysis->addAttribute("pLabDate", $genResultFecalysis['LAB_DATE']);
                        $fecalysis->addAttribute("pColor", $genResultFecalysis['COLOR']);
                        $fecalysis->addAttribute("pConsistency", $genResultFecalysis['CONSISTENCY']);
                        $fecalysis->addAttribute("pRbc", $genResultFecalysis['RBC']);
                        $fecalysis->addAttribute("pWbc", $genResultFecalysis['WBC']);
                        $fecalysis->addAttribute("pOva", $genResultFecalysis['OVA']);
                        $fecalysis->addAttribute("pParasite", $genResultFecalysis['PARASITE']);
                        $fecalysis->addAttribute("pBlood", $genResultFecalysis['BLOOD']);
                        $fecalysis->addAttribute("pPusCells", $genResultFecalysis['PUS_CELLS']);
                        $fecalysis->addAttribute("pDateAdded", $genResultFecalysis['DATE_ADDED']);
                        $fecalysis->addAttribute("pStatus", $genResultFecalysis['IS_APPLICABLE']);
                        $fecalysis->addAttribute("pDiagnosticLabFee", $genResultFecalysis['DIAGNOSTIC_FEE']);;
                        $fecalysis->addAttribute("pReportStatus", "U");
                        $fecalysis->addAttribute("pDeficiencyRemarks", $genResultFecalysis['DEFICIENCY_REMARKS']);
                    }
                }
            }

            
            if($genResultLabsPaps != NULL) {
                    // $papss = $labresult->addChild("PAPSMEARS");
                foreach ($genResultLabsPaps as $genResultPaps) {
                    if ($genResultLab['TRANS_NO'] == $genResultPaps['TRANS_NO']) {
                        $papss = $labresult->addChild("PAPSMEARS");
                        $paps = $papss->addChild("PAPSMEAR");
                        $paps->addAttribute("pReferralFacility", $genResultPaps['REFERRAL_FACILITY']);
                        $paps->addAttribute("pLabDate", $genResultPaps['LAB_DATE']);
                        $paps->addAttribute("pFindings", strtoupper($genResultPaps['FINDINGS']));
                        $paps->addAttribute("pImpression", strtoupper($genResultPaps['IMPRESSION']));
                        $paps->addAttribute("pDateAdded", $genResultPaps['DATE_ADDED']);
                        $paps->addAttribute("pStatus", $genResultPaps['IS_APPLICABLE']);
                        $paps->addAttribute("pDiagnosticLabFee", $genResultPaps['DIAGNOSTIC_FEE']);
                        $paps->addAttribute("pReportStatus", "U");
                        $paps->addAttribute("pDeficiencyRemarks", $genResultPaps['DEFICIENCY_REMARKS']);
                    }
                }
            }

            
             if($genResultLabsOgtt != NULL) {
                    // $ogtts = $labresult->addChild("OGTTS");
                foreach ($genResultLabsOgtt as $genResultOgtt) {
                    if ($genResultLab['TRANS_NO'] == $genResultOgtt['TRANS_NO']) {
                        $ogtts = $labresult->addChild("OGTTS");
                        $ogtt = $ogtts->addChild("OGTT");
                        $ogtt->addAttribute("pReferralFacility", $genResultOgtt['REFERRAL_FACILITY']);
                        $ogtt->addAttribute("pLabDate", $genResultOgtt['LAB_DATE']);
                        $ogtt->addAttribute("pExamFastingMg", $genResultOgtt['EXAM_FASTING_MG']);
                        $ogtt->addAttribute("pExamFastingMmol", $genResultOgtt['EXAM_FASTING_MMOL']);
                        $ogtt->addAttribute("pExamOgttOneHrMg", $genResultOgtt['EXAM_OGTT_ONE_MG']);
                        $ogtt->addAttribute("pExamOgttOneHrMmol", $genResultOgtt['EXAM_OGTT_ONE_MMOL']);
                        $ogtt->addAttribute("pExamOgttTwoHrMg", $genResultOgtt['EXAM_OGTT_TWO_MG']);
                        $ogtt->addAttribute("pExamOgttTwoHrMmol", $genResultOgtt['EXAM_OGTT_TWO_MMOL']);
                        $ogtt->addAttribute("pDateAdded", $genResultOgtt['DATE_ADDED']);
                        $ogtt->addAttribute("pStatus", $genResultOgtt['IS_APPLICABLE']);
                        $ogtt->addAttribute("pDiagnosticLabFee", $genResultOgtt['DIAGNOSTIC_FEE']);
                        $ogtt->addAttribute("pReportStatus", "U");
                        $ogtt->addAttribute("pDeficiencyRemarks", $genResultOgtt['DEFICIENCY_REMARKS']);
                    }
                }
            }

            
            if($getResultLabsFOBT != NULL) {
                    // $fobts = $labresult->addChild("FOBTS");
                foreach ($getResultLabsFOBT as $getResultFOBT) {
                    if ($genResultLab['TRANS_NO'] == $getResultFOBT['TRANS_NO']) {
                        $fobts = $labresult->addChild("FOBTS");
                        $fobt = $fobts->addChild("FOBT");
                        $fobt->addAttribute("pReferralFacility", $getResultFOBT['REFERRAL_FACILITY']);
                        $fobt->addAttribute("pLabDate", $getResultFOBT['LAB_DATE']);
                        $fobt->addAttribute("pFindings", $getResultFOBT['FINDINGS']);
                        $fobt->addAttribute("pDateAdded", $getResultFOBT['DATE_ADDED']);
                        $fobt->addAttribute("pStatus", $getResultFOBT['IS_APPLICABLE']);
                        $fobt->addAttribute("pDiagnosticLabFee", $getResultFOBT['DIAGNOSTIC_FEE']);
                        $fobt->addAttribute("pReportStatus", "U");
                        $fobt->addAttribute("pDeficiencyRemarks", "");
                    }
                }   
            }

           
            if($getResultLabsCreatinine != NULL) {
                    // $creatinines = $labresult->addChild("CREATININES");
                foreach ($getResultLabsCreatinine as $getResultCreatinine) {
                    if ($genResultLab['TRANS_NO'] == $getResultCreatinine['TRANS_NO']) {
                        $creatinines = $labresult->addChild("CREATININES");
                        $creatinine = $creatinines->addChild("CREATININE");
                        $creatinine->addAttribute("pReferralFacility", $getResultCreatinine['REFERRAL_FACILITY']);
                        $creatinine->addAttribute("pLabDate", $getResultCreatinine['LAB_DATE']);
                        $creatinine->addAttribute("pFindings", $getResultCreatinine['FINDINGS']);
                        $creatinine->addAttribute("pDateAdded", $getResultCreatinine['DATE_ADDED']);
                        $creatinine->addAttribute("pStatus", $getResultCreatinine['IS_APPLICABLE']);
                        $creatinine->addAttribute("pDiagnosticLabFee", $getResultCreatinine['DIAGNOSTIC_FEE']);
                        $creatinine->addAttribute("pReportStatus", "U");
                        $creatinine->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            }


            if($getResultLabsPDD != NULL) {
                    // $pdds = $labresult->addChild("PPDTests");
                foreach ($getResultLabsPDD as $getResultPDD) {
                    if ($genResultLab['TRANS_NO'] == $getResultPDD['TRANS_NO']) {
                        $pdds = $labresult->addChild("PPDTests");
                        $pdd = $pdds->addChild("PPDTest");
                        $pdd->addAttribute("pReferralFacility", $getResultPDD['REFERRAL_FACILITY']);
                        $pdd->addAttribute("pLabDate", date('Y-m-d', strtotime($getResultPDD['LAB_DATE'])));
                        $pdd->addAttribute("pFindings", $getResultPDD['FINDINGS']);
                        $pdd->addAttribute("pDateAdded", date('Y-m-d', strtotime($getResultPDD['DATE_ADDED'])));
                        $pdd->addAttribute("pStatus", $getResultPDD['IS_APPLICABLE']);
                        $pdd->addAttribute("pDiagnosticLabFee", $getResultPDD['DIAGNOSTIC_FEE']);
                        $pdd->addAttribute("pReportStatus", "U");
                        $pdd->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            }


            if($getResultLabsHbA1c != NULL) {
                    // $hba1cs = $labresult->addChild("HbA1cs");
                foreach ($getResultLabsHbA1c as $getResultHbA1c) {
                    if ($genResultLab['TRANS_NO'] == $getResultHbA1c['TRANS_NO']) {
                        $hba1cs = $labresult->addChild("HbA1cs");
                        $hba1c = $hba1cs->addChild("HbA1c");
                        $hba1c->addAttribute("pReferralFacility", $getResultHbA1c['REFERRAL_FACILITY']);
                        $hba1c->addAttribute("pLabDate", $getResultHbA1c['LAB_DATE']);
                        $hba1c->addAttribute("pFindings", $getResultHbA1c['FINDINGS']);
                        $hba1c->addAttribute("pDateAdded", $getResultHbA1c['DATE_ADDED']);
                        $hba1c->addAttribute("pStatus", $getResultHbA1c['IS_APPLICABLE']);
                        $hba1c->addAttribute("pDiagnosticLabFee", $getResultHbA1c['DIAGNOSTIC_FEE']);
                        $hba1c->addAttribute("pReportStatus", "U");
                        $hba1c->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            }

            if($getResultLabsOthDiag!= NULL) {
                    // $othDiags = $labresult->addChild("OTHERDIAGEXAMS");
                foreach ($getResultLabsOthDiag as $getResultOthDiag) {
                    if ($genResultLab['TRANS_NO'] == $getResultOthDiag['TRANS_NO']) {
                        $othDiags = $labresult->addChild("OTHERDIAGEXAMS");
                        $othDiag = $othDiags->addChild("OTHERDIAGEXAM");
                        $othDiag->addAttribute("pReferralFacility", $getResultOthDiag['REFERRAL_FACILITY']);
                        $othDiag->addAttribute("pLabDate", $getResultOthDiag['LAB_DATE']);
                        $othDiag->addAttribute("pOthDiagExam", $getResultOthDiag['OTH_DIAG_EXAM']);
                        $othDiag->addAttribute("pFindings", $getResultOthDiag['FINDINGS']);
                        $othDiag->addAttribute("pDateAdded", $getResultOthDiag['DATE_ADDED']);
                        $othDiag->addAttribute("pStatus", $getResultOthDiag['IS_APPLICABLE']);
                        $othDiag->addAttribute("pDiagnosticLabFee", $getResultOthDiag['DIAGNOSTIC_FEE']);
                        $othDiag->addAttribute("pReportStatus", "U");
                        $othDiag->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            }
        }
    } else {
            // fpe diagnostic results
            foreach ($genResultProfiling as $genResultProfilings) {
               if($genProfilingFamhist != NULL) {
                    foreach ($genProfilingFamhist as $genProfilingFamhists) {
                        if ($genResultProfilings['TRANS_NO'] == $genProfilingFamhists['TRANS_NO']) {
                           
                            if ($genProfilingFamhists['MDISEASE_CODE'] == "006") {
                                    $labresults = $konsulta->addChild("DIAGNOSTICEXAMRESULTS");
                                    $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
                                    $labresult->addAttribute("pHciCaseNo", $genResultProfilings['CASE_NO']);
                                    $labresult->addAttribute("pHciTransNo", $genResultProfilings['TRANS_NO']);
                                    $labresult->addAttribute("pPatientPin", $genResultProfilings['PX_PIN']);
                                    $labresult->addAttribute("pPatientType", $genResultProfilings['PX_TYPE']);
                                    $labresult->addAttribute("pMemPin", $genResultProfilings['MEM_PIN']);
                                    $labresult->addAttribute("pEffYear", $genResultProfilings['EFF_YEAR']);

                                        //
                                        // FBS
                                        //
                                            $getFBSRecordPerCaseNo = getReportResultLabFbsPerCaseNo($genProfilingFamhists['TRANS_NO']);
                                           
                                                $fbss = $labresult->addChild("FBSS");
                                                $fbs = $fbss->addChild("FBS");
                                                $fbs->addAttribute("pReferralFacility", $getFBSRecordPerCaseNo['REFERRAL_FACILITY']);
                                                $fbs->addAttribute("pLabDate", $getFBSRecordPerCaseNo['LAB_DATE']);
                                                $fbs->addAttribute("pGlucoseMg", $getFBSRecordPerCaseNo['GLUCOSE_MG']);
                                                $fbs->addAttribute("pGlucoseMmol", $getFBSRecordPerCaseNo['GLUCOSE_MMOL']);
                                                $fbs->addAttribute("pDateAdded", $getFBSRecordPerCaseNo['DATE_ADDED']);
                                                $fbs->addAttribute("pStatus", $getFBSRecordPerCaseNo['IS_APPLICABLE']);
                                                $fbs->addAttribute("pDiagnosticLabFee", $getFBSRecordPerCaseNo['DIAGNOSTIC_FEE']);
                                                $fbs->addAttribute("pReportStatus", $getFBSRecordPerCaseNo['REPORT_STATUS']);
                                                $fbs->addAttribute("pDeficiencyRemarks", $getFBSRecordPerCaseNo['DEFICIENCY_REMARKS']);
                                            

                                        //
                                        // RBS
                                        //
                                            $getRBSRecordPerCaseNo = getReportResultLabRbsPerCaseNo($genProfilingFamhists['TRANS_NO']);
                                           
                                                $rbss = $labresult->addChild("RBSS");
                                                $rbs = $rbss->addChild("RBS");
                                                $rbs->addAttribute("pReferralFacility", $getRBSRecordPerCaseNo['REFERRAL_FACILITY']);
                                                $rbs->addAttribute("pLabDate", $getRBSRecordPerCaseNo['LAB_DATE']);
                                                $rbs->addAttribute("pGlucoseMg", $getRBSRecordPerCaseNo['GLUCOSE_MG']);
                                                $rbs->addAttribute("pGlucoseMmol", $getRBSRecordPerCaseNo['GLUCOSE_MMOL']);
                                                $rbs->addAttribute("pDateAdded", $getRBSRecordPerCaseNo['DATE_ADDED']);
                                                $rbs->addAttribute("pStatus", $getRBSRecordPerCaseNo['IS_APPLICABLE']);
                                                $rbs->addAttribute("pDiagnosticLabFee", $getRBSRecordPerCaseNo['DIAGNOSTIC_FEE']);
                                                $rbs->addAttribute("pReportStatus", "U");
                                                $rbs->addAttribute("pDeficiencyRemarks", $getRBSRecordPerCaseNo['DEFICIENCY_REMARKS']);
                                            
                                        
                            }
                           
                        } 
                    }
                } 
            }
    }

    /*MEDICINE XML GENERATION*/
    $medicines = $konsulta->addChild("MEDICINES");
    if($genResultMedicine == NULL) {
        $meds = $medicines->addChild("MEDICINE");
        $meds->addAttribute("pHciCaseNo", "");
        $meds->addAttribute("pHciTransNo", "");
        $meds->addAttribute("pCategory", "");
        $meds->addAttribute("pDrugCode", "");
        $meds->addAttribute("pGenericCode", "");
        $meds->addAttribute("pSaltCode", "");
        $meds->addAttribute("pStrengthCode", "");
        $meds->addAttribute("pFormCode", "");
        $meds->addAttribute("pUnitCode", "");
        $meds->addAttribute("pPackageCode","");
        $meds->addAttribute("pOtherMedicine", "");
        $meds->addAttribute("pRoute","");
        $meds->addAttribute("pQuantity", "");
        $meds->addAttribute("pActualUnitPrice", "");
        $meds->addAttribute("pTotalAmtPrice", "");
        $meds->addAttribute("pInstructionQuantity", "");
        $meds->addAttribute("pInstructionStrength", "");
        $meds->addAttribute("pInstructionFrequency", "");
        $meds->addAttribute("pPrescribingPhysician", "");
        $meds->addAttribute("pIsDispensed", "N");
        $meds->addAttribute("pDateDispensed", "");
        $meds->addAttribute("pDispensingPersonnel", "");
        $meds->addAttribute("pIsApplicable", "");
        $meds->addAttribute("pDateAdded", "");
        $meds->addAttribute("pReportStatus", "U");
        $meds->addAttribute("pDeficiencyRemarks", "");
    } else{
        foreach ($genResultMedicine as $genResultMedicines) {
            $meds = $medicines->addChild("MEDICINE");
            $meds->addAttribute("pHciCaseNo", $genResultMedicines['CASE_NO']);
            $meds->addAttribute("pHciTransNo", $genResultMedicines['TRANS_NO']);
            $meds->addAttribute("pCategory", $genResultMedicines['CATEGORY']);
            $meds->addAttribute("pDrugCode", $genResultMedicines['DRUG_CODE']);
            $meds->addAttribute("pGenericCode", $genResultMedicines['GEN_CODE']);
            $meds->addAttribute("pSaltCode", $genResultMedicines['SALT_CODE']);
            $meds->addAttribute("pStrengthCode", $genResultMedicines['STRENGTH_CODE']);
            $meds->addAttribute("pFormCode", $genResultMedicines['FORM_CODE']);
            $meds->addAttribute("pUnitCode", $genResultMedicines['UNIT_CODE']);
            $meds->addAttribute("pPackageCode", $genResultMedicines['PACKAGE_CODE']);
            $meds->addAttribute("pOtherMedicine", $genResultMedicines['GENERIC_NAME']);
            $meds->addAttribute("pOthMedDrugGrouping", $genResultMedicines['DRUG_GROUPING']);
            $meds->addAttribute("pRoute", $genResultMedicines['ROUTE']);
            
            if ($genResultMedicines['QUANTITY'] == null ) {
                $meds->addAttribute("pQuantity", 0);
            } else {
                $meds->addAttribute("pQuantity", $genResultMedicines['QUANTITY']);
            }

            if ($genResultMedicines['DRUG_ACTUAL_PRICE'] == null ) {
                $meds->addAttribute("pActualUnitPrice", 0);
            } else {
                $meds->addAttribute("pActualUnitPrice", $genResultMedicines['DRUG_ACTUAL_PRICE']);
            }

            if ($genResultMedicines['AMT_PRICE'] == null ) {
                $meds->addAttribute("pTotalAmtPrice", 0);
            } else {
                $meds->addAttribute("pTotalAmtPrice", $genResultMedicines['AMT_PRICE']);
            }

            if ($genResultMedicines['INS_QUANTITY'] == null ) {
                $meds->addAttribute("pInstructionQuantity", 0);
            } else {
                $meds->addAttribute("pInstructionQuantity", $genResultMedicines['INS_QUANTITY']);
            }
            
            $meds->addAttribute("pInstructionStrength", $genResultMedicines['INS_STRENGTH']);
            $meds->addAttribute("pInstructionFrequency", $genResultMedicines['INS_FREQUENCY']);
            $meds->addAttribute("pPrescribingPhysician", $genResultMedicines['PRESC_PHYSICIAN']);
            $meds->addAttribute("pIsDispensed", $genResultMedicines['IS_DISPENSED']);
            
            if ($genResultMedicines['IS_DISPENSED'] == 'Y' && $genResultMedicines['DISPENSING_PERSONNEL'] == NULL) {
                $meds->addAttribute("pDateDispensed", $genResultMedicines['DISPENSED_DATE']);
                $meds->addAttribute("pDispensingPersonnel", $genResultMedicines['PRESC_PHYSICIAN']);
            } else if ($genResultMedicines['IS_DISPENSED'] == 'Y' && $genResultMedicines['DISPENSING_PERSONNEL'] != NULL) {
                $meds->addAttribute("pDateDispensed", $genResultMedicines['DISPENSED_DATE']);
                $meds->addAttribute("pDispensingPersonnel", $genResultMedicines['DISPENSING_PERSONNEL']);
            } else if ($genResultMedicines['IS_DISPENSED'] == 'N') {
                $meds->addAttribute("pDateDispensed", "");
                $meds->addAttribute("pDispensingPersonnel", "");
            }
           
            $meds->addAttribute("pIsApplicable", $genResultMedicines['IS_APPLICABLE']);
            $meds->addAttribute("pDateAdded", $genResultMedicines['DATE_ADDED']);
            $meds->addAttribute("pReportStatus", "U");
            $meds->addAttribute("pDeficiencyRemarks", $genResultMedicines['DEFICIENCY_REMARKS']);
        }
    }

    $dom = dom_import_simplexml($konsulta)->ownerDocument;
    $dom ->formatOutput = true;

    $xml = $dom->saveXML();
    $xmlString = str_replace("<?xml version=\"1.0\"?>\n", '', $xml);
    file_put_contents("tmp/genKonsultaXML.xml", $xmlString);

    return $xmlString;
}


/*Generate XML Report for First Encounter*/
function generateXmlFirstEncounter($genResultEnlist, $genResultProfiling, $genProfilingMedHist, $genProfilingMHspecific, $genProfilingPemisc,
                         $genProfilingSurghist, $genProfilingFamhist, $genProfilingFhspecific, $genProfilingImmunization,
                         $pStartDate, $pEndDate,  $genResultLabs, $genResultLabsFbs, $genResultLabsRbs){
    $konsulta = new SimpleXMLElement("<PCB></PCB>");

    $pReportTransNo = generateTransNo('REPORT_TRANS_NO');
    $pDateRange = $pStartDate." TO ".$pEndDate;

    $konsulta->addAttribute("pUsername", "");
    $konsulta->addAttribute("pPassword", "");
    $konsulta->addAttribute("pHciAccreNo", $_SESSION['pAccreNum']);
    $konsulta->addAttribute("pPMCCNo", "");
    $konsulta->addAttribute("pEnlistTotalCnt", count($genResultEnlist));
    $konsulta->addAttribute("pProfileTotalCnt", count($genResultProfiling));
    $konsulta->addAttribute("pSoapTotalCnt", "1");
    $konsulta->addAttribute("pCertificationId", "EKON-00-06-2020-00001");
    $konsulta->addAttribute("pHciTransmittalNumber", $pReportTransNo);

    /*ENLISTMENT XML GENERATION*/
    $enlistments = $konsulta->addChild("ENLISTMENTS");

        foreach ($genResultEnlist as $genResultEnlists) {
            $enlistment = $enlistments->addChild("ENLISTMENT");
            $enlistment->addAttribute("pHciCaseNo", $genResultEnlists['CASE_NO']);
            $enlistment->addAttribute("pHciTransNo", $genResultEnlists['TRANS_NO']);
            $enlistment->addAttribute("pEffYear", $genResultEnlists['EFF_YEAR']);
            $enlistment->addAttribute("pEnlistStat", $genResultEnlists['ENLIST_STAT']);
            $enlistment->addAttribute("pEnlistDate", $genResultEnlists['ENLIST_DATE']);
            $enlistment->addAttribute("pPackageType", $genResultEnlists['PACKAGE_TYPE']);
            $enlistment->addAttribute("pMemPin", trim($genResultEnlists['MEM_PIN']));
            $enlistment->addAttribute("pMemFname", trim(strReplaceEnye($genResultEnlists['MEM_FNAME'])));
            $enlistment->addAttribute("pMemMname", trim(strReplaceEnye($genResultEnlists['MEM_MNAME'])));
            $enlistment->addAttribute("pMemLname", trim(strReplaceEnye($genResultEnlists['MEM_LNAME'])));
            $enlistment->addAttribute("pMemExtname", trim($genResultEnlists['MEM_EXTNAME']));
            $enlistment->addAttribute("pMemDob", $genResultEnlists['MEM_DOB']);
            $enlistment->addAttribute("pPatientPin", $genResultEnlists['PX_PIN']);
            $enlistment->addAttribute("pPatientFname", trim(strReplaceEnye($genResultEnlists['PX_FNAME'])));
            $enlistment->addAttribute("pPatientMname", trim(strReplaceEnye($genResultEnlists['PX_MNAME'])));
            $enlistment->addAttribute("pPatientLname",  trim(strReplaceEnye($genResultEnlists['PX_LNAME'])));
            $enlistment->addAttribute("pPatientExtname", trim($genResultEnlists['PX_EXTNAME']));
            if ($genResultEnlists['PX_SEX'] == '0') {
                $vPxSex = "M";
            } else if ($genResultEnlists['PX_SEX'] == '1') {
                $vPxSex = "F";
            } else {
                $vPxSex = $genResultEnlists['PX_SEX'];
            }
            $enlistment->addAttribute("pPatientSex", $vPxSex);
            $enlistment->addAttribute("pPatientDob", $genResultEnlists['PX_DOB']);
            $enlistment->addAttribute("pPatientType", $genResultEnlists['PX_TYPE']);
            if ($genResultEnlists['PX_MOBILE_NO'] == null) {
                $vPxMobileNo = "-";
            } else {
                $vPxMobileNo = $genResultEnlists['PX_MOBILE_NO'];
            }
            $enlistment->addAttribute("pPatientMobileNo", $vPxMobileNo);
            $enlistment->addAttribute("pPatientLandlineNo", $genResultEnlists['PX_LANDLINE_NO']);
            $enlistment->addAttribute("pWithConsent", $genResultEnlists['WITH_CONSENT']);
            $enlistment->addAttribute("pTransDate", $genResultEnlists['TRANS_DATE']);
            $enlistment->addAttribute("pCreatedBy", $genResultEnlists['CREATED_BY']);
            $enlistment->addAttribute("pReportStatus", "U");
            $enlistment->addAttribute("pDeficiencyRemarks", "");
        }


    /*PROFILING XML GENERATION*/
    $profiling = $konsulta->addChild("PROFILING");
   
        foreach ($genResultProfiling as $genResultProfilings) {
            $profile = $profiling->addChild("PROFILE");
            $profile->addAttribute("pHciTransNo", $genResultProfilings['TRANS_NO']);
            $profile->addAttribute("pHciCaseNo", $genResultProfilings['CASE_NO']);
            $profile->addAttribute("pProfDate", $genResultProfilings['PROF_DATE']);
            $profile->addAttribute("pPatientPin", $genResultProfilings['PX_PIN']);
            $profile->addAttribute("pPatientType", $genResultProfilings['PX_TYPE']);
            $profile->addAttribute("pPatientAge", $genResultProfilings['PX_AGE']);
            $profile->addAttribute("pMemPin", $genResultProfilings['MEM_PIN']);
            $profile->addAttribute("pEffYear", $genResultProfilings['EFF_YEAR']);
            $profile->addAttribute("pATC", trim($genResultProfilings['PROFILE_OTP']));
            $profile->addAttribute("pIsWalkedIn", $genResultProfilings['WITH_ATC']);
            $profile->addAttribute("pTransDate", $genResultProfilings['DATE_ADDED']);
            $profile->addAttribute("pReportStatus", "U");
            $profile->addAttribute("pDeficiencyRemarks", "");

            if(count($genProfilingMedHist) > 0) {
                $medhists = $profile->addChild("MEDHISTS");
                foreach ($genProfilingMedHist as $genProfilingMedHists) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingMedHists['TRANS_NO']) {
                        $medhist = $medhists->addChild("MEDHIST");
                       
                        if ($genProfilingMedHists['MDISEASE_CODE'] == null || $genProfilingMedHists['MDISEASE_CODE'] == "") {
                            $medhist->addAttribute("pMdiseaseCode", "999");
                        } else {
                            $medhist->addAttribute("pMdiseaseCode", $genProfilingMedHists['MDISEASE_CODE']);    
                        }

                        $medhist->addAttribute("pReportStatus", "U");
                        $medhist->addAttribute("pDeficiencyRemarks", "");
                    }                      
                }

                foreach ($genProfilingMedHist as $genProfilingMedHists) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingMedHists['TRANS_NO']) {
                        $medhist = $medhists->addChild("MEDHIST");
                        $medhist->addAttribute("pMdiseaseCode", "999");
                        $medhist->addAttribute("pReportStatus", "U");
                        $medhist->addAttribute("pDeficiencyRemarks", "");
                    }  
                    break;                    
                }

            } else{                
                $medhists = $profile->addChild("MEDHISTS");
                $medhist = $medhists->addChild("MEDHIST");
                $medhist->addAttribute("pMdiseaseCode", "999");
                $medhist->addAttribute("pReportStatus", "U");
                $medhist->addAttribute("pDeficiencyRemarks", "");
            }

            if(count($genProfilingMHspecific) > 0) {
                $mhspecifics = $profile->addChild("MHSPECIFICS");
                foreach ($genProfilingMHspecific as $genProfilingMHspecifics) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingMHspecifics['TRANS_NO']) {
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");                        
                        $mhspecific->addAttribute("pMdiseaseCode", $genProfilingMHspecifics['MDISEASE_CODE']);
                        $mhspecific->addAttribute("pSpecificDesc", $genProfilingMHspecifics['SPECIFIC_DESC']);
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks", $genProfilingMHspecifics['DEFICIENCY_REMARKS']);
                    }
                }
                foreach ($genProfilingMHspecific as $genProfilingMHspecifics) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingMHspecifics['TRANS_NO']) {
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");                        
                        $mhspecific->addAttribute("pMdiseaseCode", "");
                        $mhspecific->addAttribute("pSpecificDesc", "");
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks", "");
                        break;
                    }
                }
            } else{
                $mhspecifics = $profile->addChild("MHSPECIFICS");
                $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                $mhspecific->addAttribute("pMdiseaseCode", "");
                $mhspecific->addAttribute("pSpecificDesc", "");
                $mhspecific->addAttribute("pReportStatus", "U");
                $mhspecific->addAttribute("pDeficiencyRemarks", "");
            }

            if(count($genProfilingSurghist) > 0) {
                $surghists = $profile->addChild("SURGHISTS");
                foreach ($genProfilingSurghist as $genProfilingSurghists) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingSurghists['TRANS_NO']) {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", $genProfilingSurghists['SURG_DESC']);
                        $surghist->addAttribute("pSurgDate", $genProfilingSurghists['SURG_DATE']);
                        $surghist->addAttribute("pReportStatus","U");
                        $surghist->addAttribute("pDeficiencyRemarks", $genProfilingSurghists['DEFICIENCY_REMARKS']);
                    } 
                }

                foreach ($genProfilingSurghist as $genProfilingSurghists) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingSurghists['TRANS_NO']) {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", "");
                        $surghist->addAttribute("pSurgDate", "");
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", "");
                        break;
                    } 
                }
            } else{                
                $surghists = $profile->addChild("SURGHISTS");
                $surghist = $surghists->addChild("SURGHIST");
                $surghist->addAttribute("pSurgDesc", "");
                $surghist->addAttribute("pSurgDate", "");
                $surghist->addAttribute("pReportStatus", "U");
                $surghist->addAttribute("pDeficiencyRemarks", "");
            }

            if(count($genProfilingFamhist) > 0) {
                $famhists = $profile->addChild("FAMHISTS");
                foreach ($genProfilingFamhist as $genProfilingFamhists) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingFamhists['TRANS_NO']) {
                        $famhist = $famhists->addChild("FAMHIST");
                        if ($genProfilingFamhists['MDISEASE_CODE'] == null || $genProfilingFamhists['MDISEASE_CODE'] == "") {
                            $famhist->addAttribute("pMdiseaseCode", "999");
                        } else {
                             $famhist->addAttribute("pMdiseaseCode", $genProfilingFamhists['MDISEASE_CODE']);
                        }
                       
                        $famhist->addAttribute("pReportStatus", "U");
                        $famhist->addAttribute("pDeficiencyRemarks", "");
                    } 
                }

                foreach ($genProfilingFamhist as $genProfilingFamhists) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingFamhists['TRANS_NO']) {
                        $famhist = $famhists->addChild("FAMHIST");
                        $famhist->addAttribute("pMdiseaseCode", "999");
                        $famhist->addAttribute("pReportStatus", "U");
                        $famhist->addAttribute("pDeficiencyRemarks", "");
                        break;
                    } 
                }
            } else{
                $famhists = $profile->addChild("FAMHISTS");
                $famhist = $famhists->addChild("FAMHIST");
                $famhist->addAttribute("pMdiseaseCode", "999");
                $famhist->addAttribute("pReportStatus", "U");
                $famhist->addAttribute("pDeficiencyRemarks", "");
            }

            if(count($genProfilingFhspecific) > 0) {
                        $fhspecifics = $profile->addChild("FHSPECIFICS");
                foreach ($genProfilingFhspecific as $genProfilingFhspecifics) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingFhspecifics['TRANS_NO']) {
                        $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                        $fhspecific->addAttribute("pMdiseaseCode", $genProfilingFhspecifics['MDISEASE_CODE']);
                        $fhspecific->addAttribute("pSpecificDesc", $genProfilingFhspecifics['SPECIFIC_DESC']);
                        $fhspecific->addAttribute("pReportStatus", "U");
                        $fhspecific->addAttribute("pDeficiencyRemarks", $genProfilingFhspecifics['DEFICIENCY_REMARKS']);
                    }
                }

                foreach ($genProfilingFhspecific as $genProfilingFhspecifics) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingFhspecifics['TRANS_NO']) {
                        $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                        $fhspecific->addAttribute("pMdiseaseCode", "");
                        $fhspecific->addAttribute("pSpecificDesc", "");
                        $fhspecific->addAttribute("pReportStatus", "U");
                        $fhspecific->addAttribute("pDeficiencyRemarks", "");
                        break;
                    }
                }
            } else{
                $fhspecifics = $profile->addChild("FHSPECIFICS");
                $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                $fhspecific->addAttribute("pMdiseaseCode", "");
                $fhspecific->addAttribute("pSpecificDesc", "");
                $fhspecific->addAttribute("pReportStatus", "U");
                $fhspecific->addAttribute("pDeficiencyRemarks", "");
            }

            $sochist = $profile->addChild("SOCHIST");
            $sochist->addAttribute("pIsSmoker", $genResultProfilings['IS_SMOKER']);
            $sochist->addAttribute("pNoCigpk", $genResultProfilings['NO_CIGPK']);
            $sochist->addAttribute("pIsAdrinker", $genResultProfilings['IS_ADRINKER']);
            $sochist->addAttribute("pNoBottles", $genResultProfilings['NO_BOTTLES']);
            $sochist->addAttribute("pIllDrugUser", $genResultProfilings['ILL_DRUG_USER']);
            $sochist->addAttribute("pIsSexuallyActive", $genResultProfilings['IS_SEXUALLY_ACTIVE']);
            $sochist->addAttribute("pReportStatus", "U");
            $sochist->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            if(count($genProfilingImmunization) > 0) {
                $immunizations = $profile->addChild("IMMUNIZATIONS");
                foreach ($genProfilingImmunization as $genProfilingImmunizations) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingImmunizations['TRANS_NO']) {
                        $immunization = $immunizations->addChild("IMMUNIZATION");

                        if ($genProfilingImmunizations['CHILD_IMMCODE'] == null || $genProfilingImmunizations['CHILD_IMMCODE'] == "") {
                            $immunization->addAttribute("pChildImmcode", "");
                        } else {
                            $immunization->addAttribute("pChildImmcode", $genProfilingImmunizations['CHILD_IMMCODE']);
                        }

                        if ($genProfilingImmunizations['YOUNGW_IMMCODE'] == null || $genProfilingImmunizations['YOUNGW_IMMCODE'] == "") {
                            $immunization->addAttribute("pYoungwImmcode", "");
                        } else {
                            $immunization->addAttribute("pYoungwImmcode", $genProfilingImmunizations['YOUNGW_IMMCODE']);
                        }

                        if ($genProfilingImmunizations['PREGW_IMMCODE'] == null || $genProfilingImmunizations['PREGW_IMMCODE'] == "") {
                            $immunization->addAttribute("pPregwImmcode", "");
                        } else {
                            $immunization->addAttribute("pPregwImmcode", $genProfilingImmunizations['PREGW_IMMCODE']);
                        }

                        if ($genProfilingImmunizations['ELDERLY_IMMCODE'] == null || $genProfilingImmunizations['ELDERLY_IMMCODE'] == "") {
                            $immunization->addAttribute("pElderlyImmcode", "");
                        } else {
                            $immunization->addAttribute("pElderlyImmcode", $genProfilingImmunizations['ELDERLY_IMMCODE']);
                        }
                        
                        
                        $immunization->addAttribute("pOtherImm", $genProfilingImmunizations['OTHER_IMM']);
                        $immunization->addAttribute("pReportStatus", "U");
                        $immunization->addAttribute("pDeficiencyRemarks", $genProfilingImmunizations['DEFICIENCY_REMARKS']);
                    } 
                }

                foreach ($genProfilingImmunization as $genProfilingImmunizations) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingImmunizations['TRANS_NO']) {
                        $immunization = $immunizations->addChild("IMMUNIZATION");
                        $immunization->addAttribute("pChildImmcode", "999");
                        $immunization->addAttribute("pYoungwImmcode", "999");
                        $immunization->addAttribute("pPregwImmcode", "999");
                        $immunization->addAttribute("pElderlyImmcode", "999");
                        $immunization->addAttribute("pOtherImm", "");
                        $immunization->addAttribute("pReportStatus", "U");
                        $immunization->addAttribute("pDeficiencyRemarks", "");
                        break;
                    }
                }
            } else{
                $immunizations = $profile->addChild("IMMUNIZATIONS");
                $immunization = $immunizations->addChild("IMMUNIZATION");
                $immunization->addAttribute("pChildImmcode", "");
                $immunization->addAttribute("pYoungwImmcode", "");
                $immunization->addAttribute("pPregwImmcode", "");
                $immunization->addAttribute("pElderlyImmcode", "");
                $immunization->addAttribute("pOtherImm", "");
                $immunization->addAttribute("pReportStatus", "U");
                $immunization->addAttribute("pDeficiencyRemarks", "");
            }

            $menshist = $profile->addChild("MENSHIST");
            $menshist->addAttribute("pMenarchePeriod", $genResultProfilings['MENARCHE_PERIOD']);
            $menshist->addAttribute("pLastMensPeriod", $genResultProfilings['LAST_MENS_PERIOD']);
            $menshist->addAttribute("pPeriodDuration", $genResultProfilings['PERIOD_DURATION']);
            $menshist->addAttribute("pMensInterval", $genResultProfilings['MENS_INTERVAL']);
            $menshist->addAttribute("pPadsPerDay", $genResultProfilings['PADS_PER_DAY']);
            $menshist->addAttribute("pOnsetSexIc", $genResultProfilings['ONSET_SEX_IC']);
            $menshist->addAttribute("pBirthCtrlMethod", $genResultProfilings['BIRTH_CTRL_METHOD']);
            $menshist->addAttribute("pIsMenopause", $genResultProfilings['IS_MENOPAUSE']);
            $menshist->addAttribute("pMenopauseAge", $genResultProfilings['MENOPAUSE_AGE']);
            $menshist->addAttribute("pIsApplicable", $genResultProfilings['MENS_IS_APPLICABLE']);
            $menshist->addAttribute("pReportStatus", "U");
            $menshist->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $preghist = $profile->addChild("PREGHIST");
            $preghist->addAttribute("pPregCnt", $genResultProfilings['PREG_CNT']);
            $preghist->addAttribute("pDeliveryCnt", $genResultProfilings['DELIVERY_CNT']);
            $preghist->addAttribute("pDeliveryTyp", $genResultProfilings['DELIVERY_TYP']);
            $preghist->addAttribute("pFullTermCnt", $genResultProfilings['FULL_TERM_CNT']);
            $preghist->addAttribute("pPrematureCnt", $genResultProfilings['PREMATURE_CNT']);
            $preghist->addAttribute("pAbortionCnt", $genResultProfilings['ABORTION_CNT']);
            $preghist->addAttribute("pLivChildrenCnt", $genResultProfilings['LIV_CHILDREN_CNT']);
            $preghist->addAttribute("pWPregIndhyp", $genResultProfilings['W_PREG_INDHYP']);
            $preghist->addAttribute("pWFamPlan", $genResultProfilings['W_FAM_PLAN']);
            if ($genResultProfilings['PREG_IS_APPLICABLE'] == null || ($genResultProfilings['PREG_CNT'] == 0)) {
                $preghist->addAttribute("pIsApplicable", "N");
            } else {
                $preghist->addAttribute("pIsApplicable", $genResultProfilings['PREG_IS_APPLICABLE']);
            }
           
            $preghist->addAttribute("pReportStatus", "U");
            $preghist->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $pepert = $profile->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $genResultProfilings['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $genResultProfilings['DIASTOLIC']);
            $pepert->addAttribute("pHr", $genResultProfilings['HR']);
            $pepert->addAttribute("pRr", $genResultProfilings['RR']);
            $pepert->addAttribute("pTemp", $genResultProfilings['TEMPERATURE']);
            if ($genResultProfilings['HEIGHT'] != null) {
                $vHeight = $genResultProfilings['HEIGHT'];
            } else {
                $vHeight = 0;
            }
            $pepert->addAttribute("pHeight", $vHeight);
            $pepert->addAttribute("pWeight", $genResultProfilings['WEIGHT']);
            $pepert->addAttribute("pBMI", $genResultProfilings['BMI']);
            $pepert->addAttribute("pZScore", utf8_encode($genResultProfilings['Z_SCORE']));
            $pepert->addAttribute("pLeftVision", $genResultProfilings['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $genResultProfilings['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $genResultProfilings['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $genResultProfilings['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $genResultProfilings['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $genResultProfilings['WAIST']);
            $pepert->addAttribute("pHip", $genResultProfilings['HIP']);
            $pepert->addAttribute("pLimbs", $genResultProfilings['LIMBS']);
            if ($genResultProfilings['MID_UPPER_ARM'] != null) {
                $vMidUpperArmCirc = $genResultProfilings['MID_UPPER_ARM'];
            } else {
                $vMidUpperArmCirc = 0;
            }
            $pepert->addAttribute("pMidUpperArmCirc", $vMidUpperArmCirc);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", "");


            $bloodtype = $profile->addChild("BLOODTYPE");
            $bloodtype->addAttribute("pBloodType", utf8_encode($genResultProfilings['blood_type']));
            $bloodtype->addAttribute("pReportStatus", "U");
            $bloodtype->addAttribute("pDeficiencyRemarks", "");

            $peadmin = $profile->addChild("PEGENSURVEY");
            $peadmin->addAttribute("pGenSurveyId", $genResultProfilings['GENSURVEY_ID']);
            $peadmin->addAttribute("pGenSurveyRem", $genResultProfilings['GENSURVEY_REM']);
            $peadmin->addAttribute("pReportStatus", "U");
            $peadmin->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            if($genProfilingPemisc != NULL) {
                $pemiscs = $profile->addChild("PEMISCS");
                foreach ($genProfilingPemisc as $genProfilingPemiscs) {
                    if ($genResultProfilings['TRANS_NO'] == $genProfilingPemiscs['TRANS_NO']) {
                        $pemisc = $pemiscs->addChild("PEMISC");
                        $pemisc->addAttribute("pSkinId", $genProfilingPemiscs['SKIN_ID']);
                        $pemisc->addAttribute("pHeentId", $genProfilingPemiscs['HEENT_ID']);
                        $pemisc->addAttribute("pChestId", $genProfilingPemiscs['CHEST_ID']);
                        $pemisc->addAttribute("pHeartId", $genProfilingPemiscs['HEART_ID']);
                        $pemisc->addAttribute("pAbdomenId", $genProfilingPemiscs['ABDOMEN_ID']);
                        $pemisc->addAttribute("pNeuroId", $genProfilingPemiscs['NEURO_ID']);
                        $pemisc->addAttribute("pRectalId", $genProfilingPemiscs['RECTAL_ID']);
                        $pemisc->addAttribute("pGuId", $genProfilingPemiscs['GU_ID']);
                        $pemisc->addAttribute("pReportStatus", "U");
                        $pemisc->addAttribute("pDeficiencyRemarks", $genProfilingPemiscs['DEFICIENCY_REMARKS']);
                    }
                }

                foreach ($genProfilingPemisc as $genProfilingPemiscs) {
                    if ($genResultProfilings['TRANS_NO'] != $genProfilingPemiscs['TRANS_NO']) {
                        $pemisc = $pemiscs->addChild("PEMISC");
                        $pemisc->addAttribute("pSkinId", "");
                        $pemisc->addAttribute("pHeentId", "");
                        $pemisc->addAttribute("pChestId", "");
                        $pemisc->addAttribute("pHeartId", "");
                        $pemisc->addAttribute("pAbdomenId", "");
                        $pemisc->addAttribute("pNeuroId", "");
                        $pemisc->addAttribute("pRectalId", "");
                        $pemisc->addAttribute("pGuId", "");
                        $pemisc->addAttribute("pReportStatus", "U");
                        $pemisc->addAttribute("pDeficiencyRemarks", "");
                        break;
                    }
                }
            } else{
                $pemiscs = $profile->addChild("PEMISCS");
                $pemisc = $pemiscs->addChild("PEMISC");
                $pemisc->addAttribute("pSkinId", "");
                $pemisc->addAttribute("pHeentId", "");
                $pemisc->addAttribute("pChestId", "");
                $pemisc->addAttribute("pHeartId", "");
                $pemisc->addAttribute("pAbdomenId", "");
                $pemisc->addAttribute("pNeuroId", "");
                $pemisc->addAttribute("pRectalId", "");
                $pemisc->addAttribute("pGuId", "");
                $pemisc->addAttribute("pReportStatus", "U");
                $pemisc->addAttribute("pDeficiencyRemarks", "");
            }

            $pespecific = $profile->addChild("PESPECIFIC");
            $pespecific->addAttribute("pSkinRem", $genResultProfilings['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $genResultProfilings['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $genResultProfilings['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $genResultProfilings['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $genResultProfilings['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $genResultProfilings['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $genResultProfilings['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $genResultProfilings['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);

            $ncdqans = $profile->addChild("NCDQANS");
            $ncdqans->addAttribute("pQid1_Yn", $genResultProfilings['QID1_YN']);
            $ncdqans->addAttribute("pQid2_Yn", $genResultProfilings['QID2_YN']);
            $ncdqans->addAttribute("pQid3_Yn", $genResultProfilings['QID3_YN']);
            $ncdqans->addAttribute("pQid4_Yn", $genResultProfilings['QID4_YN']);
            $ncdqans->addAttribute("pQid5_Ynx", $genResultProfilings['QID5_YNX']);
            $ncdqans->addAttribute("pQid6_Yn", $genResultProfilings['QID6_YN']);
            $ncdqans->addAttribute("pQid7_Yn", $genResultProfilings['QID7_YN']);
            $ncdqans->addAttribute("pQid8_Yn", $genResultProfilings['QID8_YN']);
            $ncdqans->addAttribute("pQid9_Yn", $genResultProfilings['QID9_YN']);
            $ncdqans->addAttribute("pQid10_Yn", $genResultProfilings['QID10_YN']);
            $ncdqans->addAttribute("pQid11_Yn", $genResultProfilings['QID11_YN']);
            $ncdqans->addAttribute("pQid12_Yn", $genResultProfilings['QID12_YN']);
            $ncdqans->addAttribute("pQid13_Yn", $genResultProfilings['QID13_YN']);
            $ncdqans->addAttribute("pQid14_Yn", $genResultProfilings['QID14_YN']);
            $ncdqans->addAttribute("pQid15_Yn", $genResultProfilings['QID15_YN']);
            $ncdqans->addAttribute("pQid16_Yn", $genResultProfilings['QID16_YN']);
            $ncdqans->addAttribute("pQid17_Abcde", $genResultProfilings['QID17_ABCDE']);
            $ncdqans->addAttribute("pQid18_Yn", $genResultProfilings['QID18_YN']);
            $ncdqans->addAttribute("pQid19_Yn", $genResultProfilings['QID19_YN']);
            $ncdqans->addAttribute("pQid19_Fbsmg", $genResultProfilings['QID19_FBSMG']);
            $ncdqans->addAttribute("pQid19_Fbsmmol", $genResultProfilings['QID19_FBSMMOL']);
            $ncdqans->addAttribute("pQid19_Fbsdate", $genResultProfilings['QID19_FBSDATE']);
            $ncdqans->addAttribute("pQid20_Yn", $genResultProfilings['QID20_YN']);
            $ncdqans->addAttribute("pQid20_Choleval", $genResultProfilings['QID20_CHOLEVAL']);
            $ncdqans->addAttribute("pQid20_Choledate", $genResultProfilings['QID20_CHOLEDATE']);
            $ncdqans->addAttribute("pQid21_Yn", $genResultProfilings['QID21_YN']);
            $ncdqans->addAttribute("pQid21_Ketonval", $genResultProfilings['QID21_KETONVAL']);
            $ncdqans->addAttribute("pQid21_Ketondate", $genResultProfilings['QID21_KETONDATE']);
            $ncdqans->addAttribute("pQid22_Yn", $genResultProfilings['QID22_YN']);
            $ncdqans->addAttribute("pQid22_Proteinval", $genResultProfilings['QID22_PROTEINVAL']);
            $ncdqans->addAttribute("pQid22_Proteindate", $genResultProfilings['QID22_PROTEINDATE']);
            $ncdqans->addAttribute("pQid23_Yn", $genResultProfilings['QID23_YN']);
            $ncdqans->addAttribute("pQid24_Yn", $genResultProfilings['QID24_YN']);
            $ncdqans->addAttribute("pReportStatus", "U");
            $ncdqans->addAttribute("pDeficiencyRemarks", $genResultProfilings['DEFICIENCY_REMARKS']);
        }


    /*CONSULTATION XML GENERATION*/
    $consultations = $konsulta->addChild("SOAPS");

    // if($genResultConsultation == NULL) {
        $consultation = $consultations->addChild("SOAP");
        $consultation->addAttribute("pHciCaseNo", "");
        $consultation->addAttribute("pHciTransNo", "");
        $consultation->addAttribute("pSoapDate", "");
        $consultation->addAttribute("pPatientPin", "");
        $consultation->addAttribute("pPatientType", "");
        $consultation->addAttribute("pMemPin", "");
        $consultation->addAttribute("pEffYear", "");
        $consultation->addAttribute("pATC", "");
        $consultation->addAttribute("pIsWalkedIn", "");
        $consultation->addAttribute("pCoPay", "");
        $consultation->addAttribute("pTransDate", "");
        $consultation->addAttribute("pReportStatus", "U");
        $consultation->addAttribute("pDeficiencyRemarks", "");

        $subjective = $consultation->addChild("SUBJECTIVE");
        $subjective->addAttribute("pIllnessHistory", "");
        $subjective->addAttribute("pSignsSymptoms", "");
        $subjective->addAttribute("pOtherComplaint", "");
        $subjective->addAttribute("pPainSite", "");
        $subjective->addAttribute("pReportStatus", "U");
        $subjective->addAttribute("pDeficiencyRemarks", "");

        $pepert = $consultation->addChild("PEPERT");
        $pepert->addAttribute("pSystolic", "");
        $pepert->addAttribute("pDiastolic", "");
        $pepert->addAttribute("pHr", "");
        $pepert->addAttribute("pRr", "");
        $pepert->addAttribute("pTemp", "");
        $pepert->addAttribute("pHeight", "");
        $pepert->addAttribute("pWeight", "");
        $pepert->addAttribute("pBMI", "");
        $pepert->addAttribute("pZScore", "");
        $pepert->addAttribute("pLeftVision", "");
        $pepert->addAttribute("pRightVision", "");
        $pepert->addAttribute("pLength", "");
        $pepert->addAttribute("pHeadCirc", "");
        $pepert->addAttribute("pSkinfoldThickness", "");
        $pepert->addAttribute("pWaist", "");
        $pepert->addAttribute("pHip", "");
        $pepert->addAttribute("pLimbs", "");
        $pepert->addAttribute("pMidUpperArmCirc", "");
        $pepert->addAttribute("pReportStatus", "U");
        $pepert->addAttribute("pDeficiencyRemarks", "");

        $pemiscs = $consultation->addChild("PEMISCS");
        $pemisc = $pemiscs->addChild("PEMISC");
        $pemisc->addAttribute("pSkinId","");
        $pemisc->addAttribute("pHeentId", "");
        $pemisc->addAttribute("pChestId", "");
        $pemisc->addAttribute("pHeartId", "");
        $pemisc->addAttribute("pAbdomenId", "");
        $pemisc->addAttribute("pNeuroId", "");
        $pemisc->addAttribute("pGuId", "");
        $pemisc->addAttribute("pRectalId", "");
        $pemisc->addAttribute("pReportStatus", "U");
        $pemisc->addAttribute("pDeficiencyRemarks", "");

        $pespecific = $consultation->addChild("PESPECIFIC");
        $pespecific->addAttribute("pSkinRem", "");
        $pespecific->addAttribute("pHeentRem", "");
        $pespecific->addAttribute("pChestRem","");
        $pespecific->addAttribute("pHeartRem", "");
        $pespecific->addAttribute("pAbdomenRem", "");
        $pespecific->addAttribute("pNeuroRem","");
        $pespecific->addAttribute("pRectalRem", "");
        $pespecific->addAttribute("pGuRem", "");
        $pespecific->addAttribute("pReportStatus", "U");
        $pespecific->addAttribute("pDeficiencyRemarks", "");

        $icds = $consultation->addChild("ICDS");
        $icd = $icds->addChild("ICD");
        $icd->addAttribute("pIcdCode", "");
        $icd->addAttribute("pReportStatus", "U");
        $icd->addAttribute("pDeficiencyRemarks", "");

        $diagnostics = $consultation->addChild("DIAGNOSTICS");
        $diagnostic = $diagnostics->addChild("DIAGNOSTIC");
        $diagnostic->addAttribute("pDiagnosticId", "");
        $diagnostic->addAttribute("pOthRemarks", "");
        $diagnostic->addAttribute("pIsPhysicianRecommendation", "");
        $diagnostic->addAttribute("pPatientRemarks", "");
        $diagnostic->addAttribute("pReportStatus", "U");
        $diagnostic->addAttribute("pDeficiencyRemarks", "");

        $managements = $consultation->addChild("MANAGEMENTS");
        $management = $managements->addChild("MANAGEMENT");
        $management->addAttribute("pManagementId", "");
        $management->addAttribute("pOthRemarks", "");
        $management->addAttribute("pReportStatus", "U");
        $management->addAttribute("pDeficiencyRemarks", "");

        $advice = $consultation->addChild("ADVICE");
        $advice->addAttribute("pRemarks", "");
        $advice->addAttribute("pReportStatus", "U");
        $advice->addAttribute("pDeficiencyRemarks", "");

    // } 

    /*LABORATORY RESULTS XML GENERATION*/
    if(count($genResultLabsFbs) > 0 || count($genResultLabsRbs) > 0){
        $labresults = $konsulta->addChild("DIAGNOSTICEXAMRESULTS");
        foreach ($genResultLabs as $genResultLab) {
           
                $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
                $labresult->addAttribute("pHciCaseNo", $genResultLab['CASE_NO']);
                $labresult->addAttribute("pHciTransNo", $genResultLab['TRANS_NO']);
                $labresult->addAttribute("pPatientPin", $genResultLab['PX_PIN']);
                $labresult->addAttribute("pPatientType", $genResultLab['PX_TYPE']);
                $labresult->addAttribute("pMemPin", $genResultLab['MEM_PIN']);
                $labresult->addAttribute("pEffYear", $genResultLab['EFF_YEAR']);


                if(count($genResultLabsFbs) > 0) {
                        // $fbss = $labresult->addChild("FBSS"); 
                    foreach ($genResultLabsFbs as $genResultFbs) {
                        if ($genResultLab['CASE_NO'] == $genResultFbs['CASE_NO']) {
                            if ($genResultFbs['LAB_DATE'] != null || $genResultFbs['LAB_DATE'] != "") {
                                $fbss = $labresult->addChild("FBSS"); 
                                $fbs = $fbss->addChild("FBS");
                                $fbs->addAttribute("pReferralFacility", $genResultFbs['REFERRAL_FACILITY']);
                                $fbs->addAttribute("pLabDate", $genResultFbs['LAB_DATE']);
                                $fbs->addAttribute("pGlucoseMg", $genResultFbs['GLUCOSE_MG']);
                                $fbs->addAttribute("pGlucoseMmol", $genResultFbs['GLUCOSE_MMOL']);
                                $fbs->addAttribute("pDateAdded", $genResultFbs['DATE_ADDED']);
                                $fbs->addAttribute("pStatus", $genResultFbs['IS_APPLICABLE']);
                                $fbs->addAttribute("pDiagnosticLabFee", $genResultFbs['DIAGNOSTIC_FEE']);
                                $fbs->addAttribute("pReportStatus", "U");
                                $fbs->addAttribute("pDeficiencyRemarks", $genResultFbs['DEFICIENCY_REMARKS']);
                            }
                            
                        } 

                    }
                }

                if(count($genResultLabsRbs) > 0) {
                        // $rbss = $labresult->addChild("RBSS");
                    foreach ($genResultLabsRbs as $genResultRbs) {
                        if ($genResultLab['CASE_NO'] == $genResultRbs['CASE_NO']) {
                            if ($genResultRbs['LAB_DATE'] != null || $genResultRbs['LAB_DATE'] != "") {     
                                $rbss = $labresult->addChild("RBSS");                           
                                $rbs = $rbss->addChild("RBS");
                                $rbs->addAttribute("pReferralFacility", $genResultRbs['REFERRAL_FACILITY']);
                                $rbs->addAttribute("pLabDate", $genResultRbs['LAB_DATE']);
                                $rbs->addAttribute("pGlucoseMg", $genResultRbs['GLUCOSE_MG']);
                                $rbs->addAttribute("pGlucoseMmol", $genResultRbs['GLUCOSE_MMOL']);
                                $rbs->addAttribute("pDateAdded", $genResultRbs['DATE_ADDED']);
                                $rbs->addAttribute("pStatus", $genResultRbs['IS_APPLICABLE']);
                                $rbs->addAttribute("pDiagnosticLabFee", $genResultRbs['DIAGNOSTIC_FEE']);
                                $rbs->addAttribute("pReportStatus", "U");
                                $rbs->addAttribute("pDeficiencyRemarks", $genResultRbs['DEFICIENCY_REMARKS']);
                            }
                        } 
                    }
                }
            
        } 
    }


    /*MEDICINE XML GENERATION*/
    $medicines = $konsulta->addChild("MEDICINES");
    // if($genResultMedicine == NULL) {
        $meds = $medicines->addChild("MEDICINE");
        $meds->addAttribute("pHciCaseNo", "");
        $meds->addAttribute("pHciTransNo", "");
        $meds->addAttribute("pCategory", "");
        $meds->addAttribute("pDrugCode", "");
        $meds->addAttribute("pGenericCode", "");
        $meds->addAttribute("pSaltCode", "");
        $meds->addAttribute("pStrengthCode", "");
        $meds->addAttribute("pFormCode", "");
        $meds->addAttribute("pUnitCode", "");
        $meds->addAttribute("pPackageCode","");
        $meds->addAttribute("pOtherMedicine", "");
        $meds->addAttribute("pRoute","");
        $meds->addAttribute("pQuantity", "");
        $meds->addAttribute("pActualUnitPrice", "");
        $meds->addAttribute("pTotalAmtPrice", "");
        $meds->addAttribute("pInstructionQuantity", "");
        $meds->addAttribute("pInstructionStrength", "");
        $meds->addAttribute("pInstructionFrequency", "");
        $meds->addAttribute("pPrescribingPhysician", "");
        $meds->addAttribute("pIsDispensed", "");
        $meds->addAttribute("pDateDispensed", "");
        $meds->addAttribute("pDispensingPersonnel", "");
        $meds->addAttribute("pIsApplicable", "");
        $meds->addAttribute("pDateAdded", "");
        $meds->addAttribute("pReportStatus", "U");
        $meds->addAttribute("pDeficiencyRemarks", "");
    // } 

    $dom = dom_import_simplexml($konsulta)->ownerDocument;
    $dom ->formatOutput = true;

    $xml = $dom->saveXML();
    $xmlString = str_replace("<?xml version=\"1.0\"?>\n", '', $xml);
    file_put_contents("tmp/konsulta_raw_xml_grp_fpe.xml", $xmlString);


    echo $xml;

    return $xmlString;
}

?>


