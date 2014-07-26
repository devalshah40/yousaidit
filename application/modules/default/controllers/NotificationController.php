<?php

class NotificationController extends My_UserController {

    public function init() {
        parent::init();
    }

    public function indexAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $device_type = $this->_getParam('android_device');

        if (empty($device_type)){
            $device_type = 1;
        }else{
            $device_type = 2;
        }

        $pushbadge = $this->_getParam('pushbadge');
        $pushalert = $this->_getParam('pushalert');
        $pushsound = $this->_getParam('pushsound');
        
        $devicename = $this->_getParam('devicename');
        $devicemodel = $this->_getParam('devicemodel');
        $deviceversion = $this->_getParam('deviceversion');
        
        $apnsModel = new Default_Model_ApnsDevices();
        $arr = array(		'user_id'       => $this->_getParam('user_id'),
        					'appname'       => $this->_getParam('appname'),
        					'appversion'    => $this->_getParam('appversion'),
        					'deviceuid'     => $this->_getParam('deviceuid'),
        					'devicetoken'   => $this->_getParam('devicetoken'),
        					'devicename'    => !empty($devicename)?$devicename:null,
        					'devicemodel'   => !empty($devicemodel)?$devicemodel:null,
        					'deviceversion' => !empty($deviceversion)?$deviceversion:null,
        					'pushbadge'     => !empty($pushbadge)?$pushbadge:"-",
        					'pushalert'     => !empty($pushalert)?$pushalert:"-",
        					'pushsound'     => !empty($pushsound)?$pushsound:"-",
        					'created'       => date('Y-m-d H:i:s'),
        					'device_type'   => $device_type            );
        
        $x = print_r($arr, true);
        $this->_helper->errorlog($x);
        
        $pid = $apnsModel->registerDevice($arr);
    }
}