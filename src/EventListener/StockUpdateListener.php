<?php

namespace App\EventListener;

use App\Entity\Products;
use App\Entity\StockHistoric;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Symfony\Component\Security\Core\Security;
use function PHPUnit\Framework\isInstanceOf;

class StockUpdateListener implements EventSubscriberInterface
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
      $this->updateHistoric($args);
    }

    public function updateHistoric(OnFlushEventArgs $args): void
    {

        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Products) {
                foreach ($uow->getEntityChangeSet($entity) as $field) {
                    $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity);
                    if (isset($changeset['stock'])) {
                        $previousStock = $changeset['stock'][0];
                        $newStock = $changeset['stock'][1];
                        $quantityChanged = $newStock - $previousStock;

                        $stockHistoric = new StockHistoric();
                        $stockHistoric->setStock($quantityChanged);
                        $stockHistoric->setUser($this->security->getUser());
                        $stockHistoric->setProduct($entity);
                        $em->persist($stockHistoric);
                        $classMetadata = $em->getClassMetadata('App\Entity\StockHistoric');
                        $uow->computeChangeSet($classMetadata, $stockHistoric);
                    }
                }
            }
        }
    }
}