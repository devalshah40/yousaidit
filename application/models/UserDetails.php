<?php

class Default_Model_UserDetails extends Zend_Db_Table {
    
    protected $_name = 'user_details';

    public function getUserInfo($userid) {
        $select = $this->select()->where('user_id = ' . $userid);
        
        try{
            $row = $this->fetchRow($select);
            if ($row){
                return $row;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.getUserInfo : " . $e->getMessage());
        }
        return false;
    }

    public function updateUserDetails($post) {
        
    	$data = array (
   	           //      'gender'      => ! empty($post['gender']) ? $post['gender'] : 'Male',
    	               'address1'    => ! empty($post['address1']) ? $post['address1'] : null,
    	               'address2'    => ! empty($post['address2']) ? $post['address2'] : null,
    	               'town'        => ! empty($post['town']) ? $post['town'] : null,
    	               'county'      => ! empty($post['county']) ? $post['county'] : null,
    	               'postcode'    => ! empty($post['postcode']) ? $post['postcode'] : null,
    	               'country_id'  => $post['country'],
    	               'data_share'  => ! empty($post['chkShareData']) ? $post['chkShareData'] : 'N',
    	               'interest'    => ! empty($post['interest']) ? $post['interest'] : '',
    	         //    'dob'         => date("Y-m-d", strtotime($post['dob']))
    	
    	);
		
//		if (! empty ( $post ['data_share'] )) {
//			$data_1 = array ($post ['data_share'] => $post ['data_share'] );
//			$data = array_merge ( $data, $data_1 );
//		}
        $where = 'user_id = ' . $post['id'];
        
        try{
            $result = $this->update($data, $where);
            
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.updateUserDetails : " . $e->getMessage());
        }
        return false;
    }

    public function insertUserDetails($post) {
        
        $post['dob'] = str_replace("/", "-", $post['dob']);
        
        $data = array(    'user_id'     => $post['user_id'],
                          'country_id'  => $post['country'],
                          'gender'      => ! empty($post['gender']) ? $post['gender'] : 'Male',
    	                  'address1'    => ! empty($post['address1']) ? $post['address1'] : null,
    	                  'address2'    => ! empty($post['address2']) ? $post['address2'] : null,
    	                  'town'        => ! empty($post['town']) ? $post['town'] : null,
    	                  'county'      => ! empty($post['county']) ? $post['county'] : null,
    	                  'postcode'    => ! empty($post['postcode']) ? $post['postcode'] : null,
                          'dob'         => date("Y-m-d", strtotime($post['dob'])),
                          'interest'    => ! empty($post['interest']) ? $post['interest'] : null,
                          'data_share'  => ! empty($post['chkShareData']) ? $post['chkShareData'] : 'N',
                          'categories_of_interest' => $post['categories_of_interest']       );
        
        try{
            $user_id = $this->insert($data);
            return $user_id;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.insertUserDetails : " . $e->getMessage());
        }
        return false;
    }

    /**
     * Check for existing user based on email & username, used during signup operation
     *
     * @param string $username
     * @return mysql_row / boolean
     */
    
    public function deleteUserInfo($user_id) {
        $where = 'user_id = ' . $user_id;
        try{
            $result = $this->delete($where);
            if ($result)
                return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.deleteUserInfo : " . $e->getMessage());
        }
        return false;
    }
    
    public function updateNotificationCount($user_id, $action = 'add') {
        if($action == 'add'){
            $sql = 'update ' . $this->_name . ' set notification_count = notification_count + 1 where user_id = ' . $user_id;
        }else{
            $sql = 'update ' . $this->_name . ' set notification_count = notification_count - 1 where user_id = ' . $user_id;
        }
        try{
            $result = $this->getDefaultAdapter()->query($sql);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.updateNotificationCount : " . $e->getMessage());
        }
        return false;
    }
    
    public function getNotificationCount($user_id) {
        $sql = $this->select()->where('user_id = ?', $user_id);
        try{
            $result = $this->fetchRow($sql);
            return $result;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.getNotificationCount : " . $e->getMessage());
        }
        return false;
    }
    
    public function resetNotificationCount($user_id) {
        $sql = 'update ' . $this->_name . ' set notification_count = 0 where user_id = ' . $user_id;
        try{
            $result = $this->getDefaultAdapter()->query($sql);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.resetNotificationCount : " . $e->getMessage());
        }
        return false;
    }

    public function updateCategoriesOfInterest($post) {
        
    	$data = array ( 'categories_of_interest'  => $post['categories_of_interest']    );
		
        $where = 'user_id = ' . $post['id'];
        
        try{
            $result = $this->update($data, $where);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.updateCategoriesOfInterest : " . $e->getMessage());
        }
        return false;
    }
    
    public function updateNotifications($post) {
        
    	$data = array ( 'report_violation_flag'  => $post['report_violation_flag'],
    	                'notification_status'     => $post['notification_status']       );
		
        $where = 'user_id = ' . $post['id'];
        
        try{
            $result = $this->update($data, $where);
            return true;
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("User.updateNotifications : " . $e->getMessage());
        }
        return false;
    }
    
}