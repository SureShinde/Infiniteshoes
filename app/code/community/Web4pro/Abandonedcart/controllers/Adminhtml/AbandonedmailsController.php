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

class Web4pro_Abandonedcart_Adminhtml_AbandonedmailsController  extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }


    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('newsletter/web4pro_emails')
            ->_title($this->__('Newsletter'))->_title($this->__('Abandoned Cart Emails Sent'))
            ->_addBreadcrumb($this->__('Newsletter'), $this->__('Newsletter'))
            ->_addBreadcrumb($this->__('abandonedorder'), $this->__('Mails'));

        return $this;
    }

    /**
     *  Export csv action
     */
    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('web4pro_abandonedcart/adminhtml_abandonedmails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('web4pro_abandonedcart/adminhtml_abandonedmails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }


    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

}
