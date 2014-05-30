<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 30/05/2014
*
* Classe qui permet de gerer les forms du block sondage
**/
namespace PlaygroundPublishing\Form;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\ServiceManager\ServiceManager;
use PlaygroundCMS\Form\BlockForm;

class PollForm extends BlockForm
{
    /**
    * {@inheritdoc}
    */
    public function __construct($name = null, ServiceManager $sm)
    {
        parent::__construct($name, $sm);
    }

    /**
    * getTemplates : Recuperation des templates
    *
    * @return array $templates
    */
    protected function getTemplates()
    {
        $templatesFiles = array();
        $templates = $this->getServiceManager()->get('playgroundcms_template_service')->getTemplateMapper()->findBy(array('isSystem' => 0, 'blockType' => 'PlaygroundPublishing\Blocks\PollController'));
        foreach ($templates as $template) {
            $templatesFiles[$template->getFile()] = $template->getFile();
        }

        return $templatesFiles;
    } 
}
