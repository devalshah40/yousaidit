<?php

class Admin_SurveyController extends My_AdminController {

    /**
     * init() function, call init() function of parent class
     *
     */
    public function init() {
        parent::init();
    }

    /**
     * Send survey notification to qualifying members
     *
     */
    public function sendsurveynotificationAction() {
        $sid = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        $surveyQueModel = new Default_Model_SurveyQuestion();
        $questions = $surveyQueModel->listSurveyQuestions($sid);
        if ($questions){
            $result = $this->_helper->sendnotification($sid);
            if ($result == 1){
                $this->_flashMessenger->addMessage("Survey id is missing or invalid");
            } else if ($result == 2){
                $this->_flashMessenger->addMessage("Survey notification already sent.");
            } else if ($result == 3){
                $this->_flashMessenger->addMessage("Survey notification successfully sent.");
            } else if ($result == 4){
                $this->_flashMessenger->addMessage("Survey notification already sent.");
            } else{
                $this->_flashMessenger->addMessage("Error(s) encountered. Survey notification not sent.");
            }
        } else{
            $this->_flashMessenger->addMessage("Cannot send notification. No question associated with survey.");
        }
        $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
    }

    public function indexAction() {
        
        $this->_session->search_param = '';
        $post = $this->getRequest()->getPost();
        $surveyModel = new Default_Model_Survey();
        if (! empty($post['cmdDelete'])){
            $id = $post['chk'];
            $delete_count = 0;
            if (is_array($id) && count($id) > 0){
                foreach($id as $k => $v){
                    
                    $delete_res = $surveyModel->deleteSurvey($v);
                    if ($delete_res){
                        $delete_count ++;
                    }
                }
            }
            $this->_flashMessenger->addMessage($delete_count . ' Survey(s) deleted.');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/index/');
        }
        $sort_field = 'add_date DESC';
        $field = null;
        $value = null;
        if (! empty($post['cmdSubmit'])){
            $field = $post['cmbKey'];
            $value = $post['txtValue'];
            $search_param = array("field" => $field, "value" => $value, "page" => $this->_controllername);
            $this->_session->search_param = $search_param;
            $this->view->result_type = 'filtered';
        } else{
            if ($this->_getParam('clear') == "results"){
                $search_param = array();
                $this->_session->search_param = $search_param;
            }
            $search_param = $this->_session->search_param;
            if (! empty($search_param) && ! empty($this->_session->search_param['page']) && $this->_session->search_param['page'] == 'survey'){
                $field = $this->_session->search_param['field'];
                $value = $this->_session->search_param['value'];
                $this->view->result_type = 'filtered';
            }
        }
        
        $this->view->pageTitle = ADMIN_TITLE . 'Manage Survey';
        $this->view->pgTitle = 'Survey Listing';
        
        $this->getResponse()->insert('navigation', $this->view->render('survey/survey_menu.phtml'));
        
        $surveyList = $surveyModel->listSurveyAdmin($sort_field, $field, $value);
        //var_dump($surveyList); exit;
        

        if ($surveyList){
            //$p = new My_Plugin ();
            

            //	echo "xxx"; exit;
            foreach($surveyList as $k => $v){
                $xArr[] = array('sinfo' => $v);
            }
            $pg = $this->_getParam('page');
            $this->view->pg = ! empty($pg) ? $pg : 1;
            
            //$this->p($xArr); exit;
            

            $this->view->paginator = Zend_Paginator::factory($xArr);
            $this->view->paginator->setItemCountPerPage(REC_LIMIT);
            $this->view->paginator->setPageRange(PAGE_LINK_COUNT);
            $this->view->paginator->setCurrentPageNumber($pg);
        } else{
            $this->view->paginator = null;
        }
    }

    public function get_udf_fields() {
        $arr = $this->_helper->Getudffields();
        return $arr;
    }

    public function generateUDF($post) {
        $arr = $this->_helper->Generateudf($post);
        return $arr;
    }

    public function addAction() {
        $surveyModel = new Default_Model_Survey();
        
        $post = $this->getRequest()->getPost();
        
        $is_error = false;
        if (! empty($post)){
            $errorMsg = 'Error(s) Encountered while creating survey' . "<br><br>";
            
            if (empty($post['title'])){
                $is_error = true;
                $errorMsg .= '"Title" cannot be left blank.' . "<br>";
            }
            
            //			if (empty ( $post ['start_date'] ) || empty ( $post ['end_date'] )) {
            //			   $is_error = true;
            //			   $errorMsg .= 'Values required for "Start Date", "End Date"' . "<br>";
            //			}else{
            //				$s_dt = date ( "Y-m-d", strtotime ( $post ['start_date'] ) );
            //				$e_dt = date ( "Y-m-d", strtotime ( $post ['end_date'] ) );
            //
            //				if ($s_dt < date ( "Y-m-d" )) {
            //					$is_error = true;
            //					$errorMsg .= '"Start Date" should be equal to or greater than todays date' . "<br>";
            //				}
            //
            //				if ($e_dt < $s_dt) {
            //					$is_error = true;
            //					$errorMsg .= '"End Date" should be greater then "Start Date"' . "<br>";
            //				}
            //			}
            //
            //		    if (empty ( $post ['end_time'] )) {
            //				$is_error = true;
            //				$errorMsg .= '"End Time" should have a valid value.' . "<br>";
            //			}
            

            if ($is_error == false){
                //		$post ['added_by'] = $this->_session->session_data ['user_id'];
                

                //    			$y = explode ( " ", $post ['end_time'] );
                //
                //    			if (strtolower ( $y [1] ) == 'pm') {
                //    				$z = explode ( ":", $y [0] );
                //    				if (( int ) $z [0] != 12) {
                //    					$t = ( int ) $z [0] + 12;
                //    				} else {
                //    					$t = ( int ) $z [0];
                //    				}
                //    				$t1 = $t . ":" . $z [1] . ":00";
                //    			} else {
                //    				$t1 = $y [0] . ":00";
                //    			}
                //
                //    			$post ['start_date'] = date ( 'Y-m-d', strtotime ( $post ['start_date'] ) );
                //    			$post ['end_date'] = date ( 'Y-m-d', strtotime ( $post ['end_date'] ) );
                //    			$post ['end_date'] .= " " . $t1;
                

                $post['title'] = $this->_helper->Filterspchars($post['title']);
                if (! empty($post['description'])){
                    $post['description'] = $this->_helper->Filterspchars($post['description']);
                }
                if (! empty($post['invitation_msg'])){
                    $post['invitation_msg'] = $this->_helper->Filterspchars($post['invitation_msg']);
                }
                
                if (! empty($post['completion_msg'])){
                    $post['completion_msg'] = $this->_helper->Filterspchars($post['completion_msg']);
                }
                
                $post['token'] = strtolower($this->_helper->randomstringgenerator(10));
                
                $survey_id = $surveyModel->insertSurvey($post);
                
                if ($survey_id){
                    
                    // code block to associate questions imported via CVS file
                    // directly to the survey being created ..... START
                    

                    if (! empty($post['survey_questions_flag']) && (int) $post['survey_questions_flag'] == 1){
                        $survey_questionModel = new Default_Model_SurveyQuestion();
                        $success_cnt = 0;
                        foreach($this->_session->survey_questions as $sq_k => $sq_v){
                            $sq_result = $survey_questionModel->addSurveyQuestion($sq_v, $survey_id, 0);
                            if ($sq_result){
                                $success_cnt ++;
                            }
                        }
                        $this->_session->survey_questions = null;
                        if ($success_cnt > 0){
                            $this->_flashMessenger->addMessage('Survey added successfully with ' . $success_cnt . ' questions');
                        } else{
                            $this->_flashMessenger->addMessage('Survey added successfully.');
                        }
                    } else{
                        $this->_flashMessenger->addMessage('Survey added successfully.');
                    }
                
     // code block to associate questions imported via CVS file
                // directly to the survey being created ..... START
                

                } else{
                    $this->_flashMessenger->addMessage('Error(s) encountered. Survey not added.');
                }
                $this->_redirector->gotoUrl($this->_modulename . '/survey/add');
            }
        }
        
        // flag to identify if question association is to be undertaken directly
        // after creating of survey using questions imported via CVS file
        if (! empty($this->_session->survey_questions)){
            $this->view->survey_questions_flag = 1;
        } else{
            $this->view->survey_questions_flag = 0;
        }
        
        //      $memberLevelModel = new Default_Model_MemberLevel ();
        //      $this->view->memberLevel = $memberLevelModel->listMemberLevel ();
        

        // $clientModel = new Default_Model_Client ();
        //  $this->view->clients = $clientModel->listClients ( 'company_name' );
        

        $this->view->action = 'Add';
        $this->view->pageTitle = ADMIN_TITLE . 'Add survey';
        $this->view->pgTitle = 'Add survey';
        
        $this->getResponse()->insert('navigation', $this->view->render('survey/survey_menu.phtml'));
        
        if ($is_error == true){
            foreach($post as $k => $v){
                $surveyinfo[$k] = $v;
            }
            //$this->view->parameters = array('level_id' => !empty($post['level_id'])?$post['level_id']:null, 'gender' => $post['gender'], 'post_code' => $post['post_code'], 'min_age' => $post['min_age'], 'max_age' => $post['max_age'] );
            $this->view->surveyinfo = $surveyinfo;
            $this->view->errorMsg = $errorMsg;
            $this->getResponse()->insert('error', $this->view->render('error.phtml'));
            return $this->render('add');
        }
    }

    public function contactlistAction() {
        //$this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNeverRender();
        

        $id = $this->getRequest()->getParam('id');
        
        if (! empty($id)){
            $clientcontactModel = new Default_Model_ClientContacts();
            $contactinfo = $clientcontactModel->listActiveClientContacts($id);
            if (! empty($contactinfo)){
                $str = '[';
                foreach($contactinfo as $k => $v){
                    $str .= '{ "id":"' . $v['client_contact_id'] . '", "name":"' . $v['first_name'] . " " . $v['last_name'] . '" }, ';
                }
                $str = substr($str, 0, strlen($str) - 2);
                
                $str .= ']';
                
                echo $str;
            } else{
                echo '';
            }
        } else{
            echo '';
        }
        exit();
    }

    public function statusupdateAction() {
        $id = $this->getRequest()->getParam('id');
        
        $surveyModel = new Default_Model_Survey();
        $surveyInfo = $surveyModel->surveyInfo($id);
        
        $status = $surveyInfo['status'];
        
        if ($status == 'Active'){
            $status = 'Inactive';
            $surveyInfo = $surveyModel->surveyupdatStatus($id, $status);
        
        } else{
            $status = 'Active';
            $surveyInfo = $surveyModel->surveyupdatStatus($id, $status);
        }
        
        echo $status;
        exit();
    }

    public function editAction() {
        $id = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        $pg = (! empty($pg)) ? $pg : 1;
        $surveyModel = new Default_Model_Survey();
        $post = $this->getRequest()->getPost();
        $this->view->action = 'Edit';
        $surveyInfo = $surveyModel->surveyInfo($id);
        //	$cid = $surveyInfo ['client_id'];
        $this->view->pageTitle = 'Edit survey details ';
        $this->view->pgTitle = 'Edit survey details ';
        //	$clientModel = new Default_Model_Client ();
        //	$clientInfo = $clientModel->clientInfo ( ( int ) $surveyInfo ['client_id'] );
        //	$this->view->client = $clientInfo ['company_name'];
        //	$this->view->client .= '&nbsp;(&nbsp;' . $clientInfo ['first_name'];
        //	$this->view->client .= ! empty ( $clientInfo ['last_name'] ) ? ' ' . $clientInfo ['last_name'] : '';
        //	$this->view->client .= '&nbsp;)&nbsp;';
        $this->getResponse()->insert('navigation', $this->view->render('survey/survey_menu.phtml'));
        $surveyInfo = $surveyModel->surveyInfo($id);
        //	$cid = $surveyInfo ['client_id'];
        //   $clientModel = new Default_Model_Client ();
        //    $this->view->clients = $clientModel->listClients ( 'company_name' );
        if (empty($post)){
            $surveyInfo = $surveyModel->surveyInfo($id);
            if ($surveyInfo){
                $this->view->survey_end_time = date('h:i A', strtotime($surveyInfo['end_date']));
                $surveyInfo['start_date'] = date('j M Y', strtotime($surveyInfo['start_date']));
                $surveyInfo['end_date'] = date('j M Y', strtotime($surveyInfo['end_date']));
                $this->view->surveyinfo = $surveyInfo;
                $this->view->pg = $pg;
                return $this->render('add');
            } else{
                $this->_flashMessenger->addMessage('Error(s) encountered. Unable to fetch survey details.');
                $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
            }
        } else{
            $is_error = false;
            $var_msg = 'Error encountered.' . "<br/>";
            if (empty($post['title'])){
                $is_error = true;
                $var_msg .= 'Title cannot be left empty.' . "<br/>";
            }
            $this->view->pg = $pg;
            if ($is_error == true){
                $surveyinfo = $surveyModel->surveyInfo($id);
                
                foreach($post as $k => $v){
                    $getSurvey[$k] = $v;
                }
                
                $getSurvey['survey_id'] = $post['id'];
                
                $getSurvey['add_date'] = $surveyinfo['add_date'];
                $getSurvey['updated_datetime'] = $surveyinfo['updated_datetime'];
                
                //$this->view->survey_end_time = date ( 'h:i A', strtotime ( $post ['end_date'] ) );
                

                //$getSurvey ['start_date'] = date("j M Y", strtotime($post['start_date']));
                

                //$getSurvey ['end_date'] = date("j M Y", strtotime($post['end_date']));
                

                $this->view->surveyinfo = $getSurvey;
                $this->view->errorMsg = $var_msg;
                
                $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                
                return $this->render('add');
            
            } else{
                $post['title'] = $this->_helper->Filterspchars($post['title']);
                
                if (! empty($post['description'])){
                    $post['description'] = $this->_helper->Filterspchars($post['description']);
                }
                
                if (! empty($post['invitation_msg'])){
                    $post['invitation_msg'] = $this->_helper->Filterspchars($post['invitation_msg']);
                }
                
                if (! empty($post['completion_msg'])){
                    $post['completion_msg'] = $this->_helper->Filterspchars($post['completion_msg']);
                }
                
                if ($post['responses'] == 'Private'){
                    $post['response_permission'] = '-';
                }
                
                $result = $surveyModel->updateSurvey($post);
                
                if ($result){
                    $this->_flashMessenger->addMessage('Survey detail updated successfully.');
                } else{
                    $this->_flashMessenger->addMessage('Error(s) encountered. Survey detail not updated.');
                }
                $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
            }
        }
    }

    public function questionsAction() {
        $post = $this->getRequest()->getPost();
        $surveyQueModel = new Default_Model_SurveyQuestion();
        
        if (! empty($post['cmdDelete'])){
            $id = $post['chk'];
            $delete_count = 0;
            if (is_array($id) && count($id) > 0){
                foreach($id as $k => $v){
                    $delete_res = $surveyQueModel->removeSurveyQuestion($v, $post['sid']);
                    if ($delete_res){
                        $delete_count ++;
                    }
                }
            }
            $this->_flashMessenger->addMessage($delete_count . ' Survey Question(s) deleted.');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/questions/id/' . $post['sid']);
        }
        
        if (! empty($post['cmdSortOrder'])){
            if (is_array($post['txtSortOrder']) && count($post['txtSortOrder']) > 0){
                for($s = 0; $s < count($post['txtSortOrder']); $s ++){
                    $sorder_res = $surveyQueModel->updateSurveyQuestionSortOrder($post['h_sq_id'][$s], $post['txtSortOrder'][$s]);
                }
            }
            $this->_flashMessenger->addMessage('Sort order successfully set.');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/questions/id/' . $post['sid']);
        }
        
        $sid = (int) $this->_getParam('id');
        //	$cid = ( int ) $this->_getParam ( 'cid' );
        

        if (empty($sid)){
            $this->_flashMessenger->addMessage('Invalid / Missing Survey Id');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/index');
        }
        
        $this->view->pageTitle = ADMIN_TITLE . 'Manage Survey Questions';
        $this->view->pgTitle = 'Survey Question Listing';
        
        $this->view->sid = $sid;
        
        $surveyModel = new Default_Model_Survey();
        $surveyInfo = $surveyModel->surveyInfo($sid);
        
        if ($surveyInfo){
            //	$this->view->cid = $surveyInfo ['client_id'];
        } else{
            ///		$this->view->cid = $cid;
        }
        
        $this->getResponse()->insert('navigation', $this->view->render('survey/surveyquestion_menu.phtml'));
        
        if ($surveyInfo){
            $this->view->surveyinfo = $surveyInfo;
            $questions = $surveyQueModel->listSurveyQuestions($sid);
            
            if ($questions){
                $this->view->pageTitle = ADMIN_TITLE . 'Manage Survey Questions';
                $this->view->pgTitle = 'Survey Question Listing';
                
                $pg = $this->_getParam('page');
                $this->view->pg = ! empty($pg) ? $pg : 1;
                
                //$answerModel = new Default_Model_Answer ();
                $arrQue = array();
                
                $queansModel = new Default_Model_QuestionAnswer();
                $rsaModel = new Default_Model_ResponseSetAnswer();
                $queruleModel = new Default_Model_QueRules();
                
                foreach($questions as $k => $v){
                    $answers = $rsaModel->listQueAns($v['question_id']);
                    $arrAns = array();
                    
                    if ($answers){
                        foreach($answers as $k1 => $v1){
                            $queruleInfo = $queruleModel->getQueRule($v['sq_id'], $v1['answer_id']);
                            $qr_id = null;
                            if (! empty($queruleInfo['qr_id'])){
                                $qr_id = $queruleInfo['qr_id'];
                            }
                            $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'weightage' => $v1['weightage'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption'], 'answer_type' => $v1['answer_type']);
                        }
                        $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                    }
                    
                    $answers = $queansModel->listQueAns('qa.sort_order, qa.qa_id', $v['question_id']);
                    if ($answers){
                        foreach($answers as $k1 => $v1){
                            $queruleInfo = $queruleModel->getQueRule($v['sq_id'], $v1['answer_id']);
                            $qr_id = null;
                            if (! empty($queruleInfo['qr_id'])){
                                $qr_id = $queruleInfo['qr_id'];
                            }
                            $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'weightage' => $v1['weightage'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption'], 'answer_type' => $v1['answer_type']);
                        }
                        $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                    }
                }
                
                if ($arrQue){
                    //$this->p($arrQue);exit;
                    $pg = $this->_getParam('page');
                    $this->view->pg = ! empty($pg) ? $pg : 1;
                    
                    $this->view->paginator = Zend_Paginator::factory($arrQue);
                    $this->view->paginator->setItemCountPerPage(50);
                    $this->view->paginator->setPageRange(PAGE_LINK_COUNT);
                    $this->view->paginator->setCurrentPageNumber($pg);
                } else{
                    $this->view->paginator = null;
                    $this->view->questions = null;
                }
            
            } else{
                $this->_flashMessenger->addMessage('No Questions Found');
            
     //$this->_redirector->gotoUrl ( $this->_modulename . '/survey/index' );
            }
        }
    }

    public function addquestionsAction() {
        $post = $this->getRequest()->getPost();
        
        $surveyQueModel = new Default_Model_SurveyQuestion();
        
        $sid = (int) $this->_getParam('id');
        $cid = (int) $this->_getParam('cid');
        
        if (! empty($post)){
            $survey_questionModel = new Default_Model_SurveyQuestion();
            
            $que_selected = count($post['chk']);
            $success_cnt = 0;
            
            foreach($post['chk'] as $k => $v){
                $result = $survey_questionModel->addSurveyQuestion($v, $post['sid'], 0);
                if ($result){
                    $success_cnt ++;
                }
            }
            $this->_flashMessenger->addMessage($success_cnt . " Questions successfully added to survey");
            $this->_redirector->gotoUrl($this->_modulename . '/survey/questions/id/' . $post['sid']);
        }
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage('Invalid / Missing Survey Id');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/index');
        }
        
        $this->view->pageTitle = ADMIN_TITLE . 'Add Questions To Survey';
        $this->view->pgTitle = 'Add Question To Survey';
        
        $this->view->sid = $sid;
        $this->view->cid = $cid;
        
        $surveyModel = new Default_Model_Survey();
        $surveyInfo = $surveyModel->surveyInfo($sid);
        
        if ($surveyInfo){
            $this->getResponse()->insert('navigation', $this->view->render('survey/surveyquestion_menu.phtml'));
            
            $this->view->surveyinfo = $surveyInfo;
            
            $questions = $surveyQueModel->listAvailableQuestions($sid, $cid);
            
            if ($questions){
                
                $arrQue = array();
                
                $queansModel = new Default_Model_QuestionAnswer();
                $rsaModel = new Default_Model_ResponseSetAnswer();
                
                foreach($questions as $k => $v){
                    
                    $answers = $rsaModel->listQueAns($v['question_id']);
                    
                    $arrAns = array();
                    
                    if ($answers){
                        foreach($answers as $k1 => $v1){
                            $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'weightage' => $v1['weightage'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                        }
                        $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                    }
                    
                    $answers = $queansModel->listQueAns('qa.sort_order, qa.qa_id', $v['question_id']);
                    if ($answers){
                        foreach($answers as $k1 => $v1){
                            $arrAns[] = array('answer_id' => $v1['answer_id'], 'answer_text' => $v1['answer_text'], 'weightage' => $v1['weightage'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                        }
                        $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                    }
                }
                
                if ($arrQue){
                    $pg = $this->_getParam('page');
                    $this->view->pg = ! empty($pg) ? $pg : 1;
                    
                    $this->view->paginator = Zend_Paginator::factory($arrQue);
                    $this->view->paginator->setItemCountPerPage(REC_LIMIT);
                    $this->view->paginator->setPageRange(PAGE_LINK_COUNT);
                    $this->view->paginator->setCurrentPageNumber($pg);
                } else{
                    $this->view->paginator = null;
                    $this->view->questions = null;
                }
                
                $this->view->questions = $arrQue;
            } else{
                $this->view->questions = null;
            }
        } else{
            $this->_flashMessenger->addMessage('Invalid Survey Id');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/index');
        }
    }

    public function giftsdataexportAction() {
        set_time_limit(0);
        
        $sid = 2;
        
        $tStr = '';
        
        $customerModel = new Default_Model_Customer();
        $csModel = new Default_Model_CustomerSurvey();
        
        $customers = $csModel->getCustomerGiftData($sid);
        
        if ($sid == 1){
            $export_file_name = 'gift_info_buy-group.csv';
        } else{
            $export_file_name = 'gift_info_did-not-buy-group.csv';
        }
        
        $export_fp = fopen($export_file_name, 'w');
        
        $info = $customerModel->info();
        
        foreach($info["cols"] as $col){
            if ($col == 'add_date' || $col == 'ctype' || $col == 'last_login_datetime' || $col == 'updated_datetime' || $col == 'status'){
            
            } else{
                $tStr .= $col . ",";
            }
        }
        $tStr .= "\n";
        fputs($export_fp, $tStr);
        
        foreach($customers as $ck => $cv){
            $skip_first_col = true;
            $tStr = '';
            foreach($info["cols"] as $col){
                if ($col == 'add_date' || $col == 'ctype' || $col == 'last_login_datetime' || $col == 'updated_datetime' || $col == 'status'){
                
                } else{
                    $cv[$col] = preg_replace('/\,/', '', $cv[$col]);
                    $tStr .= $cv[$col] . ",";
                }
            }
            $tStr .= "\n";
            fputs($export_fp, $tStr);
        }
        
        fclose($export_fp);
        
        exit();
    
    }

    public function responsesexportAction() {
        set_time_limit(0);
        
        $post = $this->getRequest()->getPost();
        
        $sid = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        
        $customerModel = new Default_Model_Customer();
        $csModel = new Default_Model_CustomerSurvey();
        $responseModel = new Default_Model_Response();
        $surveyQueModel = new Default_Model_SurveyQuestion();
        $queansModel = new Default_Model_QuestionAnswer();
        $rsaModel = new Default_Model_ResponseSetAnswer();
        
        $customers = $responseModel->memberSurveyTaken($sid);
        
        //$export_str = 'sample,Marina,owner,Title,FirstName,Surname,Ad1,Ad2,Ad3,Town,County,Postcode,mobile,phone,Email,telephone,office tel,mobile,telephone type,fax,boat name,BoatType,boat make,BoatLength (m),1,2,3,4,5,6,Start Date,End Date,Marina,Product,Source,status,1,2,3,4,opted out,';
        $export_str = 'inData01,inData02,inData03,inData04,inData05,inData06,inData07,inData08,inData09,inData10,inData11,inData12,inData13,inData14,inData15,inData16,inData17,inData18,inData19,inData20,inData21,inData22,inData23,inData24,inData25,inData26,inData27,inData28,inData29,inData30,inData31,inData32,inData33,inData34,inData35,inData36,inData37,inData38,inData39,inData40,inData41,inData42,dummy01,';
        
        $export_str_1 = 'inData01,inData02,inData03,inData04,inData05,inData06,inData07,inData08,inData09,inData10,inData11,inData12,inData13,inData14,inData15,inData16,inData17,inData18,inData19,inData20,inData21,inData22,inData23,inData24,inData25,inData26,inData27,inData28,inData29,inData30,inData31,inData32,inData33,inData34,inData35,inData36,inData37,inData38,inData39,inData40,inData41,inData42,dummy01,';
        
        $info = $customerModel->info();
        $run_once = true;
        
        if ($sid == 1){
            //$export_file_name = 'responses_buy-' . date("YmdHis") . '.csv';
            $export_file_name = 'responses_buy-final-222.csv';
        } else{
            //$export_file_name = 'responses_did-not-buy-' . date("YmdHis") . '.csv';
            $export_file_name = 'responses_did-not-buy-test1.csv';
        }
        
        $export_fp = fopen($export_file_name, 'w');
        foreach($customers as $ck => $cv){
            $skip_first_col = true;
            $tStr = '';
            foreach($info["cols"] as $col){
                if ($col == 'customer_id' || $col == 'add_date' || $col == 'ctype' || $col == 'last_login_datetime' || $col == 'updated_datetime' || $col == 'status'){
                
                } else{
                    $cv[$col] = preg_replace('/\,/', '', $cv[$col]);
                    $tStr .= $cv[$col] . ",";
                }
            }
            // -- dummy01
            $tStr .= "-,";
            
            $cInfo = $csModel->getCustomerSurveyId($cv['customer_id']);
            $cs_id = $cInfo['cs_id'];
            
            $questions = $surveyQueModel->listSurveyQuestions($cInfo['survey_id'], null, 'response');
            
            $total_cnt = 0;
            
            $acnt = 1;
            
            if ($run_once == true){
                $cnt = 1;
                
                $response_header = '';
                $response_header_1 = '';
                
                foreach($questions as $k => $v){
                    echo ' -- ' . $v['answer_type'] . ' -- question : ' . $v['description'] . "<br><br>";
                    $answers = $rsaModel->listQueAns($v['question_id']);
                    if ($answers){
                        if ($v['answer_type'] == 'r' || $v['answer_type'] == 'm'){
                            foreach($answers as $ak => $av){
                                $av['answer_text'] = preg_replace('/\,/', '', $av['answer_text']);
                                $response_header .= "survey" . $cnt . ",";
                                $response_header_1 .= $av['answer_text'] . ",";
                                echo $acnt . " >> if 1 - " . $av['answer_text'] . '<br>';
                                $acnt ++;
                                $cnt ++;
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63){
                                    $av['answer_text'] = preg_replace('/\,/', '', $av['answer_text']);
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $response_header .= "survey" . $cnt . ",";
                                    $response_header_1 .= "other-text-1,";
                                    echo $acnt . " >> if if 1 - " . $av['answer_text'] . '<br>';
                                    $acnt ++;
                                    $cnt ++;
                                }
                            }
                        } else{
                            $answers[0]['answer_text'] = preg_replace('/\,/', '', $answers[0]['answer_text']);
                            $response_header .= "survey" . $cnt . ",";
                            $response_header_1 .= $answers[0]['answer_text'] . ",";
                            echo $acnt . ' - else 1 - single selection <br>';
                            $acnt ++;
                            $cnt ++;
                            foreach($answers as $ak => $av){
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63){
                                    $av['answer_text'] = preg_replace('/\,/', '', $av['answer_text']);
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $response_header .= "survey" . $cnt . ",";
                                    $response_header_1 .= "other-text-2,";
                                    echo $acnt . ' -- else else 1 - ' . $av['answer_text'] . '<br>';
                                    $acnt ++;
                                    $cnt ++;
                                }
                            }
                        }
                    }
                    $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                    if ($answers){
                        if ($v['answer_type'] == 'r' || $v['answer_type'] == 'm'){
                            foreach($answers as $ak => $av){
                                $av['answer_text'] = preg_replace('/\,/', '', $av['answer_text']);
                                $response_header .= "survey" . $cnt . ",";
                                $response_header_1 .= $av['answer_text'] . ",";
                                echo $acnt . " - if 2 - " . $av['answer_text'] . '<br>';
                                $acnt ++;
                                $cnt ++;
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63){
                                    $av['answer_text'] = preg_replace('/\,/', '', $av['answer_text']);
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $response_header .= "survey" . $cnt . ",";
                                    $response_header_1 .= "other-text-3,";
                                    echo $acnt . " --  if if 2 - " . $av['answer_text'] . '<br>';
                                    $acnt ++;
                                    $cnt ++;
                                }
                            }
                        } else{
                            $answers[0]['answer_text'] = preg_replace('/\,/', '', $answers[0]['answer_text']);
                            $response_header .= "survey" . $cnt . ",";
                            $response_header_1 .= $answers[0]['answer_text'] . ",";
                            echo $acnt . ' --  else 2 - single selection <br>';
                            $acnt ++;
                            $cnt ++;
                            foreach($answers as $ak => $av){
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63){
                                    $av['answer_text'] = preg_replace('/\,/', '', $av['answer_text']);
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $response_header .= "survey" . $cnt . ",";
                                    $response_header_1 .= "other-text-4,";
                                    echo $acnt . ' -- else else 2 - ' . $av['answer_text'] . '<br>';
                                    $acnt ++;
                                    $cnt ++;
                                }
                            }
                        }
                    }
                }
                
                $response_header .= 'contactdetails01,contactdetails02,contactdetails03,contactdetails04,contactdetails05,contactdetails06,contactdetails07,contactdetails08,contactdetails09,contactdetails10,contactdetails11';
                $response_header_1 .= 'contactdetails01,contactdetails02,contactdetails03,contactdetails04,contactdetails05,contactdetails06,contactdetails07,contactdetails08,contactdetails09,contactdetails10,contactdetails11';
                
                //$response_header = substr($response_header, 0, strlen($response_header) - 1);
                

                $export_str .= $response_header . "\n";
                $export_str_1 .= $response_header_1 . "\n";
                
                fputs($export_fp, $export_str);
                
                fputs($export_fp, $export_str_1);
                
                $run_once = false;
            }
            
            echo $cv['customer_id'] . " - " . $cv['title'] . " - " . $cv['firstname'] . " - " . $cv['surname'] . "<br><br>";
            
            // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
            $cnt = 0;
            $acnt = 1;
            foreach($questions as $k => $v){
                $cnt ++;
                echo $cnt . ' -- ' . $v['answer_type'] . ' -- question : ' . $v['description'] . "<br><br>";
                $answers = $rsaModel->listQueAns($v['question_id']);
                
                if ($answers){
                    if ($v['answer_type'] == 's'){
                        echo $acnt . " -- ";
                        $acnt ++;
                    }
                    foreach($answers as $ak => $av){
                        $value = null;
                        if ($v['answer_type'] == 'r' || $v['answer_type'] == 'm'){
                            echo $acnt . " -- ";
                            $acnt ++;
                        }
                        if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                            echo $acnt . " -- ";
                            $acnt ++;
                        }
                        echo $av['answer_text'] . " * *  " . $av['answer_value'] . " *** " . $av['answer_id'] . " ***  ";
                        $response = $responseModel->getCustomerSurveyResponses($cs_id, $cv['customer_id'], $v['sq_id'], $av['answer_id']);
                        if ($response){
                            echo $response['answer_text'] . ' *##* * * * * * * *  ';
                            $value = $response['answer_text'];
                            if (is_null($value)){
                                $value = '-';
                            }
                            $yFlag = false;
                            $xFlag = false;
                            if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                                //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                $yFlag = true;
                                if (! empty($value)){
                                    $xArr = explode("^", $value);
                                    if (count($xArr) > 1){
                                        $value = $xArr[1];
                                        $xFlag = true;
                                    }
                                }
                            }
                            $value = preg_replace('/\,/', '', $value);
                            $value = preg_replace("/[\n\r]/", "", $value);
                            
                            if ($yFlag == true && $xFlag == false){
                                $value = '0,' . $value;
                            }
                            
                            if ($yFlag == true && $xFlag == true){
                                $value = '88,' . $value;
                            }
                        } else{
                            if ($v['answer_type'] == 'm'){
                                $value = "0";
                                echo 'else part value = 0 ^^^^';
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                                    $value .= ',-';
                                }
                            } else{
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $value = '-';
                                }
                            }
                        }
                        if (trim($value) != ''){
                            echo 'else part value = 0 ***^^^^ ';
                            $tStr .= $value . ",";
                        } else if ($av['answer_id'] == 16 && (empty($value) || trim($value) == '')){
                            $tStr .= '-' . ",";
                            echo 'else part value = 0 *** ^%^%^%^ ';
                        }
                        echo "<br><br>";
                    }
                }
                
                $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                if ($answers){
                    if ($v['answer_type'] == 's'){
                        echo $acnt . " -- ";
                        $acnt ++;
                    }
                    $xValue = false;
                    foreach($answers as $ak => $av){
                        $value = null;
                        if ($v['answer_type'] == 'r' || $v['answer_type'] == 'm'){
                            echo $acnt . " -- ";
                            $acnt ++;
                        }
                        if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                            echo $acnt . " -- ";
                            $acnt ++;
                        }
                        
                        echo $av['answer_text'] . " ~~~~~~ " . $av['answer_value'] . " ~~~" . $av['answer_id'] . " ~~~ ";
                        $response = $responseModel->getCustomerSurveyResponses($cs_id, $cv['customer_id'], $v['sq_id'], $av['answer_id']);
                        if ($response){
                            echo $response['answer_text'] . ' *##* >>>>>>>>>> ';
                            $value = $response['answer_text'];
                            if ($v['question_id'] == 36){
                                if (! empty($value)){
                                    $value = '-';
                                }
                            } else{
                                if (is_null($value)){
                                    $value = '-';
                                }
                            }
                            if (! empty($response['answer_text'])){
                                $xValue = true;
                            }
                            
                            $yFlag = false;
                            $xFlag = false;
                            if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                                //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                $yFlag = true;
                                if (! empty($value)){
                                    $xArr = explode("^", $value);
                                    if (count($xArr) > 1){
                                        $value = $xArr[1];
                                        $xFlag = true;
                                    }
                                }
                            }
                            $value = preg_replace('/\,/', '', $value);
                            $value = preg_replace("/[\n\r]/", "", $value);
                            if ($yFlag == true && $xFlag == false){
                                $value = '0,' . $value;
                            }
                            
                            if ($yFlag == true && $xFlag == true){
                                $value = '88,' . $value;
                            }
                        } else{
                            if ($v['answer_type'] == 'm'){
                                $value = "0";
                                echo 'else part value = 0 @@@@';
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                                    $value .= ',-';
                                    $xValue = true;
                                }
                            } else{
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67 || $av['answer_id'] == 40 || $av['answer_id'] == 63 && $av['answer_id'] != 101){
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $value = '-';
                                    $xValue = true;
                                }
                            }
                        }
                        if (trim($value) != ''){
                            echo 'else part value = 0 *** @@@@ -- : ' . $value;
                            $tStr .= $value . ",";
                            $xValue = true;
                        } else if ($av['answer_id'] == 16 && (empty($value) || trim($value) == '')){
                            if (! empty($value)){
                                $tStr .= '-' . ",";
                                echo 'else part value = 0 *** @@@@ %%%%%% RRR  --- : ' . $value;
                                $xValue = true;
                            }
                        } else{
                        }
                        echo "<br><br>";
                    }
                    if ($xValue == false){
                        $tStr .= '-' . ",";
                        echo ' $xValue = FALSE ';
                    }
                
                }
                echo '<hr>';
            }
            
            $tStr = stripslashes($tStr);
            
            $tStr .= $cv['title'] . ',' . $cv['firstname'] . ',' . $cv['surname'] . ',' . $cv['address1'] . ',' . $cv['address2'] . ',' . $cv['address3'] . ',' . $cv['address4'] . ',' . $cv['address5'] . ',' . $cv['address6'] . ',' . $cv['residence_country'] . ',' . $cv['email'];
            
            //$tStr = substr($tStr, 0, strlen($tStr) - 1);
            $export_str = $tStr . "\n";
            
            fputs($export_fp, $export_str);
        
     //ob_clean();
        

        //flush();
        

        }
        fclose($export_fp);
        
        exit();
    }

    public function responsesexportAction_1() {
        set_time_limit(0);
        
        $post = $this->getRequest()->getPost();
        
        $sid = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        
        $customerModel = new Default_Model_Customer();
        $csModel = new Default_Model_CustomerSurvey();
        $responseModel = new Default_Model_Response();
        $surveyQueModel = new Default_Model_SurveyQuestion();
        $queansModel = new Default_Model_QuestionAnswer();
        $rsaModel = new Default_Model_ResponseSetAnswer();
        
        $customers = $responseModel->memberSurveyTaken($sid);
        
        //$export_str = 'sample,Marina,owner,Title,FirstName,Surname,Ad1,Ad2,Ad3,Town,County,Postcode,mobile,phone,Email,telephone,office tel,mobile,telephone type,fax,boat name,BoatType,boat make,BoatLength (m),1,2,3,4,5,6,Start Date,End Date,Marina,Product,Source,status,1,2,3,4,opted out,';
        $export_str = 'inData01,inData02,inData03,inData04,inData05,inData06,inData07,inData08,inData09,inData10,inData11,inData12,inData13,inData14,inData15,inData16,inData17,inData18,inData19,inData20,inData21,inData22,inData23,inData24,inData25,inData26,inData27,inData28,inData29,inData30,inData31,inData32,inData33,inData34,inData35,inData36,inData37,inData38,inData39,inData40,inData41,inData42,dummy01,';
        
        $info = $customerModel->info();
        $run_once = true;
        
        if ($sid == 1){
            //$export_file_name = 'responses_buy-' . date("YmdHis") . '.csv';
            $export_file_name = 'responses_buy.csv';
        } else{
            //$export_file_name = 'responses_did-not-buy-' . date("YmdHis") . '.csv';
            $export_file_name = 'responses_did-not-buy.csv';
        }
        
        $export_fp = fopen($export_file_name, 'w');
        foreach($customers as $ck => $cv){
            $skip_first_col = true;
            $tStr = '';
            foreach($info["cols"] as $col){
                if ($col == 'customer_id' || $col == 'add_date' || $col == 'ctype' || $col == 'last_login_datetime' || $col == 'updated_datetime' || $col == 'status'){
                
                } else{
                    $cv[$col] = preg_replace('/\,/', '', $cv[$col]);
                    $tStr .= $cv[$col] . ",";
                }
            }
            // -- dummy01
            $tStr .= "-,";
            
            $cInfo = $csModel->getCustomerSurveyId($cv['customer_id']);
            $cs_id = $cInfo['cs_id'];
            
            $questions = $surveyQueModel->listSurveyQuestions($cInfo['survey_id'], null, 'response');
            
            $total_cnt = 0;
            
            if ($run_once == true){
                
                $cnt = 1;
                
                $response_header = '';
                
                foreach($questions as $k => $v){
                    $answers = $rsaModel->listQueAns($v['question_id']);
                    if ($answers){
                        if ($v['answer_type'] == 'r' || $v['answer_type'] == 'm'){
                            foreach($answers as $ak => $av){
                                $response_header .= "survey" . $cnt . ",";
                                $cnt ++;
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67){
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $response_header .= "survey" . $cnt . ",";
                                    $cnt ++;
                                }
                            }
                        } else{
                            $response_header .= "survey" . $cnt . ",";
                            $cnt ++;
                            foreach($answers as $ak => $av){
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67){
                                    //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                    $response_header .= "survey" . $cnt . ",";
                                    $cnt ++;
                                }
                            }
                        }
                    }
                    $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                    if ($answers){
                        if ($v['answer_type'] == 'r' || $v['answer_type'] == 'm'){
                            foreach($answers as $ak => $av){
                                $response_header .= "survey" . $cnt . ",";
                                $cnt ++;
                                //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67){
                                    $response_header .= "survey" . $cnt . ",";
                                    $cnt ++;
                                }
                            }
                        } else{
                            $response_header .= "survey" . $cnt . ",";
                            $cnt ++;
                            foreach($answers as $ak => $av){
                                //if($av['answer_value'] == 88 && $av['answer_id'] != 101 ){
                                if ($av['answer_id'] == 24 || $av['answer_id'] == 67){
                                    $response_header .= "survey" . $cnt . ",";
                                    $cnt ++;
                                }
                            }
                        }
                    }
                }
                
                $response_header .= 'contactdetails01,contactdetails02,contactdetails03,contactdetails04,contactdetails05,contactdetails06,contactdetails07,contactdetails08,contactdetails09,contactdetails10,contactdetails11';
                
                //$response_header = substr($response_header, 0, strlen($response_header) - 1);
                

                $export_str .= $response_header . "\n";
                fputs($export_fp, $export_str);
                $run_once = false;
            }
            
            // * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
            $cnt = 0;
            foreach($questions as $k => $v){
                $cnt ++;
                //echo $cnt . ' -- '.$v ['answer_type'].' -- question : ' . $v['description'] . "<br><br>";
                $answers = $rsaModel->listQueAns($v['question_id']);
                
                if ($answers){
                    foreach($answers as $ak => $av){
                        $value = null;
                        //echo $av['answer_text'] . " * *  " . $av['answer_value'] . " * *  ";
                        $response = $responseModel->getCustomerSurveyResponses($cs_id, $cv['customer_id'], $v['sq_id'], $av['answer_id']);
                        if ($response){
                            //echo $response['answer_text'] . ' *##* * * * * * * *  ';
                            $value = $response['answer_text'];
                            if (is_null($value)){
                                $value = '-';
                            }
                            $yFlag = false;
                            $xFlag = false;
                            if ($av['answer_value'] == 88 && $av['answer_id'] != 101){
                                $yFlag = true;
                                if (! empty($value)){
                                    $xArr = explode("^", $value);
                                    if (count($xArr) > 1){
                                        $value = $xArr[1];
                                        $xFlag = true;
                                    }
                                }
                            }
                            $value = preg_replace('/\,/', '', $value);
                            $value = preg_replace("/[\n\r]/", "", $value);
                            
                            if ($yFlag == true && $xFlag == false){
                                $value = '0,' . $value;
                            }
                            
                            if ($yFlag == true && $xFlag == true){
                                $value = '88,' . $value;
                            }
                        } else{
                            if ($v['answer_type'] == 'm'){
                                $value = "0";
                            
     //echo 'else part value = 0 ^^^^';
                            }
                            if ($av['answer_value'] == 88 && $av['answer_id'] != 101){
                                $value = '-';
                            }
                        }
                        if (trim($value) != ''){
                            //echo 'else part value = 0 ***^^^^ ';
                            $tStr .= $value . ",";
                        } else{
                            //if( empty($value) || trim($value) == '' ){
                            if ($av['answer_id'] == 16 && (empty($value) || trim($value) == '')){
                                $tStr .= '-' . ",";
                            
     //echo 'else part value = 0 *** ^%^%^%^ ';
                            }
                        }
                    
     //echo "<br><br>";
                    }
                }
                
                $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                if ($answers){
                    foreach($answers as $ak => $av){
                        $value = null;
                        //echo $av['answer_text'] . " ~~~~~~ ". $av['answer_value'] . " ~~~~~~ ";
                        $response = $responseModel->getCustomerSurveyResponses($cs_id, $cv['customer_id'], $v['sq_id'], $av['answer_id']);
                        if ($response){
                            //echo $response['answer_text'] . ' *##* >>>>>>>>>> ';
                            $value = $response['answer_text'];
                            if (is_null($value)){
                                $value = '-';
                            }
                            $yFlag = false;
                            $xFlag = false;
                            if ($av['answer_value'] == 88 && $av['answer_id'] != 101){
                                $yFlag = true;
                                if (! empty($value)){
                                    $xArr = explode("^", $value);
                                    if (count($xArr) > 1){
                                        $value = $xArr[1];
                                        $xFlag = true;
                                    }
                                }
                            }
                            $value = preg_replace('/\,/', '', $value);
                            $value = preg_replace("/[\n\r]/", "", $value);
                            if ($yFlag == true && $xFlag == false){
                                $value = '0,' . $value;
                            }
                            
                            if ($yFlag == true && $xFlag == true){
                                $value = '88,' . $value;
                            }
                        } else{
                            if ($v['answer_type'] == 'm'){
                                $value = "0";
                            
     //echo 'else part value = 0 @@@@';
                            }
                            if ($av['answer_value'] == 88 && $av['answer_id'] != 101){
                                $value = '-';
                            }
                        }
                        if (trim($value) != ''){
                            //echo 'else part value = 0 *** @@@@ ';
                            $tStr .= $value . ",";
                        } else{
                            //if( empty($value) || trim($value) == '' ){
                            if ($av['answer_id'] == 16 && (empty($value) || trim($value) == '')){
                                $tStr .= '-' . ",";
                            
     //echo 'else part value = 0 *** @@@@ %%%%%% ';
                            }
                        }
                    
     //echo "<br><br>";
                    }
                }
            
     //echo '<hr>';
            }
            
            $tStr .= $cv['title'] . ',' . $cv['firstname'] . ',' . $cv['surname'] . ',' . $cv['address1'] . ',' . $cv['address2'] . ',' . $cv['address3'] . ',' . $cv['address4'] . ',' . $cv['address5'] . ',' . $cv['address6'] . ',' . $cv['residence_country'] . ',' . $cv['email'];
            
            //$tStr = substr($tStr, 0, strlen($tStr) - 1);
            $export_str = $tStr . "\n";
            
            fputs($export_fp, $export_str);
        
     //ob_clean();
        

        //flush();
        }
        
        fclose($export_fp);
        exit();
        
        $fsize = filesize($export_file_name);
        header("Pragma: no-cache"); // required
        header('Content-disposition: attachment; filename=' . $export_file_name);
        header("Content-length: $fsize");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 31 Dec 2011 05:00:00 GMT");
        header('Content-type: application/csv');
        ob_clean();
        flush();
        readfile($export_file_name);
        
        exit();
    }

    public function responsesAction() {
        $post = $this->getRequest()->getPost();
        
        $sid = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        $export_data = false;
        
        $detailed = null;
        if (! empty($post)){
            $detailed = $post['detailed'];
            $start_date = date("Y-m-d", strtotime($post['start_date']));
            $end_date = date("Y-m-d", strtotime($post['end_date']));
        } else{
            $start_date = date("Y-m-d");
            $end_date = date("Y-m-d");
        }
        
        $this->view->pg = ! empty($pg) ? $pg : 1;
        $this->view->detailed = $detailed;
        if ($start_date > $end_date){
            $this->_flashMessenger->addMessage('Invalid date value encountered. From date should be greater then To date.');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/responses/id/' . $sid . '/page/' . $pg);
        }
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage('Invalid / Missing Survey Id');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            if ($surveyInfo){
                $this->view->surveyinfo = $surveyInfo;
                $this->view->pageTitle = ADMIN_TITLE . 'Survey Response Analysis';
                $this->view->pgTitle = 'Survey Response Analysis';
                $this->getResponse()->insert('navigation', $this->view->render('survey/surveyresponse_menu.phtml'));
                $responseModel = new Default_Model_Response();
                $surveyQueModel = new Default_Model_SurveyQuestion();
                $questions = $surveyQueModel->listSurveyQuestions($sid, null, 'response');
                if ($questions){
                    $queansModel = new Default_Model_QuestionAnswer();
                    $arrQue = array();
                    $rsaModel = new Default_Model_ResponseSetAnswer();
                    
                    $start_date = date("d M Y");
                    $this->view->start_date = $start_date;
                    
                    $end_date = date("d M Y");
                    $this->view->end_date = $end_date;
                    
                    $pArr = array('filter' => 'dt', 'value' => array('st' => $start_date, 'en' => $end_date));
                    
                    if (! empty($post['cmbSubmit'])){
                        $start_date = $post['start_date'];
                        $this->view->start_date = $start_date;
                        
                        $end_date = $post['end_date'];
                        $this->view->end_date = $end_date;
                        
                        $pArr = array('filter' => 'dt', 'value' => array('st' => $start_date, 'en' => $end_date));
                    }
                    
                    //$this->p($pArr);
                    

                    $surveyTakenCount = $responseModel->surveyTakenCount($sid);
                    $this->_session->pArr = $pArr;
                    
                    $surveyCount = 0;
                    $this->view->surveyTakenCount = 0;
                    
                    if (! empty($surveyTakenCount)){
                        $surveyCount = $surveyTakenCount['cnt'];
                        $this->view->surveyTakenCount = $surveyCount;
                    }
                    //echo $surveyCount;exit;
                    

                    $sr_response = array(stripslashes($surveyInfo['title']), $start_date, $end_date, $surveyCount);
                    
                    $answer_str = '';
                    
                    $que_counter = 0;
                    
                    foreach($questions as $k => $v){
                        $answers = $rsaModel->listQueAns($v['question_id']);
                        $arrAns = array();
                        
                        $que_counter ++;
                        
                        $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr);
                        $que_response_count = $que_response_res['cnt'];
                        if ($answers){
                            
                            foreach($answers as $k1 => $v1){
                                $percentage = 0;
                                $selectedCount = 0;
                                $ansChecked = false;
                                $ansText = null;
                                if (empty($detailed)){
                                    
                                    if ($v['answer_type'] == 'r'){
                                        
                                        $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], $v1['answer_id'], $pArr);
                                        $que_response_count = $que_response_res['cnt'];
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-2', $pArr);
                                            }
                                        }
                                        
                                        $not_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $not_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-1', $pArr);
                                            }
                                        }
                                        
                                        $little_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $little_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        }
                                        
                                        $moderate_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $moderate_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        }
                                        
                                        $major_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $major_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        }
                                        
                                        $extremely_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $extremely_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
     //echo '$$ --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['ansText'] . '<br><br>';
                                    } else{
                                        if ($v['answer_type'] == 'm'){
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr, 'm');
                                            $que_response_count = $que_response_res['cnt'];
                                        } else{
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr);
                                            $que_response_count = $que_response_res['cnt'];
                                        }
                                        
                                        $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], null, $pArr);
                                        
                                        $percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($que_response_count)){
                                                $percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
     //echo '$$ 11 --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['answer_text'] . ' -- percentage : ' . $percentage . '<br><br>';
                                    }
                                
                                } else{ // ----------------- else part ----------- if (empty ( $detailed )) no 1 {
                                    

                                    if ($v['answer_type'] == 'r'){
                                        $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], $v1['answer_id'], $pArr);
                                        $que_response_count = $que_response_res['cnt'];
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-2', $pArr);
                                            }
                                        }
                                        $not_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $not_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-1', $pArr);
                                            }
                                        }
                                        
                                        $little_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $little_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        }
                                        
                                        $moderate_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $moderate_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        }
                                        
                                        $major_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $major_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        }
                                        
                                        $extremely_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $extremely_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
     //echo '$$ --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['ansText'] . '<br><br>';
                                    } else{
                                        
                                        if ($v['answer_type'] == 'm'){
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr, 'm');
                                            $que_response_count = $que_response_res['cnt'];
                                        } else{
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr);
                                            $que_response_count = $que_response_res['cnt'];
                                        }
                                        $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], null, $pArr);
                                        //echo '$$ --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['answer_text'] . '<br><br>';
                                        

                                        $percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (!empty($surveyCount) && $que_response_count > 0 ){
                                                //$percentage = ($ansCnt ['cnt'] * 100) / $surveyCount;
                                                $percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
                                    }
                                
     // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                                

                                }
                                
                                $arrAns[] = array('answer_id' => $v1['answer_id'], 'selected_cnt' => $selectedCount, 'percentage' => $percentage, 'not_percentage' => $not_percentage, 'little_percentage' => $little_percentage, 'moderate_percentage' => $moderate_percentage, 'major_percentage' => $major_percentage, 'extremely_percentage' => $extremely_percentage, 'ansChecked' => $ansChecked, 'ansText' => $ansText, 'answer_type' => $v1['answer_type'], 'free_text' => $v1['free_text'], 'answer_text' => $v1['answer_text'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                            
                            } //  .... end ... foreach ( $answers as $k1 => $v1 ) {
                            

                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                        
                        $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                        if ($answers){
                            
                            foreach($answers as $k1 => $v1){
                                $answer_str = '';
                                //$answer_str_1 = '';
                                $percentage = 0;
                                $selectedCount = 0;
                                $ansChecked = false;
                                $ansText = null;
                                $yes_percentage = 0;
                                $not_percentage = 0;
                                $na_percentage = 0;
                                $little_percentage = 0;
                                $moderate_percentage = 0;
                                $major_percentage = 0;$extremely_percentage = 0;
                                if (empty($detailed)){
                                    
                                    if ($v['answer_type'] == 'r'){
                                        
                                        $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], $v1['answer_id'], $pArr);
                                        $que_response_count = $que_response_res['cnt'];
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-2', $pArr);
                                            }
                                        }
                                        
                                     //   $not_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $not_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-1', $pArr);
                                            }
                                        }
                                        
                                       
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $little_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        }
                                        
                                    //    $moderate_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $moderate_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        }
                                        
                                   //     $major_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $major_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        }
                                        
                                        
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $extremely_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
     //echo '@@ --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['ansText'] . '<br><br>';
                                    

                                    } else{
                                        if ($v['answer_type'] == 'm'){
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr, 'm');
                                            $que_response_count = $que_response_res['cnt'];
                                        } else{
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr);
                                            $que_response_count = $que_response_res['cnt'];
                                        }
                                        
                                        $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], null, $pArr);
                                        
                                  //      $percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($que_response_count)){
                                                $percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    }
                                
                                } else{ // ----------------- else part ----------- if (empty ( $detailed )) no 2 {
                                    

                                    if ($v['answer_type'] == 'r'){
                                        $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], $v1['answer_id'], $pArr);
                                        $que_response_count = $que_response_res['cnt'];
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-2', $pArr);
                                            }
                                        }
                                        
                               
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $not_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '-1', $pArr);
                                            }
                                        }
                                        
                                  //      $little_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $little_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '0', $pArr);
                                            }
                                        }
                                        
                                    //    $moderate_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $moderate_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '3', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '1', $pArr);
                                            }
                                        }
                                        
                                   //     $major_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $major_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                        if ($v['question_id'] == 1 || $v['question_id'] == 33){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '4', $pArr);
                                            }
                                        } else if ($v['question_id'] == 3 || $v['question_id'] == 2 || $v['question_id'] == 35 || $v['question_id'] == 34){
                                            if ($sid == 1){
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '5', $pArr);
                                            } else{
                                                $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], '2', $pArr);
                                            }
                                        }
                                        
                                   //     $extremely_percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $extremely_percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
     //echo '$$ --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['ansText'] . '<br><br>';
                                    } else{
                                        if ($v['answer_type'] == 'm'){
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr, 'm');
                                            $que_response_count = $que_response_res['cnt'];
                                        } else{
                                            $que_response_res = $responseModel->queResponseCount($sid, $v['question_id'], null, $pArr);
                                            $que_response_count = $que_response_res['cnt'];
                                        }
                                        
                                        $ansCnt = $responseModel->ansSelectedCount($sid, $v['question_id'], $v1['answer_id'], null, $pArr);
                                        
                                        //echo '$$ xxx --- answer id : ' . $v1['answer_id'] . ' --- ans count : ' . $ansCnt['cnt'] . ' ---  answer_text : ' .$v1['answer_text'] . '<br><br>';
                                        

                                   //     $percentage = 0;
                                        if (! empty($ansCnt['cnt'])){
                                            $selectedCount = $ansCnt['cnt'];
                                            if (! empty($surveyCount)){
                                                $percentage = ($ansCnt['cnt'] * 100) / $que_response_count;
                                            }
                                        }
                                    
                                    }
                                
     // @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
                                }
                                
                                $arrAns[] = array('answer_id' => $v1['answer_id'], 'selected_cnt' => $selectedCount, 'percentage' => $percentage, 'not_percentage' => $not_percentage, 'little_percentage' => $little_percentage, 'moderate_percentage' => $moderate_percentage, 'major_percentage' => $major_percentage, 'extremely_percentage' => $extremely_percentage, 'ansChecked' => $ansChecked, 'ansText' => $ansText, 'answer_type' => $v1['answer_type'], 'free_text' => $v1['free_text'], 'answer_text' => $v1['answer_text'], 'free_text' => $v1['free_text'], 'free_text_caption' => $v1['custom_free_text_caption']);
                            }
                            $arrQue[] = array('que_info' => $v, 'answers' => $arrAns);
                        }
                    }
                    
                    //$this->p($arrQue);exit;
                    $this->view->filter = $this->_getParam('cmbFilter');
                    $this->view->questions = $arrQue;
                    $this->_session->response_results = $arrQue;
                } else{
                    $this->view->questions = null;
                }
            } else{
                $this->_flashMessenger->addMessage('Invalid Survey Id');
                $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
            }
        }
    
     //Select * from response where sq_id IN ( Select sq_id from survey_question where survey_id = 5 )
    }

    public function responsesAction_old() {
        $post = $this->getRequest()->getPost();
        
        $sid = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        $export_data = false;
        
        $detailed = null;
        if (! empty($post)){
            $detailed = $post['detailed'];
            $start_date = date("Y-m-d", strtotime($post['start_date']));
            $end_date = date("Y-m-d", strtotime($post['end_date']));
        } else{
            $start_date = date("Y-m-d");
            $end_date = date("Y-m-d");
        }
        
        $this->view->pg = ! empty($pg) ? $pg : 1;
        $this->view->detailed = $detailed;
        
        if ($start_date > $end_date){
            $this->_flashMessenger->addMessage('Invalid date value encountered. From date should be greater then To date.');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/responses/id/' . $sid . '/page/' . $pg);
        }
        
        if (empty($sid)){
            $this->_flashMessenger->addMessage('Invalid / Missing Survey Id');
            $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
        } else{
            $surveyModel = new Default_Model_Survey();
            $surveyInfo = $surveyModel->surveyInfo($sid);
            
            if ($surveyInfo){
                $this->view->surveyinfo = $surveyInfo;
                
                $this->view->pageTitle = ADMIN_TITLE . 'Survey Response Analysis';
                $this->view->pgTitle = 'Survey Response Analysis';
                
                $this->getResponse()->insert('navigation', $this->view->render('survey/surveyresponse_menu.phtml'));
                
                $responseModel = new Default_Model_Response();
                
                $surveyQueModel = new Default_Model_SurveyQuestion();
                $questions = $surveyQueModel->listSurveyQuestions($sid, null, 'response');
                
                if ($questions){
                    $queansModel = new Default_Model_QuestionAnswer();
                    $arrQue = array();
                    $rsaModel = new Default_Model_ResponseSetAnswer();
                    
                    $surveyTakenCount = $responseModel->surveyTakenCount($sid);
                    
                    $surveyCount = 0;
                    $this->view->surveyTakenCount = 0;
                    
                    if (! empty($surveyTakenCount)){
                        $surveyCount = $surveyTakenCount['cnt'];
                        $this->view->surveyTakenCount = $surveyCount;
                    }
                    
                    $answer_str = '';
                    
                    $que_counter = 0;
                    
                    foreach($questions as $k => $v){
                        
                        $answers = $rsaModel->listQueAns($v['question_id']);
                        $arrAns = array();
                        
                        if ($answers){
                            
                            foreach($answers as $k1 => $v1){
                            
                            }
                        }
                        
                        $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
                        
                        if ($answers){
                            
                            foreach($answers as $k1 => $v1){
                            
                            }
                        
                        }
                    
                    }
                    
                    //$this->p($arrQue);exit;
                    $this->view->questions = $arrQue;
                } else{
                    $this->view->questions = null;
                }
            } else{
                $this->_flashMessenger->addMessage('Invalid Survey Id');
                $this->_redirector->gotoUrl($this->_modulename . '/survey/index/page/' . $pg);
            }
        }
    
     //Select * from response where sq_id IN ( Select sq_id from survey_question where survey_id = 5 )
    }

    public function anstextAction() {
        //$this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(true);
        $this->view->skipLayout = true;
        
        $sid = (int) $this->_getParam('id');
        $aid = (int) $this->_getParam('aid');
        $sqid = (int) $this->_getParam('qid');
        
        $this->view->error_flag = false;
        
        if (! empty($sid) && ! empty($aid)){
            $responseModel = new Default_Model_Response();
            $res_answerText = $responseModel->getSurverAnswerText($sid, $aid, $sqid, $this->_session->pArr);
            
            if ($res_answerText){
                $this->view->answertext = $res_answerText;
            } else{
                $this->view->answertext = null;
            }
        } else{
            $this->view->error_flag = true;
        }
    }

    public function selectioncriteriaAction() {
        $post = $this->getRequest()->getPost();
        
        $id = (int) $this->_getParam('id');
        
        $pg = (int) $this->_getParam('page');
        
        $cnt = (int) $this->_getParam('cnt');
        $this->view->cnt = $cnt;
        
        $pg = (! empty($pg)) ? $pg : 1;
        $this->view->pg = $pg;
        
        $surveyModel = new Default_Model_Survey();
        
        if (! empty($post)){
            
            $surveyModel = new Default_Model_Survey();
            
            // Generate Array for Custom Survey Parameters  --- S T A R T //
            $udfArr = $this->generateUDF($post);
            // Generate Array for Custom Survey Parameters  --- E N D //
            

            // Sample frames Age -- S T A R T //
            

            $arrAgeBand = array();
            if (! empty($post['chkAB'])){
                foreach($post['chkAB'] as $k => $v){
                    $tmp = 'txtAgeCSS' . $v;
                    $txtAgeCSS = $post[$tmp];
                    
                    $tmp = 'txtAgeCMF' . $v;
                    $txtAgeCMF = $post[$tmp];
                    
                    array_push($arrAgeBand, array($v, $txtAgeCSS, $txtAgeCMF));
                }
            }
            
            // Sample frames Age -- E N D //
            

            //$parameters = array ('post_code' => !empty($post ['post_code'])?$post ['post_code']:null, 'gender' => $post ['gender'], 'level_id' => $post ['level_id'], 'min_age' => ! empty ( $post ['min_age'] ) ? $post ['min_age'] : null, 'max_age' => ! empty ( $post ['max_age'] ) ? $post ['max_age'] : null, 'udf' => $udfArr );
            $parameters = array('post_code' => ! empty($post['post_code']) ? $post['post_code'] : null, 'gender' => $post['gender'], 'level_id' => $post['level_id'], 'udf' => $udfArr, 'ageband' => $arrAgeBand);
            
            $post['parameters'] = serialize($parameters);
            
            $survey_result = $surveyModel->updateSurveySelectionParameters($post);
            
            $urlAddOn = null;
            
            if ($survey_result){
                if (! empty($post['cmdMemberSampleCount'])){
                    $count = $this->_helper->sendnotification(array('sid' => $post['id'], 'flag' => true));
                    $urlAddOn = "/cnt/" . $count;
                }
                if (! empty($post['cmdSubmit'])){
                    $this->_flashMessenger->addMessage('Survey audience selection criteria updated successfully.');
                }
            } else{
                $this->_flashMessenger->addMessage('Error(s) encountered. Survey audience selection criteria not updated.');
            }
            $this->_redirector->gotoUrl($this->_modulename . '/survey/selectioncriteria/id/' . $post['id'] . '/page/' . $post['pg'] . $urlAddOn);
        }
        
        $surveyInfo = $surveyModel->surveyInfo($id);
        $cid = $surveyInfo['client_id'];
        
        $parameters = @unserialize($surveyInfo['parameters']);
        
        $this->view->surveyinfo = $surveyInfo;
        $this->view->parameters = $parameters;
        
        //$this->p($parameters);
        

        $arr = $this->get_udf_fields();
        
        //$this->p($arr);
        

        $this->view->arr = $arr;
        
        $memberLevelModel = new Default_Model_MemberLevel();
        $this->view->memberLevel = $memberLevelModel->listMemberLevel();
        
        $ageBandModel = new Default_Model_AgeBands();
        $this->view->ageBands = $ageBandModel->listAllAgeBands();
        
        $this->view->pageTitle = ADMIN_TITLE . 'Survey Audience Selection Criteria';
        $this->view->pgTitle = 'Survey Audience Selection Criteria';
        
        $this->getResponse()->insert('navigation', $this->view->render('survey/survey_menu.phtml'));
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

    public function updatequeresponsestatusAction() {
        $id = $this->getRequest()->getParam('id');
        
        $surveyQueModel = new Default_Model_SurveyQuestion();
        $surveyQueInfo = $surveyQueModel->getSurveyQueResponseStatus($id);
        
        $status = $surveyQueInfo['response_required'];
        
        if ($status == 'y'){
            $status = 'n';
            $surveyQueResult = $surveyQueModel->updateSurveyQuestionResponseFlag($id, $status);
        } else{
            $status = 'y';
            $surveyQueResult = $surveyQueModel->updateSurveyQuestionResponseFlag($id, $status);
        }
        
        echo $status;
        exit();
    }

    public function querulesAction() {
        $response = $this->getResponse();
        $response->insert('header', '');
        $response->insert('footer', '');
        
        $this->_helper->layout()->disableLayout();
        
        $qr_id = (int) $this->_getParam('qrid'); // question rule id
        

        $post = $this->getRequest()->getPost();
        
        $questionModel = new Default_Model_Question();
        $queansModel = new Default_Model_QuestionAnswer();
        $rsetansModel = new Default_Model_ResponseSetAnswer();
        $surveyQueModel = new Default_Model_SurveyQuestion();
        $queruleModel = new Default_Model_QueRules();
        
        $error_msg = null;
        
        if (! empty($qr_id)){
            $rule_delete_result = $queruleModel->deleteQueRule($qr_id);
            if ($rule_delete_result){
                $error_msg = 'Selected Smart Rule Delete Successfully';
            } else{
                $error_msg = 'Error(s) encountered. Smart Rule Not Deleted';
            }
        }
        
        if (! empty($post) && ! empty($post['que_sqid'])){
            $sid = (int) $post['sid']; // survey id
            $sqid = (int) $post['sq_id']; // survey question id
            $qid = (int) $post['qid']; // question id
            

            foreach($post['que_sqid'] as $qk => $qv){
                $data = array('sq_id' => $post['sq_id'], 'answer_id' => $post['cmbAnswer'], 'target_sq_id' => $qv);
                $qr_id = $queruleModel->addQueRule($data);
            }
            
            if ($qr_id){
                $error_msg = 'Question Rule Successfully Added';
            } else{
                $error_msg = 'Error(s) encountered. Question rule not added';
            }
        } else{
            $sid = (int) $this->_getParam('sid'); // survey id
            $sqid = (int) $this->_getParam('sqid'); // survey question id
            $qid = (int) $this->_getParam('qid'); // question id
        }
        
        $queInfo = $questionModel->questionInfo($qid);
        if ($queInfo){
            $this->view->queinfo = $queInfo;
            $ansInfo = $queansModel->listQueAns('sort_order', $queInfo['question_id']);
            
            if (empty($ansInfo)){
                $ansInfo = $rsetansModel->listQueAns($queInfo['question_id']);
            }
            
            $this->view->ansinfo = $ansInfo;
            $questions = $surveyQueModel->listSurveyQuestions($sid);
            $this->view->questions = $questions;
        } else{
            $error_msg = 'Invalid / missing question id. Cannot continue.';
        }
        
        $this->view->sqid = $sqid;
        $this->view->sid = $sid;
        $this->view->qid = $qid;
        
        $rules = $queruleModel->getRules($sqid);
        if ($rules){
            $this->view->rules = $rules;
        } else{
            $this->view->rules = null;
        }
        
        $this->view->error_msg = $error_msg;
    }

    public function chartAction() {
        
        $response = $this->getResponse();
        $response->insert('header', '');
        $response->insert('footer', '');
        
        $sid = (int) $this->_getParam('sid');
        $qid = (int) $this->_getParam('qid');
        
        $arrQue = $this->_session->response_results;
        $chartViewdata = $this->_session->chartdata;
        
        foreach($arrQue as $arr){
            if ($arr['que_info']['question_id'] == $qid){
                
                $this->view->title = $arr['que_info']['description'];
                
                $data = '';
                foreach($arr['answers'] as $ak => $av){
                    $data .= '["' . $av['answer_text'] . '", ' . $av['percentage'] . ' ],';
                }
                $data = substr($data, 0, strlen($data) - 1);
            }
        }
        
        $this->view->data = $data;
        $this->view->cdata = $chartViewdata;
    }

}