<?php

/**
 * Class Nex_AWS_Meta_Box
 */
class Nex_AWS_Meta_Box {
	/**
	 * Page where meta field needs to be place.
	 *
	 * @var string[] page where meta field needs to be place.
	 */
	private $screens = array(
		'nex_aws_link',
	);

	/**
	 * List of fields required for AWS links post type.
	 *
	 * @var array[] List of fields.
	 */
	private $fields;

	/**
	 * Class construct method. Adds actions to their respective WordPress hooks.
	 */
	public function __construct() {
		$this->fields = array(
			array(
				'id'    => 'bucket-name',
				'label' => esc_html__( 'Bucket Name', 'nex-aws-link-generate' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'file-name',
				'label' => esc_html__( 'File Name', 'nex-aws-link-generate' ),
				'type'  => 'text',
			),
			array(
				'id'    => 'expiry-time',
				'label' => esc_html__( 'Expiry Time (in minutes)', 'nex-aws-link-generate' ),
				'type'  => 'number',
			),
			array(
				'id'    => 'generated-link',
				'label' => esc_html__( 'Generated Link', 'nex-aws-link-generate' ),
				'type'  => 'text',
				'extra' => 'readonly',
			),
		);
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}

	/**
	 * Hooks into WordPress' add_meta_boxes function.
	 * Goes through screens (post types) and adds the meta box.
	 */
	public function add_meta_boxes(): void {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'aws-options',
				esc_html__( 'NexLogiQ AWS Options', 'nex-aws-link-generate' ),
				array( $this, 'add_meta_box_callback' ),
				$screen,
				'advanced',
				'default'
			);
		}
	}

	/**
	 * Generates the HTML for the meta box
	 *
	 * @param object $post WordPress post object.
	 */
	public function add_meta_box_callback( $post ): void {
		wp_nonce_field( 'aws_options_data', 'aws_options_nonce' );
		$this->generate_fields( $post );
	}

	/**
	 * Generates the field's HTML for the meta box.
	 *
	 * @param object $post WordPress post object.
	 */
	public function generate_fields( $post ): void {
		$output = '';
		foreach ( $this->fields as $field ) {
			$label    = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
			$db_value = esc_html( get_post_meta( $post->ID, 'aws_options_' . $field['id'], true ) );
			$input    = sprintf(
				'<input %s id="%s" name="%s" type="%s" value="%s" %s>',
				'color' !== $field['type'] ? 'class="regular-text"' : '',
				$field['id'],
				$field['id'],
				$field['type'],
				$db_value,
				array_key_exists( 'extra', $field ) ? $field['extra'] : ''
			);
			$output  .= $this->row_format( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}

	/**
	 * Generates the HTML for table rows.
	 *
	 * @param string $label label for the post meta field.
	 * @param string $input input for the post meta field.
	 *
	 * @return string
	 */
	public function row_format( $label, $input ): string {
		return sprintf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			$label,
			$input
		);
	}

	/**
	 * Hooks into WordPress' save_post function
	 *
	 * @param int $post_id post id.
	 *
	 * @return mixed
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['aws_options_nonce'] ) ) {
			return $post_id;
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['aws_options_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, 'aws_options_data' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		foreach ( $this->fields as $field ) {
			if ( isset( $_POST[ $field['id'] ] ) ) {
				switch ( $field['type'] ) {
					case 'email':
						update_post_meta( $post_id, sprintf( 'aws_options_%s', sanitize_text_field( $field['id'] ) ), sanitize_email( wp_unslash( $_POST[ $field['id'] ] ) ) );
						break;
					case 'text':
						update_post_meta( $post_id, sprintf( 'aws_options_%s', sanitize_text_field( $field['id'] ) ), sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) ) );
						break;
				}
			} elseif ( 'checkbox' === $field['type'] ) {
				update_post_meta( $post_id, 'aws_options_' . sanitize_text_field( $field['id'] ), '0' );
			}
		}
	}
}
new Nex_AWS_Meta_Box();
