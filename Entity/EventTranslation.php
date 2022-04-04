<?php

namespace Pixel\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Component\Persistence\Model\AuditableInterface;
use Sulu\Component\Persistence\Model\AuditableTrait;

/**
 * @ORM\Entity()
 * @ORM\Table(name="event_translation")
 * @ORM\Entity(repositoryClass="Pixel\EventBundle\Repository\EventRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class EventTranslation implements AuditableInterface
{
    use AuditableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @var Event
     * @ORM\ManyToOne(targetEntity="Pixel\EventBundle\Entity\Event", inversedBy="translations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $event;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private string $locale;

    /**
     * @ORM\Column(type="string")
     * @Serializer\Expose()
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     *
     * @Serializer\Expose()
     */
    private string $routePath;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     */
    private ?array $seo = null;

    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = trim($name);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getRoutePath(): string
    {
        return $this->routePath ?? '';
    }

    /**
     * @param string $routePath
     */
    public function setRoutePath(string $routePath): void
    {
        $this->routePath = $routePath;
    }

    /**
     * @return array|null
     */
    public function getSeo(): ?array
    {
        return $this->seo;
    }

    /**
     * @param array|null $seo
     */
    public function setSeo(?array $seo): void
    {
        $this->seo = $seo;
    }
}
