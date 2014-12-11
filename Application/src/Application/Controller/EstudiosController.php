<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Mail\Message;
use Zend\Validator\Identical as IdenticalValidator;
use Zend\Form\FormInterface;
use DOMPDFModule\View\Model\PdfModel;
use SlmMail\Mail\Transport\HttpTransport;
use SlmMail\Service\SendGridService;

use CsnUser\Entity\Estudioimagenes;
use CsnUser\Entity\Estudioreceta;
use CsnUser\Entity\Tiposestudio;
use CsnUser\Entity\Inventarioestudio;
use CsnUser\Entity\Estudios;
use CsnUser\Entity\Doctores;
use CsnUser\Entity\EstudioMaterial;

use Application\Form\CategoriasForm;
use Application\Form\EstudiosForm;
use Application\Form\DoctorForm;
/**
 * Registration controller
 */
class EstudiosController extends AbstractActionController
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
    public function estudiosAction()
    {
    	$query = $this->getObjectManager()->createQuery("SELECT u.id,u.firstName,u.lastName FROM CsnUser\Entity\User u WHERE u.role = '2'");
    	$doctores = $query->getArrayResult();
    	return new ViewModel(array('doctores' => $doctores));
    }
    public function listaestudiosAction()
    {
      $Hoy = new \DateTime();
      $Hoy = $Hoy->format('Y-m-d');
      $this->layout('layout/vacio');
      if($this->identity()->getRole()->getId() == 5) {
        $where = "WHERE e.ESTADO = 1 AND e.FECHA <= '$Hoy'";
      }
      if($this->request->isPost()){
        $where = "";
        $contador = 0;
        $fecha1 = $this->request->getPost('fecha1');
        $fecha2 = $this->request->getPost('fecha2');
        $doctor = $this->request->getPost('doctor');
        $paciente = $this->request->getPost('paciente');
        $estado = $this->request->getPost('estado');
        $between = '';
        $andDoctor = '';
        $andPaciente = '';
        $andEstado = '';
        if ($fecha1 && $fecha2) {
          $contador++;
          if($contador > 1){
            $between = "AND e.FECHA between '$fecha1' AND '$fecha2'";
          } else {
            $between = "e.FECHA between '$fecha1' AND '$fecha2'";
          }
        }
        if ($doctor) {
          $contador++;
          if($contador > 1){
            $andDoctor = "AND e.DOCTOR = '$doctor'";
          } else {
            $andDoctor = "e.DOCTOR = '$doctor'";
          }
        }
        if ($paciente) {
          $contador++;
          if($contador > 1){
            $andPaciente = "AND e.PACIENTE = '$paciente'";
          } else {
            $andPaciente = "e.PACIENTE = '$paciente'";
          }
        }
        if ($estado) {
          $contador++;
          if($contador > 1){
            $andEstado = "AND e.ESTADO = '$estado'";
          } else {
            $andEstado = "e.ESTADO = '$estado'";
          }
        }
        if($contador > 0)
        $where = "WHERE ".$between." ".$andDoctor." ".$andPaciente." ".$andEstado;
      }
      $queryEstudios = $this->getObjectManager()->createQuery("SELECT e,de.ID as DEPENDENCIA,p.NOMBRE,p.APELLIDO_PATERNO,p.APELLIDO_MATERNO,d.firstName,d.lastName,
        t as tNombre,es.NOMBRE as esNombre,es.ID as esID FROM CsnUser\Entity\Estudios e 
        LEFT JOIN e.PACIENTE p JOIN e.DOCTOR d LEFT JOIN e.TIPOS t JOIN e.ESTADO es LEFT JOIN e.DEPENDENCIA de ".$where." ORDER BY e.FECHA DESC")->setMaxResults(1000);
      $estudios = $queryEstudios->getArrayResult();
      return new ViewModel(array('estudios' => $estudios));
    }
    public function estudiodetallesAction() {
      $idEstudio = $this->params()->fromRoute('id');
      $query = $this->getObjectManager()->createQuery("SELECT e,t,es.ID as ESTADO,d.NOMBRE as DOCTORENV,d.ID as IDDOCTOR,p.ID as PACIENTE FROM CsnUser\Entity\Estudios e JOIN e.PACIENTE p LEFT JOIN e.TIPOS t LEFT JOIN e.ESTADO es LEFT JOIN e.DOCTORENV d WHERE e.ID = ".$idEstudio);
      $queryListaMateriales = $this->getObjectManager()->createQuery("SELECT m.ID,m.MATERIAL FROM CsnUser\Entity\Inventario m");
      $listaMateriales = $queryListaMateriales->getArrayResult();
      $estudio = $query->getArrayResult();
      $categorias = array();
      $contador = 0;
      if($estudio[0]['ESTADO'] == 2 || $estudio[0]['ESTADO'] == 3 || $estudio[0]['ESTADO'] == 4) {
        $queryString = "SELECT m,i.ID as IDMAT,i.MATERIAL FROM CsnUser\Entity\EstudioMaterial m LEFT JOIN m.INVENTARIO i WHERE m.ESTUDIO = ".$estudio[0][0]['ID']." AND m.CATEGORIA = ";
      } else {
        $queryString = "SELECT m,i.ID as IDMAT,i.MATERIAL FROM CsnUser\Entity\Inventarioestudio m LEFT JOIN m.INVENTARIO i WHERE m.CATEGORIA = 1";
      }
      foreach ($estudio[0][0]['TIPOS'] as $est) {
        $queryMateriales = $this->getObjectManager()->createQuery($queryString.$est['ID']);
        $materialresult = $queryMateriales->getArrayResult();
        $categorias[$contador]['categoria'] = $est['NOMBRECATEGORIA']; 
        $categorias[$contador]['ID'] = $est['ID'];
        $materiales = array();
        foreach ($materialresult as $mat) {
          $categorias[$contador]['materiales'][] = array('id' => $mat['IDMAT'],'material' => $mat['MATERIAL'],'cantidad' => $mat[0]['CANTIDAD']);
        }
        $contador++;
      }
      $queryPaciente = $this->getObjectManager()->createQuery("SELECT p,t,d,a as PACIENTE FROM CsnUser\Entity\Pacientes p LEFT JOIN p.TIPO_SANGUINEO t LEFT JOIN p.DISCAPACIDAD d LEFT JOIN p.ANTECEDENTES a WHERE p.ID = ".$estudio[0]['PACIENTE']);
      $paciente = $queryPaciente->getArrayResult();
      $queryMedico = $this->getObjectManager()->createQuery("SELECT d,e.ESPECIALIDAD FROM CsnUser\Entity\Doctores d LEFT JOIN d.ESPECIALIDAD e WHERE d.ID = ".$estudio[0]['IDDOCTOR']);
      $medico = $queryMedico->getArrayResult();
      return new ViewModel(array('estudio' => $estudio,'paciente' => $paciente,'categorias' => $categorias,'listaMateriales' => $listaMateriales,'medico' => $estudio[0]['DOCTORENV'],'especialidad' => $medico[0]['ESPECIALIDAD']));
    }
    public function imagenesestudioAction() {
      if($this->request->isPost()){
        $idPaciente = $this->request->getPost('idPaciente');
        $idEstudio = $this->request->getPost('idEstudio');
        $this->layout($this->vacio);
        $query = $this->getObjectManager()->createQuery("SELECT i FROM CsnUser\Entity\Estudioimagenes i WHERE i.PACIENTE = $idPaciente AND i.ESTUDIO = $idEstudio");
        $queryEstudio = $this->getObjectManager()->createQuery("SELECT e.INTERPRETACION,e.REVISION,es.ID as ESTADO FROM CsnUser\Entity\Estudios e JOIN e.ESTADO es WHERE e.ID = $idEstudio");
        $imagenes = $query->getArrayResult();
        $estado = $queryEstudio->getArrayResult();
        return new ViewModel(array('idPaciente' => $idPaciente,'idEstudio' => $idEstudio,'imagenes' => $imagenes,'estado' => $estado));
      }
    }
    
    public function recibirAction() {
      if($this->request->isPost()){
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $estudioimagenes = new Estudioimagenes();
        $data = array_merge_recursive(
                  $this->getRequest()->getPost()->toArray(),           
                  $this->getRequest()->getFiles()->toArray()
                );
        $estudioimagenes->setImagen($data['file']['name']);
        $estudioimagenes->setEstudio($objectManager->find('CsnUser\Entity\Estudios',$data['ESTUDIO']));
        $estudioimagenes->setPaciente($objectManager->find('CsnUser\Entity\Pacientes',$data['PACIENTE']));
        $estudioimagenes->setMedico($objectManager->find('CsnUser\Entity\User',$this->identity()->getId()));
        $ruta = dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/public/imagenes/Estudios/'.$data['ESTUDIO'];
        if (!file_exists($ruta)) mkdir($ruta);
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setDestination($ruta);
        if($adapter->receive($data['file']['name'])){
          $this->createthumb($ruta.'/'.$data['file']['name'],100,100);
          $objectManager->persist($estudioimagenes);
          $objectManager->flush();
        }
      }
        return new JsonModel();
    }
    public function recibirrecetaAction() {
      if($this->request->isPost()){
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $estudioreceta = new EstudioReceta();
        $data = array_merge_recursive(
                  $this->getRequest()->getPost()->toArray(),           
                  $this->getRequest()->getFiles()->toArray()
                );
        $estudioreceta->setImagen($data['file']['name']);
        $estudioreceta->setEstudio($objectManager->find('CsnUser\Entity\Estudios',$data['ESTUDIO']));
        $estudioreceta->setPaciente($objectManager->find('CsnUser\Entity\Pacientes',$data['PACIENTE']));
        $estudioreceta->setUsuario($objectManager->find('CsnUser\Entity\User',$this->identity()->getId()));
        $ruta = dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/public/imagenes/Estudios/'.$data['ESTUDIO'];
        if (!file_exists($ruta)) mkdir($ruta);
        $ruta = $ruta.'/Receta';
        if (!file_exists($ruta)) mkdir($ruta);
        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setDestination($ruta);
        if($adapter->receive($data['file']['name'])){
          $this->createthumb($ruta.'/'.$data['file']['name'],100,100);
          $objectManager->persist($estudioreceta);
          $objectManager->flush();
        }
      }
        return new JsonModel();
    }

    public function guardarinforecetaAction() {

      if ($this->request->isPost())  {
        $objectManager = $this->getObjectManager();
        $idEstudio = $this->request->getPost('estudio');
        $fechaReceta = $this->request->getPost('fechareceta');
        $folioReceta = $this->request->getPost('folioreceta');
        $estudio = $objectManager->find('CsnUser\Entity\Estudios', $idEstudio);
        $estudio->setFECHARECETA($fechaReceta);
        $estudio->setFOLIORECETA($folioReceta);
        $estudio->setPDFTOKEN('asda012090');
        $objectManager->merge($estudio);
        $objectManager->flush();
        return new JsonModel(array('resultado' => 1));
      }
    }

    public function removerAction() {
      if($this->request->isPost()){
        $archivo = $this->request->getPost('archivo');
        $idEstudio = $this->request->getPost('estudio');
        $ruta = dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/public/imagenes/Estudios/'.$idEstudio.'/'.$archivo;
        unlink($ruta.$archivo);
        return new JsonModel();
      }
    }
    public function categoriasAction() {
      return new ViewModel();
    }
    public function listacategoriasAction() {
      $this->layout('layout/vacio');
      $objectManager = $this->getObjectManager();
      $query = $objectManager->createQuery("SELECT t,d.NOMBREDEPENDENCIA FROM CsnUser\Entity\Tiposestudio t JOIN t.DEPENDENCIA d");
      $categorias = $query->getArrayResult();
      return new ViewModel(array('categorias' => $categorias));
    }
    public function categorianuevaAction() {
      $objectManager = $this->getObjectManager();
      $this->layout('layout/vacio');
      $query = $objectManager->createQuery("SELECT m FROM CsnUser\Entity\Inventario m");
      $materiales = $query->getArrayResult();
      $form = new CategoriasForm($objectManager);
      $id = $this->params()->fromRoute('id');
      if($id) {
        $queryCategoria = $objectManager->createQuery("SELECT t,d.ID as DEPENDENCIA FROM CsnUser\Entity\Tiposestudio t JOIN t.DEPENDENCIA d WHERE t.ID = $id");
        $categoria = $queryCategoria->getArrayResult();
        $form->get('NOMBRECATEGORIA')->setAttributes(array('value' => $categoria[0][0]['NOMBRECATEGORIA']));
        $form->get('COSTO')->setAttributes(array('value' => $categoria[0][0]['COSTO']));
        $form->get('DEPENDENCIA')->setAttributes(array('value' => $categoria[0]['DEPENDENCIA']));
        $form->get('ID')->setAttributes(array('value' => $categoria[0][0]['ID']));
        $form->get('ACTIVO')->setAttributes(array('checked' => $categoria[0][0]['ACTIVO']));
        $queryMateriales = $objectManager->createQuery("SELECT ie.CANTIDAD,i.MATERIAL,i.ID FROM CsnUser\Entity\Inventarioestudio ie JOIN ie.CATEGORIA e JOIN ie.INVENTARIO i WHERE e.ID = $id");
        $listaMateriales = $queryMateriales->getArrayResult();
      }
      if($this->request->isPost()){
        $materialNecesario = $this->request->getPost('MATERIALES_ARRAY');
        if($this->request->getPost('ID')) {
          $Tiposestudio = new Tiposestudio();
          $Tiposestudio->setID($this->request->getPost('ID'));
          $Tiposestudio->setNOMBRECATEGORIA($this->request->getPost('NOMBRECATEGORIA'));
          $Tiposestudio->setCOSTO($this->request->getPost('COSTO'));
          $Tiposestudio->setACTIVO($this->request->getPost('ACTIVO'));
          $Tiposestudio->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('DEPENDENCIA')));
          $objectManager->merge($Tiposestudio);
          $objectManager->flush();  
        } else {
          $Tiposestudio = new Tiposestudio();
          $Tiposestudio->setNOMBRECATEGORIA($this->request->getPost('NOMBRECATEGORIA'));
          $Tiposestudio->setCOSTO($this->request->getPost('COSTO'));
          $Tiposestudio->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('DEPENDENCIA')));
          $objectManager->persist($Tiposestudio);
          $objectManager->flush();  
        }
        foreach (json_decode($materialNecesario,true) as $mat) {
          if($this->request->getPost('ID')) {
            $Inventarioestudio = new Inventarioestudio();
            $Inventarioestudio->setINVENTARIO($objectManager->find('CsnUser\Entity\Inventario',$mat['material']));
            $Inventarioestudio->setCATEGORIA($objectManager->find('CsnUser\Entity\Tiposestudio',$this->request->getPost('ID')));
            $Inventarioestudio->setCANTIDAD($mat['cantidad']);
            $objectManager->merge($Inventarioestudio);
            $objectManager->flush();
          } else {
            $Inventarioestudio = new Inventarioestudio();
            $Inventarioestudio->setINVENTARIO($objectManager->find('CsnUser\Entity\Inventario',$mat['material']));
            $Inventarioestudio->setCATEGORIA($objectManager->find('CsnUser\Entity\Tiposestudio',$Tiposestudio->getID()));
            $Inventarioestudio->setCANTIDAD($mat['cantidad']);
            $objectManager->persist($Inventarioestudio);
            $objectManager->flush();
          }
        } 
        return new JsonModel();
      } else {
        return new ViewModel(array('form' => $form,'materiales' => $materiales, 'listaMateriales' => $listaMateriales));
      }
    }

    public function borrarmatnecesarioAction() {
      if($this->request->isPost()){
        $objectManager = $this->getObjectManager();
        $Inventarioestudio = new Inventarioestudio();
        $Inventarioestudio->setINVENTARIO($objectManager->find('CsnUser\Entity\Inventario',$this->request->getPost('idMat')));
        $Inventarioestudio->setCATEGORIA($objectManager->find('CsnUser\Entity\Tiposestudio',$this->request->getPost('idEst')));
        $objectManager->remove($objectManager->merge($Inventarioestudio));
        $objectManager->flush();
        return new JsonModel();  
      }
      
    }
    public function ModificarcostosAction()
    {
      if($this->request->isPost()) {
        $om = $this->getObjectManager();
        $porcentaje = $this->request->getPost('porcentaje');
        $opcion = $this->request->getPost('opcion');
        if($opcion == 1) {
          $multiplicador = 1+($porcentaje/100);  
        } else {
          $multiplicador = 1-($porcentaje/100);  
        }
        $queryCosto = $om->createQuery("UPDATE CsnUser\Entity\Tiposestudio t SET t.COSTO = t.COSTO * $multiplicador WHERE t.ID > 0");
        $p = $queryCosto->execute();
        return new JsonModel();
      }
    }

    public function nuevoestudioAction() {
      $objectManager = $this->getObjectManager();
      $this->layout('layout/vacio');
      $form = new EstudiosForm($objectManager);
      $idEstudio = $this->params()->fromRoute('id');
      if($idEstudio) {
        $queryEstudio = $objectManager->createQuery('SELECT e,de.ID as DEPE,do.id as DOCTOR,p.ID as PACIENTE,p.NOMBRE,p.APELLIDO_PATERNO,p.APELLIDO_MATERNO,t,d.ID as DOCTORENV
         FROM CsnUser\Entity\Estudios e LEFT JOIN e.DEPENDENCIA de LEFT JOIN e.DOCTOR do LEFT JOIN e.PACIENTE p LEFT JOIN e.DOCTORENV d LEFT JOIN e.TIPOS t where e.ID = '.$idEstudio);
        //'SELECT e.ID,p.NOMBRE,p.APELLIDO_PATERNO,p.APELLIDO_MATERNO,do.id as DOCTOR,d.NOMBRE,d.APELLIDO1,d.APELLIDO2,t,e.REVISION FROM CsnUser\Entity\Estudios e LEFT JOIN e.PACIENTE p LEFT JOIN e.DOCTORENV d LEFT JOIN e.TIPOS t LEFT JOIN e.DOCTOR do WHERE e.ID = '.$idEstudio
        $Estudioresult = $queryEstudio->getArrayResult();
        $tipos = array();
        foreach ($Estudioresult[0][0]['TIPOS'] as $tipo) {
            $tipos[] = $tipo['ID'];
        }
        $form->get('TIPOS[]')->setValueOptions($form->getCategoriasData($Estudioresult[0]['DEPE']));  
        $form->get('DOCTOR')->setAttribute('value',$Estudioresult[0]['DOCTOR']);
        $form->get('PACIENTE')->setAttribute('value',$Estudioresult[0]['PACIENTE']);
        $form->get('DOCTORENV')->setAttribute('value',$Estudioresult[0]['DOCTORENV']);
        $form->get('FECHA')->setAttribute('value',$Estudioresult[0][0]['FECHA']);
        $form->get('DEPENDENCIA')->setAttribute('value',$Estudioresult[0]['DEPE']);
        $form->get('TIPOS[]')->setAttribute('value',$tipos);
        $form->get('ID')->setAttribute('value',$Estudioresult[0][0]['ID']);
        $return = array('form' => $form,'arreglo' => $Estudioresult,'depen' => $Estudioresult[0]['DEPE'],'nompaciente' => $Estudioresult[0]['NOMBRE'].' '.$Estudioresult[0]['APELLIDO_PATERNO'].' '.$Estudioresult[0]['APELLIDO_MATERNO']);
      } else {
        $return = array('form' => $form,'arreglo' => $Estudioresult);
      }
      if($this->request->isPost()) {
        $idEstudio = $this->request->getPost('ID');
        if($idEstudio){
          $Estudio = $objectManager->find('CsnUser\Entity\Estudios', $idEstudio);
        } else {
          $Estudio = new Estudios();
        }
        $Estudio->setUSUARIO($objectManager->find('CsnUser\Entity\User',$this->identity()->getId()));
        $Estudio->setPACIENTE($objectManager->find('CsnUser\Entity\Pacientes',$this->request->getPost('PACIENTE')));
        $Estudio->setDOCTORENV($objectManager->find('CsnUser\Entity\Doctores',$this->request->getPost('DOCTORENV')));
        $Estudio->setFECHA(new \DateTime($this->request->getPost('FECHA')));
        $Estudio->setDOCTOR($objectManager->find('CsnUser\Entity\User',1));
        $Estudio->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('DEPENDENCIA')));
        $Estudio->setESTADO($objectManager->find('CsnUser\Entity\Estadosestudios', 1));
        $Estudio->setREVISION($this->request->getPost('REVISION'));
        if($idEstudio) {
          if ($this->request->getPost('TIPOS')) {
            foreach ($this->request->getPost('TIPOS') as $tipo) {
              if (!$Estudio->getTipos()->contains($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]))) {
                $Estudio->addTIPO($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]));
              }
            } 
          } 
        } else {
          foreach ($this->request->getPost('TIPOS') as $tipo) {
            $Estudio->addTIPO($objectManager->find('CsnUser\Entity\Tiposestudio',$tipo[0]));
          }
        }
        
        $idEst = $this->request->getPost('ID');
        if($idEst) {
          $Estudio->setId($idEst);
          $objectManager->merge($Estudio);  
        } else {
          $objectManager->persist($Estudio);
        }
        $objectManager->flush();
        return new JsonModel();
      } else {
        return new ViewModel($return);
      }
    }
    public function generarestudioAction() {
      $objectManager = $this->getObjectManager();
      if($this->request->isPost()){
        $materialUtilizado = $this->request->getPost('materialesarray');
        $idEstudio = $this->request->getPost('estudio');
        foreach (json_decode($materialUtilizado,true) as $mat) {
          $EstudioMaterial = new EstudioMaterial();
          $EstudioMaterial->setESTUDIO($objectManager->find('CsnUser\Entity\Estudios',$idEstudio));
          $EstudioMaterial->setINVENTARIO($objectManager->find('CsnUser\Entity\Inventario',$mat['material']));
          $EstudioMaterial->setCATEGORIA($objectManager->find('CsnUser\Entity\Tiposestudio',$mat['categoria']));
          $EstudioMaterial->setCANTIDAD($mat['cantidad']);
          $objectManager->persist($EstudioMaterial);
          $objectManager->flush();
          $inventario = $objectManager->find('CsnUser\Entity\Inventario',$mat['material']);
          $cantidadactual = $inventario->getCANTIDAD();
          $cantidadactual = $cantidadactual-$mat['cantidad'];
          $inventario->setCANTIDAD($cantidadactual);
          $objectManager->merge($inventario);
          $objectManager->flush();

        }
        $Estudio = $objectManager->find('CsnUser\Entity\Estudios',$idEstudio);
        if($Estudio->getREVISION() == false) {
          $Estudio->setESTADO($objectManager->find('CsnUser\Entity\Estadosestudios', 3));  
        } else {
          $Estudio->setESTADO($objectManager->find('CsnUser\Entity\Estadosestudios', 2));  
        }
        $objectManager->merge($Estudio);
        $objectManager->flush();
        return new JsonModel();
      }
    }

    public function abrirestudioAction() {
      $om = $this->getObjectManager();
      $id = $this->request->getPost('estudio');
      if($this->request->getPost()) {
        $Estudio = $om->find("CsnUser\Entity\Estudios",$id);
        $Estudio->setESTADO($om->find('CsnUser\Entity\Estadosestudios', 2));
        $Estudio->setREVISION(true);
        $om->merge($Estudio);
        $om->flush();
        return new JsonModel();
      }
    }

    public function nuevodoctorAction() {
      $objectManager = $this->getObjectManager();
      $this->layout('layout/vacio');
      $form = new DoctorForm($objectManager);
      $return = array('form' => $form);
      if($this->request->isPost()) {
        $Doctor = new Doctores();
        $Doctor->setESPECIALIDAD($objectManager->find('CsnUser\Entity\Especialidades',$this->request->getPost('ESPECIALIDAD')));
        $Doctor->setDEPENDENCIA($objectManager->find('CsnUser\Entity\Dependencias',$this->request->getPost('DEPENDENCIA')));
        $Doctor->setNOMBRE($this->request->getPost('NOMBRE'));
        $Doctor->setAPELLIDO1($this->request->getPost('APELLIDO1'));
        $Doctor->setAPELLIDO2($this->request->getPost('APELLIDO2'));
        $Doctor->setEMAIL($this->request->getPost('EMAIL')); 
        $Doctor->setTELEFONO($this->request->getPost('TELEFONO'));  
        $objectManager->persist($Doctor);
        $objectManager->flush();
        return new JsonModel(array('idDoctor' => $Doctor->getID()));
      } else {
        return new ViewModel($return);
      }
    }

    public function interpretacionAction() {
      if ($this->request->isPost()) {
        $this->layout('layout/vacio');
        $idEstudio = $this->request->getPost('estudio');
        $interpretacion = $this->request->getPost('inter');
        $objectManager = $this->getObjectManager();
        $estudio = $objectManager->find('CsnUser\Entity\Estudios',$idEstudio);
        $estudio->setINTERPRETACION($interpretacion);
        $estudio->setESTADO($objectManager->find('CsnUser\Entity\Estadosestudios',3));
        $estudio->setPDFTOKEN(md5(uniqid(mt_rand(), true)));
        $nombrePaciente = $estudio->getPACIENTE()->getNOMBRE().' '.$estudio->getPACIENTE()->getAPELLIDO_PATERNO().' '.$estudio->getPACIENTE()->getAPELLIDO_MATERNO();
        $emailPaciente = $estudio->getPACIENTE()->getEMAIL();
        $objectManager->merge($estudio);
        $objectManager->flush();
        //$this->pdf($idEstudio,$nombrePaciente,$emailPaciente); 
        return new JsonModel();
      }
     
    }
    public function entregarAction() {
      if($this->request->isPost()) {
        $objectManager = $this->getObjectManager();
        $this->layout('layout/vacio');
        $idEstudio = $this->request->getPost('estudio');
        $estudio = $objectManager->find('CsnUser\Entity\Estudios',$idEstudio);
        $estudio->setESTADO($objectManager->find('CsnUser\Entity\Estadosestudios',4)); 
        $objectManager->merge($estudio);
        $objectManager->flush();
        return new JsonModel();
      }
    }
    public function pacientesAction() {
      $rsm = new ResultSetMapping();
      $rsm->addEntityResult('CsnUser\Entity\Pacientes', 'p');
      $rsm->addFieldResult('p','ID','ID');
      $rsm->addFieldResult('p','NOMBRE','NOMBRE');
      $rsm->addFieldResult('p','APELLIDO_PATERNO','APELLIDO_PATERNO');
      $rsm->addFieldResult('p','APELLIDO_MATERNO','APELLIDO_MATERNO');
      
      $rsm->addFieldResult('p','FECHA_NACIMIENTO','FECHA_NACIMIENTO');
      $dato = $this->getRequest()->getQuery('term');
      $query = $this->getObjectManager()->createNativeQuery("SELECT p.* FROM Pacientes p WHERE CONCAT(p.NOMBRE,' ',p.APELLIDO_PATERNO,' ',p.APELLIDO_MATERNO) like '%$dato%'", $rsm);
      $pacientes = $query->getArrayResult();
      foreach($pacientes as $pac) {
        $json[] = array('id' => $pac['ID'],'label' => $pac['NOMBRE'].' '.$pac['APELLIDO_PATERNO'].' '.$pac['APELLIDO_MATERNO'].' ['.date_format($pac['FECHA_NACIMIENTO'],'Y/m/d').']',
        'value' => $pac['NOMBRE'].' '.$pac['APELLIDO_PATERNO'].' '.$pac['APELLIDO_MATERNO'],
        'name' => 'pac'.$pac['ID']);
      }
      $resultado = new JsonModel($json);
      return $resultado;
    }
    public function doctoresAction() {
      $rsm = new ResultSetMappingBuilder($this->getObjectManager());
      $rsm->addRootEntityFromClassMetadata('CsnUser\Entity\Doctores', 'd');
      $rsm->addJoinedEntityFromClassMetadata('CsnUser\Entity\Especialidades', 'e', 'd', 'ESPECIALIDAD', array('ID' => 'ESPECIALIDAD_id'));
      $dato = $this->getRequest()->getQuery('term');
      $query = $this->getObjectManager()
        ->createNativeQuery("SELECT d.*,e.ESPECIALIDAD FROM Doctores d left join Especialidades e 
                              on e.ID = d.ESPECIALIDAD_id 
                              WHERE CONCAT(d.NOMBRE) like '%$dato%'", $rsm);
      $doctores = $query->getArrayResult();
      foreach($doctores as $doc) {
        $json[] = array('id' => $doc['ID'],'label' => $doc['NOMBRE'].' '.$doc['APELLIDO1'].' '.$doc['APELLIDO2'].' ['.$doc['ESPECIALIDAD']['ESPECIALIDAD'].']',
        'value' => $doc['NOMBRE'].' '.$doc['APELLIDO1'].' '.$doc['APELLIDO2'],
        'name' => 'pac'.$doc['ID']);
      }
      $resultado = new JsonModel($json);
      return $resultado;
    }

    public function estudiorecetaAction() {
       if($this->request->isPost()){
        $idPaciente = $this->request->getPost('idPaciente');
        $idEstudio = $this->request->getPost('idEstudio');
        $this->layout($this->vacio);
        $query = $this->getObjectManager()->createQuery("SELECT i FROM CsnUser\Entity\Estudioreceta i WHERE i.PACIENTE = $idPaciente AND i.ESTUDIO = $idEstudio");
        $queryEstudio = $this->getObjectManager()->createQuery("SELECT es.ID as ESTADO FROM CsnUser\Entity\Estudios e JOIN e.ESTADO es WHERE e.ID = $idEstudio");
        $queryPaciente = $this->getObjectManager()->createQuery("SELECT padep,dep.NOMBREDEPENDENCIA FROM CsnUser\Entity\Pacientesdependencias padep JOIN padep.DEPENDENCIA dep WHERE padep.PACIENTE = $idPaciente");
        $imagenes = $query->getArrayResult();
        $estado = $queryEstudio->getArrayResult();
        return new ViewModel(array('idPaciente' => $idPaciente,'idEstudio' => $idEstudio,'imagenes' => $imagenes,'estado' => $estado));
      }
    }

    public function doctoresxAction()
    { 
      $query = $this->getObjectManager()->createQuery("SELECT d FROM CsnUser\Entity\Doctores d");
      $doctores = $query->getArrayResult();
      $resultado = new JsonModel($doctores);
      return $resultado;
    }

    public function estudiopdfAction() {
      //if($this->request->isPost()) {
        //$idEstudio = $this->request->getPost('idEstudio');
        $idEstudio = $this->params()->fromRoute('id');
        $om = $this->getObjectManager();
        $queryEstudio = $om->createQuery("SELECT e,t as tNombre,d.firstName,d.lastName,d.apellidoMaterno as apma,p.NOMBRE,p.APELLIDO_PATERNO,
          p.APELLIDO_MATERNO 
          FROM CsnUser\Entity\Estudios e LEFT JOIN e.DOCTOR d LEFT JOIN e.TIPOS t LEFT JOIN e.PACIENTE p WHERE e.ID = $idEstudio");
        $interpretacion = $queryEstudio->getArrayResult();
        $queryImagenes = $this->getObjectManager()->createQuery("SELECT i FROM CsnUser\Entity\Estudioimagenes i WHERE i.ESTUDIO = $idEstudio");
        $imagenes = $queryImagenes->getArrayResult();
        $pdf = new PdfModel();
        //$pdf->setOption('filename', 'estudio');
        $pdf->setVariables(array(
          'imagenes' => $imagenes,
          'interpretacion' => $interpretacion[0][0]['INTERPRETACION'],
          'doctor' => $interpretacion[0]['firstName'].' '.$interpretacion[0]['lastName'].' '.$interpretacion[0]['apma'], 
          'paciente' => $interpretacion[0]['NOMBRE'].' '.$interpretacion[0]['APELLIDO_PATERNO'].' '.$interpretacion[0]['APELLIDO_MATERNO'], 
          'idEstudio' => $idEstudio,
          'fecha' => $interpretacion[0][0]['FECHA'],
          'categorias' => $interpretacion[0][0]['TIPOS'],
        ));
        
        return $pdf;  
     // }
    }
    public function interpretacionpdfAction() {
      //if($this->request->isPost()) {
        //$idEstudio = $this->request->getPost('idEstudio');
        $this->layout('layout/vacio');
        $idEstudio = $this->params()->fromRoute('id');
        $om = $this->getObjectManager();
        $queryEstudio = $om->createQuery("SELECT e,t as tNombre,d.firstName,d.lastName,d.apellidoMaterno as apma,p.NOMBRE,p.APELLIDO_PATERNO,
          p.APELLIDO_MATERNO 
          FROM CsnUser\Entity\Estudios e LEFT JOIN e.DOCTOR d LEFT JOIN e.TIPOS t LEFT JOIN e.PACIENTE p WHERE e.ID = $idEstudio");
        $interpretacion = $queryEstudio->getArrayResult();
        $pdf = new PdfModel(array(
          'interpretacion' => $interpretacion[0][0]['INTERPRETACION'],
          'doctor' => $interpretacion[0]['firstName'].' '.$interpretacion[0]['lastName'].' '.$interpretacion[0]['apma'], 
          'paciente' => $interpretacion[0]['NOMBRE'].' '.$interpretacion[0]['APELLIDO_PATERNO'].' '.$interpretacion[0]['APELLIDO_MATERNO'], 
          'idEstudio' => $idEstudio,
          'fecha' => $interpretacion[0][0]['FECHA'],
          'categorias' => $interpretacion[0][0]['TIPOS'],
        ));
        //$pdf->setOption('filename', 'estudio');

        
        return $pdf;  
     // }
    }

    private function pdf($id,$nombrePaciente,$nombreDoctor,$emailDoctor,$emailPaciente) {
      $idEstudio = $id;
      $ruta = dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/public/imagenes/Estudios/'.$idEstudio;
      $logo = dirname(dirname(dirname(dirname(dirname(__DIR__))))).'/public/imagenes/Logo_Imedich.png';
      $file_to_save = $ruta.'/Estudio '.$idEstudio.'.pdf';
      if (!file_exists($ruta)) mkdir($ruta);
      if (!file_exists($file_to_save)) {
        $view = $this->forward()->dispatch('Application\Controller\Estudios', array("controller" => "Estudios", "action" => "estudiopdf", 'id' => $idEstudio));
        // Getting renderer
        $renderer = $this->getServiceLocator()->get('ViewManager')->getRenderer();
        // Render
        $html = $renderer->render($view);
        //Clear head plugins cache
        $dompdf = $this->getServiceLocator()->get('DOMPDF');
        $dompdf->load_html($html);
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($file_to_save, $output);
      }
          try {
                $this->enviarEmail(
                    $emailDoctor,
                    $emailPaciente,
                    'IMEDISH: Resultado del estudio del paciente '.$nombrePaciente,
                    'Estimado Dr. '.$nombreDoctor.' le adjunto los resultados del estudio del paciente '.$nombrePaciente.'. Saludos y buen día.',
                    $file_to_save
                );
                    $viewModel = new ViewModel();
                    return $viewModel;
            } catch (\Exception $e) {
                    return $this->getServiceLocator()->get('csnuser_error_view')->createErrorView(
                'Ocurrió un error al enviar el correo electrónico. Inténtelo de nuevo más tarde.',
              $e,
              $this->getOptions()->getDisplayExceptions(),
              $this->getOptions()->getNavMenu()
            );
          }
    }
    private function getOptions()
    {
        if(null === $this->options) {
            $this->options = $this->getServiceLocator()->get('csnuser_module_options');
        }
    
        return $this->options;
    }

    private function getBaseUrl() {
        $uri = $this->getRequest()->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }

    protected function getObjectManager()
    {
          if (!$this->_objectManager) {
              $this->_objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
          }

          return $this->_objectManager;
    }
  
    
    private function enviarEmail($emailDoctor = '',$emailPaciente = '', $asunto = '', $texto = '', $file = '')
    {
        $transport = $this->getServiceLocator()->get('mail.transport');
        $message = new Message(); 
        $text = new \Zend\Mime\Part($texto);
        $text->type = "text/plain";
        $pdf = new \Zend\Mime\Part(fopen($file, 'r'));
        $pdf->type = "applications/pdf";
        $pdf->encoding = 'base64';
        $pdf->filename = 'superpdf.pdf';
        $pdf->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
        $cuerpo = new \Zend\Mime\Message;
        $cuerpo->setParts(array($text,$pdf));
        $message->addTo($emailDoctor)
                ->addCc($emailPaciente)
                ->addFrom($this->getOptions()->getSenderEmailAdress())
                ->setSubject($asunto)
                ->setBody($cuerpo);

        $transport->send($message);
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