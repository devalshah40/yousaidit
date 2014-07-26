<?php

class Admin_ResponsesetController extends My_AdminController {

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
    	
    	$this->_session->search_param = '';
        $post = $this->getRequest()->getPost();

        $responseModel = new Default_Model_ResponseSet();

        if (! empty($post['cmdDelete'])){
            $id = $post['chk'];
            $delete_count = 0;
            if (is_array($id) && count($id) > 0){
                foreach($id as $k => $v){
                    $delete_res = $responseModel->deleteResponseSetInfo($v);
                    if ($delete_res){
                        $delete_count ++;
                    }
                }
            }
            $this->_flashMessenger->addMessage($delete_count . ' Response Set(s) deleted.');
            $this->_redirector->gotoUrl($this->_modulename . '/responseset/index/');
        }

        $sort_field = 'last_update_datetime DESC';

        $field = null;
        $value = null;

        if (! empty($post['cmdSubmit'])){
            $field = $post['cmbKey'];
            $value = $post['txtValue'];

            $search_param = array("field" => $field, "value" => $value);

            $this->_session->search_param = $search_param;
            $this->view->result_type = 'filtered';
        } else{
            if ($this->_getParam('clear') == "results"){
                $search_param = array();
                $this->_session->search_param = $search_param;
            }
            $search_param = $this->_session->search_param;
            if (! empty($search_param)){
                $field = $this->_session->search_param['field'];
                $value = $this->_session->search_param['value'];
                $this->view->result_type = 'filtered';
            }
        }

        $this->view->pageTitle = ADMIN_TITLE . 'Manage Response Sets';
        $this->view->pgTitle = 'Response Set Listing';

        $this->getResponse()->insert('navigation', $this->view->render('responseset/response_menu.phtml'));

        $responseList = $responseModel->listResponseSets($sort_field, $field, $value);

        if ($responseList){
            $pg = $this->_getParam('page');
            $this->view->pg = ! empty($pg) ? $pg : 1;

            $this->view->paginator = Zend_Paginator::factory($responseList);
            $this->view->paginator->setItemCountPerPage(REC_LIMIT);
            $this->view->paginator->setPageRange(PAGE_LINK_COUNT);
            $this->view->paginator->setCurrentPageNumber($pg);
        } else{
            $this->view->paginator = null;
        }
    }

    public function addAction() {

        $is_error = false;
        $var_msg = 'Error encountered.' . " ";
        $responseModel = new Default_Model_ResponseSet();
        $this->view->action = 'Add';
        $post = $this->getRequest()->getPost();

        $this->view->pageTitle = 'Add Response Set';
        $this->view->pgTitle = 'Add Response Set';

        $this->getResponse()->insert('navigation', $this->view->render('responseset/response_menu.phtml'));

        if (! empty($post)){

            if (empty($post['title'])){
                $is_error = true;
                $var_msg .= 'Title cannot be left empty.' . " ";
            }

            if ($is_error == true){
                foreach($post as $pk => $pv){
                    $resInfo[$pk] = $pv;
                }
                $this->view->resInfo = $resInfo;
                $this->view->errorMsg = $var_msg;
                $this->getResponse()->insert('error', $this->view->render('error.phtml'));

            } else{
                $rs_id = $responseModel->insertResponseSet($post);

                if ($rs_id){
                    $this->_flashMessenger->addMessage('Response Set added successfully.');
                } else{
                    $this->_flashMessenger->addMessage('Error(s) encountered. Response Set not added.');
                }
                $this->_redirector->gotoUrl($this->_modulename . '/responseset/add');
            }
        }
    }

    public function editAction() {

        $id = (int) $this->_getParam('id');

        $pg = (int) $this->_getParam('page');

        $pg = (! empty($pg)) ? $pg : 1;

        $responseModel = new Default_Model_ResponseSet();

        $post = $this->getRequest()->getPost();

        $this->view->action = 'Edit';
        $this->view->pageTitle = 'Edit Response Set details ';
        $this->view->pgTitle = 'Edit Response Set details  ';
        $this->getResponse()->insert('navigation', $this->view->render('responseset/response_menu.phtml'));

        if (empty($post)){
            $resInfo = $responseModel->responseInfo($id);

            if ($resInfo){
                $this->view->resInfo = $resInfo;

                $this->view->pg = $pg;

                return $this->render('add');
            }
        } else{
            $is_error = false;
            $var_msg = 'Error encountered.' . " ";

            if (empty($post['title'])){
                $is_error = true;
                $var_msg .= 'Title cannot be left empty.' . "<br/>";

            }

            $this->view->title = $post['title'];
            $this->view->description = $post['description'];

            if ($is_error == true){

                $resInfo = $responseModel->responseInfo($id);

                foreach($post as $pk => $pv){
                    $getpost[$pk] = $pv;
                }

                $getpost['add_date'] = $resInfo['add_date'];
                $getpost['rs_id'] = $resInfo['rs_id'];
                $getpost['last_update_datetime'] = $resInfo['last_update_datetime'];

                $this->view->resInfo = $getpost;
                $this->view->errorMsg = $var_msg;
                $this->getResponse()->insert('error', $this->view->render('error.phtml'));

                return $this->render('add');
            } else{
                $result = $responseModel->updateResponseSet($post);

                if ($result){
                    $this->_flashMessenger->addMessage('Response Set detail updated successfully.');
                } else{
                    $this->_flashMessenger->addMessage('Error(s) encountered. Response Set detail not updated.');
                }
                $this->_redirector->gotoUrl($this->_modulename . '/responseset/index/page/1');
            }
        }
    }

	public function answersAction() {
		$post = $this->getRequest ()->getPost ();
		$rs_id = ( int ) $this->_getParam ( 'id' );

		$rsaModel = new Default_Model_ResponseSetAnswer();

		if( !empty($post['cmdSortOrder']) ){
        	if( is_array($post['txtSortOrder']) && count($post['txtSortOrder']) > 0) {
        		for($s=0; $s < count($post['txtSortOrder']); $s++){
        		    $freeText = $post['txtFreeText' . $post['h_rsa_id'][$s]];
        		    if( !empty($freeText) && $freeText != '--'){
        		        $sorder_res = $rsaModel->updateAnswerSortOrder($post['h_rsa_id'][$s], $post['txtSortOrder'][$s], $post ['txtAnswerValue'] [$s], $freeText );
        		    } elseif(! empty ( $post ['txtAnswerValue'] ) ) {
        		        $sorder_res = $rsaModel->updateAnswerSortOrder($post['h_rsa_id'][$s], $post['txtSortOrder'][$s], $post ['txtAnswerValue'] [$s]);
        		    }else{  
        		      
        		    	  $sorder_res = $rsaModel->updateAnswerSortOrder($post['h_rsa_id'][$s], $post['txtSortOrder'][$s]);
        		    
        		    }
        		}
        	}
			$this->_flashMessenger->addMessage('Sort order, free text caption successfully updated.');
			$this->_redirector->gotoUrl($this->_modulename . '/responseset/answers/id/'. $post['rs_id'] ."/page/1");
		}

		if (! empty ( $post ['cmdDelete'] )) {
			$id = $post ['chk'];
			$delete_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				foreach ( $id as $k => $v ) {
					$delete_res = $rsaModel->deleteResponseSetAnswerAssociation ($v);
					if ($delete_res) {
						$delete_count ++;
					}
				}
			}
			$this->_flashMessenger->addMessage ( $delete_count . ' Answer(s) deleted.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/responseset/answers/id/' . $post ['rs_id'] . "/page/1" );
		}

		if (empty ( $rs_id )) {
			$this->_flashMessenger->addMessage ( 'Invalid / Missing Response Set Id.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/responseset/index/page/' . $this->_getParam ( 'page' ) );
		} else {
			$responseSetModel = new Default_Model_ResponseSet();
			$responseSetInfo = $responseSetModel->responseInfo( $rs_id );
			if ($responseSetInfo) {
				$this->view->rs_id = $rs_id;
				$this->view->responseSetInfo = $responseSetInfo;
			} else {
				$this->_flashMessenger->addMessage ( 'Invalid Response Set Id.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/responseset/index/page/' . $this->_getParam ( 'page' ) );
			}
		}

		$sort_field = 'rsa.sort_order';

		$this->view->pageTitle = ADMIN_TITLE . 'Manage Responset Answers';
		$this->view->pgTitle = 'Response Set - Answer Listing';

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'responseset/answer_menu.phtml' ) );

		$answerList = $rsaModel->listResponseSetAnswer ( $sort_field, 'rsa.rs_id', $rs_id, true );

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
		$rsaModel = new Default_Model_ResponseSetAnswer();

		$post = $this->getRequest ()->getPost ();

		//$this->p($post); exit;

		if (! empty ( $post )) {
			$existing = $rsaModel->existingAns ( $post ['id'], $post ['answer_id'] );

			if ($existing == true) {
				$this->_flashMessenger->addMessage ( 'Answer already added for selected response set.' );
			} else {
			    $ansModel = new Default_Model_Answer();
				foreach ( $post ['answer_id'] as $k => $v ) {
				    $arr = explode("-", $v);
				    $answer_id = $arr[0];
				    $free_text = $arr[1];
				    if($free_text == 'y'){
				        $ansInfo = $ansModel->answerInfo($answer_id);
				        if($ansInfo['free_text'] == 'y'){
				            $free_text_caption = $ansInfo['free_text_caption'];
				        }else{
				            $free_text_caption = null;
				        }
				    }else{
				        $free_text_caption = null;
				    }
					$rsa_id = $rsaModel->insertResponseSetAnswerAssociation ( $post ['id'], $answer_id, $free_text_caption );
				}
				if ($rsa_id) {
					$this->_flashMessenger->addMessage ( 'Answer successfully added to selected response set.' );
				} else {
					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Answer not added to response set.' );
				}
			}
			$this->_redirector->gotoUrl ( $this->_modulename . '/responseset/answers/id/' . $post ['id'] . "/page/1" );
		} else {
			$rs_id = ( int ) $this->_getParam ( 'id' );

			if (empty ( $rs_id )) {
				$this->_flashMessenger->addMessage ( 'Cannot add answer. Invalid / Missing Response Set Id.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/responseset/index/page/1' );
			} else {
				$responseSetModel = new Default_Model_ResponseSet();
				$responseSetInfo = $responseSetModel->responseInfo( $rs_id );
				if ($responseSetInfo) {
					$this->view->rs_id = $rs_id;
					$this->view->responseSetInfo = $responseSetInfo;

					$ansModel = new Default_Model_Answer ();
					$answers = $ansModel->listUnLinkedAnswersRS ($rs_id);

					if( $answers ){
                        $pg              = $this->_getParam('page');
                        $this->view->pg  = !empty($pg)?$pg:1;

                        $this->view->paginator = Zend_Paginator::factory($answers);
                        $this->view->paginator->setItemCountPerPage(REC_LIMIT);
                        $this->view->paginator->setPageRange(PAGE_LINK_COUNT);
                        $this->view->paginator->setCurrentPageNumber( $pg );

                        $this->view->answers_block = $this->view->render('responseset/answer_block.phtml');
                    }else{
                        $this->view->paginator = null;
                    }

				} else {
					$this->_flashMessenger->addMessage ( 'Cannot add answer to response set. Invalid Response Set Id.' );
					$this->_redirector->gotoUrl ( $this->_modulename . '/responseset/index/page/1' );
				}
			}

			$this->view->action = 'Add';
			$this->view->pageTitle = ADMIN_TITLE . 'Add Answer To Response Set';
			$this->view->pgTitle = 'Add Answer To Response Set';

			$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'responseset/answer_menu.phtml' ) );
		}
	}

    public function searchanswerAction(){
        $this->_helper->viewRenderer->setNoRender(true);

    	$response = $this->getResponse();
		$response->insert('header', '');
		$response->insert('footer', '');

    	$this->_helper->layout()->disableLayout();

    	$rs_id = (int)$this->_getParam('id');
    	$search_text = $this->_getParam('search_text');

        $rsModel = new Default_Model_ResponseSet();
	    $rsInfo = $rsModel->responseInfo($rs_id);

	    if($rsInfo){
	        $this->view->rs_id  = $rs_id;
            $this->view->rsInfo = $rsInfo;

            $ansModel = new Default_Model_Answer();
            $answers = $ansModel->listUnLinkedAnswersRS($rs_id, true, $search_text);

            $pg = $this->_getParam ( 'page' );
            $this->view->pg = ! empty ( $pg ) ? $pg : 1;

            $this->view->search_flag = true;

			$this->view->paginator = $answers;

            $answers_block = $this->view->render('responseset/answer_block.phtml');

            echo $answers_block;
	    }else{
	        echo '<br/><br/><br/><br/><div align="center">No Answers Found</div><br/><br/><br/><br/>';
	    }
        return true;
    }

}

