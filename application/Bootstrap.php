<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initView()
	{
		$view = new Zend_View();
		$view->doctype('HTML5');
		$view->headTitle('Wireless Help Desk Scheduler');
		$view->headLink()->appendStylesheet('/css/global.css');
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
				'ViewRenderer');
		
		$viewRenderer->setView($view);
		
		return $view;
	}
}

