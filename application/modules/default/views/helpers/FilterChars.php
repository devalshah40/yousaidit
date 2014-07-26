<?php
class Zend_View_Helper_FilterChars {
    function filterchars($str) {
		$sp_chars = array(' ', '`', '"', '-', '&', '?', '^', '*', ')', '(', '#', '@', '~', '+', '\'');
		if( !empty($str) ){
			return str_replace($sp_chars, '_', $str);
		}else{
			return '';
		}
    }
}