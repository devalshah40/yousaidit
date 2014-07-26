<?php
class Default_Model_Response extends Zend_Db_Table {
	protected $_name = 'response';
	public function insertSurveyResponses($post) {
//	    $query = "Insert into response(`member_id`, `sq_id`, `answer_id`, `answer_text`, `datetime`)
//	    				values(	".$post['member_id'].",
//	    						".$post['sq_id'].",
//	    						".$post['answer_id'].",
//	    						'".$post['answer_text']."',
//	    						'".date("Y-m-d H:i:s")."' )
//	    					ON DUPLICATE KEY UPDATE
//								`answer_id` = ".$post['answer_id'].",
//								`answer_text` = '" . $post['answer_text'] . "',
//								`updated_datetime` = '" . date("Y-m-d H:i:s") . "'";

		$data = array( //	'cs_id'        => $post['cs_id'],
						'user_id'  =>    $post['user_id'],
						'sq_id'        => $post['sq_id'],
						'answer_id'    => $post['answer_id'],
						'answer_text'  => $post['answer_text'],
						'datetime'	   => date("Y-m-d H:i:s")          	);

		try {
			$response_id = $this->insert($data);
			//$response_id = $this->getDefaultAdapter()->query($query);
			if($response_id){
			    return $response_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.insertSurveyResponses : " . $post['sq_id']  . " - " .$e->getMessage());
		}
		return false;
	}
    public function updateSurveyResponses($post) {

		$data = array( 	'answer_id'    => $post['answer_id'],
						'answer_text'  => $post['answer_text'],
						'updated_datetime'	   => date("Y-m-d H:i:s")            );

		$where = 'response_id = ' . $post['response_id'];

		try {
			$result = $this->update($data, $where);
			if($result){
			    return true;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.updateSurveyResponses : " . $e->getMessage());
		}
		return false;
	}
	public function surveyTakenCount($sid){
	    $select = "Select
	                   count(distinct user_id) as cnt
	               from
	                   response
	               where
	                   sq_id IN ( Select
	                                   sq_id
	                              from
	                                   survey_question
	                              where
	                                   survey_id = ".$sid.") ";

        try {
			$row = $this->getDefaultAdapter()->fetchRow($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.surveyTakenCount : " . $e->getMessage());
		}
		return false;
    }
    public function getSurverAnswerText($sid, $aid, $sqid, $pArr = null){

        $sql_addon = null;
        
	    if( !empty($pArr)){
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'a'){
                $sql_addon .= " and cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.agent_id = '".$pArr[0]['value1']."' )";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 't'){
                $sql_addon .= " and cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.team_id = '".$pArr[0]['value1']."' )";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'f'){
                $sql_addon .= " and cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.type = '".$pArr[0]['value1']."' )";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'c'){
                $sql_addon .= " and r.customer_id = '".$pArr[0]['value1']."'";
            }
            if($pArr['filter'] == 'dt'){
                $st = date("Y-m-d", strtotime($pArr['value']['st']));
                $en = date("Y-m-d", strtotime($pArr['value']['en']));
                
                $sql_addon .= " and date_format(datetime,'%Y-%m-%d') between '".$st."' and '".$en."' ";
            }
        }

       $select = "SELECT
       					m.title, m.firstname, m.surname, r.answer_text, r.datetime
                  FROM
                  		customer m, response r
                  WHERE
                  		m.customer_id = r.customer_id
                     	AND r.sq_id IN (	SELECT sq_id
                     						FROM survey_question
                     						WHERE survey_id =  '".$sid."'
											AND question_id = '".$sqid."'	) " . $sql_addon . "
                     	AND r.answer_id =  '".$aid."'
                     	AND r.answer_text != ''
                     	ORDER BY r.datetime DESC   ";
       
       //echo $select;
       
        try {
			$result = $this->getDefaultAdapter()->fetchAll($select);
			if($result) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.getSurverAnswerText : " . $e->getMessage());
		}
		return false;
    }
    public function memberSurveyTaken($sid){
//        $select = "Select
//                      distinct m.customer_id, concat(m.title, ' ', m.firstname, ' ', m.surname) as name, m.*
//                   from
//                      " . $this->_name . " r, customer m
//                   where
//                      r.customer_id = m.customer_id and
//                      sq_id in ( select
//                                    sq_id
//                                 from
//                                    survey_question
//                                 where
//                                    survey_id = " . $sid . ")";
        // 22 jun 2012 --
        $select = "	select
        				c.*
        			from
        				customer_survey cs, customer c
        			where
        				cs.customer_id = c.customer_id and
        				cs.survey_taken_date is not null and
        				cs.completed = 'y' and
        				cs.survey_id = " . $sid . " order by c.customer_id";
        
        try {
			$row = $this->getDefaultAdapter()->fetchAll($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.memberSurveyTaken : " . $e->getMessage());
		}
		return false;
    }
    public function queResponseCount($sid, $qid, $aid = null, $pArr = null, $que_response_type = null){
        if( empty($que_response_type) ){
            $field = ' response_id ';
        }else{
            if( $que_response_type == 'm'){
                $field = ' distinct cs_id ';
            }
        }
        
       $select = "Select
                      count(".$field.") as cnt
                   from
                      " . $this->_name . "
                   where
                      sq_id in ( select
                                    sq_id
                                 from
                                    survey_question where survey_id = '".$sid."' and question_id = " . $qid . " ) ";
        
        if( !empty($aid)) {
            $select .= " and answer_id = '".$aid."'";
        }
        
        if( !empty($pArr)){
           
            if($pArr['filter'] == 'dt'){
                $st = date("Y-m-d", strtotime($pArr['value']['st']));
                $en = date("Y-m-d", strtotime($pArr['value']['en']));
                
                $select .= " and date_format(datetime,'%Y-%m-%d') between '".$st."' and '".$en."' ";
            }
        }
        
        //if( $qid == 29){
            //echo 'queResponseCount : ' . $select . '<br><br>';
        //}
    // echo  $select; 
        try {
			$row = $this->getDefaultAdapter()->fetchRow($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.queResponseCount : " . $e->getMessage());
		}
		return false;
    }
    public function getAnswerDetails($sid, $qid, $aid, $ans_text = null, $pArr = null){
        $select = " Select
        				date_format(datetime, '%e %b %Y %l:%i %p') as datetime,
        				answer_text,
        				(Select concat(ref_no, '|',  date_format(date_time, '%e %b %Y %l:%i %p') ) from faults where fault_id =
							( Select fault_id from customer_survey where cs_id =
    							( Select cs_id from response where response_id = r.response_id ) ) ) as fault_ref_no_date_time,
 						(Select concat(title, ' ', firstname, ' ', surname) from customer where customer_id = r.customer_id ) as customer_name,
 						(Select name from faults, team where faults.team_id = team.team_id and fault_id = ( Select fault_id from customer_survey where cs_id = r.cs_id ) ) as team_name,
 						(Select name from faults, agent where faults.agent_id = agent.agent_id and fault_id = ( Select fault_id from customer_survey where cs_id = r.cs_id ) ) as agent_name
 					from
 						" . $this->_name . " r
 					where
                      r.sq_id in ( select
                                    sq_id
                                 from
                                    survey_question where survey_id = ".$sid." and question_id = " . $qid . ") and
                      r.answer_id = " . $aid;
        
        if( !empty($ans_text)){
            $select .= " and r.answer_text = '".$ans_text."'";
        }
        
        if( !empty($pArr)){
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'a'){
                $select .= " and r.cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.agent_id = '".$pArr[0]['value1']."' )  ";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 't'){
                $select .= " and r.cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.team_id = '".$pArr[0]['value1']."' )  ";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'f'){
                $select .= " and r.cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.type = '".$pArr[0]['value1']."' )";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'c'){
                $select .= " and r.customer_id = '".$pArr[0]['value1']."' ";
            }
            if($pArr['filter'] == 'dt'){
                $st = date("Y-m-d", strtotime($pArr['value']['st']));
                $en = date("Y-m-d", strtotime($pArr['value']['en']));
                
                $select .= " and date_format(r.datetime,'%Y-%m-%d') between '".$st."' and '".$en."' ";
            }
            //echo $select . "<br><br>";
        }
 							
		try {
			$row = $this->getDefaultAdapter()->fetchAll($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.getAnswerDetails : " . $e->getMessage());
		}
		return false;
    }
    public function ansSelectedCount($sid, $qid, $aid, $ans_text = null, $pArr = null){
        
        $select = "Select
                      count(response_id) as cnt
                   from
                      " . $this->_name . "
                   where
                      sq_id in ( select
                                    sq_id
                                 from
                                    survey_question where survey_id = ".$sid." and question_id = " . $qid . ") ";

        if( !empty($aid)){
            $select .= " and answer_id = " . $aid;
        }
        
        if( $ans_text != ''){
            $select .= " and answer_text = '".$ans_text."'";
        }
        
        if( !empty($pArr)){
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'a'){
                $select .= " and cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.agent_id = '".$pArr[0]['value1']."' )  ";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 't'){
                $select .= " and cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.team_id = '".$pArr[0]['value1']."' )  ";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'f'){
                $select .= " and cs_id in( Select cs.cs_id from customer_survey cs, faults f where cs.fault_id = f.fault_id and cs.survey_id = '".$sid."' and f.type = '".$pArr[0]['value1']."' )";
            }
            if(!empty($pArr[0]['filter1']) && $pArr[0]['filter1'] == 'c'){
                $select .= " and customer_id = '".$pArr[0]['value1']."' ";
            }
            if($pArr['filter'] == 'dt'){
                $st = date("Y-m-d", strtotime($pArr['value']['st']));
                $en = date("Y-m-d", strtotime($pArr['value']['en']));
                
                $select .= " and date_format(datetime,'%Y-%m-%d') between '".$st."' and '".$en."' ";
            }
        }
        //echo $select . "<br><Br>";
        try {
			$row = $this->getDefaultAdapter()->fetchRow($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.ansSelectedCount : " . $e->getMessage());
		}
		return false;
    }
    public function ansSelected($sid, $qid, $aid, $mid){
        $select = "Select
                      *
                   from
                      " . $this->_name . "
                   where
                      sq_id in ( select
                                    sq_id
                                 from
                                    survey_question where survey_id = ".$sid." and question_id = " . $qid . ") and
                      answer_id = " . $aid . " and customer_id = " . $mid;

        try {
			$result = $this->getDefaultAdapter()->fetchRow($select);
			if( $result ) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.ansSelected : " . $e->getMessage());
		}
		return false;
    }
    public function getCustomerSurveyResponses($cs_id, $customer_id, $sq_id, $answer_id){
        $select = $this->select('answer_text')->where('cs_id = ?', $cs_id)->where('customer_id = ?', $customer_id)->where('sq_id = ?', $sq_id)->where('answer_id = ?', $answer_id);
        if($answer_id == 101){
            //echo $select . "<br><br>";
        }
        try {
			$result = $this->fetchRow($select);
			if( !empty($result) ) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Response.getCustomerSurveyResponses : " . $e->getMessage());
		}
		return false;
    }
}