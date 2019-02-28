<?php
/**
 * Created by PhpStorm.
 * User: gliu
 * Date: 8/19/18
 * Time: 10:49 AM
 */

namespace App\Repositories;

use App\Http\Clients\AnalyticsClient;

class Messages extends BaseRepository
{
    public function __construct()
    {

    }

    public function getDataFromPostsService($query)
    {
        $client = new AnalyticsClient();
        $responsePosts = $client->getMethod('/messages' . $query,
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

    public function getUnreadMessagesLast7daysCount()
    {
        $messages = $this->getDataFromPostsService($this->getDateQueryString(7,1));
        $count_unread = 0;
        foreach ($messages as $message) {
            if ($message['archived'] == 0){
                $count_unread++;
            }
        }
        return $count_unread;
    }
}