<?php

namespace App\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Entity\Product;
use App\Entity\StockHistoric;
use Symfony\Component\Security\Core\Security;

class StockMovementSubscriber implements EventSubscriber
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
        ];
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Product) {
            return;
        }

        $em = $args->getEntityManager();
        $changeset = $em->getUnitOfWork()->getEntityChangeSet($entity);

        if (isset($changeset['stock'])) {
            $previousStock = $changeset['stock'][0];
            $newStock = $changeset['stock'][1];
            $quantityChanged = $newStock - $previousStock;

            $stockHistoric = new StockHistoric();
            $stockHistoric->setProduct($entity);
            $stockHistoric->setQuantity($quantityChanged);
            $stockHistoric->setUser($this->security->getUser());
            $stockHistoric->setMovementDate(new \DateTime());

            $em->persist($stockHistoric);
        }
    }
}
