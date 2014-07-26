<?php

class Default_Model_QuestionAnswer extends Zend_Db_Table {

	protected $_name = 'que_answer';

    public function insertQueAns($qid, $aid, $free_text_caption) {
		$data = array( 	'question_id'  => $qid,
						'answer_id'    => $aid,
		                'custom_free_text_caption' => $free_text_caption);

		try {
			$qa_id = $this->insert($data);

			if($qa_id){
			    return $qa_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.insertQueAns : " . $e->getMessage());
		}
		return false;
	}

	public function updateQueAns($post) {
		$data = array( 	'question_id'  => $post['question_id'],
						'answer_id'    => $post['answer_id']     	);

        $where = ' qa_id = ' . $post['qaid'];

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.updateQueAns : " . $e->getMessage());
		}
		return false;
	}

	public function deleteQueAns($id) {
        $where = ' qa_id = ' . $id;
		try {
			$result = $this->delete($where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.deleteQueAns : " . $e->getMessage());
		}
		return false;
	}

	public function deleteQueAnsAssociation($id) {
        $where = ' question_id = ' . $id;
		try {
			$result = $this->delete($where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.deleteQueAnsAssociation : " . $e->getMessage());
		}
		return false;
	}

	public function queansInfo($id) {
	    $select = $this->select()->where(' qa_id =  ?', $id);

		try {
			$result = $this->fetchRow($select);
            if($result){
                return $result;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.queansInfo : " . $e->getMessage());
		}
		return false;
	}

    /**
     *
     * Function to check if question is exsting in question_answer table or not ...
     * @param integer $id
     */
    public function questionInfo($id) {
	    $select = $this->select()->where('question_id = ?', $id);

		try {
			$result = $this->fetchRow($select);
            if($result){
                return $result;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.questionInfo : " . $e->getMessage());
		}
		return false;
	}

	public function listQueAns($sort_field, $que_id ) {
        $select = "Select qa.*, a.* from " . $this->_name . " qa, answer a
                        where qa.answer_id = a.answer_id and
                            qa.question_id = " . $que_id . " order by " . $sort_field;

		try {
			$row = $this->getDefaultAdapter()->fetchAll($select);
			if(count($row) > 0) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.listQueAns : " . $e->getMessage());
		}
		return false;
	}

	public function getSelectedAnsInfo($member_id, $survey_id, $question_id, $answer_id ){
	   $select = "Select * from response
	   				where
	   					user_id = ".$member_id." and
	   					answer_id = ".$answer_id." and
	   					sq_id in ( Select sq_id from survey_question
	   								where
										survey_id = ".$survey_id." and
										question_id IN ( select question_id from que_answer
															where
																question_id = ".$question_id." and
																answer_id = ".$answer_id." ))";

	   //echo $select . "<br><br>";

		try {
			$row = $this->getDefaultAdapter()->fetchRow($select);
			if($row) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.getSelectedAnsInfo : " . $e->getMessage());
		}
		return false;
	}

	public function existingAns($qid, $aid) {
	    $select = $this->select()->where('question_id =  ?', $qid)->where('answer_id = ?', $aid);

		try {
			$result = $this->fetchRow($select);
            if($result){
                return true;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.existingAns : " . $e->getMessage());
		}
		return false;
	}

	public function updateAnswerSortOrder($qa_id, $sort_order, $answer_value, $free_text_caption = null){
        $data = array('sort_order' => $sort_order);
	     if( !empty($answer_value) ){
            $caption1 = array('answer_value' => $answer_value);
            $data = array_merge($data, $caption1);
        }
        
        if( !empty($free_text_caption) ){
            $caption = array('custom_free_text_caption' => $free_text_caption);
            $data = array_merge($data, $caption);
        }

        $where = 'qa_id = ' . $qa_id;

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionAnswer.updateAnswerSortOrder : " . $e->getMessage());
		}
		return false;
    }
}