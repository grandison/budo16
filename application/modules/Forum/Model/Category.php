<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Category.php 7820 2010-11-18 22:08:00Z steve $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Forum_Model_Category extends Core_Model_Item_Collection
{
  protected $_children_types = array('forum_forum');

  protected $_collectible_type = "forum_forum";

  protected $_collection_column_name = "category_id";

  public function getHref($params = array())
  {
    $params = array_merge(array(
      'route' => 'forum_general',
      'reset' => true,
      /*
      'route' => 'default',
      'reset' => true,
      'module' => 'forum',
      'controller' => 'category',
      'action' => 'view',
      'category_id' => $this->getIdentity(),
       * 
       */
    ), $params);
    $route = $params['route'];
    $reset = $params['reset'];
    unset($params['route']);
    unset($params['reset']);
    return Zend_Controller_Front::getInstance()->getRouter()
      ->assemble($params, $route, $reset);
  }


  protected function getPrevCategory()
  {
    $table = Engine_Api::_()->getItemTable('forum_category');
    if( !in_array('order', $table->info('cols')) )
    {
      throw new Core_Model_Item_Exception('Unable to use order as order column doesn\'t exist');
    }
    

    $select = $table->select()->setIntegrityCheck(false)
      ->from($table->info('name'), 'MAX(`order`) AS max_order')
      ->where('`order` < ?', $this->order);

    $row = $select->query()->fetch();
    return $table->fetchAll($table->select()->where('`order` = ?', $row['max_order']))->current();
  }

  
  public function moveUp()
  {
    $table = $this->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try
    {
      $categories = $table->select()->order('order ASC')->query()->fetchAll();
      $new_cats   = array();
      foreach ($categories as $category) {
        if ($this->category_id == $category['category_id']) {
          $prev_cat = array_pop($new_cats);
          array_push($new_cats, $category['category_id']);
          if ($prev_cat) {
            array_push($new_cats, $prev_cat);
            unset($prev_cat);
          }
        } else {
          array_push($new_cats, $category['category_id']);
        }
      }
      foreach ($table->fetchAll($table->select()) as $row) {
        $order = array_search($row->category_id, $new_cats);
        $row->order = $order+1;
        $row->save();
      }
      $db->commit();
    }
    catch (Exception $e)
    {
      $db->rollBack();
      throw $e;
    }
  }
}