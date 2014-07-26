<?php

class Default_Model_ApnsMessage extends Zend_Db_Table {

   protected $_name = 'apns_messages';

   public function newMessage($data) {
      $arr = array('fk_device' => $data ['fk_device'], 'message' => $data ['message'], 'delivery' => $data ['delivery'], 'status' => 'queued', 'created' => date("Y-m-d H:i:s"));
      
      try{
         $pid = $this->insert($arr);
         if($pid){
            return $pid;
         }
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsMessage.newMessage : " . $e->getMessage());
      }
      return false;
   }

   public function updateMessagePushStatus($status, $id) {
      $arr = array('status' => $status);
      $where = 'pid = ' . $id;
      try{
         $result = $this->update($arr, $where);
         return true;
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsMessage.updateMessagePushStatus : " . $e->getMessage());
      }
      return false;
   }

   public function fetchMessage() {
      $sql = "SELECT
				`apns_messages`.`pid`,
				`apns_messages`.`message`,
				`apns_devices`.`devicetoken`,
				`apns_devices`.`development`

				FROM `apns_messages`

				LEFT JOIN `apns_devices` ON
				`apns_devices`.`pid` = `apns_messages`.`fk_device`

				WHERE `apns_messages`.`status`='queued'

				AND `apns_messages`.`delivery` <= '".date('Y-m-d H:i:s')."'

				AND `apns_devices`.`status`='active'

				GROUP BY `apns_messages`.`fk_device`

				ORDER BY `apns_messages`.`created` ASC

				LIMIT 100;";

      try{
         $result = $this->getDefaultAdapter()->fetchAll($sql);
         if($result){
            return $result;
         }
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsMessage.fetchMessage : " . $e->getMessage());
      }
      return false;
   }
}