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
class Zend_View_Helper_ShiftText
{
	
	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;
	
	/**
	 *  
	 */
	public function shiftText($shift, $goto)
	{
		$text = '';
		
		if ($shift instanceof Application_Model_TempShift)
		{
			$temp = $shift;
			$shift = $temp->getShift();
		}
		
		// Is there a temp shift?
		if (isset($temp))
		{
			// Has someone taken the shift?
			if ($temp->getTempConsultant())
			{
				if ($temp->getTempConsultant()->getId() == $this->view->user->getId())
				{
					// Is it too late to cancel?
					$responseTime = strtotime($temp->getResponseTime());
					$allowed = strtotime("+1 hours", $responseTime);
					
					if (time() < $allowed)
					{
						$text .= 'You are covering this shift (<a href="' . $this->view->url(array(
								'controller' => 'temp',
								'action'     => 'cancel',
								'id'         => $temp->getId(),
								'temp'       => true,
								'goto'       => $goto,
						), null, true) . '">cancel</a>)';
					}
					else
					{
						$text .= 'You are covering this shift ';
						
						$links = array();
							
						// Add a link to temp the shift
						$links[] = '<a href="' . $this->view->url(array(
								'controller' => 'temp',
								'action' => 'create',
								'id' => $temp->getId(),
								'temp'       => true,
								'goto' => $goto,
						), null, true) . '">request temp</a>';
							
						list($start, $m, $s) = explode(':', $shift->getStartTime());
						list($end, $m, $s) = explode(':', $shift->getEndTime());
							
						// If not a single hour temp
						// Absolute value to handle 2300-0200 shifts
						if (abs($end - $start) > 1)
						{
							// Add a link to temp the shift
							$links[] = ' <a href="' . $this->view->url(array(
									'controller' => 'temp',
									'action' => 'create',
									'id' => $temp->getId(),
									'form' => true,
									'goto' => $goto,
							), null, true) . '">temp part</a>';
						}
							
						$text .= ' (' . implode(', ', $links) . ')';
					}
				}
				else
				{
					$text .= $temp->getTempConsultant()->getName() . ' is covering this shift';
				}
			}
			else
			{
				if ($shift->getConsultant()->getId() == $this->view->user->getId())
				{
					$text .= '<a href="' . $this->view->url(array(
							'controller' => 'temp',
							'action'     => 'cancel',
							'id'         => $temp->getId(),
							'goto'       => $goto,
					), null, true) . '">Cancel this temp shift</a>';
				}
				else
				{
					// Allow someone to claim the shift
					$text .= '<a href="' . $this->view->url(array(
							'controller' => 'temp',
							'action'     => 'take',
							'id'         => $temp->getId(),
							'goto'       => $goto,
					), null, true) . '">Take this shift</a>';
				}
			}
		}
		// No temp shift
		else
		{
			// Is the shift assigned?
			if ($shift->getConsultant() !== null)
			{
				// No temp, assigned
				$text .= $shift->getConsultant()->getName();
		
				if ($shift->getConsultant()->getId() == $this->view->user->getId())
				{
					$links = array();
						
					// Add a link to temp the shift
					$links[] = '<a href="' . $this->view->url(array(
							'controller' => 'temp',
							'action' => 'create',
							'id' => $shift->getId(),
							'goto' => $goto,
					), null, true) . '">request temp</a>';
						
					list($start, $m, $s) = explode(':', $shift->getStartTime());
					list($end, $m, $s) = explode(':', $shift->getEndTime());
						
					// If not a single hour temp
					// Absolute value to handle 2300-0200 shifts
					if (abs($end - $start) > 1)
					{
						// Add a link to temp the shift
						$links[] = ' <a href="' . $this->view->url(array(
								'controller' => 'temp',
								'action' => 'create',
								'id' => $shift->getId(),
								'form' => true,
								'goto' => $goto,
						), null, true) . '">temp part</a>';
					}
						
					$text .= ' (' . implode(', ', $links) . ')';
				}
			}
			else
			{
				// No temp, no consultant
				$text .= 'No consultant assigned';
			}
		}
		
		return $text;
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
