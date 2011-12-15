<?php

class ConsultantsController extends Zend_Controller_Action
{

    public function init()
    {
        // Initialize action controller here
    }

    public function indexAction()
    {
        $consultantMapper = new Application_Model_ConsultantMapper();
		$this->view->consultants = $consultantMapper->fetchAll();
    }

    public function editAction()
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
			if ($request->isPost())
			{			
				$form = new Application_Form_Consultant();

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

				$form = new Application_Form_Consultant();
				$form->populate($defaults);

				$this->view->form = $form;
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
        $consultantMapper = new Application_Model_ConsultantMapper();
		
		$request = $this->getRequest();
		
		if ($request->isPost())
		{			
			$form = new Application_Form_Consultant();

			if ($form->isValid($request->getPost()))
			{
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
			$form = new Application_Form_Consultant();

			$this->view->form = $form;
		}
    }


}




