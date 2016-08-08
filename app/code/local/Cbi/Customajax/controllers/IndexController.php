<?php
class Cbi_Customajax_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
        echo 'aaaaaa';
        $this->loadLayout();
        $this->renderLayout();
    }
    public function getbyskuAction(){
        $_prdSku    = $this->getRequest()->getParam('sku');
        $_product   = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('sku',$_prdSku);
        $this->productInfo($_product);
    }
    public function getbyidAction(){
        $_prdID     = intval($this->getRequest()->getParam('id'));
        $_product   = Mage::getModel('catalog/product')->getCollection()->load($_prdID);
        $this->productInfo($_product);
    }
    public function productInfo($_product){
        $this->loadLayout();
        $this->renderLayout();
    }
}

?>