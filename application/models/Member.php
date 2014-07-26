<?php

class Default_Model_Member extends Zend_Db_Table {

	protected $_name = 'user';

	protected function hashPassword($pwd) {
		return md5(trim($pwd));
	}

	protected function isEmail($email) {
	    $pattern = '/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])' . '(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i';
		if (!preg_match($pattern , $email)) {
			return false;
		}
		return true;
	}

	/**
	 * Get member listing
	 *
	 * @param int $usergroupid
	 * @return mysql_row / boolean
	 */
	public function listMembers($sort_field = null, $field = null, $value = null) {

		if( !empty($field) && !empty($value) ) {
		    if($field == 'status'){
		        $select = $this->select()->where($field . " = " . "'".$value."'")->order($sort_field);
		    }else{
		        $select = $this->select()->where($field . " like " . "'%".$value."%'")->order($sort_field);
		    }
		}else{
			$select = $this->select()->order($sort_field);
		}

		try {
			$row = $this->fetchAll($select);
			if(count($row)>0) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.listMembers : " . $e->getMessage());
		}
		return false;
	}

	public function memberSignup($post){
		$data = array( 	'firstname'   => !empty($post['first_name'])?ucfirst($post['first_name']):$post['first_name'],
						'lastname'	   => !empty($post['last_name'])?ucfirst($post['last_name']):$post['last_name'],
						'email'		   => $post['email'],
		                'group_id'     => 2,
					//	'username'	   => $post['username'],
						//'dob'          => !empty($post ['dob'])?$post ['dob']:null,
						//'country'      => !empty($post ['country'])?$post ['country']:null,
						'password'     => md5($post['password']),
						'created_date'  => date("Y-m-d"),
		               // 'level_id'     => 1,
						//'currency_id'  => 1,
						'verified'     => 0,
						'token'        => $post['token'],
		              //  'data_share'   => $post['data_share'],
		              //   'device_id'    => !empty($post['uniqueid'])?$post['uniqueid']:null

		);

		try {
			$user_id = $this->insert($data);
			if($user_id){
			     return $user_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.memberSignup : " . $e->getMessage());
		}
		return false;
	}


	public function updateMember($post, $section = null){
		$data = array( 	'firstname'   => !empty($post['first_name'])?ucfirst($post['first_name']):$post['first_name'],
						'lastname'	   => !empty($post['last_name'])?ucfirst($post['last_name']):$post['last_name'],
                    //    'email'	       => !empty($post['email'])?ucfirst($post['email']):$post['email'],
		             	'updated_datetime'    => date("Y-m-d H:i:s")                  );


		if( !empty($section) ){
		    if( $post['c_email'] != $post['email'] ){
		        $arr = array( 'email' => $post['c_email'] , 'temp_email' => $post['email'] );
		    }else{
		        $arr = array( 'email' => $post['c_email']);
		    }
			$data = array_merge($data, $arr);
		}else{
		    // executed if call is made from backend admin
		    if( $post['email'] != 'xxxx.xxxx@xxxx.com' ){
		        $arr = array( 'email' => $post['email']);
		        $data = array_merge($data, $arr);
		    }
		}
	

		if( empty($section) ){
			$arr = array( 'status'  => $post['status'] );
			$data = array_merge($data, $arr);
		}

		if( !empty($post['password']) && !empty($post['c_password']) ){
			if( $post['password'] == $post['c_password'] ){
				$pwd = array(	'password'		=> $this->hashPassword($post['password'])	);
				$data = array_merge($data, $pwd);
			}
		}

		$where = 'user_id = ' . $post['id'];

		try {
			$result = $this->update($data, $where);
		    return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.updateMember : " . $e->getMessage());
		}
		return false;
	}

	public function isDeviceRegistered($device_id){
        $sql = $this->select()->where('device_id = ?', $device_id);

		try {
			$row = $this->fetchRow($sql);
			if($row) return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.isDeviceRegistered : " . $e->getMessage());
		}
		return false;
	}

	public function memberInfo($username, $group_id){
		if( $this->isEmail($username) == true ){
			$field = 'email';
		}else{
		    if( is_numeric($username) == true ){
		        $field = 'user_id';
		    }else{
                $field = 'email';
		    }
		}

        $sql = $this->select()->where($field . ' = ?', $username)->where('group_id = ?', $group_id);

		try {
			$row = $this->fetchRow($sql);
			return $row;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.memberInfo : " . $e->getMessage());
		}
		return false;
	}

	public function resetPassword($id, $new_pwd) {
		$data 		= array('password' => $new_pwd);
		$where 		= 'user_id = ' . $id;

		try {
			$result 	= $this->update($data , $where );
		
				return true;
		
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.resetPassword : " . $e->getMessage());
		}
		return false;
	}

	public function verifyUserAccount($userid, $token) {
		$data  = array('verified' => 1, 'status' => 'Active', 'token' => null);
		$where = 'user_id='.$userid.' and token = "'.$token.'"';

		try {
			$result = $this->update($data , $where);
			if($result){
				return true;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.verifyUserAccount : " . $e->getMessage());
		}
		return false;
	}

    public function updateLastLoginDateTime($email) {
		$data = array( 	'lastlogin_datetime'	=> date("Y-m-d H:i:s")	);
		$where = 'email = "' . $email . '"';
		try {
			$result = $this->update($data, $where);
			if($result)
				return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.updateLastLoginDateTime : " . $e->getMessage());
		}
		return false;
	}

	public function deleteMemberInfo($id){
		$where = 'user_id = ' . $id;
		try {
			$result = $this->delete($where);
			if($result){
				return true;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.deleteMemberInfo : " . $e->getMessage());
		}
		return false;
	}




	public function CheckUniqueEmail($email, $group_id, $user_id = 0) {
	    if($user_id == 0){
	        $select = $this->select ()->where ( "email = ?", $email)->where("group_id = ?" , $group_id);
	    }else{
	        $select = $this->select ()->where ( "email = ?", $email)->where("group_id = ?" ,$group_id )->where( "user_id != ? ", $user_id );
	    }

	    try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return true;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "Member.CheckUniqueEmail : " . $e->getMessage () . "\n\n" . $select );
		}
		return false;
	}


	

	// ---- check unique phone
	public function CheckUniquePhone($post) {
		$select = $this->select ()->where ( "phone =  '" . $post ['phone'] . "'" )->where ( "user_id != '" . $post ['id'] . "'" );
		try {
			$row = $this->fetchRow ( $select );
			if ($row) {
				return true;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "Member.CheckUniquePhone : " . $e->getMessage () . "\n\n" . $select );
		}

		return false;

	}

	public function updateToken($id, $token) {
		$data 		= array('token' => $token);
		$where 		= 'user_id = ' . $id;

		try {
			$result 	= $this->update($data , $where );
			if($result){
				return true;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.updateToken : " . $e->getMessage());
		}
		return false;
	}

	public function validateToken($token, $id){
        $sql = $this->select()->where('token = ?', $token)->where('user_id = ?', $id);
		try {
			$row = $this->fetchRow($sql);
			if($row){
			    return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.validateToken : " . $e->getMessage());
		}
		return false;
	}

	public function updateEmailAddress($field, $value, $id){
	    $data = array( 	$field	=> $value, 'temp_email' => null, 'token' => null, updated_datetime => date("Y-m-d H:i:s")	);

		$where = 'user_id = ' . $id;

		try {
			$result = $this->update($data, $where);
			if($result)
				return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Member.updateEmailAddress : " . $e->getMessage());
		}
		return false;
	}


}