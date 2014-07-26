<?php

class Zend_Controller_Action_Helper_Errorlog extends Zend_Controller_Action_Helper_Abstract {

	function direct($msg){
		$errMsg  = 'Date : ' . date("d-M-Y H:i:s") . "\n";
		$errMsg .= 'Message : ' . $msg . "\n";
		$errMsg .= 'Controller : ' . $this->getRequest()->getControllerName() . "\n";
		$errMsg .= 'Action : ' . $this->getRequest()->getActionName() . "\n";
		$errMsg .= '= = = = = = = = = = = = = = = = = = = = = = = = = ';
		$errMsg .= "\n";
		$fp = fopen('error_log.txt', 'a+');	fputs($fp, $errMsg);	fclose($fp);
	}
}