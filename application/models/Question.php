<?php

class Default_Model_Question extends Zend_Db_Table {

	protected $_name = 'question';

	public function insertQuestion($post) {
	    //'que_legend'   => $post['que_legend'],
	    
		$data = array(
		               	'description'  => $post['description'],
						'answer_type'  => $post['answer_type'],
						'max_answer'   => !empty($post['max_answer'])?$post['max_answer']:null,
						'rating_caption_1'   => !empty($post['rating_caption_1'])?$post['rating_caption_1']:null,
						'rating_caption_2'   => !empty($post['rating_caption_2'])?$post['rating_caption_2']:null,
                		'rating_caption_3'   => !empty($post['rating_caption_3'])?$post['rating_caption_3']:null,
                		'rating_caption_4'   => !empty($post['rating_caption_4'])?$post['rating_caption_4']:null,
                		'rating_caption_5'   => !empty($post['rating_caption_5'])?$post['rating_caption_5']:null,
						'help_text'          => !empty($post['help_text'])?$post['help_text']:null,
						'added_by'	   => $post['added_by'],
						'add_date'     => date("Y-m-d"),
						'status'	   => $post['status']		);

		try {
			$question_id = $this->insert($data);
			if($question_id){
			    return $question_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Question.insertQuestion : " . $e->getMessage());
		}
		return false;
	}

	public function updateQuestion($post) {
	    //'que_legend'       => $post['que_legend'],
	    
		$data = array(
		                'description'      => $post['description'],
						'answer_type'      => $post['answer_type'],
						'max_answer'       => !empty($post['max_answer'])?$post['max_answer']:null,
						'rating_caption_1'   => !empty($post['rating_caption_1'])?$post['rating_caption_1']:null,
						'rating_caption_2'   => !empty($post['rating_caption_2'])?$post['rating_caption_2']:null,
                		'rating_caption_3'   => !empty($post['rating_caption_3'])?$post['rating_caption_3']:null,
                		'rating_caption_4'   => !empty($post['rating_caption_4'])?$post['rating_caption_4']:null,
                		'rating_caption_5'   => !empty($post['rating_caption_5'])?$post['rating_caption_5']:null,
						'help_text'          => !empty($post['help_text'])?$post['help_text']:null,
						'updated_by'       => $post['updated_by'],
						'updated_datetime' => date("Y-m-d H:i:s"),
						'status'	       => $post['status']		);

        $where = ' question_id = ' . $post['id'];

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Question.updateQuestion : " . $e->getMessage());
		}
		return false;
	}

	public function deleteQuestion($id) {
        $where = ' question_id = ' . $id;
		try {
			$result = $this->delete($where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Question.deleteQuestion : " . $e->getMessage());
		}
		return false;
	}

	public function questionInfo($id) {
	    $select = $this->select()->where(' question_id =  ?', $id);

		try {
			$result = $this->fetchRow($select);
            if($result){
                return $result;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Question.getQuestionInfo : " . $e->getMessage());
		}
		return false;
	}

	public function listQuestions($sort_field = null, $field = null, $value = null, $strict_filter = false) {

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
			$errlog->direct("Question.listQuestions : " . $e->getMessage());
		}
		return false;
	}
}