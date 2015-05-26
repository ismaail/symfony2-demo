<?php

namespace Bookkeeper\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User
 * @package Bookkeeper\ApplicationBundle\Entity
 *
 * @ORM\Entity(repositoryClass="Bookkeeper\UserBundle\Entity\UserRepository")
 * @ORM\Table(name="user")
 *
 * @UniqueEntity("username")
 */
class User implements UserInterface, \Serializable
{
    /**#@+
     * @const string
     */
    const ROLE_ADMIN   = 'ROLE_ADMIN';
    const ROLE_MEMBER  = 'ROLE_MEMBER';
    /**#@-*/

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=50)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=4)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=8, max=120)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="roles", type="string", length=255)
     */
    protected $roles;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        $this->roles = implode(',', $roles);

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return explode(',', $this->roles);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * Hash a string using BCRYPT
     *
     * @param $string
     *
     * @return bool|string
     */
    public function hash($string)
    {
        return password_hash($string, PASSWORD_BCRYPT);
    }

    /**
     * String representation of object
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->roles,
        ));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->roles,
        ) = unserialize($serialized);
    }
}
