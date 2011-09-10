<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSettingsController.php 8319 2011-01-26 22:29:01Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_AdminSettingsController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('video_admin_main', array(), 'video_admin_main_settings');

    // Check ffmpeg path for correctness
    if( function_exists('exec') ) {
      $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
      
      $output = null;
      $return = null;
      if( !empty($ffmpeg_path) ) {
        exec($ffmpeg_path . ' -version', $output, $return);
      }
      // Try to auto-guess ffmpeg path if it is not set correctly
      $ffmpeg_path_original = $ffmpeg_path;
      if( empty($ffmpeg_path) || $return > 0 || stripos(join('', $output), 'ffmpeg') === false ) {
        $ffmpeg_path = null;
        // Windows
        if( strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ) {
          // @todo
        }
        // Not windows
        else {
          $output = null;
          $return = null;
          @exec('which ffmpeg', $output, $return);
          if( 0 == $return ) {
            $ffmpeg_path = array_shift($output);
            $output = null;
            $return = null;
            exec($ffmpeg_path . ' -version', $output, $return);
            if( 0 != $return ) {
              $ffmpeg_path = null;
            }
          }
        }
      }
      if( $ffmpeg_path != $ffmpeg_path_original ) {
        Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path = $ffmpeg_path;
      }
    }

    // Make form
    $this->view->form = $form = new Video_Form_Admin_Global();

    // Check method/data
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    $values = $form->getValues();

    // Check ffmpeg path
    if( !empty($values['video_ffmpeg_path']) ) {
      if( function_exists('exec') ) {
        $ffmpeg_path = $values['video_ffmpeg_path'];
        $output = null;
        $return = null;
        exec($ffmpeg_path . ' -version', $output, $return);
        if( $return > 0 ) {
          $form->video_ffmpeg_path->addError('FFMPEG path is not valid or does not exist');
          $values['video_ffmpeg_path'] = '';
        }
      } else {
        $form->video_ffmpeg_path->addError('The exec() function is not available. The ffmpeg path has not been saved.');
        $values['video_ffmpeg_path'] = '';
      }
    }

    // Okay, save
    foreach( $values as $key => $value ) {
      Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
    }
    $form->addNotice('Your changes have been saved.');
  }

  public function categoriesAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('video_admin_main', array(), 'video_admin_main_categories');

    $this->view->categories = Engine_Api::_()->video()->getCategories();
  }
  
  public function levelAction()
  {
    // Make navigation
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('video_admin_main', array(), 'video_admin_main_level');

    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $level = Engine_Api::_()->getItem('authorization_level', $id);
    } else {
      $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
    }

    if( !$level instanceof Authorization_Model_Level ) {
      throw new Engine_Exception('missing level');
    }

    $level_id = $id = $level->level_id;

    // Make form
    $this->view->form = $form = new Video_Form_Admin_Settings_Level(array(
      'public' => ( in_array($level->type, array('public')) ),
      'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
    ));
    $form->level_id->setValue($id);

    // Populate values
    $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    $form->populate($permissionsTable->getAllowed('video', $id, array_keys($form->getValues())));

    // Check post
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Check validitiy
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process

    $values = $form->getValues();

    $db = $permissionsTable->getAdapter();
    $db->beginTransaction();

    try
    {
      // Set permissions
      $permissionsTable->setAllowed('video', $id, $values);

      // Commit
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    $form->addNotice('Your changes have been saved.');
  }

  public function utilityAction()
  {
    if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
      return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    }
    
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('video_admin_main', array(), 'video_admin_main_utility');
    
    $ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
    if( function_exists('shell_exec') ) {
      // Get version
      $this->view->version = $version
          = shell_exec(escapeshellcmd($ffmpeg_path) . ' -version 2>&1');
      $command = "$ffmpeg_path -formats 2>&1";
      $this->view->format = $format
          = shell_exec(escapeshellcmd($ffmpeg_path) . ' -formats 2>&1')
          . shell_exec(escapeshellcmd($ffmpeg_path) . ' -codecs 2>&1');
    }

    /*
    // Get files in admin uploads
    $it = new DirectoryIterator(APPLICATION_PATH . DIRECTORY_SEPARATOR
        . 'public' . DIRECTORY_SEPARATOR . 'admin');
    $testFiles = array();
    foreach( $it as $fileinfo ) {
      if( $fileinfo->isFile() ) {
        $testFiles[$fileinfo->getFilename()] = $fileinfo->getFilename();
      }
    }
    $this->view->testFiles = $testFiles;
     * 
     */
  }

  public function addCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');

    // Generate and assign form
    $form = $this->view->form = new Video_Form_Admin_Category();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));
    // Check post
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      // we will add the category
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try
      {
        // add category to the database
        // Transaction
        $table = Engine_Api::_()->getDbtable('categories', 'video');
        $user = Engine_Api::_()->user()->getViewer();

        // insert the category into the database
        $row = $table->createRow();
        $row->user_id   =  $user->getIdentity();
        $row->category_name = $values["label"];
        $row->save();

        $db->commit();
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-settings/form.tpl');
  }

  public function deleteCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $id = $this->_getParam('id');
    $this->view->video_id=$id;
    // Check post
    if( $this->getRequest()->isPost())
    {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try
      {
        // go through logs and see which videos used this category id and set it to ZERO

        $videoTable = $this->_helper->api()->getDbtable('videos', 'video');
        $select = $videoTable->select()->where('category_id = ?', $id);
        $videos = $videoTable->fetchAll($select);

        // create permissions
        foreach( $videos as $video )
        {
          //this is not working
          $video->category_id = 0;
          $video->save();
        }

        $row = Engine_Api::_()->video()->getCategory($id);
        // delete the video category into the database
        if( $row ) {
          $row->delete();
        }


        $db->commit();
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
    }

    // Output
    $this->renderScript('admin-settings/delete.tpl');
  }

  public function editCategoryAction()
  {
    // In smoothbox
    $this->_helper->layout->setLayout('admin-simple');
    $form = $this->view->form = new Video_Form_Admin_Category();
    $form->setAction($this->getFrontController()->getRouter()->assemble(array()));

    // Check post
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      // Ok, we're good to add field
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();

      try
      {
        // edit category in the database
        // Transaction
        $row = Engine_Api::_()->video()->getCategory($values["id"]);

        $row->category_name = $values["label"];
        $row->save();
        $db->commit();
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => array('')
      ));
   }

    // Must have an id
    if( !($id = $this->_getParam('id')) )
    {
      die('No identifier specified');
    }

    // Generate and assign form
    $category = Engine_Api::_()->video()->getCategory($id);
    $form->setField($category);

    // Output
    $this->renderScript('admin-settings/form.tpl');
  }

  public function testEncodeAction()
  {
    
  }
}