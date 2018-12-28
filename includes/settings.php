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
	    'type' => 'text',
	    'name' => 'auto_block_icon',
	    'prefix' => 'acf_field_group',
	    'value' => $options['auto_block_icon'],
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
