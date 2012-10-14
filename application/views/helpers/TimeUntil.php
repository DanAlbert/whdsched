<?php
/**
 *
 * @author Dan
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * TimeUntil helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_TimeUntil
{
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 *  
	 */
	public function TimeUntil($event)
	{
		if ($event instanceof Application_Model_Meeting)
		{
			return $this->TimeUntilMeeting($event);
		}
		elseif (($event instanceof Application_Model_Shift) or
				($event instanceof Application_Model_TempShift))
		{
			return $this->TimeUntilShift($event);
		}
	}
	
	public function TimeUntilMeeting(Application_Model_Meeting $meeting)
	{
		$timeUntil = $meeting->getNextTimestamp() - time();
		
		if ($timeUntil <= 0)
		{
			return '<strong>Right now</strong>';
		}
		else
		{
			// Less than 90 minutes until this meeting
			if ($timeUntil < 60 * 90)
			{
				return '<em>In ' . floor($timeUntil / 60) . ' minutes</em>';
			}
			// Less than 24 hours until this meeting?
			else if ($timeUntil < (60 * 60 * 24))
			{
				return '<em>In ' . floor($timeUntil / 60 / 60) . ' hours</em>';
			}
			else
			{
				return '&nbsp;';
			}
		}
	}
	
	public function TimeUntilShift($shift)
	{	
		if ($shift instanceof Application_Model_TempShift)
		{
			$temp = $shift;
			$shift = $temp->getShift();
		}
		
		// If the user is not responsible for the shift
		if ((isset($temp)) and // There is a temp
			($temp->getTempConsultant() !== null) and // The shift has been taken
			($temp->getTempConsultant()->getId() != $this->user->getId())) // Not by you
		{
			$working = false;
		}
		else
		{
			$working = true;
		}
		
		$timeUntil = $shift->getStartTimestamp() - time();

		// If the user is not responsible for the shift
		if ($working === false)
		{
			return $temp->getTempConsultant()->getName() . ' is covering this shift';
		}
		else
		{
			if ($timeUntil <= 0)
			{
				return '<strong>Right now</strong>';
			}
			else
			{
				// Less than 90 minutes until this shift
				if ($timeUntil < 60 * 90)
				{
					return '<em>In ' . floor($timeUntil / 60) . ' minutes</em>';
				}
				// Less than 24 hours until this shift?
				else if ($timeUntil < (60 * 60 * 24))
				{
					return '<em>In ' . floor($timeUntil / 60 / 60) . ' hours</em>';
				}
				else
				{
					return '&nbsp;';
				}
			}
		}
	}
	
	/**
	 * Sets the view field
	 * 
	 * @param $view Zend_View_Interface       	
	 */
	public function setView(Zend_View_Interface $view)
	{
		$this->view = $view;
	}
}
