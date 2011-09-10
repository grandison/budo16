<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: FormTinyMce.php 7281 2010-09-03 03:46:33Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_View_Helper_FormTinyMce extends Zend_View_Helper_FormTextarea
{
    protected $_tinyMce;

    public function formTinyMce($name, $value = null, $attribs = null)
    {
        // Disable for mobile browsers
        $ua = $_SERVER['HTTP_USER_AGENT'];
        if( preg_match('/Mobile/i', $ua) || preg_match('/Opera Mini/i', $ua) || preg_match('/NokiaN/i', $ua) ) {
          return $this->formTextarea($name, $value, $attribs);
        }

        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        $disabled = '';
        if ($disable) {
            $disabled = ' disabled="disabled"';
        }

        if( Zend_Registry::isRegistered('Locale') ) {
          $locale = Zend_Registry::get('Locale');
          if( method_exists($locale, '__toString') ) {
            $locale = $locale->__toString();
          } else {
            $locale = (string) $locale;
          }
          $localeData = Zend_Locale_Data::getList($locale, 'layout');
          $directionality = ( @$localeData['characters'] == 'right-to-left' ? 'rtl' : 'ltr' );

          $this->view->tinyMce()->language = $locale;
          $this->view->tinyMce()->directionality = $directionality;
        }

        if (empty($attribs['rows'])) {
            $attribs['rows'] = (int) $this->rows;
        }
        if (empty($attribs['cols'])) {
            $attribs['cols'] = (int) $this->cols;
        }
        if (isset($attribs['editorOptions'])) {
            if ($attribs['editorOptions'] instanceof Zend_Config) {
                $attribs['editorOptions'] = $attribs['editorOptions']->toArray();
            }
            $this->view->tinyMce()->setOptions($attribs['editorOptions']);
            unset($attribs['editorOptions']);
        }
        $this->view->tinyMce()->render();
        $xhtml = '<textarea rows=24, cols=80, style="width:553px;" name="' . $this->view->escape($name) . '"'
                . ' id="' . $this->view->escape($id) . '"'
                . $disabled
                . $this->_htmlAttribs($attribs) . '>'
                . $this->view->escape($value) . '</textarea>';

        return $xhtml;
    }
}
