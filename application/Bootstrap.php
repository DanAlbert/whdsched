<?php

require_once 'AuthDispatchPlugin.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initSession()
	{
		Zend_Session::setOptions(array('strict' => true));
		Zend_Session::start();
	}
	
	protected function _initView()
	{
		$view = new Zend_View();
		
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
		$this->bootstrap('request');
		$this->bootstrap('view');
		$this->_view = $this->getResource('view');
		
		$this->_view->headLink()->appendStylesheet(
				$this->view->baseUrl('css/global.css'));
	}
	
	protected function _initRequest()
	{
		$this->bootstrap('FrontController');
		$front = $this->getResource('FrontController');
		
		$request = new Zend_Controller_Request_Http();
		$request->setBaseUrl('/whdsched/public/');
		$front->setRequest($request);
		
		return $request;
	}
	
	protected function _initAuth()
	{
		$this->bootstrap('session');
		$this->bootstrap('request');
		$request = $this->getResource('request');
		
		$authOptions = $this->getOption('auth');
		switch ($authOptions['type'])
		{
		case 'digest':
			Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('whdsched'));
			$adapter =  new Zend_Auth_Adapter_Http($authOptions['options']);
			$resolver = new Zend_Auth_Adapter_Http_Resolver_File($authOptions['file']);
			$adapter->setDigestResolver($resolver);
			
			// All pages require valid log in
			$plugin = new AuthDispatchPlugin($adapter);
			Zend_Controller_Front::getInstance()->registerPlugin($plugin);
			break;
		case 'ldap':
			// Fallthrough
		default:
			$auth = null; // Not implemented
			break; 
		}
		
		return $adapter;
	}
}
