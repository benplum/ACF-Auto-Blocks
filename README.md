# ACF Auto Blocks

Auto-register ACF field groups as blocks in the new editor (Gutenberg).

Requires ACF Pro 5.8-beta3 or later. [Read more about ACF Blocks for Gutenberg](https://www.advancedcustomfields.com/blog/acf-5-8-introducing-acf-blocks-for-gutenberg/).

## Rendering Blocks

### Templates

Auto Blocks will use the block key as the block template file name. For example, a block with a key of `flexible-callout` will require a template file named `flexible-callout.php`. Auto Blocks will look for block templates in an `acf-blocks` directory in the root of your theme. You can set a different path using the `acf/auto_blocks/directory` filter.

```php
function my_acf_blocks_directory( $path ) {
  $path = get_stylesheet_directory() . '/templates/my-blocks';

  return $path;
}
add_filter( 'acf/auto_blocks/directory', 'my_acf_blocks_directory' );
```

### Data

Auto Blocks will localize two arrays for use in the template file: `$block`, containing the block settings, and `$data`, containing the field values.

```php
<div id="<?php echo $block['id']; ?>">
  <h2><?php echo $data['title']; ?></h2>
</div>
```

## Converting Flexible Layouts

Auto Blocks will also assist in converting flexible layouts 'page builders' into block-ready field groups. Look for the new 'Convert to Block' action in the layout settings when editing a flexible layout.
