<?php

namespace Sokil\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_attributes_values")
 */
class UserAttributeValue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *   targetEntity="Sokil\UserBundle\Entity\User",
     *   inversedBy="attributeValues"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="UserAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     */
    protected $attribute;

    /**
     * Default value
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $value;

    public function __construct(
        User $user,
        UserAttribute $attribue,
        $value
    ) {
        $this->user = $user;
        $this->attribute = $attribue;
        $this->value = $value;
    }

    public function getId()
    {
        return $this->getId();
    }

    /**
     * @return UserAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param UserAttribute $attribute
     * @return $this
     */
    public function setAttribute(UserAttribute $attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return string value of user's attribute
     */
    public function getValue()
    {
        return $this->attribute->unserializeValue($this->value);
    }

    /**
     * @param $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $this->attribute->serializeValue($value);
        return $this;
    }

    public function __toString()
    {
        return $this->value;
    }
}