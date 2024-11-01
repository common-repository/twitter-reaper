<?php
/*
Plugin Name: Twitter Reaper
Plugin URI: 
Description: An Twitter plugin for Wordpress Developers.  Set wp_chron jobs, get tweets by hashtag or username
Version: 0
Author: Kyle Shike
Author URI:
License: GPL
Copyright: Kyle Shike
*/

include('lib/twitter_reaper_core_functions.php');

  function twitter_reaper_database_tables_setup() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'reaper_tweets';
    $charset_collate = '';

    if ( ! empty( $wpdb->charset ) ) {
      $charset_collate = "DEFAULT CHARSET={$wpdb->charset}";
    }

    if ( ! empty( $wpdb->collate ) ) {
      $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    $sql = "CREATE TABLE $table_name (
      id int(11) NOT NULL primary key AUTO_INCREMENT,
      tweet text NOT NULL,
      tweet_id varchar(255) NOT NULL,
      date_created varchar(255) NOT NULL
    ) $charset_collate;";

    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql);
  }

  function twitter_reaper_activate() {
    twitter_reaper_database_tables_setup();
    $twitter_reaper_options = array(
      'chron' => false,
      'query' => 'hashtag',
      'username' => '',
      'hashtag' => '',
      'count' => '',
      'recurrence' => ''
    );
    if (!get_option('twitter_reaper_options')) {
      update_option('twitter_reaper_options', $twitter_reaper_options);
    }
  }

  function twitter_reaper_cron_add_schedules( $schedules ) {
   // Adds once weekly to the existing schedules.
    $schedules['minutely'] = array(
      'interval' => 60,
      'display' => __( 'Once a Minute' )
    );
    $schedules['weekly'] = array(
      'interval' => 604800,
      'display' => __( 'Weekly' )
    );
    $schedules['half_hour'] = array(
      'interval' => 1800,
      'display' => __( 'Every Half Hour')
    );
    return $schedules;
  }

  add_filter( 'cron_schedules', 'twitter_reaper_cron_add_schedules' );
  add_action('twitter_reaper_event', 'twitter_reaper_save_tweets');
  register_activation_hook(__FILE__, 'twitter_reaper_activate');
  add_action( 'admin_menu', 'twitter_reaper_menu_pages_init' );

  function twitter_reaper_menu_pages_init(){
    add_menu_page(
      __('Twitter Reaper'),
      __('Twitter Reaper'), 
      'manage_options',
      'twitter_reaper/lib/twitter_reaper_results.php',
      '',
      plugins_url('twitter_reaper/assets/sickle.png')
    ); 
    add_submenu_page( 'twitter_reaper/lib/twitter_reaper_results.php', 'Reaper Options', 'Reaper Options', 'manage_options', 'twitter_reaper/lib/twitter_reaper_options.php', '');
  }

  register_deactivation_hook( __FILE__, 'twitter_reaper_prefix_deactivation' );

  function twitter_reaper_prefix_deactivation() {
    wp_clear_scheduled_hook( 'twitter_reaper_event' );
  }

  function twitter_reaper_styles() {
    wp_enqueue_style( 'twitter_reaper_styles', plugins_url('/assets/twitter_reaper_styles.css', __FILE__));
    wp_enqueue_style( 'emojis', plugins_url('/assets/emoji.css', __FILE__));
  }

  // function emoji_styles() {
  //   wp_enqueue_style( 'emojis', plugins_url('/assets/emoji.css', __FILE__));
  // }


  // add_action( 'wp_enqueue_scripts', 'emoji_styles' );
  add_action('admin_print_styles', 'twitter_reaper_styles');
