<?php

namespace TE\SearchifyBundle\Listener;

use Doctrine\Common\Annotations\Reader,
    Doctrine\Common\EventSubscriber,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Doctrine\ORM\Events,
    Doctrine\ORM\Event\LifecycleEventArgs,
    TE\SearchifyBundle\Service\TE\SearchifyBundle;

/**
 * SearchifyListener handle Searchable entites
 * Sends the updates to Searchify
 * Listens to PostPersist, PostUpdate, PostRemove lifecycle events
 */
class SearchifyListener implements EventSubscriber
{
    const ANNOTATION_CLASS = 'TE\\SearchifyBundle\\Annotation\\Searchable';

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var SearchifyService
     */
    protected $searchifyService;

    /**
     * @constructor
     *
     * @param Reader $reader
     * @param SearchifyService $searchifyService
     */
    public function __construct(Reader $reader, SearchableListener $searchifyService)
    {
        $this->reader           = $reader;
        $this->searchifyService = $searchifyService;
    }

    /**
     * Send the data to Searchify
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postPersist(LifecycleEventArgs $eventArgs)
    {
        $em =$eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass, true)) {

            $this->searchifyService->add($entity);
        }
    }

    /**
     * Send the data to Searchify
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postUpdate(LifecycleEventArgs $eventArgs)
    {
        $em =$eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass, true)) {

            $this->searchifyService->add($entity);
        }
    }

    /**
     * Send the data to Searchify
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postRemove(LifecycleEventArgs $eventArgs)
    {
        $em =$eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        $classMetadata = $em->getClassMetadata(get_class($entity));

        if ($this->isEntitySupported($classMetadata->reflClass, true)) {

            $this->searchifyService->remove($entity);
        }
    }

    /**
     * Checks if entity supports Searchable
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until trait is found
     *
     * @return boolean
     */
    private function isEntitySupported(\ReflectionClass $reflClass, $isRecursive = false)
    {
        $isSupported = in_array('TE\SearchifyBundle\Model\Searchable', $reflClass->getTraitNames());

        while ($isRecursive and !$isSupported and $reflClass->getParentClass()) {
            $reflClass = $reflClass->getParentClass();
            $isSupported = $this->isEntitySupported($reflClass, true);
        }

        return $isSupported;
    }

    public function getSubscribedEvents()
    {
        $events = [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove
        ];

        return $events;
    }
}
