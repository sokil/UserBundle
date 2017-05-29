<?php

namespace Sokil\UserBundle\CommandBus\ManageUser;

use Sokil\UserBundle\Entity\User;

abstract class AbstractCommand
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @var array
     */
    protected $groups = [];

    /**
     * @var array
     */
    protected $attributeValues = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function getValidationGroups()
    {
        return [];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $email
     * @return AbstractCommand
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $password
     * @return AbstractCommand
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     *
     */
    public function getPassword()
    {
        return $this->password;
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
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return AbstractCommand
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     * @return AbstractCommand
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeValues()
    {
        return $this->attributeValues;
    }

    /**
     * @param array $attributeValues
     * @return AbstractCommand
     */
    public function setAttributeValues(array $attributeValues)
    {
        $this->attributeValues = $attributeValues;
        return $this;
    }
}
