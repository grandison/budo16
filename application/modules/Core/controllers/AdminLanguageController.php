<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminLanguageController.php 8246 2011-01-18 04:59:04Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminLanguageController extends Core_Controller_Action_Admin
{
  protected $_languagePath;

  public function init()
  {
    $this->_languagePath = APPLICATION_PATH . '/application/languages';
  }

  public function indexAction()
  {
    $translate = Zend_Registry::get('Zend_Translate');

    // Prepare language list
    $this->view->languageList = $languageList = $translate->getList();

    // Prepare default langauge
    $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
    if( !in_array($defaultLanguage, $languageList) ) {
      if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
        $defaultLanguage = 'en';
      } else {
        $defaultLanguage = null;
      }
    }
    $this->view->defaultLanguage = $defaultLanguage;

    // Init default locale
    $localeObject = Zend_Registry::get('Locale');

    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $territories = Zend_Locale::getTranslationList('territory', $localeObject);

    $localeMultiOptions = array();
    foreach( /*array_keys(Zend_Locale::getLocaleList())*/ $languageList as $key ) {
      $languageName = null;
      if( !empty($languages[$key]) ) {
        $languageName = $languages[$key];
      } else {
        $tmpLocale = new Zend_Locale($key);
        $region = $tmpLocale->getRegion();
        $language = $tmpLocale->getLanguage();
        if( !empty($languages[$language]) && !empty($territories[$region]) ) {
          $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
        }
      }

      if( $languageName ) {
        $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
      } else {
        $localeMultiOptions[$key] = $this->view->translate('Unknown')  . ' [' . $key . ']';
      }
    }
    $localeMultiOptions = array_merge(array(
      $defaultLanguage => $defaultLanguage
    ), $localeMultiOptions);
    $this->view->languageNameList = $localeMultiOptions;

    /*

    // Prepare language name list
    $locale = Zend_Registry::get('Locale');

    $languageNameList  = array();
    $languageDataList  = Zend_Locale_Data::getList($locale, 'language');
    $territoryDataList = Zend_Locale_Data::getList($locale, 'territory');

    foreach( $languageList as $localeCode ) {
      $languageNameList[$localeCode] = Zend_Locale::getTranslation($localeCode, 'language', $locale);
      if (empty($languageNameList[$localeCode])) {
        $localeArray = explode('_', $localeCode);
        $locale      = array_shift($localeArray);
        $territory   = array_shift($localeArray);
        $languageNameList[$localeCode] = "{$territoryDataList[$territory]} {$languageDataList[$locale]}";
      }
    }
    $languageNameList = array_merge(array(
      $defaultLanguage => $defaultLanguage
    ), $languageNameList);
    $this->view->languageNameList = $languageNameList;
     * 
     */
  }

  public function createAction()
  {
    $translate = Zend_Registry::get('Zend_Translate');
    $form = $this->view->form = new Core_Form_Admin_Language_Create();
    $this->view->languageList = $languageList = $translate->getList();

    if ( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $localeCode   = $this->_getParam('language');

      $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
      if( !in_array($defaultLanguage, $languageList) ) {
        if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
          $defaultLanguage = 'en';
        } else {
          $defaultLanguage = null;
        }
      }

      if (!in_array($localeCode, $translate->getList())) {
        $filename = APPLICATION_PATH . "/application/languages/$localeCode/custom.csv";
        mkdir(dirname($filename));
        chmod(dirname($filename), 0777);
        touch($filename);
        chmod($filename, 0777);
        $csv = new Engine_Translate_Writer_Csv($filename);
        // each language pack must have at least one line written to it to be recognized
        $csv->setTranslation($localeCode, $localeCode);
        $csv->write();
      }
      
      $this->_helper->redirector->gotoRoute(array('action'=>'index'));
    }
  }

  public function uploadAction()
  {
    $form = $this->view->form = new Core_Form_Admin_Language_Upload();
    if ( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $localeCode = $this->_getParam('locale', null);
      if (!empty($localeCode)) {
        $filename = APPLICATION_PATH . "/application/languages/$localeCode/custom.csv";
        @mkdir(dirname($filename));
        @chmod(dirname($filename), 0777);

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filename)) {
          @chmod($filename, 0777);
          $this->_forward('success', 'utility', 'core', array(
            'parentRefresh'  => 2000,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Language file has been uploaded.')),
            'redirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('action'=>'index')),
          ));
        } else {
          $form->addError('Unable to import language file to this language.  Please CHMOD 777 the "/application/languages" directory an all directories and files inside it, then try again.');
        }
      } else {
        $form->addError('Unknown language');
      }

    }
  }

  public function defaultAction()
  {
    if( $this->getRequest()->isPost() ) {
      $locale    = $this->_getParam('locale', 'en');
      $translate = Zend_Registry::get('Zend_Translate');
      if (in_array($locale, $translate->getList()))
        Engine_Api::_()->getApi('settings', 'core')->core_locale_locale = $locale;
    }
  }

  public function deleteAction()
  {
    $form = $this->view->form = new Core_Form_Admin_Language_Delete();

    $languageList     = Zend_Locale_Data::getList('en', 'language');
    $territoryList    = Zend_Locale_Data::getList('en', 'territory');
    $localeCode       = $this->_getParam('locale', null);
    if (empty($localeCode))
      return;
    if (FALSE !== strpos($localeCode, '_')) {
      list($locale, $territory) = explode('_', $localeCode);
    } else {
      $locale    = $localeCode;
      $territory = null;
    }
    

    $languagePack = $languageList[$locale];
    if ($territory && !empty($territoryList[$territory]))
        $languagePack .= " ({$territoryList[$territory]})";
    $languagePack     .= "  [$localeCode]";

    $form->setDescription( sprintf($form->getDescription(), $languagePack) );


    if ( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
      $lang_dir = APPLICATION_PATH . '/application/languages/' . $localeCode;
      try {
        @Engine_Package_Utilities::fsRmdirRecursive($lang_dir, true);
        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => 2000,
            'parentRefresh'  => 2000,
            'format' => 'smoothbox',
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Language has been deleted.')),
        ));
      } catch (Exception $e) {
        $form->addError('Unable to delete language files.  Please log in through FTP and delete the directory "/application/languages/'.$localeCode.'/ and all of the files inside.');
      }
    }
  }

  public function editAction()
  {
    $this->view->locale = $locale = $this->_getParam('locale');
    $this->view->page = $page = $this->_getParam('page');
    $translate = Zend_Registry::get('Zend_Translate');

    try {
      if( !$locale || !Zend_Locale::findLocale($locale) ) {
        throw new Exception('missing locale ' . $locale);
      }
    } catch (Exception $e) {
      return $this->_helper->redirector->gotoRoute(array('action' => 'index', 'controller' => 'language'), 'admin_default', true);
    }

    // Process filter form
    $this->view->filterForm = $filterForm = new Core_Form_Admin_Language_Filter();
    $filterForm->isValid($this->_getAllParams());
    $filterValues = $filterForm->getValues();
    extract($filterValues); // search, show

    // Make query
    $filterValues = array_filter($filterValues);
    $this->view->values = $filterValues;
    $this->view->query = ( empty($filterValues) ? '' : '?' . http_build_query($filterValues) );
    
    // Assign basic locale info
    $this->view->localeObject = $localeObject = new Zend_Locale($locale);
    $this->view->locale = $locale = $localeObject->toString();

    // Get locale translation info
    $localeLanguage = $localeObject->getLanguage();
    $localeRegion   = $localeObject->getRegion();
    $this->view->localeLanguageTranslation = $localeLanguageTranslation
        = Zend_Locale::getTranslation($localeLanguage, 'language', Zend_Registry::get('Locale'));
    $this->view->localeRegionTranslation = $localeRegionTranslation
        = Zend_Locale::getTranslation($localeRegion, 'territory', Zend_Registry::get('Locale'));

    $translate = Zend_Registry::get('Zend_Translate');
    if( $localeLanguageTranslation && $localeRegionTranslation ) {
      $this->view->localeTranslation = $localeLanguageTranslation . ' '
          . sprintf($translate->translate('(%s)'), $localeRegionTranslation) . ' '
          . sprintf($translate->translate('[%s]'), $locale);
    } else if( $localeLanguageTranslation ) {
      $this->view->localeTranslation = $localeLanguageTranslation . ' '
          . sprintf($translate->translate('[%s]'), $locale);
    } else {
      $this->view->localeTranslation = sprintf($translate->translate('[%s]'), $locale);
    }
    
    // Query plural system for max and sample space
    $sample = array();
    $max    = 0;
    for ($i = 0; $i <= 1000; $i++) {
      $form = Zend_Translate_Plural::getPlural($i, $locale);
      $max  = max($max, $form);
      if (@count($sample[$form]) < 3) {
        $sample[$form][] = $i;
      }
    }
    $this->view->pluralFormCount  = ( $max + 1 );
    $this->view->pluralFormSample = $sample;

    // Get initial and default values
    $baseMessages = $translate->getMessages('en');
    if( $translate->isAvailable($locale) ) {
      $currentMessages = $translate->getMessages($locale);
    } else {
      $currentMessages = array(); // @todo this should redirect or smth
    }

    // Get phrases that are not in the english pack?
    if( !empty($currentMessages) && $locale != 'en' ) {
      $missingBasePhrases = array_diff_key($currentMessages, $baseMessages);
      $missingBasePhrases = array_combine(array_keys($missingBasePhrases), array_keys($missingBasePhrases));
      $baseMessages = array_merge($baseMessages, $missingBasePhrases);
    }
    
    // Build the fancy array
    $resultantMessages = array();
    $missing = 0;
    $index   = 0;
    foreach( $baseMessages as $key => $value ) {
      // Build
      $composite = array(
        'uid' => ++$index,
        'key' => $key,
        'original' => $value,
        'plural' => (bool) is_array($value),
      );

      // filters, plurals, and missing, oh my.
      if( isset($currentMessages[$key]) ) {
        if( 'missing' == $show ) {
          continue;
        }
        if( is_array($value) && !is_array($currentMessages[$key]) ) {
          $composite['current'] = array($currentMessages[$key]);
        } else if( !is_array($value) && is_array($currentMessages[$key]) ) {
          $composite['current'] = current($currentMessages[$key]);
        } else {
          $composite['current'] = $currentMessages[$key];
        }
      } else {
        if( 'translated' == $show ) {
          continue;
        }
        if( is_array($value) ) {
          $composite['current'] = array();
        } else {
          $composite['current'] = '';
        }
        $missing++;
      }

      // Do search
      if( $search && !$this->_searchArrayRecursive($search, $composite) ) {
        continue;
      }
      // Add
      $resultantMessages[] = $composite;
    }

    // Build the paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($resultantMessages);
    $paginator->setItemCountPerPage(50);
    $paginator->setCurrentPageNumber($page);


    // Process form POST
    if( $this->getRequest()->isPost() ) {
      $keys   = $this->_getParam('keys');
      $values = $this->_getParam('values');

      // Try to combine the values and keys arrays
      $combined = array();
      foreach( $values as $index => $value ) {
        if( is_string($value) ) {
          if( empty($value) ) continue;
          $key = $keys[$index];
          $combined[$key] = $value;
        } else if( is_array($value) ) {
          if( empty($value) || array_filter($value) === array() ) continue;
          $key = $keys[$index][0];
          $combined[$key] = $value;
        }
      }

      // Try to write to a file
      $targetFile = APPLICATION_PATH . '/application/languages/'.$locale.'/custom.csv';
      if( !file_exists($targetFile) ) {
        touch($targetFile);
        chmod($targetFile, 0777);
      }

      $writer = new Engine_Translate_Writer_Csv($targetFile);
      $writer->setTranslations($combined);
      $writer->write();

      // flush cached language vars
      @Zend_Registry::get('Zend_Cache')->clean();

      // redirect to this same page to get the new values
      return $this->_redirect($_SERVER['REQUEST_URI'], array('prependBase' => false));
    }
  }

  public function addPhraseAction()
  {
    if( $this->getRequest()->isPost() ) {
      $phrase = $this->_getParam('phrase');
      $locale = $this->_getParam('locale');

      if( $phrase && $locale ) {
        $targetFile = APPLICATION_PATH . '/application/languages/'.$locale.'/custom.csv';
        if( !file_exists($targetFile) ) {
          touch($targetFile);
          chmod($targetFile, 0777);
        }
        if( file_exists($targetFile) ) {
          $writer = new Engine_Translate_Writer_Csv($targetFile);
          $writer->setTranslations(array(
            $phrase => $phrase,
            ''=>'',
          ));
          $writer->write();
          @Zend_Registry::get('Zend_Cache')->clean();
        }
      }
    }
    
    /*
    $this->_helper->redirector->gotoRouteAndExit(array(
      'action' => 'edit',
      'phrase' => null,
      'search' => $phrase,
    ));
     * 
     */
  }
  
  public function translateAction()
  {
    // Prepare form
    $this->view->form = $form = new Core_Form_Admin_Language_Translate();

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $this->view->form = null;
    $this->view->values = $form->getValues();
  }

  public function translateRemoteAction()
  {
    set_time_limit(0);

    // Get params
    $this->view->source       = $source       = $this->_getParam('source');
    $this->view->target       = $target       = $this->_getParam('target');
    $this->view->batchCount   = $batchCount   = $this->_getParam('batchCount', 50);
    $this->view->retranslate  = $retranslate  = $this->_getParam('retranslate');
    $this->view->file         = $file         = $this->_getParam('file');
    $this->view->offset       = $offset       = $this->_getParam('offset', 0);

    // Check params

    // Check source
    if( empty($source) || !is_string($source) ) {
      throw new Engine_Exception('Invalid source data type');
    }
    if( !Zend_Locale::isLocale($source) ) {
      $this->view->status = false;
      $this->view->error = 'The source language does not appear to be a valid locale.';
      return;
    }
    if( !Engine_Service_GTranslate::isAvailableLanguage($source) ) {
      $this->view->status = false;
      $this->view->error = 'The source language does not appear to be a available for translation.';
      return;
    }

    // Check source dir
    $sourceDir = $this->_languagePath . '/' . $source;
    if( !is_dir($sourceDir) ) {
      $this->view->status = false;
      $this->view->error = 'The source language does not appear to exist.';
      return;
    }

    // Check target
    if( empty($target) || !is_string($target) ) {
      $this->view->status = false;
      $this->view->error = 'Invalid target data type.';
      return;
    }

    // See if we need to expand target
    if( in_array($target, array('all', 'all-translated', 'all-untranslated')) ) {
      $form = new Core_Form_Admin_Language_Translate();
      $targetMultiOptions = $form->target->options;
      if( $target == 'all-translated' ) {
        $targets = $targetMultiOptions['Translated'];
      } else if( $target == 'all-untranslated' ) {
        $targets = $targetMultiOptions['Untranslated'];
      } else {
        $targets = array_merge($targetMultiOptions['Untranslated'], $targetMultiOptions['Translated']);
      }

      // Meh
      $this->view->status = true;
      $this->view->state = 'listTargets';
      $this->view->targets = $targets;
      return;
    }

    // Check target
    if( !Zend_Locale::isLocale($source) ) {
      $this->view->status = false;
      $this->view->error = 'The target language does not appear to be a valid locale.';
      return;
    }
    if( !Engine_Service_GTranslate::isAvailableLanguage($source) ) {
      $this->view->status = false;
      $this->view->error = 'The target language does not appear to be a available for translation.';
      return;
    }

    // Check target dir
    $targetDir = $this->_languagePath . '/' . $target;
    if( !is_dir($targetDir) ) {
      // try to create
      if( !@mkdir($targetDir, 0777, true) ) {
        $this->view->status = false;
        $this->view->error = 'The target language pack does not exist and it was not possible to create it.';
        return;
      }
    }
    
    
    // Check files
    $sourceFiles = array();
    $it = new DirectoryIterator($sourceDir);
    foreach( $it as $file ) {
      // Ignore dirs (duh)
      if( !$file->isFile() ) continue;
      if( strtolower(substr($file->getFilename(), -4)) !== '.csv' ) continue;
      // Ignore files that have already been translated (at least for now)
      //if( file_exists($targetDir . '/' . $file->getFilename()) ) continue;
      $files[] = basename($file->getPathName());
    }

    if( empty($files) ) {
      $this->view->status = false;
      $this->view->error = 'No source files.';
      return;
    }
    
    // See if we need to expand file
    if( empty($file) ) {
      $this->view->status = true;
      $this->view->state = 'listFiles';
      $this->view->files = $files;
      return;
    } else if( !in_array($file, $files) ) {
      $this->view->status = false;
      $this->view->error = 'Not a valid file.';
      return;
    }

    $sourceFile = $sourceDir . '/' . $file;
    $targetFile = $targetDir . '/' . $file;

    // Init translate API
    $languageApi = new Engine_Service_GTranslate();
    $languageApi->setRequestType('curl');

    // Init reader and writer
    $reader = new Engine_Translate_Writer_Csv($sourceFile);
    $writer = new Engine_Translate_Writer_Csv($targetFile);

    // Get messages and diff
    $sourceMessages = $reader->getTranslations();
    $targetMessages = $writer->getTranslations();
    $deltaMessages = array_diff_key($sourceMessages, $targetMessages);

    // Take the batchCount of messages
    // In retranslate mode, we need to go by offset
    $currentMessages = array_slice($deltaMessages, ($retranslate ? $offset : 0), $batchCount);
    if( $retranslate ) {
      $this->view->newOffset = $offset = ( $offset + count($currentMessages) );
    }

    // If it's empty, we're done with the file
    if( (!$retranslate && empty($deltaMessages)) || empty($currentMessages) ) {
      $this->view->status = true;
      $this->view->state = 'fileComplete';
      return;
    }
    
    // Process plurals into normal variables temporarily
    $currentKeys = array();
    $currentValues = array();

    foreach( $currentMessages as $key => $value ) {
      if( is_array($value) ) {
        foreach( $value as $val ) {
          $currentKeys[] = $key;
          $currentValues[] = $this->_escape($val);
        }
      } else {
        $currentKeys[] = $key;
        $currentValues[] = $this->_escape($value);
      }
    }

    // Send to google
    $response  = (array) $languageApi->query($source, $target, $currentValues);
    if( !$response || count($response) !== count($currentValues) ) {
      $this->view->status = false;
      $this->view->error = 'Translation failed';
      return;
    }

    // Process response back into plurals
    $resultantMessages = array();
    foreach( $currentKeys as $index => $key ) {
      //$previousValue = $currentValues[$index];
      $value = $this->_unescape($response[$index]);

      if( isset($resultant[$key]) ) {
        if( !is_array($resultant[$key]) ) {
          $resultant[$key] = array($resultant[$key]);
        }
        $resultant[$key][] = $value;
      } else {
        $resultant[$key] = $value;
      }
    }
    
    
    // Write to file
    $writer->setTranslations($resultantMessages);
    $writer->write();


    // Send back statistics
    $this->view->status = true;
    $this->view->sourceCount = count($sourceMessages);
    $this->view->targetCount = count($targetMessages);
    $this->view->untranslatedCount = count($deltaMessages);
    $this->view->translatedCount = count($resultantMessages);
  }

  public function translatePhraseAction()
  {
    // Make sure source is valid
    $this->view->source = $source = $this->_getParam('source');
    if( !Zend_Locale::isLocale($source) ) {
      $this->view->status = false;
      $this->view->error = 'Source is not a valid Zend locale';
      return;
    }
    if( !Engine_Service_GTranslate::isAvailableLanguage($source) ) {
      $this->view->status = false;
      $this->view->error = 'Source is not a valid Google locale';
      return;
    }

    // Make sure target is valid
    $this->view->target = $target = $this->_getParam('target');
    if( !Zend_Locale::isLocale($target) ) {
      $this->view->status = false;
      $this->view->error = 'Target is not a valid Zend locale';
      return;
    }
    if( !Engine_Service_GTranslate::isAvailableLanguage($target) ) {
      $this->view->status = false;
      $this->view->error = 'Target is not a valid Google locale';
      return;
    }

    // Make sure we have a phrase
    $text = $this->_getParam('text');
    if( !$text ) {
      $this->view->status = false;
      $this->view->error = 'No text was given';
      return;
    }

    // Check for escape param
    $escape = (bool) $this->_getParam('escape', true);
    
    // Send query
    $languageApi = new Engine_Service_GTranslate();
    $languageApi->setRequestType('curl');

    if( $escape ) {
      $this->view->sourcePhraseUnescaped = $text;
      $text = $this->_escape($text);
    }

    $response  = $languageApi->query($source, $target, $text);

    if( $escape ) {
      $this->view->targetPhraseEscaped = $response;
      $response = $this->_unescape($response);
    }

    
    // Assign response
    $this->view->sourcePhrase = $text;
    $this->view->targetPhrase = $response;
  }

  public function exportAction()
  {
    $output    = array();
    $locale    = $this->_getParam('locale', 'en');
    $translate = Zend_Registry::get('Zend_Translate');

    // export en, then the language being exported, so that language pack will always contain ALL possible keys
    $output    = array_merge($translate->getMessages('en'), $translate->getMessages($locale));
    
    /*
    $languages = $translate->getList();
    $languages = array_unshift($languages, 'en');
    $languages = array_unique($languages);
echo "<pre>"; print_r($languages);exit;
    // english first, then the exported language will overwrite english
    foreach ($languages as $language) {
      $output = array_merge($output, $translate->getMessages($language));
    }
    */
    // dump to temporary file to get CSV formatting
    $tmp_file = APPLICATION_PATH . "/temporary/lang_export_{$locale}.csv";
    touch($tmp_file);
    chmod($tmp_file, 0777);
    $export   = new Engine_Translate_Writer_Csv($tmp_file);
    $export->setTranslations($output);
    $export->write();

    // force download of CSV file
    header("Content-Disposition: attachment; filename=\"$locale.csv\"");
    $fh = @fopen($tmp_file, 'r');
    if ($fh) {
      while (!feof($fh)) {
        echo fgets($fh, 4096);
        flush();
      }
      fclose($fh);
    } else 
      echo Zend_Registry::get('Zend_Translate')->_("CSV export failed.");
    @unlink($tmp_file);
    exit;
  }

  protected function _searchArrayRecursive($searchStr, $searchValue) {
    $found = false;
    if( is_array($searchValue) ) {
      foreach( $searchValue as $key => $value ) {
        if( is_string($value) && stripos($value, $searchStr) !== false ) {
          $found = true;
          break;
        } else if( is_array($value) ) {
          if( $this->_searchArrayRecursive($searchStr, $value) ) {
            $found = true;
            break;
          }
        }
      }
    }

    return $found;
  }
  
  protected function _escape($string)
  {
    // <![CDATA[
    // ]]>

    // Cdata
    // Format: {}
    //$cdataChar = "\x10";
    //$cdataData = null;
    //$string = $this->_replaceAll('/' . preg_quote('<![CDATA[', '/') . '.+?' . preg_quote(']]>', '/') . '/iu', $string, $cdataChar, $cdataData);

    // Just strip cdata for now
    $string = str_replace(array('<![CDATA[', ']]>'), array('', ''), $string);

    // Sprintf
    // Format: php.net
    $sprintfChar = "\x14";
    $sprintfData = null;
    $string = $this->_replaceAll('/\%([0-9]\$)?[ds]/iu', $string, $sprintfChar, $sprintfData);
    
    // Activity
    // Format: {}
    $activityChar = "\x11";
    $activityData = null;
    $string = $this->_replaceAll('/\{[^{}\x11\x12\x13\x14]+?\}/iu', $string, $activityChar, $activityData);

    // Substitution
    // Format: %%
    $substitutionChar = "\x12";
    $substitutionData = null;
    $string = $this->_replaceAll('/\%[^%\x11\x12\x13\x14]+?\%/iu', $string, $substitutionChar, $substitutionData);

    // Mail
    // Format: []
    $mailChar = "\x13";
    $mailData = null;
    $string = $this->_replaceAll('/\[[^\[\]\x11\x12\x13\x14]+?\]/iu', $string, $mailChar, $mailData);


    // Line breaks
    $lineChar = "\x15";
    $lineData = null;
    $string = $this->_replaceAll('/[\r\n]+/iu', $string, $lineChar, $lineData);


    // Restore everything
    $string = $this->_restoreAll($activityChar, $activityData, $string);
    $string = $this->_restoreAll($substitutionChar, $substitutionData, $string);
    $string = $this->_restoreAll($mailChar, $mailData, $string);
    $string = $this->_restoreAll($sprintfChar, $sprintfData, $string);
    $string = $this->_restoreAll($lineChar, $lineData, $string);
    
    return $string;
  }

  protected function _unescape($string)
  {
    // Decode
    $string = htmlspecialchars_decode($string, ENT_QUOTES);
    
    // Remove
    $string = preg_replace('/' . preg_quote('<![CDATA[', '/') . '(.*?)' . preg_quote(']]>', '/') . '/ius', '$1', $string);
    
    /*
    // Mail
    $string = preg_replace('/\<\?\{{3}([^\[\]]+?)\}{3}\?\>/iu', '[$1]', $string);

    // Substitution
    $string = preg_replace('/\<\?\{{2}([^%]+?)\}{2}\?\>/iu', '%$1%', $string);

    // Activity
    $string = preg_replace('/\<\?\{([^%]+?)\}\?\>/iu', '{$1}', $string);

    // Sprintf
    $string = preg_replace('/\<\?(\%([0-9]\$)?[ds])\?\>/iu', '$1', $string);
    */
    
    return $string;
  }

  protected function _replaceAll($pattern, $subject, $replace, &$replaceData)
  {
    preg_match_all($pattern, $subject, $matches, PREG_OFFSET_CAPTURE);
    if( empty($matches) ) {
      return array();
    }

    $replaceData = array();
    $delta = 0;
    foreach( $matches[0] as $match ) {
      $subString = $match[0];
      $start = $match[1] - $delta;
      $length = strlen($subString);
      $replaceData[] = $subString;
      $subject = substr($subject, 0, $start)
        . $replace
        . substr($subject, $start + $length);
      $delta += $length - 1;
    }

    return $subject;
  }

  protected function _restoreAll($char, $data, $string)
  {
    // <![CDATA[
    // ]]>
    if( is_array($data) && count($data) > 0 ) {
      $string = str_replace($char, '<![CDATA[' . $char . ']]>', $string);
      foreach( $data as $datum ) {
        $pos = strpos($string, $char);
        if( false !== $pos ) {
          $string = substr($string, 0, $pos)
            . $datum
            . substr($string, $pos + strlen($char))
            ;
        }
      }
    }
    return $string;
  }
}