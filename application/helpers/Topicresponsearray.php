<?php

class Zend_Controller_Action_Helper_Topicresponsearray extends Zend_Controller_Action_Helper_Abstract {
    
    function getVoteCount($topic_id, $TopicModelReponse) {
        $Topic_userVoted = $TopicModelReponse->getVotesCount($topic_id);
        
        if (! empty($Topic_userVoted)){
            $user_response = array();
            
            for($rr = 1; $rr <= 5; $rr ++){
                $temp = 'rres_' . $rr;
                ${$temp} = null;
                $temp = 'rper_' . $rr;
                ${$temp} = null;
                $temp = 'rvot_' . $rr;
                ${$temp} = null;
            }
            
            foreach($Topic_userVoted as $uk => $uv){
                
                if ($uv['response'] == 1){
                    $rres_1 = $uv['response'];
                    $rper_1 = $uv['percent'];
                    $rvot_1 = $uv['votes'];
                }
                if ($uv['response'] == 2){
                    $rres_2 = $uv['response'];
                    $rper_2 = $uv['percent'];
                    $rvot_2 = $uv['votes'];
                }
                
                if ($uv['response'] == 3){
                    $rres_3 = $uv['response'];
                    $rper_3 = $uv['percent'];
                    $rvot_3 = $uv['votes'];
                }
                
                if ($uv['response'] == 4){
                    $rres_4 = $uv['response'];
                    $rper_4 = $uv['percent'];
                    $rvot_4 = $uv['votes'];
                }
                
                if ($uv['response'] == 5){
                    $rres_5 = $uv['response'];
                    $rper_5 = $uv['percent'];
                    $rvot_5 = $uv['votes'];
                }
                
                $user_response = array('rres_1' => $rres_1, 'rper_1' => $rper_1, 'rvot_1' => $rvot_1, 'rres_2' => $rres_2, 'rper_2' => $rper_2, 'rvot_2' => $rvot_2, 'rres_3' => $rres_3, 'rper_3' => $rper_3, 'rvot_3' => $rvot_3, 'rres_4' => $rres_4, 'rper_4' => $rper_4, 'rvot_4' => $rvot_4, 'rres_5' => $rres_5, 'rper_5' => $rper_5, 'rvot_5' => $rvot_5);
            
            }
            
            return $user_response;
        }
    }

    function direct($votes, $TopicModelReponse) {
        
        $this->trModel = $TopicModelReponse;
        
        $arrTopic2 = array();
        foreach($votes as $voted){
            $topic_tmp = $this->getVoteCount($voted['topic_id'], $TopicModelReponse);
            $arrTopic2[] = array('topic_id' => $voted['topic_id'], 'topic_title' => $voted['name'], 'responses' => $topic_tmp);
        }
        return $arrTopic2;
    }
}