<?php
/*
Plugin Name: Advanced Custom Fields: Auto Blocks
Plugin URI: https://github.com/benplum/ACF-Auto-Blocks
Description: Auto-register ACF field groups as blocks in the block editor.
Version: 2.0.8
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
    add_action( 'init', [ $this, 'init' ], 999 );

    add_action( 'acf/init', [ $this, 'acf_init' ], 999 );

    add_filter( 'acf/settings/load_json', [ $this, 'acf_load_json' ], 999 );
    add_filter( 'acf/settings/save_json', [ $this, 'acf_save_json' ], 999 );

    add_filter( 'block_categories_all', [ $this, 'register_category' ], 999, 2 );

    add_action( 'print_default_editor_scripts', [ $this, 'admin_footer_scripts' ], 999 );

    add_action( 'save_post', [ $this, 'set_post_block_meta' ] );
  }


  // Init
  public function init() {
    $this->register_editor_blocks();
  }


  // ACF Init
  public function acf_init() {
    $this->directory = ACF_Auto_Blocks::get_directory();

    $this->register_blocks_v1();
    $this->register_blocks_v2();
  }


  // ACF Load JSON
  public function acf_load_json( $paths ) {
    // Block V2 Support
    $auto_blocks = ACF_Auto_Blocks::get_auto_blocks_v2();

    foreach ( $auto_blocks as $auto_block ) {
      $paths[] = $auto_block['dir'];
    }

    return $paths;
  }


  // ACF Save JSON
  public function acf_save_json( $path ) {
    // Block V2 Support
    if ( ! empty( $_POST['acf_field_group']['key'] ) ) {
      $slug = $_POST['acf_field_group']['key'];

      if ( strpos( $slug, 'group_block_' ) > -1 ) {
        $block = $this->directory . '/' . str_ireplace( [ 'group_block_', '_' ], [ '', '-' ], $slug );

        if ( is_dir( $block ) ) {
          $path = $block;
        }
      }
    }

    return $path;
  }


  // Register category
  public function register_category( $categories, $post ) {
    $categories = array_merge( [
      [
        'slug' => 'acf_auto_blocks',
        'title' => __( 'Auto Blocks', 'acfab' ),
        'icon'  => '',
      ],
    ], $categories );

    return $categories;
  }


  // Register blocks - v1
  public function register_blocks_v1() {
    if ( function_exists('acf_register_block') ) {
      $auto_blocks = ACF_Auto_Blocks::get_auto_blocks_v1();

      foreach ( $auto_blocks as $auto_block ) {
        $field_group = $auto_block;
        $options = ACF_Auto_Blocks::parse_options_v1( $field_group );

        $args = [
          'name'            => $options['auto_block_key'],
          'title'           => $options['title'],
          'description'     => $options['auto_block_description'],
          'category'        => 'acf_auto_blocks',
          'icon'            => $options['auto_block_icon'],
          'keywords'        => explode( ',', $options['auto_block_keywords'] ),
          'post_types'      => $options['auto_block_post_types'],
          'mode'            => $options['auto_block_mode_default'],
          'align_text'      => $options['auto_block_text_align_default'],
          'align_content'   => $options['auto_block_content_align_default'],
          'render_callback' => [ $this, 'render_block_v1' ],
          'supports'        => [
            'mode'          => ( $options['auto_block_mode'] == 0 ) ? false : true,
            'align'         => $options['auto_block_align'],
            'multiple'      => $options['auto_block_multiple'],
            'reusable'      => $options['auto_block_reusable'],
            'align_text'    => $options['auto_block_text_align'],
            'align_content' => ( $options['auto_block_content_align'] ) ? $options['auto_block_content_align_type'] : '', // 1 or matrix
            'jsx'           => $options['auto_block_jsx'],
          ],
          'example'         => [
            'attributes'    => [
              'mode'        => 'preview',
              'data'        => [
                'screenshot' => $options['auto_block_screenshot'],
                'is_preview' => true,
              ],
            ],
          ],
        ];

        $args = apply_filters( 'acf/auto_blocks/parse_block_options', $args ); // Deprecated
        $args = apply_filters( 'acf/auto_blocks/v1/parse_block_options', $args );

        acf_register_block( $args );
      }
    }
  }

  // Register blocks - v2
  public function register_blocks_v2() {
    if ( function_exists('register_block_type') ) {
      $auto_blocks = ACF_Auto_Blocks::get_auto_blocks_v2();

      foreach ( $auto_blocks as $auto_block ) {
        $options = [
          'render_callback' => [ $this, 'render_block_v2' ],
        ];

        $options = apply_filters( 'acf/auto_blocks/v2/parse_block_options', $options );

        register_block_type( $auto_block['dir'], $options );
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

  //   $templates = [];

  //   foreach ( $template_settings as $settings ) {
  //     $template = [];

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
      [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ],
      filemtime( plugin_dir_path( __FILE__ ) . 'assets/blocks.js' )
    );

    register_block_type( 'w/region', [
      'editor_script' => 'acfab-blocks',
    ] );
  }


  // Render block - v1
  public function render_block_v1( $block ) {
    $slug = str_replace( 'acf/', '', $block['name'] );
    $template = $slug;

    $this->do_render_block( $block, $slug, $template );
  }


  // Render block - v2
  public function render_block_v2( $block ) {
    $slug = str_replace( 'acf/block-', '', $block['name'] );
    $file = apply_filters( 'acf/auto_blocks/cli/template_file', 'template', $slug );
    $template = $slug . '/' . $file;

    $this->do_render_block( $block, $slug, $template );
  }


  // Render block
  public function do_render_block( $block, $slug, $template ) {
    ob_start();

    $is_preview = get_field( 'is_preview' );

    if ( $is_preview ) {
      if ( ! empty( $block['example']['attributes']['data']['screenshot'] ) ) {
        $src = wp_get_attachment_image_src( $block['example']['attributes']['data']['screenshot'], 'medium' );
        $preview = '<img src="' . $src[0] . '" alt="" class="acfab_preview_image">';
      } else {
        $preview = '<p>' . $block['title'] . ' (' . $block['name'] . ')</p>';
      }

      echo apply_filters( 'acf/auto_blocks/block_preview', $preview, $block );
    } else {
      $data = apply_filters( 'acf/auto_blocks/block_data', get_fields(), $block );

      $this->template_part( $template, [
        'is_admin' => is_admin(),
        'block' => $block,
        'data' => $data,
      ] );
    }

    $content = ob_get_clean();

    if ( is_admin() ) {
      $content = apply_filters( 'acf/auto_blocks/render_block', $content, $block );
    }

    echo $content;
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
    $blocklist = [];
    $post_type = get_post_type();
    $auto_blocks = ACF_Auto_Blocks::get_auto_blocks_v1();

    foreach ( $auto_blocks as $auto_block ) {
      $field_group = $auto_block;
      $options = ACF_Auto_Blocks::parse_options_v1( $field_group );

      if ( ! in_array( $post_type, $options['auto_block_post_types'] ) ) {
        $blocklist[] = 'acf/' . $options['auto_block_key'];
      }
    }

?>
<script>
  var acfab_blocklist = <?php echo json_encode( $blocklist ); ?>;

  wp.hooks.addFilter('blocks.registerBlockType', 'acfab_hide_blocks', function(settings, name) {
    if (acfab_blocklist.indexOf(name) > -1) {
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

  // Deprecated
  public static function get_auto_blocks() {
    return ACF_Auto_Blocks::get_auto_blocks_v1();
  }

  // Get auto blocks - v1
  public static function get_auto_blocks_v1() {
    $groups = acf_get_field_groups();

    $auto_blocks = [];

    foreach ( $groups as $group ) {
      $is_block = ( ! empty( $group['auto_block'] ) && $group['auto_block'] == 1 && ACF_Auto_Blocks::check_block_location_v1( $group['location'] ) );

      if ( $is_block ) {
        $auto_blocks[] = $group;
      }
    }

    return $auto_blocks;
  }

  // Get auto blocks - v2
  public static function get_auto_blocks_v2() {
    $dir = ACF_Auto_Blocks::get_directory() . '/';
    $scan = scandir( $dir );

    $auto_blocks = [];

    foreach ( $scan as $slug ) {
      if ( in_array( $slug, [ '.', '..' ] ) || ! is_dir( $dir . $slug ) ) {
        continue;
      }

      $json = $dir . $slug . '/block.json';

      if ( file_exists( $json ) ) {
        $args = [
          'key' => $slug,
          'auto_block_key' => $slug, // Backward compatability
          'acf_key' => ACF_Auto_blocks::snake_case( 'group_block_' . $slug ), // field group key
          'dir' => $dir . $slug,
          'json' => $json,
          'settings' => json_decode( file_get_contents( $json ), true ),
        ];

        $auto_blocks[ $slug ] = apply_filters( 'acf/auto_blocks/block_settings', $args );
      }
    }

    return array_filter( $auto_blocks );
  }


  // Validate location
  public static function check_block_location_v1( $array ) {
    $check = false;

    foreach ( $array as $item ) {
      if ( ! empty( $item['param'] ) && $item['param'] == 'block' ) {
        $check = true;
      } else if ( is_array( $item ) ) {
        $check = ACF_Auto_Blocks::check_block_location_v1( $item );
      }

      if ( $check ) {
        return true;
      }
    }

    return false;
  }


  // Parse field group options
  public static function parse_options_v1( $options ) {
    $options = wp_parse_args( $options, [
      'auto_block' => 0,
      'auto_block_key' => '',
      'auto_block_description' => '',
      'auto_block_icon' => '',
      'auto_block_keywords' => '',
      'auto_block_align' => [],
      'auto_block_multiple' => 0,
      'auto_block_reusable' => 0,
      'auto_block_mode' => true,
      'auto_block_mode_default' => 'auto',
      'auto_block_post_types' => [ 'wp_block', 'post', 'page' ],
      'auto_block_text_align' => 0,
      'auto_block_text_align_default' => '',
      'auto_block_content_align' => 0,
      'auto_block_content_align_type' => '',
      'auto_block_content_align_default' => '',
      'auto_block_content_align_default_matrix' => '',
      'auto_block_jsx' => 0,
      'auto_block_screenshot' => '',
    ] );

    if ( ! is_array( $options['auto_block_align'] ) ) {
      $options['auto_block_align'] = array_filter( [
        $options['auto_block_align']
      ] );
    }

    if ( ! is_array( $options['auto_block_post_types'] ) ) {
      $options['auto_block_post_types'] = array_filter( [
        $options['auto_block_post_types']
      ] );
    }

    if ( $options['auto_block_content_align_type'] == 'matrix' ) {
      $options['auto_block_content_align_default'] = $options['auto_block_content_align_default_matrix'];
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
    $blocks = [];

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
      if ( strpos( $block['blockName'], 'acf/' ) == 0 && ! empty( $block['attrs']['data'] ) ) {
        // $block_id = $block['attrs']['id'];

        foreach ( $block['attrs']['data'] as $field_key => $field_val ) {
          if ( substr( $field_key, 0, 1 ) !== '_' && ! empty( $block['attrs']['data'][ '_' . $field_key ] ) ) {
            // $field_obj = get_field_object( $field_key, $block_id );
            $field_obj = get_field_object( $block['attrs']['data'][ '_' . $field_key ] );

            if ( ! empty( $field_obj['auto_block_save_to_meta'] ) ) {
              $stm_key = $field_obj['auto_block_save_to_meta'];

              if ( $field_obj['type'] == 'repeater' ) { // repeater
                update_post_meta( $post_id, $stm_key, $field_val );
                update_post_meta( $post_id, '_' . $stm_key, $field_obj['key'] );

                foreach ( $block['attrs']['data'] as $subfield_key => $subfield_val ) {
                  if ( substr( $subfield_key, 0, 1 ) !== '_' && strpos( $subfield_key, $field_key . '_' ) > -1 ) {
                    $stm_sub_key = str_ireplace( $field_key, $stm_key, $subfield_key );

                    update_post_meta( $post_id, $stm_sub_key, $subfield_val );
                    update_post_meta( $post_id, '_' . $stm_sub_key, $block['attrs']['data'][ '_' . $subfield_key ] );
                  }
                }

              } else { // Standard fields
                if ( $stm_key == '_thumbnail_id' ) {
                  // set_post_thumbnail( $post_id, $field_val );
                  update_post_meta( $post_id, $stm_key, $field_val );
                } else {
                  update_post_meta( $post_id, $stm_key, $field_val );
                  update_post_meta( $post_id, '_' . $stm_key, $field_obj['key'] );
                }
              }
            }

          }
        }

      }
    }

  }


  // Helpers

  // Get Directory
  public static function get_directory() {
    return apply_filters( 'acf/auto_blocks/directory', get_stylesheet_directory() . '/acf-blocks' );
  }

  public static function snake_case( $text = '' ) {
    return strtolower( str_ireplace( '-', '_', $text ) );
  }

  public static function kebab_case( $text = '' ) {
    return strtolower( str_ireplace( '_', '-', $text ) );
  }

  public static function title_case( $text = '' ) {
    return ucwords( str_ireplace( [ '_', '-' ], ' ', $text ) );
  }

}


// Instance

ACF_Auto_Blocks::get_instance();

$path = plugin_dir_path( __FILE__ );

include $path . 'includes/cli.php';
include $path . 'includes/settings.php';
include $path . 'includes/updater.php';