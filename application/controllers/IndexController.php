<?php

class IndexController extends Zend_Controller_Action
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
		
		$shifts = $shiftMapper->fetchAllByMonth(
				$this->view->month,
				$this->view->year);
		
		$days = array();
		foreach ($shifts as $shift)
		{
			$date = $shift->getDate();
			if (!array_key_exists($date, $days))
			{
				$days[$date] = false;
			}
			
			if (($shift->getConsultant() !== null) and ($shift->getConsultant()->getId() == $user->getId()))
			{
				$temp = $tempMapper->findByShift($shift);
				
				// No one has taken the shift yet
				if (($temp === null) or
					(($temp !== null) and ($temp->getTempConsultant() === null)))
				{
					$days[$date] = true;
				}
			}
			else
			{
				// Only query database again if we haven't found any shifts yet
				if ($days[$date] === false)
				{
					$temp = $tempMapper->findByShift($shift);
					if (($temp !== null) and
						($temp->getTempConsultant() !== null) and
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
		Zend_Auth::getInstance()->clearIdentity();
		
		$session = new Zend_Session_Namespace('whdsched');
		
		// If we're masquerading as another user, just cancel the masquerade
		if (isset($session->masquerade))
		{
			unset($session->masquerade);
			unset($session->actual);
		}
		// Otherwise log out
		else
		{
			// :(
			$response = $this->getResponse();
			$response->setHeader(
					'WWW-Authenticate',
					'Basic realm="COE Wireless Helpdesk Staff');
			$response->setRawHeader('HTTP/1.0 401 Unauthorized');
			$response->sendResponse();
			return;

			if (Zend_Auth::getInstance()->hasIdentity())
			{
				$this->_messenger->addMessage('You have been logged out');
			}
			else
			{
				$this->_messenger->addMessage('Unable to log out');
			}
		}

		$this->_redirector->gotoSimple('index', 'index');

		return;
	}
}
