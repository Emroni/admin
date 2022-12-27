<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\Time;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Time>
 *
 * @method Time|null find($id, $lockMode = null, $lockVersion = null)
 * @method Time|null findOneBy(array $criteria, array $orderBy = null)
 * @method Time[]    findAll()
 * @method Time[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Time::class);
    }

    public function save(Time $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Time $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAll()
    {
        return $this->findBy([], [
            'date' => 'DESC',
        ]);
    }

    public function findByClient(Client $client)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('client', $client)
            ->leftJoin('time.task', 'task')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.client', 'client')
            ->where('client = :client')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByProject(Project $project)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('project', $project)
            ->leftJoin('time.task', 'task')
            ->leftJoin('task.project', 'project')
            ->where('project = :project')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTask(Task $task)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('task', $task)
            ->leftJoin('time.task', 'task')
            ->where('task = :task')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByTaskAndDate(Task $task, DateTime $date)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('task', $task)
            ->setParameter('date', $date->format('Y-m-d'))
            ->leftJoin('time.task', 'task')
            ->where('time.task = :task')
            ->andWhere('time.date = :date')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBillable()
    {
        return $this->createQueryBuilder('time')
            ->where('time.invoice is NULL')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBillableByClient(Client $client)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('client', $client)
            ->leftJoin('time.task', 'task')
            ->leftJoin('task.project', 'project')
            ->leftJoin('project.client', 'client')
            ->where('client = :client')
            ->andWhere('time.invoice is NULL')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBillableByProject(Project $project)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('project', $project)
            ->leftJoin('time.task', 'task')
            ->leftJoin('task.project', 'project')
            ->where('project = :project')
            ->andWhere('time.invoice is NULL')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBillableByTask(Task $task)
    {
        return $this->createQueryBuilder('time')
            ->setParameter('task', $task)
            ->leftJoin('time.task', 'task')
            ->where('task = :task')
            ->andWhere('time.invoice is NULL')
            ->orderBy('time.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
