<?php
/**
 * Plugin Name: RY widgets de recherche
 * Plugin URI:
 * Description: Recherche de retraites
 * Version: 1.0.0
 * Author: Oswald Prince
 * Author URI:
 * License: GPL2 or later
 * Text Domain: ry-search-text-domain
 * Domain Path: /languages
 */


use rySearch\core\rySearchController;

define('RY_SEARCH_ROOT', dirname(__FILE__));

$pluginExplodedName = explode('/', RY_SEARCH_ROOT);
$pluginName = end($pluginExplodedName);
define('RY_SEARCH_NAME', 'RY Search');
define('RY_SEARCH_SLUG', '/rysearch');
define('RY_SEARCH_DIR', plugins_url() . '/'.$pluginName);
define('RY_SEARCH_ACTION_URL', home_url() . RY_SEARCH_SLUG);
define('RY_SEARCH_DEFAULT_SEARCH', home_url() . '/toutes-les-retraites');
define('RY_SEARCH_PARAM_KEY', 'rys');
define('RY_SEARCH_PARAM_CALENDAR', 'calendrier');
define('RY_SEARCH_PARAM_MONTH', 'mois');
define('RY_SEARCH_PARAM_DESTINATION', 'destination');
define('RY_SEARCH_PARAM_TYPE', 'type');
define('RY_SEARCH_PARAM_PROF', 'professeur');
define('RY_SEARCH_PARAM_MIN_PRICE', 'min_price');
define('RY_SEARCH_PARAM_MAX_PRICE', 'max_price');
define('RY_SEARCH_ABSPATH', '/www/');
//define('RY_SEARCH_TXT_DOMAIN', 'ry-search-text-domain');

class rySearchPlugin
{
    /**
     * plugin constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if (!function_exists("is_plugin_active"))
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $this->_initPlugin();
        add_action( 'widgets_init', 'loadRYSearch' );
    }

    /**
     * @throws Exception
     */
    function _initPlugin()
    {
        spl_autoload_register(function ($class) {
            // Should be ABSPATH  instead of RY_SEARCH_ABSPATH but the server config dont match the folders
            $filename = ABSPATH . 'wp-content/plugins/'. str_replace('\\', DIRECTORY_SEPARATOR , $class) . '.php';
            $widgetFile = ABSPATH . 'wp-content/plugins/rySearch/core/rySearchWidgetList.php';
            if(strpos($filename, 'widgetRySearch') !== false && file_exists($widgetFile)){
                include_once $widgetFile;
            } elseif (file_exists($filename)){
                include_once $filename;
            }
        });

//        /* Load the language file */
//        $file = RY_SEARCH_DIR . '/languages/' . get_locale() . '.mo';
//        if (file_exists($file)) {
//            load_textdomain(RY_SEARCH_TXT_DOMAIN, $file);
//        }
    }
}

add_action('plugins_loaded', 'createRYSearchPlugin');
add_action('template_redirect', 'rySearchRedirect', 1);

function createRYSearchPlugin() {
    new rySearchPlugin();
}

function loadRYSearch() {
    register_widget('rySearch\core\widgetRySearchByDate');
    register_widget('rySearch\core\widgetRySearchByDestination');
    register_widget('rySearch\core\widgetRySearchByProf');
    wc_register_widgets();
}

function rySearchRedirect()
{
    global $wp_query;

    $uri = $_SERVER['REQUEST_URI'];

    if (strpos($uri, RY_SEARCH_SLUG) !== false) {

        $uriContainsAllParameters = true;
        $refererParameters = rySearchController::getRefererParameters();
        foreach ($refererParameters as $key => $value) {
            if($value && strpos($uri, $key) === false){
                $uriContainsAllParameters = false;
                break;
            }
        }

        if(!$uriContainsAllParameters){
            $redirectUrl = rySearchController::redirectUrl($refererParameters);
            header('Location:' . $redirectUrl);
            die;
        }

        $wp_query = rySearchController::getWPQuery();
        $wp_query->is_404 = false;
        $wp_query->is_category = false;
        $wp_query->is_search = false;
        unset($wp_query->query['s']);
        unset($wp_query->query_vars['s']);

        $wc = WooCommerce::instance();
        wc()->query->product_query($wp_query);

        session_start();
        $_SESSION['wp_query'] = $wp_query;

        wc_get_template_part('archive', 'product');

        unset($_SESSION['wp_query']);

        die;
    }
}

add_action( 'wp_enqueue_scripts', 'loadRYSBDLibrariesStylesheets' );
function loadRYSBDLibrariesStylesheets() {
    // enqueue the CSS
    wp_enqueue_style( "jquery-ui_css", 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',false, null);
    wp_enqueue_style( "fontAwesome_css", 'https://use.fontawesome.com/releases/v5.7.0/css/all.css',false, null);
    wp_enqueue_style( "jsdelivr_daterangepicker_css", 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',false, null);
    wp_enqueue_style( "bootstrap_css", 'https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css',false, null);
    wp_enqueue_style( "bootstrap_datepicker_css", 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/css/datepicker.css',false, null);
    wp_enqueue_style( "rysearch_css", RY_SEARCH_DIR . '/assets/style.css',false, null);
}

add_action( 'wp_enqueue_scripts', 'loadRYSBDLibrariesScripts' );
function loadRYSBDLibrariesScripts() {
    //enqueue js libraries
    if (!wp_script_is( 'jquery', 'enqueued' )) {
        wp_enqueue_script( 'jquery', "https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js" , array(), true, true );
    }

    //wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array(), null, true);
    //wp_enqueue_script('jsdelivr_jquery', 'https://cdn.jsdelivr.net/jquery/latest/jquery.min.js', array(), null, true);
    wp_enqueue_script('jsdelivr_moment', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array(), null, true);
    wp_enqueue_script('jsdelivr_daterangepicker', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array(), null, true);
    wp_enqueue_script('bootstrap_js', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js', array(), null, true);
    wp_enqueue_script('bootstrap_datepicker_js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.3.0/js/bootstrap-datepicker.js', array(), null, true);

    wp_enqueue_script('rysearch_js',RY_SEARCH_DIR . '/assets/general.js', array('jquery'), null, true );
}

