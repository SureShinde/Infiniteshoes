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
class Web4pro_Abandonedcart_Model_System_Config_Discounttype
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value'=> 1, 'label' => Mage::helper('web4pro_abandonedcart')->__('Fixed amount')),
            array('value'=> 2, 'label' => Mage::helper('web4pro_abandonedcart')->__('Percentage'))
        );
        return $options;
    }
    public function options()
    {
        $options[1] = Mage::helper('web4pro_abandonedcart')->__('Fixed amount');
        $options[2] = Mage::helper('web4pro_abandonedcart')->__('Percentage');
        return $options;
    }
}