<?php

require_once 'Zend/Loader/Autoloader.php';

require_once 'AuthDispatchPlugin.php';
require_once 'DevAuthAdapter.php';
require_once 'ServerAuthAdapter.php';

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initAutoloader()
	{
		$loader = Zend_Loader_Autoloader::getInstance();
		$loader->registerNamespace('Whdsched');
		$loader->registerNamespace('Noumenal');
	}
	
	// TODO: Christ this got ugly. Clean it up
	protected function _initConfig()
	{
		$config = $this->getOptions();
		Zend_Registry::set('config', $config);
		
		// Number of weeks in a normal term
		define('WEEKS_PER_TERM', 11);
		
		// Preferred consultants must accept the 
		// shift within 48 hours of the shift
		define('TIMEOUT_DEFAULT', 48);
		
		// Preferred consultants for special shifts must
		// accept the shift within 1 week of the shift
		define('TIMEOUT_SPECIAL', 168);
		
		if (isset($config['debug']))
		{
			if (isset($config['debug']['auth']))
			{
				define('DEBUG_AUTH', $config['debug']['auth']);
			}
			else
			{
				define('DEBUG_AUTH', false);
			}
			
			if (isset($config['debug']['db']))
			{
				if (isset($config['debug']['db']['consultant']))
				{
					define('DEBUG_DB_CONSULTANT', $config['debug']['db']['consultant']);
				}
				else
				{
					define('DEBUG_DB_CONSULTANT', false);
				}
				
				if (isset($config['debug']['db']['term']))
				{
					define('DEBUG_DB_TERM', $config['debug']['db']['term']);
				}
				else
				{
					define('DEBUG_DB_TERM', false);
				}
			}
			else
			{
				define('DEBUG_DB_CONSULTANT', false);
				define('DEBUG_DB_TERM', false);
			}
		}
		else
		{
			define('DEBUG_AUTH', false);
			define('DEBUG_DB_CONSULTANT', false);
			define('DEBUG_DB_TERM', false);
		}
	}
	
	protected function _initMail()
	{
		$options = $this->getOption('mail');
		
		if ($options['transport'] == 'smtp')
		{
			$transport = new Zend_Mail_Transport_Smtp(
					$options['smtp']['server'], array(
						'port'     => $options['smtp']['port'],
						'ssl'      => $options['smtp']['ssl'],
						'auth'     => $options['smtp']['auth'],
						'username' => $options['smtp']['username'],
						'password' => $options['smtp']['password'],
			));
		}
		else
		{
			$transport = new Zend_Mail_Transport_Sendmail();
		}
		
		Zend_Mail::setDefaultTransport($transport);
		
		Zend_Mail::setDefaultFrom(
				$options['sender']['address'],
				$options['sender']['name']);
		
		Zend_Mail::setDefaultReplyTo(
				$options['replyto']['address'],
				$options['replyto']['name']);
	}
	
	protected function _initSession()
	{
		Zend_Session::setOptions(array('strict' => true));
		Zend_Session::start();
	}
	
	protected function _initView()
	{
		$view = new Zend_View();
		$view->addHelperPath('Noumenal/View/Helper','Noumenal_View_Helper');
		
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
		
		$this->_view->headLink()->appendStylesheet(
				$this->view->baseUrl('css/tinydropdown.css'));
	}
	
	protected function _initScript()
	{
		$this->bootstrap('request');
		$this->bootstrap('view');
		$this->_view = $this->getResource('view');
		
		$this->_view->headScript()->appendFile(
				$this->view->baseUrl('js/jquery-1.7.1.min.js'));
		
		$this->_view->headScript()->appendFile(
				$this->view->baseUrl('js/tinydropdown.js'));
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
		$this->bootstrap('log');
		$this->bootstrap('session');
		
		$this->bootstrap('request');
		$request = $this->getResource('request');
		
		Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session('whdsched'));
		
		$authOptions = $this->getOption('auth');
		switch ($authOptions['type'])
		{
		case 'server':
			$adapter = new ServerAuthAdapter($request->getServer('REMOTE_USER'));
			break;
		case 'dev':
			$adapter = new DevAuthAdapter($authOptions['username']);
			break;
		case 'digest':
			$adapter = new Zend_Auth_Adapter_Http($authOptions['options']);
			$resolver = new Zend_Auth_Adapter_Http_Resolver_File($authOptions['file']);
			$adapter->setDigestResolver($resolver);
			break;
		default:
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
		if (!Zend_Registry::isRegistered('log'))
		{
			$this->bootstrap('db');
			$db = $this->getResource('db');
			$options = $this->getOptions();
			$dbOptions = $options['resources']['db'];
			$prefix = $dbOptions['params']['prefix'];
			
			$columnMap = array(
				'log_time'   => 'timestamp',
				'type'       => 'type',
				'message'    => 'message',
			);
			
			$log = new Zend_Log(
					new Zend_Log_Writer_Db($db, $prefix . 'logs', $columnMap));
			
			$log->setEventItem('type', null);
			
			
			Zend_Registry::set('log', $log);
		}
	}
}
