<?php

class Application_Model_TermMapper
{
	protected $_dbTable;
	
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
			$this->setDbTable('Application_Model_DbTable_Terms');
		}
		
		return $this->_dbTable;
	}
	
	public function save(Application_Model_Term $term)
	{
		$data = array(
			'term'	   => $term->getTerm(),
			'year'	   => $term->getYear(),
			'start_date' => $term->getStartDate(),
			'end_date'   => $term->getEndDate(),
		);
		
		$id = $term->getId();
		if ($id == null)
		{
			$data['id'] = $this->makeTermId($term);
			return $this->getDbTable()->insert($data);
		}
		else
		{
			return $this->getDbTable()->update($data, array('id = ?' => $id));
		}
		
		return true;
	}
	
	public function delete(Application_Model_Term $term)
	{
		$this->getDbTable()->delete(array('id = ?' => $term->getId()));
	}
	
	public function find($id)
	{
		$result = $this->getDbTable()->find($id);
		if (count($result) == 0)
		{
			return null;
		}
		
		$row = $result->current();
		
		$term = new Application_Model_Term();
		$term->setId($id);
		$term->setTerm($row->term);
		$term->setYear($row->year);
		$term->setStartDate($row->start_date);
		$term->setEndDate($row->end_date);
		
		return $term;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$terms = array();
		
		foreach ($resultSet as $row)
		{
			$term = new Application_Model_Term();
			$term->setId($row->id);
			$term->setTerm($row->term);
			$term->setYear($row->year);
			$term->setStartDate($row->start_date);
			$term->setEndDate($row->end_date);
			
			$terms[] = $term;
		}
		
		return $terms;
	}
	
	public function fetchAllByYear($year)
	{
		$resultSet = $this->getDbTable()->fetchAll(array('year' => $year));
		$terms = array();
		
		foreach ($resultSet as $row)
		{
			$term = new Application_Model_Term();
			$term->setId($row->id);
			$term->setTerm($row->term);
			$term->setYear($row->year);
			$term->setStartDate($row->start_date);
			$term->setEndDate($row->end_date);
			
			$terms[] = $term;
		}
		
		return $terms;
	}
	
	public function makeTermId(Application_Model_Term $term)
	{
		switch ($term->getTerm())
		{
		case 'Summer':
			$t = '00';
			$y = '' . ($term->getYear() + 1);
			break;
		case 'Fall':
			$t = '01';
			$y = '' . ($term->getYear() + 1);
			break;
		case 'Winter':
			$t = '00';
			$y = '' . $term->getYear();
			break;
		case 'Spring':
			$t = '00';
			$y = '' . $term->getYear();
			break;
		}
		
		return $t . $y;
	}
}

