<?php
/**
 * linkr-php-client
 *
 * @author    Jérémy GAULIN <jeremy@bnb.re>
 * @copyright 2019 - B&B Web Expertise
 */

namespace Bnb\Linkr;

class ShortUrl
{

    /**
     * @var string
     */
    public $alias;

    /**
     * @var string
     */
    public $shortUrl;

    /**
     * @var string
     */
    public $longUrl;

    /**
     * @var
     */
    public $createdAt;


    public function __construct($alias, $shortUrl, $longUrl, $createdAt = null)
    {

        $this->alias = $alias;
        $this->shortUrl = $shortUrl;
        $this->longUrl = $longUrl;
        $this->createdAt = empty($createdAt) ? time() : $createdAt;
    }
}