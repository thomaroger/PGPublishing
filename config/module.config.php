<?php

/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 14/05/2014
*
* Configuration pour PlaygroundPublishing
**/

return array(
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'Gedmo\Translatable\TranslatableListener',
                ),
            ),
        ),
        'driver' => array(
            'playgroundpublishing_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => __DIR__ . '/../src/PlaygroundPublishing/Entity'
            ),
            'translatable_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/PlaygroundPublishing/Entity/Translation')
            ),
            
            'orm_default' => array(
                'drivers' => array(
                    'PlaygroundPublishing\Entity'  => 'playgroundpublishing_entity',
                    'Gedmo\Translatable\Entity' => 'translatable_entities'
                )
            )
        )
    ),
   
    'router' => array(
        'routes' => array(   
            // http://dev.pgcms.fr/fr/article/mon-article-1.html Page entity article
            'article' => array(
                'type' => 'PlaygroundCMS\Router\Http\RegexSlash',
                'options' => array(
                  'regex'    => '\/(?<locale>([a-z_]{5}+))\/article\/(?<slugiverse>([\/a-z0-9-]+))-(?<id>([0-9]+)).(?<format>([xml|html|json]+))\/?',
                  'defaults' => array(
                    'controller' => 'PlaygroundPublishing\Controller\Front\Article',
                    'action'     => 'index',
                  ),
                  'spec' => '',
                ),
            'may_terminate' => true,
            ),
            
            'admin' => array(
                'child_routes' => array(
                    'playgroundpublishingadmin' => array(
                        'type' => 'Literal',
                        'priority' => 1000,
                        'options' => array(
                            'route' => '/playgroundpublishing',
                            'defaults' => array(
                                'controller' => 'PlaygroundCMS\Controller\Back\Dashboard',
                                'action' => 'index',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'articles' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/articles[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Article',
                                        'action'     => 'list',
                                    ),
                                ),    
                            ),
                            'article_create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/article/create',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Article',
                                        'action'     => 'create',
                                    ),
                                ), 
                            ),
                            'article_edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/article/edit/:id',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Article',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                ), 
                            ),
                            'article_remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/article/remove/:id',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Article',
                                        'action'     => 'remove',
                                    ),
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                ), 
                            ),

                            'categories' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/categories[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Category',
                                        'action'     => 'list',
                                    ),
                                ),    
                            ),
                            'category_create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/category/create',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Category',
                                        'action'     => 'create',
                                    ),
                                ), 
                            ),
                            'category_edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/category/edit/:id',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Category',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                ), 
                            ),
                            'category_remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/category/remove/:id',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Category',
                                        'action'     => 'remove',
                                    ),
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                ), 
                            ),
                            'tags' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tags[/:filter][/:p]',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Tag',
                                        'action'     => 'list',
                                    ),
                                ),    
                            ),
                            'tag_create' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/tag/create',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Tag',
                                        'action'     => 'create',
                                    ),
                                ), 
                            ),
                            'tag_edit' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tag/edit/:id',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Tag',
                                        'action'     => 'edit',
                                    ),
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                ), 
                            ),
                            'tag_remove' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/tag/remove/:id',
                                    'defaults' => array(
                                        'controller' => 'PlaygroundPublishing\Controller\Back\Tag',
                                        'action'     => 'remove',
                                    ),
                                    'constraints' => array(
                                        'id' => '[0-9]+',
                                    ),
                                ), 
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'PlaygroundPublishing\Controller\Front\Article'        => 'PlaygroundPublishing\Controller\Front\ArticleController',

            'PlaygroundPublishing\Controller\Back\Article' => 'PlaygroundPublishing\Controller\Back\ArticleController',
            'PlaygroundPublishing\Controller\Back\Category' => 'PlaygroundPublishing\Controller\Back\CategoryController',
            'PlaygroundPublishing\Controller\Back\Tag' => 'PlaygroundPublishing\Controller\Back\TagController',
        ),
    ),

    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'         => 'phpArray',
                'base_dir'     => __DIR__ . '/../language',
                'pattern'      => '%s.php',
                'text_domain'  => 'PlaygroundPublishing'
            ),
        ),
    ),
    'blocksType' => array(
        'publishing' => __DIR__.'/../src/PlaygroundPublishing/Blocks', 
    ),
);