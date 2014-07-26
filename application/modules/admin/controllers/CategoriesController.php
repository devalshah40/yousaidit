<?php



class Admin_CategoriesController extends My_AdminController {

	

	/**

	 * init() function, call init() function of parent class

	 *

	 */

	public function init() {

		parent::init ();

	

	}

	

	public function indexAction() {

		$this->_session->search_param = '';

		$post = $this->getRequest ()->getPost ();
	

		$categoryModel = new Default_Model_Categories ();

		

		if (! empty ( $post ['cmdDelete'] )) {

			$id = $post ['chk'];

			$delete_count = 0;

			if (is_array ( $id ) && count ( $id ) > 0) {

				foreach ( $id as $k => $v ) {

					$delete_res = $categoryModel->deleteCategory ( $v );

					if ($delete_res) {

						$delete_count ++;

					}

				}

			}

			$this->_flashMessenger->addMessage ( $delete_count . ' Categories(s) deleted.' );

			$this->_redirector->gotoUrl ( $this->_modulename . '/categories/index/page/1' );

		}

		

		$sort_field = 'add_date DESC';

		

		$field = null;

		$value = null;

		

		if (! empty ( $post ['cmdSubmit'] )) {

			$field = $post ['cmbKey'];

			$value = $post ['txtValue'];

			

			$search_param = array ("field" => $field, "value" => $value );

			

			$this->_session->search_param = $search_param;

			$this->view->result_type = 'filtered';

		} else {

			if ($this->_getParam ( 'clear' ) == "results") {

				$search_param = array ();

				$this->_session->search_param = $search_param;

			}

			$search_param = $this->_session->search_param;

			if (! empty ( $search_param )) {

				$field = $this->_session->search_param ['field'];

				$value = $this->_session->search_param ['value'];

				$this->view->result_type = 'filtered';

			}

		}

		

		$this->view->pageTitle = ADMIN_TITLE . 'Manage Categories';

		$this->view->pgTitle = 'Categories Listing';

		

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'categories/categories_menu.phtml' ) );

		

		$categoryList = $categoryModel->listcategories ( $sort_field, $field, $value );

		

		if ($categoryList) {

			$pg = $this->_getParam ( 'page' );

			$this->view->pg = ! empty ( $pg ) ? $pg : 1;

			

			$this->view->paginator = Zend_Paginator::factory ( $categoryList );

			$this->view->paginator->setItemCountPerPage ( REC_LIMIT );

			$this->view->paginator->setPageRange ( PAGE_LINK_COUNT );

			$this->view->paginator->setCurrentPageNumber ( $pg );

		} else {

			$this->view->paginator = null;

		}

	}

	

	public function addAction() {

	   $categoryModel = new Default_Model_Categories ();

		

		$this->view->action = 'Add';

		$post = $this->getRequest ()->getPost ();

		

		$is_error = false;

		$var_msg = 'Error .' . " ";

		

		if (! empty ( $post )) {

			

			if (empty ( $post ['categories_name'] )) {

				$is_error = true;

				$var_msg .= 'Category name cannot be left empty.' . " ";

			}
			
			
			if ($is_error == true) {

				foreach ( $post as $pk => $pv ) {

					$catInfo [$pk] = $pv;

				}

				

				$this->view->catInfo = $catInfo;

				$this->view->errorMsg = $var_msg;

				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );

			

			} else {

				
				

				if (! empty ( $post ['categories_name'] )) {

					$post ['categories_name'] = $this->_helper->Filterspchars ( $post ['categories_name'] );

				}

				$category_id = $categoryModel->insertCategory ( $post );

				

				if ($category_id) {

					$this->_flashMessenger->addMessage ( 'Category added successfully.' );

				} else {

					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Category not added.' );

				}

				$this->_redirector->gotoUrl ( $this->_modulename . '/categories/add' );

			}

		}

		

		$this->view->action = 'Add';

		$this->view->pageTitle = ADMIN_TITLE . 'Add Category';

		$this->view->pgTitle = 'Add Category';

		

		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'categories/categories_menu.phtml' ) );

	}

	

	public function editAction() {

		$post = $this->getRequest ()->getPost ();

	
		$categories_id = ( int ) $this->_getParam ( 'categories_id' );
	
		$pg = $this->_getParam ( 'page' );
		
		$pg = (! empty ( $pg )) ? $pg : 1;

		

		$this->view->pg = $pg;
	

		

	   $categoryModel = new Default_Model_Categories ();

		$this->view->action = 'Edit';


		$this->view->pageTitle = 'Edit Category details ';

		$this->view->pgTitle = 'Edit Category details ';



		$this->getResponse ()->insert ( 'navigation', $this->view->render ( 'categories/categories_menu.phtml' ) );

		

		if (empty ( $post )) {

			$CategoryInfo = $categoryModel->CategoryInfo ( $categories_id );

			

			if ($CategoryInfo) {

						

				$this->view->CategoryInfo = $CategoryInfo;

				

				return $this->render ( 'add' );

			} else {

				$this->_flashMessenger->addMessage ( 'Error(s) encountered. Unable to fetch Category details.' );

			}

		} else {

			

			$is_error = false;

			$var_msg = 'Error encountered.' . " ";

			

			$CategoryInfo = $categoryModel->CategoryInfo ( $categories_id );

	
			if (empty ( $post ['categories_name'] )) {

				$is_error = true;

				$var_msg .= 'Category name cannot be left empty.' . "<br/>";


			}
		

			if ($is_error == true) {
		

				$CategoryInfo = $categoryModel->CategoryInfo ( $categories_id );

			

				
				foreach ( $post as $pk => $pv ) {

					$catInfo [$pk] = $pv;

				}

				$catInfo ['add_date'] = $CategoryInfo ['add_date'];

				$catInfo ['updated_datetime'] = $CategoryInfo ['updated_datetime'];

	
				

				$this->view->CategoryInfo = $catInfo;

				$this->view->errorMsg = $var_msg;

				

				$this->getResponse ()->insert ( 'error', $this->view->render ( 'error.phtml' ) );

				

				return $this->render ( 'add' );

			} else {

					

				$result = $categoryModel->updateCategory ( $post );

				

				if ($result) {

					$this->_flashMessenger->addMessage ( 'Categories detail updated successfully.' );

				} else {

					$this->_flashMessenger->addMessage ( 'Error(s) encountered. Categories_name detail not updated.' );

				}
           
				$this->_redirector->gotoUrl ( $this->_modulename . '/categories/index/' );
			}

			


		}

	}

}