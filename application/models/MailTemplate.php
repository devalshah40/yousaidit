<?php

class Default_Model_MailTemplate extends Zend_Db_Table {
	
	protected $_name = 'mail_template';
	
	/**
	 * Get mail template while sending mail
	 *
	 * @param int $mailid
	 * @return mysql_row / boolean
	 */
	
	public function getTemplate($id)
	 {
		$select = $this->select ()->where ( 'mailid = ?', $id );
		
		
		
		try {
			$result = $this->fetchRow ( $select );
			if ($result) {
				return $result;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "MailTemplates.getTemplate : " . $e->getMessage () );
		}
		return false;
	}
	
	//  -- for listing mail templates implemented succesfully
	
	public function listMailtemplates($sort_field = null, $field = null, $value = null) {
		
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
			$errlog->direct ( "MailTemplates.listMailtemplates : " . $e->getMessage () );
		}
		return false;
	}
	
	// for fetch
	public function mailInfo($mailid) {
		$select = $this->select ()->where ( ' mailid =  ?', $id );
		
		try {
			$result = $this->fetchRow ( $select );
			if ($result) {
				return $result;
			}
		} catch ( Exception $e ) {
			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper ( 'errorlog' );
			$errlog->direct ( "MailTemplates.mailInfo : " . $e->getMessage () );
		}
		return false;
	}
	
	public function updateMailTemplates($post) {
      $data = array(	
      					'description'      => $post ['description'],
      					'subject'          => $post ['subject'],
      					'from_caption'     => $post ['from_caption'],
      					'from_email'       => $post ['from_email'],
      					'mail_text'        => $post ['mail_text'],
      					'last_update_datetime' => date("Y-m-d H:i:s")  );
      
      $where = ' mailid = ' . $post ['id'];
      
     
      try{
      	
         $result = $this->update($data, $where);
         

         if($result){
                     return true;
                    }
        
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("MailTemplates.updateMailTemplates : " . $e->getMessage());
      }
      return false;
   }
   
	
	// for listing
	

}