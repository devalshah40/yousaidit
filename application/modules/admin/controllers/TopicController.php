<?php

class Admin_TopicController extends My_AdminController {
	
	/**
	 * init() function, call init() function of parent class
	 *
	 */
	
	public function init() {
		
		parent::init ();
	
	}
	
	public function indexAction() {
		$tcat_id = ( int ) $this->_getParam ( 'cat_id' );
		$flag = ( int ) $this->_getParam ( 'flag' );
		$post = $this->getRequest ()->getPost ();
		
		$TopicModel = new Default_Model_Topic ();
		
		$CategoryModel = new Default_Model_Categories ();
		$TopicCategoryModel = new Default_Model_TopicCategories ();
		$tResponseModel = new Default_Model_TopicResponse ();
		$reportModel = new Default_Model_Report ();
		
		$catInfo = $CategoryModel->listcategories ( 'categories_name' );
		$this->view->catInfo = $catInfo;
		
		if (! empty ( $post ['cmdDelete'] )) {
			
			$id = $post ['chk'];
			$delete_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				foreach ( $id as $k => $v ) {
					$delete_topic = $TopicModel->deleteTopic ( $v );
					$delete_topicResponses = $tResponseModel->deleteTopicResponse ( $v );
					$delete_topicassociatons = $TopicCategoryModel->deleteTopicCategory ( $v );
					
					if ($delete_topic) {
						$delete_count ++;
					}
				}
			}
			if(!empty($post['flag'])){
				$url =  $this->_modulename . '/topic/index/flag/1/page/1';
			}else{
				$url =  $this->_modulename . '/topic/index/page/1';
			}
			$this->_flashMessenger->addMessage ( $delete_count . ' Topic(s) deleted.' );
			$this->_redirector->gotoUrl ($url);
		} else if (! empty ( $post ['cmdRevoke'] )) {
			$id = $post ['chk'];
			$revoke_count = 0;
			if (is_array ( $id ) && count ( $id ) > 0) {
				foreach ( $id as $k => $v ) {
					$revoke_topic = $reportModel->deleteRevokeTopics ( $v );
					$revoke_topic1 = $TopicModel->UpdateRevoketopicstatus ( $v );
					if ($revoke_topic) {
						$revoke_count++;
					}
				}
			}
			
			$this->_flashMessenger->addMessage ( $revoke_count . ' Topic(s) Revoked.' );
			$this->_redirector->gotoUrl ( $this->_modulename . '/topic/index/flag/1/page/1' );
		}
		
		$sort_field = 'add_date DESC';
		
		$field = null;
		
		$value = null;
		
		if (! empty ( $post ['cmdSubmit'] )) {
			
			$field = $post ['cmbKey'];
			$value = $post ['txtValue'];
			$flag = @$post ['flag'];
			$search_param = array ("field" => $field, "value" => $value );
			$this->_session->search_param = $search_param;
			$this->view->result_type = 'filtered';
		
		} else {
			
			if ($this->_getParam ( 'clear' ) == "results") {
				$search_param = array ();
				$this->_session->search_param = $search_param;
			
		//	$flag =  $this->_getParam ( 'flag' );
			}
			$search_param = $this->_session->search_param;
			
			if (! empty ( $search_param )) {
				$field = $this->_session->search_param ['field'];
				$value = $this->_session->search_param ['value'];
				$this->view->result_type = 'filtered';
			}
		}
		
		$this->view->pageTitle = ADMIN_TITLE . 'Manage Topics';
		
		if(empty($flag)){
			$this->view->pgTitle = 'Topics Listing';
			$this->view->col1_width = 3;
			$this->view->col2_width = 58;
			$this->view->col3_width = 12;
			$this->view->col4_width = 12;
			$this->view->col5_width = 10;
			$this->view->col6_width = 7;
		}else{
			$this->view->pgTitle = 'Flagged Topic Listing';
			$this->view->col1_width = 3;
			$this->view->col2_width = 50;
			$this->view->col3_width = 12;
			$this->view->col4_width = 12;
			$this->view->col5_width = 10;
			$this->view->col6_width = 7;
		}

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'topic/topic_menu.phtml' ) );
		
		if (! empty ( $tcat_id )) {
			$topicList = $TopicModel->getcatewiseListing ( $tcat_id, $flag );
			$this->view->cat_id = $tcat_id;
		} else {
			/*echo $flag."xxx";
			echo $post['flag']."yyy";
			*/			$topicList = $TopicModel->getTopicListing ( 'add_date desc', $field, $value, false, $flag );
		}
		
		if ($topicList) {
			
			$pg = $this->_getParam ( 'page' );
			
			$this->view->pg = ! empty ( $pg ) ? $pg : 1;
			
			$TopicModelReponse = new Default_Model_TopicResponse ();
			
			$reportModel = new Default_Model_Report ();
			
			$topicArr = array ();
			$response_count = 0;
			foreach ( $topicList as $tk => $tv ) {
				$response_count = 0;
				$tv ['response'] = $this->_helper->topicresponsearray->getVoteCount ( $tv ['topic_id'], $TopicModelReponse );
				if(is_array($tv['response'])){
					for($i = 1; $i<=5 ; $i++) {
					$response_count  +=  $tv['response']["rvot_$i"];
					}
				}
				$tv ['flag_count'] = $reportModel->getReportCount ( $tv ['topic_id'] );
				$tv['response_count'] = $response_count;
				$topicArr [] = $tv;
			}
			
			//$this->p($topicArr); exit;
			
			$this->view->flag = $flag;
			
			$this->view->paginator = Zend_Paginator::factory ( $topicArr );
			
			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );
			
			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );
			
			$this->view->paginator->setCurrentPageNumber ( $pg );
		
		} else {
			
			$this->view->flag = $flag;
			$this->view->paginator = null;
		
		}
	
	}
	
	public function editAction() {
		
		$post = $this->getRequest ()->getPost ();
		$flag = ( int ) $this->_getParam ( 'flag' );
	
		$this->view->flag = $flag;
		$this->view->action = 'Edit';
		
		$tid = ( int ) $this->_getParam ( 'topic_id' );
		
		//	echo $tid; exit;
		

		$pg = $this->_getParam ( 'page' );
		
		$pg = (! empty ( $pg )) ? $pg : 1;

		

		$this->view->pg = $pg;
		
		$TopicModel = new Default_Model_Topic ();
		
		$CategoryModel = new Default_Model_Categories ();
		
		$TopicCategoriesModel = new Default_Model_TopicCategories ();
		
		$TopicModelReponse = new Default_Model_TopicResponse ();
	/*	$topicArr = array ();
		$Topic_userVoted   = $this->_helper->topicresponsearray->getVoteCount ( $tid, $TopicModelReponse );
		$topicArr ['response'] = $Topic_userVoted;
		$this->view->topicArr = $topicArr;
		$this->view->arrTopic = $topicArr;*/
		
		
		
		$Topic_userVoted = $TopicModelReponse->getVotesCount ( $tid );
		
		//echo "<pre>" ; print_r($Topic_userVoted);
	        $error_msg = null;
	    
	
			if (! empty ( $Topic_userVoted )) {
				$this->view->arrTopic = $Topic_userVoted ;
			} else {
				$this->view->arrTopic = null;
				$this->view->errorMsg = 'No responses found, chart cannot be displayed';
			}
			
			
		$sort_field = 'categories_name';
		
		$field = 'status';
		
		$value = 'Active';
		
		$catInfo = $CategoryModel->listcategories ( $sort_field, $field, $value, $strict_filter = false );
		
		//  echo "<pre>"; var_dump($catInfo); echo "</pre>"; exit;
		

		$this->view->catInfo = $catInfo;
		
        $countryModel = new Default_Model_Countries ();

        $countryList = $countryModel->listCountry ();
		
        $this->view->countryList = $countryList;
		
		$this->view->pageTitle = 'Edit Topic details ';
		
		$this->view->pgTitle = 'Edit Topic details ';
		
		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'topic/topic_menu.phtml' ) );
		
		$topicInfo = $TopicModel->TopicInfo ( $tid );
		
		$cateditInfo = $TopicModel->getEditTopic ( $tid );
		/*   echo "<pre>";
        print_r($cateditInfo) ; exit;
      */
		
		//  var_dump($cateditInfo); exit;
		

		///	$multiple_select = explode ( ",", $cateditInfo ['categories_id'] );
		

		 $multiple_select = $cateditInfo;
		
		$this->view->multiple_select = $multiple_select;
		
		if (empty ( $post )) {
			
			if ($topicInfo) {
				
				$this->view->topicInfo = $topicInfo;
				
				return $this->render ( 'add' );
			
			} else {
				
				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Unable to fetch Topic details.' );
			
			}
		
		} else {
			
			/*echo "<pre>";
		print_r($post); exit;*/
			//$b = implode ( ",", $post ['categories_id'] );
			

			//	$this->view->catInfo = $catInfo;
			

			//	$this->view->multiple_select = $b;
			

			// turn the array in to string
			// $post ['categories_id'] = $b;
			

			$tc_id = $post ['topic_id'];
			
			$is_error = false;
			
			$var_msg = 'Error encountered.' . "<br/>";
			
			$topicInfo = $TopicModel->TopicInfo ( $tid );
			
			if (empty ( $post ['name'] )) {
				
				$is_error = true;
				
				$var_msg .= 'Topic name cannot be left empty.' . "<br/>";
			
			}
			
	    	if (empty ( $post ['categories_id'] )) {
				
				$is_error = true;
				
				$var_msg .= 'Category cannot be left empty.' . "<br/>";
			
			}
			
	    	 if ( $post['country_id']== -1 ){
	        	
                    $is_error = true;
                    
                    $var_msg .= 'Country cannot be blank.' . "<br/>";
             }
             
			if ($is_error == true) {
				
				//	echo $tid; exit;
				

				$topicInfo = $TopicModel->TopicInfo ( $tid );
				
				foreach ( $post as $pk => $pv ) {
					
					$postInfo [$pk] = $pv;
				
				}
				
				$postInfo ['add_date'] = $topicInfo ['add_date'];
				
				$postInfo ['updated_datetime'] = $topicInfo ['updated_datetime'];
				
				$this->view->multiple_select = $multiple_select;
				
				$this->view->topicInfo = $postInfo;
				
				//	$this->p($postInfo); exit;
				

				$this->view->errorMsg = $var_msg;
				
				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );
				
				return $this->render ( 'add' );
			
			} else {
				
				$result = $TopicModel->updateTopic ( $post );
				
				
				
				if ($result) {
					
				
					$tCategoryModel = new Default_Model_TopicCategories ();
					
					$result = $tCategoryModel->	updateTopicCategory ( $post );
					
					$this->_flashMessenger->addMessage ( 'Topic detail updated successfully.' );
				
				} else {
					
					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Topic detail not updated.' );
				
				}
			
			}
			if(!empty($post['flag'])){
				$url =  $this->_modulename . '/topic/index/flag/1/page/1';
			}else{
				$url =  $this->_modulename . '/topic/index/page/' . $pg;
			}
			$this->_redirector->gotoUrl ( $url );
		
		}
		
	
	}

	public function viewresponseAction() {
	        $response = $this->getResponse();
	        $response->insert('header', '');
	        $response->insert('footer', '');
	        
	        
			$topic_id = ( int ) $this->_getParam ( 'topic_id' );
			
			
	        $TopicModelReponse = new Default_Model_TopicResponse ();
			$topicArr = array ();
			$Topic_userVoted1   = $this->_helper->topicresponsearray->getVoteCount ( $topic_id, $TopicModelReponse );
			$topicArr ['response'] = $Topic_userVoted1;
			$this->view->topicArr = $topicArr;
			$this->view->arrTopic = $topicArr;
		
	        $this->_helper->layout()->disableLayout();
	      
	        $post = $this->getRequest()->getPost();
	        
			$TopicModelReponse = new Default_Model_TopicResponse ();
			$TopicModel = new Default_Model_Topic ();
		
			$topic = $TopicModel->TopicInfo ( $topic_id );
			
			$Topic_userVoted = $TopicModelReponse->getVotesCount ( $topic_id );
	        $error_msg = null;
	    
//$this->p($Topic_userVoted); exit;
			if (! empty ( $Topic_userVoted )) {
				$this->view->arrTopic = $Topic_userVoted ;
			} else {
				$this->view->arrTopic = null;
				$this->view->errorMsg = 'No responses found, chart cannot be displayed';
			}
			$this->view->title = $topic['name'] ;
			$data = '';
			$flag = false;
			$response_votes = 0;
			if (! empty ( $Topic_userVoted )) {
			foreach ( $Topic_userVoted as $arr ) {
			$response_votes += 	$arr['votes'];
				if ($arr ['response'] == 1) {
					
				$data .= '{  name: \''. $arr ['percent'].'%\', response: 1 ,y:'. $arr ['percent'].',color: \'#99cc33\' , dataLabels: { color: \'#99cc33\' } },' ;
				
			} elseif ($arr ['response'] == 2) {
			  
					$data .= '{  name: \''. $arr ['percent'].'%\', response: 2 ,y:'. $arr ['percent'].',color: \'#0099cc\'  , dataLabels: { color: \'#0099cc\' } },' ;
			}
			 elseif ($arr ['response'] == 3) {
			
				$data .= '{  name: \''. $arr ['percent'].'%\', response: 3 ,y:'. $arr ['percent'].',color: \'#ff3333\'  , dataLabels: { color: \'#ff3333\' } },' ;
				
			}
			 elseif ($arr ['response'] == 4) {
			
				$data .= '{  name: \''. $arr ['percent'].'%\', response: 4 ,y:'. $arr ['percent'].',color: \'#ffdc00\'  , dataLabels: { color: \'#ffdc00\' } },' ;
			
			} elseif ($arr ['response'] == 5) {
				
					$data .= '{  name: \''. $arr ['percent'].'%\', response: 5 ,y:'. $arr ['percent'].',color: \'#ff9933\'  , dataLabels: { color: \'#ff9933\' } },' ;
		}
			}
			$this->view->total_votes = $response_votes;
			}
		
		$data = substr ( $data, 0, strlen ( $data ) - 1 );
		
		
			
			if (! empty ( $data )) {
				$this->view->data = $data;
			} else {
				$this->view->data = null;
				$this->view->errorMsg = 'No responses found, chart cannot be displayed';
			}
	    }
}