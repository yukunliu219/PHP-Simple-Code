<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/19/18
 * Time: 10:36 AM
 */

namespace App\Repositories;

use App\Http\Clients\AnalyticsClient;

class Posts extends BaseRepository
{
    public function __construct()
    {

    }

    public function getDataFromPostsService($query)
    {
        $client = new AnalyticsClient(); //
        $responsePosts = $client->getMethod('/posts' . $query,
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

        if (is_null($responsePosts)) {
            return null;
        }
        return json_decode($responsePosts->getBody(), true);
    }

    public function getPostCountsByWeeks($weeks)
    {
        $posts = $this->getDataFromPostsService($this->getLastWeekDateQueryString($weeks * 7,1));
        return count($posts);
    }
}