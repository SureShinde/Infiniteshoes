<?php

class MT_Giftcard_Block_Sales_Order_Item_Renderer_Giftcard
    extends Mage_Sales_Block_Order_Item_Renderer_Default
{
    public function getItemOptions()
    {
        $result = parent::getItemOptions();
        $giftCardValue = '';
        $giftCardCollection = Mage::getModel('giftcard/giftcard')->getCollection()
            ->addFieldToFilter('order_item_id', $this->getItem()->getId());
        if ($giftCardCollection->count() > 0) {
            foreach ($giftCardCollection as $giftCard) {
                $giftCardValue.=$giftCard->getCode().'('.Mage::helper('giftcard')->__($giftCard->getStatus()).')<br/>';
            }

            $options = array(
                array(
                    'label' => Mage::helper('giftcard')->__('Gift Card'),
                    'value' => $giftCardValue
            ));

            if ($giftCard->getStatus() == MT_Giftcard_Model_Giftcard::STATUS_PENDING)
                $options[] = array(
                    'label' => Mage::helper('giftcard')->__('Note'),
                    'value' => Mage::helper('giftcard')->__('Please pay for gift card. After this, your gift cards will be active.')
                );

            $result = array_merge($result, $options);
        }


        return $result;
    }
}