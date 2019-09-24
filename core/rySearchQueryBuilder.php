<?php

namespace rySearch\core;

use DateTime;
use WP_Query;

class rySearchQueryBuilder
{
    /**
     * @return WP_Query
     */
    public static function getWPQuery()
    {
        $keyword = rySearchController::urlGetParameter(RY_SEARCH_PARAM_KEY);
        $dateRangeParameter = rySearchController::getDateRangeParameter();
        $parameterList = rySearchController::getRYFilterParameterList();

        $query = self::buildQuery($keyword, $dateRangeParameter->startDate, $dateRangeParameter->endDate, $parameterList);

        return $query;
    }

    /**
     * @param string $keyword
     * @param string $startDate
     * @param string $endDate
     * @param array $parameterList
     * @return WP_Query
     */
    private static function buildQuery($keyword, $startDate, $endDate, $parameterList = array())
    {
        // https://developer.wordpress.org/reference/classes/wp_query/
        global $paged;
        $keyCleared = self::clearKeyword($keyword);
        $metaQuery = self::buildDateQuery($startDate, $endDate);
        $taxQuery = self::buildTaxonomyQuery($parameterList, $keyCleared);

        $args = array(
            //'posts_per_page' => 9,
            'post_type'  => 'product',
            'post_status' => 'publish',
            'meta_key' => 'sejour_date_from',
            'orderby' => 'meta_value',
            'order'   => 'ASC',
            'meta_query' => $metaQuery,
            'tax_query' => $taxQuery,
        );

        if ($keyCleared){
            $keySearchArr = array(
                's' => $keyCleared,
                //'posts_per_page' => 9,
                'post_type'  => 'product',
                'post_status' => 'publish',
                'meta_key' => 'sejour_date_from',
                'orderby' => 'meta_value',
                'order'   => 'ASC',
                );

            $wpQueryKey = new WP_Query( $keySearchArr );
            $queryKey = $wpQueryKey->request;
        }

        $wpQueryArgs = new WP_Query( $args );
        $queryArgs = $wpQueryArgs->request;
        $realQuery = "SELECT * FROM ($queryArgs) AS QARG";
        if(isset($queryKey)){
            $realQuery .= " UNION ($queryKey)";
        }

        $realQuery = str_replace(' SQL_CALC_FOUND_ROWS', '', $realQuery);
        $realQuery = str_replace(' ORDER BY wp_postmeta.meta_value ASC LIMIT 0, 9', '', $realQuery);

        // https://regexr.com/397dr
        $query = preg_replace('/\{(.*?)\}/', "%", $realQuery);
        $realWpQuery = self::executeQuery($query);
        $postIdList = array();
        foreach ($realWpQuery as $post) {
            $postIdList[] = $post->ID;
        }

        $realQuerySearch = array(
            'paged' => $paged,
            'post__in' => $postIdList,
            'posts_per_page' => 9,
            'post_type'  => 'product',
            'post_status' => 'publish',
            'meta_key' => 'sejour_date_from',
            'orderby' => 'meta_value',
            'order'   => 'ASC',
        );
        $wpQuerySearch = new WP_Query( $realQuerySearch );

        return $wpQuerySearch;
    }

    private static function parseKeyword($keyword)
    {
        $keyExploded = explode(' ', $keyword);
        $keyParsedArray = [];
        foreach ($keyExploded as $index => $key) {
            $keyParsedArray[] = "%" . $key . "%" ;
        }

        return $keyParsedArray;
    }

    /**
     * @param $string string
     * @return string
     */
    private static function clearKeyword($string)
    {
        $string = html_entity_decode($string, ENT_QUOTES);
        $string = preg_replace("/&#?[a-z0-9]+;/i","", $string); // Remove ascii characters
        $string = remove_accents($string); // Remove accents
        $string = preg_replace('!\s+!', ' ', $string); // Convert multiple consecutive space in single space
        $string = preg_replace("/[^A-Za-z0-9\-\_\'\’\ ]/", "", $string); // Removes special chars

        return trim($string);
    }

    /**
     * @param $startDate
     * @param $endDate
     * @return array
     */
    private static function buildDateQuery($startDate, $endDate)
    {
        // start date between interval
        $dateMetaQuery = array(
            'relation' => 'AND',
            array(
                'key' => 'sejour_date_from',
                'value' => $startDate,
                'compare' => '>=',
            ),
            array(
                'key' => 'sejour_date_from',
                'value' => $endDate,
                'compare' => '<=',
            ),
        );

        return $dateMetaQuery;
    }

    /**
     * @param array $parameterList
     * @param string $keyCleared
     * @return array
     */
    private static function buildTaxonomyQuery($parameterList = array(), $keyCleared = '')
    {
        $taxKeyArr = array();

        if(!empty($parameterList)){
            $taxKeyArr = array('relation' => 'AND');
            foreach ($parameterList as $key => $value){
                if($key === RY_SEARCH_PARAM_PROF){
                    $taxKeyArr[] = self::buildSearchTaxonomyQuery('pa_professeur_organisateur', $value, $keyCleared);
                }
                if($key === RY_SEARCH_PARAM_DESTINATION){
                    $taxKeyArr[] = self::buildSearchTaxonomyQuery('pa_region', $value, $keyCleared);
                }
                if($key === RY_SEARCH_PARAM_TYPE){
                    $taxKeyArr[] = self::buildSearchTaxonomyQuery('pa_type-de-yoga', $value, $keyCleared);
                }
            }
        } elseif($keyCleared) {
            $taxKeyArr = array('relation' => 'OR');
            $taxKeyArr[] = self::buildSearchTaxonomyQuery('pa_professeur_organisateur', '', $keyCleared);
            $taxKeyArr[] = self::buildSearchTaxonomyQuery('pa_region', '', $keyCleared);
            $taxKeyArr[] = self::buildSearchTaxonomyQuery('pa_type-de-yoga', '', $keyCleared);
        }

        return $taxKeyArr;
    }

    private static function buildSearchTaxonomyQuery($taxonomyName, $value = '', $keyCleared = '')
    {
        $taxKeyArr = array();
        if($value){
            $singleHasTaxArr = self::buildSingleTaxonomyQuery($taxonomyName, $value);
        }

        if($keyCleared){
            $nameAsTaxArr = self::buildSingleTaxonomyQuery($taxonomyName, $keyCleared);
            if($nameAsTaxArr){
                session_start();
                $_SESSION['rySearchKeyIsAttribute'] = true;
            }
        }

        if(isset($singleHasTaxArr) && isset($nameAsTaxArr)){
            $base = array('relation' => 'OR');
            $taxKeyArr = array_merge($base, $nameAsTaxArr, $singleHasTaxArr);
        } elseif(isset($nameAsTaxArr)){
            $taxKeyArr = $nameAsTaxArr;
        } elseif(isset($singleHasTaxArr)){
            $taxKeyArr = $singleHasTaxArr;
        }

        return $taxKeyArr;
    }

    private static function buildSingleTaxonomyQuery($taxonomyName, $value)
    {
        $singleTaxKeyArr = null;
        $termList = get_terms($args = array('taxonomy' => $taxonomyName, 'slug' => $value));
        if($termList){
            $term = current($termList);
            $singleTaxKeyArr = array(
                'taxonomy' => $taxonomyName,
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            );
        }

        return $singleTaxKeyArr;
    }

    public static function getCurrentTaxonomyList($taxonomy = null)
    {
        $query = isset($_SESSION['wp_query']) ? $_SESSION['wp_query'] : null;
        $queryToSearch = is_null($query) ? self::getActivePostIDListQuery() : $query->request;
        $termListQuery = self::getTermListQuery($queryToSearch, $taxonomy);
        $termList = self::executeQuery($termListQuery);

        return $termList;
    }

    private static function getActivePostIDListQuery()
    {
        $todayFullFormat = new DateTime();
        $today = $todayFullFormat->format("Y-m-d");

        $post_query = "SELECT DISTINCT post_id FROM `wp_postmeta` WHERE meta_key = 'sejour_date_from' AND meta_value >= '$today'";

        return $post_query;
    }

    private static function getTermListQuery($post_query, $taxonomy = null)
    {
        $taxonomy = is_null($taxonomy) ? '%pa%' : $taxonomy ;
        $term_query = "SELECT DISTINCT term_taxonomy_id FROM `wp_term_relationships` WHERE object_id IN ($post_query)";
        $termIDListQuery = "SELECT DISTINCT term_id FROM `wp_term_taxonomy` WHERE taxonomy LIKE '$taxonomy'";

        $termListQuery = "SELECT * FROM `wp_terms` WHERE term_id IN ($term_query) AND term_id IN ($termIDListQuery) ORDER BY name";

        $termListQuery = str_replace(' SQL_CALC_FOUND_ROWS', '', $termListQuery);
        $termListQuery = str_replace(' ASC LIMIT 0, 9', '', $termListQuery);

        return $termListQuery;
    }

    private static function executeQuery($query)
    {
        global $wpdb;

        $result = $wpdb->get_results( $query );

        return $result;
    }
}