<?php 
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class ConsultaForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('consultas');

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
                'name' => 'ID',
                'attributes' => array(
                    'id' => 'id_consulta',
                ),
            ));
            $this->add(array(
                'type' => 'Zend\Form\Element\Hidden',
                'name' => 'PACIENTE',
                'attributes' => array(
                    'id' => 'cPaciente',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Text',
                'name'    => 'MOTIVO_CONS',
                'options' => array(
                    'label' => 'Motivo de consulta :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'cMotivo',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'INFO_MOTIVO',
                'options' => array(
                    'label' => 'Info. del motivo de consulta :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'aInfoMotivo',
                ),
            ));
            $this->add(array(
                'type'    => 'Zend\Form\Element\Textarea',
                'name'    => 'INFO_ADICIONAL',
                'options' => array(
                    'label' => 'Info. Adicional :',
                ),
                'attributes' => array(
                    'class' => 'Input form-control',
                    'id' => 'aInfoAdicional',
                ),
            ));
            
        // … add CSRF and submit elements …

        // Optionally set your validation group here
    }
}
 ?>