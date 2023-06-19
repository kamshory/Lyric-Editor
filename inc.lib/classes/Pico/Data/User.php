<?php
namespace Pico\Data;

use Pico\DynamicObject\DynamicObject;

/**
 * User
 * @Table (name=user)
 */
class User extends DynamicObject
{    

    /**
     * User ID
     *
     * @var string
     * @Column (name=user_id)
     * @Id
     */
    protected $userId;

    /**
     * Username
     *
     * @var string
     * @Column (name=username)
     */
    protected $userName;

    /**
     * Email
     *
     * @var string
     * @Column (name=email)
     */
    protected $email;

    /**
     * Password
     *
     * @var string
     * @Column (name=password)
     */
    protected $password;

    /**
     * Birth day
     *
     * @var string
     * @Column (name=birth_day)
     */
    protected $birthDay;

    /**
     * Gender
     *
     * @var string
     * @Column (name=gender)
     */
    protected $gender;

    /**
     * Name
     *
     * @var string
     * @Column (name=name)
     */
    protected $name;

    /**
     * Active
     *
     * @var bool
     * @Column (name=active)
     */
    protected $active;

}