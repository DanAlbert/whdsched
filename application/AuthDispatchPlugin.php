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

		$this->adapter->setRequest($this->_request);
		$this->adapter->setResponse($this->_response);
		$result = $auth->authenticate($this->adapter);

		if (!$result->isValid())
		{
			$this->_request->setControllerName('Index');
			$this->_request->setActionName('authenticate');
		}
		else
		{
			$consultantMapper = new Application_Model_ConsultantMapper();
			$identity = $result->getIdentity();
			$consultant = $consultantMapper->findByEngr($identity['username']);
			
			if ($consultant !== null)
			{
				Zend_Auth::getInstance()->getStorage()->write($consultant);
				assert(Zend_Auth::getInstance()->getIdentity() instanceof Application_Model_Consultant);
			}
			else
			{
				Zend_Auth::getInstance()->clearIdentity();
				$this->_request->setControllerName('Index');
				$this->_request->setActionName('authenticate');
			}
		}
	}
}
