<?php

/**
 * The index/calendar controller.
 *
 * This is the default controller for the application. It is named as the index 
 * controller, but it would be better named as the calendar controller (or 
 * perhaps the calendar action under the schedule controller). Some day it will 
 * actually be properly named, but for now Zend is a pain, so it's what we have.
 */
class IndexController extends Zend_Controller_Action
{
	protected $_messenger;
	protected $_redirector;
	
	public function init()
	{
		$this->_messenger = $this->_helper->getHelper('FlashMessenger');
		$this->_redirector = $this->_helper->getHelper('Redirector');
	}

	/**
	 * The default action of this controller is to display a calendar.
	 * 
	 * HTTP GET Parameters:
	 * month:	a four digit representation of the year to be displayed
	 * year:	a one or two digit representation of the month to be displayed
	 *
	 * TODO: Bugs
	 *  -	HTTP GET parameters to this function are not validated. This
	 *      shouldn't cause any real problems, it just won't display any useful
	 *      information. 
	 */
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

		// This array is keyed by a SQL formatted date. Each element is a 
		// boolean, true indicates that the current user works on that day, 
		// false indicates that they do not.
		$days = array();
		foreach ($shifts as $shift)
		{
			$date = $shift->getDate();
			if (!array_key_exists($date, $days))
			{
				$days[$date] = false;
			}
			
			// TODO: this whole block should be extracted into a 
			//       consultantWorksOnDay() or similarly named method

			// is the current user the regularly assigned consultant?
			if (($shift->getConsultant() !== null) and
				($shift->getConsultant()->getId() == $user->getId()))
			{
				$temp = $tempMapper->findByShift($shift);
				// is there a temp for this shift?
				if ($temp === null)
				{
					$days[$date] = true;
				}
				else
				{
					// No one has taken the shift yet
					if (!$temp->isTaken())
					{
						$days[$date] = true;
					}
				}
			}
			else
			{
				// Don't need to run another query if we already know the user
				// works this day
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
		
		$session = Zend_Registry::get('session');
		
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
