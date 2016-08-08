<?php
class Cbi_Delivery_Model_Observer {

    public function modifyPrice(Varien_Event_Observer $obs){
        $productId = $obs->getProduct()->getId();
        $productType = $obs->getProduct()->getTypeId();

        if ($productType == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {//neu la configurable product
            $_product = Mage::getModel('catalog/product')->load($productId);

            //tim attribute code va attribute option value
            $product = $obs->getEvent()->getQuoteItem();
            $productSku = $product->getSku();  //for configurable product
            $productAttributesOptions = $_product->getTypeInstance(true)->getConfigurableOptions($_product);
            $productAttributesOptions = reset($productAttributesOptions);
            $productConfigurableCode = '';
            $productConfigurableValue = '';
            for($i=0;$i<count($productAttributesOptions); $i++){
                if($productSku == $productAttributesOptions[$i]["sku"]){
                    $productConfigurableCode =$productAttributesOptions[$i]["attribute_code"];
                    $productConfigurableTitle =$productAttributesOptions[$i]["option_title"];
                }
            }

            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "$productConfigurableCode");
            if(count($attribute->getSource()->getAllOptions(true))>0){
                foreach ($attribute->getSource()->getAllOptions(true) as $option) {
                    if($option['label']== $productConfigurableTitle){
                        $productConfigurableValue = $option['value'];
                    }
                }//end foreach
            }
            //end phan tim attribute code va attribute option value

            //tim Delivery Date
            $item     = $obs->getQuoteItem();
            if ($item->getParentItem()) {$item = $item->getParentItem();}
            $price = $item->getProduct()->getFinalPrice();
            $product  = $item->getProduct();
            $productOptions = $product->getTypeInstance(true)->getOrderOptions($product);
            $optionList = $productOptions["info_buyRequest"]["options"];
            if(count($optionList)>0){
                $deliveryDate = 0;
                foreach($optionList as $option){
                    if($option["date"]!=''){
                        $deliveryDate = $option["date"];
                    }
                }
            }

            // xu ly lai gia dua vao so sanh voi phan delivery price tao them trong admin
            $ids = $_product->getCategoryIds();
            /*if(is_array($ids) && $deliveryDate!=0){
                //query database de tinh lai price
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');

                $price_fixed = 0;
                $price_percent = 0;
                foreach($ids as $cat){
                    $query = 'SELECT price_fixed, price_percent FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE r.cat_id ='.$cat.
                    ' AND d.delivery_from<="'.date("Y-m-d",strtotime($deliveryDate)).'"'.
                    ' AND d.delivery_to>="'.date("Y-m-d",strtotime($deliveryDate)).'"'.
                    ' AND d.attribute_code = "'.$productConfigurableCode.'"'.
                    ' AND d.attribute_value = "'.$productConfigurableValue.'" LIMIT 1';
                    ' LIMIT 1';

                    $rowArray =$read->fetchRow($query);

                    if($rowArray["price_fixed"]>0 && $rowArray["price_fixed"]>$price_fixed){
                        $price_fixed = $rowArray["price_fixed"];
                    }else if($rowArray["price_percent"]>0 && $rowArray["price_percent"]>$price_percent){
                        $price_percent = $rowArray["price_percent"];
                    }
                }//end foreach
            }*///end if

            if(is_array($ids) && $deliveryDate!=0) {
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
                        ' AND d.delivery_from<="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
                        ' AND d.delivery_to>="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
                        ' AND d.attribute_code = "' . $productConfigurableCode . '"' .
                        ' AND d.attribute_value = "' . $productConfigurableValue . '" LIMIT 1';
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
                            ' AND d.delivery_from<="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
                            ' AND d.delivery_to>="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
                            ' AND d.attribute_code = "' . $productConfigurableCode . '"' .
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
                            ' AND d.delivery_from<="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
                            ' AND d.delivery_to>="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
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
                    $price+=$price_fixed;
                }else if($price_percent>0){
                    $price = $price + ($price/100*$price_percent);
                }

                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);

            }//end xu ly lai gia


        }//end neu la configurable product
        else{
            $_product = Mage::getModel('catalog/product')->load($productId);
            $ids = $_product->getCategoryIds();

            //tim Delivery Date
            $item     = $obs->getQuoteItem();
            if ($item->getParentItem()) {$item = $item->getParentItem();}
            $price = $item->getProduct()->getFinalPrice();
            $product  = $item->getProduct();
            $productOptions = $product->getTypeInstance(true)->getOrderOptions($product);
            $optionList = $productOptions["info_buyRequest"]["options"];
            if(count($optionList)>0){
                $deliveryDate = 0;
                $optionList = array_shift($optionList);
                if($optionList['date']){
                    $deliveryDate = $optionList['date'];
                }
                /*foreach($optionList as $option){
                    if($option["date"]){
                        $deliveryDate = $option["date"];
                    }
                }*/
            }
            if(is_array($ids) && $deliveryDate!=0) {
                //query database de tinh lai price
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');

                $price_fixed = 0;
                $price_percent = 0;
                foreach ($ids as $cat) {
                    $query = 'SELECT price_fixed, price_percent FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE r.cat_id =' . $cat .
                        ' AND d.delivery_from<="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
                        ' AND d.delivery_to>="' . date("Y-m-d", strtotime($deliveryDate)) . '"' .
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
                    $price+=$price_fixed;
                }else if($price_percent>0){
                    $price = $price + ($price/100*$price_percent);
                }

                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);

            }//end xu ly lai gia
        }

        //exit();

        //$_product = Mage::getModel('catalog/product')->load($productId);
        ///print_r($_product);







        //exit();

        /*$item = $obs->getQuoteItem();
        // Ensure we have the parent item, if it has one
        $item = ( $item->getParentItem() ? $item->getParentItem() : $item );
        // Load the custom price
        $price = $this->_getPriceByItem($item);
        // Set the custom price
        if($categoryId==39){
            $item->setCustomPrice(10);
            $item->setOriginalCustomPrice(10);
        }else{
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
        }

        // Enable super mode on the product.
        $item->getProduct()->setIsSuperMode(true);*/
    }

    protected function _getPriceByItem(Mage_Sales_Model_Quote_Item $item)
    {
        //$data = $item->getProduct()->getData();
        //print_r($data);exit();
        $price;

        //use $item to determine your custom price.

        //return $price;
        return 1000;
    }
}
?>