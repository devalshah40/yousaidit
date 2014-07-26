<?php

class Default_Model_Answer extends Zend_Db_Table {

	protected $_name = 'answer';

    public function insertAnswer($post) {
		$data = array( 	'answer_type'  => $post['answer_type'],
						'weightage'    => !empty($post['weightage'])?$post['weightage']:0,
						'answer_text'  => $post['answer_text'],
						'free_text'    => $post['free_text'],
						'free_text_caption'  => ($post['free_text'] == 'n')?null:$post['free_text_caption'],
						'help_text'          => !empty($post['help_text'])?$post['help_text']:null,
						'added_by'	         => $post['added_by'],
						'add_date'           => date("Y-m-d"),
						'status'	         => $post['status']		);

		try {
			$answer_id = $this->insert($data);
			if($answer_id){
			    return $answer_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.insertAnswer : " . $e->getMessage());
		}
		return false;
	}

	public function updateAnswer($post) {

		$data = array(  'weightage'        => $post['weightage'],
		                'answer_type'      => $post['answer_type'],
						'answer_text'      => $post['answer_text'],
						'free_text'        => $post['free_text'],
						'free_text_caption'  => ($post['free_text'] == 'n')?null:$post['free_text_caption'],
						'help_text'          => !empty($post['help_text'])?$post['help_text']:null,
						'updated_by'         => $post['updated_by'],
						'updated_datetime'   => date("Y-m-d H:i:s"),
						'status'	         => $post['status']		);

        $where = ' answer_id = ' . $post['aid'];

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.updateAnswer : " . $e->getMessage());
		}
		return false;
	}

	public function deleteAnswer($id) {
        $where = ' answer_id = ' . $id;
		try {
			$result = $this->delete($where);
			if($result){
				return true;
			}

		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.deleteAnswer : " . $e->getMessage());
		}
		return false;
	}

	public function answerInfo($id) {
	    $select = $this->select()->where(' answer_id =  ?', $id);

		try {
			$result = $this->fetchRow($select);
            if($result){
                return $result;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.answerInfo : " . $e->getMessage());
		}
		return false;
	}

	public function listAnswers($sort_field = 'answer_id', $field = null, $value = null, $strict_filter = false) {
		if( !empty($field) && !empty($value) && $strict_filter == false){
			$select = $this->select()->where($field . " like " . "'%".$value."%'")->order($sort_field);
		}else{
		    if( $strict_filter == true ){
		        $select = $this->select()->where($field . " = " . "'".$value."'")->order($sort_field);
		    }else{
                $select = $this->select()->order($sort_field);
		    }
		}

		try {
			$row = $this->fetchAll($select);
			if(count($row) > 0) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.listAnswers : " . $e->getMessage());
		}
		return false;
	}

	public function listUnLinkedAnswers($qid, $ajax_call = false, $search_text = null) {
	    if($ajax_call == false){
	        $select = "Select * from " . $this->_name . " where answer_id not in ( Select answer_id from que_answer where question_id = " . $qid . ") order by answer_id desc ";
	    }else{
	        $select = "Select * from " . $this->_name . " where answer_text like '%".$search_text."%' and answer_id not in ( Select answer_id from que_answer where question_id = " . $qid . ") order by answer_id desc ";
	    }
	//    echo $select; exit;

		try {
			$result = $this->getDefaultAdapter()->fetchAll($select);
			if(count($result) > 0) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.listUnLinkedAnswers : " . $e->getMessage());
		}
		return false;
	}

	public function listUnLinkedAnswersRS($rs_id, $ajax_call = false, $search_text = null) {
	    if($ajax_call == false){
	        $select = "Select * from " . $this->_name . " where answer_id not in ( Select answer_id from response_set_answer where rs_id = " . $rs_id . ") order by answer_id desc ";
	    }else{
	        $select = "Select * from " . $this->_name . " where answer_text like '%".$search_text."%' and answer_id not in ( Select answer_id from response_set_answer where rs_id = " . $rs_id . ") order by answer_id desc ";
	    }

		try {
			$result = $this->getDefaultAdapter()->fetchAll($select);
			if(count($result) > 0) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Answer.listUnLinkedAnswersRS : " . $e->getMessage());
		}
		return false;
	}


}