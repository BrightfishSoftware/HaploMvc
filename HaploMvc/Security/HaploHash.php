<?php
namespace HaploMvc\Security;

/**
 * Class HaploHash
 * @package HaploMvc
 */
class HaploHash
{
    const HASH_BLOWFISH = '$2y$12$'; // use for PHP 5.3.7 and above
    const HASH_SHA512 = '$6$rounds=1000000$'; // use for < PHP 5.3.7

    /** @var array */
    protected $saltLengths = array(
        '$2y$12$' => 22,
        '$6$rounds=1000000$' => 16
    );

    /** @var string */
    protected $secret;
    /** @var string */
    protected $algorithm;

    /**
     * @param string $secret
     * @param string $algorithm
     */
    public function __construct($secret, $algorithm = self::HASH_BLOWFISH)
    {
        $this->secret = $secret;
        $this->algorithm = $algorithm;
    }

    /**
     * @param string $password
     * @param string $salt
     * @return string
     */
    public function generateHash($password, $salt = null)
    {
        $salt = is_null($salt) ? $this->algorithm.$this->generateSalt(
            $this->saltLengths[$this->algorithm]
        ).'$' : $salt;
        return crypt($this->secret.$password, $salt);
    }

    /**
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function checkHash($password, $hash)
    {
        return $this->generateHash($password, $hash) == $hash;
    }

    /**
     * @param int $length
     * @return string
     */
    protected function generateSalt($length)
    {
        return substr(sha1(uniqid(true)), 0, $length);
    }
}
