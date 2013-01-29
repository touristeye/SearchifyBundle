<?php

namespace TE\SearchifyBundle\Model;

trait Searchable {

    /** Fields to get from searchify */
    protected $fields = '';

    /**
     * Get array to index
     *
     * @return array
     */
    public function getArrayToIndex() {

        return array();
    }

    /**
     * @return true
     */
    public function isSearchable()
    {
        return true;
    }

}