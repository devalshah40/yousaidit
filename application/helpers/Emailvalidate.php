<?php

class Zend_Controller_Action_Helper_Emailvalidate extends Zend_Controller_Action_Helper_Abstract {

	function direct($email){
	    $pattern = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' . '(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i';
		if (!preg_match($pattern , $email)) {
			return false;
		}
		return true;
	}
}