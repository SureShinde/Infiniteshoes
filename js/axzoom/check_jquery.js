/**
*  Module: jQuery AJAX-ZOOM for Magento, /js/check_jquery.js
*  Copyright: Copyright (c) 2010-2016 Vadim Jacobi
*  License Agreement: http://www.ajax-zoom.com/index.php?cid=download
*  Version: 1.2.0
*  Date: 2016-05-07
*  Review: 2016-05-07
*  URL: http://www.ajax-zoom.com
*  Documentation: http://www.ajax-zoom.com/index.php?cid=modules&module=magento
*
*  @author    AJAX-ZOOM <support@ajax-zoom.com>
*  @copyright 2010-2016 AJAX-ZOOM, Vadim Jacobi
*  @license   http://www.ajax-zoom.com/index.php?cid=download
*/

// Inject jQuery 1.10.2 (if it is not defined) from google API CDN
// For AJAX-ZOOM you can also use any other jQuery version
// If you will be using jQuery >= 1.9 it is a good idea to also load "jquery-migrate" - https://github.com/jquery/jquery-migrate/
if ( !window.jQuery && (typeof jQuery === 'undefined') ) {
    document.write(unescape("%3Cscript type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'%3E%3C/script%3E"));
    document.write(unescape("%3Cscript type='text/javascript' %3EjQuery.noConflict();%3C/script%3E"));
}