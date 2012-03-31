<?php

function cmpTempShift(Application_Model_TempShift $a, Application_Model_TempShift $b)
{
	$atime = $a->getShift()->getStartTimestamp();
	$btime = $b->getShift()->getStartTimestamp();

	if ($atime == $btime)
	{
		return 0;
	}

	return ($atime < $btime) ? -1 : 1;
}

class TempController extends Zend_Controller_Action
{
	protected $_messenger;
	protected $_redirector;
	
	public function init()
	{
		$this->_messenger = $this->_helper->getHelper('FlashMessenger');
		$this->_redirector = $this->_helper->getHelper('Redirector');
	}

	public function indexAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$this->view->user = $user;
		
		$tempMapper = new Application_Model_TempShiftMapper();

		$this->view->days = array();
		foreach ($tempMapper->fetchAvailable() as $temp)
		{
			// Only show shifts that aren't assigned
			// or are assigned to the current user
			if (!$temp->isAssigned() or	$temp->isAssignedTo($user))
			{
				$date = $temp->getShift()->getDate();
				if (!array_key_exists($date, $this->view->days))
				{
					$this->view->days[$date] = array();
				}

				$this->view->days[$date][] = $temp;
			}
		}
		
		// Make sure they are sorted properly
		ksort($this->view->days);
		foreach ($this->view->days as $day => $shifts)
		{
			usort($shifts, 'cmpTempShift');
			$this->view->days[$day] = $shifts;
		}
	}

	public function createAction()
	{
		$log = Zend_Registry::get('log');
		$log->setEventItem('type', 'temp.create');
		
		$user = Zend_Auth::getInstance()->getIdentity();
		$shiftMapper = new Application_Model_ShiftMapper();
		$consultantMapper = new Application_Model_ConsultantMapper();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$showForm = $request->getParam('form');
		$isTemp = $request->getParam('temp');
		$confirm = $request->getParam('confirm');

		// The shift passed is a temp shift
		// Make the temp consultant the owner of the shift and delete the old temp
		if ($isTemp == true)
		{
			$temp = $tempMapper->find($id);
			$shift = $temp->getShift();
			
			$log->setEventItem('type', 'temp.retemp');
			
			if (!$shift->isInFuture() and $confirm != true)
			{
				$this->_redirector->gotoSimple('confirm-temp', 'temp', null, array(
					'id'   => $id,
					'temp' => $isTemp,
					'form' => $showForm,
					'goto' => $request->getParam('goto'),
				));
			}
			
			$shift->setConsultant($user);
			
			$tempMapper->delete($temp);
			$shiftMapper->save($shift);
		}
		else
		{
			$shift = $shiftMapper->find($id);
			
			if (!$shift->isInFuture() and $confirm != true)
			{
				$this->_redirector->gotoSimple('confirm-temp', 'temp', null, array(
					'id'   => $id,
					'temp' => $isTemp,
					'form' => $showForm,
					'goto' => $request->getParam('goto'),
				));
			}
		}
		
		$form = new Application_Form_Temp($shift);
		
		$timeUntilShift = $shift->getStartTimestamp() - time();
		$lateThresholdSecs = LATE_THRESHOLD * 60 * 60;
		if (($timeUntilShift < $lateThresholdSecs) and $shift->isInFuture())
		{
			if ($isTemp)
			{
				$log->setEventItem('type', 'temp.late-retemp');
			}
			else
			{
				$log->setEventItem('type', 'temp.late');
			}
		}
		
		// Authorized?
		if ($user->getId() == $shift->getConsultant()->getId())
		{
			// Was this a submission?
			if ($request->isPost())
			{
				if ($form->isValid($request->getPost()))
				{
					// Find ranges
					$values = $form->getValues();
					
					if (isset($values['preferred']))
					{
						$assigned = $consultantMapper->find($values['preferred']);
					}
					
					if (isset($values['hours']))
					{
						$hours = $values['hours'];
						
						// Find the ranges of hours the consultant wants to keep
						list($start, $m, $s) = explode(':', $shift->getStartTime());
						list($end, $m, $s) = explode(':', $shift->getEndTime());
						
						if ($end < $start)
						{
							$adjEnd = $end + 24;
							if (($adjEnd - $start) == count($hours))
							{
								$wholeShift = true;
							}
							else
							{
								$wholeShift = false;
							}
						}
					}
					
					if ($wholeShift === false)
					{
						$log->info("{$user->getName()} temped a partial shift {$shift}");
						
						$hours = $values['hours'];
						
						// Find the ranges of hours the consultant wants to keep
						list($start, $m, $s) = explode(':', $shift->getStartTime());
						list($end, $m, $s) = explode(':', $shift->getEndTime());

						$tmp = $this->getRanges(range($start, $end));
						$keepHours = array_diff(range($start, $end), $hours);
						$keepRanges = $this->getRanges($keepHours);

						// Find the ranges of hours the consultant wants to temp
						$tempRanges = $this->getRanges($hours);

						// Delete old shift
						$shiftMapper->delete($shift);

						// Create new shifts for each range
						foreach ($tempRanges as $range)
						{
							// The start of this range falls on the next day
							if ($range['start'] < $start)
							{
								// Increment the date by one day
								list($year, $month, $day) = explode('-', $shift->getDate());
								$date = implode('-', array($year, $month, $day + 1));
							}
							else
							{
								$date = $shift->getDate();
							}

							// Sorry Andy, the comments are going to get a little hazy from
							// here (chronologically, not necessarily line based) - dja
							$rangeStart = $range['start'];

							// Because the ranges will only be based on the start of the hour
							$rangeEnd = $range['end'] + 1;

							// Make sure we're not beyond the end of the shift
							$rangeEnd = ($rangeEnd <= $end) ? $rangeEnd : $end;

							// Make sure it's still a valid shift
							if ($rangeStart != $rangeEnd)
							{
								// Create the new shift
								$newShift = clone $shift;
								$newShift->setStartTime($rangeStart . ':00:00');
								$newShift->setEndTime($rangeEnd . ':00:00');
								$newShift->setDate($date);

								$shiftMapper->save($newShift);

								// Temp the new shift
								$temp = new Application_Model_TempShift();
								$temp->setShift($newShift);
								
								if (isset($assigned))
								{
									$temp->setAssignedConsultant($assigned);
									$temp->setTimeout(TIMEOUT_DEFAULT);
								}
								
								$tempMapper->save($temp);
								
								if (isset($assigned))
								{
									$this->mailAssigned($temp);
								}
								else
								{
									$this->mailTemp($temp);
								}
							}
						}

						// Create new shifts for each kept range
						foreach ($keepRanges as $range)
						{
							// The start of this range falls on the next day
							if ($range['start'] < $start)
							{
								// Increment the date by one day
								list($year, $month, $day) = explode('-', $shift->getDate());
								$date = implode('-', array($year, $month, $day + 1));
							}
							else
							{
								$date = $shift->getDate();
							}

							// Sorry Andy, the comments are going to get a little hazy from
							// here (chronologically, not necessarily line based) - dja
							$rangeStart = $range['start'];

							// Because the ranges will only be based on the start of the hour
							$rangeEnd = $range['end'] + 1;

							// Make sure we're not beyond the end of the shift
							$rangeEnd = ($rangeEnd <= $end) ? $rangeEnd : $end;

							// Make sure it's still a valid shift
							if ($rangeStart != $rangeEnd)
							{
								// Create the new shift
								$newShift = clone $shift;
								$newShift->setStartTime($rangeStart . ':00:00');
								$newShift->setEndTime($rangeEnd . ':00:00');
								$newShift->setDate($date);

								$shiftMapper->save($newShift);
							}
						}
					}
					else
					{
						$log->info("{$user->getName()} temped a shift {$shift}");
						
						// Temp the new shift
						$temp = new Application_Model_TempShift();
						$temp->setShift($shift);
						
						if (isset($assigned))
						{
							$temp->setAssignedConsultant($assigned);
							$temp->setTimeout(TIMEOUT_DEFAULT);
						}
						
						$tempMapper->save($temp);
						
						if (isset($assigned))
						{
							$this->mailAssigned($temp);
						}
						else
						{
							$this->mailTemp($temp);
						}
					}
					
					$this->handleRedirect($request, $shift->getDate());
				}
				else
				{
					// Errors, show form again
					$this->view->form = $form;
				}
			}
			else
			{
				// Not submission
				
				// Was the form requested?
				if ($showForm)
				{
					$this->view->form = $form; // Show it
				}
				else
				{
					$log->info("{$user->getName()} temped a shift {$shift}");
					
					// Form not requested, just temp the whole shift
					$temp = new Application_Model_TempShift();
					$temp->setShift($shift);
					$tempMapper->save($temp);
					$this->mailTemp($temp);
					
					$this->handleRedirect($request, $shift->getDate());
				}
			}
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from temping this shift');
			$this->handleRedirect($request, $shift()->getDate());
		}
	}
	
	public function confirmTempAction()
	{
		$log = Zend_Registry::get('log');
		$log->setEventItem('type', 'temp.create');
		
		$shiftMapper = new Application_Model_ShiftMapper();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$this->view->showForm = $request->getParam('form');
		$this->view->isTemp = $request->getParam('temp');
		$this->view->goto = $request->getParam('goto');

		// The shift passed is a temp shift
		// Make the temp consultant the owner of the shift and delete the old temp
		if ($this->view->isTemp == true)
		{
			$this->view->temp = $tempMapper->find($id);
			$this->view->shift = $temp->getShift();
		}
		else
		{
			$this->view->shift = $shiftMapper->find($id);
		}
		
		if ($this->view->shift->isInFuture())
		{
			$this->view->inFuture = true;
		}
		else
		{
			$this->view->inFuture = false;
		}
	}
	
	public function confirmTakeAction()
	{
		$log = Zend_Registry::get('log');
		$log->setEventItem('type', 'temp.create');
		
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$this->view->goto = $request->getParam('goto');

		$temp = $tempMapper->find($id);
		$shift = $temp->getShift();
		$this->view->temp = $temp;
		$this->view->shift = $shift;
		
		if ($temp->getShift()->isInFuture())
		{
			$this->view->inFuture = true;
		}
		else
		{
			$this->view->inFuture = false;
		}
	}

	public function cancelAction()
	{
		$log = Zend_Registry::get('log');
		$log->setEventItem('type', 'temp.cancel');
		
		$user = Zend_Auth::getInstance()->getIdentity();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$temp = $tempMapper->find($id);
		
		if ($temp !== null)
		{
			// Only let the shift's owner cancel the temp
			if (($temp->getShift()->getConsultant()) and
				($user->getId() == $temp->getShift()->getConsultant()->getId()))
			{
				$log->info("{$user->getName()} cancelled a temp {$temp}");
				$tempMapper->delete($temp);
			}
			// Allow the user that claimed the shift to change their mind
			else if (($temp->getTempConsultant() !== null) and
					 ($user->getId() == $temp->getTempConsultant()->getId()))
			{
				// Is it too late to cancel?
				$responseTime = strtotime($temp->getResponseTime());
				$allowed = strtotime("+1 hours", $responseTime);
				// TODO: What if very close to the shift time?
				// If someone claims a shift 20 minutes until the shift
				// and then cancels, who is responsible? 
				
				if (time() < $allowed)
				{
					$log->info("{$user->getName()} is no longer covering {$temp}");
				
					// Give up the temp shift
					$temp->setTempConsultant(null);
					$temp->setResponseTime(null);
					$tempMapper->save($temp);
					
					$this->mailCancelled($temp);
				}
				else
				{
					$this->_messenger->addMessage('It is too late for you to cancel this shift');
				}
			}
			else
			{
				$this->_messenger->addMessage('You are forbidden from cancelling this shift');
			}
			
			$this->handleRedirect($request, $temp->getShift()->getDate());
		}
		else
		{
			$this->_messenger->addMessage('No such temp shift');
			$this->handleRedirect($request);
		}
		
	}

	public function takeAction()
	{
		$log = Zend_Registry::get('log');
		$log->setEventItem('type', 'temp.take');
		
		$user = Zend_Auth::getInstance()->getIdentity();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$temp = $tempMapper->find($id);
		$confirm = $request->getParam('confirm');
		
		if ($temp !== null)
		{
			// If this isn't the user that temped the shift
			if (($temp->getShift()->getConsultant() === null) or
				($user->getId() != $temp->getShift()->getConsultant()->getId()))
			{
				$date = $temp->getShift()->getDate();
				$startTime = $temp->getShift()->getStartTime();
				
				// When is the shift fair game?
				$shiftTime = strtotime("{$date} {$startTime}");
				$fairGame = strtotime("-{$temp->getTimeout()} hours", $shiftTime);
				
				// Someone is assigned and the shift isn't fair game yet
				if (($temp->isAssigned()) and
					(!$temp->isAssignedTo($user)) and
					(time() < $fairGame))
				{
					$assigned = $temp->getAssignedConsultant()->getName();
					$this->_messenger->addMessage("Waiting for {$assigned} to accept or refuse this shift");
				}
				else
				{
					if ($temp->getTempConsultant() !== null)
					{
						$owner = $temp->getTempConsultant()->getName();
						$this->_messenger->addMessage("{$owner} has already claimed this shift");
					}
					else
					{
						if (!$temp->getShift()->isInFuture() and $confirm != true)
						{
							$this->_redirector->gotoSimple('confirm-take', 'temp', null, array(
								'id'   => $id,
								'goto' => $request->getParam('goto'),
							));
						}
						
						$log->info("{$user->getName()} is covering {$temp}");
						
						// Claim
						$temp->setTempConsultant($user);
						$temp->setResponseTime(date('Y-m-d H:i:s'));
						$temp->setAssignedConsultant(null);
						$temp->setTimeout(null);
						$tempMapper->save($temp);
						
						$this->mailTaken($temp);
					}
				}
				
				$this->handleRedirect($request, $temp->getShift()->getDate());
			}
			else
			{
				// The regularly scheduled consultant is trying to claim the shift
				// Just cancel the temp
				$this->_redirector->gotoSimple('cancel', 'temp', null, array(
						'id' => $id,
						'goto' => $request->getParam('goto')
				));
			}
		}
		else
		{
			$this->_messenger->addMessage('No such temp shift. It may have been cancelled');
			$this->handleRedirect($request);
		}
	}
	
	public function refuseAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$temp = $tempMapper->find($id);
		
		if ($temp !== null)
		{
			if ($temp->isAssignedTo($user))
			{
				$temp->setAssignedConsultant(null);
				$temp->setTimeout(null);
				$tempMapper->save($temp);
				
				$this->handleRedirect($request);
			}
			else
			{
				$this->_messenger->addMessage('This shift is not assigned to you');
				$this->handleRedirect($request);
			}
		}
		else
		{
			$this->_messenger->addMessage('No such temp shift. It may have been cancelled');
			$this->handleRedirect($request);
		}
	}
	
	private function gotoSchedule($date = null)
	{
		if ($date === null)
		{
			$this->_redirector->gotoSimple('index', 'schedule');
		}
		else
		{
			list($year, $month, $day) = explode('-', $date);
			$this->_redirector->gotoSimple('index', 'schedule', null, array(
					'year'  => $year,
					'month' => $month,
					'day'   => $day));
		}
	}
	
	private function handleRedirect($request, $date = null)
	{
		$goto = $request->getParam('goto');
		if ($goto == 'sched')
		{
			$this->gotoSchedule($date);
		}
		else if ($goto == 'temps')
		{
			$this->_redirector->gotoSimple('index', 'temp');
		}
		else
		{
			$this->_redirector->gotoSimple('personal', 'schedule');
		}
	}
	
	private function getRanges(array $arr)
	{
		$ranges = array();
		$handled = array();
		foreach ($arr as $e)
		{
			if (!in_array($e, $handled))
			{
				$max = $e;
				$min = $e;
				while (in_array($max + 1, $arr))
				{
					$handled[] = ++$max;
				}
					
				while (in_array($min - 1, $arr))
				{
					$handled[] = --$min;
				}
					
				$ranges[] = array('start' => $min, 'end' => $max);
			}
		}
		
		return $ranges;
	}
	
	private function mailTemp(
			Application_Model_TempShift $temp,
			array $consultants = null)
	{
		if ($consultants === null)
		{
			$consultantsMapper = new Application_Model_ConsultantMapper();
			$consultants = $consultantsMapper->fetchAll();
		}
		
		$config = Zend_Registry::get('config');
		$options = $config['mail'];
		
		$consultantName = $temp->getShift()->getConsultant()->getName();
		
		$path = $this->view->url(array(
				'controller' => 'temp',
				'action'     => 'take',
				'id'         => $temp->getId(),
			), null, true);
		
		$url = $this->getRequest()->getScheme() . '://' .
				$this->getRequest()->getHttpHost() . $path;
		
		$lines = array();
		
		$lines[] = "{$consultantName} is looking for a temp for {$temp}";
		$lines[] = '<a href="' . $url . '">Claim this shift</a>';
		
		$html = implode('<br />', $lines);
		
		$mail = new Zend_Mail();
		$mail->setBodyHtml($html);
		$mail->addTo($options['to']['address'], $options['to']['name']);
		$mail->setSubject($options['instant']['subject']);
		
		foreach ($consultants as $consultant)
		{
			// If the consultant wishes to receive emails immediately
			// and is not the consultant that posted the shift
			if ($consultant->getReceiveInstant() and
				($consultant->getId() != $temp->getShift()->getConsultant()->getId()))
			{
				$mail->addBcc($consultant->getEmail(), $consultant->getName());
			}
		}
		
		$mail->send();
	}
	
	private function mailTaken(Application_Model_TempShift $temp)
	{
		// For special shifts
		if ($temp->getShift()->getConsultant() === null)
		{
			return;
		}
		
		$consultant = $temp->getShift()->getConsultant();
		
		// If the consultant wishes to receive
		// emails when their shifts are covered
		if ($consultant->getReceiveTaken())
		{
			$config = Zend_Registry::get('config');
			$options = $config['mail'];

			$takenBy = $temp->getTempConsultant()->getName();

			list($start, $end) = explode(' - ', $temp->getShift()->getTimeString());
			$date = date('D, M j', $temp->getShift()->getStartTimeStamp());

			$html = "{$takenBy} has covered your shift from {$start} to " .
					"{$end} on {$date}";

			$mail = new Zend_Mail();
			$mail->setBodyHtml($html);
			$mail->addTo($consultant->getEmail(), $consultant->getName());
			$mail->setSubject($options['taken']['subject']);

			$mail->send();
		}
	}
	
	private function mailCancelled(Application_Model_TempShift $temp)
	{
		// For special shifts
		if ($temp->getShift()->getConsultant() === null)
		{
			return;
		}
		
		$config = Zend_Registry::get('config');
		$options = $config['mail'];
		
		$consultant = $temp->getShift()->getConsultant();
		
		list($start, $end) = explode(' - ', $temp->getShift()->getTimeString());
		$date = date('D, M j', $temp->getShift()->getStartTimeStamp());
		
		$html = "Your shift from {$start} to {$end} on {$date} is no longer " .
				"covered. You will be held responsible for this shift unless " .
				"someone claims it before the shift begins.";
		
		$mail = new Zend_Mail();
		$mail->setBodyHtml($html);
		$mail->addTo($consultant->getEmail(), $consultant->getName());
		$mail->setSubject($options['cancelled']['subject']);
		
		$mail->send();
	}
	
	private function mailAssigned(Application_Model_TempShift $temp)
	{
		// For special shifts
		if ($temp->getShift()->getConsultant() === null)
		{
			return;
		}
		
		$consultant = $temp->getAssignedConsultant();
		
		$config = Zend_Registry::get('config');
		$options = $config['mail'];

		$assignedBy = $temp->getShift()->getConsultant()->getName();

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
		
		$html = "{$assignedBy} has assigned you to a shift from {$start} to " .
				"{$end} on {$date}. {$accept}, {$refuse}";

		$mail = new Zend_Mail();
		$mail->setBodyHtml($html);
		$mail->addTo($consultant->getEmail(), $consultant->getName());
		$mail->setSubject($options['assigned']['subject']);

		$mail->send();
	}


}







