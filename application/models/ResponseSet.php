<?php

class Default_Model_ResponseSet extends Zend_Db_Table {

	protected $_name = 'response_sets';

	// delete response sets


	public function deleteResponseSetInfo($id) {

		$where = 'rs_id = ' . $id;
		try {
			$result = $this->delete ( $where );
			if ($result) {
				return true;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "ResponseSet.deleteResponseSetInfo : " . $e->getMessage () );
		}
		return false;
	}

	public function listResponseSets($sort_field = 'rs_id', $field = null, $value = null) {

		if (! empty ( $field ) && ! empty ( $value )) {
			$select = $this->select ()->where ( $field . " like " . "'%" . $value . "%'" )->order ( $sort_field );
		} else {
			$select = $this->select ()->order ( $sort_field );
		}

		try {
			$row = $this->fetchAll ( $select );
			if (count ( $row ) > 0) {
				return $row;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "ResponseSet.listResponseSets : " . $e->getMessage () );
		}
		return false;
	}

	// add response sets


	public function insertResponseSet($post) {

		$data = array ('title' => $post ['title'], 'description' => $post ['description'], 'add_date' => date ( "Y-m-d" ), 'status' => $post ['status'] );

		try {
			$rs_id = $this->insert ( $data );
			if ($rs_id) {
				return $rs_id;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "ResponseSet.insertResponseSet: " . $e->getMessage () );
		}
		return false;
	}

	// update response sets


	public function updateResponseSet($post) {

		$data = array ('title' => $post ['title'], 'description' => $post ['description'], 'last_update_datetime' => date ( "Y-m-d H:i:s" ), 'status' => $post ['status'] );

		$where = ' rs_id = ' . $post ['id'];

		try {
			$result = $this->update ( $data, $where );

			if ($result) {
				return $result;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "ResponseSet.updateResponseSet : " . $e->getMessage () );
		}
		return false;
	}

	// fetch data to from of response sets
	public function responseInfo($id) {
	    $rs_id = (int)$id;

	    if( !empty($rs_id) && is_integer($rs_id)){
	        $select = $this->select ()->where ( ' rs_id =  ?', $id );
	    }else{
	        if( !empty($id) && is_string($id)){
	            $select = $this->select ()->where ( ' title =  ?', $id );
	        }
	    }

		try {
			$result = $this->fetchRow ( $select );
			if ($result) {
				return $result;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "ResponseSet.responseInfo : " . $e->getMessage () );
		}
		return false;
	}

    public function listUnLinkedResponseSet($qid) {
        $select = "Select * from " . $this->_name . " where rs_id not in ( Select rs_id from question_response_sets where question_id = " . $qid . ") order by title ";

		try {
			$result = $this->getDefaultAdapter()->fetchAll($select);
			if(count($result) > 0) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("ResponseSet.listUnLinkedResponseSet : " . $e->getMessage());
		}
		return false;
	}


}