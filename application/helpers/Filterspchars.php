<?php
class Zend_Controller_Action_Helper_Filterspchars extends Zend_Controller_Action_Helper_Abstract {
    function direct($str) {
		if( !empty($str)){
			return iconv('UTF-8','ASCII//TRANSLIT',$str);
		}else{
			return '';
		}
    }
}