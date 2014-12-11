<?php 
namespace Application\Form;

use CsnUser\Entity\Estudios;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;


class EstudiosForm extends Form 
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('estudios');
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new Estudios());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'ID'
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Date',
            'name'    => 'FECHA',
            'options' => array(
                'label' => 'Fecha del estudio:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cFecha',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'DOCTOR',
            'tabindex' =>2,
            'options' => array(
                    'label' => 'Doctor encargado:',
                    'empty_option' => 'Seleccione un doctor:',
                    'value_options' => $this->getEncargadosData(),
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sDocencargado',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Hidden',
            'name'    => 'PACIENTE',
            'attributes' => array(
                'id' => 'cIdPaciente',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'DOCTORENV',
            'tabindex' =>2,
            'options' => array(
                    'label' => 'Doctor que le envía:',
                    'empty_option' => 'Seleccione un doctor:',
                    'value_options' => $this->getDoctoresData(),
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sDoctor',
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
            'type' => 'Zend\Form\Element\Select',
            'name' => 'DEPENDENCIA',
            'options' => array(
                    'label' => 'Dependencia:',
                    'value_options' => $this->getDependenciasData(),
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sDependencia',
            ),
        ));

         $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'TIPOS[]',
            'options' => array(
                    'label' => 'Estudio(s) a realizar:',
                    'value_options' => $this->getCategoriasData(1),
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sTipos',
                'multiple' => true,
            ),
        ));
        
        $this->add(array(
                     'type' => 'Zend\Form\Element\Checkbox',
                     'name' => 'REVISION',
                     'options' => array(
                             'use_hidden_element' => true,
                             'checked_value' => true,
                             'unchecked_value' => false
                     ),
                     'attributes' => array(
                        'id' => 'checkRevision',
                    ),
             ));
        
    }

    public function getCategoriasData($id = '') {
        if($id == '') {
            $where = 'WHERE p.DEPENDENCIA = 1';
        } else {
            $where = 'WHERE p.DEPENDENCIA = '.$id;
        }
        $query = $this->objectManager->createQuery("SELECT p FROM CsnUser\Entity\Tiposestudio p $where AND p.ACTIVO = 1");  
        $result = $query->getArrayResult();
        $categorias = array();
        foreach($result as $res) {
            $costo = $res['COSTO']*1.16;
            $categorias[$res['ID']] = $res['NOMBRECATEGORIA'].' ($ '.number_format($costo,2).')';
        }
        return $categorias;
    }

    public function getPacientesData() {
        $query = $this->objectManager->createQuery("SELECT p FROM CsnUser\Entity\Pacientes p");
        $result = $query->getArrayResult();
        $pacientes = array();
        foreach($result as $res) {
            $pacientes[$res['ID']] = $res['NOMBRE'].' '.$res['APELLIDO_PATERNO'].' '.$res['APELLIDO_MATERNO'].' ['.date_format($res['FECHA_NACIMIENTO'],'d/m/Y').']';
        }
        return $pacientes;
    }

    public function getEncargadosData() {
        $query = $this->objectManager->createQuery("SELECT u FROM CsnUser\Entity\User u WHERE u.role = 4");
        $result = $query->getArrayResult();
        $doctores = array();
        foreach($result as $res) {
            $doctores[$res['id']] = $res['firstName'].' '.$res['lastName'];
        }
        return $doctores;
    }

    public function getDependenciasData()
    {
        $query = $this->objectManager->createQuery("SELECT d FROM CsnUser\Entity\Dependencias d");
        $result = $query->getArrayResult();
        $dependencias = array();
        foreach($result as $res) {
            $dependencias[$res['ID']] = $res['NOMBREDEPENDENCIA'];
        }
        return $dependencias;
    }

    public function getDoctoresData() {
        $query = $this->objectManager->createQuery("SELECT d.ID,d.NOMBRE,d.APELLIDO1,d.APELLIDO2,e.ESPECIALIDAD FROM CsnUser\Entity\Doctores d
            LEFT JOIN d.ESPECIALIDAD e");
        $result = $query->getArrayResult();
        $doctores = array();
        foreach($result as $res) {
            $doctores[$res['ID']] = $res['NOMBRE'].' '.$res['APELLIDO1'].' '.$res['APELLIDO2'].' ['.$res['ESPECIALIDAD'].']';
        }
        return $doctores;
    }
}