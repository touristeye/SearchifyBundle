<?php

namespace TE\SearchifyBundle\Listener;

use Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    TE\SearchifyBundle\Service\SearchifyService,
    TE\SearchifyBundle\Event\ObjectEvent,
    TE\SearchifyBundle\Event\SearchifyEvents;

/**
 * SearchifyListener handle Searchable entites
 * Sends the updates to Searchify
 * Listens to PostPersist, PostUpdate, PostRemove lifecycle events
 */
class SearchifyListener implements EventSubscriberInterface
{
    const ANNOTATION_CLASS = 'TE\\SearchifyBundle\\Annotation\\Searchable';

    /**
     * @var SearchifyService
     */
    protected $searchifyService;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @constructor
     *
     * @param SearchifyService $searchifyService
     */
    public function __construct(SearchifyService $searchifyService, \Doctrine\ORM\EntityManager $em)
    {
        $this->searchifyService = $searchifyService;
        $this->em               = $em;
    }

    /**
     * Send the data to Searchify
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function onCreate(ObjectEvent $event)
    {
        $this->add($event);
    }

    /**
     * Send the data to Searchify
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function onUpdate(ObjectEvent $event)
    {
        $this->add($event);
    }

    /**
     * Add the object to Searchify
     * @param ObejctEvent $event
     */
    private function add($event) {

        $entity        = $event->getObject();
        $classMetadata = $this->em->getClassMetadata(get_class($entity));

        if ( $this->isEntitySupported($classMetadata->reflClass, true) ){

            if ( $this->hasTranslations($classMetadata->reflClass, true) ){

                $classNamespace = explode('\\', get_class($entity));
                $entityModel    = array_pop($classNamespace);

                $translations   = $this->em->getRepository('TECoreBundle:'.$entityModel.'Translation')->findBy(array(
                    'translatable' => $entity->getId()
                ));

                foreach ($translations as $tr) {
                    $entity->addTranslation($tr);
                }
            }

            $this->searchifyService->addDocument($entity);
        }
    }

    /**
     * Remove the data from Searchify
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function onRemove(ObjectEvent $event)
    {
        $entity        = $event->getObject();
        $classMetadata = $this->em->getClassMetadata(get_class($entity));

        if ( $this->isEntitySupported($classMetadata->reflClass, true) ){

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

    /**
     * Checks if entity has translations
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until trait is found
     *
     * @return boolean
     */
    private function hasTranslations(\ReflectionClass $reflClass, $isRecursive = false)
    {
        $isSupported = in_array('TE\DoctrineBehaviorsBundle\Model\Translatable\Translatable', $reflClass->getTraitNames());

        while ($isRecursive and !$isSupported and $reflClass->getParentClass()) {
            $reflClass = $reflClass->getParentClass();
            $isSupported = $this->hasTranslations($reflClass, true);
        }

        return $isSupported;
    }

    /**
     * We need to add the following on services.yml
     *     tags:
     *         - { name: kernel.event_subscriber }
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        $events = [
            SearchifyEvents::OBJECT_CREATE => array('onCreate', 0),
            SearchifyEvents::OBJECT_UPDATE => array('onUpdate', 0),
            SearchifyEvents::OBJECT_REMOVE => array('onRemove', 0)
        ];

        return $events;
    }
}
