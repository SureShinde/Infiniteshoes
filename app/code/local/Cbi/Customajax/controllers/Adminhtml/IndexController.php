<?php
class Cbi_Delivery_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        //database read adapter
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = 'SELECT * FROM delivery_price';

        $rowsArray = $read->fetchAll($query); // return all rows
        $rowArray =$read->fetchRow($query);   //return row
        //print_r($rowsArray);

        $this->loadLayout()
            ->_setActiveMenu('catalog')
            ->_title($this->__('Manage Price'));
        $block = $this->getLayout()
                    ->createBlock('core/template')
                    ->setTemplate('cbi/index.phtml')
                    ->assign('data',$rowsArray);
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
        /*$this->loadLayout()
            ->_setActiveMenu('catalog')
            ->_title($this->__('Add New'));
        $this->getLayout()->getBlock('head')
            ->addItem('js_css', 'calendar/calendar-win2k-1.css')
            ->addJs('calendar/calendar.js')
            ->addJs('calendar/calendar-setup.js');
        $block = $this->getLayout()->createBlock('core/template')->setTemplate('cbi/new.phtml');
        $this->_addContent($block);
        $this->renderLayout();*/
    }

    public function editAction(){
        $id = $this->getRequest()->getParam('id', null);
        if($id>0){
            //database read adapter
            $read = Mage::getSingleton('core/resource')->getConnection('core_read');
            $query = 'SELECT * FROM delivery_price AS d
                    LEFT JOIN delivery_relationship AS r ON d.delivery_id = r.delivery_id
                    WHERE d.delivery_id ='.$id;

            $rowArrays =$read->fetchAll($query);   //return all rows
            $this->loadLayout()
                ->_setActiveMenu('catalog')
                ->_title($this->__('Edit'));
        }else{
            $this->loadLayout()
                ->_setActiveMenu('catalog')
                ->_title($this->__('Add New'));
        }
        $this->getLayout()->getBlock('head')
            ->addItem('js_css', 'calendar/calendar-win2k-1.css')
            ->addJs('calendar/calendar.js')
            ->addJs('calendar/calendar-setup.js');
        $block = $this->getLayout()
                    ->createBlock('core/template')
                    ->setTemplate('cbi/new.phtml')
                    ->assign('results',$rowArrays);
        $this->_addContent($block);
        $this->renderLayout();
    }

    public function saveAction()
    {
        $value = $this->getRequest()->getParams();
        $id = $value["delivery_id"];
        //database write adapter
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        if($id == '' || $id==0){
            $write->insert(
                "delivery_price",
                array(
                    "attribute_code" => $value["attribute_code"],
                    "attribute_value" => $value["attribute_option"],
                    "delivery_from" => date("Y-m-d",strtotime($value["date_from"])),
                    "delivery_to" => date("Y-m-d",strtotime($value["date_to"])),
                    "price_fixed" => $value["price_fixed"],
                    "price_percent" => $value["price_percent"],
                    "text_desc" => $value["text_desc"],
                    "date_created" => date("Y-m-d H-i-s")
                )
            );
            $lastInsertId = $write->lastInsertId();
            $message = $this->__('Insert successfully');
        }else{//update
            $write->delete(
                "delivery_relationship",
                "delivery_id=".$id
            );

            $write->update(
                "delivery_price",
                array(
                    "attribute_code" => $value["attribute_code"],
                    "attribute_value" => $value["attribute_option"],
                    "delivery_from" => date("Y-m-d",strtotime($value["date_from"])),
                    "delivery_to" => date("Y-m-d",strtotime($value["date_to"])),
                    "price_fixed" => $value["price_fixed"],
                    "price_percent" => $value["price_percent"],
                    "text_desc" => $value["text_desc"]
                ),
                "delivery_id=".$id
            );
            $lastInsertId = $id;
            $message = $this->__('Update successfully');
        }

        //insert qua bang delivery_relationship
        for($i=0; $i<count($value["cates"]); $i++){
            $write->insert(
                "delivery_relationship",
                array(
                    "delivery_id" => $lastInsertId,
                    "cat_id" => $value["cates"][$i]
                )
            );
        }
        //print_r($date_to);
        Mage::getSingleton('core/session')->addSuccess($message);
        $this->_redirect('*/*/index');
    }

    function showOptionAction(){
        $value = $this->getRequest()->getParams();
        $catid = $value["catid"];
        $html ='<select name="attribute_option" class="required-entry">';
        $html.='<option value="0">Select an attribute</option>';
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', "$catid");
        if(count($attribute->getSource()->getAllOptions(true))>0){
            foreach ($attribute->getSource()->getAllOptions(true) as $option) {
                if($option['value']>0){
                    $selected = '';
                    $html.= '<option '.$selected.' value="'.$option['value'].'">'.$option['label'].'</option>';
                }
            }//end foreach
        }

        $html.='</select>';
        echo $html;
        exit();
    }

    public function deleteAction(){
        $ids = $this->getRequest()->getParam('delivery_id');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Date(s)'));
        } else {
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            try {
                $i = 0;
                foreach ($ids as $id) {
                    $write->delete(
                        "delivery_relationship",
                        "delivery_id=".$id
                    );

                    $write->delete(
                        "delivery_price",
                        "delivery_id=".$id
                    );
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $message = $this->__('Delete successfully');
        Mage::getSingleton('core/session')->addSuccess($message);
        $this->_redirect('*/*/');
    }
}