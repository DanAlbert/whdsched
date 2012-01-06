<?php

class Application_Form_EditConsultant extends Zend_Form
{

    public function init()
    {
		$this->setMethod('post');
		
		$this->addElement('text', 'firstName', array(
			'label'	  => 'First Name',
			'required'   => true,
			'filters'	=> array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$this->addElement('text', 'lastName', array(
			'label'	  => 'Last Name',
			'required'   => true,
			'filters'	=> array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$this->addElement('text', 'phone', array(
			'label'	  => 'Phone Number',
			'required'   => true,
			'filters'	=> array('Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(10, 10),
				)
			)
		));
		
		$nightly = $this->createElement('radio', 'nightly');
		$nightly->setLabel('Receive nightly temp shift emails');
		$nightly->addMultiOptions(array(
			'yes' => 'Yes',
			'no'  => 'No',
		));
		$nightly->setValue('yes');
		
		$this->addElement($nightly);
		
		$instant = $this->createElement('radio', 'instant');
		$instant->setLabel('Receive instant temp shift emails');
		$instant->addMultiOptions(array(
			'yes' => 'Yes',
			'no'  => 'No',
		));
		$instant->setValue('no');
		
		$this->addElement($instant);
		
		$this->addElement('submit', 'submit', array(
			'ignore' => true,
			'label'  => 'Save Changes',
		));
		
		$this->addElement('hash', 'csrf', array(
			'ignore' => true,
		));
    }


}

