<?php

class Zend_Controller_Action_Helper_Membersurvey extends Zend_Controller_Action_Helper_Abstract {

    function direct($surveyList) {

        //$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
      //  $x = print_r($session_data, true);   $errlog->direct('11 -> ' . $x);

     //   $member_id = (int) $session_data['user_id'];

        $surveyArr = array();

//        if (! empty($surveyList) && empty($answered_flag)){
//
//
//            foreach($surveyList as $k => $v){
//
//                $flag = false;
//  
//            }
//        }

        if (! empty($surveyList) ){
            foreach($surveyList as $k => $v){
                array_push($surveyArr, $v);
            }
        }

        return $surveyArr;

    }
}