<?php

class ConsultantsController extends Zend_Controller_Action
{

    protected $_messenger = null;

    public function init()
    {
		$this->_messenger = $this->_helper->getHelper('FlashMessenger');
    }

    public function indexAction()
    {
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		// Only show hidden users to admins
		if ($this->view->user->isAdmin())
		{
			$this->view->consultants = $consultantMapper->fetchAllSorted(true);
		}
		else
		{
			$this->view->consultants = $consultantMapper->fetchAllSorted();
		}
    }

    public function editAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$form = new Application_Form_EditConsultant();
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$consultant = $consultantMapper->find($id);
		
		if ($consultant === null)
		{
			$this->_messenger->addMessage("No such consultant");
		}
		else
		{
			if (($consultant->getId() == $user->getId()) or ($user->isAdmin()))
			{
				if ($request->isPost())
				{
					if ($form->isValid($request->getPost()))
					{
						$values = $form->getValues();
						$consultant->setFirstName($values['firstName']);
						$consultant->setLastName($values['lastName']);
						$consultant->setPhone($values['phone']);
						$consultant->setPreferredEmail($values['email']);
						$consultant->setMaxHours($values['maxHours']);
						
						if ($values['nightly'] == 'yes')
						{
							$consultant->setReceiveNightly(true);
						}
						else
						{
							$consultant->setReceiveNightly(false);
						}
						
						if ($values['instant'] == 'yes')
						{
							$consultant->setReceiveInstant(true);
						}
						else
						{
							$consultant->setReceiveInstant(false);
						}
						
						if ($values['taken'] == 'yes')
						{
							$consultant->setReceiveTaken(true);
						}
						else
						{
							$consultant->setReceiveTaken(false);
						}
						
						$consultantMapper->save($consultant);
	
						return $this->_helper->redirector('index');
					}
					else
					{
						$this->view->form = $form;
					}
				}
				else
				{
					$defaults = array(
						'firstName' => $consultant->getFirstName(),
						'lastName'  => $consultant->getLastName(),
						'engr'      => $consultant->getEngr(),
						'phone'     => $consultant->getPhone(),
						'email'     => $consultant->getPreferredEmail(),
						'maxHours'  => $consultant->getMaxHours(),
						'nightly'   => $consultant->getReceiveNightly() ? 
							'yes' : 'no',
						'instant'   => $consultant->getReceiveInstant() ? 
							'yes' : 'no',
						'taken'     => $consultant->getReceiveTaken() ? 
							'yes' : 'no',
					);
					
					$form->populate($defaults);
	
					$this->view->form = $form;
				}
			}
			else
			{
				$this->_messenger->addMessage("You are forbidden from editing this user's information.");
			}
		}
    }
	
	public function deleteConfirmAction()
	{
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		
		if ($user->isAdmin())
		{
			$consultantMapper = new Application_Model_ConsultantMapper();
			$this->view->consultant = $consultantMapper->find($id);
		}
		else
		{
			$this->_messenger->addMessage('You are forbidden from removing consultants.');
		}
	}

    public function deleteAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		if ($user->isAdmin())
		{
			$consultantMapper = new Application_Model_ConsultantMapper();
			
			$request = $this->getRequest();
			$id = $request->getParam('id');
			$consultant = $consultantMapper->find($id);
			
			if ($consultant === null)
			{
				$this->_messenger->addMessage("No such consultant");
			}
			else
			{
				$consultantMapper->delete($consultant);
				
				return $this->_helper->redirector('index');
			}
		}
		else
		{
			$this->_messenger->addMessage("You are forbidden from deleting users.");
		}
    }

    public function createAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$form = new Application_Form_Consultant();
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			if ($request->isPost())
			{
				if ($form->isValid($request->getPost()))
				{
					$consultantMapper = new Application_Model_ConsultantMapper();
					$consultant = new Application_Model_Consultant();
					
					$values = $form->getValues();
					$consultant->setFirstName($values['firstName']);
					$consultant->setLastName($values['lastName']);
					$consultant->setEngr($values['engr']);
					$consultant->setPhone($values['phone']);
	
					$consultant->setId(null);
					
					if ($consultantMapper->save($consultant) > 0)
					{
						return $this->_helper->redirector('index');
					}
					else
					{
						$this->_messenger->addMessage("Could not insert consultant");
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
			$this->_messenger->addMessage("You are forbidden from creating users.");
		}
    }

    public function viewAction()
    {
		$consultantMapper = new Application_Model_ConsultantMapper();
		
        $request = $this->getRequest();
		$id = $request->getParam('id');
		$this->view->consultant = $consultantMapper->find($id);
		$this->view->user = Zend_Auth::getInstance()->getIdentity();
    }

    public function masqueradeAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$form = new Application_Form_Masquerade();
		$request = $this->getRequest();
		
		if ($user->isAdmin())
		{
			if ($request->isPost())
			{
				if ($form->isValid($request->getPost()))
				{
					$consultantMapper = new Application_Model_ConsultantMapper();
					$values = $form->getValues();
					$consultant = $consultantMapper->find($values['consultant']);
					
					$actual = Zend_Auth::getInstance()->getIdentity();
					
					Zend_Auth::getInstance()->clearIdentity();
					Zend_Auth::getInstance()->getStorage()->write($consultant);
					
					$session = new Zend_Session_Namespace('whdsched');
					$session->masquerade = $consultant->getId();
					$session->actual = $actual->getId();
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
			$this->_messenger->addMessage("You are forbidden from masquerading");
		}
    }

    public function hideAction()
    {
        $user = Zend_Auth::getInstance()->getIdentity();
		
		if ($user->isAdmin())
		{
			$consultantMapper = new Application_Model_ConsultantMapper();
			
			$request = $this->getRequest();
			$id = $request->getParam('id');
			$hide = $request->getParam('hide');
			$consultant = $consultantMapper->find($id);
			
			if ($consultant === null)
			{
				$this->_messenger->addMessage("No such consultant");
			}
			else
			{
				$consultant->setHidden($hide);
				$consultantMapper->save($consultant);
				
				return $this->_helper->redirector('index');
			}
		}
		else
		{
			$this->_messenger->addMessage("You are forbidden from hiding users.");
		}
    }
}










