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

class Web4pro_Abandonedcart_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return array
     */
    public function getDatePeriods()
    {
        return array(
            '24h' => $this->__('Last 24 Hours'),
            '7d'  => $this->__('Last 7 Days'),
            '30d'  => $this->__('Last 30 Days'),
            '60d'  => $this->__('Last 60 Days'),
            '90d'  => $this->__('Last 90 Days'),
            'lifetime' => $this->__('Lifetime'),
        );
    }

    /**
     * @param $message
     * @param string $filename
     */
    public function log($message, $filename = 'Web4pro_Abandonedcart.log')
    {
        if(Mage::getStoreConfig(Web4pro_AbandonedCart_Model_Config::LOG)) {
            Mage::log($message, null, $filename);
        }
    }

    /**
     * @param $mailType
     * @param $mail
     * @param $name
     * @param $couponCode
     * @param $storeId
     */
    public function saveMail($mailType,$mail,$name,$couponCode,$storeId)
    {
        if(!empty($couponCode)) {
            $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
            $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
            $couponAmount = $rule->getDiscountAmount();
            switch($rule->getSimpleAction()) {
                case 'cart_fixed':
                    $couponType = 1;
                    break;
                case 'by_percent':
                    $couponType = 2;
                    break;
            }
        }
        else {
            $couponType = 0;
            $couponAmount = 0;
        }
        $sent = Mage::getModel('web4pro_abandonedcart/mailssent');
        $sent->setMailType($mailType)
            ->setStoreId($storeId)
            ->setCustomerEmail($mail)
            ->setCustomerName($name);
        if(!empty($couponCode)){
            $sent->setCouponNumber($couponCode);
        }
        if(!empty($couponType)){
            $sent->setCouponType($couponType);
        }

        if(!empty($couponAmount)){
            $sent->setCouponAmount($couponAmount);
        }

        $sent->setSentAt(Mage::getModel('core/date')->gmtDate())->save();
    }

}
