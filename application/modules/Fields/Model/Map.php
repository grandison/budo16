<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Map.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_Map extends Fields_Model_Abstract
{
  public function getKey()
  {
    return $this->field_id . '_' . $this->option_id . '_' . $this->child_id;
  }

  public function getField()
  {
    return Engine_Api::_()->fields()
      ->getFieldsMeta($this->getTable()->getFieldType())
      ->getRowMatching('field_id', $this->field_id);
  }

  public function getOption()
  {
    return Engine_Api::_()->fields()
      ->getFieldsOptions($this->getTable()->getFieldType())
      ->getRowMatching('option_id', $this->option_id);
  }

  public function getChild()
  {
    return Engine_Api::_()->fields()
      ->getFieldsMeta($this->getTable()->getFieldType())
      ->getRowMatching('field_id', $this->child_id);
  }
}