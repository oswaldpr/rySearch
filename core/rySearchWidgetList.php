<?php

namespace rySearch\core;

use WP_Widget;

class widgetRySearchByDate extends WP_Widget {

    function __construct()
    {
        parent::__construct(
            'rySearchByDate', 'RY recherche par texte et date',
            array( 'description' => __( 'Widget de recherche par texte et date'), )
        );
    }

    public function widget( $args, $instance )
    {
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ){
            echo $args['before_title'] . $title . $args['after_title'];
        }

        include WP_PLUGIN_DIR . '/rySearch/core/_searchForm.php';
        echo $args['after_widget'];
    }

    public function form( $instance )
    {
        $title = isset( $instance[ 'title' ] ) ?
            $instance[ 'title' ] : __( 'New title');

        $fieldId = $this->get_field_id( 'title' );
        $fieldName = $this->get_field_name( 'title' );
        $strHTML = '<p>';
        $strHTML .= '<label for="'. $fieldId . '">Title: </label>';
        $strHTML .= '<input type="text" class="widefat"';
        $strHTML .= ' id="' . $fieldId . '"';
        $strHTML .= ' name="' . $fieldName . '"';
        $strHTML .= ' value="'. esc_attr( $title ) . '"';
        $strHTML .= '/>';
        $strHTML .= '</p>';

        echo $strHTML;
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}

class widgetRySearchByDestination extends WP_Widget {

    function __construct()
    {
        parent::__construct(
            'rySearchByDestination', 'RY recherche par destination',
            array( 'description' => __( 'Widget de recherche par destination' ), )
        );
    }

    public function widget( $args, $instance )
    {
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ){
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo rySearchController::buildDestinationHtml();

        echo $args['after_widget'];
    }

    public function form( $instance )
    {
        $title = isset( $instance[ 'title' ] ) ?
            $instance[ 'title' ] : __( 'New title' );

        $fieldId = $this->get_field_id( 'title' );
        $fieldName = $this->get_field_name( 'title' );
        $strHTML = '<p>';
        $strHTML .= '<label for="'. $fieldId . '">Title: </label>';
        $strHTML .= '<input type="text" class="widefat"';
        $strHTML .= ' id="' . $fieldId . '"';
        $strHTML .= ' name="' . $fieldName . '"';
        $strHTML .= ' value="'. esc_attr( $title ) . '"';
        $strHTML .= '/>';
        $strHTML .= '</p>';

        echo $strHTML;
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}

class widgetRySearchByProf extends WP_Widget {

    function __construct()
    {
        parent::__construct(
            'rySearchByProf', 'RY recherche par professeur',
            array( 'description' => __( 'Widget de recherche par professeur' ), )
        );
    }

    public function widget( $args, $instance )
    {
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ){
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo rySearchController::buildActiveProfHtml();

        echo $args['after_widget'];
    }

    public function form( $instance )
    {
        $title = isset( $instance[ 'title' ] ) ?
            $instance[ 'title' ] : __( 'New title' );

        $fieldId = $this->get_field_id( 'title' );
        $fieldName = $this->get_field_name( 'title' );
        $strHTML = '<p>';
        $strHTML .= '<label for="'. $fieldId . '">Title: </label>';
        $strHTML .= '<input type="text" class="widefat"';
        $strHTML .= ' id="' . $fieldId . '"';
        $strHTML .= ' name="' . $fieldName . '"';
        $strHTML .= ' value="'. esc_attr( $title ) . '"';
        $strHTML .= '/>';
        $strHTML .= '</p>';

        echo $strHTML;
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}