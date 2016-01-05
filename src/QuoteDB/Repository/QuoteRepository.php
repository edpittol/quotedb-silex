<?php
namespace QuoteDB\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class QuoteRepository extends EntityRepository
{
    public function homepageQuotes()
    {
        $qb = $this->createQueryBuilder('q');
        $query = $qb
            ->where($qb->expr()->like('q.approved', ':approved'))
            ->orderBy('q.id', 'DESC')
            ->setParameter('approved', true)
            ->getQuery();

        return $query->getResult();
    }
}
