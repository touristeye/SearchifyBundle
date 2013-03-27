<?php

namespace TE\SearchifyBundle\Event;

final class SearchifyEvents
{
    /**
     * The searchify.object event is thrown each time an object is created, updated or removed
     * in the system.
     *
     * The event listener receives an
     * TE\SearchifyBundle\Event\ObjectEvent instance.
     *
     * @var string
     */
    const OBJECT_CREATE = 'searchify.object_create';
    const OBJECT_UPDATE = 'searchify.object_update';
    const OBJECT_REMOVE = 'searchify.object_remove';

}