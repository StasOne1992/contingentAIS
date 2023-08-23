<?php

namespace App\Service\Messenger;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\Update;

class BackgroudMessage
{
    private
    function push($topics, $type, $icon, $header, $message,): Response
    {
        $update = new Update(
            $topics,
            json_encode(['type' => $type, 'header' => $header, 'icon' => $icon, 'message' => $message])
        );
        $this->hub->publish($update);

        return new Response('published!');
    }
}