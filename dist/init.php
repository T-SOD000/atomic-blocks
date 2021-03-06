<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package Atomic Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue assets for frontend and backend
 *
 * @since 1.0.0
 *
 * @param WP_Styles $wp_styles Styles.
 */
function atomic_blocks_block_assets( WP_Styles $wp_styles ) {

	// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- Could be true or 'true'.
	$postfix = ( SCRIPT_DEBUG == true ) ? '' : '.min';

	// Load the compiled styles.
	$wp_styles->add(
		'atomic-blocks-style-css',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'blocks.style.build.css' )
	);

	// Register the FontAwesome icon library.
	$wp_styles->add(
		'atomic-blocks-fontawesome',
		plugins_url( 'dist/assets/fontawesome/css/all' . $postfix . '.css', dirname( __FILE__ ) ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'assets/fontawesome/css/all.css' )
	);
}
add_action( 'wp_default_styles', 'atomic_blocks_block_assets' );

/**
 * Conditionally print Font Awesome stylesheet the first time it is needed by a block.
 *
 * @param string $block_content Block content.
 * @return string Block content with Font Awesome stylesheet prepended if needed.
 */
function atomic_blocks_prepend_block_content_with_fontawesome( $block_content ) {
	$handle = 'atomic-blocks-fontawesome';
	if (
		! wp_style_is( $handle, 'done' )
		&&
		// Check if the content includes a class attribute that contains a Font Awesome prefix class names.
		// For a list of the prefixes, see <https://fontawesome.com/how-to-use/on-the-web/referencing-icons/basic-use>.
		preg_match( '/\sclass="[^"]*?(?<="|\s)(fa|fas|far|fal|fad|fab)(?=\s|")[^"]*?"/', $block_content )
	) {
		ob_start();
		wp_styles()->do_items( array( $handle ) );
		$block_content = ob_get_clean() . $block_content;
	}
	return $block_content;
}
add_filter( 'render_block', 'atomic_blocks_prepend_block_content_with_fontawesome' );

/**
 * Enqueue assets for backend editor
 *
 * @since 1.0.0
 */
function atomic_blocks_editor_assets() {

	// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- Could be true or 'true'.
	$postfix = ( SCRIPT_DEBUG == true ) ? '' : '.min';

	// Load the compiled blocks into the editor.
	wp_enqueue_script(
		'atomic-blocks-block-js',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components', 'wp-editor' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'blocks.build.js' ),
		true
	);

	// Load the compiled styles into the editor.
	wp_enqueue_style(
		'atomic-blocks-block-editor-css',
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'blocks.editor.build.css' )
	);

	// Load the FontAwesome icon library.
	wp_enqueue_style( 'atomic-blocks-fontawesome' );

	$user_data = wp_get_current_user();
	unset( $user_data->user_pass, $user_data->user_email );

	// Pass in REST URL.
	wp_localize_script(
		'atomic-blocks-block-js',
		'atomic_globals',
		array(
			'rest_url'      => esc_url( rest_url() ),
			'user_data'     => $user_data,
			'pro_activated' => function_exists( '\AtomicBlocksPro\plugin_loader' ),
			'is_wpe'        => function_exists( 'is_wpe' ),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'atomic_blocks_editor_assets' );


/**
 * Enqueue assets for frontend
 *
 * @since 1.0.0
 */
function atomic_blocks_frontend_assets() {

	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		return;
	}

	// Load the dismissible notice js.
	wp_enqueue_script(
		'atomic-blocks-dismiss-js',
		plugins_url( '/dist/assets/js/dismiss.js', dirname( __FILE__ ) ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . '/assets/js/dismiss.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'atomic_blocks_frontend_assets' );

add_filter( 'block_categories', 'atomic_blocks_add_custom_block_category' );
/**
 * Adds the Atomic Blocks block category.
 *
 * @param array $categories Existing block categories.
 *
 * @return array Updated block categories.
 */
function atomic_blocks_add_custom_block_category( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'atomic-blocks',
				'title' => __( 'Atomic Blocks', 'atomic-blocks' ),
			),
		)
	);
}

/**
 * Enqueue assets for settings page.
 *
 * @since 2.1.0
 */
function atomic_blocks_settings_enqueue() {

	if ( 'atomic-blocks_page_atomic-blocks-plugin-settings' === get_current_screen()->base ) {
		wp_register_style( 'atomic-blocks-getting-started', plugins_url( 'dist/getting-started/getting-started.css', __DIR__ ), false, '1.0.0' );
		wp_enqueue_style( 'atomic-blocks-getting-started' );

	}
}
add_action( 'admin_enqueue_scripts', 'atomic_blocks_settings_enqueue' );
