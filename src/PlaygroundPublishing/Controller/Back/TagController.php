<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 15/05/2014
*
* Classe de controleur de back pour la gestion des tags
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundPublishing\Entity\Tag;
use PlaygroundCMS\Security\Credential;

class TagController extends AbstractActionController
{
    /**
    * @var MAX_PER_PAGE  Nombre d'item par page
    */
    const MAX_PER_PAGE = 20;

    /**
    * @var Tag $tagService Service de Category
    */
    protected $tagService;

    /**
    * @var Locale $localeService  Service de locale
    */
    protected $localeService;

    protected $layoutService;

    protected $ressourceService;
    /**
    * @var RevisionService revisionService  Service de Revision
    */
    protected $revisionService;

 
    /**
    * listAction : Liste des layouts
    *
    * @return ViewModel $viewModel 
    */
    public function listAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "tag");

        $tagId = array();
        $ressourcesCollection = array();

    
        $p = $this->getRequest()->getQuery('page', 1);

        $tags = $this->getTagService()->getTagMapper()->findAll();
        
        $nbTag = count($tags);

        $tagsPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($tags));
        $tagsPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $tagsPaginator->setCurrentPageNumber($p);

        foreach ($tags as $tag) {
            $tagId[] = $tag->getId();
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Tag', 'recordId' => $tagId));
        foreach ($ressources as $ressource) {
            $ressourcesCollection[$ressource->getRecordId()][$ressource->getLocale()] = $ressource;
        }
        
        return new ViewModel(array('tags'                 => $tags,
                                   'tagsPaginator'        => $tagsPaginator,
                                   'ressourcesCollection' => $ressourcesCollection,
                                   'nbTag'                => $nbTag));
    }

    /**
    * createAction : Creation de layout
    *
    * @return ViewModel $viewModel 
    */
    public function createAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "tag");
        $return  = array();
        $data = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );            

            $return = $this->getTagService()->checkTag($data);
            $data = $return["data"];
            unset($return["data"]);
            $data['tag']['status'] = (int) $data['tag']['status'];


            if ($return['status'] == 0) {
                $this->getTagService()->create($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
            }
        }

        $credentials = Credential::$statusesForm;
        $tagStatuses = Tag::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('data'   => $data,
                                   'locales' => $locales,
                                   'credentials' => $credentials,
                                   'layouts' => $layouts,
                                   'tagStatuses' => $tagStatuses,
                                   'return' => $return));
    }

    /**
    * editAction : Edition d'un layout en fonction de son id
    * @param int $id : id du layout à editer
    *
    * @return ViewModel $viewModel 
    */
    public function editAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "tag");
        $return  = array();
        $data = array();
        
        $request = $this->getRequest();

        $tagId = $this->getEvent()->getRouteMatch()->getParam('id');
        $revisionId = $this->getEvent()->getRouteMatch()->getParam('revisionId', 0);

        $tag = $this->getTagService()->getTagMapper()->findById($tagId);

        $filters = array('type' => get_class($tag), 'objectId' => $tag->getId());
        $revisions = $this->getRevisionService()->getRevisionMapper()->findByAndOrderBy($filters, array('id' => 'DESC'));

        if(empty($tag)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
        }

        $translations = $this->getTagService()->getTagMapper()->getEntityRepositoryForEntity($tag->getTranslationRepository())->findTranslations($tag);
        $tag->setTranslations($translations);

         if(!empty($revisionId)){
            $revision = $this->getRevisionService()->getRevisionMapper()->findById($revisionId);
            $tag = unserialize($revision->getObject());
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Tag', 'recordId' => $tag->getId()));

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $return = $this->getTagService()->checkTag($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getTagService()->edit($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
            }
        }

        $credentials = Credential::$statusesForm;
        $tagStatuses = Tag::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('tag' => $tag,
                                   'locales' => $locales,
                                   'layouts' => $layouts,
                                   'ressources' => $ressources,
                                   'credentials' => $credentials,
                                   'tagStatuses' => $tagStatuses,
                                   'revisions' => $revisions,
                                   'return' => $return));
    }

    /**
    * removeAction : Suppression d'un layout en fonction de son id
    * @param int $id : id du layout à supprimer
    *
    * @return ViewModel $viewModel 
    */
    public function removeAction()
    {
        $tagId = $this->getEvent()->getRouteMatch()->getParam('id');
        $tag = $this->getTagService()->getTagMapper()->findById($tagId);

        if(empty($tag)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
        }

         // Suppression des ressources associées 
        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Tag', 'recordId' => $articleId));
        foreach ($ressources as $ressource) {
            $this->getRessourceService()->getRessourceMapper()->remove($ressource);
        }
        
        $this->getTagService()->getTagMapper()->remove($tag);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
    }

    /**
    * getLayoutService : Recuperation du service de Layout
    *
    * @return Layout $layoutService 
    */
    private function getTagService()
    {
        if (!$this->tagService) {
            $this->tagService = $this->getServiceLocator()->get('playgroundpublishing_tag_service');
        }

        return $this->tagService;
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
     * @return ArticleController $this
     */
    private function setRevisionService($revisionService)
    {
        $this->revisionService = $revisionService;

        return $this;
    }
}