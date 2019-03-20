<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class ACF_Auto_Block_Settings {

  protected static $instance;

  public static function get_instance() {
    if ( empty( self::$instance ) && ! ( self::$instance instanceof ACF_Auto_Block_Settings ) ) {
      self::$instance = new ACF_Auto_Block_Settings();
    }

    return self::$instance;
  }

  public function __construct() {
    add_filter( 'manage_edit-acf-field-group_columns', array( $this, 'field_group_columns' ), 999, 1 );

    add_action( 'manage_acf-field-group_posts_custom_column', array( $this, 'field_group_columns_html' ), 10, 2 );

    add_action( 'acf/render_field_group_settings', array( $this, 'field_group_settings' ), 999 );

    add_action( 'acf/update_field_group', array( $this, 'update_field_group' ), 999 );

    add_action( 'admin_footer', array( $this, 'admin_footer' ), 999 );

    add_action( 'acf/field_group/admin_footer', array( $this, 'admin_footer_edit' ), 999 );
  }


  // Modify field group columns
  public function field_group_columns( $columns ) {
    $keys = array_keys( $columns );
    $last_key = array_pop( $keys );
    $last_val = array_pop( $columns );

    $columns['acf-fg-ab'] = '<i class="acf-icon -dot-3 small acf-js-tooltip" title="' . __( 'Auto Block', 'acfab' ) . '"></i>';
    $columns[ $last_key ] = $last_val;

    return $columns;
  }


  // Draw auto block column
  public function field_group_columns_html( $column, $post_id ) {
    if ( $column !== 'acf-fg-ab' ) {
      return;
    }

    $auto_block = get_post_meta( $post_id, '_auto_block', true );

    if ( ! empty( $auto_block ) ) {
      echo '<i class="acf-icon -location auto_block small acf-js-tooltip" title="' . __( 'Auto Block', 'acfab' ) . '"></i>';
    }
  }


  // Draw field group settings
  public function field_group_settings( $field_group ) {
    $options = ACF_Auto_Blocks::parse_options( $field_group );

    $post_types = get_post_types( array(
      // 'public' => true
    ), 'objects' );

    $types = array(
      // 'wp_block' => 'Blocks',
    );
    foreach ( $post_types as $post_type ) {
      $types[ $post_type->name ] = $post_type->label . ' (' . $post_type->name . ')';
    }

    $icons_path = plugin_dir_path( ACF_Auto_Blocks::get_instance()->file ) . 'assets/icons.json';
    $icons_file = file_get_contents( $icons_path );
    $icons_json = json_decode( $icons_file, true );
    $icons = array();

    foreach ( $icons_json as $k => $v ) {
      $icons[ $v ] = '<span class="icon">' . $v . '</span>';
    }

    unset( $types['attachment'] );
    unset( $types['revision'] );
    unset( $types['nav_menu_item'] );
    unset( $types['custom_css'] );
    unset( $types['customize_changeset'] );
    unset( $types['oembed_cache'] );
    unset( $types['user_request'] );
    unset( $types['np-redirect'] );
    unset( $types['acf-field-group'] );
    unset( $types['acf-field'] );

    acf_render_field_wrap( array(
      'label' => __( 'Auto Block', 'acfab' ),
      'instructions' => __( 'Auto register this field group as a Gutenberg block. Don\'t forget to create a new template partial.', 'acfab' ),
      'type' => 'true_false',
      'name' => 'auto_block',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block'],
      'ui' => 1,
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Block Key', 'acfab' ),
      'instructions' => __( 'Identifier for block. Also used as template partial file name.', 'acfab' ),
      'type' => 'text',
      'name' => 'auto_block_key',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_key'],
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Block Description', 'acfab' ),
      'instructions' => '',
      'type' => 'text',
      'name' => 'auto_block_description',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_description'],
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Block Icon', 'acfab' ),
      'instructions' => '',
      'type' => 'radio',
      'name' => 'auto_block_icon',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_icon'],
      'toggle' => true,
      'choices' => $icons,
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Post Types', 'acfab' ),
      'instructions' => '',
      'type' => 'checkbox',
      'name' => 'auto_block_post_types',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_post_types'],
      'toggle' => true,
      'choices' => $types,
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Multiple Blocks', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_multiple',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_multiple'],
      'ui' => 1,
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Reusable Block', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_reusable',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_reusable'],
      'ui' => 1,
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Block Alignment', 'acfab' ),
      'instructions' => '',
      'type' => 'checkbox',
      'name' => 'auto_block_align',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_align'],
      'toggle' => true,
      'choices' => array(
        'left' => 'Left',
        'right' => 'Right',
        'center' => 'Center',
        'wide' => 'Wide',
        'full' => 'Full',
      ),
    ) );
  }


  // Update field group settings
  public function update_field_group( $field_group ) {
    if ( ! empty( $field_group['auto_block'] ) ) {

      $existing = false;
      if ( empty( $field_group['auto_block_key'] ) ) {
        $block_key = str_replace( '_', '-', sanitize_title( $field_group['title'] ) );
      } else {
        $block_key = $field_group['auto_block_key'];
      }

      $field_group = ACF_Auto_Blocks::parse_options( $field_group );

      foreach ( $field_group['location'] as $location_set ) {
        foreach ( $location_set as $location ) {
          if (
            $location['param'] == 'block' &&
            $location['operator'] == '==' &&
            $location['value'] == 'acf/' . $block_key
          ) {
            $existing = true;
            break;
          }
        }
      }

      if ( ! $existing ) {
        $field_group['location'][] = array(
          array(
            'param' => 'block',
            'operator' => '==',
            'value' => 'acf/' . $block_key,
          ),
        );
      }

      remove_action( 'acf/update_field_group', array( $this, 'update_field_group' ), 999 );
      acf_update_field_group( $field_group );
      add_action( 'acf/update_field_group', array( $this, 'update_field_group' ), 999 );

      update_post_meta( $field_group['ID'], '_auto_block', 'on' );
    } else {
      delete_post_meta( $field_group['ID'], '_auto_block' );
    }
  }


  // Setup admin CSS
  public function admin_footer() {
    ?>
    <style>
      #acf-field-group-wrap .wp-list-table .column-acf-fg-ab {
        width: 10%;
      }
      .acf-icon.auto_block {
        background-color: #5EE8BF;
        color: #fff;
      }
      [data-name="auto_block_icon"] .acf-radio-list li {
        float: left;
      }
      [data-name="auto_block_icon"] .acf-radio-list .icon {
        height: 35px;
        width: 35px;
        display: block;
        border-radius: 2px;
        line-height: 50px;
        margin: 2px;
        position: relative;
        text-align: center;
      }
      [data-name="auto_block_icon"] .acf-radio-list .icon:hover {
        background: #ddd;
      }
      [data-name="auto_block_icon"] .acf-radio-list input:checked + .icon {
        background: #2a9bd9;
      }
      [data-name="auto_block_icon"] .acf-radio-list svg {
        display: inline-block;
      }
      [data-name="auto_block_icon"] .acf-radio-list input {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
      }
    </style>
    <?php
  }


  // Setup admin JS
  public function admin_footer_edit() {
    ?>
    <script>
      (function($) {
        $(function() {
          if (typeof acf == "undefined" || !acf) {
            return;
          }

          $("#acf_field_group-auto_block").on("change", toggleSettings);

          toggleSettings();

          function toggleSettings() {
            var checked = $("#acf_field_group-auto_block").is(":checked");

            if (checked) {
              $('[data-name*="auto_block_"]').show();
            } else {
              $('[data-name*="auto_block_"]').hide();
            }
          }
        });
      })(jQuery);
    </script>
    <?php
  }

}


// Instance

ACF_Auto_Block_Settings::get_instance();



function acfab_init() {
  acf_add_options_sub_page( array(
    'page_title' => 'Post Type Templates',
    'menu_title' => 'Templates',
    'menu_slug' => 'acfab-templates',
    'parent_slug' => 'edit.php?post_type=acf-field-group',
    'autoload' => true,
    'position' => 0,
  ) );

  acf_add_local_field_group( array(
    'key' => 'group_5c9258a942887',
    'title' => 'Templates',
    'fields' => array(
      array(
        'key' => 'field_5c9258c55a2a2',
        'label' => 'Templates',
        'name' => 'acfab_templates',
        'type' => 'repeater',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'collapsed' => '',
        'min' => 0,
        'max' => 0,
        'layout' => 'block',
        'button_label' => 'Add Template',
        'sub_fields' => array(
          array(
            'key' => 'field_5c9258b15a2a1',
            'label' => 'Post Type',
            'name' => 'acfab_post_type',
            'type' => 'select',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
            'choices' => array(
              'post' => 'Posts (post)',
              'page' => 'Pages (page)',
            ),
            'default_value' => array(
            ),
            'allow_null' => 0,
            'multiple' => 0,
            'ui' => 0,
            'return_format' => 'value',
            'ajax' => 0,
            'placeholder' => '',
          ),
          array(
            'key' => 'field_5c925b7daeb44',
            'label' => 'Template Lock',
            'name' => 'acfab_template_lock',
            'type' => 'button_group',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '50',
              'class' => '',
              'id' => '',
            ),
            'choices' => array(
              'false' => 'None',
              'all' => 'All',
              'insert' => 'Insert',
            ),
            'allow_null' => 0,
            'default_value' => 'false',
            'layout' => 'horizontal',
            'return_format' => 'value',
          ),
          array(
            'key' => 'field_5c9258e45a2a3',
            'label' => 'Template',
            'name' => 'acfab_post_template',
            'type' => 'repeater',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '',
              'class' => '',
              'id' => '',
            ),
            'collapsed' => '',
            'min' => 1,
            'max' => 0,
            'layout' => 'row',
            'button_label' => 'Add Block',
            'sub_fields' => array(
              array(
                'key' => 'field_5c9258f85a2a4',
                'label' => 'Block',
                'name' => 'acfab_block',
                'type' => 'select',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                  'width' => '',
                  'class' => '',
                  'id' => '',
                ),
                'choices' => array(
                  'acfab/region' => 'Region',
                ),
                'default_value' => array(
                ),
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 0,
                'return_format' => 'value',
                'ajax' => 0,
                'placeholder' => '',
              ),
            ),
          ),
        ),
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'options_page',
          'operator' => '==',
          'value' => 'acfab-templates',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
  ) );
}
add_action( 'acf/init', 'acfab_init', 999 );

function acfab_populate_post_types( $field ) {
  $field['choices'] = array();

  $post_types = get_post_types( array(
  ), 'objects' );

  // $types = array(
  //   // 'wp_block' => 'Blocks',
  // );
  foreach ( $post_types as $post_type ) {
    // $types[ $post_type->name ] = $post_type->label . ' (' . $post_type->name . ')';
    $field['choices'][ $post_type->name ] = $post_type->label . ' (' . $post_type->name . ')';
  }

  return $field;
}
add_filter( 'acf/load_field/name=acfab_post_type', 'acfab_populate_post_types' );

function acfab_populate_blocks( $field ) {
  $field['choices'] = array();

  $auto_blocks = ACF_Auto_Blocks::get_instance()->get_auto_blocks();

  $field['choices'][ 'acfab/region' ] = 'Region';

  foreach ( $auto_blocks as $auto_block ) {
    $field['choices'][ 'acf/' . $auto_block['auto_block_key'] ] = $auto_block['title'];
  }

  return $field;
}
add_filter( 'acf/load_field/name=acfab_block', 'acfab_populate_blocks' );
