<?php

namespace PlaygroundPublishing;

use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;
use Zend\View\Resolver\TemplateMapResolver;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application     = $e->getTarget();
        $serviceManager  = $application->getServiceManager();
        $eventManager    = $application->getEventManager();
        $translator = $serviceManager->get('translator');
        AbstractValidator::setDefaultTranslator($translator,'playgroundpublishing');
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/../../src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'playgroundpublishing_doctrine_em' => 'doctrine.entitymanager.orm_default',
            ),    
            'factories' => array(
                // OPTION 
                'playgroundpublishing_module_options' => function  ($sm) {
                    $config = $sm->get('Configuration');

                    return new Options\ModuleOptions(isset($config['playgroundpublishing']) ? $config['playgroundpublishing'] : array());
                },
            ),
            'invokables' => array(
            ),
        );
    }}
