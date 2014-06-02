<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 30/03/2014
*
* Classe de service pour l'entite Poll
**/
namespace PlaygroundPublishing\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use Datetime;
use PlaygroundPublishing\Mapper\Poll as PollMapper;
use PlaygroundPublishing\Entity\Poll as PollEntity;
use PlaygroundCore\Filter\Slugify;


class Poll extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var PlaygroundPublishing\Mapper\Page pageMapper
     */
    protected $pollMapper;

    protected $answerService;

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
    public function create($data)
    {

        $poll = new PollEntity();
        $layoutContext = array();

        $poll->setIsWeb(0);
        if ($data['poll']['web']['active'] == 1) {
            $poll->setIsWeb(1);
            $layoutContext['web'] = $data['poll']['web']['layout'];
        }

        $poll->setIsMobile(0);
        if ($data['poll']['mobile']['active'] == 1) {
            $poll->setIsMobile(1);
            $layoutContext['mobile'] = $data['poll']['mobile']['layout'];
        }

        $poll->setStatus(PollEntity::POLL_DRAFT);

        if (!empty($data['poll']['status'])) {
            $poll->setStatus($data['poll']['status']);
        }


        $poll->setAuthor($data['poll']['author']);

        $poll->setLayoutContext(json_encode($layoutContext));
        $poll->setSecurityContext($data['poll']['visibility']);

        $startDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['poll']['start_date']['date'].' '.$data['poll']['start_date']['time']);
        $poll->setStartDate($startDate);
        $endDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['poll']['end_start']['date'].' '.$data['poll']['end_start']['time']);
        $poll->setEndDate($endDate);

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getPollMapper()->getEntityManager()->getRepository($poll->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['poll'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['poll'][$locale->getLocale()]['title']);
                $repository->translate($poll, 'title', $locale->getLocale(), $data['poll'][$locale->getLocale()]['title'])
                        ->translate($poll, 'slug', $locale->getLocale(), $slug)
                        ->translate($poll, 'question', $locale->getLocale(), $data['poll'][$locale->getLocale()]['question'])
                        ->translate($poll, 'titleMeta', $locale->getLocale(), $data['poll'][$locale->getLocale()]['title_seo'])
                        ->translate($poll, 'keywordMeta', $locale->getLocale(), $data['poll'][$locale->getLocale()]['keyword_seo'])
                        ->translate($poll, 'descriptionMeta', $locale->getLocale(), $data['poll'][$locale->getLocale()]['description_seo']); 
               
            }   
        }

        $poll = $this->getAnswserService()->create($poll, $data);

        $poll = $this->getPollMapper()->persist($poll);
        $poll = $this->getPollMapper()->findById($poll->getId());
        
        $poll->createRessource($this->getPollMapper(), $locales);
    }

    /**
    * edit : Permet d'editer une page
    * @param array $data : tableau de données 
    */
    public function edit($data){

        $poll = $this->getPollMapper()->findById($data['poll']['id']);

        $layoutContext = array();

        $poll->setIsWeb(0);
        if ($data['poll']['web']['active'] == 1) {
            $poll->setIsWeb(1);
            $layoutContext['web'] = $data['poll']['web']['layout'];
        }

        $poll->setIsMobile(0);
        if ($data['poll']['mobile']['active'] == 1) {
            $poll->setIsMobile(1);
            $layoutContext['mobile'] = $data['poll']['mobile']['layout'];
        }
        $poll->setStatus(PollEntity::POLL_DRAFT);

        if (!empty($data['poll']['status'])) {
            $poll->setStatus($data['poll']['status']);
        }

        $poll->setLayoutContext(json_encode($layoutContext));
        $poll->setSecurityContext($data['poll']['visibility']);

        $startDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['poll']['start_date']['date'].' '.$data['poll']['start_date']['time']);
        $poll->setStartDate($startDate);
        $endDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['poll']['end_start']['date'].' '.$data['poll']['end_start']['time']);
        $poll->setEndDate($endDate);


        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getPollMapper()->getEntityManager()->getRepository($poll->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['poll'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['poll'][$locale->getLocale()]['title']);
                $repository->translate($poll, 'title', $locale->getLocale(), $data['poll'][$locale->getLocale()]['title'])
                        ->translate($poll, 'slug', $locale->getLocale(), $slug)
                        ->translate($poll, 'question', $locale->getLocale(), $data['poll'][$locale->getLocale()]['question'])
                        ->translate($poll, 'titleMeta', $locale->getLocale(), $data['poll'][$locale->getLocale()]['title_seo'])
                        ->translate($poll, 'keywordMeta', $locale->getLocale(), $data['poll'][$locale->getLocale()]['keyword_seo'])
                        ->translate($poll, 'descriptionMeta', $locale->getLocale(), $data['poll'][$locale->getLocale()]['description_seo']); 
               
            }   
        }

        $poll = $this->getAnswserService()->edit($poll, $data);

        $poll = $this->getPollMapper()->update($poll);
    
        $poll->editRessource($this->getPollMapper(), $locales);
    }

    /**
    * checkPage : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkPoll($data)
    {
        $return = $this->getAnswserService()->checkAnswser($data);
        if ($return['status'] == 1) {

            return $return;
        }

        $data['poll']['status'] = (int) $data['poll']['status'];

        if (empty($data['poll']['start_date']['time'])) {
            $data['poll']['start_date']['time'] = '00:00:00';
        }
        if (empty($data['poll']['end_start']['date'])) {
            $data['poll']['end_start']['date'] = '12/31/2029';
        }

        if (empty($data['poll']['end_start']['time'])) {
            $data['poll']['end_start']['time'] = '23:59:59';
        }
        
        // Il faut au moins un titre de renseigner
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));
        $title = false;
        foreach ($locales as $locale) {
            if(!empty($data['poll'][$locale->getLocale()])) {
                if(!empty($data['poll'][$locale->getLocale()]['title'])){
                    $title = true;

                    if(empty($data['poll'][$locale->getLocale()]['question'])){

                        return array('status' => 1, 'message' => 'question is required', 'data' => $data);

                    }
                }
            }
        }
        if(!$title){
            
            return array('status' => 1, 'message' => 'One of title is required', 'data' => $data);
        }

        // Il faut au moins une plateforme d'activer
        if ($data['poll']['web']['active'] == 0 && $data['poll']['mobile']['active'] == 0) {
            
            return array('status' => 1, 'message' => 'One of platform must be activated', 'data' => $data);
        }

        // Si une plateforme est active, alors il faut un layout
        if ($data['poll']['web']['active'] == 1 && $data['poll']['web']['layout'] == '') {
            
            return array('status' => 1, 'message' => 'For a activate platform, you must have a layout', 'data' => $data);
        }

        // Si une plateforme est active, alors il faut un layout
        if ($data['poll']['mobile']['active'] == 1 && $data['poll']['mobile']['layout'] == '') {
            
            return array('status' => 1, 'message' => 'For a activate platform, you must have a layout', 'data' => $data);
        }

        // Il faut une visibility
        if(empty($data['poll']['visibility'])) {
            
            return array('status' => 1, 'message' => 'Visibility is required', 'data' => $data);  
        }
        
        // il faut un author
        if (empty($data['poll']['author'])) {
            
            return array('status' => 1, 'message' => 'The author is required', 'data' => $data);        
        }

        // Il faut un status
        if ($data['poll']['status'] == -1) {
            
            return array('status' => 1, 'message' => 'The status is required', 'data' => $data);        
        }

        // Il faut une date de debut
        if (empty($data['poll']['start_date']['date'])) {
            
            return array('status' => 1, 'message' => 'The start date is required', 'data' => $data);        
        }

        return array('status' => 0, 'message' => '', 'data' => $data);
    }

    /**
     * getArticleMapper : Getter pour articleMapper
     *
     * @return PlaygroundPublishing\Mapper\Article $articleMapper
     */
    public function getPollMapper()
    {
        if (null === $this->pollMapper) {
            $this->pollMapper = $this->getServiceManager()->get('playgroundpublishing_poll_mapper');
        }

        return $this->pollMapper;
    }

    /**
     * getArticleMapper : Getter pour articleMapper
     *
     * @return PlaygroundPublishing\Mapper\Article $articleMapper
     */
    public function getAnswserService()
    {
        if (null === $this->answerService) {
            $this->answerService = $this->getServiceManager()->get('playgroundpublishing_answer_service');
        }

        return $this->answerService;
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