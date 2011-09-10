<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 8858 2011-04-12 03:06:20Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Widget_MenuFooterController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('core_footer');
    
    // Languages
    $translate    = Zend_Registry::get('Zend_Translate');
    $languageList = $translate->getList();

    //$currentLocale = Zend_Registry::get('Locale')->__toString();

    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if( !in_array($defaultLanguage, $languageList) ) {
      if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = null;
      }
    }

    // Prepare language name list
    $languageNameList  = array();
    $languageDataList  = Zend_Locale_Data::getList(null, 'language');
    $territoryDataList = Zend_Locale_Data::getList(null, 'territory');

    foreach( $languageList as $localeCode ) {
      $languageNameList[$localeCode] = Zend_Locale::getTranslation($localeCode, 'language', $localeCode);
      if (empty($languageNameList[$localeCode])) {
        if( false !== strpos($localeCode, '_') ) {
          list($locale, $territory) = explode('_', $localeCode);
        } else {
          $locale = $localeCode;
          $territory = null;
        }
        if( isset($territoryDataList[$territory]) && isset($languageDataList[$locale]) ) {
          $languageNameList[$localeCode] = $territoryDataList[$territory] . ' ' . $languageDataList[$locale];
        } else if( isset($territoryDataList[$territory]) ) {
          $languageNameList[$localeCode] = $territoryDataList[$territory];
        } else if( isset($languageDataList[$locale]) ) {
          $languageNameList[$localeCode] = $languageDataList[$locale];
        } else {
          continue;
        }
      }
    }
    $languageNameList = array_merge(array(
      $defaultLanguage => $defaultLanguage
    ), $languageNameList);
    $this->view->languageNameList = $languageNameList;
  }

  public function getCacheKey()
  {
    //return true;
  }

  public function setLanguage()
  {

  }
}