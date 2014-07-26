<?php

class Mobile_IphoneController extends My_UserController {
	
	/**
	 * init() function, call init() function of parent class
	 *
	 */
	
	public function init() {
		parent::init ();
	}
	
	public function indexAction() {
		
		$session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
		
			
			$id = $this->_session->iphone_session_data ['user_id'];
			$userModel = new Default_Model_Member ();
			$userInfo = $userModel->memberInfo ( $id, 2 );
			
			$date = empty ( $userInfo ['lastlogin_datetime'] ) ? '--' : date ( "jS M Y h:i A", strtotime ( $userInfo ['lastlogin_datetime'] ) );
			
			$this->view->lastlogin = $date;
			
			if ($this->_session->iphone_session_data ['group_id'] == 2) {
				
				//echo "xx"; exit;
				$this->view->success_flag = 1;
				$group_id = 2;
				
				$userModel = new Default_Model_Member ();
				$member_info = $userModel->memberInfo ( $id, $group_id );
			
			} else {
				$this->view->msg = 'Invalid user type';
				$this->view->success_flag = 0;
			}
		}
	}
	
	public function processloginAction() {
		$post = $this->getRequest ()->getPost ();
		
	 //$post = array ('email' => 'kevin22' , 'password' => 'csl123');
		//	echo "<pre>"; print_r($post); echo "</pre>"; exit;
		

		///$x = print_r ( $post, true );
		//$this->_helper->errorlog ( $x );
		

		if (empty ( $post )) {
			$this->view->msg = POST_EMPTY;
			$this->view->success_flag = 0;
		} else {
			
			//	$username = $post['email'];
			//	$password = $post['password'];

			$user_type = NULL;
			
			$userModel = new Default_Model_Member ();
			$userInfo = $userModel->memberInfo ( $post ['email'], 2 );
			
			//var_dump($userInfo); exit;

			if ($userInfo) {
				if ($userInfo ['status'] == 'Inactive') {
					$this->view->msg = LOGIN_ERROR_ACCOUNT_INACTIVE;
					$this->view->success_flag = 0;
				} elseif ($userInfo ['group_id'] == '1') {
					$this->view->msg = USER_TYPE_NOT_FOUND;
					$this->view->success_flag = 0;
				} elseif ($userInfo ['verified'] == 0) {
					$this->view->msg = LOGIN_ERROR_VERIFICATION_REQUIRED;
					$this->view->success_flag = 0;
				} elseif ($userInfo ['group_id'] == 3) {
					$this->view->msg = USER_TYPE_NOT_FOUND;
					$this->view->success_flag = 0;
				} else {
					if (md5 ( $post ['password'] ) == $userInfo ['password']) {
						$userModel->updateLastLoginDateTime ( $post ['email'] );
						
						$uDetailslModel = new Default_Model_UserDetails ();
						
						$userIDeatils = $uDetailslModel->getUserInfo ( $userInfo ['user_id'] );
						
						$countryModel = new Default_Model_Countries ();
						$country_name = $countryModel->getCountryName ( $userIDeatils ['country_id'] );
						
						$session_array = array (	"login_flag" => true,
													"iphone_flag" => true,
													"group_id" => $userInfo ['group_id'],
													"user_id" => $userInfo ['user_id'],
													"firstname" => $userInfo ['firstname'],
													"lastname" => $userInfo ['lastname'],
													"email" => $userInfo ['email'],
													"signupdate" => $userInfo ['created_date'],
													"password" => $userInfo ['password'],
													"dob" => $userIDeatils ['dob'],
													"country_id" => $userIDeatils ['country_id'],
													"interest" => $userIDeatils ['interest'],
													"country_name" => $country_name,
													"data_share" => $userIDeatils ['data_share'],
													"lastlogin_datetime" => empty ( $userInfo ['lastlogin_datetime'] ) ? '--' : date ( "jS M Y h:i A", strtotime ( $userInfo ['lastlogin_datetime'] ) ) );
						
						//	$this->_session->session_array = $session_array;

						$this->_session->iphone_session_data = $session_array;
						
						$getintrest = $userIDeatils ['interest'];
						
						if (! empty ( $getintrest )) {
							
							$pieces = explode ( ",", $getintrest );
							
							$uinterest = new Default_Model_UserInterests ();
							
							$get_tags = '';
							foreach ( $pieces as $name ) {
								
								$getuID = $uinterest->UserInterestInfobyname ( $name );
								
								$get_tags .= $getuID ['intrest_id'] . ',';
							
							}
							
							$getfinalid = substr_replace ( $get_tags, "", - 1 );
						} else {
							
							$getfinalid = '';
						
						}
						
						$this->view->sharing = ($userIDeatils ['data_share'] == 'Y') ? 1 : 0;
						$this->view->signupData = date ( "d/m/Y ", strtotime ( $userInfo ['created_date'] ) );
						$this->view->intrestname = $userIDeatils ['interest'];
						$this->view->intrestid = $getfinalid;
						
						$this->view->user_id = $userInfo ['user_id'];
						$this->view->email = $userInfo ['email'];
						$this->view->fname = $userInfo ['firstname'];
						$this->view->lname = $userInfo ['lastname'];
						$this->view->dob = ! empty ( $userIDeatils ['dob'] ) ? date ( 'd/m/Y', strtotime ( $userIDeatils ['dob'] ) ) : '';
						$this->view->country = $userIDeatils ['country_id'];
						
						$this->view->gender = $userIDeatils ['gender'];
						$this->view->address1 = $userIDeatils ['address1'];
						$this->view->address2 = $userIDeatils ['address2'];
						$this->view->town = $userIDeatils ['town'];
						$this->view->county = $userIDeatils ['county'];
						$this->view->postcode = $userIDeatils ['postcode'];
						
						$this->view->countryName = $country_name;
						
						//	$this->view->name = $userInfo ['firstname'];
						//	$this->view->name .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
						//$this->view->msg   = $profile_msg;
						
						// send survey
						$surveyModel = new Default_Model_Survey ();
						
						$status = 'Active';
						
						$surveyList = null; // set to null in orde to skip mebmer survey
						
						//$surveyList = $surveyModel->getfrontendSurvey ( $status );
						
						if( !empty($surveyList)){
    						$sid = $surveyList ['survey_id'];
    						
    						$customer_surveyModel = new Default_Model_CustomerSurvey ();
    						$surveyTakenInfo = $customer_surveyModel->SurveyTakenInfo ( ( int ) $this->_session->iphone_session_data ['user_id'], $sid );
    						
    						if (empty($surveyTakenInfo)) {
    							$this->view->sid = $sid;
    						}
						}else{
						    $this->view->sid = null;
						}
						
						$this->view->success_flag = 1;
						$this->view->msg = 'You are logged in ';
					} else {
						$this->view->msg = LOGIN_ERROR_INCORRECT_LOGIN_INFO;
						$this->view->success_flag = 0;
					}
				}
			} else {
				$this->view->msg = LOGIN_ERROR_INCORRECT_LOGIN_INFO;
				$this->view->success_flag = 0;
			}
		}
	
	}
	
	public function membersignupAction() {
		$post = $this->getRequest ()->getPost ();
		
		//		$post = array ( 'first_name' => 'sasaas',
		//		                'last_name' => 'justin',
		//		                'email' => 'justins@gees.cmmso1',
		//		                'email_c' => 'justins@gees.cmmso1',
		//	                	'username' => 'jusstinwws',
		//		                'password' => 'justin',
		//		                'cpassword' => 'justin',
		//		                'dob' => 'justin',
		//		                'gender' => 'Female',
		//		                'chkInterest' => "Reading Politics Outdoors",
		//		                'chkShareData' => 'Y',
		//		                'country' => '222'
		//
		//		);
		

		//print_r($post);
		

		// for debugging post values
	$x = print_r ( $post, true );
		$this->_helper->errorlog ( $x );
		
		$response = $this->getResponse ();
		
		$countryModel = new Default_Model_Countries ();
		$countryList = $countryModel->listCountry ();
		$this->view->countryList = $countryList;
		
		$memberModel = new Default_Model_Member ();
		
		if (! empty ( $post )) {
			
			$success_flag = 1;
			$msg = null;
			
			// array of interest passed
			if (! empty ( $post ['chkInterest'] )) {
				//$chk_interest = implode ( ",", $post ['chkInterest'] );
				//str_replace("world","Peter","Hello world!");
				//$chk_interest = str_replace ( " ", ",", $post ['chkInterest'] );
				

				$post ['interest'] = $post ['chkInterest'];
			}
			
			//	Sports,Reading,Politics,Outdoors,Cars
			

			if (empty ( $post ['first_name'] )) {
				$success_flag = 0;
				$msg = 'First name cannot be blank.';
			}
			
			if (empty ( $post ['email'] )) {
				$success_flag = 0;
				$msg = EMPTY_EMAIL;
			} elseif ($post ['email'] != $post ['email_c']) {
				$success_flag = 0;
				$msg = EMAIL_CONFIRM_EMAIL_NOT_MATCH;
			} else {
				$valid_email = $this->_helper->Emailvalidate ( $post ['email'] );
				if ($valid_email == false) {
					$success_flag = 0;
					$msg = EMAIL_ADDRESS_INVALID;
				} else {
					
					$memberModel = new Default_Model_Member ();
					$uniqueEmail = $memberModel->CheckUniqueEmail ( $post ['email'], 2 );
					
					if ($uniqueEmail) {
						if ($uniqueEmail) {
							$success_flag = 0;
							$msg = sprintf ( EMAIL_ALREADY_REGISTERED, $post ['email'] );
						}
					}
				}
			}

			if (empty ( $post ['password'] ) || $post ['password'] == 'Password') {
				$success_flag = 0;
				$msg = EMPTY_PASSWORD;
			} else {
				if ($post ['password'] != $post ['cpassword']) {
					$success_flag = 0;
					$msg = PASSWORD_NOT_CONFIRMED;
				} else {
					if (strlen ( trim ( $post ['password'] ) ) < 5) {
						$success_flag = 0;
						$msg = PASSWORD_MIN_FIVE_CHARS;
					}
				}
			}
			
			if (empty ( $post ['dob'] )) {
				
				$success_flag = 0;
				$msg = INVALID_DOB;
			}
			if (empty ( $post ['country'] )) {
				
				$success_flag = 0;
				$msg = EMPTY_COUNTRY;
			}
			
			//			if (empty ( $post ['interest'] )) {
			//
			//				$success_flag = 0;
			//				$msg = EMPTY_INTEREST;
			//			}
			

			if ($success_flag == 1) {
				$post ['token'] = $this->_helper->randomstringgenerator ( 20 );
				
				$user_id = $memberModel->memberSignup ( $post );
				
				$post ['user_id'] = $user_id;
				
				if ($user_id) {
					
					$uDetailslModel = new Default_Model_UserDetails ();
					
					//$x = print_r ( $post, true );  $this->_helper->errorlog ( $x );
					
					$details_id = $uDetailslModel->insertUserDetails ( $post );
					
					$success_flag = 1;
					$msg = 'Account created. Please check your email and verify your account.';
					
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					// Send mail to member with activation link - B E G I N
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					

					$this->view->email = $post ['email'];
					
					$nm = $post ['first_name'];
					$nm .= ! empty ( $post ['last_name'] ) ? ' ' . $post ['last_name'] : '';
					
					$mailModel = new Default_Model_MailTemplate ();
					$mail_template = $mailModel->getTemplate ( 1 );
					
					$url = $this->_baseurl . "/verify-member/" . $user_id . "-" . $post ['token'];
					$activation_link = "<a href='" . $url . "'>Verify Now</a>";
					$message = sprintf ( $mail_template ['mail_text'], $nm, $activation_link, $url, $post ['email'], $post ['password'] );
					
					$mail = new Zend_Mail ();
					$mail->setBodyHTML ( stripslashes ( nl2br ( $message ) ) );
					$mail->setFrom ( $mail_template ['from_email'], $mail_template ['from_caption'] );
					$mail->addTo ( $post ['email'], $nm );
					$mail->setSubject ( $mail_template ['subject'] );
					
					try {
						$mail->send ();
					} catch ( Exception $e ) {
						$this->_helper->errorlog ( " Send mail to member with activation link : " . $e->getMessage () );
					}
				
		// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
				// Send mail to member with activation link - E N D
				// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
				} else {
					$success_flag = 0;
					$msg = 'Error(s) encountered. Member account not created.';
				}
			
			}
		} else {
			$msg = POST_EMPTY;
			$success_flag = 0;
		
		}
		
		$this->view->msg = $msg;
		$this->view->success_flag = $success_flag;
	}
	
	// client signup and logic not intended for iphone :)

	public function recoverpasswordAction() {
		$post = $this->getRequest ()->getPost ();
		
		//	$post = array ('txtEmail' => 'devaldevaldevaldevaldeval@gmail.com' );
		//$x = print_r ( $post, true );
		//$this->_helper->errorlog ( $x );
		

		if (! empty ( $post )) {
			if ((empty ( $post ['username'] ) || $post ['username'] == 'Username') && (empty ( $post ['email'] ) || $post ['email'] == 'E-mail')) {
				$this->view->msg = 'Cannot process your request. Username / E-Mail is missing';
				$this->view->success_flag = 0;
			}
		}
		
		//if (! empty ( $post ['username'] ) || ! empty ( $post ['email'] )) {
		if ( ! empty ( $post ['email'] )) {
			$user_type = null;
			
			$userModel = new Default_Model_Member ();
			$field = 'user_id';
			
//			if (! empty ( $post ['username'] )) {
//				$result = $userModel->memberInfo ( $post ['username'], 2 );
//			} else
			
			if (! empty ( $post ['email'] )) {
				//	$valid_email = $this->_helper->Emailvalidate ( $post ['txtEmail'] );
				$result = $userModel->memberInfo ( $post ['email'], 2 );
			}
			
			if ($result) {
				$new_pwd = $this->_helper->randomstringgenerator ( 10 );
				$pwd_result = $userModel->resetPassword ( $result [$field], md5 ( $new_pwd ) );
				if ($pwd_result) {
					
					if (! empty ( $result ['lastname'] )) {
						$nm = $result ['firstname'] . ' ' . $result ['lastname'];
					} else {
						$nm = $result ['firstname'];
					}
					
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					// Send mail to user with new user password - B E G I N
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					

					$mailModel = new Default_Model_MailTemplate ();
					$mail_template = $mailModel->getTemplate ( 2 );
					
					$message = sprintf ( $mail_template ['mail_text'], $nm, $new_pwd );
					
					$mail = new Zend_Mail ();
					$mail->setBodyHTML ( stripslashes ( nl2br ( $message ) ) );
					$mail->setFrom ( $mail_template ['from_email'], $mail_template ['from_caption'] );
					$mail->addTo ( $result ['email'], $nm );
					$mail->setSubject ( $mail_template ['subject'] );
					
					try {
						$mail->send ();
					} catch ( Exception $e ) {
						$this->_helper->errorlog ( 'Send mail to user with new user password :' . $e->getMessage () );
					
					}
					
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					// Send mail to user with new user password - E N D
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					

					$this->view->msg = NEW_PWD_MAILED;
					$this->view->success_flag = 1;
				} else {
					$this->view->msg = ERRORS_ENCOUNTERED;
					$this->view->success_flag = 0;
				}
			} else {
				$this->view->msg = USER_NOT_FOUND;
				$this->view->success_flag = 0;
			}
		} else {
			$this->view->msg = ERRORS_ENCOUNTERED;
			$this->view->success_flag = 0;
		}
	}
	
	public function changepasswordAction() {
		
		$post = $this->getRequest ()->getPost ();
		
		//$x = print_r ( $post, true );
		//$this->_helper->errorlog ( $x );
		
		//	$post = array ('current_pwd' => 'jusdtin', 'new_pwd' => 'justin', 'cnf_new_pwd' => 'justin' );
		
     $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
			
		$id = $this->_session->iphone_session_data ['user_id'];
	//	$this->_helper->errorlog ( 'jjjjj : ' . $id . ' ---- ' . $post ['current_pwd'] );
		$memberModel = new Default_Model_Member ();
		$getpasswd = $memberModel->memberInfo ( $id, 2 );
		
		$password = $getpasswd ['password'];
			
		//	$this->_helper->errorlog ( md5 ( $post ['current_pwd'] ) . ' ---- ' . $password );
			
			if (md5 ( $post ['current_pwd'] ) == $password) {
				
				$userModel = new Default_Model_Member ();
				$pwd_result = $userModel->resetPassword ( $id, md5 ( $post ['new_pwd'] ) );
				
				if ($pwd_result) {
					
					$success_flag = 1;
					$msg = 'Password Updated succesfully';
				
				} else {
					$success_flag = 0;
					$msg = 'Password Failed to update';
				
				}
			
			} else {
				
				$success_flag = 0;
				$msg = 'Current Password is incorrect';
			
			}
		$this->view->msg = $msg;
		$this->view->success_flag = $success_flag;
		}
		
		
	}
	
	public function editprofileAction() {

	
		$session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
            $post = $this->getRequest ()->getPost ();
		
			
	//	$x = print_r ( $post, true );
	//	$this->_helper->errorlog ( $x );
	  
			//$this->memberFunction();
			$group_id = $this->_session->iphone_session_data ['group_id'];
			
			//			$post = array ('first_name' => 'sasaas',
			//			'id' => '75', 'last_name' => 'justin',
			//			 'email' => 'justindominic.csl@gmail.com1',
			//			// 'c_email' => 'justindominic.csl234@gmail.com1',
			//			 'username' => 'justin', 'password' => 'justin',
			//			 'c_password' => 'justin', 'dob' => '2012-07-02',
			//			 'chkInterest' => "Reading Politics Outdoors",
			//			 'chkShareData' => 'Y', 'country' => '222' );
			

			//echo "xx"; exit;
			///////////////////////////////////////////////////////
			

			///////////  PROFILE EDIT FOR MEMBER WITH GROUP ID 2 ////////////////
			

			///////////////////////////////////////////////////////
			
			if ($group_id == '2') {
				
				$memberModel = new Default_Model_Member ();
				
				if (! empty ( $post )) {
					
					$post ['id'] = $this->_session->iphone_session_data ['user_id'];
					
					$success_flag = 1;
					$msg = null;
					
					$post ['dob'] = ! empty ( $post ['dob'] ) ? date ( 'Y-m-d', strtotime ( $post ['dob'] ) ) : null;
					
					if (empty ( $post ['first_name'] )) {
						$success_flag = 0;
						$msg = 'First name cannot be blank.';
					}
					
					if (empty ( $post ['country'] )) {
						$success_flag = 0;
						$msg = 'Country  cannot be blank.';
					}
					
					if (empty ( $post ['email'] ) || $post ['email'] == 'E-Mail') {
						$is_error = true;
						$msg = EMPTY_EMAIL . "<br/>";
					} else {
						$valid_email = $this->_helper->Emailvalidate ( $post ['email'] );
						if ($valid_email == false) {
							$is_error = true;
							$msg = EMAIL_ADDRESS_INVALID . "<br/>";
						} else {
							
							$memberModel = new Default_Model_Member ();
							
							if (($uniqueEmail = $memberModel->CheckUniqueEmail ( $post ['email'], 2, $post ['id'] ))) {
								if ($uniqueEmail) {
									$is_error = true;
									$msg = sprintf ( EMAIL_ALREADY_REGISTERED, $post ['email'] ) . "<br>";
								}
							}
						}
					}
					
					if (! empty ( $post ['password'] ) && strtolower ( trim ( $post ['password'] ) ) != 'password') {
						if (! empty ( $post ['password'] ) || ! empty ( $post ['c_password'] )) {
							if ($post ['password'] != $post ['c_password']) {
								$success_flag = 0;
								$msg = PASSWORD_NOT_CONFIRMED;
							} else {
								if (strlen ( trim ( $post ['password'] ) ) < 5) {
									$success_flag = 0;
									$msg = 'Minimum 5 characters required for password';
								}
							}
						}
					} else {
						$post ['password'] = null;
						$post ['c_password'] = null;
					}
					
					//echo "xxxx"; exit;
					//				echo $success_flag ;
					//				echo $msg ;
					

					$this->view->success_flag = $success_flag;
					$this->view->msg = $msg;
					
					if ($success_flag == 1) {
						//  echo "xx"; exit;
						

						$post ['c_email'] = $this->_session->iphone_session_data ['email'];
						
						$result = $memberModel->updateMember ( $post, true );
						
						if ($result) {
							
							$userdeatislModel = new Default_Model_UserDetails ();
							
							$userDeatislInfo = $userdeatislModel->updateUserDetails ( $post );
							
							$userInfo = $memberModel->memberInfo ( ( int ) $post ['id'], 2 );
							
							if ($post ['c_email'] != $post ['email']) {
								$email_token = $this->_helper->randomstringgenerator ( 24 );
								
								$result_1 = $memberModel->updateToken ( $post ['id'], $email_token );
								
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								// Send mail to member on old email address, notifying his email address
								// was updated via profile screen - B E G I N
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								$mailModel = new Default_Model_MailTemplate ();
								$mail_template = $mailModel->getTemplate ( 4 );
								
								if (! empty ( $this->_session->iphone_session_data ['lastname'] )) {
									$nm = $this->_session->iphone_session_data ['firstname'] . ' ' . $this->_session->iphone_session_data ['lastname'];
								} else {
									$nm = $this->_session->iphone_session_data ['firstname'];
								}
								
								$url = $this->_baseurl . "/undom/email-address/" . $post ['id'] . "-" . $email_token;
								$reject_email_update_link = "<a href='" . $url . "'>Undo E-Mail Change</a>";
								
								$message = sprintf ( $mail_template ['mail_text'], $nm, $post ['email'], $reject_email_update_link, $url );
								
								$mail = new Zend_Mail ();
								$mail->setBodyHTML ( stripslashes ( nl2br ( $message ) ) );
								$mail->setFrom ( $mail_template ['from_email'], $mail_template ['from_caption'] );
								$mail->addTo ( $post ['c_email'], $nm );
								$mail->setSubject ( $mail_template ['subject'] );
								
								try {
									$mail->send ();
								} catch ( Exception $e ) {
									$this->_helper->errorlog ( 'Send mail to member on old email address, notifying his email address was updated via profile screen' . $e->getMessage () );
								}
								
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								// Send mail to member on old email address, notifying his email address
								// was updated via profile screen - E N D
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								

								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								// Send mail to member on new updated email address for confirming his email address - B E G I N
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								

								$mailModel = new Default_Model_MailTemplate ();
								$mail_template = $mailModel->getTemplate ( 5 );
								
								if (! empty ( $this->_session->iphone_session_data ['lastname'] )) {
									$nm = $this->_session->iphone_session_data ['firstname'] . ' ' . $this->_session->iphone_session_data ['lastname'];
								} else {
									$nm = $this->_session->iphone_session_data ['firstname'];
								}
								
								$url = $this->_baseurl . "/confirmm/email-address/" . $post ['id'] . "-" . $email_token;
								$confirm_email_update_link = "<a href='" . $url . "'>Confirm E-Mail Address</a>";
								
								$message = sprintf ( $mail_template ['mail_text'], $nm, $confirm_email_update_link, $url );
								
								$mail = new Zend_Mail ();
								$mail->setBodyHTML ( stripslashes ( nl2br ( $message ) ) );
								$mail->setFrom ( $mail_template ['from_email'], $mail_template ['from_caption'] );
								$mail->addTo ( $post ['email'], $nm );
								$mail->setSubject ( $mail_template ['subject'] );
								
								try {
									$mail->send ();
								} catch ( Exception $e ) {
									$this->_helper->errorlog ( 'Send mail to member on new updated email address for confirming his email address' . $e->getMessage () );
								}
							
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
								// Send mail to member on new updated email address for confirming his email address - E N D
								// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
							}
							$this->view->success_flag = 1;
							$this->view->msg = 'Profile updated Succesfully.';
							
							$userInfo = $memberModel->memberInfo ( ( int ) $post ['id'], 2 );
							
							$uDetailslModel = new Default_Model_UserDetails ();
							
							$userIDeatils = $uDetailslModel->getUserInfo ( $userInfo ['user_id'] );
							
							$session_array = array ("login_flag" => true, "iphone_flag" => true, "group_id" => $userInfo ['group_id'], "user_id" => $userInfo ['user_id'], "firstname" => $userInfo ['firstname'], "lastname" => $userInfo ['lastname'], "email" => $userInfo ['email'],

							"dob" => $userIDeatils ['dob'], "country_id" => $userIDeatils ['country_id'], "interest" => $userIDeatils ['interest'], "data_share" => $userIDeatils ['data_share'],

							"lastlogin_datetime" => empty ( $userInfo ['lastlogin_datetime'] ) ? '--' : date ( "jS M Y h:i A", strtotime ( $userInfo ['lastlogin_datetime'] ) ) );
							
							$this->_session->iphone_session_data = $session_array;
						} else {
							$this->view->success_flag = 0;
							$this->view->msg = 'Error(s) encountered. Profile not updated';
						}
					}
				} else {
					$this->view->success_flag = 0;
					$this->view->msg = 'No data posted. Cannot edit profile.';
				}
			} else {
				$this->view->success_flag = 0;
				$this->view->msg = 'Cannot edit profile. Invalid user type.';
			}
		}
	}
	
	public function countrylistAction() {
		$countryModel = new Default_Model_Countries ();
		
		$country = $countryModel->listCountry ();
		
		if ($countryModel) {
			
			$this->view->country = $country;
			$this->view->success_flag = 1;
		} else {
			$this->view->country = null;
			$this->view->success_flag = 0;
		}
	}
	
	public function categorylistAction() {
		$categoryModel = new Default_Model_Categories ();
		
		$sort_field = 'categories_name';
		$field = 'status';
		$value = 'Active';
		
		$category = $categoryModel->listcategories ( $sort_field, $field, $value, $strict_filter = true );
		
		if ($category) {
			
			$this->view->category = $category;
			$this->view->success_flag = 1;
		
		} else {
			$this->view->category = null;
			$this->view->success_flag = 0;
		}
	}
	
	public function interestlistAction() {
		$interestModel = new Default_Model_UserInterests ();
		
		$interest = $interestModel->listInterest ();
		
		if ($interest) {
			
			$this->view->interest = $interest;
			
			$this->view->success_flag = 1;
		} else {
			$this->view->interest = null;
			$this->view->success_flag = 0;
		}
	}
	
	public function sendinviteAction() {
		
		//	$post = array ('txtEmail' => 'jumanji@rtiom.com' , 'txtSubject' => 'jumanji','txtMsg' => 'jumanji');
		

	   $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
		
		
		$post = $this->getRequest ()->getPost ();
		
		//$x = print_r($post, true); $this->_helper->errorlog($x);
		

		if (! empty ( $post )) {
			
			//			$error_flag = false;
			//			$error_text = 'Error(s) encountered. Cannot send invitation' . "<br><br>";
			

			if (empty ( $post ['txtEmail'] )) {
				$this->view->success_flag = 0;
				$this->view->msg = "Recipient's E-Mail address is blank";
			} else {
				$email_arr = explode ( ";", $post ['txtEmail'] );
				$new_email_arr = array ();
				foreach ( $email_arr as $k => $v ) {
					$earr = explode ( ",", $v );
					foreach ( $earr as $ek => $ev ) {
						array_push ( $new_email_arr, $ev );
					}
				}
				$email_arr = $new_email_arr;
				$valid_email_flag = true;
				foreach ( $email_arr as $k => $v ) {
					$valid_email = $this->_helper->Emailvalidate ( $v );
					if ($valid_email == false) {
						$valid_email_flag = false;
					}
				}
				if ($valid_email_flag == false) {
					
					$this->view->success_flag = 0;
					$this->view->msg = "Recepient's E-Mail address is invalid";
				
				}
			}
			if (empty ( $post ['txtSubject'] )) {
				$this->view->success_flag = 0;
				$this->view->msg = "Subject cannot be blank";
			
			}
			if (empty ( $post ['txtMsg'] )) {
				$this->view->success_flag = 0;
				$this->view->msg = "Message text cannot be blank";
			
			}
			
			if (! empty ( $this->_session->iphone_session_data ['lastname'] )) {
				$from_nm = $this->_session->iphone_session_data ['firstname'] . ' ' . $this->_session->iphone_session_data ['lastname'];
			} else {
				$from_nm = $this->_session->iphone_session_data ['firstname'];
			}
			
			$memberModel = new Default_Model_Member ();
			
			$sucess_cnt = 0;
			
			if (! empty ( $email_arr )) {
				foreach ( $email_arr as $k => $v ) {
					$invite_id = 1;
					$valid_email = $this->_helper->Emailvalidate ( $v );
					if ($valid_email == false) { // do not send invitation mail to invalid email address
						continue;
					}
					
					$existing_member = $memberModel->CheckUniqueEmail ( $v, 2 );
					if ($existing_member == true) { // do not send invitation mail to exiting m3 members
						continue;
					}
					
					$invitation_token = $this->_helper->randomstringgenerator ( 24 );
					
					//	$data = array ('recepient_email' => $v, 'sent_by_member_id' => $this->_session->session_data ['user_id'], 'invitation_token' => $invitation_token );
					//	$invite_id = $inviteModel->sendInvitation ( $data );
					

					//	if ($invite_id) {
					if ($invite_id) {
						$sucess_cnt ++;
						// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
						// Send invitation mail to member friends - B E G I N
						// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
						

						$mailModel = new Default_Model_MailTemplate ();
						$mail_template = $mailModel->getTemplate ( 3 );
						
						$url = $this->_baseurl . "/accept-invitation/" . $invitation_token;
						$accept_link = "<a href='" . $url . "'>Accept Invitation</a>";
						
						$c_msg = null;
						if (! empty ( $post ['txtMsg'] )) {
							$c_msg = 'Custom message by ' . $from_nm . "\n";
							$c_msg .= "----------------------------------------" . "\n";
							$c_msg .= htmlspecialchars ( $post ['txtMsg'] );
							$c_msg .= "\n";
							$c_msg .= "----------------------------------------" . "\n";
						}
						
						$message = sprintf ( $mail_template ['mail_text'], $accept_link, $url, $c_msg );
						
						$mail = new Zend_Mail ();
						$mail->setBodyHTML ( stripslashes ( nl2br ( $message ) ) );
						$mail->setFrom ( $this->_session->iphone_session_data ['email'], $from_nm );
						$mail->addTo ( $v, $v );
						$mail->setSubject ( $post ['txtSubject'] );
						
						try {
							$mail->send ();
						} catch ( Exception $e ) {
							$this->_helper->errorlog ( 'Send invitation mail to member friends' . $e->getMessage () );
						}
					
		// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					// Send invitation mail to member friends - E N D
					// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
					}
				}
				$invite_id = 0;
				
				if ($sucess_cnt > 0) {
					$this->view->msg = INVITATION_SENT;
					$this->view->success_flag = 1;
				} else {
					$this->view->msg = 'Invalid Email . Unable to Send Invitation';
					$this->view->success_flag = 0;
				}
			} else {
				$this->view->success_flag = 0;
				$this->view->msg = 'Missing Invitation Data';
			}
		} else {
			$this->view->success_flag = 0;
			$this->view->msg = 'Invalid / Missing Invitation Data';
		}
	}
	
	
	}

	///////////////// survey module ///////////////////

	public function membersurveylistAction() {
		
	 $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
			$user_type = $this->_session->iphone_session_data ['group_id'];
			
			if ($user_type == '2') {
				$surveyModel = new Default_Model_Survey ();
				
				$userModel = new Default_Model_Member ();
				
				$status = 'Active';
				$surveyList = $surveyModel->getfrontendSurvey ( $status );
				
				$sid = $surveyList ['survey_id'];
				
				// check if survey taken
				$customer_surveyModel = new Default_Model_CustomerSurvey ();
				$surveyTakenInfo = $customer_surveyModel->SurveyTakenInfo ( ( int ) $this->_session->iphone_session_data ['user_id'], $sid );
				
				if (! empty ( $surveyTakenInfo )) {
					
					$this->view->success_flag = 0;
					$this->view->survey = null;
					$this->view->msg = SURVEY_COMPLETED;
				} else {
					
					if (! empty ( $surveyList )) {
						$this->view->success_flag = 1;
						$this->view->survey = $surveyList;
					} else {
						$this->view->success_flag = 0;
						$this->view->survey = null;
						$this->view->msg = 'Currently there are no surveys available for you. Please try again later.';
					}
				
				}
			
			} else {
				$this->view->success_flag = 0;
				$this->view->msg = 'Cannot list survey. Invalid user type.';
			}
		}
	}
	
	public function surveyquestionsAction() {
	//	$post = $this->getRequest ()->getPost ();
		
	$session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			$user_type = $this->_session->iphone_session_data ['group_id'];
			//$user_type = 'M';
			if ($user_type == '2') {
				$post = $this->getRequest ()->getPost ();
			//		$post['id'] = 7;
				$sid = ! empty ( $post ['id'] ) ? ( int ) $post ['id'] : 0;
			//	$sid = 45;
				$surveyModel = new Default_Model_Survey ();
				$surveinfo = $surveyModel->surveyInfo ( $sid );
				
				if (! empty ( $surveinfo )) {
					$this->view->surveyinfo = $surveinfo;
					
					$survey_questionModel = new Default_Model_SurveyQuestion ();
					$questions = $survey_questionModel->listSurveyQuestions ( $sid );
					
					if ($questions) {
						
						$data = array ('action' => 'A', 'survey_id' => $sid, 'user_id' => ( int ) $this->_session->iphone_session_data ['user_id'] );
						
						$queansModel = new Default_Model_QuestionAnswer ();
						$rsaModel = new Default_Model_ResponseSetAnswer ();
						$queRulesModel = new Default_Model_QueRules ();
						
						$arrQue = array ();
						
						foreach ( $questions as $k => $v ) {
							
							// -- que rules start --
							$hide_que = false;
							$rules = $queRulesModel->isQueTargetQue ( $v ['sq_id'] );
							if (! empty ( $rules )) {
								$hide_que = true;
							}
							// -- que rules end --
							

							$answers = $rsaModel->listQueAns ( $v ['question_id'] );
							$arrAns = array ();
							if ($answers) {
								$arrQue [] = array ('que_info' => $v, 'hide_que' => $hide_que );
							}
							
							$answers = $queansModel->listQueAns ( 'qa.sort_order, qa.qa_id', $v ['question_id'] );
							if ($answers) {
								$arrQue [] = array ('que_info' => $v, 'hide_que' => $hide_que );
							}
						}
						
						$this->view->success_flag = 1;
						$this->view->questions = $arrQue;
					} else {
						$this->view->success_flag = 0;
						$this->view->questions = null;
						$this->view->msg = 'No questions associated with survey';
					}
				} else {
					$this->view->success_flag = 0;
					$this->view->msg = 'Cannot list survey questions. Invalid survey id.';
				}
			} else {
				$this->view->success_flag = 0;
				$this->view->msg = 'Cannot list survey questions. Invalid user type.';
			}
		}
	}
	
	public function surveyanswersAction() {
		
		$post = $this->getRequest ()->getPost ();
		
			$session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			$user_type = $this->_session->iphone_session_data ['group_id'];
			//$user_type = 'M';
			

			if ($user_type == '2') {
				$post = $this->getRequest ()->getPost ();
				$sid = ! empty ( $post ['id'] ) ? ( int ) $post ['id'] : 0;
				
			//	$sid = 7;
				

				$surveyModel = new Default_Model_Survey ();
				$surveinfo = $surveyModel->surveyInfo ( $sid );
				
				if (! empty ( $surveinfo )) {
					$this->view->surveyinfo = $surveinfo;
					
					$survey_questionModel = new Default_Model_SurveyQuestion ();
					$questions = $survey_questionModel->listSurveyQuestions ( $sid );
					
					if ($questions) {
						$queansModel = new Default_Model_QuestionAnswer ();
						$rsaModel = new Default_Model_ResponseSetAnswer ();
						$queRulesModel = new Default_Model_QueRules ();
						
						$arrAns = array ();
						
						foreach ( $questions as $k => $v ) {
							
							$answers = $rsaModel->listQueAns ( $v ['question_id'] );
							
							if ($answers) {
								foreach ( $answers as $k1 => $v1 ) {
									
									// ~~ que rules start ~~ //
									

									$add_que = null;
									$rules = $queRulesModel->getQueRule ( $v ['sq_id'], $v1 ['answer_id'] );
									if (! empty ( $rules )) {
										foreach ( $rules as $rk => $rv ) {
											$add_que .= $rv ['target_sq_id'] . "|";
										}
										$add_que = substr ( $add_que, 0, strlen ( $add_que ) - 1 );
									}
									
									$rmv_que = '';
									if ($v ['answer_type'] != 'm') {
										$nrules = $queRulesModel->getNonTargetQue ( $v ['sq_id'], $v1 ['answer_id'] );
										if (! empty ( $nrules )) {
											foreach ( $nrules as $rk => $rv ) {
												$rmv_que .= $rv ['target_sq_id'] . '|';
											}
											$rmv_que = substr ( $rmv_que, 0, strlen ( $rmv_que ) - 1 );
										}
									}
									
									// ~~ que rules end ~~ //
									

									$arrAns [] = array ('sq_id' => $v ['sq_id'], 'answer_id' => $v1 ['answer_id'], 'answer_text' => $v1 ['answer_text'], 'answer_type' => $v1 ['answer_type'], 'weightage' => $v1 ['weightage'], 'free_text' => $v1 ['free_text'], 'free_text_caption' => $v1 ['custom_free_text_caption'], 'add' => $add_que, 'remove' => $rmv_que );
								}
							}
							
							$answers = $queansModel->listQueAns ( 'qa.sort_order, qa.qa_id', $v ['question_id'] );
							
							if ($answers) {
								foreach ( $answers as $k1 => $v1 ) {
									
									// ~~ que rules start ~~ //
									

									$add_que = null;
									$rules = $queRulesModel->getQueRule ( $v ['sq_id'], $v1 ['answer_id'] );
									if (! empty ( $rules )) {
										foreach ( $rules as $rk => $rv ) {
											$add_que .= $rv ['target_sq_id'] . "|";
										}
										$add_que = substr ( $add_que, 0, strlen ( $add_que ) - 1 );
									}
									
									$rmv_que = '';
									if ($v ['answer_type'] != 'm') {
										$nrules = $queRulesModel->getNonTargetQue ( $v ['sq_id'], $v1 ['answer_id'] );
										if (! empty ( $nrules )) {
											foreach ( $nrules as $rk => $rv ) {
												$rmv_que .= $rv ['target_sq_id'] . '|';
											}
											$rmv_que = substr ( $rmv_que, 0, strlen ( $rmv_que ) - 1 );
										}
									}
									
									// ~~ que rules end ~~ //
									

									$arrAns [] = array ('sq_id' => $v ['sq_id'], 'answer_id' => $v1 ['answer_id'], 'answer_text' => $v1 ['answer_text'], 'answer_type' => $v1 ['answer_type'], 'weightage' => $v1 ['weightage'], 'free_text' => $v1 ['free_text'], 'free_text_caption' => $v1 ['custom_free_text_caption'], 'add' => $add_que, 'remove' => $rmv_que );
								}
							}
						
		//exit;
						}
						
						//echo '<pre>';  print_r($arrAns);     echo '<pre>';            exit;
						

						if (! empty ( $arrAns )) {
							$this->view->success_flag = 1;
							$this->view->answers = $arrAns;
						} else {
							$this->view->success_flag = 0;
							$this->view->answers = null;
							$this->view->msg = 'No answers found';
						}
					} else {
						$this->view->success_flag = 0;
						$this->view->msg = 'Cannot fetch answers. No question associated with survey.';
					}
				} else {
					$this->view->success_flag = 0;
					$this->view->msg = 'Cannot list survey answers. Invalid survey id.';
				}
			} else {
				$this->view->success_flag = 0;
				$this->view->msg = 'Cannot list survey answers. Invalid user type.';
			}
		}
	}
	
	public function surveyresponseAction() {
		
     $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
		
		$post = $this->getRequest ()->getPost ();
		
		//	$x = print_r($post, true);
		//  $this->_helper->errorlog($x);
		

		//	  $post ['qcount'] = 1;
		

		if (! empty ( $post ['qcount'] )) {
			//$this->getDefaultAdapter()->beginTransaction();
			

			//$this->_helper->errorlog(' - 1 -');
			

			$sqModel = new Default_Model_SurveyQuestion ();
			
			$var_sq_id = ( int ) $post ['qid0'];
			//	$var_sq_id = 109;
			//$this->_helper->errorlog(' - A - ' . $var_sq_id);
			

			$surveyInfo = null;
			if (! empty ( $var_sq_id )) {
				//$this->_helper->errorlog(' - B -');
				$result = $sqModel->getSurveyId ( $var_sq_id );
				if (! empty ( $result )) {
					//$this->_helper->errorlog(' - C -');
					$this->view->id = $result ['survey_id'];
					$surveyModel = new Default_Model_Survey ();
					$surveyInfo = $surveyModel->surveyInfo ( $result ['survey_id'] );
				}
			}
			
			if (! empty ( $surveyInfo )) {
				$responseModel = new Default_Model_Response ();
				//$this->_helper->errorlog(' - 2 -');
				

				$insert_error = false;
				for($q = 0; $q < ( int ) $post ['qcount']; $q ++) {
					
					$post ['user_id'] = $this->_session->iphone_session_data ['user_id'];
					
					$sq_id = ! empty ( $post ['qid' . $q] ) ? $post ['qid' . $q] : null;
					$answer_id = ! empty ( $post ['ansid' . $q] ) ? $post ['ansid' . $q] : null;
					$answer_text = ! empty ( $post ['anstext' . $q] ) ? $post ['anstext' . $q] : null;
					
					if (is_null ( $sq_id ) == false && is_null ( $answer_id ) == false) {
						$post ['sq_id'] = $sq_id;
						$post ['answer_id'] = $answer_id;
						$post ['answer_text'] = $answer_text;
						$response_id = $responseModel->insertSurveyResponses ( $post );
						if (empty ( $response_id )) {
							$insert_error = true;
						}
					} else {
						$insert_error = true;
					}
				
		//$x = print_r($data, true);        $this->_helper->errorlog(' q : ' . $q . " --- " . $x);
				}
				
				if ($insert_error == false) {
					//$this->_helper->errorlog(' - 3 -');
					//$this->getDefaultAdapter()->commit();
					$this->view->success_flag = 1;
					
					$surveytaken ['survey_id'] = $result ['survey_id'];
					$surveytaken ['user_id'] = $this->_session->iphone_session_data ['user_id'];
					$surveytaken ['completed'] = 'y';
					
					$customer_surveyModel = new Default_Model_CustomerSurvey ();
					$surveyInfo = $customer_surveyModel->insertcustomer_survey ( $surveytaken );
					
					if (! empty ( $surveyInfo ['completion_msg'] )) {
						
						$this->view->success_msg = $surveyInfo ['completion_msg'];
					} else {
						$this->view->success_msg = 'Thank you for participating.';
					}
				
				} else {
					//$this->_helper->errorlog(' - 4 -');
					//$this->getDefaultAdapter()->rollBack();
					$this->view->success_flag = 0;
					$this->view->msg = 'Error encountered. Response not submitted';
				}
			} else { // end of if for ------ if( !empty($surveyInfo)){
				//$this->_helper->errorlog(' - 5 -');
				$this->view->success_flag = 0;
				$this->view->msg = 'Error encountered. Response not submitted';
			}
		} else { // end of if for ------- if( !empty($post['qcount']) ){
			//$this->_helper->errorlog(' - 6 -');
			$this->view->success_flag = 0;
			$this->view->msg = 'Error encountered. Response not submitted';
		}
	}
	
}
	
////////////////// topic module ///////////////////
	
	// used to show topic skip and report violation and submit topic responses
	public function viewtopicpostAction() {
		
		$post = $this->getRequest ()->getPost ();
		
	//	$x = print_r ( $post, true );
	//	$this->_helper->errorlog ( $x );
		
		//	$x = print_r ( $this->_session->iphone_session_data['search_param'], true );
		//	$this->_helper->errorlog ('ssssssssss : ' . $x );
		

		$user_type = $this->_session->iphone_session_data ['group_id'];
		$user_id = $this->_session->iphone_session_data ['user_id'];
		
		$post ['user_id'] = $user_id;
		
		$userDeatislModel = new Default_Model_UserDetails ();
		$result = $userDeatislModel->getUserInfo ( $user_id );
		
		$country = $result ['country_id'];
		
	//	$post = array ('user_id' => '52', 'topic_id' => '358', 'score' => '2','report' => '' );
		
     //	$post = array ('user_id' => '52', 'topic_id' => '358', 'score' => '2','single' => '' );
		//	for report
		//	$post = array ('user_id' => '52', 'topic_id' => '131', 'report' => '1' );
		

		//	for skip
		//		$post = array ('user_id' => '52', 'topic_id' => '176', 'skip' => '1' );
		

	 $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			$this->view->success_flag = 0;
			
			if ($user_type == '2') {
			    
			    $restCounter = $userDeatislModel->resetNotificationCount($user_id);
				
				if (! empty ( $post )) {
					
					if (! empty ( $user_id )) {
						
						if (! empty ( $post ['topic_id'] )) {
							
							//		if (! empty ( $post ['score'] )) {
							

							// for reporting
							if (! empty ( $post ['report'] )) {
								
								// $post['user_id'] = $user_id;
								// $post['topic_id'] = $topic_id;
								

								$TopicModel = new Default_Model_Topic ();
								$ReportModel = new Default_Model_Report ();
								
								$insertReport = $ReportModel->insertReport ( $post );
								
								if ($insertReport) {
								    
								    // BEGIN push notification logic //

                                    $result = $this->_helper->sendreportviolationnotification($post ['topic_id']);
                            
                                    if ($result == 1){
                                        $this->_helper->errorlog(INVALID_MISSING_TOPIC_ID);
                                    } else if ($result == 3){
                                        $this->_helper->errorlog(TOPIC_VIOLATION_NOTIFICATION_SENT);
                                    } else if ($result == 4){
                                        $this->_helper->errorlog(INVALID_TOPIC_ID);
                                    } else{
                                        $this->_helper->errorlog(TOPIC_VIOLATION_NOTIFICATION_NOT_SENT);
                                    }
                        
                        		    // END push notification logic //
									
									$this->view->msg = TOPIC_REPORT;
									$this->view->success_flag = 2;
									
									$insertReport = $ReportModel->reportCount ( $post ['topic_id'] );
									
									$rcount = $insertReport ['report'];
									
									//   echo TOPIC_FAULT_LIMIT ; exit;
									
									if ($rcount >= TOPIC_FAULT_LIMIT) {
										$statusinactive = $TopicModel->Updatetopicstatus ( $post ['topic_id'] );
									}
								
								}
							
							} elseif (! empty ( $post ['score'] )) {
								$TopicModelReponse = new Default_Model_TopicResponse ();
								
								// inserts user response via javascript submit
								$insertid = $TopicModelReponse->inserttopicResponse ( $post );
								
								//			}
								

								if ($insertid) {
									
									if( empty ( $post ['single'] )) {
									$this->view->msg = TOPIC_SUBMITTED;
									$this->view->success_flag = 1;
									}else{
										
									$this->view->msg = TOPIC_SUBMITTED;
									$this->view->success_flag = 4;
									
									}
									$Topic_votes = $TopicModelReponse->getVotesCount ( $post ['topic_id'] );
									
									$this->view->Topic_votes = $Topic_votes;
									
									$Lasttopic_response = $TopicModelReponse->getLastTopic ( $post ['user_id'], $post ['topic_id'] );
									$this->view->lastresponse = $Lasttopic_response;
								
		//					echo "<pre>";
								//					print_r ( $Topic_votes );
								//					echo "</pre>";
								//					echo "<pre>";
								//					print_r ( $Lasttopic_response );
								//					echo "</pre>";
								

								///////////
								} else {
									
									$this->view->msg = TOPIC_NOT_SUBMITTED;
									$this->view->success_flag = 0;
								
								}
							} elseif (! empty ( $post ['skip'] )) {
								
								//	$this->view->msg = POST_EMPTY;
								$this->view->success_flag = 3;
								$this->view->msg = TOPIC_SKIP;
								$topicresponseModel = new Default_Model_TopicResponse ();
								
								$skiptopic = $topicresponseModel->skipCurrentTopic ( $post );
							
							} else {
								$this->view->msg = POST_EMPTY;
								$this->view->success_flag = 1;
							}
						
						} //
					

					} else {
						$this->view->msg = POST_EMPTY;
						$this->view->success_flag = 0;
					}
				
				} else {
					$this->view->msg = POST_EMPTY;
					$this->view->success_flag = 1;
				}
				
				///  pasted new topic content above
				

				$TopicModel = new Default_Model_Topic ();
				
				$CategoryModel = new Default_Model_Categories ();
				
				$sort_field = 'categories_name';
				
				$field = 'status';
				
				$value = 'Active';
				
				$catInfo = $CategoryModel->listcategories ( $sort_field, $field, $value, $strict_filter = false );
				
				$this->view->catInfo = $catInfo;
				
				if (! empty ( $this->_session->iphone_session_data ['search_param'] ['country1'] )) {
					
					$tcountry = $this->_session->iphone_session_data ['search_param'] ['country1'];
				
				}
				
				if (! empty ( $this->_session->iphone_session_data ['search_param'] ['cat_id'] )) {
					
					$category_ids = $this->_session->iphone_session_data ['search_param'] ['cat_id'];
				
				} else {
					
					$category_ids = '';
				}
				
				if (empty ( $tcountry )) {
					$tcountry = $country;
				
				}
				
				if (! empty ( $category_ids )) {
					
					$topicList = $TopicModel->getFrontendtopiclist ( $user_id, $tcountry, $category_ids );
				
				} else {
					
					$category_ids = null;
					
					$topicList = $TopicModel->getFrontendtopiclist ( $user_id, $tcountry, $category_ids );
				}
				
				if (! empty ( $topicList )) {
					
					$this->view->topicList = $topicList;
				
		//	$this->view->success_flag = 1;
				

				} else {
					$this->view->msg = TOPIC_NOT_FOUND;
					$this->view->success_flag = - 1;
				
				}
			
		///  pasted new topic content above
			

			} else {
				
				$this->view->msg = 'Invalid User';
				$this->view->success_flag = 0;
			}
		
		}
	
	}
	
	// set topic filter in session
	public function topicfilterAction() {
		
		
		
		//	$post = array ('country1' => '(null)', 'cid' => '19, 20, 21, 22,23,24,25'  );
		

		//$x = print_r ( $post, true );
		//$this->_helper->errorlog ( $x );
		
	 $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
			$post = $this->getRequest ()->getPost ();
			
			$user_type = $this->_session->iphone_session_data ['group_id'];
			$user_id = $this->_session->iphone_session_data ['user_id'];
			
			$userDeatislModel = new Default_Model_UserDetails ();
			$result = $userDeatislModel->getUserInfo ( $user_id );
			
			$country = $result ['country_id'];
			if ($user_type == '2') {
				
				if (! empty ( $post )) {
					
					if (! empty ( $post ['country1'] ) && $post ['country1'] != '(null)') {
						
						$country1 = $post ['country1'];
					
					} else {
						
						$country1 = $result ['country_id'];
					}
					
					if (! empty ( $post ['cid'] )) {
						
						//	$comma_separated = implode(",", $post['cid']);
						//	$cat_id = implode ( ",", $post ['cid'] );
						

						$cat_id = $post ['cid'];
						
						$search_param = array ("cat_id" => $cat_id, "country1" => $country1 );
					
					} else {
						
						$cat_id = '';
						
						$search_param = array ("cat_id" => '', "country1" => $country1 );
					}
					
					$this->view->cat_id = $cat_id;
					$this->view->country1 = $country1;
					
					//	$this->p($search_param);
					if (! empty ( $search_param )) {
						$this->view->success_flag = 1;
						$this->view->msg = 'Filter set';
						$this->_session->iphone_session_data ['search_param'] = $search_param;
					
					} else {
						$this->view->msg = 'Filter not set';
						$this->view->success_flag = 0;
						$this->_session->iphone_session_data ['search_param'] = $search_param;
					
					}
				
		//	$search_param = array ("country1" => '', "cat_id" => '' );
				

				} else {
					
					$search_param = array ("country1" => '', "cat_id" => '' );
					$this->_session->iphone_session_data ['search_param'] = $search_param;
					
					$this->view->msg = POST_EMPTY;
					$this->view->success_flag = 0;
				
				}
			
			} else {
				$this->view->msg = POST_EMPTY;
				$this->view->success_flag = 0;
			
			}
		}
	
	}
	
	public function addtopicAction() {
		
		$post = $this->getRequest ()->getPost ();
		
		//	$x = print_r ( $post, true );
		//	$this->_helper->errorlog ( $x );
		

		//	$post = array ('name' => '52', 'category_id' => '23') ;
		
	 $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
			$user_type = $this->_session->iphone_session_data ['group_id'];
			$user_id = $this->_session->iphone_session_data ['user_id'];
			
			$userDeatislModel = new Default_Model_UserDetails ();
			$result = $userDeatislModel->getUserInfo ( $user_id );
			
			$TopicCategoriesModel = new Default_Model_TopicCategories ();
			
			$TopicModel = new Default_Model_Topic ();
			
			$country = $result ['country_id'];
			if ($user_type == '2') {
				
				if (! empty ( $post )) {
					
					$success_flag = 1;
					
					if (empty ( $post ['name'] )) {
						$success_flag = 0;
						$this->view->success_flag = 0;
						$this->view->msg = EMPTY_TOPIC_NAME;
					}
					
					if (empty ( $post ['category_id'] )) {
						$success_flag = 0;
						$this->view->success_flag = 0;
						$this->view->msg = EMPTY_CATEGORY_NAME;
					}
					
					if ($success_flag == 1) {
						
						$post ['user_id'] = $user_id;
						$post ['country_id'] = $this->_session->iphone_session_data ['country_id'];
			            $post ['description'] = '';
						
						$inserttopic = $TopicModel->inserttopic ( $post );
						
						if ($inserttopic) {
							
							$this->view->success_flag = 1;
							$this->view->msg = TOPIC_CREATED;
							
							$addTopiccategory = $TopicCategoriesModel->insertTopicCategory ( $inserttopic, $post ['category_id'] );
						
                            // ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^
                            // start code for sending push notifications //
                            
            				$result = $this->_helper->sendnotification($inserttopic, $post ['category_id']);
                    
    						if ($result == 1){
    						    $this->_helper->errorlog(INVALID_TOPIC_ID);
    						} else if ($result == 2){
    						    $this->_helper->errorlog(TOPIC_NOTIFICATION_ALREADY_SENT);
    						} else if ($result == 3){
    						    $this->_helper->errorlog(TOPIC_NOTIFICATION_SENT);
    						} else if ($result == 4){
    						    $this->_helper->errorlog(TOPIC_NOTIFICATION_ALREADY_SENT);
    						} else if ($result == 5){
    						    $this->_helper->errorlog(NO_MEMBER_FOUND_MATCHING_SURVEY_CRITERIA);
    						} else{
    						    $this->_helper->errorlog(NO_MEMBER_FOUND_MATCHING_TOPIC_CRITERIA);
    						}
                            
                            // end code for sending push notifications //
                            // ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^
							

						} else {
							
							$this->view->success_flag = 0;
							$this->view->msg = TOPIC_CREATE_ERROR;
						
						}
					
					}
				
				} else {
					
					$this->view->msg = POST_EMPTY;
					$this->view->success_flag = 0;
				
				}
			
			} else {
				$this->view->msg = POST_EMPTY;
				$this->view->success_flag = 0;
			
			}
		
		}
	}
	
	// used to show topic reponses in percentaged on user posted and user answered topics
	public function responseAction() {
		
		$post = $this->getRequest ()->getPost ();
		
	//	$post = array ('show' => '2' );
		
	//	$x = print_r ( $post, true );
	//	$this->_helper->errorlog ( $x );
		
	 $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
			$user_type = $this->_session->iphone_session_data ['group_id'];
			$user_id = $this->_session->iphone_session_data ['user_id'];
			
			$userDeatislModel = new Default_Model_UserDetails ();
			$result = $userDeatislModel->getUserInfo ( $user_id );
			
			$TopicCategoriesModel = new Default_Model_TopicCategories ();
			
			$TopicResponseModel = new Default_Model_TopicResponse ();
			
			$TopicModel = new Default_Model_Topic ();
			
			$country = $result ['country_id'];
			if ($user_type == '2') {
				
				if (! empty ( $post ['show'] )) {
					
					$this->view->show = $post ['show'];
					
					$TopicModelReponse = new Default_Model_TopicResponse ();
					
					// user topics
					if ($post ['show'] == 1) {
						
						$userCreated = $TopicResponseModel->getUserResponse ( $user_id );
						$this->view->userCreated = $userCreated;
						
						$arr = array ();
						
						if ($userCreated) {
							$this->view->success_flag = 1;
							$this->view->msg = 'user reponses found';
							$topicArr = array ();
							$response_count = 0;
							foreach ( $userCreated as $tk => $tv ) {
								$response_count = 0;
								$tv ['response'] = $this->_helper->topicresponsearray->getVoteCount ( $tv ['topic_id'], $TopicResponseModel );
								if(is_array($tv['response'])){
									for($i = 1; $i<=5 ; $i++) {
									$response_count  +=  $tv['response']["rvot_$i"];
									}
								}
								$tv['response_count'] = $response_count;
								$topicArr [] = $tv;
							}
							
							/*
							$arrTopic = array ();
							if (! empty ( $userCreated )) {
								$arrTopic = $this->_helper->topicresponsearray ( $userCreated, $TopicModelReponse );
							}*/
						//	$this->p($topicArr); exit;
							$this->view->Topic_votes = $topicArr;
						
						} else {
							
							$this->view->success_flag = 0;
							$this->view->msg = 'user reponses not found';
						
						}
					
		//$this->p($arrTopic); exit;
					// user voted
					} elseif ($post ['show'] == 2) {
						
						$userVoted = $TopicResponseModel->getUserOtherResponse ( $user_id );
						
						$this->view->userVoted = $userVoted;
						
						$arr = array ();
						
						if ($userVoted) {
							$this->view->success_flag = 1;
							$this->view->msg = 'user reponses found';
							
							
							$topicArr = array ();
							$response_count = 0;
							foreach ( $userVoted as $tk => $tv ) {
								$response_count = 0;
								$tv ['response'] = $this->_helper->topicresponsearray->getVoteCount ( $tv ['topic_id'], $TopicResponseModel );
								if(is_array($tv['response'])){
									for($i = 1; $i<=5 ; $i++) {
									$response_count  +=  $tv['response']["rvot_$i"];
									}
								}
								$tv['response_count'] = $response_count;
								$topicArr [] = $tv;
							}
							
							
//							$arrTopic = array ();
//							if (! empty ( $userVoted )) {
//								$arrTopic = $this->_helper->topicresponsearray ( $userVoted, $TopicModelReponse );
//							}
							
							$this->view->Topic_votes = $topicArr;
						
		//	print_r($topicArr); exit;
						

						} else {
							
							$this->view->success_flag = 0;
							$this->view->msg = 'user reponses not found';
						
						}
					
					}
				
				} else {
					$this->view->msg = 'Not enough data to Process your Request.';
					$this->view->success_flag = 0;
				
				}
			
			} else {
				$this->view->msg = 'Invalid User Type';
				$this->view->success_flag = 0;
			}
		}
	
	}
	
	// show top recent and view all topics based on responses ..
	public function toptopicAction() {
		
		$post = $this->getRequest ()->getPost ();
		
		$this->view->topic_rank_limit = TOPIC_RANK_LIMIT;
		
		//$x = print_r ( $post, true );	$this->_helper->errorlog ( $x );
		
//		(
//    [country1] => -1
//    [cid] => -1
//)

		
		
		//	$x = print_r ( $this->_session->iphone_session_data['home_filter'], true );
		//	$this->_helper->errorlog ('ssssssssss : ' . $x );
	 //   $post = array ('view' => 1, 'recent' => 1  );
	
	// for most voted
	   //  $post = array ('view' => 1, 'recent' => 1  );
		
	//	$post = array ('view' => 1  );
		//$post = array ('recent' =>  '2');
		
		
		//	$post = array ( 'recent' => 1  );
		
		// recent 1 for most voted and recent 2 for recent topics
       
			 $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
			
			$user_type = $this->_session->iphone_session_data ['group_id'];
			$user_id = $this->_session->iphone_session_data ['user_id'];
			
			$userDeatislModel = new Default_Model_UserDetails ();
		    $result = $userDeatislModel->getUserInfo ( $user_id );
			
		    $defaultcountry = $result['country_id'];
      
			
			//	$viewall = $post['view'];
			

		//	$country_id = $this->_session->iphone_session_data ['home_filter'] ['homecountry'];
			
		//	$categort_ids = $this->_session->iphone_session_data ['home_filter'] ['homecat_id'];
			
		
			if(empty($post ['country1']))  {
				
				$country_id = $defaultcountry;
			
			}else{
			
			    $country_id = $post ['country1'];
			}
		
			if((empty($post ['cid'])) || ($post ['cid'] == '-1'  )) {
				
				$categort_ids = '';
			
			}else{
			
			    $categort_ids = $post ['cid'];
			}
			
			// if not empty post speacial action
			
			if (! empty ( $post )) {
				
				if(!empty($post ['view'])){
				
				$viewall = $post ['view'];
				$this->view->success_flag = 1;
				$this->view->msg = 'Listing all Topics';
				
				}else{
				
				 	$viewall = '';
				
				}
				
			if(!empty($post ['recent']) ){
				
				if($post ['recent'] == 2) {
    				$recent = $post ['recent'];
    				$this->view->success_flag = 1;
    				$this->view->msg = 'Showing Recent Topics';
    				$restCounter = $userDeatislModel->resetNotificationCount($user_id);
				}elseif($post ['recent'] == 1){
    				$recent = $post ['recent'];
    				$this->view->success_flag = 1;
    				$this->view->msg = 'Showing Most Voted Topics';
				}
				
			}else{
				 	$recent = '';
			}
				
				$TopicModelResponse = new Default_Model_TopicResponse ();
				$topicTen = $TopicModelResponse->getTopTenTopics(TOPIC_RANK_LIMIT , $viewall ,$country_id ,$categort_ids ,$recent,$user_id);
				
				if (! empty ( $topicTen )) {
							$arrTopic = $this->_helper->topicresponsearray ( $topicTen, $TopicModelResponse );
					}
				
					
				
				
				$arr = array ();
				
				if(!empty ($topicTen)){
				
				foreach ( $arrTopic as $v ) {
					
					$checkiftopictaken = $TopicModelResponse->getTopicRedirect ( $v['topic_id'], $user_id );
					
				  
					
					$v ['taken'] = ! empty ( $checkiftopictaken ) ? 1 : 0;
				
					
					array_push ( $arr, $v );
					
					
				
				}
		}
				//	print_r($topicTen);
				//	print_r($arr); exit;
				

				if (empty ( $topicTen )) {
					
					$this->view->success_flag = 0;
					$this->view->msg = 'No topics Found';
				} else {
//					$this->view->success_flag = 1;
//					$this->view->msg = 'Showing all topics';
				
				}
				
				
				//$arr['responses'] = Array();
				if ($arr) {
							$this->view->success_flag = 1;
							$this->view->msg = 'user reponses found';
							$topicArr = array ();
							$response_count = 0;
							foreach ( $arr as $tk => $tv ) {
								$response_count = 0;
							//	$tv ['response'] = $this->_helper->topicresponsearray->getVoteCount ( $tv ['topic_id'], $TopicModelResponse );
								if(is_array($tv['responses'])){
									for($i = 1; $i<=5 ; $i++) {
									$response_count  +=  $tv['responses']["rvot_$i"];
									}
								}
								$tv['response_count'] = $response_count;
								$topicArr [] = $tv;
							}
							
				
				}
				
			//	$this->p($topicArr) ; exit;
				
				if( !empty($topicArr) ){
				    $this->view->success_flag = 1;
				    $this->view->ranking = $topicArr;
				}else{
				    $this->view->success_flag = 0;
				    $this->view->ranking = null;
				}
			
			}
			
		// if empty post default action
			else {
				
				$viewall = '';
			//	$categort_ids = '';
				$recent = '';
				
				if(empty($viewall)){

					$this->view->limit = TOPIC_RANK_LIMIT;
				
				}
				
				$TopicModelResponse = new Default_Model_TopicResponse ();

				$topicTen = $TopicModelResponse->getTopTenTopics(TOPIC_RANK_LIMIT , $viewall ,$country_id ,$categort_ids ,$recent,$user_id);
				
			
			
			if (! empty ( $topicTen )) {
								$arrTopic = $this->_helper->topicresponsearray ( $topicTen, $TopicModelResponse );
							}
				
				
				
				$arr = array ();
				
				foreach ( $arrTopic as $v ) {
					
					$checkiftopictaken = $TopicModelResponse->getTopicRedirect ( $v ['topic_id'], $user_id );
					
				
					
					$v ['taken'] = ! empty ( $checkiftopictaken ) ? 1 : 0;
					
				
					
					array_push ( $arr, $v );
				
					
				//	$this->p($arr); exit;
				}

					
				if (empty ( $arrTopic )) {
					
					$this->view->success_flag = 0;
					$this->view->msg = 'No topics Found';
				} else {
					$this->view->success_flag = 1;
					$this->view->msg = 'Listing Top '.TOPIC_RANK_LIMIT.' Topics ';
				
				}
				
				
				
					if ($arr) {
							$this->view->success_flag = 1;
							$this->view->msg = 'user reponses found';
							$topicArr = array ();
							$response_count = 0;
							foreach ( $arr as $tk => $tv ) {
								$response_count = 0;
							//	$tv ['response'] = $this->_helper->topicresponsearray->getVoteCount ( $tv ['topic_id'], $TopicModelResponse );
								if(is_array($tv['responses'])){
									for($i = 1; $i<=5 ; $i++) {
									$response_count  +=  $tv['responses']["rvot_$i"];
									}
								}
								$tv['response_count'] = $response_count;
								$topicArr [] = $tv;
							}
							
				
				}
				
				
				$this->view->ranking = $topicArr;
				
			//	$this->p($arr); exit;
			
			}
		
		}
	
	}
	
	public function deletetopicAction() {

	$session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	
	     if( $session_check == 0){
	     	
			$this->view->msg = LOGIN_REQUIRED;
			$this->view->success_flag = 0;
			$this->view->expired_flag = 1;
			
		} else {
		
		$post = $this->getRequest ()->getPost ();
		
			//$x = print_r ( $post, true );			$this->_helper->errorlog ( $x );

		$user_id = ( int ) $this->_session->iphone_session_data ['user_id'];
		
		$topic_id = $post ['topic_id'];
		
		// $topic_id = 26 ;
		

		$TopicModel = new Default_Model_Topic ();
		
		$TopicModelCategory = new Default_Model_TopicCategories ();
		
		$TopicModelReponse = new Default_Model_TopicResponse ();
		
		$TopicModelReport = new Default_Model_Report ();
		

			
			if (! empty ( $topic_id )) {
				
				$deleteTopicResponses = $TopicModelReponse->deleteTopicResponse ( $topic_id );
				
				if ($deleteTopicResponses) {
					
					$deleteTopicCategory = $TopicModelCategory->deleteTopicCategory ( $topic_id );
					
					if ($deleteTopicCategory) {
						$deleteTopicreports = $TopicModelReport->deleteRevokeTopics ( $topic_id );
						$deleteTopic = $TopicModel->deleteTopic ( $topic_id );
						
						if ($deleteTopic) {
							
							$this->view->msg = TOPIC_DELETED;
							$this->view->success_flag = 1;
						
						} else {
							
							$this->view->msg = TOPIC_NOT_DELETED;
							$this->view->success_flag = 0;
						
						}
					
					}
				
				}
			
			} else {
				
				$this->view->msg = 'Invalid/Missing Topic Id.';
				$this->view->success_flag = 0;
			}
		
		}
	
	}
	
	public function logoutAction() {
		
		$this->_session->session_data = null;
		$this->_session->search_param = null;
		$this->_session->iphone_session_data = null;
		
		if (empty ( $this->_session->iphone_session_data )) {
			
			$this->view->msg = "You Have logged out Succesfully";
			$this->view->success_flag = 1;
		
		} else {
			
			$this->view->msg = "Error unable to log out";
			$this->view->success_flag = 0;
		
		}
	
	}

	public function sessioncheckAction(){
	    if( !empty($this->_session->iphone_session_data) && $this->_session->iphone_session_data['login_flag'] === true && (int)$this->_session->iphone_session_data['user_id'] != 0 && $this->_session->iphone_session_data['user_id'] != null ){
    	    $this->view->success_flag = 1;
	    }else{
    	    $this->view->success_flag = 0;
	    }
	}
	
	public function iphone_session_check($data){
	    if( !empty($data) && $data['login_flag'] === true && (int)$data['user_id'] != 0 && $data['user_id'] != null ){
    	    return 1;
	    }else{
    	    return 0;
	    }
	}
	
	public function appsettingsAction(){
	    $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	    if( $session_check == 0){
	        $this->view->msg = LOGIN_REQUIRED;
        	$this->view->success_flag = 0;
        	$this->view->expired_flag = 1;
        } else {
            $post = $this->getRequest ()->getPost ();
            
            //$x = print_r($post, true); $this->_helper->errorlog ( $x );
            
            $userDetailModel = new Default_Model_UserDetails();
            $user_id = $this->_session->iphone_session_data['user_id'];
            
            if( !empty($post) ){
                if( (int)$post['category_flag'] == 1){
                    $data['notification_status'] = 'y';
                }else{
                    $data['notification_status'] = 'n';
                }
                
                if( (int)$post['report_flag'] == 1){
                    $data['report_violation_flag'] = 'y';
                }else{
                    $data['report_violation_flag'] = 'n';
                }
            
                $data['id'] = $user_id;
    
                $result = $userDetailModel->updateNotifications($data);
                
                if( $data['notification_status'] == 'y' ){
                    if( !empty($post['categories']) ){
                        $data['categories_of_interest'] = serialize( explode(",", $post['categories']) );
                    }else{
                        $data['categories_of_interest'] = null;
                    }
                    $result = $userDetailModel->updateCategoriesOfInterest($data);
                }
                
                //$x = print_r($data, true); $this->_helper->errorlog ( $x );
                
                $this->view->post = 1;
                $this->view->success_flag = 1;
                $this->view->msg = 'Notification status updated successfully';
            }else{
                $this->view->post = 0;
                //$user_id = 54;
                $userInfo = $userDetailModel->getUserInfo($user_id);
                
                $this->view->success_flag = 1;
                $this->view->report_flag = ($userInfo['report_violation_flag'] == 'y')?'1':'0';
                $this->view->category_flag = ($userInfo['notification_status'] == 'y')?'1':'0';
                $this->view->max_count = MAX_CATEGORY_COUNT;
            }
        }
	}
	
	public function apnscategorylistAction(){
	    $session_check = $this->iphone_session_check($this->_session->iphone_session_data);
	    if( $session_check == 0){
	        $this->view->msg = LOGIN_REQUIRED;
        	$this->view->success_flag = 0;
        	$this->view->expired_flag = 1;
        } else {
            $userDetailModel = new Default_Model_UserDetails();
            $categoryModel = new Default_Model_Categories();
            
            $user_id = $this->_session->iphone_session_data['user_id'];
            //$user_id = 57;
            $userInfo = $userDetailModel->getUserInfo($user_id);
            
            $categoryList = $categoryModel->listcategories('categories_name');
            
            $this->view->success_flag = 1;
            
            $catgArr = array();
            
            if( !empty($userInfo['categories_of_interest'])){
                $categoryOfInterest = unserialize($userInfo['categories_of_interest']);
            }else{
                $categoryOfInterest = array();
            }
            
            foreach($categoryList as $ck => $cv){
                if ( in_array($cv['categories_id'], $categoryOfInterest) == true){
                    $selected_flag = "1";
                }else{
                    $selected_flag = "0";
                }
                $catgArr[] = array('id' => $cv['categories_id'], 'name' => $cv['categories_name'], 'selected_flag' => $selected_flag);
            }
            
            $this->view->categArr = $catgArr;
            
        }
	}
	
	
}