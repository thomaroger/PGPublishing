<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 14/05/2014
*
* Classe qui permet de gérer l'entity Category
**/
namespace PlaygroundPublishing\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\PostPersist;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use PlaygroundCore\Filter\Slugify;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="publishing_category")
 * @Gedmo\TranslationEntity(class="PlaygroundPublishing\Entity\Translation\CategoryTranslation")
 */

class Category implements InputFilterAwareInterface
{

    const CATEGORY_REFUSED = 0;
    const CATEGORY_PUBLISHED = 1;

    public static $statuses = array(self::CATEGORY_PUBLISHED => "Published",
                                    self::CATEGORY_REFUSED => "Refused");

    public static $categoryTranslate = array('fr_FR' => "categorie",
                                             'en_US' => "category");
    /** 
    * @var InputFilter $inputFilter
    */
    protected $inputFilter;

    /** 
    * @var string $securityContext
    */
    protected $securityContext;

    /** 
    * @var string $layoutContext
    */
    protected $layoutContext;


    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale = 'en_US';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_web", type="boolean", nullable=false)
     */
    protected $isWeb = 0;

    /**
     * @ORM\Column(name="is_mobile", type="boolean", nullable=false)
     */
    protected $isMobile = 0;

     /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $status = 0;


    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $slug;

     /**
     * @Gedmo\Translatable
     * @ORM\Column(name="title_meta", type="string", length=255, nullable=false)
     */
    protected $titleMeta;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="description_meta", type="string", length=255, nullable=false)
     */
    protected $descriptionMeta;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="keyword_meta", type="string", length=255, nullable=false)
     */
    protected $keywordMeta;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    protected $description;

     /**
     * @ORM\ManyToMany(targetEntity="PlaygroundPublishing\Entity\Article", mappedBy="categories")
     */
    protected $articles;


    protected $translations;

   /**
     * getId : Getter pour id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }
    
     /**
     * setId : Setter pour id
     * @param integer $id 
     *
     * @return Page $page
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

     /**
     * getIsWeb : Getter pour isWeb
     *
     * @return boolean $isWeb
     */
    public function getIsWeb()
    {
        return $this->isWeb;
    }
    
    /**
     * setIsWeb : Setter pour isWeb
     * @param boolean $isWeb 
     *
     * @return Page $page
     */
    public function setIsWeb($isWeb)
    {
        $this->isWeb = (boolean) $isWeb;

        return $this;
    }

     /**
     * getIsMobile : Getter pour isMobile
     *
     * @return boolean $isMobile
     */
    public function getIsMobile()
    {
        return $this->isMobile;
    }
    
    /**
     * setIsMobile : Setter pour isMobile
     * @param boolean $isMobile 
     *
     * @return Page $page
     */
    public function setIsMobile($isMobile)
    {
        $this->isMobile = (boolean) $isMobile;

        return $this;
    }

      /**
     * getStatus : Getter pour status
     *
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

     /**
     * getStatusName : Getter pour status name
     *
     * @return string $status
     */
    public function getStatusName()
    {
        return self::$statuses[$this->status];
    }
    
    /**
     * setStatus : Setter pour status
     * @param integer $status 
     *
     * @return Page $page
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

   /**
     * setCreatedAt : Setter pour created_at
     * @param dateime $created_at 
     *
     * @return Page $page
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $created_at;

        return $this;
    }

   /**
     * getCreatedAt : Getter pour created_at
     *
     * @return datetime $created_at
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * setUpdatedAt : Setter pour updated_at
     * @param dateime $updated_at 
     *
     * @return Page $page
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * updated_at : Getter pour updated_at
     *
     * @return datetime $updated_at
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * setTitle : Setter pour title
     * @param string $title 
     *
     * @return Page $page
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;

        // le slug est le titre slugifié.
        $slugify = new Slugify;
        $this->setSlug($slugify->filter($title));

        return $this;
    }

    /**
     * getTitle : Getter pour title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * setSlug : Setter pour slug
     * @param string $slug 
     *
     * @return Page $page
     */
    public function setSlug($slug)
    {
        $this->slug = (string) $slug;

        return $this;
    }

    /**
     * getSlug : Getter pour slug
     *
     * @return strign $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * setDescriptionMeta : Setter pour descriptionMeta
     * @param string $descriptionMeta 
     *
     * @return Page $page
     */
    public function setDescription($description)
    {
        $this->description = (string) $description;

        return $this;
    }

    /**
     * getDescriptionMeta : Getter pour descriptionMeta
     *
     * @return string $descriptionMeta
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * setTitleMeta : Setter pour titleMeta
     * @param string $titleMeta 
     *
     * @return Page $page
     */
    public function setTitleMeta($titleMeta)
    {
        $this->titleMeta = (string) $titleMeta;

        return $this;
    }

    /**
     * getTitleMeta : Getter pour titleMeta
     *
     * @return strign $titleMeta
     */
    public function getTitleMeta()
    {
        return $this->titleMeta;
    }

    /**
     * setKeywordMeta : Setter pour keywordMeta
     * @param string $keywordMeta 
     *
     * @return Page $page
     */
    public function setKeywordMeta($keywordMeta)
    {
        $this->keywordMeta = (string) $keywordMeta;

        return $this;
    }

    /**
     * getKeywordMeta : Getter pour keywordMeta
     *
     * @return strign $keywordMeta
     */
    public function getKeywordMeta()
    {
        return $this->keywordMeta;
    }

    /**
     * setDescriptionMeta : Setter pour descriptionMeta
     * @param string $descriptionMeta 
     *
     * @return Page $page
     */
    public function setDescriptionMeta($descriptionMeta)
    {
        $this->descriptionMeta = (string) $descriptionMeta;

        return $this;
    }

    /**
     * getDescriptionMeta : Getter pour descriptionMeta
     *
     * @return string $descriptionMeta
     */
    public function getDescriptionMeta()
    {
        return $this->descriptionMeta;
    }

    /**
     * setSecurityContext : Setter pour securityContext
     * @param string $securityContext 
     *
     * @return Page $page
     */
    public function setSecurityContext($securityContext)
    {
        $this->securityContext = (string) $securityContext;

        return $this;
    }

    /**
     * getSecurityContext : Getter pour securityContext
     *
     * @return strign $slug
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * setLayoutContext : Setter pour layoutContext
     * @param string $layoutContext 
     *
     * @return Page $page
     */
    public function setLayoutContext($layoutContext)
    {
        $this->layoutContext = (string) $layoutContext;

        return $this;
    }

    /**
     * getLayoutContext : Getter pour layoutContext
     *
     * @return strign $layoutContext
     */
    public function getLayoutContext()
    {
        return $this->layoutContext;
    }

    /**
    * @return PlaygroundCore\Entity\Locale $Categories
    */
    public function getArticles()
    {
        return $this->articles;
    }
    
    /**
    * @param PlaygroundCore\Entity\Locale $Categories
    * @return Website
    */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    
        return $this;
    }
    
    /**
    * @param PlaygroundCore\Entity\Locale $category
    * @return Website
    */
    public function addArticle($article)
    {
        $this->articles[] = $article;
    
        return $this;
    }
  

     /**
     * getArrayCopy : Convertit l'objet en tableau.
     *
     * @return array $array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * populate : Populate l'object à partir d'un array
     * @param array $data
     *
     */
    public function populate($data = array())
    {

    }

    /**
     * setInputFilter : Rajoute des Filtres
     * @param InputFilterInterface $inputFilter
     *
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /**
     * getInputFilter : Rajoute des Filtres
     *
     * @return InputFilter $inputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updated_at = new \DateTime("now");
    }

     /**
    * checkVisibility : Permet de savoir si une page est disponible en front
    *
    * @return boolean $result
    */
    public function checkVisibility()
    {
        if ($this->getStatus() != 1) {
            return false;
        }

        return true;
    }

    /**
    * createRessource : Creation d'une ressource à partir d'une entité Page
    * @param Page $pageMapper : Mapper de page
    * @param Collection $locales : Collection de locales
    */
    public function createRessource($categoryMapper, $locales)
    {
        $repository = $categoryMapper->getEntityManager()->getRepository($this->getTranslationRepository());
        $pageTranslations = $repository->findTranslations($this);
        foreach ($locales as $locale) {
            if(!empty($pageTranslations[$locale->getLocale()])) {
                $ressource = new \PlaygroundCMS\Entity\Ressource();
                $url  = strtolower("/".$locale->getLocale()."/".self::$categoryTranslate[$locale->getLocale()]."/".$pageTranslations[$locale->getLocale()]['slug'].'-'.$this->getId().'.html');
                $ressource->setUrl($url);
                $ressource->setModel(__CLASS__);
                $ressource->setRecordId($this->getId());
                $ressource->setLocale($locale->getLocale());
                $ressource->setSecurityContext($this->getSecurityContext());
                $ressource->setLayoutContext($this->getLayoutContext());
                $categoryMapper->persist($ressource);
            }
        }
    }

    /**
    * editRessource : Edition d'une ressource à partir d'une entité Page lors de l'edition d'une page
    * @param Page $pageMapper : Mapper de page
    * @param Collection $locales : Collection de locales
    */
    public function editRessource($categoryMapper, $locales)
    {
        $repository = $categoryMapper->getEntityManager()->getRepository('PlaygroundCMS\Entity\Ressource');
        $ressources = $repository->findBy(array('model' => __CLASS__, 'recordId' => $this->getId()));

        foreach ($ressources as $ressource) {
            $ressource->setSecurityContext($this->getSecurityContext());
            $ressource->setLayoutContext($this->getLayoutContext());
            $categoryMapper->persist($ressource);
        }
    }


    /**
    * getTranslationRepository :  Recuperation de l'entite PageTranslation
    *
    * @return string 
    */
    public function getTranslationRepository()
    {
        return 'PlaygroundPublishing\Entity\Translation\CategoryTranslation';
    }

    /**
    * setTranslatableLocale : Setter pour la locale
    * @param string $locale
    *
    * @return Page 
    */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }


    /**
    * getTranslatableLocale : Getter pour la  locale
    *
    * @return  string $locale
    */
    public function getTranslatableLocale()
    {
        return $this->locale;
    }

    /**
    * getTranslations : Getter pour les traductions
    *
    * @return  array $translations
    */
    public function getTranslations()
    {
        return $this->translations;
    }

     /**
    * getTranslations : Setter pour les traductions
    * @param array $translation 
    *
    * @return Page 
    */
    public function setTranslations($translations)
    {
        $this->translations = $translations;

        return $this;
    }

    /**
    * allowRevision : permet d'activer les revisions pour cette objet
    *
    * @return boolean true / false
    */
    public function allowRevision(){

        return true;
    }

}