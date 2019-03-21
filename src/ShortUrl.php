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


    public function __construct(string $alias, string $shortUrl, string $longUrl)
    {

        $this->alias = $alias;
        $this->shortUrl = $shortUrl;
        $this->longUrl = $longUrl;
    }
}