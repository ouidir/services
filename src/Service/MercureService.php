<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureService
{
    public function __construct(
        #[Autowire('%mercure_topic%')]
        private readonly string $mercureTopic,
        private readonly HubInterface $hub
    ) {
    }

    public function push(string $topic, string $data): string
    {
        $topics = $this->mercureTopic . $topic;

        $update = new Update(
            topics: $topics,
            data: $data,
            private: false,
            id: null,
            type: $topic
        );

        return $this->hub->publish($update);
    }
}
