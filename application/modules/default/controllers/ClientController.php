<?php

class ClientController extends My_UserController {

    /**
     * init() function, call init() function of parent class
     *
     */
    public function init() {
        parent::init();
    }

    /**
     * Default Action
     *
     */
    public function indexAction() {
    	$result = $this->_helper->sessioncheck($this->_session->client_session_data, $this->_session->client_session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('client-login.html');
        }
        
     //   $this->view->group_id = $this->_session->client_session_data['group_id'];
        $this->view->leftmenu = $this->view->render('leftmenu.phtml');
    }

    public function clientloginAction() {
        $post = $this->getRequest()->getPost();
        
        if ( !empty($post) ){
            $username = $post['txtUserName'];
            $password = $post['txtPassword'];
            
            $user_type = NULL;
            
            $userModel = new Default_Model_User();
            $clientModel = new Default_Model_Client();
            $clientInfo = $clientModel->clientInfo($username ,3);
            
            if ($clientInfo){
                if ($clientInfo['status'] == 'Inactive'){
                    $this->_flashMessenger->addMessage("Account Inactive");
                    $this->_redirector->gotoUrl('client-login.html');
                } elseif ($clientInfo['status'] == 'Pending'){
                    $this->_flashMessenger->addMessage("Account Pending");
                    $this->_redirector->gotoUrl('client-login.html');
                } elseif ($clientInfo['verified'] == 0){
                    $this->_flashMessenger->addMessage("verification required");
                    $this->_redirector->gotoUrl('client-login.html');
                } else{
                    if (md5($password) == $clientInfo['password']){
                        $clientModel->updateLastLoginDateTime($username);
                        $session_array = array("login_flag" => true, "group_id" => $clientInfo['group_id'], "user_id" => $clientInfo['user_id'], "firstname" => $clientInfo['firstname'], "lastname" => $clientInfo['lastname'], "email" => $clientInfo['email'], "lastlogin_datetime" => empty($clientInfo['lastlogin_datetime']) ? '--' : date("jS M Y h:i A", strtotime($clientInfo['lastlogin_datetime'])));
                        $this->_session->client_session_data = $session_array;
                        $this->_redirector->gotoUrl('client-home.html');
                    } else{
                        $this->_flashMessenger->addMessage("Invalid Email/Password . Client not Found");
                        $this->_redirector->gotoUrl('client-login.html');
                    }
                }
            } else{
                $this->_flashMessenger->addMessage("Invalid Email/Password . Client not Found");
                 
            }
            $this->_redirector->gotoUrl('client-login.html');
        }
        $response = $this->getResponse();
        $response->insert('header', '');
        $response->insert('footer', $this->view->render('footer.phtml'));
    }

	 public function editprofileAction() {
	        $result = $this->_helper->sessioncheck($this->_session->client_session_data, $this->_session->client_session_data['login_flag']);
	        
            if (empty($result)){
	            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
	            $this->_redirector->gotoUrl('client-login.html');
	        }
	
	        $group_id = $this->_session->client_session_data['group_id'];
	        
	        $post = $this->getRequest()->getPost();
	        
	        $this->view->pageTitle = ': Edit Profile';
	        
	        if (! empty($post['cmdCancel'])){
	            $this->_redirector->gotoUrl('client-home.html');
	        }
	        
            
            $clientModel = new Default_Model_Client();
            
            $cModel = new Default_Model_Clientdetails();
            
            $getinfo_id = $cModel->getClientInfo((int) $this->_session->client_session_data['user_id']);
            $this->view->client_detail = $getinfo_id;
            
            $countryModel = new Default_Model_Countries();
            $countryList = $countryModel->listCountry();
            
            $this->view->countryList = $countryList;
            
            $clientInfo = $clientModel->clientInfo((int) $this->_session->client_session_data['user_id'] , 3);
            
            $this->view->clientinfo = $clientInfo;
            
            
            $is_error = false;
            
            if (! empty($post)){
                
                $var_msg = ERROR_PROFILE_NOT_UPDATED . "<br/><br/>";
             /*   echo "<pre>";
                print_r($post);
                echo "</pre>";
              */  if (empty($post['first_name'])){
                    $is_error = true;
                    $var_msg .= EMPTY_FIRST_NAME . "<br/>";
                }
                 
                if (empty($post['last_name'])){
                    $is_error = true;
                    $var_msg .= EMPTY_LAST_NAME . "<br/>";
                }
                
                if (empty($post['company_name'])){
                    $is_error = true;
                    $var_msg .= EMPTY_COMPANY_NAME . "<br/>";
                }
                
                if (empty($post['country'])){
                    $is_error = true;
                    $var_msg .= EMPTY_COUNTRY . "<br/>";
                }
                
                if (empty($post['email']) || $post['email'] == 'E-Mail'){
                    $is_error = true;
                    $var_msg .= EMPTY_EMAIL . "<br/>";
                } else{
                    $valid_email = $this->_helper->Emailvalidate($post['email']);
                    if ($valid_email == false){
                        $is_error = true;
                        $var_msg .= EMAIL_ADDRESS_INVALID . "<br/>";
                    } else{
                        
                    //    $memberModel = new Default_Model_Member();
                        $uniqueEmail = $clientModel->CheckUniqueEmail($post['email'],3,$post['user_id']);
                        if ($uniqueEmail){
                            $is_error = true;
                            $var_msg .= sprintf(EMAIL_ALREADY_REGISTERED, $post['email']) . "<br>";
                        }
                    }
                }
                
                if (! empty($post['password']) && strtolower(trim($post['password'])) != 'password'){
                    if (! empty($post['password']) || ! empty($post['c_password'])){
                        if ($post['password'] != $post['c_password']){
                            $is_error = true;
                            $var_msg .= PASSWORD_NOT_CONFIRMED . "<br/>";
                        } else{
                            if (strlen(trim($post['password'])) < 5){
                                $is_error = true;
                                $var_msg .= PASSWORD_MIN_FIVE_CHARS . "<br>";
                            }
                        }
                    }
                } else{
                    $post['password'] = null;
                    $post['c_password'] = null;
                }
                
                if ($is_error == true){
                    foreach($post as $k => $v){
                        $clientinfo[$k] = $v;
                    }
                    $this->view->client_detail = $getinfo_id;
                    $this->view->clientinfo->firstname = @$post['first_name'];
                    $this->view->clientinfo->lastname =  @$post['last_name'];
                    $this->view->clientinfo->email =	 @$post['email'];
                    $this->view->client_detail->company_name = @$post['company_name'];
                    $this->view->client_detail->country_id = @$post['country'];
                   
                    $this->view->errorMsg = $var_msg;
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                
                } else{

                	$result = $clientModel->updateClient($post, true);
                    if ($result){
                        
                        //	$cModel = new Default_Model_Clientdetails ();
                        

                        $details_id = $cModel->updateClientDetails($post);
                        
                        $clientInfo = $clientModel->clientInfo((int) $post['user_id'] , 3);
                        $this->_session->client_session_data['login_flag'] = true;
                        $this->_session->client_session_data['firstname'] = $clientInfo['firstname'];
                        $this->_session->client_session_data['lastname'] = $clientInfo['lastname'];
                        $this->_session->client_session_data['email'] = $clientInfo['email'];
                        if ($post['c_email'] != $post['email']){
                            $email_token = $this->_helper->randomstringgenerator(24);
                            
                            $result_1 = $clientModel->updateToken($post['user_id'], $email_token);
                            
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            // Send mail to client on old email address, notifying his email address
                            // was updated via profile screen - B E G I N
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            

                            $mailModel = new Default_Model_MailTemplate();
                            $mail_template = $mailModel->getTemplate(4);
                            
                            if (! empty($this->_session->client_session_data['last_name'])){
                                $nm = $this->_session->client_session_data['first_name'] . ' ' . $this->_session->client_session_data['last_name'];
                            } else{
                                $nm = $this->_session->client_session_data['first_name'];
                            }
                            
                            $url = $this->_baseurl . "/undoc/email-address/" . $post['user_id'] . "-" . $email_token;
                            $reject_email_update_link = "<a href='" . $url . "'>Undo E-Mail Change</a>";
                            
                            $message = sprintf($mail_template['mail_text'], $nm, $post['email'], $reject_email_update_link, $url);
                            
                            $mail = new Zend_Mail();
                            $mail->setBodyHTML(stripslashes(nl2br($message)));
                            $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
                            $mail->addTo($post['c_email'], $nm);
                            $mail->setSubject($mail_template['subject']);
                            
                            try{
                                $mail->send();
                            } catch(Exception $e){
                                $this->_helper->errorlog('Send mail to client on old email address, notifying his email address was updated via profile screen' . $e->getMessage());
                            }
                            
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            // Send mail to client on old email address, notifying his email address
                            // was updated via profile screen - E N D
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            

                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            // Send mail to client on new updated email address for confirming his email address - B E G I N
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            

                            $mailModel = new Default_Model_MailTemplate();
                            $mail_template = $mailModel->getTemplate(5);
                            
                            if (! empty($this->_session->client_session_data['last_name'])){
                                $nm = $this->_session->client_session_data['first_name'] . ' ' . $this->_session->client_session_data['last_name'];
                            } else{
                                $nm = $this->_session->client_session_data['first_name'];
                            }
                            
                            $url = $this->_baseurl . "/confirmc/email-address/" . $post['id'] . "-" . $email_token;
                            $confirm_email_update_link = "<a href='" . $url . "'>Confirm E-Mail Address</a>";
                            
                            $message = sprintf($mail_template['mail_text'], $nm, $confirm_email_update_link, $url);
                            
                            $mail = new Zend_Mail();
                            $mail->setBodyHTML(stripslashes(nl2br($message)));
                            $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
                            $mail->addTo($post['email'], $nm);
                            $mail->setSubject($mail_template['subject']);
                            
                            try{
                                $mail->send();
                            } catch(Exception $e){
                                $this->_helper->errorlog($e->getMessage());
                            }
                        
     // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        // Send mail to client on new updated email address for confirming his email address - E N D
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        }
                        
                        $this->_flashMessenger->addMessage(PROFILE_UPDATED);
                    } else{
                        $this->_flashMessenger->addMessage(ERROR_PROFILE_NOT_UPDATED);
                    }
                    $this->_redirector->gotoUrl('client-profile.html');
                }
            
            }

            	$this->view->leftmenu = $this->view->render('leftmenu.phtml');
	            $this->view->clientprofile = $this->view->render('client/clientprofile.phtml');
           
        
            
	 }

  	public function clientlogoutAction() {
        $group_id = $this->_session->client_session_data['group_id'];
        $this->_session->client_session_data = null;
        $this->_session->search_param = null;
        if($group_id == 3){
            $this->_redirector->gotoUrl('client-login.html');
        }else{
            $this->_redirector->gotoUrl('client-login.html');
        }
    }

}