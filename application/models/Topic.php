<?php

class Default_Model_Topic extends Zend_Db_Table {
    
    protected $_name = 'topics';

    public function inserttopic($post) {
        
        $data = array(

        'user_id' => $post['user_id'],

        'name' => $post['name'],

        'description' => $post['description'],

        'add_date' => date("Y-m-d H:i:s"),
        
        'country_id' => $post['country_id'],

        'status' => empty($post['status']) ? 'Active' : $post['status']);
        
        try{
            
            $topic_id = $this->insert($data);
            
            if ($topic_id){
                
                return $topic_id;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.inserttopic : " . $e->getMessage());
        
        }
        
        return false;
    
    }

    public function updateTopic($post) {
        
        $data = array('name' => $post['name'],

       //'categories_id' => $post ['categories_id'],

        'description' => $post['description'],

        'updated_datetime' => date("Y-m-d H:i:s"),
        
        'country_id' => $post['country_id'],

        'status' => $post['status']);
        
        $where = ' topic_id = ' . $post['topic_id'];
        
        try{
            
            $result = $this->update($data, $where);
            
            return true;
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.updateTopic : " . $e->getMessage());
        
        }
        
        return false;
    
    }

 	public function Updatetopicstatus($topic_id) {

   	$select = " UPDATE topics SET status = 'Inactive'  WHERE topic_id = '" . $topic_id . "'";
        try{
            $result = $this->getDefaultAdapter()->fetchRow($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Topic.Updatetopicstatus : " . $e->getMessage());
        }
        return false;
    }
    
    public function UpdateRevoketopicstatus($topic_id) {

    	
	   	$select = " UPDATE topics SET status = 'Active'  WHERE topic_id = '" . $topic_id . "'";
	        try{
	            $result = $this->getDefaultAdapter()->fetchRow($select);
	            if ($result){
	                return $result;
	            }
	        } catch(Exception $e){
	            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
	            $errlog->direct("Topic.UpdateRevoketopicstatus : " . $e->getMessage());
	        }
	        return false;
    }
	
	
    public function deleteTopic($id) {
        
        $where = ' topic_id = ' . $id;
        
        try{
            
            $result = $this->delete($where);
            
            if ($result){
                
                return true;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.deleteTopic : " . $e->getMessage());
        
        }
        
        return false;
    
    }

    public function TopicInfo($id) {
        
        $select = $this->select()->where(' topic_id =  ?', $id);
        
        try{
            
            $result = $this->fetchRow($select);
            
            if ($result){
                
                return $result;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.TopicInfo : " . $e->getMessage());
        
        }
        
        return false;
    
    }

    public function listTopics($sort_field = 'topic_id', $field = null, $value = null, $strict_filter = false) {
        
        if (! empty($field) && ! empty($value) && $strict_filter == false){
            
            $select = $this->select()->where($field . " like " . "'%" . $value . "%'")->order($sort_field);
        
        } else{
            
            if ($strict_filter == true){
                
                $select = $this->select()->where($field . " = " . "'" . $value . "'")->order($sort_field);
            
            } else{
                
                $select = $this->select()->order($sort_field);
            
            }
        
        }
        
        try{
            
            $row = $this->fetchAll($select);
            
            if (count($row) > 0){
                
                return $row;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.listTopics : " . $e->getMessage());
        
        }
        
        return false;
    
    }

    public function getUserTopics($user_id) {
        
        $select = $this->select()->where("user_id = ?", $user_id);
        
        try{
            $result = $this->fetchAll($select);
            
            if ($result){
                
                return $result;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.getUserTopics : " . $e->getMessage());
        
        }
        
        return false;
    
    }

    public function getTopicListing($sort_field = 'topic_id', $field = null, $value = null, $strict_filter = false , $flag = null) {
		//echo $strict_filter; exit;
    	if(empty($flag))
		{
	    	if (! empty($field) && ! empty($value) && $strict_filter == false){
	            $select = "SELECT * FROM topics  where $field like '%$value%' order by " . $sort_field;
	        } else{
	            if ($strict_filter == true){
	                $select = "SELECT c.categories_name, t . * FROM topic_categories c, topics t WHERE t.categories_id = c.categories_id
					   		           and where '" . $field . "' = '" . $value . "' order by " . $sort_field;
	            } else{
	                $select = "SELECT  t.* FROM topics t order by " . $sort_field;
	            }
	        }
		}
		else
		{
	    	
			if (! empty($field) && ! empty($value) && $strict_filter == false){
	            $select = "SELECT * FROM topics  where $field like '%$value%' and topic_id IN (SELECT t.topic_id from topics t , report r where t.topic_id = r.topic_id) order by " . $sort_field;
	        } else{
	        
	            if ($strict_filter == true){
	                $select = "SELECT c.categories_name, t . * FROM topic_categories c, topics t WHERE t.categories_id = c.categories_id
					   		           and where '" . $field . "' = '" . $value . "' and t.topic_id IN (SELECT t.topic_id from topics t , report r where t.topic_id = r.topic_id) order by " . $sort_field;
	            } else{
	            	
	                $select = "SELECT  t.* FROM topics t where t.topic_id IN (SELECT t.topic_id from topics t , report r where t.topic_id = r.topic_id) order by " . $sort_field;
	            }
	        }
		}
	   	
        try{
            $result = $this->getDefaultAdapter()->fetchAll($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Topic.getTopicListing : " . $e->getMessage());
        }
        
        return false;
    
    }

    public function getEditTopic($tid) {
        
        $select = " SELECT categories_id FROM topic_categories tc, topics t
						WHERE t.topic_id = tc.topic_id AND t.`topic_id` = '" . $tid . "'";
        
        try{
            
            $result = $this->getDefaultAdapter()->fetchAll($select);
            
            if ($result){
                
                return $result;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.getEditTopic : " . $e->getMessage());
        
        }
        
        return false;
    
    }

    public function getcatewiseListing($tcat_id , $flag = NULL) {
        
    	if(empty($flag))
    	{
        	$select = "SELECT t.* FROM topics t , topic_categories tc   WHERE t.topic_id = tc.topic_id and tc.categories_id = '" . $tcat_id . "' ";
    	}
    	else
    	{
    		$select = "SELECT t.* FROM topics t , topic_categories tc   WHERE t.topic_id = tc.topic_id and tc.categories_id = '" . $tcat_id . "' and t.topic_id IN (SELECT t.topic_id from topics t , report r where t.topic_id = r.topic_id) ";
    	}
        try{
            
            $result = $this->getDefaultAdapter()->fetchAll($select);
            
            if ($result){
                
                return $result;
            
            }
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("Topic.getcatewiseListing : " . $e->getMessage());
        
        }
        
        return false;
    
    }
/*  public function getFrontendtopiclist($user_id, $tcountry = null, $category_ids = null) {

     if ( !empty($user_id) ){
            $sql_addon_1 = " and
            				topic_id NOT IN (
            									SELECT
            										topic_id
                  								FROM
                  									topic_response
                  								WHERE
                  									user_id = '" . $user_id . "') and

                  									topic_id NOT IN (
                  									
                                             SELECT	topic_id FROM report
                                             
                                             where	user_id = '" . $user_id . "')									 ";
        }else{
            $sql_addon_1 = "";
        }
        
        if ( !empty($tcountry) && $tcountry != -1  ){
            $sql_addon_2 = " and
                  			user_id IN (
                  							SELECT
                  								user_id
                  							FROM
                  								user_details
                  							WHERE
                  								country_id = '" . $tcountry . "') ";
        }else{
            $sql_addon_2 = "";
        }
        
        if ( !empty($category_ids) ){
            $sql_addon_3 = " and
                  			topic_id IN (
                  							SELECT
                  								DISTINCT topic_id
                  							FROM
                 								topic_categories
                 							where
                 								categories_id IN (" . $category_ids . ") ) ";
        }else{
            $sql_addon_3 = "";
        }
        
        
        $select = "	SELECT
            			*
					FROM
						topics
            		WHERE
            			status='Active' " . $sql_addon_1 . " " . $sql_addon_2 . " " . $sql_addon_3 . "
            		order by
            			add_date desc
            		LIMIT 1";
        
        
        
     //  $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
    //  $errlog->direct("Topic.getFrontendtopiclist : " . $select);
        
        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Topic.getFrontendtopiclist : " . $e->getMessage());
        }
        return false;
    }*/
    
    public function getFrontendtopiclist($user_id, $tcountry = null, $category_ids = null) {

     if ( !empty($user_id) ){
            $sql_addon_1 = " and
            				topic_id NOT IN (
            									SELECT
            										topic_id
                  								FROM
                  									topic_response
                  								WHERE
                  									user_id = '" . $user_id . "') and

                  									topic_id NOT IN (
                  									
                                             SELECT	topic_id FROM report
                                             
                                             where	user_id = '" . $user_id . "')									 ";
        }else{
            $sql_addon_1 = "";
        }
        
       if ( !empty($tcountry) && $tcountry != -1  ){
            $sql_addon_2 = "  country_id = '" . $tcountry . "' and ";
       }else{
            $sql_addon_2 = "";
       }
        
        if ( !empty($category_ids) ){
            $sql_addon_3 = " and
                  			topic_id IN (
                  							SELECT
                  								DISTINCT topic_id
                  							FROM
                 								topic_categories
                 							where
                 								categories_id IN (" . $category_ids . ") ) ";
        }else{
            $sql_addon_3 = "";
        }
        
        
        $select = "	SELECT
            			*
					FROM
						topics
            		WHERE ". $sql_addon_2 ."
            			status='Active' " . $sql_addon_1 . " " . $sql_addon_3 . "
            		order by
            			add_date desc
            		LIMIT 1";
        
    //   echo $select ;
        
        try{
            $row = $this->getDefaultAdapter()->fetchAll($select);
            if (count($row) > 0){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Topic.getFrontendtopiclist : " . $e->getMessage());
        }
        return false;
    }

    public function getTopTenTopics($tid) {
        $select = " SELECT DISTINCT topic_id , count( response ) FROM topic_response GROUP BY topic_id ORDER BY count( response ) DESC LIMIT 10 ";
        try{
            $result = $this->getDefaultAdapter()->fetchAll($select);
            if ($result){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Topic.getTopTenTopics : " . $e->getMessage());
        }
        return false;
    }

    public function updateTopicNotificationFlag($id) {
        $data = array('notification' => 'y');
        $where = ' topic_id = ' . $id;
        
        try{
            $result = $this->update($data, $where);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Topic.updateTopicNotificationFlag : " . $e->getMessage());
        }
        return false;
    }
    
    
}
	
