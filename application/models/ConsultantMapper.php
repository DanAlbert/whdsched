<?php

class Application_Model_ConsultantMapper
{
	protected $_dbTable;
	protected $_sequence = true; // Primary key autoincrements
	
	public function setDbTable($dbTable)
	{
		// If the DbTable class name was passed as a string
		if (is_string($dbTable))
		{
			// Instantiate the class specified by the string
			$dbTable = new $dbTable();
		}
		
		// $dbTable should now be an instance of table abstract
		if (!$dbTable instanceof Zend_Db_Table_Abstract)
		{
			throw new Exception('Invalid table data gateway provided');
		}
		
		$this->_dbTable = $dbTable;
		
		return $this;
	}
	
	public function getDbTable()
	{
		if ($this->_dbTable === null)
		{
			$this->setDbTable('Application_Model_DbTable_Consultants');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Consultant $consultant)
	{
		$data = array(
			'first_name'   => $consultant->getFirstName(),
			'last_name'    => $consultant->getLastName(),
			'engr'         => $consultant->getEngr(),
			'phone'        => $consultant->getPhone(),
			'recv_nightly' => $consultant->getReceiveNightly(),
			'recv_instant' => $consultant->getReceiveInstant(),
			'admin'        => $consultant->isAdmin(),
		);
		
		$id = $consultant->getId();
		if ($id == null)
		{
			unset($data['id']);
			return $this->getDbTable()->insert($data);
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_Consultant $consultant)
	{
		$this->getDbTable()->delete(array('id = ?' => $consultant->getId()));
	}
	
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$consultant = new Application_Model_Consultant();
		$consultant->setId($id);
		$consultant->setFirstName($row->first_name);
		$consultant->setLastName($row->last_name);
		$consultant->setEngr($row->engr);
		$consultant->setPhone($row->phone);
		$consultant->setReceiveNightly($row->recv_nightly);
		$consultant->setReceiveInstant($row->recv_instant);
		$consultant->setAdmin($row->admin);
		
		return $consultant;
	}
	
	public function findByEngr($engr)
	{
		$result = $this->getDbTable()->fetchAll(array('engr = ?' => $engr));
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$consultant = new Application_Model_Consultant();
		$consultant->setId($row->id);
		$consultant->setFirstName($row->first_name);
		$consultant->setLastName($row->last_name);
		$consultant->setEngr($row->engr);
		$consultant->setPhone($row->phone);
		$consultant->setReceiveNightly($row->recv_nightly);
		$consultant->setReceiveInstant($row->recv_instant);
		$consultant->setAdmin($row->admin);
		
		return $consultant;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$consultants = array();
		
		foreach ($resultSet as $row)
		{
			$consultant = new Application_Model_Consultant();
			$consultant->setId($row->id);
			$consultant->setFirstName($row->first_name);
			$consultant->setLastName($row->last_name);
			$consultant->setEngr($row->engr);
			$consultant->setPhone($row->phone);
			$consultant->setReceiveNightly($row->recv_nightly);
			$consultant->setReceiveInstant($row->recv_instant);
			$consultant->setAdmin($row->admin);
			
			$consultants[$consultant->getId()] = $consultant;
		}
		
		return $consultants;
	}
}
