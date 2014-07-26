<?php

class Zend_Controller_Action_Helper_Sessioncheck extends Zend_Controller_Action_Helper_Abstract {

	private $_redirect			= null;

	function direct($session_data, $login_flag){

		$modulename = $this->getRequest()->getModuleName();
		$controller = $this->getRequest()->getControllerName();
		$action 	= $this->getRequest()->getActionName();

		if(	$controller == 'login' && ( $action == 'index' || $action == 'process' ) ) {
    		if( $action == 'index'){
	    		if ( empty($session_data) == false && $login_flag == true ) {
	   				$this->_redirect = $modulename;
	   				return false;
	    		}
    		}
    	}else{
			if ( empty($session_data) || $login_flag == false ) {
   				$this->_redirect = $modulename . '/login/index';
   				return false;
    		}
    	}
    	return true;
	}

	function getredirecturl() {
		return $this->_redirect;
	}

}