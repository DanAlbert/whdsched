<?php

/**
 * Simple authentication adapter which succeeds as long as the username is valid
 * 
 * @author Dan Albert
 */
class ServerAuthAdapter implements Zend_Auth_Adapter_Interface
{
	private $username;
	
	public function __construct($username)
	{
		$this->username = $username;
	}
	
	public function authenticate()
	{
		$consultantMapper = new Application_Model_ConsultantMapper();
		$identity = array('username' => $this->username, 'password' => '');
		
		if ($consultantMapper->findByEngr($this->username) !== null)
		{
			return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity);
		}
		else
		{
			return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $identity);
		}
	}
}
