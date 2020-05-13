<?php
namespace App\Helpers;

use GuzzleHttp\Client;

class OneMapHelper
{
    /**
     * Base url of onemap api.
     *
     * @var string
     */
    private $baseUrl = 'https://developers.onemap.sg/';

    /**
     * Response from unsplash api.
     */
    private $response;

    /**
     * Calls unsplash api.
     *
     * @param string $url
     * @param array  $params
     *
     * @return mix
     */
    protected function call($url, $params)
    {
        $client = new Client([
            'base_uri' => $this->baseUrl,
        ]);
        $response = $client->request('GET', $url, [
            // 'headers' => [
            //     'Authorization'  => 'Client-ID '.env('UNSPLASH_APPID'),
            // ],
            'form_params' => $params,
            'query'       => $params,
        ]);
        return $response->getBody();
    }

    /**
     * Retrieve a single page from the list of all photos.
     *
     * @param array $params
     *
     * @return mixed
     */
    public function search(array $params)
    {
        return $this->call('commonapi/search', $params);
    }
}