<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 19/05/2014
*
* Classe qui permet de gerer les forms du block article
**/
namespace PlaygroundPublishing\Form;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\ServiceManager\ServiceManager;
use PlaygroundCMS\Form\BlockForm;

class ArticleForm extends BlockForm
{
    /**
    * {@inheritdoc}
    */
    public function __construct($name = null, ServiceManager $sm)
    {
        parent::__construct($name, $sm);
    }
}
