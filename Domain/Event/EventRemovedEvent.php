<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Domain\Event;

use Pixel\EventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Domain\Event\DomainEvent;

class EventRemovedEvent extends DomainEvent
{
    private int $id;
    private string $title;

    public function __construct(int $id, string $title)
    {
        parent::__construct();
        $this->id = $id;
        $this->title = $title;
    }

    public function getEventType(): string
    {
        return 'removed';
    }

    public function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function getResourceId(): string
    {
        return (string)$this->id;
    }

    public function getResourceTitle(): ?string
    {
        return $this->title;
    }

    public function getResourceSecurityContext(): ?string
    {
        return Event::SECURITY_CONTEXT;
    }
}
