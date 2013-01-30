<?php

namespace TE\SearchifyBundle\Service;

use Searchify\Api,
    Searchify\Index;

/**
 * SearchifyService is a service that manages Searchify.
 */
class SearchifyService
{
    /* Private url on Searchify */
    protected $privateUrl;

    /* Api client */
    protected $client;

    /* Index we are connected */
    protected $index;

    /* Functions you have defined on Searchify to order the results */
    protected $scoringFunctions;

    /* Fields to get */
    protected $fetchFields = '';

    /* Do we get categories */
    protected $fetchCategories = false;

    /* Do we get variables */
    protected $fetchVariables = false;

    /* Term to search for */
    protected $term = '';

    /* More filters on the search */
    protected $extraSearch = '';

    /* Autocomplete search */
    protected $autocomplete = false;

    /* Fields we want to search on */
    protected $fieldsToSearch = array();

    /* Filter by categories */
    protected $categoryFilters = array();

    /* Variables used in function filters */
    protected $variables = array();

    /* Function filters */
    protected $functionFilters = array();

    /* Docvar filters */
    protected $docvarFilters = array();

    /* Scoring tunctions to use */
    protected $scoringFunction;

    /* Offset */
    protected $start = 0;

    /* Limit */
    protected $len = 10;

    /**
     * Constructor.
     *
     * @param  string $privateUrl
     * @param  string $index
     */
    public function __construct($params)
    {
        $this->privateUrl = $params['private_url'];

        $this->client = new Api($this->privateUrl);
        $this->index  = $this->client->get_index($params['main_index']);

        $this->scoringFunctions = $params['scoring_functions'];
    }

    /**
     * Set the fields to fetch on Searchify
     *
     * @param  string $fetchFields
     * @return SearchifyService
     */
    public function setFetchFields($fetchFields) {
        $this->fetchFields = $fetchFields;
        return $this;
    }

    /**
     * Set to true if we want to fetch the categories
     *
     * @param  boolean $fetchCategories
     * @return SearchifyService
     */
    public function setFetchCategories($fetchCategories) {
        $this->fetchCategories = $fetchCategories;
        return $this;
    }

    /**
     * Set to true if we want to fetch the variables
     *
     * @param  boolean $fetchVariables
     * @return SearchifyService
     */
    public function setFetchVariables($fetchVariables) {
        $this->fetchVariables = $fetchVariables;
        return $this;
    }

    /**
     * Set the term to search
     *
     * @param  string $term
     * @return SearchifyService
     */
    public function setTerm($term) {
        $this->term = $term;
        return $this;
    }

    /**
     * Set extra search. It's useful for when we need to filter by two fields and one of them is autocomplete
     *
     * @param  string $extraSearch
     * @return SearchifyService
     */
    public function setExtraSearch($extraSearch) {
        $this->extraSearch = $extraSearch;
        return $this;
    }

    /**
     * Set the true if we want to do an open search
     *
     * @param  boolean $autocomplete
     * @return SearchifyService
     */
    public function setAutocomplete($autocomplete) {
        $this->autocomplete = $autocomplete;
        return $this;
    }

    /**
     * Set the fields on where we want to search
     *
     * @param  array $fieldsToSearch
     * @return SearchifyService
     */
    public function setFieldsToSearch($fieldsToSearch) {
        $this->fieldsToSearch = $fieldsToSearch;
        return $this;
    }

    /**
     * Set the filters on categories
     *
     * @param  array $categoryFilters
     * @return SearchifyService
     */
    public function setCategoryFilters($categoryFilters) {
        $this->categoryFilters = $categoryFilters;
        return $this;
    }

    /**
     * Set variables
     *
     * @param  array $variables
     * @return SearchifyService
     */
    public function setVariables($variables) {
        $this->variables = $variables;
        return $this;
    }

    /**
     * Set function filters
     *
     * @param  array $functionFilters
     * @return SearchifyService
     */
    public function setFunctionFilters($functionFilters) {

        // convert the key to the actual scoring function
        $filters = array();
        foreach ( $functionFilters as $key => $data ) {
            $filters[ $this->scoringFunctions[ $key ] ] = $data;
        }
        $this->functionFilters = $filters;
        return $this;
    }

    /**
     * Set docvar filters
     *
     * @param  array $docvarFilters
     * @return SearchifyService
     */
    public function setDocvarFilters($docvarFilters) {
        $this->docvarFilters = $docvarFilters;
        return $this;
    }

    /**
     * Set the scoring function to use
     *
     * @param  string $scoringFunction
     * @return SearchifyService
     */
    public function setScoringFunction($scoringFunction) {
        $this->scoringFunction = $this->scoringFunctions[ $scoringFunction ];
        return $this;
    }

    /**
     * Set the offset
     *
     * @param  int $start
     * @return SearchifyService
     */
    public function setFirstResult($start) {
        $this->start = $start;
        return $this;
    }

    /**
     * Set the limit
     *
     * @param  int $len
     * @return SearchifyService
     */
    public function setMaxResults($len) {
        $this->len = $len;
        return $this;
    }

    /**
     * Get results using all the data
     *
     * @return  array
     */
    public function getResults() {

        // Create query
        $query = $this->term;

        // if we want to do a wide search
        if ( $this->autocomplete ) {
            $query .= '*';
        }

        if ( count($this->fieldsToSearch) > 0 ) {

            $temp_query = array();

            foreach ( $this->fieldsToSearch as $field ) {
                $temp_query[] = $field . ':' . $query;
            }

            $query = join(' OR ', $temp_query);
        }

        // if we want to do a wide search
        if ( $this->extraSearch ) {
            $query = '(' .$query. ') AND ' . $this->extraSearch;
        }

        // array with params
        $params = array(
            'fetch_fields'     => $this->fetchFields,
            //'snippet_fields'   => $this->snippetFields,
            'fetch_categories' => $this->fetchCategories,
            'fetch_variables'  => $this->fetchVariables,
            'function_filters' => $this->functionFilters,
            'category_filters' => $this->categoryFilters,
            'docvar_filters'   => $this->docvarFilters,
            'variables'        => $this->variables,
            'scoring_function' => $this->scoringFunction,
            'start'            => $this->start,
            'len'              => $this->len
        );

        return $this->index->search($query, $params);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'searchify';
    }
}

// class TESearch {

//     public static $FIELDS = "lat,lng,photo,placeCategory,placeLevel,parent_id,countAttractions,countRestaurants,countEntertainment,countActivities,countHotels,countTours,bookingId,hostelbookersId,topruralTitle,topruralParents";
//     public static $FIELDS_EN = "title_en,belongsto_en,url_en";
//     public static $FIELDS_ES = "title_es,belongsto_es,url_es";
//     public static $ALL_FIELDS_EN = "title_en,url_en,belongsto_en,text_en,url_en";
//     public static $ALL_FIELDS_ES = "title_es,url_es,belongsto_es,text_es,url_es";

