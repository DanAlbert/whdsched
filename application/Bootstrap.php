<?php

require_once 'AuthDispatchPlugin.php';
require_once 'DevAuthAdapter.php';

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
		$siteOptions = $this->getOption('site');
		$request->setBaseUrl($siteOptions['root']);
		$front->setRequest($request);
		
		return $request;
	}
	
	protected function _initAuth()
	{
		$this->bootstrap('session');
		
		$this->bootstrap('request');
		$request = $this->getResource('request');
		
		Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('whdsched'));
		
		$authOptions = $this->getOption('auth');
		switch ($authOptions['type'])
		{
		case 'dev':
			$adapter = new DevAuthAdapter($authOptions['username']);
			break;
		case 'digest':
			$adapter = new Zend_Auth_Adapter_Http($authOptions['options']);
			$resolver = new Zend_Auth_Adapter_Http_Resolver_File($authOptions['file']);
			$adapter->setDigestResolver($resolver);
			break;
		case 'ldap':
			// Fallthrough
		default:
			$adapter = null; // Not implemented
			break; 
		}
		
		// This condition will be removed in a future release, as we will either have
		// an adapter, or throw an error
		if ($adapter !== null)
		{
			// All pages require valid log in
			$plugin = new AuthDispatchPlugin($adapter);
			Zend_Controller_Front::getInstance()->registerPlugin($plugin);
		}
		
		return $adapter;
	}
	
	protected function _initLog()
	{
		return new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
	}
}
