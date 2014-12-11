<?php 
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class ConsultasubForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('consultasub');

        // The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));

        // Add the user fieldset, and set it as the base fieldset

        $this->add(array(
              'name' => 'security',
              'type' => 'Zend\Form\Element\Csrf'
            ));
         
            $this->add(array(
                 'name' => 'env',
                 'attributes' => array(
                     'type' => 'submit',
                     'value' => 'Guardar',
                     'class' => 'btn btn-primary',
                 ),
             ));
            $this->add(array(
                 'name' => 'res',
                 'attributes' => array(
                     'type' => 'reset',
                     'value' => 'Reset',
                     'class' => 'btn btn-default',
                 ),
             ));
            $this->add(array(
                'type' => 'Zend\Form\Element\Hidden',
                'name' => 'IDSUB',
                'attributes' => array(
                    'id' => 'id_consultasub',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'FC',
                'options' => array(
                    'label' => 'FC :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cFC',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'TA',
                'options' => array(
                    'label' => 'TA :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cTA',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'FR',
                'options' => array(
                    'label' => 'FR :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cFR',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'PS',
                'options' => array(
                    'label' => 'PS :',
                ),
                'attributes' => array( 
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cPS',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'T',
                'options' => array(
                    'label' => 'T :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cT',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'ALTURA',
                'options' => array(
                    'label' => 'Alt. :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cAlt',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'PESO',
                'options' => array(
                    'label' => 'Peso :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cPeso',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'IMC',
                'options' => array(
                    'label' => 'IMC :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control signosvitales',
                    'id' => 'cImc',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'SUBJETIVO',
                'options' => array(
                    'label' => 'Subjetivo :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'cSubjetivo',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'OBJETIVO',
                'options' => array(
                    'label' => 'Exploración Física :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'cObjetivo',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'ANALISIS',
                'options' => array(
                    'label' => 'Analisis :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'cAnalisis',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'PLAN',
                'options' => array(
                    'label' => 'Plan :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'cPlan',
                ),
            ));
            
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'DIAGNOSTICOS',
                'options' => array(
                    'label' => 'Diagnósticos :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'cDiagnosticos',
                ),
            ));

            $this->add(array(
                'type' => 'Zend\Form\Element\Hidden',
                'name' => 'DIAG_ARRAY',
                'attributes' => array(
                    'id' => 'diag_array',
                ),
            ));

            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'TRATAMIENTO',
                'options' => array(
                    'label' => 'Tratamiento :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'aTratamiento',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'HISTORIAL',
                'options' => array(
                    'label' => 'Historial :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'aHistorial',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'INTERROGATORIO',
                'options' => array(
                    'label' => 'Interrogatorio :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'aInterrogatorio',
                ),
            ));
            
         
            
        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}
 ?>