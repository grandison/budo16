<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Tags.php 8854 2011-04-12 01:52:05Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Tags extends Engine_Db_Table
{
  protected $_rowClass = 'Core_Model_Tag';

  /**
   * Get the tag table
   *
   * @return Engine_Db_Table
   */
  public function getTagTable()
  {
    return $this;
  }

  /**
   * Get the tag map table
   *
   * @return Engine_Db_Table
   */
  public function getMapTable()
  {
    return Engine_Api::_()->getDbtable('TagMaps', 'core');
  }



  // Tags

  /**
   * Get an existing or create a new text tag
   *
   * @param string $text The tag text
   * @return Core_Model_Tag
   */
  public function getTag($text)
  {
    $text = $this->formatTagText($text);

    $table = $this->getTagTable();
    $select = $table->select()
      ->where('text = ?', $text);

    $row = $table->fetchRow($select);

    if( null === $row )
    {
      $row = $table->createRow();
      $row->text = $text;
      $row->save();
    }

    return $row;
  }

  public function getTagsByTagger($resource, Core_Model_Item_Abstract $tagger)
  {
    if( $resource instanceof Core_Model_Item_Abstract )
    {
      $resource_type = $resource->getType();
    }
    else
    {
      $resource_type = (string) $resource;
    }

    $mapTable = $this->getMapTable();
    $select = $mapTable->select()
      ->where('resource_type = ?', $resource_type)
      //->where('resource_id = ?', $resource->getIdentity())
      ->where('tagger_type = ?', $tagger->getType())
      ->where('tagger_id = ?', $tagger->getIdentity())
      ->where('tag_type = ?', 'core_tag')
      ->order('tag_id ASC');

    $identities = array();
    foreach( $mapTable->fetchAll($select) as $tagmap )
    {
      $identities[] = $tagmap->tag_id;
    }

    return Engine_Api::_()->getItemMulti('core_tag', $identities);
  }

  public function getTagsByText($text = null, $limit = 10)
  {
    $table = $this->getTagTable();
    $select = $table->select()
      ->order('text ASC')
      ->limit($limit);

    if( $text )
    {
      $select->where('text LIKE ?', '%'.$text.'%');
    }

    return $table->fetchAll($select);
  }

  /**
   * Called on text tags for formatting
   *
   * @param string $text
   * @return string
   */
  public function formatTagText($text)
  {
    // We can do formatting on tags later
    return trim($text);
  }



  // Tagging

  /**
   * Tag a resource
   *
   * @param Core_Model_Item_Abstract $resource The resource being tagged
   * @param Core_Model_Item_Abstract $tagger The resource doing the tagging
   * @param string|Core_Model_Item_Abstract $tag What is tagged in resource
   * @param array|null $extra
   * @return Engine_Db_Table_Row|null
   */
  public function addTagMap(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $tagger, $tag, $extra = null)
  {
    $tag = $this->_getTag($tag, true);

    if( !$tag ) {
      return false;
    }

    // Check if resource was already tagged with this
    if( null !== ($tagmap = $this->getTagMap($resource, $tag)) ) {
      return false; // return $tagmap;
      //throw new Core_Model_Exception('Resource was already tagged as this');
    }

    // Do the tagging
    $table = $this->getMapTable();
    $tagmap = $table->createRow();
    $tagmap->setFromArray(array(
      'resource_type' => $resource->getType(),
      'resource_id' => $resource->getIdentity(),
      'tagger_type' => $tagger->getType(),
      'tagger_id' => $tagger->getIdentity(),
      'tag_type' => $tag->getType(),
      'tag_id' => $tag->getIdentity(),
      'creation_date' => new Zend_Db_Expr('NOW()'),
      'extra' => $extra
    ));
    $tagmap->save();

    return $tagmap;
  }

  /**
   * Add multiple tags
   *
   * @param Core_Model_Item_Abstract $resource
   * @param Core_Model_Item_Abstract $tagger
   * @param array $tags
   * @return array
   */
  public function addTagMaps(Core_Model_Item_Abstract $resource, Core_Model_Item_Abstract $tagger, array $tags)
  {
    $tagmaps = array();
    foreach( $tags as $key => $value )
    {
      if( is_numeric($key) )
      {
        $tagmaps[] = $this->addTagMap($resource, $tagger, $value);
      }
      else
      {
        $tagmaps[] = $this->addTagMap($resource, $tagger, $key, $value);
      }
    }
    return $tagmaps;
  }

  /**
   * Get a tag map on resource and existing tag (for checking if already tagged)
   *
   * @param Core_Model_Item_Abstract $resource
   * @param string|Core_Model_Item_Abstract $tag
   * @return Engine_Db_Table|null
   */
  public function getTagMap(Core_Model_Item_Abstract $resource, $tag)
  {
    $tag = $this->_getTag($tag);

    $table = $this->getMapTable();
    $select = $table->select()
      ->where('resource_type = ?', $resource->getType())
      ->where('resource_id = ?', $resource->getIdentity())
      ->where('tag_type = ?', $tag->getType())
      ->where('tag_id = ?', $tag->getIdentity())
      ->limit(1)
      ;

    $tagmap = $table->fetchRow($select);
    return $tagmap;
  }

  /**
   * Get a tagmap by id that is part of resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @param integer $id
   */
  public function getTagMapById(Core_Model_Item_Abstract $resource, $id)
  {
    $table = $this->getMapTable();
    $select = $table->select()
      ->where('resource_type = ?', $resource->getType())
      ->where('resource_id = ?', $resource->getIdentity())
      ->where('tagmap_id = ?', (int) $id)
      ->limit(1)
      ;

    $tagmap = $table->fetchRow($select);
    return $tagmap;
  }

  /**
   * Get all tags for a resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @return Engine_Db_Table_Rowset
   */
  public function getTagMaps(Core_Model_Item_Abstract $resource)
  {
    return $this->getMapTable()->fetchAll($this->getTagMapSelect($resource));
  }

  /**
   * Get a select object for tags on a resource
   *
   * @param Core_Model_Item_Abstract $resource
   * @return Zend_Db_Table_Select
   */
  public function getTagMapSelect(Core_Model_Item_Abstract $resource)
  {
    $table = $this->getMapTable();
    $select = $table->select()
      ->where('resource_type = ?', $resource->getType())
      ->where('resource_id = ?', $resource->getIdentity())
      ->order('tag_id ASC')
      ;

    return $select;
  }

  public function setTagMaps(Core_Model_Item_Abstract $resource, $tagger, array $tags)
  {
    $existingTagMaps = $this->getTagMaps($resource);
    $added = array();
    $setTagIndex = array();
    $tagObjects = array();
    foreach( $tags as $tag )
    {
      if(!empty($tag)){
        $tagObject = $this->_getTag($tag);
        $tagObjects[] = $tagObject;
        $setTagIndex[$tagObject->getGuid()] = $tagObject;
      }
    }

    // Check for new tags
    foreach( $tagObjects as $tag )
    {
      if( !$existingTagMaps->getRowMatching(array(
          'tag_type' => $tag->getType(),
          'tag_id' => $tag->getIdentity(),
        )) ) {
        $added[] = $this->addTagMap($resource, $tagger, $tag);
      }
    }

    // Check for removed tags
    foreach( $existingTagMaps as $tagmap )
    {
      $key = $tagmap->tag_type . '_' . $tagmap->tag_id;
      if( empty($setTagIndex[$key]) )
      {
        $tagmap->delete();
      }
    }

    return $added;
  }

  public function removeTagMap(Core_Model_Item_Abstract $resource, $tag)
  {
    $tag = $this->_getTag($tag);
  }




  // Resources

  public function getResourcesByTagSelect($tag, array $params = array())
  {
    if( is_string($tag) ) {
      $tag = $this->_getTag($tag);
    }

    $table = $this->getMapTable();
    $select = $table->select()
      ->where('tag_type = ?', $tag->getType())
      ->where('tag_id = ?', $tag->getIdentity())
      ;

    if( !empty($params['resource_types']) ) {
      $types = array();
      foreach( $params['resource_types'] as $type )
      {
        if( !Engine_Api::_()->hasItemType($type) ) continue;
        $types[] = $type;
      }
      $select->where('resource_type IN(\''.join("', '", $types).'\')')->order('creation_date DESC');
    }

    return $select;
  }






  // Utility

  /**
   * Gets an existing string tag or returns the passed item
   *
   * @param string|Core_Model_Item_Abstract $tag
   * @return Core_Model_Item_Abstract
   * @throws Core_Model_Exception If argument is not a string or an item
   */
  protected function _getTag($tag, $return = false)
  {
    if( is_string($tag) )
    {
      $tag = $this->getTag($tag);
    }

    if( !($tag instanceof Core_Model_Item_Abstract) || !$tag->getIdentity() )
    {
      if( $return )
      {
        return null;
      }
      else
      {
        throw new Core_Model_Exception('Improper tag');
      }
    }

    return $tag;
  }
}