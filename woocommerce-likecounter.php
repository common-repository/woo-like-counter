<?php
   /*
   Plugin Name: WooCommerce Like Counter
   Plugin URI: http://www.glorywebs.com
   Description: A plugin to add like and dislike button in woocommerce product.
   Version: 1.0
   Author: Glorywebs
   Author URI: http://www.glorywebs.com
   License: GPL2
   */

global $wl_db_version;
$wl_db_version = '1.0';

function wcld_install() {
	global $wpdb;
	global $wcld_db_version;

	$table_name = $wpdb->prefix . 'woocommerce_like_dislike';
	
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
                product_id mediumint(9) DEFAULT '0' NOT NULL,
		ip varchar(24) DEFAULT '' NOT NULL,
		like_type int(1) DEFAULT '0' NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'wcld_db_version', $wcld_db_version );
}

register_activation_hook( __FILE__, 'wcld_install' );

add_action ( "woocommerce_before_shop_loop_item", "woocommerce_template_loop_product_wcld_counter", 9 );
add_action ( "woocommerce_single_product_summary", "woocommerce_template_loop_product_wcld_counter" );
if ( ! function_exists( 'woocommerce_template_loop_product_wcld_counter' ) ) {
	function woocommerce_template_loop_product_wcld_counter() {
            global $post,$wpdb;
            $table_name = $wpdb->prefix . 'woocommerce_like_dislike';
            $likes = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE like_type=1 AND product_id=".$post->ID, OBJECT );
            $dislikes = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE like_type=2 AND product_id=".$post->ID, OBJECT );
            
            echo '<div class="like-counter">'
                . '<div class="wl-counter">'
                    . '<div data="'.$post->ID.'" class="like_counter"><div class="cnt-block">Like (<span class="count">'.count($likes).'</span>)</div><div class="res-message"></div></div>'
                    . '<div data="'.$post->ID.'" class="unlike_counter"><div class="cnt-block">Dislike (<span class="count">'.count($dislikes).'</span>)</div><div class="res-message"></div></div>'
                . '</div>'
                . '</div>';
	} 
 }
 
function wcld_enqueue_script(){   
    wp_enqueue_script( 'wcld_script', plugin_dir_url( __FILE__ ) . 'js/custom.js', array('jquery'), '1.0.0', false );
    wp_localize_script( 'wcld_script', 'ajax_object',array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    
    wp_enqueue_style( 'wcld_style', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '1.0.0', false );
}
add_action('wp_enqueue_scripts', 'wcld_enqueue_script');



function wcld_action() {
	global $wpdb;
        $table_name = $wpdb->prefix . 'woocommerce_like_dislike';
        $product_id = intval( $_POST['product_id'] );
        $type = $_POST['type'];
        if($type=='like'){
            $results = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE ip = '".$_SERVER['REMOTE_ADDR']."' AND like_type=1 AND product_id=".$product_id, OBJECT );
            if(count($results)>0){
                $wpdb->delete( $table_name, array( 'ip' => $_SERVER['REMOTE_ADDR'], 'product_id' => $product_id, 'like_type' => 1 ), array( '%s', '%d', '%d' ) );
                $likes = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE like_type=1 AND product_id=".$product_id, OBJECT );
                echo json_encode(array('count'=>count($likes),'type'=>false));
            }
            else{
                $wpdb->insert( 
                    $table_name, 
                    array( 
                        'product_id' => $product_id, 
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'like_type' => 1,
                        'time' => date('Y-m-d H:i:s'),
                    ), 
                    array( 
                        '%d', 
                        '%s',
                        '%d',
                        '%s',
                    ) 
                );
                $likes = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE like_type=1 AND product_id=".$product_id, OBJECT );
                echo json_encode(array('count'=>count($likes),'type'=>true));
            }
        }
        else if($type=='dislike'){
            $results = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE ip = '".$_SERVER['REMOTE_ADDR']."' AND like_type=2 AND product_id=".$product_id, OBJECT );
            if(count($results)>0){
                 $wpdb->delete( $table_name, array( 'ip' => $_SERVER['REMOTE_ADDR'], 'product_id' => $product_id, 'like_type' => 2 ), array( '%s', '%d', '%d' ) );
                $dislikes = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE like_type=2 AND product_id=".$product_id, OBJECT );
                echo json_encode(array('count'=>count($dislikes),'type'=>false));
            }
            else{
                $wpdb->insert( 
                    $table_name, 
                    array( 
                        'product_id' => $product_id, 
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'like_type' => 2,
                        'time' => date('Y-m-d H:i:s'),
                    ), 
                    array( 
                        '%d', 
                        '%s',
                        '%d',
                        '%s',
                    ) 
                );
                $dislikes = $wpdb->get_results( "SELECT * FROM ".$table_name." WHERE like_type=2 AND product_id=".$product_id, OBJECT );
                echo json_encode(array('count'=>count($dislikes),'type'=>true));
            }
        }
	wp_die();
}

add_action( 'wp_ajax_wcld_action', 'wcld_action' );
add_action( 'wp_ajax_nopriv_wcld_action', 'wcld_action' );