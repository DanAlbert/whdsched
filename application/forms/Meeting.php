<?php

class Application_Form_Meeting extends Zend_Form
{

    public function init()
    {
        $termMapper = new Application_Model_TermMapper();
		
		$this->setMethod('post');
		
		$terms = $this->createElement('select', 'term');
		$terms->setRequired(true);
		$terms->setLabel('Term');
		foreach ($termMapper->fetchAll() as $t)
		{
			$terms->addMultiOption($t->getId(), $t->getName());
		}
		
		$this->addElement($terms);
		
		$this->addElement('text', 'startTime', array(
			'label'	  => 'Start Time (HH:mm)',
			'required'   => true,
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(5, 5),
				),
			)
		));
		
		$this->addElement('text', 'endTime', array(
			'label'	  => 'End Time (HH:mm)',
			'required'   => true,
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(5, 5),
				),
			)
		));
		
		$day = $this->createElement('select', 'day');
		$day->setRequired(true);
		$day->setLabel('Day');
		$day->addMultiOptions(Application_Model_Meeting::$VALID_DAYS);
		$this->addElement($day);
		
		$this->addElement('text', 'location', array(
			'label'	  => 'Location',
			'required'   => true,
		));
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }
}
