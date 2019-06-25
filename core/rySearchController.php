<?php

namespace rySearch\core;

use DateTime;
use WP_Query;

class rySearchController
{
    /**
     * @return WP_Query
     */
    public static function getWPQuery()
    {
        $keyword = self::urlGetParameter(RY_SEARCH_PARAM_KEY);
        $duration = self::urlGetParameter(RY_SEARCH_PARAM_DURATION);
        $prof = self::urlGetParameter(RY_SEARCH_PARAM_PROF);
        $region = self::urlGetParameter(RY_SEARCH_PARAM_DESTINATION);
        $additionalParams = array(
            'organisateur_name' => $prof,
            'pa_region' => $region,
        );

        if(strlen($duration) == 1){ // is day
            $startDateReq = self::urlGetParameter('date');
            //if dayDuration is picked but not a day from the calendar, start from today
            $startDate = $startDateReq ? date('Y-m-d', strtotime($startDateReq)) : date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+$duration day", strtotime($startDate)));
        } elseif(strlen($duration) > 4) { //is month
            $month = $duration . '-01';
            $startDate = date("Y-m-01", strtotime($month));
            $endDate = date("Y-m-t", strtotime($month));
        } else { // All from today to today + 100 years
            $startDate = date('Y-m-d'); // from today
            $endDate = date('Y-m-d', strtotime("+100 year", strtotime($startDate)));
        }

        $query = rySearchController::buildQuery($keyword, $startDate, $endDate, $additionalParams);

        return $query;
    }

    /**
     * @param string $keyword
     * @param string $startDate
     * @param string $endDate
     * @param array $additionalParams
     * @return WP_Query
     */
    public static function buildQuery($keyword, $startDate, $endDate, $additionalParams = array())
    {
        global $paged;

        $paged = ( get_query_var('page') ) ? get_query_var('page') : 1;

        $keyCleared = self::clearKeyword($keyword);
        $keySearchArr = $keyCleared ? array('s' => $keyCleared) : array();

        $metaQuery = self::buildMetaQuery($startDate, $endDate, $additionalParams);
        $taxQuery = self::buildTaxonomyQuery($additionalParams);

        $args = array(
            'paged' => $paged,
            'posts_per_page' => 10,
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
        $query->isRYSearch = true;

        return $query;
    }

    /**
     * @param $startDate
     * @param $endDate
     * @param array $additionalParams
     * @return string
     */
    public static function buildMetaQuery($startDate, $endDate, $additionalParams = array())
    {
        $inMetaArr = ['organisateur_name'];

        $baseMetaQuery = array(
            'relation' => 'AND',
            array(
                'key' => 'sejour_date_from',
                'value' => $startDate,
                'compare' => '>=',
            ),
            array(
                'key' => 'sejour_date_to',
                'value' => $endDate,
                'compare' => '<=',
            ),
        );

        $metaKeyArr = array();
        foreach ($additionalParams as $key => $value){
            if($value && in_array($key, $inMetaArr)){
                $metaKeyArr [] = array(
                    'key' => $key,
                    'value' => $value,
                    'compare' => '=',
                );
            }
        }

        $metaQuery = array_merge($baseMetaQuery, $metaKeyArr);

        return $metaQuery;
    }

    /**
     * @param array $additionalParams
     * @return array
     */
    public static function buildTaxonomyQuery($additionalParams = array())
    {
        $inTaxArr = ['pa_region'];

        $taxKeyArr = array();
        foreach ($additionalParams as $key => $value){
            if($value && in_array($key, $inTaxArr)){
                $taxKeyArr[] = array(
                    'taxonomy' => $key,
                    'field'    => 'term_id',
                    'terms'    => array( $value),
                );
            }
        }

        return $taxKeyArr;
    }

    /**
     * @return string
     */
    public static function realKeyword()
    {
        $keyword = self::urlGetParameter(RY_SEARCH_PARAM_KEY);
        $searchedKey = str_replace("\\", "", $keyword);

        return $searchedKey;
    }

    /**
     * @param $string string
     * @return string
     */
    public static function clearKeyword($string)
    {
        $string = html_entity_decode($string, ENT_QUOTES);
        $string = preg_replace("/&#?[a-z0-9]+;/i","", $string); // Remove ascii characters
        $string = remove_accents($string); // Remove accents
        $string = preg_replace('!\s+!', ' ', $string); // Convert multiple consecutive space in single space
        $string = preg_replace("/[^A-Za-z0-9\-\_\'\’\ ]/", "", $string); // Removes special chars

        return trim($string);
    }

    public static function urlGetParameter($parameter)
    {
        if($parameter === 'page'){
            $parameterValue = $_GET['page'] === null ?
                1 : (int)filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_FLOAT);
        } else {
            $parameterValue = filter_var($_GET[$parameter], FILTER_SANITIZE_STRING);
        }
        return $parameterValue;
    }

    public static function unsetSearchSession()
    {
        if($_SESSION){
            foreach ($_SESSION as $sessionName => $sessionItem) {
                if(strpos($sessionName, 'rysbd') !== false){
                    unset($_SESSION[$sessionName]);
                    wp_reset_postdata();
                }
            }
        }
    }

    public static function getSelectedDuration($duration)
    {
        $dayDurationArray = self::getDayArr();
        $monthDurationArray = self::getNextTwelveMonthsArr();

        if($dayDurationArray[$duration]){
            $durationTXT = $dayDurationArray[$duration];
        } elseif($monthDurationArray[$duration]){
            $durationTXT = $monthDurationArray[$duration];
        } else {
            $durationTXT = '';
        }

        return $durationTXT;
    }

    public static function getDayDurationHTML($duration)
    {
        $dayDurationArray = self::getDayArr();
        $dayDurationHTML = self::buildRadio($dayDurationArray, 'duration', $duration, 'day');

        return $dayDurationHTML;
    }

    public static function getMonthDurationHTML($duration)
    {
        $monthDurationArray = self::getNextTwelveMonthsArr();

        $monthDurationHTML = self::buildRadio($monthDurationArray, 'duration', $duration, 'month');

        return $monthDurationHTML;
    }

    public static function getDayArr()
    {
        $dayDurationArray = array(
            "1"=>__('1 jour', RY_SEARCH_TXT_DOMAIN),
            "3"=>__('3 jours', RY_SEARCH_TXT_DOMAIN),
            "7"=>__('7 jours', RY_SEARCH_TXT_DOMAIN)
        );

        return $dayDurationArray;
    }

    public static function getNextTwelveMonthsArr()
    {
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $monthValue = date("Y-m", strtotime( date( 'Y-m' )." +$i months"));
            $date = new DateTime($monthValue . '-01');
            $monthName = $date->format('Y F');
            $months[$monthValue] = $monthName;
        }

        return $months;
    }

    public static function buildRadio(array $inputArr, $name, $duration = '', $additionalClass = '')
    {
        $radioHTML = '<div class="multiChoiceGroupSection">';
        foreach ($inputArr as $inputValue => $inputName) {
            $isChecked = $duration == $inputValue ? 'checked' : '';
            $groupCheckedClass = $isChecked ? 'durationGroupSelected' : '';
            $inputCheckedClass = $isChecked ? 'durationSelected' : '';
            $id = "$name"."_"."$inputValue";
            $groupClass = "inputRadio durationSingleChoice " . $groupCheckedClass;
            $inputClass = "multiChoiceGroupInput durationChoice " . $additionalClass .' '. $inputCheckedClass;
            $singleInputHTML = '<div class="'.$groupClass.'">';
            $singleInputHTML .= '<input type="radio" class="'.$inputClass.'"
               id="'.$id.'"
               name="'.$name.'"
               value="'.$inputValue.'" 
               ' . $isChecked . '/>';
            $singleInputHTML .= '<label class="label-text" for="'.$id.'">'.$inputName.'</label>';
            $singleInputHTML .= '</div>';
            $radioHTML .= $singleInputHTML;
        }
        $radioHTML .= '</div>';

        return $radioHTML;
    }


    // SEARCH BY DESTINATION

    public static function buildDestinationHtml()
    {
        $destinationList = self::getDestinationList();
        $destinationSelect = self::buildULFilter($destinationList, RY_SEARCH_PARAM_DESTINATION);

        return $destinationSelect;
    }

    public static function getDestinationList()
    {
        global $wpdb;

        $tax_query = "SELECT term_id FROM `wp_term_taxonomy` WHERE taxonomy = 'pa_region'";
        $name_query = "SELECT DISTINCT * FROM `wp_terms` WHERE term_id IN ($tax_query)";
        $destinationList = $wpdb->get_results( $name_query );

        return $destinationList;
    }


    // SEARCH BY PROFESSEUR

    public static function buildActiveProfHtml()
    {
        $activeProfList = self::getActiveProfList();
        $activeProfSelect = self::buildULFilter($activeProfList, RY_SEARCH_PARAM_PROF);

        return $activeProfSelect;
    }

    public static function getActiveProfList()
    {
        global $wpdb;

        $todayFullFormat = new DateTime();
        $today = $todayFullFormat->format("Y-m-d");

        $post_query = "SELECT post_id FROM `wp_postmeta` WHERE meta_key = 'sejour_date_from' AND meta_value >= '$today'";
        $name_query = "SELECT DISTINCT meta_value FROM `wp_postmeta` WHERE meta_key = 'organisateur_name' AND post_id IN ($post_query)";
        $query = "SELECT * FROM `wp_terms` WHERE name IN ($name_query)";
        $activeProfList = $wpdb->get_results( $query );

        return $activeProfList;
    }

    // MAIN
    public static function buildSelectFilter($inputList, $inputName)
    {
        $selectHTML = '<select class="rysbd_select" name="'.$inputName.'">';
        $selectHTML .= '<option value="" disabled selected>Filtre:</option>';
        foreach ($inputList as $singleInput){
            $name = $singleInput->name;

            $optionHTML = '<option value="'.$name.'">' . $name . '</option>';
            $selectHTML .= $optionHTML;
        }
        $selectHTML .= '</select>';

        return $selectHTML;
    }

    public static function buildULFilter($inputList, $inputName)
    {
        $url = RY_SEARCH_ACTION_URL;
        $isFirst = true;
        foreach ($_GET as $name => $value){
            if($name !== $inputName){
                $char = $isFirst ? '?' : '&';
                $url .= $char . $name .'='.$value;
                $isFirst = false;
            }
        }

        $inputChar = $isFirst ? '?' : '&';

        $defaultUrl = $url. $inputChar . $inputName;
        //$ulHTML = '<input type="text" class="rysbd_select_input" name="'.$inputName.'" value="'.$_GET[$inputName].'">';
        $selected = $_GET[$inputName] ? $_GET[$inputName] : 'Filtre:';
        $ulHTML = '<div class="rysbd_select_input">'.$selected.'</div>';
        $ulHTML .= '<ul class="rysbd_select hideBox">';
        foreach ($inputList as $singleInput){
            $name = $singleInput->name;
            $currentUrl = $defaultUrl . '=' . urlencode($name);
            $liHTML = '<li><a data-type="select" href="'.$currentUrl.'">' . $name . '</a></li>';
            $ulHTML .= $liHTML;
        }
        $ulHTML .= '</ul>';

        return $ulHTML;
    }

}
