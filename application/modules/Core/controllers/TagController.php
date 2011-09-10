<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: TagController.php 8174 2011-01-08 01:53:04Z steve $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_TagController extends Core_Controller_Action_Standard
{
  public function init()
  {
    /*
    $subject = $this->_getParam('subject');
    if( is_string($subject) )
    {
      $subject = Engine_Api::_()->getItemByGuid($guid);
    }
    if( $subject instanceof Core_Model_Item_Abstract && $subject->getIdentity() )
    {
      Engine_Api::_()->core()->setSubject($subject);
    }
     */
  }
  
  public function addAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;
    //if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'tag')->isValid() ) return;

    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !method_exists($subject, 'tags') ) {
      throw new Engine_Exception('whoops! doesn\'t support tagging');
    }
    
    // GUID tagging
    if( null !== ($guid = $this->_getParam('guid')) )
    {
      $tag = Engine_Api::_()->getItemByGuid($this->_getParam('guid'));
    }

    // STRING tagging
    else if( null !== ($text = $this->_getParam('label')) )
    {
      $tag = $text;
    }

    $tagmap = $subject->tags()->addTagMap($viewer, $tag, $this->_getParam('extra'));

    if( is_null($tagmap) ) {
      // item has already been tagged
      return;
    }
    
    if( !$tagmap instanceof Core_Model_TagMap ) {
      throw new Engine_Exception('Tagmap was not recognised');
    }
    
    // Do stuff when users are tagged
    if( $tag instanceof User_Model_User && !$subject->isOwner($tag) && !$viewer->isSelf($tag) )
    {
      // Add activity
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity(
        $viewer,
        $tag,
        'tagged',
        '',
        array(
          'label' => str_replace('_', ' ', $subject->getShortType())
        )
      );
      if( $action ) $action->attach($subject);

      // Add notification
      $type_name = $this->view->translate(str_replace('_', ' ', $subject->getShortType()));
      Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification(
        $tag,
        $viewer,
        $subject,
        'tagged',
        array(
          'object_type_name' => $type_name,
          'label'            => $type_name,
        )
      );
    }
    
    $this->view->id = $tagmap->getIdentity();
    $this->view->guid = $tagmap->tag_type . '_' . $tagmap->tag_id;
    $this->view->text = $tagmap->getTitle();
    $this->view->href = $tagmap->getHref();
    $this->view->extra = $tagmap->extra;
  }

  public function removeAction()
  {
    if( !$this->_helper->requireUser()->isValid() ) return;
    if( !$this->_helper->requireSubject()->isValid() ) return;
    //if( !$this->_helper->requireAuth()->setAuthParams(null, null, 'tag')->isValid() ) return;

    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();

    // Subject doesn't have tagging
    if( !method_exists($subject, 'tags') ) {
      throw new Engine_Exception('Subject doesn\'t support tagging');
    }

    // Get tagmao
    $tagmap_id = $this->_getParam('tagmap_id');
    $tagmap = $subject->tags()->getTagMapById($tagmap_id);
    if( !($tagmap instanceof Core_Model_TagMap) ) {
      throw new Engine_Exception('Tagmap missing');
    }

    // Can remove if: is tagger, is tagged, is owner of resource, has tag permission
    if( $viewer->getGuid() != $tagmap->tagger_type . '_' . $tagmap->tagger_id &&
        $viewer->getGuid() != $tagmap->tag_type . '_' . $tagmap->tag_id &&
        !$subject->isOwner($viewer) /* &&
        !$subject->authorization()->isAllowed($viewer, 'tag') */ ) {
      throw new Engine_Exception('Not authorized');
    }
    
    $tagmap->delete();
  }

  public function suggestAction()
  {
    $tags = Engine_Api::_()->getDbtable('tags', 'core')->getTagsByText($this->_getParam('text'), $this->_getParam('limit', 40));
    $data = array();
    $mode = $this->_getParam('struct');

    if( $mode == 'text' )
    {
      foreach( $tags as $tag )
      {
        $data[] = $tag->text;
      }
    }
    else
    {
      foreach( $tags as $tag )
      {
        $data[] = array(
          'id' => $tag->tag_id,
          'label' => $tag->text
        );
      }
    }

    if( $this->_getParam('sendNow', true) )
    {
      return $this->_helper->json($data);
    }
    else
    {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }

  public function retrieveAction()
  {
    if( !$this->_helper->requireSubject()->checkRequire() ) return;

    $subject = Engine_Api::_()->core()->getSubject();
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !method_exists($subject, 'tags') ) {
      throw new Engine_Exception('whoops! doesn\'t support tagging');
    }
    
    $data = array();
    foreach( $subject->tags()->getTagMaps() as $tagmap ) {
      $data[] = array_merge($tagmap->toArray(), array(
        'id' => $tagmap->getIdentity(),
        'text' => $tagmap->getTitle(),
        'href' => $tagmap->getHref(),
        'guid' => $tagmap->tag_type . '_' . $tagmap->tag_id
      ));
    }

    if( $this->_getParam('sendNow', true) )
    {
      return $this->_helper->json($data);
    }
    else
    {
      $this->_helper->viewRenderer->setNoRender(true);
      $data = Zend_Json::encode($data);
      $this->getResponse()->setBody($data);
    }
  }
}