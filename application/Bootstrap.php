<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initView()
	{
		$view = new Zend_View();
		$view->getHelper('BaseUrl')->setBaseUrl('/whdsched/public/');
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
				'ViewRenderer');
		
		$viewRenderer->setView($view);
		
		return $view;
	}
	
	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$this->_view = $this->getResource('view');
		
		$this->_view->doctype('HTML5');
	}
	
	protected function _initTitle()
	{
		$this->bootstrap('view');
		$this->_view = $this->getResource('view');
		
		$this->_view->headTitle('Wireless Help Desk Scheduler');
	}
	
	protected function _initStylesheet()
	{
		$this->bootstrap('view');
		$this->_view = $this->getResource('view');
		
		$this->_view->headLink()->appendStylesheet(
				$this->view->baseUrl('css/global.css'));
	}
}

