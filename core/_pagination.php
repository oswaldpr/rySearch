<?php
global $wp_query;

$total   = $wp_query->found_posts;
$current = isset( $current ) ? $current : wc_get_loop_prop( 'current_page' );
$base    = isset( $base ) ? $base : esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
$format  = isset( $format ) ? $format : '';

echo '<nav class="woocommerce-pagination">';
echo paginate_links( apply_filters( 'woocommerce_pagination_args', array( // WPCS: XSS ok.
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
) ) );
echo '</nav>';
