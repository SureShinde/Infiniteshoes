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

class Web4pro_Abandonedcart_Model_System_Config_Unit
{
    public function toOptionArray()
    {
        $options = array(
            array('value'=> Web4pro_Abandonedcart_Model_Config::IN_DAYS, 'label' => Mage::helper('web4pro_abandonedcart')->__('Days')),
            array('value'=> Web4pro_Abandonedcart_Model_Config::IN_HOURS, 'label' => Mage::helper('web4pro_abandonedcart')->__('Hour(s)'))
        );
        return $options;
    }

}