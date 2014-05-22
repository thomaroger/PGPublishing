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

class ArticleTagListForm extends BlockForm
{
    /**
    * {@inheritdoc}
    */
    public function __construct($name = null, ServiceManager $sm)
    {
        parent::__construct($name, $sm);

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'configuration[tag]',
            'options' => array(
                'label' => 'Choose a tag to filter',
                'value_options' => $this->getTags(),
                'empty_option' => 'Choose a tag to filter',
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

        $this->add(array(
            'type' => 'Zend\Form\Element\Radio',
            'name' => 'configuration[current_entity]',
            'options' => array(
                'label' => 'Use the current entity ?',
                'label_attributes' => array(
                    'class'  => 'control-label'
                ),
                'value_options' => array(
                     '0' => 'No',
                     '1' => 'Yes',
                ),
            ),
            'attributes' => array(
                'class' => "icheck form-control",
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'configuration[sort][field]',
            'options' => array(
                'label' => 'Column',
                'empty_option' => 'Choose the column to sort',
                'value_options' => $this->getSupportedSorts(),
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

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'configuration[sort][direction]',
            'options' => array(
                'label' => 'Value',
                'empty_option' => 'Choose a direction',
                'value_options' => $this->getDirection(),
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

        $this->add(array(
            'name' => 'configuration[pagination][max_per_page]',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Max per page',
                'label_attributes' => array(
                    'class'  => 'control-label'
                ),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));

        $this->add(array(
            'name' => 'configuration[pagination][limit]',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Limit',
                'label_attributes' => array(
                    'class'  => 'control-label'
                ),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'form-control',
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));
    }

    private function getTags()
    {
        $tagsArray = array();
        $tags = $this->getServiceManager()->get('playgroundpublishing_tag_mapper')->findAll();
        foreach ($tags as $tag) {
            $tagsArray[$tag->getId()] = $tag->getTitle();
        }
        
        return $tagsArray;
    }

    /**
    * getSupportedSorts : Recuperation des sorts supportées par les articles
    *
    * @return array $sortsArray
    */
    private function getSupportedSorts() 
    {
        $sortsArray = array();
        $sorts = array_keys($this->getServiceManager()->get('playgroundpublishing_article_mapper')->getSupportedSorts());
        foreach ($sorts as $sort) {
            $sortsArray[$sort] = $sort;
        }

        return $sortsArray;
    }

    /**
    * {@inheritdoc}
    * setDate : Setter des données spécifique du block dans le form
    */
    public function setData($data)
    {
        parent::setData($data);

        if (!is_array($data)) {

            $categoryId = $data->getParam('tag');
            $currentEntity = $data->getParam('current_entity');
            $sort = $data->getParam('sort');
            $pagination = $data->getParam('pagination');

            if(!empty($categoryId)) {
                $this->get('configuration[tag]')->setValue($categoryId);
            }
            if(!empty($currentEntity)) {
                $this->get('configuration[current_entity]')->setValue($currentEntity);
            }
            if(!empty($sort)) {
                $this->get('configuration[sort][field]')->setValue($sort['field']);
                $this->get('configuration[sort][direction]')->setValue($sort['direction']);
            }
            if(!empty($pagination)) {
                $this->get('configuration[pagination][max_per_page]')->setValue($pagination['max_per_page']);
                $this->get('configuration[pagination][limit]')->setValue($pagination['limit']);
            }
        } else {
            if (!empty($data['configuration']['tag'])) {
                $this->get('configuration[tag]')->setValue($data['configuration']['tag']);
            }
            if (!empty($data['configuration']['current_entity'])) {
                $this->get('configuration[current_entity]')->setValue($data['configuration']['current_entity']);
            }
            if (!empty($data['configuration']['sort']['field'])) {
                $this->get('configuration[sort][field]')->setValue($data['configuration']['sort']['field']);
            }
            if (!empty($data['configuration']['sort']['direction'])) {
                $this->get('configuration[sort][direction]')->setValue($data['configuration']['sort']['direction']);
            }
            if (!empty($data['configuration']['pagination']['max_per_page'])) {
                $this->get('configuration[pagination][max_per_page]')->setValue($data['configuration']['pagination']['max_per_page']);
            }
            if (!empty($data['configuration']['pagination']['limit'])) {
                $this->get('configuration[pagination][limit]')->setValue($data['configuration']['pagination']['limit']);
            }
        }

    }

    /**
    * {@inheritdoc}
    * getConfiguration : Définit les champs spécifiques du bloc
    */
    public function getConfiguration()
    {
        return array('configuration[tag]',
                    'configuration[current_entity]',
                    'configuration[sort][field]',
                    'configuration[sort][direction]',
                    'configuration[pagination][max_per_page]',
                    'configuration[pagination][limit]'
                    );
    }

    /**
    * {@inheritdoc}
    * decorateSpecificConfguration : Modifit la configuration du bloc avant mise en base
    */
    public function decorateSpecificConfguration($data)
    {
        $configuration = array();
        
        if (!empty($data['configuration']['current_entity'])){
            $configuration['current_entity'] = $data['configuration']['current_entity'];

        }
        if (!empty($data['configuration']['tag'])){
            $configuration['tag'] = $data['configuration']['tag'];
        }
        
        if (!empty($data['configuration']['sort']['field']) && !empty($data['configuration']['sort']['direction'])) {
            $configuration['sort'] = $data['configuration']['sort'];
        }

        $configuration['pagination'] = $data['configuration']['pagination'];
        $data['configuration'] = $configuration;

        return $data;
    }
}
