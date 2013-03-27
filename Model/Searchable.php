<?php

namespace TE\SearchifyBundle\Model;

trait Searchable {

    /**
     * The class needs to have this field
     *
     * public static $fieldsToIndex = array();
     */

    /**
     * Return array to index from the given array
     *
     * @param  array $array
     * @return array
     */
    public static function getArrayToIndexFromArray($array) {

        return array();
    }

    /**
     * Return array to index from the current object
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