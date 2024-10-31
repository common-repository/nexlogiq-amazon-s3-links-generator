<?php
/*
Plugin Name: NexLogiQ Amazon S3 Links Generator
Description: Generates temporary link for files inside Amazon S3 buckets
Author: NexLogiQ, Sachit Tandukar
Author URI: https://nexlogiq.com/
Text Domain: nex-aws-link-generate
Version: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require 'vendor/autoload.php';

require 'nex-aws-post.php';
require 'nex-aws-meta-box.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

define( 'NEX_S3', __FILE__ );
define( 'NEX_S3_DIR', __DIR__ );
$options = get_option( 'nex_s3_plugin_options' );

define( 'NEX_S3_AWS_S3_ACCESS_ID', esc_attr( $options['access_key'] ) );
define( 'NEX_S3_AWS_S3_SECRET', esc_attr( $options['secret_key'] ) );

add_action( 'admin_menu', 'nex_s3_aws_add_settings_page' );
/**
 * Adds menu in WordPress sidebar for plugin settings.
 */
function nex_s3_aws_add_settings_page() {
	add_options_page( esc_html__( 'NexLogiQ AWS plugin page', 'nex-aws-link-generate' ), esc_html__( 'NexLogiQ AWS', 'nex-aws-link-generate' ), 'manage_options', 'nex-aws-link', 'nex_s3_aws_render_plugin_settings_page' );
}

/**
 * Form for the plugin settings.
 */
function nex_s3_aws_render_plugin_settings_page() {
	?>
	<h2><?php esc_html_e( 'NexLogiQ Amazon S3 Plugin Settings', 'nex-aws-link-generate' ); ?></h2>
	<form action="options.php" method="post">
		<?php
		settings_fields( 'nex_s3_plugin_options' );
		do_settings_sections( 'nex_s3_aws_plugin' );
		?>
		<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
	</form>
	<?php

}

add_action( 'admin_init', 'nex_s3_aws_register_settings' );
/**
 * Register plugin setting page.
 */
function nex_s3_aws_register_settings() {
	register_setting( 'nex_s3_plugin_options', 'nex_s3_plugin_options' );
	add_settings_section( 'api_settings', esc_html__( 'API Settings', 'nex-aws-link-generate' ), 'nex_s3_plugin_section_text', 'nex_s3_aws_plugin' );

	add_settings_field( 'nex_s3_setting_access_key', esc_html__( 'AWS S3 Access ID', 'nex-aws-link-generate' ), 'nex_s3_plugin_setting_access_key', 'nex_s3_aws_plugin', 'api_settings' );
	add_settings_field( 'nex_s3_setting_secret_key', esc_html__( 'AWS S3 Secret', 'nex-aws-link-generate' ), 'nex_s3_plugin_setting_secret_key', 'nex_s3_aws_plugin', 'api_settings' );

}

/**
 * Adds settings section description.
 */
function nex_s3_plugin_section_text() {
	echo '<p>' . esc_html__( 'Here you can set all the options for using the API', 'nex-aws-link-generate' ) . '</p>';
}

/**
 * Input field for access key.
 */
function nex_s3_plugin_setting_access_key() {
	$options = get_option( 'nex_s3_plugin_options' );
	echo "<input id='nex_s3_setting_access_key' class='regular-text wide' name='nex_s3_plugin_options[access_key]' type='text' value='" . esc_attr( $options['access_key'] ) . "' />";
}

/**
 * Input field for access secret key.
 */
function nex_s3_plugin_setting_secret_key() {
	$options = get_option( 'nex_s3_plugin_options' );
	echo "<input id='nex_s3_setting_secret_key' class='regular-text wide' name='nex_s3_plugin_options[secret_key]' type='text' value='" . esc_attr( $options['secret_key'] ) . "' />";
}

/**
 * Returns access id and secret key.
 *
 * @return array Returns access id and secret key.
 */
function nex_s3_aws_get_static_keys() {
	return array(
		'access_id' => NEX_S3_AWS_S3_ACCESS_ID,
		'secret'    => NEX_S3_AWS_S3_SECRET,
	);
}

/**
 * Create temporary URLs to your protected Amazon S3 files.
 *
 * @param string $access_key Your Amazon S3 access key.
 * @param string $secret_key Your Amazon S3 secret key.
 * @param string $bucket The bucket.
 * @param string $path The target file path.
 * @param int    $expires In minutes.
 * @return string Temporary Amazon S3 URL
 */
function nex_s3_aws_get_temporary_link( $access_key, $secret_key, $bucket, $path, $expires = 5 ) {

	$s3_client = new S3Client(
		array(
			'version'     => 'latest',
			'region'      => 'us-east-1',
			'credentials' => array(
				'key'    => $access_key,
				'secret' => $secret_key,
			),
		)
	);

	$cmd = $s3_client->getCommand(
		'GetObject',
		array(
			'Bucket' => $bucket,
			'Key'    => $path,
		)
	);

	$request = $s3_client->createPresignedRequest( $cmd, '+' . $expires . ' minutes' );

	return (string) $request->getUri();
}

add_action( 'save_post', 'nex_s3_aws_link_generate', 10, 3 );
/**
 * Saves temporarily generated links on the post.
 *
 * @param int    $post_id Post ID.
 * @param object $post Post Object.
 * @param bool   $update Whether this is an existing post being updated or not.
 */
function nex_s3_aws_link_generate( $post_id, $post, $update ) {
	if ( 'nex_aws_link' === $post->post_type && 'publish' === $post->post_status ) {
		$keys      = nex_s3_aws_get_static_keys();
		$bucket    = esc_html( get_post_meta( $post_id, 'aws_options_bucket-name', true ) );
		$path      = esc_html( get_post_meta( $post_id, 'aws_options_file-name', true ) );
		$expires   = esc_html( get_post_meta( $post_id, 'aws_options_expiry-time', true ) );
		$temp_link = nex_s3_aws_get_temporary_link( $keys['access_id'], $keys['secret'], $bucket, $path, $expires );
		update_post_meta( $post_id, 'aws_options_generated-link', $temp_link );
	}
}

/**
 * Settings link of the plugin.
 *
 * @param array $links Settings link of the plugin.
 *
 * @return mixed
 */
function nex_s3_aws_settings_link( $links ) {
	$url           = esc_url( get_admin_url() ) . 'options-general.php?page=nex-aws-link';
	$settings_link = '<a href="' . $url . '">' . esc_html__( 'Settings', 'nex-aws-link-generate' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

add_action( 'after_setup_theme', 'nex_s3_aws_after_setup_theme' );
/**
 * Add settings link in plugin screen page.
 */
function nex_s3_aws_after_setup_theme() {
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'nex_s3_aws_settings_link' );
}

