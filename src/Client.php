<?php

namespace Bnb\Linkr;

class Client
{

    const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $key;


    /**
     * Client constructor.
     *
     * @param string $url Endpoint URL of Linkr Server
     * @param string $key API Key
     */
    public function __construct(string $url, string $key)
    {

        $this->url = rtrim($url, '/');
        $this->key = $key;
    }


    /**
     * Creates a new short URL
     *
     * @param string $url the target (long) URL to shorten
     *
     * @return ShortUrl
     * @throws ShortUrlException
     */
    public function shorten(string $url): ShortUrl
    {
        $alias = '';
        $length = 3;
        $tries = 3;
        $alphabetLength = strlen(self::ALPHABET);

        do {
            for ($i = 0; $i < $length; $i++) {
                $alias .= self::ALPHABET[rand(0, $alphabetLength - 1)];
            }

            $payload = json_encode([
                'alias' => $alias,
                'outgoing_url' => $url,
            ]);

            if (empty($alias)) {
                throw new ShortUrlException('failed to build alias');
            }

            $ch = curl_init(sprintf('%s/linkr/api/link/add', $this->url));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload),
                    'X-Linkr-Key: ' . $this->key,
                ]
            );

            $result = curl_exec($ch);
            $result = json_decode($result, true);

            if ($error = curl_errno($ch)) {
                curl_close($ch);
                throw new ShortUrlException('cURL error: ' . $error);
            }

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if (empty($result)) {
                throw new ShortUrlException('empty server response');
            }

            if ($statusCode !== 200 || empty($result['success'])) {

                if (empty($result['failure']) || ! in_array($result['failure'], ['failure_unavailable_alias', 'failure_reserved_alias'])) {
                    throw new ShortUrlException('server request has failed: ' . $result['failure'] ?? 'unknown error');
                }
            }

            if ($result['success']) {
                break;
            }

            if ($tries === 0) {
                ++$length;
                $tries = 3;
            }

            $alias = '';
        } while ($alias === null && $length < 20);

        if (empty($alias)) {
            throw new ShortUrlException('failed to build a valid alias');
        }

        return new ShortUrl($alias, sprintf('%s/%s', $this->url, $alias), $url);
    }

}