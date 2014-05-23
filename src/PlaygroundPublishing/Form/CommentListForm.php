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
use PlaygroundPublishing\Entity\Comment;

class CommentListForm extends BlockForm
{
    /**
    * {@inheritdoc}
    */
    public function __construct($name = null, ServiceManager $sm)
    {
        parent::__construct($name, $sm);


        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'configuration[status]',
            'options' => array(
                'label' => 'State of the comment',
                'value_options' => $this->getCommentStatuses(),
                'label_attributes' => array(
                    'class'  => 'control-label'
                ),
            ),
            'attributes' => array(
                'class' => 'selectpicker show-tick form-control',
                'data-live-search' => "true",
                'data-size' => '3',
                'data-width' => "100%",
            ),
        ));
    }

    public function getCommentStatuses()
    {
        return Comment::$statuses;
    }

    /**
    * getTemplates : Recuperation des templates
    *
    * @return array $templates
    */
    protected function getTemplates()
    {
        $templatesFiles = array();
        $templates = $this->getServiceManager()->get('playgroundcms_template_service')->getTemplateMapper()->findBy(array('isSystem' => 0, 'blockType' => 'PlaygroundPublishing\Blocks\CommentListController'));
        foreach ($templates as $template) {
            $templatesFiles[$template->getFile()] = $template->getFile();
        }

        return $templatesFiles;
    } 

    /**
    * {@inheritdoc}
    * getConfiguration : Définit les champs spécifiques du bloc
    */
    public function getConfiguration()
    {

        return array('configuration[status]');
    }

    /**
    * {@inheritdoc}
    * setDate : Setter des données spécifique du block dans le form
    */
    public function setData($data)
    {
        parent::setData($data);
        
        if (!is_array($data)) {
            $this->get('configuration[status]')->setValue($data->getParam('status'));
        } else {
            if (!empty($data['configuration']['status'])) {
                $this->get('configuration[status]')->setValue($data['configuration']['status']);
            }
        }
    }
}
