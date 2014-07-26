<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initAutoload() {
        $autoloader = new Zend_Application_Module_Autoloader(
        	array(	'namespace' => 'Default',
            		'basePath'  => dirname(__FILE__)       )
		);
        return $autoloader;
    }


    public function run() {

		$config = new Default_Model_Configuration();
		$config_values = $config->fetchAll();

		foreach ($config_values as $cv){
			define($cv->config_key, $cv->config_value);
		}

		Zend_Controller_Action_HelperBroker::addPath( APPLICATION_PATH . DS . 'helpers' . DS );

		$frontController = Zend_Controller_Front::getInstance();

		$frontController->addModuleDirectory(APPLICATION_PATH . DS . 'modules' . DS);

		$layoutModulePlugin = new My_Plugin();

		$layoutModulePlugin->registerModuleLayout('default', APPLICATION_PATH . DS . 'modules'.DS.'default'.DS.'layouts'.DS.'scripts'.DS, 'default');

		$layoutModulePlugin->registerModuleLayout('admin', APPLICATION_PATH . DS . 'modules'.DS.'admin'.DS.'layouts'.DS.'scripts'.DS, 'admin');

		$layoutModulePlugin->registerModuleLayout('mobile', APPLICATION_PATH . DS . 'modules'.DS.'mobile'.DS.'layouts'.DS.'scripts'.DS, 'mobile');
		
		
		$frontController->registerPlugin($layoutModulePlugin);

		$frontController->setParam("useDefaultControllerAlways", false);

		$router = $frontController->getRouter();		// Returns a rewrite router by default

		//

		$route = new Zend_Controller_Router_Route("index.html",
					array(	"controller"    => "index",	"action"	=> "index"		));

		$router->addRoute("home.html", $route);
		
		$route = new Zend_Controller_Router_Route("about-us.html",
					array(	"controller"    => "index",	"action"	=> "aboutus"		)		);

		$router->addRoute("about-us", $route);
		
		$route = new Zend_Controller_Router_Route("terms-of-use.html",
					array(	"controller"    => "index",	"action"	=> "termsofuse"		)		);

		$router->addRoute("terms-of-use", $route);
		
		$route = new Zend_Controller_Router_Route("privacy-policy.html",
					array(	"controller"    => "index",	"action"	=> "privacypolicy"		)		);

		$router->addRoute("privacy-policy", $route);
		
		$route = new Zend_Controller_Router_Route("settings.html",
					array(	"controller"    => "index",	"action"	=> "settings"		)		);

		$router->addRoute("settings", $route);
		
		$route = new Zend_Controller_Router_Route("share-on-facebook.html",
					array(	"controller"    => "index",	"action"	=> "facebook"		)		);

		$router->addRoute("share-on-facebook", $route);
		
		$route = new Zend_Controller_Router_Route("facebook-logout.html",
					array(	"controller"    => "index",	"action"	=> "facebooklogout"		)		);

		$router->addRoute("facebook-logout", $route);
		
		$route = new Zend_Controller_Router_Route("categories-of-interest.html",
					array(	"controller"    => "index",	"action"	=> "categoryofinterest"		)		);

		$router->addRoute("categories-of-interest", $route);
		
		$route = new Zend_Controller_Router_Route("notifications.html",
					array(	"controller"    => "index",	"action"	=> "notifications"		)		);

		$router->addRoute("notifications", $route);
		
		
		$route = new Zend_Controller_Router_Route("share-on-twitter.html",
					array(	"controller"    => "index",	"action"	=> "twitter"		)		);

		$router->addRoute("share-on-twitter", $route);
		
		$route = new Zend_Controller_Router_Route("signup.html",
					array(	"controller"    => "user",	"action"	=> "signup"		));

		$router->addRoute("signup.html", $route);

		// client controller
		
		$route = new Zend_Controller_Router_Route("client-login.html",
					array(	"controller"    => "client",	"action"	=> "clientlogin"	)	);

		$router->addRoute("client-login", $route);
		
		$route = new Zend_Controller_Router_Route("client-home.html",
					array(	"controller"    => "client",	"action"	=> "index"	)	);

		$router->addRoute("client-home", $route);
		
		$route = new Zend_Controller_Router_Route("client-profile.html",
		    	array(	"controller"    => "client",	"action"	=> "editprofile"		)		);

		$router->addRoute("client-profile", $route);
		
		$route = new Zend_Controller_Router_Route("recover-client-password.html",
					array(	"controller"    => "user",	"action"	=> "recoverpassword", 'user_type' => 'c'	)	);

		$router->addRoute("recover-client-password", $route);
		
		// user controller
         
		$route = new Zend_Controller_Router_Route("user-login.html",
					array(	"controller"    => "user",	"action"	=> "processlogin" )	);

		$router->addRoute("user-login", $route);
		
		$route = new Zend_Controller_Router_Route("recover-password.html",
					array(	"controller"    => "user",	"action"	=> "recoverpassword"  )	);

		$router->addRoute("recover-password", $route);
		
		$route = new Zend_Controller_Router_Route("user-home.html",
					array(	"controller"    => "user",	"action"	=> "index"		)		);
					
		$router->addRoute("user-home", $route);
		
		$route = new Zend_Controller_Router_Route("viewall-topics.html",
					array(	"controller"    => "user",	"action"	=> "index", "id" => 1	)		);
					
		$router->addRoute("viewall-topics", $route);
		
	    $route = new Zend_Controller_Router_Route("edit-profile.html",
		    	array(	"controller"    => "user",	"action"	=> "editprofile"		)		);

		$router->addRoute("edit-profile", $route);
		
		$route = new Zend_Controller_Router_Route("member-signup.html",
					array(	"controller"    => "user",	"action"	=> "membersignup"		)		);

		$router->addRoute("member-signup", $route);

		$route = new Zend_Controller_Router_Route("client-signup.html",
					array(	"controller"    => "user",	"action"	=> "clientsignup"		)		);

		$router->addRoute("client-signup", $route);
		
		$route = new Zend_Controller_Router_Route("get-captcha.html",
					array(	"controller"    => "user",	"action"	=> "generatecaptcha"		)		);

		$router->addRoute("get-captcha", $route);
		
		
		
		$route = new Zend_Controller_Router_Route(":usertype/:token",
					array(	"controller"    => "user",	"action"	=> "verifyuser"		)		);

		$router->addRoute("user-verification", $route);
		
		
		$route = new Zend_Controller_Router_Route(":useraction/email-address/:token",
				   array(	"controller"    => "user",	"action"	=> "emailaddress"		)		);

		$router->addRoute("undo-confirm-email-address", $route);
		

		$route = new Zend_Controller_Router_Route("invite-friends.html",
					array(	"controller"    => "user",	"action"	=> "sendinvite"		)		);

		$router->addRoute("invite-friends", $route);
		
		
		$route = new Zend_Controller_Router_Route("invite-friends-to-mcubed.html",
					array(	"controller"    => "user",	"action"	=> "invitemcubed"		)		);

		$router->addRoute("invite-mcubed", $route);
		
		
		$route = new Zend_Controller_Router_Route("logout.html",
					array(	"controller"    => "user",	"action"	=> "logout"		)		);
					
	
		$router->addRoute("logout", $route);
		
		
		$route = new Zend_Controller_Router_Route("client-logout.html",
					array(	"controller"    => "client",	"action"	=> "clientlogout"		)		);
					
	
		$router->addRoute("client-logout", $route);
		
		///////////////survey controller //////////////////////////////
	
		
		$route = new Zend_Controller_Router_Route("take-survey.html",
					array(	"controller"    => "survey",	"action"	=> "takesurvey"		)		);

		$router->addRoute("take-survey", $route);
		
		$route = new Zend_Controller_Router_Route("thank-you.html",
					array(	"controller"    => "survey",	"action"	=> "thankyou"		)		);

		$router->addRoute("thank-you", $route);
		
		////////////survey controller/////////////\
		
		////////////topic controller/////////////\
		
		$route = new Zend_Controller_Router_Route("view-topics.html",
					array(	"controller"    => "topic",	"action"	=> "index"		)		);

		$router->addRoute("view-topics", $route);
		
		$route = new Zend_Controller_Router_Route(":topic_id/view-toptopic.html",
					array(	"controller"    => "topic",	"action"	=> "topicredirect"		)		);

		$router->addRoute("view-toptopic", $route);

		$route = new Zend_Controller_Router_Route("create-topic.html",
					array(	"controller"    => "topic",	"action"	=> "add"		)		);

		$router->addRoute("create-topic", $route);
		
		$route = new Zend_Controller_Router_Route("response.html",
					array(	"controller"    => "topic",	"action"	=> "response"		)		);

		$router->addRoute("response", $route);
		
		$route = new Zend_Controller_Router_Route(":id/topic-response.html",
					array(	"controller"    => "topic",	"action"	=> "topicresponse"		)		);

		$router->addRoute("topic-response", $route);
		
		
		$route = new Zend_Controller_Router_Route(":id/delete.html",
					array(	"controller"    => "topic",	"action"	=> "deletetopic"		)		);

		$router->addRoute("topic-delete", $route);
		
		$route = new Zend_Controller_Router_Route("filter-topics.html",
					array(	"controller"    => "topic",	"action"	=> "filtertopic"		)		);

		$router->addRoute("filter-topic", $route);
		
	
		
		////////////topic controller/////////////\
		

		$frontController->dispatch();
	}
}