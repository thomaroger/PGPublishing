<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 02/06/2014
*
* Classe de service pour l'entite Answer
**/
namespace PlaygroundPublishing\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use Datetime;
use PlaygroundPublishing\Mapper\Answer as AnswerMapper;
use PlaygroundPublishing\Entity\Answer as AnswerEntity;
use PlaygroundCore\Filter\Slugify;


class Answer extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var PlaygroundPublishing\Mapper\Page pageMapper
     */
    protected $answerMapper;

    /**
     * @var Zend\ServiceManager\ServiceManager ServiceManager
     */
    protected $serviceManager;

     /**
     * @var PlaygroundCore\Mapper\Locale localeMapper
     */
    protected $localeMapper;
    

    /**
    * create : Permet de créer une page
    * @param array $data : tableau de données 
    */
    public function create($poll, $data)
    {   
        // Modifier le tableau pour prendre en compte les locales par answers et non les answers par locale
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));
        $title = false;
        foreach ($locales as $locale) {

        }
        return $poll;
    }

    /**
    * edit : Permet d'editer une page
    * @param array $data : tableau de données 
    */
    public function edit($poll, $data, $locales){

        foreach ($locales as $locale) {
            $locale = $locale->getLocale();
            if (!empty($data['poll'][$locale])) {
                if (!empty($data['poll'][$locale]['answer'])) {
                    //var_dump($data['poll'][$locale]['answer']);
                }
            }
        }
        //die;
        return $poll;
    }

    /**
    * checkPage : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkAnswser($data)
    {
        // Valeur par défaut
        // Il faut au moins un titre de renseigner
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));
        $title = false;
        foreach ($locales as $locale) {
            if(!empty($data['poll'][$locale->getLocale()])) {
                if(!empty($data['poll'][$locale->getLocale()]['answer'])) {

                    if(count($data['poll'][$locale->getLocale()]['answer']) == 1) {

                        return array('status' => 1, 'message' => 'You must have an answer for this poll', 'data' => $data);
                    }
                    foreach ($data['poll'][$locale->getLocale()]['answer'] as $key => $value) {
                        if ($key != 0 && empty($value)) {

                            return array('status' => 1, 'message' => 'Answer is required', 'data' => $data);
                        } 
                    }
                }
            }
        }

        return array('status' => 0, 'message' => '', 'data' => $data);
    }

    /**
     * getArticleMapper : Getter pour articleMapper
     *
     * @return PlaygroundPublishing\Mapper\Article $articleMapper
     */
    public function getAnswerMapper()
    {
        if (null === $this->answerMapper) {
            $this->answerMapper = $this->getServiceManager()->get('playgroundpublishing_answer_mapper');
        }

        return $this->answerMapper;
    }

    /**
     * getLocaleMapper : Getter pour localeMapper
     *
     * @return PlaygroundCore\Mapper\Locale $localeMapper
     */
    public function getLocaleMapper()
    {
        if (null === $this->localeMapper) {
            $this->localeMapper = $this->getServiceManager()->get('playgroundcore_locale_mapper');
        }

        return $this->localeMapper;
    }

    /**
     * getServiceManager : Getter pour serviceManager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

     /**
     * setServiceManager : Setter pour le serviceManager
     * @param  ServiceManager $serviceManager
     *
     * @return Page
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}