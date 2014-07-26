<?php
class Admin_UserinterestController extends My_AdminController {
	/**
	 * init function
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
	

		$UserInterestModel = new Default_Model_UserInterests();

		

		if (! empty ( $post ['cmdDelete'] )) {

			$id = $post ['chk'];
			
			$delete_count = 0;
			
			if (is_array ( $id ) && count ( $id ) > 0) {

				foreach ( $id as $k => $v ) {

					$delete_res = $UserInterestModel->deleteUserInterest ( $v );

					if ($delete_res) {

						$delete_count ++;

					}

				}

			}

			$this->_flashMessenger->addMessage ( $delete_count . ' User\'s Interest(s) deleted.' );

			$this->_redirector->gotoUrl ( $this->_modulename . '/userinterest/index/page/1' );

		}

		

		$sort_field = '';

		

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

		

		$this->view->pageTitle = ADMIN_TITLE . 'Manage User Interest';

		$this->view->pgTitle = 'User Interest Listing';

		

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'userinterest/userinterest_menu.phtml' ) );

		

		$userinterestList = $UserInterestModel->listInterests ( $sort_field, $field, $value );

		

		if ($userinterestList) {

			$pg = $this->_getParam ( 'page' );

			$this->view->pg = ! empty ( $pg ) ? $pg : 1;

			

			$this->view->paginator = Zend_Paginator::factory ( $userinterestList );

			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );

			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );

			$this->view->paginator->setCurrentPageNumber ( $pg );

		} else {

			$this->view->paginator = null;

		}
	}
	
	public function editAction() {

		$post = $this->getRequest ()->getPost ();

	
		$intrest_id = ( int ) $this->_getParam ( 'intrest_id' );

		$pg = $this->_getParam ( 'page' );
		
		$pg = (! empty ( $pg )) ? $pg : 1;

		

		$this->view->pg = $pg;
		
		$UserInterestModel = new Default_Model_UserInterests ();

		$this->view->action = 'Edit';


		$this->view->pageTitle = 'Edit User\'s Interest details ';

		$this->view->pgTitle = 'Edit User\'s Interest details ';



	
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'userinterest/userinterest_menu.phtml' ) );
		

		if (empty ( $post )) {

			$UserInterestInfo = $UserInterestModel->UserInterestInfo ( $intrest_id );

			

			if ($UserInterestInfo) {

						

				$this->view->UserInterestInfo = $UserInterestInfo;

				

				return $this->render ( 'add' );

			} else {

				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Unable to fetch User\'s Interest details.' );

			}

		} else {

			

			$is_error = false;

			$var_msg = 'Error encountered.' . " ";

			

			$UserInterestInfo = $UserInterestModel->UserInterestInfo ( $intrest_id );

	
			if (empty ( $post ['name'] )) {

				$is_error = true;

				$var_msg .= 'User\'s  Interest cannot be left empty.' . "<br/>";


			}
		

			if ($is_error == true) {
		

				$UserInterestInfo = $UserInterestModel->UserInterestInfo ( $intrest_id );

			

				
				foreach ( $post as $pk => $pv ) {

					$userinterestInfo [$pk] = $pv;

				}

				$userinterestInfo ['name'] = $UserInterestInfo ['name'];

			//	$userinterestInfo ['updated_datetime'] = $UserInterestInfo ['updated_datetime'];

	
				

				$this->view->UserInterestInfo = $userinterestInfo;

				$this->view->errorMsg = $var_msg;

				

				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );

				

				return $this->render ( 'add' );

			} else {

					

				$result = $UserInterestModel->updateUserInterestInfo ( $post );

				

				if ($result) {

					$this->_flashMessenger->addMessage ( 'User\'s  Interest updated successfully.' );

				} else {

					$this->_flashMessenger->addMessage ( 'Error(s) encountered. User\'s  Interest detail not updated.' );

				}
           
				$this->_redirector->gotoUrl ( $this->_modulename . '/userinterest/index/' );
			}

			


		}

	}

	public function addAction() {

 		$UserInterestModel = new Default_Model_UserInterests ();
		
		

		$this->view->action = 'Add';

		$post = $this->getRequest ()->getPost ();

		

		$is_error = false;

		$var_msg = 'Error .' . " ";

		

		if (! empty ( $post )) {

			

			if (empty ( $post ['name'] )) {

				$is_error = true;

				$var_msg .= 'User\'s  Interest cannot be left empty.' . " ";

			}
			
			
			if ($is_error == true) {

				foreach ( $post as $pk => $pv ) {

					$userinterestInfo [$pk] = $pv;

				}

				

				$this->view->UserInterestInfo = $userinterestInfo;

				$this->view->errorMsg = $var_msg;

				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );

			

			} else {

				
				

				if (! empty ( $post ['name'] )) {

					$post ['name'] = $this->_helper->Filterspchars ( $post ['name'] );

				}

				$intrest_id = $UserInterestModel->insertUserInterest ( $post );

				

				if ($intrest_id) {

					$this->_flashMessenger->addMessage ( 'User\'s Interest added successfully.' );

				} else {

					$this->_flashMessenger->addMessage ( 'Error(s) encountered. User\'s Interest not added.' );

				}

				$this->_redirector->gotoUrl ( $this->_modulename . '/userinterest/add' );

			}

		}

		

		$this->view->action = 'Add';

		$this->view->pageTitle = ADMIN_TITLE . 'Add User\'s Interest';

		$this->view->pgTitle = 'Add User\'s Interest';

		

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'userinterest/userinterest_menu.phtml' ) );
	
	}
}
