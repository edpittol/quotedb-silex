<?php
namespace QuoteDB\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class AuthorRepository extends EntityRepository
{
    public function autocompleteQuery($query)
    {
        $qb = $this->createQueryBuilder('a');
        $query = $qb
            ->where($qb->expr()->like('a.name', ':query'))
            ->orderBy('a.name', 'ASC')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery();

        return $query->getArrayResult();
    }
}
