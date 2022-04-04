<?php

namespace Pixel\EventBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Pixel\EventBundle\Entity\Setting;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SettingsExtension extends AbstractExtension
{
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('event_settings', [$this, 'eventSettings']),
        ];
    }

    public function eventSettings()
    {
        return $this->entityManager->getRepository(Setting::class)->findOneBy([]);
    }
}
