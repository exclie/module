<?php 
namespace Application\Form;

use CsnUser\Entity\Agendas;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class AgendasFS extends Fieldset implements InputFilterProviderInterface
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('agendas');

        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new Agendas());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id',
            'attributes' => array(
                'id' => 'id_evento',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'doctor',
            'attributes' => array(
                'id' => 'cDoctor',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'paciente',
            'attributes' => array(
                'id' => 'cPaciente',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Hidden',
            'name'    => 'start',
            'attributes' => array(
                'id' => 'cInicio',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Hidden',
            'name'    => 'end',
            'attributes' => array(
                'id' => 'cFin',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'title',
            'options' => array(
                'label' => 'Paciente :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cTitle',
            ),
        ));
        
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'edad',
            'options' => array(
                'label' => 'Edad :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cEdad',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'telefono1',
            'options' => array(
                'label' => 'Teléfono 1 :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cTel1',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'telefono2',
            'options' => array(
                'label' => 'Teléfono 2 :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cTel2',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Textarea',
            'name'    => 'descripcion',
            'options' => array(
                'label' => 'Descripcion :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'aDesc',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'refdoctor',
            'options' => array(
                'label' => 'Doctor que le envía :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cDoc',
            ),
        ));
        $this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'dependencia',
             'options' => array(
                     'label' => 'Dependencia:',
                     'disable_inarray_validator' => true,
                     'value_options' => array(
                                     '1' => 'Pensiones',
                                     '2' => 'Cima',
                                     '3' => 'IMSS',
                                     '4' => 'ISSSTE',
                                     '5' => 'Seguro popular',
                                     '6' => 'Otra'
                                 ),
             ),
             'attributes' => array(
                'id' => 'sDependencia',
                'class' => 'form-control'
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'estudios[]',
            'options' => array(
                'label' => 'Estudio(s) a realizar: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Tiposestudio',
                'property'       => 'NOMBRECATEGORIA',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'sCategorias',
                'multiple' => true,
            ),
        ));
        $this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'color',
             'options' => array(
                     'label' => '',
                     'disable_inarray_validator' => true,
                     'value_options' => array(
                                     '#5484ed' => 'Azul',
                                     '#a4bdfc' => 'Azul ligero',
                                     '#7bd148' => 'Verde',
                                     '#46d6db' => 'Turquesa',
                                     '#7ae7bf' => 'Verde ligero',
                                     '#51b749' => 'Verde oscuro',
                                     '#fbd75b' => 'Amarillo',
                                     '#ffb878' => 'Naranja',
                                     '#ff887c' => 'Rojo',
                                     '#dc2127' => 'Rojo oscuro',
                                     '#dbadff' => 'Morado',
                                     '#e1e1e1' => 'Gris',
                                 ),
             ),
             'attributes' => array(
                'id' => 'sColores',
                'style' => 'margin-top:10px',
            ),
        ));
        
    }

    public function getInputFilterSpecification()
    {
        return array(
            'title' => array(
                'required' => false
            ),
           
            
        );
    }
}