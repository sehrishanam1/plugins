<?php
/**
 * Custom Post Type: Team Member
 *
 * @package TeamMembers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TM_CPT {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'register_order_taxonomy' ) );
	}

	/**
	 * Register the "team_member" CPT.
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'Team Members', 'Post type general name', 'team-members' ),
			'singular_name'         => _x( 'Team Member', 'Post type singular name', 'team-members' ),
			'menu_name'             => _x( 'Team Members', 'Admin Menu text', 'team-members' ),
			'name_admin_bar'        => _x( 'Team Member', 'Add New on Toolbar', 'team-members' ),
			'add_new'               => __( 'Add New', 'team-members' ),
			'add_new_item'          => __( 'Add New Team Member', 'team-members' ),
			'new_item'              => __( 'New Team Member', 'team-members' ),
			'edit_item'             => __( 'Edit Team Member', 'team-members' ),
			'view_item'             => __( 'View Team Member', 'team-members' ),
			'all_items'             => __( 'All Team Members', 'team-members' ),
			'search_items'          => __( 'Search Team Members', 'team-members' ),
			'not_found'             => __( 'No team members found.', 'team-members' ),
			'not_found_in_trash'    => __( 'No team members found in Trash.', 'team-members' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'team-member' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-groups',
			'supports'           => array( 'title', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'       => true, // Gutenberg / REST API support
		);

		register_post_type( 'team_member', $args );
	}

	/**
	 * Register a simple "Department" taxonomy (optional grouping / sorting).
	 */
	public static function register_order_taxonomy() {
		$labels = array(
			'name'          => _x( 'Departments', 'taxonomy general name', 'team-members' ),
			'singular_name' => _x( 'Department', 'taxonomy singular name', 'team-members' ),
			'search_items'  => __( 'Search Departments', 'team-members' ),
			'all_items'     => __( 'All Departments', 'team-members' ),
			'edit_item'     => __( 'Edit Department', 'team-members' ),
			'add_new_item'  => __( 'Add New Department', 'team-members' ),
			'new_item_name' => __( 'New Department Name', 'team-members' ),
			'menu_name'     => __( 'Departments', 'team-members' ),
		);

		register_taxonomy( 'tm_department', array( 'team_member' ), array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'department' ),
			'show_in_rest'      => true,
		) );
	}
}

TM_CPT::init();
