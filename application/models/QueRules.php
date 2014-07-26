<?php

class Default_Model_QueRules extends Zend_Db_Table {
    
    protected $_name = 'que_rules';

    public function getQueRule($sq_id, $answer_id) {
        $select = $this->select()->where('sq_id = ?', $sq_id)->where('answer_id = ?', $answer_id);
        
        try{
            $row = $this->fetchAll($select);
            if ($row){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QueRules.getQueRule : " . $e->getMessage());
        }
        return false;
    }
    
    public function deleteQueRule($qr_id) {
        $where = ' qr_id = ' . $qr_id;
        
        try{
            $result = $this->delete($where);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QueRules.deleteQueRule : " . $e->getMessage());
        }
        return false;
    }
    
    public function getRules($sq_id) {
        $select = "Select distinct qr.qr_id, q.description, a.answer_text from ".$this->_name." qr, question q, answer a, survey_question sq
					where qr.target_sq_id = sq.sq_id and sq.question_id = q.question_id and qr.answer_id = a.answer_id and qr.sq_id = " . $sq_id;
        
        try{
            $result = $this->getDefaultAdapter()->fetchAll($select);
            if (count($result) > 0){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QueRules.getRules : " . $e->getMessage());
        }
        return false;
    }

    public function addQueRule($post) {
        $data = array('sq_id' => $post['sq_id'], 'answer_id' => $post['answer_id'], 'target_sq_id' => $post['target_sq_id'], 'add_date' => date("Y-m-d"), 'status' => 'Active');
        
        try{
            $qr_id = $this->insert($data);
            if ($qr_id){
                return $qr_id;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QueRules.addQueRule : " . $e->getMessage());
        }
        return false;
    }
    
    public function isQueTargetQue($sq_id) {
        $select = $this->select()->where('target_sq_id = ?', $sq_id);
        
        try{
            $row = $this->fetchAll($select);
            if (count($row)>0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QueRules.isQueTargetQue : " . $e->getMessage());
        }
        return false;
    }

    public function getNonTargetQue($sq_id, $answer_id) {
        //$select = $this->select()->where('sq_id = ?', $sq_id)->where('answer_id != ?', $answer_id);
        //$select = "Select distinct target_sq_id from " . $this->_name . " where sq_id = " . $sq_id . " and answer_id != " . $answer_id;
        
        $select = "Select distinct target_sq_id from " . $this->_name . " where sq_id = " . $sq_id . " and answer_id != " . $answer_id . " and
        				target_sq_id not in ( Select distinct target_sq_id
        											from " . $this->_name . " where sq_id = " . $sq_id . " and answer_id = " . $answer_id . " )";
        //echo $select . "<br><br>";
        
        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row)>0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("QueRules.getNonTargetQue : " . $e->getMessage());
        }
    
    }
}