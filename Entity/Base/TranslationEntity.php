<?php
/**
 * User: matteo
 * Date: 04/04/12
 * Time: 10.49
 *
 * Just for fun...
 */

namespace TE\TranslationBundle\Entity\Base;

use Doctrine\ORM\Mapping as ORM;
use TE\TranslationBundle\Exception\RuntimeException;

/**
 * Superclass for a translation entity
 */
abstract class TranslationEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string $locale
     *
     * @ORM\Column(type="string", length=8)
     */
    protected $locale;

    /**
     * @var Object $object
     *
     * Related entity with ManyToOne relation
     * must be mapped by user
     */
    protected $object;

    /**
     * Constructor
     *
     * @param string $locale the locale
     */
    final public function __construct($locale)
    {
        $this->setLocale($locale);
    }

    /**
     * Id getter
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Locale setter
     *
     * @param string $locale the locale property
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Locale getter
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Object setter
     *
     * @param Object $object the object property
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * Object getter
     *
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * check if the entity has the property
     *
     * @param string $property property name
     *
     * @return bool
     */
    public function hasProperty($property)
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->hasProperty($property);
    }

    /**
     * magic method for getters and setters on translated field
     *
     * @param string $name      method name
     * @param array  $arguments arguments array
     *
     * @throws \TE\TranslationBundle\Exception\RuntimeException
     * @return null|mixed
     */
    public function __call($name, $arguments)
    {
        // GETTER
        if ( 'get' === substr($name, 0, 3) )
        {
            $property = $this->methodToProperty($name);
            return $this->$property;
        }
        // SETTER
        else if ('set' === substr($name, 0, 3) && count($arguments) == 1)
        {
            $property = $this->methodToProperty($name);
            $this->$property = $arguments[0];
            return null;
        }
        /* no method was found, throw exception */
        throw new RuntimeException(sprintf('the method %s doesn\'t exists', $name));
    }

    /**
     * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
     *
     * @param string $str String in camel case format
     *
     * @return string Translated into underscore format
     */
    private function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
     * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
     *
     * @param string $str                 String in underscore format
     * @param bool   $capitaliseFirstChar If true, capitalise the first char in $str
     *
     * @return string translated into camel caps
     */
    private function toCamelCase($str, $capitaliseFirstChar = true)
    {
        if ($capitaliseFirstChar) {
            $str[0] = strtoupper($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }

    /**
     * convert a getter/setter method to a property name
     *
     * @param string $method the method name
     *
     * @return string
     */
    private function methodToProperty($method)
    {
        // strip action
        $property = substr($method, 3);

        if ($this->hasProperty($this->fromCamelCase($property))) {
            return $this->fromCamelCase($property);
        } else if ($this->hasProperty(lcfirst($property))) {
            return lcfirst($property);
        } else {
            throw new RuntimeException(
                sprintf('there isn\'t a %s or %s property in the entity, or it is marked as "private". You need to set it as protected to make it translatable', $this->toCamelCase($property), lcfirst($property))
            );
        }
    }
}
