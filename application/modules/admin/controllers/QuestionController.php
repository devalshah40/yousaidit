<?php

class Admin_QuestionController extends My_AdminController {

	/**
	 * init() function, call init() function of parent class
	 *
	 */
	public function init() {
		parent::init ();
	}

	/**
	 * Default Action
	 *
	 */
	public function indexAction() {
		$this->_session->search_param = '';
		
		$post = $this->getRequest ()->getPost ();

		$questionModel = new Default_Model_Question ();

		if (! empty ( $post ['cmdDelete'] )) {
			$id = $post ['chk'];
			$delete_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				$qrsModel = new Default_Model_QuestionResponseSets ();
				$qaModel = new Default_Model_QuestionAnswer();

				foreach ( $id as $k => $v ) {
					$qrs_delete = $qrsModel->deleteQuestionResponseSetAssociation ( $v );
					$qa_delete = $qaModel->deleteQueAnsAssociation($v);
					$delete_res = $questionModel->deleteQuestion ( $v );
					if ($delete_res) {
						$delete_count ++;
					}
				}
			}
			$this->_flashMessenger->addMessage ( $delete_count . ' Question(s) deleted.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/' );
		}

		$sort_field = 'question_id DESC';

		$field = null;
		$value = null;

		if (! empty ( $post ['cmdSubmit'] )) {
			$field = $post ['cmbKey'];
			$value = $post ['txtValue'];

			$search_param = array ("field" => $field, "value" => $value );

			$this->_session->search_param = $search_param;
			$this->view->result_type = 'filtered';
		} else {
			if ($this->_getParam ( 'clear' ) == "results") {
				$search_param = array ();
				$this->_session->search_param = $search_param;
			}
			$search_param = $this->_session->search_param;
			if (! empty ( $search_param )) {
				$field = $this->_session->search_param ['field'];
				$value = $this->_session->search_param ['value'];
				$this->view->result_type = 'filtered';
			}
		}

		$this->view->pageTitle = ADMIN_TITLE . 'Manage Questions';
		$this->view->pgTitle = 'Question Listing';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/question_menu.phtml' ) );

		$questionList = $questionModel->listQuestions ( $sort_field, $field, $value );

		if ($questionList) {
			$qaModel = new Default_Model_QuestionAnswer ();
			$qArr = array ();
			foreach ( $questionList as $k => $v ) {
				$qa_res = $qaModel->questionInfo ( $v ['question_id'] );
				if (empty ( $qa_res )) {
					$controller = 'responseset';
				} else {
					$controller = 'answers';
				}
				$qArr [] = array ('qinfo' => $v, 'controller' => $controller );
			}

			$pg = $this->_getParam ( 'page' );
			$this->view->pg = ! empty ( $pg ) ? $pg : 1;

			$this->view->paginator = Zend_Paginator::factory ( $qArr );
			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
			$this->view->paginator->setCurrentPageNumber ( $pg );
		} else {
			$this->view->paginator = null;
		}
	}

	public function addAction() {
		$questionModel = new Default_Model_Question ();

		// client company listing ..
	//	$clientModel = new Default_Model_Client ();
	//	$clientList = $clientModel->listActiveClients ();
	//	$this->view->clientList = $clientList;

		$this->view->action = 'Add';
		$post = $this->getRequest ()->getPost ();
		$is_error = false;

		$var_msg = 'Errors encountered .' . " <br>";

		if (! empty ( $post )) {
			if (empty ( $post ['description'] )) {
				$is_error = true;
				$var_msg .= 'Question Description cannot be left empty.' . " <br>";
			}

			if ($is_error == true) {
				foreach ( $post as $pk => $pv ) {
					$resInfo [$pk] = $pv;
				}
				$this->view->resInfo = $resInfo;
				$this->view->errorMsg = $var_msg;
				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );

			} else {
				$post ['added_by'] = $this->_session->session_data ['user_id'];

				$post ['description'] = $this->_helper->Filterspchars ( $post ['description'] );

				$question_id = $questionModel->insertQuestion ( $post );

				if ($question_id) {
					$this->_flashMessenger->addMessage ( 'Question added successfully.' );
				} else {
					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Question not added.' );
				}
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/add/' );
			}
		}

		$this->view->action = 'Add';
		$this->view->pageTitle = 'Add survey question';
		$this->view->pgTitle = 'Add survey question';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/question_menu.phtml' ) );
	}

	public function editAction() {
		$id = ( int ) $this->_getParam ( 'id' );

		$pg = ( int ) $this->_getParam ( 'page' );

		$pg = (! empty ( $pg )) ? $pg : 1;

		$questionModel = new Default_Model_Question ();

		$post = $this->getRequest ()->getPost ();

		$this->view->action = 'Edit';

		// client company listing ..
	//	$clientModel = new Default_Model_Client ();
		//$clientList = $clientModel->listActiveClients ();
	//	$this->view->clientList = $clientList;

		$this->view->pageTitle = 'Edit question details ';
		$this->view->pgTitle = 'Edit question details ';
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/question_menu.phtml' ) );

		if (empty ( $post )) {
			$questionInfo = $questionModel->questionInfo ( $id );

			if ($questionInfo) {
				$adminModel = new Default_Model_User ();
				$userInfo = $adminModel->getUserInfo ( 1, $questionInfo ['added_by'] );

				if ($userInfo) {
					$this->view->added_by = $userInfo ['firstname'];
					$this->view->added_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
				}

				if (! empty ( $questionInfo ['updated_by'] )) {
					$userInfo = $adminModel->getUserInfo ( 1, $questionInfo ['updated_by'] );
					if ($userInfo) {
						$this->view->updated_by = $userInfo ['firstname'];
						$this->view->updated_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
					}
				} else {
					$this->view->updated_by = " -- ";
				}

				$this->view->action = 'Edit';
				$this->view->questioninfo = $questionInfo;

				$this->view->pg = $pg;

				return $this->render ( 'add' );
			} else {
				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Unable to fetch question details.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $pg );
			}
		} else {
			$adminModel = new Default_Model_User ();
			$questionInfo = $questionModel->questionInfo ( $id );

			$is_error = false;
			$var_msg = 'Error encountered.' . "<br> ";
			if (empty ( $post ['description'] )) {
				$is_error = true;
				$var_msg .= 'Description cannot be left empty.' . "<br/>";
			}

//		if (( $post ['client_id'] ) == ' ') {
//
//				$is_error = true;
//
//				$var_msg .= 'Question Type cannot be left empty.' . "<br/>";
//
//			}

			if ($is_error == true) {

				$questioninfo = $questionModel->questionInfo ( $id );

				foreach ( $post as $k => $v ) {
					$getquestion [$k] = $v;
				}

			//	$getquestion ['client_id'] = $post ['client_id'];
				$getquestion ['question_id'] = $post ['id'];
				$getquestion ['add_date'] = $questioninfo ['add_date'];

				$getquestion ['updated_datetime'] = $questioninfo ['updated_datetime'];

				// for getting updatedd by and added by names
				$questionInfo = $questionModel->questionInfo ( $id );

				$adminModel = new Default_Model_User ();
				$userInfo = $adminModel->getUserInfo ( 1, $questionInfo ['added_by'] );

				$this->view->added_by = $userInfo ['firstname'];
				$this->view->added_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';

				$userInfo = $adminModel->getUserInfo ( 1, $questionInfo ['updated_by'] );

				if ($userInfo) {
					$this->view->updated_by = $userInfo ['firstname'];
					$this->view->updated_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
				} else {
					$this->view->updated_by = " -- ";
				}

				$this->view->questioninfo = $getquestion;
				$this->view->errorMsg = $var_msg;

				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );

				return $this->render ( 'add' );
			} else {

				$post ['updated_by'] = $this->_session->session_data ['user_id'];

				$post ['description'] = $this->_helper->Filterspchars ( $post ['description'] );

				$result = $questionModel->updateQuestion ( $post );

				if ($result) {
					$this->_flashMessenger->addMessage ( 'Question detail updated successfully.' );
				} else {
					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Question detail not updated.' );
				}
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $pg );
			}
		}
	}

	public function addresponsesetAction() {
		$post = $this->getRequest ()->getPost ();

		$que_id = ( int ) $this->_getParam ( 'qid' );

		if (! empty ( $post ) && ! empty ( $post ['cmdAddResSet'] )) {
			if (! empty ( $post ['cmdBack'] )) {
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/responseset/qid/' . $que_id . "/page/1" );
			}
			if (! empty ( $post ['cmdAddResSet'] )) {
				$qrsModel = new Default_Model_QuestionResponseSets ();
				$id = $post ['chk'];
				$add_count = 0;
				if (is_array ( $id ) && count ( $id ) > 0) {
					foreach ( $id as $k => $v ) {
						$add_res = $qrsModel->insertQuestionResponseSetAssociation ( $que_id, $v );
					}
				}
				$this->_flashMessenger->addMessage ( 'Response set successfully added to selected question.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/responseset/qid/' . $que_id . "/page/1" );
			}
		} else {
			$questionModel = new Default_Model_Question ();
			$queInfo = $questionModel->questionInfo ( $que_id );
			if ($queInfo) {
				$this->view->qid = $que_id;
				$this->view->queinfo = $queInfo;
			} else {
				$this->_flashMessenger->addMessage ( 'Invalid Question Id.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $this->_getParam ( 'page' ) );
			}

			$rsModel = new Default_Model_ResponseSet ();

			$res_responseSet = $rsModel->listUnLinkedResponseSet ( $que_id );

			if ($res_responseSet) {
				$pg = $this->_getParam ( 'page' );
				$this->view->pg = ! empty ( $pg ) ? $pg : 1;

				$this->view->paginator = Zend_Paginator::factory ( $res_responseSet );
				$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
				$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
				$this->view->paginator->setCurrentPageNumber ( $pg );
			} else {
				$this->view->paginator = null;
			}

			$this->view->pageTitle = ADMIN_TITLE . 'Response Set - Question Association';
			$this->view->pgTitle = 'Add Response Set To Question';

			$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/que_responseset_menu.phtml' ) );
		}
	}

	public function responsesetAction() {
		$post = $this->getRequest ()->getPost ();

		$que_id = ( int ) $this->_getParam ( 'qid' );

		$qrsModel = new Default_Model_QuestionResponseSets ();

		if (! empty ( $post ['cmdSortOrder'] )) {
			if (is_array ( $post ['txtSortOrder'] ) && count ( $post ['txtSortOrder'] ) > 0) {
				for($s = 0; $s < count ( $post ['txtSortOrder'] ); $s ++) {
					$sorder_res = $qrsModel->updateQueResponseSetSortOrder ( $post ['h_qrs_id'] [$s], $post ['txtSortOrder'] [$s] );
				}
			}
			$this->_flashMessenger->addMessage ( 'Sort order successfully set.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/responseset/qid/' . $post ['qid'] . "/page/1" );
		}

		if (! empty ( $post ['cmdDelete'] )) {
			$id = $post ['chk'];
			$delete_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				foreach ( $id as $k => $v ) {
					$delete_res = $qrsModel->deleteQuestionResponseSet ( $v );
					if ($delete_res) {
						$delete_count ++;
					}
				}
			}
			$this->_flashMessenger->addMessage ( $delete_count . ' Response Set deleted.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/responseset/qid/' . $post ['qid'] . "/page/1" );
		}

		if (empty ( $que_id )) {
			$this->_flashMessenger->addMessage ( 'Invalid / Missing Question Id.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $this->_getParam ( 'page' ) );
		} else {
			$questionModel = new Default_Model_Question ();
			$queInfo = $questionModel->questionInfo ( $que_id );
			if ($queInfo) {
				$this->view->qid = $que_id;
				$this->view->queinfo = $queInfo;
			} else {
				$this->_flashMessenger->addMessage ( 'Invalid Question Id.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $this->_getParam ( 'page' ) );
			}
		}

		$sort_field = 'qrs.sort_order';

		$this->view->pageTitle = ADMIN_TITLE . 'Manage Question Response Set Association';
		$this->view->pgTitle = 'Question Response Set Listing';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/que_responseset_menu.phtml' ) );

		$responseSetList = $qrsModel->listQuestionResponseSets ( $sort_field, 'question_id', $que_id, true );

		if ($responseSetList) {
			$rsaModel = new Default_Model_ResponseSetAnswer ();
			foreach ( $responseSetList as $k => $v ) {
				$answers = $rsaModel->listResponseSetAnswer ( 'rsa.sort_order', 'rs.rs_id', $v ['rs_id'], true );
				$arrAns = array ();
				if ($answers) {
					foreach ( $answers as $k1 => $v1 ) {
						$arrAns [] = array ('answer_id' => $v1 ['answer_id'], 'answer_text' => $v1 ['answer_text'], 'weightage' => $v1 ['weightage'], 'free_text' => $v1 ['free_text'], 'free_text_caption' => $v1 ['free_text_caption'] );
					}
				}
				$rsListArr [] = array ('rsetInfo' => $v, 'answers' => $arrAns );
			}

			$pg = $this->_getParam ( 'page' );
			$this->view->pg = ! empty ( $pg ) ? $pg : 1;

			$this->view->paginator = Zend_Paginator::factory ( $rsListArr );
			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
			$this->view->paginator->setCurrentPageNumber ( $pg );
		} else {
			$this->view->paginator = null;
		}
	}

	public function answersAction() {
		$post = $this->getRequest ()->getPost ();
		$que_id = ( int ) $this->_getParam ( 'qid' );
		$queansModel = new Default_Model_QuestionAnswer ();
		if (! empty ( $post ['cmdUpdateSortOrder'] )) {
			for($i = 0; $i < count ( $post ['h_qa_id'] ); $i ++) {
				$freeText = $post ['txtFreeText' . $post ['h_qa_id'] [$i]];
				if (! empty ( $freeText ) && $freeText != '--') {
					$sort_order_res = $queansModel->updateAnswerSortOrder ( $post ['h_qa_id'] [$i], $post ['txtSortOrder'] [$i], $post ['txtAnswerValue'] [$i], $freeText );
				} elseif(! empty ( $post ['txtAnswerValue'] ) ) {
					$sort_order_res = $queansModel->updateAnswerSortOrder ( $post ['h_qa_id'] [$i], $post ['txtSortOrder'] [$i], $post ['txtAnswerValue'] [$i] );
				}else{
				
					$sort_order_res = $queansModel->updateAnswerSortOrder ( $post ['h_qa_id'] [$i], $post ['txtSortOrder'] [$i] );
				}
				
				
				if ($sort_order_res) {
					$sort_order_res ++;
				}
			}
			$this->_flashMessenger->addMessage ( 'Answer sort order successfully set.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/answers/qid/' . $post ['question_id'] . "/page/1" );
		}

		if (! empty ( $post ['cmdDelete'] )) {
			$id = $post ['chk'];
			$delete_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				foreach ( $id as $k => $v ) {
					$delete_res = $queansModel->deleteQueAns ( $v );
					if ($delete_res) {
						$delete_count ++;
					}
				}
			}
			$this->_flashMessenger->addMessage ( $delete_count . ' Answer(s) deleted.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/answers/qid/' . $post ['question_id'] . "/page/1" );
		}

		if (empty ( $que_id )) {
			$this->_flashMessenger->addMessage ( 'Invalid / Missing Question Id.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $this->_getParam ( 'page' ) );
		} else {
			$questionModel = new Default_Model_Question ();
			$queInfo = $questionModel->questionInfo ( $que_id );
			if ($queInfo) {
				$this->view->qid = $que_id;
				$this->view->queinfo = $queInfo;
			} else {
				$this->_flashMessenger->addMessage ( 'Invalid Question Id.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/' . $this->_getParam ( 'page' ) );
			}
		}

		$sort_field = 'qa.sort_order';

		$this->view->pageTitle = ADMIN_TITLE . 'Manage Answers';
		$this->view->pgTitle = 'Answer Listing';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/answer_menu.phtml' ) );

		$answerList = $queansModel->listQueAns ( $sort_field, $que_id );

		if ($answerList) {
			$pg = $this->_getParam ( 'page' );
			$this->view->pg = ! empty ( $pg ) ? $pg : 1;

			$this->view->paginator = Zend_Paginator::factory ( $answerList );
			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
			$this->view->paginator->setCurrentPageNumber ( $pg );
		} else {
			$this->view->paginator = null;
		}
	}

	public function addanswerAction() {
		$queansModel = new Default_Model_QuestionAnswer ();

		$post = $this->getRequest ()->getPost ();

		if (! empty ( $post )) {
			if (! empty ( $post ['cmdBack'] ) && $post ['cmdBack'] == 'Back To Answer Listing') {
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/answers/qid/' . $post ['qid'] . "/page/" . $post ['pg'] );
			}

			$post ['question_id'] = $post ['qid'];
			$ansModel = new Default_Model_Answer ();

			foreach ( $post ['answer_id'] as $k => $v ) {
				$arr = explode ( "-", $v );
				$answer_id = $arr [0];
				$free_text = $arr [1];
				$existing = $queansModel->existingAns ( $post ['qid'], $answer_id );
				if ($existing == false) {
					if ($free_text == 'y') {
						$ansInfo = $ansModel->answerInfo ( $answer_id );
						if ($ansInfo ['free_text'] == 'y') {
							$free_text_caption = $ansInfo ['free_text_caption'];
						} else {
							$free_text_caption = null;
						}
					} else {
						$free_text_caption = null;
					}
					$qa_id = $queansModel->insertQueAns ( $post ['question_id'], $answer_id, $free_text_caption );
				}
			}

			if ($qa_id) {
				$this->_flashMessenger->addMessage ( 'Answer added to selected question successfully.' );
			} else {
				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Answer not added to selected question.' );
			}

			$this->_redirector->gotoUrl ( $this->_modulename . '/question/answers/qid/' . $post ['qid'] );
		} else {
			$que_id = ( int ) $this->_getParam ( 'qid' );

			if (empty ( $que_id )) {
				$this->_flashMessenger->addMessage ( 'Cannot add answer. Invalid / Missing Question Id.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/1' );
			} else {
				$questionModel = new Default_Model_Question ();
				$queInfo = $questionModel->questionInfo ( $que_id );
				if ($queInfo) {
					$this->view->qid = $que_id;
					$this->view->queinfo = $queInfo;

					$ansModel = new Default_Model_Answer ();
					$answers = $ansModel->listUnLinkedAnswers ( $que_id );
                  if($answers){
                  
                    $pg = $this->_getParam ( 'page' );
					$this->view->pg = ! empty ( $pg ) ? $pg : 1;
					$this->view->paginator = Zend_Paginator::factory ( $answers );
					$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
					$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
					$this->view->paginator->setCurrentPageNumber ( $pg );
					$this->view->answers_block = $this->view->render ( 'question/answer_block.phtml' );
                  
                  
                  
                  }else{
                        $this->view->paginator = null;
                        $this->view->answers_block = $this->view->render ( 'question/answer_block.phtml' );
                    }
					

				} else {
					$this->_flashMessenger->addMessage ( 'Cannot add answer. Invalid Question Id.' );
					$this->_redirector->gotoUrl ( $this->_modulename . '/question/index/page/1' );
				}
			}

			$this->view->action = 'Add';
			$this->view->pageTitle = ADMIN_TITLE . 'Add Answer';
			$this->view->pgTitle = 'Add Answer';

			$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/answer_menu.phtml' ) );
		}
	}

	public function searchanswerAction() {
		$this->_helper->viewRenderer->setNoRender ( true );

		$response = $this->getResponse ();
		$response->insert ( 'header', '' );
		$response->insert ( 'footer', '' );

		$this->_helper->layout ()->disableLayout ();

		$que_id = ( int ) $this->_getParam ( 'qid' );
		$search_text = $this->_getParam ( 'search_text' );

		$questionModel = new Default_Model_Question ();
		$queInfo = $questionModel->questionInfo ( $que_id );

		if ($queInfo) {
			$this->view->qid = $que_id;
			$this->view->queinfo = $queInfo;

			$ansModel = new Default_Model_Answer ();
			$answers = $ansModel->listUnLinkedAnswers ( $que_id, true, $search_text );

			$pg = $this->_getParam ( 'page' );
			$this->view->pg = ! empty ( $pg ) ? $pg : 1;

			$this->view->search_flag = true;

			$this->view->paginator = $answers;

			$answers_block = $this->view->render ( 'question/answer_block.phtml' );

			echo $answers_block;
		} else {
			echo '<br/><br/><br/><br/><div align="center">No Answers Found</div><br/><br/><br/><br/>';
		}
		return true;
	}

	public function editansAction(){
	    $post = $this->getRequest()->getPost();

	    if( empty($post)){
    	    $response = $this->getResponse();
    		$response->insert('header', '');
    		$response->insert('footer', '');

    		$this->_helper->layout()->disableLayout();
        	$id = (int)$this->_getParam('id');

        	//$this->p($this->_session->ansArr[$id]);
        	$this->view->id = $id;
       	    $this->view->answerinfo = $this->_session->ansArr[$id];
	    }else{
	        $this->view->aid = $post['id'];

	        $this->_session->ansArr[$post['id']][2] = strtoupper($post['answer_type']);
	        $this->_session->ansArr[$post['id']][1] = $post['answer_text'];
	        $this->_session->ansArr[$post['id']][3] = $post['weightage'];
	        $this->_session->ansArr[$post['id']][4] = strtoupper($post['free_text']);
	        $this->_session->ansArr[$post['id']][5] = $post['free_text_caption'];

	        $this->view->answerinfo = $this->_session->ansArr[ $post['id'] ];
	    }
	}

	public function editqueAction(){
	    $post = $this->getRequest()->getPost();

	    if( empty($post)){
    	    $response = $this->getResponse();
    		$response->insert('header', '');
    		$response->insert('footer', '');

    		$this->_helper->layout()->disableLayout();
        	$id = $this->_getParam('id');

        	//$this->p($this->_session->queArr[$id]);
        	$this->view->id = $id;
       	    $this->view->questioninfo = $this->_session->queArr[$id];
	    }else{
	        $this->view->qid = $post['id'];
	        $this->view->id = '';
	        $this->_session->queArr[$post['id']][0] = $post['description'];
	        $this->_session->queArr[$post['id']][1] = strtoupper($post['answer_type']);
	        $this->_session->queArr[$post['id']][2] = $post['max_answer'];
	        $this->_session->queArr[$post['id']][3] = $post['status'];

	        $this->view->questioninfo = $this->_session->queArr[ $post['id'] ];
	    }
	}

	public function importAction() {
		$this->view->pageTitle = ADMIN_TITLE . 'Import response set, answers & survey questions';
		$this->view->pgTitle = 'Import response set, answers & survey questions';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/import_menu.phtml' ) );

		$post = $this->getRequest ()->getPost ();

		if (! empty ( $post )) {
		    $clientModel = new Default_Model_Client();
		    $clients = $clientModel->listActiveClients();
		    $this->view->clients = $clients;

		    // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~
			$adapter = new Zend_File_Transfer ();
			$files = $adapter->getFileInfo ();
			$queArr = array ();
			$row = 1;

			$is_error = false;
			$var_msg = 'Error(s) encountered.' . "<br/><br/>";

			if( empty($files['csvFileQuestion']['name']) || empty($files['csvFileAnswer']['name'])){
				$is_error = true;
				$var_msg .= 'Please Select both Question and Answers Files to be Uploaded.' . "<br/>";
			}

			if($is_error == false){

        		if( !empty($files['csvFileQuestion']['tmp_name']) ){
                    if (($handle = fopen($files['csvFileQuestion']['tmp_name'], "r")) !== FALSE) {
    					while ( ($data = fgetcsv ( $handle, 1000, "," )) !== FALSE ) {
    					    $field_count = count ( $data );
    					    if( !empty($data[0]) && !empty($data[1]) ){
    					        if( strtoupper($data[1]) == 'S' || strtoupper($data[1]) == 'M'){
    					            $is_error = true;

    					            if ($field_count == 2) {
    					                $data[2] = 0;
    					                $data[3] = 'Inactive';
    					                $is_error = false;
    					            }

    					            if ($field_count == 3) {
    					                if( empty($data[2]) ){
    					                    $data[2] = 0;
    					                }
    					                $data[3] = 'Inactive';
    					                $is_error = false;
    					            }

    					            if ($field_count == 4) {
    					                if( empty($data[2]) ){
    					                    $data[2] = 0;
    					                }
    					                if( empty($data[3]) ){
    					                    $data[3] = 'Inactive';
    					                }
    					                $is_error = false;
    					            }

    					            if ($is_error == false) {
    					                $row ++;
    					                $queArr [] = $data;
    					            }
    					        }
    					    }
    					}
    					fclose ( $handle );
    				}
        		}

        		if ($queArr) {
    				$this->_session->queArr = $queArr;
    				$this->view->queArr = $queArr;
    			} else {
    				$is_error = true;
        			$var_msg .= 'No Valid Questions Found In Selected File.' . "<br/>";
    				$this->_session->queArr = null;
    			}

        		// ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~


                $ansArr = array();
        		$row = 1;
        		if( !empty($files['csvFileAnswer']['tmp_name']) ){
                    if (($handle = fopen($files['csvFileAnswer']['tmp_name'], "r")) !== FALSE) {
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $field_count = count($data);
                            if( !empty($data[1]) && !empty($data[2]) ){
                                if( strtoupper($data[2]) == 'NV' || strtoupper($data[2]) == 'V'){
                                    $is_error = true;
                                    if($field_count == 3){
                                        $data[3] = '0';
                                        $data[4] = null;
                                        $data[5] = null;
                                        $is_error = false;
                                    }
                                    if($field_count == 4){
                                        $data[4] = null;
                                        $data[5] = null;
                                        $is_error = false;
                                    }

                                    if($field_count == 6){
                                        $is_error = false;
                                    }

                                    if($is_error == false){
                                        $row++;
                                        $ansArr[] = $data;
                                    }

                                }
                            }
                            //$this->p($data);                            echo '<hr>';
                        }
                    }
        		}
        		sort($ansArr);

        		if($ansArr){
                    $this->_session->ansArr = $ansArr;
                    $this->view->ansArr = $ansArr;
                }else{
                    $this->_session->ansArr = null;
        			$is_error = true;
        			$var_msg .= 'No Valid Answers Found In Selected File.' . "<br/>";
                }

                $this->view->is_post = true;
			}
            if ($is_error == true) {
				$this->_flashMessenger->addMessage($var_msg);
	            $this->_redirector->gotoUrl ( $this->_modulename . '/question/import' );
	     	}
		}else{
		    $this->view->is_post = false;
		}
	}

	public function importquestionAction() {
		$post = $this->getRequest ()->getPost ();

		if (! empty ( $post )) {
			$this->p($this->_session->queArr);
			//$this->p($post);

		    $process_data_flag = false;

		    if( !empty($post ['chk'])){
		        $process_data_flag = true;
    			$questionModel = new Default_Model_Question ();
    			$cnt = 0;
    		    $que_arr = array();
    			foreach ( $post ['chk'] as $k => $v ) {
    				$description = $this->_helper->Filterspchars ( $this->_session->queArr [$v] [0] );
    		        $data = array( 	'client_id'	   => $post['client_id'],
    		        				'description'  => $description,
            						'answer_type'  => $this->_session->queArr[$v][1],
            						'max_answer'   => !empty($this->_session->queArr[$v][2])?$this->_session->queArr[$v][2]:null,
            						'added_by'	   => $this->_session->session_data['user_id'],
            						'add_date'     => date("Y-m-d"),
            						'status'	   => $this->_session->queArr[$v][3]		);

    				$question_id = $questionModel->insertQuestion ( $data );

    				if ($question_id) {
    		            array_push($que_arr, array('question_id' => $question_id, 'queinfo' => $this->_session->queArr[$v]));
    					$cnt ++;
    				}
    			}
		    }

		    $this->_session->uploaded_questions = $que_arr;

		    //$this->_session->queArr = null;

            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~

		    $rsetModel = new Default_Model_ResponseSet();
		    $ansModel = new Default_Model_Answer();
		    $rsAnsModel = new Default_Model_ResponseSetAnswer();

            // response set block //
    		if( !empty($post['chkRS'])){
    		    $process_data_flag = true;
    		    $rsetCnt = 0;
    		    $rset_arr = array();
    		    foreach($post['chkRS'] as $k => $v){
    		        $rs_id = null;
    		        if( !empty($this->_session->ansArr[$v][0]) ){            // response set title
    		            $rSetTitle = $this->_session->ansArr[$v][0];
    		            $rsInfo = $rsetModel->responseInfo($rSetTitle);
    		            if( empty($rsInfo) ){
    		                $rsetData = array ('title' => $rSetTitle, 'description' => null, 'add_date' => date ( "Y-m-d" ), 'status' => 'Active' );
    		                $rs_id = $rsetModel->insertResponseSet($rsetData);
    		                if($rs_id){
    		                    $rsetCnt++;
    		                }
    		            }else{
    		                $rs_id = $rsInfo['rs_id'];
    		            }
    		        }

    		        if( !empty($this->_session->ansArr[$v][1]) ){            // answer title
    		            $free_text_caption = (strtolower($this->_session->ansArr[$v][4]) == 'n')?null:$this->_session->ansArr[$v][5];
    		            $data = array( 	'answer_type'        => strtolower($this->_session->ansArr[$v][2]),
                						'weightage'          => !empty($this->_session->ansArr[$v][3])?$this->_session->ansArr[$v][3]:0,
                						'answer_text'        => $this->_session->ansArr[$v][1],
                						'free_text'          => !empty($this->_session->ansArr[$v][4])?strtolower($this->_session->ansArr[$v][4]):'n',
                						'free_text_caption'  => $free_text_caption,
                						'added_by'	         => $this->_session->session_data['user_id'],
                						'add_date'           => date("Y-m-d"),
                						'status'	         => 'Active'		);

    		            $answer_id = $ansModel->insertAnswer($data);

    		            if($answer_id){
    		                array_push($rset_arr, array('rs_id' => $rs_id, 'title' => $rSetTitle, 'rset_ansinfo' => $this->_session->ansArr[$v] ));
    		                if($rs_id){
    		                    $rsa_id = $rsAnsModel->insertResponseSetAnswerAssociation($rs_id, $answer_id, $free_text_caption);
    		                }
    		            }
    		        }
    		    }
    		}

    		$this->_session->uploaded_rsets = $rset_arr;

    		if( !empty($post['chkAns'])){
    		    $process_data_flag = true;
    		    $ansCnt = 0;
    		    // answer set block //
    		    $ans_arr = array();
    		    foreach($post['chkAns'] as $k => $v){
    		        if( !empty($this->_session->ansArr[$v][1]) ){            // answer title
    		            $free_text_caption = (strtolower($this->_session->ansArr[$v][4]) == 'n')?null:$this->_session->ansArr[$v][5];
    		            $data = array( 	'answer_type'        => strtolower($this->_session->ansArr[$v][2]),
                						'weightage'          => !empty($this->_session->ansArr[$v][3])?$this->_session->ansArr[$v][3]:0,
                						'answer_text'        => $this->_session->ansArr[$v][1],
                						'free_text'          => !empty($this->_session->ansArr[$v][4])?strtolower($this->_session->ansArr[$v][4]):'n',
                						'free_text_caption'  => $free_text_caption,
                						'added_by'	         => $this->_session->session_data['user_id'],
                						'add_date'           => date("Y-m-d"),
                						'status'	         => 'Active'		);

    		            $answer_id = $ansModel->insertAnswer($data);

    		            if($answer_id){
    		                array_push($ans_arr, array('answer_id' => $answer_id, 'ansinfo' => $this->_session->ansArr[$v]));
    		                $ansCnt++;
    		            }
    		        }
    		    }
    		}

    		$this->_session->uploaded_ans = $ans_arr;

    		//$this->_session->ansArr = null;

            // ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~ ~

    		$msg = '';
    		if($rsetCnt){
    		    $msg = $rsetCnt . ' Response set(s), ';
    		}
    		if($ansCnt){
    		    $msg .= $ansCnt . ' Answer(s), ';
    		}
    		if($cnt){
    		    $msg .= $cnt . ' Question(s) ';
    		}

    		if($process_data_flag == true){
    		    $this->_flashMessenger->addMessage($msg . ' imported successfully');

    		    // variable to hold questions that have been successfully associated
    		    // with response set / answers

    		    $this->_session->survey_questions = array();
    		}else{
    		    $this->_flashMessenger->addMessage('Cannot Continue. Zero Response set, Answer, Question Selected for importing');
    		}

		    $this->_redirector->gotoUrl($this->_modulename . '/question/associateansrset');
		}else{
			$this->_redirector->gotoUrl ( $this->_modulename . '/question/index' );
		}
    }

    public function associateansrsetAction(){

        $this->view->pageTitle = ADMIN_TITLE . 'Associate Questions - Response Set, Answers';
		$this->view->pgTitle = 'Associate Questions - Response Set, Answers';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'question/question_menu.phtml' ) );

		$up_que = $this->_session->uploaded_questions;

        $queansModel = new Default_Model_QuestionAnswer();
        $rsaModel = new Default_Model_ResponseSetAnswer();
        $queArr = array();
		foreach($up_que as $k => $v){
		    $flag = false;
		    $answers = $rsaModel->listQueAns ($v ['question_id'] );
		    if(empty($answers)){
		        $answers = $queansModel->listQueAns('qa.sort_order', $v['question_id']);
		        if(empty($answers)){
		            $flag = true;
		        }
		    }
		    if($flag == true){
		        array_push($queArr, $v);
		    }
		}

        $this->view->queArr = $queArr;

        // * *  * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

        //$que_id = (int)$this->_getParam('qid');
        //$this->p($this->_session->uploaded_rsets);
        //$this->p($this->_session->uploaded_ans);

        $this->view->ans = $this->_session->uploaded_ans;

        $this->view->rset = $this->_session->uploaded_rsets;
    }

    public function queansrsAction(){
        $post = $this->getRequest()->getPost();

	    if( $post['ansgrp'] == 'r'){
	        if( !empty($post['cmdSubmit']) ){
	            $cnt = 0;
	            $qrsModel = new Default_Model_QuestionResponseSets();
	            foreach($post['queid'] as $k => $v){
	                $qrs_id = $qrsModel->insertQuestionResponseSetAssociation($v, $post['rset']);
	                if($qrs_id){
	                    array_push($this->_session->survey_questions, $v);
	                    $cnt++;
	                }
	            }
	            $this->_flashMessenger->addMessage( $cnt . ' Question(s) successfully associated with selected response set');
	            $this->_redirector->gotoUrl($this->_modulename . '/question/associateansrset');
	        }else{
	            $this->_flashMessenger->addMessage('0 Question associated with response set');
	            $this->_redirector->gotoUrl($this->_modulename . '/question/associateansrset');
	        }
	    }

	    if( $post['ansgrp'] == 'a'){
	        if( !empty($post['cmdSubmit']) ){
	            $queansModel = new Default_Model_QuestionAnswer();
	            $ansModel = new Default_Model_Answer();
	            $cnt = 0;
	            foreach($post['queid'] as $qk => $qv){
    	            foreach($post['answer_id'] as $k => $v){
    	                $existing = $queansModel->existingAns($qv, $v);
    	                if($existing == false){
    	                    if(strtolower($post['free_text'][$k]) == 'y'){
    	                        $ansInfo = $ansModel->answerInfo($v);
    	                        if($ansInfo['free_text'] == 'y'){
    	                            $free_text_caption = $ansInfo['free_text_caption'];
    							} else {
    	                            $free_text_caption = null;
    	                        }
    	                    }else{
    	                        $free_text_caption = null;
    	                    }
    	                    $qa_id = $queansModel->insertQueAns($qv, $v, $free_text_caption);
    	                }
    	            }
    	            if($qa_id){
    	                array_push($this->_session->survey_questions, $qv);
    	                $cnt++;
    	            }
	            }
	            $this->_flashMessenger->addMessage( $cnt . ' Question(s) successfully associated with selected answers');
	            $this->_redirector->gotoUrl($this->_modulename . '/question/associateansrset');
	        }else{
	            $this->_flashMessenger->addMessage('0 Question associated with answers');
	            $this->_redirector->gotoUrl($this->_modulename . '/question/associateansrset');
	        }
	    }
	    $this->_redirector->gotoUrl($this->_modulename . '/question/associateansrset');
	}

	public function associate_que_ans($post){
        $queansModel = new Default_Model_QuestionAnswer();
        $ansModel = new Default_Model_Answer();
        $cnt = 0;

        foreach($post['answer_id'] as $k => $v){
            $existing = $queansModel->existingAns($post['qid'], $v);
            if($existing == false){
                if(strtolower($post['free_text'][$k]) == 'y'){
                    $ansInfo = $ansModel->answerInfo($v);
                    if($ansInfo['free_text'] == 'y'){
                        $free_text_caption = $ansInfo['free_text_caption'];
					} else {
                        $free_text_caption = null;
                    }
                }else{
                    $free_text_caption = null;
                }
                $qa_id = $queansModel->insertQueAns($post['qid'], $v, $free_text_caption);
                if($qa_id){
                    $cnt++;
                }
            }
        }
        return $cnt;
	}

	public function rsetansassociationAction(){
	    $response = $this->getResponse();
		$response->insert('header', '');
		$response->insert('footer', '');

    	$this->_helper->layout()->disableLayout();
    	$qid = (int)$this->_getParam('qid');

    	$post = $this->getRequest()->getPost();

    	if( !empty($post)){
    	    $aflag = $this->_getParam('aflag');

    	    if($aflag == 'a') {
    	        if( $post['ans_type'] == 'ca' && !empty($post['cmdDeleteAns']) ){
    	            $qaModel = new Default_Model_QuestionAnswer();
    	            $cnt = 0;
    	            foreach($post['qa_id'] as $k => $v) {
    	                $qa_id = $qaModel->deleteQueAns($v);
    	                if($qa_id) {
    	                    $cnt++;
    	                }
    	            }
    	            if($cnt){
    	                $this->_flashMessenger->addMessage($cnt . ' Answer successfully removed');
    	            }else{
    	                $this->_flashMessenger->addMessage('Error(s) encountered. Question - answer association not removed');
    	            }
    	            $this->_redirector->gotoUrl($this->_modulename . '/question/rsetansassociation/qid/' . $post['qid']);
    	        }

    	        if( $post['ans_type'] == 'a' && !empty($post['cmdAssociateAns']) ){
    	            $cnt = $this->associate_que_ans($post);
    	            if($cnt){
    	                $this->_flashMessenger->addMessage( $cnt . ' Answer(s) successfully associated with question');
    	            }else{
    	                $this->_flashMessenger->addMessage('Error(s) encountered. Question not associated with selected answer');
    	            }
    	            $this->_redirector->gotoUrl($this->_modulename . '/question/rsetansassociation/qid/' . $post['qid']);
    	        }
    	    }

    	    if($aflag == 'r' ) {
    	        if(!empty($post['cmdDeleteRSet']) ){
    	            $qrsModel = new Default_Model_QuestionResponseSets();
    	            $qrs_id = $qrsModel->deleteQuestionResponseSetAssociation($post['qid']);
    	            if($qrs_id){
    	                $this->_flashMessenger->addMessage('Question - response set association successfully removed');
    	            }else{
    	                $this->_flashMessenger->addMessage('Error(s) encountered. Question - response set association not removed');
    	            }
    	        }else{
    	            $this->_flashMessenger->addMessage('Error(s) encountered. Question - response set association not removed');
    	        }
    	        $this->_redirector->gotoUrl($this->_modulename . '/question/rsetansassociation/qid/' . $post['qid']);
    	    }

    	    if($aflag == 'no') {

    	        if( $post['ansgrp'] == 'r' && !empty($post['cmdAssociateRSet']) ){
    	            $qrsModel = new Default_Model_QuestionResponseSets();
    	            $qrs_id = $qrsModel->insertQuestionResponseSetAssociation($post['qid'], $post['rset']);
    	            if($qrs_id){
    	                $this->_flashMessenger->addMessage('Question successfully associated with selected response set');
    	            }else{
    	                $this->_flashMessenger->addMessage('Error(s) encountered. Question not associated with response set');
    	            }
    	            $this->_redirector->gotoUrl($this->_modulename . '/question/rsetansassociation/qid/' . $post['qid']);
    	        }
    	        if( $post['ansgrp'] == 'a'){
    	            $cnt = $this->associate_que_ans($post);
    	            if($cnt){
    	                $this->_flashMessenger->addMessage( $cnt . ' Answer(s) successfully associated with question');
    	            }else{
    	                $this->_flashMessenger->addMessage('Error(s) encountered. Question not associated with selected answer');
    	            }
    	            $this->_redirector->gotoUrl($this->_modulename . '/question/rsetansassociation/qid/' . $post['qid']);
    	        }
    	    }

    	}                // END OF ..... if(!empty($post)) {


    	if( !empty($qid) ){
    	    $this->view->qid = $qid;
    	    $queModel = new Default_Model_Question();
    	    $queansModel = new Default_Model_QuestionAnswer();
    	    $rsaModel = new Default_Model_ResponseSetAnswer();

    	    $queInfo = $queModel->questionInfo($qid);
    	    $this->view->queInfo = $queInfo;

    	    $no_association = false;
    	    $association_type = null;
            $answers = $rsaModel->listQueAns ($qid);
            if($answers){
                $association_type = 'r';
                $this->view->answers = true;
            }else{
                $answers = $queansModel->listQueAns('qa.sort_order', $qid);
                if($answers){
                    $association_type = 'a';
                    $this->view->answers = true;
                }else{
                    $no_association = true;
                }
            }
            $this->view->association_type = $association_type;
            $this->view->no_association = $no_association;

            if($association_type == 'a'){
                $qaModel = new Default_Model_QuestionAnswer();
                $answers = $qaModel->listQueAns('sort_order', $qid);

                if($answers){
                    $this->view->answers = $answers;
                }else{
                    $this->view->answers = null;
                }

                $ansModel = new Default_Model_Answer ();
                $answers = $ansModel->listUnLinkedAnswers ( $qid );

                if($answers){
                    $this->view->available_answers = $answers;
                }else{
                    $this->view->available_answers = null;
                }
            }


            if($association_type == 'r'){
                $qrsModel = new Default_Model_QuestionResponseSets();
                $responseSetList = $qrsModel->listQuestionResponseSets('sort_order', 'question_id', $qid, true);

                if ($responseSetList) {
                    $rsaModel = new Default_Model_ResponseSetAnswer ();
                    foreach ( $responseSetList as $k => $v ) {
                        $answers = $rsaModel->listResponseSetAnswer ( 'rsa.sort_order', 'rs.rs_id', $v ['rs_id'], true );
                        if ($answers) {
                            $arrAns = array ();
                            foreach ( $answers as $k1 => $v1 ) {
                                $arrAns [] = array ('answer_id' => $v1 ['answer_id'], 'answer_text' => $v1 ['answer_text'], 'weightage' => $v1 ['weightage'], 'free_text' => $v1 ['free_text'], 'free_text_caption' => $v1 ['free_text_caption'] );
                            }
                        }
                        $rsListArr [] = array ('rsetInfo' => $v, 'answers' => $arrAns );
                    }
                    $this->view->rset = $rsListArr;
                }else{
                    $this->view->rset = null;
                }

                $rsModel = new Default_Model_ResponseSet ();
                $res_responseSet = $rsModel->listUnLinkedResponseSet ( $qid );

                $rsListArr  = array();

                if ($res_responseSet) {
                    $rsaModel = new Default_Model_ResponseSetAnswer ();
                    foreach ( $res_responseSet as $k => $v ) {
                        $answers = $rsaModel->listResponseSetAnswer ( 'rsa.sort_order', 'rs.rs_id', $v ['rs_id'], true );
                        if ($answers) {
                            $arrAns = array ();
                            foreach ( $answers as $k1 => $v1 ) {
                                $arrAns [] = array ('answer_id' => $v1 ['answer_id'], 'answer_text' => $v1 ['answer_text'], 'weightage' => $v1 ['weightage'], 'free_text' => $v1 ['free_text'], 'free_text_caption' => $v1 ['free_text_caption'] );
                            }
                        }
                        $rsListArr [] = array ('rsetInfo' => $v, 'answers' => $arrAns );
                    }
                    $this->view->available_rset = $rsListArr;
                }else{
                    $this->view->available_rset = null;
                }

            }

            if($no_association == true){
                $rsModel = new Default_Model_ResponseSet();
                $responseSetList = $rsModel->listResponseSets();

                if ($responseSetList) {
                    $rsaModel = new Default_Model_ResponseSetAnswer ();
                    foreach ( $responseSetList as $k => $v ) {
                        $answers = $rsaModel->listResponseSetAnswer ( 'rsa.sort_order', 'rs.rs_id', $v ['rs_id'], true );
                        if ($answers) {
                            $arrAns = array ();
                            foreach ( $answers as $k1 => $v1 ) {
                                $arrAns [] = array ('answer_id' => $v1 ['answer_id'], 'answer_text' => $v1 ['answer_text'], 'weightage' => $v1 ['weightage'], 'free_text' => $v1 ['free_text'], 'free_text_caption' => $v1 ['free_text_caption'] );
                            }
                        }
                        $rsListArr [] = array ('rsetInfo' => $v, 'answers' => $arrAns );
                    }
                    $this->view->rset = $rsListArr;
                }else{
                    $this->view->rset = null;
                }

                $ansModel = new Default_Model_Answer();
                $answerList = $ansModel->listAnswers();
                if($answerList){
                    $this->view->ans = $answerList;
                }else{
                    $this->view->ans = null;
                }
            }
    	}else{

    	}
	}




}