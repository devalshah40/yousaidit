<?php

class Default_Model_ResponseSetAnswer extends Zend_Db_Table {

    protected $_name = 'response_set_answer';

    public function insertResponseSetAnswerAssociation($rs_id, $answer_id, $free_text_caption = null) {
        $data = array('rs_id' => $rs_id, 'answer_id' => $answer_id, 'custom_free_text_caption' => $free_text_caption, 'add_date' => date('Y-m-d'));

        try{
            $rsa_id = $this->insert($data);
            if ($rsa_id){
                return $rsa_id;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("ResponseSetAnswer.insertResponseSetAnswerAssociation : " . $e->getMessage());
        }
        return false;
    }

    public function deleteResponseSetAnswerAssociation($rsa_id) {
        $sql = "rsa_id = " . $rsa_id;

        try{
            $result = $this->delete($sql);
            if ($result){
                return true;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("ResponseSetAnswer.deleteResponseSetAnswerAssociation : " . $e->getMessage());
        }
        return false;
    }

    public function listResponseSetAnswer($sort_field = null, $field = null, $value = null, $strict_filter = false) {
		if (! empty ( $field ) && ! empty ( $value ) && $strict_filter == false ) {
			$select_addon = ' and ' . $field . ' like "%'.$value.'%" order by ' . $sort_field;
		} else {
		    if( $strict_filter == true ){
		        $select_addon = ' and ' . $field . ' = "' . $value . '" order by ' . $sort_field;
		    }else{
                $select_addon = ' order by ' . $sort_field;
		    }
		}

        $select = "Select rsa.*, rs.title, a.*
        				from " . $this->_name . " rsa, response_sets rs, answer a
						where
							rsa.rs_id = rs.rs_id and
							rsa.answer_id = a.answer_id " . $select_addon;

        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("ResponseSetAnswer.listResponseSetAnswer : " . $e->getMessage());
        }
        return false;
    }

    public function existingAns($rs_id, $aid) {
	    $select = $this->select()->where('rs_id =  ?', $rs_id)->where('answer_id = ?', $aid);

		try {
			$result = $this->fetchRow($select);
            if($result){
                return true;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("ResponseSetAnswer.existingAns : " . $e->getMessage());
		}
		return false;
	}

	public function updateAnswerSortOrder($rsa_id, $sort_order, $answer_value, $free_text_caption = null){
        $data = array('sort_order' => $sort_order);

        if( !empty($free_text_caption) ){
            $caption = array('custom_free_text_caption' => $free_text_caption);
            $data = array_merge($data, $caption);
        }
        
	     if( !empty($answer_value) ){
            $caption1 = array('answer_value' => $answer_value);
            $data = array_merge($data, $caption1);
        }

        $where = 'rsa_id = ' . $rsa_id;

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("ResponseSetAnswer.updateAnswerSortOrder : " . $e->getMessage());
		}
		return false;
    }

    public function listQueAns($que_id ) {
        $select = "Select
        				rsa.*, a.*
        				from
        					" . $this->_name . " rsa, answer a, question_response_sets qrs
        				where 	rsa.answer_id = a.answer_id and
        						rsa.rs_id = qrs.rs_id and
								qrs.question_id = ".$que_id."
						order by
								qrs.sort_order, rsa.sort_order ";

		try {
			$row = $this->getDefaultAdapter()->fetchAll($select);
			if(count($row) > 0) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("ResponseSetAnswer.listQueAns : " . $e->getMessage());
		}
		return false;
	}
}