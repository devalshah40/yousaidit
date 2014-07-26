<?php
class Default_Model_SurveyQuestion extends Zend_Db_Table {
	protected $_name = 'survey_question';
	public function listSurveyQuestions($sid, $mid = null, $section = 'admin'){
	    if( !empty($mid)){
	       $sql_1  = ', ( Select r.response_id
        			 		from response r
        			 		where r.sq_id = sq.sq_id and
        			 		customer_id = ' . $mid . ') as response_id ';
	    }else{
	       $sql_1 = '';
	    }
	    
	    $sql_1 = '';

        $select = 'Select q.*, sq.sq_id, sq.sort_order, sq.response_required ' . $sql_1 . '
	        			from ' . $this->_name . ' sq, question q
                    where
                    	sq.question_id = q.question_id and
                    	sq.survey_id = ' . $sid;
        
        if( $section != 'admin'){
            $select .= '  and q.status = "Active" ';
        }
        
        $select .= ' order by sq.sort_order ASC';
        
		try {
			$row = $this->getDefaultAdapter()->fetchAll($select);
			if(count($row) > 0) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.listSurveyQuestions : " . $e->getMessage());
		}
		return false;
    }
    public function listAvailableQuestions($sid,$cid){
        $sub_select = 'Select
                          question_id
                       from
                          ' . $this->_name . '
                       where
                          survey_id = ' . $sid;

        $select     = "Select
                          *
                       from
                          question
                       where
                          question_id NOT IN ( ".$sub_select." ) and ( client_id = $cid or client_id = 0 ) order by question_id desc";

		try {
			$result = $this->getDefaultAdapter()->fetchAll($select);
			if(count($result) > 0) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.listAvailableQuestions : " . $e->getMessage());
		}
		return false;
    }
    public function addSurveyQuestion($que_id, $survey_id, $sort_order = 0){
        $data = array('survey_id' => $survey_id, 'question_id' => $que_id, 'sort_order' => $sort_order);
		try {
			$sq_id = $this->insert($data);
			if($sq_id) {
				return $sq_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.addSurveyQuestion : " . $e->getMessage());
		}
		return false;
    }
    public function removeSurveyQuestion($que_id, $survey_id){
        $where = 'survey_id = ' . $survey_id .  ' and question_id = ' . $que_id;
		try {
			$result = $this->delete($where);
			if($result) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.removeSurveyQuestion : " . $e->getMessage());
		}
		return false;
    }
    public function updateSurveyQuestionSortOrder($sq_id, $sort_order){
        $data = array('sort_order' => $sort_order);
        $where = 'sq_id = ' . $sq_id;

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.updateSurveyQuestionSortOrder : " . $e->getMessage());
		}
		return false;
    }
    public function updateSurveyQuestionResponseFlag($sq_id, $flag){

        $data = array('response_required' => $flag);
        
        $where = 'sq_id = ' . $sq_id;

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.updateSurveyQuestionResponseFlag : " . $e->getMessage());
		}

		return false;
    }
    public function getSurveyId($id){
	    $select = "Select distinct survey_id from survey_question where sq_id = " . $id;

        try {
			$row = $this->getDefaultAdapter()->fetchRow($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.getSurveyId : " . $e->getMessage());
		}
		return false;
	}
    public function getSurveyQueResponseStatus($id){
	    $select = $this->select()->where('sq_id = ?', $id);

        try {
			$row = $this->fetchRow($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("SurveyQuestion.getSurveyQueResponseStatus : " . $e->getMessage());
		}
		return false;
	}
}