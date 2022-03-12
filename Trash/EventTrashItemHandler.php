<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Trash;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\EventBundle\Admin\EventAdmin;
use Pixel\EventBundle\Domain\Event\EventRestoredEvent;
use Pixel\EventBundle\Entity\Event;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\RouteBundle\Entity\Route;
use Sulu\Bundle\TrashBundle\Application\DoctrineRestoreHelper\DoctrineRestoreHelperInterface;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfiguration;
use Sulu\Bundle\TrashBundle\Application\RestoreConfigurationProvider\RestoreConfigurationProviderInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\RestoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashItemHandler\StoreTrashItemHandlerInterface;
use Sulu\Bundle\TrashBundle\Domain\Model\TrashItemInterface;
use Sulu\Bundle\TrashBundle\Domain\Repository\TrashItemRepositoryInterface;

class EventTrashItemHandler implements StoreTrashItemHandlerInterface, RestoreTrashItemHandlerInterface, RestoreConfigurationProviderInterface
{
    private TrashItemRepositoryInterface $trashItemRepository;
    private EntityManagerInterface $entityManager;
    private DoctrineRestoreHelperInterface $doctrineRestoreHelper;
    private DomainEventCollectorInterface $domainEventCollector;

    public function __construct(
        TrashItemRepositoryInterface $trashItemRepository,
        EntityManagerInterface $entityManager,
        DoctrineRestoreHelperInterface $doctrineRestoreHelper,
        DomainEventCollectorInterface $domainEventCollector
    )
    {
        $this->trashItemRepository = $trashItemRepository;
        $this->entityManager = $entityManager;
        $this->doctrineRestoreHelper = $doctrineRestoreHelper;
        $this->domainEventCollector = $domainEventCollector;
    }

    public static function getResourceKey(): string
    {
        return Event::RESOURCE_KEY;
    }

    public function store(object $resource, array $options = []): TrashItemInterface
    {
        $image = $resource->getImage();
        $pdf = $resource->getPdf();

        $data = [
            "name" => $resource->getName(),
            "description" => $resource->getDescription(),
            "slug" => $resource->getRoutePath(),
            "seo" => $resource->getSeo(),
            "startDate" => $resource->getStartDate(),
            "endDate" => $resource->getEndDate(),
            "enabled" => $resource->getEnabled(),
            "imageId" => $image ? $image->getId() : null,
            "pdfId" => $pdf ? $pdf->getId() : null,
            "location" => $resource->getLocation(),
            "url" => $resource->getUrl(),
            "email" => $resource->getEmail(),
            "phoneNumber" => $resource->getPhoneNumber(),
            "cards" => $resource->getCards(),
        ];

        return $this->trashItemRepository->create(
            Event::RESOURCE_KEY,
            (string)$resource->getId(),
            $resource->getName(),
            $data,
            null,
            $options,
            Event::SECURITY_CONTEXT,
            null,
            null
        );
    }

    public function restore(TrashItemInterface $trashItem, array $restoreFormData = []): object
    {
        $data = $trashItem->getRestoreData();
        $eventId = (int)$trashItem->getResourceId();
        $event = new Event();
        $event->setName($data['name']);
        $event->setDescription($data['description']);
        $event->setRoutePath($data['slug']);
        $event->setSeo($data['seo']);
        $event->setStartDate(new \DateTimeImmutable($data['startDate']['date']));
        if(isset($data['endDate'])){
            $event->setEndDate(new \DateTimeImmutable($data['endDate']['date']));
        }
        $event->setEnabled($data['enabled']);
        $event->setImage($this->entityManager->find(MediaInterface::class, $data['imageId']));
        if ($data['pdfId']) {
            $event->setPdf($this->entityManager->find(MediaInterface::class, $data['pdfId']));
        }
        $event->setLocation($data['location']);
        $event->setUrl($data['url']);
        $event->setEmail($data['email']);
        $event->setPhoneNumber($data['phoneNumber']);
        $event->setCards($data['cards']);
        $this->domainEventCollector->collect(
            new EventRestoredEvent($event, $data)
        );

        $this->doctrineRestoreHelper->persistAndFlushWithId($event, $eventId);
        $this->createRoute($this->entityManager, $eventId, $event->getRoutePath(), Event::class);
        $this->entityManager->flush();
        return $event;
    }

    private function createRoute(EntityManagerInterface $manager, int $id, string $slug, string $class)
    {
        $route = new Route();
        $route->setPath($slug);
        $route->setLocale('fr');
        $route->setEntityClass($class);
        $route->setEntityId($id);
        $route->setHistory(0);
        $route->setCreated(new \DateTime());
        $route->setChanged(new \DateTime());
        $manager->persist($route);
    }

    public function getConfiguration(): RestoreConfiguration
    {
        return new RestoreConfiguration(null, EventAdmin::EVENT_EDIT_FORM_VIEW, ['id' => 'id']);
    }
}
