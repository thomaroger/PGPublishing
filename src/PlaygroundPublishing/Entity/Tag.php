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
 * @ORM\Table(name="publishing_tag")
 * @Gedmo\TranslationEntity(class="PlaygroundPublishing\Entity\Translation\TagTranslation")
 */

class Tag implements InputFilterAwareInterface
{

    const TAG_REFUSED = 0;
    const TAG_PUBLISHED = 1;

    public static $statuses = array(self::TAG_PUBLISHED => "Published",
                                    self::TAG_REFUSED => "Refused");
    /** 
    * @var InputFilter $inputFilter
    */
    protected $inputFilter;


    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    protected $locale;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\ManyToMany(targetEntity="PlaygroundPublishing\Entity\Article", mappedBy="tags")
     */
    private $articles;

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
    * getTranslationRepository :  Recuperation de l'entite PageTranslation
    *
    * @return string 
    */
    public function getTranslationRepository()
    {
        return 'PlaygroundPublishing\Entity\Translation\TagTranslation';
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

}