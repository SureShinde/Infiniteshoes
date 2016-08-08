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
class Web4pro_Abandonedcart_Adminhtml_AbandonedorderController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/web4pro_abandonedcart')
            ->_title($this->__('Sales'))->_title($this->__('Abandoned'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__('abandonedorder'), $this->__('Abandoned'));

        return $this;
    }

    public function exportCsvAction()
    {
        $fileName   = 'orders.csv';
        $grid       = $this->getLayout()->createBlock('web4pro_abandonedcart/adminhtml_abandonedorder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName   = 'orders.xml';
        $grid       = $this->getLayout()->createBlock('web4pro_abandonedcart/adminhtml_abandonedorder_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function gridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function dashboardAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('dashboard/web4pro_abandonedcart')
            ->_title($this->__('Dashboard'))->_title($this->__('Abandoned'))
            ->_addBreadcrumb($this->__('Dashboard'), $this->__('Dashboard'))
            ->_addBreadcrumb($this->__('abandonedorder'), $this->__('Abandoned'))
            ->renderLayout();

    }

    public function ajaxBlockAction()
    {
        $output   = '';
        $blockTab = $this->getRequest()->getParam('block');
        if ($blockTab =='totals') {
            $output = $this->getLayout()->createBlock('web4pro_abandonedcart/adminhtml_dashboard_' . $blockTab)->toHtml();
        }
        $this->getResponse()->setBody($output);
        return;
    }
}
