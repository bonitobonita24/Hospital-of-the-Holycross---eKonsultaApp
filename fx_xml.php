<?php
/* v01.06.00.202206 **/
error_reporting(0);

function getRegistration($pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT * 
                                FROM ".$ini['EPCB'].".TSEKAP_TBL_ENLIST
                                WHERE case_no = :caseno");

        $stmt->bindParam(':caseno', $pCaseNo);

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

function getProfiling($pCaseNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, profile.TRANS_NO AS TRANS_NO, menshist.IS_APPLICABLE as MENS_IS_APPLICABLE, preghist.IS_APPLICABLE as PREG_IS_APPLICABLE
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
                                            WHERE profile.CASE_NO = :caseno
                                            AND profile.IS_FINALIZE = 'Y'
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

        $stmt->bindParam(':caseno', $pCaseNo);
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

/*Generation of Report - Profiling - MEDICAL HISTORY*/
function getProfMedHist($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT distinct medhist.MDISEASE_CODE, profile.TRANS_NO, profile.CASE_NO
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MEDHIST AS medhist ON profile.TRANS_NO = medhist.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND medhist.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getProfMHSpecific($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT mhspecific.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_MHSPECIFIC AS mhspecific ON profile.TRANS_NO = mhspecific.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND mhspecific.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getProfSurghist($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT surghist.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_SURGHIST AS surghist ON profile.TRANS_NO = surghist.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND surghist.UPD_CNT = profile.UPD_CNT
                                             GROUP BY profile.TRANS_NO
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getProfFamhist($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT distinct famhist.MDISEASE_CODE, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FAMHIST AS famhist ON profile.TRANS_NO = famhist.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND famhist.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getProfFHSpecific($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT fhspecific.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_FHSPECIFIC AS fhspecific ON profile.TRANS_NO = fhspecific.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND fhspecific.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getProfImmunization($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT immune.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile 
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_IMMUNIZATION AS immune ON profile.TRANS_NO = immune.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND immune.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getProfPEMISC($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT pemisc.*, profile.TRANS_NO, profile.CASE_NO, profile.UPD_CNT, profile.DATE_ADDED, profile.EFF_YEAR, profile.IS_FINALIZE
                                       FROM ".$ini['EPCB'].".TSEKAP_TBL_PROFILE AS profile
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_PROF_PEMISC AS pemisc ON profile.TRANS_NO = pemisc.TRANS_NO 
                                            WHERE profile.TRANS_NO = :transno
                                            AND profile.IS_FINALIZE = 'Y'
                                            AND pemisc.UPD_CNT = profile.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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


/* Consultation **/
function getIndividualConsultation($pCaseno){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, soap.TRANS_NO
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT as pepert ON soap.TRANS_NO = pepert.TRANS_NO 
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC as pespecific ON soap.TRANS_NO = pespecific.TRANS_NO 
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE as subjective ON soap.TRANS_NO = subjective.TRANS_NO 
                                        WHERE soap.case_no = :caseno
                                          GROUP BY soap.TRANS_NO                                          
                                            ORDER BY soap.SOAP_DATE ASC");

        $stmt->bindParam(':caseno', $pCaseno);

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

function getCaseConsultation($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT distinct CASE_NO
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                        WHERE soap.SOAP_DATE BETWEEN :pxStartDate AND :pxEndDate
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

function getConsultation($pStartDate, $pEndDate){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *, soap.TRANS_NO 
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                         LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEPERT as pepert ON soap.TRANS_NO = pepert.TRANS_NO
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PESPECIFIC as pespecific ON soap.TRANS_NO = pespecific.TRANS_NO
                                        LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_SUBJECTIVE as subjective ON soap.TRANS_NO = subjective.TRANS_NO
                                        WHERE soap.SOAP_DATE BETWEEN :pxStartDate AND :pxEndDate
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

/*Generation of Report - Consultation - PERTINENT FINDINGS PER SYSTEM*/
function getConsultationPEMISC($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT pemisc.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_PEMISC as pemisc ON soap.TRANS_NO = pemisc.TRANS_NO 
                                            WHERE soap.TRANS_NO = :transno                                          
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND pemisc.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getConsultationIcd($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT icd.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_ICD as icd ON soap.TRANS_NO = icd.TRANS_NO 
                                            WHERE soap.TRANS_NO = :transno
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND icd.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getConsultationDiagnostic($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT diagnostic.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_DIAGNOSTIC as diagnostic ON soap.TRANS_NO = diagnostic.TRANS_NO 
                                            WHERE soap.TRANS_NO = :transno
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND diagnostic.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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
function getConsultationManagement($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT management.*, soap.TRANS_NO, soap.CASE_NO, soap.DATE_ADDED, soap.UPD_CNT, soap.EFF_YEAR
                                      FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as soap
                                          LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP_MANAGEMENT as management ON soap.TRANS_NO = management.TRANS_NO 
                                            WHERE soap.TRANS_NO = :transno
                                              AND soap.XPS_MODULE = 'SOAP'
                                              AND management.UPD_CNT = soap.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

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

/* **/
function getDiagFBS ($pTransNo) {
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FBS 
                                    WHERE TRANS_NO = :transno  
                                    AND IS_APPLICABLE IN ('D', 'W', 'X') 
                                    ");

        $stmt->bindParam(':transno', $pTransNo);
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

/* **/
function getDiagRBS ($pTransNo) {
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

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
    
    return $result;
}

function getDiagCBC($pTransNo) {
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CBC as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagUrinalysis($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_URINALYSIS as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')     
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagFecalysis($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FECALYSIS as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY a.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagChestXray($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CHESTXRAY as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY a.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagSputum($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_SPUTUM as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X') 
                                      GROUP BY a.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagLipidProf($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_LIPIDPROF as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno   
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagECG($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_ECG as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagOGTT($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OGTT as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno   
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')   
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagPapSmear($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PAPSSMEAR as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagFOBT($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_FOBT as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagCreatinine($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_CREATININE as b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagPDD($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_PPD_TEST b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagHbA1c($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_HBA1C b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')");

        $stmt->bindParam(':transno', $pTransNo);

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

function getDiagOthDiag($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *
                                  FROM ".$ini['EPCB'].".TSEKAP_TBL_SOAP as a
                                    LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_DIAG_OTHERS b ON a.TRANS_NO = b.TRANS_NO
                                    WHERE a.TRANS_NO = :transno
                                    AND b.IS_APPLICABLE IN ('D', 'W', 'X')
                                      GROUP BY b.TRANS_NO");

        $stmt->bindParam(':transno', $pTransNo);

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

function getMedicine($pTransNo){
    $ini = parse_ini_file("config.ini");

    try {
        $conn = new PDO("mysql:host=".$ini["DBSERVER"].";dbname=".$ini["EPCB"], $ini['APPUSERNAME'], $ini['APPPASSWORD']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT *                            
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE as a
                                     LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as b ON a.TRANS_NO = b.TRANS_NO
                                        WHERE a.TRANS_NO = :transno 
                                            AND a.UPD_CNT = b.UPD_CNT
                                             ");

        $stmt->bindParam(':transno', $pTransNo);

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($result) > 1) {
            $stmt = $conn->prepare("SELECT *                            
                                    FROM ".$ini['EPCB'].".TSEKAP_TBL_MEDICINE as a
                                     LEFT JOIN ".$ini['EPCB'].".TSEKAP_TBL_SOAP as b ON a.TRANS_NO = b.TRANS_NO
                                        WHERE a.TRANS_NO = :transno 
                                            AND a.UPD_CNT = b.UPD_CNT
                                            AND a.GEN_CODE NOT IN ('NOMED')
                                             ");

            $stmt->bindParam(':transno', $pTransNo);

            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        }

        return $result;
    }
    catch(PDOException $e)
    {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

/*Generate XML Report*/
function generateKonsultaBatchXML($vGetCaseNo, $vGetConsultation) {
    $konsulta = new SimpleXMLElement("<PCB></PCB>");

    $pReportTransNo = generateTransNo('REPORT_TRANS_NO');
    $pDateRange = $pStartDate." TO ".$pEndDate;

    if (count($vGetConsultation) > 0) {
        $cntSOAP = count($vGetConsultation);
    } else {
        $cntSOAP = 1;
    }

    $konsulta->addAttribute("pUsername", "");
    $konsulta->addAttribute("pPassword", "");
    $konsulta->addAttribute("pHciAccreNo", $_SESSION['pAccreNum']);
    $konsulta->addAttribute("pPMCCNo", "");
    $konsulta->addAttribute("pEnlistTotalCnt", count($vGetCaseNo));
    $konsulta->addAttribute("pProfileTotalCnt", count($vGetCaseNo));
    $konsulta->addAttribute("pSoapTotalCnt", $cntSOAP);
    $konsulta->addAttribute("pCertificationId", "EKON-00-06-2020-00001");
    $konsulta->addAttribute("pHciTransmittalNumber", $pReportTransNo);

    /*START ENLISTMENT XML GENERATION*/
    $enlistments = $konsulta->addChild("ENLISTMENTS");

        foreach ($vGetCaseNo as $vCaseNo) {
            $vGetEnlist = getRegistration($vCaseNo["CASE_NO"]);

            $enlistment = $enlistments->addChild("ENLISTMENT");
            $enlistment->addAttribute("pHciCaseNo", $vGetEnlist['CASE_NO']);
            $enlistment->addAttribute("pHciTransNo", $vGetEnlist['TRANS_NO']);
            $enlistment->addAttribute("pEffYear", $vGetEnlist['EFF_YEAR']);
            $enlistment->addAttribute("pEnlistStat", $vGetEnlist['ENLIST_STAT']);
            $enlistment->addAttribute("pEnlistDate", date('Y-m-d', strtotime($vGetEnlist['ENLIST_DATE'])));
            $enlistment->addAttribute("pPackageType", $vGetEnlist['PACKAGE_TYPE']);
            $enlistment->addAttribute("pMemPin", trim($vGetEnlist['MEM_PIN']));
            $enlistment->addAttribute("pMemFname", trim(strReplaceEnye($vGetEnlist['MEM_FNAME'])));
            $enlistment->addAttribute("pMemMname", trim(strReplaceEnye($vGetEnlist['MEM_MNAME'])));
            $enlistment->addAttribute("pMemLname", trim(strReplaceEnye($vGetEnlist['MEM_LNAME'])));
            $enlistment->addAttribute("pMemExtname", trim($vGetEnlist['MEM_EXTNAME']));
            $enlistment->addAttribute("pMemDob", date('Y-m-d', strtotime($vGetEnlist['MEM_DOB'])));
            $enlistment->addAttribute("pPatientPin", trim($vGetEnlist['PX_PIN']));
            $enlistment->addAttribute("pPatientFname", trim(strReplaceEnye($vGetEnlist['PX_FNAME'])));
            $enlistment->addAttribute("pPatientMname", trim(strReplaceEnye($vGetEnlist['PX_MNAME'])));
            $enlistment->addAttribute("pPatientLname", trim(strReplaceEnye($vGetEnlist['PX_LNAME'])));
            $enlistment->addAttribute("pPatientExtname", trim($vGetEnlist['PX_EXTNAME']));
            if ($vGetEnlist['PX_SEX'] == '0') {
                $vPxSex = "M";
            } else if ($vGetEnlist['PX_SEX'] == '1') {
                $vPxSex = "F";
            } else {
                $vPxSex = $vGetEnlist['PX_SEX'];
            }
            $enlistment->addAttribute("pPatientSex", $vPxSex);
            $enlistment->addAttribute("pPatientDob", date('Y-m-d', strtotime($vGetEnlist['PX_DOB'])));
            $enlistment->addAttribute("pPatientType", $vGetEnlist['PX_TYPE']);
            if ($vGetEnlist['PX_MOBILE_NO'] == null) {
                $vPxMobileNo = "-";
            } else {
                $vPxMobileNo = $vGetEnlist['PX_MOBILE_NO'];
            }
            $enlistment->addAttribute("pPatientMobileNo", $vPxMobileNo);
            $enlistment->addAttribute("pPatientLandlineNo", $vGetEnlist['PX_LANDLINE_NO']);
            $enlistment->addAttribute("pWithConsent", $vGetEnlist['WITH_CONSENT']);
            $enlistment->addAttribute("pTransDate", date('Y-m-d', strtotime($vGetEnlist['TRANS_DATE'])));
            $enlistment->addAttribute("pCreatedBy", $vGetEnlist['CREATED_BY']);
            $enlistment->addAttribute("pReportStatus", "U");
            $enlistment->addAttribute("pDeficiencyRemarks", "");
        } /*END ENLISTMENT */
   

    /*START PROFILING XML GENERATION*/
    $profiling = $konsulta->addChild("PROFILING");
    
        foreach ($vGetCaseNo as $vCaseNo) {
            $vGetProfiling = getProfiling($vCaseNo["CASE_NO"]);

            $profile = $profiling->addChild("PROFILE");
                $profile->addAttribute("pHciTransNo", $vGetProfiling['TRANS_NO']);
                $profile->addAttribute("pHciCaseNo", $vGetProfiling['CASE_NO']);
                $profile->addAttribute("pProfDate", $vGetProfiling['PROF_DATE']);
                $profile->addAttribute("pPatientPin", $vGetProfiling['PX_PIN']);
                $profile->addAttribute("pPatientType", $vGetProfiling['PX_TYPE']);
                $profile->addAttribute("pPatientAge", $vGetProfiling['PX_AGE']);
                $profile->addAttribute("pMemPin", $vGetProfiling['MEM_PIN']);
                $profile->addAttribute("pEffYear", $vGetProfiling['EFF_YEAR']);
                $profile->addAttribute("pATC", trim($vGetProfiling['PROFILE_OTP']));
                $profile->addAttribute("pIsWalkedIn", $vGetProfiling['WITH_ATC']);
                $profile->addAttribute("pTransDate", $vGetProfiling['DATE_ADDED']);
                $profile->addAttribute("pReportStatus", "U");
                $profile->addAttribute("pDeficiencyRemarks", "");

            $vGetProfMedHists = getProfMedHist($vGetProfiling['TRANS_NO']);
            $medhists = $profile->addChild("MEDHISTS");
                if (count($vGetProfMedHists) > 0) {
                   foreach ($vGetProfMedHists as $vGetProfMedHist) {
                        $medhist = $medhists->addChild("MEDHIST");
                        if ($vGetProfMedHist['MDISEASE_CODE'] == null || $vGetProfMedHist['MDISEASE_CODE'] == "") {
                            $medhist->addAttribute("pMdiseaseCode", "999");
                        } else {
                            $medhist->addAttribute("pMdiseaseCode", $vGetProfMedHist['MDISEASE_CODE']);
                        }
                        $medhist->addAttribute("pReportStatus", "U");
                        $medhist->addAttribute("pDeficiencyRemarks", "");
                    }
                } else {
                    $medhist = $medhists->addChild("MEDHIST");
                    $medhist->addAttribute("pMdiseaseCode", "999");
                    $medhist->addAttribute("pReportStatus", "U");
                    $medhist->addAttribute("pDeficiencyRemarks", "");
                } 

            $vGetProfMHSpecifics = getProfMHSpecific($vGetProfiling['TRANS_NO']);
            $mhspecifics = $profile->addChild("MHSPECIFICS");
                if (count($vGetProfMHSpecifics) > 0) {
                    foreach ($vGetProfMHSpecifics as $vGetProfMHSpecific) {
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                        $mhspecific->addAttribute("pMdiseaseCode", $vGetProfMHSpecific['MDISEASE_CODE']);
                        $mhspecific->addAttribute("pSpecificDesc", $vGetProfMHSpecific['SPECIFIC_DESC']);
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks","");
                    }
                } else {
                    $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                    $mhspecific->addAttribute("pMdiseaseCode", "");
                    $mhspecific->addAttribute("pSpecificDesc", "");
                    $mhspecific->addAttribute("pReportStatus", "U");
                    $mhspecific->addAttribute("pDeficiencyRemarks","");
                }

            $vGetProfSurghists = getProfSurghist($vGetProfiling['TRANS_NO']);
            $surghists = $profile->addChild("SURGHISTS");
                if (count($vGetProfSurghists) > 0 ) {
                    foreach ($vGetProfSurghists as $vGetProfSurghist) {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", $vGetProfSurghist['SURG_DESC']);
                        $surghist->addAttribute("pSurgDate", $vGetProfSurghist['SURG_DATE']);
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", "");
                    }
                } else {
                    $surghist = $surghists->addChild("SURGHIST");
                    $surghist->addAttribute("pSurgDesc", "");
                    $surghist->addAttribute("pSurgDate", "");
                    $surghist->addAttribute("pReportStatus", "U");
                    $surghist->addAttribute("pDeficiencyRemarks", "");
                }
                

            $vGetProfFamhists = getProfFamhist($vGetProfiling['TRANS_NO']);
            $famhists = $profile->addChild("FAMHISTS");
                if(count($vGetProfFamhists) > 0) {
                    foreach ($vGetProfFamhists as $vGetProfFamhist) {
                        $famhist = $famhists->addChild("FAMHIST");
                        if ($vGetProfFamhist['MDISEASE_CODE'] == null || $vGetProfFamhist['MDISEASE_CODE'] == "") {
                            $famhist->addAttribute("pMdiseaseCode", "999");
                        } else {
                            $famhist->addAttribute("pMdiseaseCode", $vGetProfFamhist['MDISEASE_CODE']);
                        }
                        $famhist->addAttribute("pReportStatus", "U");
                        $famhist->addAttribute("pDeficiencyRemarks", "");
                    }
                } else {
                    $famhist = $famhists->addChild("FAMHIST");
                    $famhist->addAttribute("pMdiseaseCode", "999");
                    $famhist->addAttribute("pReportStatus", "U");
                    $famhist->addAttribute("pDeficiencyRemarks", "");
                }
               

            $vGetProfFHSpecifics = getProfFHSpecific($vGetProfiling['TRANS_NO']);
            $fhspecifics = $profile->addChild("FHSPECIFICS");
                if (count($vGetProfFHSpecifics) > 0) {
                    foreach ($vGetProfFHSpecifics as $vGetProfFHSpecific) {
                            $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                            $fhspecific->addAttribute("pMdiseaseCode", $vGetProfFHSpecific['MDISEASE_CODE']);
                            $fhspecific->addAttribute("pSpecificDesc", $vGetProfFHSpecific['SPECIFIC_DESC']);
                            $fhspecific->addAttribute("pReportStatus", "U");
                            $fhspecific->addAttribute("pDeficiencyRemarks", "");
                    }
                } else {
                    $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                    $fhspecific->addAttribute("pMdiseaseCode", "");
                    $fhspecific->addAttribute("pSpecificDesc", "");
                    $fhspecific->addAttribute("pReportStatus", "U");
                    $fhspecific->addAttribute("pDeficiencyRemarks", "");
                }

            $sochist = $profile->addChild("SOCHIST");
            if ($vGetProfiling['IS_SMOKER'] != null || $vGetProfiling['IS_SMOKER'] != "") {
                $sochist->addAttribute("pIsSmoker", $vGetProfiling['IS_SMOKER']);
            } else {
                $sochist->addAttribute("pIsSmoker", "N");
            }
            $sochist->addAttribute("pNoCigpk", $vGetProfiling['NO_CIGPK']);
            if ($vGetProfiling['IS_ADRINKER'] != null || $vGetProfiling['IS_ADRINKER'] != "") {
                $sochist->addAttribute("pIsAdrinker", $vGetProfiling['IS_ADRINKER']);
            } else {
                $sochist->addAttribute("pIsAdrinker", "N");
            }
            $sochist->addAttribute("pNoBottles", $vGetProfiling['NO_BOTTLES']);
            if ($vGetProfiling['ILL_DRUG_USER'] != null || $vGetProfiling['ILL_DRUG_USER'] != "") {
                $sochist->addAttribute("pIllDrugUser", $vGetProfiling['ILL_DRUG_USER']);
            } else {
                $sochist->addAttribute("pIllDrugUser", "N");
            }
            if ($vGetProfiling['IS_SEXUALLY_ACTIVE'] != null || $vGetProfiling['IS_SEXUALLY_ACTIVE'] != "") {
                $sochist->addAttribute("pIsSexuallyActive", $vGetProfiling['IS_SEXUALLY_ACTIVE']);
            } else {
                $sochist->addAttribute("pIsSexuallyActive", "N");
            }
            $sochist->addAttribute("pReportStatus", "U");
            $sochist->addAttribute("pDeficiencyRemarks", "");

            $vGetProfImmunizations = getProfImmunization($vGetProfiling['TRANS_NO']);
            $immunizations = $profile->addChild("IMMUNIZATIONS");
            if (count ($vGetProfImmunizations) > 0) {
                foreach ($vGetProfImmunizations as $vGetProfImmunization) {
                    $immunization = $immunizations->addChild("IMMUNIZATION");

                    if ($vGetProfImmunization['CHILD_IMMCODE'] == null || $vGetProfImmunization['CHILD_IMMCODE'] == "") {
                        $immunization->addAttribute("pChildImmcode", "999");
                    } else {
                        $immunization->addAttribute("pChildImmcode", $vGetProfImmunization['CHILD_IMMCODE']);
                    }

                    if ($vGetProfImmunization['YOUNGW_IMMCODE'] == null || $vGetProfImmunization['YOUNGW_IMMCODE'] == "") {
                        $immunization->addAttribute("pYoungwImmcode", "999");
                    } else {
                        $immunization->addAttribute("pYoungwImmcode", $vGetProfImmunization['YOUNGW_IMMCODE']);
                    }

                    if ($vGetProfImmunization['PREGW_IMMCODE'] == null || $vGetProfImmunization['PREGW_IMMCODE'] == "") {
                        $immunization->addAttribute("pPregwImmcode", "999");
                    } else {
                        $immunization->addAttribute("pPregwImmcode", $vGetProfImmunization['PREGW_IMMCODE']);
                    }
                    
                    if ($vGetProfImmunization['ELDERLY_IMMCODE'] == null || $vGetProfImmunization['ELDERLY_IMMCODE'] == "") {
                        $immunization->addAttribute("pElderlyImmcode", "999");
                    } else {
                        $immunization->addAttribute("pElderlyImmcode", $vGetProfImmunization['ELDERLY_IMMCODE']);
                    }
                    $immunization->addAttribute("pOtherImm", $vGetProfImmunization['OTHER_IMM']);
                    $immunization->addAttribute("pReportStatus", "U");
                    $immunization->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
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
            $menshist->addAttribute("pMenarchePeriod", $vGetProfiling['MENARCHE_PERIOD']);
            $menshist->addAttribute("pLastMensPeriod", $vGetProfiling['LAST_MENS_PERIOD']);
            $menshist->addAttribute("pPeriodDuration", $vGetProfiling['PERIOD_DURATION']);
            $menshist->addAttribute("pMensInterval", $vGetProfiling['MENS_INTERVAL']);
            $menshist->addAttribute("pPadsPerDay", $vGetProfiling['PADS_PER_DAY']);
            $menshist->addAttribute("pOnsetSexIc", $vGetProfiling['ONSET_SEX_IC']);
            $menshist->addAttribute("pBirthCtrlMethod", $vGetProfiling['BIRTH_CTRL_METHOD']);
            $menshist->addAttribute("pIsMenopause", $vGetProfiling['IS_MENOPAUSE']);
            $menshist->addAttribute("pMenopauseAge", $vGetProfiling['MENOPAUSE_AGE']);
            $menshist->addAttribute("pIsApplicable", $vGetProfiling['MENS_IS_APPLICABLE']);
            $menshist->addAttribute("pReportStatus", "U");
            $menshist->addAttribute("pDeficiencyRemarks","");

            $preghist = $profile->addChild("PREGHIST");
            $preghist->addAttribute("pPregCnt", $vGetProfiling['PREG_CNT']);
            $preghist->addAttribute("pDeliveryCnt", $vGetProfiling['DELIVERY_CNT']);
            $preghist->addAttribute("pDeliveryTyp", $vGetProfiling['DELIVERY_TYP']);
            $preghist->addAttribute("pFullTermCnt", $vGetProfiling['FULL_TERM_CNT']);
            $preghist->addAttribute("pPrematureCnt", $vGetProfiling['PREMATURE_CNT']);
            $preghist->addAttribute("pAbortionCnt", $vGetProfiling['ABORTION_CNT']);
            $preghist->addAttribute("pLivChildrenCnt", $vGetProfiling['LIV_CHILDREN_CNT']);
            $preghist->addAttribute("pWPregIndhyp", $vGetProfiling['W_PREG_INDHYP']);
            $preghist->addAttribute("pWFamPlan", $vGetProfiling['W_FAM_PLAN']);
            if ($vGetProfiling['PREG_IS_APPLICABLE'] == null || ($vGetProfiling['PREG_CNT'] == 0)) {
                $preghist->addAttribute("pIsApplicable", "N");
            } else {
                $preghist->addAttribute("pIsApplicable", "Y");
            }
            
            $preghist->addAttribute("pReportStatus", "U");
            $preghist->addAttribute("pDeficiencyRemarks", "");

            $pepert = $profile->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $vGetProfiling['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $vGetProfiling['DIASTOLIC']);
            $pepert->addAttribute("pHr", $vGetProfiling['HR']);
            $pepert->addAttribute("pRr", $vGetProfiling['RR']);
            $pepert->addAttribute("pTemp", $vGetProfiling['TEMPERATURE']);
            $pepert->addAttribute("pHeight", $vGetProfiling['HEIGHT']);
            $pepert->addAttribute("pWeight", $vGetProfiling['WEIGHT']);
            $pepert->addAttribute("pBMI", $vGetProfiling['BMI']);
            $pepert->addAttribute("pZScore", "");
            $pepert->addAttribute("pLeftVision", $vGetProfiling['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $vGetProfiling['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $vGetProfiling['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $vGetProfiling['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $vGetProfiling['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $vGetProfiling['WAIST']);
            $pepert->addAttribute("pHip", $vGetProfiling['HIP']);
            $pepert->addAttribute("pLimbs", $vGetProfiling['LIMBS']);
            if ($vGetProfiling['MID_UPPER_ARM'] != null) {
                $vMidUpperArmCirc = $vGetProfiling['MID_UPPER_ARM'];
            } else {
                $vMidUpperArmCirc = 0;
            }
            $pepert->addAttribute("pMidUpperArmCirc", $vMidUpperArmCirc);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", "");


            $bloodtype = $profile->addChild("BLOODTYPE");
            $bloodtype->addAttribute("pBloodType", $vGetProfiling['blood_type']);
            $bloodtype->addAttribute("pReportStatus", "U");
            $bloodtype->addAttribute("pDeficiencyRemarks", "");

            $peadmin = $profile->addChild("PEGENSURVEY");
            $peadmin->addAttribute("pGenSurveyId", $vGetProfiling['GENSURVEY_ID']);
            $peadmin->addAttribute("pGenSurveyRem", $vGetProfiling['GENSURVEY_REM']);
            $peadmin->addAttribute("pReportStatus", "U");
            $peadmin->addAttribute("pDeficiencyRemarks", "");

            $vGetProfPEMISCS = getProfPEMISC($vGetProfiling['TRANS_NO']);
            $pemiscs = $profile->addChild("PEMISCS");
            if (count($vGetProfPEMISCS) > 0) {
                foreach ($vGetProfPEMISCS as $vGetProfPEMISC) {
                    $pemisc = $pemiscs->addChild("PEMISC");
                    $pemisc->addAttribute("pSkinId", $vGetProfPEMISC['SKIN_ID']);
                    $pemisc->addAttribute("pHeentId", $vGetProfPEMISC['HEENT_ID']);
                    $pemisc->addAttribute("pChestId", $vGetProfPEMISC['CHEST_ID']);
                    $pemisc->addAttribute("pHeartId", $vGetProfPEMISC['HEART_ID']);
                    $pemisc->addAttribute("pAbdomenId", $vGetProfPEMISC['ABDOMEN_ID']);
                    $pemisc->addAttribute("pNeuroId", $vGetProfPEMISC['NEURO_ID']);
                    $pemisc->addAttribute("pRectalId", $vGetProfPEMISC['RECTAL_ID']);
                    $pemisc->addAttribute("pGuId", $vGetProfPEMISC['GU_ID']);
                    $pemisc->addAttribute("pReportStatus", "U");
                    $pemisc->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
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
            $pespecific->addAttribute("pSkinRem", $vGetProfiling['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $vGetProfiling['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $vGetProfiling['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $vGetProfiling['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $vGetProfiling['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $vGetProfiling['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $vGetProfiling['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $vGetProfiling['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", "");

            $ncdqans = $profile->addChild("NCDQANS");
            $ncdqans->addAttribute("pQid1_Yn", $vGetProfiling['QID1_YN']);
            $ncdqans->addAttribute("pQid2_Yn", $vGetProfiling['QID2_YN']);
            $ncdqans->addAttribute("pQid3_Yn", $vGetProfiling['QID3_YN']);
            $ncdqans->addAttribute("pQid4_Yn", $vGetProfiling['QID4_YN']);
            $ncdqans->addAttribute("pQid5_Ynx", $vGetProfiling['QID5_YNX']);
            $ncdqans->addAttribute("pQid6_Yn", $vGetProfiling['QID6_YN']);
            $ncdqans->addAttribute("pQid7_Yn", $vGetProfiling['QID7_YN']);
            $ncdqans->addAttribute("pQid8_Yn", $vGetProfiling['QID8_YN']);
            $ncdqans->addAttribute("pQid9_Yn", $vGetProfiling['QID9_YN']);
            $ncdqans->addAttribute("pQid10_Yn", $vGetProfiling['QID10_YN']);
            $ncdqans->addAttribute("pQid11_Yn", $vGetProfiling['QID11_YN']);
            $ncdqans->addAttribute("pQid12_Yn", $vGetProfiling['QID12_YN']);
            $ncdqans->addAttribute("pQid13_Yn", $vGetProfiling['QID13_YN']);
            $ncdqans->addAttribute("pQid14_Yn", $vGetProfiling['QID14_YN']);
            $ncdqans->addAttribute("pQid15_Yn", $vGetProfiling['QID15_YN']);
            $ncdqans->addAttribute("pQid16_Yn", $vGetProfiling['QID16_YN']);
            $ncdqans->addAttribute("pQid17_Abcde", $vGetProfiling['QID17_ABCDE']);
            $ncdqans->addAttribute("pQid18_Yn", $vGetProfiling['QID18_YN']);
            $ncdqans->addAttribute("pQid19_Yn", $vGetProfiling['QID19_YN']);
            $ncdqans->addAttribute("pQid19_Fbsmg", $vGetProfiling['QID19_FBSMG']);
            $ncdqans->addAttribute("pQid19_Fbsmmol", $vGetProfiling['QID19_FBSMMOL']);
            $ncdqans->addAttribute("pQid19_Fbsdate", $vGetProfiling['QID19_FBSDATE']);
            $ncdqans->addAttribute("pQid20_Yn", $vGetProfiling['QID20_YN']);
            $ncdqans->addAttribute("pQid20_Choleval", $vGetProfiling['QID20_CHOLEVAL']);
            $ncdqans->addAttribute("pQid20_Choledate", $vGetProfiling['QID20_CHOLEDATE']);
            $ncdqans->addAttribute("pQid21_Yn", $vGetProfiling['QID21_YN']);
            $ncdqans->addAttribute("pQid21_Ketonval", $vGetProfiling['QID21_KETONVAL']);
            $ncdqans->addAttribute("pQid21_Ketondate", $vGetProfiling['QID21_KETONDATE']);
            $ncdqans->addAttribute("pQid22_Yn", $vGetProfiling['QID22_YN']);
            $ncdqans->addAttribute("pQid22_Proteinval", $vGetProfiling['QID22_PROTEINVAL']);
            $ncdqans->addAttribute("pQid22_Proteindate", $vGetProfiling['QID22_PROTEINDATE']);
            $ncdqans->addAttribute("pQid23_Yn", $vGetProfiling['QID23_YN']);
            $ncdqans->addAttribute("pQid24_Yn", $vGetProfiling['QID24_YN']);
            $ncdqans->addAttribute("pReportStatus", "U");
            $ncdqans->addAttribute("pDeficiencyRemarks", "");
            
        } /*END PROFILING **/


    /*START CONSULTATION XML GENERATION*/
    $consultations = $konsulta->addChild("SOAPS");
        foreach ($vGetConsultation as $vGetConsult) {
            $consultation = $consultations->addChild("SOAP");
            $consultation->addAttribute("pHciCaseNo", $vGetConsult['CASE_NO']);
            $consultation->addAttribute("pHciTransNo", $vGetConsult['TRANS_NO']);
            $consultation->addAttribute("pSoapDate", $vGetConsult['SOAP_DATE']);
            $consultation->addAttribute("pPatientPin", $vGetConsult['PX_PIN']);
            $consultation->addAttribute("pPatientType", $vGetConsult['PX_TYPE']);
            $consultation->addAttribute("pMemPin", $vGetConsult['MEM_PIN']);
            $consultation->addAttribute("pEffYear", $vGetConsult['EFF_YEAR']);
            $consultation->addAttribute("pATC", trim($vGetConsult['SOAP_OTP']));
            $consultation->addAttribute("pIsWalkedIn", $vGetConsult['WITH_ATC']);
            $consultation->addAttribute("pCoPay", $vGetConsult['CO_PAY']);
            $consultation->addAttribute("pTransDate", $vGetConsult['DATE_ADDED']);
            $consultation->addAttribute("pReportStatus", "U");
            $consultation->addAttribute("pDeficiencyRemarks", "");

            $subjective = $consultation->addChild("SUBJECTIVE");
            if ($vGetConsult['ILLNESS_HISTORY'] == null || $vGetConsult['ILLNESS_HISTORY'] == "") {
                $vIllnestHist = "NOT APPLICABLE";
            } else {
                $vIllnestHist = $vGetConsult['ILLNESS_HISTORY'];
            }
            $subjective->addAttribute("pIllnessHistory", $vIllnestHist);
            $subjective->addAttribute("pSignsSymptoms", $vGetConsult['SIGNS_SYMPTOMS']);
                $chiefComplaintList = explode (";", $vGetConsult['SIGNS_SYMPTOMS']);
                /*foreach ($chiefComplaintList as $chiefComplaint) {
                    if ($chiefComplaint == "X") {
                        if ($vGetConsult['OTHER_COMPLAINT'] == null || $vGetConsult['OTHER_COMPLAINT'] == "") {
                            $vOtherComplaintStr = "NOT APPLICABLE";
                            break;
                        } else {
                            $vOtherComplaintStr = $vGetConsult['OTHER_COMPLAINT'];
                            break;
                        }
                    } 

                } */
                // X is not added but with chief complaint
                if(strpos($vGetConsult['SIGNS_SYMPTOMS'], "X") !== false){
                    //echo "Found!";
                    if ($vGetConsult['OTHER_COMPLAINT'] == null || $vGetConsult['OTHER_COMPLAINT'] == "") {
                        $vOtherComplaintStr = "NOT APPLICABLE";
                    } else {
                        $vOtherComplaintStr = $vGetConsult['OTHER_COMPLAINT'];
                    }
                } else {
                    $vOtherComplaintStr = "";
                }

                /*
                foreach ($chiefComplaintList as $chiefComplaint) {
                    if ($chiefComplaint == "38") {
                        if ($vGetConsult['PAIN_SITE'] == null || $vGetConsult['PAIN_SITE'] == "") {
                            $vPainSiteStr = "-";
                            break;
                        } else {
                            $vPainSiteStr = $vGetConsult['PAIN_SITE'];
                            break;
                        }
                    } 
                } */

                // 38 is not added but with chief complaint
                if(strpos($vGetConsult['SIGNS_SYMPTOMS'], "38") !== false){
                    //echo "Found!";
                    if ($vGetConsult['PAIN_SITE'] == null || $vGetConsult['PAIN_SITE'] == "") {
                        $vPainSiteStr = "-";
                    } else {
                        $vPainSiteStr = $vGetConsult['PAIN_SITE'];
                    }
                } else {
                    $vPainSiteStr = "";
                }

                /*
                if ($vGetConsult['SIGNS_SYMPTOMS'] == "X") {
                    if ($vGetConsult['OTHER_COMPLAINT'] == null || $vGetConsult['OTHER_COMPLAINT'] == "") {
                        $vOtherComplaintStr = "NOT APPLICABLE";
                    } else {
                        $vOtherComplaintStr = $vGetConsult['OTHER_COMPLAINT'];
                    }
                }

                if ($vGetConsult['SIGNS_SYMPTOMS'] == "38") {
                    if ($vGetConsult['PAIN_SITE'] == null || $vGetConsult['PAIN_SITE'] == "") {
                        $vPainSiteStr = "-";
                    } else {
                        $vPainSiteStr = $vGetConsult['PAIN_SITE'];
                    }
                } */
                
            $subjective->addAttribute("pOtherComplaint", $vOtherComplaintStr);
            $subjective->addAttribute("pPainSite", $vPainSiteStr);
            $subjective->addAttribute("pReportStatus", "U");
            $subjective->addAttribute("pDeficiencyRemarks", "");

            $pepert = $consultation->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $vGetConsult['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $vGetConsult['DIASTOLIC']);
            $pepert->addAttribute("pHr", $vGetConsult['HR']);
            $pepert->addAttribute("pRr", $vGetConsult['RR']);
            $pepert->addAttribute("pTemp", $vGetConsult['TEMPERATURE']);
            if ($vGetConsult['HEIGHT'] != null) {
                $vHeight = $vGetConsult['HEIGHT'];
            } else {
                $vHeight = 0;
            }
            $pepert->addAttribute("pHeight", $vHeight);
            $pepert->addAttribute("pWeight", $vGetConsult['WEIGHT']);
            $pepert->addAttribute("pBMI", $vGetConsult['BMI']);
            $pepert->addAttribute("pZScore", $vGetConsult['Z_SCORE']);
            $pepert->addAttribute("pLeftVision", $vGetConsult['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $vGetConsult['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $vGetConsult['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $vGetConsult['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $vGetConsult['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $vGetConsult['WAIST']);
            $pepert->addAttribute("pHip", $vGetConsult['HIP']);
            $pepert->addAttribute("pLimbs", $vGetConsult['LIMBS']);
            if ($vGetConsult['MID_UPPER_ARM'] != null) {
                $vMidUpperArmCirc = $vGetConsult['MID_UPPER_ARM'];
            } else {
                $vMidUpperArmCirc = 0;
            }
            $pepert->addAttribute("pMidUpperArmCirc", $vMidUpperArmCirc);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", "");

            $vGetConsultationPEMISCS = getConsultationPEMISC($vGetConsult['TRANS_NO']);
            $pemiscs = $consultation->addChild("PEMISCS");
            if (count ($vGetConsultationPEMISCS) > 0) {
                foreach ($vGetConsultationPEMISCS as $vGetConsultationPEMISC) {
                    $pemisc = $pemiscs->addChild("PEMISC");
                    $pemisc->addAttribute("pSkinId", $vGetConsultationPEMISC['SKIN_ID']);
                    $pemisc->addAttribute("pHeentId", $vGetConsultationPEMISC['HEENT_ID']);
                    $pemisc->addAttribute("pChestId", $vGetConsultationPEMISC['CHEST_ID']);
                    $pemisc->addAttribute("pHeartId", $vGetConsultationPEMISC['HEART_ID']);
                    $pemisc->addAttribute("pAbdomenId", $vGetConsultationPEMISC['ABDOMEN_ID']);
                    $pemisc->addAttribute("pNeuroId", $vGetConsultationPEMISC['NEURO_ID']);
                    $pemisc->addAttribute("pGuId", $vGetConsultationPEMISC['GU_ID']);
                    $pemisc->addAttribute("pRectalId", $vGetConsultationPEMISC['RECTAL_ID']);
                    $pemisc->addAttribute("pReportStatus", "U");
                    $pemisc->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
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
            $pespecific->addAttribute("pSkinRem", $vGetConsult['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $vGetConsult['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $vGetConsult['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $vGetConsult['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $vGetConsult['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $vGetConsult['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $vGetConsult['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $vGetConsult['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", "");

            $vGetConsultationIcds = getConsultationIcd($vGetConsult['TRANS_NO']);
            $icds = $consultation->addChild("ICDS");
            if (count($vGetConsultationIcds) > 0) {
                foreach ($vGetConsultationIcds as $vGetConsultationIcd) {
                    $icd = $icds->addChild("ICD");
                    if ($vGetConsultationIcd['ICD_CODE'] != null || $vGetConsultationIcd['ICD_CODE'] != "") {
                        $icd->addAttribute("pIcdCode", $vGetConsultationIcd['ICD_CODE']);
                    } else {
                        $icd->addAttribute("pIcdCode", "000");
                    }
                   
                    $icd->addAttribute("pReportStatus", "U");
                    $icd->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
                $icd = $icds->addChild("ICD");
                $icd->addAttribute("pIcdCode", "000");
                $icd->addAttribute("pReportStatus", "U");
                $icd->addAttribute("pDeficiencyRemarks", "");
            }
                
            $vGetConsultationDiagnostics = getConsultationDiagnostic($vGetConsult['TRANS_NO']);
            $diagnostics = $consultation->addChild("DIAGNOSTICS");
                foreach ($vGetConsultationDiagnostics as $vGetConsultationDiagnostic) {
                    $diagnostic = $diagnostics->addChild("DIAGNOSTIC");
                    $diagnostic->addAttribute("pDiagnosticId", $vGetConsultationDiagnostic['DIAGNOSTIC_ID']);
                    $diagnostic->addAttribute("pOthRemarks", $vGetConsultationDiagnostic['OTH_REMARKS']);
                    $diagnostic->addAttribute("pIsPhysicianRecommendation", $vGetConsultationDiagnostic['IS_DR_RECOMMENDED']);
                    $diagnostic->addAttribute("pPatientRemarks", $vGetConsultationDiagnostic['PX_REMARKS']);
                    $diagnostic->addAttribute("pReportStatus", "U");
                    $diagnostic->addAttribute("pDeficiencyRemarks", "");
                }

            $vGetConsultationManagements = getConsultationManagement($vGetConsult['TRANS_NO']);
            $managements = $consultation->addChild("MANAGEMENTS");
            if (count($vGetConsultationManagements) > 0) {
                foreach ($vGetConsultationManagements as $vGetConsultationManagement) {
                    $management = $managements->addChild("MANAGEMENT");
                    $management->addAttribute("pManagementId", $vGetConsultationManagement['MANAGEMENT_ID']);
                    if ($vGetConsultationManagement['MANAGEMENT_ID'] == "X") {
                        $management->addAttribute("pOthRemarks", $vGetConsultationManagement['OTH_REMARKS']);
                    } else {
                        $management->addAttribute("pOthRemarks", "");
                    } 
                    $management->addAttribute("pReportStatus", "U");
                    $management->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
                $management = $managements->addChild("MANAGEMENT");
                $management->addAttribute("pManagementId", "0");
                $management->addAttribute("pOthRemarks", "");
                $management->addAttribute("pReportStatus", "U");
                $management->addAttribute("pDeficiencyRemarks", "");
            }

            if ($vGetConsult['REMARKS'] != NULL) {
                $advice = $consultation->addChild("ADVICE");
                $advice->addAttribute("pRemarks", $vGetConsult['REMARKS']);
                $advice->addAttribute("pReportStatus", "U");
                $advice->addAttribute("pDeficiencyRemarks", "");
            } else {
                $advice = $consultation->addChild("ADVICE");
                $advice->addAttribute("pRemarks", "NOT APPLICABLE");
                $advice->addAttribute("pReportStatus", "U");
                $advice->addAttribute("pDeficiencyRemarks", "");
            }

        } /*END CONSULTATION**/

    /*START DIAGNOTIC EXAM RESULTS XML GENERATION*/    
    foreach ($vGetConsultation as $vCaseDiag) {

        $labresults = $konsulta->addChild("DIAGNOSTICEXAMRESULTS");
        $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
        $labresult->addAttribute("pHciCaseNo", $vCaseDiag['CASE_NO']);

        $vGetProfiling = getProfiling($vCaseDiag["CASE_NO"]);
            $vGetProfFamhists = getProfFamhist($vGetProfiling['TRANS_NO']);
                foreach ($vGetProfFamhists as $vGetProfFamhist) {
                    if ($vGetProfFamhist['MDISEASE_CODE'] == "006") {
                        $labresult->addAttribute("pHciTransNo", $vGetProfiling['TRANS_NO']);
                        break;
                    } else {
                        $labresult->addAttribute("pHciTransNo", $vCaseDiag['TRANS_NO']);
                        break;
                    }
                }
        
        $labresult->addAttribute("pPatientPin", $vCaseDiag['PX_PIN']);
        $labresult->addAttribute("pPatientType", $vCaseDiag['PX_TYPE']);
        $labresult->addAttribute("pMemPin", $vCaseDiag['MEM_PIN']);
        $labresult->addAttribute("pEffYear", $vCaseDiag['EFF_YEAR']);

        //
        // FPE: FBS / RBS
        //
        foreach ($vGetProfFamhists as $vGetProfFamhist) {
            if ($vGetProfFamhist['MDISEASE_CODE'] == "006") {
                $vGetFBSS = getDiagFBS($vGetProfFamhist['TRANS_NO']);
                if (count($vGetFBSS) > 0)  {
                    foreach ($vGetFBSS as $vGetFBS) {
                        $fbss = $labresult->addChild("FBSS");
                        $fbs = $fbss->addChild("FBS");
                        $fbs->addAttribute("pReferralFacility", $vGetFBS['REFERRAL_FACILITY']);
                        $fbs->addAttribute("pLabDate", $vGetFBS['LAB_DATE']);
                        $fbs->addAttribute("pGlucoseMg", $vGetFBS['GLUCOSE_MG']);
                        $fbs->addAttribute("pGlucoseMmol", $vGetFBS['GLUCOSE_MMOL']);
                        $fbs->addAttribute("pDateAdded", $vGetFBS['DATE_ADDED']);
                        $fbs->addAttribute("pStatus", $vGetFBS['IS_APPLICABLE']);
                        $fbs->addAttribute("pDiagnosticLabFee", $vGetFBS['DIAGNOSTIC_FEE']);
                        $fbs->addAttribute("pReportStatus", "U");
                        $fbs->addAttribute("pDeficiencyRemarks", "");
                    }
                }

                $vGetRBSS = getDiagRBS($vGetProfFamhist['TRANS_NO']);
                if (count($vGetRBSS) > 0) {
                    foreach ($vGetRBSS as $vGetRBS) {
                        $rbss = $labresult->addChild("RBSS");
                        $rbs = $rbss->addChild("RBS");
                        $rbs->addAttribute("pReferralFacility", $vGetRBS['REFERRAL_FACILITY']);
                        $rbs->addAttribute("pLabDate", $vGetRBS['LAB_DATE']);
                        $rbs->addAttribute("pGlucoseMg", $vGetRBS['GLUCOSE_MG']);
                        $rbs->addAttribute("pGlucoseMmol", $vGetRBS['GLUCOSE_MMOL']);
                        $rbs->addAttribute("pDateAdded", $vGetRBS['DATE_ADDED']);
                        $rbs->addAttribute("pStatus", $vGetRBS['IS_APPLICABLE']);
                        $rbs->addAttribute("pDiagnosticLabFee", $vGetRBS['DIAGNOSTIC_FEE']);
                        $rbs->addAttribute("pReportStatus", "U");
                        $rbs->addAttribute("pDeficiencyRemarks", "");
                    }
                }
            } 
        }

        // FBS
        $vGetFBSS = getDiagFBS($vCaseDiag['TRANS_NO']);
            if (count($vGetFBSS) > 0) {
                foreach ($vGetFBSS as $vGetFBS) {
                    $fbss = $labresult->addChild("FBSS");
                    $fbs = $fbss->addChild("FBS");
                    $fbs->addAttribute("pReferralFacility", $vGetFBS['REFERRAL_FACILITY']);
                    $fbs->addAttribute("pLabDate", $vGetFBS['LAB_DATE']);
                    $fbs->addAttribute("pGlucoseMg", $vGetFBS['GLUCOSE_MG']);
                    $fbs->addAttribute("pGlucoseMmol", $vGetFBS['GLUCOSE_MMOL']);
                    $fbs->addAttribute("pDateAdded", $vGetFBS['DATE_ADDED']);
                    $fbs->addAttribute("pStatus", $vGetFBS['IS_APPLICABLE']);
                    $fbs->addAttribute("pDiagnosticLabFee", $vGetFBS['DIAGNOSTIC_FEE']);
                    $fbs->addAttribute("pReportStatus", "U");
                    $fbs->addAttribute("pDeficiencyRemarks", "");
                }
            }

        // RBS
        $vGetRBSS = getDiagRBS($vCaseDiag['TRANS_NO']);
            if (count($vGetRBSS) > 0) {
                foreach ($vGetRBSS as $vGetRBS) {
                    $rbss = $labresult->addChild("RBSS");
                    $rbs = $rbss->addChild("RBS");
                    $rbs->addAttribute("pReferralFacility", $vGetRBS['REFERRAL_FACILITY']);
                    $rbs->addAttribute("pLabDate", $vGetRBS['LAB_DATE']);
                    $rbs->addAttribute("pGlucoseMg", $vGetRBS['GLUCOSE_MG']);
                    $rbs->addAttribute("pGlucoseMmol", $vGetRBS['GLUCOSE_MMOL']);
                    $rbs->addAttribute("pDateAdded", $vGetRBS['DATE_ADDED']);
                    $rbs->addAttribute("pStatus", $vGetRBS['IS_APPLICABLE']);
                    $rbs->addAttribute("pDiagnosticLabFee", $vGetRBS['DIAGNOSTIC_FEE']);
                    $rbs->addAttribute("pReportStatus", "U");
                    $rbs->addAttribute("pDeficiencyRemarks", "");
                }
            }

        // CBC
        $vGetCBCS = getDiagCBC($vCaseDiag['TRANS_NO']);
            if (count($vGetCBCS) > 0) {
                foreach ($vGetCBCS as $vGetCBC) {
                    $cbcs = $labresult->addChild("CBCS");
                    $cbc = $cbcs->addChild("CBC");
                    $cbc->addAttribute("pReferralFacility", $vGetCBC['REFERRAL_FACILITY']);
                    $cbc->addAttribute("pLabDate", $vGetCBC['LAB_DATE']);
                    $cbc->addAttribute("pHematocrit", $vGetCBC['HEMATOCRIT']);
                    $cbc->addAttribute("pHemoglobinG", $vGetCBC['HEMOGLOBIN_G']);
                    $cbc->addAttribute("pHemoglobinMmol", $vGetCBC['HEMOGLOBIN_MMOL']);
                    $cbc->addAttribute("pMhcPg", $vGetCBC['MHC_PG']);
                    $cbc->addAttribute("pMhcFmol", $vGetCBC['MHC_FMOL']);
                    $cbc->addAttribute("pMchcGhb", $vGetCBC['MCHC_GHB']);
                    $cbc->addAttribute("pMchcMmol", $vGetCBC['MCHC_MMOL']);
                    $cbc->addAttribute("pMcvUm", $vGetCBC['MCV_UM']);
                    $cbc->addAttribute("pMcvFl", $vGetCBC['MCV_FL']);
                    $cbc->addAttribute("pWbc1000", $vGetCBC['WBC_1000']);
                    $cbc->addAttribute("pWbc10", $vGetCBC['WBC_10']);
                    $cbc->addAttribute("pMyelocyte", $vGetCBC['MYELOCYTE']);
                    $cbc->addAttribute("pNeutrophilsBnd", $vGetCBC['NEUTROPHILS_BND']);
                    $cbc->addAttribute("pNeutrophilsSeg", $vGetCBC['NEUTROPHILS_SEG']);
                    $cbc->addAttribute("pLymphocytes", $vGetCBC['LYMPHOCYTES']);
                    $cbc->addAttribute("pMonocytes", $vGetCBC['MONOCYTES']);
                    $cbc->addAttribute("pEosinophils", $vGetCBC['EOSINOPHILS']);
                    $cbc->addAttribute("pBasophils", $vGetCBC['BASOPHILS']);
                    $cbc->addAttribute("pPlatelet", $vGetCBC['PLATELET']);
                    $cbc->addAttribute("pDateAdded", $vGetCBC['DATE_ADDED']);
                    $cbc->addAttribute("pStatus", $vGetCBC['IS_APPLICABLE']);
                    $cbc->addAttribute("pDiagnosticLabFee", $vGetCBC['DIAGNOSTIC_FEE']);
                    $cbc->addAttribute("pReportStatus", "U");
                    $cbc->addAttribute("pDeficiencyRemarks", "");
                }
            }

        // Urinalysis
        $vGetDiagUrinalysis = getDiagUrinalysis($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagUrinalysis) > 0) {
                foreach ($vGetDiagUrinalysis as $vGetDiagUrine) {
                    $urinalysiss = $labresult->addChild("URINALYSISS");
                    $urinalysis = $urinalysiss->addChild("URINALYSIS");
                    $urinalysis->addAttribute("pReferralFacility", $vGetDiagUrine['REFERRAL_FACILITY']);
                    $urinalysis->addAttribute("pLabDate", $vGetDiagUrine['LAB_DATE']);
                    $urinalysis->addAttribute("pGravity", $vGetDiagUrine['GRAVITY']);
                    $urinalysis->addAttribute("pAppearance", $vGetDiagUrine['APPEARANCE']);
                    $urinalysis->addAttribute("pColor", $vGetDiagUrine['COLOR']);
                    $urinalysis->addAttribute("pGlucose", $vGetDiagUrine['GLUCOSE']);
                    $urinalysis->addAttribute("pProteins", $vGetDiagUrine['PROTEINS']);
                    $urinalysis->addAttribute("pKetones", $vGetDiagUrine['KETONES']);
                    $urinalysis->addAttribute("pPh", $vGetDiagUrine['PH']);
                    $urinalysis->addAttribute("pRbCells", $vGetDiagUrine['RB_CELLS']);
                    $urinalysis->addAttribute("pWbCells", $vGetDiagUrine['WB_CELLS']);
                    $urinalysis->addAttribute("pBacteria", $vGetDiagUrine['BACTERIA']);
                    $urinalysis->addAttribute("pCrystals", $vGetDiagUrine['CRYSTALS']);
                    $urinalysis->addAttribute("pBladderCell", $vGetDiagUrine['BLADDER_CELL']);
                    $urinalysis->addAttribute("pSquamousCell", $vGetDiagUrine['SQUAMOUS_CELL']);
                    $urinalysis->addAttribute("pTubularCell", $vGetDiagUrine['TUBULAR_CELL']);
                    $urinalysis->addAttribute("pBroadCasts", $vGetDiagUrine['BROAD_CASTS']);
                    $urinalysis->addAttribute("pEpithelialCast", $vGetDiagUrine['EPITHELIAL_CAST']);
                    $urinalysis->addAttribute("pGranularCast", $vGetDiagUrine['GRANULAR_CAST']);
                    $urinalysis->addAttribute("pHyalineCast", $vGetDiagUrine['HYALINE_CAST']);
                    $urinalysis->addAttribute("pRbcCast", $vGetDiagUrine['RBC_CAST']);
                    $urinalysis->addAttribute("pWaxyCast", $vGetDiagUrine['WAXY_CAST']);
                    $urinalysis->addAttribute("pWcCast", $vGetDiagUrine['WC_CAST']);
                    $urinalysis->addAttribute("pAlbumin", $vGetDiagUrine['ALBUMIN']);
                    $urinalysis->addAttribute("pPusCells", $vGetDiagUrine['PUS_CELLS']);
                    $urinalysis->addAttribute("pDateAdded", $vGetDiagUrine['DATE_ADDED']);
                    $urinalysis->addAttribute("pStatus", $vGetDiagUrine['IS_APPLICABLE']);
                    $urinalysis->addAttribute("pDiagnosticLabFee", $vGetDiagUrine['DIAGNOSTIC_FEE']);
                    $urinalysis->addAttribute("pReportStatus", "U");
                    $urinalysis->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // Fecalysis
        $vGetDiagFecalysis = getDiagFecalysis($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagFecalysis) > 0) {
                foreach ($vGetDiagFecalysis as $vGetDiagFecal) {
                    $fecalysiss = $labresult->addChild("FECALYSISS");
                    $fecalysis = $fecalysiss->addChild("FECALYSIS");
                    $fecalysis->addAttribute("pReferralFacility", $vGetDiagFecal['REFERRAL_FACILITY']);
                    $fecalysis->addAttribute("pLabDate", $vGetDiagFecal['LAB_DATE']);
                    $fecalysis->addAttribute("pColor", $vGetDiagFecal['COLOR']);
                    $fecalysis->addAttribute("pConsistency", $vGetDiagFecal['CONSISTENCY']);
                    $fecalysis->addAttribute("pRbc", $vGetDiagFecal['RBC']);
                    $fecalysis->addAttribute("pWbc", $vGetDiagFecal['WBC']);
                    $fecalysis->addAttribute("pOva", $vGetDiagFecal['OVA']);
                    $fecalysis->addAttribute("pParasite", $vGetDiagFecal['PARASITE']);
                    $fecalysis->addAttribute("pBlood", $vGetDiagFecal['BLOOD']);
                    $fecalysis->addAttribute("pPusCells", $vGetDiagFecal['PUS_CELLS']);
                    $fecalysis->addAttribute("pDateAdded", $vGetDiagFecal['DATE_ADDED']);
                    $fecalysis->addAttribute("pStatus", $vGetDiagFecal['IS_APPLICABLE']);
                    $fecalysis->addAttribute("pDiagnosticLabFee", $vGetDiagFecal['DIAGNOSTIC_FEE']);;
                    $fecalysis->addAttribute("pReportStatus", "U");
                    $fecalysis->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // ChestXray
        $vGetDiagChestXrays = getDiagChestXray($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagChestXrays) > 0) {
                foreach ($vGetDiagChestXrays as $vGetDiagChestXray) {
                    $chestxrays = $labresult->addChild("CHESTXRAYS");
                    $chestxray = $chestxrays->addChild("CHESTXRAY");
                    $chestxray->addAttribute("pReferralFacility", $vGetDiagChestXray['REFERRAL_FACILITY']);
                    $chestxray->addAttribute("pLabDate", $vGetDiagChestXray['LAB_DATE']);
                    $chestxray->addAttribute("pFindings", $vGetDiagChestXray['FINDINGS']);
                    $chestxray->addAttribute("pRemarksFindings", $vGetDiagChestXray['REMARKS_FINDINGS']);
                    $chestxray->addAttribute("pObservation", $vGetDiagChestXray['OBSERVATION']);
                    $chestxray->addAttribute("pRemarksObservation", $vGetDiagChestXray['REMARKS_OBSERVATION']);
                    $chestxray->addAttribute("pDateAdded", $vGetDiagChestXray['DATE_ADDED']);
                    $chestxray->addAttribute("pStatus", $vGetDiagChestXray['IS_APPLICABLE']);
                    $chestxray->addAttribute("pDiagnosticLabFee", $vGetDiagChestXray['DIAGNOSTIC_FEE']);
                    $chestxray->addAttribute("pReportStatus", "U");
                    $chestxray->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // Sputum
        $vGetDiagSputums = getDiagSputum($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagSputums) > 0) {
                foreach ($vGetDiagSputums as $vGetDiagSputum) {
                    $sputums = $labresult->addChild("SPUTUMS");
                    $sputum = $sputums->addChild("SPUTUM");
                    $sputum->addAttribute("pReferralFacility", $vGetDiagSputum['REFERRAL_FACILITY']);
                    $sputum->addAttribute("pLabDate", $vGetDiagSputum['LAB_DATE']);
                    $sputum->addAttribute("pDataCollection", $vGetDiagSputum['DATA_COLLECTION']);
                    $sputum->addAttribute("pFindings", $vGetDiagSputum['FINDINGS']);
                    $sputum->addAttribute("pRemarks", $vGetDiagSputum['REMARKS']);
                    $sputum->addAttribute("pNoPlusses", $vGetDiagSputum['NO_PLUSSES']);
                    $sputum->addAttribute("pDateAdded", $vGetDiagSputum['DATE_ADDED']);
                    $sputum->addAttribute("pStatus", $vGetDiagSputum['IS_APPLICABLE']);
                    $sputum->addAttribute("pDiagnosticLabFee", $vGetDiagSputum['DIAGNOSTIC_FEE']);
                    $sputum->addAttribute("pReportStatus", "U");
                    $sputum->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // Lipid Profile
        $vGetDiagLipidProfs = getDiagLipidProf($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagLipidProfs) > 0) {
                foreach ($vGetDiagLipidProfs as $vGetDiagLipidProf) {
                    $lipidprofs = $labresult->addChild("LIPIDPROFILES");
                    $lipidprof = $lipidprofs->addChild("LIPIDPROFILE");
                    $lipidprof->addAttribute("pReferralFacility", $vGetDiagLipidProf['REFERRAL_FACILITY']);
                    $lipidprof->addAttribute("pLabDate", $vGetDiagLipidProf['LAB_DATE']);
                    $lipidprof->addAttribute("pLdl", $vGetDiagLipidProf['LDL']);
                    $lipidprof->addAttribute("pHdl", $vGetDiagLipidProf['HDL']);
                    $lipidprof->addAttribute("pTotal", $vGetDiagLipidProf['TOTAL']);
                    $lipidprof->addAttribute("pCholesterol", $vGetDiagLipidProf['CHOLESTEROL']);
                    $lipidprof->addAttribute("pTriglycerides", $vGetDiagLipidProf['TRIGLYCERIDES']);
                    $lipidprof->addAttribute("pDateAdded", $vGetDiagLipidProf['DATE_ADDED']);
                    $lipidprof->addAttribute("pStatus", $vGetDiagLipidProf['IS_APPLICABLE']);
                    $lipidprof->addAttribute("pDiagnosticLabFee", $vGetDiagLipidProf['DIAGNOSTIC_FEE']);
                    $lipidprof->addAttribute("pReportStatus", "U");
                    $lipidprof->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // ECG
        $vGetDiagECGS = getDiagECG($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagECGS) > 0) {
                foreach ($vGetDiagECGS as $vGetDiagECG) {                    
                    $ecgs = $labresult->addChild("ECGS");
                    $ecg = $ecgs->addChild("ECG");
                    $ecg->addAttribute("pReferralFacility", $vGetDiagECG['REFERRAL_FACILITY']);
                    $ecg->addAttribute("pLabDate", $vGetDiagECG['LAB_DATE']);
                    $ecg->addAttribute("pFindings", $vGetDiagECG['FINDINGS']);
                    $ecg->addAttribute("pRemarks", strtoupper($vGetDiagECG['REMARKS']));
                    $ecg->addAttribute("pDateAdded", $vGetDiagECG['DATE_ADDED']);
                    $ecg->addAttribute("pStatus", $vGetDiagECG['IS_APPLICABLE']);
                    $ecg->addAttribute("pDiagnosticLabFee", $vGetDiagECG['DIAGNOSTIC_FEE']);
                    $ecg->addAttribute("pReportStatus", "U");
                    $ecg->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // OGTT
        $vGetDiagOGTTS = getDiagOGTT($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagOGTTS) > 0) {
                foreach ($vGetDiagOGTTS as $vGetDiagOGTT) {
                    $ogtts = $labresult->addChild("OGTTS");
                    $ogtt = $ogtts->addChild("OGTT");
                    $ogtt->addAttribute("pReferralFacility", $vGetDiagOGTT['REFERRAL_FACILITY']);
                    $ogtt->addAttribute("pLabDate", $vGetDiagOGTT['LAB_DATE']);
                    $ogtt->addAttribute("pExamFastingMg", $vGetDiagOGTT['EXAM_FASTING_MG']);
                    $ogtt->addAttribute("pExamFastingMmol", $vGetDiagOGTT['EXAM_FASTING_MMOL']);
                    $ogtt->addAttribute("pExamOgttOneHrMg", $vGetDiagOGTT['EXAM_OGTT_ONE_MG']);
                    $ogtt->addAttribute("pExamOgttOneHrMmol", $vGetDiagOGTT['EXAM_OGTT_ONE_MMOL']);
                    $ogtt->addAttribute("pExamOgttTwoHrMg", $vGetDiagOGTT['EXAM_OGTT_TWO_MG']);
                    $ogtt->addAttribute("pExamOgttTwoHrMmol", $vGetDiagOGTT['EXAM_OGTT_TWO_MMOL']);
                    $ogtt->addAttribute("pDateAdded", $vGetDiagOGTT['DATE_ADDED']);
                    $ogtt->addAttribute("pStatus", $vGetDiagOGTT['IS_APPLICABLE']);
                    $ogtt->addAttribute("pDiagnosticLabFee", $vGetDiagOGTT['DIAGNOSTIC_FEE']);
                    $ogtt->addAttribute("pReportStatus", "U");
                    $ogtt->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // Pap Smear
        $vGetDiagPapSmears = getDiagPapSmear($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagPapSmears) > 0) {
                foreach ($vGetDiagPapSmears as $vGetDiagPapSmear) {
                    $papss = $labresult->addChild("PAPSMEARS");
                    $paps = $papss->addChild("PAPSMEAR");
                    $paps->addAttribute("pReferralFacility", $vGetDiagPapSmear['REFERRAL_FACILITY']);
                    $paps->addAttribute("pLabDate", $vGetDiagPapSmear['LAB_DATE']);
                    $paps->addAttribute("pFindings", strtoupper($genResultPaps['FINDINGS']));
                    $paps->addAttribute("pImpression", strtoupper($genResultPaps['IMPRESSION']));
                    $paps->addAttribute("pDateAdded", $vGetDiagPapSmear['DATE_ADDED']);
                    $paps->addAttribute("pStatus", $vGetDiagPapSmear['IS_APPLICABLE']);
                    $paps->addAttribute("pDiagnosticLabFee", $vGetDiagPapSmear['DIAGNOSTIC_FEE']);
                    $paps->addAttribute("pReportStatus", "U");
                    $paps->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // FOBT
        $vGetDiagFOBTS = getDiagFOBT($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagFOBTS) > 0) {
                foreach ($vGetDiagFOBTS as $vGetDiagFOBT) {
                    $fobts = $labresult->addChild("FOBTS");
                    $fobt = $fobts->addChild("FOBT");
                    $fobt->addAttribute("pReferralFacility", $vGetDiagFOBT['REFERRAL_FACILITY']);
                    $fobt->addAttribute("pLabDate", $vGetDiagFOBT['LAB_DATE']);
                    $fobt->addAttribute("pFindings", $vGetDiagFOBT['FINDINGS']);
                    $fobt->addAttribute("pDateAdded", $vGetDiagFOBT['DATE_ADDED']);
                    $fobt->addAttribute("pStatus", $vGetDiagFOBT['IS_APPLICABLE']);
                    $fobt->addAttribute("pDiagnosticLabFee", $vGetDiagFOBT['DIAGNOSTIC_FEE']);
                    $fobt->addAttribute("pReportStatus", "U");
                    $fobt->addAttribute("pDeficiencyRemarks", "");
                } 
            }
              

        // Creatinine
        $vGetDiagCreatinines = getDiagCreatinine($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagCreatinines) > 0) {
                foreach ($vGetDiagCreatinines as $vGetDiagCreatinine) {
                    $creatinines = $labresult->addChild("CREATININES");
                    $creatinine = $creatinines->addChild("CREATININE");
                    $creatinine->addAttribute("pReferralFacility", $vGetDiagCreatinine['REFERRAL_FACILITY']);
                    $creatinine->addAttribute("pLabDate", $vGetDiagCreatinine['LAB_DATE']);
                    $creatinine->addAttribute("pFindings", $vGetDiagCreatinine['FINDINGS']);
                    $creatinine->addAttribute("pDateAdded", $vGetDiagCreatinine['DATE_ADDED']);
                    $creatinine->addAttribute("pStatus", $vGetDiagCreatinine['IS_APPLICABLE']);
                    $creatinine->addAttribute("pDiagnosticLabFee", $vGetDiagCreatinine['DIAGNOSTIC_FEE']);
                    $creatinine->addAttribute("pReportStatus", "U");
                    $creatinine->addAttribute("pDeficiencyRemarks", "");
                }
            }
            

        // PDD Test
        $vGetDiagPDDS = getDiagPDD($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagPDDS) > 0) {
                foreach ($vGetDiagPDDS as $vGetDiagPDD) {
                    $pdds = $labresult->addChild("PPDTests");
                    $pdd = $pdds->addChild("PPDTest");
                    $pdd->addAttribute("pReferralFacility", $vGetDiagPDD['REFERRAL_FACILITY']);
                    $pdd->addAttribute("pLabDate", date('Y-m-d', strtotime($vGetDiagPDD['LAB_DATE'])));
                    $pdd->addAttribute("pFindings", $vGetDiagPDD['FINDINGS']);
                    $pdd->addAttribute("pDateAdded", date('Y-m-d', strtotime($vGetDiagPDD['DATE_ADDED'])));
                    $pdd->addAttribute("pStatus", $vGetDiagPDD['IS_APPLICABLE']);
                    $pdd->addAttribute("pDiagnosticLabFee", $vGetDiagPDD['DIAGNOSTIC_FEE']);
                    $pdd->addAttribute("pReportStatus", "U");
                    $pdd->addAttribute("pDeficiencyRemarks", "");
                }
            } 
            

        // HbA1C
        $vGetDiagHbA1cs = getDiagHbA1c($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagHbA1cs) >0 ) {
                foreach ($vGetDiagHbA1cs as $vGetDiagHbA1c) {
                    $hba1cs = $labresult->addChild("HbA1cs");
                    $hba1c = $hba1cs->addChild("HbA1c");
                    $hba1c->addAttribute("pReferralFacility", $vGetDiagHbA1c['REFERRAL_FACILITY']);
                    $hba1c->addAttribute("pLabDate", $vGetDiagHbA1c['LAB_DATE']);
                    $hba1c->addAttribute("pFindings", $vGetDiagHbA1c['FINDINGS']);
                    $hba1c->addAttribute("pDateAdded", $vGetDiagHbA1c['DATE_ADDED']);
                    $hba1c->addAttribute("pStatus", $vGetDiagHbA1c['IS_APPLICABLE']);
                    $hba1c->addAttribute("pDiagnosticLabFee", $vGetDiagHbA1c['DIAGNOSTIC_FEE']);
                    $hba1c->addAttribute("pReportStatus", "U");
                    $hba1c->addAttribute("pDeficiencyRemarks", "");
                }
            }   
            

        // Others
        $vGetDiagOthDiags = getDiagOthDiag($vCaseDiag['TRANS_NO']);
            if (count($vGetDiagOthDiags) > 0) {
                foreach ($vGetDiagOthDiags as $vGetDiagOthDiag) {
                    $othDiags = $labresult->addChild("OTHERDIAGEXAMS");
                    $othDiag = $othDiags->addChild("OTHERDIAGEXAM");
                    $othDiag->addAttribute("pReferralFacility", $vGetDiagOthDiag['REFERRAL_FACILITY']);
                    $othDiag->addAttribute("pLabDate", $vGetDiagOthDiag['LAB_DATE']);
                    $othDiag->addAttribute("pOthDiagExam", $vGetDiagOthDiag['OTH_DIAG_EXAM']);
                    $othDiag->addAttribute("pFindings", $vGetDiagOthDiag['FINDINGS']);
                    $othDiag->addAttribute("pDateAdded", $vGetDiagOthDiag['DATE_ADDED']);
                    $othDiag->addAttribute("pStatus", $vGetDiagOthDiag['IS_APPLICABLE']);
                    $othDiag->addAttribute("pDiagnosticLabFee", $vGetDiagOthDiag['DIAGNOSTIC_FEE']);
                    $othDiag->addAttribute("pReportStatus", "U");
                    $othDiag->addAttribute("pDeficiencyRemarks", "");
                }
            }
    } /*END DIAGNOTIC EXAM RESULTS **/


    /*MEDICINE XML GENERATION*/
    foreach ($vGetConsultation as $vCase) {
        $vGetMedicines = getMedicine($vCase["TRANS_NO"]);
        if (count($vGetMedicines) > 0) {
            $medicines = $konsulta->addChild("MEDICINES");
            foreach ($vGetMedicines as $vGetMedicine) {
                $meds = $medicines->addChild("MEDICINE");
                $meds->addAttribute("pHciCaseNo", $vGetMedicine['CASE_NO']);
                $meds->addAttribute("pHciTransNo", $vGetMedicine['TRANS_NO']);
                $meds->addAttribute("pCategory", $vGetMedicine['CATEGORY']);
                $meds->addAttribute("pDrugCode", $vGetMedicine['DRUG_CODE']);
                $meds->addAttribute("pGenericCode", $vGetMedicine['GEN_CODE']);
                $meds->addAttribute("pSaltCode", $vGetMedicine['SALT_CODE']);
                $meds->addAttribute("pStrengthCode", $vGetMedicine['STRENGTH_CODE']);
                $meds->addAttribute("pFormCode", $vGetMedicine['FORM_CODE']);
                $meds->addAttribute("pUnitCode", $vGetMedicine['UNIT_CODE']);
                $meds->addAttribute("pPackageCode", $vGetMedicine['PACKAGE_CODE']);
                $meds->addAttribute("pOtherMedicine", $vGetMedicine['GENERIC_NAME']);
                
                if ($vGetMedicine['GENERIC_NAME'] != null && ($vGetMedicine['DRUG_GROUPING'] == null || $vGetMedicine['DRUG_GROUPING'] == "")) {
                    $vDrugGrouping = "OTHERS";
                } else {
                    $vDrugGrouping = $vGetMedicine['DRUG_GROUPING'];
                }

                $meds->addAttribute("pOthMedDrugGrouping", $vDrugGrouping);

                //$meds->addAttribute("pOthMedDrugGrouping", $vGetMedicine['DRUG_GROUPING']);
                $meds->addAttribute("pRoute", $vGetMedicine['ROUTE']);
            
                if ($vGetMedicine['QUANTITY'] == null ) {
                    $meds->addAttribute("pQuantity", 0);
                } else {
                    $meds->addAttribute("pQuantity", $vGetMedicine['QUANTITY']);
                }

                if ($vGetMedicine['DRUG_ACTUAL_PRICE'] == null ) {
                    $meds->addAttribute("pActualUnitPrice", 0);
                } else {
                    $meds->addAttribute("pActualUnitPrice", $vGetMedicine['DRUG_ACTUAL_PRICE']);
                }

                if ($vGetMedicine['AMT_PRICE'] == null ) {
                    $meds->addAttribute("pTotalAmtPrice", 0);
                } else {
                    $meds->addAttribute("pTotalAmtPrice", $vGetMedicine['AMT_PRICE']);
                }

                if ($vGetMedicine['INS_QUANTITY'] == null ) {
                    $meds->addAttribute("pInstructionQuantity", 0);
                } else {
                    $meds->addAttribute("pInstructionQuantity", $vGetMedicine['INS_QUANTITY']);
                }
                
                $meds->addAttribute("pInstructionStrength", $vGetMedicine['INS_STRENGTH']);
                $meds->addAttribute("pInstructionFrequency", $vGetMedicine['INS_FREQUENCY']);
                $meds->addAttribute("pPrescribingPhysician", $vGetMedicine['PRESC_PHYSICIAN']);
                $meds->addAttribute("pIsDispensed", $vGetMedicine['IS_DISPENSED']);
                
                if ($vGetMedicine['IS_DISPENSED'] == 'Y' && $vGetMedicine['DISPENSING_PERSONNEL'] == NULL) {
                    $meds->addAttribute("pDateDispensed", $vGetMedicine['DISPENSED_DATE']);
                    $meds->addAttribute("pDispensingPersonnel", $vGetMedicine['PRESC_PHYSICIAN']);
                } else if ($vGetMedicine['IS_DISPENSED'] == 'Y' && $vGetMedicine['DISPENSING_PERSONNEL'] != NULL) {
                    $meds->addAttribute("pDateDispensed", $vGetMedicine['DISPENSED_DATE']);
                    $meds->addAttribute("pDispensingPersonnel", $vGetMedicine['DISPENSING_PERSONNEL']);
                } else if ($vGetMedicine['IS_DISPENSED'] == 'N') {
                    $meds->addAttribute("pDateDispensed", "");
                    $meds->addAttribute("pDispensingPersonnel", "");
                }

                if ($vGetMedicine['IS_APPLICABLE'] == null || $vGetMedicine['IS_APPLICABLE'] == "") {
                    $vIsMedsApplicable = "N";
                } else {
                    $vIsMedsApplicable = "Y";
                }
               
                $meds->addAttribute("pIsApplicable", $vIsMedsApplicable);
                $meds->addAttribute("pDateAdded", $vGetMedicine['DATE_ADDED']);
                $meds->addAttribute("pReportStatus", "U");
                $meds->addAttribute("pDeficiencyRemarks", "");
            }
        } else {
           $medicines = $konsulta->addChild("MEDICINES"); 
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
            $meds->addAttribute("pIsApplicable", "N");
            $meds->addAttribute("pDateAdded", "");
            $meds->addAttribute("pReportStatus", "U");
            $meds->addAttribute("pDeficiencyRemarks", "");
        }
    }

    $dom = dom_import_simplexml($konsulta)->ownerDocument;
    $dom ->formatOutput = true;

    $xml = $dom->saveXML();
    $xmlString = str_replace("<?xml version=\"1.0\"?>\n", '', $xml);
    file_put_contents("tmp/konsulta_raw_xml_grp_all.xml", $xmlString);

    return $xmlString;
}


/*Generate XML Report*/
function generateXmlPerIndividual ($pCaseNo) {
    $konsulta = new SimpleXMLElement("<PCB></PCB>");

    $pReportTransNo = generateTransNo('REPORT_TRANS_NO');
    $pDateRange = $pStartDate." TO ".$pEndDate;

    $konsulta->addAttribute("pUsername", "");
    $konsulta->addAttribute("pPassword", "");
    $konsulta->addAttribute("pHciAccreNo", $_SESSION['pAccreNum']);
    $konsulta->addAttribute("pPMCCNo", "");
    $konsulta->addAttribute("pEnlistTotalCnt", 1);
    $konsulta->addAttribute("pProfileTotalCnt", 1);
    $konsulta->addAttribute("pSoapTotalCnt", 1);
    $konsulta->addAttribute("pCertificationId", "EKON-00-06-2020-00001");
    $konsulta->addAttribute("pHciTransmittalNumber", $pReportTransNo);

    /*ENLISTMENT XML GENERATION*/
        $enlistments = $konsulta->addChild("ENLISTMENTS");

            $vGetEnlist = getRegistration($pCaseNo);

            $enlistment = $enlistments->addChild("ENLISTMENT");
            $enlistment->addAttribute("pHciCaseNo", $vGetEnlist['CASE_NO']);
            $enlistment->addAttribute("pHciTransNo", $vGetEnlist['TRANS_NO']);
            $enlistment->addAttribute("pEffYear", $vGetEnlist['EFF_YEAR']);
            $enlistment->addAttribute("pEnlistStat", $vGetEnlist['ENLIST_STAT']);
            $enlistment->addAttribute("pEnlistDate", $vGetEnlist['ENLIST_DATE']);
            $enlistment->addAttribute("pPackageType", $vGetEnlist['PACKAGE_TYPE']);
            $enlistment->addAttribute("pMemPin", $vGetEnlist['MEM_PIN']);
            $enlistment->addAttribute("pMemFname", trim(strReplaceEnye($vGetEnlist['MEM_FNAME'])));
            $enlistment->addAttribute("pMemMname", trim(strReplaceEnye($vGetEnlist['MEM_MNAME'])));
            $enlistment->addAttribute("pMemLname", trim(strReplaceEnye($vGetEnlist['MEM_LNAME'])));
            $enlistment->addAttribute("pMemExtname", trim($vGetEnlist['MEM_EXTNAME']));
            $enlistment->addAttribute("pMemDob", $vGetEnlist['MEM_DOB']);
            $enlistment->addAttribute("pPatientPin", $vGetEnlist['PX_PIN']);
            $enlistment->addAttribute("pPatientFname", trim(strReplaceEnye($vGetEnlist['PX_FNAME'])));
            $enlistment->addAttribute("pPatientMname", trim(strReplaceEnye($vGetEnlist['PX_MNAME'])));
            $enlistment->addAttribute("pPatientLname", trim(strReplaceEnye($vGetEnlist['PX_LNAME'])));
            $enlistment->addAttribute("pPatientExtname", trim($vGetEnlist['PX_EXTNAME']));
            if ($vGetEnlist['PX_SEX'] == '0') {
                $vPxSex = "M";
            } else if ($vGetEnlist['PX_SEX'] == '1') {
                $vPxSex = "F";
            } else {
                $vPxSex = $vGetEnlist['PX_SEX'];
            }
            $enlistment->addAttribute("pPatientSex", $vPxSex);
            $enlistment->addAttribute("pPatientDob", $vGetEnlist['PX_DOB']);
            $enlistment->addAttribute("pPatientType", $vGetEnlist['PX_TYPE']);
            if ($vGetEnlist['PX_MOBILE_NO'] == null) {
                $vPxMobileNo = "-";
            } else {
                $vPxMobileNo = $vGetEnlist['PX_MOBILE_NO'];
            }
            $enlistment->addAttribute("pPatientMobileNo", $vPxMobileNo);
            $enlistment->addAttribute("pPatientLandlineNo", $vGetEnlist['PX_LANDLINE_NO']);
            $enlistment->addAttribute("pWithConsent", $vGetEnlist['WITH_CONSENT']);
            $enlistment->addAttribute("pTransDate", $vGetEnlist['TRANS_DATE']);
            $enlistment->addAttribute("pCreatedBy", $vGetEnlist['CREATED_BY']);
            $enlistment->addAttribute("pReportStatus", "U");
            $enlistment->addAttribute("pDeficiencyRemarks", "");

    /*PROFILING XML GENERATION*/
        $profiling = $konsulta->addChild("PROFILING");
            $vGetProfiling = getProfiling($pCaseNo);
    
            $profile = $profiling->addChild("PROFILE");
            $profile->addAttribute("pHciTransNo", $vGetProfiling['TRANS_NO']);
            $profile->addAttribute("pHciCaseNo", $vGetProfiling['CASE_NO']);
            $profile->addAttribute("pProfDate", $vGetProfiling['PROF_DATE']);
            $profile->addAttribute("pPatientPin", $vGetProfiling['PX_PIN']);
            $profile->addAttribute("pPatientType", $vGetProfiling['PX_TYPE']);
            $profile->addAttribute("pPatientAge", $vGetProfiling['PX_AGE']);
            $profile->addAttribute("pMemPin", $vGetProfiling['MEM_PIN']);
            $profile->addAttribute("pEffYear", $vGetProfiling['EFF_YEAR']);
            $profile->addAttribute("pATC", $vGetProfiling['PROFILE_OTP']);
            $profile->addAttribute("pIsWalkedIn", $vGetProfiling['WITH_ATC']);
            $profile->addAttribute("pTransDate", $vGetProfiling['DATE_ADDED']);
            $profile->addAttribute("pReportStatus", "U");
            $profile->addAttribute("pDeficiencyRemarks", "");

            $vGetProfMedHists = getProfMedHist($vGetProfiling['TRANS_NO']);
            $medhists = $profile->addChild("MEDHISTS");
            if (count($vGetProfMedHists) > 0) {
                foreach ($vGetProfMedHists as $vGetProfMedHist) {
                    $medhist = $medhists->addChild("MEDHIST");
                    if ($vGetProfMedHist['MDISEASE_CODE'] == null || $vGetProfMedHist['MDISEASE_CODE'] == "") {
                        $medhist->addAttribute("pMdiseaseCode", "999");
                    } else {
                        $medhist->addAttribute("pMdiseaseCode", $vGetProfMedHist['MDISEASE_CODE']);
                    }
                    $medhist->addAttribute("pReportStatus", "U");
                    $medhist->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
                $medhist = $medhists->addChild("MEDHIST");
                $medhist->addAttribute("pMdiseaseCode", "999");
                $medhist->addAttribute("pReportStatus", "U");
                $medhist->addAttribute("pDeficiencyRemarks", "");
            }


            $vGetProfMHSpecifics = getProfMHSpecific($vGetProfiling['TRANS_NO']);
            $mhspecifics = $profile->addChild("MHSPECIFICS");
                if (count($vGetProfMHSpecifics) > 0) {
                    foreach ($vGetProfMHSpecifics as $vGetProfMHSpecific) {
                        $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                        $mhspecific->addAttribute("pMdiseaseCode", $vGetProfMHSpecific['MDISEASE_CODE']);
                        $mhspecific->addAttribute("pSpecificDesc", $vGetProfMHSpecific['SPECIFIC_DESC']);
                        $mhspecific->addAttribute("pReportStatus", "U");
                        $mhspecific->addAttribute("pDeficiencyRemarks","");
                    }
                } else {
                    $mhspecific = $mhspecifics->addChild("MHSPECIFIC");
                    $mhspecific->addAttribute("pMdiseaseCode", "");
                    $mhspecific->addAttribute("pSpecificDesc", "");
                    $mhspecific->addAttribute("pReportStatus", "U");
                    $mhspecific->addAttribute("pDeficiencyRemarks","");
                }

            $vGetProfSurghists = getProfSurghist($vGetProfiling['TRANS_NO']);
            $surghists = $profile->addChild("SURGHISTS");
                if (count($vGetProfSurghists) > 0 ) {
                    foreach ($vGetProfSurghists as $vGetProfSurghist) {
                        $surghist = $surghists->addChild("SURGHIST");
                        $surghist->addAttribute("pSurgDesc", $vGetProfSurghist['SURG_DESC']);
                        $surghist->addAttribute("pSurgDate", $vGetProfSurghist['SURG_DATE']);
                        $surghist->addAttribute("pReportStatus", "U");
                        $surghist->addAttribute("pDeficiencyRemarks", "");
                    }
                } else {
                    $surghist = $surghists->addChild("SURGHIST");
                    $surghist->addAttribute("pSurgDesc", "");
                    $surghist->addAttribute("pSurgDate", "");
                    $surghist->addAttribute("pReportStatus", "U");
                    $surghist->addAttribute("pDeficiencyRemarks", "");
                }

            $vGetProfFamhists = getProfFamhist($vGetProfiling['TRANS_NO']);
            $famhists = $profile->addChild("FAMHISTS");
            if (count($vGetProfFamhists) > 0) {
                foreach ($vGetProfFamhists as $vGetProfFamhist) {
                    $famhist = $famhists->addChild("FAMHIST");
                    if ($vGetProfFamhist['MDISEASE_CODE'] == null || $vGetProfFamhist['MDISEASE_CODE'] == "") {
                        $famhist->addAttribute("pMdiseaseCode", "999");
                    } else {
                        $famhist->addAttribute("pMdiseaseCode", $vGetProfFamhist['MDISEASE_CODE']);
                    }
                    $famhist->addAttribute("pReportStatus", "U");
                    $famhist->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
                 $famhist = $famhists->addChild("FAMHIST");
                 $famhist->addAttribute("pMdiseaseCode", "999");
                 $famhist->addAttribute("pReportStatus", "U");
                 $famhist->addAttribute("pDeficiencyRemarks", "");
            }

            $vGetProfFHSpecifics = getProfFHSpecific($vGetProfiling['TRANS_NO']);
            $fhspecifics = $profile->addChild("FHSPECIFICS");
                if (count($vGetProfFHSpecifics) > 0) {
                    foreach ($vGetProfFHSpecifics as $vGetProfFHSpecific) {
                            $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                            $fhspecific->addAttribute("pMdiseaseCode", $vGetProfFHSpecific['MDISEASE_CODE']);
                            $fhspecific->addAttribute("pSpecificDesc", $vGetProfFHSpecific['SPECIFIC_DESC']);
                            $fhspecific->addAttribute("pReportStatus", "U");
                            $fhspecific->addAttribute("pDeficiencyRemarks", "");
                    }
                } else {
                    $fhspecific = $fhspecifics->addChild("FHSPECIFIC");
                    $fhspecific->addAttribute("pMdiseaseCode", "");
                    $fhspecific->addAttribute("pSpecificDesc", "");
                    $fhspecific->addAttribute("pReportStatus", "U");
                    $fhspecific->addAttribute("pDeficiencyRemarks", "");
                }

           $sochist = $profile->addChild("SOCHIST");
            if ($vGetProfiling['IS_SMOKER'] != null || $vGetProfiling['IS_SMOKER'] != "") {
                $sochist->addAttribute("pIsSmoker", $vGetProfiling['IS_SMOKER']);
            } else {
                $sochist->addAttribute("pIsSmoker", "N");
            }
            $sochist->addAttribute("pNoCigpk", $vGetProfiling['NO_CIGPK']);
            if ($vGetProfiling['IS_ADRINKER'] != null || $vGetProfiling['IS_ADRINKER'] != "") {
                $sochist->addAttribute("pIsAdrinker", $vGetProfiling['IS_ADRINKER']);
            } else {
                $sochist->addAttribute("pIsAdrinker", "N");
            }
            $sochist->addAttribute("pNoBottles", $vGetProfiling['NO_BOTTLES']);
            if ($vGetProfiling['ILL_DRUG_USER'] != null || $vGetProfiling['ILL_DRUG_USER'] != "") {
                $sochist->addAttribute("pIllDrugUser", $vGetProfiling['ILL_DRUG_USER']);
            } else {
                $sochist->addAttribute("pIllDrugUser", "N");
            }
            if ($vGetProfiling['IS_SEXUALLY_ACTIVE'] != null || $vGetProfiling['IS_SEXUALLY_ACTIVE'] != "") {
                $sochist->addAttribute("pIsSexuallyActive", $vGetProfiling['IS_SEXUALLY_ACTIVE']);
            } else {
                $sochist->addAttribute("pIsSexuallyActive", "N");
            }
            $sochist->addAttribute("pReportStatus", "U");
            $sochist->addAttribute("pDeficiencyRemarks", "");

            $vGetProfImmunizations = getProfImmunization($vGetProfiling['TRANS_NO']);
            $immunizations = $profile->addChild("IMMUNIZATIONS");
            if (count ($vGetProfImmunizations) > 0) {
                foreach ($vGetProfImmunizations as $vGetProfImmunization) {
                    $immunization = $immunizations->addChild("IMMUNIZATION");

                    if ($vGetProfImmunization['CHILD_IMMCODE'] == null || $vGetProfImmunization['CHILD_IMMCODE'] == "") {
                        $immunization->addAttribute("pChildImmcode", "");
                    } else {
                        $immunization->addAttribute("pChildImmcode", $vGetProfImmunization['CHILD_IMMCODE']);
                    }

                    if ($vGetProfImmunization['YOUNGW_IMMCODE'] == null || $vGetProfImmunization['YOUNGW_IMMCODE'] == "") {
                        $immunization->addAttribute("pYoungwImmcode", "");
                    } else {
                        $immunization->addAttribute("pYoungwImmcode", $vGetProfImmunization['YOUNGW_IMMCODE']);
                    }

                    if ($vGetProfImmunization['PREGW_IMMCODE'] == null || $vGetProfImmunization['PREGW_IMMCODE'] == "") {
                        $immunization->addAttribute("pPregwImmcode", "");
                    } else {
                        $immunization->addAttribute("pPregwImmcode", $vGetProfImmunization['PREGW_IMMCODE']);
                    }
                    
                    if ($vGetProfImmunization['ELDERLY_IMMCODE'] == null || $vGetProfImmunization['ELDERLY_IMMCODE'] == "") {
                        $immunization->addAttribute("pElderlyImmcode", "");
                    } else {
                        $immunization->addAttribute("pElderlyImmcode", $vGetProfImmunization['ELDERLY_IMMCODE']);
                    }
                    $immunization->addAttribute("pOtherImm", $vGetProfImmunization['OTHER_IMM']);
                    $immunization->addAttribute("pReportStatus", "U");
                    $immunization->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
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
            $menshist->addAttribute("pMenarchePeriod", $vGetProfiling['MENARCHE_PERIOD']);
            $menshist->addAttribute("pLastMensPeriod", $vGetProfiling['LAST_MENS_PERIOD']);
            $menshist->addAttribute("pPeriodDuration", $vGetProfiling['PERIOD_DURATION']);
            $menshist->addAttribute("pMensInterval", $vGetProfiling['MENS_INTERVAL']);
            $menshist->addAttribute("pPadsPerDay", $vGetProfiling['PADS_PER_DAY']);
            $menshist->addAttribute("pOnsetSexIc", $vGetProfiling['ONSET_SEX_IC']);
            $menshist->addAttribute("pBirthCtrlMethod", $vGetProfiling['BIRTH_CTRL_METHOD']);
            $menshist->addAttribute("pIsMenopause", $vGetProfiling['IS_MENOPAUSE']);
            $menshist->addAttribute("pMenopauseAge", $vGetProfiling['MENOPAUSE_AGE']);
            $menshist->addAttribute("pIsApplicable", $vGetProfiling['MENS_IS_APPLICABLE']);
            $menshist->addAttribute("pReportStatus", "U");
            $menshist->addAttribute("pDeficiencyRemarks","");

            $preghist = $profile->addChild("PREGHIST");
            $preghist->addAttribute("pPregCnt", $vGetProfiling['PREG_CNT']);
            $preghist->addAttribute("pDeliveryCnt", $vGetProfiling['DELIVERY_CNT']);
            $preghist->addAttribute("pDeliveryTyp", $vGetProfiling['DELIVERY_TYP']);
            $preghist->addAttribute("pFullTermCnt", $vGetProfiling['FULL_TERM_CNT']);
            $preghist->addAttribute("pPrematureCnt", $vGetProfiling['PREMATURE_CNT']);
            $preghist->addAttribute("pAbortionCnt", $vGetProfiling['ABORTION_CNT']);
            $preghist->addAttribute("pLivChildrenCnt", $vGetProfiling['LIV_CHILDREN_CNT']);
            $preghist->addAttribute("pWPregIndhyp", $vGetProfiling['W_PREG_INDHYP']);
            $preghist->addAttribute("pWFamPlan", $vGetProfiling['W_FAM_PLAN']);

            if (($vGetProfiling['PREG_IS_APPLICABLE'] == null || $vGetProfiling['PREG_IS_APPLICABLE'] == "") && ($vGetProfiling['PREG_CNT'] == 0) ) {
                $preghist->addAttribute("pIsApplicable", "N");
            } else {
                $preghist->addAttribute("pIsApplicable", "Y");
            }
            
            $preghist->addAttribute("pReportStatus", "U");
            $preghist->addAttribute("pDeficiencyRemarks", "");

            $pepert = $profile->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $vGetProfiling['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $vGetProfiling['DIASTOLIC']);
            $pepert->addAttribute("pHr", $vGetProfiling['HR']);
            $pepert->addAttribute("pRr", $vGetProfiling['RR']);
            $pepert->addAttribute("pTemp", $vGetProfiling['TEMPERATURE']);
            $pepert->addAttribute("pHeight", $vGetProfiling['HEIGHT']);
            $pepert->addAttribute("pWeight", $vGetProfiling['WEIGHT']);
            $pepert->addAttribute("pBMI", $vGetProfiling['BMI']);
            $pepert->addAttribute("pZScore", "");
            $pepert->addAttribute("pLeftVision", $vGetProfiling['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $vGetProfiling['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $vGetProfiling['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $vGetProfiling['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $vGetProfiling['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $vGetProfiling['WAIST']);
            $pepert->addAttribute("pHip", $vGetProfiling['HIP']);
            $pepert->addAttribute("pLimbs", $vGetProfiling['LIMBS']);
            if ($vGetProfiling['MID_UPPER_ARM'] != null) {
                $vMidUpperArmCirc = $vGetProfiling['MID_UPPER_ARM'];
            } else {
                $vMidUpperArmCirc = 0;
            }
            $pepert->addAttribute("pMidUpperArmCirc", $vMidUpperArmCirc);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", "");

            $bloodtype = $profile->addChild("BLOODTYPE");
            $bloodtype->addAttribute("pBloodType", $vGetProfiling['blood_type']);
            $bloodtype->addAttribute("pReportStatus", "U");
            $bloodtype->addAttribute("pDeficiencyRemarks", "");

            $peadmin = $profile->addChild("PEGENSURVEY");
            $peadmin->addAttribute("pGenSurveyId", $vGetProfiling['GENSURVEY_ID']);
            $peadmin->addAttribute("pGenSurveyRem", $vGetProfiling['GENSURVEY_REM']);
            $peadmin->addAttribute("pReportStatus", "U");
            $peadmin->addAttribute("pDeficiencyRemarks", "");

            $vGetProfPEMISCS = getProfPEMISC($vGetProfiling['TRANS_NO']);
            $pemiscs = $profile->addChild("PEMISCS");
            if (count($vGetProfPEMISCS) > 0) {
                foreach ($vGetProfPEMISCS as $vGetProfPEMISC) {
                    $pemisc = $pemiscs->addChild("PEMISC");
                    $pemisc->addAttribute("pSkinId", $vGetProfPEMISC['SKIN_ID']);
                    $pemisc->addAttribute("pHeentId", $vGetProfPEMISC['HEENT_ID']);
                    $pemisc->addAttribute("pChestId", $vGetProfPEMISC['CHEST_ID']);
                    $pemisc->addAttribute("pHeartId", $vGetProfPEMISC['HEART_ID']);
                    $pemisc->addAttribute("pAbdomenId", $vGetProfPEMISC['ABDOMEN_ID']);
                    $pemisc->addAttribute("pNeuroId", $vGetProfPEMISC['NEURO_ID']);
                    $pemisc->addAttribute("pRectalId", $vGetProfPEMISC['RECTAL_ID']);
                    $pemisc->addAttribute("pGuId", $vGetProfPEMISC['GU_ID']);
                    $pemisc->addAttribute("pReportStatus", "U");
                    $pemisc->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
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
            $pespecific->addAttribute("pSkinRem", $vGetProfiling['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $vGetProfiling['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $vGetProfiling['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $vGetProfiling['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $vGetProfiling['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $vGetProfiling['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $vGetProfiling['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $vGetProfiling['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", "");

            $ncdqans = $profile->addChild("NCDQANS");
            $ncdqans->addAttribute("pQid1_Yn", $vGetProfiling['QID1_YN']);
            $ncdqans->addAttribute("pQid2_Yn", $vGetProfiling['QID2_YN']);
            $ncdqans->addAttribute("pQid3_Yn", $vGetProfiling['QID3_YN']);
            $ncdqans->addAttribute("pQid4_Yn", $vGetProfiling['QID4_YN']);
            $ncdqans->addAttribute("pQid5_Ynx", $vGetProfiling['QID5_YNX']);
            $ncdqans->addAttribute("pQid6_Yn", $vGetProfiling['QID6_YN']);
            $ncdqans->addAttribute("pQid7_Yn", $vGetProfiling['QID7_YN']);
            $ncdqans->addAttribute("pQid8_Yn", $vGetProfiling['QID8_YN']);
            $ncdqans->addAttribute("pQid9_Yn", $vGetProfiling['QID9_YN']);
            $ncdqans->addAttribute("pQid10_Yn", $vGetProfiling['QID10_YN']);
            $ncdqans->addAttribute("pQid11_Yn", $vGetProfiling['QID11_YN']);
            $ncdqans->addAttribute("pQid12_Yn", $vGetProfiling['QID12_YN']);
            $ncdqans->addAttribute("pQid13_Yn", $vGetProfiling['QID13_YN']);
            $ncdqans->addAttribute("pQid14_Yn", $vGetProfiling['QID14_YN']);
            $ncdqans->addAttribute("pQid15_Yn", $vGetProfiling['QID15_YN']);
            $ncdqans->addAttribute("pQid16_Yn", $vGetProfiling['QID16_YN']);
            $ncdqans->addAttribute("pQid17_Abcde", $vGetProfiling['QID17_ABCDE']);
            $ncdqans->addAttribute("pQid18_Yn", $vGetProfiling['QID18_YN']);
            $ncdqans->addAttribute("pQid19_Yn", $vGetProfiling['QID19_YN']);
            $ncdqans->addAttribute("pQid19_Fbsmg", $vGetProfiling['QID19_FBSMG']);
            $ncdqans->addAttribute("pQid19_Fbsmmol", $vGetProfiling['QID19_FBSMMOL']);
            $ncdqans->addAttribute("pQid19_Fbsdate", $vGetProfiling['QID19_FBSDATE']);
            $ncdqans->addAttribute("pQid20_Yn", $vGetProfiling['QID20_YN']);
            $ncdqans->addAttribute("pQid20_Choleval", $vGetProfiling['QID20_CHOLEVAL']);
            $ncdqans->addAttribute("pQid20_Choledate", $vGetProfiling['QID20_CHOLEDATE']);
            $ncdqans->addAttribute("pQid21_Yn", $vGetProfiling['QID21_YN']);
            $ncdqans->addAttribute("pQid21_Ketonval", $vGetProfiling['QID21_KETONVAL']);
            $ncdqans->addAttribute("pQid21_Ketondate", $vGetProfiling['QID21_KETONDATE']);
            $ncdqans->addAttribute("pQid22_Yn", $vGetProfiling['QID22_YN']);
            $ncdqans->addAttribute("pQid22_Proteinval", $vGetProfiling['QID22_PROTEINVAL']);
            $ncdqans->addAttribute("pQid22_Proteindate", $vGetProfiling['QID22_PROTEINDATE']);
            $ncdqans->addAttribute("pQid23_Yn", $vGetProfiling['QID23_YN']);
            $ncdqans->addAttribute("pQid24_Yn", $vGetProfiling['QID24_YN']);
            $ncdqans->addAttribute("pReportStatus", "U");
            $ncdqans->addAttribute("pDeficiencyRemarks", "");



    /*CONSULTATION XML GENERATION*/
    $vGetConsultation = getIndividualConsultation($pCaseNo);

    if(count($vGetConsultation) > 0) {
        $consultations = $konsulta->addChild("SOAPS");
        foreach ($vGetConsultation as $vGetConsult) {
            $consultation = $consultations->addChild("SOAP");
            $consultation->addAttribute("pHciCaseNo", $vGetConsult['CASE_NO']);
            $consultation->addAttribute("pHciTransNo", $vGetConsult['TRANS_NO']);
            $consultation->addAttribute("pSoapDate", $vGetConsult['SOAP_DATE']);
            $consultation->addAttribute("pPatientPin", $vGetConsult['PX_PIN']);
            $consultation->addAttribute("pPatientType", $vGetConsult['PX_TYPE']);
            $consultation->addAttribute("pMemPin", $vGetConsult['MEM_PIN']);
            $consultation->addAttribute("pEffYear", $vGetConsult['EFF_YEAR']);
            $consultation->addAttribute("pATC", trim($vGetConsult['SOAP_OTP']));
            $consultation->addAttribute("pIsWalkedIn", $vGetConsult['WITH_ATC']);
            $consultation->addAttribute("pCoPay", $vGetConsult['CO_PAY']);
            $consultation->addAttribute("pTransDate", $vGetConsult['DATE_ADDED']);
            $consultation->addAttribute("pReportStatus", "U");
            $consultation->addAttribute("pDeficiencyRemarks", "");

            $subjective = $consultation->addChild("SUBJECTIVE");
            if ($vGetConsult['ILLNESS_HISTORY'] == null || $vGetConsult['ILLNESS_HISTORY'] == "") {
                $vIllnestHist = "NOT APPLICABLE";
            } else {
                $vIllnestHist = $vGetConsult['ILLNESS_HISTORY'];
            }
            $subjective->addAttribute("pIllnessHistory", $vIllnestHist);
            $subjective->addAttribute("pSignsSymptoms", $vGetConsult['SIGNS_SYMPTOMS']);
                
                /*$chiefComplaintList = explode (";", $vGetConsult['SIGNS_SYMPTOMS']);
                foreach ($chiefComplaintList as $chiefComplaint) {
                   if ($chiefComplaint == "X") {
                        if ($vGetConsult['OTHER_COMPLAINT'] == null || $vGetConsult['OTHER_COMPLAINT'] == "") {
                            $vOtherComplaintStr = "NOT APPLICABLE";
                        } else {
                            $vOtherComplaintStr = $vGetConsult['OTHER_COMPLAINT'];
                        }
                        break;
                   }
                }

                foreach ($chiefComplaintList as $chiefComplaint) {
                  if ($chiefComplaint == "38") {
                        if ($vGetConsult['PAIN_SITE'] == null || $vGetConsult['PAIN_SITE'] == "") {
                            $vPainSiteStr = "-";
                        } else {
                            $vPainSiteStr = $vGetConsult['PAIN_SITE'];
                        }
                        break;
                   }
                   
                }

                if ($vGetConsult['SIGNS_SYMPTOMS'] == "X") {
                    if ($vGetConsult['OTHER_COMPLAINT'] == null || $vGetConsult['OTHER_COMPLAINT'] == "") {
                        $vOtherComplaintStr = "NOT APPLICABLE";
                    } else {
                        $vOtherComplaintStr = $vGetConsult['OTHER_COMPLAINT'];
                    }
                }

                if ($vGetConsult['SIGNS_SYMPTOMS'] == "38") {
                    if ($vGetConsult['PAIN_SITE'] == null || $vGetConsult['PAIN_SITE'] == "") {
                        $vPainSiteStr = "-";
                    } else {
                        $vPainSiteStr = $vGetConsult['PAIN_SITE'];
                    }
                }*/

                // X is not added but with chief complaint
                if(strpos($vGetConsult['SIGNS_SYMPTOMS'], "X") !== false){
                    //echo "Found!";
                    if ($vGetConsult['OTHER_COMPLAINT'] == null || $vGetConsult['OTHER_COMPLAINT'] == "") {
                        $vOtherComplaintStr = "NOT APPLICABLE";
                    } else {
                        $vOtherComplaintStr = $vGetConsult['OTHER_COMPLAINT'];
                    }
                } else {
                    $vOtherComplaintStr = "";
                }

                // 38 is not added but with chief complaint
                if(strpos($vGetConsult['SIGNS_SYMPTOMS'], "38") !== false){
                    //echo "Found!";
                    if ($vGetConsult['PAIN_SITE'] == null || $vGetConsult['PAIN_SITE'] == "") {
                        $vPainSiteStr = "-";
                    } else {
                        $vPainSiteStr = $vGetConsult['PAIN_SITE'];
                    }
                } else {
                    $vPainSiteStr = "";
                }

            $subjective->addAttribute("pOtherComplaint", $vOtherComplaintStr);
            $subjective->addAttribute("pPainSite", $vPainSiteStr);
            $subjective->addAttribute("pReportStatus", "U");
            $subjective->addAttribute("pDeficiencyRemarks", "");

            $pepert = $consultation->addChild("PEPERT");
            $pepert->addAttribute("pSystolic", $vGetConsult['SYSTOLIC']);
            $pepert->addAttribute("pDiastolic", $vGetConsult['DIASTOLIC']);
            $pepert->addAttribute("pHr", $vGetConsult['HR']);
            $pepert->addAttribute("pRr", $vGetConsult['RR']);
            $pepert->addAttribute("pTemp", $vGetConsult['TEMPERATURE']);
            if ($vGetConsult['HEIGHT'] != null) {
                $vHeight = $vGetConsult['HEIGHT'];
            } else {
                $vHeight = 0;
            }
            $pepert->addAttribute("pHeight", $vHeight);
            $pepert->addAttribute("pWeight", $vGetConsult['WEIGHT']);
            $pepert->addAttribute("pBMI", $vGetConsult['BMI']);
            $pepert->addAttribute("pZScore", $vGetConsult['Z_SCORE']);
            $pepert->addAttribute("pLeftVision", $vGetConsult['LEFT_VISUAL_ACUITY']);
            $pepert->addAttribute("pRightVision", $vGetConsult['RIGHT_VISUAL_ACUITY']);
            $pepert->addAttribute("pLength", $vGetConsult['LENGTH']);
            $pepert->addAttribute("pHeadCirc", $vGetConsult['HEAD_CIRC']);
            $pepert->addAttribute("pSkinfoldThickness", $vGetConsult['HEAD_CIRC']);
            $pepert->addAttribute("pWaist", $vGetConsult['WAIST']);
            $pepert->addAttribute("pHip", $vGetConsult['HIP']);
            $pepert->addAttribute("pLimbs", $vGetConsult['LIMBS']);
            if ($vGetConsult['MID_UPPER_ARM'] != null) {
                $vMidUpperArmCirc = $vGetConsult['MID_UPPER_ARM'];
            } else {
                $vMidUpperArmCirc = 0;
            }
            $pepert->addAttribute("pMidUpperArmCirc", $vMidUpperArmCirc);
            $pepert->addAttribute("pReportStatus", "U");
            $pepert->addAttribute("pDeficiencyRemarks", "");

            $vGetConsultationPEMISCS = getConsultationPEMISC($vGetConsult['TRANS_NO']);
            $pemiscs = $consultation->addChild("PEMISCS");
            if (count ($vGetConsultationPEMISCS) > 0) {
                foreach ($vGetConsultationPEMISCS as $vGetConsultationPEMISC) {
                    $pemisc = $pemiscs->addChild("PEMISC");
                    $pemisc->addAttribute("pSkinId", $vGetConsultationPEMISC['SKIN_ID']);
                    $pemisc->addAttribute("pHeentId", $vGetConsultationPEMISC['HEENT_ID']);
                    $pemisc->addAttribute("pChestId", $vGetConsultationPEMISC['CHEST_ID']);
                    $pemisc->addAttribute("pHeartId", $vGetConsultationPEMISC['HEART_ID']);
                    $pemisc->addAttribute("pAbdomenId", $vGetConsultationPEMISC['ABDOMEN_ID']);
                    $pemisc->addAttribute("pNeuroId", $vGetConsultationPEMISC['NEURO_ID']);
                    $pemisc->addAttribute("pGuId", $vGetConsultationPEMISC['GU_ID']);
                    $pemisc->addAttribute("pRectalId", $vGetConsultationPEMISC['RECTAL_ID']);
                    $pemisc->addAttribute("pReportStatus", "U");
                    $pemisc->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
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
            $pespecific->addAttribute("pSkinRem", $vGetConsult['SKIN_REM']);
            $pespecific->addAttribute("pHeentRem", $vGetConsult['HEENT_REM']);
            $pespecific->addAttribute("pChestRem", $vGetConsult['CHEST_REM']);
            $pespecific->addAttribute("pHeartRem", $vGetConsult['HEART_REM']);
            $pespecific->addAttribute("pAbdomenRem", $vGetConsult['ABDOMEN_REM']);
            $pespecific->addAttribute("pNeuroRem", $vGetConsult['NEURO_REM']);
            $pespecific->addAttribute("pRectalRem", $vGetConsult['RECTAL_REM']);
            $pespecific->addAttribute("pGuRem", $vGetConsult['GU_REM']);
            $pespecific->addAttribute("pReportStatus", "U");
            $pespecific->addAttribute("pDeficiencyRemarks", "");

            $vGetConsultationIcds = getConsultationIcd($vGetConsult['TRANS_NO']);
            $icds = $consultation->addChild("ICDS");
            if (count($vGetConsultationIcds) > 0) {
                foreach ($vGetConsultationIcds as $vGetConsultationIcd) {
                    $icd = $icds->addChild("ICD");
                    if ($vGetConsultationIcd['ICD_CODE'] != null || $vGetConsultationIcd['ICD_CODE'] != "") {
                        $icd->addAttribute("pIcdCode", $vGetConsultationIcd['ICD_CODE']);
                    } else {
                        $icd->addAttribute("pIcdCode", "000");
                    }
                   
                    $icd->addAttribute("pReportStatus", "U");
                    $icd->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
                $icd = $icds->addChild("ICD");
                $icd->addAttribute("pIcdCode", "000");
                $icd->addAttribute("pReportStatus", "U");
                $icd->addAttribute("pDeficiencyRemarks", "");
            }

            $vGetConsultationDiagnostics = getConsultationDiagnostic($vGetConsult['TRANS_NO']);
            $diagnostics = $consultation->addChild("DIAGNOSTICS");
                foreach ($vGetConsultationDiagnostics as $vGetConsultationDiagnostic) {
                    $diagnostic = $diagnostics->addChild("DIAGNOSTIC");
                    $diagnostic->addAttribute("pDiagnosticId", $vGetConsultationDiagnostic['DIAGNOSTIC_ID']);
                    $diagnostic->addAttribute("pOthRemarks", $vGetConsultationDiagnostic['OTH_REMARKS']);
                    $diagnostic->addAttribute("pIsPhysicianRecommendation", $vGetConsultationDiagnostic['IS_DR_RECOMMENDED']);
                    $diagnostic->addAttribute("pPatientRemarks", $vGetConsultationDiagnostic['PX_REMARKS']);
                    $diagnostic->addAttribute("pReportStatus", "U");
                    $diagnostic->addAttribute("pDeficiencyRemarks", "");
                }


            $vGetConsultationManagements = getConsultationManagement($vGetConsult['TRANS_NO']);
            $managements = $consultation->addChild("MANAGEMENTS");
            if (count($vGetConsultationManagements) > 0) {
                foreach ($vGetConsultationManagements as $vGetConsultationManagement) {
                    $management = $managements->addChild("MANAGEMENT");
                    $management->addAttribute("pManagementId", $vGetConsultationManagement['MANAGEMENT_ID']);
                    if ($vGetConsultationManagement['MANAGEMENT_ID'] == "X") {
                        $management->addAttribute("pOthRemarks", $vGetConsultationManagement['OTH_REMARKS']);
                    } else {
                        $management->addAttribute("pOthRemarks", "");
                    } 
                    $management->addAttribute("pReportStatus", "U");
                    $management->addAttribute("pDeficiencyRemarks", "");
                }
            } else {
                $management = $managements->addChild("MANAGEMENT");
                $management->addAttribute("pManagementId", "0");
                $management->addAttribute("pOthRemarks", "");
                $management->addAttribute("pReportStatus", "U");
                $management->addAttribute("pDeficiencyRemarks", "");
            }


            if ($vGetConsult['REMARKS'] != NULL) {
                $advice = $consultation->addChild("ADVICE");
                $advice->addAttribute("pRemarks", $vGetConsult['REMARKS']);
                $advice->addAttribute("pReportStatus", "U");
                $advice->addAttribute("pDeficiencyRemarks", "");
            } else {
                $advice = $consultation->addChild("ADVICE");
                $advice->addAttribute("pRemarks", "NOT APPLICABLE");
                $advice->addAttribute("pReportStatus", "U");
                $advice->addAttribute("pDeficiencyRemarks", "");
            }

        }

    } else {
        $consultations = $konsulta->addChild("SOAPS");
        $consultation = $consultations->addChild("SOAP");
        $consultation->addAttribute("pHciCaseNo", "");
        $consultation->addAttribute("pHciTransNo", "");
        $consultation->addAttribute("pSoapDate", "");
        $consultation->addAttribute("pPatientPin", "");
        $consultation->addAttribute("pPatientType", "MM");
        $consultation->addAttribute("pMemPin", "");
        $consultation->addAttribute("pEffYear", "");
        $consultation->addAttribute("pATC", "");
        $consultation->addAttribute("pIsWalkedIn", "N");
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
        
    }

/*LABORATORY RESULTS XML GENERATION*/     
if (count($vGetConsultation) > 0) {
        /*START DIAGNOTIC EXAM RESULTS XML GENERATION*/    
    foreach ($vGetConsultation as $vCaseDiag) {

        $labresults = $konsulta->addChild("DIAGNOSTICEXAMRESULTS");
        $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
        $labresult->addAttribute("pHciCaseNo", $vCaseDiag['CASE_NO']);

        $vGetProfiling = getProfiling($vCaseDiag["CASE_NO"]);
            $vGetProfFamhists = getProfFamhist($vGetProfiling['TRANS_NO']);
                foreach ($vGetProfFamhists as $vGetProfFamhist) {
                    if ($vGetProfFamhist['MDISEASE_CODE'] == "006") {
                        $labresult->addAttribute("pHciTransNo", $vGetProfiling['TRANS_NO']);
                        break;
                    } else {
                        $labresult->addAttribute("pHciTransNo", $vCaseDiag['TRANS_NO']);
                        break;
                    }
                }
        
        $labresult->addAttribute("pPatientPin", $vCaseDiag['PX_PIN']);
        $labresult->addAttribute("pPatientType", $vCaseDiag['PX_TYPE']);
        $labresult->addAttribute("pMemPin", $vCaseDiag['MEM_PIN']);
        $labresult->addAttribute("pEffYear", $vCaseDiag['EFF_YEAR']);

        //
        // FPE: FBS / RBS
        //
        foreach ($vGetProfFamhists as $vGetProfFamhist) {
            if ($vGetProfFamhist['MDISEASE_CODE'] == "006") {
                $vGetFBSS = getDiagFBS($vGetProfFamhist['TRANS_NO']);
                foreach ($vGetFBSS as $vGetFBS) {
                    $fbss = $labresult->addChild("FBSS");
                    $fbs = $fbss->addChild("FBS");
                    $fbs->addAttribute("pReferralFacility", $vGetFBS['REFERRAL_FACILITY']);
                    $fbs->addAttribute("pLabDate", $vGetFBS['LAB_DATE']);
                    $fbs->addAttribute("pGlucoseMg", $vGetFBS['GLUCOSE_MG']);
                    $fbs->addAttribute("pGlucoseMmol", $vGetFBS['GLUCOSE_MMOL']);
                    $fbs->addAttribute("pDateAdded", $vGetFBS['DATE_ADDED']);
                    $fbs->addAttribute("pStatus", $vGetFBS['IS_APPLICABLE']);
                    $fbs->addAttribute("pDiagnosticLabFee", $vGetFBS['DIAGNOSTIC_FEE']);
                    $fbs->addAttribute("pReportStatus", "U");
                    $fbs->addAttribute("pDeficiencyRemarks", "");
                }

                $vGetRBSS = getDiagRBS($vGetProfFamhist['TRANS_NO']);
                foreach ($vGetRBSS as $vGetRBS) {
                    $rbss = $labresult->addChild("RBSS");
                    $rbs = $rbss->addChild("RBS");
                    $rbs->addAttribute("pReferralFacility", $vGetRBS['REFERRAL_FACILITY']);
                    $rbs->addAttribute("pLabDate", $vGetRBS['LAB_DATE']);
                    $rbs->addAttribute("pGlucoseMg", $vGetRBS['GLUCOSE_MG']);
                    $rbs->addAttribute("pGlucoseMmol", $vGetRBS['GLUCOSE_MMOL']);
                    $rbs->addAttribute("pDateAdded", $vGetRBS['DATE_ADDED']);
                    $rbs->addAttribute("pStatus", $vGetRBS['IS_APPLICABLE']);
                    $rbs->addAttribute("pDiagnosticLabFee", $vGetRBS['DIAGNOSTIC_FEE']);
                    $rbs->addAttribute("pReportStatus", "U");
                    $rbs->addAttribute("pDeficiencyRemarks", "");
                }
            } 
        }

        // FBS
        $vGetFBSS = getDiagFBS($vCaseDiag['TRANS_NO']);
            foreach ($vGetFBSS as $vGetFBS) {
                $fbss = $labresult->addChild("FBSS");
                $fbs = $fbss->addChild("FBS");
                $fbs->addAttribute("pReferralFacility", $vGetFBS['REFERRAL_FACILITY']);
                $fbs->addAttribute("pLabDate", $vGetFBS['LAB_DATE']);
                $fbs->addAttribute("pGlucoseMg", $vGetFBS['GLUCOSE_MG']);
                $fbs->addAttribute("pGlucoseMmol", $vGetFBS['GLUCOSE_MMOL']);
                $fbs->addAttribute("pDateAdded", $vGetFBS['DATE_ADDED']);
                $fbs->addAttribute("pStatus", $vGetFBS['IS_APPLICABLE']);
                $fbs->addAttribute("pDiagnosticLabFee", $vGetFBS['DIAGNOSTIC_FEE']);
                $fbs->addAttribute("pReportStatus", "U");
                $fbs->addAttribute("pDeficiencyRemarks", "");
            }

        // RBS
        $vGetRBSS = getDiagRBS($vCaseDiag['TRANS_NO']);
            foreach ($vGetRBSS as $vGetRBS) {
                $rbss = $labresult->addChild("RBSS");
                $rbs = $rbss->addChild("RBS");
                $rbs->addAttribute("pReferralFacility", $vGetRBS['REFERRAL_FACILITY']);
                $rbs->addAttribute("pLabDate", $vGetRBS['LAB_DATE']);
                $rbs->addAttribute("pGlucoseMg", $vGetRBS['GLUCOSE_MG']);
                $rbs->addAttribute("pGlucoseMmol", $vGetRBS['GLUCOSE_MMOL']);
                $rbs->addAttribute("pDateAdded", $vGetRBS['DATE_ADDED']);
                $rbs->addAttribute("pStatus", $vGetRBS['IS_APPLICABLE']);
                $rbs->addAttribute("pDiagnosticLabFee", $vGetRBS['DIAGNOSTIC_FEE']);
                $rbs->addAttribute("pReportStatus", "U");
                $rbs->addAttribute("pDeficiencyRemarks", "");
            }

        // CBC
        $vGetCBCS = getDiagCBC($vCaseDiag['TRANS_NO']);
            foreach ($vGetCBCS as $vGetCBC) {
                $cbcs = $labresult->addChild("CBCS");
                $cbc = $cbcs->addChild("CBC");
                $cbc->addAttribute("pReferralFacility", $vGetCBC['REFERRAL_FACILITY']);
                $cbc->addAttribute("pLabDate", $vGetCBC['LAB_DATE']);
                $cbc->addAttribute("pHematocrit", $vGetCBC['HEMATOCRIT']);
                $cbc->addAttribute("pHemoglobinG", $vGetCBC['HEMOGLOBIN_G']);
                $cbc->addAttribute("pHemoglobinMmol", $vGetCBC['HEMOGLOBIN_MMOL']);
                $cbc->addAttribute("pMhcPg", $vGetCBC['MHC_PG']);
                $cbc->addAttribute("pMhcFmol", $vGetCBC['MHC_FMOL']);
                $cbc->addAttribute("pMchcGhb", $vGetCBC['MCHC_GHB']);
                $cbc->addAttribute("pMchcMmol", $vGetCBC['MCHC_MMOL']);
                $cbc->addAttribute("pMcvUm", $vGetCBC['MCV_UM']);
                $cbc->addAttribute("pMcvFl", $vGetCBC['MCV_FL']);
                $cbc->addAttribute("pWbc1000", $vGetCBC['WBC_1000']);
                $cbc->addAttribute("pWbc10", $vGetCBC['WBC_10']);
                $cbc->addAttribute("pMyelocyte", $vGetCBC['MYELOCYTE']);
                $cbc->addAttribute("pNeutrophilsBnd", $vGetCBC['NEUTROPHILS_BND']);
                $cbc->addAttribute("pNeutrophilsSeg", $vGetCBC['NEUTROPHILS_SEG']);
                $cbc->addAttribute("pLymphocytes", $vGetCBC['LYMPHOCYTES']);
                $cbc->addAttribute("pMonocytes", $vGetCBC['MONOCYTES']);
                $cbc->addAttribute("pEosinophils", $vGetCBC['EOSINOPHILS']);
                $cbc->addAttribute("pBasophils", $vGetCBC['BASOPHILS']);
                $cbc->addAttribute("pPlatelet", $vGetCBC['PLATELET']);
                $cbc->addAttribute("pDateAdded", $vGetCBC['DATE_ADDED']);
                $cbc->addAttribute("pStatus", $vGetCBC['IS_APPLICABLE']);
                $cbc->addAttribute("pDiagnosticLabFee", $vGetCBC['DIAGNOSTIC_FEE']);
                $cbc->addAttribute("pReportStatus", "U");
                $cbc->addAttribute("pDeficiencyRemarks", "");
            }

        // Urinalysis
        $vGetDiagUrinalysis = getDiagUrinalysis($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagUrinalysis as $vGetDiagUrine) {
                $urinalysiss = $labresult->addChild("URINALYSISS");
                $urinalysis = $urinalysiss->addChild("URINALYSIS");
                $urinalysis->addAttribute("pReferralFacility", $vGetDiagUrine['REFERRAL_FACILITY']);
                $urinalysis->addAttribute("pLabDate", $vGetDiagUrine['LAB_DATE']);
                $urinalysis->addAttribute("pGravity", $vGetDiagUrine['GRAVITY']);
                $urinalysis->addAttribute("pAppearance", $vGetDiagUrine['APPEARANCE']);
                $urinalysis->addAttribute("pColor", $vGetDiagUrine['COLOR']);
                $urinalysis->addAttribute("pGlucose", $vGetDiagUrine['GLUCOSE']);
                $urinalysis->addAttribute("pProteins", $vGetDiagUrine['PROTEINS']);
                $urinalysis->addAttribute("pKetones", $vGetDiagUrine['KETONES']);
                $urinalysis->addAttribute("pPh", $vGetDiagUrine['PH']);
                $urinalysis->addAttribute("pRbCells", $vGetDiagUrine['RB_CELLS']);
                $urinalysis->addAttribute("pWbCells", $vGetDiagUrine['WB_CELLS']);
                $urinalysis->addAttribute("pBacteria", $vGetDiagUrine['BACTERIA']);
                $urinalysis->addAttribute("pCrystals", $vGetDiagUrine['CRYSTALS']);
                $urinalysis->addAttribute("pBladderCell", $vGetDiagUrine['BLADDER_CELL']);
                $urinalysis->addAttribute("pSquamousCell", $vGetDiagUrine['SQUAMOUS_CELL']);
                $urinalysis->addAttribute("pTubularCell", $vGetDiagUrine['TUBULAR_CELL']);
                $urinalysis->addAttribute("pBroadCasts", $vGetDiagUrine['BROAD_CASTS']);
                $urinalysis->addAttribute("pEpithelialCast", $vGetDiagUrine['EPITHELIAL_CAST']);
                $urinalysis->addAttribute("pGranularCast", $vGetDiagUrine['GRANULAR_CAST']);
                $urinalysis->addAttribute("pHyalineCast", $vGetDiagUrine['HYALINE_CAST']);
                $urinalysis->addAttribute("pRbcCast", $vGetDiagUrine['RBC_CAST']);
                $urinalysis->addAttribute("pWaxyCast", $vGetDiagUrine['WAXY_CAST']);
                $urinalysis->addAttribute("pWcCast", $vGetDiagUrine['WC_CAST']);
                $urinalysis->addAttribute("pAlbumin", $vGetDiagUrine['ALBUMIN']);
                $urinalysis->addAttribute("pPusCells", $vGetDiagUrine['PUS_CELLS']);
                $urinalysis->addAttribute("pDateAdded", $vGetDiagUrine['DATE_ADDED']);
                $urinalysis->addAttribute("pStatus", $vGetDiagUrine['IS_APPLICABLE']);
                $urinalysis->addAttribute("pDiagnosticLabFee", $vGetDiagUrine['DIAGNOSTIC_FEE']);
                $urinalysis->addAttribute("pReportStatus", "U");
                $urinalysis->addAttribute("pDeficiencyRemarks", "");
            }

        // Fecalysis
        $vGetDiagFecalysis = getDiagFecalysis($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagFecalysis as $vGetDiagFecal) {
                $fecalysiss = $labresult->addChild("FECALYSISS");
                $fecalysis = $fecalysiss->addChild("FECALYSIS");
                $fecalysis->addAttribute("pReferralFacility", $vGetDiagFecal['REFERRAL_FACILITY']);
                $fecalysis->addAttribute("pLabDate", $vGetDiagFecal['LAB_DATE']);
                $fecalysis->addAttribute("pColor", $vGetDiagFecal['COLOR']);
                $fecalysis->addAttribute("pConsistency", $vGetDiagFecal['CONSISTENCY']);
                $fecalysis->addAttribute("pRbc", $vGetDiagFecal['RBC']);
                $fecalysis->addAttribute("pWbc", $vGetDiagFecal['WBC']);
                $fecalysis->addAttribute("pOva", $vGetDiagFecal['OVA']);
                $fecalysis->addAttribute("pParasite", $vGetDiagFecal['PARASITE']);
                $fecalysis->addAttribute("pBlood", $vGetDiagFecal['BLOOD']);
                $fecalysis->addAttribute("pPusCells", $vGetDiagFecal['PUS_CELLS']);
                $fecalysis->addAttribute("pDateAdded", $vGetDiagFecal['DATE_ADDED']);
                $fecalysis->addAttribute("pStatus", $vGetDiagFecal['IS_APPLICABLE']);
                $fecalysis->addAttribute("pDiagnosticLabFee", $vGetDiagFecal['DIAGNOSTIC_FEE']);;
                $fecalysis->addAttribute("pReportStatus", "U");
                $fecalysis->addAttribute("pDeficiencyRemarks", "");
            }

        // ChestXray
        $vGetDiagChestXrays = getDiagChestXray($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagChestXrays as $vGetDiagChestXray) {
                $chestxrays = $labresult->addChild("CHESTXRAYS");
                $chestxray = $chestxrays->addChild("CHESTXRAY");
                $chestxray->addAttribute("pReferralFacility", $vGetDiagChestXray['REFERRAL_FACILITY']);
                $chestxray->addAttribute("pLabDate", $vGetDiagChestXray['LAB_DATE']);
                $chestxray->addAttribute("pFindings", $vGetDiagChestXray['FINDINGS']);
                $chestxray->addAttribute("pRemarksFindings", $vGetDiagChestXray['REMARKS_FINDINGS']);
                $chestxray->addAttribute("pObservation", $vGetDiagChestXray['OBSERVATION']);
                $chestxray->addAttribute("pRemarksObservation", $vGetDiagChestXray['REMARKS_OBSERVATION']);
                $chestxray->addAttribute("pDateAdded", $vGetDiagChestXray['DATE_ADDED']);
                $chestxray->addAttribute("pStatus", $vGetDiagChestXray['IS_APPLICABLE']);
                $chestxray->addAttribute("pDiagnosticLabFee", $vGetDiagChestXray['DIAGNOSTIC_FEE']);
                $chestxray->addAttribute("pReportStatus", "U");
                $chestxray->addAttribute("pDeficiencyRemarks", "");
            }

        // Sputum
        $vGetDiagSputums = getDiagSputum($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagSputums as $vGetDiagSputum) {
                $sputums = $labresult->addChild("SPUTUMS");
                $sputum = $sputums->addChild("SPUTUM");
                $sputum->addAttribute("pReferralFacility", $vGetDiagSputum['REFERRAL_FACILITY']);
                $sputum->addAttribute("pLabDate", $vGetDiagSputum['LAB_DATE']);
                $sputum->addAttribute("pDataCollection", $vGetDiagSputum['DATA_COLLECTION']);
                $sputum->addAttribute("pFindings", $vGetDiagSputum['FINDINGS']);
                $sputum->addAttribute("pRemarks", $vGetDiagSputum['REMARKS']);
                $sputum->addAttribute("pNoPlusses", $vGetDiagSputum['NO_PLUSSES']);
                $sputum->addAttribute("pDateAdded", $vGetDiagSputum['DATE_ADDED']);
                $sputum->addAttribute("pStatus", $vGetDiagSputum['IS_APPLICABLE']);
                $sputum->addAttribute("pDiagnosticLabFee", $vGetDiagSputum['DIAGNOSTIC_FEE']);
                $sputum->addAttribute("pReportStatus", "U");
                $sputum->addAttribute("pDeficiencyRemarks", "");
            }

        // Lipid Profile
        $vGetDiagLipidProfs = getDiagLipidProf($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagLipidProfs as $vGetDiagLipidProf) {
                $lipidprofs = $labresult->addChild("LIPIDPROFILES");
                $lipidprof = $lipidprofs->addChild("LIPIDPROFILE");
                $lipidprof->addAttribute("pReferralFacility", $vGetDiagLipidProf['REFERRAL_FACILITY']);
                $lipidprof->addAttribute("pLabDate", $vGetDiagLipidProf['LAB_DATE']);
                $lipidprof->addAttribute("pLdl", $vGetDiagLipidProf['LDL']);
                $lipidprof->addAttribute("pHdl", $vGetDiagLipidProf['HDL']);
                $lipidprof->addAttribute("pTotal", $vGetDiagLipidProf['TOTAL']);
                $lipidprof->addAttribute("pCholesterol", $vGetDiagLipidProf['CHOLESTEROL']);
                $lipidprof->addAttribute("pTriglycerides", $vGetDiagLipidProf['TRIGLYCERIDES']);
                $lipidprof->addAttribute("pDateAdded", $vGetDiagLipidProf['DATE_ADDED']);
                $lipidprof->addAttribute("pStatus", $vGetDiagLipidProf['IS_APPLICABLE']);
                $lipidprof->addAttribute("pDiagnosticLabFee", $vGetDiagLipidProf['DIAGNOSTIC_FEE']);
                $lipidprof->addAttribute("pReportStatus", "U");
                $lipidprof->addAttribute("pDeficiencyRemarks", "");
            }

        // ECG
        $vGetDiagECGS = getDiagECG($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagECGS as $vGetDiagECG) {                    
                $ecgs = $labresult->addChild("ECGS");
                $ecg = $ecgs->addChild("ECG");
                $ecg->addAttribute("pReferralFacility", $vGetDiagECG['REFERRAL_FACILITY']);
                $ecg->addAttribute("pLabDate", $vGetDiagECG['LAB_DATE']);
                $ecg->addAttribute("pFindings", $vGetDiagECG['FINDINGS']);
                $ecg->addAttribute("pRemarks", strtoupper($vGetDiagECG['REMARKS']));
                $ecg->addAttribute("pDateAdded", $vGetDiagECG['DATE_ADDED']);
                $ecg->addAttribute("pStatus", $vGetDiagECG['IS_APPLICABLE']);
                $ecg->addAttribute("pDiagnosticLabFee", $vGetDiagECG['DIAGNOSTIC_FEE']);
                $ecg->addAttribute("pReportStatus", "U");
                $ecg->addAttribute("pDeficiencyRemarks", "");
            }

        // OGTT
        $vGetDiagOGTTS = getDiagOGTT($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagOGTTS as $vGetDiagOGTT) {
                $ogtts = $labresult->addChild("OGTTS");
                $ogtt = $ogtts->addChild("OGTT");
                $ogtt->addAttribute("pReferralFacility", $vGetDiagOGTT['REFERRAL_FACILITY']);
                $ogtt->addAttribute("pLabDate", $vGetDiagOGTT['LAB_DATE']);
                $ogtt->addAttribute("pExamFastingMg", $vGetDiagOGTT['EXAM_FASTING_MG']);
                $ogtt->addAttribute("pExamFastingMmol", $vGetDiagOGTT['EXAM_FASTING_MMOL']);
                $ogtt->addAttribute("pExamOgttOneHrMg", $vGetDiagOGTT['EXAM_OGTT_ONE_MG']);
                $ogtt->addAttribute("pExamOgttOneHrMmol", $vGetDiagOGTT['EXAM_OGTT_ONE_MMOL']);
                $ogtt->addAttribute("pExamOgttTwoHrMg", $vGetDiagOGTT['EXAM_OGTT_TWO_MG']);
                $ogtt->addAttribute("pExamOgttTwoHrMmol", $vGetDiagOGTT['EXAM_OGTT_TWO_MMOL']);
                $ogtt->addAttribute("pDateAdded", $vGetDiagOGTT['DATE_ADDED']);
                $ogtt->addAttribute("pStatus", $vGetDiagOGTT['IS_APPLICABLE']);
                $ogtt->addAttribute("pDiagnosticLabFee", $vGetDiagOGTT['DIAGNOSTIC_FEE']);
                $ogtt->addAttribute("pReportStatus", "U");
                $ogtt->addAttribute("pDeficiencyRemarks", "");
            }

        // Pap Smear
        $vGetDiagPapSmears = getDiagPapSmear($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagPapSmears as $vGetDiagPapSmear) {
                $papss = $labresult->addChild("PAPSMEARS");
                $paps = $papss->addChild("PAPSMEAR");
                $paps->addAttribute("pReferralFacility", $vGetDiagPapSmear['REFERRAL_FACILITY']);
                $paps->addAttribute("pLabDate", $vGetDiagPapSmear['LAB_DATE']);
                $paps->addAttribute("pFindings", strtoupper($genResultPaps['FINDINGS']));
                $paps->addAttribute("pImpression", strtoupper($genResultPaps['IMPRESSION']));
                $paps->addAttribute("pDateAdded", $vGetDiagPapSmear['DATE_ADDED']);
                $paps->addAttribute("pStatus", $vGetDiagPapSmear['IS_APPLICABLE']);
                $paps->addAttribute("pDiagnosticLabFee", $vGetDiagPapSmear['DIAGNOSTIC_FEE']);
                $paps->addAttribute("pReportStatus", "U");
                $paps->addAttribute("pDeficiencyRemarks", "");
            }

        // FOBT
        $vGetDiagFOBTS = getDiagFOBT($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagFOBTS as $vGetDiagFOBT) {
                $fobts = $labresult->addChild("FOBTS");
                $fobt = $fobts->addChild("FOBT");
                $fobt->addAttribute("pReferralFacility", $vGetDiagFOBT['REFERRAL_FACILITY']);
                $fobt->addAttribute("pLabDate", $vGetDiagFOBT['LAB_DATE']);
                $fobt->addAttribute("pFindings", $vGetDiagFOBT['FINDINGS']);
                $fobt->addAttribute("pDateAdded", $vGetDiagFOBT['DATE_ADDED']);
                $fobt->addAttribute("pStatus", $vGetDiagFOBT['IS_APPLICABLE']);
                $fobt->addAttribute("pDiagnosticLabFee", $vGetDiagFOBT['DIAGNOSTIC_FEE']);
                $fobt->addAttribute("pReportStatus", "U");
                $fobt->addAttribute("pDeficiencyRemarks", "");
            }   

        // Creatinine
        $vGetDiagCreatinines = getDiagCreatinine($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagCreatinines as $vGetDiagCreatinine) {
                $creatinines = $labresult->addChild("CREATININES");
                $creatinine = $creatinines->addChild("CREATININE");
                $creatinine->addAttribute("pReferralFacility", $vGetDiagCreatinine['REFERRAL_FACILITY']);
                $creatinine->addAttribute("pLabDate", $vGetDiagCreatinine['LAB_DATE']);
                $creatinine->addAttribute("pFindings", $vGetDiagCreatinine['FINDINGS']);
                $creatinine->addAttribute("pDateAdded", $vGetDiagCreatinine['DATE_ADDED']);
                $creatinine->addAttribute("pStatus", $vGetDiagCreatinine['IS_APPLICABLE']);
                $creatinine->addAttribute("pDiagnosticLabFee", $vGetDiagCreatinine['DIAGNOSTIC_FEE']);
                $creatinine->addAttribute("pReportStatus", "U");
                $creatinine->addAttribute("pDeficiencyRemarks", "");
            }

        // PDD Test
        $vGetDiagPDDS = getDiagPDD($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagPDDS as $vGetDiagPDD) {
                $pdds = $labresult->addChild("PPDTests");
                $pdd = $pdds->addChild("PPDTest");
                $pdd->addAttribute("pReferralFacility", $vGetDiagPDD['REFERRAL_FACILITY']);
                $pdd->addAttribute("pLabDate", date('Y-m-d', strtotime($vGetDiagPDD['LAB_DATE'])));
                $pdd->addAttribute("pFindings", $vGetDiagPDD['FINDINGS']);
                $pdd->addAttribute("pDateAdded", date('Y-m-d', strtotime($vGetDiagPDD['DATE_ADDED'])));
                $pdd->addAttribute("pStatus", $vGetDiagPDD['IS_APPLICABLE']);
                $pdd->addAttribute("pDiagnosticLabFee", $vGetDiagPDD['DIAGNOSTIC_FEE']);
                $pdd->addAttribute("pReportStatus", "U");
                $pdd->addAttribute("pDeficiencyRemarks", "");
            }

        // HbA1C
        $vGetDiagHbA1cs = getDiagHbA1c($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagHbA1cs as $vGetDiagHbA1c) {
                $hba1cs = $labresult->addChild("HbA1cs");
                $hba1c = $hba1cs->addChild("HbA1c");
                $hba1c->addAttribute("pReferralFacility", $vGetDiagHbA1c['REFERRAL_FACILITY']);
                $hba1c->addAttribute("pLabDate", $vGetDiagHbA1c['LAB_DATE']);
                $hba1c->addAttribute("pFindings", $vGetDiagHbA1c['FINDINGS']);
                $hba1c->addAttribute("pDateAdded", $vGetDiagHbA1c['DATE_ADDED']);
                $hba1c->addAttribute("pStatus", $vGetDiagHbA1c['IS_APPLICABLE']);
                $hba1c->addAttribute("pDiagnosticLabFee", $vGetDiagHbA1c['DIAGNOSTIC_FEE']);
                $hba1c->addAttribute("pReportStatus", "U");
                $hba1c->addAttribute("pDeficiencyRemarks", "");
            }

        // Others
        $vGetDiagOthDiags = getDiagOthDiag($vCaseDiag['TRANS_NO']);
            foreach ($vGetDiagOthDiags as $vGetDiagOthDiag) {
                $othDiags = $labresult->addChild("OTHERDIAGEXAMS");
                $othDiag = $othDiags->addChild("OTHERDIAGEXAM");
                $othDiag->addAttribute("pReferralFacility", $vGetDiagOthDiag['REFERRAL_FACILITY']);
                $othDiag->addAttribute("pLabDate", $vGetDiagOthDiag['LAB_DATE']);
                $othDiag->addAttribute("pOthDiagExam", $vGetDiagOthDiag['OTH_DIAG_EXAM']);
                $othDiag->addAttribute("pFindings", $vGetDiagOthDiag['FINDINGS']);
                $othDiag->addAttribute("pDateAdded", $vGetDiagOthDiag['DATE_ADDED']);
                $othDiag->addAttribute("pStatus", $vGetDiagOthDiag['IS_APPLICABLE']);
                $othDiag->addAttribute("pDiagnosticLabFee", $vGetDiagOthDiag['DIAGNOSTIC_FEE']);
                $othDiag->addAttribute("pReportStatus", "U");
                $othDiag->addAttribute("pDeficiencyRemarks", "");
            }

    } /*END DIAGNOTIC EXAM RESULTS **/
} else {
    $vGetProfFamhists = getProfFamhist($vGetProfiling['TRANS_NO']);
    foreach ($vGetProfFamhists as $vGetProfFamhist) {
        if ($vGetProfFamhist['MDISEASE_CODE'] == "006") {
            $labresults = $konsulta->addChild("DIAGNOSTICEXAMRESULTS");
            $labresult = $labresults->addChild("DIAGNOSTICEXAMRESULT");
            $labresult->addAttribute("pHciCaseNo", $vGetProfiling['CASE_NO']);
            $labresult->addAttribute("pHciTransNo", $vGetProfiling['TRANS_NO']);
            $labresult->addAttribute("pPatientPin", $vGetProfiling['PX_PIN']);
            $labresult->addAttribute("pPatientType", $vCaseDiag['PX_TYPE']);
            $labresult->addAttribute("pMemPin", $vGetProfiling['MEM_PIN']);
            $labresult->addAttribute("pEffYear", $vGetProfiling['EFF_YEAR']);

            $vGetFBSS = getDiagFBS($vGetProfFamhist['TRANS_NO']);
                foreach ($vGetFBSS as $vGetFBS) {
                    $fbss = $labresult->addChild("FBSS");
                    $fbs = $fbss->addChild("FBS");
                    $fbs->addAttribute("pReferralFacility", $vGetFBS['REFERRAL_FACILITY']);
                    $fbs->addAttribute("pLabDate", $vGetFBS['LAB_DATE']);
                    $fbs->addAttribute("pGlucoseMg", $vGetFBS['GLUCOSE_MG']);
                    $fbs->addAttribute("pGlucoseMmol", $vGetFBS['GLUCOSE_MMOL']);
                    $fbs->addAttribute("pDateAdded", $vGetFBS['DATE_ADDED']);
                    $fbs->addAttribute("pStatus", $vGetFBS['IS_APPLICABLE']);
                    $fbs->addAttribute("pDiagnosticLabFee", $vGetFBS['DIAGNOSTIC_FEE']);
                    $fbs->addAttribute("pReportStatus", "U");
                    $fbs->addAttribute("pDeficiencyRemarks", "");
                }

            $vGetRBSS = getDiagRBS($vGetProfFamhist['TRANS_NO']);
                foreach ($vGetRBSS as $vGetRBS) {
                    $rbss = $labresult->addChild("RBSS");
                    $rbs = $rbss->addChild("RBS");
                    $rbs->addAttribute("pReferralFacility", $vGetRBS['REFERRAL_FACILITY']);
                    $rbs->addAttribute("pLabDate", $vGetRBS['LAB_DATE']);
                    $rbs->addAttribute("pGlucoseMg", $vGetRBS['GLUCOSE_MG']);
                    $rbs->addAttribute("pGlucoseMmol", $vGetRBS['GLUCOSE_MMOL']);
                    $rbs->addAttribute("pDateAdded", $vGetRBS['DATE_ADDED']);
                    $rbs->addAttribute("pStatus", $vGetRBS['IS_APPLICABLE']);
                    $rbs->addAttribute("pDiagnosticLabFee", $vGetRBS['DIAGNOSTIC_FEE']);
                    $rbs->addAttribute("pReportStatus", "U");
                    $rbs->addAttribute("pDeficiencyRemarks", "");
                }

            break;
        } 
    }
}
            
    /*MEDICINE XML GENERATION*/
if(count($vGetConsultation) > 0) {
    foreach ($vGetConsultation as $vCase) {
        $vGetMedicines = getMedicine($vCase["TRANS_NO"]);
        if (count($vGetMedicines) > 0) {
            $medicines = $konsulta->addChild("MEDICINES");
            foreach ($vGetMedicines as $vGetMedicine) {
                $meds = $medicines->addChild("MEDICINE");
                $meds->addAttribute("pHciCaseNo", $vGetMedicine['CASE_NO']);
                $meds->addAttribute("pHciTransNo", $vGetMedicine['TRANS_NO']);
                $meds->addAttribute("pCategory", $vGetMedicine['CATEGORY']);
                $meds->addAttribute("pDrugCode", $vGetMedicine['DRUG_CODE']);
                $meds->addAttribute("pGenericCode", $vGetMedicine['GEN_CODE']);
                $meds->addAttribute("pSaltCode", $vGetMedicine['SALT_CODE']);
                $meds->addAttribute("pStrengthCode", $vGetMedicine['STRENGTH_CODE']);
                $meds->addAttribute("pFormCode", $vGetMedicine['FORM_CODE']);
                $meds->addAttribute("pUnitCode", $vGetMedicine['UNIT_CODE']);
                $meds->addAttribute("pPackageCode", $vGetMedicine['PACKAGE_CODE']);
                $meds->addAttribute("pOtherMedicine", $vGetMedicine['GENERIC_NAME']);

                if ($vGetMedicine['GENERIC_NAME'] != null && ($vGetMedicine['DRUG_GROUPING'] == null || $vGetMedicine['DRUG_GROUPING'] == "")) {
                    $vDrugGrouping = "OTHERS";
                } else {
                    $vDrugGrouping = $vGetMedicine['DRUG_GROUPING'];
                }

                $meds->addAttribute("pOthMedDrugGrouping", $vDrugGrouping);
                $meds->addAttribute("pRoute", $vGetMedicine['ROUTE']);
            
                if ($vGetMedicine['QUANTITY'] == null ) {
                    $meds->addAttribute("pQuantity", 0);
                } else {
                    $meds->addAttribute("pQuantity", $vGetMedicine['QUANTITY']);
                }

                if ($vGetMedicine['DRUG_ACTUAL_PRICE'] == null ) {
                    $meds->addAttribute("pActualUnitPrice", 0);
                } else {
                    $meds->addAttribute("pActualUnitPrice", $vGetMedicine['DRUG_ACTUAL_PRICE']);
                }

                if ($vGetMedicine['AMT_PRICE'] == null ) {
                    $meds->addAttribute("pTotalAmtPrice", 0);
                } else {
                    $meds->addAttribute("pTotalAmtPrice", $vGetMedicine['AMT_PRICE']);
                }

                if ($vGetMedicine['INS_QUANTITY'] == null ) {
                    $meds->addAttribute("pInstructionQuantity", 0);
                } else {
                    $meds->addAttribute("pInstructionQuantity", $vGetMedicine['INS_QUANTITY']);
                }
                
                $meds->addAttribute("pInstructionStrength", $vGetMedicine['INS_STRENGTH']);
                $meds->addAttribute("pInstructionFrequency", $vGetMedicine['INS_FREQUENCY']);
                $meds->addAttribute("pPrescribingPhysician", $vGetMedicine['PRESC_PHYSICIAN']);
                $meds->addAttribute("pIsDispensed", $vGetMedicine['IS_DISPENSED']);
                
                if ($vGetMedicine['IS_DISPENSED'] == 'Y' && $vGetMedicine['DISPENSING_PERSONNEL'] == NULL) {
                    $meds->addAttribute("pDateDispensed", $vGetMedicine['DISPENSED_DATE']);
                    $meds->addAttribute("pDispensingPersonnel", $vGetMedicine['PRESC_PHYSICIAN']);
                } else if ($vGetMedicine['IS_DISPENSED'] == 'Y' && $vGetMedicine['DISPENSING_PERSONNEL'] != NULL) {
                    $meds->addAttribute("pDateDispensed", $vGetMedicine['DISPENSED_DATE']);
                    $meds->addAttribute("pDispensingPersonnel", $vGetMedicine['DISPENSING_PERSONNEL']);
                } else if ($vGetMedicine['IS_DISPENSED'] == 'N') {
                    $meds->addAttribute("pDateDispensed", "");
                    $meds->addAttribute("pDispensingPersonnel", "");
                }

                if ($vGetMedicine['IS_APPLICABLE'] == null || $vGetMedicine['IS_APPLICABLE'] = "") {
                    $vIsMedsApplicable = "N";
                } else {
                    $vIsMedsApplicable = "Y";
                }
               
                $meds->addAttribute("pIsApplicable", $vIsMedsApplicable);
                $meds->addAttribute("pDateAdded", $vGetMedicine['DATE_ADDED']);
                $meds->addAttribute("pReportStatus", "U");
                $meds->addAttribute("pDeficiencyRemarks", "");
            }
        } else {
           $medicines = $konsulta->addChild("MEDICINES"); 
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
            $meds->addAttribute("pIsApplicable", "N");
            $meds->addAttribute("pDateAdded", "");
            $meds->addAttribute("pReportStatus", "U");
            $meds->addAttribute("pDeficiencyRemarks", "");
        }
    }
} else {
    $medicines = $konsulta->addChild("MEDICINES"); 
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
    $meds->addAttribute("pIsApplicable", "N");
    $meds->addAttribute("pDateAdded", "");
    $meds->addAttribute("pReportStatus", "U");
    $meds->addAttribute("pDeficiencyRemarks", "");
}

    $dom = dom_import_simplexml($konsulta)->ownerDocument;
    $dom ->formatOutput = true;

    $xml = $dom->saveXML();
    $xmlString = str_replace("<?xml version=\"1.0\"?>\n", '', $xml);
    file_put_contents("tmp/konsulta_raw_xml_ind.xml", $xmlString);

    return $xmlString;
}

?>