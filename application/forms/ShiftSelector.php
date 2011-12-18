<?php

class Application_Form_ShiftSelector extends Zend_Form
{

	private $shifts;
	
	public function __construct(array $shifts)
	{
		$this->shifts = $shifts;	
		Zend_Form::__construct();

	} 

	

    public function init()
    {
		$shifts = $this->createElement('multiCheckbox', 'shifts');
		$shifts->setLabel('Shifts');
		
		foreach($this->shifts as $shift){
			$shifts->addMultiOption($shift->getId(), $shift->__toString());
		}		
		$this->addElement($shifts);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }


}

