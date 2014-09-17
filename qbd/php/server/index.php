<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

ini_set( 'soap.wsdl_cache_enabled', '0' );

require_once('classes/base/qb.php');
require_once('classes/base/qbwc.php');

require_once('classes/base/server.php');
require_once('classes/entity/customer.php');
require_once('classes/entity/invoice.php');

$server = new SoapServer('soap/qb.wsdl');
$server->setClass("QBServerClass");
$server->handle();
