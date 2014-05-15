<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 03/04/2014
*
* Classe de service pour l'entite Layout
**/
namespace PlaygroundPublishing\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundCore\Filter\Slugify;
use PlaygroundPublishing\Mapper\Category as CategoryMapper;
use PlaygroundPublishing\Entity\Category as CategoryEntity;

class Category extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var PlaygroundCMS\Mapper\Layout layoutMapper
     */
    protected $categoryMapper;

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
        $category = new CategoryEntity();
        
        $category->setStatus(CategoryEntity::CATEGORY_REFUSED);

        if ($data['category']['status'] != -1) {
            $category->setStatus($data['category']['status']);
        }

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getCategoryMapper()->getEntityManager()->getRepository($category->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['category'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['category'][$locale->getLocale()]['title']);
                $repository->translate($category, 'title', $locale->getLocale(), $data['category'][$locale->getLocale()]['title'])
                           ->translate($category, 'slug', $locale->getLocale(), $slug)
                           ->translate($category, 'description', $locale->getLocale(), $data['category'][$locale->getLocale()]['description']); 
            }   
        }

        $category = $this->getCategoryMapper()->persist($category);
    }

    /**
    * update : Permet de modifer un layout
    * @param array $data : tableau de données 
    */
    public function edit($data)
    {
        $category = $this->getCategoryMapper()->findById($data['category']['id']);
        
        $category->setStatus(CategoryEntity::CATEGORY_REFUSED);

        if ($data['category']['status'] != -1) {
            $category->setStatus($data['category']['status']);
        }

        $slugify = new Slugify;
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));

        $repository = $this->getCategoryMapper()->getEntityManager()->getRepository($category->getTranslationRepository());

        foreach ($locales as $locale) {
            if(!empty($data['category'][$locale->getLocale()])) {
                $slug = $slugify->filter($data['category'][$locale->getLocale()]['title']);
                $repository->translate($category, 'title', $locale->getLocale(), $data['category'][$locale->getLocale()]['title'])
                           ->translate($category, 'slug', $locale->getLocale(), $slug)
                           ->translate($category, 'description', $locale->getLocale(), $data['category'][$locale->getLocale()]['description']); 
            }   
        }

        $category = $this->getCategoryMapper()->persist($category);
    }

    /**
    * checkLayout : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkCategory($data)
    {
        // Il faut au moins un titre de renseigner
        $locales = $this->getLocaleMapper()->findBy(array('active_front' => 1));
        $title = true;
        foreach ($locales as $locale) {
            if(!empty($data['category'][$locale->getLocale()])) {
                if(empty($data['category'][$locale->getLocale()]['title'])){
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
     * getLayoutMapper : Getter pour categoryMapper
     *
     * @return PlaygroundCMS\Mapper\Category $categoryMapper
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
     * @return Block $this
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

}