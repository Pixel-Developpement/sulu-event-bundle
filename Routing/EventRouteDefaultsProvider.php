<?php

namespace Pixel\EventBundle\Routing;

use Pixel\EventBundle\Controller\Website\EventController;
use Pixel\EventBundle\Entity\Event;
use Pixel\EventBundle\Repository\EventRepository;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

class EventRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        return [
            '_controller' => EventController::class . '::indexAction',
            'event' => $object ?: $this->eventRepository->findById($id, $locale),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        return true;
    }

    public function supports($entityClass)
    {
        return Event::class === $entityClass;
    }
}
