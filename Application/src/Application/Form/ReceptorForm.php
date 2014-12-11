<?php 
namespace Application\Form;

use CsnUser\Entity\Receptor;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;

class ReceptorForm extends Form
{
    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('receptor');

        // The form will hydrate an object of type "BlogPost"
        $this->setHydrator(new DoctrineHydrator($objectManager));
         
            $this->add(array(
                 'name' => 'env',
                 'attributes' => array(
                     'type' => 'submit',
                     'value' => 'Guardar',
                     'class' => 'btn btn-primary',
                     'id' => 'cBotG',
                 ),
             ));
         
         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'RFC',
               'options' => array(
                   'label' => 'RFC:',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cRfcR',
                   'onblur' => 'verificar_campo2(this)',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'NOMBRE',
               'options' => array(
                   'label' => 'Nombre fiscal:',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cNombreR',
                   'onblur' => 'verificar_campo2(this)',
               ),
           ));

         $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'PAIS',
            'options' => array(
                'label' => 'País: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Paises',
                'property'       => 'NOMBRE',
                'is_method'      => false,
                
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sPaises',
                'data-placeholder' => 'Seleccione un país',
            ),
        ]);
         $this->add([
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'ESTADO',
            'options' => array(
                'label' => 'Entidad: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Estados',
                'property'       => 'NOMBRE',
                'is_method'      => false,
                
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sEstados',
                'onchange' => 'municipios2(this.value)',
                'data-placeholder' => 'Seleccione una entidad',
            ),
        ]);
        $this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'MUNICIPIO',
             'options' => array(
                     'label' => 'Ciudad o municipio: ',
                     'disable_inarray_validator' => true,
             ),
             'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sMunicipios',
            ),
        ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'COLONIA',
               'options' => array(
                   'label' => 'Colonia',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cColoniaR',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'CALLE',
               'options' => array(
                   'label' => 'Calle',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cCalleR',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'CP',
               'options' => array(
                   'label' => 'Codigo Postal',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cCodPosE',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'NUM_INT',
               'options' => array(
                   'label' => 'Numero interno',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cNumInR',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'NUM_EXT',
               'options' => array(
                   'label' => 'Numero externo',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cNumExR',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'EMAIL',
               'options' => array(
                   'label' => 'Correo electronico',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cEmail',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'TELEFONO',
               'options' => array(
                   'label' => 'Telefono:',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cTelF',
               ),
           ));

         $this->add(array(   
               'type'    => 'Zend\Form\Element\Text',
               'name'    => 'TELMOVIL',
               'options' => array(
                   'label' => 'Telefono móvil:',
               ),
               'attributes' => array(
                   'class' => 'Input form-control',
                   'id' => 'cTelM',
               ),
           ));

    }
}
 ?>