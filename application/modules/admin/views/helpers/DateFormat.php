<?php
class Zend_View_Helper_dateformat{
function dateFormat($dt, $rval = null, $format = null){
    	if( !empty($dt) && $dt != '0000-00-00'){

    		$time = date('h:i A', strtotime($dt));

    		if( !empty($format) ){
				$dt = date($format, strtotime($dt));    			
    		}else{
    			$dt = date('jS M Y', strtotime($dt));	
    		}

			if( $rval == null){
				return $dt;
			}else{
				return $dt . " " . $time;
			}
    	}else{
    		return '--';
    	}
    }
}