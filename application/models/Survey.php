<?php

class Default_Model_Survey extends Zend_Db_Table {
    
    protected $_name = 'survey';

    public function insertSurvey($post) {
        $data = array('title' => $post['title'], 'description' => $post['description'], 'invitation_msg' => $post['invitation_msg'], 'completion_msg' => $post['completion_msg'], 'add_date' => date("Y-m-d"), 'token' => $post['token'], 'status' => $post['status']);
        
        if (! empty($post['start_date']) && ! empty($post['end_date'])){
            $arr = array('start_date' => $post['start_date'], 'end_date' => $post['end_date']);
            $data = array_merge($data, $arr);
        }
        
        try{
            $survey_id = $this->insert($data);
            
            if ($survey_id){
                return $survey_id;
            }
        
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.insertSurvey : " . $e->getMessage());
        }
        return false;
    }

    public function updateSurvey($post, $section = null) {
        $data = array('title' => $post['title'], 'description' => $post['description'], 'invitation_msg' => $post['invitation_msg'], 'completion_msg' => $post['completion_msg'], 'updated_datetime' => date("Y-m-d H:i:s"));
        
        if (! empty($post['start_date']) && ! empty($post['end_date'])){
            $arr = array('start_date' => $post['start_date'], 'end_date' => $post['end_date']);
            $data = array_merge($data, $arr);
        }
        
        $arr = array('status' => $post['status']);
        $data = array_merge($data, $arr);
        
        $where = ' survey_id = ' . $post['id'];
        
        try{
            $result = $this->update($data, $where);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.updateSurvey : " . $e->getMessage());
        }
        return false;
    }

    public function deleteSurvey($sid) {
        $this->getDefaultAdapter()->beginTransaction();
        
        $select = "	Select
       					sq_id
       				from
       					survey_question
       				where
       					survey_id = " . $sid . " ";
        
        $condition = "sq_id in (" . $select . ")";
        
        try{
            $result = $this->getDefaultAdapter()->delete('response', $condition);
            try{
                $condition = "survey_id = '" . $sid . "'";
                $result = $this->getDefaultAdapter()->delete('survey_question', $condition);
                
                try{
                    $where = ' survey_id = ' . $sid;
                    $result = $this->delete($where);
                    $this->getDefaultAdapter()->commit();
                    return true;
                } catch(Exception $e){
                    $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
                    $errlog->direct("Survey.deleteSurvey - survey : " . $e->getMessage());
                }
                $this->getDefaultAdapter()->rollBack();
                return false;
            } catch(Exception $e){
                $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
                $errlog->direct("Survey.deleteSurvey - survey question delete : " . $e->getMessage());
            }
            $this->getDefaultAdapter()->rollBack();
            return false;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.deleteSurvey - response delete : " . $e->getMessage());
        }
        $this->getDefaultAdapter()->rollBack();
        return false;
    }

    public function getActiveSurveyInfo() {
        $select = "Select * from " . $this->_name . " where status = 'Active' order by survey_id limit 0, 1";
        
        try{
            $result = $this->getDefaultAdapter()->fetchRow($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.surveyInfo : " . $e->getMessage());
        }
        return false;
    }

    public function surveyInfo($id) {
        if ((int) $id != 0){
            $select = $this->select()->where(' survey_id =  ?', $id);
        } else{
            $select = $this->select();
        }
 
        try{
            $result = $this->fetchRow($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.surveyInfo : " . $e->getMessage());
        }
        return false;
    }

    public function getfrontendSurvey($status) {
        $select = $this->select()->where(' status =  ?', $status);
        try{
            $result = $this->fetchRow($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.getfrontendSurvey : " . $e->getMessage());
        }
        return false;
    }

    // updates status inactive active ..
    

    public function surveyupdatStatus($id, $status) {
        $data = array('status' => $status);
        $select = "survey_id = " . $id;
        
        try{
            $result = $this->update($data, $select);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.surveyupdatStatus : " . $e->getMessage());
        }
        return false;
    }

    public function listSurveyAdmin($sort_field = null, $field = null, $value = null) {
        if (! empty($field) && ! empty($value)){
            $select = $this->select()->where($field . " like " . "'%" . $value . "%'")->order($sort_field);
        } else{
            $select = $this->select()->order($sort_field);
        }
        //echo $select; exit;
        try{
            $row = $this->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.listSurveyAdmin : " . $e->getMessage());
        }
        return false;
    }

    public function listSurvey($sort_field = null, $field = null, $value = null, $search_flag = false) {
        $client_type = null;
        if (! empty($value)){
            $arr = explode("^", $value);
            if (count($arr) == 2){
                $value = $arr[0];
                $client_type = $arr[1];
            } else{
                $value = $arr[0];
                $client_type = 'C';
            }
        } else{
            $client_type = 'C';
        }
        
        if (! empty($field) && ! empty($value) && $search_flag == false){
            $select = $this->select()->where($field . " like " . "'%" . $value . "%'")->order($sort_field);
        } else{
            if ($search_flag == true){
                $select = $this->select()->where($field . " = " . "'" . $value . "'")->order($sort_field);
            } else{
                $select = $this->select()->order($sort_field);
            }
        }
        echo $select;
        exit();
        try{
            $row = $this->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.listSurvey : " . $e->getMessage());
        }
        return false;
    }

    public function surveyResult($id) {
        
        $sql1 = "Select
	    				count(r1.response_id)
	    		   from
	    		   	    response r1
	    		   where
	    		        r1.member_id = " . $id . " AND
	    				r1.sq_id IN (
								 Select
	                                  sq.sq_id
	                             from
	                                  survey_question sq
                                 where
                                 	  sq.survey_id = s.survey_id ) ";
        
        $select1 = "Select * from (
    		            Select
    						s.*, (" . $sql1 . ")  as response_count
          	            from
          	            	" . $this->_name . " s
          	            where
          	            	end_date < curdate() and

          	            	responses = 'Public' and

          	            	response_permission = 'M' and

          	            	s.type = 'NS' and

          	               	s.survey_id IN ( Select distinct survey_id from survey_question ) and

          	               	s.status = 'Active' ) as a ";
        
        $select2 = "Select * from (
    					Select
    						s.*, (" . $sql1 . ")  as response_count
          	            from
          	            	" . $this->_name . " s
          	            where
          	            	end_date < curdate() and

          	            	responses = 'Public' and

          	            	s.type = 'NS' and

          	               	s.survey_id IN ( Select distinct survey_id from survey_question ) and

          	               	s.status = 'Active' ) as a where a.response_count > 0 ";
        
        $select = $select1 . " union " . $select2;
        
        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.surveyResult : " . $e->getMessage());
        }
        return false;
    }

    public function listMemberSurvey($id, $answered_flag) {
        
        $sql1 = "Select
	    				count(r1.response_id)
	    		   from
	    		   	    response r1
	    		   where
	    		        r1.member_id = " . $id . " AND
	    				r1.sq_id IN (
								 Select
	                                  sq.sq_id
	                             from
	                                  survey_question sq
                                 where
                                 	  sq.survey_id = s.survey_id ) ";
        
        $sql2 = "Select
	    				count(sq.sq_id)
	    		   from
	    		   	    survey_question sq
	    		   where
						sq.survey_id = s.survey_id ";
        
        $sql3 = "	Select
	    				a.sq_id
	    			from ( SELECT
	    						sq.survey_id, sq.sq_id, (   Select
																max(response_id)
					  										from
					  											response
					  										where
					  											member_id = " . $id . "
					  											and sq_id = sq.sq_id ) as response_id
						   from
								survey_question sq ) as a
					where
						a.survey_id = s.survey_id and
						a.response_id is NULL
						order by a.sq_id LIMIT 0, 1";
        
        $sql4 = null;
        
        if (! empty($answered_flag)){
            $sql4 = ' where a.response_count > 0 and a.survey_id not in (73, 74) ';
            //$sql4 = ' where a.response_count > 0';
            // remove comment from the above line no (357) and delete line number 356
            // after temporary fix of 'Permanent' survey is lifted ... jigar 09 Nov 2011
            

            $sql7 = '';
            $order_by = ' a.response_date_time DESC ';
        } else{
            $sql4 = ' where a.response_count = 0';
            $sql7 = " '" . date('Y-m-d h:i') . "' between s.start_date and s.end_date and ";
            $order_by = ' a.start_date DESC ';
        }
        
        $sql5 = "select
					max(datetime)
				from
					response
				where
					sq_id in ( select
									sq_id
		   					   from
									survey_question
		   					   where
									survey_id = s.survey_id ) and member_id = " . $id . " ";
        $sql6 = "Select
					action
				from
					survey_invitations
				where
					survey_id = s.survey_id and member_id = " . $id . " ";
        
        $select = "Select * from (
	    			   Select
	    			   		s.*, (" . $sql1 . ")  as response_count,
	    			   		(" . $sql2 . ") as question_count,
	    			   		(" . $sql3 . ") as next_question,
		    			    (" . $sql5 . ") as response_date_time,
		    			    (" . $sql6 . ") as accept_flag
      	               from " . $this->_name . " s

      	               where " . $sql7 . "

      	               s.survey_id IN ( Select distinct survey_id from survey_question ) and

      	               s.survey_id NOT IN (Select survey_id from survey_invitations where member_id = " . $id . " and action = 'R' ) and
      	               
      	               s.status = 'Active' ) as a " . $sql4;
        
        //s.status = 'Active' ) as a " . $sql4 . " order by " . $order_by;
        // remove comment from the above line no (400) and delete line number 398
        // after temporary fix of 'Permanent' survey is lifted ... jigar 09 Nov 2011
        

        // * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * %
        

        if (empty($answered_flag)){
            $select1 = "Select * from (
    	    			   Select
    	    			   		s.*, 0  as response_count,
    	    			   		(" . $sql2 . ") as question_count,
    	    			   		(" . $sql3 . ") as next_question,
    		    			    (" . $sql5 . ") as response_date_time,
    		    			    'A' as accept_flag
          	               from " . $this->_name . " s
    
          	               where
    
          	               s.survey_id IN (73, 74) and
          	               
          	               s.status = 'Active' ) as a ";
            
            $select = "select * from ( " . $select . " union " . $select1 . " ) as a order by " . $order_by;
        }
        
        // * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * % * %
        

        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Survey.listMemberSurvey : " . $e->getMessage());
        }
        return false;
    }

}