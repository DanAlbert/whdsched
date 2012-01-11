<?php

class ConsultantsController extends Zend_Controller_Action
{

    public function init()
    {
		// Initialize action controller here
    }

    public function indexAction()
    {
		$this->view->messages = array();
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
		$this->view->messages = array();
		$user = Zend_Auth::getInstance()->getIdentity();
		
		$form = new Application_Form_EditConsultant();
		$consultantMapper = new Application_Model_ConsultantMapper();
		
		$request = $this->getRequest();
		$id = $request->getParam('id');
		$consultant = $consultantMapper->find($id);
		
		if ($consultant === null)
		{
			$this->view->error = "No such consultant";
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
						'nightly'   => $consultant->getReceiveNightly() ? 
							'yes' : 'no',
						'instant'   => $consultant->getReceiveInstant() ? 
							'yes' : 'no',
					);
					
					$form->populate($defaults);
	
					$this->view->form = $form;
				}
			}
			else
			{
				$this->view->messages[] = "You are forbidden from editing this user's information.";
			}
		}
		
    }

    public function deleteAction()
    {
		$this->view->messages = array();
		$user = Zend_Auth::getInstance()->getIdentity();
		
		if ($user->isAdmin())
		{
			$consultantMapper = new Application_Model_ConsultantMapper();
			
			$request = $this->getRequest();
			$id = $request->getParam('id');
			$consultant = $consultantMapper->find($id);
			
			if ($consultant === null)
			{
				$this->view->messages[] = "No such consultant";
			}
			else
			{
				$consultantMapper->delete($consultant);
				
				return $this->_helper->redirector('index');
			}
		}
		else
		{
			$this->view->messages[] = "You are forbidden from deleting users.";
		}
		
    }

    public function createAction()
    {
		$this->view->messages = array();
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
						$this->view->messages[] = "Could not insert consultant";
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
			$this->view->messages[] = "You are forbidden from creating users.";
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


}






