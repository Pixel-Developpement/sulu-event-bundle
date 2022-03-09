<?php

namespace Pixel\EventBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Pixel\EventBundle\Entity\Event;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

class EventRepository extends EntityRepository implements DataProviderRepositoryInterface
{
    use DataProviderRepositoryTrait;

    public function create(string $locale): Event
    {
        $event = new Event();
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
