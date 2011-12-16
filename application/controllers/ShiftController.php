<?php

class ShiftController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function createAction()
    {
		$form = new Application_Form_Shift();
		$request = $this->getRequest();
		
		// Submitted data?
		if ($request->isPost())
		{
			// Valid data?
			if ($form->isValid($request->getPost()))
			{
				$shiftMapper = new Application_Model_ShiftMapper();
				$termMapper = new Application_Model_TermMapper();
				
				$shift = new Application_Model_Shift();
				
				$values = $form->getValues();
				
				$shift->setStartTime(strval($values['startTime']) . '00');
				$shift->setEndTime(strval($values['endTime']) . '00');
				$days = $values['days'];
				
				$term = $termMapper->find($values['term']);
				
				// Make UNIX timestamps out of the SQL date stamps
				list($year, $month, $day) = explode('-', $term->getStartDate());
				$date = mktime(0, 0, 0, $month, $day, $year);
				
				list($year, $month, $day) = explode('-', $term->getEndDate());
				$endDate = mktime(0, 0, 0, $month, $day + 1, $year);
				
				// Within the range...
				while ($date < $endDate)
				{
					$d = getdate($date);
					$wday = $d['wday']; // Day of week
					
					// Add shift for this day?
					if (in_array($wday, $days))
					{
						$shift->setDate(date("Y-m-d", $date)); // Format for SQL

						// Create a shift for each selected location
						foreach ($values['location'] as $location)
						{
							$shift->setId(null); // Insert each time, not update
							$shift->setLocation($location);

							// For testing
							/*$this->view->error .= 'Creating shift from ' .
									$shift->getStartTime() . ' - ' .
									$shift->getEndTime() . ' @ ' .
									$shift->getLocation() . ' on ' .
									$d['weekday'] . ' ' .
									$shift->getDate() . '<br />';*/
							
							if ($shiftMapper->save($shift) <= 0)
							{
								$this->view->error = "Could not insert shift";
							}
						}
					}
					
					$date = mktime(0, 0, 0,
							date('m', $date),
							date('d', $date) + 1,
							date('y', $date)); // Next day
				}
				
				// An error will be displayed if any insert failed
				if (!isset($this->view->error))
				{
					return $this->_helper->redirector('index');
				}
			}
			else
			{
				$this->view->form = $form;
			}
		}
		else
		{
			$this->view->form = $form;
		}
    }

    public function assignAction()
    {
		$form = new Application_Form_Assign();
		$request = $this->getRequest();
		
		if ($request->isPost())
		{			
			if ($form->isValid($request->getPost()))
			{
				$shiftMapper = new Application_Model_ShiftMapper();
				$consultantMapper = new Application_Model_ConsultantMapper();
				$termMapper = new Application_Model_TermMapper();
				
				$values = $form->getValues();
				
				$consultant = $consultantMapper->find($values['consultant']);
				$wday = $values['day'];
				
				$h = substr($values['startTime'], 0, 2);
				$m = substr($values['startTime'], 2, 2);
				$startTime = "{$h}:{$m}:00";
				
				$h = substr($values['endTime'], 0, 2);
				$m = substr($values['endTime'], 2, 2);
				$endTime = "{$h}:{$m}:00";
				
				$location = $values['location'];
				
				$term = $termMapper->find($values['term']);
				
				// Make UNIX timestamps out of the SQL date stamps
				list($year, $month, $day) = explode('-', $term->getStartDate());
				$date = mktime(0, 0, 0, $month, $day, $year);
				
				list($year, $month, $day) = explode('-', $term->getEndDate());
				$endDate = mktime(0, 0, 0, $month, $day + 1, $year);
				
				$shifts = $shiftMapper->fetchAllByTerm($term);
				foreach ($shifts as $shift)
				{
					list($year, $month, $day) = explode('-', $shift->getDate());
					$date = getdate(mktime(0, 0, 0, $month, $day, $year));
					
					if ($date['wday'] == $wday)
					{
						if (($shift->getStartTime() == $startTime) and
							($shift->getEndTime() == $endTime))
						{
							if ($location == $shift->getLocation())
							{
								$shift->setConsultant($consultant);
								$shiftMapper->save($shift);
							}
						}
					}
				}
				
				// An error will be displayed if any insert failed
				if (!isset($this->view->error))
				{
					return $this->_helper->redirector('index');
				}
			}
			else
			{
				$this->view->form = $form;
			}
		}
		else
		{
			$this->view->form = $form;
		}
    }
}





