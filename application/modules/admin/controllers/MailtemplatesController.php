<?php
class Admin_MailtemplatesController extends My_AdminController {
	
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
		
		$mailModel = new Default_Model_MailTemplate ();
		
		$maillist = $mailModel->listMailtemplates ( 'mailid' );
		
		$this->view->maillist = $maillist;
		
		$this->view->pageTitle = ADMIN_TITLE . 'Manage Mail templates';
		$this->view->pgTitle = 'Mail Templates';
		
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'mailtemplates/mail_menu.phtml' ) );
	
	}
	
	public function editAction() {
		
		$id = ( int ) $this->_getParam ( 'id' );
		
		$mailModel = new Default_Model_MailTemplate ();
		
		$post = $this->getRequest ()->getPost ();
		
		$this->view->action = 'edit';
		
		$getTemplate = $mailModel->getTemplate ( $id );
		
		$this->view->getTemplate = $getTemplate;
		$this->view->pageTitle = ADMIN_TITLE . 'Manage Mail templates';
		$this->view->pgTitle = 'Edit Mail Templates';
		
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'mailtemplates/mail_menu.phtml' ) );
		
		if (empty ( $post )) {
			
			if ($getTemplate) {
				
				$this->view->pageTitle = ADMIN_TITLE . 'Manage Mail Templates';
				$this->view->pgTitle = 'Edit Mail Templates';
				
				$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'mailtemplates/mail_menu.phtml' ) );
				
				return $this->render ( 'edit' );
			
			} else {
				
				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Unable to fetch mailtemplates details.' );
				$this->_redirector->gotoUrl ( $this->_modulename . '/mailtemplates/index/' );
			}
		
		} else {
			
			if (empty ( $post ['description'] ) || trim ( $post ['description'] ) == '') {
				$is_error = true;
				$this->view->errorMsg = 'Cannot continue. "description" cannot be left empty.';
			} else if (empty ( $post ['subject'] ) || trim ( $post ['subject'] ) == '') {
				$is_error = true;
				$this->view->errorMsg = 'Cannot continue. "subject" cannot be left empty.';
			} else if (empty ( $post ['from_caption'] )) {
				$is_error = true;
				$this->view->errorMsg = 'Cannot continue. "from_caption" cannot be left empty.';
			} else if ($this->_helper->emailvalidate ( $post ['from_email'] ) == false) {
				$is_error = true;
				$this->view->errorMsg = 'Cannot continue. "E-Mail" is invalid or empty.';
			} else if (empty ( $post ['mail_text'] )) {
				$is_error = true;
				$this->view->errorMsg = 'Cannot continue. "mail_text" cannot be left empty.';
			}
			
			if ($is_error == true) {
				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );
				foreach ( $post as $k => $v ) {
					$mailinfo [$k] = $v;
				}
			} else {
				
				$result = $mailModel->updateMailTemplates ( $post );
				
				if ($result) {
					$this->_flashMessenger->addMessage ( 'MailTemplate detail updated successfully.' );
				} else {
					$getTemplate = $mailModel->getTemplate ( $id );
					
					return $this->render ( 'edit' );
				}
				$this->_redirector->gotoUrl ( $this->_modulename . '/mailtemplates/index/' );
			}
		}
	}
}