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

            $payload = [
                'alias' => $alias,
                'outgoing_url' => $url,
            ];

            if (empty($alias)) {
                throw new ShortUrlException('failed to build alias');
            }

            list($result, $statusCode) = $this->query('PUT', 'link/add', $payload);

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


    /**
     * @param string $alias
     *
     * @return ShortUrl
     * @throws ShortUrlException
     */
    public function info(string $alias): ShortUrl
    {
        list($result, $statusCode) = $this->query('POST', 'link/details', ['alias' => $alias]);

        if ($statusCode !== 200 || empty($result['success'])) {
            throw new ShortUrlException('server request has failed: ' . $result['failure'] ?? 'unknown error');
        }

        $result = $result['details'];

        return new ShortUrl($result['alias'], $result['full_alias'], $result['outgoing_url'], $result['submit_time']);
    }


    /**
     * @param string $alias
     *
     * @throws ShortUrlException
     */
    public function delete(string $alias): void
    {
        list($result, $statusCode) = $this->query('POST', 'link/details', ['alias' => $alias]);

        if ($statusCode !== 200 || empty($result['success'])) {
            throw new ShortUrlException('server request has failed: ' . $result['failure'] ?? 'unknown error');
        }

        list($result, $statusCode) = $this->query('DELETE', 'link/delete', ['link_id' => $result['details']['link_id']]);

        if ($statusCode !== 200 || empty($result['success'])) {
            throw new ShortUrlException('server request has failed: ' . $result['failure'] ?? 'unknown error');
        }
    }


    /**
     * @param string $payload
     * @param string $method PUT, POST or GET
     *
     * @return array
     * @throws ShortUrlException
     */
    protected function query(string $method, string $action, array $payload = null): array
    {
        $headers = [];
        $url = sprintf('%s/linkr/api/%s', $this->url, $action);

        if (in_array($method, ['GET', 'HEAD'])) {
            $url .= '?' . http_build_query($payload);
        }

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (in_array($method, ['PUT', 'POST', 'DELETE'])) {
            $payload = json_encode($payload);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Content-Length: ' . strlen($payload);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['X-Linkr-Key: ' . $this->key], $headers));

        $error = curl_exec($ch);
        $result = json_decode($error, true);

        if ($error = curl_errno($ch)) {
            curl_close($ch);
            throw new ShortUrlException('cURL error: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if (empty($result)) {
            throw new ShortUrlException('empty server response');
        }

        return [$result, $statusCode];
    }

}