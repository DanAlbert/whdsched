<?php

class ShiftController extends Zend_Controller_Action
{

	protected $_messenger;
    public function init()
    {
        $this->_messenger = $this->_helper->getHelper('FlashMessenger');
    }

    public function indexAction()
    {
        // action body
    }

    public function createAction()
    {
    	$this->view->messages = array();
    	$user = Zend_Auth::getInstance()->getIdentity();
    	
		$form = new Application_Form_Shift();
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			// Submitted data?
			if ($request->isPost())
			{
				// Valid data?
				if ($form->isValid($request->getPost()))
				{
					$shiftMapper = new Application_Model_ShiftMapper();
					$termMapper = new Application_Model_TermMapper();
					
					$shift = new Application_Model_Shift();
					
					$values = $form->getValues();
					
					$shift->setStartTime(strval($values['startTime']) . '00');
					$shift->setEndTime(strval($values['endTime']) . '00');
					$days = $values['days'];
					
					$term = $termMapper->find($values['term']);
					
					// Make UNIX timestamps out of the SQL date stamps
					list($year, $month, $day) = explode('-', $term->getStartDate());
					$date = mktime(0, 0, 0, $month, $day, $year);
					
					list($year, $month, $day) = explode('-', $term->getEndDate());
					$endDate = mktime(0, 0, 0, $month, $day + 1, $year);
					
					// Within the range...
					while ($date < $endDate)
					{
						$d = getdate($date);
						$wday = $d['wday']; // Day of week
						
						// Add shift for this day?
						if (in_array($wday, $days))
						{
							$shift->setDate(date("Y-m-d", $date)); // Format for SQL
	
							// Create a shift for each selected location
							foreach ($values['location'] as $location)
							{
								$shift->setId(null); // Insert each time, not update
								$shift->setLocation($location);
	
								// For testing
								/*$this->view->error .= 'Creating shift from ' .
										$shift->getStartTime() . ' - ' .
										$shift->getEndTime() . ' @ ' .
										$shift->getLocation() . ' on ' .
										$d['weekday'] . ' ' .
										$shift->getDate() . '<br />';*/
								
								if ($shiftMapper->save($shift) <= 0)
								{
									$this->view->error = "Could not insert shift";
								}
							}
						}
						
						$date = mktime(0, 0, 0,
								date('m', $date),
								date('d', $date) + 1,
								date('y', $date)); // Next day
					}
					
					// An error will be displayed if any insert failed
					if (!isset($this->view->error))
					{
						return $this->_helper->redirector('index');
					}
				}
				else
				{
					$this->view->form = $form;
				}
			}
			else
			{
				$this->view->form = $form;
			}
		}
		else
		{
			$this->view->messages[] = 'You are forbidden from creating shifts.';
		}
		
    }

    public function assignAction()
    {
    	$this->view->messages = array();
    	$user = Zend_Auth::getInstance()->getIdentity();
    	
		$form = new Application_Form_Assign();
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			if ($request->isPost())
			{			
				if ($form->isValid($request->getPost()))
				{
					$shiftMapper = new Application_Model_ShiftMapper();
					$consultantMapper = new Application_Model_ConsultantMapper();
					$termMapper = new Application_Model_TermMapper();
					
					$values = $form->getValues();
					
					$consultant = $consultantMapper->find($values['consultant']);
					$wday = $values['day'];
					
					$h = substr($values['startTime'], 0, 2);
					$m = substr($values['startTime'], 2, 2);
					$startTime = "{$h}:{$m}:00";
					
					$h = substr($values['endTime'], 0, 2);
					$m = substr($values['endTime'], 2, 2);
					$endTime = "{$h}:{$m}:00";
					
					$location = $values['location'];
					
					$term = $termMapper->find($values['term']);
					
					// Make UNIX timestamps out of the SQL date stamps
					list($year, $month, $day) = explode('-', $term->getStartDate());
					$date = mktime(0, 0, 0, $month, $day, $year);
					
					list($year, $month, $day) = explode('-', $term->getEndDate());
					$endDate = mktime(0, 0, 0, $month, $day + 1, $year);
					
					$shifts = $shiftMapper->fetchAllByTerm($term);
					foreach ($shifts as $shift)
					{
						list($year, $month, $day) = explode('-', $shift->getDate());
						$date = getdate(mktime(0, 0, 0, $month, $day, $year));
						
						if ($date['wday'] == $wday)
						{
							if (($shift->getStartTime() == $startTime) and
								($shift->getEndTime() == $endTime))
							{
								if ($location == $shift->getLocation())
								{
									$shift->setConsultant($consultant);
									$shiftMapper->save($shift);
								}
							}
						}
					}
					
					// An error will be displayed if any insert failed
					if (!isset($this->view->error))
					{
						return $this->_helper->redirector('index');
					}
				}
				else
				{
					$this->view->form = $form;
				}
			}
			else
			{
				$this->view->form = $form;
			}
		}
		else
		{
			$this->view->messages[] = 'You are forbidden from assigning shifts.';
			
		}
		
    }

    public function specialAction()
    {
    	$this->view->messages = $this->_messenger->getMessages();
    	$user = Zend_Auth::getInstance()->getIdentity();

    	if ($user->isAdmin())
    	{
			$this->view->month = $this->getRequest()->getParam('month');
			if ($this->view->month == null)
			{
				$this->view->month = date('n');
			}
			
			$this->view->year = $this->getRequest()->getParam('year');
			if ($this->view->year == null)
			{
				$this->view->year = date('Y');
			}
    	
    	}
    	else
    	{
    		$this->view->messages[] = 'You are forbidden from creating special shifts';
    	
    	}
    	
    	
    }

    public function keepAction()
    {
    	
    	$user = Zend_Auth::getInstance()->getIdentity();
    	$shiftMapper = new Application_Model_ShiftMapper();
    	$tempMapper = new Application_Model_TempShiftMapper();
    	
    	if($user->isAdmin())
    	{
	    	$request = $this->getRequest();
	    	
	    	$day = $request->getParam("day");
	    	$month = $request->getParam("month");
	    	$year = $request->getParam("year");
	    	$shifts = $shiftMapper->fetchAllByDate(mktime(0, 0, 0, $month, $day, $year));
	    	
	    	if(count($shifts) == 0)
	    	{
	    		$this->_messenger->addMessage('No Shifts Exist');
	    		$this->_helper->getHelper('Redirector')->gotoSimple('special', 'shift', null, array(
	    			'month' => $month,
	    			'year' => $year,
	    			'day' => $day));
	    	
	    	}
	    	
	    	
	    	$form = new Application_Form_ShiftSelector($shifts);
	    	
	    	if($request->isPost())
	    	{
	    		if($form->isValid($request->getPost()))
	    		{
	    			$values = $form->getValues();
	    			$keep = $values['shifts'];
					foreach ($shifts as $shift) 
					{
						if (in_array($shift->getId(), $keep)) {
							$consultant = $shift->getConsultant();
							$shift->setConsultant(null);
							
							$temp = new Application_Model_TempShift();
							$temp->setAssignedConsultant($consultant);
							$temp->setShift($shift);
							$temp->setPostTime(time());
							$temp->setTimeout(168); 		//One week
							$this->_messenger->addMessage('Adding Temp');
							//$shiftMapper->save($shift);
							//$tempMapper->save($temp);
						}
						else 
						{
							$this->_messenger->addMessage('Deleting Shift');
							//$shiftMapper->delete($shift);	
						}	
					}
	    		}
	    	}
	    	else 
	    	{
		    	
				
				$this->view->form = $form;
			}
			
			
		}
		else 
		{
			$this->view->messages[] = 'Forbidden!!!!';
		}
		
		$this->view->messages = $this->_messenger->getMessages();
    }


}









