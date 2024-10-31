<?php

/**
 * Register post type for saving AWS links.
 */
function nex_s3_amazon_save() {

	$labels = array(
		'name'                  => _x( 'AWS Links', 'Post Type General Name', 'nex-aws-link-generate' ),
		'singular_name'         => _x( 'AWS Link', 'Post Type Singular Name', 'nex-aws-link-generate' ),
		'menu_name'             => __( 'AWS Links', 'nex-aws-link-generate' ),
		'name_admin_bar'        => __( 'AWS Link', 'nex-aws-link-generate' ),
		'archives'              => __( 'AWS Link Archives', 'nex-aws-link-generate' ),
		'attributes'            => __( 'AWS Link Attributes', 'nex-aws-link-generate' ),
		'parent_item_colon'     => __( 'Parent AWS Link:', 'nex-aws-link-generate' ),
		'all_items'             => __( 'All AWS Links', 'nex-aws-link-generate' ),
		'add_new_item'          => __( 'Add New AWS Link', 'nex-aws-link-generate' ),
		'add_new'               => __( 'Add New', 'nex-aws-link-generate' ),
		'new_item'              => __( 'New AWS Link', 'nex-aws-link-generate' ),
		'edit_item'             => __( 'Edit AWS Link', 'nex-aws-link-generate' ),
		'update_item'           => __( 'Update AWS Link', 'nex-aws-link-generate' ),
		'view_item'             => __( 'View AWS Link', 'nex-aws-link-generate' ),
		'view_items'            => __( 'View AWS Links', 'nex-aws-link-generate' ),
		'search_items'          => __( 'Search AWS Link', 'nex-aws-link-generate' ),
		'not_found'             => __( 'Not found', 'nex-aws-link-generate' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'nex-aws-link-generate' ),
		'featured_image'        => __( 'Featured Image', 'nex-aws-link-generate' ),
		'set_featured_image'    => __( 'Set featured image', 'nex-aws-link-generate' ),
		'remove_featured_image' => __( 'Remove featured image', 'nex-aws-link-generate' ),
		'use_featured_image'    => __( 'Use as featured image', 'nex-aws-link-generate' ),
		'insert_into_item'      => __( 'Insert into item', 'nex-aws-link-generate' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'nex-aws-link-generate' ),
		'items_list'            => __( 'AWS Links list', 'nex-aws-link-generate' ),
		'items_list_navigation' => __( 'AWS Links list navigation', 'nex-aws-link-generate' ),
		'filter_items_list'     => __( 'Filter AWS Links list', 'nex-aws-link-generate' ),
	);
	$args   = array(
		'label'               => __( 'AWS Link', 'nex-aws-link-generate' ),
		'description'         => __( 'Post Type Description', 'nex-aws-link-generate' ),
		'labels'              => $labels,
		'supports'            => array( 'title' ),
		'taxonomies'          => array(),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-admin-links',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => false,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
		'show_in_rest'        => false,
	);
	register_post_type( 'nex_aws_link', $args );

}
add_action( 'init', 'nex_s3_amazon_save', 0 );
