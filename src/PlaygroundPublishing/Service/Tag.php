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

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getTagMapper()->getEntityManager()->getRepository($tag->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['tag'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['tag'][$locale->getLocale()]['title']);
                $repository->translate($tag, 'title', $locale->getLocale(), $data['tag'][$locale->getLocale()]['title'])
                           ->translate($tag, 'slug', $locale->getLocale(), $slug); 
            }   
        }

        $tag = $this->getTagMapper()->persist($tag);
    }

    /**
    * update : Permet de modifer un layout
    * @param array $data : tableau de données 
    */
    public function edit($data)
    {
        $tag = $this->getTagMapper()->findById($data['tag']['id']);
        
        $tag->setStatus(TagEntity::TAG_REFUSED);

        if ($data['tag']['status'] != -1) {
            $tag->setStatus($data['tag']['status']);
        }

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getTagMapper()->getEntityManager()->getRepository($tag->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['tag'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['tag'][$locale->getLocale()]['title']);
                $repository->translate($tag, 'title', $locale->getLocale(), $data['tag'][$locale->getLocale()]['title'])
                           ->translate($tag, 'slug', $locale->getLocale(), $slug); 
            }   
        }

        $tag = $this->getTagMapper()->persist($tag);
    }

    /**
    * checkLayout : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkTag($data)
    {
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