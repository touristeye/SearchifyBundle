<?php

namespace TE\SearchifyBundle\Service;

use Searchify\Api,
    Searchify\Index;

/**
 * SearchifyService is a service that manages Searchify.
 */
class SearchifyService
{
    protected $privateUrl;

    protected $index;

    protected $client;

    /**
     * Constructor.
     *
     * @param  string $privateUrl
     * @param  string $index
     */
    public function __construct($privateUrl, $index)
    {
        $this->privateUrl = $privateUrl;

        $this->client = new Api($privateUrl);
        $this->index  = $this->client->get_index($index);
    }

    /**
     * Search
     *
     * @param string $model
     * @param string $s
     */
    public function search($params) {
        return 'hola';
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

//     public static $SCORING_FUNCTIONS = array(
//         'recent'   => 0,
//         'points'   => 1,
//         'distance' => 2,
//         'distance_and_points' => 3,
//         'bounds'   => 4,
//         'hunch' => 5
//     );

//     private $index = null;

//     /**
//      * Returns the objects resulted of the search
//      *
//      * @param string $q
//      * @param string $lang
//      * @param array $params
//      *
//      * @return IndexTankApiResponse with the results
//      */
//     public function search( $query, $lang, $params=array() ){
//         $query = str_replace('(', '', str_replace(')', '', trim($query)));

//         $model = isset($params['model']) ? $params['model'] : '';
//         $offset = isset($params['offset']) ? $params['offset'] : 0;
//         $limit = isset($params['limit']) ? $params['limit'] : 20;

//         // if we want to search on the title instead of the text
//         $searchOnTitle = isset($params['searchOnTitle']) ? $params['searchOnTitle'] : false;
//         $autocomplete = isset($params['autocomplete']) ? $params['autocomplete'] : false;

//         // For places
//         $placeLevel = isset($params['placeLevel']) ? $params['placeLevel'] : NULL;
//         //$hasHotels = isset($params['hasHotels']) ? $params['hasHotels'] : 0;
//         $category = isset($params['category']) ? $params['category'] : 0;
//         $publishedEN = isset($params['publishedEN']) ? $params['publishedEN'] : 0;
//         $publishedES = isset($params['publishedES']) ? $params['publishedES'] : 0;
//         $parentId = isset($params['parentId']) ? $params['parentId'] : 0;
//         $basicData = isset($params['basicData']) ? $params['basicData'] : false;

//         $lat = isset($params['lat']) ? $params['lat'] : 0;
//         $lng = isset($params['lng']) ? $params['lng'] : 0;

//         $west = isset($params['west']) ? $params['west'] : 0;
//         $east = isset($params['east']) ? $params['east'] : 0;
//         $north = isset($params['north']) ? $params['north'] : 0;
//         $south = isset($params['south']) ? $params['south'] : 0;

//         $scoring_function = isset($params['scoring_function'])
//             ? TESearch::$SCORING_FUNCTIONS[$params['scoring_function']]
//             : TESearch::$SCORING_FUNCTIONS['points'];

//         // filter by categories
//         $category_filters = array();
//         if ( $model )
//         {
//             $category_filters['model'] = $model;
//         }
//         if ( $placeLevel )
//         {
//             $category_filters['placeLevel'] = $placeLevel;
//         }
//         /*if ( $hasHotels )
//         {
//             $category_filters['hasHotels'] = 'true';
//         }*/
//         if ( $category )
//         {
//             $category_filters['placeCategory'] = $category;
//         }
//         if ( $publishedEN )
//         {
//             $category_filters['publishedEN'] = $publishedEN;
//         }
//         if ( $publishedES )
//         {
//             $category_filters['publishedES'] = $publishedES;
//         }

//         // autocomplete search - add *
//         if ( $autocomplete )
//         {
//             $query .= '*';
//         }

//         // autocomplete search
//         if ( $searchOnTitle )
//         {
//             $query = 'title_en:'.$query.' OR title_es:'.$query;
//         }

//         // If we only want the IDs
//         if ( $lang == '' )
//         {
//             $fetch_fields = '';
//             $snippet_field = '';
//             $fetch_categories = false;
//             $fetch_variables = false;
//         }
//         // if we want more info
//         else
//         {
//             // fields to get
//             $fields_lang = ($basicData ? '' : 'ALL_' ) . 'FIELDS_'.strtoupper($lang);
//             $fetch_fields = TESearch::$FIELDS . ','. TESearch::$$fields_lang;
//             $fetch_variables = true;

//             // just return title, lat & lng
//             if ( $basicData )
//             {
//                 $snippet_field = '';
//                 $fetch_categories = false;
//             }
//             // get all the data, get the snippet
//             else
//             {
//                 $snippet_field = '';
//                 //'text_'.$lang;
//                 $fetch_categories = true;
//                 $query_snippet = '';
//                 $previous_symbol = '';

//                 foreach ( explode(' ', $query) as $keyname )
//                 {
//                     // dont search for this special keywords on the snippet
//                     if ( in_array($keyname, array('AND', 'OR', 'NOT')) )
//                     {
//                         $previous_symbol = $keyname;
//                         continue;
//                     }

//                     // dont search for a keyname with a previous NOT or -
//                     if ( $previous_symbol == 'NOT' || substr($keyname, 0, 1) == '-' )
//                     {
//                         $previous_symbol = '';
//                         continue;
//                     }

//                     // delete + from the keyname
//                     if ( substr($keyname, 0, 1) == '+' )
//                     {
//                         $keyname = substr($keyname, 1);
//                     }

//                     $query_snippet .= ($query_snippet != '' ? ' AND ' : '') . $snippet_field.':'.$keyname;
//                 }


//                 if ( $query_snippet == '' )
//                 {
//                     throw new Exception("You have to search for a word");
//                 }

//                 //$query .= ' OR (' . $query_snippet . ')';
//             }
//         }

//         // search
//         $params = array(
//             'scoring_function' => $scoring_function,
//             'start'            => $offset,
//             'len'              => $limit
//         );

//         if ( $snippet_field != '' ) $params['snippet_fields']   = $snippet_field;
//         if ( $fetch_fields != '' )  $params['fetch_fields']     = $fetch_fields;
//         if ( $fetch_categories )    $params['fetch_categories'] = 'true';
//         if ( $fetch_variables )     $params['fetch_variables'] = 'true';
//         if ( $lat != '' && $lng != '' )
//         {
//             $params['variables'] = array(
//                 0 => $lat,
//                 1 => $lng
//             );
//         }

//         if ( $west != '' && $east != '' && $north != '' && $south != '' )
//         {
//             $params['function_filters'] = array(
//                 $scoring_function => array(array(0.01,NULL))
//             );
//             $params['docvar_filters'] = array(
//                 0 => array(array($north, $south)),
//                 1 => array(array($west, $east))
//             );
//             $params['variables'] = array(
//                 0 => $lat,
//                 1 => $lng
//             );

//             /*$params['variables'] = array(
//                 0 => $north,
//                 1 => $south,
//                 2 => $west,
//                 3 => $east
//             );*/
//         }

//         if ( count($category_filters) > 0 )
//         {
//             $params['category_filters'] = json_encode($category_filters);
//         }

//         // we only want places from a parent
//         if ( $parentId )
//         {
//             $query .= ' parent_id:'.$parentId;
//         }

//         $res = $this->index->search($query, $params);

//         return $res;
//     }

// }
