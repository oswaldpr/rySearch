<?php

namespace rySearch\core;

use DateTime;
use WP_Query;

class rySearchController
{
    public static function getActionFormUrl()
    {
        $actionUrl = RY_SEARCH_ACTION_URL;
        $isFirst = true;
        foreach ($_GET as $name => $value){
            if($value !== ''){
                $char = $isFirst ? '?' : '&';
                $actionUrl .= $char . $name .'='.$value;
                $isFirst = false;
            }
        }

        return $actionUrl;
    }

    private static function getURIParts()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uriArr = explode('?', $uri);
        $paramArr = explode('&', $uriArr[1]);

        $uriParts = new \stdClass();
        $uriParts->base = $uriArr[0];
        $uriParts->paramArr = $paramArr;

        return $uriParts;
    }

    public static function getDefaultSearchUrl($paramToBeDefault)
    {
        $uriParts = self::getURIParts();
        $uri = $uriParts->base;
        $isFirst = true;
        foreach ($uriParts->paramArr as $param) {
            if(strpos($param, $paramToBeDefault) === false){
                $char = $isFirst ? '?' : '&';
                $uri .= $char . $param;
                $isFirst = false;
            }
        }

        return $uri;
    }

    /**
     * @return WP_Query
     */
    public static function getWPQuery()
    {
        $keyword = self::urlGetParameter(RY_SEARCH_PARAM_KEY);
        $dateRangeParameter = self::getDateRangeParameter();
        $parameterList = self::getRYFilterParameterList();

        $query = rySearchQueryBuilder::buildQuery($keyword, $dateRangeParameter->startDate, $dateRangeParameter->endDate, $parameterList);

        return $query;
    }

    /**
     * @return \stdClass
     */
    public static function getDateRangeParameter()
    {
        $dates = self::urlGetParameter(RY_SEARCH_PARAM_DATES);
        if($dates){
            $dateArray = explode(' to ', $dates);
            $startDate = date('Y-m-d', strtotime($dateArray[0]));
            $endDate = date('Y-m-d', strtotime($dateArray[1]));
        } else {
            // All from today to today + 100 years
            $startDate = date('Y-m-d'); // from today
            $endDate = date('Y-m-d', strtotime("+100 year", strtotime($startDate)));
        }

        $dateRangeParameter = new \stdClass();
        $dateRangeParameter->startDate = $startDate;
        $dateRangeParameter->endDate = $endDate;

        return $dateRangeParameter;
    }

    /**
     * @return string
     */
    public static function getDateRangeDefaultValue()
    {
        $dateRangeParameter = rySearchController::getDateRangeParameter();
        $startDate = isset($dateRangeParameter->startDate) ? date("d/m/Y", strtotime($dateRangeParameter->startDate)) : '';
        $endDate = isset($dateRangeParameter->endDate) ? date("d/m/Y", strtotime($dateRangeParameter->endDate)) : '';
        $dateRangeValue = 'Date de début --> Date de fin';
        $dateRangeValue = $startDate . ' --> ' . $endDate;

        return $dateRangeValue;
    }

    private static function getRYFilterParameterList()
    {
        $profParameter = self::urlGetParameter(RY_SEARCH_PARAM_PROF);
        $destinationParameter = self::urlGetParameter(RY_SEARCH_PARAM_DESTINATION);
        $typeParameter = self::urlGetParameter(RY_SEARCH_PARAM_TYPE);

        $parameterList = [];
        $parameterList = isset($profParameter) && $profParameter !== '' ?
            array_merge($parameterList, array(RY_SEARCH_PARAM_PROF => $profParameter)) : $parameterList;
        $parameterList = isset($destinationParameter) && $destinationParameter !== '' ?
            array_merge($parameterList, array(RY_SEARCH_PARAM_DESTINATION => $destinationParameter)) : $parameterList;
        $parameterList = isset($typeParameter) && $typeParameter !== '' ?
            array_merge($parameterList, array(RY_SEARCH_PARAM_TYPE => $typeParameter)) : $parameterList;

        return $parameterList;
    }

    public static function getRefererParameters()
    {
        $refererParameters = array();
        $referer = $_SERVER['HTTP_REFERER'];

        $parameterStr = explode('?', $referer)[1];
        $parameterList = explode('&', $parameterStr);
        foreach ($parameterList as $param) {
            $singleParam = explode('=', $param);
            $refererParameters[$singleParam[0]] = $singleParam[1];
        }

        return $refererParameters;
    }

    public static function redirectUrl($refererParameters)
    {
        foreach ($refererParameters as $key => $value) {
            if(!$_GET[$key] && $_GET[$key] !== ''){
                $_GET[$key] = $value;
            }
        }

        return self::getActionFormUrl();
    }

    public static function urlGetParameter($parameter)
    {
        if($parameter === 'page'){
            $parameterValue = $_GET['page'] === null ?
                1 : (int)filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_FLOAT);
        } else {
            $parameterValue = filter_var($_GET[$parameter], FILTER_SANITIZE_STRING);
        }

        $parameterValue = $parameterValue === 'all' ? '' : $parameterValue;

        return $parameterValue;
    }
    // SEARCH BY DESTINATION
    public static function buildDestinationHtml()
    {
        $destinationList = rySearchQueryBuilder::getCurrentTaxonomyList('pa_region');
        $destinationULSelect = self::buildULFilter($destinationList, RY_SEARCH_PARAM_DESTINATION, 'Destination');

        return $destinationULSelect;
    }

    // SEARCH BY PROFESSEUR
    public static function buildProfHtml()
    {
        $profList = rySearchQueryBuilder::getCurrentTaxonomyList('pa_professeur_organisateur');
        $profULSelect = self::buildULFilter($profList, RY_SEARCH_PARAM_PROF, 'Professeur');

        return $profULSelect;
    }

    // SEARCH BY TYPE
    public static function buildTypeHtml()
    {
        $typeList = rySearchQueryBuilder::getCurrentTaxonomyList('pa_type-de-yoga');
        $typeULSelect = self::buildULFilter($typeList, RY_SEARCH_PARAM_TYPE, 'Type');

        return $typeULSelect;
    }

    // SEARCH BY MONTH
    public static function buildMonthHTML()
    {
        $monthDurationArray = self::getNextTwelveMonthsArr();
        $month = self::convertArrayToDBOutput($monthDurationArray);
        $monthDurationHTML = self::buildULFilter($month, RY_SEARCH_PARAM_MONTH, 'Mois');

        return $monthDurationHTML;
    }

    private static function getNextTwelveMonthsArr()
    {
        $months = array();
        for ($i = 1; $i <= 12; $i++) {
            $monthValue = date("Y-m", strtotime( date( 'Y-m' )." +$i months"));
            $date = new DateTime($monthValue . '-01');
            $monthName = $date->format('Y F');
            $month = explode(' ', $monthName);
            $monthFR = self::trMonthName($month[1]);
            $months[$monthValue] = $month[0] . ' ' . $monthFR;
        }

        return $months;
    }

    private static function convertArrayToDBOutput($array)
    {
        $dbOutput = [];
        foreach ($array as $key => $value) {
            $single = new \stdClass();
            $single->slug = $key;
            $single->name = $value;
            $dbOutput[] = $single;
        }

        return $dbOutput;
    }

    private static function trMonthName($monthName)
    {
        $array = array(
            'january' => 'Janvier',
            'february' => 'Février',
            'march' => 'Mars',
            'april' => 'Avril',
            'may' => 'Mai',
            'june' => 'Juin',
            'july' => 'Juillet',
            'august' => 'Août',
            'september' => 'Septembre',
            'october' => 'Octobre',
            'november' => 'Novembre',
            'december' => 'Décembre',
        );
        $name = $array[strtolower( $monthName )];

        return $name;
    }

    // MAIN
    private static function buildULFilter($inputList, $inputName, $defaultName)
    {
        $url = RY_SEARCH_ACTION_URL;
        $isFirst = true;
        foreach ($_GET as $name => $value){
            if($name !== $inputName || $inputName === RY_SEARCH_PARAM_MONTH){
                $char = $isFirst ? '?' : '&';
                $url .= $char . $name .'='.$value;
                $isFirst = false;
            }
        }

        $inputChar = $isFirst ? '?' : '&';
        $defaultUrl = $url. $inputChar . $inputName;
        $clearUrl = $defaultUrl . '=all';
        $liListHTML = '';
        if($inputList){
            foreach ($inputList as $singleInput){
                $name = $singleInput->name;
                $slug = $singleInput->slug;

                if(in_array($inputName, array(RY_SEARCH_PARAM_PROF, RY_SEARCH_PARAM_DESTINATION, RY_SEARCH_PARAM_TYPE))){
                    $isSelected = isset($_GET[$inputName]) && $_GET[$inputName] === $slug;
                    $currentUrl = $isSelected ? $clearUrl : $defaultUrl . '=' . $slug;
                } elseif($inputName === RY_SEARCH_PARAM_MONTH) {
                    $month = $slug . '-01';
                    $startDate = date("Y-m-01", strtotime($month));
                    $endDate = date("Y-m-t", strtotime($month));
                    $dateSlug = '&' . RY_SEARCH_PARAM_DATES . '=' . $startDate . ' to ' . $endDate;
                    $isSelected = isset($_GET[$inputName]) && $_GET[$inputName] === $slug;

                    $str = RY_SEARCH_PARAM_DATES . '=';
                    if(strpos($url, '?' . $str) > 0){
                        $urlArray = explode('?', $url);
                        $monthDefaultUrl = $urlArray[0] . '?' . RY_SEARCH_PARAM_MONTH;
                    } elseif (strpos($url, '&' . $str) > 0){
                        $urlArray = explode('&', $url);
                        $monthDefaultUrl = $urlArray[0] . '&' . RY_SEARCH_PARAM_MONTH;
                    } else {
                        $monthDefaultUrl = $defaultUrl;
                    }

                    if(isset($urlArray)){
                        foreach ($urlArray as $index => $urlPart) {
                            $isDateParameter = strpos($urlPart, $str) > 0;
                            if($index > 0 && $isDateParameter){
                                $monthDefaultUrl .= '&' . $urlPart;
                            }
                        }
                    }

                    $currentUrl = $isSelected ? $clearUrl : $monthDefaultUrl . '=' . $slug . $dateSlug;
                } else {
                    $isSelected = isset($_GET[$inputName]) && $_GET[$inputName] === $name;
                    $currentUrl = $isSelected ? $clearUrl : $defaultUrl . '=' . urlencode($name);
                }

                $selectedClassHTML = '';
                $selectedBeforeHTML = '';
                if($isSelected){
                    $destNameSelected = $name;
                    $selectedClassHTML = 'class="ulSelected"';
                    $selectedBeforeHTML = '<span class="spanSelected"><a href="'.$clearUrl.'">x</a></span><div class="clear"></div>';
                }

                $liHTML = '<li '.$selectedClassHTML.'><a class="ry_li_select_value" data-type="select" href="'.$currentUrl.'">' . $name . '</a>'.$selectedBeforeHTML.'</li>';
                $liListHTML .= $liHTML;
            }
        } else {
            $liListHTML .= "<li>Il n'existe pas d'options correspondants à vos critères de recherche</li>";
        }

        $selected = isset($destNameSelected) ? $destNameSelected : $defaultName;
        $ulHTML = '<div class="rysbd_select_input">'.$selected.'</div>';
        $ulHTML .= '<ul class="ry_ul_select hideBox">';
        $ulHTML .= $liListHTML;
        $ulHTML .= '</ul>';

        return $ulHTML;
    }

    public static function getPagination()
    {
        $pagHtml = '';
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, RY_SEARCH_SLUG) !== false) {

            $urlParts = explode('/', $uri);
            foreach ($urlParts as $key => $value){
                if($value === 'page'){
                    $pageKey = $key + 1;
                    $current = $urlParts[$pageKey];
                    break;
                }
            }

            global $wp_query;
            $total   = $wp_query->found_posts;
            $current = isset( $current ) ? $current : 1;
            $base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
            $format  = isset( $format ) ? $format : '';

            $pagHtml = '<nav class="woocommerce-pagination">';
            $pagHtml .= paginate_links( apply_filters( 'woocommerce_pagination_args', array( // WPCS: XSS ok.
                'base'         => $base,
                'format'       => $format,
                'add_args'     => false,
                'current'      => max( 1, $current ),
                'total'        => $total,
                'prev_text'    => '&larr;',
                'next_text'    => '&rarr;',
                'type'         => 'list',
                'end_size'     => 3,
                'mid_size'     => 3,
            ) ) );;
            $pagHtml .= '</nav>';
        }

        return $pagHtml;
    }

    private static function buildSelectFilter($optionArray, $inputName, $inputDisplayName, $selectedValue = null)
    {
        $defaultSelected = is_null($selectedValue) ? 'selected' : '';
        $selectHTML = '<select class="ry_select" name="'.$inputName.'">';
        $selectHTML .= '<option value="" '.$defaultSelected.'>'.$inputDisplayName.':</option>';
        foreach ($optionArray as $value => $name){
            $selected = $value === $selectedValue ? 'selected' : '';
            $optionHTML = '<option value="'.$value.'" '.$selected.'>' . $name . '</option>';
            $selectHTML .= $optionHTML;
        }
        $selectHTML .= '</select>';

        return $selectHTML;
    }

    private static function buildRadio(array $inputArr, $name, $duration = '', $additionalClass = '')
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

}