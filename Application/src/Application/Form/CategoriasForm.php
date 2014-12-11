<?php 
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;


class CategoriasForm extends Form 
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('categorias');
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'ID',
            'attributes' => array(
                'id' => 'hEstudio',
            ),
        ));
         $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'MATERIALES_ARRAY',
            'attributes' => array(
                'id' => 'hMateriales',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'NOMBRECATEGORIA',
            'options' => array(
                'label' => 'Nombre del estudio:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cCategoria',
                'onblur' => 'verificar_campo(this)',
            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'COSTO',
            'options' => array(
                'label' => 'Costo del estudio:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cCosto',
                'onblur' => 'verificar_campo(this)',
            ),
        ));

        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'DEPENDENCIA',
            'options' => array(
                'label' => 'Costo para: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Dependencias',
                'property'       => 'NOMBREDEPENDENCIA',
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dDependencias',
            ),
        ));
        $this->add(array(
                     'type' => 'Zend\Form\Element\Checkbox',
                     'name' => 'ACTIVO',
                     'options' => array(
                             'use_hidden_element' => true,
                             'checked_value' => true,
                             'unchecked_value' => false
                     ),
                     'attributes' => array(
                        'id' => 'checkActivo',
                        'checked' => true,
                    ),
             ));
   
    }

}