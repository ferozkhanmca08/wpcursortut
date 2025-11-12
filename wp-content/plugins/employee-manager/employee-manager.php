<?php
/**
 * Plugin Name: Employee Manager
 * Description: Manage employees (id, name, dept, salary) with admin and REST CRUD.
 * Version: 1.0.0
 * Author: Cursor Assistant
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EMPMGR_PLUGIN_VERSION', '1.0.0' );
define( 'EMPMGR_PLUGIN_FILE', __FILE__ );
define( 'EMPMGR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once EMPMGR_PLUGIN_DIR . 'includes/class-employee-manager.php';
require_once EMPMGR_PLUGIN_DIR . 'includes/class-employee-admin.php';
require_once EMPMGR_PLUGIN_DIR . 'includes/class-employee-rest.php';

register_activation_hook( __FILE__, [ 'Employee_Manager', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'Employee_Manager', 'deactivate' ] );

add_action( 'plugins_loaded', function () {
	Employee_Manager::instance();
	Employee_Manager_Admin::instance();
	Employee_Manager_REST::instance();
} );


