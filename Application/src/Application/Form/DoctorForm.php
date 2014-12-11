<?php 
namespace Application\Form;

use CsnUser\Entity\Doctores;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;


class DoctorForm extends Form 
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('doctores');
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new Doctores());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'ID'
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'NOMBRE',
            'options' => array(
                'label' => 'Nombre del doctor:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cNombre',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'APELLIDO1',
            'options' => array(
                'label' => 'Apellido paterno:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cApellido1',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'APELLIDO2',
            'options' => array(
                'label' => 'Apellido materno:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cApellido2',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'ESPECIALIDAD',
            'options' => array(
                'label' => 'Especialidad del médico: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Especialidades',
                'property'       => 'ESPECIALIDAD',
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sEspecialidad',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'DEPENDENCIA',
            'options' => array(
                'label' => 'Dependencia: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Dependencias',
                'property'       => 'NOMBREDEPENDENCIA',
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sDependencia',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'EMAIL',
            'options' => array(
                'label' => 'Email:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cEmail',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'TELEFONO',
            'options' => array(
                'label' => 'Teléfono:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cTelefono',
            ),
        ));
        
    }

}