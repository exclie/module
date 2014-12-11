<?php 
namespace Application\Form;

use CsnUser\Entity\Pacientes;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;


class PacientesForm extends Form implements InputFilterProviderInterface
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('pacientes');
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager))
             ->setObject(new Pacientes());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'ID_PACIENTE'
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'IdEstudio'
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'CURP',
            'options' => array(
                'label' => 'CURP:',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cCurp',
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
                'id' => 'cRfc',
                'onblur' => '',
            ),
        ));

        $this->add(array(
             'name' => 'env',
             'attributes' => array(
                 'type' => 'submit',
                 'value' => 'Enviar',
                 'class' => 'btn btn-primary',
                 'id' => 'bEnviar',
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
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'NOMBRE',
            'options' => array(
                'label' => 'Nombre',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cNombre',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'APELLIDO_PATERNO',
            'options' => array(
                'label' => 'Apellido Paterno'
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cAppa',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'APELLIDO_MATERNO',
            'options' => array(
                'label' => 'Apellido Materno'
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cApma',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'OCUPACION',
            'options' => array(
                'label' => 'Ocupación:'
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cApma',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'SEXO',
             'options' => array(
                     'label' => 'Sexo:',
                     'disable_inarray_validator' => true,
                     'value_options' => array(
                                     '1' => 'Femenino',
                                     '2' => 'Masculino',
                                 ),
             ),
             'attributes' => array(
                'id' => 'sSexo',
                'class' => 'form-control',
                'onchange' => 'ocultar()'
            ),
        ));
        $this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'EMBARAZO',
             'options' => array(
                     'label' => 'Embarazo:',
                     'disable_inarray_validator' => true,
                     'value_options' => array(
                                     '1' => 'No',
                                     '2' => 'Si',
                                 ),
             ),
             'attributes' => array(
                'id' => 'sEmbarazo',
                'class' => 'form-control'

            ),
        ));

        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'CALLE',
            'options' => array(
                'label' => 'Calle: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cCalle',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'NUMERO_EXT',
            'options' => array(
                'label' => 'Núm. exterior: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cNumex',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'NUMERO_INT',
            'options' => array(
                'label' => 'Núm. interior: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cNumin',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'CODIGO_POSTAL',
            'options' => array(
                'label' => 'Código Postal: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cCodigo',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'COLONIA',
            'options' => array(
                'label' => 'Colonia: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cColonia',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'EMAIL',
            'options' => array(
                'label' => 'Email: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cEmail',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Date',
            'name'    => 'FECHA_NACIMIENTO',
            'options' => array(
                'label' => 'Fecha de nacimiento: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cFechaNacimiento',
                'onblur' => 'verificar_campo(this)',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'TELEFONO_1',
            'options' => array(
                'label' => 'Teléfono: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cTelefono',
            ),
        ));
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'AFILIACION',
            'options' => array(
                'label' => '# Derechohabiente: '
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cAfiliacion',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'DISCAPACIDAD',
            'options' => array(
                'label' => 'Discapacidad: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Discapacidades',
                'property'       => 'NOMBRE',
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dDiscapacidad',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'TIPO_SANGUINEO',
            'options' => array(
                'label' => 'Tipo Sanguíneo: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Tipossanguineos',
                'property'       => 'NOMBRE',
                'is_method'      => false,
            ),
            'attributes' => array(
                'value' => 9,
                'class' => 'form-control dropdown',
                'id' => 'dTipossanguineos',
            ),
        ));
        $this->add(array(
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
                'id' => 'dEstados',
                'onchange' => 'municipios(this.value)',
                'data-placeholder' => 'Seleccione una entidad',
            ),
        ));
        $this->add(array(
             'type' => 'Zend\Form\Element\Select',
             'name' => 'MUNICIPIO',
             'options' => array(
                     'label' => 'Ciudad o municipio: ',
                     'disable_inarray_validator' => true,
             ),
             'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dMunicipios',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'VIVIENDA',
            'options' => array(
                'label' => 'Vivienda: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Viviendas',
                'property'       => 'NOMBRE',
                'is_method'      => false,
                
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dVivienda',
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'CLIENTE',
            'tabindex' =>2,
            'options' => array(
                    'label' => 'Persona fiscal:',
                    'value_options' => $this->getClientesData(),
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'sCliente',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'GRUPO_ETNICO',
            'options' => array(
                'label' => 'Grupo étnico: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Gruposetnicos',
                'property'       => 'NOMBRE',
                'is_method'      => false,
                
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dGrupoetnico',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'NIVEL_SOCIOECONOMICO',
            'options' => array(
                'label' => 'Nivel Eco. : ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Nivelessocioeconomicos',
                'property'       => 'NOMBRE',
                'is_method'      => false,
                
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dNivel',
            ),
        ));
        $this->add(array(
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'name' => 'RELIGION',
            'options' => array(
                'label' => 'Religion: ',
                'object_manager' => $objectManager,
                'target_class'   => 'CsnUser\Entity\Religiones',
                'property'       => 'NOMBRE',
                'is_method'      => false,
                
            ),
            'attributes' => array(
                'class' => 'form-control dropdown',
                'id' => 'dReligion',
            ),
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'ID_PACIENTE' => array(
                'required' => false
            ),

            'NOMBRE' => array(
                'required' => true,
                'filters'   => array(
                    array(  'name'      =>  'StripTags'),
                    array(  'name'      =>  'StringTrim'),
                ),
            ),
            'APELLIDO_PATERNO' => array(
                'required' => true,
                'filters'   => array(
                    array(  'name'      =>  'StripTags'),
                    array(  'name'      =>  'StringTrim'),
                ),
            ),
            'CODIGO_POSTAL' => array(
                'required' => false,
                'filters'   => array(
                    array(  'name'      =>  'StripTags'),
                    array(  'name'      =>  'StringTrim'),
                ),
                'validators' => array(
                    array('name' => 'Digits'),
                ),
            ),
            'CURP' => array(
                            'required'  => false,
                            'filters'   => array(
                                array(  'name'      =>  'StripTags'),
                                array(  'name'      =>  'StringTrim'),
                            ),
                            'validators'    => array(
                                array(
                                    'name'  => 'StringLength',
                                    'options' => array(
                                        'encoding'  =>  'UTF-8',
                                        'min'       => 1,
                                        'max'       => 100,
                                    ),
                                ),
                                array(
                                'name' => 'Regex',
                                'options' => array(
                                    'pattern' => '^[A-Z]{1}[AEIOU]{1}[A-Z]{2}[0-9]{2}(0[1-9]|1[0-2])(0[1-9]|1[0-9]|2[0-9]|3[0-1])[HM]{1}(AS|BC|BS|CC|CS|CH|CL|CM|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[0-9A-Z]{1}[0-9]{1}^',
                                    'messages' => array(
                                        'regexNotMatch' => "Curp inválida.",
                                    ),
                                ),
                            )),
            ),
            
        );
    }
    public function getClientesData() {
        $query = $this->objectManager->createQuery("SELECT c FROM CsnUser\Entity\Receptor c");
        $result = $query->getArrayResult();
        $clientes = array();
        foreach($result as $res) {
            $clientes[$res['ID']] = $res['NOMBRE'];
        }
        return $clientes;
    }
}