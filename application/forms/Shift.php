<?php

class Application_Form_Shift extends Zend_Form
{
	public function init()
	{
		$termMapper = new Application_Model_TermMapper();
		
		$this->setMethod('post');
		
		$this->addElement('text', 'startTime', array(
			'label'	  => 'Start Time',
			'required'   => true,
			'filters'	=> array('StringTrim', 'Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(4, 4),
				),
			)
		));
		
		$this->addElement('text', 'endTime', array(
			'label'	  => 'End Time',
			'required'   => true,
			'filters'	=> array('StringTrim', 'Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$days = $this->createElement('multiCheckbox', 'days');
		$days->setRequired(true);
		$days->setLabel('Days');
		$days->addMultiOptions(array(
			0 => 'Sunday',
			1  => 'Monday',
			2  => 'Tuesday',
			3 => 'Wednesday',
			4 => 'Thursday',
			5 => 'Friday',
			6 => 'Saturday',
		));
		
		$this->addElement($days);
		
		$location = $this->createElement('multiCheckbox', 'location');
		$location->setRequired(true);
		$location->setLabel('Location');
		$location->addMultiOptions(array(
			'WHD'  => 'Helpdesk',
			'Lab'  => 'Labs',
			'KEC'  => 'KEC 1130',
			'Owen' => 'Owen 237',
		));
		
		$this->addElement($location);
		
		$terms = $this->createElement('select', 'term');
		$terms->setRequired(true);
		$terms->setLabel('Term');
		foreach ($termMapper->fetchAll() as $t)
		{
			$terms->addMultiOption($t->getId(), $t->getName());
		}
		
		$this->addElement($terms);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
	}
}

