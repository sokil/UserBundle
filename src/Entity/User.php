<?php

namespace Sokil\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @UniqueEntity("email")
 */
class User implements AdvancedUserInterface, \Serializable
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

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
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $email;

    /**
     * The salt to use for hashing
     *
     * @var string
     * @ORM\Column(type="string")
     */
    protected $salt;

    /**
     * Encrypted password for persistance
     *
     * @var string
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $credentialsExpireAt;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $roles;

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
     * @var Collection
     * @ORM\OneToMany(
     *  targetEntity="Sokil\UserBundle\Entity\UserAttributeValue",
     *  mappedBy="user",
     *  indexBy="attribute_id",
     *  cascade={"remove", "persist"})
     */
    protected $attributeValues;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted = false;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    protected $locked;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $expiresAt;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->roles = array();
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
     * Set name
     *
     * @deprecated Name builder need to be implemented
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
     * @deprecated Name builder need to be implemented
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->setUsername($email);
        $this->email = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {

    }


    public function isCredentialsNonExpired()
    {
        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setCredentialsExpireAt(\DateTime $date)
    {
        $this->credentialsExpireAt = $date;

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

    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Get user roles
     *
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function setRoles(array $roles)
    {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * Gets the groups granted to the user.
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    public function getGroupNames()
    {
        $names = array();
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(GroupInterface $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    public function removeGroup(GroupInterface $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
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


    /**
     * Get user's attributes
     *
     * @return Collection
     */
    public function getAttributeValues()
    {
        return $this->attributeValues;
    }

    /**
     * @param UserAttributeValue $attributeValue
     * @return $this
     */
    public function addAttributeValue(UserAttributeValue $attributeValue)
    {
        $this->attributeValues->set($attributeValue->getAttribute()->getId(), $attributeValue);
        return $this;
    }

    /**
     * @return string
     */
    public function getGravatarDefaultUrl()
    {
        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)));
    }

    /**
     * Get gravatar url
     *
     * @param int $size Size in pixels, defaults to 80px [ 1 - 2048 ]
     * @param string $defaultImage Default image to use
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
     * @param array $attributes Additional key/value attributes to include to URL
     * @return string
     */
    public function getGravatarUrl(
        $size = 80,
        $defaultImage = 'mm',
        $rating = 'g',
        array $attributes = []
    ) {
        $params = http_build_query([
            's' => $size,
            'd' => $defaultImage,
            'r' => $rating,
        ] + $attributes);

        return $this->getGravatarDefaultUrl() . '?' . $params;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled && !$this->deleted;
    }

    public function setEnabled($boolean)
    {
        $this->enabled = (Boolean) $boolean;

        return $this;
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
     * Restore user
     * @return User
     */
    public function restore()
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

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function setLocked($boolean)
    {
        $this->locked = $boolean;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setExpiresAt(\DateTime $date)
    {
        $this->expiresAt = $date;

        return $this;
    }

    public function isAccountNonExpired()
    {
        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->username,
            $this->locked,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->credentialsExpireAt,
            $this->email,
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->password,
            $this->salt,
            $this->username,
            $this->locked,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->credentialsExpireAt,
            $this->email,
        ) = $data;
    }

    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
