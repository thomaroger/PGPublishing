<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 25/03/2014
*
* Classe de controleur de back pour la gestion des sondages
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundCMS\Security\Credential;
use PlaygroundPublishing\Entity\Poll;

class PollController extends AbstractActionController
{
    /**
    * @var MAX_PER_PAGE  Nombre d'item par page
    */
    const MAX_PER_PAGE = 20;

    /**
    * @var Service $pageService Service de page
    */
    protected $pollService;

    /**
    * @var Service $pageService Service de page
    */
    protected $answerService;

    /**
    * @var Ressource $ressourceService  Service de ressource
    */
    protected $ressourceService;
    
    /**
    * @var Layout $layoutService  Service de layout
    */
    protected $layoutService;
    
    /**
    * @var Locale $localeService  Service de locale
    */
    protected $localeService;
    /**
    * @var RevisionService revisionService  Service de Revision
    */
    protected $revisionService;
    
    /**
    * indexAction : Liste des articles
    *
    * @return ViewModel $viewModel 
    */
    public function listAction()
    {
        $pollsId = array();
        $ressourcesCollection = array();
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "poll");
        $p = $this->getRequest()->getQuery('page', 1);

        $polls = $this->getPollService()->getPollMapper()->findAll();
        
        $nbPolls = count($polls);

        $pollsPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($polls));
        $pollsPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $pollsPaginator->setCurrentPageNumber($p);

        foreach ($polls as $poll) {
            $pollsId[] = $poll->getId();
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Poll', 'recordId' => $pollsId));
        foreach ($ressources as $ressource) {
            $ressourcesCollection[$ressource->getRecordId()][$ressource->getLocale()] = $ressource;
        }

        $credentials = Credential::$statusesForm;
        $pollsStatuses = Poll::$statuses;

        $files = $this->getLayoutService()->getLayouts();

        return new ViewModel(array('polls'                => $polls,
                                   'pollsPaginator'       => $pollsPaginator,
                                   'nbPolls'              => $nbPolls,
                                   'files'                => $files,
                                   'credentials'          => $credentials,
                                   'pollsStatuses'        => $pollsStatuses,
                                   'ressourcesCollection' => $ressourcesCollection));
    }

    /**
    * createAction : Creation de page
    *
    * @return ViewModel $viewModel 
    */
    public function createAction()
    {
        $return  = array();
        $data = array();
        
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "poll");

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );
            $return = $this->getPollService()->checkPoll($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getPollService()->create($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/polls');
            }
        }

        $credentials = Credential::$statusesForm;
        $pollsStatuses = Poll::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('credentials'      => $credentials,
                                   'pollsStatuses' => $pollsStatuses,
                                   'layouts'          => $layouts,
                                   'locales'          => $locales,
                                   'data'             => $data,
                                   'return'           => $return));
    }

    /**
    * editAction : Edition d'une page en fonction d'un id
    * @param int $id : Id de la page
    *
    * @return ViewModel $viewModel 
    */
    public function editAction()
    {
        $return  = array();
        $data = array();

        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "poll");
        
        $request = $this->getRequest();

        $pollId = $this->getEvent()->getRouteMatch()->getParam('id');
        $revisionId = $this->getEvent()->getRouteMatch()->getParam('revisionId', 0);

        $poll = $this->getPollService()->getPollMapper()->findById($pollId);

        $filters = array('type' => get_class($poll), 'objectId' => $poll->getId());
        $revisions = $this->getRevisionService()->getRevisionMapper()->findByAndOrderBy($filters, array('id' => 'DESC'));

        if(empty($poll)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/polls');
        }

        $translations = $this->getPollService()->getPollMapper()->getEntityRepositoryForEntity($poll->getTranslationRepository())->findTranslations($poll);
        $poll->setTranslations($translations);
        $answers = $poll->getAnswers();
        

        if(!empty($revisionId)){
            $revision = $this->getRevisionService()->getRevisionMapper()->findById($revisionId);
            $poll = unserialize($revision->getObject());
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Poll', 'recordId' => $pollId));
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );
            $return = $this->getPollService()->checkPoll($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getPollService()->edit($data);
                
                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/polls');
            }
        }

        $credentials = Credential::$statusesForm;
        $pollsStatuses = Poll::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        foreach ($answers as $answer) {
            $translations = $this->getAnswerService()->getAnswerMapper()->getEntityRepositoryForEntity($answer->getTranslationRepository())->findTranslations($answer);
            $answer->setTranslations($translations);
        }

        return new ViewModel(array('credentials'      => $credentials,
                                   'pollsStatuses'    => $pollsStatuses,
                                   'layouts'          => $layouts,
                                   'locales'          => $locales,
                                   'poll'             => $poll,
                                   'answers'          => $answers,
                                   'ressources'       => $ressources,
                                   'revisions'        => $revisions,
                                   'return'           => $return));
    }

    /**
    * removeAction : Edition d'une page en fonction d'un id
    * @param int $id : Id de la page
    *
    * @return ViewModel $viewModel 
    */
    
    public function removeAction()
    {
        $pollId = $this->getEvent()->getRouteMatch()->getParam('id');
        $poll = $this->getArticleService()->getArticleMapper()->findById($pollId);

        if(empty($poll)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/polls');
        }

        // Suppression des ressources associées 
        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Poll', 'recordId' => $pollId));
        foreach ($ressources as $ressource) {
            $this->getRessourceService()->getRessourceMapper()->remove($ressource);
        }

        // remove answers
        $this->getPollService()->getPollMapper()->remove($poll);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/polls');
    }


    /**
    * getTagService : Recuperation du service de tag
    *
    * @return Tag $tagService 
    */
    private function getPollService()
    {
        if (!$this->pollService) {
            $this->pollService = $this->getServiceLocator()->get('playgroundpublishing_poll_service');
        }

        return $this->pollService;
    }

    /**
    * getTagService : Recuperation du service de tag
    *
    * @return Tag $tagService 
    */
    private function getAnswerService()
    {
        if (!$this->answerService) {
            $this->answerService = $this->getServiceLocator()->get('playgroundpublishing_answer_service');
        }

        return $this->answerService;
    }

    /**
    * getLocaleService : Recuperation du service de locale
    *
    * @return Locale $localeService 
    */
    private function getLocaleService()
    {
        if (!$this->localeService) {
            $this->localeService = $this->getServiceLocator()->get('playgroundcore_locale_service');
        }

        return $this->localeService;
    }

    /**
    * getLayoutService : Recuperation du service de Layout
    *
    * @return Layout $layoutService 
    */
    private function getLayoutService()
    {
        if (!$this->layoutService) {
            $this->layoutService = $this->getServiceLocator()->get('playgroundcms_layout_service');
        }

        return $this->layoutService;
    }

    /**
    * getRessourceService : Recuperation du service de Ressource
    *
    * @return Ressource $ressourceService 
    */
    private function getRessourceService()
    {
        if (!$this->ressourceService) {
            $this->ressourceService = $this->getServiceLocator()->get('playgroundcms_ressource_service');
        }

        return $this->ressourceService;
    }

    /**
     * getRevisionService : Recuperation du service de revision
     *
     * @return RevisionService $revisionService : revisionService
     */
    private function getRevisionService()
    {
        if (null === $this->revisionService) {
            $this->setRevisionService($this->getServiceLocator()->get('playgroundcms_revision_service'));
        }

        return $this->revisionService;
    }

    /**
     * setRevisionService : Setter du service de revision
     * @param  RevisionService $revisionService
     *
     * @return PollController $this
     */
    private function setRevisionService($revisionService)
    {
        $this->revisionService = $revisionService;

        return $this;
    }
}
