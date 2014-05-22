<?php

namespace PlaygroundPublishing;

use Zend\Mvc\MvcEvent;
use Zend\Validator\AbstractValidator;
use Zend\View\Resolver\TemplateMapResolver;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $translator = $e->getTarget()->getServiceManager()->get('translator');
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

                 // MAPPER
                
                'playgroundpublishing_category_mapper' => function  ($sm) {
                    return new Mapper\Category($sm->get('playgroundpublishing_doctrine_em'), $sm->get('playgroundpublishing_module_options'));
                },

                'playgroundpublishing_tag_mapper' => function  ($sm) {
                    return new Mapper\Tag($sm->get('playgroundpublishing_doctrine_em'), $sm->get('playgroundpublishing_module_options'));
                },

                'playgroundpublishing_article_mapper' => function  ($sm) {
                    return new Mapper\Article($sm->get('playgroundpublishing_doctrine_em'), $sm->get('playgroundpublishing_module_options'));
                },

                'playgroundpublishing-blocks-articleblock-form' => function  ($sm) {
                    $form = new Form\ArticleForm(null, $sm);

                    return $form;
                },

                'playgroundpublishing-blocks-articlecategorylist-form'  => function  ($sm) {
                    $form = new Form\ArticleCategoryListForm(null, $sm);

                    return $form;
                },

                'playgroundpublishing-blocks-articletaglist-form'  => function  ($sm) {
                    $form = new Form\ArticleTagListForm(null, $sm);

                    return $form;
                },

                'playgroundpublishing-blocks-articlelist-form'  => function  ($sm) {
                    $form = new Form\ArticleListForm(null, $sm);

                    return $form;
                },

                'playgroundpublishing-blocks-taglist-form'  => function  ($sm) {
                    $form = new Form\TagListForm(null, $sm);

                    return $form;
                },

                'playgroundpublishing-blocks-categorylist-form'  => function  ($sm) {
                    $form = new Form\CategoryListForm(null, $sm);

                    return $form;
                },
            ),
            'invokables' => array(
                'playgroundpublishing_category_service' => 'PlaygroundPublishing\Service\Category',
                'playgroundpublishing_tag_service'      => 'PlaygroundPublishing\Service\Tag',
                'playgroundpublishing_article_service'  => 'PlaygroundPublishing\Service\Article',

            ),
        );
    }}
