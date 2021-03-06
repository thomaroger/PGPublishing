<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 25/03/2014
*
* Classe de controleur de back pour la gestion des articles
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundCMS\Security\Credential;
use PlaygroundPublishing\Entity\Article;
use PlaygroundPublishing\Entity\Category;
use PlaygroundPublishing\Entity\Tag;

class ArticleController extends AbstractActionController
{
    /**
    * @var MAX_PER_PAGE  Nombre d'item par page
    */
    const MAX_PER_PAGE = 20;

    /**
    * @var Service $pageService Service de page
    */
    protected $articleService;

    /**
    * @var Service $pageService Service de page
    */
    protected $categoryService;

    /**
    * @var Service $pageService Service de page
    */
    protected $tagService;

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
    * @var ModuleOptions $cmsOptions  Options de playgroundcms
    */
    protected $cmsOptions;
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
        $articlesId = array();
        $ressourcesCollection = array();
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "article");
        $p = $this->getRequest()->getQuery('page', 1);

        $articles = $this->getArticleService()->getArticleMapper()->findAll();
        
        $nbArticles = count($articles);

        $articlesPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($articles));
        $articlesPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $articlesPaginator->setCurrentPageNumber($p);

        foreach ($articles as $article) {
            $articlesId[] = $article->getId();
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Article', 'recordId' => $articlesId));
        foreach ($ressources as $ressource) {
            $ressourcesCollection[$ressource->getRecordId()][$ressource->getLocale()] = $ressource;
        }

        $credentials = Credential::$statusesForm;
        $articlesStatuses = Article::$statuses;

        $files = $this->getLayoutService()->getLayouts();

        return new ViewModel(array('articles'             => $articles,
                                   'articlesPaginator'    => $articlesPaginator,
                                   'nbArticles'           => $nbArticles,
                                   'files'                => $files,
                                   'credentials'          => $credentials,
                                   'articlesStatuses'     => $articlesStatuses,
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
        $this->layout()->setVariable('subNav', "article");

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );
            $return = $this->getArticleService()->checkArticle($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getArticleService()->create($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/articles');
            }
        }

        $credentials = Credential::$statusesForm;
        $articlesStatuses = Article::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        $tags = $this->getTagService()->getTagMapper()->findBy(array('status' => Category::CATEGORY_PUBLISHED));
        $categories = $this->getCategoryService()->getCategoryMapper()->findBy(array('status' => Tag::TAG_PUBLISHED));

        return new ViewModel(array('credentials'      => $credentials,
                                   'articlesStatuses' => $articlesStatuses,
                                   'layouts'          => $layouts,
                                   'locales'          => $locales,
                                   'data'             => $data,
                                   'tags'             => $tags,
                                   'categories'       => $categories,
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
        $this->layout()->setVariable('subNav', "article");
        
        $request = $this->getRequest();

        $articleId = $this->getEvent()->getRouteMatch()->getParam('id');
        $revisionId = $this->getEvent()->getRouteMatch()->getParam('revisionId', 0);

        $article = $this->getArticleService()->getArticleMapper()->findById($articleId);

        $filters = array('type' => get_class($article), 'objectId' => $article->getId());
        $revisions = $this->getRevisionService()->getRevisionMapper()->findByAndOrderBy($filters, array('id' => 'DESC'));

        if(empty($article)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/articles');
        }

        $translations = $this->getArticleService()->getArticleMapper()->getEntityRepositoryForEntity($article->getTranslationRepository())->findTranslations($article);
        $article->setTranslations($translations);

        if(!empty($revisionId)){
            $revision = $this->getRevisionService()->getRevisionMapper()->findById($revisionId);
            $article = unserialize($revision->getObject());
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Article', 'recordId' => $articleId));
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );
            $return = $this->getArticleService()->checkArticle($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getArticleService()->edit($data);
                
                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/articles');
            }
        }

        $credentials = Credential::$statusesForm;
        $articlesStatuses = Article::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));
        $tags = $this->getTagService()->getTagMapper()->findBy(array('status' => Category::CATEGORY_PUBLISHED));
        $categories = $this->getCategoryService()->getCategoryMapper()->findBy(array('status' => Tag::TAG_PUBLISHED));

        return new ViewModel(array('credentials'      => $credentials,
                                   'articlesStatuses' => $articlesStatuses,
                                   'layouts'          => $layouts,
                                   'locales'          => $locales,
                                   'article'          => $article,
                                   'tags'             => $tags,
                                   'categories'       => $categories,
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
        $articleId = $this->getEvent()->getRouteMatch()->getParam('id');
        $article = $this->getArticleService()->getArticleMapper()->findById($articleId);

        if(empty($article)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/articles');
        }

        // Suppression des ressources associées 
        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Article', 'recordId' => $articleId));
        foreach ($ressources as $ressource) {
            $this->getRessourceService()->getRessourceMapper()->remove($ressource);
        }

        $this->getArticleService()->getArticleMapper()->remove($article);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/articles');
    }


    /**
    * getTagService : Recuperation du service de tag
    *
    * @return Tag $tagService 
    */
    private function getTagService()
    {
        if (!$this->tagService) {
            $this->tagService = $this->getServiceLocator()->get('playgroundpublishing_tag_service');
        }

        return $this->tagService;
    }

     /**
    * getCategoryService : Recuperation du service de category
    *
    * @return Category $categoryService 
    */
    private function getCategoryService()
    {
        if (!$this->categoryService) {
            $this->categoryService = $this->getServiceLocator()->get('playgroundpublishing_category_service');
        }

        return $this->categoryService;
    }

    /**
    * getPageService : Recuperation du service de page
    *
    * @return Page $pageService 
    */
    private function getArticleService()
    {
        if (!$this->articleService) {
            $this->articleService = $this->getServiceLocator()->get('playgroundpublishing_article_service');
        }

        return $this->articleService;
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
    * getCMSOptions : Recuperation des options de playgroundCMS
    *
    * @return ModuleOptions $cmsOptions 
    */
    private function getCMSOptions()
    {
        if (!$this->cmsOptions) {
            $this->cmsOptions = $this->getServiceLocator()->get('playgroundcms_module_options');
        }

        return $this->cmsOptions;
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
