<?php
/**
 * Plugin Name: Logged In Users
 * Description: Displays recently logged in users with their IP Addresses and login timestamps
 * Version: 1.0
 * Author: Neha khan
 * Author URI: test.test
 */

if (! defined('ABSPATH')) {
  exit;
}

add_action('wp_login', 'recently_logged_users',10 , 2);

function recently_logged_users($user_login) {
  $ip_address = $_SERVER['REMOTE_ADDR'];
  $login_time = current_time('mysql');

  global $wpdb;
  $table = $wpdb->prefix . 'recent_logins';

  // print_r($ip_address);
  // print_r($login_time);
  // print_r($table);

  // die;
  $wpdb->insert(
    $table,
    array(
      'username' => $user_login,
      'ip' => $ip_address,
      'login_time' => $login_time,
    )
  );
}

register_activation_hook(__FILE__,'recent_logged_user_create_table');

function recent_logged_user_create_table() {
  global $wpdb;
  $table = $wpdb->prefix . 'recent_logins';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table (
      id int(9) NOT NULL AUTO_INCREMENT,
      username varchar(60) NOT NULL,
      ip varchar(100) NOT NULL,
      login_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY (id)
  ) $charset_collate;";
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

add_action('admin_menu', 'rlu_register_admin_menu');

function rlu_register_admin_menu() {
  add_menu_page(
    'Recently Logged In Users',
    'Recently Logged In Users',
    'manage_options',
    'recently-logged-in-users',
    'rlu_display_login_data',
    'dashicons-admin-users',
    6
  );
}

function rlu_display_login_data() {
  if (! current_user_can('manage_options')) {
    return;
  }

  global $wpdb;
  $table = $wpdb->prefix . 'recent_logins';
  $logins = $wpdb->get_results("SELECT * FROM $table ORDER BY login_time DESC");

  echo '<div class="wrap">';
  echo '<h1>Recently Logged In Users</h1>';
  echo '<table class="wp-list-table widefat fixed striped">';
  echo '<thead><tr><th>Username</th><th>IP Address</th><th>Login Time</th></tr></thead>';
  echo '</tbody>';

  foreach ($logins as $login) {
    echo '<tr>';
    echo '<td>' . esc_html($login->username) . '</td>';
    echo '<td>' . esc_html($login->ip) . '</td>';
    echo '<td>' . esc_html($login->login_time) . '</td>';
    echo '</tr>';
  }

  echo '</tbody>';
  echo '</table>';
  echo '</div>';
}

?>