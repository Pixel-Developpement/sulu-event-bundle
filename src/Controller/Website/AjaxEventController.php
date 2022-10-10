<?php

namespace Pixel\EventBundle\Controller\Website;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\EventBundle\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class AjaxEventController extends AbstractController
{
    /**
     * @Route("events_block/{limit}", name="events_block", option={"expose"=true}, method={"POST"})
     */
     public function eventsBlock(EntityManagerInterface $entityManager, int $limit): JsonResponse
     {
         $events = $entityManager->getRepository(Event::class)->findAllScheduledEvents($limit);
         if(empty($events)){
             return new JsonResponse(["success" => true, "template" => $this->renderView("events/empty.html.twig")]);
         }
         return new JsonResponse(["success" => true, "template" => $this->renderView("events/index.html.twig", ["events" => $events])]);
     }
}
