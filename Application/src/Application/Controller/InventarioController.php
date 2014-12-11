<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Validator\Identical as IdenticalValidator;
use Zend\Form\FormInterface;

use Application\Form\InventarioForm;

use CsnUser\Entity\Inventario;
/**
 * Registration controller
 */
class InventarioController extends AbstractActionController
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
    public function InventarioAction()
    {
    	return new ViewModel();
    }
    public function MaterialnuevoAction()
    {
      $objectManager = $this->getObjectManager();
      $this->layout('layout/vacio');
      $form = new InventarioForm($objectManager);
      $id = $this->params()->fromRoute('id');
      if($id){
        $queryMaterial = $objectManager->createQuery('SELECT i FROM CsnUser\Entity\Inventario i WHERE i.ID = '.$id);
        $material = $queryMaterial->getArrayResult();
        $form->get('MATERIAL')->setAttributes(array('value' => $material[0]['MATERIAL']));
        $form->get('CANTIDAD')->setAttributes(array('value' => $material[0]['CANTIDAD']));
        $form->get('CANTIDADMIN')->setAttributes(array('value' => $material[0]['CANTIDADMIN']));
        $form->get('FECHACADUCIDAD')->setAttributes(array('value' => $material[0]['FECHACADUCIDAD']));
        $form->get('ID')->setAttributes(array('value' => $material[0]['ID']));
      }
      if($this->request->isPost()) {
        $form->setData($this->request->getPost());
        if($form->isValid()) {
          $hydrator = new DoctrineObject(
                $objectManager,
                'CsnUser\Entity\Inventario'
          );
          $material = new Inventario();
          $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
          $material = $hydrator->hydrate($data,$material);
          $objectManager->persist($material);
          $objectManager->flush();
          return new JsonModel(array('chido' => '1','data' => $data, 'material' => $material));
        } else {
          echo "error";
        }
      }
      return new ViewModel(array('form' => $form));
    }

    public function ListamaterialAction() {
      $this->layout('layout/vacio');
      $objectManager = $this->getObjectManager();
      $queryMaterial = $objectManager->createQuery('SELECT i FROM CsnUser\Entity\Inventario i');
      $material = $queryMaterial->getArrayResult();
      return new ViewModel(array('material' => $material));
    }

    protected function getObjectManager()
    {
          if (!$this->_objectManager) {
              $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
          }

          return $this->_objectManager;
    }

}