<?php

class Zend_Controller_Action_Helper_Randomstringgenerator extends Zend_Controller_Action_Helper_Abstract {

	function direct($len = 20){
		$base 	= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghjkmnpqrstwxyz123456789';
		$max  	= strlen($base)-1;
		$string = '';

		mt_srand((double) microtime() * 1000000);

		while(strlen($string) < $len)
		  $string .= $base{mt_rand(0, $max)};

		return $string;
	}
}