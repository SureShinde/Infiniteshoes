<?php
class Cbi_Customajax_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $productId  = (int) $this->getRequest()->getParam('id');
        $productId = 18;
        Mage::helper('catalog/product')->initProduct($productId, $this);
        $this->loadLayout();
        echo $this->renderLayout();
        echo $productId;
        echo 'vao day';exit();
        //$this->loadLayout();
        //$this->renderLayout();
    }

    public function priceAction(){
        $attributeCode  = $this->getRequest()->getParam('attributeCode', "");
        $attributeValue = $this->getRequest()->getParam('attributeValue', "");
        $attributeValue = str_replace("option", "", $attributeValue);
        $date           = $this->getRequest()->getParam('date', 0);
        $productType    = $this->getRequest()->getParam('productType', "");
        $productId      = $this->getRequest()->getParam('productId', "");

        if($productType=="configurable"){
            $_product = Mage::getModel('catalog/product')->load($productId);

            //tim attribute code va attribute option value
            /*$attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "$attributeCode");
            //echo '<pre>';
            if(count($attribute->getSource()->getAllOptions(true))>0){
                $attributeValue = 0;
                foreach ($attribute->getSource()->getAllOptions(true) as $option) {
                    if($option['label'] == $attributeTitle){
                        $attributeValue = $option['value'];
                    }
                }//end foreach
            }*/

            // xu ly lai gia dua vao so sanh voi phan delivery price tao them trong admin
            $ids = $_product->getCategoryIds();
            if(is_array($ids) && $date!=0) {
                //query database de tinh lai price
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');

                $price_fixed = 0;
                $price_percent = 0;
                $text_desc = 'Delivery Price: ';

                // kiem tra dau tien truong hop co du cac thong tin (cates, attribute code, attribute value)
                // neu co thong tin thi ngung khong co tiep tuc kiem tra
                foreach ($ids as $cat) {
                    $query = 'SELECT price_fixed, price_percent, text_desc FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE r.cat_id =' . $cat .
                        ' AND d.delivery_from<="' . date("Y-m-d", strtotime($date)) . '"' .
                        ' AND d.delivery_to>="' . date("Y-m-d", strtotime($date)) . '"' .
                        ' AND d.attribute_code = "' . $attributeCode . '"' .
                        ' AND d.attribute_value = "' . $attributeValue . '" LIMIT 1';
                    $rowArray = $read->fetchRow($query);
                    if ($rowArray["price_fixed"] > 0 && $rowArray["price_fixed"] > $price_fixed) {
                        $price_fixed = $rowArray["price_fixed"];
                    } else if ($rowArray["price_percent"] > 0 && $rowArray["price_percent"] > $price_percent) {
                        $price_percent = $rowArray["price_percent"];
                    }
                    if($rowArray["text_desc"]){
                        $text_desc = $rowArray["text_desc"].': ';
                    }
                }//end foreach

                // kiem tra lan 2 truong hop co thong tin (cates, attribute code)
                // neu co thong tin thi ngung khong co tiep tuc kiem tra
                if(!$price_fixed && !$price_percent){
                    foreach ($ids as $cat) {
                        $query = 'SELECT price_fixed, price_percent, text_desc FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE r.cat_id =' . $cat .
                            ' AND d.delivery_from<="' . date("Y-m-d", strtotime($date)) . '"' .
                            ' AND d.delivery_to>="' . date("Y-m-d", strtotime($date)) . '"' .
                            ' AND d.attribute_code = "' . $attributeCode . '"' .
                            ' AND (d.attribute_value = "" OR d.attribute_value is null ) LIMIT 1';
                        $rowArray = $read->fetchRow($query);
                        if ($rowArray["price_fixed"] > 0 && $rowArray["price_fixed"] > $price_fixed) {
                            $price_fixed = $rowArray["price_fixed"];
                        } else if ($rowArray["price_percent"] > 0 && $rowArray["price_percent"] > $price_percent) {
                            $price_percent = $rowArray["price_percent"];
                        }
                        if($rowArray["text_desc"]){
                            $text_desc = $rowArray["text_desc"].': ';
                        }
                    }//end foreach
                }//end if

                // kiem tra lan 3 truong hop chi co thong tin (cates)
                // neu co thong tin thi ngung khong co tiep tuc kiem tra
                if(!$price_fixed && !$price_percent){
                    foreach ($ids as $cat) {
                        $query = 'SELECT price_fixed, price_percent, text_desc FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE r.cat_id =' . $cat .
                            ' AND d.delivery_from<="' . date("Y-m-d", strtotime($date)) . '"' .
                            ' AND d.delivery_to>="' . date("Y-m-d", strtotime($date)) . '"' .
                            ' AND (d.attribute_code = 0 OR d.attribute_code is null)' .
                            ' AND (d.attribute_value = "" OR d.attribute_value is null ) LIMIT 1';
                        $rowArray = $read->fetchRow($query);
                        if ($rowArray["price_fixed"] > 0 && $rowArray["price_fixed"] > $price_fixed) {
                            $price_fixed = $rowArray["price_fixed"];
                        } else if ($rowArray["price_percent"] > 0 && $rowArray["price_percent"] > $price_percent) {
                            $price_percent = $rowArray["price_percent"];
                        }
                        if($rowArray["text_desc"]){
                            $text_desc = $rowArray["text_desc"].': ';
                        }
                    }//end foreach
                }//end if
            }

            //khi da co gia thi xu ly lai gia
            if($price_fixed>0 || $price_percent>0){
                if($price_fixed>0){
                    echo "<div class='deliveryPricePlus' id='deliveryPricePlus'>".$text_desc."<b>$$price_fixed</b></div>";
                }else if($price_percent>0){
                    echo "<div class='deliveryPricePlus' id='deliveryPricePlus'>".$text_desc."<b>$price_percent %</b></div>";
                    //echo $price_percent." %";
                }
            }//end xu ly lai gia

        }//end configurable product
        else if($productType=="simple"){
            $_product = Mage::getModel('catalog/product')->load($productId);
            $ids = $_product->getCategoryIds();

            // xu ly lai gia dua vao so sanh voi phan delivery price tao them trong admin
            if(is_array($ids) && $date!=0) {
                //query database de tinh lai price
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');

                $price_fixed = 0;
                $price_percent = 0;
                foreach ($ids as $cat) {
                    $query = 'SELECT price_fixed, price_percent FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE r.cat_id =' . $cat .
                        ' AND d.delivery_from<="' . date("Y-m-d", strtotime($date)) . '"' .
                        ' AND d.delivery_to>="' . date("Y-m-d", strtotime($date)) . '"' .
                        ' AND (d.attribute_code = 0 OR d.attribute_code is null)' .
                        ' AND (d.attribute_value = "" OR d.attribute_value is null ) LIMIT 1';
                    $rowArray = $read->fetchRow($query);

                    if ($rowArray["price_fixed"] > 0 && $rowArray["price_fixed"] > $price_fixed) {
                        $price_fixed = $rowArray["price_fixed"];
                    } else if ($rowArray["price_percent"] > 0 && $rowArray["price_percent"] > $price_percent) {
                        $price_percent = $rowArray["price_percent"];
                    }
                }//end foreach
            }

            //khi da co gia thi xu ly lai gia
            if($price_fixed>0 || $price_percent>0){
                if($price_fixed>0){
                    echo "<div class='deliveryPricePlus' id='deliveryPricePlus'>Delivery Price: <b>$$price_fixed</b></div>";
                }else if($price_percent>0){
                    echo "<div class='deliveryPricePlus' id='deliveryPricePlus'>Delivery Price: <b>$price_percent %</b></div>";
                    //echo $price_percent." %";
                }
            }//end xu ly lai gia

        }
        exit();
    }
}

?>