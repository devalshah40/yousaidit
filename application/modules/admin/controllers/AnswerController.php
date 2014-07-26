<?php

class Admin_AnswerController extends My_AdminController {
	
	/**
	 * init() function, call init() function of parent class
	 *
	 */
	public function init() {
		parent::init ();
	
	}
	
	public function indexAction() {
		$this->_session->search_param = '';
		$post = $this->getRequest ()->getPost ();
		
		$que_id = ( int ) $this->_getParam ( 'qid' );
		
		$answerModel = new Default_Model_Answer ();
		
		if (! empty ( $post ['cmdDelete'] )) {
			$id = $post ['chk'];
			$delete_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				foreach ( $id as $k => $v ) {
					$delete_res = $answerModel->deleteAnswer ( $v );
					if ($delete_res) {
						$delete_count ++;
					}
				}
			}
			$this->_flashMessenger->addMessage ( $delete_count . ' Answer(s) deleted.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/answer/index/page/1' );
		}
		
		$sort_field = 'add_date DESC';
		
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
		
		$this->view->pageTitle = ADMIN_TITLE . 'Manage Answers';
		$this->view->pgTitle = 'Answer Listing';
		
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'answer/answer_menu.phtml' ) );
		
		$answerList = $answerModel->listAnswers ( $sort_field, $field, $value );
		
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
	
	public function addAction() {
		$answerModel = new Default_Model_Answer ();
		
		$this->view->action = 'Add';
		$post = $this->getRequest ()->getPost ();
		
		$is_error = false;
		$var_msg = 'Error .' . " ";
		
		if (! empty ( $post )) {
			
			if (empty ( $post ['answer_text'] )) {
				$is_error = true;
				$var_msg .= 'Answer Text cannot be left empty.' . " ";
			}
			
			if ($is_error == true) {
				foreach ( $post as $pk => $pv ) {
					$ansInfo [$pk] = $pv;
				}
				
				$this->view->ansInfo = $ansInfo;
				$this->view->errorMsg = $var_msg;
				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );
			
			} else {
				$post ['added_by'] = $this->_session->session_data ['user_id'];
				
				if (! empty ( $post ['answer_text'] )) {
					$post ['answer_text'] = $this->_helper->Filterspchars ( $post ['answer_text'] );
				}
				if (! empty ( $post ['free_text_caption'] )) {
					$post ['free_text_caption'] = $this->_helper->Filterspchars ( $post ['free_text_caption'] );
				}
				
				$answer_id = $answerModel->insertAnswer ( $post );
				
				if ($answer_id) {
					$this->_flashMessenger->addMessage ( 'Answer added successfully.' );
				} else {
					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Answer not added.' );
				}
				$this->_redirector->gotoUrl ( $this->_modulename . '/answer/add' );
			}
		}
		
		$this->view->action = 'Add';
		$this->view->pageTitle = ADMIN_TITLE . 'Add Answer';
		$this->view->pgTitle = 'Add Answer';
		
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'answer/answer_menu.phtml' ) );
	}
	
	public function editAction() {
		$post = $this->getRequest ()->getPost ();
		
		$aid = ( int ) $this->_getParam ( 'aid' );
		
		$pg = ( int ) $this->_getParam ( 'page' );
		
		$rset = ( int ) $this->_getParam ( 'rset' );
		
		$qset = ( int ) $this->_getParam ( 'qset' );
		
		$qrset = ( int ) $this->_getParam ( 'qrset' );
		
		if ($rset && empty ( $post )) {
			$this->_session->redirect_url = $_SERVER ['HTTP_REFERER'];
			$this->view->redirect_url = $_SERVER ['HTTP_REFERER'];
		}
		if ($qset && empty ( $post )) {
			
			$this->_session->r_url = $_SERVER ['HTTP_REFERER'];
			$this->view->r_url = $_SERVER ['HTTP_REFERER'];
		
		}
		
		if ($qrset && empty ( $post )) {
			
			$this->_session->rset_url = $_SERVER ['HTTP_REFERER'];
			$this->view->rset_url = $_SERVER ['HTTP_REFERER'];
		
		}
		
		$pg = (! empty ( $pg )) ? $pg : 1;
		
		$this->view->pg = $pg;
		
		$answerModel = new Default_Model_Answer ();
		
		$this->view->action = 'Edit';
		
		$this->view->pageTitle = 'Edit answer details ';
		$this->view->pgTitle = 'Edit answer details ';
		
		$this->view->rset = $rset;
		$this->view->qset = $qset;
		$this->view->qrset = $qrset;
		
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'answer/answer_menu.phtml' ) );
		
		if (empty ( $post )) {
			$answerInfo = $answerModel->answerInfo ( $aid );
			
			if ($answerInfo) {
				$adminModel = new Default_Model_User ();
				$userInfo = $adminModel->getUserInfo ( 1, $answerInfo ['added_by'] );
				
				if ($userInfo) {
					$this->view->added_by = $userInfo ['firstname'];
					$this->view->added_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
				}
				
				if (! empty ( $answerInfo ['updated_by'] )) {
					$userInfo = $adminModel->getUserInfo ( 1, $answerInfo ['updated_by'] );
					if ($userInfo) {
						$this->view->updated_by = $userInfo ['firstname'];
						$this->view->updated_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
					}
				} else {
					$this->view->updated_by = " -- ";
				}
				
				$this->view->answerinfo = $answerInfo;
				
				return $this->render ( 'add' );
			} else {
				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Unable to fetch answer details.' );
			}
		} else {
			
			$is_error = false;
			$var_msg = 'Error encountered.' . " ";
			
			$answerInfo = $answerModel->answerInfo ( $aid );
			$adminModel = new Default_Model_User ();
			
			if (empty ( $post ['answer_text'] )) {
				$is_error = true;
				$var_msg .= 'Answer Text cannot be left empty.' . "<br/>";
			
			}
			
			if ($is_error == true) {
				
				$answerinfo = $answerModel->answerInfo ( $aid );
				
				foreach ( $post as $pk => $pv ) {
					$getAnswer [$pk] = $pv;
				}
				
				$getAnswer ['answer_id'] = $post ['aid'];
				$getAnswer ['add_date'] = $answerinfo ['add_date'];
				$getAnswer ['updated_datetime'] = $answerinfo ['updated_datetime'];
				
				$adminModel = new Default_Model_User ();
				$userInfo = $adminModel->getUserInfo ( 1, $answerInfo ['added_by'] );
				
				$this->view->added_by = $userInfo ['firstname'];
				$this->view->added_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
				
				$userInfo = $adminModel->getUserInfo ( 1, $answerInfo ['updated_by'] );
				
				if ($userInfo) {
					$this->view->updated_by = $userInfo ['firstname'];
					$this->view->updated_by .= ! empty ( $userInfo ['lastname'] ) ? ' ' . $userInfo ['lastname'] : '';
				} else {
					$this->view->updated_by = " -- ";
				}
				
				$this->view->answerinfo = $getAnswer;
				$this->view->errorMsg = $var_msg;
				
				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );
				
				return $this->render ( 'add' );
			} else {
				$post ['updated_by'] = $this->_session->session_data ['user_id'];
				
				if (! empty ( $post ['answer_text'] )) {
					$post ['answer_text'] = $this->_helper->Filterspchars ( $post ['answer_text'] );
				}
				if (! empty ( $post ['free_text_caption'] )) {
					$post ['free_text_caption'] = $this->_helper->Filterspchars ( $post ['free_text_caption'] );
				}
				
				$result = $answerModel->updateAnswer ( $post );
				
				if ($result) {
					$this->_flashMessenger->addMessage ( 'Answer detail updated successfully.' );
				} else {
					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Answer detail not updated.' );
				}
			}
			
			if ($rset) {
				$this->_redirector->gotoUrl ( $this->_session->redirect_url );
			} elseif ($qset) {
				
				$this->_redirector->gotoUrl ( $this->_session->r_url );
			} 

			elseif ($qrset) {
				
				$this->_redirector->gotoUrl ( $this->_session->rset_url );
			
			} else {
				$this->_redirector->gotoUrl ( $this->_modulename . '/answer/index/page/' . $pg );
			}
		}
	}
}