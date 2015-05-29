<?php

namespace Bookkeeper\UserBundle\Security;

/**
 * Class Token
 * @package Bookkeeper\UserBundle\Security
 */
class Token
{
    /** @var string */
    protected $alphabet;

    /** @var int */
    protected $alphabetLength;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAlphabet([
            implode(range('a', 'z')),
            implode(range('A', 'Z')),
            implode(range(0, 9)),
            '=',
        ]);
    }

    /**
     * @param array $alphabet
     */
    public function setAlphabet($alphabet)
    {
        $this->alphabet = implode($alphabet);
    }

    /**
     * @param int $length   Token length
     *
     * @return string       Generated token string
     */
    public function generate($length)
    {
        $token = '';
        $alphabetLength = strlen($this->alphabet);

        for ($i = 0; $i < $length; $i++) {
            $randomKey = $this->getRandomInteger(0, $alphabetLength);
            $token     .= $this->alphabet[$randomKey];
        }

        return $token;
    }

    /**
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    protected function getRandomInteger($min, $max)
    {
        $range = ($max - $min);

        if ($range < 0) {
            // Not so random...
            return $min;
        }

        $log = log($range, 2);

        // Length in bytes.
        $bytes = (int) ($log / 8) + 1;

        // Length in bits.
        $bits = (int) $log + 1;

        // Set all lower bits to 1.
        $filter = (int) (1 << $bits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            // Discard irrelevant bits.
            $rnd = $rnd & $filter;

        } while ($rnd >= $range);

        return ($min + $rnd);
    }
}
