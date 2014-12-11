<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Validator\Identical as IdenticalValidator;
use Zend\Form\FormInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder; 

use CsnUser\Entity\User;
use CsnUser\Entity\Agendas;
use CsnUser\Entity\Pacientes;
use CsnUser\Entity\Consultas;
use CsnUser\Entity\Consultasub;
use CsnUser\Entity\Receptor;
use CsnUser\Entity\Pacientesdependencias;
use CsnUser\Entity\Consultaimagenes;
use CsnUser\Options\ModuleOptions;
use CsnUser\Service\SimpleThumbs;
use CsnUser\Service\UserService as UserCredentialsService;

use Application\Form\PacientesCrearForm;
use Application\Form\ConsultaForm;
use Application\Form\PacientesForm;
use Application\Form\ConsultasubForm;
use Application\Form\ReceptorForm;
use Application\Form\LoginForm;
use Application\Form\ImagenesForm;
use Zend\Stdlib\ErrorHandler;


/**
 * Registration controller
 */
class PacientesController extends AbstractActionController
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
    private $uploads = '/../public/images/uploads';
    private $uploads_rel = '/images/uploads/';
    /**
     * Register Index Action
     *
     * Displays user registration form using Doctrine ORM and Zend annotations
     *
     * @return Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    } 
    public function pacientesajaxAction()
    {
      $dato = $this->getRequest()->getPost('nombre');
      $dbal = $this->getObjectManager()->getConnection('default');
      $query = "SELECT p.*,e.NOMBRE as ESTADO,m.NOMBRE as MUNICIPIO FROM Pacientes p 
        LEFT JOIN Estados e ON p.ESTADO_id = e.ID 
        LEFT JOIN Municipios m ON p.MUNICIPIO_id = m.ID
        WHERE CONCAT(p.NOMBRE,' ',p.APELLIDO_PATERNO,' ',p.APELLIDO_MATERNO) like '%$dato%'";
      $pacientes = $dbal->fetchAll( $query );
      //$pacientes = $query->getArrayResult();
      $resultado = new JsonModel($pacientes);
      return $resultado;
    }

    public function listaconsultasAction() {
      $em = $this->getObjectManager();
      $queryConsultas = $em->createQuery("SELECT c.FECHA_SUB,pa.NOMBRE,pa.APELLIDO_PATERNO,pa.APELLIDO_MATERNO,co.MOTIVO_CONS 
        FROM CsnUser\Entity\Consultasub c JOIN c.CONSULTA co LEFT JOIN co.PACIENTE pa WHERE c.MEDICO = ".$this->identity()->getId()
        .' order by c.FECHA_SUB desc');
      $consultas = $queryConsultas->getArrayResult();
      $resultado = new ViewModel(array('consultas' => $consultas));
      return $resultado;
    }

    public function pacientenuevoAction() {
      $result = null;
      $idEstudio = $this->params()->fromRoute('id');
      $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
      $query = $this->getObjectManager()->createQuery("SELECT d.ID,d.NOMBREDEPENDENCIA FROM CsnUser\Entity\Dependencias d");
      $dependencias = $query->getArrayResult();
      $hydrator = new DoctrineObject(
          $objectManager,
          'CnsUser\Entity\Pacientes'
      );

      $form = new PacientesForm($objectManager);
      if($idEstudio) {
        $form->get('IdEstudio')->setAttribute('value',$idEstudio);
      }
      if ($this->request->isPost()) {
          $form->setData($this->request->getPost());
          if ($form->isValid()) {
            $paciente = new Pacientes();
            $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
            $paciente = $hydrator->hydrate($data, $paciente);
            $paciente->setFECHA_REGISTRO(new \DateTime()); 

            $objectManager->persist($paciente);
            $objectManager->flush();
            if($this->request->getPost('IdEstudio')) {
              $estudio = $objectManager->find('CsnUser\Entity\Estudios', $this->request->getPost('IdEstudio'));
              $estudio->setPACIENTE($objectManager->find('CsnUser\Entity\Pacientes', $paciente->getId()));
              $objectManager->merge($estudio);
              $objectManager->flush();
              $queryAgenda = $objectManager->createQuery("SELECT a FROM CsnUser\Entity\Agendas a WHERE a.ESTUDIO =".$estudio->getId());
              $agendaresult = $queryAgenda->getArrayResult();
              $IdAgenda = $agendaresult[0]['id'];
              $agenda = $objectManager->find('CsnUser\Entity\Agendas',$IdAgenda);
              $agenda->setPaciente($paciente);
              $agenda->setTitle($paciente->getNOMBRE().' '.$paciente->getAPELLIDO_PATERNO().' '.$paciente->getAPELLIDO_MATERNO());
              $agenda->setPacientenr($paciente->getNOMBRE().' '.$paciente->getAPELLIDO_PATERNO().' '.$paciente->getAPELLIDO_MATERNO());
              $objectManager->merge($agenda);
              $objectManager->flush();
            }
            if($this->request->getPost('ins')){
              $instis = json_decode($this->request->getPost('ins'),true);
              foreach($instis as $ins){
                $padep = new Pacientesdependencias();
                $padep->setPACIENTE($objectManager->find('CsnUser\Entity\Pacientes',$paciente->getID()));
                $padep->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$ins['IDINSTITUCION']));
                $padep->setAFILIACION($ins['AFILIACION']);
                $objectManager->persist($padep);
                $objectManager->flush();
              }
            }
            $result = 1;
          } else {
            $result = 0;
          }
          $data = array('resultado' => $result);
          return new JsonModel($data);
      } else {
        $data = array('form' => $form,'result' => $result,'dependencias' => $dependencias);
        return new ViewModel($data);  
      }
    }
    public function getCIE10Action()
    {
      $dato = $this->getRequest()->getQuery('term');
      $query = $this->getObjectManager()->createQuery("SELECT c FROM CsnUser\Entity\CIE10 c WHERE (c.NOMBRE_ENFERMEDAD LIKE '%$dato%' OR c.CIE_NUMERO LIKE '%$dato%')");
      $enfermedad = $query->getArrayResult();
      foreach($enfermedad as $enf) {
        $json[] = array('id' => $enf['CIE_NUMERO'],'label' => '['.$enf['CIE_NUMERO'].'] '.$enf['NOMBRE_ENFERMEDAD'],'value' => $enf['NOMBRE_ENFERMEDAD'],'name' => $enf['ID']);
      }
      $resultado = new JsonModel($json);
      return $resultado;
    }
    public function calendarioAction()
    {
        $idPaciente = $this->params()->fromRoute('id');
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $hydrator = new DoctrineObject(
            $objectManager,
            'CsnUser\Entity\Agendas'
        );
        $form = new AgendasForm($objectManager);
        $agenda = new Agendas();
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
            $data = array('form' => $form,'nombre' => $paciente->getNOMBRE(),'apPaterno' => $paciente->getAPELLIDO_PATERNO(), 'idPaciente' => $idPaciente); 
        } else {
            $data = array('form' => $form);
        }
        return new ViewModel($data);
    }
    public function vereventosAction()
    {
        $query = $this->getObjectManager()->createQuery("SELECT a.title,a.start,a.end,a.descripcion,a.id,a.color,a.refdoctor,a.edad,a.dependencia,a.estudios FROM CsnUser\Entity\Agendas a WHERE a.usuariox = ".$this->identity()->getId());
        $eventos = $query->getArrayResult();
        $resultado = new JsonModel($eventos);
        return $resultado;
    }

    public function verconsultaAction() {
      $pac = $this->request->getPost('pac');
      $con = $this->request->getPost('consul');
      if($con != ''){
        $query = $this->getObjectManager()->createQuery("SELECT c,cs,d FROM CsnUser\Entity\Consultas c LEFT JOIN c.CONSULTASUBS cs LEFT JOIN cs.DIAGNOSTICOS d WHERE c.PACIENTE = $pac AND cs = $con");
      } else {
        $query = $this->getObjectManager()->createQuery("SELECT c FROM CsnUser\Entity\Consultas c WHERE c.PACIENTE = $pac");
      }
      $consulta = $query->getArrayResult();
      $resultado = new JsonModel($consulta);
      return $resultado;
    }
    public function borrareventoAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if($this->request->isPost()){
            $id = $this->request->getPost('id');
            $evento = $objectManager->find('CsnUser\Entity\Agendas',$id);
            $objectManager->remove($evento);
            $objectManager->flush();
        }
        return new JsonModel($data);

    }
    public function consultasAction() { 
      $form = new ConsultaForm($this->getObjectManager());
      $form2 = new ConsultasubForm($this->getObjectManager());
      if($this->request->isPost()) {
        $idPaciente = $this->request->getPost('pac');
        $query = $this->getObjectManager()->createQuery("SELECT p,t,d,a FROM CsnUser\Entity\Pacientes p JOIN p.TIPO_SANGUINEO t JOIN p.DISCAPACIDAD d LEFT JOIN p.ANTECEDENTES a WHERE p.ID = $idPaciente");
        $querycons = $this->getObjectManager()->createQuery("SELECT c,cs FROM CsnUser\Entity\Consultas c LEFT JOIN c.CONSULTASUBS cs WHERE c.PACIENTE = $idPaciente");
        $idsub = $this->request->getPost('idsub');
        $paciente = $query->getArrayResult();
        $consultas = $querycons->getArrayResult();
        $data = array('form' => $form, 'form2' => $form2, 'paciente' => $paciente, 'consultas' => $consultas);
        if($idsub) {
          $querysub = $this->getObjectManager()->createQuery("SELECT c,cs FROM CsnUser\Entity\Consultas c LEFT JOIN c.CONSULTASUBS cs WHERE c.PACIENTE = $idPaciente AND c.CONSULTASUBS = $idsub");
          $subconsulta = $querysub->getArrayResult();
          $data = array('form' => $form, 'form2' => $form2, 'paciente' => $paciente, 'consultas' => $consultas, 'subconsulta' => $subconsulta);
        }
      } else {
        $data = array('form' => $form, 'form2' => $form2);
      }
      return new ViewModel($data);
    }

    public function guardarconsultaAction()
    {
      $hoy = new \DateTime();
      $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
      $hydrator = new DoctrineObject(
            $objectManager,
            'CsnUser\Entity\Consultas'
        );
      $form = new ConsultaForm($objectManager);
      $form2 = new ConsultasubForm($objectManager);
      if($this->request->isPost()){
        $form->setValidationGroup('MOTIVO_CONS','INFO_MOTIVO','INFO_ADICIONAL','PACIENTE');
        $form->setData($this->request->getPost());
        $form2->setValidationGroup('FC','FR','TA','PS','T','ALTURA','PESO','SUBJETIVO','OBJETIVO','DIAG_ARRAY');
        $form2->setData($this->request->getPost());
        if($form->isValid()) {
          $consulta = new Consultas();
          $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
          $consulta = $hydrator->hydrate($data,$consulta);
          $consulta->setMedico($this->identity()->getId());
          $consulta->setFecha_Cons($hoy);
          $consulta->setPaciente($this->request->getPost('PACIENTE'));
          $id = $this->request->getPost('ID');
          if(!$id) {
              $objectManager->persist($consulta);
          } else {
              $consulta = $objectManager->find('CsnUser\Entity\Consultas',$id);
              if($consulta->getFecha_Cons() === $hoy){
                $consulta = $hydrator->hydrate($data,$consulta);
                $consulta->setMedico($this->identity()->getId());
                $consulta->setFecha_Cons($hoy);
                $consulta->setPaciente($this->request->getPost('PACIENTE'));
                $objectManager->persist($consulta);
              }
          }
          $objectManager->flush();
        }
        if($form2->isValid()) {
          $consultasub = new Consultasub();
          $data = $form2->getData(FormInterface::VALUES_AS_ARRAY);
          $consultasub = $hydrator->hydrate($data,$consultasub);
          $consultasub->setConsulta($objectManager->find('CsnUser\Entity\Consultas',$consulta->getId()));
          $consultasub->setFecha_Sub($hoy);
          $diagnosticos = $this->request->getPost('DIAG_ARRAY');
          $diagnosticos = str_replace("[","",$diagnosticos);
          $diagnosticos = str_replace("]","",$diagnosticos);
          $diagnosticos = str_replace('"','',$diagnosticos);
          $diagnosticos = explode(",",$diagnosticos);
          if($diagnosticos[0] != '' && $diagnosticos){
            foreach($diagnosticos as $d) {
              $consultasub->addDIAGNOSTICO($objectManager->find('CsnUser\Entity\CIE10',$d));
            }
          }
          $idsub = $this->request->getPost('IDSUB');
          if(!$idsub) {
              $objectManager->persist($consultasub);
              $objectManager->flush();
          } else {
              $consultasub = $objectManager->find('CsnUser\Entity\Consultasub',$idsub);
              $consultasub = $hydrator->hydrate($data,$consultasub);
              $objectManager->persist($consultasub);
              $objectManager->flush();
          }
        } 
        $resultado = array('id_consulta' => $consulta->getId(),'id' => $id,'consultasub' => $consultasub->getId(),'paciente' => $consulta->getPaciente());
        return new JsonModel(array('resultado' => $resultado));
      }
  
    }
    public function tablaconsultasAction(){
      $this->layout($this->vacio);
      $idPaciente = $this->request->getPost('pac');
      $querycons = $this->getObjectManager()->createQuery("SELECT c,cs FROM CsnUser\Entity\Consultas c LEFT JOIN c.CONSULTASUBS cs WHERE c.PACIENTE = $idPaciente");
      $consultas = $querycons->getArrayResult();
      return new ViewModel(array('consultas' => $consultas));
    }
    public function imagenesconsultaAction() {
      $this->layout($this->vacio);
          if ($this->request->isPost()) {
            $pac = $this->request->getPost('pac');
            $consulta = $this->request->getPost('con');
            $consultasub = $this->request->getPost('cons');
            $query = $this->getObjectManager()->createQuery("SELECT i FROM CsnUser\Entity\Consultaimagenes i WHERE i.PACIENTE = $pac AND i.CONSULTASUB = $consultasub");
            $imagenes = $query->getArrayResult();
            return new ViewModel(array(
              'pac' => $pac,
              'consulta' => $consulta,
              'consultasub' => $consultasub,
              'imagenes' => $imagenes,
              )); 
          }  
    }
    public function recibirAction() {
      if($this->request->isPost()){
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $consultaimagenes = new Consultaimagenes();
        $data = array_merge_recursive(
                  $this->getRequest()->getPost()->toArray(),           
                  $this->getRequest()->getFiles()->toArray()
                );
        print_r($data);
        $consultaimagenes->setImagen($data['file']['name']);
        $consultaimagenes->setConsulta($objectManager->find('CsnUser\Entity\Consultas',$data['consulta']));
        $consultaimagenes->setConsultasub($objectManager->find('CsnUser\Entity\Consultasub',$data['consultasub']));
        $consultaimagenes->setPaciente($objectManager->find('CsnUser\Entity\Pacientes',$data['paciente']));
        $consultaimagenes->setMedico($objectManager->find('CsnUser\Entity\User',$this->identity()->getId()));
        $ruta = dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/public/imagenes/Consultas/'.$data['paciente'];
        if (!file_exists($ruta)) mkdir($ruta);
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setDestination($ruta);
        if($adapter->receive($data['file']['name'])){
          $this->createthumb($ruta.'/'.$data['file']['name'],100,100);
          $objectManager->persist($consultaimagenes);
          $objectManager->flush();
        }
      }
        return new JsonModel();
    
    }
    public function removerAction() {
      if($this->request->isPost()){
        $archivo = $this->request->getPost('archivo');
        $ruta = dirname(__DIR__).'/assets/'.$this->identity()->getId();
        unlink($ruta.$archivo);
        return new JsonModel();
      }
    }

    public function municipiosAction()
    { 
      $id = $this->getRequest()->getPost('municipioid');
      $query = $this->getObjectManager()->createQuery("SELECT m FROM CsnUser\Entity\Municipios m WHERE m.ESTADO = $id");
      $municipios = $query->getArrayResult();
      $resultado = new JsonModel($municipios);
      return $resultado;
    }

    public function clientesAction()
    {
      $query = $this->getObjectManager()->createQuery("SELECT m FROM CsnUser\Entity\Receptor m");
      $clientes = $query->getArrayResult();
      $resultado = new JsonModel($clientes);
      return $resultado;
    }

    public function checarcurpAction()
    {
      $curp = $this->getRequest()->getPost('checarcurp');
      $query = $this->getObjectManager()->createQuery("SELECT p.CURP FROM CsnUser\Entity\Pacientes u WHERE p.CURP = '$curp'");
      $usuarios = $query->getArrayResult();
      if($usuarios){
        $data = array('a' => '2');
        $resultado = new JsonModel(array($data));
      } else {
        $data = array('a' => '1');
        $resultado = new JsonModel(array($data));
      }
      return $resultado;
    }
    public function nuevoclienteAction() {
      $this->layout($this->vacio);
      $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
      $form = new ReceptorForm($objectManager);
      if($this->request->isPost()) {
        $form->setData($this->request->getPost());
        if($form->isValid()) {
          $hydrator = new DoctrineObject(
                $objectManager,
                'CsnUser\Entity\Receptor'
          );
          $cliente = new Receptor();
          $data = $form->getData(FormInterface::VALUES_AS_ARRAY);
          $cliente = $hydrator->hydrate($data,$cliente);
          $objectManager->persist($cliente);
          $objectManager->flush();
          return new JsonModel(array('result' => 1,'cliente' => $cliente->getId()));
        } else {
          return new JsonModel(array('result' => 0,'mensajes' => $form->getMessages()));
        }
      } else {
        $data = array('form' => $form);
        return new ViewModel($data);  
      }
      
    }
    /**
     * get entityManager
     *
     * @return Doctrine\ORM\EntityManager
     */   
    protected function getObjectManager()
    {
          if (!$this->_objectManager) {
              $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
          }

          return $this->_objectManager;
    }



  public function createthumb($name,$new_w,$new_h)
  {
    $system=explode(".",$name);
    if (preg_match("/jpg|jpeg/",$system[1])){$src_img=imagecreatefromjpeg($name);}
    if (preg_match("/png/",$system[1])){$src_img=imagecreatefrompng($name);}
    $old_x=imageSX($src_img);
    $old_y=imageSY($src_img);
    if ($old_x > $old_y) 
    {
      $thumb_w=$new_w;
      $thumb_h=$old_y*($new_h/$old_x);
    }
    if ($old_x < $old_y) 
    {
      $thumb_w=$old_x*($new_w/$old_y);
      $thumb_h=$new_h;
    }
    if ($old_x == $old_y) 
    {
      $thumb_w=$new_w;
      $thumb_h=$new_h;
    }
    $dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
    imagecopyresampled($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
    if (preg_match("/png/",$system[1]))
    {
      imagepng($dst_img,$system[0].'_th.'.$system[1]); 
    } else {
      imagejpeg($dst_img,$system[0].'_th.'.$system[1]); 
    }
    imagedestroy($dst_img); 
    imagedestroy($src_img); 
  }
    
       
}