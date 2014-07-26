<?php

class Default_Model_ApnsDevices extends Zend_Db_Table {

   protected $_name = 'apns_devices';

   public function listMemberDevices($userIdArr, $device_type) {
      if(is_array($userIdArr) && count($userIdArr) >= 1){
         $user_ids = implode(',', $userIdArr);
      }else{
         $user_ids = $userIdArr;
      }

      $select = mysql_escape_string("Select pid, devicetoken, user_id from " . $this->_name . " where user_id IN(" . $user_ids . ") and device_type = " . $device_type);

      try{
         $row = $this->getDefaultAdapter()->fetchAll($select);
         if(count($row) > 0){
            return $row;
         }
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsDevices.listMemberDevices : " . $e->getMessage());
      }
      return false;
   }

   public function getMemberDevicStatus($id) {
      $select = $this->select()->where('pid = ?', $id)->where('status = "active"')->limit(1);

      try{
         $row = $this->fetchRow($select);
         if($row){
            return $row;
         }
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsDevices.getMemberDevicStatus : " . $e->getMessage());
      }
      return false;
   }

   public function registerDevice($data) {

      $sql = "INSERT INTO `apns_devices`
				VALUES (
					NULL,
					'".$data ['user_id']."',
					'".$data ['appname']."',
					'".$data ['appversion']."',
					'".$data ['deviceuid']."',
					'".$data ['devicetoken']."',
					'".$data ['devicename']."',
					'".$data ['devicemodel']."',
					'".$data ['deviceversion']."',
					'".$data ['pushbadge']."',
					'".$data ['pushalert']."',
					'".$data ['pushsound']."',
					'sandbox',
					'active',
					NOW(),
					NOW(),
					'".$data ['device_type']."'
				)
				ON DUPLICATE KEY UPDATE
				`user_id`='".$data ['user_id']."',
				`devicetoken`='".$data ['devicetoken']."',
				`devicename`='".$data ['devicename']."',
				`devicemodel`='". $data ['devicemodel']."',
				`deviceversion`='".$data ['deviceversion']."',
				`pushbadge`='".$data ['pushbadge']."',
				`pushalert`='".$data ['pushalert']."',
				`pushsound`='".$data ['pushsound']."',
				`status`='active',
				`modified`=NOW();";

      try{
         $pid = $this->getDefaultAdapter()->query($sql);
         if($pid){
            return $pid;
         }
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsDevices.registerDevice : " . $e->getMessage());
      }
      return false;
   }

   public function unRegisterDevice($token) {
      $arr = array('status' => 'uninstalled');
      $where = "token = '" . $token . "'";

      try{
         $result = $this->update($arr, $where);
         return true;
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsDevices.unRegisterDevice : " . $e->getMessage());
      }
      return false;
   }

   public function deleteMemberDevice($member_id){
      $where = "member_id = " . $member_id;

      try{
         $result = $this->delete($where);
         return true;
      }catch(Exception $e){
         $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
         $errlog->direct("ApnsDevices.deleteMemberDevice : " . $e->getMessage());
      }
      return false;
   }
}