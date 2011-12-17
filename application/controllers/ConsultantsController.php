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
		$this->view->consultants = $consultantMapper->fetchAll();
    }

    public function editAction()
    {
    	$this->view->messages = array();
    	$user = Zend_Auth::getInstance()->getIdentity();
    	
		$form = new Application_Form_Consultant();
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
			if ($consultant->getId() == $user->getId())
			{
				if ($request->isPost())
				{
					if ($form->isValid($request->getPost()))
					{
						$values = $form->getValues();
						$consultant->setFirstName($values['firstName']);
						$consultant->setLastName($values['lastName']);
						$consultant->setEngr($values['engr']);
						$consultant->setPhone($values['phone']);
	
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
					);
					
					$form->populate($defaults);
	
					$this->view->form = $form;
				}
			}
			else
			{
				$this->view->messages[] = "You are forbidden from editting this user's information.";
			}
		}
    }

    public function deleteAction()
    {
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
			$consultantMapper->delete($consultant);
			
			return $this->_helper->redirector('index');
		}
    }

    public function createAction()
    {
		$form = new Application_Form_Consultant();
		$request = $this->getRequest();
		
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
					$this->view->error = "Could not insert consultant";
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




