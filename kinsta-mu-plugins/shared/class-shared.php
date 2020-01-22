<?php
/**
 * Shared classes
 *
 * @package KinstaMUPlugins
 * @subpackage Shared
 * @since 1.0.0
 */

namespace Kinsta;

if ( ! defined( 'ABSPATH' ) ) { // If this file is called directly.
	die( 'No script kiddies please!' );
}

/**
 * Shared class
 *
 * Common functionalities required in the 'mu-plugins'.
 *
 * @since 1.0.0
 */
class Shared {
	/**
	 * Plugin constructor
	 * Sets the hooks required for the plugin's functionality
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_head', array( $this, 'init_tooltipster' ) );
		add_action( 'admin_body_class', array( $this, 'body_classes' ) );

		if ( KINSTAMU_WHITELABEL === false ) {
			add_filter( 'admin_footer_text', [ $this, 'modify_admin_footer_text' ], 99 );
		}
	}

	/**
	 * Whitlable all the Kinsta branded pages and options in the backend
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param  string $classes CSS class names to add in the <body>.
	 * @return string
	 **/
	public function body_classes( $classes ) {
		if ( defined( 'KINSTAMU_WHITELABEL' ) && KINSTAMU_WHITELABEL === true ) {
			$classes .= ' kinstamu-whitelabel';
		}
		return $classes;
	}

	/**
	 * Set the name and value of the option into array before saving it.
	 *
	 * @param array  $array The list of options.
	 * @param string $name  Option name.
	 * @param mixed  $value Option value.
	 * @return void
	 */
	private function set_options_array_value( array $array, $name, $value ) {
		if ( substr( $name, -1 ) !== ']' ) {
			$array[ $name ] = $value;
		} else {
			$name = str_replace( '][', '|', $name );
			$name = substr( str_replace( '[', '|', $name ), 0, -1 );
			$name = explode( '|', $name );
		}

		$count = count( $name );

		if ( 2 === $count ) {
			$array[ $name[0] ][ $name[1] ] = $value;
		}

		if ( 3 === $count ) {
			$array[ $name[0] ][ $name[1] ][ $name[2] ] = $value;
		}

		if ( 4 === $count ) {
			$array[ $name[0] ][ $name[1] ][ $name[2] ][ $name[3] ] = $value;
		}
	}

	/**
	 * Load assets in the Kinsta plugin setting page.
	 *
	 * @param  string $page The page slug.
	 * @return void
	 */
	public function assets( $page ) {
		if ( substr_count( $page, 'kinsta' ) === 0 ) {
			return;
		}

		wp_enqueue_style( 'kinsta-shared', $this->shared_resource_url( 'shared/styles/common.css' ), array(), KINSTAMU_VERSION );
		wp_enqueue_script( 'kinsta-loader', $this->shared_resource_url( 'shared/scripts/kinsta-loader.js' ), array( 'jquery', 'jquery-effects-core' ), KINSTAMU_VERSION, true );

		wp_enqueue_script( 'tooltipster', $this->shared_resource_url( 'shared/scripts/tooltipster.bundle.min.js' ), array( 'jquery' ), KINSTAMU_VERSION, true );
		wp_enqueue_style( 'tooltipster', $this->shared_resource_url( 'shared/styles/tooltipster.bundle.css' ), array(), KINSTAMU_VERSION );
	}

	/**
	 * Init tooltip
	 *
	 * @return void
	 */
	public function init_tooltipster() {
		$screen = get_current_screen();
		if ( 0 === substr_count( $screen->id, 'kinsta' ) ) {
			return;
		} ?>
		<script>
			jQuery(document).ready(function() {
				jQuery('.kinsta-tooltip').tooltipster({
					theme: 'tooltipster-borderless',
					interactive: true,
					maxWidth: 360
				});
			});
		</script>
		<?php
	}

	/**
	 * Load translations
	 *
	 * @since 1.0.0
	 * @author Daniel Pataki
	 */
	public function load_textdomain() {
		load_muplugin_textdomain( 'kinsta-mu-plugins', dirname( plugin_basename( __FILE__ ) ) . '/translations' );
	}

	/**
	 * Print the "select" field
	 *
	 * @param  string  $option_name The table name to store the option.
	 * @param  string  $name        The option name (key).
	 * @param  string  $value       The $name value.
	 * @param  string  $label       The control lable.
	 * @param  boolean $info        Whether to show the tooltip.
	 * @param  boolean $options     Options to show in the select field.
	 * @return void
	 */
	public static function kinsta_select_field( $option_name, $name, $value, $label, $info = false, $options ) {
		?>
		<div class='kinsta-select-field kinsta-control-container' data-option-name="<?php echo esc_attr( $option_name ); ?>">
			<label>
				<select name='<?php echo esc_attr( $name ); ?>' class='kinsta-control'>
				<?php foreach ( $options as $option_value => $option_name ) : ?>
				<option <?php echo selected( $value, $option_value ); ?> value="<?php echo esc_attr( $option_value ); ?>"><?php echo esc_attr( $option_name ); ?></option>
				<?php endforeach ?>
				</select>
				<span class='kinsta-label'><?php echo esc_attr( $label ); ?></span>
			</label>
			<input type='hidden' name='kinsta-nonce' value='<?php echo esc_attr( 'kinsta_select_field_' . wp_create_nonce( $name ) ); ?>'>
			<?php self::kinsta_tooltip( $info, $name ); ?>
		</div>
		<?php
	}

	/**
	 * Print tooltip
	 *
	 * @param  string $content The tooltip content.
	 * @param  string $name    The name of field where the tooltip is associated to.
	 * @return void
	 */
	public static function kinsta_tooltip( $content, $name ) {
		if ( ! empty( $content ) ) :
			$name = str_replace( array( '[', ']' ), '_', $name );
			?>
			<span class="kinsta-tooltip" data-tooltip-content="#kinsta-tooltip-<?php echo esc_attr( $name ); ?>"><img src='<?php echo esc_attr( self::shared_resource_url( 'shared' ) ); ?>/images/info.svg'></span>
			<div class="kinsta-tooltip-content"><span id="kinsta-tooltip-<?php echo esc_attr( $name ); ?>"><?php echo esc_attr( $content ); ?></span></div>
			<?php
		endif;
	}

	/**
	 * Fix missing recource issues with mu plugin's static files
	 *
	 * It was handled by " plugin_dir_url( __FILE__ )" before we switched to this
	 *
	 * @author Laci <laszlo@kinsta.com>
	 * @version 1.2
	 * @since 2.0.1 resource issue fixing
	 *
	 * @param string $path optional param which is added to the end of the returned string.
	 * @return string URL path of the kinsta-mu-plugins.
	 */
	public static function shared_resource_url( $path = '' ) {
		$mu_url = ( is_ssl() ) ? str_replace( 'http://', 'https://', WPMU_PLUGIN_URL ) : WPMU_PLUGIN_URL;
		$full_path = $mu_url . '/kinsta-mu-plugins/' . $path;

		if ( defined( 'KINSTAMU_CUSTOM_MUPLUGIN_URL' ) && KINSTAMU_CUSTOM_MUPLUGIN_URL !== '' ) {
			$full_path = KINSTAMU_CUSTOM_MUPLUGIN_URL . '/kinsta-mu-plugins/' . $path;
		}

		return $full_path;
	}

	/**
	 * Modify Footer Text
	 * Modifies the thank you text in the bottom of the admin
	 *
	 * @since 1.0.0
	 * @author Daniel Pataki
	 */
	public function modify_admin_footer_text() {
		// Translators: %1$s WordPress, %2$s Kinsta URL.
		return '<span id="footer-thankyou">' . sprintf( __( 'Thanks for creating with %1$s and hosting with %2$s', 'kinsta-mu-plugins' ), '<a href="https://wordpress.org/">WordPress</a>', '<a href="https://kinsta.com/?utm_source=client-wp-admin&utm_medium=bottom-cta" target="_blank">Kinsta</a>' ) . '</span>';
	}
}

new Shared();
