<?php

class TempController extends Zend_Controller_Action
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
        // action body
    }

    public function createAction()
    {
    	$user = Zend_Auth::getInstance()->getIdentity();
    	$shiftMapper = new Application_Model_ShiftMapper();
    	$tempMapper = new Application_Model_TempShiftMapper();
    	
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $shift = $shiftMapper->find($id);
        
        if ($user->getId() == $shift->getConsultant()->getId())
        {
	        $temp = new Application_Model_TempShift();
	        $temp->setPostTime(time());
	        $temp->setShift($shift);
	        $tempMapper->save($temp);
	        
	        $this->gotoSchedule($shift->getDate());
        }
        else
        {
        	$this->_messenger->addMessage('You are forbidden from temping this shift');
        	$this->gotoSchedule($shift->getDate());
        }
        
    }

    public function cancelAction()
    {
    	$user = Zend_Auth::getInstance()->getIdentity();
    	$tempMapper = new Application_Model_TempShiftMapper();
    	
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $temp = $tempMapper->find($id);
        
        if ($temp !== null)
        {
        	// Only let the shift's owner cancel the temp
	        if ($user->getId() == $temp->getShift()->getConsultant()->getId())
	        {
	        	$tempMapper->delete($temp);
	        }
	        // Allow the user that claimed the shift to change their mind
	        else if (($temp->getTempConsultant() !== null) and
	        		 ($user->getId() == $temp->getTempConsultant()->getId()))
	        {
	        	// Give up the temp shift
	        	$temp->setTempConsultant(null);
	        	$temp->setResponseTime(null);
	        	$tempMapper->save($temp);
	        	
				/*$date = $temp->getShift()->getDate();
				$startTime = $temp->getShift()->getStartTime();
				
				// Is it too late to cancel?
	        	$responseTime = strtotime("{$date} {$startTime}");
	        	//$allowed = strtotime("+1 hours", $responseTime);
	        	$allowed = strtotime("+1 minutes", $responseTime); // FOR TESTING ONLY
				// TODO: What if very close to the shift time?
				// If someone claims a shift 20 minutes until the shift
				// and then cancels, who is responsible? 
	        	
	        	if (time() < $allowed)
	        	{
		        	// Give up the temp shift
		        	$temp->setTempConsultant(null);
		        	$temp->setResponseTime(null);
		        	$tempMapper->save($temp);
	        	}
	        	else
	        	{
	        		$this->_messenger->addMessage('It is too late for you to cancel this shift');
	        	}*/
	        }
	        else
	        {
	        	$this->_messenger->addMessage('You are forbidden from cancelling this shift');
	        }
        }
        else
        {
	        $this->_messenger->addMessage('No such temp shift');
        }
        
        $this->gotoSchedule($temp->getShift()->getDate());
    }

    public function takeAction()
    {
    	$user = Zend_Auth::getInstance()->getIdentity();
    	$tempMapper = new Application_Model_TempShiftMapper();
    	
        $request = $this->getRequest();
        $id = $request->getParam('id');
        $temp = $tempMapper->find($id);
        
        if ($temp !== null)
        {
        	// If this isn't the user that temped the shift
	        if ($user->getId() != $temp->getShift()->getConsultant()->getId())
	        {
				$date = $temp->getShift()->getDate();
				$startTime = $temp->getShift()->getStartTime();
				
				// When is the shift fair game?
	        	$shiftTime = strtotime("{$date} {$startTime}");
	        	$fairGame = strtotime("-{$temp->getTimeout()} hours", $shiftTime);
	        	
	        	// Someone is assigned and the shift isn't fair game yet
	        	if (($temp->getAssignedConsultant() !== null) and
	        		(time() < $fairGame))
	        	{
		        	$assigned = $temp->getAssignedConsultant()->getName();
	        		$this->_messenger->addMessage("Waiting for {$assigned} to accept or refuse this shift");
	        	}
	        	else
	        	{
	        		// Claim
	        		$temp->setTempConsultant($user);
	        		$temp->setResponseTime(time());
	        		$tempMapper->save($temp);
	        	}
				
		        $this->gotoSchedule($temp->getShift()->getDate());
	        }
	        else
	        {
	        	// The regularly scheduled consultant is trying to claim the shift
	        	// Just cancel the temp
	        	$this->_redirector->gotoSimple('cancel', 'temp', null, array('id' => $id));
	        }
        }
        else
        {
        	$this->_messenger->addMessage('No such temp shift');
        	$this->gotoSchedule();
        }
    }
    
    private function gotoSchedule($date = null)
    {
    	if ($date === null)
    	{
    		$this->_redirector->gotoSimple('index', 'schedule');
    	}
    	else
    	{
	    	list($year, $month, $day) = explode('-', $date);
	    	$this->_redirector->gotoSimple('index', 'schedule', null, array(
	    			'year'  => $year,
	    			'month' => $month,
	    			'day'   => $day));
    	}
    }


}







