<?php
error_reporting(0);
date_default_timezone_set("Asia/Manila");
ini_set('max_execution_time', '0');

/* Retrive FPE */
function getPrevPxRecordEnlist($pCaseNo){
    $ini = parse_ini_file("config.ini");
 
     try {
         $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
         $stmt = $conn->prepare("SELECT * 
                                 FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST  
                                 WHERE CASE_NO = :caseno");
 
         $stmt->bindParam(':caseno', $pCaseNo);
 
         $stmt->execute();
 
         $result = $stmt->fetch(PDO::FETCH_ASSOC);
 
         return $result;
     }
     catch(PDOException $e)
     {
         echo "Error: " . $e->getMessage();
     }
 
     $conn = null;
 }

 function getCarryOverConsultation($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
                SELECT *
                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP
                WHERE CASE_NO = :caseno                                   
                ORDER BY SOAP_DATE ASC
        ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


 /*InsertSelect Patient Information*/
function saveCarryoverProfilingInfo($pCurrentCaseNo, $pPrevFPECaseNo, $pEffYear){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $vTransNo = generateTransNo('PROF_NO'); //automatically generated
        
        //insertProfilingInfo
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROFILE (
                CASE_NO, TRANS_NO, HCI_NO, PX_PIN, PX_TYPE, MEM_PIN, PROF_DATE, PROF_BY, EFF_YEAR, DATE_ADDED, 
                PROFILE_OTP, IS_FINALIZE, XPS_MODULE, WITH_ATC
            ) 
            SELECT 
                :currentCaseNo, :transNo, HCI_NO, PX_PIN, PX_TYPE, MEM_PIN, PROF_DATE, PROF_BY, :effYear, 
                DATE_ADDED, PROFILE_OTP, IS_FINALIZE, XPS_MODULE, WITH_ATC
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':currentCaseNo', $pCurrentCaseNo);
        $stmt->bindParam(':effYear', $pEffYear);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertProfilingOtherInfo
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_OINFO (
                PX_AGE,PX_OCCUPATION,PX_EDUCATION,DATE_ADDED,ADDED_BY,TRANS_NO,PX_POB,PX_RELIGION,PX_MOTHER_MNLN,
                PX_MOTHER_MNMI,PX_MOTHER_FN,PX_MOTHER_EXTN,PX_FATHER_LN,PX_FATHER_MI,PX_FATHER_FN,PX_FATHER_EXTN,
                PX_MOTHER_BDAY,PX_FATHER_BDAY, UPD_CNT
            ) 
            SELECT PX_AGE,PX_OCCUPATION,PX_EDUCATION,b.DATE_ADDED,ADDED_BY,:transNo,PX_POB,PX_RELIGION,PX_MOTHER_MNLN,PX_MOTHER_MNMI,PX_MOTHER_FN,PX_MOTHER_EXTN, PX_FATHER_LN,PX_FATHER_MI,PX_FATHER_FN,PX_FATHER_EXTN,PX_MOTHER_BDAY,PX_FATHER_BDAY, b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_OINFO b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertPastMedicalHistory
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST (
                MDISEASE_CODE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT
            ) 
            SELECT 
                MDISEASE_CODE,b.DATE_ADDED,ADDED_BY,:transNo,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertPastMedicalHistorySpecific
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC (
                MDISEASE_CODE,SPECIFIC_DESC,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT
            ) 
            SELECT 
                MDISEASE_CODE,SPECIFIC_DESC,b.DATE_ADDED,ADDED_BY,:transNo,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertPastSurgicalHistory
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST (
                SURG_DESC,SURG_DATE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT
            ) 
            SELECT 
                SURG_DESC,SURG_DATE,b.DATE_ADDED,ADDED_BY,:transNo,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertFamilyMedicalHistory
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST (
                MDISEASE_CODE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT
            ) 
            SELECT 
                MDISEASE_CODE,b.DATE_ADDED,ADDED_BY,:transNo,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertFamilyMedicalHistorySpecific
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC (
                MDISEASE_CODE,SPECIFIC_DESC,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT
            ) 
            SELECT 
                MDISEASE_CODE,SPECIFIC_DESC,b.DATE_ADDED,ADDED_BY,:transNo,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertResultsFBS
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS (
               CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, GLUCOSE_MG, GLUCOSE_MMOL, DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE
            ) 
            SELECT 
                :currentCaseNo,:transNo, LAB_DATE, REFERRAL_FACILITY, GLUCOSE_MG, GLUCOSE_MMOL, b.DATE_ADDED, ADDED_BY, 
                b.UPD_CNT, b.XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS b ON a.TRANS_NO = b.TRANS_NO
            WHERE a.CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->bindParam(':currentCaseNo', $pCurrentCaseNo);
        $stmt->execute();

        //insertResultsRBS
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS (
               CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, GLUCOSE_MG, GLUCOSE_MMOL, DATE_ADDED, ADDED_BY, UPD_CNT, 
               XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE
            ) 
            SELECT 
                :currentCaseNo,:transNo, LAB_DATE, REFERRAL_FACILITY, GLUCOSE_MG, GLUCOSE_MMOL, b.DATE_ADDED, ADDED_BY, 
                b.UPD_CNT, b.XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS b ON a.TRANS_NO = b.TRANS_NO
            WHERE a.CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->bindParam(':currentCaseNo', $pCurrentCaseNo);
        $stmt->execute();

        //insertPersonalSocialHistory
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST (
                IS_SMOKER, NO_CIGPK, IS_ADRINKER, NO_BOTTLES, ILL_DRUG_USER, DATE_ADDED, ADDED_BY, TRANS_NO, UPD_CNT, 
                IS_SEXUALLY_ACTIVE
            )
            SELECT 
                IS_SMOKER, NO_CIGPK, IS_ADRINKER, NO_BOTTLES, ILL_DRUG_USER, b.DATE_ADDED, ADDED_BY, :transNo, b.UPD_CNT, 
                IS_SEXUALLY_ACTIVE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertImmunizations
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION (
                CHILD_IMMCODE,YOUNGW_IMMCODE,PREGW_IMMCODE,ELDERLY_IMMCODE,DATE_ADDED,ADDED_BY,TRANS_NO,OTHER_IMM,UPD_CNT
                )
            SELECT 
                CHILD_IMMCODE,YOUNGW_IMMCODE,PREGW_IMMCODE,ELDERLY_IMMCODE,b.DATE_ADDED,ADDED_BY,:transNo,OTHER_IMM,
                b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertMenstrualHistory
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MENSHIST (
                MENARCHE_PERIOD,LAST_MENS_PERIOD,PERIOD_DURATION,MENS_INTERVAL,PADS_PER_DAY,ONSET_SEX_IC,
                BIRTH_CTRL_METHOD,IS_MENOPAUSE,MENOPAUSE_AGE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT,IS_APPLICABLE
            )
            SELECT 
                MENARCHE_PERIOD,LAST_MENS_PERIOD,PERIOD_DURATION,MENS_INTERVAL,PADS_PER_DAY,ONSET_SEX_IC,
                BIRTH_CTRL_METHOD,IS_MENOPAUSE,MENOPAUSE_AGE,b.DATE_ADDED,ADDED_BY,:transNo,b.UPD_CNT,IS_APPLICABLE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MENSHIST b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertPrenancyHistory
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PREGHIST (
                PREG_CNT,DELIVERY_CNT,DELIVERY_TYP,FULL_TERM_CNT,PREMATURE_CNT,ABORTION_CNT,LIV_CHILDREN_CNT,
                W_PREG_INDHYP,DATE_ADDED,ADDED_BY,TRANS_NO,W_FAM_PLAN,UPD_CNT,IS_APPLICABLE
            )
            SELECT 
                PREG_CNT,DELIVERY_CNT,DELIVERY_TYP,FULL_TERM_CNT,PREMATURE_CNT,ABORTION_CNT,LIV_CHILDREN_CNT,
                W_PREG_INDHYP,b.DATE_ADDED,ADDED_BY,:transNo,W_FAM_PLAN,b.UPD_CNT,IS_APPLICABLE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PREGHIST b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertPertinentPhysicalExam
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT (
                SYSTOLIC,DIASTOLIC,HR,RR,HEIGHT,WEIGHT,TEMPERATURE,DATE_ADDED,ADDED_BY,TRANS_NO,VISION,LENGTH,
                HEAD_CIRC,UPD_CNT, LEFT_VISUAL_ACUITY, RIGHT_VISUAL_ACUITY, SKIN_THICKNESS, WAIST, HIP, LIMBS,BMI,
                Z_SCORE,MID_UPPER_ARM
            )
            SELECT 
                SYSTOLIC,DIASTOLIC,HR,RR,HEIGHT,WEIGHT,TEMPERATURE,b.DATE_ADDED,ADDED_BY,:transNo,VISION,LENGTH,
                HEAD_CIRC,b.UPD_CNT, LEFT_VISUAL_ACUITY, RIGHT_VISUAL_ACUITY, SKIN_THICKNESS, WAIST, HIP, LIMBS,BMI,
                Z_SCORE,MID_UPPER_ARM
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertBloodType
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_BLOODTYPE (
                TRANS_NO,BLOOD_TYPE,BLOOD_RH,DATE_ADDED,ADDED_BY,UPD_CNT
            )
            SELECT 
                :transNo,BLOOD_TYPE,BLOOD_RH,b.DATE_ADDED,ADDED_BY,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_BLOODTYPE b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertPeGeneralSurvey
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEGENSURVEY (
                TRANS_NO,GENSURVEY_ID,GENSURVEY_REM,DATE_ADDED,ADDED_BY
            )
            SELECT 
                :transNo,GENSURVEY_ID,GENSURVEY_REM,b.DATE_ADDED,ADDED_BY
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEGENSURVEY b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertProfilePhysicalExamMisc
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC (
                SKIN_ID, HEENT_ID, CHEST_ID, HEART_ID, ABDOMEN_ID, NEURO_ID, GU_ID, RECTAL_ID, TRANS_NO, DATE_ADDED, 
                ADDED_BY, UPD_CNT
            ) 
            SELECT 
                SKIN_ID, HEENT_ID, CHEST_ID, HEART_ID, ABDOMEN_ID, NEURO_ID, GU_ID, RECTAL_ID, :transNo, b.DATE_ADDED, 
                ADDED_BY, b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();
        
        //insertProfilePhysicalExamMiscRemarks
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC (
                SKIN_REM, HEENT_REM, CHEST_REM, HEART_REM, ABDOMEN_REM, TRANS_NO, DATE_ADDED, ADDED_BY, NEURO_REM, GU_REM,
                RECTAL_REM, UPD_CNT
            ) 
            SELECT 
                SKIN_REM, HEENT_REM, CHEST_REM, HEART_REM, ABDOMEN_REM, :transNo, b.DATE_ADDED, ADDED_BY, NEURO_REM, 
                GU_REM,RECTAL_REM, b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        //insertNcdHighRiskAssessment
        $stmt = $conn->prepare("
            INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_NCDQANS (
                QID1_YN,QID2_YN,QID3_YN,QID4_YN,QID5_YNX,QID6_YN,QID7_YN,QID8_YN,QID9_YN,QID10_YN,QID11_YN,QID12_YN,
                QID13_YN,QID14_YN,QID15_YN,QID16_YN,QID17_ABCDE,QID18_YN,QID19_YN,QID19_FBSMG,QID19_FBSMMOL,QID19_FBSDATE,
                QID20_YN,QID20_CHOLEVAL,QID20_CHOLEDATE,QID21_YN,QID21_KETONVAL,QID21_KETONDATE,QID22_YN,QID22_PROTEINVAL,
                QID22_PROTEINDATE,QID23_YN,QID24_YN,TRANS_NO,DATE_ADDED,ADDED_BY,UPD_CNT
                ) 
            SELECT 
                QID1_YN,QID2_YN,QID3_YN,QID4_YN,QID5_YNX,QID6_YN,QID7_YN,QID8_YN,QID9_YN,QID10_YN,QID11_YN,QID12_YN,
                QID13_YN,QID14_YN,QID15_YN,QID16_YN,QID17_ABCDE,QID18_YN,QID19_YN,QID19_FBSMG,QID19_FBSMMOL,QID19_FBSDATE,
                QID20_YN,QID20_CHOLEVAL,QID20_CHOLEDATE,QID21_YN,QID21_KETONVAL,QID21_KETONDATE,QID22_YN,QID22_PROTEINVAL,
                QID22_PROTEINDATE,QID23_YN,QID24_YN,:transNo,b.DATE_ADDED,ADDED_BY,b.UPD_CNT
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_NCDQANS b ON a.TRANS_NO = b.TRANS_NO
            WHERE CASE_NO = :prevCaseNo
        ");
        $stmt->bindParam(':prevCaseNo', $pPrevFPECaseNo);
        $stmt->bindParam(':transNo', $vTransNo);
        $stmt->execute();

        $conn->commit();

        echo '<script>alert("Successfully carry-over FPE to the current year!");window.location="registration_search.php";</script>';

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: InsertSelectCarryOver " . $e->getMessage();
        echo '<script>alert("Error: InsertSelectCarryOver - '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

/*Get Others of Diagnostic*/
function getDiagnosticOthers($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC
                                WHERE TRANS_NO = :pTransNo
                                AND DIAGNOSTIC_ID = '99'");

        $stmt->bindParam(':pTransNo', $transno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Save Laboratory and Imaging Results*/
function saveLaboratoryResults($data){
    $ini = parse_ini_file("config.ini");
    $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $conn->begintransaction();

        session_start();
        $pUserId = $_SESSION['pUserID'];
        $pHciNo = $_SESSION['pHciNum'];
        $pXPSmodule = "SOAP"; /*SOAP - Consultation*/

        /*Start Consultation Patient Details*/
        $pCaseNo=$data['pCaseNo'];
        $pTransNo=$data['pConsultTransNo'];
        $getUpdCntSoap = getUpdCntConsultation($pTransNo);
        $getUpdCnt = $getUpdCntSoap['UPD_CNT'];
        /*End Consultation Patient Details*/

        /*Start Essential Services Laboratory Results*/
        /*Results - Complete Blood Count (CBC)*/
        $pIsApplicableCbc=$data['diagnostic_1_status'];
        $pReferralFacilityCBC=$data['diagnostic_1_accre_diag_fac'];
        if($data['diagnostic_1_lab_exam_date'] != NULL){
            $pLabDate = date('Y-m-d',strtotime($data['diagnostic_1_lab_exam_date']));
        } else{
            $pLabDate = NULL;
        }
        $pLabFeeCBC = $data['diagnostic_1_lab_fee'];
        $pCoPayCBC = NULL;
        $pHematocrit = $data['diagnostic_1_hematocrit'];
        $pHemoglobinG = $data['diagnostic_1_hemoglobin_gdL'];
        $pHemoglobinMmol = $data['diagnostic_1_hemoglobin_mmolL'];
        $pMhcPg = $data['diagnostic_1_mhc_pgcell'];
        $pMhcFmol = $data['diagnostic_1_mhc_fmolcell'];
        $pMchcGhb = $data['diagnostic_1_mchc_gHbdL'];
        $pMchcMmol = $data['diagnostic_1_mchc_mmolHbL'];
        $pMcvUm = $data['diagnostic_1_mcv_um'];
        $pMcvFl = $data['diagnostic_1_mcv_fL'];
        $pWbc1000 = $data['diagnostic_1_wbc_cellsmmuL'];
        $pWbc10 = $data['diagnostic_1_wbc_cellsL'];
        $pMyelocyte = $data['diagnostic_1_myelocyte'];
        $pNeutrophilsBnd = $data['diagnostic_1_neutrophils_bands'];
        $pNeurophilsSeg = $data['diagnostic_1_neutrophils_segmenters'];
        $pLymphocytes = $data['diagnostic_1_lymphocytes'];
        $pMonocytes = $data['diagnostic_1_monocytes'];
        $pEosinophils = $data['diagnostic_1_eosinophils'];
        $pBasophils = $data['diagnostic_1_basophils'];
        $pPlatelet = $data['diagnostic_1_platelet'];
        if ($pIsApplicableCbc != "N") {
            insertResultsCBC($conn, $pCaseNo,$pLabDate,$pLabFeeCBC,$pCoPayCBC, $pReferralFacilityCBC, $pHematocrit, $pHemoglobinG, $pHemoglobinMmol, $pMhcPg, $pMhcFmol, $pMchcGhb, $pMchcMmol, $pMcvUm, $pMcvFl, $pWbc1000, $pWbc10, 
                $pMyelocyte,$pNeutrophilsBnd, $pNeurophilsSeg, $pLymphocytes, $pMonocytes, $pEosinophils, $pBasophils, $pPlatelet, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, $pIsApplicableCbc);
        }
        
        /* Results - Urinalysis */
        $pIsApplicableUrine=$data['diagnostic_2_status'];
        $pReferralFacilityUrinalysis=$data['diagnostic_2_accre_diag_fac'];
        if($data['diagnostic_2_lab_exam_date'] != NULL){
            $pLabDateUrinalysis = date('Y-m-d',strtotime($data['diagnostic_2_lab_exam_date']));
        } else{
            $pLabDateUrinalysis = NULL;
        }
        $pLabFeeUrinalysis = $data['diagnostic_2_lab_fee'];
        $pCoPayUrinalysis = NULL;
        $pGravity = $data['diagnostic_2_sg'];
        $pAppearance = $data['diagnostic_2_appearance'];
        $pColor = $data['diagnostic_2_color'];
        $pGlucose = $data['diagnostic_2_glucose'];
        $pProteins = $data['diagnostic_2_proteins'];
        $pKetones = $data['diagnostic_2_ketones'];
        $pPh = $data['diagnostic_2_pH'];
        $pRbCells = $data['diagnostic_2_rbc'];
        $pWbCells = $data['diagnostic_2_wbc'];
        $pBacteria = $data['diagnostic_2_bacteria'];
        $pCrystals = $data['diagnostic_2_crystals'];
        $pBladderCell = $data['diagnostic_2_bladder_cells'];
        $pSquamousCell = $data['diagnostic_2_squamous_cells'];
        $pTubularCell = $data['diagnostic_2_tubular_cells'];
        $pBroadCasts = $data['diagnostic_2_broad_casts'];
        $pEpithelialCast = $data['diagnostic_2_epithelial_cell_casts'];
        $pGranularCast = $data['diagnostic_2_granular_casts'];
        $pHyalineCast = $data['diagnostic_2_hyaline_casts'];
        $pRbcCast = $data['diagnostic_2_rbc_casts'];
        $pWaxyCast = $data['diagnostic_2_waxy_casts'];
        $pWcCast = $data['diagnostic_2_wc_casts'];
        $pAlbumin = $data['diagnostic_2_alb'];
        $pPusCells = $data['diagnostic_2_pus'];
        if ($pIsApplicableUrine != "N") {
            insertResultsUrinalysis($conn, $pCaseNo,$pLabDateUrinalysis,$pLabFeeUrinalysis, $pCoPayUrinalysis, $pReferralFacilityUrinalysis, $pGravity, $pAppearance, $pColor, $pGlucose, $pProteins, $pKetones, $pPh, $pRbCells, $pWbCells, $pBacteria, $pCrystals,
            $pBladderCell, $pSquamousCell, $pTubularCell, $pBroadCasts, $pEpithelialCast, $pGranularCast, $pHyalineCast, $pRbcCast, $pWaxyCast, $pWcCast, $pAlbumin, $pPusCells, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableUrine);
        }

        /* Results - Fecalysis */
        $pIsApplicableFeca=$data['diagnostic_3_status'];
        $pReferralFacilityFecalysis=$data['diagnostic_3_accre_diag_fac'];
        if($data['diagnostic_3_lab_exam_date'] != NULL){
            $pLabDateFecalysis = date('Y-m-d',strtotime($data['diagnostic_3_lab_exam_date']));
        } else{
            $pLabDateFecalysis = NULL;
        }
        $pLabFeeFecalysis = $data['diagnostic_3_lab_fee'];
        $pCoPayFecalysis = NULL;
        $pColorFecalysis = $data['diagnostic_3_color'];
        $pConsistency = $data['diagnostic_3_consistency'];
        $pRBC = $data['diagnostic_3_rbc'];
        $pWBC = $data['diagnostic_3_wbc'];
        $pOva = $data['diagnostic_3_ova'];
        $pParasite = $data['diagnostic_3_parasite'];
        $pBlood = $data['diagnostic_3_blood'];
        $pOccultBlood = $data['diagnostic_3_occult_blood'];
        $pPusCell = $data['diagnostic_3_pus'];
        if ($pIsApplicableFeca != "N") {
            insertResultsFecalysis($conn, $pCaseNo,$pLabDateFecalysis, $pLabFeeFecalysis, $pCoPayFecalysis, $pReferralFacilityFecalysis, $pColorFecalysis, $pConsistency, $pRBC, $pWBC, $pOva, $pParasite, $pBlood, $pOccultBlood, $pPusCell, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableFeca);
        }

        /* Results - Chest X-Ray */
        $pIsApplicableXray=$data['diagnostic_4_status'];
        $pReferralFacilityXray=$data['diagnostic_4_accre_diag_fac'];
        if($data['diagnostic_4_lab_exam_date'] != NULL){
            $pLabDateXray = date('Y-m-d',strtotime($data['diagnostic_4_lab_exam_date']));
        } else{
            $pLabDateXray = NULL;
        }
        $pLabFeeXray = $data['diagnostic_4_lab_fee'];
        $pCoPayXray = NULL;
        $pFindingsXray = $data['diagnostic_4_chest_findings'];
        $pRemarkFindings = $data['diagnostic_4_chest_findings_remarks'];
        $pObservation = $data['pObservation'];
        $pRemarkObservation = $data['pObservationRemarks'];
        if ($pIsApplicableXray != "N") {
            insertResultsChestXray($conn, $pCaseNo,$pLabDateXray, $pLabFeeXray, $pCoPayXray, $pReferralFacilityXray, $pFindingsXray, $pRemarkFindings, $pObservation, $pRemarkObservation, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableXray);
        }

        /* Results - Sputum */
        $pIsApplicableSputum=$data['diagnostic_5_status'];
        $pReferralFacilitySputum=$data['diagnostic_5_accre_diag_fac'];
        if($data['diagnostic_5_lab_exam_date'] != NULL){
            $pLabDateSputum = date('Y-m-d',strtotime($data['diagnostic_5_lab_exam_date']));
            $pDataCollect = "1";
        } else{
            $pLabDateSputum = NULL;
            $pDataCollect = "X";
        }
        $pLabFeeSputum = $data['diagnostic_5_lab_fee'];
        $pCoPaySputum = NULL;
        $pFindingsSputum = $data['diagnostic_5_sputum'];
        $pRemarksSputum = $data['diagnostic_5_sputum_remarks'];
        $pNoPlusses = $data['diagnostic_5_plusses'];
        if ($pIsApplicableSputum != "N") {
            insertResultsSputum($conn, $pCaseNo,$pLabDateSputum, $pLabFeeSputum, $pCoPaySputum, $pReferralFacilitySputum, $pFindingsSputum, $pRemarksSputum, $pNoPlusses, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableSputum,$pDataCollect);
        }

        /* Results - Lipid Profile */
        $pIsApplicableLipid=$data['diagnostic_6_status'];
        $pReferralFacilityLipid=$data['diagnostic_6_accre_diag_fac'];
        if($data['diagnostic_6_lab_exam_date'] != NULL){
            $pLabDateLipid = date('Y-m-d',strtotime($data['diagnostic_6_lab_exam_date']));
        } else{
            $pLabDateLipid = NULL;
        }
        $pLabFeeLipid = $data['diagnostic_6_lab_fee'];
        $pCoPayLipid = NULL;
        $pLdl = $data['diagnostic_6_ldl'];
        $pHdl = $data['diagnostic_6_hdl'];
        $pTotal = NULL;
        $pCholesterol = $data['diagnostic_6_cholesterol'];
        $pTriglycerides = $data['diagnostic_6_triglycerides'];
        if ($pIsApplicableLipid != "N") {
            insertResultsLipidProfile($conn, $pCaseNo,$pLabDateLipid, $pLabFeeLipid, $pCoPayLipid, $pReferralFacilityLipid, $pLdl, $pHdl, "", $pCholesterol, $pTriglycerides, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableLipid);
        }

        /* Results - Fasting Blood Sugar (FBS) */
        $pIsApplicableFbs = $data['diagnostic_7_status'];
        $pReferralFacilityFBS = $data['diagnostic_7_accre_diag_fac'];
        if($data['diagnostic_7_lab_exam_date'] != NULL){
            $pLabDateFBS = date('Y-m-d',strtotime($data['diagnostic_7_lab_exam_date']));
        } else{
            $pLabDateFBS = NULL;
        }
        $pLabFeeFBS = $data['diagnostic_7_lab_fee'];
        $pCoPayFBS = NULL;
        $pGlucoseMg = $data['diagnostic_7_glucose_mgdL'];
        $pGlucosemmol = $data['diagnostic_7_glucose_mmolL'];
        if ($pIsApplicableFbs != "N") {
            insertResultsFBS($conn, $pCaseNo,$pLabDateFBS, $pLabFeeFBS, $pCoPayFBS, $pReferralFacilityFBS, $pGlucoseMg, $pGlucosemmol, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableFbs);
        }

        /* Results - Random Blood Sugar (RBS) */
        $pIsApplicableRbs = $data['diagnostic_19_status'];
        $pReferralFacilityRBS = $data['diagnostic_19_accre_diag_fac'];
        if($data['diagnostic_19_lab_exam_date'] != NULL){
            $pLabDateRBS = date('Y-m-d',strtotime($data['diagnostic_19_lab_exam_date']));
        } else{
            $pLabDateRBS = NULL;
        }
        $pLabFeeRBS = $data['diagnostic_19_lab_fee'];
        $pCoPayRBS = NULL;
        $pGlucoseMgRBS = $data['diagnostic_19_glucose_mgdL'];
        $pGlucosemmolRBS = $data['diagnostic_19_glucose_mmolL'];
        if ($pIsApplicableRbs != "N") {
            insertResultsRBS($conn, $pCaseNo,$pLabDateRBS, $pLabFeeRBS, $pCoPayFBS, $pReferralFacilityRBS, $pGlucoseMgRBS, $pGlucosemmolRBS, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableRbs);
        }

        /* Results - Electrocardiogram (ECG) */
        $pIsApplicableEcg = strtoupper($data['diagnostic_9_status']);
        $pReferralFacilityECG = strtoupper($data['diagnostic_9_accre_diag_fac']);
        if($data['diagnostic_9_lab_exam_date'] != NULL){
            $pLabDateECG = date('Y-m-d',strtotime($data['diagnostic_9_lab_exam_date']));
        } else{
            $pLabDateECG = NULL;
        }
        $pLabFeeECG = $data['diagnostic_9_lab_fee'];
        $pCoPayECG = NULL;
        $pFindingsECG = $data['diagnostic_9_ecg'];
        $pRemarksECG = $data['diagnostic_9_ecg_remarks'];
        if ($pIsApplicableEcg != "N") {
            insertResultsECG($conn, $pCaseNo,$pLabDateECG, $pLabFeeECG, $pCoPayECG, $pReferralFacilityECG, $pFindingsECG, $pRemarksECG, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableEcg);
        }
        
        /* Results - Pap Smear */
        $pIsApplicablePaps = strtoupper($data['diagnostic_13_status']);
        $pReferralFacilityPaps = strtoupper($data['diagnostic_13_accre_diag_fac']);
        if($data['diagnostic_13_lab_exam_date'] != NULL){
            $pLabDatePapsSmear = date('Y-m-d',strtotime($data['diagnostic_13_lab_exam_date']));
        } else{
            $pLabDatePapsSmear = NULL;
        }
        $pLabFeePapsSmear = $data['diagnostic_13_lab_fee'];
        $pCoPayPapsSmear = NULL;
        $pFindingsPapsSmear = $data['diagnostic_13_papsSmearFindings'];
        $pImpressionPapsSmear = $data['diagnostic_13_papsSmearImpression'];
        if ($pIsApplicablePaps != "N") {
            insertResultsPapsSmear($conn, $pCaseNo,$pLabDatePapsSmear,$pLabFeePapsSmear,$pCoPayPapsSmear,$pReferralFacilityPaps, $pFindingsPapsSmear,$pImpressionPapsSmear,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicablePaps);
        }
        
        /* Results - Oral Glucose Tolerance Test (OGTT) */
        $pIsApplicableOgtt = strtoupper($data['diagnostic_14_status']);
        $pReferralFacilityOGTT = strtoupper($data['diagnostic_14_accre_diag_fac']);
        if($data['diagnostic_14_lab_exam_date'] != NULL){
            $pLabDateOGTT = date('Y-m-d',strtotime($data['diagnostic_14_lab_exam_date']));
        } else{
            $pLabDateOGTT = NULL;
        }
        $pLabFeeOGTT = $data['diagnostic_14_lab_fee'];
        $pCoPayOGTT = NULL;
        $pFastingMg = $data['diagnostic_14_fasting_mg'];
        $pFastingMmol = $data['diagnostic_14_fasting_mmol'];
        $pOgttOneHrMg= $data['diagnostic_14_oneHr_mg'];
        $pOgttOneHrMmol = $data['diagnostic_14_oneHr_mmol'];
        $pOgttTwoHrsMg = $data['diagnostic_14_twoHr_mg'];
        $pOgttTwoHrsMmol = $data['diagnostic_14_twoHr_mmol'];
        if ($pIsApplicableOgtt != "N") {
            insertResultsOGTT($conn, $pCaseNo, $pLabDateOGTT,$pLabFeeOGTT,$pCoPayOGTT,$pReferralFacilityOGTT,$pFastingMg,$pFastingMmol,$pOgttOneHrMg,$pOgttOneHrMmol,$pOgttTwoHrsMg,$pOgttTwoHrsMmol,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule, $pIsApplicableOgtt);
        }

        /*Fecal Occult Blood Test (FOBT)*/  //ekonsulta
        $pIsApplicableFobt = strtoupper($data['diagnostic_15_status']);
        $pReferralFacilityFobt = strtoupper($data['diagnostic_15_accre_diag_fac']);
        if($data['diagnostic_15_lab_exam_date'] != NULL){
            $pLabDateFobt = date('Y-m-d',strtotime($data['diagnostic_15_lab_exam_date']));
        } else{
            $pLabDateFobt = NULL;
        }
               
        $pLabFeeFobt = $data['diagnostic_15_lab_fee'];
        $pCoPayFobt = NULL;
        $pFindingsFobt = $data['diagnostic_15_fobt'];
        if ($pIsApplicableFobt != "N") {
            insertResultsFOBT($conn, $pCaseNo, $pLabDateFobt, $pLabFeeFobt, $pCoPayFobt,$pReferralFacilityFobt,$pFindingsFobt,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableFobt);
        }

        /*HbA1c*/ //ekonsulta
        $pIsApplicableHbA1c = strtoupper($data['diagnostic_18_status']);
        $pReferralFacilityHbA1c = strtoupper($data['diagnostic_18_accre_diag_fac']);
        if($data['diagnostic_18_lab_exam_date'] != NULL){
            $pLabDateHbA1c = date('Y-m-d',strtotime($data['diagnostic_18_lab_exam_date']));
        } else{
            $pLabDateHbA1c = NULL;
        }
        $pLabFeeHbA1c = $data['diagnostic_18_lab_fee'];
        $pCoPayHbA1c = NULL;
        $pFindingsHbA1c = $data['diagnostic_18_hba1c_mmol'];
        if ($pIsApplicableHbA1c != "N") {
            insertResultsHbA1c($conn, $pCaseNo, $pLabDateHbA1c, $pLabFeeHbA1c, $pCoPayHbA1c,$pReferralFacilityHbA1c,$pFindingsHbA1c,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableHbA1c);
        }

        /*Creatinine*/ //ekonsulta
        $pIsApplicableCreatinine = strtoupper($data['diagnostic_8_status']);
        $pReferralFacilityCreatinine = strtoupper($data['diagnostic_8_accre_diag_fac']);
        if($data['diagnostic_8_lab_exam_date'] != NULL){
            $pLabDateCreatinine = date('Y-m-d',strtotime($data['diagnostic_8_lab_exam_date']));
        } else{
            $pLabDateCreatinine = NULL;
        }
        $pLabFeeCreatinine = $data['diagnostic_8_lab_fee'];
        $pCoPayCreatinine = NULL;
        $pFindingsCreatinine = $data['diagnostic_8_creatinine_mgdl'];
        if ($pIsApplicableCreatinine != "N") {
            insertResultsCreatinine($conn, $pCaseNo, $pLabDateCreatinine, $pLabFeeCreatinine, $pCoPayCreatinine,$pReferralFacilityCreatinine,$pFindingsCreatinine,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableCreatinine);
        }

        /*PPD Test (Tuberculosis)*/ 
        $pIsApplicablePddt = strtoupper($data['diagnostic_17_status']);
        $pReferralFacilityPddt = strtoupper($data['diagnostic_17_accre_diag_fac']);
        if($data['diagnostic_17_lab_exam_date'] != NULL){
            $pLabDatePddt = date('Y-m-d',strtotime($data['diagnostic_17_lab_exam_date']));
        } else{
            $pLabDatePddt = NULL;
        }
        $pLabFeePddt  = $data['diagnostic_17_lab_fee'];
        $pCoPayPddt = NULL;
        $pFindingsPddt  = $data['diagnostic_17_ppdt'];
        if ($pIsApplicablePddt != "N") {
            insertResultsPPDT($conn, $pCaseNo, $pLabDatePddt, $pLabFeePddt, $pCoPayPddt,$pReferralFacilityPddt,$pFindingsPddt,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicablePddt);
        }

        /*Others*/ 
        $pIsApplicableOth = strtoupper($data['diagnostic_99_status']);
        $pReferralFacilityOth = strtoupper($data['diagnostic_99_accre_diag_fac']);
        if($data['diagnostic_99_lab_exam_date'] != NULL){
            $pLabDateOth = date('Y-m-d',strtotime($data['diagnostic_99_lab_exam_date']));
        } else{
            $pLabDateOth = NULL;
        }
        $pLabFeeOth  = $data['diagnostic_99_lab_fee'];
        $pCoPayOth = NULL;
        $pFindingsOthExam  = $data['diagnostic_99_oth1'];
        $pOthDiagExam  = $data['diagnostic_oth_remarks'];
        if ($pIsApplicableOth != "N") {
            insertResultsOthersDiagExam($conn, $pCaseNo, $pLabDateOth, $pLabFeeOth, $pCoPayOth,$pReferralFacilityOth,$pOthDiagExam,$pFindingsOthExam,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableOth);
        }
    /*End Laboratory Results*/
      
        $conn->commit();

        echo '<script>alert("Successfully Saved the Record."); window.location="labs_search.php";</script>';
    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: Encountered error while saving Laboratory/ Imaging Results - " . $e->getMessage();
        echo '<script>alert("Error: Encountered error while saving Laboratory/ Imaging Results - '.$e->getMessage().'");</script>';
    } finally {
        $conn = null;
    }

}

/*Get Laboratory/ Imaging Status*/
function getLaboratoryStatus($id, $transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        //cbc
        if ($id == '1') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_cbc
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //urinalysis
        if ($id == '2') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_urinalysis
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //fecalysis
        if ($id == '3') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_fecalysis
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //chest x-ray
        if ($id == '4') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_chestxray
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //sputum
        if ($id == '5') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_sputum
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //lipid profile
        if ($id == '6') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_lipidprof
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //fbs
        if ($id == '7') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_fbs
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //creatinine
        if ($id == '8') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_creatinine
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //ecg
        if ($id == '9') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_ecg
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //pap smear
        if ($id == '13') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_papssmear
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //ogtt
        if ($id == '14') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_ogtt
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //fobt
        if ($id == '15') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_fobt
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //ppdt
        if ($id == '17') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_ppd_test
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //hba1c
        if ($id == '18') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_hba1c
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //rbs
        if ($id == '19') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_rbs
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }

        //others
        if ($id == '99') {
            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, LAB_DATE, IS_APPLICABLE as 'LAB_STATUS'
                                FROM ".$ini['EPCB'].".tsekap_tbl_diag_others
                                WHERE TRANS_NO = :pTransNo");

            $stmt->bindParam(':pTransNo', $transno);
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;
}

/*Update Enlist table using Assign table*/
function updateEnlistUsingAssignTbl(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD'], array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE  konsulta.tsekap_tbl_enlist, konsulta.tsekap_tbl_assign
                                SET     konsulta.tsekap_tbl_enlist.px_lname = konsulta.tsekap_tbl_assign.assigned_last_name,
                                        konsulta.tsekap_tbl_enlist.px_fname = konsulta.tsekap_tbl_assign.assigned_first_name,
                                        konsulta.tsekap_tbl_enlist.px_mname = konsulta.tsekap_tbl_assign.assigned_middle_name,
                                        konsulta.tsekap_tbl_enlist.px_extname = konsulta.tsekap_tbl_assign.assigned_ext_name,
                                        konsulta.tsekap_tbl_enlist.px_dob = konsulta.tsekap_tbl_assign.assigned_dob,
                                        konsulta.tsekap_tbl_enlist.px_sex = konsulta.tsekap_tbl_assign.primary_sex,
                                        konsulta.tsekap_tbl_enlist.mem_pin = konsulta.tsekap_tbl_assign.primary_pin,
                                        konsulta.tsekap_tbl_enlist.mem_lname = konsulta.tsekap_tbl_assign.primary_last_name,
                                        konsulta.tsekap_tbl_enlist.mem_fname = konsulta.tsekap_tbl_assign.primary_first_name,
                                        konsulta.tsekap_tbl_enlist.mem_mname = konsulta.tsekap_tbl_assign.primary_middle_name,
                                        konsulta.tsekap_tbl_enlist.mem_extname = konsulta.tsekap_tbl_assign.primary_ext_name,
                                        konsulta.tsekap_tbl_enlist.mem_dob = konsulta.tsekap_tbl_assign.primary_dob,
                                        konsulta.tsekap_tbl_enlist.mem_sex = konsulta.tsekap_tbl_assign.primary_sex
                                WHERE   konsulta.tsekap_tbl_enlist.px_pin = konsulta.tsekap_tbl_assign.assigned_pin");

        $stmt->execute();

        $count = $stmt->rowCount();

        return $count;

    } catch (PDOException $e) {
        $conn->rollBack();

        echo "Error: AssignToEnlistTbl" . $e->getMessage();
        echo '<script>alert("Error: AssignToEnlistTbl'.$e->getMessage().'");</script>';
    }

    $conn = null;
}

function searchBeneficiaryForXMLGeneration($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $current_Year = date('Y');

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                  WHERE PX_PIN LIKE :pxPin");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

      
        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                  WHERE PX_LNAME LIKE :pxLname
                                    AND PX_FNAME LIKE :pxFname");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

      
        if(isset($_GET['pPIN']) && !empty($_GET['pPIN']) AND isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT *, DADATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                  WHERE PX_PIN LIKE :pxPin
                                    AND PX_LNAME LIKE :pxLname
                                    AND PX_FNAME LIKE :pxFname");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) AND isset($_GET['pMiddleName']) && !empty($_GET['pMiddleName']) AND isset($_GET['pSuffix']) && !empty($_GET['pSuffix'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                  WHERE PX_LNAME LIKE :pxLname
                                    AND PX_FNAME LIKE :pxFname
                                    AND PX_MNAME LIKE :pxMname
                                    AND PX_EXTNAME LIKE :pxExtname");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxMname', $pMiddleName);
            $stmt->bindParam(':pxExtname', $pSuffix);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) && isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) && isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){

            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                  WHERE PX_LNAME LIKE :pxLname
                                    AND PX_FNAME LIKE :pxFname
                                    AND PX_DOB LIKE :pxDob");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($pDateOfBirth)));
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Get Patient Record to Update/Edit Registration Table
function getEnlistRecord($pin, $effYear){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * 
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST 
                                WHERE PX_PIN = :pin
                                AND EFF_YEAR = :effYear");

        $stmt->bindParam(':pin', $pin);
        $stmt->bindParam(':effYear', $effYear);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function connDB() {
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    // Check connection
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    else{
        return true;
    }
    mysqli_close($connection);
}

/* GET PREV CONSULTATION RECORD  */
function getPrevConsultationTransNo($caseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT a.CASE_NO, b.TRANS_NO, b.SOAP_DATE
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS b ON a.CASE_NO = b.CASE_NO 
                                    WHERE a.CASE_NO = :pCaseNo
                                    ORDER BY b.soap_date desc");

        $stmt->bindParam(':pCaseNo', $caseNo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result[0];

}

function getPrevConsultationRecord($pSoapTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.*, soap.CASE_NO, soap.TRANS_NO, soap.SOAP_OTP
                                            FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS enlist 
                                                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap ON enlist.CASE_NO = soap.CASE_NO 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE AS subjective ON soap.TRANS_NO = subjective.TRANS_NO 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT AS pepert ON soap.TRANS_NO = pepert.TRANS_NO 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC AS pespecific ON soap.TRANS_NO = pespecific.TRANS_NO 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ADVICE AS advice ON soap.TRANS_NO = advice.TRANS_NO                               
                                                    WHERE soap.TRANS_NO = :pxTransNo");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result[0];

}


function getRecommendedDiagnosticExam($pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC as b ON a.TRANS_NO = b.TRANS_NO 
                                        WHERE (a.CASE_NO = :caseno AND b.IS_DR_RECOMMENDED = 'Y') OR 
                                            (a.CASE_NO = :caseno AND b.PX_REMARKS IN ('RQ','RF'))
                                            GROUP BY b.DIAGNOSTIC_ID   
                                              ORDER BY b.DATE_ADDED DESC");
        $stmt->bindParam(':caseno', $pCaseNo);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getZScoreG023($pLength, $pWeight){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                    FROM ".$ini['EPCB'].".TSEKAP_LIB_ZSCORE_G023
                                        WHERE LENGTH = :length
                                          AND WEIGHT = :weight");

        $stmt->bindParam(':length', $pLength);
        $stmt->bindParam(':weight', $pWeight);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getZScoreB023($pLength, $pWeight){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                    FROM ".$ini['EPCB'].".TSEKAP_LIB_ZSCORE_B023
                                        WHERE LENGTH = :length
                                          AND WEIGHT = :weight");

        $stmt->bindParam(':length', $pLength);
        $stmt->bindParam(':weight', $pWeight);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


function getZScoreG2460($pHeight, $pWeight){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                    FROM ".$ini['EPCB'].".TSEKAP_LIB_ZSCORE_G2460
                                        WHERE HEIGHT = :height
                                          AND WEIGHT = :weight");

        $stmt->bindParam(':height', $pHeight);
        $stmt->bindParam(':weight', $pWeight);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getZScoreB2460($pHeight, $pWeight){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                    FROM ".$ini['EPCB'].".TSEKAP_LIB_ZSCORE_B2460
                                        WHERE HEIGHT = :height
                                          AND WEIGHT = :weight");

        $stmt->bindParam(':height', $pHeight);
        $stmt->bindParam(':weight', $pWeight);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


function getPatientHsaRecordForSOAP($caseno) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT AS pepert ON profile.TRANS_NO = pepert.TRANS_NO 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC AS pespecific ON profile.TRANS_NO = pespecific.TRANS_NO 
                                WHERE profile.CASE_NO = :caseNo
                                AND profile.IS_FINALIZE = 'Y'
                                AND pepert.UPD_CNT = (SELECT UPD_CNT FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE 
                                                WHERE CASE_NO = :caseNo
                                                AND IS_FINALIZE = 'Y')
                                AND pespecific.UPD_CNT = (SELECT UPD_CNT FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE 
                                                WHERE CASE_NO = :caseNo
                                                AND IS_FINALIZE = 'Y')
                                ");

        $stmt->bindParam(":caseNo", $caseno);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;


}
function getPatientHSAPemiscRecorForSOAP($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS a 
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC AS b ON a.TRANS_NO = b.TRANS_NO
                                        WHERE a.CASE_NO = :caseNo
                                            AND a.IS_FINALIZE = 'Y'
                                            AND b.UPD_CNT = (SELECT UPD_CNT FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE 
                                                WHERE CASE_NO = :caseNo
                                                AND IS_FINALIZE = 'Y')");

        $stmt->bindParam(':caseNo', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


function getPatientPepertPrevTransNo($transNo) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS a 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT AS pepert ON a.TRANS_NO = pepert.TRANS_NO 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC AS pespecific ON a.TRANS_NO = pespecific.TRANS_NO 
                                WHERE a.TRANS_NO = :transno
                                ");

        $stmt->bindParam(":transno", $transNo);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;


}

function getPatientPemicsPrevTransNo($transNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS a 
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEMISC AS b ON a.TRANS_NO = b.TRANS_NO
                                        WHERE a.trans_no = :transno");

        $stmt->bindParam(':transno', $transNo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getPatientHSASocHistRecorForSOAP($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS a 
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST AS b ON a.TRANS_NO = b.TRANS_NO
                                        WHERE a.CASE_NO = :casseNo
                                        ORDER BY a.DATE_ADDED DESC");

        $stmt->bindParam(':casseNo', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function descChestXrayFindings($code){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".tsekap_lib_chest_findings
                                            WHERE FINDING_ID = :id");

        $stmt->bindParam(':id', $code);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getLabCbc($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO, b.TRANS_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                    AND b.LAB_DATE IS NOT NULL
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabUrinalysis($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabCreatinine($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO, b.TRANS_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CREATININE as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabFecalysis($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");
        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabChestXray($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabSputum($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabLipidProfile($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");
        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabFbs($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D') AND b.XPS_MODULE = 'PROF'
                                                  ORDER BY b.DATE_ADDED DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabFbsSOAP($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X') AND b.XPS_MODULE = 'SOAP'
                                                  ORDER BY b.DATE_ADDED DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabRbs($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                     AND b.IS_APPLICABLE IN ('D') AND b.XPS_MODULE = 'PROF'
                                                  ORDER BY b.DATE_ADDED DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabRbsSOAP($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X') AND b.XPS_MODULE = 'SOAP'
                                                  ORDER BY b.DATE_ADDED DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}



function getLabEcg($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_ECG as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabOgtt($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OGTT as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabPapSmear($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PAPSSMEAR as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabFecalOccultBlood($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FOBT as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabHba1c($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_HBA1C as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabPPDTest($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PPD_TEST as b ON a.CASE_NO = b.CASE_NO
                                                WHERE b.case_no = :caseno
                                                    AND b.IS_APPLICABLE IN ('D','W', 'X')
                                                  ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabOthers($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.CASE_NO
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a 
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OTHERS as b ON a.CASE_NO = b.CASE_NO
                                    WHERE b.case_no = :caseno
                                        AND b.IS_APPLICABLE IN ('D')
                                        ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getEKASLabOthers($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT a.CASE_NO, b.TRANS_NO, b.OTH_DIAG_EXAM, b.LAB_DATE, b.FINDINGS, b.IS_APPLICABLE
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a 
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OTHERS as b ON a.CASE_NO = b.CASE_NO
                                    WHERE b.TRANS_NO = :transno
                                        AND b.IS_APPLICABLE IN ('D')
                                        ORDER BY b.LAB_DATE DESC");

        $stmt->bindParam(':transno', $transno);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


/*PX Record*/
function describeSignsSymptoms($code){
   $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".tsekap_lib_symptoms WHERE SYMPTOMS_ID = :code");

        $stmt->bindParam(':code', $code);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function describeMedDisease($code){
   $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".tsekap_lib_mdiseases WHERE MDISEASE_CODE = :code");

        $stmt->bindParam(':code', $code);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getPxRecordProfile($pCaseNo){
   $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT a.TRANS_NO, a.PX_PIN, a.PROF_DATE, b.BLOOD_TYPE, c.PX_AGE,
                                d.PREG_CNT, d.DELIVERY_CNT,d.FULL_TERM_CNT,d.PREMATURE_CNT,d.ABORTION_CNT,d.LIV_CHILDREN_CNT,
                                e.IS_SMOKER,e.NO_CIGPK,e.IS_ADRINKER,e.NO_BOTTLES,e.ILL_DRUG_USER,e.IS_SEXUALLY_ACTIVE,
                                f.MENARCHE_PERIOD,f.LAST_MENS_PERIOD,f.PERIOD_DURATION,f.MENS_INTERVAL,f.BIRTH_CTRL_METHOD,
                                f.IS_MENOPAUSE,f.MENOPAUSE_AGE,f.IS_APPLICABLE
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS a 
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_prof_bloodtype as b ON a.TRANS_NO = b.TRANS_NO
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_prof_oinfo as c ON a.TRANS_NO = c.TRANS_NO
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_prof_preghist as d ON a.TRANS_NO = d.TRANS_NO
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_prof_sochist as e ON a.TRANS_NO = e.TRANS_NO
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_prof_menshist as f ON a.TRANS_NO = f.TRANS_NO
                                WHERE a.CASE_NO = :caseno
                                AND b.UPD_CNT = a.UPD_CNT
                                AND c.UPD_CNT = a.UPD_CNT
                                AND d.UPD_CNT = a.UPD_CNT
                                AND e.UPD_CNT = a.UPD_CNT
                                AND a.IS_FINALIZE = 'Y'");

        $stmt->bindParam(':caseno', $pCaseNo);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getPxRecordEnlist($pxPin, $effYear){
   $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * 
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST  
                                WHERE PX_PIN = :pxPin
                                AND EFF_YEAR = :effYear");

        $stmt->bindParam(':pxPin', $pxPin);
        $stmt->bindParam(':effYear', $effYear);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*To generate data per individual*/
function getLabResultPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT DISTINCT CASE_NO, TRANS_NO, PX_PIN, PX_TYPE, MEM_PIN, EFF_YEAR
                                   FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                    WHERE case_no = :caseno 
                                      ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getLabCbcPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO, cbc.TRANS_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC as cbc ON enlist.CASE_NO = cbc.CASE_NO
                                                WHERE cbc.case_no = :caseno
                                                AND cbc.IS_APPLICABLE IN ('D', 'W', 'X')
                                                  ORDER BY cbc.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabUrinePerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS as urinalysis ON enlist.CASE_NO = urinalysis.CASE_NO
                                                WHERE urinalysis.case_no = :caseno     
                                                 AND urinalysis.IS_APPLICABLE IN ('D', 'W', 'X') 
                                                  GROUP BY urinalysis.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabFecalysisPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS as fecalysis ON enlist.CASE_NO = fecalysis.CASE_NO
                                                WHERE fecalysis.case_no = :caseno 
                                                AND fecalysis.IS_APPLICABLE IN ('D', 'W', 'X') 
                                                  GROUP BY fecalysis.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabChestXrayPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY as chestxray ON enlist.CASE_NO = chestxray.CASE_NO
                                                WHERE chestxray.case_no = :caseno
                                                AND chestxray.IS_APPLICABLE IN ('D', 'W', 'X') 
                                                  GROUP BY chestxray.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabSputumPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM as sputum ON enlist.CASE_NO = sputum.CASE_NO
                                                WHERE sputum.case_no = :caseno   
                                                 AND sputum.IS_APPLICABLE IN ('D', 'W', 'X')   
                                                  GROUP BY sputum.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabLipidProfPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO 
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF as lipidprof ON enlist.CASE_NO = lipidprof.CASE_NO
                                                WHERE lipidprof.case_no = :caseno  
                                                 AND lipidprof.IS_APPLICABLE IN ('D', 'W', 'X')    
                                                  GROUP BY lipidprof.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabFbsPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS 
                                WHERE case_no = :caseno     
                                AND IS_APPLICABLE IN ('D', 'W', 'X') ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabEcgPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_ECG as ecg ON enlist.CASE_NO = ecg.CASE_NO
                                                WHERE ecg.case_no = :caseno  
                                                AND ecg.IS_APPLICABLE IN ('D', 'W', 'X')  
                                                  GROUP BY ecg.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabOgttPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OGTT as ogtt ON enlist.CASE_NO = ogtt.CASE_NO
                                                WHERE ogtt.case_no = :caseno   
                                                AND ogtt.IS_APPLICABLE IN ('D', 'W', 'X')  
                                                  GROUP BY ogtt.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLabPapsSmearPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO
                                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist 
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PAPSSMEAR as paps ON enlist.CASE_NO = paps.CASE_NO
                                                WHERE paps.case_no = :caseno
                                                AND paps.IS_APPLICABLE IN ('D', 'W', 'X')
                                                  GROUP BY paps.CASE_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabFOBTPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FOBT as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.case_no = :caseno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

         $stmt->bindParam(':caseno', $caseno);


        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabCreatininePerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CREATININE as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.case_no = :caseno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabPDDPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PPD_TEST b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.case_no = :caseno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);


        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabHbA1cPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_HBA1C b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.case_no = :caseno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

         $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabOthDiagPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OTHERS b ON a.TRANS_NO = b.TRANS_NO
                                     WHERE a.case_no = :caseno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

       $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabRBSPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS 
                                   WHERE case_no = :caseno
                                    AND IS_APPLICABLE IN ('D', 'W', 'X') ");

       $stmt->bindParam(':caseno', $caseno);


        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


/*Generation of Report - Medicine*/
function getMedicinePerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT meds.*, meds.TRANS_NO, meds.CASE_NO
                                     FROM ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE as meds
                                     LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap ON meds.TRANS_NO = soap.TRANS_NO
                                        WHERE meds.case_no = :caseno
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation*/
function getMainConsultPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, soap.TRANS_NO 
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                        WHERE soap.case_no = :caseno
                                          ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getConsultationPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, soap.TRANS_NO
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ADVICE as advice ON soap.TRANS_NO = advice.TRANS_NO 
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT as pepert ON soap.TRANS_NO = pepert.TRANS_NO 
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC as pespecific ON soap.TRANS_NO = pespecific.TRANS_NO 
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE as subjective ON soap.TRANS_NO = subjective.TRANS_NO 
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE as meds ON soap.TRANS_NO = meds.TRANS_NO 
                                        WHERE soap.case_no = :caseno
                                          AND advice.UPD_CNT = soap.UPD_CNT
                                          AND pepert.UPD_CNT = soap.UPD_CNT
                                          AND pespecific.UPD_CNT = soap.UPD_CNT
                                          AND subjective.UPD_CNT = soap.UPD_CNT
                                          AND meds.UPD_CNT = soap.UPD_CNT
                                          GROUP BY soap.TRANS_NO                                          
                                            ORDER BY soap.SOAP_DATE ASC");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - DIAGNOSTIC*/
function getConsultationDiagnosticPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT diagnostic.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC as diagnostic ON soap.TRANS_NO = diagnostic.TRANS_NO 
                                            WHERE soap.case_no = :caseno
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - ICD*/
function getConsultationIcdPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT icd.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ICD as icd ON soap.TRANS_NO = icd.TRANS_NO 
                                            WHERE soap.case_no = :caseno
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND icd.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getConsultationIcdPerIndividualPxRecord($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT icd.*, icd.TRANS_NO, soap.UPD_CNT
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ICD as icd ON soap.TRANS_NO = icd.TRANS_NO 
                                            WHERE icd.trans_no = :transno
                                              AND icd.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $transno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - Management*/
function getConsultationManagementPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT management.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MANAGEMENT as management ON soap.TRANS_NO = management.TRANS_NO 
                                            WHERE soap.case_no = :caseno
                                              AND management.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - PERTINENT FINDINGS PER SYSTEM*/
function getConsultationPemiscPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT pemisc.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEMISC as pemisc ON soap.TRANS_NO = pemisc.TRANS_NO 
                                            WHERE soap.case_no = :caseno  
                                              AND pemisc.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Enlistment/Registration*/
function getEnlistmentPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                        WHERE case_no = :caseNo
                                          AND XPS_MODULE = 'K'");

        $stmt->bindParam(':caseNo', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*Generation of Report - Profiling*/
function getResultProfilingPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, profile.TRANS_NO AS TRANS_NO, profile.REMARKS AS PROFILE_REM
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENT DETAILS*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_OINFO AS oinfo ON profile.TRANS_NO = oinfo.TRANS_NO /*PATIENTS DETAILS OTHER INFO*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST AS sochist ON profile.TRANS_NO = sochist.TRANS_NO /*PERSONAL/SOCIAL HISTORY*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PREGHIST AS preghist ON profile.TRANS_NO = preghist.TRANS_NO /*OB-GYNE HISTORY - PREGNANCY*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MENSHIST AS menshist ON profile.TRANS_NO = menshist.TRANS_NO /*OB-GYNE HISTORY - MENSTRUAL*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT AS pepert ON profile.TRANS_NO = pepert.TRANS_NO /*PERTINENT PHYSICAL EXAMINATION FINDINGS*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_BLOODTYPE AS bloodtype ON profile.TRANS_NO = bloodtype.TRANS_NO /*PERTINENT PHYSICAL EXAMINATION FINDINGS - BLOOD TYPE*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC AS pespecific ON profile.TRANS_NO = pespecific.TRANS_NO /*PERTINENT PHYSICAL EXAMINATION FINDINGS - REMARKS*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_NCDQANS AS ncdqans ON profile.TRANS_NO = ncdqans.TRANS_NO /*NCD HIGH-RISK ASSESSMENT*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEGENSURVEY AS survey ON profile.TRANS_NO = survey.TRANS_NO /*PLAN/MANAGEMENT - ADVICE*/
                                            WHERE profile.case_no = :caseno
                                                GROUP BY profile.TRANS_NO");

        $stmt->bindParam(':caseno', $caseno);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - MEDICAL HISTORY*/
function getProfilingMedHistPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT medhist.*, profile.TRANS_NO, profile.CASE_NO, profile.DATE_ADDED, profile.EFF_YEAR
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST AS medhist ON profile.TRANS_NO = medhist.TRANS_NO 
                                            WHERE profile.case_no = :caseno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND profile.UPD_CNT = medhist.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - MEDICAL HISTORY REMARKS*/
function getProfilingMHspecificPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT mhspecific.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED
                                   FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                      LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC AS mhspecific ON profile.TRANS_NO = mhspecific.TRANS_NO 
                                        WHERE profile.IS_FINALIZE = 'Y'
                                          AND profile.case_no = :caseno
                                          AND mhspecific.UPD_CNT = profile.UPD_CNT
                                         ");

        $stmt->bindParam(':caseno', $caseno);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - PERTINENT FINDINGS PER SYSTEM*/
function getProfilingPemiscPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT pemisc.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC AS pemisc ON profile.TRANS_NO = pemisc.TRANS_NO 
                                            WHERE profile.IS_FINALIZE = 'Y'
                                              AND profile.case_no = :caseno
                                              AND pemisc.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - SURGICAL HISTORY*/
function getProfilingSurghistPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT surghist.*, profile.TRANS_NO, profile.CASE_NO, profile.DATE_ADDED, profile.EFF_YEAR
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST AS surghist ON profile.TRANS_NO = surghist.TRANS_NO 
                                            WHERE profile.case_no = :caseno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND profile.UPD_CNT = surghist.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - FAMILY HISTORY*/
function getProfilingFamhistPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT famhist.*, profile.TRANS_NO, profile.CASE_NO, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST AS famhist ON profile.TRANS_NO = famhist.TRANS_NO 
                                            WHERE profile.case_no = :caseno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND profile.UPD_CNT = famhist.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - FAMILY HISTORY REMARKS*/
function getProfilingFHspecificPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT fhspecific.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC AS fhspecific ON profile.TRANS_NO = fhspecific.TRANS_NO 
                                            WHERE profile.IS_FINALIZE = 'Y'
                                              AND profile.case_no = :caseno
                                              AND fhspecific.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - IMMUNIZATION*/
function getProfilingImmunizationPerIndividual($caseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT immune.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION AS immune ON profile.TRANS_NO = immune.TRANS_NO 
                                            WHERE profile.IS_FINALIZE = 'Y'
                                              AND profile.case_no = :caseno
                                              AND immune.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':caseno', $caseno);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*To generate all data per individual*/
function getRegistrationRecord(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * 
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                ORDER BY ENLIST_DATE DESC" );

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


/*Essential Services Get Results*/
function getCreatinineResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CREATININE
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getHbA1cResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_HBA1C
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getECGResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_ecg
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getSputumResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_sputum
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getOGTTResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_ogtt
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getLipidProfileResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_lipidprof
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getChestXrayResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_chestxray
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getPapsSmearResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_papssmear
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getUrinalysisResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_urinalysis
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getFecalysisResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_fecalysis
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getCBCResults($caseNo,$module){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".tsekap_tbl_diag_cbc
                              WHERE case_no = :caseno AND xps_module = :module");

        $stmt->bindParam(':caseno', $caseNo);
        $stmt->bindParam(':module', $module);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;

    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


function searchAssignedMember($pPIN, $pEffYear){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       
        
        $stmt = $conn->prepare("SELECT *
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                              WHERE ASSIGNED_PIN LIKE :pxPin
                              AND EFF_YEAR = :effYear");

        $stmt->bindParam(':pxPin', $pPIN);
        $stmt->bindParam(':effYear', $pEffYear);
    
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    } catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


/*20191213*/
//Search Feature used in Enlistment/Registration, Consultation, Profiling/Health Screening & Assessment Search Module
function searchBasedOnAssignmentMasterlist($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $current_Year = date('Y');

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(ASSIGNED_DOB, :DATE_FORMAT) AS ASSIGNED_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                                  WHERE ASSIGNED_PIN LIKE :pxPin
                                  ORDER BY EFF_YEAR DESC");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

      
        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(ASSIGNED_DOB, :DATE_FORMAT) AS ASSIGNED_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                                  WHERE ASSIGNED_LAST_NAME LIKE :pxLname
                                    AND ASSIGNED_FIRST_NAME LIKE :pxFname
                                    ORDER BY EFF_YEAR DESC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

      
        if(isset($_GET['pPIN']) && !empty($_GET['pPIN']) AND isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(ASSIGNED_DOB, :DATE_FORMAT) AS ASSIGNED_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                                  WHERE ASSIGNED_PIN LIKE :pxPin
                                    AND ASSIGNED_LAST_NAME LIKE :pxLname
                                    AND ASSIGNED_FIRST_NAME LIKE :pxFname
                                    ORDER BY EFF_YEAR DESC");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) AND isset($_GET['pMiddleName']) && !empty($_GET['pMiddleName']) AND isset($_GET['pSuffix']) && !empty($_GET['pSuffix'])){
            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(ASSIGNED_DOB, :DATE_FORMAT) AS ASSIGNED_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                                  WHERE ASSIGNED_LAST_NAME LIKE :pxLname
                                    AND ASSIGNED_FIRST_NAME LIKE :pxFname
                                    AND ASSIGNED_MIDDLE_NAME LIKE :pxMname
                                    AND ASSIGNED_EXT_NAME LIKE :pxExtname
                                    ORDER BY EFF_YEAR DESC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxMname', $pMiddleName);
            $stmt->bindParam(':pxExtname', $pSuffix);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) && isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) && isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){

            $stmt = $conn->prepare("SELECT *, DATE_FORMAT(ASSIGNED_DOB, :DATE_FORMAT) AS ASSIGNED_DOB
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                                  WHERE ASSIGNED_LAST_NAME LIKE :pxLname
                                    AND ASSIGNED_FIRST_NAME LIKE :pxFname
                                    AND ASSIGNED_DOB LIKE :pxDob
                                    ORDER BY EFF_YEAR DESC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($pDateOfBirth)));
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


function authenticateUser($pUserID, $pUserPassword){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /*$stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_HCI_PROFILE
                                  WHERE USER_ID = :pUserId COLLATE latin1_bin
                                    AND USER_PASSWORD = :pUserPass COLLATE latin1_bin");*/

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_HCI_PROFILE
                                  WHERE USER_ID = :pUserId
                                    AND USER_PASSWORD = :pUserPass");

        $stmt->bindParam(':pUserId', $pUserID);
        $stmt->bindParam(':pUserPass', $pUserPassword);

        $stmt->execute();

        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            session_start();
            $_SESSION['pHciNum']=$row['HCI_NO'];
            $_SESSION['pAccreNum']=$row['ACCRE_NO'];
            $_SESSION['pPmccNum']=$row['PMCC_NO'];
            $_SESSION['pEmdID']=$row['USER_EMPID'];
            $_SESSION['pHospName']=$row['HOSP_NAME'];
            $_SESSION['pHospAddBrgy']=$row['HOSP_ADDBRGY'];
            $_SESSION['pHospAddMun']=$row['HOSP_ADDMUN'];
            $_SESSION['pHospAddProv']=$row['HOSP_ADDPROV'];
            $_SESSION['pHospAddRegion']=$row['HOSP_ADDREG'];
            $_SESSION['pHospAddLhio']=$row['HOSP_ADDLHIO'];
            $_SESSION['pHospSector']=$row['SECTOR'];
            $_SESSION['pHospZipCode']=$row['HOSP_ADDZIPCODE'];
            $_SESSION['pUserLname']=$row['USER_LNAME'];
            $_SESSION['pUserFname']=$row['USER_FNAME'];
            $_SESSION['pUserMname']=$row['USER_MNAME'];
            $_SESSION['pUserSuffix']=$row['USER_EXTNAME'];
            $_SESSION['pUserID']=$pUserID;
            $_SESSION['pUserPassword']=$pUserPassword;
            $_SESSION['user_is_logged_in'] = true;
            return true;
        }
        else {
            echo '<script type="text/javascript">alert("Invalid User ID/ Password!");window.location="index.php";</script>';
        }

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function checkLogin() {
    session_start();
    if (!isset($_SESSION['user_is_logged_in']) || $_SESSION['user_is_logged_in'] != true){
        session_destroy();
        header("location:index.php");
        exit();
    }
}

/*SOAP LIBRARY LISTS*/
function listComplaint(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_SYMPTOMS  
                                              WHERE LIB_STAT = '1' 
                                                AND SYMPTOMS_ID NOT IN('X')
                                                ORDER BY SYMPTOMS_DESC ASC");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function listSkin(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT *
                FROM ".$ini['EPCB'].".TSEKAP_LIB_SKIN
                  WHERE LIB_STAT = '1'
                  ORDER BY SORT_NO ASC";

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $skin[] = $row;
    }

    return $skin;

    mysqli_close($connection);
}

function listHeent(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_HEENT
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $heent[] = $row;
    }

    return $heent;
    mysqli_close($connection);
}

function listChest(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_CHEST
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $chest[] = $row;
    }

    return $chest;
    mysqli_close($connection);
}

function listHeart(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_HEART
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $heart[] = $row;
    }

    return $heart;
    mysqli_close($connection);
}

function listAbdomen(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_ABDOMEN
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $abdomen[] = $row;
    }

    return $abdomen;
    mysqli_close($connection);
}

function listExtremities(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_EXTREMITIES
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $extremities[] = $row;
    }

    return $extremities;
    mysqli_close($connection);
}

function listNeuro(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_NEURO
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $neuro[] = $row;
    }

    return $neuro;
    mysqli_close($connection);
}

function listGenitourinary(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_GENITOURINARY
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $gu[] = $row;
    }

    return $gu;
    mysqli_close($connection);
}

function listDigitalRectal(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_DIGITAL_RECTAL
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $digital[] = $row;
    }

    return $digital;
    mysqli_close($connection);
}

function listSkinExtremities(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_SKIN_EXTREMITIES
                  WHERE LIB_STAT = "1"
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $skin[] = $row;
    }

    return $skin;
    mysqli_close($connection);
}


function listICD(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_ICD
                  WHERE LIB_STAT = "1"
                  ORDER BY ICD_DESC ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $icd[] = $row;
    }

    return $icd;
    mysqli_close($connection);
}

function listDiagnosis(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_DIAGNOSTIC
                WHERE LIB_STAT = "1" 
                  AND DIAGNOSTIC_ID NOT IN ("X", "0")
                  ORDER BY DIAGNOSTIC_DESC ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $diag[] = $row;
    }

    return $diag;
    mysqli_close($connection);
}

//ekonsulta
function listDiagnosisConsultation(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_DIAGNOSTIC
                WHERE LIB_STAT = "1" 
                  AND DIAGNOSTIC_ID NOT IN ("0")
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $diag[] = $row;
    }

    return $diag;
    mysqli_close($connection);
}

function listPhysicianRecommendation(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_RECOMMENDATION
                WHERE LIB_STAT = "1" 
                  ORDER BY SORT_NO ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $diag[] = $row;
    }

    return $diag;
    mysqli_close($connection);
}


function listManagement(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_MANAGEMENT
                  WHERE MANAGEMENT_ID NOT IN ("X", "0")
                  ORDER BY MANAGEMENT_ID ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $mngt[] = $row;
    }

    return $mngt;
    mysqli_close($connection);
}

/*Address Library*/
function listProvince(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = "SELECT PROCODE, PROVINCE, AREACODE, LHIO,
                     CASE 
                          WHEN PROVINCE = '74' THEN 'NCR, SECOND DISTRICT'
                          WHEN PROVINCE = '75' THEN 'NCR, THIRD DISTRICT'
                          WHEN PROVINCE = '76' THEN 'NCR, FOURTH DISTRICT'
                          WHEN PROVINCE = '82' THEN 'NCR, FIFTH DISTRICT'
                          WHEN PROVINCE = '83' THEN 'NCR, SIXTH DISTRICT'
                          ELSE PROV_NAME
                      END AS PROV_NAME
                FROM ".$ini['EPCB'].".LIB_PROVINCE
                  ORDER BY PROV_NAME ASC";

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $data[] = $row;
    }

    return $data;
    mysqli_close($connection);
}

function listGenMedsDesc(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_MEDS_GENERIC
                  ORDER BY GEN_DESC ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $drugs[] = $row;
    }

    return $drugs;
    mysqli_close($connection);
}

function listDrugsDesc(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.TSEKAP_LIB_MEDICINE
                WHERE DRUG_CODE NOT IN ("NOMED0000000000000000000000000")
                  ORDER BY DRUG_DESC ASC';
    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $drugs[] = $row;
    }

    return $drugs;
    mysqli_close($connection);
}

function listChestXrayObservation(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_CHEST_OBSERVATION
                                            WHERE LIB_STAT = '1' 
                                            ORDER BY OBSERVE_ID ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function listChestXrayFindings(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_CHEST_FINDINGS
                                            WHERE LIB_STAT = '1' 
                                            ORDER BY FINDING_ID ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function listHCIclass(){
    $ini = parse_ini_file("config.ini");
    $connection = mysqli_connect($ini['DBSERVER'], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
    if (!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $query = 'SELECT *
                FROM '.$ini["EPCB"].'.LIB_HCI_CLASS
                  ORDER BY CLASS_DEF ASC';

    $result = mysqli_query( $connection, $query);

    if(!$result ) {
        die('Could not get data: ' . mysqli_error());
    }

    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $class[] = $row;
    }

    return $class;
    mysqli_close($connection);
}


function getMunicipality($pProvCode){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".LIB_MUNICIPALITY  WHERE PROVINCE = :provCode ORDER BY MUN_NAME ASC");

        $stmt->bindParam(':provCode', $pProvCode);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getBarangay($pMunCode, $pProvCode){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * 
                                            FROM ".$ini['EPCB'].".LIB_BARANGAY  
                                                WHERE MUNICIPALITY = :munCode 
                                                  AND PROVINCE = :provCode
                                                    ORDER BY BRGY_NAME ASC");

        $stmt->bindParam(':munCode', $pMunCode);
        $stmt->bindParam(':provCode', $pProvCode);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getRegionLhio($pProvCode){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".LIB_PROVINCE  WHERE PROVINCE = :provCode");

        $stmt->bindParam(':provCode', $pProvCode);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getZipCode($pMunCode, $pProvCode){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                            FROM ".$ini['EPCB'].".LIB_ZIPCODE
                                                WHERE MUNICIPALITY = :munCode
                                                  AND PROVINCE = :provCode");

        $stmt->bindParam(':munCode', $pMunCode);
        $stmt->bindParam(':provCode', $pProvCode);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getPatientInfoEnlist($pPxInfo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['K'].".TSEKAP_TBL_ENLIST  WHERE PX_PIN = :pxPin");

        $stmt->bindParam(':pxPin', $pPxInfo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Get Patient Record to Update/Edit Registration Table
function getPatientRecord($pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST WHERE CASE_NO = :pxCaseNo");

        $stmt->bindParam(':pxCaseNo', $pCaseNo);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET RECORD OF PATIENT THAT HAS A CONSULTATION RECORD  */
function checkPatientConsultationRecordExist($pCaseNum){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT soap.TRANS_NO, soap.*, enlist.CASE_NO, enlist.PX_LNAME, enlist.PX_FNAME, enlist.PX_MNAME, enlist.PX_EXTNAME, enlist.MEM_PIN, meds.*
                                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap
                                              RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS enlist ON soap.CASE_NO = enlist.CASE_NO
                                              LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE AS meds ON soap.CASE_NO = meds.CASE_NO
                                                WHERE soap.CASE_NO = :caseNo
                                                GROUP BY meds.CASE_NO");


        $stmt->bindParam(':caseNo', $pCaseNum);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET RECORD OF PATIENT THAT HAS ALREADY HSA  */
function getPatientInfoConsultationList($pCaseNum){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT soap.TRANS_NO, soap.*, profile.CASE_NO, profile.IS_FINALIZE, enlist.CASE_NO, enlist.PX_LNAME, enlist.PX_FNAME, enlist.PX_MNAME, enlist.PX_EXTNAME, enlist.MEM_PIN
                                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap
                                              RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile ON profile.CASE_NO = soap.CASE_NO
                                              RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS enlist ON profile.CASE_NO = enlist.CASE_NO
                                                WHERE profile.CASE_NO = :caseNo
                                                  AND profile.IS_FINALIZE = 'Y'");


        $stmt->bindParam(':caseNo', $pCaseNum);
        // $stmt->bindParam(':accreNo', $_SESSION['pAccreNum']);

        // echo "<pre>"; print_r($_SESSION['pAccreNum']); echo"</pre>";

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getPatientInfoConsultation($pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *,
                            CASE CIVIL_STATUS
                                WHEN 'S' THEN 'SINGLE'
                                WHEN 'M' THEN 'MARRIED'
                                WHEN 'W' THEN 'WIDOWED'
                                WHEN 'X' THEN 'SEPERATED'
                                WHEN 'A' THEN 'ANNULED'
                                ELSE '-'
                            END CIVIL_STATUS                            
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST WHERE CASE_NO = :pxCaseNo");

        $stmt->bindParam(':pxCaseNo', $pCaseNo);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getConsultationEnlistInfo($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT a.*, b.*, b.TRANS_NO           
            FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP b ON a.CASE_NO = b.CASE_NO
            WHERE b.TRANS_NO = :pTransNo
        ");

        $stmt->bindParam(':pTransNo', $pTransNo);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD TO VIEW, EDIT, UPDATE*/
function getPatientConsultationRecord($pSoapTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.*, soap.CASE_NO, soap.TRANS_NO, soap.SOAP_OTP
                                            FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS enlist /*PATIENT INFO*/
                                                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap ON enlist.CASE_NO = soap.CASE_NO /*CONSULTATION INFO*/
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE AS subjective ON soap.TRANS_NO = subjective.TRANS_NO /*SUBJECTIVE/ HISTORY OF ILLNESS*/
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT AS pepert ON soap.TRANS_NO = pepert.TRANS_NO /*OBJECTIVE/PHYSICAL EXAMINATION */
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC AS pespecific ON soap.TRANS_NO = pespecific.TRANS_NO /*OBJECTIVE/PHYSICAL EXAMINATION - REMARKS */
                                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ADVICE AS advice ON soap.TRANS_NO = advice.TRANS_NO /*PLAN/MANAGEMENT - ADVICE*/                                
                                                    WHERE soap.TRANS_NO = :pxTransNo
                                                      AND soap.UPD_CNT = :updCnt
                                                      AND subjective.UPD_CNT = :updCnt
                                                      AND pepert.UPD_CNT = :updCnt
                                                      AND pespecific.UPD_CNT = :updCnt
                                                      AND advice.UPD_CNT = :updCnt");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result[0];

}

/*GET PROFILING RECORD - PATIENT'S HSA RECORD*/
function getPatientHsaList($pHsaCaseNo, $pEffYear){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, PROF_DATE, PROF_BY, EFF_YEAR, PX_PIN
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE 
                                WHERE CASE_NO = :caseNo
                                AND EFF_YEAR = :effYear");

        $stmt->bindParam(':caseNo', $pHsaCaseNo);
        $stmt->bindParam(':effYear', $pEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET PROFILING RECORD WITH CONSULTATION FROM PREVIOUS YEAR*/
function getPatientFPEConsultInfo($pin, $effYear){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, a.TRANS_NO, a.CASE_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE a
                                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP b ON a.case_no = b.case_no
                                WHERE a.PX_PIN = :pin
                                AND a.EFF_YEAR = :effYear");

        $stmt->bindParam(':pin', $pin);
        $stmt->bindParam(':effYear', $effYear);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD - OBJECTIVE/PHYSICAL EXAMINATION TO VIEW, EDIT, UPDATE*/
function getPatientPemiscRecord($pSoapTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEMISC AS pemisc ON soap.TRANS_NO = pemisc.TRANS_NO
                                        WHERE soap.TRANS_NO = :pxTransNo
                                          AND soap.UPD_CNT = :updCnt
                                          AND pemisc.UPD_CNT = :updCnt");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD - ASSESSMENT/DIAGNOSIS TO VIEW, EDIT, UPDATE*/
function getPatientAssessmentRecord($pSoapTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, libIcd.ICD_DESC, libIcd.ICD_CODE                       
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ICD AS icd ON soap.TRANS_NO = icd.TRANS_NO
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_LIB_ICD AS libIcd ON icd.ICD_CODE = libIcd.ICD_CODE
                                    WHERE soap.TRANS_NO = :pxTransNo
                                      AND soap.UPD_CNT = :updCnt
                                      AND icd.UPD_CNT = :updCnt");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD - PLAN/MANAGEMENT - DIAGNOSTIC EXAMINATION  TO VIEW, EDIT, UPDATE*/
function getConsultationDiagnosticInfo($pSoapTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS diagnostic ON soap.TRANS_NO = diagnostic.TRANS_NO
                                    WHERE soap.TRANS_NO = :pxTransNo");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD - PLAN/MANAGEMENT - DIAGNOSTIC EXAMINATION  TO VIEW, EDIT, UPDATE*/
function getPatientDiagnosticRecord($pSoapTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS diagnostic ON soap.TRANS_NO = diagnostic.TRANS_NO
                                    WHERE soap.TRANS_NO = :pxTransNo
                                      AND soap.UPD_CNT = :updCnt
                                      AND diagnostic.UPD_CNT = :updCnt");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD - PLAN/MANAGEMENT - MANAGEMENENT  TO VIEW, EDIT, UPDATE*/
function getPatientManagementRecord($pSoapTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MANAGEMENT AS management ON soap.TRANS_NO = management.TRANS_NO
                                    WHERE soap.TRANS_NO = :pxTransNo
                                      AND soap.UPD_CNT = :updCnt
                                      AND management.UPD_CNT = :updCnt");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET CONSULTATION RECORD - OBLIGATED SERVICE TO VIEW, EDIT, UPDATE*/
// function getPatientObligatedServiceRecord($pSoapTransNo,$getUpdCnt){
//     $ini = parse_ini_file("config.ini");

//     try {
//         $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
//         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//         $stmt = $conn->prepare("SELECT *                       
//                             FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
//                                 LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_OBLIGATED AS obligated ON soap.TRANS_NO = obligated.TRANS_NO
//                                     WHERE soap.TRANS_NO = :pxTransNo
//                                       AND soap.UPD_CNT = :updCnt
//                                       AND obligated.UPD_CNT = :updCnt");

//         $stmt->bindParam(':pxTransNo', $pSoapTransNo);
//         $stmt->bindParam(':updCnt', $getUpdCnt);

//         $stmt->execute();

//         $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

//         return $result;
//     }
//     catch(PDOException $e)
//     {
//         echo "Error: " . $e->getMessage();
//     }

//     $conn = null;

// }

/*GET CONSULTATION RECORD - MEDICINE TO VIEW, EDIT, UPDATE*/
function getPatientSoapMedicine($pSoapTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                       
                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS soap 
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE AS meds ON soap.TRANS_NO = meds.TRANS_NO
                                    WHERE soap.TRANS_NO = :pxTransNo
                                      AND soap.UPD_CNT = :updCnt
                                      AND meds.TRANS_NO = :pxTransNo
                                      AND meds.UPD_CNT = :updCnt");

        $stmt->bindParam(':pxTransNo', $pSoapTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getLabResults($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT diag.*, soap.CASE_NO, soap.TRANS_NO, 
                                            enlist.CASE_NO, enlist.PX_DOB, enlist.PX_SEX
                                            FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC as diag
                                                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap ON soap.TRANS_NO = diag.TRANS_NO
                                                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist ON enlist.CASE_NO = soap.CASE_NO
                                                  WHERE diag.TRANS_NO LIKE :pTransNo
                                                    AND diag.UPD_CNT = (SELECT UPD_CNT FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP WHERE TRANS_NO LIKE :pTransNo)");

        $stmt->bindParam(':pTransNo', $pTransNo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getMeds($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDICINE
                                            WHERE DRUG_CODE = :drugCode
                                            ORDER BY DRUG_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getMedsSalt($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDICINE
                                            WHERE GEN_CODE = :drugCode
                                            GROUP BY SALT_CODE                                            
                                            ORDER BY GEN_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getMedsUnit($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDICINE
                                            WHERE GEN_CODE = :drugCode
                                            GROUP BY UNIT_CODE                                            
                                            ORDER BY GEN_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getMedsCopay($code){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_COPAYMENT
                                            WHERE DRUG_CODE = :drugCode");

        $stmt->bindParam(':drugCode', $code);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getMedsStrength($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDICINE
                                            WHERE GEN_CODE = :drugCode
                                            GROUP BY STRENGTH_CODE                                            
                                            ORDER BY GEN_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getMedsForm($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDICINE
                                            WHERE GEN_CODE = :drugCode
                                            GROUP BY FORM_CODE                                            
                                            ORDER BY GEN_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getMedsPackage($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDICINE
                                            WHERE GEN_CODE = :drugCode
                                            GROUP BY PACKAGE_CODE                                            
                                            ORDER BY GEN_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function descMedsSalt($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_SALT
                                            WHERE SALT_CODE = :drugCode 
                                            ORDER BY SALT_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function descMedsUnit($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_UNIT
                                            WHERE UNIT_CODE = :drugCode 
                                            ORDER BY UNIT_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}
function descMedsGeneric($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_GENERIC
                                            WHERE GEN_CODE = :drugCode 
                                            ORDER BY GEN_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}
function descMedsStrength($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_STRENGTH
                                            WHERE STRENGTH_CODE = :drugCode 
                                            ORDER BY STRENGTH_CODE ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function descMedsForm($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_FORM
                                            WHERE FORM_CODE = :drugCode 
                                            ORDER BY FORM_DESC ASC");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function descMedsPackage($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_LIB_MEDS_PACKAGE
                                            WHERE PACKAGE_CODE = :drugCode");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function descMedsDrugCode($pMeds){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".tsekap_lib_medicine
                                            WHERE DRUG_CODE = :drugCode");

        $stmt->bindParam(':drugCode', $pMeds);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Describe Obligated Service
function describeObligatedService($serviceID){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *       
                            FROM ".$ini['EPCB'].".TSEKAP_LIB_OBLIGATED
                              WHERE SERVICE_ID = :pServiceId");

        $stmt->bindParam(':pServiceId', $serviceID);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Describe Obligated Service - Waived Reason
function describeWaivedReason($pReasonID){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *       
                            FROM ".$ini['EPCB'].".TSEKAP_LIB_WAIVED_REASON
                              WHERE REASON_ID = :pReasonId");

        $stmt->bindParam(':pReasonId', $pReasonID);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Describe Diagnostic Laboratory Results
function describeLabResults($pDiagnosticID){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *       
                            FROM ".$ini['EPCB'].".TSEKAP_LIB_DIAGNOSTIC
                              WHERE DIAGNOSTIC_ID = :pDiagID");

        $stmt->bindParam(':pDiagID', $pDiagnosticID);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Describe Province Address
function describeProvinceAddress($pProviceAddress){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *       
                            FROM ".$ini['EPCB'].".LIB_PROVINCE
                              WHERE PROVINCE = :provinceCode");

        $stmt->bindParam(':provinceCode', $pProviceAddress);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Describe Municipality Address
function describeMunicipalityAddress($pMunicipalityAddress, $pProvinceAddress){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *       
                            FROM ".$ini['EPCB'].".LIB_MUNICIPALITY
                              WHERE MUNICIPALITY = :municipalityCode
                                AND PROVINCE = :provinceCode");

        $stmt->bindParam(':municipalityCode', $pMunicipalityAddress);
        $stmt->bindParam(':provinceCode', $pProvinceAddress);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Describe Barangay Address
function describeBarangayAddress($pBarangayAddress,$MunicipalityAddress, $ProvinceAddress){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *       
                            FROM ".$ini['EPCB'].".LIB_BARANGAY
                              WHERE BARANGAY = :brgyCode
                                AND MUNICIPALITY =  :munCode
                                AND PROVINCE =  :provCode");

        $stmt->bindParam(':brgyCode', $pBarangayAddress);
        $stmt->bindParam(':munCode', $MunicipalityAddress);
        $stmt->bindParam(':provCode', $ProvinceAddress);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Generate Reports
function generateReport($pReportType,$pPxType,$pFromDate,$pToDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $search_format = "%Y-%m-%d";
        $fromdate=date('Y-m-d', strtotime($pFromDate));
        $todate=date('Y-m-d', strtotime($pToDate));

        if(!empty($pReportType) && !empty($pPxType) && !empty($pFromDate) && !empty($pToDate)){
            switch($pReportType){
                //Registered
                case '1':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, 
                                                              DATE_FORMAT(TRANS_DATE, :DATE_FORMAT) AS TRANS_DATE, 
                                                              CASE PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                                                  WHERE PX_TYPE IN ('MM','DD')
                                                                        AND TRANS_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, 
                                                              DATE_FORMAT(TRANS_DATE, :DATE_FORMAT) AS TRANS_DATE, 
                                                              CASE PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                                                  WHERE PX_TYPE LIKE 'MM'
                                                                    AND TRANS_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT CASE_NO, TRANS_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, 
                                                              DATE_FORMAT(TRANS_DATE, :DATE_FORMAT) AS TRANS_DATE, 
                                                              CASE PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                                                  WHERE PX_TYPE LIKE 'DD'
                                                                    AND TRANS_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;
                //Screened and Assessed
                case '2':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              PROFILE.CASE_NO, DATE_FORMAT(PROFILE.PROF_DATE, :DATE_FORMAT) AS PROF_DATE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS PROFILE ON PROFILE.CASE_NO = ENLIST.CASE_NO
                                                                    WHERE ENLIST.PX_TYPE IN ('MM','DD')
                                                                        AND PROFILE.PROF_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              PROFILE.CASE_NO, DATE_FORMAT(PROFILE.PROF_DATE, :DATE_FORMAT) AS PROF_DATE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS PROFILE ON PROFILE.CASE_NO = ENLIST.CASE_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'MM'
                                                                    AND PROFILE.PROF_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              PROFILE.CASE_NO, DATE_FORMAT(PROFILE.PROF_DATE, :DATE_FORMAT) AS PROF_DATE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS PROFILE ON PROFILE.CASE_NO = ENLIST.CASE_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'DD'
                                                                    AND PROFILE.PROF_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;
                //Consulted
                case '3':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    WHERE ENLIST.PX_TYPE IN ('MM','DD')
                                                                        AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'MM'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'DD'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;
                //Services Provided
                case '4':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE,
                                                              OBLIGATED.TRANS_NO, OBLIGATED.SERVICE_ID, OBLIGATED.REASON_ID, OBLIGATED.REMARKS, OBLIGATED.SERVICE_VALUE, OBLIGATED.BP_TYPE
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_OBLIGATED AS OBLIGATED ON OBLIGATED.TRANS_NO = SOAP.TRANS_NO
                                                                    WHERE ENLIST.PX_TYPE IN ('MM','DD')
                                                                        AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                        GROUP BY OBLIGATED.TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE,
                                                              OBLIGATED.TRANS_NO, OBLIGATED.SERVICE_ID, OBLIGATED.REASON_ID, OBLIGATED.REMARKS, OBLIGATED.SERVICE_VALUE, OBLIGATED.BP_TYPE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_OBLIGATED AS OBLIGATED ON OBLIGATED.TRANS_NO = SOAP.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'MM'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                    GROUP BY OBLIGATED.TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE,
                                                              OBLIGATED.TRANS_NO, OBLIGATED.SERVICE_ID, OBLIGATED.REASON_ID, OBLIGATED.REMARKS, OBLIGATED.SERVICE_VALUE, OBLIGATED.BP_TYPE 
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_OBLIGATED AS OBLIGATED ON OBLIGATED.TRANS_NO = SOAP.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'DD'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                    GROUP BY OBLIGATED.TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;

                //Laboratories
                case '5':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE,
                                                              CBC.*, URINE.*, FECALYSIS.*, XRAY.*, SPUTUM.*, LIPIDPROF.*, FBS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC AS CBC ON SOAP.TRANS_NO = CBC.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS AS URINE ON SOAP.TRANS_NO = URINE.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS AS FECALYSIS ON SOAP.TRANS_NO = FECALYSIS.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY AS XRAY ON SOAP.TRANS_NO = XRAY.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM AS SPUTUM ON SOAP.TRANS_NO = SPUTUM.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF AS LIPIDPROF ON SOAP.TRANS_NO = LIPIDPROF.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS AS FBS ON SOAP.TRANS_NO = FBS.TRANS_NO
                                                                    WHERE ENLIST.PX_TYPE IN ('MM','DD')
                                                                        AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE,
                                                              CBC.*, URINE.*, FECALYSIS.*, XRAY.*, SPUTUM.*, LIPIDPROF.*, FBS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC AS CBC ON SOAP.TRANS_NO = CBC.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS AS URINE ON SOAP.TRANS_NO = URINE.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS AS FECALYSIS ON SOAP.TRANS_NO = FECALYSIS.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY AS XRAY ON SOAP.TRANS_NO = XRAY.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM AS SPUTUM ON SOAP.TRANS_NO = SPUTUM.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF AS LIPIDPROF ON SOAP.TRANS_NO = LIPIDPROF.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS AS FBS ON SOAP.TRANS_NO = FBS.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'MM'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE,
                                                              CBC.*, URINE.*, FECALYSIS.*, XRAY.*, SPUTUM.*, LIPIDPROF.*, FBS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC AS CBC ON SOAP.TRANS_NO = CBC.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS AS URINE ON SOAP.TRANS_NO = URINE.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS AS FECALYSIS ON SOAP.TRANS_NO = FECALYSIS.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY AS XRAY ON SOAP.TRANS_NO = XRAY.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM AS SPUTUM ON SOAP.TRANS_NO = SPUTUM.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF AS LIPIDPROF ON SOAP.TRANS_NO = LIPIDPROF.TRANS_NO
                                                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS AS FBS ON SOAP.TRANS_NO = FBS.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'DD'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;
                //Prescribed Drugs
                case '6':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, MEDS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MEDS AS MEDS ON MEDS.TRANS_NO = SOAP.TRANS_NO
                                                                    WHERE ENLIST.PX_TYPE IN ('MM','DD')
                                                                        AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                        AND IS_DISPENSED_MEDS = 'NO'
                                                                            GROUP BY TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, MEDS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MEDS AS MEDS ON MEDS.TRANS_NO = SOAP.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'MM'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                    AND IS_DISPENSED_MEDS = 'NO'
                                                                      GROUP BY TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, MEDS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MEDS AS MEDS ON MEDS.TRANS_NO = SOAP.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'DD'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                    AND IS_DISPENSED_MEDS = 'NO'
                                                                        GROUP BY TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;

                //Dispensed Drugs
                case '7':
                    switch($pPxType){
                        case 'All':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, MEDS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MEDS AS MEDS ON MEDS.TRANS_NO = SOAP.TRANS_NO
                                                                    WHERE ENLIST.PX_TYPE IN ('MM','DD')
                                                                        AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                        AND IS_DISPENSED_MEDS = 'YES'
                                                                            GROUP BY TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'MM':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, MEDS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MEDS AS MEDS ON MEDS.TRANS_NO = SOAP.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'MM'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                    AND IS_DISPENSED_MEDS = 'YES'
                                                                      GROUP BY TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        case 'DD':
                            $stmt = $conn->prepare("SELECT ENLIST.CASE_NO, ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, 
                                                              CASE ENLIST.PX_TYPE
                                                                WHEN 'MM' THEN 'MEMBER'
                                                                WHEN 'DD' THEN 'DEPENDENT'
                                                                WHEN 'NM' THEN 'NON-MEMBER'
                                                                ELSE '-'
                                                              END PX_TYPE, 
                                                              ENLIST.EFF_YEAR,
                                                              SOAP.CASE_NO, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, MEDS.*
                                                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON SOAP.CASE_NO = ENLIST.CASE_NO
                                                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MEDS AS MEDS ON MEDS.TRANS_NO = SOAP.TRANS_NO
                                                                  WHERE ENLIST.PX_TYPE LIKE 'DD'
                                                                    AND SOAP.SOAP_DATE BETWEEN STR_TO_DATE(:fromDate, :SEARCH_FORMAT) AND STR_TO_DATE(:toDate, :SEARCH_FORMAT)
                                                                    AND IS_DISPENSED_MEDS = 'YES'
                                                                        GROUP BY TRANS_NO");

                            $stmt->bindParam(':DATE_FORMAT', $date_format);
                            $stmt->bindParam(':SEARCH_FORMAT', $search_format);
                            $stmt->bindParam(':fromDate', $fromdate);
                            $stmt->bindParam(':toDate', $todate);
                            break;
                        default:
                            echo 'No record found!';
                    }
                    break;

                default:
                    echo 'No record found!';
            }

        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

//Search Feature used in Enlistment/Registration, Consultation, Profiling/Health Screening & Assessment Search Module
function searchClientResult($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth, $pModule){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $current_Year = date('Y');

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("
                SELECT CLAIM_ID, CASE_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB, PX_TYPE, EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                WHERE PX_PIN LIKE :pxPin
            ");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':xpsMod', $pModule);
        }

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN']) AND isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){
            $stmt = $conn->prepare("
                SELECT CLAIM_ID, CASE_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB, PX_TYPE, EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                WHERE PX_PIN LIKE :pxPin
                AND PX_DOB LIKE :pxDob
            ");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($pDateOfBirth)));
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':xpsMod', $pModule);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("
                SELECT CLAIM_ID, CASE_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB, PX_TYPE, EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                WHERE PX_LNAME LIKE :pxLname
                AND PX_FNAME LIKE :pxFname
                ORDER BY TRANS_NO ASC
            ");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':xpsMod', $pModule);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) && isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) && isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){
            $stmt = $conn->prepare("
                SELECT CLAIM_ID, CASE_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB, PX_TYPE, EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                WHERE PX_LNAME LIKE :pxLname
                AND PX_FNAME LIKE :pxFname
                AND PX_DOB LIKE :pxDob
                ORDER BY TRANS_NO ASC
            ");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($pDateOfBirth)));
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':xpsMod', $pModule);
        }

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN']) AND isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("
                SELECT CLAIM_ID, CASE_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB, PX_TYPE, EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                WHERE PX_PIN LIKE :pxPin
                AND PX_LNAME LIKE :pxLname
                AND PX_FNAME LIKE :pxFname
                ORDER BY TRANS_NO ASC
            ");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':xpsMod', $pModule);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) AND isset($_GET['pMiddleName']) && !empty($_GET['pMiddleName']) AND isset($_GET['pSuffix']) && !empty($_GET['pSuffix'])){
            $stmt = $conn->prepare("
                SELECT CLAIM_ID, CASE_NO, PX_PIN, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, DATE_FORMAT(PX_DOB, :DATE_FORMAT) AS PX_DOB, PX_TYPE, EFF_YEAR, DATE_FORMAT(ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                WHERE PX_LNAME LIKE :pxLname
                AND PX_FNAME LIKE :pxFname
                AND PX_MNAME LIKE :pxMname
                AND PX_EXTNAME LIKE :pxExtname
                ORDER BY TRANS_NO ASC
            ");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxMname', $pMiddleName);
            $stmt->bindParam(':pxExtname', $pSuffix);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':xpsMod', $pModule);
        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Search Feature used in Enlistment/Registration, Consultation, Profiling/Health Screening & Assessment Search Module
function searchClientConsultation($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth, $pModule){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $current_Year = date('Y');


        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("
                SELECT a.CASE_NO, a.PX_PIN, a.PX_LNAME, a.PX_FNAME, a.PX_MNAME, a.PX_EXTNAME, DATE_FORMAT(a.PX_DOB, :DATE_FORMAT) AS PX_DOB, a.PX_TYPE, a.EFF_YEAR, 
                DATE_FORMAT(a.ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE, b.TRANS_NO,  DATE_FORMAT(b.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST a
                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP b ON a.CASE_NO = b.CASE_NO
                WHERE a.PACKAGE_TYPE = :pPackageType
                AND a.PX_PIN = :pxPin
                ORDER BY b.SOAP_DATE DESC
            ");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':pPackageType', $pModule);
            $stmt->bindParam(':pLname', $pLastName);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) && isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){
            $stmt = $conn->prepare("
                SELECT a.CASE_NO, a.PX_PIN, a.PX_LNAME, a.PX_FNAME, a.PX_MNAME, a.PX_EXTNAME, DATE_FORMAT(a.PX_DOB, :DATE_FORMAT) AS PX_DOB, a.PX_TYPE, a.EFF_YEAR, 
                DATE_FORMAT(a.ENLIST_DATE, :DATE_FORMAT) AS ENLIST_DATE, b.TRANS_NO, DATE_FORMAT(b.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE
                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST a
                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP b ON a.CASE_NO = b.CASE_NO
                WHERE a.PACKAGE_TYPE = :pPackageType
                AND a.PX_PIN = :pxPin
                AND a.PX_DOB = :pxDob
                AND a.PX_LNAME = :pxLname
                AND a.PX_FNAME = :pxFname
                AND (a.PX_MNAME = :pxMname OR :pxMname is null)
                AND (a.PX_EXTNAME = :pxExtName OR :pxExtName is null)
                ORDER BY b.SOAP_DATE DESC
            ");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
            $stmt->bindParam(':pPackageType', $pModule);
            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxMname', $pMiddleName);
            $stmt->bindParam(':pxExtName', $pSuffix);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($pDateOfBirth)));
        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Search Feature used in ekas
function getProfilingTransactionForSlip($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               
        $stmt = $conn->prepare("SELECT a.CASE_NO, b.TRANS_NO, a.PX_TYPE, a.PX_LNAME, a.PX_FNAME, a.PX_MNAME, a.PX_EXTNAME, 
                            a.PX_PIN, a.PX_DOB, a.PX_CONTACTNO, b.PROFILE_OTP, a.PX_SEX, a.ENLIST_DATE, a.PX_DOB, a.PX_MOBILE_NO, a.PX_LANDLINE_NO,
                                CASE 
                                    WHEN a.PX_TYPE = 'MM' THEN 'MEMBER'
                                    WHEN a.PX_TYPE = 'DD' THEN 'DEPENDENT'
                                    ELSE 'UNDEFINED'
                                END AS PX_TYPE
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS b ON a.CASE_NO = b.CASE_NO
                                      WHERE b.TRANS_NO LIKE :transno");

        $stmt->bindParam(':transno', $transno);
    
        
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getConsultationTransactionForSlip($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               
        $stmt = $conn->prepare("SELECT a.CASE_NO, b.TRANS_NO, a.PX_TYPE, a.PX_LNAME, a.PX_FNAME, a.PX_MNAME, a.PX_EXTNAME, 
                            a.PX_PIN, a.PX_DOB, a.PX_MOBILE_NO, a.PX_LANDLINE_NO, b.SOAP_OTP, a.PX_SEX, a.ENLIST_DATE, a.PX_DOB,a.PX_MOBILE_NO, a.PX_LANDLINE_NO, b.SOAP_DATE,
                                CASE 
                                    WHEN a.PX_TYPE = 'MM' THEN 'MEMBER'
                                    WHEN a.PX_TYPE = 'DD' THEN 'DEPENDENT'
                                    ELSE 'UNDEFINED'
                                END AS PX_TYPE
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS b ON a.CASE_NO = b.CASE_NO
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE AS c ON a.TRANS_NO = b.TRANS_NO
                                      WHERE b.TRANS_NO LIKE :transno");

        $stmt->bindParam(':transno', $transno);
    
        
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getConsultationTransactionForMedicine($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               
        $stmt = $conn->prepare("SELECT b.CASE_NO, b.TRANS_NO, 
                                b.DRUG_CODE, b.GEN_CODE, b.STRENGTH_CODE, b.FORM_CODE, b.UNIT_CODE,  b.GENERIC_NAME, b.QUANTITY,
                                b.PRESC_PHYSICIAN, b.IS_DISPENSED,b.DISPENSED_DATE,b.DISPENSING_PERSONNEL,b.CATEGORY 
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS a
                                    RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE AS b ON a.TRANS_NO = b.TRANS_NO
                                      WHERE b.TRANS_NO LIKE :transno
                                      AND b.GEN_CODE NOT IN ('NOMED')");

        $stmt->bindParam(':transno', $transno);
    
        
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getProfilingTransactionForMedicine($transno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               
        $stmt = $conn->prepare("SELECT b.CASE_NO, b.TRANS_NO, 
                                b.DRUG_CODE, b.GEN_CODE, b.STRENGTH_CODE, b.FORM_CODE, b.UNIT_CODE,  b.GENERIC_NAME, b.QUANTITY 
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS a
                                    RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE AS b ON a.TRANS_NO = b.TRANS_NO
                                      WHERE b.TRANS_NO LIKE :transno");

        $stmt->bindParam(':transno', $transno);
    
        
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

//Search Feature used in ekas
function searchTransactionPerScreening($pPIN, $pLastName, $pFirstName, $pMiddleName){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $current_Year = date('Y');

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS b ON a.CASE_NO = b.CASE_NO
                                  WHERE a.PX_PIN LIKE :pxPin");

            $stmt->bindParam(':pxPin', $pPIN);
        }
       

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a 
                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS b ON a.CASE_NO = b.CASE_NO
                                  WHERE a.PX_LNAME LIKE :pxLname
                                    AND a.PX_FNAME LIKE :pxFname
                                    ORDER BY b.TRANS_NO ASC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
        }
       
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function searchTransactionPerConsultation($pPIN, $pLastName, $pFirstName, $pMiddleName){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";
        $current_Year = date('Y');

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS c ON a.CASE_NO = c.CASE_NO
                                  WHERE a.PX_PIN LIKE :pxPin");

            $stmt->bindParam(':pxPin', $pPIN);
        }
       

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a 
                                RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS b ON a.CASE_NO = b.CASE_NO
                                  WHERE a.PX_LNAME LIKE :pxLname
                                    AND a.PX_FNAME LIKE :pxFname
                                    ORDER BY b.TRANS_NO ASC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
        }
       
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


//Search Feature used in Laboratory Search Module
function searchLabResult($pPIN, $pLastName, $pFirstName, $pMiddleName, $pSuffix, $pDateOfBirth){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $date_format = "%m/%d/%Y";

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN'])){
            $stmt = $conn->prepare("SELECT SOAP.TRANS_NO, SOAP.PX_PIN, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, SOAP.EFF_YEAR,
                                ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, DATE_FORMAT(ENLIST.PX_DOB, :DATE_FORMAT) AS PX_DOB,
                                DIAG.DIAGNOSTIC_ID, DIAG.TRANS_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS DIAG       
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON DIAG.TRANS_NO = SOAP.TRANS_NO                            
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST ON SOAP.PX_PIN = ENLIST.PX_PIN                               
                                      WHERE SOAP.PX_PIN LIKE :pxPin
                                      GROUP BY SOAP.TRANS_NO");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN']) AND isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){
            $stmt = $conn->prepare("SELECT SOAP.TRANS_NO, SOAP.PX_PIN, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, SOAP.EFF_YEAR,
                                ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, DATE_FORMAT(ENLIST.PX_DOB, :DATE_FORMAT) AS PX_DOB,
                                DIAG.DIAGNOSTIC_ID, DIAG.TRANS_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS DIAG       
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON DIAG.TRANS_NO = SOAP.TRANS_NO                            
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST ON SOAP.PX_PIN = ENLIST.PX_PIN 
                                  WHERE ENLIST.PX_PIN LIKE :pxPin
                                      AND ENLIST.PX_DOB LIKE :pxDob
                                      GROUP BY SOAP.TRANS_NO");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':pxDob', date('m/d/Y', strtotime($pDateOfBirth)));
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT SOAP.TRANS_NO, SOAP.PX_PIN, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, SOAP.EFF_YEAR,
                                ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, DATE_FORMAT(ENLIST.PX_DOB, :DATE_FORMAT) AS PX_DOB,
                                DIAG.DIAGNOSTIC_ID, DIAG.TRANS_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS DIAG       
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON DIAG.TRANS_NO = SOAP.TRANS_NO                            
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST ON SOAP.PX_PIN = ENLIST.PX_PIN 
                                  WHERE ENLIST.PX_LNAME LIKE :pxLname
                                    AND ENLIST.PX_FNAME LIKE :pxFname
                                    GROUP BY SOAP.TRANS_NO
                                    ORDER BY SOAP.TRANS_NO ASC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) AND isset($_GET['pDateOfBirth']) && !empty($_GET['pDateOfBirth'])){
            $stmt = $conn->prepare("SELECT SOAP.TRANS_NO, SOAP.PX_PIN, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, SOAP.EFF_YEAR,
                                ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, DATE_FORMAT(ENLIST.PX_DOB, :DATE_FORMAT) AS PX_DOB,
                                DIAG.DIAGNOSTIC_ID, DIAG.TRANS_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS DIAG       
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON DIAG.TRANS_NO = SOAP.TRANS_NO                            
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST ON SOAP.PX_PIN = ENLIST.PX_PIN 
                                  WHERE ENLIST.PX_LNAME LIKE :pxLname
                                    AND ENLIST.PX_FNAME LIKE :pxFname
                                    AND ENLIST.PX_DOB LIKE :pxDob
                                    GROUP BY SOAP.TRANS_NO
                                    ORDER BY SOAP.TRANS_NO ASC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxDob', date('m/d/Y', strtotime($pDateOfBirth)));
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pPIN']) && !empty($_GET['pPIN']) AND isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName'])){
            $stmt = $conn->prepare("SELECT SOAP.TRANS_NO, SOAP.PX_PIN, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, SOAP.EFF_YEAR,
                                ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, DATE_FORMAT(ENLIST.PX_DOB, :DATE_FORMAT) AS PX_DOB,
                                DIAG.DIAGNOSTIC_ID, DIAG.TRANS_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS DIAG       
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON DIAG.TRANS_NO = SOAP.TRANS_NO                            
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST ON SOAP.PX_PIN = ENLIST.PX_PIN 
                                  WHERE ENLIST.PX_PIN LIKE :pxPin
                                    AND ENLIST.PX_LNAME LIKE :pxLname
                                    AND ENLIST.PX_FNAME LIKE :pxFname
                                    GROUP BY SOAP.TRANS_NO
                                    ORDER BY SOAP.TRANS_NO ASC");

            $stmt->bindParam(':pxPin', $pPIN);
            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        if(isset($_GET['pLastName']) && !empty($_GET['pLastName']) AND isset($_GET['pFirstName']) && !empty($_GET['pFirstName']) AND isset($_GET['pMiddleName']) && !empty($_GET['pMiddleName']) AND isset($_GET['pSuffix']) && !empty($_GET['pSuffix'])){
            $stmt = $conn->prepare("SELECT SOAP.TRANS_NO, SOAP.PX_PIN, DATE_FORMAT(SOAP.SOAP_DATE, :DATE_FORMAT) AS SOAP_DATE, SOAP.EFF_YEAR,
                                ENLIST.PX_PIN, ENLIST.PX_LNAME, ENLIST.PX_FNAME, ENLIST.PX_MNAME, ENLIST.PX_EXTNAME, DATE_FORMAT(ENLIST.PX_DOB, :DATE_FORMAT) AS PX_DOB,
                                DIAG.DIAGNOSTIC_ID, DIAG.TRANS_NO
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC AS DIAG       
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP AS SOAP ON DIAG.TRANS_NO = SOAP.TRANS_NO                            
                                    INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS ENLIST ON SOAP.PX_PIN = ENLIST.PX_PIN 
                                  WHERE ENLIST.PX_LNAME LIKE :pxLname
                                    AND ENLIST.PX_FNAME LIKE :pxFname
                                    AND ENLIST.PX_MNAME LIKE :pxMname
                                    AND ENLIST.PX_EXTNAME LIKE :pxExtname
                                    GROUP BY SOAP.TRANS_NO
                                    ORDER BY SOAP.TRANS_NO ASC");

            $stmt->bindParam(':pxLname', $pLastName);
            $stmt->bindParam(':pxFname', $pFirstName);
            $stmt->bindParam(':pxMname', $pMiddleName);
            $stmt->bindParam(':pxExtname', $pSuffix);
            $stmt->bindParam(':DATE_FORMAT', $date_format);
        }

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*Insert data in Facility Registration table*/
function saveHciProfileRegistration($hospRegistration){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_HCI_PROFILE(
              HCI_NO, ACCRE_NO, PMCC_NO, HOSP_NAME, HOSP_ADDBRGY, HOSP_ADDMUN, HOSP_ADDPROV, HOSP_ADDREG, HOSP_ADDZIPCODE, HOSP_ADDLHIO,
              SECTOR, EMAIL_ADD, TEL_NO, DATE_REGISTERED, USER_ID, USER_PASSWORD, USER_EMPID, USER_LNAME, USER_FNAME, USER_MNAME, USER_EXTNAME,
              USER_SEX, USER_DOB, CIPHER_KEY)
                VALUES(:hciNo, :accreNo, :pmccNo, :hospName ,:hospBrgy, :hospMun, :hospProv, :hospRegion, :hospZipCode, :hospLhio,
                      :sector, :hospEmail, :hospTelNo, NOW(), :userid, :userPassword, :userEmpId, :userLname, :userFname, :userMname, :userExtname, :userSex, :userDoB, :userKey)");

        $stmt->bindParam(':hciNo', trim($hospRegistration['pHciNo']));
        $stmt->bindParam(':accreNo', trim($hospRegistration['pAccreNo']));
        $stmt->bindParam(':pmccNo', trim($hospRegistration['pPmccNo']));
        $stmt->bindParam(':hospName', trim(strtoupper($hospRegistration['pHospName'])));
        $stmt->bindParam(':hospBrgy', $hospRegistration['pHospAddBrgy']);
        $stmt->bindParam(':hospMun', $hospRegistration['pHospAddMun']);
        $stmt->bindParam(':hospProv', $hospRegistration['pHospAddProv']);
        $stmt->bindParam(':hospRegion', $hospRegistration['pHospRegion']);
        $stmt->bindParam(':hospZipCode', $hospRegistration['pHospZipCode']);
        $stmt->bindParam(':hospLhio', $hospRegistration['pHospLhio']);
        $stmt->bindParam(':sector', strtoupper($hospRegistration['pHospSector']));
        $stmt->bindParam(':hospEmail', $hospRegistration['pHospEmailAdd']);
        $stmt->bindParam(':hospTelNo', trim($hospRegistration['pHospTelNo']));
        $stmt->bindParam(':userid', trim($hospRegistration['pUserId']));
        $stmt->bindParam(':userPassword', trim($hospRegistration['pUserPassword']));
        $stmt->bindParam(':userEmpId', trim(strtoupper($hospRegistration['pUserEmpID'])));
        $stmt->bindParam(':userLname', trim(strtoupper($hospRegistration['pUserLname'])));
        $stmt->bindParam(':userFname', trim(strtoupper($hospRegistration['pUserFname'])));
        $stmt->bindParam(':userMname', trim(strtoupper($hospRegistration['pUserMname'])));
        $stmt->bindParam(':userExtname', trim(strtoupper($hospRegistration['pUserExtName'])));
        $stmt->bindParam(':userSex', $hospRegistration['pUserSex']);
        $stmt->bindParam(':userDoB',date('Y-m-d', strtotime($hospRegistration['pUserDoB'])));
        $stmt->bindParam(':userKey',$hospRegistration['pHciKey']);

        $stmt->execute();

        $conn->commit();

        echo '<script>alert("Successfully saved!");window.location="index.php";</script>';

    } catch (PDOException $e) {
        $conn->rollback();
        echo '<script>alert("Error: User Name is already in use.");</script>';
    }

    $conn = null;
}
/*Insert data for Enlistment/Registration Module*/
/*Insert data in Enlistment/Registration table*/
function savePatientRegistration($enlistDetails){
    $ini = parse_ini_file("config.ini");
    $pUserId = $_SESSION['pUserID'];

    if($enlistDetails['pPatientType']== 'MM'){
        $enlistDetails['pMemberPIN'] = $enlistDetails['pPatientPIN'];
        $enlistDetails['pMemberLastName']=strtoupper($enlistDetails['pPatientLastName']);
        $enlistDetails['pMemberFirstName']=strtoupper($enlistDetails['pPatientFirstName']);
        $enlistDetails['pMemberMiddleName']=strtoupper($enlistDetails['pPatientMiddleName']);
        $enlistDetails['pMemberSuffix']=strtoupper($enlistDetails['pPatientSuffix']);
        $enlistDetails['pMemberDateOfBirth']=$enlistDetails['pPatientDateOfBirth'];
        $enlistDetails['pMemberSex']=$enlistDetails['pPatientSexX'];
    }

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $pCaseNo = generateTransNo('CASE_NO');
        $pTransNo = generateTransNo('ENLIST_NO');
        $pXPSmodule = "K"; //KONSULTA       
        $pPatientPin=$enlistDetails['pPatientPIN'];
        $pMemPin=$enlistDetails['pMemberPIN'];
        $pEffectivityYear=date('Y', strtotime($enlistDetails['pEnlistmentDate']));

        $checkPxRecord = checkPatientRecordExist($pPatientPin, $pEffectivityYear, $pXPSmodule);
        $checkMemberAssigned = checkPatientRecordAssigned($pPatientPin, $pEffectivityYear);

        if($checkPxRecord == false) {

            $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_ENLIST (
                                  CASE_NO, TRANS_NO, TRANS_DATE, ACCRE_NO,  
                                  PX_TYPE, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, PX_PIN, PX_DOB, PX_SEX, 
                                  MEM_PIN, MEM_LNAME, MEM_FNAME, MEM_MNAME, MEM_EXTNAME, MEM_DOB, 
                                  ENLIST_DATE, PACKAGE_TYPE, CREATED_BY, EFF_YEAR, WITH_CONSENT, 
                                  MEM_SEX, XPS_MODULE, DATE_ADDED,
                                  PX_MOBILE_NO, PX_LANDLINE_NO) 
                                    VALUES(:caseNo, :transNo, NOW(), :accreNo, 
                                            :pxType, :pxLname, :pxFname, :pxMname, :pxExtname, :pxPin, :pxDob, :pxSex, 
                                            :memPin, :memLname, :memFname, :memMname, :memExtname, :memDob, 
                                    :enlistDate, :enlistType, :createdBY, :effYear, :withConsent, 
                                    :memSex, :xpsMod, NOW(),
                                    :mobileNumber, :landlineNumber)");

            $stmt->bindParam(':caseNo', $pCaseNo);
            $stmt->bindParam(':transNo',$pTransNo);
            $stmt->bindParam(':enlistDate', date('Y-m-d', strtotime($enlistDetails['pEnlistmentDate'])));
            $stmt->bindParam(':accreNo', $enlistDetails['pAccreNum']);
            $stmt->bindParam(':pxType', $enlistDetails['pPatientType']);
            $stmt->bindParam(':pxLname', strtoupper($enlistDetails['pPatientLastName']));
            $stmt->bindParam(':pxFname', strtoupper($enlistDetails['pPatientFirstName']));
            $stmt->bindParam(':pxMname', strtoupper($enlistDetails['pPatientMiddleName']));
            $stmt->bindParam(':pxExtname', strtoupper($enlistDetails['pPatientSuffix']));
            $stmt->bindParam(':pxPin', $enlistDetails['pPatientPIN']);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($enlistDetails['pPatientDateOfBirth'])));
            $stmt->bindParam(':pxSex', $enlistDetails['pPatientSexX']);            
            $stmt->bindParam(':memPin', $enlistDetails['pMemberPIN']);
            $stmt->bindParam(':memLname', strtoupper($enlistDetails['pMemberLastName']));
            $stmt->bindParam(':memFname', strtoupper($enlistDetails['pMemberFirstName']));
            $stmt->bindParam(':memMname', strtoupper($enlistDetails['pMemberMiddleName']));
            $stmt->bindParam(':memExtname', strtoupper($enlistDetails['pMemberSuffix']));
            $stmt->bindParam(':memDob', date('Y-m-d', strtotime($enlistDetails['pMemberDateOfBirth'])));           
            $stmt->bindParam(':enlistType', $enlistDetails['pEnlistType']);
            $stmt->bindParam(':createdBY', strtoupper($pUserId));
            $stmt->bindParam(':effYear', date('Y', strtotime($enlistDetails['pEnlistmentDate'])));
            $stmt->bindParam(':withConsent', $enlistDetails['pWithConsentValue']);           
            $stmt->bindParam(':memSex', $enlistDetails['pMemberSex']);           
            $stmt->bindParam(':xpsMod', $pXPSmodule);
            $stmt->bindParam(':mobileNumber', $enlistDetails['pPatientContactMobileNumber']);
            $stmt->bindParam(':landlineNumber', $enlistDetails['pPatientLandlineNumber']);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_HIST_ENLIST (
                              HIST_NO, CASE_NO, TRANS_NO, TRANS_DATE, ACCRE_NO, 
                              PX_TYPE, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, PX_PIN, PX_DOB, PX_CONTACTNO, PX_SEX,
                              MEM_PIN, MEM_LNAME, MEM_FNAME, MEM_MNAME, MEM_EXTNAME, MEM_DOB, 
                              ENLIST_DATE, PACKAGE_TYPE, CREATED_BY, EFF_YEAR, WITH_CONSENT, 
                              MEM_SEX, UPD_CNT, XPS_MODULE) 
                                VALUES(:histNo, :caseNo, :transNo, NOW(), :accreNo, 
                                :pxType, :pxLname, :pxFname, :pxMname, :pxExtname, :pxPin, :pxDob, :pxContactNo, :pxSex, 
                                :memPin, :memLname, :memFname, :memMname, :memExtname, :memDob,
                                :enlistDate, :enlistType, :createdBY, :effYear, :withConsent, 
                                :memSex, :updCnt, :xpsMod)");

            $stmt->bindParam(':histNo', generateTransNo('HIST_ENO'));
            $stmt->bindParam(':caseNo', $pCaseNo);
            $stmt->bindParam(':transNo',$pTransNo);
            $stmt->bindParam(':enlistDate', date('Y-m-d', strtotime($enlistDetails['pEnlistmentDate'])));            
            $stmt->bindParam(':accreNo', $enlistDetails['pAccreNum']);            
            $stmt->bindParam(':pxType', $enlistDetails['pPatientType']);
            $stmt->bindParam(':pxLname', strtoupper($enlistDetails['pPatientLastName']));
            $stmt->bindParam(':pxFname', strtoupper($enlistDetails['pPatientFirstName']));
            $stmt->bindParam(':pxMname', strtoupper($enlistDetails['pPatientMiddleName']));
            $stmt->bindParam(':pxExtname', strtoupper($enlistDetails['pPatientSuffix']));
            $stmt->bindParam(':pxPin', $enlistDetails['pPatientPIN']);
            $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($enlistDetails['pPatientDateOfBirth'])));
            $stmt->bindParam(':pxContactNo', $enlistDetails['pPatientContactNumber']);
            $stmt->bindParam(':pxSex', $enlistDetails['pPatientSexX']);           
            $stmt->bindParam(':memPin', $enlistDetails['pMemberPIN']);
            $stmt->bindParam(':memLname', strtoupper($enlistDetails['pMemberLastName']));
            $stmt->bindParam(':memFname', strtoupper($enlistDetails['pMemberFirstName']));
            $stmt->bindParam(':memMname', strtoupper($enlistDetails['pMemberMiddleName']));
            $stmt->bindParam(':memExtname', strtoupper($enlistDetails['pMemberSuffix']));
            $stmt->bindParam(':memDob', date('Y-m-d', strtotime($enlistDetails['pMemberDateOfBirth'])));            
            $stmt->bindParam(':enlistType', $enlistDetails['pEnlistType']);
            $stmt->bindParam(':createdBY', strtoupper($pUserId));
            $stmt->bindParam(':effYear', date('Y'));
            $stmt->bindParam(':withConsent', $enlistDetails['pWithConsentValue']);
            $stmt->bindParam(':memSex', $enlistDetails['pMemberSex']);            
            $stmt->bindParam(':updCnt', $enlistDetails['pUpdCntEnlist']);
            $stmt->bindParam(':xpsMod', $pXPSmodule);

            $stmt->execute();
            $conn->commit();

            echo '<script>alert("Successfully saved!");window.location="registration_search.php?";</script>';

        }
        else{
            echo '<script>alert("Client record already exist!");window.location="registration_search.php";</script>';
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        $conn->rollback();
        echo '<script>alert("Error: '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

/*Update data in Enlistment/Registration table*/
function updatePatientRegistration($enlistDetails){
    $ini = parse_ini_file("config.ini");
    $pUserId = $_SESSION['pUserID'];

    try {
        $conn = new PDO("mysql:host=" . $ini["DBSERVER"] . ";dbname=" . $ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $stmt = $conn->prepare("UPDATE " . $ini['EPCB'] . ".TSEKAP_TBL_ENLIST 
                                    SET ACCRE_NO = :accreNo,
                                        PX_TYPE = :pxType,
                                        PX_LNAME = :pxLname,
                                        PX_FNAME = :pxFname,
                                        PX_MNAME = :pxMname,
                                        PX_EXTNAME = :pxExtname,
                                        PX_PIN = :pxPin,
                                        PX_DOB = :pxDob,
                                        PX_SEX = :pxSex,
                                        MEM_PIN = :memPin,
                                        MEM_LNAME = :memLname,
                                        MEM_FNAME = :memFname,
                                        MEM_MNAME = :memMname,
                                        MEM_EXTNAME = :memExtname,
                                        MEM_DOB = :memDob,          
                                        ENLIST_DATE = :enlistDate,
                                        CREATED_BY = :createdBY,
                                        EFF_YEAR = :effYear,
                                        MEM_SEX = :memSex,
                                        PX_MOBILE_NO = :mobileno,
                                        PX_LANDLINE_NO = :landlineno,
                                        UPD_CNT = :updcnt               
                                    WHERE CASE_NO = :caseNo");

        $stmt->bindParam(':caseNo', $enlistDetails['pCaseNum']);
        $stmt->bindParam(':enlistDate', date('Y-m-d', strtotime($enlistDetails['pEnlistmentDate'])));
        $stmt->bindParam(':accreNo', $enlistDetails['pAccreNum']);
        $stmt->bindParam(':pxType', $enlistDetails['pPatientType']);
        $stmt->bindParam(':pxLname', strtoupper($enlistDetails['pPatientLastName']));
        $stmt->bindParam(':pxFname', strtoupper($enlistDetails['pPatientFirstName']));
        $stmt->bindParam(':pxMname', strtoupper($enlistDetails['pPatientMiddleName']));
        $stmt->bindParam(':pxExtname', strtoupper($enlistDetails['pPatientSuffix']));
        $stmt->bindParam(':pxPin', $enlistDetails['pPatientPIN']);
        $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($enlistDetails['pPatientDateOfBirth'])));
        $stmt->bindParam(':pxSex', $enlistDetails['pPatientSexX']);
        $stmt->bindParam(':memPin', $enlistDetails['pMemberPIN']);
        $stmt->bindParam(':memLname', strtoupper($enlistDetails['pMemberLastName']));
        $stmt->bindParam(':memFname', strtoupper($enlistDetails['pMemberFirstName']));
        $stmt->bindParam(':memMname', strtoupper($enlistDetails['pMemberMiddleName']));
        $stmt->bindParam(':memExtname', strtoupper($enlistDetails['pMemberSuffix']));
        $stmt->bindParam(':memDob', date('Y-m-d', strtotime($enlistDetails['pMemberDateOfBirth'])));
        $stmt->bindParam(':createdBY', strtoupper($pUserId));
        $stmt->bindParam(':effYear', date('Y'));
        $stmt->bindParam(':memSex', $enlistDetails['pMemberSex']);
        $stmt->bindParam(':mobileno', $enlistDetails['pPatientContactMobileNumber']);
        $stmt->bindParam(':landlineno', $enlistDetails['pPatientLandlineNumber']);
        $stmt->bindParam(':updcnt', $enlistDetails['pUpdCntEnlist']);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO " . $ini['EPCB'] . ".TSEKAP_HIST_ENLIST (
                              HIST_NO, CASE_NO, TRANS_NO, TRANS_DATE, HCI_NO, ACCRE_NO, 
                              PX_TYPE, PX_LNAME, PX_FNAME, PX_MNAME, PX_EXTNAME, PX_PIN, PX_DOB, PX_SEX, 
                              MEM_PIN, MEM_LNAME, MEM_FNAME, MEM_MNAME, MEM_EXTNAME, MEM_DOB,
                              ENLIST_DATE, PACKAGE_TYPE, CREATED_BY, EFF_YEAR, WITH_CONSENT, 
                              MEM_SEX, UPD_CNT) 
                                VALUES(:histNo, :caseNo, :transNo, NOW(), :hciNo, :accreNo, :pxType, :pxLname, :pxFname, :pxMname, :pxExtname, :pxPin, :pxDob, :pxSex,:memPin, :memLname, :memFname, :memMname, :memExtname, :memDob, 
                                :enlistDate, :enlistType, :createdBY, :effYear, :withConsent, :memSex, :updCnt)");

        $stmt->bindParam(':histNo', generateTransNo('HIST_ENO'));
        $stmt->bindParam(':caseNo', $enlistDetails['pCaseNum']);
        $stmt->bindParam(':transNo', $enlistDetails['pTransNum']);
        $stmt->bindParam(':enlistDate', date('Y-m-d', strtotime($enlistDetails['pEnlistmentDate'])));
        $stmt->bindParam(':hciNo', $enlistDetails['pHCInum']);
        $stmt->bindParam(':accreNo', $enlistDetails['pAccreNum']);
        $stmt->bindParam(':pxType', $enlistDetails['pPatientType']);
        $stmt->bindParam(':pxLname', strtoupper($enlistDetails['pPatientLastName']));
        $stmt->bindParam(':pxFname', strtoupper($enlistDetails['pPatientFirstName']));
        $stmt->bindParam(':pxMname', strtoupper($enlistDetails['pPatientMiddleName']));
        $stmt->bindParam(':pxExtname', strtoupper($enlistDetails['pPatientSuffix']));
        $stmt->bindParam(':pxPin', $enlistDetails['pMemberPIN']);
        $stmt->bindParam(':pxDob', date('Y-m-d', strtotime($enlistDetails['pPatientDateOfBirth'])));
        $stmt->bindParam(':pxSex', $enlistDetails['pPatientSexX']);
        $stmt->bindParam(':memPin', $enlistDetails['pPatientPIN']);
        $stmt->bindParam(':memLname', strtoupper($enlistDetails['pMemberLastName']));
        $stmt->bindParam(':memFname', strtoupper($enlistDetails['pMemberFirstName']));
        $stmt->bindParam(':memMname', strtoupper($enlistDetails['pMemberMiddleName']));
        $stmt->bindParam(':memExtname', strtoupper($enlistDetails['pMemberSuffix']));
        $stmt->bindParam(':memDob', date('Y-m-d', strtotime($enlistDetails['pMemberDateOfBirth'])));
        $stmt->bindParam(':enlistType', $enlistDetails['pEnlistType']);
        $stmt->bindParam(':createdBY', $pUserId);
        $stmt->bindParam(':effYear', date('Y'));
        $stmt->bindParam(':withConsent', $enlistDetails['pWithConsent']);
        $stmt->bindParam(':memSex', $enlistDetails['pMemberSex']);
        $stmt->bindParam(':updCnt', $enlistDetails['pUpdCntEnlist']);
        $stmt->execute();

        $conn->commit();

        echo '<script>alert("Successfully saved!");window.location="registration_search.php";</script>';

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        $conn->rollback();
        echo '<script>alert("Error: '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

function checkPatientRecordExist($pPatientPin,$pEffectivityYear,$pXPSmodule){
    $ini = parse_ini_file("config.ini");
    try {
        $conn = new PDO("mysql:host=" . $ini["DBSERVER"] . ";dbname=" . $ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                        WHERE PX_PIN = :pxPin
                                          AND EFF_YEAR = :pxEffYear
                                          AND XPS_MODULE = :pModule");

        $stmt->bindParam(':pxPin', $pPatientPin);
        $stmt->bindParam(':pxEffYear', $pEffectivityYear);
        $stmt->bindParam(':pModule', $pXPSmodule);

        $stmt->execute();
        $conn->commit();

        if($row = $stmt->fetchAll(PDO::FETCH_ASSOC)){
            return true;
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        $conn->rollback();
        echo '<script>alert("Error: '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

function checkPatientRecordAssigned($pMemberPin, $pEffectivityYear){
    $ini = parse_ini_file("config.ini");
    try {
        $conn = new PDO("mysql:host=" . $ini["DBSERVER"] . ";dbname=" . $ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN
                                        WHERE ASSIGNED_PIN = :px_pin
                                          AND EFF_YEAR = :effYear");

        $stmt->bindParam(':px_pin', $pMemberPin);
        $stmt->bindParam(':effYear', $pEffectivityYear);

        $stmt->execute();
        $conn->commit();

        if($row = $stmt->fetchAll(PDO::FETCH_ASSOC)){
            return true;
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        $conn->rollback();
        echo '<script>alert("Error: '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

/*Insert date for Follow-up Medicine*/
function saveFollowUpMedicine($soapInfo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=" . $ini["DBSERVER"] . ";dbname=" . $ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        session_start();
        $pUserId = $_SESSION['pUserID'];
        $pHciNo = $_SESSION['pHciNum'];
        $pXPSmodule = "SOAP"; /*SOAP - Consultation*/

        /*Start Consultation Patient Details*/
        $pCaseNo = $soapInfo['pCaseNo'];
        $pPatientPin = $soapInfo['pPatientPIN'];
        $pPatientType = $soapInfo['pPatientType'];
        $pMemPin = $soapInfo['pMemPin'];
        $pEffYear = $soapInfo['pEffYear'];
        $pSoapDate = $soapInfo['pSOAPDate'];
        $pSoapOtp = $soapInfo['pSoapOTP'];
        $pwithOtp=$soapInfo['walkedInChecker'];

        $getUpdCnt = 0;
        $pSoapCoPay = 0;
        $pTransNo = generateTransNo('SOAP_NO'); //automatically generated
        insertConsultationPatientInfo($pCaseNo, $pTransNo, $pHciNo, $pPatientPin, $pPatientType, $pMemPin, $pSoapDate, $pUserId, $pEffYear, $pSoapOtp, $getUpdCnt, $pXPSmodule,$pwithOtp,$pSoapCoPay);
        /*Start Medicine*/
        /*Medicine*/
        $pDoctorName = $soapInfo['pPrescDoctor'];
        $pDrugCodeMeds = $soapInfo['pDrugCodeMeds'];
        $pGenCodeMeds = $soapInfo['pGenCodeMeds'];
        $pSaltMed = $soapInfo['pSaltCodeMeds'];
        $pStrengthMeds = $soapInfo['pStrengthCodeMeds'];
        $pFormMeds = $soapInfo['pFormCodeMeds'];
        $pUnitMed = $soapInfo['pUnitCodeMeds'];
        $pPackageMeds = $soapInfo['pPackageCodeMeds'];
        $pQuantityMeds = $soapInfo['pQtyMeds'];
        $pUnitPriceMeds = $soapInfo['pUnitPriceMeds'];
        $pCopayMeds = "";
        $pTotalAmtPriceMeds = $soapInfo['pTotalPriceMeds'];
        $pInsQtyMeds = $soapInfo['pQtyInsMeds'];
        $pInsStrengthMeds = $soapInfo['pStrengthInsMeds'];
        $pInsFreqMeds = $soapInfo['pFrequencyInsMeds'];
        $pGenericOtherMeds = $soapInfo['pOtherMeds'];

        //Dispensing
        $pIsMedsDispensed = $soapInfo['pIsDispensed'];
        $pChkMedsDispensedDate = $soapInfo['pDispensedDate'];       
        $pMedsDispensingPersonnel = $soapInfo['pDispensingPersonnel'];

        $pMedsCategory = "";

        $pOthMedDrugGroup = $soapInfo['pOthMedDrugGrouping[]']; //v01.04.00.202201

        //processing
        $pApplicable = $soapInfo['medsStatus'];

        if($pApplicable == "Y") {    
            for ($i = 0; $i < count($pIsMedsDispensed); $i++) {
                if($pChkMedsDispensedDate[$i] != ""){
                    $pMedsDispensedDate = date('Y-m-d', strtotime($pChkMedsDispensedDate[$i]));
                } else {
                    $pMedsDispensedDate = NULL;
                }          

                insertMedicine($pDrugCodeMeds[$i], $pGenCodeMeds[$i], $pStrengthMeds[$i], $pFormMeds[$i], $pPackageMeds[$i],
                    $pQuantityMeds[$i], $pUnitPriceMeds[$i], $pCopayMeds, $pTotalAmtPriceMeds[$i], $pInsQtyMeds[$i], $pInsStrengthMeds[$i], $pInsFreqMeds[$i],
                    $pCaseNo, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, $pDoctorName, $pApplicable,$pGenericOtherMeds[$i], $pSaltMed[$i], $pUnitMed[$i],"", $pIsMedsDispensed[$i], $pMedsDispensedDate,$pMedsDispensingPersonnel,$pMedsCategory,$pOthMedDrugGroup);
            }
        }
        else{
            insertMedicine("NOMED0000000000000000000000000", "NOMED", "00000", "00000", "00000",
                NULL, NULL, NULL,NULL, NULL, "N/A", "N/A",
                $pCaseNo, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, NULL, "N", "","00000", "00000", "", "N",NULL,NULL,"-","");
        }
        /*End Medicine*/

        $conn->commit();

        echo '<script>alert("Successfully saved!");window.location="followup_meds_search.php";</script>';


    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: FollowUpMeds - " . $e->getMessage();
        echo '<script>alert("Error: FollowUpMeds - ' . $e->getMessage() . '");</script>';
    }
}

/*Insert date for Follow-up Medicine*/
function saveMedicines($soapInfo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=" . $ini["DBSERVER"] . ";dbname=" . $ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_AUTOCOMMIT, false);

        // Begin transaction
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
        }

         // Confirm transaction is active
        if ($conn->inTransaction()) {

            session_start();
            $pUserId = $_SESSION['pUserID'];
            $pHciNo = $_SESSION['pHciNum'];
            $pXPSmodule = "SOAP"; /*SOAP - Consultation*/

            /*Start Consultation Patient Details*/
            $pCaseNo = $soapInfo['pCaseNo'];
            $pTransNo = $soapInfo['pConsultTransNo'];
            $pPatientPin = $soapInfo['pPatientPIN'];
            $pPatientType = $soapInfo['pPatientType'];
            $pMemPin = $soapInfo['pMemPin'];
            $pEffYear = $soapInfo['pEffYear'];
            $pSoapDate = $soapInfo['pSOAPDate'];
            $pSoapOtp = $soapInfo['pSoapOTP'];
            $pwithOtp=$soapInfo['walkedInChecker'];

            $getUpdCnt = 0;
            $pSoapCoPay = 0;

            /*Start Medicine*/
            /*Medicine*/
            $pDoctorName = $soapInfo['pPrescDoctor'];
            $pDrugCodeMeds = $soapInfo['pDrugCodeMeds'];
            $pGenCodeMeds = $soapInfo['pGenCodeMeds'];
            $pSaltMed = $soapInfo['pSaltCodeMeds'];
            $pStrengthMeds = $soapInfo['pStrengthCodeMeds'];
            $pFormMeds = $soapInfo['pFormCodeMeds'];
            $pUnitMed = $soapInfo['pUnitCodeMeds'];
            $pPackageMeds = $soapInfo['pPackageCodeMeds'];
            $pQuantityMeds = $soapInfo['pQtyMeds'];
            $pUnitPriceMeds = $soapInfo['pUnitPriceMeds'];
            $pCopayMeds = "";
            $pTotalAmtPriceMeds = $soapInfo['pTotalPriceMeds'];
            $pInsQtyMeds = $soapInfo['pQtyInsMeds'];
            $pInsStrengthMeds = $soapInfo['pStrengthInsMeds'];
            $pInsFreqMeds = $soapInfo['pFrequencyInsMeds'];
            $pGenericOtherMeds = $soapInfo['pOtherMeds'];

            //Dispensing
            $pIsMedsDispensed = $soapInfo['pIsDispensed'];
            $pChkMedsDispensedDate = $soapInfo['pDispensedDate'];       
            $pMedsDispensingPersonnel = $soapInfo['pDispensingPersonnel'];

            $pMedsCategory = "";

            $pOthMedDrugGroup = $soapInfo['pOthMedDrugGrouping[]']; //v01.04.00.202201

            //processing
            $pApplicable = $soapInfo['medsStatus'];

            /*Advice */        
            if ($soapInfo['advice_remarks'] != NULL || $soapInfo['advice_remarks'] != ""){
                $pAdviceRemarks = $soapInfo['advice_remarks'];
            } else {
                $pAdviceRemarks = "NOT APPLICABLE";
            }
            insertAdvice($conn, $pAdviceRemarks, $pUserId, $pTransNo, $getUpdCnt);

            if($pApplicable == "Y") {    
                for ($i = 0; $i < count($pIsMedsDispensed); $i++) {
                    if($pChkMedsDispensedDate[$i] != ""){
                        $pMedsDispensedDate = date('Y-m-d', strtotime($pChkMedsDispensedDate[$i]));
                    } else {
                        $pMedsDispensedDate = NULL;
                    }          

                    insertMedicine($conn, $pDrugCodeMeds[$i], $pGenCodeMeds[$i], $pStrengthMeds[$i], $pFormMeds[$i], $pPackageMeds[$i],
                        $pQuantityMeds[$i], $pUnitPriceMeds[$i], $pCopayMeds, $pTotalAmtPriceMeds[$i], $pInsQtyMeds[$i], $pInsStrengthMeds[$i], $pInsFreqMeds[$i],
                        $pCaseNo, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, $pDoctorName, $pApplicable,$pGenericOtherMeds[$i], $pSaltMed[$i], $pUnitMed[$i],"", $pIsMedsDispensed[$i], $pMedsDispensedDate,$pMedsDispensingPersonnel,$pMedsCategory,$pOthMedDrugGroup);
                }
            }
            else{
                insertMedicine($conn, "NOMED0000000000000000000000000", "NOMED", "00000", "00000", "00000",
                    NULL, NULL, NULL,NULL, NULL, "N/A", "N/A",
                    $pCaseNo, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, NULL, "N", "","00000", "00000", "", "N",NULL,NULL,"-","");
            }
            /*End Medicine*/

            $conn->commit();

            echo '<script>alert("Successfully saved!");window.location="medicine_search.php";</script>';
        } else {
            throw new Exception("Medicines : Failed to start transaction.");
        }


    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        //echo "Error: saveMedicines - " . $e->getMessage();
        echo '<script>alert("Error in Medicines : ' . $e->getMessage() . '");</script>';
    } finally {
        $conn = null;
    }
}


/*Insert data for Consultation Module*/
function saveConsultationInfo($soapInfo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Begin transaction
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
        }

        // Confirm transaction is active
        if ($conn->inTransaction()) {
            session_start();
            $pUserId = $_SESSION['pUserID'];
            $pHciNo = $_SESSION['pHciNum'];
            $pXPSmodule = "SOAP"; /*SOAP - Consultation*/

            /*Start Consultation Patient Details*/
            $pCaseNo=$soapInfo['pCaseNo'];
            $pPatientPin=$soapInfo['pPatientPIN'];
            $pPatientType=$soapInfo['pPatientType'];
            $pMemPin=$soapInfo['pMemPin'];
            $pEffYear=$soapInfo['pEffYear'];
            $pSoapDate=$soapInfo['pSOAPDate'];
            $pSoapCoPay=$soapInfo['pCoPayment'];
            
            // $pwithOtp=$soapInfo['walkedInChecker']; //pIsWalkedIn in DTD

            $pwithOtp = "Y";
            $pSoapOtp = "WALKEDIN";

            if(isset($soapInfo['saveClientSoap'])){
                $getUpdCnt = $soapInfo['pUpdCntSoap'];
                $pTransNo =generateTransNo('SOAP_NO'); //automatically generated
                insertConsultationPatientInfo($conn, $pCaseNo, $pTransNo, $pHciNo, $pPatientPin, $pPatientType, $pMemPin, $pSoapDate, $pUserId, $pEffYear, $pSoapOtp, $getUpdCnt, $pXPSmodule,$pwithOtp,$pSoapCoPay);
            }

            if(isset($soapInfo['updateClientSoap'])){
                $getUpdCnt = $soapInfo['pUpdCntSoap'];
                $pTransNo = $soapInfo['pSoapTransNum'];

                updateConsultationPatientInfo($conn, $pCaseNo, $pTransNo, $pHciNo, $pPatientPin, $pPatientType, $pMemPin, $pSoapDate, $pUserId, $pEffYear, $pSoapOtp, $getUpdCnt);
            }
            /*End Consultation Patient Details*/

            /*Start Subjective/ History of Illness*/
            $pChiefComplaint=NULL;
            $pSymptoms=$soapInfo['pChiefComplaint'];
            $pIllnessHist=$soapInfo['pIllnessHistory'];
            $pOtherComplaint=$soapInfo['pOtherChiefComplaint'];
            $pPainSite=$soapInfo['pPainSite'];
            
            insertSubjectiveHistory($conn, $pUserId, $pTransNo, $pChiefComplaint, $pIllnessHist, $pOtherComplaint, $getUpdCnt,$pSymptoms,$pPainSite);
            /*End Subjective/ History of Illness*/

            /*Start Objective/Physical Examination*/
            /*Part 1: Pertinent Examination*/
            $pSystolic=$soapInfo['pe_bp_u'];
            $pDiastolic=$soapInfo['pe_bp_l'];
            $pHr=$soapInfo['pe_hr'];
            $pRr=$soapInfo['pe_rr'];
            
            if ($soapInfo['txtPhExHeightCm'] != null) {
                $pHeight = $soapInfo['txtPhExHeightCm'];
            } else {
                $pHeight = 0;
            }
            $pWeight=$soapInfo['txtPhExWeightKg'];
            //$pWaist=$soapInfo['pe_waist_cm'];
            $pTemp = $soapInfo['pe_temp'];
            $pVision=NULL;
            $pLength=$soapInfo['txtPhExLengthCm'];
            $pHeadCirc=$soapInfo['txtPhExHeadCircCm'];
            //konsulta
            $pLeftEyeVision=$soapInfo['pe_visual_acuityL'];
            $pRightEyeVision=$soapInfo['pe_visual_acuityR'];
            $pWaist=$soapInfo['txtPhExBodyCircWaistCm'];
            $pHip=$soapInfo['txtPhExBodyCircHipsCm'];
            $pLimbs=$soapInfo['txtPhExBodyCircLimbsCm'];
            $pBMI=$soapInfo['txtPhExBMI'];
            //pZScore=$soapInfo['txtPhExZscoreCm'];
            $pZScore="";
            $pSkinThickness=$soapInfo['txtPhExSkinfoldCm'];

            insertObjectivePhysicalExam($conn, $pSystolic, $pDiastolic, $pHr, $pRr, $pHeight, $pWeight, $pTemp, $pUserId, $pTransNo, $pVision, $pLength, $pHeadCirc, $getUpdCnt,$pLeftEyeVision,$pRightEyeVision,$pWaist,$pHip,$pLimbs,$pBMI,$pZScore,$pSkinThickness);

            /*Part 2: Pertinent Findings per System*/
            $pSkin = $soapInfo['skinExtremities'];
            $pGenito = $soapInfo['genitourinary'];
            $pRectal = $soapInfo['rectal'];
            $pHeent = $soapInfo['heent'];
            $pChest = $soapInfo['chest'];
            $pHeart = $soapInfo['heart'];
            $pAbdomen = $soapInfo['abdomen'];
            $pNeuro = $soapInfo['neuro'];

            /*A. Heent*/
            for ($i = 0; $i < count($pHeent); $i++) {
                if ($pHeent[$i] != '') {
                    insertPhysicalExamMisc($conn, null, $pHeent[$i], null, null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*B. Chest/Lungs*/
            for ($i = 0; $i < count($pChest); $i++) {
                if ($pChest[$i] != '') {
                    insertPhysicalExamMisc($conn, null, null, $pChest[$i], null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*C. Heart*/
            for ($i = 0; $i < count($pHeart); $i++) {
                if ($pHeart[$i] != '') {
                    insertPhysicalExamMisc($conn, null, null, null, $pHeart[$i], null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*D. Abdomen*/
            for ($i = 0; $i < count($pAbdomen); $i++) {
                if ($pAbdomen[$i] != '') {
                    insertPhysicalExamMisc($conn, null, null, null, null, $pAbdomen[$i], null, null, null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*E. Genitourinary*/
            for ($i = 0; $i < count($pGenito); $i++) {
                if ($pGenito[$i] != '') {
                    insertPhysicalExamMisc($conn, null, null, null, null, null, null, $pGenito[$i], null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*F. Digital Rectal Examination*/
            for ($i = 0; $i < count($pRectal); $i++) {
                if ($pRectal[$i] != '') {
                    insertPhysicalExamMisc($conn, null, null, null, null, null, null, null, $pRectal[$i], $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*G. Skin/Extremities*/
            for ($i = 0; $i < count($pSkin); $i++) {
                if ($pSkin[$i] != '') {
                    insertPhysicalExamMisc($conn, $pSkin[$i], null, null, null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*H. Neurological*/
            for ($i = 0; $i < count($pNeuro); $i++) {
                if ($pNeuro[$i] != '') {
                    insertPhysicalExamMisc($conn, null, null, null, null, null, $pNeuro[$i], null, null, $pTransNo, $pUserId, $getUpdCnt);
                }
            }

            /*Part 3 Remarks*/
            $pHeentRemarks = $soapInfo['heent_remarks'];
            $pChestRemarks = $soapInfo['chest_lungs_remarks'];
            $pHeartRemarks = $soapInfo['heart_remarks'];
            $pAbdomenRemarks = $soapInfo['abdomen_remarks'];
            $pGenitoRemarks = $soapInfo['gu_remarks'];
            $pRectalRemarks = $soapInfo['rectal_remarks'];
            $pSkinExtremitiesRemarks = $soapInfo['skinExtremities_remarks'];
            $pNeuroRemarks = $soapInfo['neuro_remarks'];

            insertPhysicalExamMiscRemarks($conn, $pHeentRemarks, $pChestRemarks, $pHeartRemarks, $pAbdomenRemarks, $pGenitoRemarks, $pRectalRemarks, $pSkinExtremitiesRemarks, $pNeuroRemarks, $pTransNo, $pUserId, $getUpdCnt);
            /*End Objective/Physical Examination*/

            /*Start Assessment/Diagnosis*/
            $pDiagnosis = $soapInfo['diagnosis'];
            for ($i = 0; $i < count($pDiagnosis); $i++) {
                insertAssessmentDiagnosis($conn, $pUserId, $pTransNo, $pDiagnosis[$i], ($i+1), $getUpdCnt);
            }
            /*End Assessment/Diagnosis*/

            /*Start Plan/Management*/
            /*Diagnosis Examination*/
            $pDiagnostic = $soapInfo['diagnostic'];
            // if($pDiagnostic != NULL) {
                for  ($i = 0; $i < count($soapInfo['diagnostic']); $i++) {

                    //cbc
                    if($pDiagnostic[$i] == "1"){
                        if($soapInfo['diagnostic_1_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_1_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_1_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_1_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //xray
                    if($pDiagnostic[$i] == "4"){
                        if($soapInfo['diagnostic_4_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_4_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_4_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_4_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //creatinine
                    if($pDiagnostic[$i] == "8"){
                        if($soapInfo['diagnostic_8_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_8_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_8_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_8_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //ecg
                    if($pDiagnostic[$i] == "9"){
                        if($soapInfo['diagnostic_9_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_9_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_9_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_9_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //fbs
                    if($pDiagnostic[$i] == "7"){
                        if($soapInfo['diagnostic_7_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_7_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_7_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_7_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //rbs
                    if($pDiagnostic[$i] == "19"){
                        if($soapInfo['diagnostic_19_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_19_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_19_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_19_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //fobt
                    if($pDiagnostic[$i] == "15"){
                        if($soapInfo['diagnostic_15_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_15_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_15_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_15_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //fecalysis
                    if($pDiagnostic[$i] == "3"){
                        if($soapInfo['diagnostic_3_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_3_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_3_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_3_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //hba1c
                    if($pDiagnostic[$i] == "18"){
                        if($soapInfo['diagnostic_18_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_18_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_18_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_18_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //lipid profile
                    if($pDiagnostic[$i] == "6"){
                        if($soapInfo['diagnostic_6_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_6_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_6_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_6_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //ogtt
                    if($pDiagnostic[$i] == "14"){
                        if($soapInfo['diagnostic_14_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_14_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_14_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_14_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //papsmear
                    if($pDiagnostic[$i] == "13"){
                        if($soapInfo['diagnostic_13_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_13_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_13_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_13_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //ppdt
                    if($pDiagnostic[$i] == "17"){
                        if($soapInfo['diagnostic_17_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_17_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_17_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_17_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //sputum
                    if($pDiagnostic[$i] == "5"){
                        if($soapInfo['diagnostic_5_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_5_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_5_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_5_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //urinalysis
                    if($pDiagnostic[$i] == "2"){
                        if($soapInfo['diagnostic_2_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_2_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_2_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_2_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks="";
                    }

                    //others
                    if($pDiagnostic[$i] == "99"){
                        if($soapInfo['diagnostic_99_doctor'] != null) {
                            $pIsDrRecommended=$soapInfo['diagnostic_99_doctor'];
                        } else {
                            $pIsDrRecommended="X";
                        }
                    
                        if($soapInfo['diagnostic_99_patient'] != null) {
                            $pIsPxRemarks=$soapInfo['diagnostic_99_patient'];
                        } else {
                            $pIsPxRemarks="XX";
                        }

                        $pOthRemarks=$soapInfo['diagnostic_oth_remarks'];
                    }

                    insertDiagnosticExamination($conn, $pDiagnostic[$i], $pOthRemarks, $pUserId, $pTransNo, $getUpdCnt,$pIsDrRecommended,$pIsPxRemarks);
                }

            /*Management */
            $pManagement=$soapInfo['management'];
            $pOthMgmtRemarks="";

            if($pManagement != NULL) {
                for ($i = 0; $i < count($pManagement); $i++) {

                    if ($pManagement[$i] == 'X') {
                        $pOthMgmtRemarks=$soapInfo['management_oth_remarks'];
                    } else {
                        $pOthMgmtRemarks="";
                    }

                    insertManagement($conn, $pManagement[$i], $pUserId, $pTransNo, $pOthMgmtRemarks, $getUpdCnt);
                }
            }
            else{
                insertManagement($conn, "0", $pUserId, $pTransNo, $pOthMgmtRemarks, $getUpdCnt);
            }

    /*=============================================================================*/
            /*Start Medicine*/
            /*Medicine*/
            $pDoctorName = $soapInfo['pPrescDoctor'];
            $pDrugCodeMeds = $soapInfo['pDrugCodeMeds'];
            $pGenCodeMeds = $soapInfo['pGenCodeMeds'];
            $pSaltMed = $soapInfo['pSaltCodeMeds'];
            $pStrengthMeds = $soapInfo['pStrengthCodeMeds'];
            $pFormMeds = $soapInfo['pFormCodeMeds'];
            $pUnitMed = $soapInfo['pUnitCodeMeds'];
            $pPackageMeds = $soapInfo['pPackageCodeMeds'];
            $pQuantityMeds = $soapInfo['pQtyMeds'];
            $pUnitPriceMeds = $soapInfo['pUnitPriceMeds'];
            $pCopayMeds = "";
            $pTotalAmtPriceMeds = $soapInfo['pTotalPriceMeds'];
            $pInsQtyMeds = $soapInfo['pQtyInsMeds'];
            $pInsStrengthMeds = $soapInfo['pStrengthInsMeds'];
            $pInsFreqMeds = strtoupper($soapInfo['pFrequencyInsMeds']);
            $pGenericOtherMeds = $soapInfo['pOtherMeds'];

            $pMedsCategory = $soapInfo['pDrugCategory']; //v1.2.1.1

            $pOthMedDrugGroup = $soapInfo['pOthMedDrugGrouping']; //v01.04.00.202201


            //Dispensing
            $pIsMedsDispensed = $soapInfo['pIsDispensed'];
            $pChkMedsDispensedDate = $soapInfo['pDispensedDate'];       
            $pMedsDispensingPersonnel = $soapInfo['pDispensingPersonnel'];

            //processing
            $pApplicable = $soapInfo['medsStatus'];

            insertMedicine($conn, "NOMED0000000000000000000000000", "NOMED", "00000", "00000", "00000",
                NULL, NULL, NULL,NULL, NULL, "N/A", "N/A",
                $pCaseNo, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, NULL, "N", "","00000", "00000", "", "N",NULL,NULL,"-","");

       
            /*End Medicine*/

            $conn->commit();

            echo '<script>alert("Successfully Saved the Consultation Record."); window.location="consultation_list_of_all_patients.php";</script>';
        } else {
            throw new Exception("Consultation: Failed to start transaction.");
        }

    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        } 
        echo '<script>alert("Error in Consultation : '.$e->getMessage().'");</script>';
    } finally {
        $conn = null;
    }

}

/*Update Consultation - Patient Information Sub-module*/
function updateConsultationPatientInfo($conn, $pCaseNo, $pTransNo, $pHciNo, $pPatientPin, $pPatientType, $pMemPin, $pSoapDate, $pUserId, $pEffYear, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        // $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE ".$ini['EPCB'].".TSEKAP_TBL_SOAP
                                    SET HCI_NO = :hciNo,
                                        PX_PIN = :pxPin,
                                        PX_TYPE = :pxType,
                                        MEM_PIN = :memPin,
                                        SOAP_DATE = :soapDate,
                                        SOAP_BY = :soapBy,
                                        DATE_ADDED = NOW(),
                                        EFF_YEAR = :effYear,
                                        UPD_CNT = :updCnt
                                    WHERE TRANS_NO = :transNo
                                      AND CASE_NO = :caseNo");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':hciNo', $pHciNo);
        $stmt->bindParam(':pxPin', $pPatientPin);
        $stmt->bindParam(':pxType', $pPatientType);
        $stmt->bindParam(':memPin', $pMemPin);
        $stmt->bindParam(':soapDate', $pSoapDate);
        $stmt->bindParam(':soapBy', $pUserId);
        $stmt->bindParam(':effYear', $pEffYear);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        // $conn->rollBack();
        // echo "Error: C01" . $e->getMessage();
        echo '<script>alert("Error: C01'.$e->getMessage().'");</script>';
    }

    // $conn = null;
}

/*Insert Consultation - Patient Information Sub-module*/
function insertConsultationPatientInfo($conn, $pCaseNo, $pTransNo, $pHciNo, $pPatientPin, $pPatientType, $pMemPin, $pSoapDate, $pUserId, $pEffYear, $pSoapOtp, $getUpdCnt, $pXPSmodule,$pwithOtp,$pSoapCoPay){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP(
                        CASE_NO, TRANS_NO, HCI_NO, PX_PIN, PX_TYPE, MEM_PIN, SOAP_DATE, SOAP_BY,DATE_ADDED,EFF_YEAR,SOAP_OTP,UPD_CNT,XPS_MODULE,WITH_ATC,CO_PAY) 
                          VALUES(:caseNo, 
                                 :transNo, 
                                 :hciNo,
                                 :pxPin, 
                                 :pxType, 
                                 :memPin, 
                                 :soapDate, 
                                 :soapBy,
                                 NOW(), 
                                 :effYear,
                                 :soapOTP,
                                 :updCnt,
                                 :xpsMod,
                                 :withATC,
                                 :copay)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':hciNo', $pHciNo);
        $stmt->bindParam(':pxPin', $pPatientPin);
        $stmt->bindParam(':pxType', $pPatientType);
        $stmt->bindParam(':memPin', $pMemPin);
        $stmt->bindParam(':soapDate', date('Y-m-d', strtotime($pSoapDate)));
        $stmt->bindParam(':soapBy', $pUserId);
        $stmt->bindParam(':effYear', $pEffYear);
        $stmt->bindParam(':soapOTP', $pSoapOtp);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsMod', $pXPSmodule);
        $stmt->bindParam(':withATC', $pwithOtp);
        $stmt->bindParam(':copay', $pSoapCoPay);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: C01'.$e->getMessage().'");</script>';
    }
}

/*Subjective/ History of Illness Sub-module*/
function insertSubjectiveHistory($conn, $pUserId, $pTransNo, $pChiefComplaint, $pIllnessHist, $pOtherComplaint, $getUpdCnt, $pSymptoms, $pPainSite){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE(
                                DATE_ADDED, ADDED_BY, TRANS_NO, CHIEF_COMPLAINT, ILLNESS_HISTORY, OTHER_COMPLAINT, UPD_CNT, SIGNS_SYMPTOMS, PAIN_SITE) 
                                  VALUES(NOW(),
                                         :addedBy, 
                                         :transNo,
                                         :chiefComplaint, 
                                         :illnessHist,
                                         :othComplaint,
                                         :updCnt,
                                         :signsSymptoms,
                                         :painSite)");

        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':chiefComplaint', strtoupper($pChiefComplaint));
        $stmt->bindParam(':illnessHist',strtoupper($pIllnessHist));
        $stmt->bindParam(':othComplaint',strtoupper($pOtherComplaint));
        $stmt->bindParam(':updCnt',$getUpdCnt);
        $stmt->bindParam(':signsSymptoms',$pSymptoms);
        $stmt->bindParam(':painSite',strtoupper($pPainSite));
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: C02'.$e->getMessage().'");</script>';
    }

}

/*Obligated Services*/
function insertObligatedServices($pUserId, $pTransNo, $pServiceID, $pReasonID, $pRemarks, $pServiceValue, $pBPType, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_OBLIGATED(
                        DATE_ADDED, ADDED_BY, TRANS_NO, SERVICE_ID, REASON_ID, REMARKS, SERVICE_VALUE, BP_TYPE, UPD_CNT) 
                          VALUES( NOW(), 
                                 :addedBy, 
                                 :transNo,
                                 :serviceId, 
                                 :reasonId, 
                                 :remarks, 
                                 :serviceValue, 
                                 :bpType,
                                 :updCnt)");

        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':serviceId', $pServiceID);
        $stmt->bindParam(':reasonId', $pReasonID);
        $stmt->bindParam(':remarks', $pRemarks);
        $stmt->bindParam(':serviceValue', $pServiceValue);
        $stmt->bindParam(':bpType', $pBPType);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: C03" . $e->getMessage();
        echo '<script>alert("Error: C03'.$e->getMessage().'");</script>';
    }

    $conn = null;
}

/*Objective/Physical Examination Sub-module*/
/*Part 1*/
function insertObjectivePhysicalExam($conn, $pSystolic, $pDiastolic, $pHr, $pRr, $pHeight, $pWeight, $pTemperature, $pUserId, $pTransNo, $pVision, $pLength, $pHeadCirc, $getUpdCnt,
                                    $pLeftEyeVision,$pRightEyeVision,$pWaist,$pHip,$pLimbs,$pBMI,$pZScore,$pSkinThickness){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT(
                                SYSTOLIC, DIASTOLIC, HR, RR, HEIGHT, WEIGHT, TEMPERATURE, DATE_ADDED, ADDED_BY, TRANS_NO, VISION, LENGTH, HEAD_CIRC, UPD_CNT,
                                LEFT_VISUAL_ACUITY,RIGHT_VISUAL_ACUITY,WAIST,HIP,LIMBS,BMI,Z_SCORE,SKIN_THICKNESS) 
                                  VALUES(:systolic, 
                                         :diastolic, 
                                         :hr, 
                                         :rr, 
                                         :height, 
                                         :weight, 
                                         :temp, 
                                         NOW(), 
                                         :addedBy, 
                                         :transNo, 
                                         :vision, 
                                         :length, 
                                         :headCirc,
                                         :updCnt,
                                         :leftVision,
                                         :rightVision,
                                         :waist,
                                         :hip,
                                         :limbs,
                                         :bmi,
                                         :zScore,
                                         :skinThickness)");

        $stmt->bindParam(':systolic', $pSystolic);
        $stmt->bindParam(':diastolic', $pDiastolic);
        $stmt->bindParam(':hr', $pHr);
        $stmt->bindParam(':rr', $pRr);
        $stmt->bindParam(':height', $pHeight);
        $stmt->bindParam(':weight', $pWeight);
        $stmt->bindParam(':temp',$pTemperature);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':vision', $pVision);
        $stmt->bindParam(':length', $pLength);
        $stmt->bindParam(':headCirc', $pHeadCirc);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':leftVision', $pLeftEyeVision);
        $stmt->bindParam(':rightVision', $pRightEyeVision);
        $stmt->bindParam(':waist', $pWaist);
        $stmt->bindParam(':hip', $pHip);
        $stmt->bindParam(':limbs', $pLimbs);
        $stmt->bindParam(':bmi', $pBMI);
        $stmt->bindParam(':zScore', $pZScore);
        $stmt->bindParam(':skinThickness', $pSkinThickness);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: C04'.$e->getMessage().'");</script>';
    }
}

/*Part 2*/
function insertPhysicalExamMisc($conn, $pSkin, $pHeent, $pChest, $pHeart, $pAbdomen, $pNeuro, $pGU, $pRectal, $pTransNo, $pUserId,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {


        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEMISC(
                        SKIN_ID, HEENT_ID, CHEST_ID, HEART_ID, ABDOMEN_ID, NEURO_ID, GU_ID, RECTAL_ID, TRANS_NO, DATE_ADDED, ADDED_BY, UPD_CNT) 
                          VALUES(:skinId, 
                                 :heentId, 
                                 :chestId, 
                                 :heartId, 
                                 :abdomenId, 
                                 :neuroId, 
                                 :guId,
                                 :rectalId,
                                 :transNo, 
                                 NOW(), 
                                 :addedBy,
                                 :updCnt)");

        $stmt->bindParam(':skinId', $pSkin);
        $stmt->bindParam(':heentId',$pHeent);
        $stmt->bindParam(':chestId', $pChest);
        $stmt->bindParam(':heartId', $pHeart);
        $stmt->bindParam(':abdomenId', $pAbdomen);
        $stmt->bindParam(':neuroId', $pNeuro);
        $stmt->bindParam(':guId', $pGU);
        $stmt->bindParam(':rectalId', $pRectal);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: C05'.$e->getMessage().'");</script>';
    }

}

/*Part 3 Remarks*/
function insertPhysicalExamMiscRemarks($conn, $pHeentRemarks, $pChestRemarks, $pHeartRemarks, $pAbdomenRemarks, $pGenitoRemarks, $pRectalRemarks, $pSkinExtremitiesRemarks, $pNeuroRemarks, $pTransNo, $pUserId, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC(
                                SKIN_REM,HEENT_REM,CHEST_REM,HEART_REM,ABDOMEN_REM,NEURO_REM,GU_REM,RECTAL_REM,TRANS_NO,DATE_ADDED,ADDED_BY,UPD_CNT) 
                                  VALUES(:skinRem, 
                                         :heentRem, 
                                         :chestRem, 
                                         :heartRem, 
                                         :abdomenRem, 
                                         :neuroRem,
                                         :guRem,
                                         :rectalRem,
                                         :transNo,
                                         NOW(), 
                                         :addedBy,
                                         :updCnt)");

        $stmt->bindParam(':skinRem', strtoupper($pSkinExtremitiesRemarks));
        $stmt->bindParam(':heentRem', strtoupper($pHeentRemarks));
        $stmt->bindParam(':chestRem', strtoupper($pChestRemarks));
        $stmt->bindParam(':heartRem', strtoupper($pHeartRemarks));
        $stmt->bindParam(':abdomenRem', strtoupper($pAbdomenRemarks));
        $stmt->bindParam(':neuroRem',strtoupper($pNeuroRemarks));
        $stmt->bindParam(':guRem',strtoupper($pGenitoRemarks));
        $stmt->bindParam(':rectalRem',strtoupper($pRectalRemarks));
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt',$getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: C06'.$e->getMessage().'");</script>';
    }
}

/*Assessment/Diagnosis Sub-module*/
function insertAssessmentDiagnosis($conn, $pUserId, $pTransNo, $pDiagnosis, $pSeqNo, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ICD(
                    TRANS_NO, ICD_CODE, DATE_ADDED, ADDED_BY, SEQ_NO, UPD_CNT) 
                      VALUES(:transNo, 
                             :icdCode, 
                             NOW(), 
                             :addedBy, 
                             :seqNo,
                             :updCnt)");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':icdCode', $pDiagnosis);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':seqNo', $pSeqNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: C07'.$e->getMessage().'");</script>';
    }

}

/*Plan/Management Sub-module in Consultation*/
/*Plan/Management - Diagnosis Examination*/
function insertDiagnosticExamination($conn, $pDiagnostic, $pOthRemarks, $pUserId, $pTransNo, $getUpdCnt,$pIsDrRecommended,$pIsPxRemarks){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC(
                                DIAGNOSTIC_ID, DATE_ADDED, ADDED_BY, TRANS_NO, OTH_REMARKS, UPD_CNT,
                                IS_DR_RECOMMENDED,PX_REMARKS) 
                                  VALUES(:diagnosticId,
                                         NOW(),
                                         :addedBy,
                                         :transNo,
                                         :othRemarks,
                                         :updCnt,
                                         :isDrRecommended,
                                         :pxRemarks)");

        $stmt->bindParam(':diagnosticId', $pDiagnostic);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':othRemarks', strtoupper($pOthRemarks));
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':isDrRecommended', $pIsDrRecommended);
        $stmt->bindParam(':pxRemarks', $pIsPxRemarks);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: C08'.$e->getMessage().'");</script>';
    }
}

/* Plan/Management - Management */
function insertManagement($conn, $pManagement, $pUserId, $pTransNo, $pOthMgmtRemarks,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MANAGEMENT(
                   MANAGEMENT_ID, DATE_ADDED, ADDED_BY, TRANS_NO, OTH_REMARKS,UPD_CNT) 
                      VALUES(:managementId, 
                             NOW(), 
                             :addedBy, 
                             :transNo, 
                             :othRemarks,
                             :updCnt)");

        $stmt->bindParam(':managementId', $pManagement);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':othRemarks', strtoupper($pOthMgmtRemarks));
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: C09'.$e->getMessage().'");</script>';
    }

}
/* Plan/Management - Management */
function insertAdvice($conn, $pAdviceRemarks, $pUserId, $pTransNo, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ADVICE(
                           REMARKS, DATE_ADDED, ADDED_BY, TRANS_NO, UPD_CNT) 
                              VALUES(:remarks, 
                                     NOW(), 
                                     :addedBy, 
                                     :transNo,
                                     :updCnt)");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':remarks', strtoupper($pAdviceRemarks));
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: Error in insertAdvice: '.$e->getMessage().'");</script>';
    }

}

/*Medicine*/
function insertMedicine($conn, $pDrugCode, $pGenCodeMed, $pStrengthMed, $pFormMed, $pPackageMed,
                        $pQuantityMed, $pUnitPriceMed, $pCopayMed, $pTotalAmtPriceMed,$pInsQtyMed,$pInsStrengthMed,$pInsFreqMed,
                        $pCaseNo, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule, $pPrescDoc, $pIsApplicable, $pGenericName, $pSaltMed, $pUnitMed, $pRouteMed,
                        $pIsMedsDispensed,$pMedsDispensedDate,$pMedsDispensingPersonnel, $pMedsCategory, $pOthMedDrugGroup){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE(
                               CASE_NO, TRANS_NO, DRUG_CODE, GEN_CODE, STRENGTH_CODE, FORM_CODE, PACKAGE_CODE, INS_QUANTITY, INS_STRENGTH, INS_FREQUENCY,
                               QUANTITY, DRUG_ACTUAL_PRICE, CO_PAYMENT, AMT_PRICE, PRESC_PHYSICIAN, IS_APPLICABLE, XPS_MODULE, DATE_ADDED, ADDED_BY, UPD_CNT, GENERIC_NAME,
                               SALT_CODE, UNIT_CODE, ROUTE,
                               IS_DISPENSED, DISPENSED_DATE, DISPENSING_PERSONNEL, CATEGORY, DRUG_GROUPING) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :drugCode,
                                         :genCode,
                                         :streCode, 
                                         :formCode,
                                         :packCode,
                                         :insQty,
                                         :insStre,
                                         :insFreq,
                                         :qty,
                                         :unitPrice,
                                         :coPay,
                                         :amtPrice,  
                                         :prescDoc,
                                         :isApplicable,                                       
                                         :xpsModule,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :genname,
                                         :saltCode,
                                         :unitCode,
                                         :route,
                                         :isDispensed,
                                         :dispensedDate,
                                         :dispensingPersonnel,
                                         :category,
                                         :drugGroup)");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':drugCode', $pDrugCode);
        $stmt->bindParam(':genCode', $pGenCodeMed);
        $stmt->bindParam(':streCode', $pStrengthMed);
        $stmt->bindParam(':formCode', $pFormMed);
        $stmt->bindParam(':packCode', $pPackageMed);
        $stmt->bindParam(':insQty', $pInsQtyMed);
        $stmt->bindParam(':insStre', strtoupper($pInsStrengthMed));
        $stmt->bindParam(':insFreq', strtoupper($pInsFreqMed));
        $stmt->bindParam(':qty', $pQuantityMed);
        $stmt->bindParam(':unitPrice', $pUnitPriceMed);
        $stmt->bindParam(':coPay', $pCopayMed);
        $stmt->bindParam(':amtPrice', $pTotalAmtPriceMed);
        $stmt->bindParam(':prescDoc', strtoupper($pPrescDoc));
        $stmt->bindParam(':isApplicable', strtoupper($pIsApplicable));
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':genname', strtoupper($pGenericName));
        $stmt->bindParam(':saltCode', $pSaltMed);
        $stmt->bindParam(':unitCode', $pUnitMed);
        $stmt->bindParam(':route', $pRouteMed);
        $stmt->bindParam(':isDispensed', $pIsMedsDispensed);
        $stmt->bindParam(':dispensedDate', $pMedsDispensedDate);
        $stmt->bindParam(':dispensingPersonnel', strtoupper($pMedsDispensingPersonnel));
        $stmt->bindParam(':category', strtoupper($pMedsCategory));
        $stmt->bindParam(':drugGroup', strtoupper($pOthMedDrugGroup));
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error in insertMedicine: - '.$e->getMessage().'");</script>';
    }

}

/*Plan Management: Diagnostic Examination*/
/*Results - Complete Blood Count */
function insertResultsCBC($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay, $pReferralFacility, $pHematocrit, $pHemoglobinG, $pHemoglobinMmol, $pMhcPg, $pMhcFmol, $pMchcGhb, $pMchcMmol, $pMcvUm, $pMcvFl, $pWbc1000, $pWbc10, $pMyelocyte,
                          $pNeutrophilsBnd, $pNeurophilsSeg, $pLymphocytes, $pMonocytes, $pEosinophils, $pBasophils, $pPlatelet, $pTransNo, $pUserId, $getUpdCnt, $pXpsModule, $pIsApplicableCbc){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC(
                               CASE_NO, TRANS_NO, REFERRAL_FACILITY, LAB_DATE, HEMATOCRIT, HEMOGLOBIN_G, HEMOGLOBIN_MMOL, MHC_PG, MHC_FMOL, MCHC_GHB, MCHC_MMOL, MCV_UM, MCV_FL, WBC_1000, WBC_10, MYELOCYTE,
                               NEUTROPHILS_BND, NEUTROPHILS_SEG, LYMPHOCYTES, MONOCYTES, EOSINOPHILS, BASOPHILS, PLATELET, DATE_ADDED, ADDED_BY, UPD_CNT,XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT, IS_APPLICABLE) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :referralFacility,
                                         :labDate, 
                                         :hematocrit,
                                         :hemoglobinG,
                                         :hemoglobinMmol,
                                         :mhcPg,
                                         :mhcFmol,
                                         :mchcGhb,
                                         :mchcMmol,
                                         :mcvUm,
                                         :mcvFl,
                                         :wbc1000,
                                         :wbc10,
                                         :myelocyte,
                                         :neutrophilsBnd,
                                         :neurophilsSeg,
                                         :lymphocytes,
                                         :monocytes,
                                         :eosinophils,
                                         :basophils,
                                         :platelet,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':hematocrit', $pHematocrit);
        $stmt->bindParam(':hemoglobinG', $pHemoglobinG);
        $stmt->bindParam(':hemoglobinMmol', $pHemoglobinMmol);
        $stmt->bindParam(':mhcPg', $pMhcPg);
        $stmt->bindParam(':mhcFmol', $pMhcFmol);
        $stmt->bindParam(':mchcGhb', $pMchcGhb);
        $stmt->bindParam(':mchcMmol', $pMchcMmol);
        $stmt->bindParam(':mcvUm', $pMcvUm);
        $stmt->bindParam(':mcvFl', $pMcvFl);
        $stmt->bindParam(':wbc1000', $pWbc1000);
        $stmt->bindParam(':wbc10', $pWbc10);
        $stmt->bindParam(':myelocyte', $pMyelocyte);
        $stmt->bindParam(':neutrophilsBnd', $pNeutrophilsBnd);
        $stmt->bindParam(':neurophilsSeg', $pNeurophilsSeg);
        $stmt->bindParam(':lymphocytes', $pLymphocytes);
        $stmt->bindParam(':monocytes', $pMonocytes);
        $stmt->bindParam(':eosinophils', $pEosinophils);
        $stmt->bindParam(':basophils', $pBasophils);
        $stmt->bindParam(':platelet', $pPlatelet);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXpsModule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableCbc);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: D01'.$e->getMessage().'");</script>';
    }

}

/* Results - Urinalysis */
function insertResultsUrinalysis($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay, $pReferralFacility, $pGravity, $pAppearance, $pColor, $pGlucose, $pProteins, $pKetones, $pPh, $pRbCells, $pWbCells, $pBacteria, $pCrystals,
                                 $pBladderCell, $pSquamousCell, $pTubularCell, $pBroadCasts, $pEpithelialCast, $pGranularCast, $pHyalineCast, $pRbcCast, $pWaxyCast, $pWcCast,
                                 $pAlbumin, $pPusCells, $pTransNo, $pUserId, $getUpdCnt,$pXpsModule,$pIsApplicableUrine){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS(
                       CASE_NO, TRANS_NO, LAB_DATE, REFERRAL_FACILITY, GRAVITY, APPEARANCE, COLOR, GLUCOSE, PROTEINS, KETONES, PH, RB_CELLS, WB_CELLS, BACTERIA, CRYSTALS, BLADDER_CELL,
                       SQUAMOUS_CELL, TUBULAR_CELL, BROAD_CASTS, EPITHELIAL_CAST,GRANULAR_CAST, HYALINE_CAST, RBC_CAST, WAXY_CAST, WC_CAST, DATE_ADDED, ADDED_BY, UPD_CNT,
                       ALBUMIN, PUS_CELLS, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :gravity,
                                 :appearance,
                                 :color,
                                 :glucose,
                                 :proteins,
                                 :ketones,
                                 :ph,
                                 :rbCells,
                                 :wbCells,
                                 :bacteria,
                                 :crystals,
                                 :bladderCell,
                                 :squamousCell,
                                 :tubularCell,
                                 :broadCasts,
                                 :epithelialCast,
                                 :granularCast,
                                 :hyalineCast,
                                 :rbcCast,
                                 :waxyCast,
                                 :wcCast,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :albumin,
                                 :pusCells,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':gravity', $pGravity);
        $stmt->bindParam(':appearance', $pAppearance);
        $stmt->bindParam(':color',$pColor);
        $stmt->bindParam(':glucose',$pGlucose);
        $stmt->bindParam(':proteins', $pProteins);
        $stmt->bindParam(':ketones', $pKetones);
        $stmt->bindParam(':ph', $pPh);
        $stmt->bindParam(':rbCells', $pRbCells);
        $stmt->bindParam(':wbCells', $pWbCells);
        $stmt->bindParam(':bacteria', $pBacteria);
        $stmt->bindParam(':crystals', $pCrystals);
        $stmt->bindParam(':bladderCell', $pBladderCell);
        $stmt->bindParam(':squamousCell', $pSquamousCell);
        $stmt->bindParam(':tubularCell',$pTubularCell);
        $stmt->bindParam(':broadCasts', $pBroadCasts);
        $stmt->bindParam(':epithelialCast', $pEpithelialCast);
        $stmt->bindParam(':granularCast', $pGranularCast);
        $stmt->bindParam(':hyalineCast', $pHyalineCast);
        $stmt->bindParam(':rbcCast',$pRbcCast);
        $stmt->bindParam(':waxyCast', $pWaxyCast);
        $stmt->bindParam(':wcCast', $pWcCast);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':albumin', $pAlbumin);
        $stmt->bindParam(':pusCells', $pPusCells);
        $stmt->bindParam(':xpsModule', $pXpsModule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableUrine);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: D02'.$e->getMessage().'");</script>';
    }
}

/* Results - Fecalysis */
function  insertResultsFecalysis($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay, $pReferralFacility, $pColorFecalysis, $pConsistency,$pRBC,$pWBC,$pOva,$pParasite,$pBlood,$pOccultBlood,$pPusCell,
                                 $pTransNo, $pUserId, $getUpdCnt,$pXpsModule,$pIsApplicableFecalysis){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS(
                               CASE_NO, TRANS_NO, LAB_DATE, REFERRAL_FACILITY, COLOR, CONSISTENCY, RBC, WBC, OVA, PARASITE,BLOOD, OCCULT_BLOOD,PUS_CELLS, DATE_ADDED, ADDED_BY, 
                               UPD_CNT,XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE)
                                  VALUES(:caseNo,
                                         :transNo,
                                         :labDate,
                                         :referralFacility,
                                         :color,
                                         :consistency,
                                         :rbc,
                                         :wbc,
                                         :ova,
                                         :parasite,
                                         :blood,
                                         :occultBlood,
                                         :pusCell,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':color', $pColorFecalysis);
        $stmt->bindParam(':consistency', $pConsistency);
        $stmt->bindParam(':rbc', $pRBC);
        $stmt->bindParam(':wbc', $pWBC);
        $stmt->bindParam(':ova', $pOva);
        $stmt->bindParam(':parasite', $pParasite);
        $stmt->bindParam(':blood', $pBlood);
        $stmt->bindParam(':occultBlood', $pOccultBlood);
        $stmt->bindParam(':pusCell', $pPusCell);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule',$pXpsModule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableFecalysis);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: D03'.$e->getMessage().'");</script>';
    }
}
/* Results - Chest X-Ray */
function  insertResultsChestXray($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay, $pReferralFacility,$pFindingsXray,$pRemarkFindings,$pObservation,$pRemarkObservation,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableXray){
    $ini = parse_ini_file("config.ini");

    try {
        

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY(
                               CASE_NO, TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS, REMARKS_FINDINGS, OBSERVATION, REMARKS_OBSERVATION, DATE_ADDED, ADDED_BY, UPD_CNT,XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :labDate,
                                         :referralFacility,
                                         :findings,
                                         :remarkFindings,
                                         :observation,
                                         :remarkObservation,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':findings',$pFindingsXray);
        $stmt->bindParam(':remarkFindings', strtoupper($pRemarkFindings));
        $stmt->bindParam(':observation', $pObservation);
        $stmt->bindParam(':remarkObservation', $pRemarkObservation);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableXray);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: D04'.$e->getMessage().'");</script>';
    }
}

/* Results - Sputum */
function  insertResultsSputum($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay, $pReferralFacility,$pFindingsSputum,$pRemarksSputum,$pNoPlusses,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableSputum,$pDataCollect){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM(
                               CASE_NO, TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS, REMARKS, NO_PLUSSES, DATE_ADDED, ADDED_BY, UPD_CNT,XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE,DATA_COLLECTION) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :labDate,
                                         :referralFacility,
                                         :findings,
                                         :remarks,
                                         :noPlusses,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable,
                                         :dataCollect)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':findings', strtoupper($pFindingsSputum));
        $stmt->bindParam(':remarks', strtoupper($pRemarksSputum));
        $stmt->bindParam(':noPlusses', $pNoPlusses);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableSputum);
        $stmt->bindParam(':dataCollect', $pDataCollect);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: D05'.$e->getMessage().'");</script>';
    }

}

/* Results - Lipid Profile */
function  insertResultsLipidProfile($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay, $pReferralFacility,$pLdl,$pHdl,$pTotal,$pCholesterol,$pTriglycerides,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableLipid){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF(
                                CASE_NO, TRANS_NO, REFERRAL_FACILITY, LAB_DATE, LDL, HDL, TOTAL, CHOLESTEROL, TRIGLYCERIDES, DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :referralFacility,
                                         :labDate,
                                         :ldl,
                                         :hdl,
                                         :total,
                                         :cholesterol,
                                         :triglycerides,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':ldl', $pLdl);
        $stmt->bindParam(':hdl', $pHdl);
        $stmt->bindParam(':total', $pTotal);
        $stmt->bindParam(':cholesterol', $pCholesterol);
        $stmt->bindParam(':triglycerides', $pTriglycerides);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableLipid);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: D06'.$e->getMessage().'");</script>';
    }

}

/* Results - Fasting Blood Sugar */
function  insertResultsFBS($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay,$pReferralFacility,$pGlucoseMg,$pGlucosemmol,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableFbs){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS(
                               CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, GLUCOSE_MG, GLUCOSE_MMOL, DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :labDate,
                                         :referralFacility,
                                         :glucoseMg,
                                         :glucoseMmol,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':glucoseMg', $pGlucoseMg);
        $stmt->bindParam(':glucoseMmol', $pGlucosemmol);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableFbs);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: D07'.$e->getMessage().'");</script>';
    }

}

/* Results - Random Blood Sugar */
function  insertResultsRBS($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay,$pReferralFacility,$pGlucoseMg,$pGlucosemmol,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableFbs){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS(
                               CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, GLUCOSE_MG, GLUCOSE_MMOL, DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                                  VALUES(:caseNo,
                                         :transNo,
                                         :labDate,
                                         :referralFacility,
                                         :glucoseMg,
                                         :glucoseMmol,
                                         NOW(),
                                         :addedBy,
                                         :updCnt,
                                         :xpsModule,
                                         :diagLabFee,
                                         :coPayment,
                                         :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':glucoseMg', $pGlucoseMg);
        $stmt->bindParam(':glucoseMmol', $pGlucosemmol);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableFbs);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: RBS'.$e->getMessage().'");</script>';
    }

}


/* Results - ECG */
function  insertResultsECG($conn, $pCaseNo,$pLabDate, $pLabFee, $pCoPay,$pReferralFacility,$pFindingsECG,$pRemarksECG,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableEcg){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_ECG(
                       CASE_NO, TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS, REMARKS, DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :findings,
                                 :remarks,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':findings', $pFindingsECG);
        $stmt->bindParam(':remarks', $pRemarksECG);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableEcg);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - Paps Smear */
function  insertResultsPapsSmear($conn, $pCaseNo,$pLabDate, $pLabFee, $pCoPay,$pReferralFacility,$pFindingsPaps,$pImpressionPaps,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicablePaps){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PAPSSMEAR(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS, IMPRESSION, DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE,DIAGNOSTIC_FEE,CO_PAYMENT,IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :findings,
                                 :impression,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':findings', $pFindingsPaps);
        $stmt->bindParam(':impression', $pImpressionPaps);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicablePaps);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - Oral Glucose Tolerance Test (OGTT)*/
function  insertResultsOGTT($conn, $pCaseNo, $pLabDate, $pLabFee, $pCoPay,$pReferralFacility,$pFastingMg,$pFastingMmol,$pOgttOneHrMg,$pOgttOneHrMmol,$pOgttTwoHrsMg,$pOgttTwoHrsMmol,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableOgtt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OGTT(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, EXAM_FASTING_MG, EXAM_FASTING_MMOL, EXAM_OGTT_ONE_MG, EXAM_OGTT_ONE_MMOL, EXAM_OGTT_TWO_MG, EXAM_OGTT_TWO_MMOL, 
                       DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE, DIAGNOSTIC_FEE, CO_PAYMENT, IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :fastingMg,
                                 :fastingMmol,
                                 :oneHrMg,
                                 :oneHrMmol,
                                 :twoHrsMg,
                                 :twoHrsMmol,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDate);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':fastingMg', $pFastingMg);
        $stmt->bindParam(':fastingMmol', $pFastingMmol);
        $stmt->bindParam(':oneHrMg', $pOgttOneHrMg);
        $stmt->bindParam(':oneHrMmol', $pOgttOneHrMmol);
        $stmt->bindParam(':twoHrsMg', $pOgttTwoHrsMg);
        $stmt->bindParam(':twoHrsMmol', $pOgttTwoHrsMmol);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFee);
        $stmt->bindParam(':coPayment', $pCoPay);
        $stmt->bindParam(':isApplicable', $pIsApplicableOgtt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - Fecal Occult Blood Test (FOBT)*/
function  insertResultsFOBT($conn, $pCaseNo, $pLabDateFobt, $pLabFeeFobt, $pCoPayFobt,$pReferralFacility,$pFindingsFobt,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableFobt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FOBT(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS,  
                       DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE, DIAGNOSTIC_FEE, CO_PAYMENT, IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :findings,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDateFobt);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':findings', $pFindingsFobt);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFeeFobt);
        $stmt->bindParam(':coPayment', $pCoPayFobt);
        $stmt->bindParam(':isApplicable', $pIsApplicableFobt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - Platelet ekonsulta*/
function insertResultsHbA1c($conn, $pCaseNo, $pLabDateHbA1c, $pLabFeeHbA1c, $pCoPayHbA1c,$pReferralFacility,$pFindingsHbA1c,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableHbA1c){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_HBA1C(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS,  
                       DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE, DIAGNOSTIC_FEE, CO_PAYMENT, IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :findings,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDateHbA1c);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':findings', $pFindingsHbA1c);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFeeHbA1c);
        $stmt->bindParam(':coPayment', $pCoPayHbA1c);
        $stmt->bindParam(':isApplicable', $pIsApplicableHbA1c);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - Creatinine ekonsulta*/
function insertResultsCreatinine($conn, $pCaseNo, $pLabDateCreatinine, $pLabFeeCreatinine, $pCoPayCreatinine,$pReferralFacility,$pFindingsCreatinine,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableCreatinine){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CREATININE(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS,  
                       DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE, DIAGNOSTIC_FEE, CO_PAYMENT, IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :findings,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDateCreatinine);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':findings', $pFindingsCreatinine);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFeeCreatinine);
        $stmt->bindParam(':coPayment', $pCoPayCreatinine);
        $stmt->bindParam(':isApplicable', $pIsApplicableCreatinine);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - PDDT ekonsulta*/
function insertResultsPPDT($conn, $pCaseNo, $pLabDatePddt, $pLabFeePddt, $pCoPayPddt,$pReferralFacility,$pFindingsPddt,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicablePddt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PPD_TEST(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, FINDINGS,  
                       DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE, DIAGNOSTIC_FEE, CO_PAYMENT, IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :findings,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDatePddt);
        $stmt->bindParam(':referralFacility', $pReferralFacility);
        $stmt->bindParam(':findings', $pFindingsPddt);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFeePddt);
        $stmt->bindParam(':coPayment', $pCoPayPddt);
        $stmt->bindParam(':isApplicable', $pIsApplicablePddt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/* Results - Others Diagnostic Exam ekonsulta*/
function insertResultsOthersDiagExam($conn, $pCaseNo, $pLabDateOth, $pLabFeeOth, $pCoPayOth,$pReferralFacilityOth,$pOthDiagExam,$pFindingsOthExam,$pTransNo, $pUserId, $getUpdCnt,$pXPSmodule,$pIsApplicableOth){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OTHERS(
                       CASE_NO,TRANS_NO, LAB_DATE, REFERRAL_FACILITY, OTH_DIAG_EXAM, FINDINGS,  
                       DATE_ADDED, ADDED_BY, UPD_CNT, XPS_MODULE, DIAGNOSTIC_FEE, CO_PAYMENT, IS_APPLICABLE) 
                          VALUES(:caseNo,
                                 :transNo,
                                 :labDate,
                                 :referralFacility,
                                 :othExam,
                                 :findings,
                                 NOW(),
                                 :addedBy,
                                 :updCnt,
                                 :xpsModule,
                                 :diagLabFee,
                                 :coPayment,
                                 :isApplicable)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':labDate', $pLabDateOth);
        $stmt->bindParam(':referralFacility', $pReferralFacilityOth);
        $stmt->bindParam(':othExam', $pOthDiagExam);
        $stmt->bindParam(':findings', $pFindingsOthExam);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':xpsModule', $pXPSmodule);
        $stmt->bindParam(':diagLabFee', $pLabFeeOth);
        $stmt->bindParam(':coPayment', $pCoPayOth);
        $stmt->bindParam(':isApplicable', $pIsApplicableOth);
        $stmt->execute();

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

}

/*Generation of Report - HCI Profile Information*/
function getHciProfileInfo($pAccreNo, $pUserId){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_HCI_PROFILE
                                                WHERE ACCRE_NO = :accreNo
                                                AND USER_ID = :userId");

        $stmt->bindParam(':accreNo', $pAccreNo);
        $stmt->bindParam(':userId', $pUserId);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*Generation of Report - HCI Profile Information*/
function getHciProfileKeyPerIndividual($pAccreNo, $pUserId){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT ACCRE_NO, CIPHER_KEY 
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_HCI_PROFILE
                                WHERE ACCRE_NO = :accreNo
                                AND USER_ID = :userId");

        $stmt->bindParam(':accreNo', $pAccreNo);
        $stmt->bindParam(':userId', $pUserId);

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*Generation of Report - Enlistment/Registration*/
function getReportResultEnlistment($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
                                    RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS b ON a.CASE_NO = b.CASE_NO
                                        WHERE b.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                        AND b.IS_FINALIZE = 'Y'");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getReportResultEnlistmentFirstEnctr($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS a
            RIGHT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS b ON a.CASE_NO = b.CASE_NO
            WHERE b.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND a.EFF_YEAR = :pEffYear
            AND b.IS_FINALIZE = 'Y'
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


/*Generation of Report - Profiling*/
function getReportResultProfiling($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT *, profile.TRANS_NO AS TRANS_NO, preghist.IS_APPLICABLE as PREG_IS_APPLICABLE, menshist.IS_APPLICABLE AS MENS_IS_APPLICABLE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_OINFO AS oinfo ON profile.TRANS_NO = oinfo.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST AS sochist ON profile.TRANS_NO = sochist.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PREGHIST AS preghist ON profile.TRANS_NO = preghist.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MENSHIST AS menshist ON profile.TRANS_NO = menshist.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT AS pepert ON profile.TRANS_NO = pepert.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_BLOODTYPE AS btype ON profile.TRANS_NO = btype.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC AS pespecific ON profile.TRANS_NO = pespecific.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_NCDQANS AS ncdqans ON profile.TRANS_NO = ncdqans.TRANS_NO 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEGENSURVEY AS survey ON profile.TRANS_NO = survey.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND oinfo.UPD_CNT = profile.UPD_CNT
            AND sochist.UPD_CNT = profile.UPD_CNT
            AND preghist.UPD_CNT = profile.UPD_CNT
            AND menshist.UPD_CNT = profile.UPD_CNT
            AND pepert.UPD_CNT = profile.UPD_CNT
            AND btype.UPD_CNT = profile.UPD_CNT
            AND pespecific.UPD_CNT = profile.UPD_CNT
            AND ncdqans.UPD_CNT = profile.UPD_CNT
            GROUP BY profile.TRANS_NO, profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - MEDICAL HISTORY*/
function getReportResultProfilingMedHist($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT distinct medhist.MDISEASE_CODE, profile.TRANS_NO, profile.CASE_NO
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST AS medhist ON profile.TRANS_NO = medhist.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND medhist.UPD_CNT = profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - MEDICAL HISTORY REMARKS*/
function getReportResultProfilingMHspecific($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT mhspecific.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.PROF_DATE, profile.EFF_YEAR, profile.IS_FINALIZE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC AS mhspecific ON profile.TRANS_NO = mhspecific.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND mhspecific.UPD_CNT = profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - PERTINENT FINDINGS PER SYSTEM*/
function getReportResultProfilingPEmisc($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT pemisc.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.PROF_DATE, profile.EFF_YEAR, profile.IS_FINALIZE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC AS pemisc ON profile.TRANS_NO = pemisc.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND pemisc.UPD_CNT = profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - SURGICAL HISTORY*/
function getReportResultProfilingSurghist($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT surghist.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.PROF_DATE, profile.EFF_YEAR, profile.IS_FINALIZE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST AS surghist ON profile.TRANS_NO = surghist.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND surghist.UPD_CNT = profile.UPD_CNT
            GROUP BY profile.TRANS_NO
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - FAMILY HISTORY*/
function getReportResultProfilingFamhist($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT distinct famhist.MDISEASE_CODE, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.PROF_DATE, profile.EFF_YEAR, profile.IS_FINALIZE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST AS famhist ON profile.TRANS_NO = famhist.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND famhist.UPD_CNT = profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - FAMILY HISTORY REMARKS*/
function getReportResultProfilingFHspecific($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT fhspecific.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.PROF_DATE, profile.EFF_YEAR, profile.IS_FINALIZE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC AS fhspecific ON profile.TRANS_NO = fhspecific.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND fhspecific.UPD_CNT = profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - IMMUNIZATION*/
function getReportResultProfilingImmunization($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT immune.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.PROF_DATE, profile.EFF_YEAR, profile.IS_FINALIZE
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION AS immune ON profile.TRANS_NO = immune.TRANS_NO 
            WHERE profile.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate
            AND profile.IS_FINALIZE = 'Y'
            AND profile.EFF_YEAR = :pEffYear
            AND immune.UPD_CNT = profile.UPD_CNT
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Profiling - DIAGNOSTIC*/
// function getReportResultProfilingDiagnostic($pStartDate, $pEndDate){
//     $ini = parse_ini_file("config.ini");

//     try {
//         $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
//         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//         $stmt = $conn->prepare("SELECT diagnostic.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
//                                        FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
//                                           LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_DIAGNOSTIC AS diagnostic ON profile.TRANS_NO = diagnostic.TRANS_NO 
//                                             WHERE profile.IS_FINALIZE = 'Y'
//                                               AND profile.XPS_MODULE = 'HSA'
//                                               AND profile.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
//                                               AND diagnostic.UPD_CNT = profile.UPD_CNT
//                                              ");

//         $stmt->bindParam(':pxStartDate', $pStartDate);
//         $stmt->bindParam(':pxEndDate', $pEndDate);

//         $stmt->execute();

//         $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

//         return $result;
//     }
//     catch(PDOException $e)
//     {
//         echo "Error: " . $e->getMessage();
//     }

//     $conn = null;
// }

/*Generation of Report - Profiling - MANAGEMENT*/
function getReportResultProfilingManagement($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT management.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MANAGEMENT AS management ON profile.TRANS_NO = management.TRANS_NO 
                                            WHERE profile.IS_FINALIZE = 'Y'
                                              AND profile.XPS_MODULE = 'HSA'
                                              AND profile.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                              AND management.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation*/
function getReportResultMainConsult($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, soap.TRANS_NO 
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                        WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                          AND soap.XPS_MODULE = 'SOAP'
                                          ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultConsultation($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, soap.TRANS_NO 
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ADVICE as advice ON soap.TRANS_NO = advice.TRANS_NO /*PLAN/MANAGEMENT - ADVICE*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT as pepert ON soap.TRANS_NO = pepert.TRANS_NO /*PHYSICAL EXAMINATION PERTINENT*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC as pespecific ON soap.TRANS_NO = pespecific.TRANS_NO /*PERTINENT PHYSICAL FINDINGS PER SYSTEM REMARKS*/
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE as subjective ON soap.TRANS_NO = subjective.TRANS_NO /*CHIEF COMPLAINT*/
                                        WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                          AND soap.XPS_MODULE = 'SOAP'
                                          AND advice.UPD_CNT = soap.UPD_CNT
                                          AND pepert.UPD_CNT = soap.UPD_CNT
                                          AND pespecific.UPD_CNT = soap.UPD_CNT
                                          AND subjective.UPD_CNT = soap.UPD_CNT
                                            GROUP BY soap.TRANS_NO
                                          ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - DIAGNOSTIC*/
function getReportResultConsultationDiagnostic($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT diagnostic.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC as diagnostic ON soap.TRANS_NO = diagnostic.TRANS_NO 
                                            WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND diagnostic.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - ICD*/
function getReportResultConsultationIcd($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT icd.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ICD as icd ON soap.TRANS_NO = icd.TRANS_NO 
                                            WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND icd.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - Management*/
function getReportResultConsultationManagement($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT management.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MANAGEMENT as management ON soap.TRANS_NO = management.TRANS_NO 
                                            WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND management.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Consultation - PERTINENT FINDINGS PER SYSTEM*/
function getReportResultConsultationPemisc($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT pemisc.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEMISC as pemisc ON soap.TRANS_NO = pemisc.TRANS_NO 
                                            WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate                                            
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND pemisc.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generation of Report - Medicine*/
function getReportResultMedicine($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                              
                                     FROM ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE as meds
                                     LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap ON meds.TRANS_NO = soap.TRANS_NO
                                        WHERE meds.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate  
                                            AND meds.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabFirstEncounter($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT a.CASE_NO, b.MEM_PIN, a.PX_PIN, a.PX_TYPE, a.EFF_YEAR, b.TRANS_NO
            FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as a
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE as b ON a.CASE_NO = b.CASE_NO
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST as c ON b.TRANS_NO = c.TRANS_NO
            WHERE b.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate 
            AND b.IS_FINALIZE = 'Y'   
            AND b.EFF_YEAR = :pEffYear                                     
            AND c.MDISEASE_CODE = '006'
            GROUP BY b.TRANS_NO
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getReportResultLab($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                              FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap ON enlist.CASE_NO = soap.CASE_NO
                                WHERE soap.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                  ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getReportResultLabCbc($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate  
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabUrine($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate 
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')     
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabFecalysis($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate 
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY a.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabChestXray($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY a.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabSputum($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate 
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X') 
                                      GROUP BY a.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabLipidProf($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate   
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabFbs($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS 
                                    WHERE DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate  
                                    AND IS_APPLICABLE IN ('D', 'W', 'X') 
                                    ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabFbsFirstEncounter($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT *
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE as a
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST as c ON a.TRANS_NO = c.TRANS_NO
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS as b ON a.TRANS_NO = b.TRANS_NO
            WHERE a.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate 
            AND a.IS_FINALIZE = 'Y'
            AND a.EFF_YEAR = :pEffYear 
            AND c.MDISEASE_CODE = '006'
            AND b.IS_APPLICABLE IN ('D') AND b.XPS_MODULE = 'PROF'   
            AND b.UPD_CNT = a.UPD_CNT  
            GROUP BY a.TRANS_NO
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getReportResultLabRBS($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS 
                                    WHERE DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate  
                                    AND IS_APPLICABLE IN ('D')   
                                    ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabRBSFirstEncounter($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    $vEffYear = date('Y', strtotime($pStartDate));

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT *
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE as a
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST as c ON a.TRANS_NO = c.TRANS_NO
            LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_RBS as b ON a.TRANS_NO = b.TRANS_NO
            WHERE a.PROF_DATE BETWEEN :pxStartDate AND :pxEndDate  
            AND a.IS_FINALIZE = 'Y'
            AND a.EFF_YEAR = :pEffYear
            AND c.MDISEASE_CODE = '006'
            AND b.IS_APPLICABLE IN ('D') AND b.XPS_MODULE = 'PROF'
            AND b.UPD_CNT = a.UPD_CNT   
            GROUP BY b.CASE_NO
        ");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);
        $stmt->bindParam(':pEffYear', $vEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabEcg($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_ECG as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabOgtt($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OGTT as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate   
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')   
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabPapsSmear($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PAPSSMEAR as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabFOBT($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FOBT as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabCreatinine($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CREATININE as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabPDD($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PPD_TEST b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}


function getReportResultLabHbA1c($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_HBA1C b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

function getReportResultLabOthDiag($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OTHERS b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.DATE_ADDED BETWEEN :pxStartDate AND :pxEndDate
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

//Get List of Member Assignment
function getReportMemberAssignment($pEffYear){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT * 
            FROM ".$ini['EPCB'].".TSEKAP_TBL_ASSIGN 
            WHERE eff_year = :effyear
        ");

        $stmt->bindParam(':effyear', $pEffYear);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

//Get List of Consultation
function getEnlistedConsultationList(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT soap.TRANS_NO, soap.SOAP_DATE, enlist.PX_LNAME, enlist.PX_FNAME, enlist.PX_MNAME, 
                                        enlist.PX_EXTNAME, enlist.PX_PIN, enlist.PX_DOB, enlist.PX_TYPE, enlist.EFF_YEAR
                                            FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist
                                              INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap ON enlist.CASE_NO = soap.CASE_NO
                                              GROUP BY enlist.PX_PIN, enlist.EFF_YEAR
                                              ");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

//Get List of Registered with HSA
function getListEnlistedWithScreening(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("
            SELECT 
            a.TRANS_NO, a.PROF_DATE, enlist.PX_LNAME, enlist.PX_FNAME, enlist.PX_MNAME, enlist.PX_EXTNAME, 
            enlist.PX_PIN, enlist.PX_DOB, enlist.PX_TYPE, enlist.EFF_YEAR  
            FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE as a
            INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_ENLIST as enlist ON a.PX_PIN = enlist.PX_PIN
            GROUP BY enlist.PX_PIN, enlist.EFF_YEAR  
            ORDER BY a.PROF_DATE DESC 
        ");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Get Enlistment/Registration Record*/
function getEnlistmentList($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST 
                                        WHERE TRANS_DATE BETWEEN :pxStartDate AND :pxEndDate
                                          AND XPS_MODULE = 'EPCB'");

        $stmt->bindParam(':pxStartDate', $pStartDate);
        $stmt->bindParam(':pxEndDate', $pEndDate);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Insert data for Profiling/Health Screening & Assessment Module (HSA)*/
function saveProfilingInfo($profiling){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        session_start();
        $pUserId = $_SESSION['pUserID'];
        $pXPSmodule = "PROF"; /*HSA - Health Screening & Assessment (Profiling in PCB)*/

        /*Start Patient Details Sub-module*/
        $pCaseNo=$profiling['txtPerHistCaseNo'];
        $pPatientPin=$profiling['txtPerHistPxPIN'];
        $pPatientType=$profiling['txtPerHistPatType'];
        $pMemPin=$profiling['txtPerHistMemPIN'];
        $pEffYear=$profiling['txtPerHistEffYear'];
        
        $pFinalizedData=$profiling['pFinalize'];
        $pProfDate = date('Y-m-d', strtotime($profiling['txtPerHistProfDate']));
        // $pIsWithATC = $profiling['walkedInChecker']; //pIsWalkedIn in DTD

        $pIsWithATC = "Y";
        $pOTP = "WALKEDIN";

        /*HSA OF PATIENT*/
        if ($_POST['pHsaTransNo'] == "" || $_POST['pHsaTransNo'] == NULL) {

            if (isset($_POST['saveRecord']) || isset($_POST['saveFinalizeHSA'])) {
                $pTransNo = generateTransNo('PROF_NO'); //automatically generated
                $getUpdCnt = $profiling['pUpdCntProfile'];
                // Start Patient Details Sub-module
                insertProfilingInfo($conn, $pCaseNo, $pTransNo, $pProfDate, $pHciNo, $pPatientPin, $pPatientType, $pMemPin, $pUserId, $pEffYear, $pOTP, $pFinalizedData, $pXPSmodule, $pIsWithATC);
                /*End Patient Details Sub-module*/
            }
           
        } else if ($_POST['pHsaTransNo'] != "") {
                $pTransNo = $profiling['pHsaTransNo'];
                $getUpdCnt = $profiling['pUpdCntProfile'] + 1;
                // Start Patient Details Sub-module
                updateProfilingInfo($conn, $pTransNo, $pPatientPin, $pPatientType, $pMemPin, $pUserId, $pEffYear, $getUpdCnt, $pFinalizedData, $pProfDate);
                /*End Patient Details Sub-module*/
        }

      
        /*Patient Details Sub-module: Other Information */
        $pPxAge = strtoupper($profiling['txtPerHistPatAge']);
        $pPxOccupation = NULL;
        $pPxEducation = NULL;
        $pPxPoB = NULL;
        $pPxReligion = NULL;
        $pMomMLastName = NULL;
        $pMomMMiddleName = NULL;
        $pMomFirstname = NULL;
        $pMomExtName = NULL;
        $pMomDob = NULL;
        $pDadLastName = NULL;
        $pDadMiddleName = NULL;
        $pDadFirstname = NULL;
        $pDadExtName = NULL;
        $pDadDob = NULL;

        insertProfilingOtherInfo($conn, $pPxAge, $pPxOccupation, $pPxEducation, $pUserId, $pTransNo, $pPxPoB, $pPxReligion, $pMomMLastName, $pMomMMiddleName, $pMomFirstname, $pMomExtName, $pMomDob, $pDadLastName, $pDadMiddleName, $pDadFirstname, $pDadExtName, $pDadDob, $getUpdCnt);
        /*End Patient Details Sub-module*/

        /*Start Medical & Surgical History Sub-module*/
        /*Past Medical History*/
        $pPastMedHistory = $profiling['chkMedHistDiseases'];

        for ($i = 0; $i < count($pPastMedHistory); $i++) {
            if ($pPastMedHistory[$i] != '') {
                insertPastMedicalHistory($conn, $pPastMedHistory[$i], $pUserId, $pTransNo, $getUpdCnt);

                /*Past Medical History - Specific Diseases*/
                if ($pPastMedHistory[$i] == '001'):
                    $pSpecificDesc = $profiling['txtMedHistAllergy'];
                elseif ($pPastMedHistory[$i] == '003'):
                    $pSpecificDesc = $profiling['txtMedHistCancer'];
                elseif ($pPastMedHistory[$i] == '009'):
                    $pSpecificDesc = $profiling['txtMedHistHepatitis'];
                elseif ($pPastMedHistory[$i] == '011'):
                    $pSpecificDesc = $profiling['txtMedHistBPSystolic'] . " / " . $profiling['txtMedHistBPDiastolic'] . " mmHg";
                elseif ($pPastMedHistory[$i] == '015'):
                    $pSpecificDesc = $profiling['txtMedHistPTB'];
                elseif ($pPastMedHistory[$i] == '016'):
                    $pSpecificDesc = $profiling['txtMedHistExPTB'];
                elseif ($pPastMedHistory[$i] == '998'):
                    $pSpecificDesc = $profiling['txaMedHistOthers'];
                else:
                    $pSpecificDesc = "";
                endif;

                if ($pSpecificDesc != "" ||  $pSpecificDesc != null) {
                    insertPastMedicalHistorySpecific($conn, $pPastMedHistory[$i], strtoupper($pSpecificDesc), $pTransNo, $pUserId, $getUpdCnt);
                }
                
            }
        }

        /*Past Surgical History*/
        $pOperation = $profiling['operation'];
        $pOperationDates = $profiling['operationDate'];
        if (count($pOperation) >= 1) {
            for ($i = 0; $i < count($pOperation); $i++) {
                if($pOperationDates[$i] != ""){
                    $pOperationDate[$i] = date('Y-m-d', strtotime($pOperationDates[$i]));
                } else {
                    $pOperationDate[$i] = NULL;
                }
                insertPastSurgicalHistory($conn, $pOperation[$i], $pOperationDate[$i], $pUserId, $pTransNo, $getUpdCnt);
            }
        } 
        /*End Medical & Surgical History Sub-module*/

        /*Start Family & Personal History Sub-module*/
        /*Family History*/
        $pFamMedHistory = $profiling['chkFamHistDiseases'];

        for ($i = 0; $i < count($pFamMedHistory); $i++) {
            if ($pFamMedHistory[$i] != '') {
                insertFamilyMedicalHistory($conn, $pFamMedHistory[$i], $pUserId, $pTransNo, $getUpdCnt);

                /*Past Medical History - Specific Diseases*/
                if ($pFamMedHistory[$i] == '001'):
                    $pSpecificDesc = $profiling['txtFamHistAllergy'];
                elseif ($pFamMedHistory[$i] == '003'):
                    $pSpecificDesc = $profiling['txtFamHistCancer'];
                elseif ($pFamMedHistory[$i] == '009'):
                    $pSpecificDesc = $profiling['txtFamHistHepatitis'];
                elseif ($pFamMedHistory[$i] == '011'):
                    $pSpecificDesc = $profiling['txtFamHistBPSystolic'] . "/" . $profiling['txtFamHistBPDiastolic'] . " mmHg";
                elseif ($pFamMedHistory[$i] == '015'):
                    $pSpecificDesc = $profiling['txtFamHistPTB'];
                elseif ($pFamMedHistory[$i] == '016'):
                    $pSpecificDesc = $profiling['txtFamHistExPTB'];
                elseif ($pFamMedHistory[$i] == '998'):
                    $pSpecificDesc = $profiling['txaFamHistOthers'];
                else:
                    $pSpecificDesc = "";
                endif;

                if ($pSpecificDesc != "" || $pSpecificDesc != null) {
                    insertFamilyMedicalHistorySpecific($conn, $pFamMedHistory[$i], strtoupper($pSpecificDesc), $pTransNo, $pUserId, $getUpdCnt);
                }
                

                //Diabetes Mellitus
                if ($pFamMedHistory[$i] == '006') {
                    /* Results - Fasting Blood Sugar (FBS) */
                    $pIsApplicableFbs = "D";
                    $pReferralFacilityFBS = strtoupper($profiling['diagnostic_7_accre_diag_fac']);
                    $pLabFeeFBS = $profiling['diagnostic_7_lab_fee'];
                    $pCoPayFBS = NULL;
                    $pGlucoseMg = $profiling['diagnostic_7_glucose_mgdL'];
                    $pGlucosemmol = $profiling['diagnostic_7_glucose_mmolL'];

                    if($profiling['diagnostic_7_lab_exam_date'] != NULL && ($pGlucoseMg != NULL || $pGlucosemmol != NULL)){
                        $pLabDateFBS = date('Y-m-d',strtotime($profiling['diagnostic_7_lab_exam_date']));
                        insertResultsFBS($conn, $pCaseNo,$pLabDateFBS, $pLabFeeFBS, $pCoPayFBS, $pReferralFacilityFBS, $pGlucoseMg, $pGlucosemmol, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableFbs);
                    }    

                    /* Results - Random Blood Sugar (RBS) */
                    $pIsApplicableRBS = "D";
                    $pReferralFacilityRBS = strtoupper($profiling['diagnostic_19_accre_diag_fac']);                    
                    $pLabFeeRBS = $profiling['diagnostic_19_lab_fee'];
                    $pCoPayRBS = NULL;
                    $pGlucoseMgRBS = $profiling['diagnostic_19_glucose_mgdL'];
                    $pGlucosemmolRBS = $profiling['diagnostic_19_glucose_mmolL'];
                    if($profiling['diagnostic_19_lab_exam_date'] != NULL && ($pGlucoseMgRBS != NULL || $pGlucosemmolRBS != NULL)){
                        $pLabDateRBS = date('Y-m-d',strtotime($profiling['diagnostic_19_lab_exam_date']));
                        insertResultsRBS($conn, $pCaseNo,$pLabDateRBS, $pLabFeeRBS, $pCoPayRBS, $pReferralFacilityRBS, $pGlucoseMgRBS, $pGlucosemmolRBS, $pTransNo, $pUserId, $getUpdCnt, $pXPSmodule,$pIsApplicableRBS);
                    }                    
                }
            }
        }


        /*Personal/Social History*/
        $pIsSmoker = $profiling['radFamHistSmoke'];
        $pNoCigPack = $profiling['txtFamHistCigPk'];
        $pIsAlcoholDrinker = $profiling['radFamHistAlcohol'];
        $pNoBottles = $profiling['txtFamHistBottles'];
        $pIllDrugUser = $profiling['radFamHistDrugs'];
        $pIsSexuallyActive = $profiling['radFamHistSexualHist'];

        if ($profiling['radFamHistSexualHist'] != NULL){
            $pIsSexuallyActive = $profiling['radFamHistSexualHist'];
        } else{
            $pIsSexuallyActive = "N";
        }


        insertPersonalSocialHistory($conn, $pIsSmoker, $pNoCigPack, $pIsAlcoholDrinker, $pNoBottles, $pIllDrugUser, $pUserId, $pTransNo, $getUpdCnt, $pIsSexuallyActive);
        /*End Family & Personal History Sub-module*/

        /*Start Immunizations Sub-module*/
        $pForChildren = $profiling['chkImmChild'];
        $pForAdult = $profiling['chkImmAdult'];
        $pForPregWoman = $profiling['chkImmPregnant'];
        $pForElderly = $profiling['chkImmElderly'];
        $pOthersImm = $profiling['txaImm'];

        /*No immunization*/
        if (count($pForChildren) == 0 && count($pForAdult) == 0 && count($pForPregWoman) == 0 && count($pForElderly) == 0) {
            insertImmunizations($conn, '999', '999', '999', '999', $pUserId, $pTransNo, null, $getUpdCnt);
        }

        /*For Children*/
        for ($i = 0; $i < count($pForChildren); $i++) {
            if ($pForChildren[$i] != '') {
                insertImmunizations($conn, $pForChildren[$i], null, null, null, $pUserId, $pTransNo, null, $getUpdCnt);
            } 
        }

        /*For Adult*/
        for ($i = 0; $i < count($pForAdult); $i++) {
            if ($pForAdult[$i] != '') {
                insertImmunizations($conn, null, $pForAdult[$i], null, null, $pUserId, $pTransNo, null, $getUpdCnt);
            } 
        }

        /*For Pregnant Woman*/
        for ($i = 0; $i < count($pForPregWoman); $i++) {
            if ($pForPregWoman[$i] != '') {
                insertImmunizations($conn, null, null, $pForPregWoman[$i], null, $pUserId, $pTransNo, null, $getUpdCnt);
            } 
        }

        /*For Elderly and Immunocompromised*/
        for ($i = 0; $i < count($pForElderly); $i++) {
            if ($pForElderly[$i] != '') {
                insertImmunizations($conn, null, null, null, $pForElderly[$i], $pUserId, $pTransNo, null, $getUpdCnt);
            } 
        }

        if (!empty($pOthersImm)) {
            insertImmunizations($conn, null, null, null, null, $pUserId, $pTransNo, $pOthersImm, $getUpdCnt);
        }
        /*End Immunizations Sub-module*/

        /*Start OB-Gyne History Sub-module*/
        /*Menstrual History*/
        $pMenarche = $profiling['txtOBHistMenarche'];

        if($profiling['txtOBHistLastMens'] != NULL){
            $pLastMensPeriod = date('Y-m-d',strtotime($profiling['txtOBHistLastMens']));
            $pIsMensApplicable = $profiling['mhDone'];
            $pPregIsApplicable = $profiling['pregDone'];
        } else{
            $pLastMensPeriod = NULL;
            $pIsMensApplicable = "N";
            $pPregIsApplicable = "N";
        }

        $pPeriodDuration = $profiling['txtOBHistPeriodDuration'];
        $pMensInterval = $profiling['txtOBHistInterval'];
        $pPadsPerDay = $profiling['txtOBHistPadsPerDay'];
        $pOnsetSexIC = $profiling['txtOBHistOnsetSexInt'];
        $pBirthControlMethod = $profiling['txtOBHistBirthControl'];
        $pIsMenopause = $profiling['radOBHistMenopause'];
        $pMenopauseAge = $profiling['txtOBHistMenopauseAge'];

        insertMenstrualHistory($conn, $pMenarche, $pLastMensPeriod, $pPeriodDuration, $pMensInterval, $pPadsPerDay, $pOnsetSexIC, $pBirthControlMethod, $pIsMenopause, $pMenopauseAge, $pUserId, $pTransNo, $getUpdCnt,$pIsMensApplicable);

        /*Pregnany History*/
        // $pPregIsApplicable = $profiling['pregDone'];
        $pPregCnt = $profiling['txtOBHistGravity'];
        $pDeliveryCnt = $profiling['txtOBHistParity'];
        $pDeliveryType = $profiling['optOBHistDelivery'];
        $pFullTermCnt = $profiling['txtOBHistFullTerm'];
        $pPrematureCnt = $profiling['txtOBHistPremature'];
        $pAbortionCnt = $profiling['txtOBHistAbortion'];
        $pLivChildrenCnt = $profiling['txtOBHistLivingChildren'];
        $pWithPregIndHyp = $profiling['chkOBHistPreEclampsiaValue'];
        /*Family Planning*/
        $pWithFamPlan = $profiling['radOBHistWFamPlan'];
        insertPrenancyHistory($conn, $pPregCnt, $pDeliveryCnt, $pDeliveryType, $pFullTermCnt, $pPrematureCnt, $pAbortionCnt, $pLivChildrenCnt, $pWithPregIndHyp, $pUserId, $pTransNo, $pWithFamPlan, $getUpdCnt, $pPregIsApplicable);
        /*End OB-Gyne History Sub-module*/

        /*Start Pertinent Physical Examination Findings Sub-module*/
        /*Physical Exam Findings: Adult and Pediatric Patient*/
        $pSystolic = $profiling['txtPhExSystolic'];
        $pDiastolic = $profiling['txtPhExBPDiastolic'];
        $pHr = $profiling['txtPhExHeartRate'];
        $pRr = $profiling['txtPhExRespiratoryRate'];
        if ($profiling['txtPhExHeightCm'] != null) {
            $pHeight = $profiling['txtPhExHeightCm'];
        } else {
            $pHeight = 0;
        }
        
        $pWeight = $profiling['txtPhExWeightKg'];
        $pVisionAquity = NULL;
        $pLength = $profiling['txtPhExLengthCm'];
        $pHeadCirc = $profiling['txtPhExHeadCircCm'];
        $pTemp = $profiling['txtPhExTemp'];
        $pVisionAquityLeft = $profiling['txtPhExVisualAcuityL'];
        $pVisionAquityRight = $profiling['txtPhExVisualAcuityR'];
        $pSkinThickness = $profiling['txtPhExSkinfoldCm'];
        $pWaist = $profiling['txtPhExBodyCircWaistCm'];
        $pHip = $profiling['txtPhExBodyCircHipsCm'];
        $pLimbs = $profiling['txtPhExBodyCircLimbsCm'];
        $pBMI = $profiling['txtPhExBMI'];
        //$pZScore = $profiling['txtPhExZscoreCm'];
        $pZScore = "";
        $pMidUpperArm = $profiling['txtPhExMidUpperArmCirc'];
        insertPertinentPhysicalExam($conn, $pSystolic, $pDiastolic, $pHr, $pRr, $pHeight, $pWeight, $pVisionAquity, $pLength, $pHeadCirc, $pUserId, $pTransNo, $getUpdCnt, $pTemp, $pVisionAquityLeft, $pVisionAquityRight, $pSkinThickness, $pWaist, $pHip, $pLimbs,$pBMI,$pZScore,$pMidUpperArm);


        /*Blood Type and Blood Rhesus*/
        $pBloodType = $profiling['radPhExBloodType'];
        $pBloodRh = NULL;
        insertBloodType($conn, $pBloodType, $pBloodRh, $pUserId, $pTransNo, $getUpdCnt);

        /*General Survey*/
        $pGenSurveyId = $profiling['pGenSurvey'];
        $pGenSurveyRem = $profiling['pGenSurveyRemarks'];
        insertPeGeneralSurvey($conn, $pGenSurveyId, strtoupper($pGenSurveyRem), $pUserId, $pTransNo);

        /*Physical Exam Misc*/
        $pSkin = $profiling['skinExtremities'];
        $pGenito = $profiling['genitourinary'];
        $pRectal = $profiling['rectal'];
        $pHeent = $profiling['heent'];
        $pChest = $profiling['chest'];
        $pHeart = $profiling['heart'];
        $pAbdomen = $profiling['abdomen'];
        $pNeuro = $profiling['neuro'];

        /*No Physical Exam Misc*/
        if (count($pSkin) == 0 && count($pGenito) == 0 && count($pRectal) == 0 && count($pHeent) == 0 && count($pChest) == 0 && count($pHeart) == 0 && count($pAbdomen) == 0 && count($pNeuro) == 0) {
            insertProfilePhysicalExamMisc($conn, null, null, null, null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
        }

        /*A. Heent*/
        for ($i = 0; $i < count($pHeent); $i++) {
            if ($pHeent[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, $pHeent[$i], null, null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*B. Chest/Lungs*/
        for ($i = 0; $i < count($pChest); $i++) {
            if ($pChest[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, null, $pChest[$i], null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*C. Heart*/
        for ($i = 0; $i < count($pHeart); $i++) {
            if ($pHeart[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, null, null, $pHeart[$i], null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*D. Abdomen*/
        for ($i = 0; $i < count($pAbdomen); $i++) {
            if ($pAbdomen[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, null, null, null, $pAbdomen[$i], null, null, null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*E. Genitourinary*/
        for ($i = 0; $i < count($pGenito); $i++) {
            if ($pGenito[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, null, null, null, null, null, $pGenito[$i], null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*F. Digital Rectal Examination*/
        for ($i = 0; $i < count($pRectal); $i++) {
            if ($pRectal[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, null, null, null, null, null, null, $pRectal[$i], $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*G. Skin/Extremities*/
        for ($i = 0; $i < count($pSkin); $i++) {
            if ($pSkin[$i] != '') {
                insertProfilePhysicalExamMisc($conn, $pSkin[$i], null, null, null, null, null, null, null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*H. Neurological*/
        for ($i = 0; $i < count($pNeuro); $i++) {
            if ($pNeuro[$i] != '') {
                insertProfilePhysicalExamMisc($conn, null, null, null, null, null, $pNeuro[$i], null, null, $pTransNo, $pUserId, $getUpdCnt);
            }
        }

        /*Remarks*/
        $pHeentRemarks = strtoupper($profiling['heent_remarks']);
        $pChestRemarks = strtoupper($profiling['chest_lungs_remarks']);
        $pHeartRemarks = strtoupper($profiling['heart_remarks']);
        $pAbdomenRemarks = strtoupper($profiling['abdomen_remarks']);
        $pGenitoRemarks = strtoupper($profiling['gu_remarks']);
        $pRectalRemarks = strtoupper($profiling['rectal_remarks']);
        $pSkinExtremitiesRemarks = strtoupper($profiling['skinExtremities_remarks']);
        $pNeuroRemarks = strtoupper($profiling['neuro_remarks']);
        insertProfilePhysicalExamMiscRemarks($conn, $pHeentRemarks, $pChestRemarks, $pHeartRemarks, $pAbdomenRemarks, $pGenitoRemarks, $pRectalRemarks, $pSkinExtremitiesRemarks, $pNeuroRemarks, $pTransNo, $pUserId, $getUpdCnt);
        /*End Pertinent Physical Examination Findings Sub-module*/
/*=============================================*/
        
        /*2020-03-27 Remove Laboratory Results, Plan/Management and Medicine functions for KONSULTA program*/

        /*Start NCD High-Risk Assessment Sub-module*/
        $pQ1 = $profiling['Q1'];
        $pQ2 = $profiling['Q2'];
        $pQ3 = $profiling['Q3'];
        $pQ4 = $profiling['Q4'];
        $pQ5 = $profiling['Q5'];
        $pQ511 = $profiling['Q5_1_1'];
        $pQ6 = $profiling['Q6'];
        $pQ7 = $profiling['Q7'];
        $pQ8 = $profiling['Q8'];
        $pQ67811 = $profiling['Q678_1_1'];
        $pQ67812 = $profiling['Q678_1_2'];
        $pQ67813 = $profiling['Q678_1_3'];
        if ($profiling['ncdRbgDate'] != null) {
            $pNcdRbgDate = date('Y-m-d', strtotime($profiling['ncdRbgDate']));
        } else {
            $pNcdRbgDate = null;
        }
        $pQ67821 = $profiling['Q678_2_1'];
        $pQ67822 = $profiling['Q678_2_2'];
        if ($profiling['ncdRblDate'] != null) {
            $pNcdRblDate = date('Y-m-d', strtotime($profiling['ncdRblDate']));
        } else {
            $pNcdRblDate = null;
        }
        $pQ67831 = $profiling['Q678_3_1'];
        $pQ67832 = $profiling['Q678_3_2'];
        if ($profiling['ncdUkDate'] != null) {
            $pNcdUkDate = date('Y-m-d', strtotime($profiling['ncdUkDate']));
        } else {
            $pNcdUkDate = null;
        }
        $pQ67841 = $profiling['Q678_4_1'];
        $pQ67842 = $profiling['Q678_4_2'];
        if ($profiling['ncdUpDate'] != null) {
            $pNcdUpDate = date('Y-m-d', strtotime($profiling['ncdUpDate']));
        } else {
            $pNcdUpDate = null;
        }
        $pQ23 = $profiling['Q23'];
        $pQ9 = $profiling['Q9'];
        $pQ10 = $profiling['Q10'];
        $pQ11 = $profiling['Q11'];
        $pQ12 = $profiling['Q12'];
        $pQ13 = $profiling['Q13'];
        $pQ14 = $profiling['Q14'];
        $pQ15 = $profiling['Q15'];
        $pQ24 = $profiling['Q24'];
        $pQ16 = $profiling['Q16'];
        $pQ17 = $profiling['Q17'];

        insertNcdHighRiskAssessment($conn, $pQ1, $pQ2, $pQ3, $pQ4, $pQ5, $pQ511, $pQ6, $pQ7, $pQ8, $pQ67811, $pQ67812, $pQ67813, $pNcdRbgDate, $pQ67821, $pQ67822, $pNcdRblDate, $pQ67831, $pQ67832, $pNcdUkDate, $pQ67841, $pQ67842, $pNcdUpDate, $pQ23, $pQ9, $pQ10, $pQ11, $pQ12, $pQ13, $pQ14, $pQ15, $pQ24, $pQ16, $pQ17, $pTransNo, $pUserId, $getUpdCnt);
        /*End NCD High-Risk Assessment Sub-module*/

        $conn->commit();

        if (isset($_POST['saveRecord'])) {            
            echo '<script>alert("Successfully saved!");window.location="hsa_list_of_all_patients.php";</script>';
        } else if (isset($_POST['saveFinalizeHSA'])) {
            echo '<script>alert("The client record was saved and finalized. Updating of record is no longer allowed");window.location="hsa_list_of_all_patients.php";</script>';
        }
        
       

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: Main Profiling " . $e->getMessage();
        echo '<script>alert("Error: Main Profiling '.$e->getMessage().'");</script>';
    }

    $conn = null;
}
/*Update Patient Information*/
function updateProfilingInfo($conn, $pTransNo,$pPatientPin,$pPatientType,$pMemPin,$pUserId,$pEffYear,$getUpdCnt,$pFinalizedData, $pProfDate){
    $ini = parse_ini_file("config.ini");

    try {
        // $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE ".$ini['EPCB'].".TSEKAP_TBL_PROFILE
                                    SET PX_PIN = :pxPin, 
                                        PX_TYPE = :pxType,
                                        MEM_PIN = :memPin,
                                        PROF_DATE = :profDate,
                                        PROF_BY = :profBy,
                                        EFF_YEAR = :effYear,
                                        UPD_CNT = :updCnt,
                                        IS_FINALIZE = :finalize
                                    WHERE TRANS_NO = :transNo");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':pxPin', $pPatientPin);
        $stmt->bindParam(':pxType', $pPatientType);
        $stmt->bindParam(':memPin', $pMemPin);
        $stmt->bindParam(':profBy', $pUserId);
        $stmt->bindParam(':effYear', $pEffYear);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':finalize', $pFinalizedData);
        $stmt->bindParam(':profDate', $pProfDate);
        $stmt->execute();

    } catch (PDOException $e) {
        // $conn->rollBack();
        // echo "Error: InsertUpdateHSA01-01 - " . $e->getMessage();
        echo '<script>alert("Error: InsertUpdateHSA01-01 - '.$e->getMessage().'");</script>';
    }

    // $conn = null;
}

/*Plan/Management Sub-module in HSA*/
/*Plan/Management - Diagnosis Examination*/
function insertProfilingDiagnosticExamination($pDiagnostic, $pOthRemarks, $pFacility, $pReferralFacility, $pUserId, $pTransNo, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_DIAGNOSTIC(
                                DIAGNOSTIC_ID, DATE_ADDED, ADDED_BY, TRANS_NO, OTH_REMARKS, FACILITY, REFERRAl_FACILITY, UPD_CNT) 
                                  VALUES(:diagnosticId, 
                                         NOW(), 
                                         :addedBy,
                                         :transNo,
                                         :othRemarks,
                                         :facility,
                                         :referralfacility,
                                         :updCnt)");

        $stmt->bindParam(':diagnosticId', $pDiagnostic);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':othRemarks', strtoupper($pOthRemarks));
        $stmt->bindParam(':facility', $pFacility);
        $stmt->bindParam(':referralfacility', strtoupper($pReferralFacility));
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: C08" . $e->getMessage();
        echo '<script>alert("Error: C08'.$e->getMessage().'");</script>';
    }

    $conn = null;
}


/* Plan/Management - Management */
function insertProfilingManagement($pManagement, $pUserId, $pTransNo, $pOthMgmtRemarks,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MANAGEMENT(
                   MANAGEMENT_ID, DATE_ADDED, ADDED_BY, TRANS_NO, OTH_REMARKS,UPD_CNT) 
                      VALUES(:managementId, 
                             NOW(), 
                             :addedBy, 
                             :transNo, 
                             :othRemarks,
                             :updCnt)");

        $stmt->bindParam(':managementId', $pManagement);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':othRemarks', $pOthMgmtRemarks);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: C09" . $e->getMessage();
        echo '<script>alert("Error: C09'.$e->getMessage().'");</script>';
    }

    $conn = null;
}
/* Plan/Management - Management */
function insertProfilingAdvice($pAdviceRemarks, $pUserId, $pTransNo, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_ADVICE(
                           REMARKS, DATE_ADDED, ADDED_BY, TRANS_NO, UPD_CNT) 
                              VALUES(:remarks, 
                                     NOW(), 
                                     :addedBy, 
                                     :transNo,
                                     :updCnt)");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':remarks', strtoupper($pAdviceRemarks));
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: C10" . $e->getMessage();
        echo '<script>alert("Error: C10'.$e->getMessage().'");</script>';
    }

    $conn = null;
}

/*Insert Patient Information*/
function insertProfilingInfo($conn, $pCaseNo,$pTransNo,$pProfDate, $pHciNo,$pPatientPin,$pPatientType,$pMemPin,$pUserId,$pEffYear,$pOTP,$pFinalizedData, $pXPSmodule, $pIsWithATC){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROFILE(
                        CASE_NO, TRANS_NO, HCI_NO, PX_PIN, PX_TYPE, MEM_PIN, PROF_DATE, PROF_BY, EFF_YEAR, DATE_ADDED, PROFILE_OTP, IS_FINALIZE, XPS_MODULE, WITH_ATC) 
                          VALUES(:caseNo, 
                                 :transNo, 
                                 :hciNo,
                                 :pxPin, 
                                 :pxType, 
                                 :memPin, 
                                 :profDate, 
                                 :profBy,
                                 :effYear,
                                 NOW(),
                                 :otp,
                                 :finalize,
                                 :xpsMod,
                                 :withAtc)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':profDate', $pProfDate);
        $stmt->bindParam(':hciNo', $pHciNo);
        $stmt->bindParam(':pxPin', $pPatientPin);
        $stmt->bindParam(':pxType', $pPatientType);
        $stmt->bindParam(':memPin', $pMemPin);
        $stmt->bindParam(':profBy', $pUserId);
        $stmt->bindParam(':effYear', $pEffYear);
        $stmt->bindParam(':otp', $pOTP);
        $stmt->bindParam(':finalize', $pFinalizedData);
        $stmt->bindParam(':xpsMod', $pXPSmodule);
        $stmt->bindParam(':withAtc', $pIsWithATC);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA01-01 - '.$e->getMessage().'");</script>';
    }
}

/*Insert Other Info in Patient Tab */
function insertProfilingOtherInfo($conn, $pPxAge,$pPxOccupation,$pPxEducation,$pUserId,$pTransNo,$pPxPoB,$pPxReligion,$pMomMLastName,$pMomMMiddleName,$pMomFirstname,$pMomExtName,$pMomDob,$pDadLastName,$pDadMiddleName,$pDadFirstname,$pDadExtName,$pDadDob,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_OINFO(
                        PX_AGE,PX_OCCUPATION,PX_EDUCATION,DATE_ADDED,ADDED_BY,TRANS_NO,PX_POB,PX_RELIGION,PX_MOTHER_MNLN,PX_MOTHER_MNMI,PX_MOTHER_FN,PX_MOTHER_EXTN,
                        PX_FATHER_LN,PX_FATHER_MI,PX_FATHER_FN,PX_FATHER_EXTN,PX_MOTHER_BDAY,PX_FATHER_BDAY, UPD_CNT) 
                          VALUES(:pxAge,
                                 :pxOccupation,
                                 :pxEducation,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :pxPob,
                                 :pxReligion,
                                 :pxMomLname,
                                 :pxMomMname,
                                 :pxMomFname,
                                 :pxMomExtName,
                                 :pxDadLname,
                                 :pxDadMname,
                                 :pxDadFname,
                                 :pxDadExtName,
                                 :pxMomBday,
                                 :pxDadBday,
                                 :updCnt)");

        $stmt->bindParam(':pxAge', $pPxAge);
        $stmt->bindParam(':pxOccupation', $pPxOccupation);
        $stmt->bindParam(':pxEducation', $pPxEducation);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':pxPob', $pPxPoB);
        $stmt->bindParam(':pxReligion', $pPxReligion);
        $stmt->bindParam(':pxMomLname', $pMomMLastName);
        $stmt->bindParam(':pxMomMname', $pMomMMiddleName);
        $stmt->bindParam(':pxMomFname', $pMomFirstname);
        $stmt->bindParam(':pxMomExtName', $pMomExtName);
        $stmt->bindParam(':pxDadLname', $pDadLastName);
        $stmt->bindParam(':pxDadMname', $pDadMiddleName);
        $stmt->bindParam(':pxDadFname', $pDadFirstname);
        $stmt->bindParam(':pxDadExtName', $pDadExtName);
        $stmt->bindParam(':pxMomBday', $pMomDob);
        $stmt->bindParam(':pxDadBday', $pDadDob);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA01-02 - '.$e->getMessage().'");</script>';
    }

}

/*Insert Past Medical History*/
function insertPastMedicalHistory($conn, $pPastMedHistory,$pUserId,$pTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST(
                        MDISEASE_CODE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT) 
                          VALUES(:mDiseaseCode,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt)");

        $stmt->bindParam(':mDiseaseCode', $pPastMedHistory);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA02-01 - '.$e->getMessage().'");</script>';
    }

}

/*Insert Specific Past Medical History*/
function insertPastMedicalHistorySpecific($conn, $pPastMedHistory,$pSpecificDesc,$pTransNo,$pUserId,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC(
                        MDISEASE_CODE,SPECIFIC_DESC,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT) 
                          VALUES(:mDiseaseCode,
                                 :specificDesc,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt)");

        $stmt->bindParam(':mDiseaseCode', $pPastMedHistory);
        $stmt->bindParam(':specificDesc', strtoupper($pSpecificDesc));
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA02-02 - '.$e->getMessage().'");</script>';
    }

}

/*Insert Past Surgical History*/
function insertPastSurgicalHistory($conn, $pOperation,$pOperationDate,$pUserId,$pTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST(
                        SURG_DESC,SURG_DATE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT) 
                          VALUES(:surgHist,
                                 :surgDate,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt)");

        $stmt->bindParam(':surgHist', strtoupper($pOperation));
        $stmt->bindParam(':surgDate', $pOperationDate);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA02-03 - '.$e->getMessage().'");</script>';
    }
}

/*Insert Family Medical History*/
function insertFamilyMedicalHistory($conn, $pFamMedHistory,$pUserId,$pTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST(
                        MDISEASE_CODE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT) 
                          VALUES(:mDiseaseCode,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt)");

        $stmt->bindParam(':mDiseaseCode', $pFamMedHistory);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA03-01 - '.$e->getMessage().'");</script>';
    }
}

/*Insert Specific Family Medical History*/
function insertFamilyMedicalHistorySpecific($conn, $pFamMedHistory,$pSpecificDesc,$pTransNo,$pUserId,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC(
                        MDISEASE_CODE,SPECIFIC_DESC,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT) 
                          VALUES(:mDiseaseCode,
                                 :specificDesc,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt)");

        $stmt->bindParam(':mDiseaseCode', $pFamMedHistory);
        $stmt->bindParam(':specificDesc', $pSpecificDesc);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA03-02 - '.$e->getMessage().'");</script>';
    }
}
/*Insert Personal/ Social History*/
function insertPersonalSocialHistory($conn, $pIsSmoker,$pNoCigPack,$pIsAlcoholDrinker,$pNoBottles,$pIllDrugUser,$pUserId,$pTransNo,$getUpdCnt, $pIsSexuallyActive){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST(
                        IS_SMOKER, NO_CIGPK, IS_ADRINKER, NO_BOTTLES, ILL_DRUG_USER, DATE_ADDED, ADDED_BY, TRANS_NO, UPD_CNT, IS_SEXUALLY_ACTIVE)
                          VALUES(:isSmoker,
                                 :noCigPack,
                                 :isAlDrinker,
                                 :noBottles,
                                 :illDrugUser,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt,
                                 :sexuallyActive)");

        $stmt->bindParam(':isSmoker', $pIsSmoker);
        $stmt->bindParam(':noCigPack', $pNoCigPack);
        $stmt->bindParam(':isAlDrinker', $pIsAlcoholDrinker);
        $stmt->bindParam(':noBottles', $pNoBottles);
        $stmt->bindParam(':illDrugUser', $pIllDrugUser);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':sexuallyActive', $pIsSexuallyActive);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA03-03 - '.$e->getMessage().'");</script>';
    }
}

/*Insert Immunizations*/
function insertImmunizations($conn, $pForChildren,$pForAdult,$pForPregWoman,$pForElderly,$pUserId,$pTransNo,$pOthersImm,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION(
                        CHILD_IMMCODE,YOUNGW_IMMCODE,PREGW_IMMCODE,ELDERLY_IMMCODE,DATE_ADDED,ADDED_BY,TRANS_NO,OTHER_IMM,UPD_CNT)
                          VALUES(:childImmCode,
                                 :youngImmCode,
                                 :pregwImmCode,
                                 :elderlyImmCode,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :otherImmCode,
                                 :updCnt)");

        $stmt->bindParam(':childImmCode', $pForChildren);
        $stmt->bindParam(':youngImmCode', $pForAdult);
        $stmt->bindParam(':pregwImmCode', $pForPregWoman);
        $stmt->bindParam(':elderlyImmCode', $pForElderly);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':otherImmCode', strtoupper($pOthersImm));
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();


    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA04-01 - '.$e->getMessage().'");</script>';
    }
}

/*Insert Menstrual History*/
function insertMenstrualHistory($conn, $pMenarche,$pLastMensPeriod,$pPeriodDuration,$pMensInterval,$pPadsPerDay,$pOnsetSexIC,$pBirthControlMethod,$pIsMenopause,$pMenopauseAge,$pUserId,$pTransNo,$getUpdCnt, $pIsApplicable){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_MENSHIST(
                        MENARCHE_PERIOD,LAST_MENS_PERIOD,PERIOD_DURATION,MENS_INTERVAL,PADS_PER_DAY,ONSET_SEX_IC,BIRTH_CTRL_METHOD,IS_MENOPAUSE,MENOPAUSE_AGE,DATE_ADDED,ADDED_BY,TRANS_NO,UPD_CNT,IS_APPLICABLE)
                          VALUES(:menarche,
                                 :lastMensPeriod,
                                 :periodDuration,
                                 :mensInterval,
                                 :padsPerDay,
                                 :onsetSexInt,
                                 :birthCtrlMethod,
                                 :isMenopause,
                                 :menopauseAge,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :updCnt,
                                 :isApplicable)");

        $stmt->bindParam(':menarche', $pMenarche);
        $stmt->bindParam(':lastMensPeriod', $pLastMensPeriod);
        $stmt->bindParam(':periodDuration', $pPeriodDuration);
        $stmt->bindParam(':mensInterval', $pMensInterval);
        $stmt->bindParam(':padsPerDay', $pPadsPerDay);
        $stmt->bindParam(':onsetSexInt', $pOnsetSexIC);
        $stmt->bindParam(':birthCtrlMethod', $pBirthControlMethod);
        $stmt->bindParam(':isMenopause', $pIsMenopause);
        $stmt->bindParam(':menopauseAge', $pMenopauseAge);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':isApplicable', $pIsApplicable);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA05-01 - '.$e->getMessage().'");</script>';
    }

}

/*Insert Pregnancy History and Family Planning*/
function insertPrenancyHistory($conn, $pPregCnt,$pDeliveryCnt,$pDeliveryType,$pFullTermCnt,$pPrematureCnt,$pAbortionCnt,$pLivChildrenCnt,$pWithPregIndHyp,$pUserId,$pTransNo,$pWithFamPlan,$getUpdCnt, $pPregIsApplicable){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PREGHIST(
                        PREG_CNT,DELIVERY_CNT,DELIVERY_TYP,FULL_TERM_CNT,PREMATURE_CNT,ABORTION_CNT,LIV_CHILDREN_CNT,W_PREG_INDHYP,DATE_ADDED,ADDED_BY,TRANS_NO,W_FAM_PLAN,UPD_CNT,IS_APPLICABLE)
                          VALUES(:pregCnt,
                                 :deliveryCnt,
                                 :deliverytype,
                                 :fullTermCnt,
                                 :prematureCnt,
                                 :abortionCnt,
                                 :livChildrenCnt,
                                 :wPregIndHyp,
                                 NOW(),
                                 :addedBy,
                                 :transNo,
                                 :wFamPlan,
                                 :updCnt,
                                 :isApplicable)");

        $stmt->bindParam(':pregCnt', $pPregCnt);
        $stmt->bindParam(':deliveryCnt', $pDeliveryCnt);
        $stmt->bindParam(':deliverytype', $pDeliveryType);
        $stmt->bindParam(':fullTermCnt', $pFullTermCnt);
        $stmt->bindParam(':prematureCnt', $pPrematureCnt);
        $stmt->bindParam(':abortionCnt', $pAbortionCnt);
        $stmt->bindParam(':livChildrenCnt', $pLivChildrenCnt);
        $stmt->bindParam(':wPregIndHyp', $pWithPregIndHyp);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':wFamPlan', $pWithFamPlan);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':isApplicable', $pPregIsApplicable);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA05-02 - '.$e->getMessage().'");</script>';
    }

}

/*Insert Pertinent Physical Examination Findings*/
function insertPertinentPhysicalExam($conn, $pSystolic,$pDiastolic,$pHr,$pRr,$pHeight,$pWeight,$pVisionAquity,$pLength,$pHeadCirc,$pUserId,$pTransNo,$getUpdCnt,$pTemperature, $pVisionAquityLeft, $pVisionAquityRight, $pSkinThickness, $pWaist, $pHip, $pLimbs, $pBMI, $pZScore,$pMidUpperArm){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT(
                        SYSTOLIC,DIASTOLIC,HR,RR,HEIGHT,WEIGHT,TEMPERATURE,DATE_ADDED,ADDED_BY,TRANS_NO,VISION,LENGTH,HEAD_CIRC,UPD_CNT,
                        LEFT_VISUAL_ACUITY, RIGHT_VISUAL_ACUITY, SKIN_THICKNESS, WAIST, HIP, LIMBS,BMI,Z_SCORE,MID_UPPER_ARM)
                          VALUES(:systolic, 
                                 :diastolic, 
                                 :hr, 
                                 :rr, 
                                 :height, 
                                 :weight, 
                                 :temp, 
                                 NOW(), 
                                 :addedBy, 
                                 :transNo, 
                                 :vision, 
                                 :length, 
                                 :headCirc,
                                 :updCnt,
                                 :visionLeft,
                                 :visionRight,
                                 :skinThickness,
                                 :waist,
                                 :hip,
                                 :limbs,
                                 :bmi,
                                 :zscore,
                                 :midupperarm)");

        $stmt->bindParam(':systolic', $pSystolic);
        $stmt->bindParam(':diastolic', $pDiastolic);
        $stmt->bindParam(':hr', $pHr);
        $stmt->bindParam(':rr', $pRr);
        $stmt->bindParam(':height', $pHeight);
        $stmt->bindParam(':weight', $pWeight);
        $stmt->bindParam(':temp',$pTemperature);
        $stmt->bindParam(':vision', $pVisionAquity);
        $stmt->bindParam(':length', $pLength);
        $stmt->bindParam(':headCirc', $pHeadCirc);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->bindParam(':visionLeft', $pVisionAquityLeft);
        $stmt->bindParam(':visionRight', $pVisionAquityRight);
        $stmt->bindParam(':skinThickness', $pSkinThickness);
        $stmt->bindParam(':waist', $pWaist);
        $stmt->bindParam(':hip', $pHip);
        $stmt->bindParam(':limbs', $pLimbs);
        $stmt->bindParam(':bmi', $pBMI);
        $stmt->bindParam(':zscore', $pZScore);
        $stmt->bindParam(':midupperarm', $pMidUpperArm);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSA06-01 - '.$e->getMessage().'");</script>';
    }

}

/*Insert General Survey*/
function insertPeGeneralSurvey($conn, $pGenSurveyId, $pGenSurveyRem, $pUserId, $pTransNo){
    $ini = parse_ini_file("config.ini");

    try {

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEGENSURVEY(
                        TRANS_NO,GENSURVEY_ID,GENSURVEY_REM,DATE_ADDED,ADDED_BY)
                          VALUES(:transNo, 
                                 :genSurvey, 
                                 :genSurveyRem, 
                                 NOW(),
                                 :addedBy)");

        $stmt->bindParam(':genSurvey', $pGenSurveyId);
        $stmt->bindParam(':genSurveyRem', $pGenSurveyRem);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->execute();

    } catch (PDOException $e) {
        echo '<script>alert("Error: InsertHSAGenSurvey - '.$e->getMessage().'");</script>';
    }

}

/*Insert General Survey*/
function insertCourseInTheWard($pActionDate, $pDocActionOrder, $pUserId, $pTransNo, $pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_COURSE_WARD(
                        CASE_NO, TRANS_NO,ACTION_DATE,DOCTORS_ACTION,DATE_ADDED,ADDED_BY)
                          VALUES(:caseNo,
                                 :transNo, 
                                 :actDate, 
                                 :docOrder, 
                                 NOW(),
                                 :addedBy)");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->bindParam(':actDate', date('Y-m-d', strtotime($pActionDate)));
        $stmt->bindParam(':docOrder', strtoupper($pDocActionOrder));
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->execute();

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: InsertCourseWard - " . $e->getMessage();
        echo '<script>alert("Error: InsertCourseWard - '.$e->getMessage().'");</script>';
    }

    $conn = null;
}
/*Insert Profile Blood Type*/
function insertBloodType($conn, $pBloodType,$pBloodRh,$pUserId,$pTransNo,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        // $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_BLOODTYPE(
                        TRANS_NO,BLOOD_TYPE,BLOOD_RH,DATE_ADDED,ADDED_BY,UPD_CNT)
                          VALUES(:transNo, 
                                 :bloodType, 
                                 :bloodRh, 
                                 NOW(),
                                 :addedBy,
                                 :updCnt)");

        $stmt->bindParam(':bloodType', $pBloodType);
        $stmt->bindParam(':bloodRh', $pBloodRh);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        // $conn->rollBack();
        // echo "Error: InsertHSA06-02 - " . $e->getMessage();
        echo '<script>alert("Error: InsertHSA06-02 - '.$e->getMessage().'");</script>';
    }

    // $conn = null;
}

/*Insert Profile Physical Examination Miscellaneous */
function insertProfilePhysicalExamMisc($conn, $pSkin, $pHeent, $pChest, $pHeart, $pAbdomen, $pNeuro, $pGU, $pRectal, $pTransNo, $pUserId, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        // $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC(
                        SKIN_ID, HEENT_ID, CHEST_ID, HEART_ID, ABDOMEN_ID, NEURO_ID, GU_ID, RECTAL_ID, TRANS_NO, DATE_ADDED, ADDED_BY, UPD_CNT) 
                          VALUES(:skinId, 
                                 :heentId, 
                                 :chestId, 
                                 :heartId, 
                                 :abdomenId, 
                                 :neuroId, 
                                 :guId,
                                 :rectalId,
                                 :transNo, 
                                 NOW(), 
                                 :addedBy,
                                 :updCnt)");

        $stmt->bindParam(':skinId', $pSkin);
        $stmt->bindParam(':heentId',$pHeent);
        $stmt->bindParam(':chestId', $pChest);
        $stmt->bindParam(':heartId', $pHeart);
        $stmt->bindParam(':abdomenId', $pAbdomen);
        $stmt->bindParam(':neuroId', $pNeuro);
        $stmt->bindParam(':guId', $pGU);
        $stmt->bindParam(':rectalId', $pRectal);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        // $conn->rollBack();
        // echo "Error: InsertHSA06-03 - " . $e->getMessage();
        echo '<script>alert("Error: InsertHSA06-03 - '.$e->getMessage().'");</script>';
    }

    // $conn = null;
}

/*Insert Physical Examination Misc Remarks*/
function insertProfilePhysicalExamMiscRemarks($conn, $pHeentRemarks, $pChestRemarks, $pHeartRemarks, $pAbdomenRemarks, $pGenitoRemarks, $pRectalRemarks, $pSkinExtremitiesRemarks,$pNeuroRemarks, $pTransNo, $pUserId, $getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        // $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC(
                                SKIN_REM, HEENT_REM, CHEST_REM, HEART_REM, ABDOMEN_REM, TRANS_NO, DATE_ADDED, ADDED_BY, NEURO_REM, GU_REM, RECTAL_REM, UPD_CNT) 
                                  VALUES(:skinRem, 
                                         :heentRem, 
                                         :chestRem, 
                                         :heartRem, 
                                         :abdomenRem, 
                                         :transNo,
                                         NOW(), 
                                         :addedBy,
                                         :neuroRem,
                                         :guRem,
                                         :rectalRem,
                                         :updCnt)");

        $stmt->bindParam(':skinRem', $pSkinExtremitiesRemarks);
        $stmt->bindParam(':heentRem', $pHeentRemarks);
        $stmt->bindParam(':chestRem', $pChestRemarks);
        $stmt->bindParam(':heartRem', $pHeartRemarks);
        $stmt->bindParam(':abdomenRem', $pAbdomenRemarks);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':neuroRem',$pNeuroRemarks);
        $stmt->bindParam(':guRem',$pGenitoRemarks);
        $stmt->bindParam(':rectalRem',$pRectalRemarks);
        $stmt->bindParam(':updCnt',$getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        // $conn->rollBack();
        // echo "Error: InsertHSA06-04 - " . $e->getMessage();
        echo '<script>alert("Error: InsertHSA06-04 - '.$e->getMessage().'");</script>';
    }

    // $conn = null;
}

/*Insert NCD High-risk Assessment*/
function insertNcdHighRiskAssessment($conn, $pQ1,$pQ2,$pQ3,$pQ4,$pQ5,$pQ511,$pQ6,$pQ7,$pQ8,$pQ67811,$pQ67812,$pQ67813,$pNcdRbgDate,$pQ67821,$pQ67822,$pNcdRblDate,
                                     $pQ67831,$pQ67832,$pNcdUkDate,$pQ67841,$pQ67842,$pNcdUpDate,$pQ23,$pQ9,$pQ10,$pQ11,$pQ12,$pQ13,$pQ14,$pQ15,$pQ24,$pQ16,$pQ17,$pTransNo, $pUserId,$getUpdCnt){
    $ini = parse_ini_file("config.ini");

    try {
        // $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        // $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_PROF_NCDQANS(
                                QID1_YN,QID2_YN,QID3_YN,QID4_YN,QID5_YNX,QID6_YN,QID7_YN,QID8_YN,QID9_YN,QID10_YN,QID11_YN,QID12_YN,QID13_YN,QID14_YN,QID15_YN,QID16_YN,
                                QID17_ABCDE,QID18_YN,QID19_YN,QID19_FBSMG,QID19_FBSMMOL,QID19_FBSDATE,QID20_YN,QID20_CHOLEVAL,QID20_CHOLEDATE,QID21_YN,
                                QID21_KETONVAL,QID21_KETONDATE,QID22_YN,QID22_PROTEINVAL,QID22_PROTEINDATE,QID23_YN,QID24_YN,TRANS_NO,DATE_ADDED,ADDED_BY,UPD_CNT) 
                                  VALUES(:qId1_yn,
                                         :qId2_yn,
                                         :qId3_yn,
                                         :qId4_yn,
                                         :qId5_ynx,
                                         :qId6_yn,
                                         :qId7_yn,
                                         :qId8_yn,
                                         :qId9_yn,
                                         :qId10_yn,
                                         :qId11_yn,
                                         :qId12_yn,
                                         :qId13_yn,
                                         :qId14_yn,
                                         :qId15_yn,
                                         :qId16_yn,
                                         :qId17_abcde,
                                         :qId18_yn,
                                         :qId19_yn,
                                         :qId19_fbsMg,
                                         :qId19_fbsMmol,
                                         :qId19_fbsDate,
                                         :qId20_yn,
                                         :qId20_choleVal,
                                         :qId20_choleDate,
                                         :qId21_yn,
                                         :qId21_ketonVal,
                                         :qId21_ketonDate,
                                         :qId22_yn,
                                         :qId22_proteinVal,
                                         :qId22_proteinDate,
                                         :qId23_yn,
                                         :qId24_yn,                                         
                                         :transNo,
                                         NOW(), 
                                         :addedBy,
                                         :updCnt)");

        $stmt->bindParam(':qId1_yn', $pQ1);
        $stmt->bindParam(':qId2_yn', $pQ2);
        $stmt->bindParam(':qId3_yn', $pQ3);
        $stmt->bindParam(':qId4_yn', $pQ4);
        $stmt->bindParam(':qId5_ynx', $pQ5);
        $stmt->bindParam(':qId6_yn', $pQ6);
        $stmt->bindParam(':qId7_yn', $pQ7);
        $stmt->bindParam(':qId8_yn', $pQ8);
        $stmt->bindParam(':qId9_yn', $pQ9);
        $stmt->bindParam(':qId10_yn', $pQ10);
        $stmt->bindParam(':qId11_yn', $pQ11);
        $stmt->bindParam(':qId12_yn', $pQ12);
        $stmt->bindParam(':qId13_yn', $pQ13);
        $stmt->bindParam(':qId14_yn', $pQ14);
        $stmt->bindParam(':qId15_yn', $pQ15);
        $stmt->bindParam(':qId16_yn', $pQ16);
        $stmt->bindParam(':qId17_abcde', $pQ17);
        $stmt->bindParam(':qId19_yn', $pQ67811);
        $stmt->bindParam(':qId18_yn', $pQ511);
        $stmt->bindParam(':qId19_fbsMg', $pQ67812);
        $stmt->bindParam(':qId19_fbsMmol', $pQ67813);
        $stmt->bindParam(':qId19_fbsDate', $pNcdRbgDate);
        $stmt->bindParam(':qId20_yn', $pQ67821);
        $stmt->bindParam(':qId20_choleVal', $pQ67822);
        $stmt->bindParam(':qId20_choleDate', $pNcdRblDate);
        $stmt->bindParam(':qId21_yn', $pQ67831);
        $stmt->bindParam(':qId21_ketonVal', $pQ67832);
        $stmt->bindParam(':qId21_ketonDate', $pNcdUkDate);
        $stmt->bindParam(':qId22_yn', $pQ67841);
        $stmt->bindParam(':qId22_proteinVal', $pQ67842);
        $stmt->bindParam(':qId22_proteinDate', $pNcdUpDate);
        $stmt->bindParam(':qId23_yn', $pQ23);
        $stmt->bindParam(':qId24_yn', $pQ24);
        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->bindParam(':addedBy', $pUserId);
        $stmt->bindParam(':updCnt', $getUpdCnt);
        $stmt->execute();

    } catch (PDOException $e) {
        // $conn->rollBack();
        // echo "Error: InsertHSA07-01 - " . $e->getMessage();
        echo '<script>alert("Error: InsertHSA07-01 - '.$e->getMessage().'");</script>';
    }

    // $conn = null;
}

/*Get Updated Count Value in Registration per Transaction*/
function getUpdCntRegistration($pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT MAX(UPD_CNT) AS UPD_CNT
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                WHERE CASE_NO = :caseNo");

        $stmt->bindParam(':caseNo', $pCaseNo);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;
}

/*Get Updated Count Value in Profiling per Transaction*/
function getUpdCntProfiling($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT MAX(UPD_CNT) AS UPD_CNT
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE
                                WHERE TRANS_NO = :transNo");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;
}

/*Get Updated Count Value in Consultation per Transaction*/
function getUpdCntConsultation($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT UPD_CNT AS UPD_CNT
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP
                                WHERE TRANS_NO = :transNo");

        $stmt->bindParam(':transNo', $pTransNo);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;
}

/*Get Updated Count Value in Consultation per Transaction*/
function getPackageType(){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_LIB_PACKAGE_TYPE 
                                WHERE LIB_STATUS = 1
                                  AND PACKAGE_ID IN ('E', 'P')
                                  ORDER BY LIB_SORT ASC");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;
}

/*Insert XML Member Assignment*/
function uploadMemberAssignment($upload){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        /*Start Parsing XML data into table*/
        libxml_use_internal_errors(true);
        $xml=simplexml_load_string(utf8_encode($upload['uploadAssignment'])); //or simplexml_load_file

        $userName = (string)$xml['pUsername'];
        $userPassword = (string)$xml['pPassword'];
        $accreNo = $xml['pHciAccreNo'];
        $countAssignment = (string)$xml['pAssignmentTotalCnt'];
        $trasmittalNo = (string)$xml['pReportTransmittalNumber'];
        // $rangeStartDate = (string)$xml['pStartDate'];
        // $rangeEndDate = (string)$xml['pEndDate'];
        // $rangeDate = $rangeStartDate.'-'.$rangeEndDate;
        $rangeDate = NULL;

        if($accreNo == $_SESSION['pAccreNum']) {
            /*Start Save File Uploaded*/
            $pUploadId = generateTransNo('UPLOAD_ID');
            $pUploadModule = "ASSIGNMENT";

            $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_UPLOAD(
                                              UPLOAD_ID, UPLOAD_XML, UPLOAD_MODULE, DATE_UPLOADED, RANGE_DATE)
                                        VALUES(:uploadId,
                                               :uploadXml,
                                               :uploadModule,
                                               NOW(),
                                               :rangeDate)");

            $stmt->bindParam(':uploadId', $pUploadId);
            $stmt->bindParam(':uploadModule', $pUploadModule);
            $stmt->bindParam(':uploadXml', $upload['uploadAssignment']);
            $stmt->bindParam(':rangeDate', $rangeDate);
            $stmt->execute();
            /*End Save File Uploaded*/

            foreach ($xml->ASSIGNMENT as $assign) {
                    $stmt2 = $conn->prepare("INSERT INTO " . $ini['EPCB'] . ".TSEKAP_TBL_ASSIGN(
                                    ACCRE_NO, EFF_YEAR, PACKAGE_TYPE, ASSIGNED_PIN, ASSIGNED_LAST_NAME, ASSIGNED_FIRST_NAME, ASSIGNED_MIDDLE_NAME, ASSIGNED_EXT_NAME, ASSIGNED_DOB, ASSIGNED_SEX, ASSIGNED_TYPE, PRIMARY_PIN, PRIMARY_LAST_NAME, PRIMARY_FIRST_NAME, PRIMARY_MIDDLE_NAME, PRIMARY_EXT_NAME, PRIMARY_DOB, PRIMARY_SEX, MOBILE_NUMBER, LANDLINE_NUMBER, MEM_NCAT, MEM_NCAT_DESC, ASSIGNED_DATE, ASSIGNED_STATUS, CREATED_DATE, PH_REPORT_TRANS_NO)
                                VALUES(:accreno,
                                       :effYear,
                                       :packageType,
                                       :assignedPin,
                                       :assignedLastName,
                                       :assignedFirstName,
                                       :assignedMiddleName,
                                       :assignedExtName,
                                       :assignedDateOfBirth,   
                                       :assignedSex,                                     
                                       :assignedType,
                                       :primaryPin,
                                       :primaryLastName,
                                       :primaryFirstName,
                                       :primaryMiddleName,
                                       :primaryExtName,
                                       :primaryDateOfBirth,
                                       :primarySex,
                                       :mobileNumber,
                                       :landlineNumber,
                                       :memncat,
                                       :memncatdesc,
                                       :assignedDate,
                                       :assignedStatus,
                                       NOW(),
                                       :reportNumber
                                       ) ON DUPLICATE KEY UPDATE
                                            ACCRE_NO = :accreno,
                                            EFF_YEAR = :effYear,
                                            PACKAGE_TYPE = :packageType,
                                            ASSIGNED_PIN = :assignedPin,
                                            ASSIGNED_LAST_NAME = :assignedLastName,
                                            ASSIGNED_FIRST_NAME = :assignedFirstName,
                                            ASSIGNED_MIDDLE_NAME = :assignedMiddleName,
                                            ASSIGNED_EXT_NAME = :assignedExtName,
                                            ASSIGNED_DOB = :assignedDateOfBirth, 
                                            ASSIGNED_SEX = :assignedSex,  
                                            ASSIGNED_TYPE = :assignedType,
                                            PRIMARY_PIN = :primaryPin,
                                            PRIMARY_LAST_NAME = :primaryLastName,
                                            PRIMARY_FIRST_NAME = :primaryFirstName,
                                            PRIMARY_MIDDLE_NAME = :primaryMiddleName,
                                            PRIMARY_EXT_NAME = :primaryExtName,
                                            PRIMARY_DOB = :primaryDateOfBirth,
                                            PRIMARY_SEX = :primarySex,
                                            MOBILE_NUMBER = :mobileNumber,
                                            LANDLINE_NUMBER = :landlineNumber,
                                            MEM_NCAT = :memncat,
                                            MEM_NCAT_DESC = :memncatdesc,
                                            ASSIGNED_DATE = :assignedDate,
                                            ASSIGNED_STATUS = :assignedStatus,
                                            CREATED_DATE = NOW(),
                                            PH_REPORT_TRANS_NO = :reportNumber");

                    $stmt2->bindParam(':accreno', $accreNo);
                    $stmt2->bindParam(':assignedPin', $assign['pAssignedPin']);
                    $stmt2->bindParam(':assignedLastName', $assign['pAssignedLastName']);
                    $stmt2->bindParam(':assignedFirstName', $assign['pAssignedFirstName']);
                    $stmt2->bindParam(':assignedMiddleName', $assign['pAssignedMiddleName']);
                    $stmt2->bindParam(':assignedExtName', $assign['pAssignedExtName']);
                    $stmt2->bindParam(':assignedDateOfBirth', date('Y-m-d', strtotime($assign['pAssignedDateOfBirth'])));
                    $stmt2->bindParam(':assignedSex', $assign['pAssignedSex']);
                    $stmt2->bindParam(':assignedType', $assign['pAssignedType']);
                    $stmt2->bindParam(':primaryPin', $assign['pPrimaryPIN']);
                    $stmt2->bindParam(':primaryLastName', $assign['pPrimaryLastName']);
                    $stmt2->bindParam(':primaryFirstName', $assign['pPrimaryFirstName']);
                    $stmt2->bindParam(':primaryMiddleName', $assign['pPrimaryMiddleName']);
                    $stmt2->bindParam(':primaryExtName', $assign['pPrimaryExtName']);
                    $stmt2->bindParam(':primaryDateOfBirth', date('Y-m-d', strtotime($assign['pPrimaryDateOfBirth'])));
                    $stmt2->bindParam(':primarySex', $assign['pPrimarySex']);
                    $stmt2->bindParam(':mobileNumber', $assign['pMobileNumber']);
                    $stmt2->bindParam(':landlineNumber', $assign['pLandlineNumber']);
                    $stmt2->bindParam(':memncat', $assign['pMemberNewCat']);
                    $stmt2->bindParam(':memncatdesc', $assign['pMemberNewCatDesc']);
                    $stmt2->bindParam(':packageType', $assign['pPackageType']);
                    $stmt2->bindParam(':assignedDate', $assign['pAssignedDate']);
                    $stmt2->bindParam(':assignedStatus', $assign['pAssignedStatus']);
                    $stmt2->bindParam(':effYear', $assign['pEffYear']);
                    $stmt2->bindParam(':reportNumber', $trasmittalNo);
                    $stmt2->execute();
            }
            echo '<script>alert("Successfully saved!");window.location="assignment_masterlist.php"</script>';
        }
        else {
            echo '<script>alert("The file contains invalid Accreditation Number.");</script>';
        }
        /*End Parsing XML data into table*/

        $conn->commit();

    } catch (PDOException $e) {
        $conn->rollback();
        echo $e->getMessage();
        echo '<script>alert("Error: '.$e->getMessage().'");</script>';
    }

    $conn = null;
}


/*Insert XML Member Assignment*/
function uploadFeedbackReport($upload){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        /*Start Parsing XML data into table*/
        $xml = simplexml_load_string($upload['uploadFeedbackReport']);
        $userName = (string)$xml['pUsername'];
        $userPassword = (string)$xml['pPassword'];
        $accreNo = (string)$xml['pHciAccreNo'];
        $countAssignment = (string)$xml['pAssignmentTotalCnt'];
        $pmccNo = (string)$xml['pPmccNo'];
        $trasmittalNo = (string)$xml['pReportTransmittalNumber'];

        if($accreNo == $_SESSION['pAccreNum']) {
            /*Start Save File Uploaded*/
            $pUploadId = generateTransNo('UPLOAD_ID');
            $pUploadModule = "FEEDBACK";

            $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_UPLOAD(
                                              UPLOAD_ID, UPLOAD_XML, UPLOAD_MODULE, DATE_UPLOADED)
                                        VALUES(:uploadId, 
                                               :uploadXml,
                                               :uploadModule,
                                               NOW())");

            $stmt->bindParam(':uploadId', $pUploadId);
            $stmt->bindParam(':uploadModule', $pUploadModule);
            $stmt->bindParam(':uploadXml', $upload['uploadFeedbackReport']);
            $stmt->execute();
            /*End Save File Uploaded*/

            echo '<script>alert("Successfully saved!");window.location="upload_report_feedback.php"</script>';
        } else{
            echo '<script>alert("The file contains invalid Accreditation Number.");</script>';
        }
        /*End Parsing XML data into table*/
        $conn->commit();
    } catch (PDOException $e) {
        $conn->rollback();
        echo $e->getMessage();
        echo '<script>alert("Error: '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

/*Insert XML report*/
function insertGeneratedXMLreport($xmlReport, $reportTransNo, $dateRange){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $conn->begintransaction();

        $stmt = $conn->prepare("INSERT INTO ".$ini['EPCB'].".TSEKAP_TBL_REPORTS(
                                REPORT_TRANS_NO, ACCRE_NO, DATE_RANGE, DATE_GENERATED, XML_CONTENT) 
                                  VALUES(:reportTransNo, 
                                         :accreNo, 
                                         :dateRange, 
                                         NOW(), 
                                         :xmlReport)");

        $stmt->bindParam(':reportTransNo', $reportTransNo);
        $stmt->bindParam(':accreNo', $_SESSION['pAccreNum']);
        $stmt->bindParam(':dateRange', $dateRange);
        $stmt->bindParam(':xmlReport', $xmlReport);
        $stmt->execute();

        $conn->commit();

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Error: Saving Generated XML Report - " . $e->getMessage();
        echo '<script>alert("Error: Saving Generated XML Report - '.$e->getMessage().'");</script>';
    }

    $conn = null;
}

function listMedicalDiseases(){

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_LIB_MDISEASES
                                WHERE LIB_STAT = '1'
                                ORDER BY MDISEASE_CODE ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;

}



function generateTransNo($seq_name) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // CHECK IF SEQUENCE NAME EXIST
        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_SEQNO
                                WHERE SEQ_NAME = :SEQ_NAME");
        $stmt->bindParam(':SEQ_NAME', $seq_name);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            $result = $result[0];
            $seq_format = strlen($result["SEQ_FORMAT"]);
            $seq_prefix = $result["SEQ_PREFIX"];
            $cycle_period_format = date('YmdHs');
        }
        else {
            throw new Exception("Sequence name ".$seq_name." not found.");
        }

        //$seq_code = $seq_prefix.$_SESSION["pAccreNum"].date($cycle_period_format);

        $seq_code = $seq_prefix.$_SESSION["pAccreNum"].$cycle_period_format;

        // CHECK IF SEQUENCE EXIST IN LOGS
        $stmt = $conn->prepare("SELECT LAST_VALUE, DATE_FORMAT(LAST_GEN_DATE, '%m/%Y') as LAST_GEN_DATE, LAST_GEN_BY
                                FROM ".$ini['EPCB'].".TSEKAP_SEQNO_DET
                                WHERE SEQ_NAME = :SEQ_NAME
                                AND SEQ_CODE = :SEQ_CODE");
        $stmt->bindParam(':SEQ_NAME', $seq_name);
        $stmt->bindParam(':SEQ_CODE', $seq_code);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result) {
            $result = $result[0];
            $last_value = $result["LAST_VALUE"];
            $last_gen_date = $result["LAST_GEN_DATE"];
            $current_date = date("m/Y");

            if($last_gen_date != $current_date) {
                $new_value = 1;
            }
            else {
                if($last_value != "99999") {
                    $new_value = $last_value+1;
                }
                else {
                    throw new Exception("Maximum sequence number reached for sequence name ".$seq_name." and code ".$_SESSION["pAccreNum"]);
                }
            }

            $query = "UPDATE ".$ini['EPCB'].".TSEKAP_SEQNO_DET 
                      SET LAST_VALUE = :LAST_VALUE, 
                          LAST_GEN_DATE = NOW(), 
                          LAST_GEN_BY = :LAST_GEN_BY
                      WHERE SEQ_NAME = :SEQ_NAME
                      AND SEQ_CODE = :SEQ_CODE";
        }
        else {
            $new_value = 1;
            $query = "INSERT INTO ".$ini['EPCB'].".TSEKAP_SEQNO_DET (SEQ_NAME, SEQ_CODE, LAST_VALUE, LAST_GEN_DATE, LAST_GEN_BY)
                      VALUES (:SEQ_NAME, :SEQ_CODE, :LAST_VALUE, NOW(), :LAST_GEN_BY)";
        }

        // INSERT SEQUENCE IN LOG IF NOT EXIST; INCREMENT IF EXIST;
        $conn->begintransaction();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':SEQ_NAME', $seq_name);
        $stmt->bindParam(':SEQ_CODE', $seq_code);
        $stmt->bindParam(':LAST_VALUE', $new_value);
        $stmt->bindParam(':LAST_GEN_BY', $_SESSION["pUserID"]);
        $stmt->execute();
        $conn->commit();

        $sequence_no = str_pad($new_value, $seq_format, 0, STR_PAD_LEFT);
        $trans_no = $seq_code.$sequence_no;
    }
    catch(PDOException $e) {
        $message = htmlentities($e->getMessage(),ENT_QUOTES);
        $message = preg_replace('~[\r\n]+~', ' ', $message);
        echo "<script>alert('".$message."');</script>";
        $conn->rollBack();
    }
    catch (Exception $ex) {
        $err_msg = $ex->getMessage();
        $message = htmlentities($err_msg,ENT_NOQUOTES);
        $message = preg_replace('~[\r\n]+~', ' ', $message);
        echo "<script>alert('".$message."');</script>";
    }

    $conn = null;

    return $trans_no;

}

//Get Enlistment/Registration Data Module
function getEnlistData($case_no) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                WHERE CASE_NO = :CASE_NO");
        $stmt->bindParam(":CASE_NO", $case_no);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result[0];

}

/*GET PROFILING RECORD TO EDIT/UPDATE PATIENT INFORMATION*/
function getPatientHsaRecord($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, enlist.CASE_NO, enlist.EFF_YEAR
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST AS enlist /*ENLISTMENT RECORD*/
                                INNER JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile ON enlist.CASE_NO = profile.CASE_NO /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_OINFO AS oinfo ON profile.TRANS_NO = oinfo.TRANS_NO /*PATIENTS DETAILS OTHER INFO*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SOCHIST AS sochist ON profile.TRANS_NO = sochist.TRANS_NO /*PERSONAL/SOCIAL HISTORY*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PREGHIST AS preghist ON profile.TRANS_NO = preghist.TRANS_NO /*OB-GYNE HISTORY - PREGNANCY*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MENSHIST AS menshist ON profile.TRANS_NO = menshist.TRANS_NO /*OB-GYNE HISTORY - MENSTRUAL*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT AS pepert ON profile.TRANS_NO = pepert.TRANS_NO /*PERTINENT PHYSICAL EXAMINATION FINDINGS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_BLOODTYPE AS bloodtype ON profile.TRANS_NO = bloodtype.TRANS_NO /*PERTINENT PHYSICAL EXAMINATION FINDINGS - BLOOD TYPE*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PESPECIFIC AS pespecific ON profile.TRANS_NO = pespecific.TRANS_NO /*PERTINENT PHYSICAL EXAMINATION FINDINGS - REMARKS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_NCDQANS AS ncdqans ON profile.TRANS_NO = ncdqans.TRANS_NO /*NCD HIGH-RISK ASSESSMENT*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEGENSURVEY AS gensurvey ON profile.TRANS_NO = gensurvey.TRANS_NO /*NCD HIGH-RISK ASSESSMENT*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND oinfo.UPD_CNT = :updCnt
                                  AND sochist.UPD_CNT = :updCnt
                                  AND preghist.UPD_CNT = :updCnt
                                  AND menshist.UPD_CNT = :updCnt
                                  AND pepert.UPD_CNT = :updCnt
                                  AND bloodtype.UPD_CNT = :updCnt
                                  AND pespecific.UPD_CNT = :updCnt
                                  AND ncdqans.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result[0];

}

/*GET PATIENT PAST MEDICAL HISTORY*/
function getPatientHsaPastMedicalHistory($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST AS medhist ON profile.TRANS_NO = medhist.TRANS_NO /*PAST MEDICAL HISTORY CHECKLIST*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND medhist.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET PATIENT PAST MEDICAL HISTORY*/
function getPatientHsaPastMedicalRemarks($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC AS mhspecific ON profile.TRANS_NO = mhspecific.TRANS_NO /*PAST MEDICAL HISTORY REMARKS*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND mhspecific.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


/*GET PATIENT FAMILY HISTORY*/
function getPatientHsaFamilyHistory($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, famhist.MDISEASE_CODE
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST AS famhist ON profile.TRANS_NO = famhist.TRANS_NO /*FAMILY HISTORY CHECKLIST*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC AS fhspecific ON profile.TRANS_NO = fhspecific.TRANS_NO /*FAMILY HISTORY - SPECIFIC REMARKS*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND famhist.UPD_CNT = :updCnt
                                  AND fhspecific.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getPatientHsaLabsFbs($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS p 
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_diag_fbs AS a ON p.TRANS_NO = a.TRANS_NO 
                                WHERE p.TRANS_NO = :transNo
                                  AND p.UPD_CNT = :updCnt
                                  AND a.UPD_CNT = :updCnt
                                  AND a.IS_APPLICABLE = 'D'");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getPatientHsaLabsRbs($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS p 
                                LEFT JOIN ".$ini['EPCB'].".tsekap_tbl_diag_rbs AS a ON p.TRANS_NO = a.TRANS_NO 
                                WHERE p.TRANS_NO = :transNo
                                  AND p.UPD_CNT = :updCnt
                                  AND a.UPD_CNT = :updCnt
                                  AND a.IS_APPLICABLE = 'D'");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


/*GET PATIENT FAMILY HISTORY REMARKS*/
function getPatientHsaFamilyRemarks($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC AS fhspecific ON profile.TRANS_NO = fhspecific.TRANS_NO /*FAMILY HISTORY - SPECIFIC REMARKS*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND fhspecific.UPD_CNT = :updCnt");


        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}
/*GET PATIENT IMMUNIZATIONS*/
function getPatientHsaImmunization($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION AS immune ON profile.TRANS_NO = immune.TRANS_NO /*FAMILY HISTORY CHECKLIST*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND immune.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET PATIENT PERTINENT MISCELLANEOUS */
function getPatientHsaPertinentMisc($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC AS pemisc ON profile.TRANS_NO = pemisc.TRANS_NO /*FAMILY HISTORY CHECKLIST*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND pemisc.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function getPatientHsaPepert($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEPERT AS pepert ON profile.TRANS_NO = pepert.TRANS_NO
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND pepert.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}


function getPatientHsaMedicine($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE AS meds ON profile.TRANS_NO = meds.TRANS_NO /*MEDICINE*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND meds.TRANS_NO = :transNo
                                  AND meds.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

/*GET PATIENT PERTINENT MISCELLANEOUS */
function getPatientHsaSurgicalHistory($hsa_transNo, $getUpdCnt) {

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile /*PATIENTS DETAILS*/
                                LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST AS surghist ON profile.TRANS_NO = surghist.TRANS_NO /*FAMILY HISTORY CHECKLIST*/
                                WHERE profile.TRANS_NO = :transNo
                                  AND profile.UPD_CNT = :updCnt
                                  AND surghist.UPD_CNT = :updCnt");

        $stmt->bindParam(":transNo", $hsa_transNo);
        $stmt->bindParam(":updCnt", $getUpdCnt);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

}

function listChildImmunizations(){

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_LIB_IMMCHILD
                                WHERE LIB_STAT = '1'
                                ORDER BY SORT_NO ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;

}

function listAdultImmunizations(){

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_LIB_IMMYOUNGW
                                WHERE LIB_STAT = '1'
                                ORDER BY SORT_NO ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;

}

function listPregnantImmunizations(){

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_LIB_IMMPREGW
                                WHERE LIB_STAT = '1'
                                ORDER BY SORT_NO ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;

}

function listElderlyImmunizations(){

    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                FROM ".$ini['EPCB'].".TSEKAP_LIB_IMMELDERLY
                                WHERE LIB_STAT = '1'
                                ORDER BY SORT_NO ASC");
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;

    return $result;

}


?>