<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: install.php 8405 2011-02-05 03:36:15Z john $
 * @author     Stephen
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Installer extends Engine_Package_Installer_Module
{
  function onInstall()
  {
    $this->_checkFfmpegPath();
    $this->_addUserProfileContent();
    //$this->_addVideoProfileContent();
    
    parent::onInstall();
  }

  protected function _checkFfmpegPath()
  {
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);

    // Check ffmpeg path for correctness
    if( function_exists('exec') && function_exists('shell_exec') ) {
      // Api is not available
      //$ffmpeg_path = Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
      $ffmpeg_path = $db->select()
        ->from('engine4_core_settings', 'value')
        ->where('name = ?', 'video.ffmpeg.path')
        ->limit(1)
        ->query()
        ->fetchColumn(0)
        ;

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
            if( 0 == $return ) {
              $ffmpeg_path = null;
            }
          }
        }
      }
      if( $ffmpeg_path != $ffmpeg_path_original ) {
        $count = $db->update('engine4_core_settings', array(
          'value' => $ffmpeg_path,
        ), array(
          'name = ?' => 'video.ffmpeg.path',
        ));
        if( $count === 0 ) {
          try {
            $db->insert('engine4_core_settings', array(
              'value' => $ffmpeg_path,
              'name' => 'video.ffmpeg.path',
            ));
          } catch( Exception $e ) {

          }
        }
      }
    }
  }

  protected function _addUserProfileContent()
  {
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);
    

    // profile page
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'user_profile_index')
      ->limit(1);
    $page_id = $select->query()->fetchObject()->page_id;


    // video.profile-videos

    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_content')
      ->where('page_id = ?', $page_id)
      ->where('type = ?', 'widget')
      ->where('name = ?', 'video.profile-videos')
      ;
    $info = $select->query()->fetch();

    if( empty($info) ) {

      // container_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('page_id = ?', $page_id)
        ->where('type = ?', 'container')
        ->limit(1);
      $container_id = $select->query()->fetchObject()->content_id;

      // middle_id (will always be there)
      $select = new Zend_Db_Select($db);
      $select
        ->from('engine4_core_content')
        ->where('parent_content_id = ?', $container_id)
        ->where('type = ?', 'container')
        ->where('name = ?', 'middle')
        ->limit(1);
      $middle_id = $select->query()->fetchObject()->content_id;

      // tab_id (tab container) may not always be there
      $select
        ->reset('where')
        ->where('type = ?', 'widget')
        ->where('name = ?', 'core.container-tabs')
        ->where('page_id = ?', $page_id)
        ->limit(1);
      $tab_id = $select->query()->fetchObject();
      if( $tab_id && @$tab_id->content_id ) {
          $tab_id = $tab_id->content_id;
      } else {
        $tab_id = null;
      }

      // tab on profile
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type'    => 'widget',
        'name'    => 'video.profile-videos',
        'parent_content_id' => ($tab_id ? $tab_id : $middle_id),
        'order'   => 12,
        'params'  => '{"title":"Videos","titleCount":true}',
      ));

    }
  }

  protected function _addVideoProfileContent()
  {
    $db     = $this->getDb();
    $select = new Zend_Db_Select($db);
    
    // Check if it's already been placed
    $select = new Zend_Db_Select($db);
    $select
      ->from('engine4_core_pages')
      ->where('name = ?', 'video_index_view')
      ->limit(1);
      ;
    $info = $select->query()->fetch();

    if( empty($info) ) {
      $db->insert('engine4_core_pages', array(
        'name' => 'video_index_view',
        'displayname' => 'Video View',
        'title' => 'View Video',
        'description' => 'This is the view page for a video.',
        'custom' => 0,
        'provides' => 'subject=video',
      ));
      $page_id = $db->lastInsertId('engine4_core_pages');

      // containers
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'main',
        'parent_content_id' => null,
        'order' => 1,
        'params' => '',
      ));
      $container_id = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'right',
        'parent_content_id' => $container_id,
        'order' => 1,
        'params' => '',
      ));
      $right_id = $db->lastInsertId('engine4_core_content');

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'container',
        'name' => 'middle',
        'parent_content_id' => $container_id,
        'order' => 3,
        'params' => '',
      ));
      $middle_id = $db->lastInsertId('engine4_core_content');
      
      // middle column content
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.content',
        'parent_content_id' => $middle_id,
        'order' => 1,
        'params' => '',
      ));

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'core.comments',
        'parent_content_id' => $middle_id,
        'order' => 2,
        'params' => '',
      ));

      // right column
      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'video.show-same-tags',
        'parent_content_id' => $right_id,
        'order' => 1,
        'params' => '',
      ));

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'video.show-also-liked',
        'parent_content_id' => $right_id,
        'order' => 2,
        'params' => '',
      ));

      $db->insert('engine4_core_content', array(
        'page_id' => $page_id,
        'type' => 'widget',
        'name' => 'video.show-same-poster',
        'parent_content_id' => $right_id,
        'order' => 3,
        'params' => '',
      ));
    }
  }
}
