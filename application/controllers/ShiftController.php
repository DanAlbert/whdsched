<?php

class ShiftController extends Zend_Controller_Action
{
    protected $_messenger = null;

    public function init()
    {
		$this->_messenger = $this->_helper->getHelper('FlashMessenger');
		$ajaxContext = $this->_helper->getHelper('AjaxContext');
		$ajaxContext->addActionContext('set', 'json');
    }

    public function indexAction()
    {
		// action body
    }
	
	public function createAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$form = new Application_Form_Shift();
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			// Submitted and valid data?
			if (($request->isPost()) and ($form->isValid($request->getPost())))
			{
                $shiftMapper = new Application_Model_ShiftMapper();

                $shift = new Application_Model_Shift();

                $values = $form->getValues();

				$startTime = strval($values['startTime']) . '00';
				$endTime = strval($values['endTime']) . '00';
                $shift->setStartTime($startTime);
                $shift->setEndTime($endTime);
				
				$location = $values['location'];
				$date = $values['date'];

                // Make UNIX timestamp out of the input
				list($m, $d, $y) = explode('/', $date);
                $date = mktime(0, 0, 0, $m, $d, $y);
				
				$shift->setDate(date("Y-m-d", $date)); // Format for SQL

				// Create a shift for selected location
				$shift->setId(null); // Insert, not update
				$shift->setLocation($location);
				$shift->setDate(date('Y-m-d', $date));

				// For testing
				/*$this->view->error .= 'Creating shift from ' .
				$shift->getStartTime() . ' - ' .
				$shift->getEndTime() . ' @ ' .
				$shift->getLocation() . ' on ' .
				$d['weekday'] . ' ' .
				$shift->getDate() . '<br />';*/

				if ($shiftMapper->save($shift) <= 0)
				{
					$this->_messenger->addMessage("Could not insert shift");
				}
				else
				{
					$this->_helper->getHelper('Redirector')->gotoSimple(
						'index', 'schedule', null, array(
							'month' => $m,
							'year' => $y,
							'day' => $d));
				}
			}
			else
			{
				$this->view->form = $form;
			}
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from creating shifts.');
		}
	}

    public function createBulkAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$form = new Application_Form_ShiftBulk();
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			// Submitted and valid data?
			if (($request->isPost()) and ($form->isValid($request->getPost())))
			{
                $shiftMapper = new Application_Model_ShiftMapper();
                $termMapper = new Application_Model_TermMapper();

                $shift = new Application_Model_Shift();

                $values = $form->getValues();
				
				$weeks = $values['weeks'];

                $shift->setStartTime(strval($values['startTime']) . '00');
                $shift->setEndTime(strval($values['endTime']) . '00');
                $days = $values['days'];

                $term = $termMapper->find($values['term']);

                // Make UNIX timestamps out of the SQL date stamps
                list($year, $month, $day) = explode('-', $term->getStartDate());
                $date = mktime(0, 0, 0, $month, $day, $year);

				if ($weeks == -1)
				{
					list($year, $month, $day) = explode('-', $term->getEndDate());
					$endDate = mktime(0, 0, 0, $month, $day, $year);
				}
				else
				{
					$nDays = $weeks * 7;
					$endDate = mktime(0, 0, 0, $month, $day + $nDays, $year);
					$endDate = strtotime('last Saturday', $endDate);
				}
				
				$error = false;
				
                // Within the range...
                while ($date <= $endDate)
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

                            // For debug
                            /*$this->_messenger->addMessage('Creating shift from ' .
                                    $shift->getStartTime() . ' - ' .
                                    $shift->getEndTime() . ' @ ' .
                                    $shift->getLocation() . ' on ' .
                                    $wday . ' ' .
                                    $shift->getDate());*/

                            if ($shiftMapper->save($shift) <= 0)
                            {
								$error = true;
                                $this->_messenger->addMessage("Could not insert shift");
                            }
                        }
                    }

                    $date = mktime(0, 0, 0,
                            date('m', $date),
                            date('d', $date) + 1,
                            date('y', $date)); // Next day
                }

                // An error will be displayed if any insert failed
                if ($error === false)
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
			$this->_messenger->addMessage('You are forbidden from creating shifts.');
		}
    }
	
	public function removeAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		if ($user->isAdmin())
		{
			$shiftMapper = new Application_Model_ShiftMapper();
			$shift = $shiftMapper->find($id);
			if ($shift !== null)
			{
				$this->view->shift = $shift;
			}
			else
			{
				$this->_messenger->addMessage('Shift not found.');
				
				$this->_helper->getHelper('Redirector')->gotoSimple(
					'index', 'schedule');
			}
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from removing shifts.');
		}
	}
	
	public function deleteAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		if ($user->isAdmin())
		{
			$shiftMapper = new Application_Model_ShiftMapper();
			$shift = $shiftMapper->find($id);
			if ($shift !== null)
			{
				$date = $shift->getDate();
				list($year, $month, $day) = explode('-', $date);
				if (!$shiftMapper->delete($shift))
				{
					$this->_messenger->addMessage('An error occurred while deleting the shift.');
				}
				
				$this->_helper->getHelper('Redirector')->gotoSimple(
					'index', 'schedule', null, array(
						'month' => $month,
						'year' => $year,
						'day' => $day));
			}
			else
			{
				$this->_messenger->addMessage('Shift not found.');
			}
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from removing shifts.');
		}
		
		$this->_helper->getHelper('Redirector')->gotoSimple(
			'index', 'schedule');
	}

    public function assignAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			$consultantsMapper = new Application_Model_ConsultantMapper();
			$shiftMapper = new Application_Model_ShiftMapper();
			$termMapper = new Application_Model_TermMapper();

			try
			{
				$term = $termMapper->getCurrentOrNextTerm();
				$this->view->term = $term;
				$consultants = $consultantsMapper->fetchAll();
			
				$start = $term->getStartDate();
				list($y, $m, $d) = explode('-', $start);
				$date = mktime(0, 0, 0, $m, $d, $y);
				while (date('w', $date) != 0)
				{
					$date += 60 * 60 * 24;
				}

				$start = date('Y-m-d', $date);
				$end = date('Y-m-d', $date + (60 * 60 * 24 * 6));

				$shifts = $shiftMapper->fetchAllInRange($start, $end);

				$sched = array();
				foreach ($shifts as $shift)
				{
					$time = $shift->getTimeString();
					if (!array_key_exists($time, $sched))
					{
						$sched[$time] = array(
							'Monday' => array(),
							'Tuesday' => array(),
							'Wednesday' => array(),
							'Thursday' => array(),
							'Friday' => array(),
							'Saturday' => array(),
							'Sunday' => array(),
						);
					}

					$wday = date('l', strtotime($shift->getDate()));
					$loc = $shift->getLocation();
					if (array_key_exists($loc, $sched[$time][$wday]))
					{
						// Error: Multiple shifts for a location not yet supported
						$this->_messenger->addMessage(
								'Multiple shifts detected for single location and time');
					}

					$sched[$time][$wday][$loc] = $shift;
				}

				ksort($sched);
				$this->view->sched = $sched;
				$this->view->consultants = $consultants;
			}
			catch (Exception $e)
			{
				$this->_messenger->addMessage('No future or current terms to schedule');
			}
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from assigning shifts.');
		}
    }

    public function specialAction()
    {
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
			$this->_messenger->addMessage('You are forbidden from creating special shifts');
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
							$temp->setTimeout(TIMEOUT_SPECIAL);
							$shiftMapper->save($shift);
							$tempMapper->save($temp);
							
							$this->mailAssigned($temp);
						}
						else 
						{
							$shiftMapper->delete($shift);	
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
			$this->_messenger->addMessage('Forbidden!!!!');
		}
    }

    public function setAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();

		if ($user->isAdmin())
		{
			$consultantMapper = new Application_Model_ConsultantMapper();
			$shiftMapper = new Application_Model_ShiftMapper();

			$request = $this->getRequest();

			$consultantId = $request->getParam('consultant');
			if ($consultantId == null)
			{
				$consultant = null;
			}
			else
			{
				$consultant = $consultantMapper->find($consultantId);
			}

			$shiftId = $request->getParam('shift');
			$shift = $shiftMapper->find($shiftId);
			$shifts = $shiftMapper->fetchAllSimilar($shift);
			foreach ($shifts as $s)
			{
				$s->setConsultant($consultant);
				$shiftMapper->save($s);
			}
		}
    }

    public function availableAction()
    {
        $shiftMapper = new Application_Model_ShiftMapper();
		$this->view->available = $shiftMapper->fetchAllUnassignedThisTerm();
    }
	
	public function tempAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		
		if ($user->isAdmin())
		{
			$shiftMapper = new Application_Model_ShiftMapper();
			$tempMapper = new Application_Model_TempShiftMapper();
			$unassignedShifts = $shiftMapper->fetchAllUnassignedThisTerm();
			
			foreach ($unassignedShifts as $unassigned)
			{
				$shifts = $shiftMapper->fetchAllSimilar($unassigned);
				foreach ($shifts as $shift)
				{
					$temp = new Application_Model_TempShift();
					$temp->setShift($shift);
					$tempMapper->save($temp);
				}
			}
			
			$this->_messenger->addMessage("This term's unassigned shifts have been temped");
			return $this->_helper->redirector('index', 'index');
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from performing this action');
		}
	}

    public function takeAction()
    {
        $user = Zend_Auth::getInstance()->getIdentity();
		$shiftMapper = new Application_Model_ShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		$this->view->success = false;
		
		$this->view->shift = $shiftMapper->find($id);
		if ($this->view->shift !== null)
		{
			$shifts = $shiftMapper->fetchAllSimilar($this->view->shift);
			foreach ($shifts as $shift)
			{
				if ($shift->getConsultant() !== null)
				{
					$this->_messenger->addMessage('This shift is already assigned');
					return;
				}
			}

			// TODO: Use transactions so the two loops can be merged
			foreach ($shifts as $shift)
			{
				$shift->setConsultant($user);
				$shiftMapper->save($shift);
			}
		}
		else
		{
			$this->_messenger->addMessage('No such shift exists');
			return;
		}
		
		$this->view->success = true;
    }
	
	private function mailAssigned(Application_Model_TempShift $temp)
	{
		$consultant = $temp->getAssignedConsultant();
		
		$config = Zend_Registry::get('config');
		$options = $config['mail'];

		list($start, $end) = explode(' - ', $temp->getShift()->getTimeString());
		$date = date('D, M j', $temp->getShift()->getStartTimeStamp());
		
		$acceptPath = $this->view->url(array(
				'controller' => 'temp',
				'action'     => 'take',
				'id'         => $temp->getId(),
			), null, true);
		
		$refusePath = $this->view->url(array(
				'controller' => 'temp',
				'action'     => 'refuse',
				'id'         => $temp->getId(),
			), null, true);
		
		$acceptUrl = $this->getRequest()->getScheme() . '://' .
				$this->getRequest()->getHttpHost() . $acceptPath;
		$refuseUrl = $this->getRequest()->getScheme() . '://' .
				$this->getRequest()->getHttpHost() . $refusePath;
		
		$accept = '<a href="' . $acceptUrl . '">Accept</a>';
		$refuse = '<a href="' . $refuseUrl . '">Refuse</a>';
		
		$html = "You have been given priority over a shift from {$start} to " .
				"{$end} on {$date}. {$accept}, {$refuse}";

		$mail = new Zend_Mail();
		$mail->setBodyHtml($html);
		$mail->addTo($consultant->getEmail(), $consultant->getName());
		$mail->setSubject($options['assigned']['subject']);

		$mail->send();
	}


}

















