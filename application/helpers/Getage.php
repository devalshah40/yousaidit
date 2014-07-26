<?php

class Zend_Controller_Action_Helper_Getage extends Zend_Controller_Action_Helper_Abstract {

   function direct($birthday) {
      if(! empty($birthday)){
         $tmpArr = explode('-', $birthday);
         if(count($tmpArr) == 3){
            list($year, $month, $day) = explode("-", $birthday);

            $year_diff = date("Y") - (int)$year;

            $month_diff = date("m") - (int)$month;

            $day_diff = date("d") - (int)$day;

            if($day_diff < 0 || $month_diff < 0){
               $year_diff --;
            }
            return $year_diff;
         }else{
            return 0;
         }
      }else{
         return 0;
      }
   }
}