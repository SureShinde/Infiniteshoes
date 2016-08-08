<?php
/**
 * WEB4PRO - Creating profitable online stores
 *
 *
 * @author    WEB4PRO <amutylo@web4pro.com.ua>
 * @category  WEB4PRO
 * @package   Web4pro_ModuleName
 * @copyright Copyright (c) 2014 WEB4PRO (http://www.web4pro.net)
 * @license   http://www.web4pro.net/license.txt
 */
class Web4pro_Abandonedcart_Model_EventObserver
{
    public function saveConfig(Varien_Event_Observer $observer)
    {
        if(Mage::app()->getRequest()->getParam('store')) {
            $scope = 'store';
        }
        elseif(Mage::app()->getRequest()->getParam('website')) {
            $scope = 'website';
        }
        else {
            $scope = 'default';
        }

        $store = is_null($observer->getEvent()->getStore()) ? Mage::app()->getDefaultStoreView()->getCode(): $observer->getEvent()->getStore();

            $config =  new Mage_Core_Model_Config();
            $config->saveConfig(Web4pro_Abandonedcart_Model_Config::ACTIVE,true,$scope,$store);
            Mage::getConfig()->cleanCache();

    }

    public function loadCustomer(Varien_Event_Observer $observer){
        $quote = $observer->getEvent()->getQuote();
        if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $action = Mage::app()->getRequest()->getActionName();
            $onCheckout = $action == 'saveOrder' || $action == 'savePayment' || $action == 'saveShippingMethod' || $action == 'saveBilling';
            if (isset($_COOKIE['email']) && $_COOKIE['email'] != 'none' && !$onCheckout) {
                $email = str_replace(' ', '+', $_COOKIE['email']);
                if($quote->getCustomerEmail() != $email){
                    $quote->setCustomerEmail($email)
                        ->save();
                }
            }
        }
        return $observer;
    }

    public function saveAbandonedCartFlagToOrder(Varien_Event_Observer $observer){
        $order = $observer->getEvent()->getOrder();
        $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $flag = $quote->getData('web4pro_abandonedcart_flag');
        $counter = $quote->getData('web4pro_abandonedcart_counter');
        if($order->getData('web4pro_abandonedcart_flag')){
            $order->setData('web4pro_abandonedcart_flag',$flag);
            $order->setData('web4pro_abandonedcart_counter',$counter);
            $order->save();
        }
        return $observer;
    }
}