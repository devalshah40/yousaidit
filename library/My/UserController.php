<?php

Zend_Loader::loadFile('frontend_messages.php');

class My_UserController extends Zend_Controller_Action {
    
    public $_redirector = null;
    public $_flashMessenger = null;
    public $_modulename = null;
    public $_session = null;
    public $_actionname = null;
    public $_controllername = null;

    public function p($arr) {
        if (is_array($arr)){
            echo '<pre>' . print_r($arr, true) . "</pre>";
        } else{
            if (! empty($arr)){
                echo $arr . "<br>";
            } else{
                echo 'EMPTY String<br>';
            }
        }
    }

    public function init() {
        $request = $this->getRequest();
        
        $this->_redirector = $this->_helper->getHelper('Redirector');
        
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        
        $this->_modulename = $request->getModuleName();
        
        $this->_actionname = $request->getActionName();
        
        $this->_controllername = $request->getControllerName();
        
        $this->_baseurl = 'http://' . $_SERVER['SERVER_NAME'] . $request->getBaseURL();
        
        $this->_session = new Zend_Session_Namespace('yousaidit_frontend');
        
        $errorArr = $this->_flashMessenger->getMessages();
        
        if (! empty($errorArr)){
            $this->view->errorMsg = $errorArr[0];
        }
        
        $this->view->controllername = $this->_controllername;
        
        $this->view->actionname = $this->_actionname;
        
    //    echo  $this->_controllername."  --   ".$this->_actionname;
        
        $this->view->modulename = $this->_modulename;
        
        $this->view->siteTitle = SITE_TITLE;
        
        $this->view->app_version = APPLICATION_ENV . ' Version';
        
        if (preg_match('/iPhone/i', $_SERVER['HTTP_USER_AGENT']) == true || preg_match('/Android/i', $_SERVER['HTTP_USER_AGENT']) == true || preg_match('/SymbianOS/i', $_SERVER['HTTP_USER_AGENT']) == true){
            $this->_session->iPhone = true;
        }
        
        $response = $this->getResponse();
        
        if ($this->_modulename != 'mobile'){
            
            if (! empty($this->_session->session_data) || ! empty($this->_session->client_session_data)){
                if (! empty($this->_session->client_session_data)){
                    $this->view->group_id = $this->_session->client_session_data['group_id'];
                    $this->view->lastlogin = $this->_session->client_session_data['lastlogin_datetime'];
                    $this->view->firstname = $this->_session->client_session_data['firstname'];
                    $this->view->lastname = $this->_session->client_session_data['lastname'];
                } elseif (! empty($this->_session->session_data)){
                    $this->view->group_id = $this->_session->session_data['group_id'];
                    $this->view->lastlogin = $this->_session->session_data['lastlogin_datetime'];
                    $this->view->firstname = $this->_session->session_data['firstname'];
                    $this->view->lastname = $this->_session->session_data['lastname'];
                }
            }
            if ($this->_modulename == 'default' && $this->_controllername == 'index' && $this->_actionname == 'index'){
                $response->insert('header', '');
                $response->insert('footer', $this->view->render('footer.phtml'));
            } elseif ($this->_modulename == 'default' && $this->_controllername == 'notification' && $this->_actionname == 'index') {
                // just do nothing
            } else{
                $response->insert('header', $this->view->render('header.phtml'));
                $response->insert('footer', $this->view->render('footer.phtml'));
            }
        }
        
        $errorArr = $this->_flashMessenger->getMessages();
        
        if (! empty($errorArr)){
            $this->view->errorMsg = $errorArr[0];
            if (! empty($this->_session->session_data)){
                if ($this->_session->iPhone == true){
                    $response->insert('error', $this->view->render('ierror.phtml'));
                } else{
                    $response->insert('error', $this->view->render('error.phtml'));
                }
            }
        }
    }
}