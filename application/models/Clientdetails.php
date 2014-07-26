<?php

class Default_Model_Clientdetails extends Zend_Db_Table {
	
	protected $_name = 'client_details';
	


	public function getClientInfo( $userid) {
		$select = $this->select ()->where ( 'user_id = ' . $userid );
		
		try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "Clientdetails.getClientInfo : " . $e->getMessage () );
		}
		return false;
	}
	


	 
	public function updateClientDetails($post) {
		
		$data = array (
		               'company_name' => $post ['company_name'], 
		               
		                'country_id' => $post ['country']
		                
		 );
		
		
		
		$where = 'user_id = ' . $post ['user_id'];
		
		try {
			$result = $this->update ( $data, $where );
		
		
				return true;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "Clientdetails.updateClientDetails : " . $e->getMessage () );
		}
		return false;
	}
	

	public function insertClientDetails($post) {
		

		
		$data = array (
	                   'user_id' => $post ['user_id'], 
		               'company_name' => $post ['company_name'], 
		               'country_id' => $post ['country']
		
                       );  
		
	
		
		try {
			$user_id = $this->insert ( $data );
			return $user_id;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "Clientdetails.insertClientDetails : " . $e->getMessage () );
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
		try {
			$result = $this->delete ( $where );
			if ($result)
				return true;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.deleteUserInfo : " . $e->getMessage () );
		}
		return false;
	}
	


}