<?php
/**
*  Module: jQuery AJAX-ZOOM for Magento, /js/axzoom/lic.php
*  Copyright: Copyright (c) 2010-2016 Vadim Jacobi
*  License Agreement: http://www.ajax-zoom.com/index.php?cid=download
*  Version: 1.2.1
*  Date: 2016-05-15
*  Review: 2016-05-15
*  URL: http://www.ajax-zoom.com
*  Documentation: http://www.ajax-zoom.com/index.php?cid=modules&module=magento
*
*  @author    AJAX-ZOOM <support@ajax-zoom.com>
*  @copyright 2010-2016 AJAX-ZOOM, Vadim Jacobi
*  @license   http://www.ajax-zoom.com/index.php?cid=download
*/

$obj = SimpleXML_Load_String(implode('', file(dirname(dirname(dirname(__FILE__))).'/app/etc/local.xml')));
error_reporting(0);
$tmp = array();

if (function_exists('mysqli_connect')){
	$mysqli = mysqli_connect(preg_replace('/:[0-9]{1,}$/', '', (string)$obj->global->resources->default_setup->connection->host), (string)$obj->global->resources->default_setup->connection->username, (string)$obj->global->resources->default_setup->connection->password, (string)$obj->global->resources->default_setup->connection->dbname);
	$data_query = mysqli_query($mysqli, "SELECT `value` FROM `" . (string)$obj->global->resources->db->table_prefix . "core_config_data` WHERE `path` = 'axzoom_options/license/lic'");
	$data = mysqli_fetch_array($data_query);
	$tmp = unserialize($data['value']);
	mysqli_close($mysqli);
}
elseif (function_exists('mysql_connect')){
	$db_connect = mysql_connect(preg_replace('/:[0-9]{1,}$/', '', (string)$obj->global->resources->default_setup->connection->host), (string)$obj->global->resources->default_setup->connection->username, (string)$obj->global->resources->default_setup->connection->password);
	$db = mysql_select_db((string)$obj->global->resources->default_setup->connection->dbname, $db_connect);
	$data_query = mysql_query("SELECT `value` FROM `" . (string)$obj->global->resources->db->table_prefix . "core_config_data` WHERE `path` = 'axzoom_options/license/lic'");
	$data = mysql_fetch_array($data_query);
	$tmp = unserialize($data['value']);
	mysql_close($db_connect);
}

if (!empty($tmp)){
	foreach ($tmp as $key => $l){
		$zoom['config']['licenses'][$l['domain']] = array(
			'licenceType' => $l['type'],
			'licenceKey' => $l['license'],
			'error200' => $l['error200'],
			'error300' => $l['error300']
		);
	}
}
?>