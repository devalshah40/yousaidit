<?php

class Admin_IndexController extends My_AdminController {

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
		$this->view->pageTitle = ADMIN_TITLE . 'Admin Home';
    }

}