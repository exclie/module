<?php 
namespace Application\Form;

use Doctrine\Common\Persistence\ObjectManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Form;


class PagorecepcionForm extends Form 
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        parent::__construct('pagos');
        $this->objectManager = $objectManager;
        $this->setHydrator(new DoctrineHydrator($objectManager));

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'ID',
            'attributes' => array(
                'id' => 'hIdPago',
            ),
        ));
     
        $this->add(array(
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'CANTIDAD',
            'options' => array(
                'label' => 'Importe :',
            ),
            'attributes' => array(
                'class' => 'Montos Input form-control',
                'id' => 'cMonto',
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
            'type'    => 'Zend\Form\Element\Text',
            'name'    => 'IVA',
            'options' => array(
                'label' => 'IVA (16%) :',
            ),
            'attributes' => array(
                'class' => 'Input form-control',
                'id' => 'cIVA',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'TIPOPAGO',
            'tabindex' =>2,
            'options' => array(
                    'label' => 'Forma de pago:',
                    'value_options' => $this->getTiposPagoData(),
            ),
            'attributes' => array(
                'class' => 'tipospago form-control dropdown',
                'id' => 'sTipopago',
            ),
        ));
   
    }
    public function getTiposPagoData() {
        $query = $this->objectManager->createQuery("SELECT p FROM CsnUser\Entity\Tipospago p WHERE p.ID < 6");
        $result = $query->getArrayResult();
        $tipospago = array();
        foreach($result as $res) {
            $tipospago[$res['ID']] = $res['TIPOPAGO'];
        }
        return $tipospago;
    }

}