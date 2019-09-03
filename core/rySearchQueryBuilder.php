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
        global $paged;
        
        $keyCleared = self::clearKeyword($keyword);
        $keySearchArr = $keyCleared ? array('s' => $keyCleared) : array();

        $metaQuery = self::buildDateQuery($startDate, $endDate);
        $taxQuery = self::buildTaxonomyQuery($parameterList);

        $args = array(
            'paged' => $paged,
            'posts_per_page' => 9,
            'post_type'  => 'product',
            'post_status' => 'publish',
            'meta_key' => 'sejour_date_from',
            'orderby' => 'meta_value',
            'order'   => 'ASC',
            'meta_query' => $metaQuery,
            'tax_query' => $taxQuery,
        );

        $queryArgs = array_merge($keySearchArr, $args);

        $query = new WP_Query( $queryArgs );

        return $query;
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
     * @return array
     */
    private static function buildTaxonomyQuery($parameterList = array())
    {
        $taxKeyArr = count($parameterList) > 1 ? array('relation' => 'AND') : array();

        foreach ($parameterList as $key => $value){
            if($key === RY_SEARCH_PARAM_PROF){
                $taxKeyArr[] = self::buildSingleTaxonomyQuery($value, 'pa_professeur_organisateur');
            }
            if($key === RY_SEARCH_PARAM_DESTINATION){
                $taxKeyArr[] = self::buildSingleTaxonomyQuery($value, 'pa_region');
            }
            if($key === RY_SEARCH_PARAM_TYPE){
                $taxKeyArr[] = self::buildSingleTaxonomyQuery($value, 'pa_type-de-yoga');
            }
        }

        return $taxKeyArr;
    }

    private static function buildSingleTaxonomyQuery($value, $taxonomyName)
    {
        $termList = get_terms($args = array('taxonomy' => $taxonomyName, 'slug' => $value));
        $term = current($termList);
        $singleTaxKeyArr = array(
            'taxonomy' => $taxonomyName,
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        );

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

        $termListQuery = "SELECT * FROM `wp_terms` WHERE term_id IN ($term_query) AND term_id IN ($termIDListQuery)";

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