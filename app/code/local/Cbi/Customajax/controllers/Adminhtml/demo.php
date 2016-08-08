<?php
/** * Add additional options to order item product options (this is missing in the core)
 * * @param Varien_Event_Observer $observer */

public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer){
    $quoteItem = $observer->getItem();
    if($additionalOptions = $quoteItem->getOptionByCode('additional_options')){
        $orderItem = $observer->getOrderItem();
        $options = $orderItem->getProductOptions();
        $options['additional_options']= unserialize($additionalOptions->getValue());
        $orderItem->setProductOptions($options);
    }
}
        /**
         * Manipulate the custom product options * *
         * @param Varien_Event_Observer $observer
         * @return void
         */publicfunction checkoutCartProductAddAfter(Varien_Event_Observer $observer){ $item = $observer->getQuoteItem(); $infoArr = array();if($info = $item->getProduct()->getCustomOption('info_buyRequest')){ $infoArr = unserialize($info->getValue());}// Set additional options in case of a reorderif($infoArr && isset($infoArr['additional_options'])){// An additional options array is set on the buy request - this is a reorder $item->addOption(array('code'=>'additional_options','value'=> serialize($infoArr['additional_options'])));return;} $options =Mage::helper('catalog/product_configuration')->getCustomOptions($item);foreach($options as $option){// The only way to identify a custom option without// hardcoding ID's is the label :-(// But manipulating options this way is hackish anywayif('Size'=== $option['label']){ $optId = $option['option_id'];// Add replacement custom option with modified value $additionalOptions = array(array('code'=>'my_code','label'=> $option['label'],'value'=> $option['value'].' YOUR EXTRA TEXT','print_value'=> $option['print_value'].' YOUR EXTRA TEXT',)); $item->addOption(array('code'=>'additional_options','value'=> serialize($additionalOptions),));// Update info_buyRequest to reflect changesif($infoArr && isset($infoArr['options'])&& isset($infoArr['options'][$optId])){// Remove real custom option unset($infoArr['options'][$optId]);// Add replacement additional option for reorder (see above) $infoArr['additional_options']= $additionalOptions; $info->setValue(serialize($infoArr)); $item->addOption($info);}// Remove real custom option id from option_ids listif($optionIdsOption = $item->getProduct()->getCustomOption('option_ids')){ $optionIds = explode(',', $optionIdsOption->getValue());if(false!==($idx = array_search($optId, $optionIds))){ unset($optionIds[$idx]); $optionIdsOption->setValue(implode(',', $optionIds)); $item->addOption($optionIdsOption);}}// Remove real custom option $item->removeOption('option_'. $optId);}}
?>