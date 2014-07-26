<?php

Zend_Loader::loadFile('backend_messages.php');

class My_AdminController extends Zend_Controller_Action {

	public 	$_redirector 		= null;
	public 	$_flashMessenger 	= null;
	public  $_modulename		= null;
	public 	$_session		 	= null;
	public  $_actionname		= null;
	public  $_controllername	= null;

	public function p($arr){
		if( is_array($arr)){
			echo '<pre>' . print_r($arr, true) . "</pre>";
		}else{
			if( !empty($arr)){
				echo $arr . "<br>";
			}else{
				echo 'EMPTY String<br>';
			}
		}
	}

    public function init(){
    	$request				= $this->getRequest();

		$this->_redirector 		= $this->_helper->getHelper('Redirector');

		$this->_flashMessenger 	= $this->_helper->getHelper('FlashMessenger');

		$this->_modulename 		= $request->getModuleName();

		$this->_actionname		= $request->getActionName();

		$this->_controllername	= $request->getControllerName();

		$this->_baseurl			= 'http://' . $_SERVER['SERVER_NAME'] . $request->getBaseURL();

		$this->_session 		= new Zend_Session_Namespace('yousaidit_backend');

		$errorArr = $this->_flashMessenger->getMessages();

		if( !empty($errorArr) ){
			$this->view->errorMsg = $errorArr[0];
		}

		$this->view->controllername 	= $this->_controllername;

		$this->view->actionname 		= $this->_actionname;

		$this->view->modulename 		= $this->_modulename;

		$this->view->siteTitle 			= ADMIN_TITLE;

		// -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -

		$result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);

		if( empty($result) ){
			$this->_redirector->gotoUrl($this->_helper->sessioncheck->getredirecturl());
		}else{
			$this->view->admin_name = $this->_session->session_data['firstname'];
			$this->view->lastlogin  = $this->_session->session_data['lastlogin'];
		}

		// -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -  -

		$response = $this->getResponse();

        if( $this->getRequest()->getControllerName() == 'login'){
			$response->insert('header', $this->view->render('login_header.phtml'));
			$response->insert('footer', $this->view->render('login_footer.phtml'));
		}else{
			$response->insert('header', $this->view->render('header.phtml'));
			$response->insert('footer', $this->view->render('footer.phtml'));
		}

		$errorArr = $this->_flashMessenger->getMessages();

		if( !empty($errorArr) ){
			$this->view->errorMsg = $errorArr[0];
			$response->insert('error', $this->view->render('error.phtml'));
		}

    }
}