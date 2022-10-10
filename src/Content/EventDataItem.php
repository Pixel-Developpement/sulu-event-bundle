<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Content;

use JMS\Serializer\Annotation as Serializer;
use Pixel\EventBundle\Entity\Event;
use Sulu\Component\SmartContent\ItemInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class EventDataItem implements ItemInterface
{
    private Event $entity;

    public function __construct(Event $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getId(): string
    {
        return (string)$this->entity->getId();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getTitle(): string
    {
        return (string)$this->entity->getName();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getImage(): ?string
    {
        return null;
    }

    public function getResource(): Event
    {
        return $this->entity;
    }
}
