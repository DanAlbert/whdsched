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
					'options'   => array(4, 4),
				),
			)
		));
		
		$this->addElement('text', 'date', array(
			'label'	  => 'Date (MM/DD/YYYY)',
			'required'   => true,
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(10, 10),
				),
			)
		));
		
		$location = $this->createElement('select', 'location');
		$location->setRequired(true);
		$location->setLabel('Location');
		$location->addMultiOptions(array(
			'WHD'      => 'Helpdesk',
			'Lab'      => 'Labs',
			'KEC'      => 'KEC 1130',
			'Owen'     => 'Owen 237',
			'WHD-Temp' => 'Helpdesk Extra',
		));
		
		$this->addElement($location);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
	}
}

