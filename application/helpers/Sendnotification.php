<?php

class Zend_Controller_Action_Helper_Sendnotification extends Zend_Controller_Action_Helper_Abstract {

    function direct($t_id, $tc_id) {
        
        $topic_id = (int)$t_id;
        $topic_category_id = (int)$tc_id;
        
        if (empty($topic_id)){
            return 1;
        } else{
            $errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');
            
            $topicModel = new Default_Model_Topic();
            $topicInfo = $topicModel->TopicInfo($topic_id);
            
            if ($topicInfo){
                if ($topicInfo['notification'] == 'y'){
                    return 2;
                } else{
                    $topicCategModel = new Default_Model_TopicCategories();
                    $topic_categ_info = $topicCategModel->getTopicCategory($topic_id);
                    
                    $topic_country_id = $topicInfo['country_id'];
                    
                    //$errlog->direct('-- 1 --');
                    
                    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                    // send notification for all qualifying members - S T A R T
                    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

                    $userModel = new Default_Model_User();
                    $userList = $userModel->listAllActiveUsers();
                    
                    $userArray = array();
                    
                    foreach($userList as $k => $v){
                        
                        if( $v['notification_status'] == 'y'){                   // check if user has activated notifications
                            
                            if( $topic_country_id == $v['country_id']){          // check if user belongs to the same country as topic being created
                                
                                $categArr = unserialize($v['categories_of_interest']);

                                if( !empty($categArr) && count($categArr) > 0 ){

                                    foreach($categArr as $ck => $cv){
                                        if( $cv == $topic_categ_info['categories_id'] ){    // check if topic category is listed as 'category of interest'
                                            array_push($userArray, $v['user_id']);
                                        }
                                    }

                                }
                            }
                        }

                    }
                    
                    //echo '<pre>';  print_r($userArray);   echo '</pre>';        exit;
                    //$x = print_r($userArray, true);   $errlog->direct('3 -- ' . $x);

                    if ($userArray){
                        $apnsDeviceModel = new Default_Model_ApnsDevices();
                        $apnsDevices = $apnsDeviceModel->listMemberDevices($userArray, 1);
                        
                        $androidDevices = $apnsDeviceModel->listMemberDevices($userArray, 2);
                    } else{
                        $apnsDevices = null;
                        $androidDevices = null;
                    }
                    
                    //$x = print_r($apnsDevices, true);  $errlog->direct('iPhone : ' . $x);
                    //$x = print_r($androidDevices, true);  $errlog->direct('Android : ' . $x);
                    
                    if (! empty($apnsDevices) || ! empty($androidDevices)){
                        $alert_msg = 'New topic titled "' . stripslashes($topicInfo['name']) . '" posted on YouSaidIt';
                        $apnsMessageModel = new Default_Model_ApnsMessage();
                    }
                    
                    // start notification code for iphone users //
                    
                    if (! empty($apnsDevices)){
                        $userDetailsModel = new Default_Model_UserDetails();
                        $iphone_send_flag = false;
                        $iphone_send_count = 0;
                        
                        foreach($apnsDevices as $ak => $av){

                            $result_1 = $userDetailsModel->updateNotificationCount($av['user_id']);
                            $result_2 = $userDetailsModel->getNotificationCount($av['user_id']);
                            
                            if( !empty($result_2)){
                                $nCnt = (int)$result_2['notification_count'];
                            }else{
                                $nCnt = 1;
                            }
                            
                            $message = new Zend_Mobile_Push_Message_Apns();
                            $message->setAlert($alert_msg);
                            $message->setBadge($nCnt);
                            $message->setSound('beep.wav');
                            $message->setId(time());
                            $message->setToken($av['devicetoken']);
         
                            $apns = new Zend_Mobile_Push_Apns();
                            $apns->setCertificate(APPLICATION_PATH . DS . "apns" . DS . 'ck.pem');
         
                            try {
                                $apns->connect(Zend_Mobile_Push_Apns::SERVER_SANDBOX_URI);
                            } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
                                // you can either attempt to reconnect here or try again later
                                $errlog->direct('Zend_Mobile_Push_Exception_ServerUnavailable : ' . $e->getMessage() );
                                exit(1);
                            } catch (Zend_Mobile_Push_Exception $e) {
                                $errlog->direct('APNS Connection Error:' . $e->getMessage() );
                                exit(1);
                            }
         
                            try {
                                $message_id = $apnsMessageModel->newMessage( array('fk_device' => $av['pid'], 'message' => $alert_msg, 'delivery' => date("Y-m-d H:i:s") ) );
                                $send_flag = $apns->send($message);

                                if($send_flag){
                                    $iphone_send_flag = true;
                                    $iphone_send_count++;
                                    $result = $apnsMessageModel->updateMessagePushStatus('delivered', $message_id);
                                }else{
                                    $result = $apnsMessageModel->updateMessagePushStatus('failed', $message_id);
                                    $result_1 = $userDetailsModel->updateNotificationCount($av['user_id'], 'subtract');
                                }

                                usleep(10000);

                            } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
                                
                                $result = $apnsMessageModel->updateMessagePushStatus('failed', $message_id);
                                
                                // you would likely want to remove the token from being sent to again
                                $errlog->direct('Zend_Mobile_Push_Exception_InvalidToken : ' . $e->getMessage() );
                                
                            } catch (Zend_Mobile_Push_Exception $e) {
                                
                                $result = $apnsMessageModel->updateMessagePushStatus('failed', $message_id);
                                
                                // all other exceptions only require action to be sent
                                $errlog->direct('Zend_Mobile_Push_Exception : ' . $e->getMessage() );
                            }
        
                            $apns->close();
                        }
                        
                        if($iphone_send_flag == true){
                            $errlog->direct('iPhone Notification sent to ' . $iphone_send_count . ' devices ');
                        }else{
                            $errlog->direct('iPhone Notification sent to 0 devices ');
                        }
                    }
                    
                    // end notification code for iphone users //
                    
                    // @  @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ @ //
                    
                    // start notification code for android users //
                    
                    if (! empty($androidDevices)){
                        $userDetailsModel = new Default_Model_UserDetails();
                        $android_send_flag = false;
                        $android_send_count = 0;
                        
                        foreach($androidDevices as $ak => $av){
                            $message = new Zend_Mobile_Push_Message_Gcm();
                            $message->setId(time());
                            $message->addToken($av['devicetoken']);
                            $message->setData(array('message' => $alert_msg));
                            
                            $gcm = new Zend_Mobile_Push_Gcm();
                            $gcm->setApiKey('AIzaSyDOQJSGzC0g25Ee1Q95GhX9PUdnZdyduZc');
                            
                            $response = false;
                            
                            try {
                                $message_id = $apnsMessageModel->newMessage( array('fk_device' => $av['pid'], 'message' => $alert_msg, 'delivery' => date("Y-m-d H:i:s") ) );
                                $response = $gcm->send($message);
                                if( $response ){
                                    $android_send_flag = true;
                                    $android_send_count++;
                                    $result = $apnsMessageModel->updateMessagePushStatus('delivered', $message_id);
                                }else{
                                    $result = $apnsMessageModel->updateMessagePushStatus('failed', $message_id);
                                }
                                usleep(10000);
                            } catch (Zend_Mobile_Push_Exception $e) {
                                // all other exceptions only require action to be sent or implementation of exponential backoff.
                                $errlog->direct( $e->getMessage() );
                                $errlog->direct( 'Failed for : ' . $av['pid'] );
                            }
                            
                            // handle all errors and registration_id's
                            foreach ($response->getResults() as $k => $v) {
                                if ($v['registration_id']) {
                                    $msg = sprintf("%s has a new registration id of: %s", $k, $v['registration_id']);
                                }
                                if ($v['error']) {
                                    $msg = sprintf("%s had an error of: %s", $k, $v['error']);
                                }
                                if ($v['message_id']) {
                                    $msg = sprintf("%s was successfully sent the message, message id is: %s", $k, $v['message_id']);
                                }
                                $errlog->direct('Android GCM : ' . $msg);
                            }
                        }
                        if($android_send_flag == true){
                            $errlog->direct('Android Notification sent to ' . $android_send_count . ' devices ');
                        }else{
                            $errlog->direct('Android Notification sent to 0 devices ');
                        }
                    }
                    
                    // start notification code for android users //
                
                    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                    // send notification for all qualifying members - E N D
                    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
                    
                    if( (int)$iphone_send_count > 0 || (int)$android_send_count > 0 ){
                        return 3;
                    }

                }
            } else{
                return 4;
            }
        }
    }
}