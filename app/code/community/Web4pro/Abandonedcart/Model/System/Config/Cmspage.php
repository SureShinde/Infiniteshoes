<?php
/**
 * WEB4PRO - Creating profitable online stores
 *
 *
 * @author    WEB4PRO <amutylo@web4pro.com.ua>
 * @category  WEB4PRO
 * @package   Web4pro_Abandonedcart
 * @copyright Copyright (c) 2014 WEB4PRO (http://www.web4pro.net)
 * @license   http://www.web4pro.net/license.txt
 */

class Web4pro_Abandonedcart_Model_System_Config_Cmspage
{

    public function toOptionArray()
    {
        $collection = Mage::getModel('cms/page')->getCollection()->addOrder('title', 'asc');
        return array('checkout/cart'=> "Shopping Cart page") + $collection->toOptionIdArray();
    }

}
