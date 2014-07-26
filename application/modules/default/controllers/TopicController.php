<?php

class TopicController extends My_UserController {
	
	/**
	 * init() function, call init() function of parent class
	 *
	 */
	public function init() {
		parent::init ();
	}
	
	public function testapnsAction(){
	    
        $message = new Zend_Mobile_Push_Message_Apns();
        $message->setAlert('Zend Mobile Push Example');
        $message->setBadge(1);
        $message->setSound('beep.wav');
        $message->setId(time());
        $message->setToken('62c78a984a35b3f01bcdb5a874bc1015cab83951f6f4fa43b33d8cd987772131');
         
        $apns = new Zend_Mobile_Push_Apns();
        $apns->setCertificate(APPLICATION_PATH . DS . "apns" . DS . 'ck.pem');
        // if you have a passphrase on your certificate:
        // $apns->setCertificatePassphrase('foobar');
         
        try {
            $apns->connect(Zend_Mobile_Push_Apns::SERVER_SANDBOX_URI);
        } catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
            // you can either attempt to reconnect here or try again later
            exit(1);
        } catch (Zend_Mobile_Push_Exception $e) {
            echo 'APNS Connection Error:' . $e->getMessage();
            exit(1);
        }
         
        try {
            $apns->send($message);
        } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
            // you would likely want to remove the token from being sent to again
            echo $e->getMessage();
        } catch (Zend_Mobile_Push_Exception $e) {
            // all other exceptions only require action to be sent
            echo $e->getMessage();
        }
        
        $apns->close();
        
        $apns = new Zend_Mobile_Push_Apns();
        $apns->setCertificate(APPLICATION_PATH . DS . "apns" . DS . 'ck.pem');
    	try {
    	    $apns->connect(Zend_Mobile_Push_Apns::SERVER_FEEDBACK_SANDBOX_URI);
    	} catch (Zend_Mobile_Push_Exception_ServerUnavailable $e) {
    	    // you can either attempt to reconnect here or try again later
    	    exit(1);
    	} catch (Zend_Mobile_Push_Exception $e) {
    	    echo 'APNS Connection Error:' . $e->getMessage();
    	    exit(1);
    	}
     
    	$tokens = $apns->feedback();
    	while(list($token, $time) = each($tokens)) {
    	    echo $time . "\t" . $token . PHP_EOL;
    	}
        
        $apns->close();

        exit;
	}
	
	/**
	 * Default Action
	 *
	 */
	public function indexAction() {
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		$post = $this->getRequest ()->getPost ();
	//	echo "<pre>"; print_r($_POST);
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		if (! empty ( $user_id )) {
			$UserDeatilsModel = new Default_Model_UserDetails ();
			$user_info = $UserDeatilsModel->getUserInfo ( $user_id );
			$this->view->userInfo = $user_info;
			$this->view->user_id = $user_id;
		
		// $this->_session->search_param['country1'] = $user_info['country_id'];
		}
		
		$this->view->pageTitle = ': View Topics';
		
		$response = $this->getResponse ();
		
		if (! empty ( $post )) {
			$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
			
			if (empty ( $result )) {
				$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
				$this->_redirector->gotoUrl ( 'index.html' );
			}
			
			if (! empty ( $user_id )) {
				if (! empty ( $post ['topic_id'] )) {
					$TopicModelReponse = new Default_Model_TopicResponse ();
					
					// inserts user response via javascript submit
					$post ['user_id'] = $user_id;
					
					$isResponseAvailable = $TopicModelReponse->getTopicResponse( $user_id , $post ['topic_id'] );
				
					if(!$isResponseAvailable){
						$insertid = $TopicModelReponse->inserttopicResponse ( $post );
					}
				//	if ($insertid) {
						$Topic_votes = $TopicModelReponse->getVotesCount ( $post ['topic_id'] );
						
						for($t = 1; $t <= 5; $t ++) {
							$TVotes [$t] = array ('response' => 0, 'percent' => 0, 'votes' => 0 );
						}
						
						foreach ( $Topic_votes as $tk => $tv ) {
							$TVotes [$tv ['response']] = array ('response' => $tv ['response'], 'percent' => $tv ['percent'], 'votes' => $tv ['votes'] );
						}
						$this->_session->response ['score'] = $post ['score'];
						$this->_session->response ['Topic_votes'] = $TVotes;
				//	}
					$this->_redirector->gotoUrl ( 'view-topics.html' );
				}
			}
		}
		
		$userDeatislModel = new Default_Model_UserDetails ();
		$result1 = $userDeatislModel->getUserInfo ( $user_id );
		
		$country = $result1 ['country_id'];
		
		if (! empty ( $this->_session->response )) {
			$this->view->Topic_votes = $this->_session->response ['Topic_votes'];
			$this->view->score = $this->_session->response ['score'];
			$this->_session->response = null;
		}
		
		$TopicModel = new Default_Model_Topic ();
		
		if (! empty ( $this->_session->search_param ['cat_id'] )) {
			$category_ids = implode ( ",", $this->_session->search_param ['cat_id'] );
		} else {
			$category_ids = null;
		}
		
		if (! empty ( $this->_session->search_param ['country1'] )) {
			
			$tcountry = ( int ) $this->_session->search_param ['country1'];
		
		} else {
			
			$tcountry = $country;
		
		}
		
		$topicList = $TopicModel->getFrontendtopiclist ( $user_id, $tcountry, $category_ids );
				
	//	$Topic_Categories_Model = new Default_Model_TopicCategories();
	//	$category_name = $Topic_Categories_Model->gettopicCategory( $topicList[0]['topic_id']);
	
		if( !empty($topicList)){
		    $this->view->topicList = $topicList;
		}else{
		    $this->view->topicList = null;
		    $this->view->topic_not_found = TOPIC_NOT_FOUND;
		}

//		$this->view->category_name = $category_name['categories_name'];
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
	}
	
	public function addAction() {
		
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		$post = $this->getRequest ()->getPost ();
		
		$this->view->pageTitle = ': Create Topics';
		
		$response = $this->getResponse ();
		
		$CategoryModel = new Default_Model_Categories ();
		
		$TopicCategoriesModel = new Default_Model_TopicCategories ();
		
		$catInfo = $CategoryModel->listcategories ( 'categories_name' );
		
		$this->view->catInfo = $catInfo;
		
		$TopicModel = new Default_Model_Topic ();
		
		if (! empty ( $post )) {
			
			$is_error = false;
			$var_msg = 'Error(s) encountered.' . "<br/><br/>";
			
			if (empty ( $post ['name'] )) {
				$is_error = true;
				$var_msg .= EMPTY_TOPIC_NAME . "<br/>";
			}
			
			if (empty ( $post ['cid'] )) {
				$is_error = true;
				$var_msg .= EMPTY_CATEGORY_NAME . "<br/>";
			}
			
			if ($is_error == false) {
				
				$post ['user_id'] = $user_id;
				$post ['description'] = '';
				$post['country_id'] = ( int ) $this->_session->session_data ['country_id'];
				$inserttopic = $TopicModel->inserttopic ( $post );
				
				if ($inserttopic) {
					
					$addTopiccategory = $TopicCategoriesModel->insertTopicCategory ( $inserttopic, $post ['cid'] );

					// * *  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
                    // start --- facebook topic post on wall ---
                    
//                    $config = Zend_Registry::get('config');
//                    $facebook = new My_Facebook(array('appId' => $config->fb->app_id, 'secret' => $config->fb->app_secret, 'cookie' => true));
//                    $session = $facebook->getLoginStatusUrl();
//
//                    if (! empty($session)){
//                        try{
//                            // Proceed knowing you have a logged in user who's authenticated.
//                            $user_profile = $facebook->api('/me');
//                            $facebook->api("/me/feed", "post", array(
//
//                                    message => "Hey look, posted a topic on YouSaidIt, titled - " . stripslashes($post['name']). " " ,
//                                    picture => "http://www.compubits-solutions.in/yousaidit/public/images/face-5.png",
//                                    link => "http://www.compubits-solutions.in/yousaidit/",
//                                    name => "YouSaitIt",
//                                    caption => ""    )    );
//
//                        } catch(My_FacebookApiException $e){
//                            $this->_helper->errorlog('UserId : ' . $this->_session->session_data['user_id'] . ' -- Error encountered while posting on wall : ' . $e->getMessage() );
//                        }
//                    }
                    
                    // end --- facebook topic post on wall ---
                    // * *  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
                    
                    
                    // ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^ ^^
                    // start code for sending push notifications //
                    
    				$result = $this->_helper->sendnotification($inserttopic, $post ['cid']);
            
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

					$this->_flashMessenger->addMessage ( TOPIC_CREATED );
					$this->_redirector->gotoUrl ( 'create-topic.html' );
				}
			} else {
				$this->view->errorMsg = $var_msg;
				$this->view->post = $post;
			}
		}
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
	}
	
	// for showing all response  own/other
	public function responseAction() {
		
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}elseif($this->getRequest()->isXmlHttpRequest()) {
     			   if ($this->getRequest()->isPost()) {
     				   	$tab = $this->_getParam ( 'tab' );
						$this->_session->session_data ['tab'] = $tab;
			            exit;
        			}
    	}
		
		$post = $this->getRequest ()->getPost ();
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		$this->view->pageTitle = ': Responses';
		
		$response = $this->getResponse ();
		
		$TopicModel = new Default_Model_Topic ();
		
		$TopicModelReponse = new Default_Model_TopicResponse ();
		
		////////////////////////////////////////////////////////////////////////////
		//  code to show user topics responses
		//////////////////////////////////////////////////////////////////

		//	$userCreated = $TopicModel->getUserTopics ( $user_id );

		// to show only those user reponse with any response and leave out which are not responded
		$userCreated = $TopicModelReponse->getUserResponse ( $user_id );
	
		$pg1 = $this->_getParam ( 'page1' );
		$pg2 = $this->_getParam ( 'page2' );
		
		if(empty($pg1) && empty($pg2)){
			$this->_session->session_data['page1'] = 1;
			$this->_session->session_data['page2'] = 1;
		}
		if(!empty($pg1)){
			$this->_session->session_data['page1'] =  $pg1;
		}
	
		if (!empty($pg2)){
			$this->_session->session_data['page2'] =  $pg2;
		}
		$pg1 = $this->_session->session_data['page1'];
		$pg2 = $this->_session->session_data['page2'];
		
		//$tab = $this->_getParam ( 'tab' );
	//	$this->view->tab = ! empty ( $tab ) ? $tab : 1; echo $this->_session->session_data['tab'];

		$this->view->tab = ! empty ($this->_session->session_data['tab'] ) ? $this->_session->session_data['tab'] : 0;
	
		$arrTopic = array ();
		if (! empty ( $userCreated )) {
			$arrTopic = $this->_helper->topicresponsearray ( $userCreated, $TopicModelReponse );
		}
		if( !empty($arrTopic))
		{
			$this->view->paginator = Zend_Paginator::factory ( $arrTopic );
			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
			$this->view->paginator->setCurrentPageNumber ( $pg1 );
		}else{
			$this->view->arrTopic = $arrTopic;
		}
		
		////////////////////////////////////////////////////////////////////////////
		//  code to show user clicked response on other users except his
		//////////////////////////////////////////////////////////////////

		$userVoted = $TopicModelReponse->getUserOtherResponse ( $user_id );
		
		$arrTopic2 = array ();
		if (! empty ( $userVoted )) {
			$arrTopic2 = $this->_helper->topicresponsearray ( $userVoted, $TopicModelReponse );
		}
		if( !empty($arrTopic2))
		{
			$this->view->paginator2 = Zend_Paginator::factory ( $arrTopic2 );
			$this->view->paginator2->setItemCountPerPage ( REC_LIMIT );
			$this->view->paginator2->setPageRange ( PAGE_LINK_COUNT );
			$this->view->paginator2->setCurrentPageNumber ( $pg2 );
		}else{
			$this->view->arrTopic2 = $arrTopic;
		}
	//	$this->view->arrTopic2 = $arrTopic2;
		
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
	
	}
	
	public function reportAction() {
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		//TOPIC_FAULT_LIMIT
		$post = $this->getRequest ()->getPost ();
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		$topic_id = ( int ) $this->_getParam ( 'topic_id' );
		
		$post ['user_id'] = $user_id;
		
		$post ['topic_id'] = $topic_id;
		
		$TopicModel = new Default_Model_Topic ();
		$ReportModel = new Default_Model_Report ();
		
		$insertReport = $ReportModel->insertReport ( $post );
		
		if ($insertReport) {
			$insertReport = $ReportModel->reportCount ( $topic_id );
			$rcount = $insertReport ['report'];
			if ($rcount >= TOPIC_FAULT_LIMIT) {
				$statusinactive = $TopicModel->Updatetopicstatus ( $topic_id );
			}
		}
		
		if ($insertReport) {

		    // BEGIN push notification logic //

            $result = $this->_helper->sendreportviolationnotification($topic_id);
    
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
		    
			$this->_flashMessenger->addMessage ( TOPIC_REPORT );
			$this->_redirector->gotoUrl ( 'view-topics.html' );
		}
	}
	
	// for showing indivual topic response deatisls
	public function topicresponseAction() {
		
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
		
		$post = $this->getRequest ()->getPost ();
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		$topic_id = ( int ) $this->_getParam ( 'id' );
		
		$TopicModel = new Default_Model_Topic ();
		
		$TopicModelReponse = new Default_Model_TopicResponse ();
		
		$topic = $TopicModel->TopicInfo ( $topic_id );
		
		$this->view->topic_title = $topic ['name'];
		
		$Topic_userVoted = $TopicModelReponse->getVotesCount ( $topic_id );
		
		if (! empty ( $Topic_userVoted )) {
			$this->view->arrTopic = $Topic_userVoted;
		} else {
			$this->view->arrTopic = null;
			$this->view->errorMsg = 'No responses found, chart cannot be displayed';
		}
		$data = '';
		$flag = false;
		//$this->p($Topic_userVoted);exit;
//		if (! empty ( $Topic_userVoted )) {
//			foreach ( $Topic_userVoted as $arr ) {
//				if ($arr ['response'] == 1) {
//					$data .= '["' . $arr ['percent'] . '%", ' . $arr ['percent'] . ' ],';
//				} elseif ($arr ['response'] == 2) {
//					$data .= '["' . $arr ['percent'] . '%", ' . $arr ['percent'] . ' ],';
//				} elseif ($arr ['response'] == 3) {
//					$data .= '["' . $arr ['percent'] . '%", ' . $arr ['percent'] . ' ],';
//				} elseif ($arr ['response'] == 4) {
//					$data .= '["' . $arr ['percent'] . '%", ' . $arr ['percent'] . ' ],';
//				} elseif ($arr ['response'] == 5) {
//					$data .= '["' . $arr ['percent'] . '%", ' . $arr ['percent'] . ' ],';
//				}
//			}
//		}
	$total_votes = 0;
			if (! empty ( $Topic_userVoted )) {
			foreach ( $Topic_userVoted as $arr ) {
				$total_votes += $arr['votes'];
				if ($arr ['response'] == 1) {
					
					
				$data .= '{  name: \''. $arr ['percent'].'%\', response: 1 ,y:'. $arr ['percent'].',color: \'#99cc33\' , dataLabels: { color: \'#99cc33\' } },' ;
				
			} elseif ($arr ['response'] == 2) {
			  
					$data .= '{  name: \''. $arr ['percent'].'%\', response: 2 ,y:'. $arr ['percent'].',color: \'#0099cc\'  , dataLabels: { color: \'#0099cc\' } },' ;
			}
			 elseif ($arr ['response'] == 3) {
			
				$data .= '{  name: \''. $arr ['percent'].'%\', response: 3 ,y:'. $arr ['percent'].',color: \'#ff3333\'  , dataLabels: { color: \'#ff3333\' } },' ;
				
			}
			 elseif ($arr ['response'] == 4) {
			
				$data .= '{  name: \''. $arr ['percent'].'%\', response: 4 ,y:'. $arr ['percent'].',color: \'#ffdc00\'  , dataLabels: { color: \'#ffdc00\' } },' ;
			
			} elseif ($arr ['response'] == 5) {
				
					$data .= '{  name: \''. $arr ['percent'].'%\', response: 5 ,y:'. $arr ['percent'].',color: \'#ff9933\'  , dataLabels: { color: \'#ff9933\' } },' ;
		}
			}
			
			}
		$this->view->total_votes = $total_votes;
		$data = substr ( $data, 0, strlen ( $data ) - 1 );
		
	//	$this->p($data);
		
		
		if (! empty ( $data )) {
			$this->view->data = $data;
		} else {
			$this->view->data = null;
			$this->view->errorMsg = 'No responses found, chart cannot be displayed';
		}
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
	
	}
	
	public function skiptopicAction() {
		
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
		
		$post = $this->getRequest ()->getPost ();
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		$topic_id = ( int ) $this->_getParam ( 'topic_id' );
		
		$post ['user_id'] = $user_id;
		
		$post ['topic_id'] = $topic_id;
		
		$TopicModel = new Default_Model_Topic ();
		
		$topicresponseModel = new Default_Model_TopicResponse ();
		
		$skiptopic = $topicresponseModel->skipCurrentTopic ( $post );
		
		if ($skiptopic) {
			$this->_flashMessenger->addMessage ( TOPIC_SKIP );
			$this->_redirector->gotoUrl ( 'view-topics.html' );
		}
	
	}
	
	public function deletetopicAction() {
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		$post = $this->getRequest ()->getPost ();
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		$topic_id = ( int ) $this->_getParam ( 'id' );
		
		$TopicModel = new Default_Model_Topic ();
		
		$TopicModelCategory = new Default_Model_TopicCategories ();
		
		$TopicModelReponse = new Default_Model_TopicResponse ();
		
		$TopicModelReport = new Default_Model_Report ();
		
		$page_value = $_SERVER['HTTP_REFERER'];
	    $last = substr(strrchr($page_value, "/"), 1 );

	    if (! empty ( $topic_id )) {
			
			//delete topic reponse
			$deleteTopicResponses = $TopicModelReponse->deleteTopicResponse ( $topic_id );
			
			if ($deleteTopicResponses) {
				// delete topic category asscaitions
				$deleteTopicCategory = $TopicModelCategory->deleteTopicCategory ( $topic_id );
				
				if ($deleteTopicCategory) {
					
					// delete topic reports
					$deleteTopicreports = $TopicModelReport->deleteRevokeTopics ( $topic_id );
					
					// delete topic
					$deleteTopic = $TopicModel->deleteTopic ( $topic_id );
					
					if ($deleteTopic) {
						$this->_flashMessenger->addMessage ( TOPIC_DELETED );
						$this->_redirector->gotoUrl ( 'topic/response/id/1/tab/1/page1/'.$last );
					
					} else {
						
						$this->_flashMessenger->addMessage ( TOPIC_DELETED );
						$this->_redirector->gotoUrl ( 'topic/response/id/1/tab/1/page1/'.$last );
					
					}
				
				}
			
			}
		
		}
	}
	
	public function filtertopicAction() {
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		$post = $this->getRequest ()->getPost ();
		
		$countryModel = new Default_Model_Countries ();
		$countryList = $countryModel->listCountry ();
		$this->view->countryList = $countryList;
		$this->view->user_country_id = $this->_session->session_data ['country_id'];
		
		$CategoryModel = new Default_Model_Categories ();
		$catInfo = $CategoryModel->listcategories ( 'categories_name', 'status', 'Active', $strict_filter = false );
		$this->view->catInfo = $catInfo;
		
		if (! empty ( $post )) {
			$country1 = $post ['country1'];
			
			if (! empty ( $post ['cid'] )) {
				$search_param = array ("country1" => $country1, "cat_id" => $post ['cid'] );
			} else {
				$search_param = array ("country1" => $country1, "cat_id" => '' );
			}
			
			$this->_session->search_param = $search_param;
			
			$tccategory = $this->_session->search_param ['cat_id'];
			
			$this->view->tccategory = $tccategory;
			
			$tcountry = ( int ) $this->_session->search_param ['country1'];
			
			$this->view->tcountry = $tcountry;
			
			$this->_redirector->gotoUrl ( 'view-topics.html' );
		
		} else {
			$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
			
			$tccategory = ! empty ( $this->_session->search_param ['cat_id'] ) ? $this->_session->search_param ['cat_id'] : null;
			$tcountry = ( int ) $this->_session->search_param ['country1'];
			
			$this->view->tccategory = $tccategory;
			$this->view->country_id = $tcountry;
			
			if (empty ( $this->_session->search_param ['country1'] )){
				$user_id = ( int ) $this->_session->session_data ['user_id'];
				$uDetailslModel = new Default_Model_UserDetails ();
				$userIDeatils = $uDetailslModel->getUserInfo ( $user_id );
				$tcountry = $userIDeatils ['country_id'];
				$this->view->country_id = $tcountry;
			}
		}
	}
	
	public function topicredirectAction() {
		
		$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
		
		if (empty ( $result )) {
			$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
			$this->_redirector->gotoUrl ( 'index.html' );
		}
		$post = $this->getRequest ()->getPost ();
		$tid = ( int ) $this->_getParam ( 'topic_id' );
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		$TopicResponseModel = new Default_Model_TopicResponse ();
		$topicList = $TopicResponseModel->getTopicRedirect ( $tid, $user_id );
		if (! empty ( $topicList )) {
			//	$this->_flashMessenger->addMessage('You have already responded on this Topic');
			//	$url = "$tid/topic-response.html";
			$this->_redirector->gotoUrl ( "$tid/topic-response.html" );
		
		// $this->_redirector->gotoUrl('edit-profile.html');
		}
		
		$post = $this->getRequest ()->getPost ();
		
		$user_id = ( int ) $this->_session->session_data ['user_id'];
		
		if (! empty ( $user_id )) {
			$UserDeatilsModel = new Default_Model_UserDetails ();
			$user_info = $UserDeatilsModel->getUserInfo ( $user_id );
			$this->view->userInfo = $user_info;
			$this->view->user_id = $user_id;
		
		// $this->_session->search_param['country1'] = $user_info['country_id'];
		}
		$this->view->pageTitle = ': View Topics';
		
		$response = $this->getResponse ();
		
		if (! empty ( $post )) {
			$result = $this->_helper->sessioncheck ( $this->_session->session_data, $this->_session->session_data ['login_flag'] );
			
			if (empty ( $result )) {
				$this->_flashMessenger->addMessage ( LOGIN_REQUIRED );
				$this->_redirector->gotoUrl ( 'index.html' );
			}
			
			if (! empty ( $user_id )) {
				if (! empty ( $post ['topic_id'] )) {
					$TopicModelReponse = new Default_Model_TopicResponse ();
					
					// inserts user response via javascript submit
					$post ['user_id'] = $user_id;
					
					$insertid = $TopicModelReponse->inserttopicResponse ( $post );
					
				if ($insertid) {
						$Topic_votes = $TopicModelReponse->getVotesCount ( $post ['topic_id'] );
						
						for($t = 1; $t <= 5; $t ++) {
							$TVotes [$t] = array ('response' => 0, 'percent' => 0, 'votes' => 0 );
						}
						
						foreach ( $Topic_votes as $tk => $tv ) {
							$TVotes [$tv ['response']] = array ('response' => $tv ['response'], 'percent' => $tv ['percent'], 'votes' => $tv ['votes'] );
						}
						$this->_session->response ['score'] = $post ['score'];
						$this->_session->response ['Topic_votes'] = $TVotes;
					}
						$this->_redirector->gotoUrl ( 'view-topics.html' );
				}
			}
		}
		$TopicModel = new Default_Model_Topic ();
		$topicList = $TopicModel->TopicInfo ( $tid );
		$this->view->topicList = $topicList;
		$this->view->leftmenu = $this->view->render ( 'leftmenu.phtml' );
	
		//	$params = array( 'topic_id' => $tid);
	//	$this->_forward('index', 'topic', $this->_modulename, $params = null);
	}

}
