<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 4/17/2018
 * Time: 10:59 AM
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page = 'upload';
include('header.php');
checkLogin();
include 'menu.php';
include_once 'PhilHealthEClaimsEncryptor.php';

if($_POST['submitReportResult']){
    $key = $_POST['fileKey'];
    if(isset($_FILES['fileUploadReportResult'])){
        $errors= array();
        $data = array();
        $file_name = $_FILES['fileUploadReportResult']['name'];
        $file_size =$_FILES['fileUploadReportResult']['size'];
        $file_tmp =$_FILES['fileUploadReportResult']['tmp_name'];
        $file_type=$_FILES['fileUploadReportResult']['type'];
        $file_ext=strtolower(end(explode('.',$file_name)));

        $expensions= array("xml");
        if(in_array($file_ext,$expensions)=== false){
            $errors[]="extension not allowed, please choose a XML file.";
            echo "<script>alert('File not allowed, please choose an XML file.');</script>";
        }

//        if($file_size > 2097152){
//            $errors[]='File size must be exactly 2 MB';
//            echo "<script>alert('File size must be exactly 2 MB.');</script>";
//        }

        if(empty($errors)==true){
            if (is_uploaded_file($_FILES['fileUploadReportResult']['tmp_name'])) {
                $fileXml= move_uploaded_file($file_tmp,"files/Uploads/".$file_name);
                $encryptedDataAsJson = file_get_contents("files/Uploads/".$file_name);
            }

            if(!empty($encryptedDataAsJson)){
                try{
                    $decryptor = new PhilHealthEClaimsEncryptor();
                    $decryptor->setLoggingEnabled(true);
                    $decryptedOutput = $decryptor->decryptPayloadDataToXml($encryptedDataAsJson,$key);
                    $logs = print_r($decryptor->getLogs(), true);

                    /*Save as Decrypted XML File*/
                    $pFileName = "files/Downloads/Feedback_".$file_name;
                    $fileUploaded = file_put_contents($pFileName, $decryptedOutput);

                    if($fileUploaded == true){
                        $_POST['uploadFeedbackReport'] = $decryptedOutput;
                        uploadFeedbackReport($_POST);
                    } else{
                        echo "<script>alert('Error: Report Results Unsuccessfully Uploaded. Please, check file.');</script>";
                    }
                } catch (Exception $e){
                    $errors[] = $e->getMessage();
                }
            } else{
                echo "<script>alert('File uploaded is empty! Please re-upload.');</script>";
            }
        }
    }
}
?>

    <div style="margin: 5px;">
        <div class="row">
            <div class="col-sm-7 col-xs-8"><b>UPLOAD REPORT FEEDBACK MODULE</b></div>
            <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
        </div>
    </div>

    <div id="content">
        <div id="content_div" align="center" style="margin: 0px 0px 20px 0px;">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">UPLOAD REPORT FEEDBACK MODULE</h3>
                </div>
                <div class="panel-body">
                    <form action="" name="formUploadAssign" method="POST" enctype="multipart/form-data">
                        <div class="container">
                            <?php
                            $getHCIinfo = getHciProfileInfo($pAccreNo, $pUserId);
                            $hciKey = $getHCIinfo['CIPHER_KEY'];
                            echo '<input type="hidden"
                               name="fileKey"
                               id="fileKey"
                               value="'.$hciKey.'"/>'
                            ?>
                            <table>
                                <tr>
                                    <td style="font-style: normal;font-weight: bold;">Select file to upload:</td>
                                    <td>
                                        <input type="file"
                                               class="form-control"
                                               name="fileUploadReportResult"
                                               id="fileUploadReportResult"
                                               style="width:250px;margin:0px 10px 0px 10px;"
                                        />
                                    </td>
                                    <td>
                                        <input type="submit"
                                               class="btn btn-success"
                                               value="Upload"
                                               name="submitReportResult"
                                        />
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="wait_image" align="center" style="margin: 30px 0px;">
        <img src="res/images/LoadingWait.gif" alt="Please Wait" />
    </div>

    <script>
        $(document).ready(function () {
            $('.dropdown-toggle').dropdown();
        });
    </script>
<?php
include('footer.php');
?>