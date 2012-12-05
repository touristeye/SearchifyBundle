<?php
/**
 * User: matteo
 * Date: 04/04/12
 * Time: 10.17
 *
 * Just for fun...
 */

namespace TE\TranslationBundle\Entity\Base;

use TE\TranslationBundle\Entity\Base\TranslationEntity,
    TE\TranslationBundle\Exception\RuntimeException;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * SuperClass for Translatable entities
 */
abstract class TranslatableEntity
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * get the name of the TranslationEntity
     *
     * @return mixed
     */
    public function getTranslationEntity()
    {
        return get_class($this).'Translation';
    }

    /**
     * get the default language
     *
     * @return string
     */
    public function getDefaultLanguage()
    {
        // return the language of the translation we have
        if ( 1 === count($this->translations) )
        {
            return $this->translations[0]->getLocale();
        }
        // return the default language
        // @todo it must be set up on the config.yml
        else
        {
            return 'en';
        }
    }

    /**
     * get all the languages
     *
     * @return array
     * @todo it must be set up on the config.yml
     */
    private function getAllLanguages()
    {
        return array('en', 'es', 'it');
    }

    /**
     * magic method for getters and setters on translated field
     * i.e.:
     *   getTitle() get title default language
     *   getTitleEn() get title in en
     *
     * @param string $name      method name
     * @param array  $arguments arguments array
     *
     * @throws \TE\TranslationBundle\Exception\RuntimeException
     * @return null|mixed
     */
    public function __call($name, $arguments)
    {
        // SETTER
        if ('set' === substr($name, 0, 3) && count($arguments) == 1) {

            $language = strtolower(substr($name, strlen($name) - 2));

            // we support that language
            if ( in_array($language, $this->getAllLanguages()) )
            {
                // remove language from method name
                $methodName = substr($name, 0, strlen($name) - 2);
            }
            // it's the default language
            else
            {
                $language = $this->getDefaultLanguage();
                $methodName = $name;
            }

            // if no translations, create all of them
            $creating = false;
            if ( 0 == count($this->getTranslations()) ) {

                $creating = true;

                $this->setTranslations(new ArrayCollection());

                foreach ( $this->getAllLanguages() as $l ) {
                    $translationEntity = $this->getTranslationEntity();
                    $tr = new $translationEntity($l);
                    $this->addTranslation($tr);
                }
            }

            // get the value from the translation object
            foreach ( $this->getTranslations() as $tr ) {
                if ( $language == $tr->getLocale() || $creating ) {
                    $tr->$methodName($arguments[0]);
                }
            }
            return null;

            // load translation from the DB with that language
            /*$tr = $this->em->findBy( array('locale' => $language, 'object_id' => $this->getId()) );
            if ( $tr )
            {
                $this->addTranslation($tr);
                $tr->$methodName($arguments[0]);
                return null;
            }*/

        }
        // GETTER
        else if ('get' === substr($name, 0, 3) && count($arguments) == 0)
        {
            $language = strtolower(substr($name, strlen($name) - 2));

            // we support that language
            if ( in_array($language, $this->getAllLanguages()) )
            {
                // remove language from method name
                $methodName = substr($name, 0, strlen($name) - 2);
            }
            // it's the default language
            else
            {
                $language = $this->getDefaultLanguage();
                $methodName = $name;
            }

            // get the value from the translation object
            foreach ( $this->getTranslations() as $tr ) {
                if ( $language == $tr->getLocale() ) {
                    return $tr->$methodName();
                }
            }
        }

        /* no method was found, throw exception */
        throw new RuntimeException(sprintf('the method %s doesn\'t exists', $name));
    }

    /**
     * add a translation
     *
     * @param \TE\TranslationBundle\Entity\Base\TranslationEntity $translation the translation to add
     */
    public function addTranslation(TranslationEntity $translation)
    {
        if (!$this->getTranslations()->contains($translation)) {
            $translations   = $this->getTranslations();
            $translations[] = $translation;
            $translation->setObject($this);
        }
    }

    /**
     * Translations setter
     *
     * @param ArrayCollection $translations the traduzioni property
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * Translations getter
     *
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

}
