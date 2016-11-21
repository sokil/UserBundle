<?php

namespace Sokil\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @UniqueEntity("email")
 */
class User extends \FOS\UserBundle\Entity\User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $phone;

    /**
     * @ORM\ManyToMany(targetEntity="Sokil\UserBundle\Entity\Group")
     * @ORM\JoinTable(
     *     name="users_groups",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @var Collection
     * @ORM\OneToMany(
     *  targetEntity="Sokil\UserBundle\Entity\UserAttributeValue",
     *  mappedBy="user",
     *  indexBy="attribute_id",
     *  cascade={"remove", "persist"})
     */
    protected $attributeValues;

    public function __construct()
    {
        parent::__construct();

        $this->attributeValues = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->setUsername($email);
        return parent::setEmail($email);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return User
     */
    public function setPhone($phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get roles directly attached to user (without inherited)
     * @return array
     */
    public function getOwnRoles()
    {
        return $this->roles;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Get user's attributes
     *
     * @var Collection
     */
    public function getAttributeValues()
    {
        return $this->attributeValues;
    }

    public function addAttributeValue(UserAttributeValue $attributeValue)
    {
        $this->attributeValues->set($attributeValue->getAttribute()->getId(), $attributeValue);
        return $this;
    }

    public function getGravatarDefaultUrl()
    {
        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)));
    }

    /**
     * Get gravatar url
     *
     * @param string $size Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $defaultImage Default imageset to use
     *  - 404: do not load any image if none is associated with the email hash, instead return an HTTP 404 (File Not Found) response
     *  - mm: (mystery-man) a simple, cartoon-style silhouetted outline of a person (does not vary by email hash)
     *  - identicon: a geometric pattern based on an email hash
     *  - monsterid: a generated 'monster' with different colors, faces, etc
     *  - wavatar: generated faces with differing features and backgrounds
     *  - retro: awesome generated, 8-bit arcade-style pixelated faces
     *  - blank: a transparent PNG image (border added to HTML below for demonstration purposes)
     * @param string $rating Maximum rating (inclusive)
     *  - g: suitable for display on all websites with any audience type.
     *  - pg: may contain rude gestures, provocatively dressed individuals, the lesser swear words, or mild violence.
     *  - r: may contain such things as harsh profanity, intense violence, nudity, or hard drug use.
     *  - x: may contain hardcore sexual imagery or extremely disturbing violence.
     * @param $attributes Additional key/value attributes to include to URL
     * @return string
     */
    public function getGravatarUrl($size = 80, $defaultImage = 'mm', $rating = 'g', $attributes = array())
    {
        $params = http_build_query([
            's' => $size,
            'd' => $defaultImage,
            'r' => $rating,
        ] + $attributes);

        return $this->getGravatarDefaultUrl() . '?' . $params;
    }

    /**
     * Delete user
     * @return User
     */
    public function delete()
    {
        $this->deleted = true;
        return $this;
    }

    /**
     * Undelete user
     * @return User
     */
    public function undelete()
    {
        $this->deleted = false;
        return $this;
    }

    /**
     * Check if user is deleted
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    public function isEnabled()
    {
        return parent::isEnabled() && !$this->deleted;
    }

    /**
     * Get list of related group ids
     *
     * @return int[]
     */
    public function getGroupIds()
    {
        $ids = array();

        /* @var $group \Sokil\UserBundle\Entity\Group */
        foreach ($this->getGroups() as $group) {
            $ids[] = $group->getId();
        }

        return $ids;
    }

    public function __toString()
    {
        return $this->name;
    }
}
