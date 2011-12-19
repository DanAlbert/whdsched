<?php

class ScheduleController extends Zend_Controller_Action
{

	public function init()
	{
		/* Initialize action controller here */
	}

	public function indexAction()
	{
		$this->view->messages = $this->_helper->getHelper('FlashMessenger')->getMessages();
		
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		
		$day = $this->getRequest()->getParam('day');
		$month = $this->getRequest()->getParam('month');
		$year = $this->getRequest()->getParam('year');
		
		if (($day == null) and ($month == null) and ($year == null))
		{
			$this->timestamp = time();
		}
		else
		{
			if (($day == null) or ($month == null) or ($year == null))
			{
				$this->view->error = "Incomplete date provided";
			}
			else
			{
				$this->timestamp = mktime(0, 0, 0, $month, $day, $year);
			}
		}
		if (isset($this->timestamp))
		{
			$this->view->timestamp = $this->timestamp;
			$this->view->schedule = $this->getSchedule();
		}
	}

	private function getSchedule()
	{
		$shiftMapper = new Application_Model_ShiftMapper();
		$tempShiftMapper = new Application_Model_TempShiftMapper();
		
		$shifts = $shiftMapper->fetchAllByDate($this->timestamp);
		foreach ($shifts as $key => $shift)
		{
			$temp = $tempShiftMapper->findByShift($shift);
			if ($temp !== null)
			{
				$shifts[$key] = $temp;
			}
		}
		
		return $shifts;
	}
}

?>
