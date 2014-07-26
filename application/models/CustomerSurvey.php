<?php



class Default_Model_CustomerSurvey extends Zend_Db_Table {



	protected $_name = 'customer_survey';



    public function insertcustomer_survey($surveytaken) {

		$data = array( 	'user_id'                 => $surveytaken['user_id'],
				
						'survey_id'               => $surveytaken['survey_id'],

						'survey_taken_date'       =>  date("Y-m-d"),

		                'completed'	              =>  ($surveytaken['completed']=='y')?'y':'n'	

		                
		);



		try {

			$cs_id = $this->insert($data);

			if($cs_id){

			    return $cs_id;

			}

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("CustomerSurvey.insertcustomer_survey : " . $e->getMessage());

		}

		return false;

	}



	public function SurveyTakenInfo($user_id,$survey_id) {

	    $select = $this->select()->where(' user_id =  ?', $user_id)
	                             ->where(' survey_id =  ?', $survey_id);;


		try {

			$result = $this->fetchRow($select);

            if($result){

                return $result;

            }

		}catch (Exception $e){

			$errlog = Zend_Controller_Action_HelperBroker::getStaticHelper('errorlog');

			$errlog->direct("CustomerSurvey.SurveyTakenInfo : " . $e->getMessage());

		}

		return false;

	}






}