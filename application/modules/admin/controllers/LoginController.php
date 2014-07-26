<?php

class Admin_LoginController extends My_AdminController {

	/**
	 * init() function, call init() function of parent class
	 *
	 */
	public function init(){
		parent::init();
    }

    /**
     * Default Action
     *
     */
    public function indexAction(){
		$this->view->pageTitle = 'Admin Login Page';
    }

	/**
	 * Logout
	 *
	 */
	public function logoutAction(){
		$this->_session->session_data = null;
		$this->_redirector->gotoUrl($this->_modulename . '/login/index');
    }

	/**
	 * Verify admin login details
	 *
	 */
    public function processAction(){
		$request = $this->getRequest();
		$txtUserName   	= $request->getPost('txtUserName');
		$txtPassword 	= $request->getPost('txtPassword');

        if ( empty($txtUserName) || empty($txtPassword) ) {
        	$this->_flashMessenger->addMessage('Missing Login Information');
            $this->_redirector->gotoUrl($this->_modulename . '/login/index');
        }else{
			$userModel = new Default_Model_User();
			$result = $userModel->checkLoginInfo($txtUserName, $txtPassword, 1);
			if( $result ){
				$lastlogin	   = date("jS M Y H:i", strtotime($result['lastlogin_datetime']));
				$result_1 	   = $userModel->updateLastLoginDateTime($result['user_id']);
				$session_array = array(	"login_flag" 	=> true,
										"user_id"		=> $result['user_id'],
										"username"		=> $result['username'],
										"firstname"		=> $result['firstname'],
										"lastname"		=> $result['lastname'],
										"email"			=> $result['email'],
										"lastlogin"		=> $lastlogin					);
				$this->_session->session_data = $session_array;
			}else{
	        	$this->_flashMessenger->addMessage('Either username / password is incorrect');
    	        $this->_redirector->gotoUrl($this->_modulename . '/login/index');
			}
        }
		$this->_redirector->gotoUrl($this->_modulename . '/index/index');
    }

}