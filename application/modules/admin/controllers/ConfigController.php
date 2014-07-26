<?php

// Config Controller
// Used for viewing / updating site wide configurable parameters

class Admin_ConfigController extends My_AdminController {

	/**
	 * init function
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
    	$this->view->pgTitle 	= 'Configuration';
		$this->view->pageTitle 	= $this->view->pgTitle;
		$this->getResponse()->insert('navigation', $this->view->render('config/config_menu.phtml'));

		$configModel			= new Default_Model_Configuration();
		$configGroupModel 		= new Default_Model_ConfigurationGroup();

		$config_group_values 	= $configGroupModel->fetchAll();

		$this->view->group 		= $config_group_values;

		$post					= $this->getRequest()->getPost();

		if( !empty($post) ){
			$config_group_id 	= $post['id'];
			$config_id 			= $post['cid'];
			$result 			= $configModel->updateConfigOption($post['txtValue'], $config_id);
			if( $result ){
				$this->_flashMessenger->addMessage('Configuration parameter updated successfully');
			}else{
				$this->_flashMessenger->addMessage('Error(s) encountered. Configuration parameter not updated');
			}
			$this->_redirector->gotoUrl($this->_modulename . '/config/index/id/' . $config_group_id);
		}else{
			$config_group_id 	= $this->_getParam('id');
			$config_id 			= $this->_getParam('cid');
		}

		if( !empty($config_id) ){
			$this->view->cid = $config_id;
		}else{
			$this->view->cid = null;
		}

		if( empty($config_group_id) ){
			$config_group_id = 1;
		}

		$this->view->group_id   = $config_group_id;


		$config_values 			= $configModel->getGroupConfigOptions($config_group_id);

		if( $config_values ){
			$this->view->config = $config_values;
		}else{
			$this->view->config = null;
		}
    }

}