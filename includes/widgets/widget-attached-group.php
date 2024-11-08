<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * A widget to display BuddyForms Attached Group
 *
 * @package BuddyPress Custom Group Types
 * @since 0.1-beta
 */

class BuddyForms_APWG_Taxonomy_Term_Post_Widget extends WP_Widget
{
    /**
     * Initialize the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function __construct() {
        $widget_ops = array(
            'classname'   => 'buddyforms_apwg_taxonomy_term_post',
            'description' => __( 'BuddyForms APWG Term Post', 'buddyforms' )
        );

        parent::__construct( false, __( 'BuddyForms APWG Term Post', 'buddyforms' ), $widget_ops );
    }

    /**
     * Display the widget
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function widget( $args, $instance ) {
        global $post, $buddyforms;

        extract( $args );

        if ( !bp_is_group() )
            return;

        $groups_post_id = groups_get_groupmeta( bp_get_group_id(), 'group_post_id' );

        if(empty($groups_post_id))
            return;

        $form_slug      = get_post_meta( $groups_post_id, '_bf_form_slug', true );

        if(empty($form_slug))
            return;

        if( ! isset( $buddyforms[$form_slug]['form_fields'] )){
            return;
        }

        foreach($buddyforms[$form_slug]['form_fields'] as $key => $form_field){
            if($form_field['type'] == 'apwg_taxonomy'){
                $apwg_taxonomys[$form_field['slug']] = $buddyforms[$form_field['apwg_taxonomy']]['post_type'];
            }
        }

        if(!is_array($apwg_taxonomys))
            return;

        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';

        $show_title = ! empty( $instance['show_title'] ) ? esc_attr( $instance['show_title'] ) : '';
        $show_avatar = ! empty( $instance['show_avatar'] ) ? esc_attr( $instance['show_avatar'] ) : '';
        $show_excerpt = ! empty( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';

        if( ! empty( $title ) )
            echo $before_title . $title . $after_title;

        foreach($apwg_taxonomys as $field_slug => $post_type){
            $term = wp_get_post_terms($groups_post_id, 'bf_apwg_' . $field_slug, array("fields" => "all"));

            if(is_wp_error($term))
                return;

            if ( isset($term[0]->slug)) {

                $args=array(
                    'name' => $term[0]->slug,
                    'post_type' => $post_type,
                    'post_status' => 'publish',
                    'posts_per_page' => 1
                );

                $get_the_post_thumbnail_attr = array(
                    'class' => "avatar",
                );

                $app_posts = get_posts($args);

                $tmp = '';
                if( $app_posts ) {

                    $tmp .= '<div id="item-list" class="widget">';
                    $tmp .= '<ul>';

                    foreach ( $app_posts as $post ) :

                        if ( $groups_post_id != $post->ID ) {

                            setup_postdata( $post );

                            $tmp .= '<a href="'.get_permalink().'" title="'.the_title_attribute(array('echo'=> 0)).'">';
                            $tmp .= '<li>';

                            if( ! empty( $show_title ) ){
                                $tmp .= '<h3 class="post_title">'  . get_the_title()   . '</h3>';
                            }

                            if( ! empty( $show_avatar ) ){
                                $tmp .= '<p>' . get_the_post_thumbnail($post->ID , 'post-thumbnails' , $get_the_post_thumbnail_attr) . '</p>';
                            }

                            if( ! empty( $show_excerpt ) ){
                                $tmp .= '<p class="post_excerpt">' . get_the_excerpt() . '</p>';
                            }

                            $tmp .= '</li>';
                            $tmp .= '</a>';
                            $tmp .= '<div class="clear"></div>';
                        }

                    endforeach;

                    $tmp .= '</ul>';
                    $tmp .= '</div>';

                    echo $tmp;

                    // Reset $tmp and the Query
                    $tmp = '';
                    wp_reset_query();
                }
            }
        }


    }

    /**
     * Update any widget options
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function update( $new_instance, $old_instance ) {
        $instance          = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['show_title'] = strip_tags( $new_instance['show_title'] );
        $instance['show_avatar'] = strip_tags( $new_instance['show_avatar'] );
        $instance['show_excerpt'] = strip_tags( $new_instance['show_excerpt'] );

        return $instance;
    }

    /**
     * Show the widget options form
     *
     * @package BuddyPress Custom Group Types
     * @since 0.1-beta
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';

        $show_title = ! empty( $instance['show_title'] ) ? esc_attr( $instance['show_title'] ) : '';
        $show_avatar = ! empty( $instance['show_avatar'] ) ? esc_attr( $instance['show_avatar'] ) : '';
        $show_excerpt = ! empty( $instance['show_excerpt'] ) ? esc_attr( $instance['show_excerpt'] ) : '';
        ?>
        <div>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>">
                    <p><?php _e( 'Display the Post used to create the Taxonomy Term.', 'buddyforms' ) ?></p>
                    <br>
                    <?php _e( 'Taxonomy Term Post Title:', 'buddyforms' ) ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title ?>" />


                </label>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show the title', 'buddyforms' ) ?></label>
                <input <?php checked( $show_title, 'show_title') ?> class="widefat" id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" type="checkbox" value="show_title" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'show_avatar' ); ?>"><?php _e( 'Show the avatar', 'buddyforms' ) ?></label>
                <input <?php checked( $show_avatar, 'show_avatar') ?> class="widefat" id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" type="checkbox" value="show_avatar" />
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>"><?php _e( 'Show the excerpt', 'buddyforms' ) ?></label>
                <input <?php checked( $show_excerpt, 'show_excerpt') ?> class="widefat" id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" type="checkbox" value="show_excerpt" />
            </p>
        </div>
    <?php
    }
}
