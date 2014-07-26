<?php
	error_reporting(E_ALL);

	define('DS', DIRECTORY_SEPARATOR);

	date_default_timezone_set('Asia/Calcutta');

// 	 set_include_path(implode(PATH_SEPARATOR, array(	    realpath('..' . DS . '..' . DS . '..' . DS . '..' . DS .'ZendServer' . DS .'share' . DS .'ZendFramework' . DS .'library'),
// 													    get_include_path()								)));
	
	defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . DS . '..' . DS .'application'));
	set_include_path(get_include_path() . PATH_SEPARATOR . APPLICATION_PATH . '/../library');
	defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

	require_once 'Zend'.DS.'Application.php';

	$application = new Zend_Application( APPLICATION_ENV, APPLICATION_PATH . DS . 'configs' . DS . 'application.ini' );

	$config 	= new Zend_Config_Ini(APPLICATION_PATH . DS . 'configs' . DS .'application.ini', 'production');

	Zend_Registry::set('config', $config);

	try {

		$application->bootstrap();

		$application->run();

	}catch (Exception $e){

		echo $e->getMessage();

	}