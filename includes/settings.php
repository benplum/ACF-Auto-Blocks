<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class ACF_Auto_Blocks_Settings {

  protected static $instance;

  public static function get_instance() {
    if ( empty( self::$instance ) && ! ( self::$instance instanceof ACF_Auto_Blocks_Settings ) ) {
      self::$instance = new ACF_Auto_Blocks_Settings();
    }

    return self::$instance;
  }

  public function __construct() {
    // add_action( 'acf/init', array( $this, 'acf_init' ), 998 );

    // add_action( 'acf/save_post', array( $this, 'export_templates' ), 999 );

    // add_filter( 'acf/load_field/name=acfab_post_type', array( $this, 'populate_post_type_select' ), 999, 1 );
    // add_filter( 'acf/prepare_field/name=acfab_post_type', array( $this, 'populate_post_type_select' ), 999, 1 );

    // add_filter( 'acf/load_field/name=acfab_block', array( $this, 'populate_block_select' ), 999, 1 );
    // add_filter( 'acf/prepare_field/name=acfab_block', array( $this, 'populate_block_select' ), 999, 1 );

    //

    add_filter( 'manage_edit-acf-field-group_columns', array( $this, 'field_group_columns' ), 999, 1 );

    add_action( 'manage_acf-field-group_posts_custom_column', array( $this, 'field_group_columns_html' ), 10, 2 );

    add_action( 'acf/render_field_group_settings', array( $this, 'field_group_settings' ), 999 );

    add_action( 'acf/update_field_group', array( $this, 'update_field_group' ), 999 );

    add_action( 'acf/render_field_settings', array( $this, 'acf_render_field_settings' ), 999, 1 );

    add_action( 'admin_footer', array( $this, 'admin_footer' ), 999 );

    add_action( 'acf/field_group/admin_footer', array( $this, 'admin_footer_edit' ), 999 );
  }


  // // ACF Init
  // public function acf_init() {
  //   acf_add_options_sub_page( array(
  //     'page_title' => 'Post Type Templates',
  //     'menu_title' => 'Templates',
  //     'menu_slug' => 'acfab-templates',
  //     'parent_slug' => 'edit.php?post_type=acf-field-group',
  //     'autoload' => true,
  //     'position' => '1',
  //   ) );

  //   acf_add_local_field_group( array(
  //     'key' => 'group_5c9258a942887',
  //     'title' => 'Templates',
  //     'fields' => array(
  //       array(
  //         'key' => 'field_5c9258c55a2a2',
  //         'label' => 'Templates',
  //         'name' => 'acfab_templates',
  //         'type' => 'repeater',
  //         'instructions' => '',
  //         'required' => 0,
  //         'conditional_logic' => 0,
  //         'wrapper' => array(
  //           'width' => '',
  //           'class' => '',
  //           'id' => '',
  //         ),
  //         'collapsed' => '',
  //         'min' => 0,
  //         'max' => 0,
  //         'layout' => 'block',
  //         'button_label' => 'Add Template',
  //         'sub_fields' => array(
  //           array(
  //             'key' => 'field_5c9258b15a2a1',
  //             'label' => 'Post Type',
  //             'name' => 'acfab_post_type',
  //             'type' => 'select',
  //             'instructions' => '',
  //             'required' => 1,
  //             'conditional_logic' => 0,
  //             'wrapper' => array(
  //               'width' => '50',
  //               'class' => '',
  //               'id' => '',
  //             ),
  //             'choices' => array(
  //               'post' => 'Posts (post)',
  //               'page' => 'Pages (page)',
  //             ),
  //             'default_value' => array(
  //             ),
  //             'allow_null' => 0,
  //             'multiple' => 0,
  //             'ui' => 0,
  //             'return_format' => 'value',
  //             'ajax' => 0,
  //             'placeholder' => '',
  //           ),
  //           array(
  //             'key' => 'field_5c925b7daeb44',
  //             'label' => 'Template Lock',
  //             'name' => 'acfab_template_lock',
  //             'type' => 'button_group',
  //             'instructions' => '',
  //             'required' => 0,
  //             'conditional_logic' => 0,
  //             'wrapper' => array(
  //               'width' => '50',
  //               'class' => '',
  //               'id' => '',
  //             ),
  //             'choices' => array(
  //               'false' => 'None',
  //               'all' => 'All',
  //               'insert' => 'Insert',
  //             ),
  //             'allow_null' => 0,
  //             'default_value' => 'false',
  //             'layout' => 'horizontal',
  //             'return_format' => 'value',
  //           ),
  //           array(
  //             'key' => 'field_5c9258e45a2a3',
  //             'label' => 'Template',
  //             'name' => 'acfab_post_template',
  //             'type' => 'repeater',
  //             'instructions' => '',
  //             'required' => 1,
  //             'conditional_logic' => 0,
  //             'wrapper' => array(
  //               'width' => '',
  //               'class' => '',
  //               'id' => '',
  //             ),
  //             'collapsed' => '',
  //             'min' => 1,
  //             'max' => 0,
  //             'layout' => 'table',
  //             'button_label' => 'Add Block',
  //             'sub_fields' => array(
  //               array(
  //                 'key' => 'field_5c9258f85a2a4',
  //                 'label' => 'Block',
  //                 'name' => 'acfab_block',
  //                 'type' => 'select',
  //                 'instructions' => '',
  //                 'required' => 1,
  //                 'conditional_logic' => 0,
  //                 'wrapper' => array(
  //                   'width' => '',
  //                   'class' => '',
  //                   'id' => '',
  //                 ),
  //                 'choices' => array(
  //                   'acfab/region' => 'Region',
  //                 ),
  //                 'default_value' => array(
  //                 ),
  //                 'allow_null' => 0,
  //                 'multiple' => 0,
  //                 'ui' => 0,
  //                 'return_format' => 'value',
  //                 'ajax' => 0,
  //                 'placeholder' => '',
  //               ),
  //             ),
  //           ),
  //         ),
  //       ),
  //     ),
  //     'location' => array(
  //       array(
  //         array(
  //           'param' => 'options_page',
  //           'operator' => '==',
  //           'value' => 'acfab-templates',
  //         ),
  //       ),
  //     ),
  //     'menu_order' => 0,
  //     'position' => 'normal',
  //     'style' => 'seamless',
  //     'label_placement' => 'top',
  //     'instruction_placement' => 'label',
  //     'hide_on_screen' => '',
  //     'active' => true,
  //     'description' => '',
  //   ) );
  // }


  // // Export post templates
  // function export_templates() {
  //   $screen = get_current_screen();

  //   if ( strpos( $screen->id, 'acfab-templates' ) == true ) {
  //     $path = acf_get_setting('save_json');
  //     $path = untrailingslashit( $path );

  //     if ( !is_writable( $path ) ) {
  //       return false;
  //     }

  //     $template_settings = get_field( 'acfab_templates', 'option' );

  //     $file = 'acfab_templates.json';
  //     $data = array(
  //       'modified' => current_time( 'timestamp'),
  //       'templates' => $template_settings,
  //     );

  //     $f = fopen( "{$path}/{$file}", 'w' );
  //     fwrite( $f, acf_json_encode( $data ) );
  //     fclose( $f );
  //   }
  // }


  // // Populate post types
  // public function populate_post_type_select( $field ) {
  //   $types = array();

  //   $post_types = get_post_types( array(
  //     // 'public' => true
  //   ), 'objects' );

  //   foreach ( $post_types as $post_type ) {
  //     $types[ $post_type->name ] = $post_type->label . ' (' . $post_type->name . ')';
  //   }

  //   unset( $types['wp_block'] ); //
  //   unset( $types['attachment'] );
  //   unset( $types['revision'] );
  //   unset( $types['nav_menu_item'] );
  //   unset( $types['custom_css'] );
  //   unset( $types['customize_changeset'] );
  //   unset( $types['oembed_cache'] );
  //   unset( $types['user_request'] );
  //   unset( $types['np-redirect'] );
  //   unset( $types['acf-field-group'] );
  //   unset( $types['acf-field'] );

  //   $field['choices'] = $types;

  //   return $field;
  // }


  // // Populate blocks
  // public function populate_block_select( $field ) {
  //   $field['choices'] = array();

  //   // $auto_blocks = ACF_Auto_Blocks::get_instance()->get_auto_blocks();
  //   $auto_blocks = ACF_Auto_Blocks::get_auto_blocks();

  //   $field['choices'][ 'acfab/region' ] = 'Region';

  //   foreach ( $auto_blocks as $auto_block ) {
  //     $field['choices'][ 'acf/' . $auto_block['auto_block_key'] ] = $auto_block['title'];
  //   }

  //   return $field;
  // }


  // Modify field group columns
  public function field_group_columns( $columns ) {
    $keys = array_keys( $columns );
    $last_key = array_pop( $keys );
    $last_val = array_pop( $columns );

    // $columns['acf-fg-ab'] = '<i class="acf-icon -dot-3 small acf-js-tooltip" title="' . __( 'Auto Block', 'acfab' ) . '"></i>';
    $columns['acf-fg-ab'] = __( 'Auto Block', 'acfab' );
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
      // echo '<i class="acf-icon -location auto_block small acf-js-tooltip" title="' . __( 'Auto Block', 'acfab' ) . '"></i>';
      echo '<i class="acf-icon -brick auto_block" title="' . __( 'Auto Block', 'acfab' ) . '"></i>';
      // echo '<span class="dashicons dashicons-block-default"></span>';
    }
  }


  // Draw field group settings
  public function field_group_settings( $field_group ) {
    $options = ACF_Auto_Blocks::parse_options_v1( $field_group );

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
      'label' => __( 'Auto Block (V1)', 'acfab' ),
      'instructions' => __( '<b>Important: This option is being deprecated in favor of block.json (V2).</b> <a href="#">Learn more about ACF Blocks V2</a> <br> Auto register this field group as a Gutenberg block. Don\'t forget to create a new template partial.', 'acfab' ),
      'type' => 'true_false',
      'name' => 'auto_block',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block'],
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Block Key', 'acfab' ),
      'instructions' => __( 'Identifier for block. Also used as template partial file name.', 'acfab' ),
      'type' => 'text',
      'name' => 'auto_block_key',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_key'],
      // 'required' => true,
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
      'label' => __( 'Block Keywords', 'acfab' ),
      'instructions' => '',
      'type' => 'text',
      'name' => 'auto_block_keywords',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_keywords'],
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
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Reusable Block', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_reusable',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_reusable'],
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Mode Switching', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_mode',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_mode'],
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Default Mode', 'acfab' ),
      'instructions' => '',
      'type' => 'button_group',
      'name' => 'auto_block_mode_default',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_mode_default'],
      // 'toggle' => true,
      'choices' => array(
        'auto' => 'Auto',
        'preview' => 'Preview',
        'edit' => 'Edit',
      ),
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Screenshot', 'acfab' ),
      'instructions' => __( 'Use a static image as the block preview.', 'acfab' ),
      'type' => 'image',
      'name' => 'auto_block_screenshot',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_screenshot'],
      ''
      // 'toggle' => true,
      // 'choices' => array(
      //   'auto' => 'Auto',
      //   'preview' => 'Preview',
      //   'edit' => 'Edit',
      // ),
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

    acf_render_field_wrap( array(
      'label' => __( 'Text Alignment', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_text_align',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_text_align'],
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Default Text Alignment', 'acfab' ),
      'instructions' => '',
      'type' => 'button_group',
      'name' => 'auto_block_text_align_default',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_text_align_default'],
      'choices' => array(
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
      ),
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Content Alignment', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_content_align',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_content_align'],
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Content Alignment Type', 'acfab' ),
      'instructions' => '',
      'type' => 'button_group',
      'name' => 'auto_block_content_align_type',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_content_align_type'],
      'choices' => array(
        '1' => 'Vertical',
        'matrix' => 'Matrix',
      ),
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Default Content Alignment', 'acfab' ),
      'instructions' => '',
      'type' => 'button_group',
      'name' => 'auto_block_content_align_default',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_content_align_default'],
      'layout' => 'vertical',
      'choices' => array(
        'top' => 'Top',
        'center' => 'Center',
        'bottom' => 'Bottom',
      ),
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'Default Content Alignment', 'acfab' ),
      'instructions' => '',
      'type' => 'button_group',
      'name' => 'auto_block_content_align_default_matrix',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_content_align_default_matrix'],
      'choices' => array(
        'top left' => 'Top Left',
        'top center' => 'Top Center',
        'top right' => 'Top Right',
        'center left' => 'Center Left',
        'center center' => 'Center Center',
        'center right' => 'Center Right',
        'bottom left' => 'Bottom Left',
        'bottom center' => 'Bottom Center',
        'bottom right' => 'Bottom Right',
      ),
    ) );

    acf_render_field_wrap( array(
      'label' => __( 'InnerBlocks (JSX)', 'acfab' ),
      'instructions' => '',
      'type' => 'true_false',
      'name' => 'auto_block_jsx',
      'prefix' => 'acf_field_group',
      'value' => $options['auto_block_jsx'],
      'ui' => true,
      'ui_on_text' => 'On',
      'ui_off_text' => 'Off',
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

      $field_group = ACF_Auto_Blocks::parse_options_v1( $field_group );

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


  // Draw field settings
  // TODO: make this work with V2 (right now it hides if auto block check box is disbaled)
  function acf_render_field_settings( $field ) {
    $types = array(
      // 'text',
      // 'textarea',
      // 'link',
      // 'enhanced_link', //
      // 'number',
      // 'range',
      // 'url',
      // 'password',
      //
      'message',
      'accordian',
      'tab',
      'group',
      // 'repeater',
      'flexible_content',
      'clone',
    );

    if ( in_array( $field['type'], $types ) ) {
      return;
    }

    acf_render_field_setting( $field, array(
      'label' => 'Save to meta',
      'instructions' => 'Auto Block only. Must be unique. Leave blank to disable.',
      'name' => 'auto_block_save_to_meta',
      'type' => 'text',
    ), true );
  }


  // Setup admin CSS
  public function admin_footer() {
    ?>
    <style>
      /* Field Group Settings */
      .acf-admin-field-groups .wp-list-table .column-acf-fg-ab {
        width: 10%;
      }
      .acf-icon.auto_block {
        background-color: #5EE8BF;
        color: #fff;
      }
      .acf-icon.auto_block:before {
        content: "\f12b";
      }
      [data-name="auto_block_icon"] .acf-radio-list li {
        float: left;
      }
      [data-name="auto_block_icon"] .acf-radio-list .icon {
        height: 35px;
        width: 35px;
        padding: 5px;
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
      [data-name="auto_block_icon"] .acf-radio-list input:checked + .icon svg path:not([fill="none"]),
      [data-name="auto_block_icon"] .acf-radio-list input:checked + .icon svg circle:not([fill="none"]) {
        fill: #ffffff;
      }
      [data-name="auto_block_icon"] .acf-radio-list input {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
      }
      /* Block Editor */
      .editor-styles-wrapper [data-type="acfab/region"] {
        max-width: none;
      }
      @media screen and (min-width: 600px) {
        .editor-styles-wrapper [data-type="acfab/region"] {
          margin-right: 0;
          margin-left: 0;
        }
      }

      [data-name="auto_block_content_align_default_matrix"] .acf-button-group {
        display: flex;
        flex-wrap: wrap;
        max-width: 500px;
      }
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label {
        flex: 0 0 29%;
        box-sizing: border-box;
        overflow: hidden;
        text-overflow: elipsis;
        white-space: nowrap;
      }
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(1) {
        border-radius: 2px 0 0 0;
      }
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(3) {
        border-radius: 0 2px 0 0;
      }
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(7) {
        border-radius: 0 0 0 2px;
      }
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(9) {
        border-radius: 0 0 2px 0;
      }

      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(4),
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(5),
      [data-name="auto_block_content_align_default_matrix"] .acf-button-group label:nth-child(6) {
        margin-top: -1px;
        margin-bottom: -1px;
      }

      .acfab_preview_image {
        display: block;
        max-width: 1000px;
        width: 100%;
        object-fit: scale-down;
        object-position: center;
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

          $('[name="acf_field_group[auto_block_icon]"]').each(function() {
            $(this).next('.icon').html( $(this).val() );
          });

          $("#acf_field_group-auto_block").on("change", toggleSettings);

          // $("#acf_field_group-auto_block_mode").on("change", checkSettings);
          $("#acf_field_group-auto_block_text_align").on("change", checkSettings);
          $("#acf_field_group-auto_block_content_align").on("change", checkSettings);
          $('[name="acf_field_group[auto_block_content_align_type]"]').on("change", checkSettings);

          toggleSettings();
          checkSettings();

          $('[data-name="auto_block_save_to_meta"] .acf-input').append('<p>Save this field\'s value as post meta with the defined key. Note: Using multiple instances of this block on the same post will overwrite each other\'s values. Use sparingly and purposefully.</p>')

          function toggleSettings() {
            // ACFAB Settings
            if ($("#acf_field_group-auto_block").is(":checked")) {
              $('[data-name*="auto_block_"]').show();
            } else {
              $('[data-name*="auto_block_"]').hide();
            }
          }

          function checkSettings() {
            // // Mode
            // if ($("#acf_field_group-auto_block_mode").is(":checked")) {
            //   $('[data-name="auto_block_mode_default"]').show();
            // } else {
            //   $('[data-name="auto_block_mode_default"]').hide();
            // }

            // Text Alignment
            if ($("#acf_field_group-auto_block_text_align").is(":checked")) {
              $('[data-name="auto_block_text_align_default"]').show();
            } else {
              $('[data-name="auto_block_text_align_default"]').hide();
            }

            // Content Alignment
            if ($("#acf_field_group-auto_block_content_align").is(":checked")) {
              $('[data-name="auto_block_content_align_type"]').show();

              // Content Alignment Type
              if ( $('[name="acf_field_group[auto_block_content_align_type]"]').filter(":checked").val() == '1' ) {
                $('[data-name="auto_block_content_align_default"]').show();
                $('[data-name="auto_block_content_align_default_matrix"]').hide();
              } else {
                $('[data-name="auto_block_content_align_default"]').hide();
                $('[data-name="auto_block_content_align_default_matrix"]').show();
              }
            } else {
              $('[data-name="auto_block_content_align_type"]').hide();
              $('[data-name*="auto_block_content_align_default"]').hide();
            }

          }

        });
      })(jQuery);
    </script>
    <?php
  }

}


// Instance

ACF_Auto_Blocks_Settings::get_instance();
