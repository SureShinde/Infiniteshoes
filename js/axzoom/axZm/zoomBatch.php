<?php
/**
* Plugin: jQuery AJAX-ZOOM, zoomBatch.php
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Version: 4.3.1
* Date: 2016-03-07
* Review: 2016-03-07
* URL: http://www.ajax-zoom.com
* Documentation: http://www.ajax-zoom.com/index.php?cid=docs


* This is the basic batch process file. 
* With this you can process a larger amount of images from one folder or all subdolders (Ver. 4.2.1+) at once and get visual feedback. 
* If you need a "draft" example php file without visual feedback which could run as cron job, please contact the support...
* $_SESSION (session cookies) must be activated in your browser.

* !important - you might need to adjust some settings below to suit your needs (first 100 lines):
-    $yourSecretPassWord - set this var to some password to access this file. 
-    $zoom['config']['pic'] - set this var as the base directory of your high resolution images
-    $_GET['example'] - uncomment and set $_GET['example'] depending on the example you are using
    Please note that the value of example parameter passed over the query string to AJAX-ZOOM 
    does not always correspond to the number of an example found in /examples folder of the download package. 
    To find out which "example" value is used see sourcecode of the implementation in question 
    or inspect an ajax call to "/axZm/zoomLoad.php" with Firebug or an other developer tool.
**/

// Session life time
ini_set("session.gc_maxlifetime", 65535); 

// Start session
if(!session_id()){session_start();}

// Logout
if (isset($_GET['logout'])){unset($_SESSION['axZmProtectBatch'], $_SESSION['axZmBtch']); header("Location: ".$_SERVER['PHP_SELF']); die();}

// PASSWORD. Simple (very basic) password protection for this file.
$yourSecretPassWord = mt_rand().mt_rand(); // change this password mt_rand().mt_rand();

// Check posted password
if (isset($_POST['pass']) && $_POST['pass'] == $yourSecretPassWord){$_SESSION['axZmProtectBatchTry'] = 0; $_SESSION['axZmProtectBatch'] = crypt($_POST['pass']);}
elseif ($_POST['pass']){if (!$_SESSION['axZmProtectBatchTry']){$_SESSION['axZmProtectBatchTry'] = 0;} $_SESSION['axZmProtectBatchTry'] += 1; if ($_SESSION['axZmProtectBatchTry'] > 9){echo "<html><head><title>Login</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body style='font-family: verdana; color: red;'>Failed to login</body></html>";exit;}}

// Unset all Sessions in this document. All Sessions here are stored in $_SESSION['axZmBtch']
if ( ( empty($_GET) && empty($_POST) ) OR isset($_GET['unsetBatch'])){unset ($_SESSION['axZmBtch']);}

// Display login fields
if (!isset($_SESSION['axZmProtectBatch']) || crypt($yourSecretPassWord, $_SESSION['axZmProtectBatch']) != $_SESSION['axZmProtectBatch']){
    if (!(empty($_GET) && empty($_POST))){echo "<html><head><title>Redirect</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"><script>self.location.href = '".$_SERVER['PHP_SELF']."';</script></head><body></body></html>";exit;}
    echo "<html><head><title>Login</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body style='font-family: verdana'><FORM method='POST'><table width=100% height=100%><tr><td align='center' valign='middle'><div style='width: 400px; padding: 30px 10px 30px 10px; border: #AAA 1px solid; background-color: #DBEAF9; border-radius: 5px;'><div style='width: 300px; text-align: left; font-size: 80%;'># Password</div><input type='password' name='pass' style='width: 300px; border: #AAA 1px solid;'><div style='width: 300px; text-align: right; font-size: 70%; color: #444'>You can set the pass inside this file<BR>there is no default password</div></div></td></tr></table></FORM></body></html>";    
    exit;
}

// Uncomment and set this var if needed
// $_GET['example'] = 'magento';

// Include all needed classes
$noObjectsInclude = 1;
$inludeCustomConfig = 1;
require('zoomInc.inc.php');

// Ecommerce plugins have different settings
$setPicBaseDir = true;
$arrayEcomPlugins = array('magento', 'xtc', 'oxid');
if (isset($_GET['example'])){
    if (in_array($_GET['example'], $arrayEcomPlugins)){
        $setPicBaseDir = false;
    }
}

// SET THIS VAR IF NEEDED
if ($setPicBaseDir){
    $zoom['config']['pic'] = $zoom['config']['installPath'].'/pic/'; // base directory, can be just /
    if (!is_dir($axZmH->checkSlash($zoom['config']['fpPP'].$zoom['config']['pic'],'add'))){
        $zoom['config']['pic'] = '/';
    }
}

// If the above code does not return what you need, uncomment and set $zoom['config']['pic'] below
// $zoom['config']['pic'] = '/set/your/httpdocs/start/path/pictures/folder'

// Activate imagemagick
// $zoom['config']['im'] = true; 
// $zoom['config']['pyrProg'] = 'IM';
// $zoom['config']['gPyramidProg'] = 'IM';

// Set the size of initial pictures manually
// $zoom['config']['picDim'] = "600x400";

// Override errors etc...
$zoom['config']['cTimeCompareDialog'] = false;
$zoom['config']['firstImageDialog'] = false;
$zoom['config']['galleryDialog'] = false;
$zoom['config']['pyrDialog'] = false;
$zoom['config']['gPyramidDialog'] = false;
$zoom['config']['warnings'] = false; 
$zoom['config']['errors'] = false;
$zoom['config']['visualConf'] = false;
$zoom['config']['pyrProgErrorRemove'] = true;

// Override any other parameters set in zoomConfig.inc.php
$zoom['config']['makeFirstImage'] = true; // just adding this as new one
$zoom['config']['useFullGallery'] = true; // will generate inline gallery thumbs
$zoom['config']['useGallery'] = true; // will generate gallery thumbs
$zoom['config']['gPyramid'] = false; // will generate gPyramid
$zoom['config']['pyrTiles'] = true; // will generate tiles

// DO NOT EDIT THE FOLLOWING CODE !
$zoom['config']['picDir'] =         $axZmH->checkSlash($zoom['config']['fpPP'].'/'.$zoom['config']['pic'],'add');

$zoom['config']['picX'] =             intval($axZmH->getf('x',$zoom['config']['picDim']));
$zoom['config']['picY'] =             intval($axZmH->getl('x',$zoom['config']['picDim']));
$zoom['config']['galPicX'] =         intval($axZmH->getf('x',$zoom['config']['galleryPicDim']));
$zoom['config']['galPicY'] =         intval($axZmH->getl('x',$zoom['config']['galleryPicDim']));
$zoom['config']['galFullPicX'] =     intval($axZmH->getf('x',$zoom['config']['galleryFullPicDim']));
$zoom['config']['galFullPicY'] =     intval($axZmH->getl('x',$zoom['config']['galleryFullPicDim']));
$zoom['config']['galHorPicX'] =     intval($axZmH->getf('x',$zoom['config']['galleryHorPicDim']));
$zoom['config']['galHorPicY'] =     intval($axZmH->getl('x',$zoom['config']['galleryHorPicDim']));

#######################################################################################
#################################### Batch parameters #################################
#######################################################################################

// PHP_SELF should be ok for iframe
// for includes /path/to/cms/zoomBatch.php
$zoom['batch']['selfFile'] = $_SERVER['PHP_SELF'];

// Define the start (home) directory where images are located (for dropdown option list)
$zoom['batch']['startPic'] = $zoom['config']['pic'];

// Preview image settings in the file list
$zoom['batch']['prevWidth'] = 471;
$zoom['batch']['prevHeight'] = 500;

// Ver. 4.2.11+ create /axZm/batchLog directory to store errors there
$zoom['batch']['logErrorsPath'] = dirname(__FILE__).'/batchLog';

// Enable thumbs for batch list (will be displayed to the right)
$zoom['batch']['enableBatchThumbs'] = false;

// Make many different thumbs for gallery ($zoom['config']['galleryPicDim'] will be replaced with the values specified in this array)
// $zoom['batch']['galRes'] = array(1=>'50x50', '60x60', '70x70', '80x80', '100x100', '120x120', '150x150', '180x180');

// Make many different initial pictures ($zoom['config']['picDim'] will be replaced with the values specified in this array)
// $zoom['batch']['initRes'] = array(1=>'420x280', '420x630', '480x360', '480x320', '480x480', '480x720', '600x400', '600x600', '780x520', '800x600');

// Pause between ajax requests
$zoom['batch']['pause'] = 1000; // ms (1000ms = 1sec)
$zoom['batch']['allowDelete'] = true; // boolean
$zoom['batch']['allowBatchDelete'] = true; // boolean
$zoom['batch']['allowDeleteInSubfolders'] = true; // boolean
$zoom['batch']['confirmDelete'] = true; // boolean
$zoom['batch']['confirmBatch'] = true; // boolean
$zoom['batch']['stopOnError'] = false; // boolean
$zoom['batch']['showHowTo'] = true; // boolean
$zoom['batch']['showSettings'] = true;  // boolean
$zoom['batch']['deleteOnTheFlyThumbs'] = true; //boolean

// Do not change
if ($zoom['config']['stepPicDim'] && is_array($zoom['config']['stepPicDim']) && !empty($zoom['config']['stepPicDim'])){
    $zoom['config']['stepPicDim'][0] = array('w'=>$zoom['config']['picX'], 'h'=>$zoom['config']['picY'], $zoom['config']['initPicQual']);
    ksort($zoom['config']['stepPicDim']);
}else{
    $zoom['config']['stepPicDim'] = array();
}

$zoom['batch']['arrayMake']['In'] = $zoom['config']['makeFirstImage'] ? true : false;
$zoom['batch']['arrayMake']['Th'] = ( ($zoom['config']['useGallery'] OR $zoom['config']['useFullGallery']) ? true : false );
//$zoom['batch']['arrayMake']['gP'] = $zoom['config']['gPyramid'] ? true : false;
$zoom['batch']['arrayMake']['Ti'] = $zoom['config']['pyrTiles'] ? true : false;

// Language vars
$zoom['batch']['arrayMakeName']['In'] = 'Initial Image';
$zoom['batch']['arrayMakeName']['Th'] = 'Thumbs';
//$zoom['batch']['arrayMakeName']['gP'] = 'gPyramid';
$zoom['batch']['arrayMakeName']['Ti'] = 'Tiles';

// Icons
$zoom['batch']['iconNames']['Ok']='batch_ok.png';
$zoom['batch']['iconNames']['Error']='batch_error.png';
$zoom['batch']['iconNames']['n']='batch_n.png';
$zoom['batch']['iconNames']['Trash']='batch_trash.png';
$zoom['batch']['iconNames']['Smile']='batch_smile.png';
$zoom['batch']['iconNames']['Warning']='batch_alert.png';
$zoom['batch']['iconNames']['Info']='batch_info.png';

$zoom['batch']['iconOk'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['Ok'].'" width="16" height="16" border="0" alt="">';
$zoom['batch']['iconError'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['Error'].'" width="16" height="16" border="0" alt="">';
$zoom['batch']['iconN'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['n'].'" width="16" height="16" border="0" alt="">';

$zoom['batch']['iconTrash'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['Trash'].'" style="cursor: pointer" width="16" height="16" border="0" title="Delete">';
$zoom['batch']['iconSmile'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['Smile'].'" width="32" height="32" border="0" alt="" align="left" style="margin: 0px 5px 0px 0px">';
$zoom['batch']['iconWarning'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['Warning'].'" width="32" height="32" border="0" alt="" align="left" style="margin: 0px 5px 0px 0px">';
$zoom['batch']['iconInfo'] = '<img src="'.$zoom['config']['icon'].$zoom['batch']['iconNames']['Info'].'" width="32" height="32" border="0" alt="" align="left" style="margin: 0px 5px 0px 0px">';

// End Batch parameters

// Generate a dropdown list with all subdirectories
if (!isset($_SESSION['axZmBtch']['dirTreeArray'])){
    $startTime = microtime(true);
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['pyrTilesPath'],'remove'));
    $exclude[] = 'zoomtiles';
    $exclude[] = 'zoomtiles_80';
    $exclude[] = 'cropJSON';
    $exclude[] = 'hotspotJS';
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['gPyramidPath'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['thumbs'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['temp'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['gallery'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['icon'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['js'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['tempCache'],'remove'));
    $exclude[] = $axZmH->getl('/', $axZmH->checkSlash($zoom['config']['mapPath'],'remove'));
    
    $_SESSION['axZmBtch']['dirTreeArray'] = $axZmH->getDirTree($zoom['batch']['startPic'], $zoom['config']['fpPP'], $exclude);
    $totalTime = sprintf('%.2f', (microtime(true) - $startTime));
}

// Change direcory and $zoom['config']['pic'], $zoom['config']['picDir'] respectively.
if (isset($_SESSION['axZmBtch']['currentDir']) AND !isset($_GET['dir'])){
    $_GET['dir'] = $_SESSION['axZmBtch']['currentDir'];
}

// Change and save to session $zoom['config']['pic'], $zoom['config']['picDir']
if (isset($_GET['dir']) && isset($_SESSION['axZmBtch']['dirTreeArray'])){
    if (!empty($_SESSION['axZmBtch']['dirTreeArray'])){
        if (is_array($_SESSION['axZmBtch']['dirTreeArray'][$_GET['dir']])){        
            $zoom['config']['pic'] = $axZmH->checkSlash($_SESSION['axZmBtch']['dirTreeArray'][$_GET['dir']]['DIR_PATH'], 'remove');
            $zoom['config']['picDir'] = $axZmH->checkSlash($zoom['config']['fpPP'].$zoom['config']['pic'],'add');
            
            $_SESSION['axZmBtch']['pic'] = $zoom['config']['pic'];
            $_SESSION['axZmBtch']['picDir'] = $zoom['config']['picDir'];
            $_SESSION['axZmBtch']['currentDir'] = $_GET['dir'];
        }
    }
}elseif (isset($_GET['dir']) AND !isset($_SESSION['axZmBtch']['dirTreeArray'])){
    unset ($_GET['dir']);
}

if (!isset($_SESSION['axZmBtch']['currentDir'])){
    $_SESSION['axZmBtch']['currentDir'] = 'HOME';
};

// Preview images are created on the fly
if (isset($_GET['previewPic'])){
    ob_start();
        $axZm->rawThumb(
            $zoom, 
            array(
                'picDir' => $zoom['config']['picDir'],
                'imgName' => $_GET['previewPic'],
                'prevWidth' => intval($zoom['batch']['prevWidth']),
                'prevHeight' => intval($zoom['batch']['prevHeight']),
                'qual' => 100,
                'cache' => false,
                'download' => false
            )
        );
    ob_end_flush();
    exit;
}


function batchProcess($zoom){
    $return = '';
    $return .= "<table id=\"processTable\" class=\"batchProcessTable\" cellspacing=\"0\" cellpadding=\"1\">";
        $return .= "<thead><tr>";
            $return .= "<th>Filename</th>";
            $return .= "<th width=\"20px\">In</th>";
            $return .= "<th width=\"20px\">Th</th>";
            //$return .= "<th width=\"20px\">gP</th>";
            $return .= "<th width=\"20px\">Ti</th>";
            $return .= "<th width=\"45px\">Time</th>";
            if (isset($zoom['batch']['batchThumbs'])){
                $return .= "<th style=\"width: ".($zoom['config'][$zoom['batch']['batchThumbs'].'X']+3)."px;\">&nbsp;</th>";
            }
        $return .= "</tr></thead>";
        $return .= "<tbody></tbody>";
    $return .= "</table>";
    
    return $return;
}

// zoomObjects.inc.php is not included
// Define the $pic_list_array as we need it here
// The standard setting will allow to to browse through all directories
if (!isset($_SESSION['axZmBtch']['batchJob']) || empty($_SESSION['axZmBtch']['batchJob'])){
    // write here how you want to retrieve your $pic_list_array
    $pic_list_array = array();
    $handle = opendir($zoom['config']['picDir']);
    while ($file = readdir ($handle)) {
        //if ( strtolower( $axZmH->getl('.',$file) ) == 'jpg' ){
        if ( $axZmH->isValidFileType($file) ){
            array_push($pic_list_array, $file);
        }
    }
    closedir($handle);
    $pic_list_array = $axZmH->natIndex($pic_list_array);
    
    $pic_list_temp_array = array(); 
    $pic_list_data = array();
    $n=0;
    foreach ($pic_list_array as $k=>$v){
        $n++;
        $pic_list_temp_array[$n] = $pic_list_array[$k];
        $pic_list_data[$n]['imgSize'] = getimagesize($zoom['config']['picDir'].$pic_list_array[$k]);
        $pic_list_data[$n]['fileSize'] = filesize($zoom['config']['picDir'].$pic_list_array[$k]);
    }
    $pic_list_array = $pic_list_temp_array;
    $_SESSION['axZmBtch']['pclstdt'] = $pic_list_data;
}

// Set options from Session if they have been set dynamically
if (isset($_SESSION['axZmBtch']['arrayMake'])){
    $zoom['batch']['arrayMake'] = $_SESSION['axZmBtch']['arrayMake'];
    if ($zoom['batch']['arrayMake']['Th'] AND !($zoom['config']['useFullGallery'] || $zoom['config']['useGallery'])){
        $zoom['config']['useFullGallery'] = true;
        $zoom['config']['useGallery'] = true;
    }
}


// Ajax change options
if ($_GET['switchBatch']){
    $retScript = '';
    $a = explode('_',$_GET['switchBatch']);
    if (array_key_exists($a[0],$zoom['batch']['arrayMake'])){
        $zoom['batch']['arrayMake'][$a[0]] = (($a[1] == 'on') ? true : false);
        $_SESSION['axZmBtch']['arrayMake'] = $zoom['batch']['arrayMake'];    
        
        if ($zoom['batch']['arrayMake']['Th'] && !$zoom['config']['useFullGallery'] && !$zoom['config']['useGallery']){
            $zoom['config']['useFullGallery'] = true;
            $zoom['config']['useGallery'] = true;
        }
        
        if ($zoom['batch']['arrayMake']['Th'] && $zoom['batch']['enableBatchThumbs']){
            if ($zoom['config']['useFullGallery']){
                $zoom['batch']['batchThumbs'] = 'galFullPic';
            }
            
            elseif ($zoom['config']['useGallery']){
                $zoom['batch']['batchThumbs'] = 'galPic';
            }
        }

        $_SESSION['axZmBtch']['arrayMake'] = $zoom['batch']['arrayMake'];        
        $retScript .= "<script type=\"text/javascript\">";
        $retScript .= "
            $('#batchList').html('".$axZmH->batchList($zoom, $pic_list_array, $pic_list_data)."');
            $('#processTable').remove();
            $('#batchProcess').prepend('".batchProcess($zoom)."');
            $.zoomBatch.trOver();
        ";
        $retScript .= "</script>";
        echo $retScript;
    }
    exit;
}

// Choose thumb size if thumbs should be shown in the batch process list
if ($zoom['batch']['enableBatchThumbs']){
    if ($zoom['config']['useFullGallery']){
        $zoom['batch']['batchThumbs'] = 'galFullPic';
    }elseif($zoom['config']['useGallery']){
        $zoom['batch']['batchThumbs'] = 'galPic';
    }
}

// Ajax change directory
if ($_GET['dir'] AND $_GET['dirReplace']){
    $_SESSION['axZmBtch']['currentDir'] = $_GET['dir'];
    $retScript = "<script type=\"text/javascript\">";
    $retScript .= "
        $('#batchList').html('".$axZmH->batchList($zoom, $pic_list_array, $pic_list_data)."');
        $.zoomBatch.trOver();
        $.zoomBatch.currentDir = '".$_GET['dir']."';
    ";
    $retScript .= "</script>";
    echo $retScript;
    exit;
}

// Ajax Delete all thumbs, initial image(s) etc
// The original file will not be deleted, unless you set the last parameter of the method removeAxZm to true!
if (isset($_GET['delPic']) AND $zoom['batch']['allowDelete']){
    if (!empty($pic_list_array)){
        if ($pic_list_array[(int)$_GET['delPic']]){
            $arrDel = $zoom['batch']['arrayMake'];
            if (isset($arrDel['In']) && $arrDel['In'] == 1){
                $arrDel['mO'] = 1;
            }
            
            if ($zoom['batch']['deleteOnTheFlyThumbs'] && isset($arrDel['Th']) && $arrDel['Th'] == 1){
                $arrDel['tC'] = 1;
            }
            
            $axZmH -> removeAxZm($zoom, $pic_list_array[$_GET['delPic']], $arrDel, false);
            $retScript = "<script type=\"text/javascript\">";
            $retScript .= $zoom['batch']['arrayMake']['In'] ? "$('#In".$_GET['delPic']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
            $retScript .= $zoom['batch']['arrayMake']['Th'] ? "$('#Th".$_GET['delPic']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
            //$retScript .= $zoom['batch']['arrayMake']['gP'] ? "$('#gP".$_GET['delPic']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
            $retScript .= $zoom['batch']['arrayMake']['Ti'] ? "$('#Ti".$_GET['delPic']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
            $retScript .= "</script>";
            echo $retScript;
        }
    }
    exit;
}

// Ajax Delete all thumbs, initial image(s) etc for selected files
if (isset($_GET['submitDelete']) AND $zoom['batch']['allowBatchDelete']){
    $startTime = microtime(true);
    $del_array = array();
    $toDeleteImages = array();
    $retScript = "<script type=\"text/javascript\">";
    if (!empty($pic_list_array)){
        // list ist posted
        
        foreach ($pic_list_array as $k=>$v){
            if (isset($_POST['f'.$k])){
                $del_array[$k] = $v;
            }
        }
        
        if (!empty($del_array)){
            $retScript = "<script type=\"text/javascript\">";
            foreach ($del_array as $k=>$v){
            
                $arrDel = $zoom['batch']['arrayMake'];
                if (isset($arrDel['In']) && $arrDel['In'] == 1){
                    $arrDel['mO'] = 1;
                }
                
                if ($zoom['batch']['deleteOnTheFlyThumbs'] && isset($arrDel['Th']) && $arrDel['Th'] == 1){
                    $arrDel['tC'] = 1;
                }
                
                $axZmH -> removeAxZm($zoom, $v, $arrDel, false);
                $retScript .= $zoom['batch']['arrayMake']['In'] ? "$('#In".$k."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
                $retScript .= $zoom['batch']['arrayMake']['Th'] ? "$('#Th".$k."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
                //$retScript .= $zoom['batch']['arrayMake']['gP'] ? "$('#gP".$k."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';
                $retScript .= $zoom['batch']['arrayMake']['Ti'] ? "$('#Ti".$k."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');" : '';                
            }
        }
    }
    
    // Delete all in folders
    if ($zoom['batch']['allowDeleteInSubfolders'] === true && $_POST['folders'] && is_array($_POST['folders']) && !empty($_POST['folders'])){
        // Identify all subfolders
        $foldersArray = array();
        foreach($_POST['folders'] as $k=>$v){
            $filtered = array($v);
            foreach ($_SESSION['axZmBtch']['dirTreeArray'] as $a=>$b){
                if (strpos($b['DIR_KEY'], $v.'_') === 0){
                    array_push($filtered, $a);
                }
            }
            $foldersArray = array_merge($foldersArray, $filtered);
        }
        
        // Select all images in these folders
        // We only need imge names as reference to delete cache
        
        foreach ($foldersArray as $k=>$v){
            $folderInfo = $_SESSION['axZmBtch']['dirTreeArray'][$v];
            if ($folderInfo['DIR_PATH']){
                $folderToOpen = $axZmH->checkSlash($zoom['config']['fpPP'].'/'.$folderInfo['DIR_PATH'], 'add');
                foreach (glob($folderToOpen.'*.*') as $file){ // do not use GLOB_BRACE
                    $fileName = $axZmH->getl('/', str_replace('\\', '/', $file));
                    if ($axZmH->isValidFileType($fileName)){
                        array_push($toDeleteImages, $fileName);
                    }
                }
            }
        }
        
        // Trigger delete
        if (!empty($toDeleteImages)){
            foreach ($toDeleteImages as $k=>$v){
                $axZmH -> removeAxZm($zoom, $v, $arrDel = $zoom['batch']['arrayMake'], false);
            }
        }
    }
    
    $numDeleted = count($del_array) + count($toDeleteImages);
    
    $retScript .= "$('.processMsg').remove(); $('.batchInfo').css('display', 'none'); ";
    $retScript .= "$('#batchProcess').append('<div class=\"processMsg\" id=\"processMsgNotice\">".$zoom['batch']['iconInfo']." Batch delete for $numDeleted images completed in ".(round( (microtime(true) - $startTime), 4))." seconds.</div>');";
    $retScript .= "$('#passFiles input').attr({disabled: false});";
    $retScript .= "$('#leftFrameFoot input').attr({disabled: false});";
    $retScript .= "</script>";
    echo $retScript;
    exit;
}

// This is for ajax batch process
if ((isset($_GET['zoomID']) AND !empty($_SESSION['axZmBtch']['batchJob'])) OR isset($_GET['submitList']) ){

    // Submited List of images evaluation
    if (isset($_GET['submitList'])){ 
        $_SESSION['axZmBtch']['batchJobN'] = 0; // reset number for folder done
        if (!isset($_SESSION['axZmBtch']['batchJobNt'])){$_SESSION['axZmBtch']['batchJobNt'] = 0;}
        if (!isset($_SESSION['axZmBtch']['startTimeT'])){$_SESSION['axZmBtch']['startTimeT'] = microtime(true);}

        // Strip $pic_list_array from values, that have been unchecked
        $pic_list_temp_array = $pic_list_array;
        foreach ($pic_list_array as $k=>$v){
            if (!isset($_POST['f'.$k])){
                unset($pic_list_temp_array[$k]);
            }
        }
        
        // Pic list array for farther processing
        $pic_list_array = $pic_list_temp_array;
        
        // Posted folders
        if ($_POST['folders'] && is_array($_POST['folders']) && !empty($_POST['folders'])){
            $foldersArray = array();
            foreach($_POST['folders'] as $k=>$v){
                $filtered = array($v);
                foreach ($_SESSION['axZmBtch']['dirTreeArray'] as $a=>$b){
                    if (strpos($b['DIR_KEY'], $v.'_') === 0){
                        array_push($filtered, $a);
                    }
                }
                $foldersArray = array_merge($foldersArray, $filtered);
            }
            $_SESSION['axZmBtch']['batchFolders'] = $foldersArray;
            $_SESSION['axZmBtch']['batchFoldersIndex'] = -1;
        }
        
        // No images to process in this folder but other folders might have not been processed yet
        if (empty($pic_list_array) && !empty($_SESSION['axZmBtch']['batchFolders'])){
            // Increase index of folder processing
            $_SESSION['axZmBtch']['batchFoldersIndex']++;

            // Proceed to next folder if present
            if ($_SESSION['axZmBtch']['batchFolders'][$_SESSION['axZmBtch']['batchFoldersIndex']]){
                echo "<script type=\"text/javascript\">
                    $.zoomBatch.changeDir('".$_SESSION['axZmBtch']['batchFolders'][$_SESSION['axZmBtch']['batchFoldersIndex']]."', function(){
                        // Select all files not done
                        $.zoomBatch.smartSelect(true);
                        
                        // Submit selection
                        $.zoomBatch.batchSubmit(true);
                    });
                </script>";
                exit;
            }else{
                // done
                $_GET['showResults'] = true;
            }
        }
        
        // We have images to process
        elseif (!empty($pic_list_array)){
            // Save $pic_list_array to session in order not read them every time
            $_SESSION['axZmBtch']['batchJob'] = $pic_list_array; // jobs in current folder
            $_SESSION['axZmBtch']['batchJobCount'] = count($pic_list_array); // nuber of images to process in that folder
            $_SESSION['axZmBtch']['startTime'] = microtime(true); // start time for that folder

            reset($_SESSION['axZmBtch']['batchJob']);
            
            // Init first image for further processing
            $_GET['zoomID'] = key ($_SESSION['axZmBtch']['batchJob']);
            
            // Trigger first file generation
            echo "<script type=\"text/javascript\">
                $('#passFiles input').attr({disabled: true});
                $('#leftFrameFoot input').attr({disabled: true});
                $('.processMsg').remove();
                $('.batchInfo').css('display', 'none');
                $('table.batchProcessTable thead').css('display', 'table-header-group');    
                $('table.batchProcessTable tbody tr').remove();
                $('#batchProcess').append('<div class=\"processMsg\" style=\"height:20px; border: none; background-color: transparent; background-image: none;\"></div>');
                $('#processTable > tbody').append('<tr id=\"rowWait_".$_GET['zoomID']."\" style=\"background-color: #FFF7B1\"><td colspan=\"".(5+(isset($zoom['batch']['batchThumbs']) ? 0 : -1))."\" valign=\"middle\">Processing <strong>".$_SESSION['axZmBtch']['batchJob'][$_GET['zoomID']]."</strong>, please wait...</td><td align=\"right\"><img src=\"".$zoom['config']['icon']."batch_process_loader.gif\" width=\"16\" height=\"16\"></td></tr>');
                $.ajax({
                    url: '".$zoom['batch']['selfFile']."?zoomID=".$_GET['zoomID']."',
                    timeout: 360000,
                    cache: false,
                    success: function (data){
                        $('#batchOpr').html(data);
                    }
                });
            </script>";
            exit;
        }/*
        // disabled because with folders this can be ok
        else{
            echo "<script type=\"text/javascript\">
                $('#passFiles input').attr({disabled: false}); 
                $('#leftFrameFoot input').attr({disabled: false}); 
                $('.processMsg').remove();
                $('#batchProcess').append('<div class=\"processMsg\" id=\"processMsgNotice\">".$zoom['batch']['iconWarning']." No images selected!</div>');
            </script>";
            exit;
        }*/
        if (!$_GET['showResults']){
            exit;
        }
    }
    
    // Iterate (with Ajax) until $_SESSION['axZmBtch']['batchJob'] (piclist_array) is empty
    if (isset($_GET['zoomID']) AND !empty($_SESSION['axZmBtch']['batchJob'])){
        settype ($_GET['zoomID'],'int');
        // Time for one image
        $startTime = microtime(true);
        $imageSlicer = $zoom['config']['imageSlicer']; if (!is_array($imageSlicer)){$imageSlicer = array();}
        $slicerPostArr = array(
            'zoomID' => $_GET['zoomID'],
            'example' => $_GET['example'],
            'pic' => $zoom['config']['pic'],
            'pic_list_data' => serialize(array($_GET['zoomID'] => $pic_list_data[$_GET['zoomID']])),
            'pic_list_array' => serialize(array($_GET['zoomID'] => $pic_list_array[$_GET['zoomID']]))
        );
        
        if ($imageSlicer['enabled'] && !empty($imageSlicer['parameters'])){
            foreach ($imageSlicer['parameters'] as $a => $b){
                if (isset($_GET[$b])){
                    $slicerPostArr[$b] = $_GET[$b];
                }
            }
        }
        
        
        // Srart counter for errors
        if (!isset($_SESSION['axZmBtch']['batchErrors'])){$_SESSION['axZmBtch']['batchErrors'] = 0;}
        if (!isset($_SESSION['axZmBtch']['batchErrorFiles'])){$_SESSION['axZmBtch']['batchErrorFiles'] =array(); $_SESSION['axZmBtch']['batchErrorFilesWithPath'] = array();}
        
        $_SESSION['axZmBtch']['batchJobN']++; // current folder
        $_SESSION['axZmBtch']['batchJobNt']++; // overall
        
        // Define some important parameters on the fly
        $zoom['config']['orgImgName'] = $_SESSION['axZmBtch']['batchJob'][$_GET['zoomID']];
        $zoom['config']['orgImgSize'] = getimagesize($zoom['config']['picDir'].$zoom['config']['orgImgName']);
        $zoom['config']['smallImgName'] = $axZmH->composeFileName($_SESSION['axZmBtch']['batchJob'][$_GET['zoomID']], $zoom['config']['picDim'], '_');
        
        // Make initial image
        if ($zoom['batch']['arrayMake']['In']){
            
            if (is_array($zoom['batch']['initRes']) AND !empty($zoom['batch']['initRes'])){
                $savePicDim = $zoom['config']['picDim'];
                
                foreach ($zoom['batch']['initRes'] as $k=>$v){
                    $zoom['config']['picDim'] = $v;
                    $zoom['config']['picX'] = intval($axZmH->getf('x',$zoom['config']['picDim']));
                    $zoom['config']['picY'] = intval($axZmH->getl('x',$zoom['config']['picDim']));
                    $zoom['config']['smallImgName'] = $axZmH->composeFileName($_SESSION['axZmBtch']['batchJob'][$_GET['zoomID']], $zoom['config']['picDim'], '_');
                    
                    if ($imageSlicer['enabled']){
                        $slicerPostArr['task'] = 'makeFirstImage';
                        $makeFirstImage = $axZmH->httpRequestQuery(
                            $imageSlicer['method'],
                            $imageSlicer['host'],
                            $imageSlicer['port'],
                            $imageSlicer['uri'],
                            $imageSlicer['timeout'],      
                            ($imageSlicer['method'] == 'GET' ? $slicerPostArr : array()),
                            ($imageSlicer['method'] == 'POST' ? $slicerPostArr : array()),         
                            $imageSlicer['headers']
                        );
                    } else {
                        $makeFirstImage = $axZm->makeFirstImage($zoom); 
                    }        
                }
                $zoom['config']['picDim'] = $savePicDim;
                $zoom['config']['picX'] = intval($axZmH->getf('x',$zoom['config']['picDim']));
                $zoom['config']['picY'] = intval($axZmH->getl('x',$zoom['config']['picDim']));
                $zoom['config']['smallImgName'] = $axZmH->composeFileName($_SESSION['axZmBtch']['batchJob'][$_GET['zoomID']], $zoom['config']['picDim'], '_');

            } else {
                if ($imageSlicer['enabled']){
                    $slicerPostArr['task'] = 'makeFirstImage';
                    $makeFirstImage = $axZmH->httpRequestQuery(
                        $imageSlicer['method'],
                        $imageSlicer['host'],
                        $imageSlicer['port'],
                        $imageSlicer['uri'],
                        $imageSlicer['timeout'],      
                        ($imageSlicer['method'] == 'GET' ? $slicerPostArr : array()),
                        ($imageSlicer['method'] == 'POST' ? $slicerPostArr : array()),         
                        $imageSlicer['headers']
                    );
                } else {
                    $makeFirstImage = $axZm->makeFirstImage($zoom); 
                }
            }
        }

        // Make thumbs
        if ($zoom['batch']['arrayMake']['Th']){

            if (is_array($zoom['batch']['galRes']) AND !empty($zoom['batch']['galRes'])){
                $saveGalleryPicDim = $zoom['config']['galleryPicDim'];
                
                $zoom['config']['useGallery'] = true;
                $zoom['config']['useFullGallery'] = false;
                $zoom['config']['useHorGallery'] = false;
                
                foreach ($zoom['batch']['galRes'] as $k=>$v){
                    $zoom['config']['galleryPicDim'] = $v;
                    $zoom['config']['galPicX'] = intval($axZmH->getf('x',$zoom['config']['galleryPicDim']));
                    $zoom['config']['galPicY'] = intval($axZmH->getl('x',$zoom['config']['galleryPicDim']));

                    // will return an array (0 => [(int) num images made], 1 => [(array) with errors])
                    $makeThumb = $axZm->makeThumb($zoom, $_SESSION['axZmBtch']['batchJob'], $_GET['zoomID']); 
                }
                $zoom['config']['galleryPicDim'] = $saveGalleryPicDim;
            }else{
                if ($imageSlicer['enabled']){
                    $slicerPostThumbsArr = array(
                        'task' => 'makeThumb',
                        'zoomID' => $_GET['zoomID'],
                        'example' => $_GET['example'],
                        'pic' => $zoom['config']['pic'],
                        'pic_list_data' => serialize($pic_list_data),
                        'pic_list_array' => serialize($pic_list_array)
                    );

                    if (!empty($imageSlicer['parameters'])){foreach ($imageSlicer['parameters'] as $a => $b){if (isset($_GET[$b])){$slicerPostThumbsArr[$b] = $_GET[$b];}}}
                    
                    $makeThumb = $axZmH->httpRequestQuery(
                        $imageSlicer['method'],
                        $imageSlicer['host'],
                        $imageSlicer['port'],
                        $imageSlicer['uri'],
                        $imageSlicer['timeout'],      
                        ($imageSlicer['method'] == 'GET' ? $slicerPostThumbsArr : array()),
                        ($imageSlicer['method'] == 'POST' ? $slicerPostThumbsArr : array()),         
                        $imageSlicer['headers']
                    );
                }else{
                    // will return an array (0 => [(int) num images made], 1 => [(array) with errors])
                    $makeThumb = $axZm->makeThumb($zoom, $_SESSION['axZmBtch']['batchJob'], $_GET['zoomID']); 
                }
            
            }
        }
        
        // Make gPyramid
        /*
        if ($zoom['batch']['arrayMake']['gP']){
            // Define image size of initial image
            if ($zoom['batch']['arrayMake']['In']){
                $zoom['config']['smallImgSize'] = getimagesize($zoom['config']['thumbDir'].$axZmH->md5Path($zoom['config']['orgImgName'], $zoom['config']['subfolderStructure']).$zoom['config']['smallImgName']);
            }else{
                $zoom['config']['smallImgSize'] =  $axZmH->virtualResize($zoom['config']['orgImgSize'],array($zoom['config']['picX'],$zoom['config']['picY']));
            }
            $gPyramidPicDir = $zoom['config']['gPyramidDir'].$axZmH->md5path($zoom['config']['orgImgName'], $zoom['config']['subfolderStructure']).$axZmH->getf('.',$zoom['config']['orgImgName']);
            if (!is_dir($gPyramidPicDir)){
                $gPyramid = $axZm->gPyramid($zoom); // will return true or false
            }else{
                $gPyramid = true;
            }
        }*/
        
        // Make tiles
        if ($zoom['batch']['arrayMake']['Ti']){
            if (!$axZmH->tileExists($zoom, $zoom['config']['orgImgName'])){
                if ($imageSlicer['enabled']){
                    $slicerPostArr['task'] = 'makeZoomTiles';
     
                    $makeZoomTiles = $axZmH->httpRequestQuery(
                        $imageSlicer['method'],
                        $imageSlicer['host'],
                        $imageSlicer['port'],
                        $imageSlicer['uri'],
                        $imageSlicer['timeout'],
                        ($imageSlicer['method'] == 'GET' ? $slicerPostArr : array()),
                        ($imageSlicer['method'] == 'POST' ? $slicerPostArr : array()),
                        $imageSlicer['headers']
                    );
                } else {
                    $makeZoomTiles = $axZm->makeZoomTiles($zoom);
                }
            }else{
                $makeZoomTiles = true;
            }
        }

        // Return ajax results with javascript into the batch table
        if (!isset($_SESSION['axZmBtch']['batchColor'])){$_SESSION['axZmBtch']['batchColor']='#FFFFFF';}
        if ($_SESSION['axZmBtch']['batchColor']=='#FFFFFF'){$_SESSION['axZmBtch']['batchColor']='#EEEEEE';}
        else {$_SESSION['axZmBtch']['batchColor']='#FFFFFF';}
        
        $error = false;
        $jsIcons = '';

        $returnRow = "<tr id=\"row_".$_GET['zoomID']."\" style=\"background-color: ".$_SESSION['axZmBtch']['batchColor'].";\">";
        
            $returnRow .= "<td style=\"text-align: left; word-wrap: break-word;\">".$zoom['config']['orgImgName']."</td>";
            
            if (!isset($makeFirstImage)){
                $returnRow .= "<td>".$zoom['batch']['iconN']."</td>";
            }
            elseif ($makeFirstImage){
                $returnRow .= "<td>".$zoom['batch']['iconOk']."</td>";
                $jsIcons = "$('#In".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Ok']."');";
            }
            else {
                $returnRow .= "<td>".$zoom['batch']['iconError']."</td>"; 
                $jsIcons = "$('#In".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');";
                $_SESSION['axZmBtch']['batchErrors']++; 
                if (!in_array($zoom['config']['orgImgName'], $_SESSION['axZmBtch']['batchErrorFiles'])){
                    array_push($_SESSION['axZmBtch']['batchErrorFiles'], $zoom['config']['orgImgName']);
                    array_push($_SESSION['axZmBtch']['batchErrorFilesWithPath'], $zoom['config']['picDir'].$zoom['config']['orgImgName']);
                }
                $error = true;
            }
            
            if (!isset($makeThumb)){
                $returnRow .= "<td>".$zoom['batch']['iconN']."</td>";
            }
            elseif (empty($makeThumb[1])){
                $returnRow .= "<td>".$zoom['batch']['iconOk']."</td>";
                $jsIcons .= "$('#Th".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Ok']."');";
            }
            else {
                $returnRow .= "<td>".$zoom['batch']['iconError']."</td>"; 
                $jsIcons .= "$('#Th".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');";
                $_SESSION['axZmBtch']['batchErrors']++; 
                if (!in_array($zoom['config']['orgImgName'], $_SESSION['axZmBtch']['batchErrorFiles'])){
                    array_push($_SESSION['axZmBtch']['batchErrorFiles'], $zoom['config']['orgImgName']);
                    array_push($_SESSION['axZmBtch']['batchErrorFilesWithPath'], $zoom['config']['picDir'].$zoom['config']['orgImgName']);
                }
                $error = true;
            }
            /*
            if (!isset($gPyramid)){
                $returnRow .= "<td>".$zoom['batch']['iconN']."</td>";
            }
            elseif ($gPyramid){
                $returnRow .= "<td>".$zoom['batch']['iconOk']."</td>";
                $jsIcons .= "$('#gP".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Ok']."');";
            }
            else {
                $returnRow .= "<td>".$zoom['batch']['iconError']."</td>"; 
                $jsIcons .= "$('#gP".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');";
                $_SESSION['axZmBtch']['batchErrors']++; 
                if (!in_array($zoom['config']['orgImgName'], $_SESSION['axZmBtch']['batchErrorFiles'])){array_push($_SESSION['axZmBtch']['batchErrorFiles'], $zoom['config']['orgImgName']);}
                $error=true;
            }
            */
            
            if (!isset($makeZoomTiles)){
                $returnRow.="<td>".$zoom['batch']['iconN']."</td>";
            }
            elseif ($makeZoomTiles){
                $returnRow.="<td>".$zoom['batch']['iconOk']."</td>";
                $jsIcons .= "$('#Ti".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Ok']."');";
            }
            else {
                $returnRow.="<td>".$zoom['batch']['iconError']."</td>"; 
                $jsIcons .= "$('#Ti".$_GET['zoomID']."').attr('src','".$zoom['config']['icon'].$zoom['batch']['iconNames']['Error']."');";
                $_SESSION['axZmBtch']['batchErrors']++; 
                if (!in_array($zoom['config']['orgImgName'], $_SESSION['axZmBtch']['batchErrorFiles'])){
                        array_push($_SESSION['axZmBtch']['batchErrorFiles'], $zoom['config']['orgImgName']);
                        array_push($_SESSION['axZmBtch']['batchErrorFilesWithPath'], $zoom['config']['picDir'].$zoom['config']['orgImgName']);
                }
                $error=true;
            }
            
            $returnRow.="<td>".sprintf('%.3f', (microtime(true) - $startTime))."</td>";
            
            if (isset($zoom['batch']['batchThumbs'])){
                if (empty($makeThumb[1])){
                    $thumbNameGal = $axZmH->getf('.',$zoom['config']['orgImgName']).'_'.$zoom['config'][$zoom['batch']['batchThumbs'].'X'].'x'.$zoom['config'][$zoom['batch']['batchThumbs'].'Y'].'.'.$axZmH->getl('.',$zoom['config']['orgImgName']);
                    $returnRow.='<td style="background-color:#484646">';
                    $returnRow.='<div style="background-image: url('.$zoom['config']['gallery'].$thumbNameGal.'); background-position: center center; background-repeat: no-repeat; height: '.($zoom['config'][$zoom['batch']['batchThumbs'].'Y']+2).'px; margin:1px;"></div>';
                    $returnRow.='</td>';
                }else{
                    $returnRow.="<td>&nbsp;</td>";
                }
            }
            
        $returnRow.="</tr>";

        $prozessCountString = 'file '.$_SESSION['axZmBtch']['batchJobN'].' of '.$_SESSION['axZmBtch']['batchJobCount'];
        if (isset($_SESSION['axZmBtch']['batchFolders']) && $_SESSION['axZmBtch']['batchFoldersIndex'] != -1){
            $prozessCountString .= ' [folder '.($_SESSION['axZmBtch']['batchFoldersIndex']+1).' from '.count($_SESSION['axZmBtch']['batchFolders']).', ';
            $prozessCountString .= 'images processed: '.$_SESSION['axZmBtch']['batchJobNt'].']';
        }
        
        // Update progressbar
        $progressBar="
            var headerW = parseInt($('#batchProcessHead').width());
            var progressBarW = Math.floor(headerW * (".$_SESSION['axZmBtch']['batchJobN']."/".$_SESSION['axZmBtch']['batchJobCount']."));
            $('#batchProgressBar').animate({width: progressBarW},{duration: 200, easing: 'linear'});
        ";
        
        echo "<script type=\"text/javascript\">
            $('#rowWait_'+".$_GET['zoomID'].").remove();
            $('#processTable > tbody').append('".$returnRow."');
            
            ".$jsIcons."
            ".$progressBar."
            
            $('#batchProcess').scrollTo('#row_".$_GET['zoomID']."',{duration: 100});
            $('#batchList').scrollTo('#d".$_GET['zoomID']."',{duration: 100});
            if ($('#counterDiv').get()){
                $('#counterDiv').html('".$prozessCountString."');
            }
            ";
            
            if ($error){
                echo "$('#row_".$_GET['zoomID']."').css('backgroundColor', '#EED4D4');";
                if ($zoom['batch']['stopOnError']){
                    //unset
                    $_SESSION['axZmBtch']['batchJob'] = array();
                    echo "$.stopedOnError = true;";
                }
            }else{
                echo "$('#f".$_GET['zoomID']."').attr({checked: false});";
            }
        
        echo "</script>";

        // Unset current made zoomID from $_SESSION['axZmBtch']['batchJob']
        unset($_SESSION['axZmBtch']['batchJob'][$_GET['zoomID']]);
        
        // Triger next zoomID via Ajax, if available
        if (!empty($_SESSION['axZmBtch']['batchJob'])){
            reset($_SESSION['axZmBtch']['batchJob']);
            $nextJobID = key ($_SESSION['axZmBtch']['batchJob']);            
            
            // Add waiting dialog
            echo "<script type=\"text/javascript\">
                $('#processTable > tbody').append('<tr id=\"rowWait_".$nextJobID."\" style=\"background-color: #FFF7B1\"><td colspan=\"".(5+(isset($zoom['batch']['batchThumbs']) ? 0 : -1))."\" valign=\"middle\">Processing <strong>".$_SESSION['axZmBtch']['batchJob'][$nextJobID]."</strong>, please wait...</td><td align=\"right\"><img src=\"".$zoom['config']['icon']."batch_process_loader.gif\" width=\"16\" height=\"16\"></td></tr>');
                setTimeout(function(){
                    $.ajax({
                        url: '".$zoom['batch']['selfFile']."?zoomID=$nextJobID',
                        timeout: 360000,
                        cache: false,
                        success: function (data){
                            $('#batchOpr').html(data);
                        },
                        complete: function () {
                        }
                    });
                }, ".$zoom['batch']['pause'].");
            </script>";
            exit;
        }else{
            $_GET['showResults'] = 1;
        }
    }
    
    if (empty($_SESSION['axZmBtch']['batchJob']) && !empty($_SESSION['axZmBtch']['batchFolders']) && !($zoom['batch']['stopOnError'] && count($_SESSION['axZmBtch']['batchErrors']) > 0)){
        $_SESSION['axZmBtch']['batchFoldersIndex']++;
        if ($_SESSION['axZmBtch']['batchFolders'][$_SESSION['axZmBtch']['batchFoldersIndex']]){
            unset($_GET['showResults']);
            echo "<script type=\"text/javascript\">
                $.zoomBatch.changeDir('".$_SESSION['axZmBtch']['batchFolders'][$_SESSION['axZmBtch']['batchFoldersIndex']]."', function(){
                    // Select all files not done
                    $.zoomBatch.smartSelect(true);
                    
                    // Submit selection
                    $.zoomBatch.batchSubmit(true);
                });
            </script>";
            exit;
        }else{
            $_GET['showResults'] = 1;
        }
    }
    
    // showResults set above, hmm
    if ( isset($_GET['showResults'])){
        // Show overall results
        $totalTime = round(microtime(true) - $_SESSION['axZmBtch']['startTimeT'], 4);
        if ($totalTime < 1){$totalTime = 1;}
        
        echo "<script type=\"text/javascript\">
            $('#passFiles input').attr({disabled: false});
            $('#leftFrameFoot input').attr({disabled: false}); 
            $('.processMsg').remove();
            var mmm = '<div class=\"processMsg\" id=\"processMsg\">';
                mmm += '<div style=\"float: right;\"><input type=\"button\" value=\"Clear\" onClick=\"$.zoomBatch.clearBatchResults();\"></div>';
                mmm += '".(($_SESSION['axZmBtch']['batchErrors'] == 0) ? $zoom['batch']['iconSmile'] : $zoom['batch']['iconWarning'])." \
                    Batch process completed \
                    ".(($_SESSION['axZmBtch']['batchErrors'] > 0) ? "<br>with ".$_SESSION['axZmBtch']['batchErrors']." errors" : '')."<br>\
                    in ".$axZmH->seconds2time($totalTime, 'string')."; images processed: ".$_SESSION['axZmBtch']['batchJobNt']."<br><br>';
        ";
        
        if ($zoom['batch']['stopOnError'] && count($_SESSION['axZmBtch']['batchErrorFiles']) > 0){
            echo"
                if ($.stopedOnError){
                    $.stopedOnError = false;
                    mmm += 'Please note, that the batch process has been stoped because of an error processing this image: ".implode(', ', $_SESSION['axZmBtch']['batchErrorFiles'])."';
                }
            ";
        } elseif (count($_SESSION['axZmBtch']['batchErrorFiles']) > 0){
            echo"
                mmm += 'Files processed with errors: ".implode(', ', $_SESSION['axZmBtch']['batchErrorFiles'])."';
            ";
        }
        
        echo "
            mmm += '</div>';
            mmm += '<div class=\"processMsg\" style=\"height:5px; border: none; background-color: transparent; background-image: none;\"></div>';
            
            $('#batchProcess').append(mmm).scrollTo('.processMsg');
            if ($('#counterDiv').get()){
                $('#counterDiv').html('finished');
                setTimeout(function(){
                    $('#batchProgressBar').animate({width: 0},{duration: 500, easing: 'linear'});
                }, 1000);
            }
        </script>";
        
        // unset all sessions about jobs...
        unset($_SESSION['axZmBtch']['batchJobCount']);
        unset($_SESSION['axZmBtch']['batchJob']);
        unset($_SESSION['axZmBtch']['batchJobN']);
        unset($_SESSION['axZmBtch']['batchJobNt']);
        unset($_SESSION['axZmBtch']['batchErrors']);
        unset($_SESSION['axZmBtch']['batchColor']);
        unset($_SESSION['axZmBtch']['startTime']);
        unset($_SESSION['axZmBtch']['startTimeT']);
        unset($_SESSION['axZmBtch']['batchFolders']);
        unset($_SESSION['axZmBtch']['batchFoldersIndex']);
        
        // Log errors
        if ($zoom['batch']['logErrorsPath'] && isset($_SESSION['axZmBtch']['batchErrorFilesWithPath']) && !empty($_SESSION['axZmBtch']['batchErrorFilesWithPath'])){
            if (is_dir($zoom['batch']['logErrorsPath']) && is_writable($zoom['batch']['logErrorsPath'])){
                $log = implode("\r\n", $_SESSION['axZmBtch']['batchErrorFilesWithPath']);
                file_put_contents($zoom['batch']['logErrorsPath'].'/log_'.date("Y.m.d.h.i.s").'.txt', $log);
            }
        }
        
        unset($_SESSION['axZmBtch']['batchErrorFiles']);
        unset($_SESSION['axZmBtch']['batchErrorFilesWithPath']);
        exit;
    }
}

// Batch conversion list filenames
// This is what you see at the beginning
else{
    echo $axZmH->setDoctype($zoom['config']['doctype']);
    echo "
    <head>
    <title>Batch Conversion Ajax-Zoom</title>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
    <meta http-equiv=\"imagetoolbar\" content=\"no\">
    ";
    
    // Javascripts
    //$exclude=array('ui.core','ui.draggable','effects.core','browser','mousewheel','jScrollPane','facebox','axZm','axZmDemo','colorpicker');
    //echo $axZmH->drawZoomJs($zoom, $min = false, $exclude); 
    echo "
        <script type=\"text/javascript\" src=\"".$zoom['config']['js']."plugins/jquery-1.7.2.min.js\"></script>\n
        <script type=\"text/javascript\" src=\"".$zoom['config']['js']."plugins/jquery.scrollTo.min.js\"></script>\n
        <script type=\"text/javascript\" src=\"".$zoom['config']['js']."plugins/demo/jquery.form.min.js\"></script>\n
        <link type=\"text/css\" rel=\"stylesheet\" href=\"".$zoom['config']['js']."plugins/jquery.ui/themes/ajax-zoom/jquery-ui.css\" />\n
        <script type=\"text/javascript\" src=\"".$zoom['config']['js']."plugins/jquery.ui/js/jquery-ui-1.8.24.custom.min.js\"></script>\n
    ";
    
    // CSS
    echo '
    <style type="text/css" media="screen"> 
        body {margin:0px; padding:0px; overflow-y: hidden; overflow-x:hidden; background-color: #FFFFFF}
        html {margin:0px; padding:0px; overflow-y: hidden; overflow-x: hidden; border: 0; font-family: Tahoma, Arial; font-size: 10pt;}
        form {padding:0px; margin:0px;}
        h3{font-size: 120%; color: #1a4a7a; margin-bottom: 5px; margin-left: 5px; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8)}
        
        checkbox {padding:0px; margin:0px}
        .checkBoxFolder {padding:0px; margin: 2px 0 0 2px;}
        .batchSelect {font-family: Tahoma, Arial; font-size: 8pt; max-width: 440px;}
        .opt_0 {background-color: #AEC6E7}
        .opt_1 {background-color: #FEFFBA}
        .opt_2 {background-color: #FFFFE2}
        .opt_3 {background-color: #FFFFF5}
        .opt_4 {background-color: #FFFFFF}
        .batchButton {font-family: Tahoma, Arial; font-size: 9pt;}
        
        .batchMainFrame {margin-top: 20px}
        .mainInnerFrame {margin: 0 auto; width: 1020px; overflow-x: hidden;}
        
        .leftFrameParent {float:left; width: 502px;}
        .rightFrameParent {float: right; width: 502px;}
        
        .leftFrameHead {width: 500px; height: 32px; float: left; font-size: 12pt; background-color: #000000;  border-left: 1px solid #454545; border-right: 1px solid #454545; color: #FFFFFF; background-image: url('.$zoom['config']['icon'].'batch_head.png); background-repeat: repeat-x;}
        .leftFrame {width:500px; height: 500px; clear: both; float: left;  overflow-y: scroll; overflow-x:hidden; border-left: 1px solid #454545; border-right: 1px solid #454545; background-color: #F9F9F9;}
        
        .leftFrameFoot {width: 490px; padding: 5px; clear: both; float: left; background-color: #C2C2C2; border: 1px solid #454545;}
        .rightFrameFoot {width: 490px; padding: 5px; clear: both; float: left; background-color: #C2C2C2; border: 1px solid #454545;}
        .leftFrameInnerHead {position: fixed;}
        .leftFrameInnerBody {margin-top: 20px;}

        table.leftFrameFolder {width: 485px;}
        table.leftFrameFolder tr:nth-child(odd) { background-color: #f3f3cd;}
        table.leftFrameFolder tr:nth-child(even) { background-color: #ffffe6;}
        
        
        table.leftFrameFolder thead tr th {background-color: #C2C2C2; background-image: url('.$zoom['config']['icon'].'tbl_header.jpg); text-align: left; vertical-align: middle; font-weight: normal; font-family: Tahoma, Arial; font-size: 9pt; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF;}
        table.leftFrameFolder tbody tr td {font-size: 9pt; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; word-wrap: break-word; -webkit-user-select: none; -moz-user-select: none; -khtml-user-select: none; -ms-user-select: none; user-select: none;}
        table.leftFrameFolder tbody tr td:nth-child(2){padding-left: 5px;}
        .oneClick td{background-color: #EEE;}
        
        table.leftFrameTable {width: 485px;}
        table.leftFrameTable tr:nth-child(odd) { background-color: #EEEEEE;}
        table.leftFrameTable tr:nth-child(even) { background-color: #FFFFFF;}
        table.leftFrameTable thead tr th {background-color: #C2C2C2; background-image: url('.$zoom['config']['icon'].'tbl_header.jpg); text-align: left; vertical-align: middle; font-weight: normal; font-family: Tahoma, Arial; font-size: 9pt; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF;}
        table.leftFrameTable tbody tr td {font-size: 8pt; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF; word-wrap: break-word;}

        .batchProcessHead {width: 500px; height: 32px; float: left; font-size: 12pt; background-color: #000000;  border-left: 1px solid #454545; border-right: 1px solid #454545; color: #FFFFFF; background-image: url('.$zoom['config']['icon'].'batch_head.png); background-repeat: repeat-x; position: relative;}
        .batchProgressBar {position: absolute; z-index: 1; width: 0px; height: 32px; background-image: url('.$zoom['config']['icon'].'batch_progressbar.gif); background-position: bottom; background-repeat: repeat-x;}
        .batchProcessText {position: absolute; z-index: 2; margin: 6px 0 0 5px; font-size: 11pt;}

        .batchProcess {width: 500px; height: 572px; clear: both; float: left; overflow-y: scroll; overflow-x:hidden; border-left: 1px solid #454545; border-right: 1px solid #454545; background-color: #F9F9F9;}
        .footerRow {border: #FFFFFF 1px solid; background-color: #E2E2E2; padding: 3px; }
        
        table.batchProcessTable {width: 484px; font-size: 8pt;}
        table.batchProcessTable thead {display: none;}
        table.batchProcessTable thead tr th {background-color: #C2C2C2; background-image: url('.$zoom['config']['icon'].'tbl_header.jpg); text-align: left; vertical-align: middle; font-weight: normal; font-family: Tahoma, Arial; font-size: 9pt; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF;}
        table.batchProcessTable tbody tr td {text-align: center; border-right: 1px solid #CCCCCC; border-bottom: 1px solid #CCCCCC; border-left: 1px solid #FFFFFF; border-top: 1px solid #FFFFFF;}

        
        .processMsg {min-height: 32px; padding:3px; margin:3px; border: 1px solid #7F7F7F; background-color: #EEEEEE; color: #000000; background-image: url('.$zoom['config']['icon'].'batch_bg.jpg);}
        
        .batchInfo {min-height: 32px; padding:3px; background-color: #FFF; color: #000000;}
        .batchInfo ul{list-style-type: circle; padding-left: 15px;}
        .batchInfo li{margin-bottom: 7px;}
        
        .imgPrevDiv1 {border: 1px #454545 solid; position: absolute; z-index: 11; background-image: url('.$zoom['config']['icon'].'batch_loader.gif); background-position: center center; background-repeat: no-repeat; background-color: #F1F1F1;}
        .imgPrevDiv {position: absolute; z-index: 10; box-shadow: 5px 5px 7px #818181; -webkit-box-shadow: 5px 5px 7px #818181; -moz-box-shadow: 5px 5px 7px #818181;}

        #infoSettings {font-size: 8pt;}
        #infoSettings tr:nth-child(odd) { background-color: #EEE;}
        #infoSettings tr:nth-child(even) { background-color: #FFF;}
        #infoSettings td {vertical-align: top; padding: 3px;}
        
        .folderIcon {margin: 0px 0px 0px 2px; cursor: pointer; width: 16px; height: 16px;}
        .breadcrumbCotainer {position: absolute; left: 50%; height: 20px; visibility: hidden;}
        #bci {line-height: 20px; color: #444; font-size: 8pt;}
        #bci span {padding: 3px 5px 3px 5px;}
        #bci a,a:active {text-decoration: none; color: #444; padding: 3px 5px 3px 5px;}
        #bci a:hover{color: #000; background-color: #EEEEEE; }
    </style>
    ';
    
    
    // Some Javascript
    echo "
    <script type=\"text/javascript\">
    
        $.fn.axZmGetPropType = function(type){
            var oldJQuery = parseFloat($.fn.jquery) < 1.6;
             
            if (oldJQuery){
                return $(this).attr(type);
            }else{
                return $(this).prop(type);
            }
        };
        
        $.zoomBatch = {};
        $.zoomBatch.currentDir = 'HOME';
        $.zoomBatch.confirmDelete = ".$axZmH->ptj($zoom['batch']['confirmDelete']).";
        $.zoomBatch.allowBatchDelete = ".$axZmH->ptj($zoom['batch']['allowBatchDelete']).";
        $.zoomBatch.confirmBatch = ".$axZmH->ptj($zoom['batch']['confirmBatch']).";
        ".$axZmH->arrayToJSObject($_SESSION['axZmBtch']['dirTreeArray'], '$.zoomBatch.dirTreeArray', false, false, false).";

        $.zoomBatch.selectAllCheckbox = function(formID){
            $('#'+formID + ' input[type=checkbox]').attr({checked: true});
        };

        $.zoomBatch.deselectAllCheckbox = function(formID){
            $('#'+formID + ' input[type=checkbox]').attr({checked: false});
        };
        
        $.zoomBatch.toggleCheckBox = function (id){
            if ( $('#'+id).axZmGetPropType('checked') ){
                $('#'+id).attr({checked: false});
            }else{
                $('#'+id).attr({checked: true});
            }
        };
        
        $.zoomBatch.smartSelect = function (noMsg){
            $.zoomBatch.deselectAllCheckbox('passFiles');
            var n = m = 0;
            $('#leftFrameTable tbody tr').each(function(){
                var rowHtml = $(this).html();
                m++;
                if (rowHtml.indexOf('".$zoom['batch']['iconNames']['Error']."') > 0){
                    var id = $(this).attr('id').split('d').join('');
                    $('#f'+id).attr({checked: true});
                    n++;
                }
            });
            
            if (noMsg){
                return true;
            }

            if (n == 0 && m != 0){
                alert ('Everything has been done already!');
                return false;
            }
            else if (m == 0){
                alert ('No images in this folder or selection!');
                return false;
            }
            else{
                return true;
            }
        }
        
        $.zoomBatch.reload  = function(){
            $.zoomBatch.changeDir($.zoomBatch.currentDir || 'HOME');
        };

        
        $.zoomBatch.bc = function(id){
            var bci = $('#bci');
            bci.empty();
            if (id != 'HOME'){
                var idParts = id.split('_');
                var arrRet = [];
                var strL = '';
                $.each(idParts, function(n, d){
                    if (!strL){strL='1'}else{strL += '_'+d;}
                    arrRet.push(strL);
                });
                
                $.each(arrRet, function(k, v){
                    var newA = $('<a />');
                    if (v == '1'){newA.html('HOME').attr('href', 'javascript: void(0)');newA.bind('click', function(){\$.zoomBatch.changeDir('HOME')})}
                    else {newA.html($.zoomBatch.dirTreeArray[v]['DIR_NAME']).attr('href', 'javascript: void(0)');newA.bind('click', function(){\$.zoomBatch.changeDir(v)})}
                    if ((k+1) != arrRet.length){
                        bci.append(newA);
                        bci.append(' &#10140; ');    
                    }else{
                        $('<span />').html(newA.html()).appendTo(bci);
                    }
                })
            } else {
                $('<span />').html('HOME').appendTo(bci);
            }
        };
        
        $.zoomBatch.changeDir = function(id, clb){
            $('#dirLoader').css('visibility','visible').fadeTo('fast',1);
            $.ajax({
                url: '".$zoom['batch']['selfFile']."?dirReplace=1&dir='+id,
                timeout: 360000,
                cache: false,
                success: function (data){
                    
                    // Replace table
                    $('#batchOpr').html(data);
                    
                    // Update select field
                    $('#batchSelect').val(id);
                    
                    $.zoomBatch.bc(id);
                    
                    if ($.isFunction(clb)){
                        setTimeout(clb, 1000);
                    }
                },
                complete: function () {
                    $('#dirLoader').fadeTo('fast',0);
                    
                }
            });
        };

        $.zoomBatch.deleteZoom = function(id){
            function delZoomConfirm(id){
                $.ajax({
                    url: '".$zoom['batch']['selfFile']."?delPic='+id,
                    timeout: 360000,
                    cache: false,
                    success: function (data){
                        $('#batchOpr').html(data);
                    },
                    complete: function () {
                    }
                });            
            }
            
            if ($.zoomBatch.confirmDelete){
                var check = confirm('Do you really want to delete all \\najax-zoom created files for this image?\\n\\nThe image itself will not be deleted!');
                if (check){
                    delZoomConfirm(id);
                }                        
            }else{
                delZoomConfirm(id);
            }
        };

        $.zoomBatch.checkSelected = function(ovr){
            var n = $('#passFiles input[type=checkbox]:checked').length;
            if (ovr){return true;}
            if (n == 0){
                alert ('No images or folders selected!');
                return false;
            }else{
                return true;
            }    
        };
        
        $.zoomBatch.checkFolderSelected = function(){
            return $('.checkBoxFolder:checked').length;
        };
        
        $.zoomBatch.batchDelete = function(){
            function delBatchConfirm(){
                $.zoomBatch.ajaxSubmitCustom('passFiles','batchOpr','".$zoom['batch']['selfFile']."?submitDelete=1');
                $('#passFiles input').attr({disabled: true});
                $('#leftFrameFoot input').attr({disabled: true});
                $('#batchProcess').append('<div class=\"processMsg\" id=\"processMsgNotice\">".$zoom['batch']['iconInfo']."Delete process started, please wait!</div>');
            }
            
            if (!$.zoomBatch.checkSelected()){
                return false;
            }
            
            if ($.zoomBatch.confirmDelete){
                var check = confirm('Do you really want to delete all \\najax-zoom created files for the selected images?\\n\\nThe source images will not be deleted!');
                if (check){
                    delBatchConfirm();
                }                        
            }else{
                delBatchConfirm();
            }
        };
        
        $.zoomBatch.clearBatchResults = function(aaa){
            $('.processMsg').remove();
            $('table.batchProcessTable tbody tr').remove();    
            $('table.batchProcessTable thead').css('display', 'none');    
            if (!aaa){
                $('.batchInfo').css('display', 'block');
            }
        };
        
        $.zoomBatch.ajaxSubmitCustom = function (formName, targetDiv, ajaxUrl){
            $('#'+formName).ajaxSubmit ({
                target: '#'+targetDiv,
                url: ajaxUrl,
                type: 'post'
            }); 
            return false; 
        };
        
        $.zoomBatch.batchSubmit = function(noCheck){
            if (!noCheck && !$.zoomBatch.checkSelected()){
                return false;
            }
            
            if (!noCheck && $.zoomBatch.confirmBatch){
                var numFolders = $('.checkBoxFolder:checked').length;
                if (numFolders > 0){
                    var check = confirm('Do you want to start batch process in '+numFolders+' folders and all the subfolders?\\nThis confirmation does not appear if you select single images');
                    if (!check){
                        return false;
                    }
                }
            }
            
            
            
            $.zoomBatch.ajaxSubmitCustom('passFiles','batchOpr','".$zoom['batch']['selfFile']."?submitList=1');
            $('#passFiles input').attr({disabled: true});
            $('#leftFrameFoot input').attr({disabled: true});
            $.zoomBatch.clearBatchResults(true);
            $('.batchInfo').css('display', 'none');
            $('#batchProcess').append('<div class=\"processMsg\" id=\"processMsgNotice\">".$zoom['batch']['iconInfo']." Batch process started, please wait!</div>');
        };
        
        $.zoomBatch.switchBatch = function(what){
            if ( $('#batchSwitch'+what).axZmGetPropType('checked') ){
                var act = 'on';
            }else{
                var act = 'off';
            }
            $('#dirLoader').css('visibility','visible').fadeTo('fast',1);
            $('#leftFrameFoot input').attr({disabled: true}); 
            $.ajax({
                url: '".$zoom['batch']['selfFile']."?switchBatch='+what+'_'+act,
                timeout: 10000,
                cache: false,
                success: function (data){
                    $('#batchOpr').html(data);
                },
                complete: function () {
                    $('#dirLoader').fadeTo('fast',0);
                    $('#leftFrameFoot input').attr({disabled: false}); 
                }
            });            
        };
        
        $.zoomBatch.adjHeights = function(time){
             if (!$.zoomBatch.adjHeightsTime){
                 $.zoomBatch.adjHeightsTime = new Date().getTime();
             }else{
                 if (new Date().getTime() - $.zoomBatch.adjHeightsTime < 100){
                     return;
                 }
             }
             
             
            var parentDiv = $('#batchMainFrame').parent();
            // Set the following to fit of a height of a certain parent div
            // var windowHeight = parseInt(parentDiv.height());
            var windowHeight = parseInt($(window).height());
            
            var headerHeight = parseInt($('#batchProcessHead').height());
            var leftFrameFootHeight = parseInt($('#leftFrameFoot').outerHeight());
            var rightFrameFootHeight = parseInt($('#rightFrameFoot').outerHeight());
            var mainFrameMargin = parseInt($('#batchMainFrame').css('margin-top'));
            
            
            $('#batchProcess').animate( {height: (windowHeight-headerHeight-(mainFrameMargin*2)-rightFrameFootHeight-2)} , {queue: false, duration: time, easing: 'swing'} );
            $('#batchList').animate( {height: (windowHeight-headerHeight-(mainFrameMargin*2)-leftFrameFootHeight-2)} , {queue: false, duration: time, easing: 'swing'});

            $('#breadcrumbCotainer').css({visibility: 'visible', width: $('.mainInnerFrame').outerWidth(), marginLeft: -($('.mainInnerFrame').outerWidth()/2)});
            
            
            
        };
        
        $.zoomBatch.trOver=function(){

            $('td:eq(1)', $('#leftFrameFolder tr')).each(function(){
                var _this = $(this);
                var _thisFolder = _this.attr('data-folder');
                if (_thisFolder){
                    _this.prev().find('img').bind('click', function(){
                        _this.trigger('dblclick');
                    });
                
                    _this.css('cursor', 'pointer')
                    .bind('dblclick', function(e){
                        $.zoomBatch.changeDir(_thisFolder);
                        _this.parent().removeClass('oneClick').css({backgroundColor: '#AAAAAA', color: '#FFFFFF'})
                        .children().css({borderColor: '#AAAAAA'});
                        e.preventDefault();
                    })
                    .bind('click', function(){
                        _this.parent().addClass('oneClick');
                        setTimeout(function(){
                            if (_this.length){
                                _this.parent().removeClass('oneClick');
                                var chbox = _this.next().find('input[type=checkbox]');
                                if (chbox.prop('checked') || chbox.attr('checked')){
                                    chbox.prop('checked', false).attr('checked',false);
                                }else{
                                    chbox.prop('checked', true).attr('checked',true);
                                }
                                
                            }
                        }, 1000)
                    });
                }
            });
            
            var trColor = {};
            
            $('#passFiles input[type=checkbox]').each(function(){
                var IDnum = $(this).attr('name').split('f').join('');
                
                if (IDnum){
                    trColor[IDnum] = {};
                    trColor[IDnum]['bg'] = $('#d'+IDnum).css('backgroundColor');
                    trColor[IDnum]['borderTopColor'] = $('#fname'+IDnum).css('borderTopColor');
                    trColor[IDnum]['borderRightColor'] = $('#fname'+IDnum).css('borderRightColor');
                    trColor[IDnum]['borderBottomColor'] = $('#fname'+IDnum).css('borderBottomColor');
                    trColor[IDnum]['borderLeftColor'] = $('#fname'+IDnum).css('borderLeftColor');
                    trColor[IDnum]['color'] = $('#fname'+IDnum).css('color');
                    
                    $('#fname'+IDnum).bind('click', function(){
                        $.zoomBatch.toggleCheckBox('f'+IDnum);
                    });
                    
                    $('#fname'+IDnum).bind('mouseover', function(){
                        $('#d'+IDnum).css({
                            backgroundColor: '#3399cc', 
                            cursor: 'pointer'
                        });
                        $('#d'+IDnum+' td').css({
                            borderColor: '#3399cc',
                            color: '#FFFFFF'
                        });
                    });
                    
                    $('#fname'+IDnum).bind('mouseout', function(){
                        $('#d'+IDnum).css({
                            backgroundColor: trColor[IDnum]['bg'], 
                            cursor: 'default'
                        });
                        $('#d'+IDnum+' td').css({
                            borderTopColor: trColor[IDnum]['borderTopColor'],
                            borderRightColor: trColor[IDnum]['borderRightColor'],
                            borderBottomColor: trColor[IDnum]['borderBottomColor'],
                            borderLeftColor: trColor[IDnum]['borderLeftColor'],
                            color: trColor[IDnum]['color']
                        });
                    });
                }
                
            }); 
        };
        
        $.zoomBatch.previewPic = function(id, pic, sw, sh){
            var pos = $('#prev' + id).position();
            if (!pic){
                pic = $('#prev' + id).attr('data-img');
            }
            if (!pic){return;}
            $('.imgPrevDiv').remove();
            var windowHeight = parseInt($(window).height());
            var w = ".$zoom['batch']['prevWidth']."; 
            var h = ".$zoom['batch']['prevHeight'].";
            var prc = ((w/sw)>(h/sh)) ? (h/sh) : (w/sw);
            w = Math.round(sw*prc);
            h = Math.round(sh*prc);
            pos.top = ((pos.top+h)>(windowHeight-40)) ? (windowHeight-40-h) : pos.top;
            var overl = '<div class=\"imgPrevDiv\" style=\"width: '+(w+20)+'px; height: '+(h+20)+'px; left: '+(pos.left+40)+'px; top: '+pos.top+'px;\" id=\"prv'+id+'\"><div class=\"imgPrevDiv1\" style=\"width: '+(w+20)+'px; height: '+(h+20)+'px;\"><img src=\"".$zoom['config']['icon']."empty.gif\" id=\"prvImg'+id+'\" border=\"0\" style=\"width: auto; height: auto; margin: 10px 0px 0px 10px;\"></div></div>';
            var rand = (new Date()).getTime();
            
            $('<img>')
            .load(function(){
                var newTime = (new Date()).getTime() - rand;
                $('#prvImg' + id).attr('src', '?previewPic='+pic+'&rand='+rand);
                $('#prvImg' + id).parent().append('<div style=\"position: absolute; font-size: 9px; right: 10px; bottom: -1px; z-index: 5;\">Thumb generation & load time: '+newTime+' ms</div>')
                $('#prvImg' + id).parent().css('backgroundImage', 'none');
            })
            .attr('src', '?previewPic='+pic+'&rand='+rand);

            $('body').append(overl);
            
            $('#prvImg' + id).mouseenter(function(){
                $(this).unbind('mouseenter');
                clearTimeout($.previewPicUnload);
            })
            .mouseleave(function(){
                $(this).unbind('mouseleave');
                $.previewPicUnload = setTimeout(function(){
                    $('#prv' + id).fadeOut(200,function(){
                        $('#prv' + id).remove();
                    });
                }, 500);
            });
            
            $('#prev' + id).mouseleave(function(){
                $(this).unbind('mouseleave');
                $.previewPicUnload = setTimeout(function(){
                    $('#prv' + id).fadeOut(200,function(){
                        $('#prv' + id).remove();
                    });
                }, 500);    
            });
        };
        
        $(window).load(function(){
            $('#passFiles').ajaxForm();
            
            $('#buttonSelectAll').click(function(){
                $.zoomBatch.selectAllCheckbox('passFiles');
            });
            
            $('#buttonDeselectAll').click(function(){
                $.zoomBatch.deselectAllCheckbox('passFiles');
            });
            
            $('#buttonBatch').click(function(){
                $.zoomBatch.batchSubmit();
            });
            
            if ($.zoomBatch.allowBatchDelete){
                $('#buttonDelete').click(function(){
                    $.zoomBatch.batchDelete();
                });                
            }
            
            $('#buttonSmartSelect').click(function(){
                $.zoomBatch.smartSelect();
            });
            
            setTimeout(function(){
                $.zoomBatch.adjHeights(0);
            }, 250);
            
            $.zoomBatch.trOver();
            
            $.zoomBatch.bc('HOME');
            
        }).bind('resize', function(){
            $.zoomBatch.adjHeights(0);
        });
    </script>    
    
    </head>
    <body>
    ";

        
        // Save it to session and make available for ajax requests
        $dirTreeOptions = '';
        if (isset($_SESSION['axZmBtch']['dirTreeArray'])){
            if (is_array($_SESSION['axZmBtch']['dirTreeArray']) AND !empty($_SESSION['axZmBtch']['dirTreeArray'])){
                // Generate an option list with all folders
                $dirTreeOptions = $axZmH->directoryOptions((isset($dirTreeArray) ? $dirTreeArray : $_SESSION['axZmBtch']['dirTreeArray']), false );
                $dirTreeOptions = "
                    <div style='margin: 5px 5px 0px 5px;'>
                        <div id=\"dirLoader\" style=\"float: left; visibility: hidden; width: 20px; height: 16px; background-image: url(".$zoom['config']['icon']."batch_dir_loader.gif); background-repeat: no-repeat; background-position: bottom right;\"></div>
                        <div style=\"float: right; text-align: right;\">
                        <SELECT class=\"batchSelect\" id=\"batchSelect\" name=\"dir\" onChange=\"$.zoomBatch.changeDir(this.value); this.blur();\">
                            $dirTreeOptions
                        </SELECT>
                        </div>
                    </div>
                ";                
            }
        }
        
        echo "<div class=\"breadcrumbCotainer\" id=\"breadcrumbCotainer\">
            <div id=\"bci\"></div>
        </div>";
        
        echo "<div class=\"batchMainFrame\" id=\"batchMainFrame\">";
            echo "<div class=\"mainInnerFrame\">";
                
                echo "<div class=\"leftFrameParent\">";
                    echo "<div class=\"leftFrameHead\">$dirTreeOptions</div>";
                    echo "<FORM id=\"passFiles\" method=\"POST\" action=\"\" onsubmit=\"return false;\">";
                        echo "<div class=\"leftFrame\" id=\"batchList\">";
                        echo $axZmH->batchList($zoom, $pic_list_array, $pic_list_data);
                        echo "</div>";
                    echo "</FORM>";
                    
                    echo "<div class=\"leftFrameFoot\" id=\"leftFrameFoot\">";
                        echo "<div class=\"footerRow\" style=\"margin-bottom: 3px;\">";
                            echo "<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>";
                                echo "<td><input type=\"button\" class=\"batchButton\" value=\"Select All\" id=\"buttonSelectAll\" style=\"width: 65px\"></td>";
                                echo "<td><input type=\"button\" class=\"batchButton\" value=\"Deselect All\" id=\"buttonDeselectAll\" style=\"width: 79px\"></td>";
                                echo "<td><input type=\"button\" class=\"batchButton\" value=\"Smart Select\" style=\"font-weight: bold; color: green;\" id=\"buttonSmartSelect\" style=\"width: 94px\"></td>";
                                echo "<td><input type=\"button\" class=\"batchButton\" value=\"Batch Process\" style=\"font-weight: bold;\" id=\"buttonBatch\" style=\"width: 105px\"></td>";
                                if ($zoom['batch']['allowBatchDelete'] === true){
                                    echo "<td><input type=\"button\" class=\"batchButton\" value=\"Delete\" id=\"buttonDelete\" style=\"font-weight: bold; color: red;\" style=\"width: 70px\"></td>";
                                }
                            echo "</table>";
                            
                        echo "</div>";
                        echo "<div class=\"footerRow\">";
                            foreach ($zoom['batch']['arrayMakeName'] as $k=>$v){
                                echo "<input type=\"checkbox\" id=\"batchSwitch$k\" value=\"1\" onClick=\"$.zoomBatch.switchBatch('$k')\" style=\"vertical-align: middle;\"";
                                if ($zoom['batch']['arrayMake'][$k]){ echo ' checked';}

                                echo "> - $v";
                                if ($k == 'In'){
                                    echo ' ('.$zoom['config']['picDim'].')';
                                }
                                echo "&nbsp;&nbsp;";
                            }
                        echo "</div>";
                    echo "</div>";
                
                echo "</div>";
                
                // right table
                echo "<div class=\"rightFrameParent\">";
                    echo "<div class=\"batchProcessHead\" id=\"batchProcessHead\">
                            <div class=\"batchProgressBar\" id=\"batchProgressBar\"></div>
                            <div class=\"batchProcessText\">Batch process <SPAN id=\"counterDiv\"></SPAN> </div> 
                          </div>
                    ";
                        
                    echo "<div class=\"batchProcess\" id=\"batchProcess\">";
                        
                        // table header
                        echo batchProcess($zoom);
     
                        $return = '';
                        $return .= "<table id=\"processTable\" class=\"batchProcessTable\" cellspacing=\"0\" cellpadding=\"1\">";
                            $return .= "<thead><tr>";
                                $return .= "<th>Filename</th>";
                                $return .= "<th width=\"20px\">In</th>";
                                $return .= "<th width=\"20px\">Th</th>";
                                $return .= "<th width=\"20px\">gP</th>";
                                $return .= "<th width=\"20px\">Ti</th>";
                                $return .= "<th width=\"45px\">Time</th>";
                                if (isset($zoom['batch']['batchThumbs'])){
                                    $return .= "<th style=\"width: ".($zoom['config'][$zoom['batch']['batchThumbs'].'X']+4)."px;\">&nbsp;</th>";
                                }
                            $return .= "</tr></thead>";
                            $return .= "<tbody></tbody>";
                        $return .= "</table>";
                        
                        if ($zoom['batch']['showHowTo'] == true){
                            echo "<div class=\"batchInfo\">
                                <h3>About</h3>
                                <p style=\"padding-left: 5px; margin-top: 0\">This is the basic batch process file. 
                                    With this you can process a larger amount of images from one folder or all subfolders 
                                    at once and get visual feedback.
                                </p>
                                <p style=\"padding-left: 5px;\">You do not necessarily have to do this step as AJAX-ZOOM will process 
                                    the files on-the-fly if image tiles and other cached images have not been generated yet! 
                                    However if you have thousands of images it is a good idea to batch process all existing images  
                                    planned to show over AJAX-ZOOM before launching it.
                                </p>
                                <h3>How-to</h3>
                                <ul style=\"margin-top: 0\">
                                    <li>You can navigate through folders by <b>double click</b>, simple click on the folder icon left to the folder name 
                                        or selecting the folder in the select form field at the header of left table. 
                                    </li>
                                    <li>Select images and or folders out of the left table and press the \"Batch Process\" button. 
                                    </li>
                                    <li>If you choose a folder which contains subfolders all images in these subfolders 
                                        as well as images in folders of subfolders (level independent) will be processed.
                                    </li>
                                    <li>Depending on the number of images it can last hours or even days! 
                                        Processing a single image with 7-15 MP should not last much more, than 10 seconds. 
                                        Please try couple images first.
                                    </li>
                                    <li>Do not close this browser window while processing the images!!!
                                    </li>
                                    <li>All images <b>must have</b> unique filenames even if they are in different folders! 
                                    </li>
                                    <li>If you select a folder and press \"Delete\" button, all AJAX-ZOOM cache for images under 
                                        the selected folder and its subfolders will be deleted.
                                    </li>
                            ";
                            
                                if ($zoom['config']['licenceKey'] == 'demo'){
                                    echo "<li>Also please note that with the demo license images over 3.2 megapixel (not to be confused with megabyte) will not proceed.
                                    </li>
                                    ";
                                }
                            
                            echo "</ul></div>";
                        }
     
                        if ($zoom['batch']['showSettings'] == true){
                            echo "<div class=\"batchInfo\">";
                                echo "<h3>Settings info in this batch file</h3>";
                                echo "<table id=\"infoSettings\" cellspacing=\"1\">";
                                    echo "<tr><td width=\"200\">\"licenceKey\"</td><td>".$zoom['config']['licenceKey']."</td>";
                                    echo "<tr><td>\"licenceType\"</td><td>".$zoom['config']['licenceType']."</td>";
                                    if (isset($zoom['config']['error200'])){
                                         "<tr><td>\"error200\"</td><td>".$zoom['config']['error200']."</td>";
                                    }
                                    if (isset($zoom['config']['error300'])){
                                         "<tr><td>\"error300\"</td><td>".$zoom['config']['error300']."</td>";
                                    }
                                    echo "<tr><td>\"memory_limit\"</td><td>".$zoom['config']['memory_limit']."</td>";
                                    echo "<tr><td>Image Magick enabled</td><td>".(($zoom['config']['im'] && $zoom['config']['pyrProg'] == 'IM') ? 'yes' : 'no, will use GD')."</td>";
                                    echo "<tr><td>\"fileTypeArray\"</td><td>".implode(', ', $zoom['config']['fileTypeArray'])."</td>";
                                    
                                    echo "<tr><td>\$_GET[\"example\"]</td><td>".(isset($_GET["example"]) ? $_GET["example"] : 'not defined - you might want to set it at the top of this file to target your final options set')."</td>";
                                    
                                    echo "<tr><td>\"stepPicDim\"</td><td>";
                                        if ($zoom['config']['stepPicDim'] && is_array($zoom['config']['stepPicDim']) && !empty($zoom['config']['stepPicDim'])){
                                            echo '<pre>';
                                            print_r($zoom['config']['stepPicDim']);
                                            echo '</pre>';
                                        }else{
                                            echo "no";
                                        }
                                    echo "</td>";
                                    
                                    echo "<tr><td>Base directory of the source images (can be set inside this file: change \$zoom['config']['pic'])</td><td>".$zoom['config']['picDir']."</td>";
                                    echo "<tr><td>\"pyrTilesDir\"</td><td>".$zoom['config']['pyrTilesDir']."</td>";
                                    echo "<tr><td>\"galleryDir\"</td><td>".$zoom['config']['galleryDir']."</td>";
                                    echo "<tr><td>\"thumbDir\"</td><td>".$zoom['config']['thumbDir']."</td>";
                                    
                                    echo "<tr><td>Pause time between batch</td><td>".$zoom['batch']['pause']." ms</td>";
                                    echo "<tr><td>\"allowDelete\"</td><td>".($zoom['batch']['allowDelete'] ? 'true' : 'false')."</td>";
                                    echo "<tr><td>\"allowBatchDelete\"</td><td>".($zoom['batch']['allowBatchDelete'] ? 'true' : 'false')."</td>";
                                    echo "<tr><td>\"allowDeleteInSubfolders\"</td><td>".($zoom['batch']['allowDeleteInSubfolders'] ? 'true' : 'false')."</td>";
                                    echo "<tr><td>\"confirmDelete\"</td><td>".($zoom['batch']['confirmDelete'] ? 'true' : 'false')."</td>";
                                    echo "<tr><td>\"confirmBatch\"</td><td>".($zoom['batch']['confirmBatch'] ? 'true' : 'false')."</td>";
                                    echo "<tr><td>\"stopOnError\"</td><td>".($zoom['batch']['stopOnError'] ? 'true' : 'false')."</td>";
                                    echo "<tr><td>\"showHowTo\"</td><td>".($zoom['batch']['showHowTo'] ? 'true' : 'false')."</td>";
                                    
                                echo "</table>";
                            echo "</div>";
                        }
                        

                    echo "</div>";
                    echo "<div class='batchProcessFoot' id='batchResults'></div>";

                     echo "<div class=\"rightFrameFoot\" id=\"rightFrameFoot\">";
                         echo "<div class=\"footerRow\" style=\"height: 24px; padding: 1px; text-align: right;\">";
                            echo "<input type=\"button\" value=\"Logout\" style=\"\" onclick=\"location.href='?logout=1'\">";
                         echo "</div>";
                     echo "</div>";
                     
                echo "</div>";
                
            echo "</div>";
        echo "</div>";
      
        echo "<div id='batchOpr'></div>";
    echo "</body>
    </html>";
}

?>