<?php
/*
Plugin Name: Advanced Custom Fields: Auto Blocks
Plugin URI: https://github.com/benplum/ACF-Auto-Blocks
Description: Auto-register ACF field groups as blocks in the new editor (Gutenberg).
Version: 1.0.0
Author: Ben Plum
Author URI: https://benplum.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACF_Auto_Blocks {

  protected static $instance;

	public $version = '1.0.0';
	public $name = 'ACF Auto Blocks';
	public $slug = 'acf-auto-blocks';
  public $key = 'acfab';
	public $file = __FILE__;
  public $settings;
  public $regions = array();
  public $layouts = array();
  public $directory = '';

  public static function get_instance() {
		if ( empty( self::$instance ) && ! ( self::$instance instanceof ACF_Auto_Blocks ) ) {
			self::$instance = new ACF_Auto_Blocks();
		}

		return self::$instance;
	}

  public function __construct() {
    add_action( 'acf/init', array( $this, 'acf_init' ), 999 );

		add_filter( 'block_categories', array( $this, 'register_category' ), 999, 2 );

		add_action( 'print_default_editor_scripts', array( $this, 'admin_footer_scripts' ), 999 );
  }


	// Init
	public function acf_init() {
		$this->directory = apply_filters( 'acf/auto_blocks/directory', get_template_directory() . '/acf-blocks' );

		$this->register_blocks();
	}


	// Register category
	public function register_category( $categories, $post ) {
		$categories[] = array(
			'slug' => 'acf_auto_blocks',
			'title' => __( 'Auto Blocks', 'acfab' ),
			'icon'  => '',
		);

	  return $categories;
	}


	// Register blocks
	public function register_blocks() {
		if ( function_exists('acf_register_block') ) {
	    $auto_blocks = $this->get_auto_blocks();

	    foreach ( $auto_blocks as $auto_block ) {
	      $field_group = acf_get_field_group( $auto_block->ID );
				$options = ACF_Auto_Blocks::parse_options( $field_group );

	  		acf_register_block( array(
	  			'name'            => $options['auto_block_key'],
	  			'title'           => $options['title'],
	  			'description'     => $options['auto_block_description'],
	  			'category'        => 'acf_auto_blocks',
	  			'icon'            => $options['auto_block_icon'],
	  			'keywords'        => $options['auto_block_align'],
	  			'post_types'      => $options['auto_block_post_types'],
	  			'mode'            => 'edit',
	  			'render_callback'	=> array( $this, 'render_block' ),
	  			'supports'        => array(
	  				'align'         => $options['auto_block_align'],
	  				'multiple'      => $options['auto_block_multiple'],
	  				'reusable'      => $options['auto_block_reusable'],
	  			),
	  		) );
	    }
		}
	}


	// Render block
	public function render_block( $block ) {
		$slug = str_replace( 'acf/', '', $block['name'] );

		$this->template_part( $slug, array(
			'block' => $block,
	    'data' => get_fields(),
		) );
	}


	// Load block template
	public function template_part( $template, $args ) {
	  $path = $this->directory . '/' . $template . '.php';

	  if ( file_exists( $path ) ) {
	    extract( $args );

			try {
	    	include $path;
			} catch (Exception $e) {
				echo __( 'Error in block template file.', 'acfab' ) . ' (' . $template . '.php)';
			} catch (Error $e) {
				echo __( 'Error in block template file.', 'acfab' ) . ' (' . $template . '.php)';
			}
	  } else {
			echo __( 'Block template file not found.', 'acfab' ) . ' (' . $template . '.php)';
		}
	}


	// Hide blocks based on post type
	function admin_footer_scripts() {
	  $blacklist = array();
		$post_type = get_post_type();
		$auto_blocks = $this->get_auto_blocks();

		foreach ( $auto_blocks as $auto_block ) {
			$field_group = acf_get_field_group( $auto_block->ID );
			$options = ACF_Auto_Blocks::parse_options( $field_group );

			if ( ! in_array( $post_type, $options['auto_block_post_types'] ) ) {
				$blacklist[] = 'acf/' . $options['auto_block_key'];
			}
		}

		?>
		<script>
		var acfab_blacklist = <?php echo json_encode( $blacklist ); ?>;

		wp.hooks.addFilter('blocks.registerBlockType', 'acfab_hide_blocks', function(settings, name) {
		  if (acfab_blacklist.indexOf(name) > -1) {
		    settings = $.extend(true, settings, {
		      supports: {
		        inserter: false
		      }
		    });
		  }

			return settings;
		});
		</script>
		<?php
	}


	// Get auto blocks
	public function get_auto_blocks() {
		// TODO optimize this?
		$auto_blocks = get_posts( array(
			'post_type' => 'acf-field-group',
			'post_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_auto_block',
					'value' => 'on',
				),
			),
		) );

		return $auto_blocks;
	}


	// Parse field group options
	public static function parse_options( $options ) {
		$options = wp_parse_args( $options, array(
			'auto_block' => 0,
			'auto_block_key' => '',
			'auto_block_description' => '',
			'auto_block_keywords' => '',
			'auto_block_align' => array(),
			'auto_block_multiple' => 0,
			'auto_block_reusable' => 0,
			'auto_block_post_types' => array( 'wp_block', 'post', 'page' ),
		) );

		if ( ! is_array( $options['auto_block_align'] ) ) {
			$options['auto_block_align'] = array_filter( array(
				$options['auto_block_align']
			) );
		}

		if ( ! is_array( $options['auto_block_post_types'] ) ) {
			$options['auto_block_post_types'] = array_filter( array(
				$options['auto_block_post_types']
			) );
		}

		$options['auto_block_post_types'][] = 'wp_block';
    $options['auto_block_post_types'] = array_unique( $options['auto_block_post_types'] );

		return $options;
	}

}


// Instance

ACF_Auto_Blocks::get_instance();

include 'includes/converter.php';
include 'includes/settings.php';
