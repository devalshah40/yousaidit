<?php

class Default_Model_Countries extends Zend_Db_Table {
    
    protected $_name = 'countries';

    public function listCountry() {
        $select = $this->select()->order('country_name');
        
        try{
            $result = $this->fetchAll($select)->toArray();
            if (count($result) > 0){
                return $result;
            }
        } catch(Exception $e){
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            $errlog->direct("Countries.listCountry : " . $e->getMessage());
        }
        return false;
    }

    public function getCountryName($country_id){
    	
    	 $select = $this->select()->where(' country_id =  ?', $country_id);


		try {

			$result = $this->fetchRow($select)->toArray();

            if($result){

                return $result['country_name'];

            }

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("Countries.getCountryName : " . $e->getMessage());

		}

		return false;
    	
    }
     
}