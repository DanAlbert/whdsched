<?php

class MeetingAttendeesController extends Zend_Controller_Action
{
	private $messenger;
	private $user;
	
    public function init()
    {
		$this->user = Zend_Auth::getInstance()->getIdentity();
		$this->view->user = $this->user;
		
		$this->messenger = $this->_helper->getHelper('FlashMessenger');
    }

    public function indexAction()
    {
        // action body
    }
	
	public function addAction()
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		$meetingMapper = new Application_Model_MeetingMapper();
		
		$request = $this->getRequest();
		$params = $request->getParams();
		
		$meetingId = $params['meeting'];
		$consultantId = $params['consultant'];
		
		if ($this->user->getId() == $consultantId)
		{
			$attendeeMapper = new Application_Model_MeetingAttendeesMapper();
			
			try
			{
				$consultant = $consultantMapper->find($consultantId);
				$meeting = $meetingMapper->find($meetingId);
				
				$attendee = new Application_Model_MeetingAttendee();
				$attendee->setConsultant($consultant);
				$attendee->setMeeting($meeting);
				$attendeeMapper->save($attendee);
			}
			catch (Exception $e)
			{
				$this->messenger->addMessage("Could not add user to meeting: {$e->getMessage()}");
			}
		}
		else
		{
			$this->messenger->addMessage("You are forbidden from adding other users to meetings.");
		}
		
		return $this->_helper->redirector('term', 'meetings');
	}
	
	public function removeAction()
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		$meetingMapper = new Application_Model_MeetingMapper();
		
		$request = $this->getRequest();
		$params = $request->getParams();
		
		$meetingId = $params['meeting'];
		$consultantId = $params['consultant'];
		
		if ($this->user->getId() == $consultantId)
		{
			$attendeeMapper = new Application_Model_MeetingAttendeesMapper();
			
			try
			{
				$consultant = $consultantMapper->find($consultantId);
				$meeting = $meetingMapper->find($meetingId);
				
				$attendee = $attendeeMapper->findWhere($meeting, $consultant);
				$attendeeMapper->delete($attendee);
			}
			catch (Exception $e)
			{
				$this->messenger->addMessage("Could not remove user from meeting: {$e->getMessage()}");
			}
		}
		else
		{
			$this->messenger->addMessage("You are forbidden from removing other users from meetings.");
		}
		
		return $this->_helper->redirector('term', 'meetings');
	}
}

