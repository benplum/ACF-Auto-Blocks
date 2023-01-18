# ACF Auto Blocks

Auto-register ACF field groups as blocks in the block editor.

## Creating Blocks

ACF Auto Blocks includes a cli script for creating new blocks:

```
wp autoblocks make_block foo-bar --name="Foo Bar" --icon="editor-code"
```

This will create a new directory in your theme named `/acf-blocks/foo` containing a `block.json`, `group_block_foo.json`, and `template.php` file. You can generate additional files using the [CLI Hooks](#markdown-cli-hooks).

Once the block has been created, head to your admin and import the new field group to start adding fields to your block. You can edit the `block.json` based on the [block metadata spec](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/).

## Hooks

### `acf/auto_blocks/directory`

Filters block directory. Must return path relative to site install. Defaults to `{path-to-theme}/acf-blocks`.

```
function my_autoblocks_directory( $dir ) {
    // ...
    return $dir;
}
add_filter( 'acf/auto_blocks/directory', 'my_autoblocks_directory', 10, 2 );
```

### `acf/auto_blocks/v2/parse_block_options`

Filters block options used for `register_block_type`.

```
function my_autoblocks_parse_block_options_v2( $options ) {
    // ...
    return $options;
}
add_filter( 'acf/auto_blocks/v2/parse_block_options', 'my_autoblocks_parse_block_options_v2', 10, 1 );
```

### `acf/auto_blocks/v1/parse_block_options`

Filters block options used for `acf_register_block`. (Deprecated)

```
function my_autoblocks_parse_block_options_v1( $options ) {
    // ...
    return $options;
}
add_filter( 'acf/auto_blocks/v2/parse_block_options', 'my_autoblocks_parse_block_options_v1', 10, 1 );
```

### `acf/auto_blocks/block_data`

Filters block data.

```
function my_autoblocks_block_data( $data, $block ) {
    // ...
    return $data;
}
add_filter( 'acf/auto_blocks/block_data', 'my_autoblocks_block_data', 10, 2 );
```

### `acf/auto_blocks/block_settings`

Filters block settings.

```
function my_autoblocks_block_settings( $settings ) {
    // ...
    return $settings;
}
add_filter( 'acf/auto_blocks/block_settings', 'my_autoblocks_block_settings', 10, 2 );
```

### `acf/auto_blocks/block_preview`

Filters admin block preview.

```
function my_autoblocks_block_preview( $html, $block ) {
    // ...
    return $html;
}
add_filter( 'acf/auto_blocks/block_preview', 'my_autoblocks_block_preview', 10, 2 );
```

### `acf/auto_blocks/render_block`

Filters block render.

```
function my_autoblocks_render_block( $html, $block ) {
    // ...
    return $html;
}
add_filter( 'acf/auto_blocks/render_block', 'my_autoblocks_render_block', 10, 2 );
```

## CLI Hooks

### `acf/auto_blocks/cli/build_json`

Filters block json file.

```
function my_autoblocks_cli_build_json( $json, $values = [] ) {
    // ...
    return $json;
}
add_filter( 'acf/auto_blocks/cli/build_json', 'my_autoblocks_cli_build_json', 10, 2 );
```

### `acf/auto_blocks/cli/build_field_group`

Filters ACF field group json file.

```
function my_autoblocks_cli_build_field_group( $json, $values = [] ) {
    // ...
    return $json;
}
add_filter( 'acf/auto_blocks/cli/build_field_group', 'my_autoblocks_cli_build_field_group', 10, 2 );
```

### `acf/auto_blocks/cli/build_template`

Filters template php file.

```
function my_autoblocks_cli_build_template( $php, $values = [] ) {
    // ...
    return $php;
}
add_filter( 'acf/auto_blocks/cli/build_template', 'my_autoblocks_cli_build_template', 10, 2 );
```

### `acf/auto_blocks/cli/make_block`

Run after base block files have been created.

```
function my_autoblocks_cli_make_block( $args = [], $assoc_args = [], $values = [] ) {
    // ...
}
add_action( 'acf/auto_blocks/cli/build_template', 'my_autoblocks_cli_make_block', 10, 3 );
```

**Note:** `$values` will contain an associative array that can be used to find replace block specific values.

```
$values = [
  '{key}' => $key_kebab,
  '{key_kebab}' => $key_kebab,
  '{key_snake}' => $key_snake,
  '{dir}' => $dir,
  '{name}' => $name,
  '{icon}' => $icon,
];
```
