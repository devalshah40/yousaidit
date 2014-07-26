<?php

class Default_Model_Configuration extends Zend_Db_Table {

	protected $_name = 'configuration';

	/**
	 * Get configuration parameters as per config_group_id
	 *
	 * @param int $config_group_id
	 * @return mysql_result_set / boolean
	 */
	public function getGroupConfigOptions($config_group_id) {
		$select = $this->select()->where('config_group_id = ?', $config_group_id);

		try {
			$result = $this->fetchAll($select);
			if($result) {
				return $result;
			}
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Configuration.getGroupConfigOptions : " . $e->getMessage());
		}
		return false;
	}

	/**
	 * Updates configuration parameter
	 *
	 * @param string $value
	 * @param int $config_id
	 * @return boolean
	 */
	public function updateConfigOption($value, $config_id){
		$data  = array('config_value' => $value);
		$where = " config_id = '".$config_id."'";

		try {
			$result = $this->update($data , $where);
			return true;
		}catch (Exception $e){
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
			$errlog->direct("Configuration.updateConfigOption : " . $e->getMessage());
		}
		return false;
	}

}