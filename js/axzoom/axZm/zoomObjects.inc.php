<?php
/**
* Plugin: jQuery AJAX-ZOOM, zoomObjects.inc.php
* Copyright: Copyright (c) 2010-2016 Vadim Jacobi
* License Agreement: http://www.ajax-zoom.com/index.php?cid=download
* Version: 4.3.1
* Date: 2016-03-07
* Review: 2016-03-07
* URL: http://www.ajax-zoom.com
* Documentation: http://www.ajax-zoom.com/index.php?cid=docs
*/

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//// Ducumentation about this file can be found here: http://www.ajax-zoom.com/index.php?cid=docs#zoomObjects ////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(!session_id()){session_start();}

///////////////////////////////////////////
//// STEP 1 - DEFINING THE IMAGE ARRAY ////
///////////////////////////////////////////

if (isset($_GET['zoomData'])){
	// Ver. 4.2.1+ processing $_GET['zoomData'] moved to axZmH class and is called over getZoomData method
	// If you would like to change it, you can simply write you code here as it was before 
	// or you could create your own class e.g.
	/* 	class my_axZmH extends axZmH{
			function my_getZoomData($zoom){
				// your code goes here
			}
		};
		$my_axZmH = new my_axZmH;
		$getZoomDataReturn = $my_axZmH->my_getZoomData($zoom);
	*/
	
	$getZoomDataReturn = $axZmH->getZoomData($zoom);
	$zoom = $getZoomDataReturn[0];
	$pic_list_array = $getZoomDataReturn[1];
	$pic_list_data = $getZoomDataReturn[2];
	$zoomTmp = $getZoomDataReturn[3];
}

elseif (isset($_GET['3dDir']) && strlen($_GET['3dDir'])){
	// Ver. 4.2.1+ processing $_GET['3dDir'] moved to axZmH class and is called over get3dDir method
	// If you would like to change it, you can simply write you code here as it was before 
	// or you could create your own class e.g.
	/* 	class my_axZmH extends axZmH{
			function my_get3dDir($zoom){
				// your code goes here
			}
		};
		$my_axZmH = new my_axZmH;
		$get3dDirReturn = $my_axZmH->my_get3dDir($zoom);
	*/
	$get3dDirReturn = $axZmH->get3dDir($zoom);
	$zoom = $get3dDirReturn[0];
	$pic_list_array = $get3dDirReturn[1];
	$pic_list_data = $get3dDirReturn[2];
	$zoomTmp = $get3dDirReturn[3];
} 

elseif (isset($_GET['zoomDir']) && strlen($_GET['zoomDir'])){
	// Ver. 4.2.1+ processing $_GET['zoomDir'] moved to axZmH class and is called over getZoomDir method
	// If you would like to change it, you can simply write you code here as it was before 
	// or you could create your own class e.g.
	/* 	class my_axZmH extends axZmH{
			function my_getZoomDir($zoom){
				// your code goes here
			}
		};
		$my_axZmH = new my_axZmH;
		$getZoomDirReturn = $my_axZmH->my_getZoomDir($zoom);
	*/
	$getZoomDirReturn = $axZmH->getZoomDir($zoom, isset($axZmScanDir) ? true : false);
	$zoom = $getZoomDirReturn[0];
	$pic_list_array = $getZoomDirReturn[1];
	$pic_list_data = $getZoomDirReturn[2];
	$zoomTmp = $getZoomDirReturn[3];
}


////////////////////////////////////////
//// STEP 2 - COLLECT INFORMATION //////
////////////////////////////////////////
if (isset($_GET['example']) && in_array($_GET['example'], array('magento', 'oxid', 'xtc'))){
	$zoom['config']['galleryThumbDesc'] = false;
	$zoom['config']['galleryThumbFullDesc'] = false;
}

// Ver. 4.2.1+ processing the results is moved to axZmH class and is called over preProceedList method
// If you would like to change it, you can simply write you code here as it was before 
// or you could create your own class e.g.
/* 	class my_axZmH extends axZmH{
		function my_preProceedList($zoom){
			// your code goes here
		}
	};
	$my_axZmH = new my_axZmH;
	$preProceedListReturn = $my_axZmH->my_preProceedList($zoom);
*/
if (isset($pic_list_array)){
	$preProceedListReturn = $axZmH->preProceedList($zoom, $pic_list_array, $pic_list_data, $zoomTmp);
	$zoom = $preProceedListReturn[0];
	$pic_list_array = $preProceedListReturn[1];
	$pic_list_data = $preProceedListReturn[2];
	$zoomTmp = $preProceedListReturn[3];
}
?>