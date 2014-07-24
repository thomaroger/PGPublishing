<?php

/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 14/05/2014
*
* Classe qui permet de gérer la partie I18n de l'entity Sondage
**/

namespace PlaygroundPublishing\Entity\Translation;

use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Entity;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * Gedmo\Translatable\Entity\Translation
 *
 * @Table(
 *         name="publishing_poll_answer_translations",
 *         indexes={@index(name="answer_translations_lookup_idx", columns={
 *             "locale", "object_class", "foreign_key"
 *         })},
 *         uniqueConstraints={@UniqueConstraint(name="answer_translations_lookup_unique_idx", columns={
 *             "locale", "object_class", "field", "foreign_key"
 *         })}
 * )
 * @Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class AnswerTranslation extends AbstractTranslation
{
    /**
     * All required columns are mapped through inherited superclass
     */
}