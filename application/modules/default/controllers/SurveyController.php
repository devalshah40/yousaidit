<?php

class SurveyController extends My_UserController {

    /**
     * init() function, call init() function of parent class
     *
     */
    public function init() {
        parent::init();
    }

    public function sendsurveynotificationAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $sid = (int) $this->_getParam('sid');
        $result = $this->_helper->sendnotification($sid);
        
        if ($result == 1){
            $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
        } else if ($result == 2){
            $this->_flashMessenger->addMessage(SURVEY_NOTIFICATION_ALREADY_SENT);
        } else if ($result == 3){
            $this->_flashMessenger->addMessage(SURVEY_NOTIFICATION_SENT);
        } else if ($result == 4){
            $this->_flashMessenger->addMessage(SURVEY_NOTIFICATION_ALREADY_SENT);
        } else if ($result == 5){
            $this->_flashMessenger->addMessage(NO_MEMBER_FOUND_MATCHING_SURVEY_CRITERIA);
        } else{
            $this->_flashMessenger->addMessage(SURVEY_NOTIFICATION_NOT_SENT);
        }
        
        $this->_redirector->gotoUrl("survey-listing.html");
    }

    public function indexAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Survey Listing ';
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'Survey Listing';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        $surveyModel = new Default_Model_Survey();
        
        $user_type = $this->_session->session_data['user_type'];
        
        if ($user_type == 'C' || $user_type == 'CC'){
            
            if ($user_type == 'C'){
                $surveyListing = $surveyModel->listSurvey('add_date DESC', 'client_id', $this->_session->session_data['client_id'] . "^C", true);
            }
            if ($user_type == 'CC'){
                $surveyListing = $surveyModel->listSurvey('add_date DESC', 'client_id', $this->_session->session_data['client_contact_id'] . "^CC", true);
            }
            
            if ($surveyListing){
                $surveyArr = array();
                $surveyQueModel = new Default_Model_SurveyQuestion();
                $rewardModel = new Default_Model_RewardTypes();
                foreach($surveyListing as $k => $v){
                    $reward = $rewardModel->getRewardInfo($v['reward_type_id']);
                    if ($reward){
                        $reward_info = $reward['name'];
                    } else{
                        $reward_info = null;
                    }
                    $questions = $surveyQueModel->listSurveyQuestions($v['survey_id']);
                    if ($questions){
                        $arr = array('response_flag' => true);
                    } else{
                        $arr = array('response_flag' => false);
                    }
                    array_push($surveyArr, array('sinfo' => $v, 'resinfo' => $arr, 'reward' => $reward_info));
                }
                $this->view->surveylisting = $surveyArr;
            } else{
                $this->view->surveylisting = null;
            }
            if ($this->_session->iPhone == true){
                return $this->render('i-index');
            }
        } else{
            $this->_redirector->gotoUrl('user-home.html');
        }
    }

    public function surveyresultsAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Survey Results ';
        
        $user_type = $this->_session->session_data['user_type'];
        
        if ($user_type == 'M'){
            $surveyModel = new Default_Model_Survey();
            $surveyList = $surveyModel->surveyResult((int) $this->_session->session_data['member_id']);
            
            $this->view->survey = $surveyList;
            
            if ($this->_session->iPhone == true){
                $this->view->header_title = 'Survey Results';
                $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
                return $this->render('isurveyresults');
            }
        } else{
            $this->_redirector->gotoUrl('user-home.html');
        }
    }

    public function membersurveyAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Member Survey Listing ';
        
        $user_type = $this->_session->session_data['user_type'];
        
        if ($user_type == 'M'){
            $surveyModel = new Default_Model_Survey();
            
            $address1 = $this->_session->session_data['address1'];
            $city = $this->_session->session_data['city'];
            $state = $this->_session->session_data['state'];
            $post_code = $this->_session->session_data['post_code'];
            $country = $this->_session->session_data['country'];
            $gender = $this->_session->session_data['gender'];
            $dob = $this->_session->session_data['dob'];
            $level_id = $this->_session->session_data['level_id'];
            $age = $this->_session->session_data['age'];
            
            //$this->p($this->_session->session_data); exit;
            

            if (empty($address1) || empty($city) || empty($state) || empty($post_code) || empty($country) || empty($gender) || empty($dob)){
                $this->_flashMessenger->addMessage(EMPTY_PROFILE_FIELDS);
                $this->_redirector->gotoUrl('edit-profile.html');
            }
            
            $answered_flag = (int) $this->_getParam('answered');
            $this->view->answered_flag = $answered_flag;
            
            $surveyList = $surveyModel->listMemberSurvey((int) $this->_session->session_data['member_id'], $answered_flag);
            
            $surveyArr = $this->_helper->membersurvey($surveyList, $answered_flag, $this->_session->session_data);
            
            if (! empty($surveyArr)){
                $this->view->survey = $surveyArr;
            } else{
                $this->view->survey = null;
            }
            
            if ($this->_session->iPhone == true){
                if ($answered_flag == 1){
                    $this->view->header_title = 'Completed Survey';
                } else{
                    $this->view->header_title = 'Available Survey';
                }
                $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
                return $this->render('imembersurvey');
            }
        } else{
            $this->_redirector->gotoUrl('user-home.html');
        }
    }

    public function membergroupsurveyAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Focus Group Survey Listing ';
        
        $user_type = $this->_session->session_data['user_type'];
        
        if ($user_type == 'M'){
            $surveyModel = new Default_Model_FocusGroupSurvey();
            $surveyList = $surveyModel->listMemberSurvey();
            
            if (! empty($surveyList)){
                $p = new My_Plugin();
                $surveyArr = array();
                foreach($surveyList as $k => $v){
                    $v['reward_points'] = $p->getRewardPoints($v['value']);
                    array_push($surveyArr, $v);
                }
                $this->view->survey = $surveyArr;
            } else{
                $this->view->survey = null;
            }
        } else{
            $this->_redirector->gotoUrl('user-home.html');
        }
    }

    public function groupsurveylistAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Focus Group Survey Listing ';
        
        $surveyModel = new Default_Model_FocusGroupSurvey();
        
        $user_type = $this->_session->session_data['user_type'];
        
        if ($user_type == 'C'){
            $surveyListing = $surveyModel->listSurvey('add_date DESC', 'client_id', $this->_session->session_data['client_id'], true);
            
            if ($surveyListing){
                
                $this->view->surveylisting = $surveyListing;
            } else{
                $this->view->surveylisting = null;
            }
        } else{
            $this->_redirector->gotoUrl('user-home.html');
        }
    }

    public function createfocusgroupAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Create Focus Group Survey';
        
        $post = $this->getRequest()->getPost();
        
        if (! empty($post) && ! empty($post['cmdCancel'])){
            $this->_redirector->gotoUrl('focus-group-survey-listing.html');
        }
        
        if (! empty($post) && ! empty($post['cmdSubmit'])){
            $surveyModel = new Default_Model_FocusGroupSurvey();
            
            $post['status'] = 'Active';
            $post['client_id'] = $this->_session->session_data['client_id'];
            
            $post['start_date'] = date('Y-m-d', strtotime($post['start_date']));
            
            $fg_survey_id = $surveyModel->insertSurvey($post);
            
            if ($fg_survey_id){
                $this->_flashMessenger->addMessage(SURVEY_CREATED);
            } else{
                $this->_flashMessenger->addMessage(SURVEY_NOT_CREATED);
            }
            
            $this->_redirector->gotoUrl("focus-group-survey-listing.html");
        }
    }

    public function groupsurveyinfoAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        $surveyModel = new Default_Model_FocusGroupSurvey();
        if (empty($post)){
            $sid = (int) $this->_getParam('sid');
            
            if (empty($sid)){
                $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
                $this->_redirector->gotoUrl("focus-group-survey-listing.html");
            } else{
                $surveyInfo = $surveyModel->surveyInfo($sid);
                
                if ($surveyInfo){
                    $this->view->pageTitle = ': Survey Information - ' . $surveyInfo['title'];
                    
                    $surveyInfo['start_date'] = date('j M Y', strtotime($surveyInfo['start_date']));
                    
                    $this->view->surveyinfo = $surveyInfo;
                } else{
                    $this->_flashMessenger->addMessage(INVALID_SURVEY_ID_CANNOT_SHOW_SURVEY);
                    $this->_redirector->gotoUrl("focus-group-survey-listing.html");
                }
            }
        } else{
            if (! empty($post['cmdCancel'])){
                $this->_redirector->gotoUrl("focus-group-survey-listing.html");
            }
            
            $post['start_date'] = date('Y-m-d', strtotime($post['start_date']));
            
            $result = $surveyModel->updateSurvey($post, true);
            
            if ($result){
                $this->_flashMessenger->addMessage(SURVEY_DETAILS_UPDATED);
            } else{
                $this->_flashMessenger->addMessage(SURVEY_DETAILS_NOT_UPDATED);
            }
            $this->_redirector->gotoUrl('focus-group-survey-listing.html');
        }
    }

    public function createAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ': Create Survey';
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'Create Survey';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        $rewardType = new Default_Model_RewardTypes();
        $rTypes = $rewardType->listRewardTypes('Active');
        $this->view->rTypes = $rTypes;
        
        $post = $this->getRequest()->getPost();
        
        $user_type = $this->_session->session_data['user_type'];
        
        $memberLevelModel = new Default_Model_MemberLevel();
        $this->view->memberLevel = $memberLevelModel->listMemberLevel();
        
        if (! empty($post) && ! empty($post['cmdSubmit'])){
            
            // Generate Array for Custom Survey Parameters  --- S T A R T //
            $udfArr = $this->_helper->Generateudf($post);
            // Generate Array for Custom Survey Parameters  --- E N D //
            

            $st_dt = true;
            $en_dt = true;
            if ($this->_session->iPhone == true){
                $dtArr = array('Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12);
                if (! empty($post['sdate']) && ! empty($post['smonth']) && ! empty($post['syear'])){
                    if (checkdate($dtArr[$post['smonth']], $post['sdate'], $post['syear']) == true){
                        $post['start_date'] = $post['syear'] . "-" . $dtArr[$post['smonth']] . "-" . $post['sdate'];
                    } else{
                        $st_dt = false;
                    }
                }
                if (! empty($post['edate']) && ! empty($post['emonth']) && ! empty($post['eyear'])){
                    if (checkdate($dtArr[$post['emonth']], $post['edate'], $post['eyear']) == true){
                        $post['end_date'] = $post['eyear'] . "-" . $dtArr[$post['emonth']] . "-" . $post['edate'];
                    } else{
                        $en_dt = false;
                    }
                }
                if (! empty($post['Hour']) && ! empty($post['Minute'])){
                    $post['end_time'] = $post['Hour'] . ":" . $post['Minute'] . " " . $post['AMPM'];
                }
            }
            
            $errorMsg = 'Error(s) Encountered while creating survey' . "<br><br>";
            $is_error = false;
            if (empty($post['title'])){
                $is_error = true;
                $errorMsg .= '"Title" cannot be blank.' . "<br>";
            }
            
            if ($st_dt == false || $en_dt == false){
                $is_error = true;
                $errorMsg .= 'Invalid date values entered for "Start Date" or "End Date"' . "<br>";
            } else{
                if (empty($post['start_date']) || empty($post['end_date'])){
                    $is_error = true;
                    $errorMsg .= 'Values required for "Start Date", "End Date"' . "<br>";
                } else{
                    $s_dt = date("Y-m-d", strtotime($post['start_date']));
                    $e_dt = date("Y-m-d", strtotime($post['end_date']));
                    
                    if ($s_dt < date("Y-m-d")){
                        $is_error = true;
                        $errorMsg .= '"Start Date" should be equal to or greater than todays date' . "<br>";
                    }
                    
                    if ($e_dt < $s_dt){
                        $is_error = true;
                        $errorMsg .= '"End Date" should be greater then "Start Date"' . "<br>";
                    }
                }
            }
            
            if (empty($post['end_time'])){
                $is_error = true;
                $errorMsg .= '"End Time" should have a valid value.' . "<br>";
            }
            
            //			if (! empty ( $post ['min_age'] ) && ! empty ( $post ['max_age'] )) {
            //				if($post ['min_age'] > $post ['max_age']){
            //				    $is_error = true;
            //				    $errorMsg .= '"Max age" should be greater than "Min age" ' . "<br>";
            //				}
            //			}
            

            if ((int) $post['reward_type_id'] == 1){
                if (empty($post['reward_points'])){
                    $is_error = true;
                    $errorMsg .= '"Reward Points" should have a valid value.' . "<br>";
                }
            } else{
                if (empty($post['reward_info'])){
                    $is_error = true;
                    $errorMsg .= '"Reward Info" cannot be blank' . "<br>";
                }
            }
            
            if ($is_error == true){
                foreach($post as $k => $v){
                    $this->view->$k = $v;
                }
                $arr = $this->_helper->Getudffields();
                $this->view->arr = $arr;
                
                $this->view->errorMsg = $errorMsg;
                if ($this->_session->iPhone == true){
                    $this->getResponse()->insert('error', $this->view->render('ierror.phtml'));
                    return $this->render('icreate');
                } else{
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                    return $this->render('create');
                }
            }
            
            $surveyModel = new Default_Model_Survey();
            
            //$parameters = array ('post_code' => ! empty ( $post ['post_code'] ) ? $post ['post_code'] : null, 'gender' => $post ['gender'], 'level_id' => ! empty ( $post ['level_id'] ) ? $post ['level_id'] : null, 'min_age' => ! empty ( $post ['min_age'] ) ? $post ['min_age'] : null, 'max_age' => ! empty ( $post ['max_age'] ) ? $post ['max_age'] : null, 'udf' => $udfArr  );
            

            $parameters = array('post_code' => ! empty($post['post_code']) ? $post['post_code'] : null, 'gender' => $post['gender'], 'level_id' => ! empty($post['level_id']) ? $post['level_id'] : null, 'udf' => $udfArr);
            
            $post['parameters'] = serialize($parameters);
            $post['status'] = 'Inactive';
            $post['client_type'] = $user_type;
            
            $y = explode(" ", $post['end_time']);
            
            if (strtolower($y[1]) == 'pm'){
                $z = explode(":", $y[0]);
                if ((int) $z[0] != 12){
                    $t = (int) $z[0] + 12;
                } else{
                    $t = (int) $z[0];
                }
                $t1 = $t . ":" . $z[1] . ":00";
            } else{
                $t1 = $y[0] . ":00";
            }
            
            if ($user_type == 'C'){
                $post['client_id'] = $this->_session->session_data['client_id'];
            }
            
            if ($user_type == 'CC'){
                $post['client_id'] = $this->_session->session_data['client_contact_id'];
            }
            
            $post['start_date'] = date('Y-m-d', strtotime($post['start_date']));
            $post['end_date'] = date('Y-m-d', strtotime($post['end_date']));
            $post['end_date'] .= " " . $t1;
            
            if (! empty($post['title'])){
                $post['title'] = $this->_helper->Filterspchars($post['title']);
            }
            
            if (! empty($post['description'])){
                $post['description'] = $this->_helper->Filterspchars($post['description']);
            }
            
            if (! empty($post['invitation_msg'])){
                $post['invitation_msg'] = $this->_helper->Filterspchars($post['invitation_msg']);
            }
            
            if (! empty($post['completion_msg'])){
                $post['completion_msg'] = $this->_helper->Filterspchars($post['completion_msg']);
            }
            
            $survey_id = $surveyModel->insertSurvey($post);
            
            if ($survey_id){
                $this->_flashMessenger->addMessage(SURVEY_CREATED);
            } else{
                $this->_flashMessenger->addMessage(SURVEY_NOT_CREATED);
            }
            $this->_redirector->gotoUrl("survey-listing.html");
        } else{
            //		    $arr = $this->_helper->Getudffields();
        //		    $this->view->arr = $arr;
        //
        //		    $ageBandModel = new Default_Model_AgeBands();
        //		    $this->view->ageBands = $ageBandModel->listAllAgeBands();
        //
        //			if ($this->_session->iPhone == true) {
        //				return $this->render ( 'icreate' );
        //			}
        }
    }

    public function surveyinfoAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        $surveyModel = new Default_Model_Survey();
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'Survey Info';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        $rewardType = new Default_Model_RewardTypes();
        $rTypes = $rewardType->listRewardTypes('Active');
        $this->view->rTypes = $rTypes;
        
        $memberLevelModel = new Default_Model_MemberLevel();
        $this->view->memberLevel = $memberLevelModel->listMemberLevel();
        
        if (empty($post)){
            $sid = (int) $this->_getParam('sid');
            
            if (empty($sid)){
                $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
                $this->_redirector->gotoUrl("survey-listing.html");
            } else{
                $surveyInfo = $surveyModel->surveyInfo($sid);
                
                if ($surveyInfo){
                    $this->view->pageTitle = ': Survey Information - ' . $surveyInfo['title'];
                    
                    if ($this->_session->iPhone == true){
                        $sa = date("Y-M-d", strtotime($surveyInfo['start_date']));
                        $sd = explode("-", $sa);
                        $this->view->syear = $sd[0];
                        $this->view->smonth = $sd[1];
                        $this->view->sdate = $sd[2];
                        
                        $ea = date("Y-M-d", strtotime($surveyInfo['end_date']));
                        $ed = explode("-", $ea);
                        $this->view->eyear = $ed[0];
                        $this->view->emonth = $ed[1];
                        $this->view->edate = $ed[2];
                        
                        $et = date('h:i A', strtotime($surveyInfo['end_date']));
                        $e = explode(":", $et);
                        $this->view->Hour = $e[0];
                        
                        $z = explode(" ", $e[1]);
                        $this->view->Minute = $z[0];
                        $this->view->AMPM = $z[1];
                    } else{
                        $this->view->survey_end_time = date('h:i A', strtotime($surveyInfo['end_date']));
                        $surveyInfo['start_date'] = date('j M Y', strtotime($surveyInfo['start_date']));
                        $surveyInfo['end_date'] = date('j M Y', strtotime($surveyInfo['end_date']));
                    }
                    
                    //$arr = $this->_helper->Getudffields();
                    //$this->view->arr = $arr;
                    

                    //$parameters = unserialize($surveyInfo['parameters']);
                    

                    //$this->view->parameters = $parameters;
                    

                    $p = new My_Plugin();
                    $this->view->reward_points_value = $p->getRewardPoints($surveyInfo['reward_points']);
                    //$this->view->max_points = $p->getRewardPoints($surveyInfo['max_amt']);
                    

                    $this->view->surveyinfo = $surveyInfo;
                    
                    if ($this->_session->iPhone == true){
                        return $this->render('isurveyinfo');
                    }
                } else{
                    $this->_flashMessenger->addMessage(INVALID_SURVEY_ID_CANNOT_SHOW_SURVEY);
                    $this->_redirector->gotoUrl("survey-listing.html");
                }
            }
        } else{
            
            $st_dt = true;
            $en_dt = true;
            if ($this->_session->iPhone == true){
                $dtArr = array('Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6, 'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12);
                
                if (! empty($post['sdate']) && ! empty($post['smonth']) && ! empty($post['syear'])){
                    if (checkdate($dtArr[$post['smonth']], $post['sdate'], $post['syear']) == true){
                        $post['start_date'] = $post['syear'] . "-" . $dtArr[$post['smonth']] . "-" . $post['sdate'];
                    } else{
                        $st_dt = false;
                    }
                }
                
                if (! empty($post['edate']) && ! empty($post['emonth']) && ! empty($post['eyear'])){
                    if (checkdate($dtArr[$post['emonth']], $post['edate'], $post['eyear']) == true){
                        $post['end_date'] = $post['eyear'] . "-" . $dtArr[$post['emonth']] . "-" . $post['edate'];
                    } else{
                        $en_dt = false;
                    }
                }
                
                if (! empty($post['Hour']) && $post['Minute'] != ''){
                    $post['end_time'] = $post['Hour'] . ":" . $post['Minute'] . " " . $post['AMPM'];
                }
            }
            
            $errorMsg = 'Error(s) Encountered while creating survey' . "<br><br>";
            $is_error = false;
            if (empty($post['title'])){
                $is_error = true;
                $errorMsg .= '"Title" cannot be blank.' . "<br>";
            }
            
            if ($st_dt == false || $en_dt == false){
                $is_error = true;
                $errorMsg .= 'Invalid date values entered for "Start Date" or "End Date"' . "<br>";
            } else{
                if (empty($post['start_date']) || empty($post['end_date'])){
                    $is_error = true;
                    $errorMsg .= 'Values required for "Start Date", "End Date"' . "<br>";
                } else{
                    
                    $s_dt = date("Y-m-d", strtotime($post['start_date']));
                    
                    $e_dt = date("Y-m-d", strtotime($post['end_date']));
                    
                    //echo $s_dt . " ---- " . date( "Y-m-d" ); exit;
                    

                    if ($e_dt < $s_dt){
                        $is_error = true;
                        $errorMsg .= '"End Date" should be greater then "Start Date"' . "<br>";
                    }
                }
            }
            
            if (empty($post['end_time'])){
                $is_error = true;
                $errorMsg .= '"End Time" should have a valid value.' . "<br>";
            }
            
            //        	if (! empty ( $post ['min_age'] ) && ! empty ( $post ['max_age'] )) {
            //				if($post ['min_age'] > $post ['max_age']){
            //				    $is_error = true;
            //				    $errorMsg .= '"Max age" should be greater than "Min age" ' . "<br>";
            //				}
            //			}
            

            if ((int) $post['reward_type_id'] == 1){
                if (empty($post['reward_points'])){
                    $is_error = true;
                    $errorMsg .= '"Reward Points" should have a valid value.' . "<br>";
                }
            } else{
                if (empty($post['reward_info'])){
                    $is_error = true;
                    $errorMsg .= '"Reward Info" cannot be blank' . "<br>";
                }
            }
            
            if ($is_error == true){
                $postArr = array();
                
                foreach($post as $k => $v){
                    $postArr[$k] = $v;
                    $this->view->$k = $v;
                }
                
                $postArr['survey_id'] = $post['id'];
                
                $parameters['level_id'] = ! empty($post['level_id']) ? $post['level_id'] : null;
                $parameters['gender'] = $post['gender'];
                $parameters['post_code'] = $post['post_code'];
                //$parameters ['min_age'] = $post ['min_age'];
                //$parameters ['max_age'] = $post ['max_age'];
                

                $this->view->parameters = $parameters;
                $this->view->survey_end_time = ! empty($post['end_time']) ? $post['end_time'] : null;
                $this->view->surveyinfo = $postArr;
                
                $this->view->errorMsg = $errorMsg;
                if ($this->_session->iPhone == true){
                    $this->getResponse()->insert('error', $this->view->render('ierror.phtml'));
                    return $this->render('isurveyinfo');
                } else{
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                    return $this->render('surveyinfo');
                }
            }
            
            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
            

            $y = explode(" ", $post['end_time']);
            
            if (strtolower($y[1]) == 'pm'){
                $z = explode(":", $y[0]);
                if ((int) $z[0] != 12){
                    $t = (int) $z[0] + 12;
                } else{
                    $t = (int) $z[0];
                }
                $t1 = $t . ":" . $z[1] . ":00";
            } else{
                $t1 = $y[0] . ":00";
            }
            
            $post['start_date'] = date('Y-m-d', strtotime($post['start_date']));
            $post['end_date'] = date('Y-m-d', strtotime($post['end_date']));
            $post['end_date'] .= " " . $t1;
            
            // Generate Array for Custom Survey Parameters  --- S T A R T //
            // $udfArr = $this->_helper->Generateudf($post);
            // Generate Array for Custom Survey Parameters  --- E N D //
            

            $parameters = array('post_code' => $post['post_code'], 'gender' => $post['gender'], 'level_id' => $post['level_id'], 'min_age' => $post['min_age'], 'max_age' => $post['max_age'], 'udf' => null);
            
            $post['parameters'] = serialize($parameters);
            
            if (! empty($post['title'])){
                $post['title'] = $this->_helper->Filterspchars($post['title']);
            }
            
            if (! empty($post['description'])){
                $post['description'] = $this->_helper->Filterspchars($post['description']);
            }
            
            $result = $surveyModel->updateSurvey($post, true);
            
            if ($result){
                $this->_flashMessenger->addMessage(SURVEY_DETAILS_UPDATED);
            } else{
                $this->_flashMessenger->addMessage(SURVEY_DETAILS_NOT_UPDATED);
            }
            $this->_redirector->gotoUrl('survey-listing.html');
        }
    }

    public function surveyquestionsAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'View/Select Survey Questions';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        if (! empty($post) && ! empty($post['cmdSortOrder'])){
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($post['sid']);
            
            if ($surveyInfo){
                $surveyQueModel = new Default_Model_SurveyQuestion();
                if (is_array($post['sort_order']) && count($post['sort_order']) > 0){
                    for($s = 0; $s < count($post['sort_order']); $s ++){
                        $sorder_res = $surveyQueModel->updateSurveyQuestionSortOrder($post['h_que_id'][$s], $post['sort_order'][$s]);
                    }
                }
                $this->_flashMessenger->addMessage(QUESTION_SORT_ORDER_SET);
                $url = 'view-select-questions/' . $post['sid'] . '/' . $this->_helper->Filterchars($surveyInfo['title']) . '.html';
                $this->_redirector->gotoUrl($url);
            } else{
                $this->_flashMessenger->addMessage(QUESTION_CANNOT_BE_REMOVED);
                $this->_redirector->gotoUrl("survey-listing.html");
            }
        }
        
        if (! empty($post) && ! empty($post['cmdRemove'])){
            
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($post['sid']);
            
            if ($surveyInfo){
                $survey_questionModel = new Default_Model_SurveyQuestion();
                
                $success_cnt = 0;
                
                foreach($post['chk'] as $k => $v){
                    $result = $survey_questionModel->removeSurveyQuestion($v, $post['sid']);
                    if ($result){
                        $success_cnt ++;
                    }
                }
                
                $this->_flashMessenger->addMessage($success_cnt . "' Question(s) removed from survey");
                $url = 'view-select-questions/' . $post['sid'] . '/' . $this->_helper->Filterchars($surveyInfo['title']) . '.html';
                $this->_redirector->gotoUrl($url);
            } else{
                $this->_flashMessenger->addMessage(QUESTION_CANNOT_BE_REMOVED);
                $this->_redirector->gotoUrl("survey-listing.html");
            }
        }
        
        $sid = (int) $this->_getParam('sid');
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
            $this->_redirector->gotoUrl('survey-listing.html');
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            
            if ($surveyInfo){
                $this->view->pageTitle = ': Survey Questions - ' . $surveyInfo['title'];
                
                $this->view->surveyinfo = $surveyInfo;
                
                $survey_questionModel = new Default_Model_SurveyQuestion();
                
                $questions = $survey_questionModel->listSurveyQuestions($sid);
                
                if ($questions){
                    $arrQue = array();
                    $queansModel = new Default_Model_QuestionAnswer();
                    $rsaModel = new Default_Model_ResponseSetAnswer();
                    foreach($questions as $k => $v){
                        $answers = $rsaModel->listQueAns($v['question_id']);
                        if ($answers){
                            $arrAns = array();
                            foreach($answers as $k1 => $v1){
                                $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                            }
                            
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                        $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                        if ($answers){
                            $arrAns = array();
                            foreach($answers as $k1 => $v1){
                                $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                            }
                            
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                    }
                    
                    $this->view->questions = $arrQue;
                } else{
                    $this->view->questions = null;
                }
                if ($this->_session->iPhone == true){
                    return $this->render('isurveyquestions');
                }
            } else{
                $this->_flashMessenger->addMessage(INVALID_SURVEY_ID);
                $this->_redirector->gotoUrl('survey-listing.html');
            }
        }
    }

    public function selectquestionsAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $sid = (int) $this->_getParam('sid');
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
            $this->_redirector->gotoUrl('survey-listing.html');
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            
            if ($surveyInfo){
                $this->view->pageTitle = ': Select Survey Questions - ' . $surveyInfo['title'];
                
                $this->view->surveyinfo = $surveyInfo;
                
                $survey_questionModel = new Default_Model_SurveyQuestion();
                
                $cid = $surveyInfo['client_id'];
                
                $client_type = $surveyInfo['client_type'];
                
                if ($client_type == 'CC'){
                    
                    $clientContactsModel = new Default_Model_ClientContacts();
                    
                    $clientid = $clientContactsModel->clientcontactInfo($cid);
                    $c_id = $clientid['client_id'];
                    
                    $questions = $survey_questionModel->listAvailableQuestions($sid, $c_id);
                
                } else{
                    
                    $questions = $survey_questionModel->listAvailableQuestions($sid, $cid);
                }
                
                if ($questions){
                    $arrQue = array();
                    $queansModel = new Default_Model_QuestionAnswer();
                    $rsaModel = new Default_Model_ResponseSetAnswer();
                    foreach($questions as $k => $v){
                        $answers = $rsaModel->listQueAns($v['question_id']);
                        if ($answers){
                            $arrAns = array();
                            foreach($answers as $k1 => $v1){
                                $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                            }
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                        $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                        if ($answers){
                            $arrAns = array();
                            foreach($answers as $k1 => $v1){
                                $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                            }
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                    }
                    
                    $this->view->questions = $arrQue;
                } else{
                    $this->view->questions = null;
                }
                if ($this->_session->iPhone == true){
                    return $this->render('iselectquestions');
                }
            } else{
                $this->_flashMessenger->addMessage(INVALID_SURVEY_ID);
                $this->_redirector->gotoUrl('survey-listing.html');
            }
        }
    }

    //  default page action
    public function takesurveyAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        
        // get the only active survey from db
        $surveyModel = new Default_Model_Survey();
        $surveyInfo = $surveyModel->getfrontendSurvey('Active');
        
        if ($surveyInfo){
            $this->view->pageTitle = ': Take Survey - ' . $surveyInfo['title'];
            
            $this->view->surveyinfo = $surveyInfo;
            
            $survey_questionModel = new Default_Model_SurveyQuestion();
            
            // get the survey id from the survey info
            $sid = $surveyInfo['survey_id'];
            $this->_session->sid = $sid;
            
            // check if a user has already taken that survey
            $customer_surveyModel = new Default_Model_CustomerSurvey();
            $surveyTakenInfo = $customer_surveyModel->SurveyTakenInfo((int) $this->_session->session_data['user_id'], $sid);
            
            // if yes redirect to thank you page
            if (! empty($surveyTakenInfo)){
                $this->_flashMessenger->addMessage(SURVEY_COMPLETED);
                $this->_redirector->gotoUrl('thank-you.html');
            }
            
            $questions = $survey_questionModel->listSurveyQuestions((int) $this->_session->sid, (int) $this->_session->session_data['user_id']);
            
            if ($questions){
                $arrQue = array();
                $queansModel = new Default_Model_QuestionAnswer();
                $rsaModel = new Default_Model_ResponseSetAnswer();
                $queRulesModel = new Default_Model_QueRules();
                
                foreach($questions as $k => $v){
                    $answers = $rsaModel->listQueAns($v['question_id']);
                    if ($answers){
                        $arrAns = array();
                        foreach($answers as $k1 => $v1){
                            
                            // -- que rules start --
                            $rules = $queRulesModel->getQueRule($v['sq_id'], $v1['answer_id']);
                            $arrRules = null;
                            
                            foreach($rules as $rk => $rv){
                                $arrRules[] = array('qr_id' => $rv['qr_id'], 'sq_id' => $rv['sq_id'], 'answer_id' => $rv['answer_id'], 'target_sq_id' => $rv['target_sq_id']);
                            }
                            // -- que rules end --
                            

                            $ans_info = $queansModel->getSelectedAnsInfo((int) $this->_session->session_data['user_id'], $sid, $v['question_id'], $v1['answer_id']);
                            
                            if ($ans_info){
                                $response_id = $ans_info['response_id'];
                                $selected_flag = true;
                                $user_ans_text = $ans_info['answer_text'];
                            } else{
                                $response_id = 0;
                                $selected_flag = false;
                                $user_ans_text = null;
                            }
                            
                            $arrAns[] = array('answer_id' => $v1['answer_id'], 'response_id' => $response_id, 'answer_text' => $v1['answer_text'], 'answer_type' => $v1['answer_type'], 'selected_flag' => $selected_flag, 'user_ans_text' => $user_ans_text, 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption'], 'rules' => $arrRules);
                        }
                        $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                    }
                    
                    $answers = $queansModel->listQueAns('qa.qa_id', $v['question_id']);
                    if ($answers){
                        $arrAns = array();
                        foreach($answers as $k1 => $v1){
                            
                            // -- que rules start --
                            $rules = $queRulesModel->getQueRule($v['sq_id'], $v1['answer_id']);
                            $arrRules = null;
                            
                            foreach($rules as $rk => $rv){
                                $arrRules[] = array('qr_id' => $rv['qr_id'], 'sq_id' => $rv['sq_id'], 'answer_id' => $rv['answer_id'], 'target_sq_id' => $rv['target_sq_id']);
                            }
                            // -- que rules end --

                            $ans_info = $queansModel->getSelectedAnsInfo((int) $this->_session->session_data['user_id'], $sid, $v['question_id'], $v1['answer_id']);
                            
                            if ($ans_info){
                                $response_id = $ans_info['response_id'];
                                $selected_flag = true;
                                $user_ans_text = $ans_info['answer_text'];
                            } else{
                                $response_id = 0;
                                $selected_flag = false;
                                $user_ans_text = null;
                            }
                            
                            $arrAns[] = array('answer_id' => $v1['answer_id'], 'response_id' => $response_id, 'answer_text' => $v1['answer_text'], 'answer_type' => $v1['answer_type'], 'selected_flag' => $selected_flag, 'user_ans_text' => $user_ans_text, 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption'], 'rules' => $arrRules);
                        }
                        $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                    }
                }
                
                //$this->p($arrQue);  exit;

                $this->view->questions = $arrQue;
            } else{
                $this->view->questions = null;
            }
        }
    }

    public function rejectsurveyAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $sid = (int) $this->_getParam('sid');
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            
            if ($surveyInfo){
                $siModel = new Default_Model_SurveyInvitations();
                $data = array('action' => 'R', 'survey_id' => $sid, 'member_id' => (int) $this->_session->session_data['member_id']);
                $invitation_result = $siModel->updateSurveyInvitationAction($data);
                if ($invitation_result){
                    $this->_flashMessenger->addMessage('Survey rejected successfully');
                } else{
                    $this->_flashMessenger->addMessage('Error(s) encountered. Survey not rejected');
                }
            } else{
                $this->_flashMessenger->addMessage(INVALID_SURVEY_ID);
            }
        }
        $this->_redirector->gotoUrl('member-survey-listing.html');
    }

    // where 2dn step to submit responses
    public function surveyresponseAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        
        if (! empty($post['cmdCancel'])){
            $this->_redirector->gotoUrl('user-home.html');
        }
        
        $sid = (int) $this->_session->sid;
        
        $process_response = false;
        
        if (! empty($sid)){
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            if ($surveyInfo){
                $process_response = true;
            }
        }
        
        $flag = false;
        $partial_flag = false;
        
        if ($process_response == true){
            
            $data['user_id'] = $this->_session->session_data['user_id'];
            
            $responseModel = new Default_Model_Response();
            
            foreach($post['qid'] as $k => $v){
                $str_1 = 'ans' . $v;
                
                if (! empty($post[$str_1])){
                    if (is_array($post[$str_1])){
                        $answers = $post[$str_1];
                    } else{
                        $answers = array($post[$str_1]);
                    }
                } else{
                    $answers = null;
                }
                
                //$this->p($answers);		exit;
                foreach($answers as $k1 => $v1){
                    
                    $answer_id = $v1;
                    
                    if (! empty($answer_id)){
                        $str_2 = 'txt' . $answer_id . "_" . $v;
                        
                        if (! empty($post[$str_2])){
                            $free_text = $post[$str_2];
                        } else{
                            $free_text = null;
                        }
                        
                        //						$str_2 = 'resid' . $v;
                        //						if (! empty ( $post [$str_2] )) {
                        //							$response_id = $post [$str_2];
                        //						} else {
                        //			$response_id = 0;
                        //						}
                        

                        $data['user_id'] = (int) $this->_session->session_data['user_id'];
                        $data['sq_id'] = $v;
                        $data['answer_id'] = $answer_id;
                        $data['answer_text'] = $free_text;
                        $response_id = $responseModel->insertSurveyResponses($data);

                        if ($response_id){
                            $flag = true;
                        } else{
                            $partial_flag = true;
                        }
                    }
                }
            }
        }
        
        if ($flag == false){
            $this->_flashMessenger->addMessage(SURVEY_RESPONSES_NOT_SUBMITTED);
        }
        
		if ($flag == true) {
			 $surveytaken['survey_id'] = $sid;
	         $surveytaken['user_id'] = ( int ) $this->_session->session_data ['user_id'];
	         $surveytaken['completed'] = 'y';
	         
	        $customer_surveyModel = new Default_Model_CustomerSurvey ();
			$surveyInfo = $customer_surveyModel->insertcustomer_survey ($surveytaken );
		
           $this->_flashMessenger->addMessage ( SURVEY_RESPONSES_SUBMITTED );

		}
		
		$this->_redirector->gotoUrl('thank-you.html');
    
		//$this->_redirector->gotoUrl ( 'thank-you.html' );
	}
	
    public function thankyouAction() {
        
  	  $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = 'Thank You';
        
        $this->view->is_error = $this->_session->is_error;
        
        //   $this->view->greeting = $this->getGreeting();
        

        //   $this->view->cust_name = $this->_session->session_data['name'];
        

        if ($this->_session->is_error == true){
            
            $this->view->err_msg = $this->_session->err_msg;
        
        } else{
            
            $this->view->err_msg = null;
        
        }
        
        if ($this->_session->iPhone == true){
            
            $this->view->header_title = $this->view->greeting;
            
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
            
            return $this->render('i-thankyou');
        
        }
    
    }

    public function addsurveyquestionAction() {
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        
        $sid = (int) $post['sid'];
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
            $this->_redirector->gotoUrl('survey-listing.html');
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            
            if ($surveyInfo){
                $this->view->surveyinfo = $surveyInfo;
                
                $survey_questionModel = new Default_Model_SurveyQuestion();
                
                $que_selected = count($post['chk']);
                $success_cnt = 0;
                
                foreach($post['chk'] as $k => $v){
                    $result = $survey_questionModel->addSurveyQuestion($v, $sid);
                    if ($result){
                        $success_cnt ++;
                    }
                }
                
                $this->_flashMessenger->addMessage($success_cnt . " Questions successfully added to survey");
                
                $url = 'view-select-questions/' . $surveyInfo['survey_id'] . '/' . $this->_helper->Filterchars($surveyInfo['title']) . '.html';
                
                $this->_redirector->gotoUrl($url);
            
            } else{
                $this->_flashMessenger->addMessage(INVALID_SURVEY_ID);
                $this->_redirector->gotoUrl('survey-listing.html');
            }
        }
    }

    public function viewsurveyresponseAction() {
        $skip = (int) $this->_getParam('skip');
        $this->view->skip = $skip;
        if (empty($skip)){
            $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
            
            if (empty($result)){
                $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
                $this->_redirector->gotoUrl('index.html');
            }
        }
        
        $sid = (int) $this->_getParam('sid');
        $pg = (int) $this->_getParam('page');
        
        $this->view->user_type = $this->_session->session_data['user_type'];
        
        if ($this->_session->session_data['user_type'] != 'M'){
            $mid = (int) $this->_getParam('cmbMemberId');
            $detailed = (int) $this->_getParam('detailed');
        } else{
            $mid = 0;
            $detailed = false;
        }
        
        // for displaying color array
        $colorsArray = array('Red', 'Orange', 'Chocolate', 'Blue', 'Brown', 'Coral', 'Yellow', 'DeepPink', 'CornflowerBlue', 'Crimson', 'DarkGoldenRod', 'Fuchsia', 'Gold', 'GreenYellow', 'Indigo', 'MediumPurple', 'DodgerBlue', 'Lime', 'Purple', 'Thistle', 'AntiqueWhite');
        
        $this->view->colorsArray = $colorsArray;
        
        $this->view->pg = ! empty($pg) ? $pg : 1;
        $this->view->mid = $mid;
        $this->view->detailed = $detailed;
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
            $this->_redirector->gotoUrl($this->_modulename . '/survey-listing.html');
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            
            if ($surveyInfo){
                $this->view->surveyinfo = $surveyInfo;
                
                $this->view->pageTitle = ': View Survey Responses - ' . $surveyInfo['title'];
                
                $responseModel = new Default_Model_Response();
                $surveyTakenCount = $responseModel->surveyTakenCount($sid);
                
                $surveyCount = 0;
                $this->view->surveyTakenCount = 0;
                
                if (! empty($surveyTakenCount)){
                    $surveyCount = $surveyTakenCount['cnt'];
                    $this->view->surveyTakenCount = $surveyCount;
                }
                
                if (empty($skip)){
                    if ($this->_session->session_data['user_type'] != 'M'){
                        if ($surveyCount > 0){
                            $result_1 = $responseModel->memberSurveyTaken($sid);
                            if ($result_1){
                                $this->view->result_1 = $result_1;
                            } else{
                                $this->view->result_1 = null;
                            }
                        }
                    }
                } else{
                    $this->view->result_1 = null;
                }
                
                $surveyQueModel = new Default_Model_SurveyQuestion();
                $questions = $surveyQueModel->listSurveyQuestions($sid);
                
                if ($questions){
                    $queansModel = new Default_Model_QuestionAnswer();
                    $rsaModel = new Default_Model_ResponseSetAnswer();
                    $arrQue = array();
                    
                    foreach($questions as $k => $v){
                        $answers = $rsaModel->listQueAns($v['question_id']);
                        $arrAns = array();
                        
                        $que_response_res = $responseModel->queResponseCount($sid, $v['question_id']);
                        $que_response_count = $que_response_res['cnt'];
                        
                        if ($answers){
                            $c = 0;
                            foreach($answers as $k1 => $v1){
                                
                                if ($c == 20){
                                    $c = 0;
                                }
                                $percentage = 0;
                                $selectedCount = 0;
                                
                                $ansChecked = false;
                                $ansText = null;
                                
                                if (empty($detailed)){
                                    $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id']);
                                    if (! empty($ansCnt['cnt'])){
                                        $selectedCount = $ansCnt['cnt'];
                                        if (! empty($surveyCount)){
                                            //$percentage = ( $ansCnt['cnt'] * 100 ) / $surveyCount;
                                            $percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                        }
                                    }
                                } else{
                                    $ansValue = $responseModel->ansSelected($sid, $v['question_id'], $v1['answer_id'], $mid);
                                    if (! empty($ansValue)){
                                        $ansChecked = true;
                                        $ansText = $ansValue['answer_text'];
                                    }
                                }
                                
                               $arrAns[] = array( 'answer_id'           => $v1['answer_id'],
                               					  'color'				=> $colorsArray[$c],
                                                  'selected_cnt'        => $selectedCount,
                                                  'percentage'          => $percentage,
                                                  'ansChecked'          => $ansChecked,
                                                  'ansText'             => $ansText,
                                                  'answer_type'         => $v1['answer_type'],
                                                  'chart_flag'          =>($v1['answer_type'] == 'v') ? false : true,
                                                  'free_text'           => $v1['free_text'],
                                                  'answer_text'         => $v1['answer_text'],
                                                  'free_text'           => $v1['free_text'],
                                                  'free_text_caption'   => $v1['custom_free_text_caption']               );
                               
                                
                                $c ++;
                            
                            }
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                        
                        $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                        
                        if ($answers){
                            $c = 0;
                            foreach($answers as $k1 => $v1){
                                if ($c == 20){
                                    $c = 0;
                                }
                                $percentage = 0;
                                $selectedCount = 0;
                                
                                $ansChecked = false;
                                $ansText = null;
                                
                                if (empty($detailed)){
                                    $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id']);
                                    if (! empty($ansCnt['cnt'])){
                                        $selectedCount = $ansCnt['cnt'];
                                        if (! empty($surveyCount)){
                                            //$percentage = ( $ansCnt['cnt'] * 100 ) / $surveyCount;
                                            $percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                        }
                                    }
                                } else{
                                    $ansValue = $responseModel->ansSelected($sid, $v['question_id'], $v1['answer_id'], $mid);
                                    if (! empty($ansValue)){
                                        $ansChecked = true;
                                        $ansText = $ansValue['answer_text'];
                                    }
                                }
                                
                               $arrAns[] = array( 'answer_id'           => $v1['answer_id'],
                               					  'color'				=> $colorsArray[$c],
                                                  'selected_cnt'        => $selectedCount,
                                                  'percentage'          => $percentage,
                                                  'ansChecked'          => $ansChecked,
                                                  'ansText'             => $ansText,
                                                  'answer_type'         => $v1['answer_type'],
                                                  'chart_flag'          =>($v1['answer_type'] == 'v') ? false : true,
                                                  'free_text'           => $v1['free_text'],
                                                  'answer_text'         => $v1['answer_text'],
                                                  'free_text'           => $v1['free_text'],
                                                  'free_text_caption'   => $v1['custom_free_text_caption']               );
                                $c ++;
                            
                            }
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                    
                    }
                    
                    $this->view->questions = $arrQue;
                } else{
                    $this->view->questions = null;
                }
                
                if ($this->_session->iPhone == true){
                    $this->view->header_title = 'Survey Response';
                    $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
                    return $this->render('iviewsurveyresponse');
                }
            } else{
                $this->_flashMessenger->addMessage(INVALID_SURVEY_ID);
                $this->_redirector->gotoUrl($this->_modulename . '/survey-listing.html');
            }
        }
    }

    public function sampleframeAction() {
        $sid = $this->_getParam('id');
        $ab = $this->_getParam('ab');
        
        if (! empty($sid) && ! empty($ab)){
            $cnt = $this->_helper->sampleframes($sid, $ab);
            echo $cnt;
            exit();
        } else{
            return 0;
        }
    }

    public function selectioncriteriaAction() {
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $post = $this->getRequest()->getPost();
        $surveyModel = new Default_Model_Survey();
        
        if ($this->_session->iPhone == true){
            $this->view->header_title = 'Survey Selection Criteria';
            $this->getResponse()->insert('header', $this->view->render('iheader.phtml'));
        }
        
        $memberLevelModel = new Default_Model_MemberLevel();
        $this->view->memberLevel = $memberLevelModel->listMemberLevel();
        
        $arr = $this->_helper->Getudffields();
        $this->view->arr = $arr;
        
        $ageBandModel = new Default_Model_AgeBands();
        $this->view->ageBands = $ageBandModel->listAllAgeBands();
        
        if (empty($post)){
            $sid = (int) $this->_getParam('sid');
            
            if (empty($sid)){
                $this->_flashMessenger->addMessage(INVALID_MISSING_SURVEY_ID);
                $this->_redirector->gotoUrl("survey-listing.html");
            } else{
                $surveyInfo = $surveyModel->surveyInfo($sid);
                
                if ($surveyInfo){
                    $this->view->pageTitle = ': Survey Information - ' . $surveyInfo['title'];
                    
                    if ($this->_session->iPhone == true){
                        $sa = date("Y-M-d", strtotime($surveyInfo['start_date']));
                        $sd = explode("-", $sa);
                        $this->view->syear = $sd[0];
                        $this->view->smonth = $sd[1];
                        $this->view->sdate = $sd[2];
                        
                        $ea = date("Y-M-d", strtotime($surveyInfo['end_date']));
                        $ed = explode("-", $ea);
                        $this->view->eyear = $ed[0];
                        $this->view->emonth = $ed[1];
                        $this->view->edate = $ed[2];
                        
                        $et = date('h:i A', strtotime($surveyInfo['end_date']));
                        $e = explode(":", $et);
                        $this->view->Hour = $e[0];
                        
                        $z = explode(" ", $e[1]);
                        $this->view->Minute = $z[0];
                        $this->view->AMPM = $z[1];
                    } else{
                        $this->view->survey_end_time = date('h:i A', strtotime($surveyInfo['end_date']));
                        $surveyInfo['start_date'] = date('j M Y', strtotime($surveyInfo['start_date']));
                        $surveyInfo['end_date'] = date('j M Y', strtotime($surveyInfo['end_date']));
                    }
                    
                    $arr = $this->_helper->Getudffields();
                    $this->view->arr = $arr;
                    
                    $parameters = unserialize($surveyInfo['parameters']);
                    
                    //$this->p($parameters);
                    

                    $this->view->parameters = $parameters;
                    
                    $p = new My_Plugin();
                    $this->view->reward_points_value = $p->getRewardPoints($surveyInfo['reward_points']);
                    //$this->view->max_points = $p->getRewardPoints($surveyInfo['max_amt']);
                    

                    $this->view->surveyinfo = $surveyInfo;
                    
                    if (! empty($this->_session->eligible_member_count)){
                        $this->view->member_count = $this->_session->eligible_member_count;
                        $this->_session->eligible_member_count = null;
                    }
                    
                    if ($this->_session->iPhone == true){
                        return $this->render('iselectioncriteria');
                    }
                } else{
                    $this->_flashMessenger->addMessage(INVALID_SURVEY_ID_CANNOT_SHOW_SURVEY);
                    $this->_redirector->gotoUrl("survey-listing.html");
                }
            }
        } else{
            // Generate Array for Custom Survey Parameters  --- S T A R T //
            $udfArr = $this->generateUDF($post);
            // Generate Array for Custom Survey Parameters  --- E N D //
            

            // Sample frames Age -- S T A R T //
            

            $arrAgeBand = array();
            if (! empty($post['chkAB']) && ! empty($post['chkSFAge'])){
                foreach($post['chkAB'] as $k => $v){
                    $tmp = 'txtAgeCSS' . $v;
                    $txtAgeCSS = $post[$tmp];
                    
                    $tmp = 'txtAgeCMF' . $v;
                    $txtAgeCMF = $post[$tmp];
                    
                    array_push($arrAgeBand, array($v, $txtAgeCSS, $txtAgeCMF));
                }
            }
            
            // Sample frames Age -- E N D //
            // $this->p($post); $this->p($arrAgeBand); exit;
            

            $parameters = array('post_code' => ! empty($post['post_code']) ? $post['post_code'] : null, 'gender' => $post['gender'], 'level_id' => $post['level_id'], 'udf' => $udfArr, 'ageband' => $arrAgeBand);
            
            $post['parameters'] = serialize($parameters);
            
            $survey_result = $surveyModel->updateSurveySelectionParameters($post);
            
            if ($survey_result){
                if (! empty($post['cmdMemberCount'])){
                    $count = $this->_helper->sendnotification(array('sid' => $post['id'], 'flag' => true));
                    $this->_session->eligible_member_count = $count;
                }
                if (! empty($post['cmdSubmit'])){
                    $this->_flashMessenger->addMessage('Survey audience selection criteria updated successfully.');
                }
            } else{
                $this->_flashMessenger->addMessage('Error(s) encountered. Survey audience selection criteria not updated.');
            }
            //$this->_redirector->gotoUrl ('survey-listing.html');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/selectioncriteria/sid/' . $post['id']);
        }
        
        if ($this->_session->iPhone == true){
            return $this->render('iselectioncriteria');
        }
    }

    public function generateUDF($post) {
        $arr = $this->_helper->Generateudf($post);
        return $arr;
    }

}