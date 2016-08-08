<?php
/**
*  Module: jQuery AJAX-ZOOM for Magento, /app/code/local/Ax/Zoom/Model/Galleryposition.php
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

class Ax_Zoom_Model_Galleryposition
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'top', 'label'=>Mage::helper('axzoom')->__('top')),
            array('value'=>'right', 'label'=>Mage::helper('axzoom')->__('right')),
            array('value'=>'bottom', 'label'=>Mage::helper('axzoom')->__('bottom')),
            array('value'=>'left', 'label'=>Mage::helper('axzoom')->__('left'))
        );
    }

}