<?php

namespace Ku\SsoServerBundle\Security;

/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class DomainChecker
{
    private $domains;

    /**
     * DomainChecker constructor.
     *
     * @param $domains
     */
    public function __construct($domains) { $this->domains = $domains; }

    public function isRegistered($url)
    {

    }

    protected function getSchemeAndHost($url)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        dump($scheme, $host);

        return rtrim($scheme . '://' . $host, '/');
    }
}