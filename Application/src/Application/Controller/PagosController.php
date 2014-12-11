<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Validator\Identical as IdenticalValidator;
use Zend\Form\FormInterface;

use Application\Form\PagorecepcionForm;

use CsnUser\Entity\Pagos;
use CsnUser\Entity\Pagoscategorias;
use CsnUser\Entity\Pagostipos;
/**
 * Registration controller
 */
class PagosController extends AbstractActionController
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_objectManager;
    /**
     * Inventario Index Action
     *
     * Muestra la lista del inventario disponible
     *
     * @return Zend\View\Model\ViewModel
     */
    public function PagosAction()
    {
    	return new ViewModel();
    }
    public function PagorecepcionAction()
    {
      $idEstudio = $this->params()->fromRoute('id');
      $om =  $this->getObjectManager();
      $queryEstudio = $om->createQuery("SELECT e,t FROM CsnUser\Entity\Estudios e JOIN e.TIPOS t WHERE e.ID = $idEstudio");
      $estudio = $queryEstudio->getArrayResult();
      $form = new PagorecepcionForm($om);
      $this->layout('layout/vacio');
      $data = array('form' => $form,'estudio' => $estudio);
      return new ViewModel($data);
    }
    public function NuevopagorecepcionAction()
    {
      if($this->request->isPost()) {
        $objectManager = $this->getObjectManager();
        $pago = new Pagos();
        $pago->setESTUDIO($objectManager->find('CsnUser\Entity\Estudios',$this->request->getPost('idEstudio')));
        $pago->setCANTIDAD($this->request->getPost('total'));
        $pago->setSERVICIOAD($this->request->getPost('servicioad'));
        $pago->setMONTOSERV($this->request->getPost('montoserv'));
        $pago->setIVA($this->request->getPost('iva'));
        $pago->setIMPORTE($this->request->getPost('importe'));
        $pago->setCAMBIO($this->request->getPost('cambio'));
        $pago->setFECHA(new \DateTime());
        $pago->setUSUARIO($objectManager->find('CsnUser\Entity\User', $this->identity()->getId()));
        $objectManager->persist($pago);
        $objectManager->flush();
        $categorias = $this->request->getPost('detalles');
        foreach ($categorias as $cate) {
          $pagodetalle = new Pagoscategorias();
          $pagodetalle->setPAGO($objectManager->find('CsnUser\Entity\Pagos',$pago->getID()));
          $pagodetalle->setCATEGORIA($objectManager->find('CsnUser\Entity\Tiposestudio', $cate['categoria']));
          $pagodetalle->setCANTIDAD($cate['cantidad']);
          $pagodetalle->setTOTAL($cate['total']);
          $pagodetalle->setDESCUENTO($cate['descuento']);
          $objectManager->persist($pagodetalle);
          $objectManager->flush();
        }
        $formasdepago = $this->request->getPost('tiposdepago'); 
        foreach ($formasdepago as $fpago) {
          $formapago = new Pagostipos();
          $formapago->setPAGO($objectManager->find('CsnUser\Entity\Pagos',$pago->getID()));
          $formapago->setTIPOPAGO($objectManager->find('CsnUser\Entity\Tipospago',$fpago['tipopago']));
          $formapago->setMONTO($fpago['importe']);
          $formapago->setNUMCUENTA($fpago['cuenta']);
          $formapago->setBANCO($fpago['banco']);
          $objectManager->persist($formapago);
          $objectManager->flush();
        }
        $estudio = $objectManager->find('CsnUser\Entity\Estudios',$this->request->getPost('idEstudio'));
        $estudio->setPAGADO(1);
        $objectManager->merge($estudio);
        $objectManager->flush();
        return new JsonModel(array('id' => $pago->getId()));
      }
    }

    public function verpagoAction()
    {
      $this->layout('layout/vacio');
      $idEstudio = $this->params()->fromRoute('id');
      $om =  $this->getObjectManager();
      $queryPago = $om->createQuery("SELECT p FROM CsnUser\Entity\Pagos p WHERE p.ESTUDIO = $idEstudio");
      $pago = $queryPago->getArrayResult();
      $idPago = $pago[0]['ID'];
      $queryTipos = $om->createQuery("SELECT t,tp.TIPOPAGO as TIPOP,tp.ID as TIPOPID FROM CsnUser\Entity\Pagostipos t LEFT JOIN t.TIPOPAGO tp WHERE t.PAGO = $idPago");
      $tipos = $queryTipos->getArrayResult();
      $queryCategorias = $om->createQuery("SELECT c,cg.NOMBRECATEGORIA FROM CsnUser\Entity\Pagoscategorias c LEFT JOIN c.CATEGORIA cg WHERE c.PAGO = $idPago");
      $categorias = $queryCategorias->getArrayResult();
      $form = new PagorecepcionForm($om);
      $form->get('CANTIDAD')->setAttribute('value',$pago[0]['CANTIDAD']);
      $data = array('form' => $form,'pagos' => $pago,'tipos' => $tipos,'categorias' => $categorias, 'estudio' => $idEstudio);
      $vista = new ViewModel($data);
      return $vista;
    }

    public function EditardependenciaAction()
    {
      if($this->request->isPost()) {
        $objectManager = $this->getObjectManager();
        $dependencia = $objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('idDependencia'));
        $dependencia->setNOMBREDEPENDENCIA($this->request->getPost('nombredependencia'));
        $objectManager->merge($dependencia);
        $objectManager->flush();
        return new JsonModel(array('iddep' => $dependencia->getId()));
      }
    }

    public function gettiposAction()
    {
      if($this->request->isPost()) {
        $objectManager = $this->getObjectManager();
        $queryDependencias = $objectManager->createQuery('SELECT t FROM CsnUser\Entity\Tiposestudio t WHERE t.DEPENDENCIA = '.$this->request->getPost('dependencia'));
        $dependencias = $queryDependencias->getArrayResult();
        return new JsonModel($dependencias);
      }
    }

    public function ListadependenciasAction() {
      $this->layout('layout/vacio');
      $objectManager = $this->getObjectManager();
      $queryDependencias = $objectManager->createQuery('SELECT d FROM CsnUser\Entity\Dependencias d');
      $dependencias = $queryDependencias->getArrayResult();
      return new ViewModel(array('dependencias' => $dependencias));
    }

    public function ReciboAction()
    {
      $this->layout('layout/vacio');
      $idEstudio = $this->params()->fromRoute('id');
      $om =  $this->getObjectManager();
      $queryEstudio = $om->createQuery("SELECT p.ID as PACIENTE FROM CsnUser\Entity\Estudios e LEFT JOIN e.PACIENTE p WHERE e.ID = $idEstudio");
      $estudio = $queryEstudio->getArrayResult();
      $idPaciente = $estudio[0]['PACIENTE'];
      $queryPaciente = $om->createQuery("SELECT p,e,m FROM CsnUser\Entity\Pacientes p LEFT JOIN p.ESTADO e LEFT JOIN p.MUNICIPIO m WHERE p.ID = $idPaciente");
      $paciente = $queryPaciente->getArrayResult();
      $queryPago = $om->createQuery("SELECT p FROM CsnUser\Entity\Pagos p WHERE p.ESTUDIO = $idEstudio");
      $pago = $queryPago->getArrayResult();
      $idPago = $pago[0]['ID'];
      $queryTipos = $om->createQuery("SELECT t,tp.TIPOPAGO as TIPOP,tp.ID as TIPOPID FROM CsnUser\Entity\Pagostipos t LEFT JOIN t.TIPOPAGO tp WHERE t.PAGO = $idPago");
      $tipos = $queryTipos->getArrayResult();
      $queryCategorias = $om->createQuery("SELECT c,cg.NOMBRECATEGORIA FROM CsnUser\Entity\Pagoscategorias c LEFT JOIN c.CATEGORIA cg WHERE c.PAGO = $idPago");
      $categorias = $queryCategorias->getArrayResult();
      $data = array('pagos' => $pago,'tipos' => $tipos,'categorias' => $categorias,'paciente' => $paciente);
      $vista = new ViewModel($data);
      return $vista;
    }

    protected function getObjectManager()
    {
          if (!$this->_objectManager) {
              $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
          }

          return $this->_objectManager;
    }

}