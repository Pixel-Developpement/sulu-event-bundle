<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Link;

use Pixel\EventBundle\Entity\Event;
use Pixel\EventBundle\Repository\EventRepository;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkConfigurationBuilder;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventLinkProvider implements LinkProviderInterface
{
    private EventRepository $eventRepository;
    private TranslatorInterface $translator;

    public function __construct(EventRepository $eventRepository, TranslatorInterface $translator)
    {
        $this->eventRepository = $eventRepository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return LinkConfigurationBuilder::create()
            ->setTitle($this->translator->trans('event'))
            ->setResourceKey(Event::RESOURCE_KEY) // the resourceKey of the entity that should be loaded
            ->setListAdapter('table')
            ->setDisplayProperties(['name'])
            ->setOverlayTitle($this->translator->trans('event'))
            ->setEmptyText($this->translator->trans('event.emptyEvent'))
            ->setIcon('su-calendar')
            ->getLinkConfiguration();
    }

    /**
     * {@inheritdoc}
     */
    public function preload(array $hrefs, $locale, $published = true): array
    {
        if (0 === count($hrefs)) {
            return [];
        }

        $items = $this->eventRepository->findBy(['id' => $hrefs]); // load items by id
        foreach ($items as $item) {
            $result[] = new LinkItem($item->getId(), $item->getName(), $item->getRoutePath(), $item->getEnabled()); // create link-item foreach item
        }

        return $result;
    }
}
