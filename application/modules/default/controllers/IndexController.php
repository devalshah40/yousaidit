<?php

class IndexController extends My_UserController {

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
        
        $this->view->pageTitle = ': Home';
        $this->view->header_title = ' Home';
        
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if ($result == true){
            $this->_redirector->gotoUrl('user-home.html');
        }
    }

    public function aboutusAction() {
        $this->view->pageTitle = ': About Us';
        $this->view->header_title = ' About Us';
        $response = $this->getResponse();
    }

    public function termsofuseAction() {
        $this->view->pageTitle = ': Terms Of Use';
        $this->view->header_title = ' About Us';
    }

    public function privacypolicyAction() {
        $this->view->pageTitle = ': Privacy Policy';
        $this->view->header_title = ' Privacy Policy';
    }

    public function settingsAction(){
		$result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        if( !empty($this->_session->fbsession_data['fb_login_flag']) && $this->_session->fbsession_data['fb_login_flag'] === true){
            $this->view->fb_login_flag = $this->_session->fbsession_data['fb_login_flag'];
        }else{
            $this->view->fb_login_flag = null;
        }
        
	    $this->view->leftmenu = $this->view->render('leftmenu.phtml');
	}
	
	public function facebookAction(){
        $config = Zend_Registry::get('config');
        $facebook = new My_Facebook( array('appId' => $config->fb->app_id, 'secret' => $config->fb->app_secret, 'cookie' => true) );
        $session = $facebook->getSession();
        
        if (! empty($session)){
            $uid = $facebook->getUser();
            $user = $facebook->api('/me');
            
            if (! empty($user)){
                //echo '<pre>';        print_r($user);        echo '</pre><br/>';
                $this->_session->fbsession_data['fb_login_flag'] = true;
                $this->_session->fbsession_data['fb_oauth_uid']  = $uid;
                $this->_session->fbsession_data['fb_user_name'] = $user['name'];
            }else{
                $this->_flashMessenger->addMessage('Error(s) encountered, Cannot login to facebook');
            }
            //echo '<pre>';        print_r($this->_session->session_data);        echo '</pre><br/>';
            //exit;
            $this->_redirector->gotoUrl('settings.html');
        }else{
            $login_url = $facebook->getLoginUrl();
            header("Location: " . $login_url);
            exit;
        }
    }
    
    public function facebooklogoutAction(){
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
            
        $this->_session->fbsession_data = null;
        $this->_redirector->gotoUrl('user-home.html');
    }

    public function twitterAction(){
        
    }
    
    public function categoryofinterestAction(){
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ' : Categories of Interest';
        $this->view->header_title = ' Categories of Interest';
        
        $post = $this->getRequest()->getPost();
        
        $userDetailModel = new Default_Model_UserDetails();
        
        if( !empty($post) ){
            if( count($post['cmbCatId']) > MAX_CATEGORY_COUNT ){
                $this->view->cmbNotification = $post['cmbNotification'];
                $this->view->categories_of_interest = $post['cmbCatId'];
                
                $this->view->errorMsg = 'Error(s) encountered cannot continue.' . "<br/>" . 'A maximum of '.MAX_CATEGORY_COUNT.' category selection is allowed.';
            }else{
                if( !empty($post['cmbCatId']) ){
                    $data['categories_of_interest'] = serialize($post['cmbCatId']);
                }else{
                    $data['categories_of_interest'] = null;
                }
    
                $data['id'] = $this->_session->session_data['user_id'];
    
                $result = $userDetailModel->updateCategoriesOfInterest($data);
                
                if($result){
                    $this->_flashMessenger->addMessage ('Categories of interest updated successfully');
                }else{
                    $this->_flashMessenger->addMessage ('Error(s) encountered. Categories of interest not updated');
                }
                $this->_redirector->gotoUrl ( 'categories-of-interest.html' );
            }
        }else{
            $userInfo = $userDetailModel->getUserInfo($this->_session->session_data['user_id']);
            $this->view->cmbNotification = $userInfo['notification_status'];
            $this->view->categories_of_interest = unserialize($userInfo['categories_of_interest']);
        }
        
        $this->view->max_categ = MAX_CATEGORY_COUNT;
        $this->view->leftmenu = $this->view->render('leftmenu.phtml');
        
        $CategoryModel = new Default_Model_Categories ();
		$catInfo = $CategoryModel->listcategories ( 'categories_name' );
		$this->view->catinfo = $catInfo;

    }
    
    public function notificationsAction(){
        $result = $this->_helper->sessioncheck($this->_session->session_data, $this->_session->session_data['login_flag']);
        
        if (empty($result)){
            $this->_flashMessenger->addMessage(LOGIN_REQUIRED);
            $this->_redirector->gotoUrl('index.html');
        }
        
        $this->view->pageTitle = ' : Notifications';
        $this->view->header_title = ' Notifications';
        
        $post = $this->getRequest()->getPost();
        
        $userDetailModel = new Default_Model_UserDetails();
        
        $this->view->leftmenu = $this->view->render('leftmenu.phtml');
        
        if( !empty($post) ){
            $data['notification_status'] = $post['cmbNotification'];
            $data['report_violation_flag'] = $post['cmbReportViolation'];
            
            $data['id'] = $this->_session->session_data['user_id'];
    
            $result = $userDetailModel->updateNotifications($data);
            
            if($result){
                $this->_flashMessenger->addMessage ('Notification updated successfully');
            }else{
                $this->_flashMessenger->addMessage ('Error(s) encountered. Notification not updated');
            }

            $this->_redirector->gotoUrl ( 'notifications.html' );
                        
        }else{
            $userInfo = $userDetailModel->getUserInfo($this->_session->session_data['user_id']);
            $this->view->cmbNotification = $userInfo['notification_status'];
            $this->view->cmbReportViolation = $userInfo['report_violation_flag'];
        }
        
    }
}
