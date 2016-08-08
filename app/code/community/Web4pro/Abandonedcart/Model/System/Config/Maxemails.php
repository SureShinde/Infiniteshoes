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
class Web4pro_Abandonedcart_Model_System_Config_Maxemails
{
    public function toOptionArray()
    {
        $options = array();
        for($i = 0; $i < Web4pro_Abandonedcart_Model_Config::MAXTIMES_NUM; $i++){
            $options[]=array('value'=> $i, 'label' => $i+1);
        }
        return $options;
    }
}