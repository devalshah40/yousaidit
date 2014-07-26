<?php

class Default_Model_Report extends Zend_Db_Table {

	protected $_name = 'report';

    public function insertReport($post) {
		$data = array( 
						'user_id'  =>    $post['user_id'],
						'topic_id'    => $post['topic_id'],
					);

		try {
			$report_id = $this->insert($data);
			if($report_id){
			    return $report_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Report.insertReport : " . $e->getMessage());
		}
		return false;
	}



	public function reportInfo($id) {
		    $select = $this->select()->where(' topic_id =  ?', $id);
	
			try {
				$result = $this->fetchRow($select);
	            if($result){
	                return $result;
	            }
			}catch (Exception $e){
				$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
				$errlog->direct("Report.reportInfo : " . $e->getMessage());
			}
			return false;
		}
	
	public function getReportCount($topic_id) {
	    $select = "SELECT count( `report_id` ) as count FROM `report` where topic_id = '".$topic_id."' GROUP BY `topic_id` ORDER BY `topic_id`";

		 try{
            $result = $this->getDefaultAdapter()->fetchAll($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Report.getReportCount : " . $e->getMessage());
        }
        
		return false;
	}
	
	
	public function reportCount($id) {
	    
		$select = "SELECT count(report_id) as report from report where topic_id  = '" . $id . "'";
		
			try {
				$result = $this->getDefaultAdapter()->fetchRow($select);
	            if($result){
	                return $result;
	            }
			}catch (Exception $e){
				$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
				$errlog->direct("Report.reportCount : " . $e->getMessage());
			}
			return false;
		}

	public function deleteRevokeTopics($id) {
	    
        $where = 'topic_id = ' . $id;
        try{
            $result = $this->delete($where);
            if ($result){
                return true;
            }
        }catch (Exception $e){
				$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
				$errlog->direct("Report.deleteRevokeTopics : " . $e->getMessage());
			}
			return false;
		}
	
}