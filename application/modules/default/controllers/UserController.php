<?php

class UserController extends My_UserController {

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
 
    	
        $post = $this->getRequest()->getPost();
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
  		$viewall = $this->_getParam('id');
  		 if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
      	  } else{
      	  
      	  	if(empty($post['categort_ids'])){
        		$categort_ids = $this->_session->session_data ['categort_ids'];
        	}else{
        		$this->_session->session_data ['categort_ids'] = implode(',', $post['categort_ids']);
        		$categort_ids = implode(',', $post['categort_ids']);
        	}
			$this->view->categort_ids = $categort_ids;
        	$countryModel = new Default_Model_Countries ();
			$countryList = $countryModel->listCountry ();
			$countryName = $countryModel->getCountryName ($this->_session->session_data ['country_id']);
			$this->view->countryList = $countryList;
			$CategoryModel = new Default_Model_Categories ();
			$catInfo = $CategoryModel->listcategories ( 'categories_name', 'status', 'Active', $strict_filter = false );
			$this->view->catInfo = $catInfo;
        	$pg = $this->_getParam ( 'page' );
			$this->view->pg = ! empty ( $pg ) ? $pg : 1;
			
			$user_id 			= ( int ) $this->_session->session_data ['user_id'];
			$id = $this->_getParam ( 'country_id' );
		  	if(empty($id))
			{
				$country_id 			= ( int ) $this->_session->session_data ['topic_country_id'];
			}else{
				$this->_session->session_data ['topic_country_id'] = $id;
        		$country_id = $id;
			}
			$recent = $this->_getParam('recent');
	        if(empty($recent))
			{
				$recent 			= ( int ) $this->_session->session_data ['topic_recent'];
			}else{
				$this->_session->session_data ['topic_recent'] = $recent;
			}
			$this->view->recent   = $recent;
  			/*if(empty($recent))
			{
				$recent 			= ( int ) $this->_session->session_data ['topic_recent'];
			}else{
				$this->_session->session_data ['topic_recent'] = $recent;
        		$recent = $recent;
			}*/
			$this->view->country_id = $this->_session->session_data ['topic_country_id'] ;
		//	$this->view->user_country_name = $countryName ;
			$this->view->user_country_id = $this->_session->session_data ['country_id'];
			$TopicModelResponse = new Default_Model_TopicResponse();
			$topicTen = $TopicModelResponse->getTopTenTopics(TOPIC_RANK_LIMIT , $viewall ,$country_id ,$categort_ids ,$recent , $user_id);
			$arr = array();
			if(!empty($topicTen)){
				foreach ($topicTen as $k => $v){
					$checkiftopictaken = $TopicModelResponse->getTopicRedirect ( $v['topic_id'], $user_id );
					$v ['taken'] = ! empty ( $checkiftopictaken ) ? 1 : 0;
					array_push ( $arr, $v );
				}
				$topicTen = $arr;
			}
			if( !empty(	$viewall ) && !empty($topicTen))
			{
				$this->view->paginator = Zend_Paginator::factory ( $topicTen );
				$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
				$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
				$this->view->paginator->setCurrentPageNumber ( $pg );
			}else{
				$this->view->topicTen = $topicTen;
			}
			$this->view->viewall   = $viewall;
	        $this->view->leftmenu = $this->view->render('leftmenu.phtml');
        }
    }

    public function processloginAction() {
        $post = $this->getRequest()->getPost();
        
        
        if (empty($post['email']) && empty($post['txtPassword'])){
            $this->_flashMessenger->addMessage("Please Enter Login info");
            $this->_redirector->gotoUrl('index.html');
        } else{
            
            $email = $post['email'];
            $password = $post['txtPassword'];
            
            $user_type = NULL;
            
            $userModel = new Default_Model_Member();
            $userInfo = $userModel->memberInfo($email, 2);
            
            if ($userInfo){
                if ($userInfo['status'] == 'Inactive'){
                    $this->_flashMessenger->addMessage("Account Inactive");
                    $this->_redirector->gotoUrl('index.html');
                } elseif ($userInfo['status'] == 'Pending'){
                    $this->_flashMessenger->addMessage("Account Pending");
                    $this->_redirector->gotoUrl('index.html');
                } elseif ($userInfo['verified'] == 0){
                    $this->_flashMessenger->addMessage("verification required");
                    $this->_redirector->gotoUrl('index.html');
                } else{
                    if (md5($password) == $userInfo['password']){
                        $userModel->updateLastLoginDateTime($email);
                        
                        $uDetailslModel = new Default_Model_UserDetails ();
						
						$userIDeatils = $uDetailslModel->getUserInfo ( $userInfo ['user_id'] );
                        
                        $session_array = array("login_flag" => true, "group_id" => $userInfo['group_id'], "user_id" => $userInfo['user_id'],"country_id" => $userIDeatils['country_id'] ,"topic_country_id" => -1 , "topic_recent" => 1 , "categort_ids" => '' , "firstname" => $userInfo['firstname'], "lastname" => $userInfo['lastname'], "email" => $userInfo['email'], "lastlogin_datetime" => empty($userInfo['lastlogin_datetime']) ? '--' : date("jS M Y h:i A", strtotime($userInfo['lastlogin_datetime'])));
                        
                        $this->_session->session_data = $session_array;
                        
                        // get the only active survey from db
                    //    $surveyModel = new Default_Model_Survey();
                     //   $surveyInfo = $surveyModel->getfrontendSurvey('Active');
                        $surveyInfo = '';
                        if (!empty($surveyInfo)){
                            // check if a user has already taken that survey
                            $customer_surveyModel = new Default_Model_CustomerSurvey();
                            $surveyTakenInfo = $customer_surveyModel->SurveyTakenInfo((int) $this->_session->session_data['user_id'], $surveyInfo['survey_id']);
                            
                            // if survey is not taken then redirect to take survey page
                            if ( empty($surveyTakenInfo) ){
                                $this->_redirector->gotoUrl('take-survey.html');
                            }else{
                                $this->_redirector->gotoUrl('view-topics.html');
                            }
                        } else{
                            $this->_redirector->gotoUrl('view-topics.html');
                        }
                    
                    } else{
                        $this->_flashMessenger->addMessage("Email or password incorrect");
                        $this->_redirector->gotoUrl('index.html');
                    }
                }
            } else{
                $this->_flashMessenger->addMessage("Invalid email/password entered");
            }
            $this->_redirector->gotoUrl('index.html');
        }
    }

    public function recoverpasswordAction() {
    
        $post = $this->getRequest()->getPost();
	//	$user_type = $this->_getParam('user_type');
   
        $this->view->pageTitle = ': Recover Lost Password';
        
        $this->view->header_title = 'Recover Password';
        $response = $this->getResponse();
        
        if (! empty($post)){
        	
            if (! empty($post['cmdCancel'])){
		           $this->_redirector->gotoUrl('index.html');
			}
        	
        	$group_id = $post['user_type'];
            
            $userModel = new Default_Model_Member();
            $field = 'user_id';
            
           if (! empty($post['txtEmail'])){
                $valid_email = $this->_helper->Emailvalidate($post['txtEmail']);
                if ($valid_email){
                    $result = $userModel->memberInfo($post['txtEmail'], $group_id);
                } else{
                    $this->_flashMessenger->addMessage(EMAIL_ADDRESS_INVALID);
                }
            }
            
            if ($result){
                $new_pwd = $this->_helper->randomstringgenerator(10);
                $pwd_result = $userModel->resetPassword($result[$field], md5($new_pwd));
                if ($pwd_result){
                    
                    if (! empty($result['lastname'])){
                        $nm = $result['firstname'] . ' ' . $result['lastname'];
                    } else{
                        $nm = $result['firstname'];
                    }
                    
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    // Send mail to user with new user password - B E G I N
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    

                    $mailModel = new Default_Model_MailTemplate();
                    $mail_template = $mailModel->getTemplate(2);
                    
                    $message = sprintf($mail_template['mail_text'], $nm, $new_pwd);
                    
                    $mail = new Zend_Mail();
                    $mail->setBodyHTML(stripslashes(nl2br($message)));
                    $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
                    $mail->addTo($result['email'], $nm);
                    $mail->setSubject($mail_template['subject']);
                    
                    try{
                        $mail->send();
                    } catch(Exception $e){
                        $this->_helper->errorlog('Send mail to user with new user password :' . $e->getMessage());
                    
                    }
                    
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    // Send mail to user with new user password - E N D
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    

                    $this->_flashMessenger->addMessage(NEW_PWD_MAILED);
                    $this->_redirector->gotoUrl('index.html');
                } else{
                    $this->_flashMessenger->addMessage(ERRORS_ENCOUNTERED);
                }
            } else{
                $this->_flashMessenger->addMessage(USER_NOT_FOUND);
            }
            $this->_redirector->gotoUrl('recover-password.html');
        }
        $response = $this->getResponse();
        $response->insert('header', '');
        $response->insert('footer', $this->view->render('footer.phtml'));
    }

    public function signupAction() {
        $this->view->pageTitle = ': Signup';
           
        $post = $this->getRequest()->getPost();
        
        $countryModel = new Default_Model_Countries();
        $countryList = $countryModel->listCountry();
        $this->view->countryList = $countryList;
        
        // user intrest passed from countried model
        $userInterestModel = new Default_Model_UserInterests();
        $interesteList = $userInterestModel->listInterest();
        $this->view->interesteList = $interesteList;
        
        $memberModel = new Default_Model_Member();
        
        if(!empty($post['mid']) && $post['mid'] == 'mid')
        {
        	          if (! empty($post)){
			            if (! empty($post['cmdCancel'])){
			                $this->_redirector->gotoUrl('index.html');
			            }
			            
			            $is_error = false;
			            $var_msg = 'Error(s) encountered.' . "<br/><br/>";
			            
			            if (empty($post['first_name']) || $post['first_name'] == 'First Name'){
			                $is_error = true;
			                $var_msg .= EMPTY_FIRST_NAME . "<br/>";
			            }
			            
        	           if (empty($post['last_name']) || $post['last_name'] == 'Last Name'){
			                $is_error = true;
			                $var_msg .= EMPTY_LAST_NAME . "<br/>";
			            }
			            
        	           if (empty($post['email']) || $post['email'] == 'E-Mail'){
			                $is_error = true;
			                $var_msg .= EMPTY_EMAIL . "<br/>";
			            }else{
			                $valid_email = $this->_helper->Emailvalidate($post['email']);
			                $valid_email1 = $this->_helper->Emailvalidate($post['email_c']);
			                
			                if ($valid_email == false || $valid_email1 == false){
			                    $is_error = true;
			                    $var_msg .= EMAIL_ADDRESS_INVALID . "<br/>";
			                }elseif($valid_email == true && $valid_email1 == true){
			                    if ($post['email'] != $post['email_c']){
					                $is_error = true;
					                $var_msg .= EMAIL_CONFIRM_EMAIL_NOT_MATCH . "<br/>";
					            }else{
    			                    $memberModel = new Default_Model_Member();
    			                    $uniqueEmail = $memberModel->CheckUniqueEmail($post['email'], 2);

    			                    if ($uniqueEmail){
    			                        $is_error = true;
    			                        $var_msg .= sprintf(EMAIL_ALREADY_REGISTERED, $post['email']) . "<br>";
    			                    }
					            }
			                }
			            }
			         	  	    
        	          if (empty($post['country'])){
			                $is_error = true;
			                $var_msg .= EMPTY_COUNTRY . "<br/>";
			            }
			            
			            if (empty($post['password']) || $post['password'] == 'Password'){
			                $is_error = true;
			                $var_msg .= EMPTY_PASSWORD . "<br/>";
			            } else{
			                if ($post['password'] != $post['cpassword']){
			                    $is_error = true;
			                    $var_msg .= PASSWORD_NOT_CONFIRMED . "<br/>";
			                } else{
			                    if (strlen(trim($post['password'])) < 5){
			                        $is_error = true;
			                        $var_msg .= PASSWORD_MIN_FIVE_CHARS . "<br>";
			                    }
			                }
			            }
			            if (empty($post['dob'])){
			                $is_error = true;
			                $var_msg .= INVALID_DOB . "<br/>";
			            }
			           
        	            if (empty($post['gender'])){
			                $is_error = true;
			                $var_msg .= EMPTY_GENDER . "<br/>";
			            }
			            
			          // array of interest passed
			            if (! empty($post['chkInterest'])){
			                $chk_interest = implode(",", $post['chkInterest']);
			                
			                $post['interest'] = $chk_interest;
			            }
			            else
			            {
			            //	$is_error = true;
			            //	$var_msg .= EMPTY_INTEREST . "<br/>";
			            }
			            if ($this->validateCaptcha($post['captcha']) == false){
			                $is_error = true;
			                $var_msg .= INVALID_SECURITY_CODE . "<br/>";
			            }
			            
			            if ($is_error == false){
			                $post['token'] = $this->_helper->randomstringgenerator(20);
			                
			                $user_id = $memberModel->memberSignup($post);
			                
			                $post['user_id'] = $user_id;
			                
			                if ($user_id){
			                    
			                    $uDetailslModel = new Default_Model_UserDetails();
			                    
//			                    $categoryModel = new Default_Model_Categories();
//			                    $categories = $categoryModel->listcategories('categories_name');
//
//			                    $categArr = array();
//			                    foreach($categories as $ck => $cv){
//			                        array_push($categArr, $cv['categories_id']);
//			                    }
//			                    $post['categories_of_interest'] = serialize($categArr);

			                    $post['categories_of_interest'] = null;
			                    
			                    $details_id = $uDetailslModel->insertUserDetails($post);
			                    
			                    $this->_flashMessenger->addMessage(MEMBER_ACCOUNT_CREATED);
			                    
			                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			                    // Send mail to member with activation link - B E G I N
			                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			
			                    $this->view->email = $post['email'];
			                    
			                    $nm = $post['first_name'];
			                    $nm .= ! empty($post['last_name']) ? ' ' . $post['last_name'] : '';
			                    
			                    $mailModel = new Default_Model_MailTemplate();
			                    $mail_template = $mailModel->getTemplate(1);
			                    
			                    $url = $this->_baseurl . "/verify-member/" . $user_id . "-" . $post['token'];
			                    $activation_link = "<a href='" . $url . "'>Verify Now</a>";
			                    $message = sprintf($mail_template['mail_text'], $nm, $activation_link, $url, $post['email'], $post['password']);
			                    
			                    $mail = new Zend_Mail();
			                    $mail->setBodyHTML(stripslashes(nl2br($message)));
			                    $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
			                    $mail->addTo($post['email'], $nm);
			                    $mail->setSubject($mail_template['subject']);
			                    
			                    try{
			                        $mail->send();
			                    } catch(Exception $e){
			                        $this->_helper->errorlog(" Send mail to member with activation link : " . $e->getMessage());
			                    }
			                
			                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			                    // Send mail to member with activation link - E N D
			                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~

			                } else{
			                    $this->_flashMessenger->addMessage(MEMBER_ACCOUNT_NOT_CREATED);
			                }
			                $this->_redirector->gotoUrl('index.html');
			            } else{
			            	
			                foreach($post as $k => $v){
			                    $this->view->$k = $v;
			                }
			                  
			                $this->view->errorMsg = $var_msg;
			            
			            }
			        } else{
			            // show member signup form
			            $arr_token = $this->_session->arr_token;
			        
			        }
        }
        else if(!empty($post['cid']) && $post['cid'] == 'cid')
        {
                if (! empty($post)){
			            if (! empty($post['cmdCancel'])){
			                $this->_redirector->gotoUrl('index.html');
			            }
			            
			            $is_error = false;
			            $var_msg = 'Error(s) encountered.' . "<br/><br/>";
			            
			            // member and contact model declared for email and unique username validation
			            $clientModel = new Default_Model_Client();
			            
			            if (empty($post['first_name']) || $post['first_name'] == 'First Name'){
			                $is_error = true;
			                $var_msg .= EMPTY_FIRST_NAME . "<br/>";
			            }
			            
                   
        	            if (empty($post['last_name']) || $post['last_name'] == 'Last Name'){
			                $is_error = true;
			                $var_msg .= EMPTY_LAST_NAME . "<br/>";
			            }
			            
			            // check for empty client contact in case of client type selected
			            if (! empty($post['client_type']) && $post['client_type'] == "client_contact"){
			                if (empty($post['client_id'])){
			                    $is_error = true;
			                    $var_msg .= EMPTY_CLIENT_CONTACT . "<br/>";
			                }
			            }
			            
			            if (empty($post['company_name']) || $post['company_name'] == 'Company Name'){
			                $is_error = true;
			                $var_msg .= EMPTY_COMPANY_NAME . "<br/>";
			            }
			            
               			if (empty($post['email']) || $post['email'] == 'E-Mail'){
			                $is_error = true;
			                $var_msg .= EMPTY_EMAIL . "<br/>";
			            }else{
			                $valid_email = $this->_helper->Emailvalidate($post['email']);
			                $valid_email1 = $this->_helper->Emailvalidate($post['email_c']);
			                
			                if ($valid_email == false || $valid_email1 == false){
			                    $is_error = true;
			                    $var_msg .= EMAIL_ADDRESS_INVALID . "<br/>";
			                }elseif($valid_email == true && $valid_email1 == true){
			                    if ($post['email'] != $post['email_c']){
					                $is_error = true;
					                $var_msg .= EMAIL_CONFIRM_EMAIL_NOT_MATCH . "<br/>";
					            }else{
    			                    $memberModel = new Default_Model_Member();
    			                    $uniqueEmail = $memberModel->CheckUniqueEmail($post['email'], 3);

    			                    if ($uniqueEmail){
    			                        $is_error = true;
    			                        $var_msg .= sprintf(EMAIL_ALREADY_REGISTERED, $post['email']) . "<br>";
    			                    }
					            }
			                }
			            }
			            
			            if (empty($post['password']) || $post['password'] == 'Password'){
			                $is_error = true;
			                $var_msg .= EMPTY_PASSWORD . "<br/>";
			            } else{
			                if ($post['password'] != $post['cpassword']){
			                    $is_error = true;
			                    $var_msg .= PASSWORD_NOT_CONFIRMED . "<br/>";
			                } else{
			                    if (strlen(trim($post['password'])) < 5){
			                        $is_error = true;
			                        $var_msg .= PASSWORD_MIN_FIVE_CHARS . "<br>";
			                    }
			                }
			            }
			            
                   if (empty($post['country'])){
			                $is_error = true;
			                $var_msg .= EMPTY_COUNTRY . "<br/>";
			            }
			            
			            if ($this->validateCaptcha($post['captcha']) == false){
			                $is_error = true;
			                $var_msg .= INVALID_SECURITY_CODE;
			            }
			            
			            if ($is_error == false){
			                $post['token'] = $this->_helper->randomstringgenerator(20);
			                
			                $client_id = $clientModel->clientSignup($post);
			                $this->_flashMessenger->addMessage(CLIENT_ACCOUNT_CREATED);
			                $url = $this->_baseurl . "/verify-client/" . $client_id . "-" . $post['token'];
			                
			                if ($client_id){
			                    
			                    $post['user_id'] = $client_id;
			                    
			                    $cDetailslModel = new Default_Model_Clientdetails();
			                    
			                    $details_id = $cDetailslModel->insertClientDetails($post);
			                    
			                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			                    // Send mail to client / client contact with activation link - B E G I N
			                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			                    
			
			                    $nm = ! empty($post['lastname']) ? $post['firstname'] . ' ' . $post['lastname'] : $post['firstname'];
			                    
			                    $mailModel = new Default_Model_MailTemplate();
			                    $mail_template = $mailModel->getTemplate(1);
			                    
			                    $activation_link = "<a href='" . $url . "'>Verify Now</a>";
			                    $message = sprintf($mail_template['mail_text'], $nm, $activation_link, $url, $post['email'], $post['password']);
			                    
			                    $mail = new Zend_Mail();
			                    $mail->setBodyHTML(stripslashes(nl2br($message)));
			                    $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
			                    $mail->addTo($post['email'], $nm);
			                    $mail->setSubject($mail_template['subject']);
			                    
			                    try{
			                        $mail->send();
			                    } catch(Exception $e){
			                        $this->_helper->errorlog('Send mail to client / client contact with activation link : ' . $e->getMessage());
			                    }
			                
			     // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			                // Send mail to client / client contact with activation link - E N D
			                // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			                
			
			                } else{
			                    $this->_flashMessenger->addMessage(SIGNUP_NOT_SUCESSFULL);
			                }
			                
			                $this->_redirector->gotoUrl('index.html');
			            } else{
			                foreach($post as $k => $v){
			                	$j = $k."1";
			                    $this->view->$j = $v;
			                }
			                $this->view->valueerror = 1;
			                $this->view->errorMsg1 = $var_msg;
			            
			            }
			        } else{
			        
			        }
        }
        $response = $this->getResponse();
        $response->insert('header', '');
        $response->insert('footer', $this->view->render('footer.phtml'));
    }

    public function membersignupAction() {
        $post = $this->getRequest()->getPost();
        
        $this->view->pageTitle = ': Member Registration';
        
        $this->view->header_title = 'Member SignUp';
        $response = $this->getResponse();
    
        $countryModel = new Default_Model_Countries();
        $countryList = $countryModel->listCountry();
        $this->view->countryList = $countryList;
        
        // user intrest passed from countried model
        $userInterestModel = new Default_Model_UserInterests();
        $interesteList = $userInterestModel->listInterest();
        $this->view->interesteList = $interesteList;
        
        $memberModel = new Default_Model_Member();
        
        if (! empty($post)){
            if (! empty($post['cmdCancel'])){
                $this->_redirector->gotoUrl('index.html');
            }
            
            $is_error = false;
            $var_msg = 'Error(s) encountered.' . "<br/><br/>";
            
            // array of interest passed
            if (! empty($post['chkInterest'])){
                $chk_interest = implode(",", $post['chkInterest']);
                
                $post['interest'] = $chk_interest;
            }
            
            if (empty($post['first_name']) || $post['first_name'] == 'First Name'){
                $is_error = true;
                $var_msg .= EMPTY_FIRST_NAME . "<br/>";
            }
            
            if (empty($post['email']) || $post['email'] == 'E-Mail'){
                $is_error = true;
                $var_msg .= EMPTY_EMAIL . "<br/>";
            } elseif ($post['email'] != $post['email_c']){
                
                $is_error = true;
                $var_msg .= EMAIL_CONFIRM_EMAIL_NOT_MATCH . "<br/>";
            
            } else{
                $valid_email = $this->_helper->Emailvalidate($post['email']);
                if ($valid_email == false){
                    $is_error = true;
                    $var_msg .= EMAIL_ADDRESS_INVALID . "<br/>";
                } else{
                    
                    $memberModel = new Default_Model_Member();
                    $uniqueEmail = $memberModel->CheckUniqueEmail($post['email'], 2);
                    if ($uniqueEmail){
                        $is_error = true;
                        $var_msg .= sprintf(EMAIL_ALREADY_REGISTERED, $post['email']) . "<br>";
                    }
                }
            }
            
//            if (empty($post['username']) || $post['username'] == 'Username'){
//                $is_error = true;
//                $var_msg .= USERNAME_EMPTY . "<br/>";
//            } else{
//                if (strlen(trim($post['username'])) < 5){
//                    $is_error = true;
//                    $var_msg .= USERNAME_MIN_FIVE_CHARS . "<br>";
//                } else{
//                    $existing_member = $memberModel->memberInfo($post['username'], 2);
//                    if ($existing_member){
//                        $is_error = true;
//                        $var_msg .= sprintf(USERNAME_ALREADY_REGISTERED, $post['username']) . "<br/>";
//                    }
//                }
//            }
            
            if (empty($post['password']) || $post['password'] == 'Password'){
                $is_error = true;
                $var_msg .= EMPTY_PASSWORD . "<br/>";
            } else{
                if ($post['password'] != $post['cpassword']){
                    $is_error = true;
                    $var_msg .= PASSWORD_NOT_CONFIRMED . "<br/>";
                } else{
                    if (strlen(trim($post['password'])) < 5){
                        $is_error = true;
                        $var_msg .= PASSWORD_MIN_FIVE_CHARS . "<br>";
                    }
                }
            }
            if (empty($post['dob'])){
                $is_error = true;
                $var_msg .= INVALID_DOB . "<br/>";
            }
            if (empty($post['country'])){
                $is_error = true;
                $var_msg .= EMPTY_COUNTRY . "<br/>";
            }
            
            if ($this->validateCaptcha($post['captcha']) == false){
                $is_error = true;
                $var_msg .= INVALID_SECURITY_CODE . "<br/>";
            }
            
            if ($is_error == false){
                $post['token'] = $this->_helper->randomstringgenerator(20);
                
                $user_id = $memberModel->memberSignup($post);
                
                $post['user_id'] = $user_id;
                
                if ($user_id){
                    
                    $uDetailslModel = new Default_Model_UserDetails();
                    
                    $details_id = $uDetailslModel->insertUserDetails($post);
                    
                    $this->_flashMessenger->addMessage(MEMBER_ACCOUNT_CREATED);
                    
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    // Send mail to member with activation link - B E G I N
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    

                    $this->view->email = $post['email'];
                    
                    $nm = $post['first_name'];
                    $nm .= ! empty($post['last_name']) ? ' ' . $post['last_name'] : '';
                    
                    $mailModel = new Default_Model_MailTemplate();
                    $mail_template = $mailModel->getTemplate(1);
                    
                    $url = $this->_baseurl . "/verify-member/" . $user_id . "-" . $post['token'];
                    $activation_link = "<a href='" . $url . "'>Verify Now</a>";
                    $message = sprintf($mail_template['mail_text'], $nm, $activation_link, $url, $post['username'], $post['password']);
                    
                    $mail = new Zend_Mail();
                    $mail->setBodyHTML(stripslashes(nl2br($message)));
                    $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
                    $mail->addTo($post['email'], $nm);
                    $mail->setSubject($mail_template['subject']);
                    
                    try{
                        $mail->send();
                    } catch(Exception $e){
                        $this->_helper->errorlog(" Send mail to member with activation link : " . $e->getMessage());
                    }
                
     // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                // Send mail to member with activation link - E N D
                // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                } else{
                    $this->_flashMessenger->addMessage(MEMBER_ACCOUNT_NOT_CREATED);
                }
                $this->_redirector->gotoUrl('index.html');
            } else{
                foreach($post as $k => $v){
                    $this->view->$k = $v;
                }
                
                $this->view->errorMsg = $var_msg;
            
            }
        } else{
            // show member signup form
            $arr_token = $this->_session->arr_token;
        
        }
    }

    public function clientsignupAction() {
        $post = $this->getRequest()->getPost();
        
        $clientModel = new Default_Model_Client();
        
        $countryModel = new Default_Model_Countries();
        $countryList = $countryModel->listCountry();
        $this->view->countryList = $countryList;
        
        $this->view->pageTitle = ': Client Registration';
        
        $this->view->header_title = 'Client SignUp';
        
        if (! empty($post)){
            if (! empty($post['cmdCancel'])){
                $this->_redirector->gotoUrl('index.html');
            }
            
            $is_error = false;
            $var_msg = 'Error(s) encountered.' . "<br/><br/>";
            
            // member and contact model declared for email and unique username validation
            $clientModel = new Default_Model_Client();
            
            if (empty($post['first_name']) || $post['first_name'] == 'First Name'){
                $is_error = true;
                $var_msg .= EMPTY_FIRST_NAME . "<br/>";
            }
            // check for empty client contact in case of client type selected
            if (! empty($post['client_type']) && $post['client_type'] == "client_contact"){
                if (empty($post['client_id'])){
                    $is_error = true;
                    $var_msg .= EMPTY_CLIENT_CONTACT . "<br/>";
                }
            }
            
            if (empty($post['company_name']) || $post['company_name'] == 'Company Name'){
                $is_error = true;
                $var_msg .= EMPTY_COMPANY_NAME . "<br/>";
            }
            
            if (empty($post['email']) || $post['email'] == 'E-Mail'){
                $is_error = true;
                $var_msg .= EMPTY_EMAIL . "<br/>";
            } elseif ($post['email'] != $post['email_c']){
                
                $is_error = true;
                $var_msg .= EMAIL_CONFIRM_EMAIL_NOT_MATCH . "<br/>";
            
            } else{
                $valid_email = $this->_helper->Emailvalidate($post['email']);
                if ($valid_email == false){
                    $is_error = true;
                    $var_msg .= EMAIL_ADDRESS_INVALID . "<br/>";
                } else{
                    
                    $memberModel = new Default_Model_Member();
                    $uniqueEmail = $memberModel->CheckUniqueEmail($post['email'], 3);
                    if ($uniqueEmail){
                        $is_error = true;
                        $var_msg .= sprintf(EMAIL_ALREADY_REGISTERED, $post['email']) . "<br>";
                    }
                }
            }
            
            if (empty($post['username']) || $post['username'] == 'Username'){
                $is_error = true;
                $var_msg .= USERNAME_EMPTY . "<br/>";
            } else{
                if (strlen(trim($post['username'])) < 5){
                    $is_error = true;
                    $var_msg .= USERNAME_MIN_FIVE_CHARS . "<br>";
                } else{
                    $existing_client = $clientModel->clientInfo($post['username']);
                    if ($existing_client){
                        $is_error = true;
                        $var_msg .= sprintf(USERNAME_ALREADY_REGISTERED, $post['username']) . "<br/>";
                    }
                }
            }
            
            if (empty($post['password']) || $post['password'] == 'Password'){
                $is_error = true;
                $var_msg .= EMPTY_PASSWORD . "<br/>";
            } else{
                if ($post['password'] != $post['cpassword']){
                    $is_error = true;
                    $var_msg .= PASSWORD_NOT_CONFIRMED . "<br/>";
                } else{
                    if (strlen(trim($post['password'])) < 5){
                        $is_error = true;
                        $var_msg .= PASSWORD_MIN_FIVE_CHARS . "<br>";
                    }
                }
            }
            
            if ($this->validateCaptcha($post['captcha']) == false){
                $is_error = true;
                $var_msg .= INVALID_SECURITY_CODE;
            }
            
            if ($is_error == false){
                $post['token'] = $this->_helper->randomstringgenerator(20);
                
                $client_id = $clientModel->clientSignup($post);
                $this->_flashMessenger->addMessage(CLIENT_ACCOUNT_CREATED);
                $url = $this->_baseurl . "/verify-client/" . $client_id . "-" . $post['token'];
                
                if ($client_id){
                    
                    $post['user_id'] = $client_id;
                    
                    $cDetailslModel = new Default_Model_Clientdetails();
                    
                    $details_id = $cDetailslModel->insertClientDetails($post);
                    
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    // Send mail to client / client contact with activation link - B E G I N
                    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    

                    $nm = ! empty($post['lastname']) ? $post['firstname'] . ' ' . $post['lastname'] : $post['firstname'];
                    
                    $mailModel = new Default_Model_MailTemplate();
                    $mail_template = $mailModel->getTemplate(1);
                    
                    $activation_link = "<a href='" . $url . "'>Verify Now</a>";
                    $message = sprintf($mail_template['mail_text'], $nm, $activation_link, $url, $post['username'], $post['password']);
                    
                    $mail = new Zend_Mail();
                    $mail->setBodyHTML(stripslashes(nl2br($message)));
                    $mail->setFrom($mail_template['from_email'], $mail_template['from_caption']);
                    $mail->addTo($post['email'], $nm);
                    $mail->setSubject($mail_template['subject']);
                    
                    try{
                        $mail->send();
                    } catch(Exception $e){
                        $this->_helper->errorlog('Send mail to client / client contact with activation link : ' . $e->getMessage());
                    }
                
     // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                // Send mail to client / client contact with activation link - E N D
                // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                

                } else{
                    $this->_flashMessenger->addMessage(SIGNUP_NOT_SUCESSFULL);
                }
                
                $this->_redirector->gotoUrl('index.html');
            } else{
                foreach($post as $k => $v){
                    $this->view->$k = $v;
                }
                
                $this->view->errorMsg = $var_msg;
            
     //
            //				if( $this->_session->iPhone == true ){
            //				    return $this->render('iclientsignup');
            //				}else{
            //				    return $this->render ( 'clientsignup' );
            //				}
            }
        } else{
            //		    if( $this->_session->iPhone == true ){
        //		        return $this->render('iclientsignup');
        //		    }
        }
    }
    
    public function editprofileAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }

        $group_id = $this->_session->session_data['group_id'];
        
        $post = $this->getRequest()->getPost();
        
        $this->view->pageTitle = ': Edit Profile';
        
        if (! empty($post['cmdCancel'])){
            $this->_redirector->gotoUrl('user-home.html');
        }
        
        ///////////////////////////////////////////////////////
        

        ///////////  PROFILE EDIT FOR MEMBER WITH GROUP ID 2 ////////////////
        

        ///////////////////////////////////////////////////////
      
        if ($group_id == '2'){
            
            $is_error = false;
            
            $memberModel = new Default_Model_Member();
            
            $userdetailsModel = new Default_Model_UserDetails();
            
            $userInfo = $userdetailsModel->getUserInfo((int) $this->_session->session_data['user_id']);
            
            $memberInfo = $memberModel->memberInfo((int) $this->_session->session_data['user_id'], $group_id);
            
     
            $this->view->memberinfo = $memberInfo;
            
            $this->view->userInfo = $userInfo;
            
            $countryModel = new Default_Model_Countries();
            $countryList = $countryModel->listCountry();
            
            // user intrest passed from countried model
            $interestModel = new Default_Model_UserInterests();
            $interesteList = $interestModel->listInterest();
            $this->view->interesteList = $interesteList;
            
            $this->view->countryList = $countryList;
            
            if (! empty($post)){
               $var_msg = ERROR_PROFILE_NOT_UPDATED . "<br/><br/>";
                 
                // array of interest passed
                if (! empty($post['chkInterest'])){
                    $chk_interest = implode(",", $post['chkInterest']);
                    
                    $post['interest'] = $chk_interest;
                }
                else
                {
                //	 $is_error = true;
                 //   $var_msg .= EMPTY_INTEREST . "<br/>";
                }
                
                if (empty($post['first_name'])){
                    $is_error = true;
                    $var_msg .= EMPTY_FIRST_NAME . "<br/>";
                }
                
	            if (empty($post['last_name']) || $post['last_name'] == 'Last Name'){
			                $is_error = true;
			                $var_msg .= EMPTY_LAST_NAME . "<br/>";
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
                		$memberModel = new Default_Model_Member ();
						$uniqueEmail = $memberModel->CheckUniqueEmail ( $post ['email'],2, $post ['id'] );
                        if ($uniqueEmail){
                            $is_error = true;
                            $var_msg .= sprintf(EMAIL_ALREADY_REGISTERED, $post['email']) . "<br>";
                        }
                    }
                }
            
                if (empty($post['address1']) || $post['address1'] == 'Address 1'){
			                $is_error = true;
			                $var_msg .= EMPTY_ADDRESS1 . "<br/>";
	            }
	            
                if (empty($post['town']) || $post['town'] == 'Town'){
			                $is_error = true;
			                $var_msg .= EMPTY_TOWN . "<br/>";
	            }
                
	            
                if (empty($post['county']) || $post['county'] == 'County'){
			                $is_error = true;
			                $var_msg .= EMPTY_COUNTY . "<br/>";
	            }
                
                if (empty($post['postcode']) || $post['postcode'] == 'Post Code'){
			                $is_error = true;
			                $var_msg .= EMPTY_POSTCODE . "<br/>";
	            }
                
                if (empty($post['country'])){
                    $is_error = true;
                    $var_msg .= EMPTY_COUNTRY . "<br/>";
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
                        $memberinfo[$k] = $v;
                    }
            
                       // array of interest passed
                if (! empty($post['chkInterest'])){
                    $chk_interest = implode(",", $post['chkInterest']);
                    
                    $post['interest'] = $chk_interest;
                }
                else
                {
                	$chk_interest = NULL;
                 $post['interest'] = $chk_interest;
                }

                    $this->view->memberinfo->firstname  =  !empty($post['first_name']) ? $post['first_name'] : '';
                    $this->view->memberinfo->lastname  = $post['last_name'];
                    $this->view->memberinfo->email  = $post['email'];
                    $this->view->userInfo->country_id  = $post['country'];
                    
                    $this->view->userInfo->town  = $post['town'];
                    $this->view->userInfo->county  = $post['county'];
                    $this->view->userInfo->address1  = $post['address1'];
                    $this->view->userInfo->address2  = $post['address2'];
                    $this->view->userInfo->postcode  = $post['postcode'];
                    
                    
                    $this->view->userInfo->interest  =$chk_interest;
                    $this->view->userInfo->data_share  =   !empty($post['chkShareData']) ? $post['chkShareData'] : '';
                    $this->view->errorMsg = $var_msg;
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                
                }
                
                if ($is_error == false){
                    
                    $result = $memberModel->updateMember($post, true);
                    
                    $this->_flashMessenger->addMessage(PROFILE_UPDATED);
                    
                    if ($result){
                        
                        $userdeatislModel = new Default_Model_UserDetails();
                        
                        $userDeatislInfo = $userdeatislModel->updateUserDetails($post);
                        $this->_session->session_data ['country_id'] = $post['country'];
                        $this->_session->session_data ['topic_country_id'] = -1;
                        $this->_session->search_param ['country1'] = $post['country'];
                        
                        $userInfo = $memberModel->memberInfo((int) $post['id'] , 2);
                        if ($post['c_email'] != $post['email']){
                            $email_token = $this->_helper->randomstringgenerator(24);
                            
                            $result_1 = $memberModel->updateToken($post['id'], $email_token);
                            
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            // Send mail to member on old email address, notifying his email address
                            // was updated via profile screen - B E G I N
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            $mailModel = new Default_Model_MailTemplate();
                            $mail_template = $mailModel->getTemplate(4);
                            
                            if (! empty($this->_session->session_data['last_name'])){
                                $nm = $this->_session->session_data['first_name'] . ' ' . $this->_session->session_data['last_name'];
                            } else{
                                $nm = $this->_session->session_data['first_name'];
                            }
                            
                            $url = $this->_baseurl . "/undom/email-address/" . $post['id'] . "-" . $email_token;
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
                                $this->_helper->errorlog('Send mail to member on old email address, notifying his email address was updated via profile screen' . $e->getMessage());
                            }
                            
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            // Send mail to member on old email address, notifying his email address
                            // was updated via profile screen - E N D
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            

                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            // Send mail to member on new updated email address for confirming his email address - B E G I N
                            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                            

                            $mailModel = new Default_Model_MailTemplate();
                            $mail_template = $mailModel->getTemplate(5);
                            
                            if (! empty($this->_session->session_data['last_name'])){
                                $nm = $this->_session->session_data['first_name'] . ' ' . $this->_session->session_data['last_name'];
                            } else{
                                $nm = $this->_session->session_data['first_name'];
                            }
                            
                            $url = $this->_baseurl . "/confirmm/email-address/" . $post['id'] . "-" . $email_token;
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
                                $this->_helper->errorlog('Send mail to member on new updated email address for confirming his email address' . $e->getMessage());
                            }
                        
     // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        // Send mail to member on new updated email address for confirming his email address - E N D
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        }
                        
                        $this->_flashMessenger->addMessage(PROFILE_UPDATED);
                    
                    } else{
                        $this->_flashMessenger->addMessage(ERROR_PROFILE_NOT_UPDATED);
                    }
                    
                    $this->_redirector->gotoUrl('edit-profile.html');
                }
            
            }
            
             	$this->view->leftmenu = $this->view->render('leftmenu.phtml');
	            $this->view->memberprofile = $this->view->render('user/memberprofile.phtml');
           
        
        }
    }

    public function verifyuserAction() {
        $token = $this->getRequest()->getParam('token');
        $usertype = $this->getRequest()->getParam('usertype');
        
        if (empty($token)){
            $this->_flashMessenger->addMessage(USER_INFO_MISSING_CANNOT_VERIFY);
            $this->_redirector->gotoUrl('index.html');
        } else{
            $client_id = null;
            $strToken = null;
            $tmpArr = explode("-", $token);
            
            if (count($tmpArr) == 2){
                $userid = (int) $tmpArr[0];
                $strToken = $tmpArr[1];
            }
            
            if (empty($userid) || empty($strToken) || strlen($strToken) != 20){
                $this->_flashMessenger->addMessage(USER_INFO_MISSING_CANNOT_VERIFY);
                $this->_redirector->gotoUrl('index.html');
            }
        }
        
        $tmpArr = explode("-", $usertype);
        
        if (count($tmpArr) == 2){
            $utype = $tmpArr[1];
        } else{
            $this->_flashMessenger->addMessage(USER_INFO_MISSING_CANNOT_VERIFY);
            $this->_redirector->gotoUrl('index.html');
        }
        
        if (strtolower(trim($utype)) == 'member'){
            $userModel = new Default_Model_Member();
        } elseif (strtolower(trim($utype)) == 'client'){
            $userModel = new Default_Model_Client();
        } else{
            $this->_flashMessenger->addMessage(USER_INFO_MISSING_CANNOT_VERIFY);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $result = $userModel->verifyUserAccount($userid, $strToken);
        
        if ($result){
            if (strtolower(trim($utype)) == 'member'){
                $this->_flashMessenger->addMessage(USER_ACCOUNT_VERIFIED);
            } elseif (strtolower(trim($utype)) == 'client'){
                $this->_flashMessenger->addMessage(CLIENT_ACCOUNT_VERIFIED);
            }
        } else{
            $this->_flashMessenger->addMessage(USER_ACCOUNT_VERIFY_ERROR);
        }
        
        $this->_redirector->gotoUrl('index.html');
    }

    public function logoutAction() {
        $group_id = $this->_session->session_data['group_id'];
        $this->_session->session_data = null;
        $this->_session->search_param = null;
        if($group_id == 2){
            $this->_redirector->gotoUrl('index.html');
        }else{
            $this->_redirector->gotoUrl('client-login.html');
        }
    }

    private function captcha() {
        $config = Zend_Registry::get('config');
        
        $captcha = new Zend_Captcha_Image();
        
        $captcha->setTimeout('300')->setWordLen('5')->setDotNoiseLevel(10)->setLineNoiseLevel(0)->setHeight('50')->setWidth('130')->setFont('font/arial.ttf')->setImgDir($config->captcha_image->path)->setImgUrl($this->_baseurl . "/" . $config->captcha_image->url);
        
        $captcha->generate();
        
        $this->view->id = $captcha->getId();
        
        $this->view->captcha = $captcha->render();
    }

    public function generatecaptchaAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        
        $response = $this->getResponse();
        $response->insert('header', '');
        $response->insert('footer', '');
        
        $this->_helper->layout()->disableLayout();
        
        $this->captcha();
        
        $cap = $this->view->render('user/generatecaptcha.phtml');
        
        echo $cap;
    }

    private function validateCaptcha($captcha) {
        $captchaId = $captcha['id'];
        $captchaInput = $captcha['value'];
        
        $captchaSession = new Zend_Session_Namespace('Zend_Form_Captcha_' . $captchaId);
        $captchaIterator = $captchaSession->getIterator();
        if (! empty($captchaIterator['word'])){
            $captchaWord = $captchaIterator['word'];
        } else{
            $captchaWord = false;
        }
        
        if ($captchaWord){
            if ($captchaInput != $captchaWord){
                return false;
            } else{
                return true;
            }
        } else{
            return false;
        }
    }

    public function emailaddressAction() {
        
        //	echo "cc"; exit;
        

        $useraction = trim($this->_getParam('useraction'));
        $token = $this->_getParam('token');
        
        if ($useraction != 'undom' && $useraction != 'undoc' && $useraction != 'undocc' && $useraction != 'confirmm' && $useraction != 'confirmc' && $useraction != 'confirmcc'){
            $this->_flashMessenger->addMessage(USER_ACTION_INVALID_MISSING);
            $this->_redirector->gotoUrl('index.html');
        }
        
        if (empty($token)){
            $this->_flashMessenger->addMessage(MISSING_TOKEN_INFO);
            $this->_redirector->gotoUrl('index.html');
        } else{
            $id = null;
            $strToken = null;
            $tmpArr = explode("-", $token);
            
            if (count($tmpArr) == 2){
                $id = (int) $tmpArr[0];
                $strToken = $tmpArr[1];
            }
            
            if (empty($id) || empty($strToken) || strlen($strToken) != 24){
                $this->_flashMessenger->addMessage(MISSING_INFORMATION);
                $this->_redirector->gotoUrl('index.html');
            } else{
                if ($useraction == 'undom'){
                    $userModel = new Default_Model_Member();
                    $token_res = $userModel->validateToken($strToken, $id);
                    if ($token_res){
                        $email_res = $userModel->updateEmailAddress('temp_email', null, $id);
                        $this->_flashMessenger->addMessage(REVERTED_BACK_EMAIL_CHANGE);
                    } else{
                        $this->_flashMessenger->addMessage(INVALID_TOKEN);
                    }
                } else if ($useraction == 'confirmm'){
                    $userModel = new Default_Model_Member();
                    $token_res = $userModel->validateToken($strToken, $id);
                    if ($token_res){
                        $email_res = $userModel->updateEmailAddress('email', $token_res['temp_email'], $id);
                        $this->_flashMessenger->addMessage(EMAIL_CONFIRMED);
                    } else{
                        $this->_flashMessenger->addMessage(INVALID_TOKEN);
                    }
                }
                
                if ($useraction == 'undoc'){
                    $clientModel = new Default_Model_Client();
                    $token_res = $clientModel->validateToken($strToken, $id);
                    if ($token_res){
                        $email_res = $clientModel->updateEmailAddress('temp_email', null, $id);
                        $this->_flashMessenger->addMessage(REVERTED_BACK_EMAIL_CHANGE);
                    } else{
                        $this->_flashMessenger->addMessage(INVALID_TOKEN);
                    }
                } else if ($useraction == 'confirmc'){
                    $clientModel = new Default_Model_Client();
                    $token_res = $clientModel->validateToken($strToken, $id);
                    if ($token_res){
                        $email_res = $clientModel->updateEmailAddress('email', $token_res['temp_email'], $id);
                        $this->_flashMessenger->addMessage(EMAIL_CONFIRMED);
                    } else{
                        $this->_flashMessenger->addMessage(INVALID_TOKEN);
                    }
                }
                
                if ($useraction == 'undocc'){
                    $ccModel = new Default_Model_ClientContacts();
                    $token_res = $ccModel->validateToken($strToken, $id);
                    if ($token_res){
                        $email_res = $ccModel->updateEmailAddress('temp_email', null, $id);
                        $this->_flashMessenger->addMessage(REVERTED_BACK_EMAIL_CHANGE);
                    } else{
                        $this->_flashMessenger->addMessage(INVALID_TOKEN);
                    }
                }
            
            }
        }
        $this->_redirector->gotoUrl('index.html');
    }

    public function sendinviteAction() {
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'Invite Friends';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        $post = $this->getRequest()->getPost();
        
        if (! empty($post)){
            if (! empty($post['cmdCancel'])){
                $this->_redirector->gotoUrl('user-home.html');
            }
            
            $error_flag = false;
            $error_text = 'Error(s) encountered. Cannot send invitation' . "<br><br>";
            
            if (empty($post['txtEmail'])){
                $error_flag = true;
                $error_text .= "Recipient's E-Mail address is blank" . "<br>";
            } else{
                $email_arr = explode(";", $post['txtEmail']);
                $new_email_arr = array();
                foreach($email_arr as $k => $v){
                    $earr = explode(",", $v);
                    foreach($earr as $ek => $ev){
                        array_push($new_email_arr, $ev);
                    }
                }
                $email_arr = $new_email_arr;
                $valid_email_flag = true;
                foreach($email_arr as $k => $v){
                    $valid_email = $this->_helper->Emailvalidate($v);
                    if ($valid_email == false){
                        $valid_email_flag = false;
                    }
                }
                if ($valid_email_flag == false){
                    $error_flag = true;
                    $error_text .= "Recepient's E-Mail address is invalid" . "<br>";
                }
            }
            if (empty($post['txtSubject'])){
                $error_flag = true;
                $error_text .= "Subject cannot be blank" . "<br>";
            }
            if (empty($post['txtMsg'])){
                $error_flag = true;
                $error_text .= "Message text cannot be blank" . "<br>";
            }
            
            if ($error_flag == true){
                foreach($post as $k => $v){
                    $this->view->$k = $v;
                }
                $this->view->errorMsg = $error_text;
                if ($this->_session->iPhone == true){
                    $this->getResponse()->insert('error', $this->view->render('ierror.phtml'));
                    return $this->render('isendinvite');
                } else{
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                    $this->view->leftmenu = $this->view->render('leftmenu.phtml');
                    return $this->render('sendinvite');
                }
            }
            
            //	$inviteModel = new Default_Model_Invitations ();
            

            if (! empty($this->_session->session_data['lastname'])){
                $from_nm = $this->_session->session_data['firstname'] . ' ' . $this->_session->session_data['lastname'];
            } else{
                $from_nm = $this->_session->session_data['firstname'];
            }
            
            $memberModel = new Default_Model_Member();
            
            $sucess_cnt = 0;
            
            if (! empty($email_arr)){
                foreach($email_arr as $k => $v){
                    $invite_id = 1;
                    $valid_email = $this->_helper->Emailvalidate($v);
                    if ($valid_email == false){ // do not send invitation mail to invalid email address
                        continue;
                    }
                    
                    $existing_member = $memberModel->CheckUniqueEmail($v , 2);
                    if ($existing_member == true){ // do not send invitation mail to exiting m3 members
                        continue;
                    }
                    
                    $invitation_token = $this->_helper->randomstringgenerator(24);
                    
                    //	$data = array ('recepient_email' => $v, 'sent_by_member_id' => $this->_session->session_data ['user_id'], 'invitation_token' => $invitation_token );
                    //	$invite_id = $inviteModel->sendInvitation ( $data );
                    

                    //	if ($invite_id) {
                    if ($invite_id){
                        $sucess_cnt ++;
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        // Send invitation mail to member friends - B E G I N
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        

                        $mailModel = new Default_Model_MailTemplate();
                        $mail_template = $mailModel->getTemplate(3);
                        
                        $url = $this->_baseurl . "/accept-invitation/" . $invitation_token;
                        $accept_link = "<a href='" . $url . "'>Accept Invitation</a>";
                        
                        $c_msg = null;
                        if (! empty($post['txtMsg'])){
                            $c_msg = 'Custom message by ' . $from_nm . "\n";
                            $c_msg .= "----------------------------------------" . "\n";
                            $c_msg .= htmlspecialchars($post['txtMsg']);
                            $c_msg .= "\n";
                            $c_msg .= "----------------------------------------" . "\n";
                        }
                        
                        $message = sprintf($mail_template['mail_text'], $accept_link, $url, $c_msg);
                        
                        $mail = new Zend_Mail();
                        $mail->setBodyHTML(stripslashes(nl2br($message)));
                        $mail->setFrom($this->_session->session_data['email'], $from_nm);
                        $mail->addTo($v, $v);
                        $mail->setSubject($post['txtSubject']);
                        
                        try{
                            $mail->send();
                        } catch(Exception $e){
                            $this->_helper->errorlog('Send invitation mail to member friends' . $e->getMessage());
                        }
                    
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        // Send invitation mail to member friends - E N D
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    }
                }
                $invite_id = 0;
                if ($sucess_cnt > 0){
                    //$this->_flashMessenger->addMessage ( $sucess_cnt . " " . INVITATION_SENT );
                    $this->_flashMessenger->addMessage(INVITATION_SENT);
                } else{
                    $this->_flashMessenger->addMessage(ZERO_INVITATION_SENT);
                }
            } else{
                $this->_flashMessenger->addMessage(INVITATION_NOT_SENT);
            }
            $this->_redirector->gotoUrl('user-home.html');
        } else{
            $this->view->leftmenu = $this->view->render('leftmenu.phtml');
        }
    }

    public function invitemcubedAction() {
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'Invite Friends';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        $post = $this->getRequest()->getPost();
        
        if (! empty($post)){
            if (! empty($post['cmdCancel'])){
                $this->_redirector->gotoUrl('user-home.html');
            }
            
            $error_flag = false;
            $error_text = 'Error(s) encountered. Cannot send invitation' . "<br><br>";
            
            if (empty($post['txtEmail'])){
                $error_flag = true;
                $error_text .= "Recipient's E-Mail address is blank" . "<br>";
            } else{
                $email_arr = explode(";", $post['txtEmail']);
                $new_email_arr = array();
                foreach($email_arr as $k => $v){
                    $earr = explode(",", $v);
                    foreach($earr as $ek => $ev){
                        array_push($new_email_arr, $ev);
                    }
                }
                $email_arr = $new_email_arr;
                $valid_email_flag = true;
                foreach($email_arr as $k => $v){
                    $valid_email = $this->_helper->Emailvalidate($v);
                    if ($valid_email == false){
                        $valid_email_flag = false;
                    }
                }
                if ($valid_email_flag == false){
                    $error_flag = true;
                    $error_text .= "Recepient's E-Mail address is invalid" . "<br>";
                }
            }
            if (empty($post['txtSubject'])){
                $error_flag = true;
                $error_text .= "Subject cannot be blank" . "<br>";
            }
            if (empty($post['txtMsg'])){
                $error_flag = true;
                $error_text .= "Message text cannot be blank" . "<br>";
            }
            
            if ($error_flag == true){
                foreach($post as $k => $v){
                    $this->view->$k = $v;
                }
                $this->view->errorMsg = $error_text;
                if ($this->_session->iPhone == true){
                    $this->getResponse()->insert('error', $this->view->render('ierror.phtml'));
                    return $this->render('isendinvite');
                } else{
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
              	    $this->view->leftmenu = $this->view->render('leftmenu.phtml');
                    return $this->render('sendinvite');
                }
            }
            
            //	$inviteModel = new Default_Model_Invitations ();
            

            if (! empty($this->_session->session_data['lastname'])){
                $from_nm = $this->_session->session_data['firstname'] . ' ' . $this->_session->session_data['lastname'];
            } else{
                $from_nm = $this->_session->session_data['firstname'];
            }
            
            $memberModel = new Default_Model_Member();
            
            $sucess_cnt = 0;
            
            if (! empty($email_arr)){
                foreach($email_arr as $k => $v){
                    $invite_id = 1;
                    $valid_email = $this->_helper->Emailvalidate($v);
                    if ($valid_email == false){ // do not send invitation mail to invalid email address
                        continue;
                    }
                    
                    $existing_member = $memberModel->CheckUniqueEmail($v);
                    if ($existing_member == true){ // do not send invitation mail to exiting m3 members
                        continue;
                    }
                    
                    $invitation_token = $this->_helper->randomstringgenerator(24);
                    
                    //	$data = array ('recepient_email' => $v, 'sent_by_member_id' => $this->_session->session_data ['user_id'], 'invitation_token' => $invitation_token );
                    //	$invite_id = $inviteModel->sendInvitation ( $data );
                    

                    //	if ($invite_id) {
                    if ($invite_id){
                        $sucess_cnt ++;
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        // Send invitation mail to member friends - B E G I N
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        

                        $mailModel = new Default_Model_MailTemplate();
                        $mail_template = $mailModel->getTemplate(3);
                        
                        $url = $this->_baseurl . "/accept-invitation/" . $invitation_token;
                        $accept_link = "<a href='" . $url . "'>Accept Invitation</a>";
                        
                        $c_msg = null;
                        if (! empty($post['txtMsg'])){
                            $c_msg = 'Custom message by ' . $from_nm . "\n";
                            $c_msg .= "----------------------------------------" . "\n";
                            $c_msg .= htmlspecialchars($post['txtMsg']);
                            $c_msg .= "\n";
                            $c_msg .= "----------------------------------------" . "\n";
                        }
                        
                        $message = sprintf($mail_template['mail_text'], $accept_link, $url, $c_msg);
                        
                        $mail = new Zend_Mail();
                        $mail->setBodyHTML(stripslashes(nl2br($message)));
                        $mail->setFrom($this->_session->session_data['email'], $from_nm);
                        $mail->addTo($v, $v);
                        $mail->setSubject($post['txtSubject']);
                        
                        try{
                            $mail->send();
                        } catch(Exception $e){
                            $this->_helper->errorlog('Send invitation mail to member friends' . $e->getMessage());
                        }
                    
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                        // Send invitation mail to member friends - E N D
                        // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
                    }
                }
                $invite_id = 0;
                if ($sucess_cnt > 0){
                    //$this->_flashMessenger->addMessage ( $sucess_cnt . " " . INVITATION_SENT );
                    $this->_flashMessenger->addMessage(INVITATION_SENT);
                } else{
                    $this->_flashMessenger->addMessage(ZERO_INVITATION_SENT);
                }
            } else{
                $this->_flashMessenger->addMessage(INVITATION_NOT_SENT);
            }
            $this->_redirector->gotoUrl('user-home.html');
        } else{
             $this->view->leftmenu = $this->view->render('leftmenu.phtml');
        }
    }

}