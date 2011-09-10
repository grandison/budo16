<?php return array(
  'package' => array(
    'type' => 'external',
    'name' => 'tinymce',
    'version' => '4.1.5',
    'revision' => '$Revision: 8942 $',
    'path' => 'externals/tinymce',
    'repository' => 'socialengine.net',
    'title' => 'Tinymce',
    'author' => 'Webligo Developments',
    'changeLog' => array(
      '4.1.5' => array(
        '*' => 'Upgraded from version 3.3.8 to 3.4.2 to fix issues with IE9, see tinymce\'s site for details',
      ),
      '4.1.4' => array(
        'manifest.php' => 'Incremented version',
        'plugins/bbcode/editor_plugin.js' => 'Fixed linebreaks with BBCode',
        'plugins/bbcode/editor_plugin_src.js' => 'Fixed linebreaks with BBCode',
        'themes/advanced/image.htm' => 'Support for uploading image',
        'themes/advanced/upload.htm' => 'Support for uploading image',
        'themes/advanced/js/image.js' => 'Support for uploading image',
        'themes/advanced/skins/default/content.css' => 'Fixed the way paragraphs are displayed'
      ),
      '4.0.2' => array(
        '*' => 'Added language packs',
      ),
      '4.0.1' => array(
        '*' => 'Upgraded from version 3.2.4.1 to 3.3.8; See tinymce\'s site for details',
      ),
    ),
    'directories' => array(
      'externals/tinymce',
    )
  )
) ?>