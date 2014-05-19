<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 25/03/2014
*
* Classe de service pour l'entite Page
**/
namespace PlaygroundPublishing\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use Datetime;
use PlaygroundPublishing\Mapper\Article as ArticleMapper;
use PlaygroundPublishing\Entity\Article as ArticleEntity;
use PlaygroundCore\Filter\Slugify;


class Article extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var PlaygroundPublishing\Mapper\Page pageMapper
     */
    protected $articleMapper;

    /**
     * @var PlaygroundPublishing\Mapper\Tag tagMapper
     */
    protected $tagMapper;

    /**
     * @var PlaygroundPublishing\Mapper\Category categoryMapper
     */
    protected $categoryMapper;

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

        $article = new ArticleEntity();
        $layoutContext = array();

        $article->setIsWeb(0);
        if ($data['article']['web']['active'] == 1) {
            $article->setIsWeb(1);
            $layoutContext['web'] = $data['article']['web']['layout'];
        }

        $article->setIsMobile(0);
        if ($data['article']['mobile']['active'] == 1) {
            $article->setIsMobile(1);
            $layoutContext['mobile'] = $data['article']['mobile']['layout'];
        }

        $article->setStatus(ArticleEntity::ARTICLE_DRAFT);

        if (!empty($data['article']['status'])) {
            $article->setStatus($data['article']['status']);
        }


        $article->setAuthor($data['article']['author']);

        $article->setLayoutContext(json_encode($layoutContext));
        $article->setSecurityContext($data['article']['visibility']);

        $startDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['article']['start_date']['date'].' '.$data['article']['start_date']['time']);
        $article->setStartDate($startDate);
        $endDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['article']['end_start']['date'].' '.$data['article']['end_start']['time']);
        $article->setEndDate($endDate);

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getArticleMapper()->getEntityManager()->getRepository($article->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['article'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['article'][$locale->getLocale()]['title']);
                $repository->translate($article, 'title', $locale->getLocale(), $data['article'][$locale->getLocale()]['title'])
                        ->translate($article, 'slug', $locale->getLocale(), $slug)
                        ->translate($article, 'abstract', $locale->getLocale(), $data['article'][$locale->getLocale()]['abstract'])
                        ->translate($article, 'content', $locale->getLocale(), $data['article'][$locale->getLocale()]['content'])
                        ->translate($article, 'titleMeta', $locale->getLocale(), $data['article'][$locale->getLocale()]['title_seo'])
                        ->translate($article, 'keywordMeta', $locale->getLocale(), $data['article'][$locale->getLocale()]['keyword_seo'])
                        ->translate($article, 'descriptionMeta', $locale->getLocale(), $data['article'][$locale->getLocale()]['description_seo']); 
               
            }   
        }

        if (!empty($data['article']['tags'])) { 
            $tags = $data['article']['tags'];
            foreach ($tags as $tagId) {
                $tag = $this->getTagMapper()->findById($tagId);
                $article->addTag($tag);
            }
        }

         if (!empty($data['article']['categories'])) { 
            $categories = $data['article']['categories'];
            foreach ($categories as $categoryId) {
                $category = $this->getCategoryMapper()->findById($categoryId);
                $article->addCategory($category);
            }
        }


        $article = $this->getArticleMapper()->persist($article);
        $article = $this->getArticleMapper()->findById($article->getId());
        
        $article->createRessource($this->getArticleMapper(), $locales);
    }

    /**
    * edit : Permet d'editer une page
    * @param array $data : tableau de données 
    */
    public function edit($data){

        $article = $this->getArticleMapper()->findById($data['article']['id']);

        $layoutContext = array();

        $article->setIsWeb(0);
        if ($data['article']['web']['active'] == 1) {
            $article->setIsWeb(1);
            $layoutContext['web'] = $data['article']['web']['layout'];
        }

        $article->setIsMobile(0);
        if ($data['article']['mobile']['active'] == 1) {
            $article->setIsMobile(1);
            $layoutContext['mobile'] = $data['article']['mobile']['layout'];
        }
        $article->setStatus(ArticleEntity::ARTICLE_DRAFT);

        if (!empty($data['article']['status'])) {
            $article->setStatus($data['article']['status']);
        }

        $article->setLayoutContext(json_encode($layoutContext));
        $article->setSecurityContext($data['article']['visibility']);

        $startDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['article']['start_date']['date'].' '.$data['article']['start_date']['time']);
        $article->setStartDate($startDate);
        $endDate = DateTime::createFromFormat('m/d/Y H:i:s', $data['article']['end_start']['date'].' '.$data['article']['end_start']['time']);
        $article->setEndDate($endDate);


        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getArticleMapper()->getEntityManager()->getRepository($article->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['article'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['article'][$locale->getLocale()]['title']);
                $repository->translate($article, 'title', $locale->getLocale(), $data['article'][$locale->getLocale()]['title'])
                        ->translate($article, 'slug', $locale->getLocale(), $slug)
                        ->translate($article, 'titleMeta', $locale->getLocale(), $data['article'][$locale->getLocale()]['title_seo'])
                        ->translate($article, 'keywordMeta', $locale->getLocale(), $data['article'][$locale->getLocale()]['keyword_seo'])
                        ->translate($article, 'descriptionMeta', $locale->getLocale(), $data['article'][$locale->getLocale()]['description_seo']); 
               
            }   
        }


        $article->setCategories(array());
        $article->setTags(array());

        $article = $this->getArticleMapper()->update($article);
        
        if (!empty($data['article']['tags'])) { 
            $tags = $data['article']['tags'];
            foreach ($tags as $tagId) {
                $tag = $this->getTagMapper()->findById($tagId);
                $article->addTag($tag);
            }
        }

         if (!empty($data['article']['categories'])) { 
            $categories = $data['article']['categories'];
            foreach ($categories as $categoryId) {
                $category = $this->getCategoryMapper()->findById($categoryId);
                $article->addCategory($category);
            }
        }

        $article = $this->getArticleMapper()->update($article);
        $article->editRessource($this->getArticleMapper(), $locales);
    }

    /**
    * checkPage : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkArticle($data)
    {
        // Valeur par défaut

        $data['article']['status'] = (int) $data['article']['status'];

        if (empty($data['article']['start_date']['time'])) {
            $data['article']['start_date']['time'] = '00:00:00';
        }
        if (empty($data['article']['end_start']['date'])) {
            $data['article']['end_start']['date'] = '12/31/2029';
        }

        if (empty($data['article']['end_start']['time'])) {
            $data['article']['end_start']['time'] = '23:59:59';
        }
        
        // Il faut au moins un titre de renseigner
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));
        $title = false;
        foreach ($locales as $locale) {
            if(!empty($data['article'][$locale->getLocale()])) {
                if(!empty($data['article'][$locale->getLocale()]['title'])){
                    $title = true;
                    if(empty($data['article'][$locale->getLocale()]['abstract'])){
                        
                        return array('status' => 1, 'message' => 'abstract is required', 'data' => $data);
                    }

                    if(empty($data['article'][$locale->getLocale()]['content'])){

                        return array('status' => 1, 'message' => 'content is required', 'data' => $data);

                    }
                }
            }
        }
        if(!$title){
            
            return array('status' => 1, 'message' => 'One of title is required', 'data' => $data);
        }

        // Il faut au moins une plateforme d'activer
        if ($data['article']['web']['active'] == 0 && $data['article']['mobile']['active'] == 0) {
            
            return array('status' => 1, 'message' => 'One of platform must be activated', 'data' => $data);
        }

        // Si une plateforme est active, alors il faut un layout
        if ($data['article']['web']['active'] == 1 && $data['article']['web']['layout'] == '') {
            
            return array('status' => 1, 'message' => 'For a activate platform, you must have a layout', 'data' => $data);
        }

        // Si une plateforme est active, alors il faut un layout
        if ($data['article']['mobile']['active'] == 1 && $data['article']['mobile']['layout'] == '') {
            
            return array('status' => 1, 'message' => 'For a activate platform, you must have a layout', 'data' => $data);
        }

        // Il faut une visibility
        if(empty($data['article']['visibility'])) {
            
            return array('status' => 1, 'message' => 'Visibility is required', 'data' => $data);  
        }
        
        // il faut un author
        if (empty($data['article']['author'])) {
            
            return array('status' => 1, 'message' => 'The author is required', 'data' => $data);        
        }

        // Il faut un status
        if ($data['article']['status'] == -1) {
            
            return array('status' => 1, 'message' => 'The status is required', 'data' => $data);        
        }

        // Il faut une date de debut
        if (empty($data['article']['start_date']['date'])) {
            
            return array('status' => 1, 'message' => 'The start date is required', 'data' => $data);        
        }

      

        return array('status' => 0, 'message' => '', 'data' => $data);
    }

    /**
     * getArticleMapper : Getter pour articleMapper
     *
     * @return PlaygroundPublishing\Mapper\Article $articleMapper
     */
    public function getArticleMapper()
    {
        if (null === $this->articleMapper) {
            $this->articleMapper = $this->getServiceManager()->get('playgroundpublishing_article_mapper');
        }

        return $this->articleMapper;
    }

    /**
     * getTagMapper : Getter pour tagMapper
     *
     * @return PlaygroundPublishing\Mapper\Tag $tagMapper
     */
    public function getTagMapper()
    {
        if (null === $this->tagMapper) {
            $this->tagMapper = $this->getServiceManager()->get('playgroundpublishing_tag_mapper');
        }

        return $this->tagMapper;
    }

    /**
     * getTagMapper : Getter pour tagMapper
     *
     * @return PlaygroundPublishing\Mapper\Tag $tagMapper
     */
    public function getCategoryMapper()
    {
        if (null === $this->categoryMapper) {
            $this->categoryMapper = $this->getServiceManager()->get('playgroundpublishing_category_mapper');
        }

        return $this->categoryMapper;
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