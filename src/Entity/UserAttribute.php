<?php

namespace Sokil\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Sokil\UserBundle\Repository\UserAttributeRepository")
 * @ORM\Table(name="users_attributes")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *  "string" = "Sokil\UserBundle\Entity\UserAttribute\StringAttribute",
 *  "entity" = "Sokil\UserBundle\Entity\UserAttribute\EntityAttribute"
 * })
 */
abstract class UserAttribute
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Name of attribute
     * @ORM\Column(type="string", length=50, nullable=false)
     * @var string
     */
    protected $name;

    /**
     * Description of attribute
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $description;

    /**
     * Attribute metadata
     * @ORM\Column(type="json_array", length=65535, nullable=true)
     * @var string
     */
    protected $meta;

    /**
     * Default value
     * @ORM\Column(name="default_value", type="string", length=255, nullable=true)
     * @var string
     */
    protected $defaultValue;

    /**
     * @ORM\ManyToMany(targetEntity="Sokil\UserBundle\Entity\Group")
     * @ORM\JoinTable(
     *     name="users_attributes_groups",
     *     joinColumns={@ORM\JoinColumn(name="attribute_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    abstract public function getType();

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function serializeValue($value)
    {
        return $value;
    }

    public function unserializeValue($value)
    {
        return $value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function setMeta($name, $value)
    {
        $this->meta[$name] = $value;
        return $this;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function isDefaultValueGetFromCreator()
    {
        return !empty($this->meta['defaultValueFromCreator']);
    }

    public function getPrintFormat()
    {
        return !empty($this->meta['printFormat']) ? $this->meta['printFormat'] : null;
    }

    public function setPrintFormat($printFormat)
    {
        return $this->setMeta('printFormat', $printFormat);
    }

    /**
     * Roles that can view value of attribute
     * @return array|null
     */
    public function getViewRoles()
    {
        return isset($this->meta['roles']['view']) ? $this->meta['roles']['view'] : null;
    }

    /**
     * Roles that can edit value of attribute
     * @return array|null
     */
    public function getEditRoles()
    {
        return isset($this->meta['roles']['edit']) ? $this->meta['roles']['edit'] : null;
    }

    /**
     * Check if value is translateable
     *
     * @return bool
     */
    public function isTranslateable()
    {
        return !empty($this->meta['translateable']);
    }
}