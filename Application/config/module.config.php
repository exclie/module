<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'CsnUser\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'CsnUser\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'pacientes' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/pacientes[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Pacientes',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
            ),
            'estudios' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/estudios[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Estudios',
                        'action' => 'estudios',
                    ),
                ),
                'may_terminate' => true,
            ),
            'agenda' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/agenda[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Agenda',
                        'action' => 'calendario',
                    ),
                ),
                'may_terminate' => true,
            ),
            'inventario' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/inventario[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Inventario',
                        'action' => 'inventario',
                    ),
                ),
                'may_terminate' => true,
            ),
            'dependencias' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dependencias[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Dependencias',
                        'action' => 'dependencias',
                    ),
                ),
                'may_terminate' => true,
            ),
            'pagos' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/pagos[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Pagos',
                        'action' => 'pagos',
                    ),
                ),
                'may_terminate' => true,
            ),
            'registro' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/registro[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Registro',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'Zend\Authentication\AuthenticationService' => 'CsnUser\Service\Factory\AuthenticationFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'es_ES',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'CsnUser\Controller\Index' => 'CsnUser\Controller\IndexController',
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Pacientes' => 'Application\Controller\PacientesController',
            'Application\Controller\Estudios' => 'Application\Controller\EstudiosController',
            'Application\Controller\Agenda' => 'Application\Controller\AgendaController',
            'Application\Controller\Inventario' => 'Application\Controller\InventarioController',
            'Application\Controller\Dependencias' => 'Application\Controller\DependenciasController',
            'Application\Controller\Pagos' => 'Application\Controller\PagosController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
