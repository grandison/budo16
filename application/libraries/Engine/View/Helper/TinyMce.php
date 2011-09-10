<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: TinyMce.php 8702 2011-03-24 23:21:23Z char $
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_TinyMce extends Zend_View_Helper_Abstract
{
  protected $_enabled = false;
  protected $_defaultScript = 'externals/tinymce/tiny_mce.js';
  protected $_html = true;
  protected $_bbcode = false;
  protected $_supported = array(
    'mode' => array(
      'textareas', 'specific_textareas', 'exact', 'none'
    ),
    'theme' => array(
      'simple', 'advanced'
    ),
    'format' => array(
      'html', 'xhtml'
    ),
    'languages' => array(
      'en', 'ar', 'ca', 'el', 'fr', 'hy', 'ka', 'ml', 'pl', 'si', 'te', 'vi',
      'az', 'ch', 'gl', 'ia', 'kl', 'mn', 'ps', 'sk', 'th', 'zh', 'be', 'cs',
      'es', 'gu', 'id', 'ko', 'ms', 'pt', 'sl', 'tr', 'zu', 'bg', 'cy', 'et',
      'he', 'ii', 'lb', 'nb', 'ro', 'sq', 'tt', 'bn', 'da', 'eu', 'hi', 'is',
      'lt', 'nl', 'ru', 'sr', 'tw', 'br', 'de', 'fa', 'hr', 'it', 'lv', 'nn',
      'sc', 'sv', 'uk', 'bs', 'dv', 'fi', 'hu', 'ja', 'mk', 'no', 'se', 'ta',
      'ur',
    ),
    'directionality' => array(
      'rtl', 'ltr',
    ),
    'plugins' => array(
      'style', 'layer', 'table', 'save',
      'advhr', 'advimage', 'advlink', 'emotions',
      'iespell', 'insertdatetime', 'preview', 'media',
      'searchreplace', 'print', 'contextmenu', 'paste',
      'directionality', 'fullscreen', 'noneditable', 'visualchars',
      'nonbreakfing', 'xhtmlxtras', 'imagemanager', 'filemanager', 'template'
    ),
  );
  protected $_config = array(
    'mode' => 'textareas',
    'plugins' => array(
      'emotions', 'table', 'fullscreen', 'media', 'preview', 'paste',
    ),
    'theme' => 'advanced',
    'theme_advanced_buttons1' => array(
      'undo', 'redo', 'cleanup', 'removeformat', 'pasteword', '|', 'code',
      'media', 'image', 'fullscreen', 'preview'
    ),
    'theme_advanced_buttons2' => '',
    'theme_advanced_buttons3' => '',
    'theme_advanced_toolbar_align' => 'left',
    'theme_advanced_toolbar_location' => 'top',
    'element_format' => 'html',
    'height' => '225px',
    'convert_urls' => false,
    'media_strict' => false,
  );
  protected $_scriptPath;
  protected $_scriptFile;
  protected $_useCompressor = false;

  public function __set($name, $value)
  {
    $method = 'set' . $name;
    if( !method_exists($this, $method) ) {
      throw new Engine_Exception('Invalid tinyMce property');
    }
    $this->$method($value);
  }

  public function __get($name)
  {
    $method = 'get' . $name;
    if( !method_exists($this, $method) ) {
      throw new Engine_Exception('Invalid tinyMce property');
    }
    return $this->$method();
  }

  public function setOptions(array $options)
  {
    $methods = get_class_methods($this);
    foreach( $options as $key => $value ) {
      $method = 'set' . ucfirst($key);
      if( in_array($method, $methods) ) {
        $this->$method($value);
      } else {
        $this->_config[$key] = $value;
      }
    }
    return $this;
  }

  public function TinyMce()
  {
    return $this;
  }

  public function setBbcode($value)
  {
    $this->_bbcode = (bool) $value;
    $this->updateSettings();
  }

  public function setHtml($value)
  {
    $this->_html = (bool) $value;
    $this->updateSettings();
  }

  public function setLanguage($language)
  {
    if( !in_array($language, $this->_supported['languages']) ) {
      list($language) = explode('_', $language);
      if( !in_array($language, $this->_supported['languages']) ) {
        return $this;
      }
    }

    $this->_config['language'] = $language;

    return $this;
  }

  public function setDirectionality($directionality)
  {
    if( in_array($directionality, $this->_supported['directionality']) ) {
      $this->_config['directionality'] = $directionality;
    }

    return $this;
  }

  public function updateSettings()
  {
    if( $this->_html ) { // HTML
      $this->_config['theme_advanced_buttons2'] = array(
        'fontselect', 'fontsizeselect', 'bold', 'italic', 'underline',
        'strikethrough', 'forecolor', 'backcolor', '|', 'justifyleft',
        'justifycenter', 'justifyright', 'justifyfull', '|', 'bullist',
        'numlist', '|', 'outdent', 'indent', 'blockquote',
      );
    } else if( $this->_bbcode ) { // BBCODE
      $this->_config['plugins'][] = 'bbcode';
      //        $this->_config['theme_advanced_styles'] = "Code=codeStyle;Quote=quoteStyle";
      $this->_config['content_css'] = "bbcode.css";
      $this->_config['entity_encoding'] = "raw";
      $this->_config['add_unload_trigger'] = 0;
      $this->_config['remove_linebreaks'] = false;
      $this->_config['theme_advanced_buttons1'] = array(
        'bold', 'italic', 'underline', 'undo', 'redo', 'link', 'unlink',
        'image', 'forecolor', 'removeformat', 'cleanup', 'code',
      );
    } else { // Nothing
      $this->_config['theme_advanced_buttons1'] = '';
    }
  }

  public function setScriptPath($path)
  {
    $this->_scriptPath = rtrim($path, '/');
    return $this;
  }

  public function setScriptFile($file)
  {
    $this->_scriptFile = (string) $file;
  }

  public function useCompressor($switch)
  {
    $this->_useCompressor = (bool) $switch;
    return $this;
  }

  public function render()
  {
    if( false === $this->_enabled ) {
      $this->_renderScript();
      $this->_renderCompressor();
      $this->_renderEditor();
    }
    $this->_enabled = true;
  }

  protected function _renderScript()
  {
    if( null === $this->_scriptFile ) {
      $script = $this->view->baseUrl() . '/' . $this->_defaultScript;
    } else {
      if( null === $this->_scriptPath ) {
        $this->_scriptPath = $this->view->baseUrl();
      }
      $script = $this->_scriptPath . '/' . $this->_scriptFile;
    }

    $this->view->headScript()->appendFile($script);
    return $this;
  }

  protected function _renderCompressor()
  {
    if( false === $this->_useCompressor ) {
      return;
    }
    $script = 'tinyMCE_GZ.init({' . PHP_EOL
        . 'themes: "' . implode(',', $this->_supportedTheme) . '"' . PHP_EOL
        . 'plugins: "' . implode(',', $this->_supportedPlugins) . '"' . PHP_EOL
        . 'languages: "' . implode(',', $this->_supportedLanguages) . '"' . PHP_EOL
        . 'disk_cache: true' . PHP_EOL
        . 'debug: false' . PHP_EOL
        . '});';

    $this->view->headScript()->appendScript($script);
    return $this;
  }

  protected function _renderEditor()
  {
    $script = 'tinyMCE.init({' . PHP_EOL;

    $length = count($this->_config);
    $i = 0;
    foreach( $this->_config as $name => $value ) {
      if( is_array($value) ) {
        $value = implode(',', $value);
      }
      if( !is_bool($value) ) {
        $value = '"' . $value . '"';
      } else {
        $value = ( $value ? 'true' : 'false' );
      }
      $script .= $name . ': ' . $value . ($i == $length - 1 ? '' : ',') . PHP_EOL;
      $i++;
    }

    $script .= '});';

    $this->view->headScript()->appendScript($script);
    return $this;
  }

}

