<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Core.php 8413 2011-02-09 01:12:03Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Plugin_Core
{
  public function onItemDeleteBefore($event)
  {
    $payload = $event->getPayload();

    if( $payload instanceof Core_Model_Item_Abstract ) {

      // Delete tagmaps
      $tagMapTable = Engine_Api::_()->getDbtable('TagMaps', 'core');

      // Delete tagmaps by resource
      $tagMapSelect = $tagMapTable->select()
        ->where('resource_type = ?', $payload->getType())
        ->where('resource_id = ?', $payload->getIdentity());
      foreach( $tagMapTable->fetchAll($tagMapSelect) as $tagMap ) {
        $tagMap->delete();
      }

      // Delete tagmaps by tagger
      $tagMapSelect = $tagMapTable->select()
        ->where('tagger_type = ?', $payload->getType())
        ->where('tagger_id = ?', $payload->getIdentity());
      foreach( $tagMapTable->fetchAll($tagMapSelect) as $tagMap ) {
        $tagMap->delete();
      }

      // Delete tagmaps by tag
      $tagMapSelect = $tagMapTable->select()
        ->where('tag_type = ?', $payload->getType())
        ->where('tag_id = ?', $payload->getIdentity());
      foreach( $tagMapTable->fetchAll($tagMapSelect) as $tagMap ) {
        $tagMap->delete();
      }

      // Delete links
      $linksTable = Engine_Api::_()->getDbtable('links', 'core');

      // Delete links by parent
      $linksSelect = $linksTable->select()
        ->where('parent_type = ?', $payload->getType())
        ->where('parent_id = ?', $payload->getIdentity());
      foreach( $linksTable->fetchAll($linksSelect) as $link ) {
        $link->delete();
      }

      // Delete links by owner
      $linksSelect = $linksTable->select()
        ->where('owner_type = ?', $payload->getType())
        ->where('owner_id = ?', $payload->getIdentity());
      foreach( $linksTable->fetchAll($linksSelect) as $link ) {
        $link->delete();
      }

      // Delete comments
      $commentTable = Engine_Api::_()->getDbtable('comments', 'core');

      // Delete comments by parent
      $commentSelect = $commentTable->select()
        ->where('resource_type = ?', $payload->getType())
        ->where('resource_id = ?', $payload->getIdentity());
      foreach( $commentTable->fetchAll($commentSelect) as $comment ) {
        $comment->delete();
      }

      // Delete comments by poster
      $commentSelect = $commentTable->select()
        ->where('poster_type = ?', $payload->getType())
        ->where('poster_id = ?', $payload->getIdentity());
      foreach( $commentTable->fetchAll($commentSelect) as $comment ) {
        $comment->delete();
      }

      // Delete likes
      $likeTable = Engine_Api::_()->getDbtable('likes', 'core');

      // Delete likes by resource
      $likeSelect = $likeTable->select()
        ->where('resource_type = ?', $payload->getType())
        ->where('resource_id = ?', $payload->getIdentity());
      foreach( $likeTable->fetchAll($likeSelect) as $like ) {
        $like->delete();
      }

      // Delete likes by poster
      $likeSelect = $likeTable->select()
        ->where('poster_type = ?', $payload->getType())
        ->where('poster_id = ?', $payload->getIdentity());
      foreach( $likeTable->fetchAll($likeSelect) as $like ) {
        $like->delete();
      }


      // Delete styles
      $stylesTable = Engine_Api::_()->getDbtable('styles', 'core');
      $stylesSelect = $stylesTable->select()
        ->where('type = ?', $payload->getType())
        ->where('id = ?', $payload->getIdentity());
      foreach( $stylesTable->fetchAll($stylesSelect) as $styles ) {
        $styles->delete();
      }
      
      // Delete reports
      $reportTable = Engine_Api::_()->getDbtable('reports', 'core');
      $reportTable->delete(array(
        'subject_type = ?' => $payload->getType(),
        'subject_id = ?' => $payload->getIdentity(),
      ));
    }

    // Users only
    if( $payload instanceof User_Model_User ) {

      // Delete reports
      $reportTable = Engine_Api::_()->getDbtable('reports', 'core');

      // Delete reports by reporter
      $reportSelect = $reportTable->select()
        ->where('user_id = ?', $payload->getIdentity());
      foreach( $reportTable->fetchAll($reportSelect) as $report ) {
        $report->delete();
      }
    }
  }
}