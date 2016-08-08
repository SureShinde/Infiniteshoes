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

$installer = $this;

$installer->startSetup();

$conn->addColumn($installer->getTable('sales_flat_quote'), 'web4pro_abandonedcart_counter', 'INT(11) NULL DEFAULT 0');

$conn->addColumn($installer->getTable('sales_flat_quote'), 'web4pro_abandonedcart_flag', 'INT(11) NULL DEFAULT 0');

$installer->getConnection()->addColumn(
    $installer->getTable('sales_flat_quote'), 'web4pro_abandonedcart_token', 'varchar(255)', null, array('default' => 'null')
);


$conn->addColumn($installer->getTable('sales_flat_order'), 'web4pro_abandonedcart_counter', 'INT(11) NULL DEFAULT 0');


$conn->addColumn($installer->getTable('sales_flat_order'), 'web4pro_abandonedcart_flag', 'INT(11) NULL DEFAULT 0');

$installer->run("
	CREATE TABLE IF NOT EXISTS `{$this->getTable('web4pro_mails_sent')}` (
	  `id` INT(10) unsigned NOT NULL auto_increment,
	  `store_id` smallint(5),
	  `mail_type` ENUM('abandoned cart','new order', 'wishlist') NOT NULL,
	  `customer_email` varchar(255),
	  `customer_name` varchar(255),
	  `coupon_number` varchar(255),
	  `coupon_type` smallint(2),
	  `coupon_amount` decimal(10,2),
      `sent_at` DATETIME NOT NULL ,
	  PRIMARY KEY  (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$installer->endSetup();