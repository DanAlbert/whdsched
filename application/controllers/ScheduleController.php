<?php

class ScheduleController extends Zend_Controller_Action
{
	protected $_messenger;
	
    public function init()
    {
		$this->_messenger = $this->_helper->getHelper('FlashMessenger');
    }

    public function indexAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		$this->view->user = $user;
		
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		$day = $this->getRequest()->getParam('day');
		$month = $this->getRequest()->getParam('month');
		$year = $this->getRequest()->getParam('year');
		
		if (($day == null) and ($month == null) and ($year == null))
		{
			$timestamp = time();
		}
		else
		{
			if (($day == null) or ($month == null) or ($year == null))
			{
				$this->view->error = "Incomplete date provided";
			}
			else
			{
				$timestamp = mktime(0, 0, 0, $month, $day, $year);
			}
		}
		
		if (isset($timestamp))
		{
			$this->view->timestamp = $timestamp;
			$this->view->schedule = $this->getSchedule($timestamp);
		}
    }

	public function personalAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		$shiftMapper = new Application_Model_ShiftMapper();
		$tempShiftMapper = new Application_Model_TempShiftMapper();
		
		$days = array();
		
		// Get the user's next 10 shifts
		$shifts = $shiftMapper->fetchUpcomingShiftsByConsultant($user, true, 10);
		foreach ($shifts as $key => $shift)
		{
			$date = $shift->getDate();
			if (!array_key_exists($date, $days))
			{
				$days[$date] = array();
			}
			
			$temp = $tempShiftMapper->findByShift($shift);
			if ($temp !== null)
			{
				$days[$date][] = $temp;
			}
			else
			{
				$days[$date][] = $shift;
			}
		}
		
		$this->view->user = $user;
		$this->view->days = $days;
	}

    private function getSchedule($timestamp)
    {
		$shiftMapper = new Application_Model_ShiftMapper();
		$tempShiftMapper = new Application_Model_TempShiftMapper();
		$sched = array();
		
		$shifts = $shiftMapper->fetchAllByDate($timestamp);
		foreach ($shifts as $key => $shift)
		{
			$time = $shift->getTimeString();
			$location = $shift->getLocation();
			
			if (!array_key_exists($time, $sched))
			{
				$sched[$time] = array();
			}
			
			$temp = $tempShiftMapper->findByShift($shift);
			if ($temp !== null)
			{
				$sched[$time][$location] = $temp;
			}
			else
			{
				$sched[$time][$location] = $shift;
			}
		}
		
		return $sched;
	}


}


?>

