<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll()
    {
        return $this->queryAll()
            ->getQuery()
            ->getResult();
    }

    public function queryAll()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.name', 'ASC');
    }

    public function queryByClient(Client $client)
    {
        return $this->createQueryBuilder('p')
            ->setParameter('clientId', $client->getId())
            ->join('p.client', 'c')
            ->where('c.id = :clientId')
            ->orderBy('c.name', 'ASC')
            ->addOrderBy('p.name', 'ASC');
    }
}
