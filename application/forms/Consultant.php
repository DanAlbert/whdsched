<?php

class Application_Form_Consultant extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
		
		$this->addElement('text', 'firstName', array(
			'label'      => 'First Name',
			'required'   => true,
			'filters'    => array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$this->addElement('text', 'lastName', array(
			'label'      => 'Last Name',
			'required'   => true,
			'filters'    => array('StringTrim'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 40),
				),
			)
		));
		
		$this->addElement('text', 'engr', array(
			'label'      => 'Engineering Username',
			'required'   => true,
			'filters'    => array('StringTrim', 'StringToLower'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(0, 8),
				),
				array(
					'validator' => 'Alnum',
				)
			)
		));
		
		$this->addElement('text', 'phone', array(
			'label'      => 'Phone Number',
			'required'   => true,
			'filters'    => array('Digits'),
			'validators' => array(
				array(
					'validator' => 'StringLength',
					'options'   => array(10, 10),
				)
			)
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

