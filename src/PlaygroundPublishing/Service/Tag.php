<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 03/04/2014
*
* Classe de service pour l'entite tag
**/
namespace PlaygroundPublishing\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundCore\Filter\Slugify;
use PlaygroundPublishing\Mapper\Tag as TagMapper;
use PlaygroundPublishing\Entity\Tag as TagEntity;

class Tag extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var PlaygroundCMS\Mapper\Tag tagMapper
     */
    protected $tagMapper;

    /**
     * @var PlaygroundCore\Mapper\Locale localeMapper
     */
    protected $localeMapper;
    
    /**
     * @var Zend\ServiceManager\ServiceManager ServiceManager
     */
    protected $serviceManager;
        
    /**
    * create : Permet de créer un Layout
    * @param array $data : tableau de données 
    */
    public function create($data)
    {
        $tag = new TagEntity();
        
        $tag->setStatus(TagEntity::TAG_REFUSED);

        if ($data['tag']['status'] != -1) {
            $tag->setStatus($data['tag']['status']);
        }

        $tag->setIsWeb(0);
        if ($data['tag']['web']['active'] == 1) {
            $tag->setIsWeb(1);
            $layoutContext['web'] = $data['tag']['web']['layout'];
        }

        $tag->setIsMobile(0);
        if ($data['tag']['mobile']['active'] == 1) {
            $tag->setIsMobile(1);
            $layoutContext['mobile'] = $data['tag']['mobile']['layout'];
        }

        $tag->setLayoutContext(json_encode($layoutContext));
        $tag->setSecurityContext($data['tag']['visibility']);


        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getTagMapper()->getEntityManager()->getRepository($tag->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['tag'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['tag'][$locale->getLocale()]['title']);
                $repository->translate($tag, 'title', $locale->getLocale(), $data['tag'][$locale->getLocale()]['title'])
                           ->translate($tag, 'slug', $locale->getLocale(), $slug)
                           ->translate($tag, 'titleMeta', $locale->getLocale(), $data['tag'][$locale->getLocale()]['title_seo'])
                           ->translate($tag, 'keywordMeta', $locale->getLocale(), $data['tag'][$locale->getLocale()]['keyword_seo'])
                           ->translate($tag, 'descriptionMeta', $locale->getLocale(), $data['tag'][$locale->getLocale()]['description_seo']);
            }   
        }

        $tag = $this->getTagMapper()->persist($tag);
        $tag = $this->getTagMapper()->findById($tag->getId());

        $tag->createRessource($this->getTagMapper(), $locales);
    }

    /**
    * update : Permet de modifer un layout
    * @param array $data : tableau de données 
    */
    public function edit($data)
    {
        $tag = $this->getTagMapper()->findById($data['tag']['id']);
        
        $tag->setStatus(TagEntity::TAG_REFUSED);

        $tag->setIsWeb(0);
        if ($data['tag']['web']['active'] == 1) {
            $tag->setIsWeb(1);
            $layoutContext['web'] = $data['tag']['web']['layout'];
        }

        $tag->setIsMobile(0);
        if ($data['tag']['mobile']['active'] == 1) {
            $tag->setIsMobile(1);
            $layoutContext['mobile'] = $data['tag']['mobile']['layout'];
        }

        if ($data['tag']['status'] != -1) {
            $tag->setStatus($data['tag']['status']);
        }

        $tag->setLayoutContext(json_encode($layoutContext));
        $tag->setSecurityContext($data['tag']['visibility']);

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getTagMapper()->getEntityManager()->getRepository($tag->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['tag'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['tag'][$locale->getLocale()]['title']);
                $repository->translate($tag, 'title', $locale->getLocale(), $data['tag'][$locale->getLocale()]['title'])
                           ->translate($tag, 'slug', $locale->getLocale(), $slug)                           
                           ->translate($tag, 'titleMeta', $locale->getLocale(), $data['tag'][$locale->getLocale()]['title_seo'])
                           ->translate($tag, 'keywordMeta', $locale->getLocale(), $data['tag'][$locale->getLocale()]['keyword_seo'])
                           ->translate($tag, 'descriptionMeta', $locale->getLocale(), $data['tag'][$locale->getLocale()]['description_seo']); 
            }   
        }

        $tag = $this->getTagMapper()->update($tag);
        $tag->editRessource($this->getTagMapper(), $locales);

    }

    /**
    * checkLayout : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkTag($data)
    {
          // Il faut au moins une plateforme d'activer
        if ($data['tag']['web']['active'] == 0 && $data['tag']['mobile']['active'] == 0) {
            
            return array('status' => 1, 'message' => 'One of platform must be activated', 'data' => $data);
        }

        // Si une plateforme est active, alors il faut un layout
        if ($data['tag']['web']['active'] == 1 && $data['tag']['web']['layout'] == '') {
            
            return array('status' => 1, 'message' => 'For a activate platform, you must have a layout', 'data' => $data);
        }

        // Si une plateforme est active, alors il faut un layout
        if ($data['tag']['mobile']['active'] == 1 && $data['tag']['mobile']['layout'] == '') {
            
            return array('status' => 1, 'message' => 'For a activate platform, you must have a layout', 'data' => $data);
        }

        // Il faut une visibility
        if(empty($data['tag']['visibility'])) {
            
            return array('status' => 1, 'message' => 'Visibility is required', 'data' => $data);  
        }


        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));
        $title = true;
        foreach ($locales as $locale) {
            if(!empty($data['tag'][$locale->getLocale()])) {
                if(empty($data['tag'][$locale->getLocale()]['title'])){
                    $title = false;
                }
            }
        }
        if(!$title){
            
            return array('status' => 1, 'message' => 'all title is required', 'data' => $data);
        }

        return array('status' => 0, 'message' => '', 'data' => $data);
    }

    /**
     * getLayoutMapper : Getter pour tagMapper
     *
     * @return PlaygroundCMS\Mapper\Tag $tagMapper
     */
    public function getTagMapper()
    {
        if (null === $this->tagMapper) {
            $this->tagMapper = $this->getServiceManager()->get('playgroundpublishing_tag_mapper');
        }

        return $this->tagMapper;
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
     * @return Block $this
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

}