(function() {
  var registerBlockType = wp.blocks.registerBlockType;
  var el = wp.element.createElement;
  var dispatch = wp.data.dispatch;
  var InnerBlocks = wp.editor.InnerBlocks;

  registerBlockType('acfab/region', {

    title: 'Region',
    icon: el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
      el('path', { d: 'M21 18H2v2h19v-2zm-2-8v4H4v-4h15m1-2H3c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h17c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm1-4H2v2h19V4z' } )
    ),
    category: 'common',
    class: 'block_region',
    supports: {
      inserter: false
    },

    edit: function() {
      dispatch( 'core/block-editor' ).setTemplateValidity( true );

      return el('div', { style: { } },
        el(InnerBlocks, {
					templateLock: false
        })
      );
    },

    save: function() {
      return el('div', { style: { } },
        el(InnerBlocks.Content, {})
      );
    }

  });

})();
