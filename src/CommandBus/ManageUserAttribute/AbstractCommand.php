<?php

namespace Sokil\UserBundle\CommandBus\ManageUserAttribute;

use Sokil\UserBundle\Entity\UserAttribute;

abstract class AbstractCommand
{
    /**
     * @var UserAttribute
     */
    private $userAttribute;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @return UserAttribute
     */
    public function getUserAttribute()
    {
        return $this->userAttribute;
    }

    /**
     * @param UserAttribute $userAttribute
     */
    public function setUserAttribute($userAttribute)
    {
        $this->userAttribute = $userAttribute;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
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
}