<?php

namespace Pixel\EventBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="Pixel\EventBundle\Repository\EventRepository")
 * @Serializer\ExclusionPolicy("all")
 * */
class Event
{
    public const RESOURCE_KEY = 'events';
    public const LIST_KEY = 'events';
    public const FORM_KEY = 'event_details';
    public const SEO_FORM_KEY = 'seo';
    public const SECURITY_CONTEXT = 'event.events';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Serializer\Expose()
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Serializer\Expose()
     */
    private $endDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     */
    private ?bool $enabled;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @Serializer\Expose()
     */
    private ?MediaInterface $image;

    /**
     * @ORM\ManyToOne(targetEntity=MediaInterface::class)
     * @Serializer\Expose()
     */
    private ?MediaInterface $pdf = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Serializer\Expose()
     */
    private ?array $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private ?string $url;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Serializer\Expose()
     */
    private ?string $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     */
    private ?string $phoneNumber;

    /**
     * @var Collection<string, EventTranslation>
     * @ORM\OneToMany(targetEntity="Pixel\EventBundle\Entity\EventTranslation", mappedBy="event", cascade={"ALL"}, indexBy="locale")
     * @Serializer\Exclude
     */
    private $translations;

    /**
     * @var string
     */
    private string $locale = 'fr';

    public function __construct()
    {
        $this->enabled = true;
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @Serializer\VirtualProperty(name="name")
     * @return string
     */
    public function getName(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getName();
    }

    protected function getTranslation(string $locale): ?EventTranslation
    {
        if (!$this->translations->containsKey($locale)) {
            return null;
        }
        return $this->translations->get($locale);
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setName($name);
        return $this;
    }

    protected function createTranslation(string $locale): EventTranslation
    {
        $translation = new EventTranslation($this, $locale);
        $this->translations->set($locale, $translation);
        return $translation;
    }

    /**
     * @Serializer\VirtualProperty(name="description")
     * @return string|null
     */
    public function getDescription(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getDescription();
    }

    /**
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setDescription($description);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="route")
     * @return string|null
     */
    public function getRoutePath(): ?string
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getRoutePath();
    }

    public function setRoutePath(string $routePath): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setRoutePath($routePath);
        return $this;
    }

    /**
     * @Serializer\VirtualProperty(name="seo")
     * @return array|null
     */
    public function getSeo(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            return null;
        }
        return $translation->getSeo();
    }

    protected function emptySeo(): array
    {
        return [
            "seo" => [
                "title" => "",
                "description" => "",
                "keywords" => "",
                "canonicalUrl" => "",
                "noIndex" => "",
                "noFollow" => "",
                "hideinSitemap" => ""
            ]
        ];
    }

    /**
     * @Serializer\VirtualProperty(name="ext")
     * @return array|null
     */
    public function getExt(): ?array
    {
        $translation = $this->getTranslation($this->locale);
        if(!$translation){
            return null;
        }
        return ($translation->getSeo()) ? ['seo' => $translation->getSeo()] : $this->emptySeo();
    }

    /**
     * @param array|null $seo
     * @return self
     */
    public function setSeo(?array $seo): self
    {
        $translation = $this->getTranslation($this->locale);
        if (!$translation) {
            $translation = $this->createTranslation($this->locale);
        }
        $translation->setSeo($seo);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     */
    public function setStartDate($startDate): void
    {
        $this->startDate = $startDate;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     */
    public function setEndDate($endDate): void
    {
        $this->endDate = $endDate;
    }

    /**
     * @return array|null
     */
    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * @param array|null $location
     */
    public function setLocation(?array $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param ?string $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param ?string $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return array<string, mixed>
     *
     * @Serializer\VirtualProperty()
     * @Serializer\SerializedName("image")
     */
    public function getImageData(): ?array
    {
        if ($image = $this->getImage()) {
            return [
                'id' => $image->getId(),
            ];
        }

        return null;
    }

    /**
     * @return MediaInterface|null
     */
    public function getImage(): ?MediaInterface
    {
        return $this->image;
    }

    /**
     * @param MediaInterface|null $image
     */
    public function setImage(?MediaInterface $image): void
    {
        $this->image = $image;
    }

    /**
     * @return MediaInterface|null
     */
    public function getPdf(): ?MediaInterface
    {
        return $this->pdf;
    }

    /**
     * @param MediaInterface|null $pdf
     */
    public function setPdf(?MediaInterface $pdf): void
    {
        $this->pdf = $pdf;
    }

    /**
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    /**
     * @param bool|null $enabled
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }
}
