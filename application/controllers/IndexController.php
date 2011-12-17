<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$this->view->month = $this->getRequest()->getParam('month');
		if ($this->view->month == null)
		{
			$this->view->month = date('n');
		}
		
		$this->view->year = $this->getRequest()->getParam('year');
		if ($this->view->year == null)
		{
			$this->view->year = date('Y');
		}
    }

    public function authenticateAction()
    {
    }

    public function logoutAction()
    {
    	$this->view->messages = array();
    	
        Zend_Auth::getInstance()->clearIdentity();
        if (Zend_Auth::getInstance()->hasIdentity())
        {
        	$this->view->messages[] = 'You have been logged out';
        }
        else
        {
        	$this->view->messages[] = 'Unable to log out';
        }
    }
}
