<?php

class LogController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$user = Zend_Auth::getInstance()->getIdentity();
		
		if ($user->isAdmin())
		{
			$logMapper = new Application_Model_LogMapper();
			$this->view->logs = $logMapper->fetchAllDesc();
		}
		else
		{
			$this->_helper->FlashMessenger('You are forbidden from accessing logs');
		}
    }


}

