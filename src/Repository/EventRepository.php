<?php

namespace Pixel\EventBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pixel\EventBundle\Entity\Event;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

class EventRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, new ClassMetadata(Event::class));
    }

    public function create(string $locale): Event
    {
        $event = new Event();
        $event->setDefaultLocale($locale);
        $event->setLocale($locale);
        return $event;
    }

    public function save(Event $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id, string $locale): ?Event
    {
        $event = $this->find($id);
        if (!$event) {
            return null;
        }
        $event->setLocale($locale);
        return $event;
    }

    public function findAllForSitemap(int $page, int $limit): array
    {
        $offset = ($page * $limit) - $limit;
        $criteria = [
            'enabled' => true,
        ];
        return $this->findBy($criteria, [], $limit, $offset);
    }

    public function countForSitemap()
    {
        $query = $this->createQueryBuilder('e')
            ->select('count(e)');
        return $query->getQuery()->getSingleScalarResult();
    }

    public function findAllScheduledEvents(int $limit)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.enabled = 1 AND (e.startDate >= :now OR (e.endDate IS NOT NULL AND e.endDate >= :now))')
            ->orderBy("e.startDate", "ASC")
            ->setMaxResults($limit)
            ->setParameter("now", (new \DateTimeImmutable())->format("Y-m-d"));
        return $query->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function appendJoins(QueryBuilder $queryBuilder, $alias, $locale)
    {
        //$queryBuilder->addSelect('category')->leftJoin($alias . '.category', 'category');
        //$queryBuilder->addSelect($alias.'.category');
    }

    public function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias)
    {
        return $alias . '.category';
        //$queryBuilder->addSelect($alias.'.category');
    }

    protected function appendSortByJoins(QueryBuilder $queryBuilder, string $alias, string $locale): void
    {
        $queryBuilder->innerJoin($alias . '.translations', 'translation', Join::WITH, 'translation.locale = :locale');
        $queryBuilder->setParameter('locale', $locale);
    }
}
