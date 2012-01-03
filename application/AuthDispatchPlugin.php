<?php

/**
 * AuthDispatchPlugin
 *
 * @author Dan Albert
 */
class AuthDispatchPlugin extends Zend_Controller_Plugin_Abstract
{
	protected $adapter;

	public function __construct(Zend_Auth_Adapter_Interface $adapter)
	{
		$this->adapter = $adapter;
	}

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$auth = Zend_Auth::getInstance();
		
		// HTTP Basic and Digest require the request and response
		if (($this->adapter instanceof Zend_Auth_Adapter_Http) or
			($this->adapter instanceof Zend_Auth_Adapter_Digest))
		{
			$this->adapter->setRequest($this->_request);
			$this->adapter->setResponse($this->_response);
		}
		
		if (DEBUG)
		{
			Zend_Registry::get('log')->debug('Authenticating');
		}
		
		$result = $auth->authenticate($this->adapter);

		if (!$result->isValid())
		{
			if (DEBUG)
			{
				Zend_Registry::get('log')->debug('Invalid credentials');
			}
			
			$this->_request->setControllerName('Index');
			$this->_request->setActionName('authenticate');
		}
		else
		{
			if (DEBUG)
			{
				Zend_Registry::get('log')->debug('Valid credentials');
				Zend_Registry::get('log')->debug('Verifying username');
			}
			
			$consultantMapper = new Application_Model_ConsultantMapper();
			$identity = $result->getIdentity();
			$consultant = $consultantMapper->findByEngr($identity['username']);
			
			if ($consultant !== null)
			{
				if (DEBUG)
				{
					Zend_Registry::get('log')->debug('Valid user');
				}
				
				Zend_Auth::getInstance()->getStorage()->write($consultant);
				assert(Zend_Auth::getInstance()->getIdentity() instanceof Application_Model_Consultant);
			}
			else
			{
				if (DEBUG)
				{
					Zend_Registry::get('log')->debug('Invalid user');
				}
				
				Zend_Auth::getInstance()->clearIdentity();
				$this->_request->setControllerName('Index');
				$this->_request->setActionName('authenticate');
			}
		}
	}
}
