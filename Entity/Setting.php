<?php

namespace Pixel\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="event_setting")
 * @Serializer\ExclusionPolicy("all")
 */
class Setting implements AuditableInterface
{
    use AuditableTrait;

    public const RESOURCE_KEY = "event_settings";
    public const FORM_KEY = "event_settings";
    public const SECURITY_CONTEXT = "event_settings.settings";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Serializer\Expose()
     */
    private ?MediaInterface $defaultImage = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     */
    private ?int $limitBlockEvent = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return array|null
     */
    public function getDefaultImage(): ?MediaInterface
    {
        return $this->defaultImage;
    }

    /**
     * @param MediaInterface|null $defaultImage
     */
    public function setDefaultImage(?MediaInterface $defaultImage): void
    {
        $this->defaultImage = $defaultImage;
    }

    /**
     * @return int|null
     */
    public function getLimitBlockEvent(): ?int
    {
        return $this->limitBlockEvent;
    }

    /**
     * @param int|null $limitBlockEvent
     */
    public function setLimitBlockEvent(?int $limitBlockEvent): void
    {
        $this->limitBlockEvent = $limitBlockEvent;
    }
}
