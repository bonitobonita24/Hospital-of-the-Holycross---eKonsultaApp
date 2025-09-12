<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 9/4/2018
 * Time: 10:54 AM
 */

if(isset($_REQUEST["file"])){
    // Get parameters
    $filepath = "../Output/".$pAccreNo."_".date('Ymd').".xml";
    if(in_array($file, $images, true)){
        $filepath = "../images/" . $file;

        // Process download
        if(file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            flush(); // Flush system output buffer
            readfile($filepath);
            exit;
        }
    }
    else{
        echo "File does not exist.";
    }
}
?>