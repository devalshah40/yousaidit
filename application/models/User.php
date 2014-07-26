<?php

class Default_Model_User extends Zend_Db_Table {
	
	protected $_name = 'user';
	
	/**
	 * Return an encrypted password
	 *
	 * @param string $pwd
	 * @return string
	 */
	protected function hashPassword($pwd) {
		return md5 ( trim ( $pwd ) );
	}
	
	/**
	 * Get user details from username, password and usergroupid
	 *
	 * @param string $username
	 * @param string $pwd
	 * @param int $usergroupid
	 * @return mysql_row / boolean
	 */
	public function checkLoginInfo($username, $pwd, $groupid) {
		$select = $this->select ()->where ( 'username  = ? ', $username )->where ( 'password  = ? ', $this->hashPassword ( $pwd ) )->where ( 'group_id  = ' . $groupid );
		
		try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.checkLoginInfo : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Get user list as per user type
	 *
	 * @param int $usergroupid
	 * @return mysql_row / boolean
	 */
	public function listUsers($usergroupid, $sort_field = null, $field = null, $value = null) {
		if (! empty ( $field ) && ! empty ( $value )) {
			$select = $this->select ()->where ( 'group_id = ' . $usergroupid . " and " . $field . " like " . "'%" . $value . "%'" )->order ( $sort_field );
		
		} else {
			$select = $this->select ()->where ( 'group_id = ' . $usergroupid )->order ( $sort_field );
		}
		
		try {
			$row = $this->fetchAll ( $select );
			if (count($row)) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.listUsers : " . $e->getMessage () );
		}
		return false;
	}
	
	public function listAllActiveUsers() {
		$select = "	Select
						u.*, ud.country_id, ud.notification_status, ud.categories_of_interest
					from
						" . $this->_name . " u, user_details ud
					where
						u.user_id = ud.user_id and
						u.status = 'Active' and
						u.group_id = 2
					order by
						u.user_id ";
		try {
			$row = $this->getDefaultAdapter()->fetchAll( $select );
			if (count($row)) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.listAllActiveUsers : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Get user details
	 *
	 * @param int $usergroupid
	 * @param int $userid
	 * @return mysql_row / boolean
	 */
	public function getUserInfo($usergroupid, $userid) {
		$select = $this->select ()->where ( 'group_id = ' . $usergroupid )->where ( 'user_id = ' . $userid );
		
		try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.getUserInfo : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Update user last login date & time
	 *
	 * @param int $user_id
	 * @return boolean
	 */
	public function updateLastLoginDateTime($user_id) {
		$data = array ('lastlogin_datetime' => date ( "Y-m-d H:i:s" ) );
		$where = 'user_id = ' . $user_id;
		try {
			$result = $this->update ( $data, $where );
			if ($result)
				return true;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.updateLastLoginDateTime : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Reset user password, called when user submits forgot password request
	 *
	 * @param int $id
	 * @param string $new_pwd
	 * @return booelan
	 */
	public function resetUserPassword($id, $new_pwd) {
		$data = array ('password' => $new_pwd );
		$where = 'user_id = ' . $id;
		
		try {
			$result = $this->update ( $data, $where );
			return true;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.resetUserPassword : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Update user details
	 *
	 * @param array $post
	 * @return boolean
	 */
	public function updateUserDetails($post) {
		
		$data = array ('firstname' => $post ['firstname'], 'lastname' => $post ['lastname'], 'email' => $post ['email'], 'updated_datetime' => date ( "Y-m-d H:i:s" )  );
		
		if (! empty ( $post ['verified'] )) {
			$data_1 = array ('verified' => $post ['verified'] );
			$data = array_merge ( $data, $data_1 );
		}
		
		if (! empty ( $post ['status'] )) {
			$data_1 = array ('status' => $post ['status'] );
			$data = array_merge ( $data, $data_1 );
		}
		
		if (! empty ( $post ['password'] )) {
			if ($post ['password'] == $post ['c_password']) {
				$data_1 = array ('password' => md5 ( $post ['c_password'] ) );
				$data = array_merge ( $data, $data_1 );
			}
		}
		
		$where = 'user_id = ' . $post ['id'];
		
		try {
			$result = $this->update ( $data, $where );
		
		
				return true;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.updateUserDetails : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Get user details from either username / email, password and usergroupid
	 *
	 * @param string $username
	 * @param string $pwd
	 * @param int $usergroupid
	 * @return mysql_row / boolean
	 */
	public function findCredentials($value, $cookie_search = null) {
		$email_validator = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'emailvalidate' );
		
		if ($email_validator->direct ( $value ) == true) {
			$field = 'email';
		} else {
			if ($cookie_search == true) {
				$field = 'md5(username)';
			} else {
				$field = 'username';
			}
		}
		
		$select = $this->select ()->where ( $field . '   = ? ', $value )->where ( 'group_id  = 2' );
		
		try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.findCredentials : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Insert user details
	 *
	 * @param array $post
	 * @return boolean
	 */
	public function insertUser($post) {
		
		$data = array ('firstname' => $post ['firstname'], 'lastname' => $post ['lastname'], 'email' => $post ['email'], 'group_id' => $post ['group_id'], 'username' => $post ['username'], 'created_date' => date ( "Y-m-d" ), 'status' => $post ['status'] );
		
		if (! empty ( $post ['password'] )) {
			if ($post ['password'] == $post ['c_password']) {
				$data_1 = array ('password' => md5 ( $post ['c_password'] ) );
				$data = array_merge ( $data, $data_1 );
			}
		}
		
		try {
			$user_id = $this->insert ( $data );
			return $user_id;
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.insertUser : " . $e->getMessage () );
		}
		return false;
	}
	
	/**
	 * Check for existing user based on email & username, used during signup operation
	 *
	 * @param string $username
	 * @return mysql_row / boolean
	 */
	public function existingUser($username = null) {
		$select = $this->select ()->from ( $this->_name, 'user_id' )->where ( 'username = ?', $username );
		
		try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.existingUser : " . $e->getMessage () );
			return true;
		}
		return false;
	}
	
	/**
	 * Delete user info
	 *
	 * @param int $user_id
	 * @return boolean
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
	
	/**
	 * Verify user account after successfull registration taking into consideration the user & token passed
	 *
	 * @param int $userid
	 * @param string $token
	 * @return boolean
	 */
	public function verifyUserAccount($userid, $token) {
		$data = array ('verified' => "Yes", 'status' => 'Active' );
		$where = 'userid=' . $userid . ' and token = "' . $token . '"';
		
		try {
			$result = $this->update ( $data, $where );
			if ($result) {
				return true;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.verifyUserAccount : " . $e->getMessage () );
		}
		return false;
	}

	public function update_oauth_info($uid, $oauth_provider, $user_id){
		$data = array ('oauth_provider' => $oauth_provider, 'oauth_uid' => $uid );
		$where = 'userid=' . $user_id;

		try {
			$result = $this->update ( $data, $where );
			if ($result) {
				return true;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "User.update_oauth_info : " . $e->getMessage () );
		}
		return false;
	}
	
}