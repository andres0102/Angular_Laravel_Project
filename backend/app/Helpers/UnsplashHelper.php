<?php
namespace App\Helpers;

use GuzzleHttp\Client;

class UnsplashHelper
{
    /**
     * Base url of unsplash api.
     *
     * @var string
     */
    private $baseUrl = 'https://api.unsplash.com/';

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
            'headers' => [
                'Authorization'  => 'Client-ID '.env('UNSPLASH_APPID'),
            ],
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
    public function photos(array $params)
    {
        return $this->call('photos', $params);
    }

    /**
     * Search for the photos with search query.
     *
     * @param string $query
     * @param array  $params
     *
     * @return mixed
     */
    public function search($query, array $params)
    {
        $params['query'] = $query;
        return $this->call('search/photos', $params);
    }

    /**
     * Retrieve curated list of photos.
     *
     * @param array $params
     *
     * @return mixed
     */
    public function curated(array $params)
    {
        return $this->call('photos/curated', $params);
    }

    /**
     * Retrieve a single photo using id.
     *
     * @param string $id
     * @param array  $params
     *
     * @return mixed
     */
    public function single($id, array $params)
    {
        return $this->call('photos/'.$id, $params);
    }

    /**
     * Retrieve a random photo.
     *
     * @param array $params
     *
     * @return mixed
     */
    public function random(array $params)
    {
        return $this->call('photos/random', $params);
    }

    /**
     * Retrieve statistics of the photo.
     *
     * @param string $id
     * @param array  $params
     *
     * @return mixed
     */
    public function statistics($id, array $params)
    {
        return $this->call('/photos/'.$id.'/statistics', $params);
    }

    /**
     * Retrieve download link of the photo.
     *
     * @param string $id
     * @param array  $params
     *
     * @return mixed
     */
    public function download($id, array $params)
    {
        return $this->call('/photos/'.$id.'/download', $params);
    }
}