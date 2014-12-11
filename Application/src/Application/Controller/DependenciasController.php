<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Validator\Identical as IdenticalValidator;
use Zend\Form\FormInterface;

use Application\Form\DependenciaForm;

use CsnUser\Entity\Dependencias;
/**
 * Registration controller
 */
class DependenciasController extends AbstractActionController
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
    public function DependenciasAction()
    {
    	return new ViewModel();
    }
    public function NuevadependenciaAction()
    {
      if($this->request->isPost()){
        $objectManager = $this->getObjectManager();
        $dependencia = new Dependencias();
        $dependencia->setNOMBREDEPENDENCIA($this->request->getPost('dep'));
        $objectManager->persist($dependencia);
        $objectManager->flush();
        return new JsonModel(array('id' => $dependencia->getId()));
      }
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
        $queryDependencias = $objectManager->createQuery('SELECT t FROM CsnUser\Entity\Tiposestudio t WHERE t.DEPENDENCIA = '.$this->request->getPost('dependencia').' AND t.ACTIVO = 1');
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


    protected function getObjectManager()
    {
          if (!$this->_objectManager) {
              $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
          }

          return $this->_objectManager;
    }

}