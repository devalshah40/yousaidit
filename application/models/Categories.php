<?php

class Default_Model_Categories extends Zend_Db_Table {

	protected $_name = 'categories';

    public function insertCategory($post) {
		$data = array( 	'categories_name'         => $post['categories_name'],
						'categories_description'  => $post['categories_description'],
						'add_date'                =>  date("Y-m-d"),
		                'status'	              =>  $post['status']		);

		try {
			$categories_id = $this->insert($data);
			if($categories_id){
			    return $categories_id;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Categories.insertCategory : " . $e->getMessage());
		}
		return false;
	}

	public function updateCategory($post) {
		$data = array(  'categories_name'         => $post['categories_name'],
						'categories_description'  => $post['categories_description'],
						'updated_datetime'   => date("Y-m-d H:i:s"),
						'status'	         => $post['status']		);

        $where = ' categories_id = ' . $post['categories_id'];

		try {
			$result = $this->update($data, $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Categories.updateCategory : " . $e->getMessage());
		}
		return false;
	}

	public function deleteCategory($id) {
        $where = ' categories_id = ' . $id;
		try {
			$result = $this->delete($where);
			if($result){
				return true;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Categories.deleteCategory : " . $e->getMessage());
		}
		return false;
	}

	public function CategoryInfo($id) {
	    $select = $this->select()->where(' categories_id =  ?', $id);
		try {
			$result = $this->fetchRow($select);
            if($result){
                return $result;
            }
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Categories.CategoryInfo : " . $e->getMessage());
		}
		return false;
	}

	public function listcategories($sort_field, $field = null, $value = null, $strict_filter = false) {
		if( !empty($field) && !empty($value) && $strict_filter == false){
			$select = $this->select()->where($field . " like " . "'%".$value."%'")->order($sort_field);
		}else{
		    if( $strict_filter == true ){
		        $select = $this->select()->where($field . " = " . "'".$value."'")->order($sort_field);
		    }else{
                $select = $this->select()->order($sort_field);
		    }
		}
			    
		try {
			$row = $this->fetchAll($select);
			if(count($row) > 0) {
				return $row;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Categories.listcategories : " . $e->getMessage());
		}

		return false;
	}
}