<?php

class Admin_UserController extends My_AdminController {

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
        
        $controller = $this->_controllername;
        
        if ($controller == 'user'){
            
            $usergroupid = (int) $this->_request->getParam('gid');
            
            if ($usergroupid == 1){
                
                $user_type = 'Administrator';
            
            } elseif ($usergroupid == 2){
                
                $user_type = 'Member';
            
            } elseif ($usergroupid == 3){
                
                $user_type = 'Client';
            
            } else{
                
                $user_type = 'Administrator';
            }
            // user group id
            

            $this->view->usergroupid = $usergroupid;
            
            $this->view->pageTitle = ': Manage Site ' . $user_type;
            
            $post = $this->getRequest()->getPost();
            
            $userModel = new Default_Model_User();
            
            if (! empty($post['cmdDelete'])){
                $id = $post['chk'];
                $delete_count = 0;
                if (is_array($id) && count($id) > 0){
                    foreach($id as $k => $v){
                    	$userDetailsModel  =new Default_Model_UserDetails();
                    	$clientDetailsModel  =new Default_Model_Clientdetails;
                    	 if ($usergroupid == 2)
                    	 {
                    		$delete_res_userdetails = $userDetailsModel->deleteUserInfo($v);
                    	 }
                    	 else
                    	 {
                    	 	$delete_res_clientdetails = $clientDetailsModel->deleteUserInfo($v);
                    	  }
                    	    $delete_res = $userModel->deleteUserInfo($v);
	                        if ($delete_res){
	                            $delete_count ++;
	                        }
                    	
                    }
                }
                $this->_flashMessenger->addMessage($delete_count . ' User(s) deleted.');
                $this->_redirector->gotoUrl($this->_modulename . '/user/index/gid/' . $usergroupid);
            }
            
            $sort_field = 'firstname';
            
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
            
            $this->view->pageTitle = ADMIN_TITLE . 'Manage ' . $user_type;
            $this->view->pgTitle = 'Manage ' . $user_type . ' Listing';
            
            $this->view->gid = $usergroupid;
            
            $this->getResponse()->insert('navigation', $this->view->render('user/user_menu.phtml'));
            
            $userList = $userModel->listUsers($usergroupid, $sort_field, $field, $value);
            
            if ($userList){
                $pg = $this->_getParam('page');
                if (empty($pg)){
                    $this->view->pg = 1;
                } else{
                    $this->view->pg = $pg;
                }
                $this->view->paginator = Zend_Paginator::factory($userList);
                $this->view->paginator->setItemCountPerPage(REC_LIMIT);
                $this->view->paginator->setPageRange(PAGE_LINK_COUNT);
                $this->view->paginator->setCurrentPageNumber($pg);
            } else{
                
                $this->view->paginator = null;
            
            }
        } else{
            
            $this->_session->search_param = '';
        }
    }

    /**
     * Edit User Details Screen
     *
     */
    public function editAction() {
        $gid = (int) $this->_getParam('gid');
        $uid = (int) $this->_getParam('id');
        $pg = (int) $this->_getParam('page');
        $this->view->gid = $gid;
        
        if ($gid == 1){
            
            $user_type = 'Administrator';
        
        } elseif ($gid == 2){
            
            $user_type = 'Member';
            
            $userdetailsModel = new Default_Model_UserDetails();
            
            $userDetailsInfo = $userdetailsModel->getUserInfo($uid);
            $this->view->userDetailsInfo = $userDetailsInfo;
        
        } elseif ($gid == 3){
            
            $user_type = 'Client';
            
            $cModel = new Default_Model_Clientdetails();
            
            $getinfo_id = $cModel->getClientInfo($uid);
            $this->view->client_detail = $getinfo_id;
        
        }
        
        $countryModel = new Default_Model_Countries();
        $countryList = $countryModel->listCountry();
        
        // user intrest passed from countried model
        $userInterestModel = new Default_Model_UserInterests();
        $interesteList = $userInterestModel->listInterest();
        $this->view->interesteList = $interesteList;
        
        $this->view->countryList = $countryList;
        
        $userModel = new Default_Model_User();
        
        $userInfo = $userModel->getUserInfo($gid, $uid);
        
        if ($userInfo){
            $this->view->action = 'Edit';
            $this->view->userinfo = $userInfo;
            
            $this->view->pageTitle = 'Edit ' . $user_type . " details ";
            $this->view->pgTitle = 'Edit ' . $user_type . " details ";
            
            $this->view->gid = $gid;
            $this->getResponse()->insert('navigation', $this->view->render('user/user_menu.phtml'));
            
            $this->view->pg = $pg;
            
            return $this->render('add');
        } else{
            $is_error = true;
        }
        
        if ($is_error == true){
            $pg = (! empty($pg)) ? $pg : 1;
            $this->_flashMessenger->addMessage('Error(s) encountered. Unable to fetch user details.');
            $this->_redirector->gotoUrl($this->_modulename . '/user/index/gid/' . $gid . '/page/' . $pg);
        }
    }

    /**
     * Update Details Action
     *
     */
    public function editdetailsAction() {
    	
        $post = $this->getRequest()->getPost();
        $gid = (int) $this->_getParam('gid');
        $this->view->action = 'Edit';
        $this->view->gid = $gid;
        $userModel = new Default_Model_User();
        
        $userdetailsModel = new Default_Model_UserDetails();
        
        $userInfo = $userdetailsModel->getUserInfo($post['id']);
        
        $this->view->userInfo = $userInfo;
        
        $cModel = new Default_Model_Clientdetails();
        
        $getinfo_id = $cModel->getClientInfo($post['id']);
        
        $this->view->client_detail = $getinfo_id;
        
        
        $countryModel = new Default_Model_Countries();
        $countryList = $countryModel->listCountry();
        
        $this->view->countryList = $countryList;
        
        if (! empty($post)){
      
            $is_error = false;
            $var_msg = 'Error(s) encountered.' . "<br/><br/>";
            
            $userModel = new Default_Model_User();
            
            if (empty($post['firstname'])){
                $is_error = true;
                $var_msg .= 'First name cannot be left empty.' . "<br/>";
            }
            
            if (empty($post['lastname'])){
                $is_error = true;
                $var_msg .= 'Last name cannot be left empty.' . "<br/>";
            }
            
            if (empty($post['email'])){
                $is_error = true;
                $var_msg .= 'Email address cannot be left empty' . "<br/>";
            } else{
                $valid_email = $this->_helper->Emailvalidate($post['email']);
                if ($valid_email == false){
                    $is_error = true;
                    $var_msg .= ' Email address is invalid .' . "<br/>";
                } else{
                    
                    $clientModel = new Default_Model_Client();
                    $uniqueEmail = $clientModel->CheckUniqueEmail($post['email'], $post['gid'], $post['id']);
                    if ($uniqueEmail){
                        $is_error = true;
                        $var_msg .= ' Email address already Registered .' . "<br/>";
                    }
                }
            }
          if ($gid == 2){
            if (empty($post['address1'])){
                $is_error = true;
                $var_msg .= 'Address1 cannot be left empty.' . "<br/>";
            }
            
            if (empty($post['town'])){
                $is_error = true;
                $var_msg .= 'Town cannot be left empty.' . "<br/>";
            }
         
            if (empty($post['county'])){
                $is_error = true;
                $var_msg .= 'County cannot be left empty.' . "<br/>";
            }
            
            if (empty($post['postcode'])){
                $is_error = true;
                $var_msg .= 'Postcode cannot be left empty.' . "<br/>";
            }
          }
            if ($gid != 1){
                
                if (empty($post['country'])){
                    $is_error = true;
                    $var_msg .= "Country Cannot be left Empty" . "<br/>";
                }
                
                if ($gid == 3){
                    
                    if (empty($post['company_name'])){
                        $is_error = true;
                        $var_msg .= 'Company Name ' . "<br/>";
                    }
                
                }
            
            }
            if ($post['password'] != $post['c_password']){
                $is_error = true;
                $var_msg .= 'Password & confirm password do not match.' . "<br/>";
            }
             if ($gid == 2){
       		/*if (empty($post['gender'])){
                $is_error = true;
                $var_msg .= 'Please Select Your Gender.' . "<br/>";
            }*/
            
             }
            if ($is_error == true){
                $userModel = new Default_Model_User();
                
                $countryModel = new Default_Model_Countries();
                $countryList = $countryModel->listCountry();
                
                $post['group_id'] = $gid;
                
                $uid = $post['id'];
                
                $userinfo = $userModel->getUserInfo($post['group_id'], $uid);
                
                $userDetailsInfo = $userdetailsModel->getUserInfo($post['id']);
                
                $getinfo_id = $cModel->getClientInfo($post['id']);
                
           		foreach($post as $pk => $pv){
                    $getposted[$pk] = $pv;
                }
                foreach($post as $pk => $pv){
                    $userposted[$pk] = $pv;
                }
                
                $getposted['user_id'] = $post['id'];
                $getposted['username'] = $userinfo['username'];
                
                $getposted['created_date'] = $userinfo['created_date'];
                $getposted['lastlogin_datetime'] = $userinfo['lastlogin_datetime'];
                $getposted['updated_datetime'] = $userinfo['updated_datetime'];
                
                //	$clientposted ['updated_datetime'] = $userinfo ['country_id'];
                
/*
                $clientposted['country_id'] = $getinfo_id['country_id'];
                $clientposted['company_name'] = $getinfo_id['company_name'];
            
                $clientposted['country_id'] = $getinfo_id['country_id'];
           */  //   $clientposted['company_name'] = $post['company_name'];
             if ($gid == 3){
                
                $clientposted['country_id'] = $post['country'];
                $clientposted['company_name'] = $post['company_name'];
             }
              if ($gid == 2){
                
                $userposted['dob'] =   $userDetailsInfo['dob'];
              }
              $userposted['interest'] = $userDetailsInfo['interest'];
                $userposted['data_share'] = $userDetailsInfo['data_share'];
                $userposted['gender'] = $userDetailsInfo['gender'];
                $userposted['country_id'] =   $getposted['country'];
                //				$getposted ['updated_datetime'] = $userinfo ['updated_datetime'];
				
                $this->view->userDetailsInfo = $userposted;
                $this->view->userinfo = $getposted;
                if ($gid == 3){
                         $this->view->client_detail = $clientposted;
                }
                 $this->view->errorMsg = $var_msg;
       			 $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                
                if ($gid == 1){
                    
                    $user_type = 'Administrator';
                
                } elseif ($gid == 2){
                    
                    $user_type = 'Member';
                
                } elseif ($gid == 3){
                    
                    $user_type = 'Client';
                
                }
                
                // set page edit user type details
                $this->view->pageTitle = 'Edit ' . $user_type . " details ";
                $this->view->pgTitle = 'Edit ' . $user_type . " details ";
                
                $this->getResponse()->insert('navigation', $this->view->render('user/user_menu.phtml'));
                
                // user intrest passed from countried model
                $userInterestModel = new Default_Model_UserInterests();
                $interesteList = $userInterestModel->listInterest();
                $this->view->interesteList = $interesteList;
                                
                return $this->render('add');
            }

            else{
                
                $userUpdate = $userModel->updateUserDetails($post);
      			if ($userUpdate){
              		
                   if( $gid != 3 )
                   {
	                   $userDetailsUpdate = new Default_Model_UserDetails();
	                   $userDetailsUpdate1 = $userDetailsUpdate->updateUserDetails($post);
                   }
                   else
                   {
					   $cModel = new Default_Model_Clientdetails();
     				   $cModelUpdate = $cModel->updateClientDetails($post);
                   }
                   	$this->_flashMessenger->addMessage('Details successfully updated.');
                } else{
                    $this->_flashMessenger->addMessage('Error(s) encountered. Details not updated.');
                    
                    $this->_redirector->gotoUrl($this->_modulename . '/user/index/gid/' . $gid . '/page/' . $post['pg']);
                
                }
                
                $this->_redirector->gotoUrl($this->_modulename . '/user/index/gid/' . $gid . 'page/' . $post['pg']);
            
            }
        }
    }

    /**
     * Add User Details Screen
     *
     */
    public function adddetailsAction() {
        $post = $this->getRequest()->getPost();
        
        if (! empty($post)){
            $userModel = new Default_Model_User();
            
            $gid = 1;
            $user_type = 'Administrator';
            
            $existing_user = true;
            $is_error = false;
            
            if (empty($post['username'])){
                $is_error = true;
                $this->view->errorMsg = 'Cannot continue. "User Name" cannot be left empty.';
                $this->getResponse()->insert('error', $this->view->render('error.phtml'));
            } else{
                $existing_user = $userModel->existingUser($post['username'], 1);
            }
            
            if ($existing_user == false){ // username is available
                $this->view->action = 'Add';
                
                $is_error = false;
                
                if (empty($post['firstname']) || trim($post['firstname']) == ''){
                    $is_error = true;
                    $this->view->errorMsg = 'Cannot continue. "First Name" cannot be left empty.';
                } else if (empty($post['email']) || trim($post['email']) == ''){
                    $is_error = true;
                    $this->view->errorMsg = 'Cannot continue. "E-Mail" cannot be left empty.';
                } else if ($this->_helper->emailvalidate($post['email']) == false){
                    $is_error = true;
                    $this->view->errorMsg = 'Cannot continue. "E-Mail" is invalid or empty.';
                } else if (empty($post['password'])){
                    $is_error = true;
                    $this->view->errorMsg = 'Cannot continue. "Password" cannot be left empty.';
                } else if ($post['password'] != $post['c_password']){
                    $is_error = true;
                    $this->view->errorMsg = 'Cannot continue. "Password" & "Confirm Password" do not match.';
                }
                
                if ($is_error == true){
                    $this->getResponse()->insert('error', $this->view->render('error.phtml'));
                    foreach($post as $k => $v){
                        $userinfo[$k] = $v;
                    }
                } else{
                    $post['group_id'] = 1;
                    $user_id = $userModel->insertUser($post);
                    
                    if ($user_id){
                        $this->_flashMessenger->addMessage('User details successfully added.');
                    } else{
                        $this->_flashMessenger->addMessage('Error(s) encountered. User details not added.');
                    }
                    $this->_redirector->gotoUrl($this->_modulename . '/user/index/gid/1/page/' . $post['pg']);
                }
            } else{
                if ($is_error == false){
                    $this->view->errorMsg = 'Cannot continue.' . $user_type . ' with username "' . $post['username'] . '" already exists.';
                }
                $this->getResponse()->insert('error', $this->view->render('error.phtml'));
            }
            
            foreach($post as $k => $v){
                $userinfo[$k] = $v;
            }
            
            $this->view->action = 'Add';
            $this->view->pg = 1;
            $this->view->gid = $gid;
            $this->view->userinfo = $userinfo;
            
            $this->view->pageTitle = 'Add New ' . $user_type;
            $this->view->pgTitle = 'Add New ' . $user_type;
            $this->getResponse()->insert('navigation', $this->view->render('user/user_menu.phtml'));
            
            return $this->render('add');
        } else{
            $this->_flashMessenger->addMessage('Error(s) encountered. User details not added.');
        }
        $group_id = ! empty($post['group_id']) ? $post['group_id'] : 1;
        $this->_redirector->gotoUrl($this->_modulename . '/user/index/gid/' . $group_id . '/page/' . $post['pg']);
    }

    /**
     * Edit User Details Screen
     *
     */
    public function addAction() {
        
        $user_type = 'Administrator';
        $gid = 1;
        
        $this->view->pg = 1;
        $this->view->gid = $gid;
        $this->view->action = 'Add';
        $this->view->pageTitle = ADMIN_TITLE . 'Add New Site ' . $user_type;
        $this->view->pgTitle = 'Add New Site ' . $user_type;
        $this->getResponse()->insert('navigation', $this->view->render('user/user_menu.phtml'));
    }

}