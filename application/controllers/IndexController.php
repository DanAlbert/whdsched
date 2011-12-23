<?php

class IndexController extends Zend_Controller_Action
{

	public function init()
	{
		/* Initialize action controller here */
	}

	public function indexAction()
	{
		$shiftMapper = new Application_Model_ShiftMapper();
		$tempMapper = new Application_Model_TempShiftMapper();
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$this->view->month = $this->getRequest()->getParam('month');
		if ($this->view->month == null)
		{
			$this->view->month = date('m');
		}
		
		$this->view->year = $this->getRequest()->getParam('year');
		if ($this->view->year == null)
		{
			$this->view->year = date('Y');
		}
		
		$shifts = $shiftMapper->fetchAllByMonth(date($this->view->month));
		$days = array();
		foreach ($shifts as $shift)
		{
			$date = $shift->getDate();
			if (!array_key_exists($date, $days))
			{
				$days[$date] = false;
			}
			
			if ($shift->getConsultant()->getId() == $user->getId())
			{
				$days[$date] = true;
			}
			else
			{
				// Only query database again if we haven't found any shifts yet
				if ($days[$date] === false)
				{
					$temp = $tempMapper->findByShift($shift);
					if (($temp !== null) and
						($temp->getTempConsultant()->getId() == $user->getId()))
					{
						$days[$date] = true;
					}
				}
			}
		}
		
		$this->view->days = $days;
	}

	public function authenticateAction()
	{
	}

	public function logoutAction()
	{
		$this->view->messages = array();
		
		Zend_Auth::getInstance()->clearIdentity();
		if (Zend_Auth::getInstance()->hasIdentity())
		{
			$this->view->messages[] = 'You have been logged out';
		}
		else
		{
			$this->view->messages[] = 'Unable to log out';
		}
	}
}
