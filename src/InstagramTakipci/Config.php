<?php

namespace InstagramTakipci;

/**
 * Class for storing the configuration for the application.
 */
class Config
{

    /**
     * IG username
     *
     * @var string
     */
    protected $username;

    /**
     * IG password
     *
     * @var string
     */
    protected $password;

    /**
     * Array containing hashtags for liking pictures.
     *
     * @var array
     */
    protected $hashTags;

    /**
     * Array containing comments to write to pictures.
     *
     * @var array
     */
    protected $comments;

    /**
     * Low limit for random sleep time.
     *
     * @var int
     */
    protected $sleepLowLimit;

    /**
     * Low limit for random sleep time.
     *
     * @var int
     */
    protected $sleepHighLimit;

    public function __construct(
    $username, $password, $hashTags, $comments, $sleepLowLimit, $sleepHighLimit
    )
    {
        $this->username = $username;
        $this->password = $password;
        $this->hashTags = $hashTags;
        $this->comments = $comments;
        $this->sleepLowLimit = $sleepLowLimit;
        $this->sleepHighLimit = $sleepHighLimit;
    }

    public static function create(
    $username, $password, $hashTags = array(), $comments = array(), $sleepLowLimit = 5, $sleepHighLimit = 30
    )
    {
        return new static($username, $password, $hashTags, $comments, $sleepLowLimit, $sleepHighLimit);
    }

    /**
     * Gets the IG username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the IG username.
     *
     * @param string $username the username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the IG password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the IG password.
     *
     * @param string $password the password
     *
     * @return self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Gets the Array containing hashtags for liking pictures..
     *
     * @return array
     */
    public function getHashTags()
    {
        return $this->hashTags;
    }

    /**
     * Sets the Array containing hashtags for liking pictures..
     *
     * @param array $hashTags the hash tags
     *
     * @return self
     */
    public function setHashTags(array $hashTags)
    {
        $this->hashTags = $hashTags;

        return $this;
    }

    /**
     * Gets the Array containing comments to write to pictures..
     *
     * @return array
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Sets the Array containing comments to write to pictures..
     *
     * @param array $comments the comments
     *
     * @return self
     */
    public function setComments(array $comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Gets the Low limit for random sleep time..
     *
     * @return int
     */
    public function getSleepLowLimit()
    {
        return $this->sleepLowLimit;
    }

    /**
     * Sets the Low limit for random sleep time..
     *
     * @param int $sleepLowLimit the sleep low limit
     *
     * @return self
     */
    public function setSleepLowLimit($sleepLowLimit)
    {
        $this->sleepLowLimit = $sleepLowLimit;

        return $this;
    }

    /**
     * Gets the Low limit for random sleep time..
     *
     * @return int
     */
    public function getSleepHighLimit()
    {
        return $this->sleepHighLimit;
    }

    /**
     * Sets the Low limit for random sleep time..
     *
     * @param int $sleepHighLimit the sleep high limit
     *
     * @return self
     */
    public function setSleepHighLimit($sleepHighLimit)
    {
        $this->sleepHighLimit = $sleepHighLimit;

        return $this;
    }

}
