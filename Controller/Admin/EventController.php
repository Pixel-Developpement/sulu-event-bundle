<?php

declare(strict_types=1);

namespace Pixel\EventBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Pixel\DirectoryBundle\Repository\CardRepository;
use Pixel\EventBundle\Common\DoctrineListRepresentationFactory;
use Pixel\EventBundle\Domain\Event\EventCreatedEvent;
use Pixel\EventBundle\Domain\Event\EventModifiedEvent;
use Pixel\EventBundle\Domain\Event\EventRemovedEvent;
use Pixel\EventBundle\Entity\Event;
use Pixel\EventBundle\Repository\EventRepository;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\MediaBundle\Media\Manager\MediaManagerInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Bundle\TrashBundle\Application\TrashManager\TrashManagerInterface;
use Sulu\Component\Rest\AbstractRestController;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\Exception\RestException;
use Sulu\Component\Rest\RequestParametersTrait;
use Sulu\Component\Security\SecuredControllerInterface;
use Sulu\Component\Webspace\Manager\WebspaceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @RouteResource("event")
 */
class EventController extends AbstractRestController implements ClassResourceInterface, SecuredControllerInterface
{

    use RequestParametersTrait;

    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var DoctrineListBuilderFactoryInterface
     */
    private DoctrineListRepresentationFactory $doctrineListRepresentationFactory;



    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    private WebspaceManagerInterface $webspaceManager;
    private RouteManagerInterface $routeManager;
    private RouteRepositoryInterface $routeRepository;
    private MediaManagerInterface $mediaManager;
    private TrashManagerInterface $trashManager;
    private DomainEventCollectorInterface $domainEventCollector;
    private EventRepository $repository;
    private CardRepository $cardRepository;

    public function __construct(
        ViewHandlerInterface              $viewHandler,
        DoctrineListRepresentationFactory $doctrineListRepresentationFactory,
        EntityManagerInterface            $entityManager,
        WebspaceManagerInterface          $webspaceManager,
        RouteManagerInterface             $routeManager,
        RouteRepositoryInterface          $routeRepository,
        MediaManagerInterface             $mediaManager,
        TrashManagerInterface             $trashManager,
        DomainEventCollectorInterface     $domainEventCollector,
        EventRepository                   $repository,
        CardRepository                    $cardRepository,
        ?TokenStorageInterface            $tokenStorage = null
    )
    {
        $this->viewHandler = $viewHandler;
        $this->doctrineListRepresentationFactory = $doctrineListRepresentationFactory;
        $this->entityManager = $entityManager;
        $this->webspaceManager = $webspaceManager;
        $this->routeManager = $routeManager;
        $this->routeRepository = $routeRepository;
        $this->mediaManager = $mediaManager;
        $this->trashManager = $trashManager;
        $this->domainEventCollector = $domainEventCollector;
        $this->repository = $repository;
        $this->cardRepository = $cardRepository;
        parent::__construct($viewHandler, $tokenStorage);
    }

    public function cgetAction(Request $request): Response
    {
        $locale = $request->query->get('locale');
        $listRepresentation = $this->doctrineListRepresentationFactory->createDoctrineListRepresentation(
            Event::RESOURCE_KEY,
            [],
            ['locale' => $locale]
        );

        return $this->handleView($this->view($listRepresentation));
    }

    public function getAction(int $id, Request $request): Response
    {
        $event = $this->load($id, $request);
        if (!$event) {
            throw new NotFoundHttpException();
        }
        return $this->handleView($this->view($event));
    }

    protected function load(int $id, Request $request): ?Event
    {
        return $this->repository->findById($id, (string)$this->getLocale($request));
    }

    public function putAction(Request $request, int $id): Response
    {
        $event = $this->load($id, $request);
        if (!$event) {
            throw new NotFoundHttpException();
        }
        $data = $request->request->all();
        $this->mapDataToEntity($data, $event, $request);
        $this->updateRoutesForEntity($event);
        $this->domainEventCollector->collect(
            new EventModifiedEvent($event, $data)
        );
        $this->entityManager->flush();
        $this->save($event);
        return $this->viewHandler->handle(View::create($event));
    }

    protected function mapDataToEntity(array $data, Event $entity, Request $request): void
    {
        $endDate = $data['endDate'] ?? null;
        $imageId = $data['image']['id'] ?? null;
        $enabled = $data['enabled'] ?? null;
        $seo = (isset($data['ext']['seo'])) ? $data['ext']['seo'] : null;
        $url = $data['url'] ?? null;
        $email = $data['email'] ?? null;
        $phoneNumber = $data['phoneNumber'] ?? null;
        $pdf = $data['pdf']['id'] ?? null;
        $images = $data['images'] ?? null;

        $entity->setName($data['name']);
        $entity->setStartDate(new \DateTimeImmutable($data['startDate']));
        $entity->setEndDate($endDate ? new \DateTimeImmutable($data['endDate']) : null);
        $entity->setDescription($data['description']);
        $entity->setEnabled($enabled);
        $entity->setRoutePath($data['routePath']);
        $entity->setImage($imageId ? $this->mediaManager->getEntityById($data['image']['id']) : null);
        $entity->setPdf($pdf ? $this->mediaManager->getEntityById($data['pdf']['id']) : null);
        $entity->setLocation($data['location']);
        $entity->setSeo($seo);
        $entity->setUrl($url);
        $entity->setEmail($email);
        $entity->setPhoneNumber($phoneNumber);
        $entity->setImages($images);
    }

    protected function updateRoutesForEntity(Event $entity): void
    {
        // create route for all locales of the application because event entity is not localized
        foreach ($this->webspaceManager->getAllLocales() as $locale) {
            $this->routeManager->createOrUpdateByAttributes(
                Event::class,
                (string)$entity->getId(),
                $locale,
                $entity->getRoutePath(),
            );
        }
    }

    protected function save(Event $event): void
    {
        $this->repository->save($event);
    }

    public function postAction(Request $request): Response
    {
        $event = $this->create($request);
        $data = $request->request->all();
        $this->mapDataToEntity($data, $event, $request);
        $this->save($event);
        $this->updateRoutesForEntity($event);
        $this->domainEventCollector->collect(
            new EventCreatedEvent($event, $data)
        );
        $this->entityManager->flush();
        return $this->handleView($this->view($event, 201));
    }

    protected function create(Request $request): Event
    {
        return $this->repository->create((string)$this->getLocale($request));
    }

    public function deleteAction(int $id): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($id);
        $eventName = $event->getName();
        if ($event) {
            $this->trashManager->store(Event::RESOURCE_KEY, $event);
            $this->entityManager->remove($event);
            $this->removeRoutesForEntity($event);
            $this->domainEventCollector->collect(
                new EventRemovedEvent($id, $eventName)
            );
        }
        $this->entityManager->flush();
        return $this->viewHandler->handle(View::create());
    }

    protected function removeRoutesForEntity(Event $entity): void
    {
        // remove route for all locales of the application because event entity is not localized
        foreach ($this->webspaceManager->getAllLocales() as $locale) {
            $routes = $this->routeRepository->findAllByEntity(
                Event::class,
                (string)$entity->getId(),
                $locale
            );

            foreach ($routes as $route) {
                $this->routeRepository->remove($route);
            }
        }
    }

    /**
     * @Rest\Post("/events/{id}")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EntityNotFoundException
     */
    public function postTriggerAction(int $id, Request $request): Response
    {
        $action = $this->getRequestParameter($request, 'action', true);
        //$locale = $this->getRequestParameter($request, 'locale', true);

        try {
            switch ($action) {
                case 'enable':
                    $item = $this->entityManager->getReference(Event::class, $id);
                    $item->setEnabled(true);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                case 'disable':
                    $item = $this->entityManager->getReference(Event::class, $id);
                    $item->setEnabled(false);
                    $this->entityManager->persist($item);
                    $this->entityManager->flush();
                    break;
                default:
                    throw new BadRequestHttpException(sprintf('Unknown action "%s".', $action));
            }
        }
        catch (RestException $exc) {
            $view = $this->view($exc->toArray(), 400);
            return $this->handleView($view);
        }

        return $this->viewHandler->handle(View::create($item));
    }

    public function getSecurityContext(): string
    {
        return Event::SECURITY_CONTEXT;
    }
}
