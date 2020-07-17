<?php
/*
Plugin Name: Advanced Custom Fields: Auto Blocks
Plugin URI: https://github.com/benplum/ACF-Auto-Blocks
Description: Auto-register ACF field groups as blocks in the new block editor (Gutenberg).
Version: 1.1.4
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

  public $file = __FILE__;
  public $directory = '';

  public static function get_instance() {
    if ( empty( self::$instance ) && ! ( self::$instance instanceof ACF_Auto_Blocks ) ) {
      self::$instance = new ACF_Auto_Blocks();
    }

    return self::$instance;
  }

  public function __construct() {
    add_action( 'init', array( $this, 'init' ), 999 );

    add_action( 'acf/init', array( $this, 'acf_init' ), 999 );

    add_filter( 'block_categories', array( $this, 'register_category' ), 999, 2 );

    add_action( 'print_default_editor_scripts', array( $this, 'admin_footer_scripts' ), 999 );

    // add_action( 'acf/update_value', array( $this, 'acf_update_value' ), 999, 3 );
    add_action( 'save_post', array( $this, 'set_post_block_meta' ) );
  }


  // Init
  public function init() {
    // $this->register_post_templates();
    $this->register_editor_blocks();
  }


  // ACF Init
  public function acf_init() {
    $this->directory = apply_filters( 'acf/auto_blocks/directory', get_stylesheet_directory() . '/acf-blocks' );

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
      // $auto_blocks = $this->get_auto_blocks();
      $auto_blocks = ACF_Auto_Blocks::get_auto_blocks();

      foreach ( $auto_blocks as $auto_block ) {
        // $field_group = acf_get_field_group( $auto_block['ID'] );
        $field_group = $auto_block;
        $options = ACF_Auto_Blocks::parse_options( $field_group );

        $args = array(
          'name'            => $options['auto_block_key'],
          'title'           => $options['title'],
          'description'     => $options['auto_block_description'],
          'category'        => 'acf_auto_blocks',
          'icon'            => $options['auto_block_icon'],
          'keywords'        => $options['auto_block_align'],
          'post_types'      => $options['auto_block_post_types'],
          'mode'            => $options['auto_block_mode_default'],
          'render_callback' => array( $this, 'render_block' ),
          'supports'        => array(
            'mode'          => ( $options['auto_block_mode'] == 0 ) ? false : true,
            'align'         => $options['auto_block_align'],
            'multiple'      => $options['auto_block_multiple'],
            'reusable'      => $options['auto_block_reusable'],
          ),
        );

        $args = apply_filters( 'acf/auto_blocks/parse_block_options', $args );

        acf_register_block( $args );
      }
    }
  }


  // // Register post templates
  // public function register_post_templates() {
  //   $path = acf_get_setting('save_json');
  //   $path = untrailingslashit( $path );

  //   $file = 'acfab_templates.json';
  //   $data = array(
  //     'modified' => current_time( 'timestamp'),
  //     'templates' => $template_settings,
  //   );

  //   if ( file_exists( "{$path}/{$file}" ) ) {
  //     $json = file_get_contents( "{$path}/{$file}" );
  //     $template_settings = json_decode( $json, true );
  //   }

  //   if ( empty( $template_settings ) ) {
  //     $template_settings = get_field( 'acfab_templates', 'option' );
  //   }

  //   if ( empty( $template_settings ) ) {
  //     return;
  //   }

  //   $templates = array();

  //   foreach ( $template_settings as $settings ) {
  //     $template = array();

  //     foreach ( $settings['acfab_post_template'] as $block ) {
  //       $template[] = array( $block['acfab_block'] );
  //     }

  //     $templates[ $settings['acfab_post_type'] ] = array(
  //       'template' => $template,
  //       'template_lock' => $settings['acfab_template_lock'],
  //     );
  //   }

  //   foreach ( $templates as $post_type => $options ) {
  //     $object = get_post_type_object( $post_type );

  //     $object->template = $options['template'];

  //     if ( ! empty( $options['template_lock'] ) ) {
  //       $object->template_lock = $options['template_lock'];
  //     }
  //   }
  // }


  // Register editor blocks
  public function register_editor_blocks() {
  	if ( ! function_exists( 'register_block_type' ) ) {
  		return;
  	}

  	wp_register_script(
  		'acfab-blocks',
  		plugin_dir_url( __FILE__ ) . 'assets/blocks.js',
  		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
  		filemtime( plugin_dir_path( __FILE__ ) . 'assets/blocks.js' )
  	);

  	register_block_type( 'w/region', array(
  		'editor_script' => 'acfab-blocks',
  	) );
  }


  // Render block
  public function render_block( $block ) {
    $slug = str_replace( 'acf/', '', $block['name'] );

    ob_start();

    $this->template_part( $slug, array(
      'is_admin' => is_admin(),
      'block' => $block,
      'data' => get_fields(),
    ) );

    $content = ob_get_clean();

    if ( is_admin() ) {
      echo apply_filters( 'acf/auto_blocks/render_block', $content, $block );
    } else {
      echo $content;
    }
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
    // $auto_blocks = $this->get_auto_blocks();
    $auto_blocks = ACF_Auto_Blocks::get_auto_blocks();

    foreach ( $auto_blocks as $auto_block ) {
      // $field_group = acf_get_field_group( $auto_block['ID'] );
      $field_group = $auto_block;
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
  public static function get_auto_blocks() {
    $groups = acf_get_field_groups();

    $auto_blocks = array();

    foreach ( $groups as $group ) {
      $is_block = ACF_Auto_Blocks::check_block_location( $group['location'] );

      if ( $is_block ) {
        $auto_blocks[] = $group;
      }
    }

    return $auto_blocks;
  }


  // Validate location
  public static function check_block_location( $array ) {
    $check = false;

    foreach ( $array as $item ) {
      if ( ! empty( $item['param'] ) && $item['param'] == 'block' ) {
        $check = true;
      } else if ( is_array( $item ) ) {
        $check = ACF_Auto_Blocks::check_block_location( $item );
      }

      if ( $check ) {
        return true;
      }
    }

    return false;
  }


  // Parse field group options
  public static function parse_options( $options ) {
    $options = wp_parse_args( $options, array(
      'auto_block' => 0,
      'auto_block_key' => '',
      'auto_block_description' => '',
      'auto_block_icon' => '',
      'auto_block_keywords' => '',
      'auto_block_align' => array(),
      'auto_block_multiple' => 0,
      'auto_block_reusable' => 0,
      'auto_block_mode' => true,
      'auto_block_mode_default' => 'auto',
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


  // Return block data
  public static function get_post_blocks( $id = false ) {
    if ( empty( $id ) ) {
      $id = get_the_id();
    }

    $post = get_post( $id );
    $blocks = array();

    if ( has_blocks( $post->post_content ) ) {
      $all_blocks = parse_blocks( $post->post_content );

      foreach ( $all_blocks as $block ) {
        if ( ! empty( $block['blockName'] ) ) {
          $blocks[] = $block;
        }
      }
    }

    return $blocks;
  }


  // Save block data as post meta on save
  public function set_post_block_meta( $post_id ) {
    $blocks = ACF_Auto_Blocks::get_post_blocks( $post_id );

    foreach ( $blocks as $block ) {
      if ( strpos( $block['blockName'], 'acf/' ) == 0 ) {
        $block_id = $block['attrs']['id'];

        foreach ( $block['attrs']['data'] as $key => $val ) {
          if ( substr( $key, 0, 1 ) !== '_' ) {
            $field = get_field_object( $key, $block_id );

            if ( ! empty( $field['auto_block_save_to_meta'] ) ) {
              $meta_key = $field['auto_block_save_to_meta'];

              if ( $meta_key == '_thumbnail_id' ) {
                set_post_thumbnail( $post_id, $val );
              } else {
                update_post_meta( $post_id, $meta_key, $val );
                update_post_meta( $post_id, '_' . $meta_key, $field['key'] );
              }
            }

          }
        }

      }
    }

  }

}


// Instance

ACF_Auto_Blocks::get_instance();

include 'includes/converter.php';
include 'includes/settings.php';
include 'includes/updater.php';