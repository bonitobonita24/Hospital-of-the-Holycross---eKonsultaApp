<?php
/**
 * Created by PhpStorm.
 * User: llantoz
 * Date: 01/28/2020
 * Time: 10:41 AM
 */

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $page = 'upload';
    include('header.php');
    checkLogin();
    include 'menu.php';
    include_once 'PhilHealthEClaimsEncryptor.php';

    if($_POST['submitAssignment']){
        $key = trim($_POST['fileKey']);
        if(isset($_FILES['fileUploadAssign'])){
            $errors= array();
            $data = array();
            $file_name = $_FILES['fileUploadAssign']['name'];
            $file_size =$_FILES['fileUploadAssign']['size'];
            $file_tmp =$_FILES['fileUploadAssign']['tmp_name'];
            $file_type=$_FILES['fileUploadAssign']['type'];
            $file_ext=strtolower(end(explode('.',$file_name)));
            $fileName = explode('_', $file_name);


            $extensions= array("xml");
            if(in_array($file_ext,$extensions)=== false){
                $errors[]="extension not allowed, please choose an XML file.";
                echo "<script>alert('File not allowed, please choose an XML file.');</script>";
            }

            if($fileName[0] !== $_SESSION['pAccreNum']){
                $errors[]="file not allowed, file has invalid accreditation number.";
                echo "<script>alert('File not allowed, file has invalid accreditation number.');</script>";
            }

            if(empty($errors)==true){
                if (is_uploaded_file($_FILES['fileUploadAssign']['tmp_name'])) {
                    $fileXml= move_uploaded_file($file_tmp,"files/Uploads/".$file_name);
                    $encryptedDataAsJson = file_get_contents("files/Uploads/".$file_name);
                }

				if(!empty($encryptedDataAsJson)){
                    try{
                        $decryptor = new PhilHealthEClaimsEncryptor();
                        $decryptor->setLoggingEnabled(true);
                        $decryptedOutput = $decryptor->decryptPayloadDataToXml($encryptedDataAsJson, $key);

                        $logs = print_r($decryptor->getLogs(), true);

                        // Debug logging
                        error_log("Upload Assignment: Decryption successful. Output length: " . strlen($decryptedOutput));
                        error_log("Decryptor Logs: " . $logs);

                        /*Save as Decrypted XML File*/
                        $pFileName = "files/Downloads/Assignment_".$file_name;
                        $fileUploaded = file_put_contents($pFileName, $decryptedOutput);

                        if($fileUploaded == true){
							$_POST['uploadAssignment'] = $decryptedOutput;
                            $uploadResult = uploadMemberAssignment($_POST);
                            if($uploadResult){
                                echo "<script>alert('Assignment Successfully Uploaded!');</script>";
                            } else{
                                echo "<script>alert('Error: Assignment database upload failed.');</script>";
                            }
                        } else{
                            error_log("Failed to write decrypted file: " . $pFileName . " - file_put_contents returned: " . var_export($fileUploaded, true));
                            echo "<script>alert('Error: Assignment Unsuccessfully Uploaded. Failed to save decrypted file.');</script>";
                        }

                    } catch (Exception $e){
                        $errors[] = $e->getMessage();
                        error_log("Upload Assignment Exception: " . $e->getMessage());
                        echo "<script>alert('Error during decryption: " . addslashes($e->getMessage()) . "');</script>";

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
        <div class="col-sm-7 col-xs-8"></div>
        <div align="right" class="col-sm-5 col-xs-4"><?php echo date('F d, Y - l'); ?></div>
    </div>
</div>

<div id="content">
    <div id="content_div" align="center" style="margin: 0px 0px 20px 0px;">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">UPLOAD REGISTRATION MASTERLIST MODULE</h3>
            </div>
            <div class="panel-body">
                <form action="" name="formUploadAssign" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to submit the file?');">
                    <div class="container">
                        <?php
                        $getHCIinfo = getHciProfileInfo($pAccreNo, $pUserId);
                        $hciKey = trim($getHCIinfo['CIPHER_KEY']);
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
                                           name="fileUploadAssign"
                                           id="fileUploadAssign"
                                           style="width:250px;margin:0px 10px 0px 10px;"
                                    />
                                </td>
                                <td>
                                    <input type="submit"
                                           class="btn btn-success"
                                           value="Upload"
                                           name="submitAssignment"
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