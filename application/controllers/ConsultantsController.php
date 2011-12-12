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
}
