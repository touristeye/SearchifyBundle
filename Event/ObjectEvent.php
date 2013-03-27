<?php

namespace TE\SearchifyBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class ObjectEvent extends Event
{
    protected $object;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }
}