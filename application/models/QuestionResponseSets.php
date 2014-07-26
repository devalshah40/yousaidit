<?php

class Default_Model_QuestionResponseSets extends Zend_Db_Table {

    protected $_name = 'question_response_sets';

    public function insertQuestionResponseSetAssociation($question_id, $rs_id) {
        $data = array('question_id' => $question_id, 'rs_id' => $rs_id, 'add_date' => date('Y-m-d'));

        try{
            $qrs_id = $this->insert($data);
            if ($qrs_id){
                return $qrs_id;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QuestionResponseSets.insertQuestionResponseSetAssociation : " . $e->getMessage());
        }
        return false;
    }

    public function deleteQuestionResponseSet($qrs_id) {
        $sql = "qrs_id = " . $qrs_id;

        try{
            $result = $this->delete($sql);
            if ($result){
                return true;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QuestionResponseSets.deleteQuestionResponseSet : " . $e->getMessage());
        }
        return false;
    }

    public function deleteQuestionResponseSetAssociation($question_id) {
        $sql = "question_id = " . $question_id;

        try{
            $result = $this->delete($sql);
            if ($result){
                return true;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QuestionResponseSets.deleteQuestionResponseSetAssociation : " . $e->getMessage());
        }
        return false;
    }

    public function listQuestionResponseSets($sort_field = 'qrs_id', $field = null, $value = null, $strict_filter = false) {
		if (! empty ( $field ) && ! empty ( $value ) && $strict_filter == false ) {
			$select_addon = ' and ' . $field . ' like "%'.$value.'%" order by ' . $sort_field;
		} else {
		    if( $strict_filter == true ){
		        $select_addon = ' and ' . $field . ' = "' . $value . '" order by ' . $sort_field;
		    }else{
                $select_addon = ' order by ' . $sort_field;
		    }
		}

        $select = "Select qrs.*, rs.*
        				from " . $this->_name . " qrs, response_sets rs
						where
							qrs.rs_id = rs.rs_id " . $select_addon;
      

        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QuestionResponseSets.listQuestionResponseSets : " . $e->getMessage());
        }
        return false;
    }

	public function updateQueResponseSetSortOrder($qrs_id, $sort_order){
        $data = array('sort_order' => $sort_order);
        $where = 'qrs_id = ' . $qrs_id;

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("QuestionResponseSets.updateQueResponseSetSortOrder : " . $e->getMessage());
		}
		return false;
    }

}