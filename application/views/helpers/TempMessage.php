<?php
/**
 *
 * @author Dan
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * ShiftText helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_TempMessage
{
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	public function tempMessage(
			Application_Model_TempShift $temp,
			Application_Model_Consultant $user)
	{
		$timeUntil = $temp->getShift()->getStartTimestamp() - time();
		$timeStr = $this->getTimeString($temp);
		
		// If the shift has a user assigned to it
		if ($temp->getShift()->isOwned())
		{
			$link = $this->getConsultantLink($temp->getShift()->getConsultant());
			
			if ($temp->isAssignedTo($user))
			{
				return "<strong>{$link} has assigned you to this shift</strong>";
			}
			else if ($temp->isAssigned())
			{
				$link = $this->getConsultantLink($temp->getAssignedConsultant());
				return "<em>Assigned to {$link}</em>";
			}
			else
			{
				// Less than 24 hours until this shift?
				if ($timeUntil < (60 * 60 * 24))
				{
					return "<strong>{$link} needs a temp {$timeStr}</strong>";
				}
				else
				{
					return "<em>{$link} needs a temp</em>";
				}
			}
		}
		else
		{
			if ($temp->isAssignedTo($user))
			{
				return "<strong>Assigned to you</strong>";
			}
			else if ($temp->isAssigned())
			{
				$link = $this->getConsultantLink($temp->getAssignedConsultant());
				return "<em>Assigned to {$link}</em>";
			}
			else
			{
				// Less than 24 hours until this shift?
				if ($timeUntil < (60 * 60 * 24))
				{
					$timeStr = ucfirst($timeStr);
					return "<strong>{$timeStr}</strong>";
				}
				else
				{
					return "<em>Not yet filled!</em>";
				}
			}
		}
	}
	
	/**
	 *  
	 */
	public function getTimeString(Application_Model_TempShift $temp)
	{
		$timeUntil = $temp->getShift()->getStartTimestamp() - time();

		// Less than 90 minutes until this shift
		if ($timeUntil < 60 * 90)
		{
			return 'in ' . floor($timeUntil / 60) . ' minutes';
		}
		// Less than 24 hours until this shift?
		else if ($timeUntil < (60 * 60 * 24))
		{
			return 'in ' . floor($timeUntil / 3600) . ' hours';
		}
		// More than a day in the future
		else
		{
			return null;
		}
	}
	
	/**
	 * 
	 */
	public function getConsultantLink(Application_Model_Consultant $consultant)
	{
		return '<a href="' . $this->view->url(array(
			'controller' => 'consultants',
			'action'     => 'view',
			'id'         => $consultant->getId(),
		), null, true) . '">' . $consultant->getFirstName() . '</a>';
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
