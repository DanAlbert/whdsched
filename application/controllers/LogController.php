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
			$logsPerPage = 20;
			
			$page = $this->getRequest()->getParam('page');
			if ($page == null)
			{
				$page = 0;
			}
			else
			{
				$page--;
			}
			
			$logMapper = new Application_Model_LogMapper();
			
			$start = $logsPerPage * $page;
			$npages = ceil(count($logMapper->fetchAll()) / $logsPerPage);
			
			$this->view->logs = $logMapper->fetchDesc($logsPerPage, $start);
			$this->view->page = $page + 1;
			$this->view->totalPages = $npages;
		}
		else
		{
			$this->_helper->FlashMessenger('You are forbidden from accessing logs');
		}
    }


}

