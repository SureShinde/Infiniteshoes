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

require_once Mage::getModuleDir('controllers','Mage_Checkout').DS.'CartController.php';

class Web4pro_Abandonedcart_AbandonedController extends Mage_Checkout_CartController
{

    public function loadquoteAction()
    {
        $params = $this->getRequest()->getParams();
        if(isset($params['id']))
        {
            //get quote
            $quote = Mage::getModel('sales/quote')->load($params['id']);
            //url redirect to
            $url = Mage::getStoreConfig(Web4pro_Abandonedcart_Model_Config::PAGE,$quote->getStoreId());
            if(isset($params['coupon'])){
                $quote->setCouponCode($params['coupon']);
                $quote->save();
            }
            if((!isset($params['token']) || (isset($params['token'])&&$params['token']!=$quote->getData('web4pro_abandonedcart_token'))) && Mage::getStoreConfig(Web4pro_Abandonedcart_Model_Config::AUTOLOGIN, $quote->getStoreId())) {
                Mage::getSingleton('customer/session')->addNotice($this->__("Your abandoned cart token is not valid or incorrect"));
                $this->_redirect($url);
            }
            else {
                $url = Mage::getStoreConfig(Web4pro_Abandonedcart_Model_Config::PAGE,$quote->getStoreId());
                $quote->setData('web4pro_abandonedcart_flag',1);
                $quote->save();
                if(!$quote->getCustomerId()) {
                    $this->_getSession()->setQuoteId($quote->getId());
                    $this->_redirect($url);
                }
                else {
                    if(Mage::getStoreConfig(Web4pro_Abandonedcart_Model_Config::AUTOLOGIN,$quote->getStoreId())) {
                        $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
                        if($customer->getId())
                        {
                            Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                        }
                        $this->_redirect($url);
                    }
                    else {
                        if(Mage::helper('customer')->isLoggedIn()) {
                            $this->_redirect($url);
                        }
                        else {
                            Mage::getSingleton('customer/session')->addNotice($this->__("Please, login to complete your order"));
                            $this->_redirect('customer/account');
                        }
                    }
                }
            }
        }
    }
}