<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

class ACF_Auto_Block_Converter {

  protected static $instance;

  public static function get_instance() {
    if ( empty( self::$instance ) && ! ( self::$instance instanceof ACF_Auto_Block_Converter ) ) {
      self::$instance = new ACF_Auto_Block_Converter();
    }

    return self::$instance;
  }

  public function __construct() {
    add_action( 'acf/admin_footer', array( $this, 'admin_footer' ) );

    add_action( 'wp_ajax_acf_auto_block_converter', array( $this, 'convert_layout' ) );
  }


  // Setup admin JS
  public function admin_footer() {
    ?>
    <script>
      (function($) {
        $(function() {
          if (typeof acf == "undefined" || !acf) {
            return;
          }

          $(".acf-fl-actions")
            .append('<li><a href="#" class="convert_to_block"><?php echo __( 'Convert to Block', 'acfab' ); ?></a></li>')
            .on("click", function(e) {
              e.preventDefault();
              e.stopPropagation();

              var $target = $(e.currentTarget);
              var $layout = $target.parents(".acf-field-setting-fc_layout").eq(0);
              var $field = $target.parents(".acf-field-object-flexible-content").eq(0);

              var fieldKey = $field.data("key");
              var layoutKey = $layout.data("id");

              $.ajax({
                url: ajaxurl,
                type: "get",
                data: {
                  action: "acf_auto_block_converter",
                  field: fieldKey,
                  layout: layoutKey,
                },
                success: function(response, textStatus, jqXHR) {
                  if (response.indexOf('Error') > -1) {
                    alert(response);
                  } else {
                    alert("<?php echo __( 'Conversion Complete', 'acfab' ); ?>");
                  }

                  console.log(response);
                },
                error: function(jqXHR, textStatus, error) {
                  alert("<?php echo __( 'Conversion Error', 'acfab' ); ?>");

                  console.log(error);
                }
              });
            });
        });
      })(jQuery);
    </script>
    <?php
  }


  // Convert flexible layout to stand alone field group
  public function convert_layout() {
    $field_key = $_GET['field'];
    $layout_key = $_GET['layout'];

    if ( empty( $field_key ) || empty( $layout_key ) ) {
      echo 'Error: Missing data';
      die();
    }

    $group = null;
    $field = null;
    $layout = null;

    // Find field
    $field = get_field_object( $field_key );

    if ( empty( $field ) ) {
      echo 'Error: Field not found';
      die();
    }

    // Find Layout

    foreach ( $field['layouts'] as $l_key => $l ) {
      if ( $l_key == $layout_key ) {
        $layout = $l;
        break;
      }
    }

    if ( empty( $layout ) ) {
      echo 'Error: Layout not found';
      die();
    }

    $new_fields = $layout['sub_fields'];

    foreach ( $new_fields as &$f ) {
      unset( $f['parent'] );
      unset( $f['parent_layout'] );

      $f['key'] = 'field_' . uniqid();
    }

    $new_title = $layout['label'];
    $new_key = str_replace( '_', '-', sanitize_title( $layout['label'] ) );

    $new_group = array(
      'key' => 'group_' . uniqid(),
      'title' => $new_title,
      'name' => $new_title,
      'fields' => $new_fields,
      'location' => array(
        array(
          array(
            'param' => 'block',
            'operator' => '==',
            'value' => 'acf/' . $new_key,
          ),
        ),
      ),
      'active' => 1,
      'auto_block' => 'on',
      'auto_block_key' => $new_key,
      'auto_block_align' => array(),
      'auto_block_multiple' => 0,
      'auto_block_reusable' => 0,
    );

    $new_group = acf_update_field_group( $new_group );

    foreach ( $new_fields as &$f ) {
      $f['parent'] = $new_group['ID'];

      $f = acf_update_field( $f );
    }

    update_post_meta( $new_group['ID'], 'auto_block', 'on' );

    echo 'Done';
    die();
  }

}


// Instance

ACF_Auto_Block_Converter::get_instance();
