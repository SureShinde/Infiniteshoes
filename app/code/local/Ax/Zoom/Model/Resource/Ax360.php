<?php
/**
*  Module: jQuery AJAX-ZOOM for Magento, /app/code/local/Ax/Zoom/Model/Resource/Ax360.php
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

class Ax_Zoom_Model_Resource_Ax360 extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('axzoom/table_ax360', 'id_360');
    }
}