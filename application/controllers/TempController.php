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
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$tempMapper = new Application_Model_TempShiftMapper();

		$this->view->days = array();
		foreach ($tempMapper->fetchAvailable() as $temp)
		{
			$date = $temp->getShift()->getDate();
			if (!array_key_exists($date, $this->view->days))
			{
				$this->view->days[$date] = array();
			}

			$this->view->days[$date][] = $temp;
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
		$user = Zend_Auth::getInstance()->getIdentity();
		$shiftMapper = new Application_Model_ShiftMapper();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$showForm = $request->getParam('form');
		$isTemp = $request->getParam('temp');
		
		// The shift passed is a temp shift
		// Make the temp consultant the owner of the shift and delete the old temp
		if ($isTemp == true)
		{
			$temp = $tempMapper->find($id);
			$shift = $temp->getShift();
			$shift->setConsultant($user);
			
			$tempMapper->delete($temp);
			$shiftMapper->save($shift);
		}
		else
		{
			$shift = $shiftMapper->find($id);
		}
		
		$form = new Application_Form_Temp($shift);
		
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
							
							$tempMapper->save($temp);
							$this->mailTemp($temp);
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

	public function cancelAction()
	{
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
					// Give up the temp shift
					$temp->setTempConsultant(null);
					$temp->setResponseTime(null);
					$tempMapper->save($temp);
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
		$user = Zend_Auth::getInstance()->getIdentity();
		$tempMapper = new Application_Model_TempShiftMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$temp = $tempMapper->find($id);
		
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
				if (($temp->getAssignedConsultant() !== null) and
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
						// Claim
						$temp->setTempConsultant($user);
						$temp->setResponseTime(date('Y-m-d H:i:s'));
						$tempMapper->save($temp);
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
		$mail->addTo('');
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


}







