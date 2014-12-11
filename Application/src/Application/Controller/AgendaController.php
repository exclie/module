<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Validator\Identical as IdenticalValidator;
use Zend\Form\FormInterface;

use CsnUser\Entity\Agendas;
use CsnUser\Entity\Estudios;
use CsnUser\Entity\Recepcionistasdoctores;

use Application\Form\AgendasForm;
/**
 * Registration controller
 */
class AgendaController extends AbstractActionController
{
	/**
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_objectManager;
    
    /**
     * @var Zend\Mvc\I18n\Translator
     */
    protected $translatorHelper;
    
    /**
     * @var Zend\Form\Form
     */
    protected $userFormHelper;
    /**
     * @var Plantilla
     */
    protected $plantilla = 'layout/inicio';
    /**
     * @var Plantilla
     */
    protected $vacio = 'layout/vacio';

    public $session;
    /**
     * Register Index Action
     *
     * Displays user registration form using Doctrine ORM and Zend annotations
     *
     * @return Zend\View\Model\ViewModel
     */
    public function calendarioAction()
    {
        $idPaciente = $this->params()->fromRoute('id');
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $hydrator = new DoctrineObject(
            $objectManager,
            'CsnUser\Entity\Agendas'
        );
        $agenda = new Agendas();
        $form = new AgendasForm($objectManager);
        if($this->request->isPost()){
            $form->setData($this->request->getPost());
            if($form->isValid()) {
                $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
                $agenda = $hydrator->hydrate($data['agendas'],$agenda);
                $agenda->setUsuariox($objectManager->find('CsnUser\Entity\User', $this->identity()->getId()));
                $id = $this->request->getPost('id');
                if(!$id){
                    $objectManager->persist($agenda);
                } else {
                    $objectManager->find('CsnUser\Entity\Agendas',$id);
                    $objectManager->persist($agenda);
                }
                $objectManager->flush();
            }
        } 
        if($idPaciente){
            $paciente = $objectManager->find('CsnUser\Entity\Pacientes', $idPaciente);
            $data = array('form' => $form,'nombre' => $paciente->getNOMBRE(),'apPaterno' => $paciente->getAPELLIDO_PATERNO(),'apMaterno' => $paciente->getAPELLIDO_MATERNO(), 'idPaciente' => $idPaciente,'fecha_nac' => $paciente->getFECHA_NACIMIENTO()); 
        } else {
            $data = array('form' => $form);
        }
        $queryrole = $this->getObjectManager()->createQuery("SELECT r.id as role FROM CsnUser\Entity\User u JOIN u.role r WHERE u.id = ".$this->identity()->getId());
        $role = $queryrole->getArrayResult();
        $query = $this->getObjectManager()->createQuery("SELECT u.id,u.firstName,u.lastName FROM CsnUser\Entity\User u WHERE u.role = 4");
        $doctores = $query->getArrayResult();
        $data['doctores'] = $doctores; 
        return new ViewModel($data);
    }
    public function vereventosAction()
    {
        if($this->request->isPost()) {
          $idDoctor = $this->request->getPost('idDoctor');
          $query = $this->getObjectManager()->createQuery("SELECT a.title,a.start,a.end,a.descripcion,a.id,a.color,a.pacientenr,u.firstName,u.lastName,u.apellidoMaterno,p.ID as paciente,d.ID as refdocid,
            a.refdoctor,a.edad,de.ID as dependencia,a.telefono1,a.telefono2 FROM CsnUser\Entity\Agendas a LEFT JOIN a.paciente p LEFT JOIN a.usuariox u LEFT JOIN a.refdocid d LEFT JOIN a.dependencia de WHERE a.doctor = ".$idDoctor);
          $eventos = $query->getArrayResult();
          $resultado = new JsonModel($eventos);
          return $resultado;  
        }
    }
    public function vereventoAction() {
      if($this->request->isPost()) {
          $idDoctor = $this->request->getPost('idDoctor');
          $idEvento = $this->request->getPost('idEvento');
          $query = $this->getObjectManager()->createQuery("SELECT a.id,t.ID FROM CsnUser\Entity\Agendas a LEFT JOIN a.tipo t where a.id = $idEvento ");
          $eventos = $query->getArrayResult();
          return new JsonModel($eventos);
        }
    }
    public function guardareventoAction() 
    {
      $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
      $hydrator = new DoctrineObject(
          $objectManager,
          'CsnUser\Entity\Agendas'
      );
      $form = new AgendasForm($objectManager);
      if($this->request->isPost()){
        $estudio = new Estudios();
        $idPaciente = $this->request->getPost('paciente');
        if($this->request->getPost('id')) { //Evento existente, (update)
          $eventoquery = $objectManager->createQuery('SELECT a.id,e.ID as ESTUDIO FROM CsnUser\Entity\Agendas a JOIN a.ESTUDIO e WHERE a.id = '.$this->request->getPost('id'));
          $estudioId = $eventoquery->getArrayResult();
          $estudioId = $estudioId[0]['ESTUDIO'];
          $estudio = $objectManager->find('CsnUser\Entity\Estudios', $estudioId);
          if($estudio->getESTADO()->getId() == 8){ //Actualizar el estudio solo si se encuentra en estado 'agendado'
            $fechaEstudio = new \DateTime($this->request->getPost('start'));
            $estudio->setFECHA($fechaEstudio);
            $estudio->setDoctor($objectManager->find('CsnUser\Entity\User',$this->request->getPost('doctor')));
            $estudio->setDOCTORENV($objectManager->find('CsnUser\Entity\Doctores',$this->request->getPost('refdocid')));
            $estudio->setPACIENTENR($this->request->getPost('title')); // Paciente no registrado
            $estudio->setUsuario($objectManager->find('CsnUser\Entity\User', $this->identity()->getId()));
            $estudio->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('dependencia')));
            $estudio->setEstado($objectManager->find('CsnUser\Entity\Estadosestudios', 8)); // Estado agendado.
            if ($idPaciente) { //Paciente registrado
              $estudio->setPaciente($objectManager->find('CsnUser\Entity\Pacientes', $idPaciente));  
            } else { //Paciente no registrado
              $estudio->setPaciente(null);
            }
            if($this->request->getPost('tipo')) {
              foreach ($this->request->getPost('tipo') as $tipo) {
                if (!$estudio->getTipos()->contains($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]))) {
                  $estudio->addTIPO($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]));
                }
              }
            }
            $objectManager->merge($estudio);  
          }
        } else { //Evento nuevo
          $fechaEstudio = new \DateTime($this->request->getPost('start'));
          $estudio->setFECHA($fechaEstudio);
          $estudio->setDoctor($objectManager->find('CsnUser\Entity\User',$this->request->getPost('doctor')));
          $estudio->setDOCTORENV($objectManager->find('CsnUser\Entity\Doctores',$this->request->getPost('refdocid')));
          $estudio->setPACIENTENR($this->request->getPost('title')); // Paciente no registrado
          $estudio->setUsuario($objectManager->find('CsnUser\Entity\User', $this->identity()->getId()));
          $estudio->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('dependencia')));
          $estudio->setEstado($objectManager->find('CsnUser\Entity\Estadosestudios', 8));
          if ($idPaciente) { //Paciente registrado
            $estudio->setPaciente($objectManager->find('CsnUser\Entity\Pacientes', $idPaciente));  
          }
          foreach ($this->request->getPost('tipo') as $tipo) {
            $estudio->addTIPO($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]));
          }
          $objectManager->persist($estudio);
        }
        $objectManager->flush();

        if($this->request->getPost('id')){
          $agenda = $objectManager->find('CsnUser\Entity\Agendas',$this->request->getPost('id'));
          $agenda->setId($this->request->getPost('id'));
          //$agenda->setESTUDIO($objectManager->find('CsnUser\Entity\Estudios',$estudio->getId()));
          $agenda->setDescripcion($this->request->getPost('descripcion'));
          $agenda->setEdad($this->request->getPost('edad'));
          $agenda->setRefdoctor($this->request->getPost('refdoctor'));
          $agenda->setRefdocid($objectManager->find('CsnUser\Entity\Doctores',$this->request->getPost('refdocid')));
          $agenda->setDependencia($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('dependencia')));
          $nombredependencia = $objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('dependencia'))->getNOMBREDEPENDENCIA();
          $agenda->setTelefono1($this->request->getPost('telefono1'));
          $agenda->setTelefono2($this->request->getPost('telefono2'));
          $agenda->setPacientenr($this->request->getPost('pacientenr'));
          $agenda->setStart($this->request->getPost('start'));
          $agenda->setEnd($this->request->getPost('end'));
          $agenda->setColor($this->request->getPost('color'));
          $agenda->setDoctor($objectManager->find('CsnUser\Entity\User',$this->request->getPost('doctor')));
          $agenda->setUsuariox($objectManager->find('CsnUser\Entity\User', $this->identity()->getId()));
          $nombretipo = '';
          if($this->request->getPost('tipo')) {
            $tipos = array();
            $agenda->getTipo()->clear();
            foreach ($this->request->getPost('tipo') as $tipo) {
              $nombretipo = $nombretipo.' '.$objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0])->getNOMBRECATEGORIA().';';
              if(!$agenda->getTipo()->contains($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]))) {
                $agenda->getTipo()->add($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]));
              }
            } 
          } else if ($agenda->getTipo()->count() > 0) {
            foreach ($agenda->getTipo() as $tipos) {
              $nombretipo = $nombretipo.' '.$tipos->getNOMBRECATEGORIA().';';
            }
          }
          $agenda->setTitle($this->request->getPost('title').' ['.$nombredependencia.']: '.$nombretipo);
          $objectManager->merge($agenda);  
        } else {
          $agenda = new Agendas();
          if($idPaciente){
              $agenda->setPaciente($objectManager->find('CsnUser\Entity\Pacientes', $idPaciente));
          }
          $agenda->setESTUDIO($objectManager->find('CsnUser\Entity\Estudios',$estudio->getId()));
          $agenda->setDescripcion($this->request->getPost('descripcion'));
          $agenda->setEdad($this->request->getPost('edad'));
          $agenda->setRefdoctor($this->request->getPost('refdoctor'));
          $agenda->setRefdocid($objectManager->find('CsnUser\Entity\Doctores',$this->request->getPost('refdocid')));
          $agenda->setDependencia($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('dependencia')));
          $nombredependencia = $objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('dependencia'))->getNOMBREDEPENDENCIA();
          $agenda->setTelefono1($this->request->getPost('telefono1'));
          $agenda->setPacientenr($this->request->getPost('pacientenr'));
          $agenda->setTelefono2($this->request->getPost('telefono2'));
          $agenda->setTitle($this->request->getPost('title'));
          $agenda->setStart($this->request->getPost('start'));
          $agenda->setEnd($this->request->getPost('end'));
          $agenda->setColor($this->request->getPost('color'));
          $agenda->setDoctor($objectManager->find('CsnUser\Entity\User',$this->request->getPost('doctor')));
          $agenda->setUsuariox($objectManager->find('CsnUser\Entity\User', $this->identity()->getId()));
          if($this->request->getPost('tipo')) {
            $nombretipo = '';
            $tipos = array();
            foreach ($this->request->getPost('tipo') as $tipo) {
              $nombretipo = $nombretipo.' '.$objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0])->getNOMBRECATEGORIA().';';
              $agenda->getTipo()->add($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]));
            }
          }
          $agenda->setTitle($this->request->getPost('title').' ['.$nombredependencia.']: '.$nombretipo);
          $objectManager->persist($agenda);
        }
        $objectManager->flush();
        return new JsonModel(array('tipo' => $this->request->getPost('tipo')));
      }
    }
    public function borrareventoAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if($this->request->isPost()){
            $id = $this->request->getPost('id');
            $evento = $objectManager->find('CsnUser\Entity\Agendas',$id);
            if($evento->getESTUDIO() && $evento->getESTUDIO()->getESTADO()->getId() == 8){
              $objectManager->remove($objectManager->find('CsnUser\Entity\Estudios',$evento->getESTUDIO()));
            }
            $objectManager->remove($evento);
            $objectManager->flush();
        }
        return new JsonModel($data);

    }

    protected function getObjectManager()
    {
          if (!$this->_objectManager) {
              $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
          }

          return $this->_objectManager;
    }
    

}