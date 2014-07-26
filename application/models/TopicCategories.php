<?php


class Default_Model_TopicCategories extends Zend_Db_Table {



	protected $_name = 'topic_categories';



    public function insertTopicCategory($tc_id,$cat_id) {

		$data = array( 	'topic_id'       => $tc_id,
				
						'categories_id'  => $cat_id,

						);



		try {

			$tc_id = $this->insert($data);

			if($tc_id){

			    return $tc_id;

			}

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("Categories.insertCategory : " . $e->getMessage());

		}

		return false;

	}
	
    public function updateTopicCategory($post ) {
        
        $data = array(

       'categories_id' => $post ['categories_id']

       );
        
        $where = ' topic_id = ' . $post['topic_id'];
        
        try{
            
            $result = $this->update($data, $where);
            
            return true;
        
        } catch(Exception $e){
            
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $errlog->direct("TopicCategories.updateTopicCategory : " . $e->getMessage());
        
        }
        
        return false;
    
    }

	public function deleteTopicCategory($tc_id) {

        $where = 'topic_id = ' . $tc_id;

		try {

			$result = $this->delete($where);

			return true;

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("TopicCategories.deleteTopicCategory : " . $e->getMessage());

		}

		return false;

	}
	
// 	public function getTopicCategory($tc_id) {

// 	    $select = $this->select()->where(' topic_id =  ?', $tc_id);

// 		try {

// 			$result = $this->fetchRow($select);

// 			if($result){
// 			    return $result;
// 			}

// 		}catch (Exception $e){

// 			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

// 			$errlog->direct("TopicCategories.getTopicCategory : " . $e->getMessage());

// 		}

// 		return false;

// 	}
	
	public function gettopicCategory($topic_id)
	{  
        $sql = "SELECT  c.categories_name FROM   categories c , topic_categories tc  

				WHERE  tc.categories_id = c.categories_id and 

				tc.topic_id = ". $topic_id;

		try {
			 $result = $this->getDefaultAdapter()->fetchRow($sql);
			return $result;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("TopicCategories.gettopicCategory : " . $e->getMessage());
		}
		return false;
	}	

	
}
