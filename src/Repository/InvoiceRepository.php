<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function save(Invoice $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Invoice $entity, bool $flush = false): void
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
        return $this->createQueryBuilder('invoice')
            ->orderBy('invoice.id', 'DESC');
    }

    public function queryByProject(Project $project)
    {
        return $this->createQueryBuilder('invoice')
            ->setParameter('project', $project)
            ->join('invoice.project', 'project')
            ->where('project = :project')
            ->orderBy('project.name', 'ASC')
            ->orderBy('invoice.id', 'DESC');
    }

    public function findAwaiting()
    {
        return $this->findBy([
            'paidDate' => null,
        ], [
            'id' => 'DESC',
        ]);
    }
}
