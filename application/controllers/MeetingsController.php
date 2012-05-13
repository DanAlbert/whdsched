<?php

class MeetingsController extends Zend_Controller_Action
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
		$meetingMapper = new Application_Model_MeetingMapper();
        $this->view->meetings = $meetingMapper->fetchAll();
    }

    public function termAction()
    {
		$meetingMapper = new Application_Model_MeetingMapper();
		$termMapper = new Application_Model_TermMapper();
		
		$request = $this->getRequest();
		$params = $request->getParams();
		
		try
		{
			if (isset($params['id']))
			{
				$id = $params['id'];
				$term = $termMapper->find($id);
			}
			else
			{
				$term = $termMapper->getCurrentTerm();
			}
			
			$this->view->meetings = $meetingMapper->fetchAllByTerm($term);
		}
		catch (Exception $e)
		{
			$this->messenger->addMessage($e->getMessage());
		}
    }
	
	public function viewAction()
	{
		$meetingMapper = new Application_Model_MeetingMapper();
		$attendeesMapper = new Application_Model_MeetingAttendeesMapper();
		
        $request = $this->getRequest();
		$id = $request->getParam('id');
		
		$meeting = $meetingMapper->find($id);
		
		$this->view->meeting = $meeting;
		$this->view->attendees = $attendeesMapper->fetchConsultantsByMeeting($meeting);
	}
	
	public function createAction()
	{
		$form = new Application_Form_Meeting();
		$request = $this->getRequest();
		
		if ($this->user->isAllowed('meeting', 'create'))
		{
			if ($request->isPost())
			{
				if ($form->isValid($request->getPost()))
				{
					$meetingMapper = new Application_Model_MeetingMapper();
					$termMapper = new Application_Model_TermMapper();
					
					$params = $request->getParams();
					$termId = $params['term'];
					$location = $params['location'];
					$day = $params['day'];
					$startTime = $params['startTime'];
					$endTime = $params['endTime'];
					
					$term = $termMapper->find($termId);
					
					$meeting = new Application_Model_Meeting();
					$meeting->setTerm($term);
					$meeting->setDay($day);
					$meeting->setStartTime($startTime);
					$meeting->setEndTime($endTime);
					$meeting->setLocation($location);
					
					try
					{
						if ($meetingMapper->save($meeting) > 0)
						{
							return $this->_helper->redirector('index');
						}
						else
						{
							$this->messenger->addMessage("Could not insert consultant");
						}
					}
					catch (Zend_Db_Statement_Exception $e)
					{
						if ($e->getCode() == 23000)
						{
							$this->messenger->addMessage("Duplicate entry");
						}
						else
						{
							$this->messenger->addMessage("{$e->getCode()}: {$e->getMessage()}");
						}
						
						$this->view->form = $form;
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
		else
		{
			$this->messenger->addMessage("You are forbidden from creating meetings.");
		}
	}
	
	public function deleteConfirmAction()
	{
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		if ($this->user->isAllowed('meeting', 'delete'))
		{
			$meetingMapper = new Application_Model_MeetingMapper();
			$this->view->meeting = $meetingMapper->find($id);
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from removing meetings.');
		}
	}
	
	public function deleteAction()
	{
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		if ($this->user->isAllowed('meeting', 'delete'))
		{
			$meetingMapper = new Application_Model_MeetingMapper();
			
			try
			{
				$meeting = $meetingMapper->find($id);
				$meetingMapper->delete($meeting);
			}
			catch (Zend_Db_Statement_Exception $e)
			{
				$this->messenger->addMessage("{$e->getCode()}: {$e->getMessage()}");
			}
			
			return $this->_helper->redirector('index');
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from removing meetings.');
		}
	}
}
