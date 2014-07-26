<?php

class Default_Model_UserInterests extends Zend_Db_Table {
	
	protected $_name = 'user_interest';
	
	public function listInterest() {
		$select = $this->select ();
		try {
			$result = $this->fetchAll ( $select );
			if (count ( $result ) > 0) {
				return $result;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "UserInterests.listInterest : " . $e->getMessage () );
		}
		return false;
	}
	
	public function listInterests($sort_field, $field = null, $value = null, $strict_filter = false) {
		
		if (! empty ( $field ) && ! empty ( $value ) && $strict_filter == false) {
			
			$select = $this->select ()->where ( $field . " like " . "'%" . $value . "%'" )->order ( $sort_field );
		
		} else {
			
			if ($strict_filter == true) {
				
				$select = $this->select ()->where ( $field . " = " . "'" . $value . "'" )->order ( $sort_field );
			
			} else {
				
				 $select = $this->select ()->order ( $sort_field );
			
			}
		
		}
		
		try {
			
			$row = $this->fetchAll ( $select );
			
			if (count ( $row ) > 0) {
				
				return $row;
			
			}
		
		} catch ( Exception $e ) {
			
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			
			$errlog->direct ( "UserInterests.listInterests : " . $e->getMessage () );
		
		}
		
		return false;
	
	}

	public function UserInterestInfo($id) {

	    $select = $this->select()->where(' intrest_id =  ?', $id);



		try {

			$result = $this->fetchRow($select);

            if($result){

                return $result;

            }

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct ( "UserInterests.UserInterestInfo : " . $e->getMessage () );
		
		}

		return false;

	}
	
	
	public function UserInterestInfobyname($name) {

	    $select = $this->select()->where(' name =  ?', $name);



		try {

			$result = $this->fetchRow($select);

            if($result){

                return $result;

            }

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct ( "UserInterests.UserInterestInfobyname : " . $e->getMessage () );
		
		}

		return false;

	}

	public function updateUserInterestInfo($post) {



		$data = array(  'name'         => $post['name'],
				
						);



        $where = ' intrest_id = ' . $post['intrest_id'];



		try {

			$result = $this->update($data, $where);

			return true;

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("UserInterests.updateUserInterestInfo : " . $e->getMessage());

		}

		return false;

	}
	

	public function deleteUserInterest($id) {

        $where = ' intrest_id = ' . $id;

		try {

			$result = $this->delete($where);

			if($result){

				return true;

			}



		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("UserInterests.deleteUserInterest : " . $e->getMessage());

		}

		return false;

	}
	
	public function insertUserInterest($post) {

		$data = array( 	'name'         => $post['name']
					);



		try {

			$intrest_id = $this->insert($data);

			if($intrest_id){

			    return $intrest_id;

			}

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("UserInterests.insertUserInterest : " . $e->getMessage());

		}

		return false;

	}

}