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
		
		if ($request->isPost())
		{			
			if ($form->isValid($request->getPost()))
			{
				$shiftMapper = new Application_Model_ShiftMapper();
				$shift = new Application_Model_Shift();
				
				$values = $form->getValues();
				
				$shift->setStartTime($values['startTime'] . '00');
				$shift->setEndTime($values['endTime'] . '00');
				
				list($month, $day, $year) = explode('-', $values['startDate']);
				$date = mktime(0, 0, 0, $month, $day, $year);
				
				list($month, $day, $year) = explode('-', $values['endDate']);
				$endDate = mktime(0, 0, 0, $month, $day + 1, $year);
				
				while ($date < $endDate)
				{
					$shift->setDate(date("Y-m-d", $date));
					
					foreach ($values['location'] as $location)
					{
						$shift->setId(null); // Insert each time, not update
						
						$shift->setLocation($location);
						/*$this->view->error .= 'Creating shift ' .
								$shift->getStartTime() . ' - ' .
								$shift->getEndTime() . ' @ ' .
								$shift->getLocation() . ' on ' .
								$shift->getDate() . '<br />';*/

						if ($shiftMapper->save($shift) <= 0)
						{
							$this->view->error = "Could not insert shift";
						}
					}
					
					$date += 60 * 60 * 24; // Next day
				}
				
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



