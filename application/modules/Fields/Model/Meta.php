<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Meta.php 7467 2010-09-25 00:38:51Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Model_Meta extends Fields_Model_Abstract
{
  protected $_elementName;

  public function getParentMaps()
  {
    return Engine_Api::_()->fields()
      ->getFieldsMaps($this->getTable()->getFieldType())
      ->getRowsMatching('field_id', $this->field_id);
  }

  public function getChildMaps()
  {
    return Engine_Api::_()->fields()
      ->getFieldsMaps($this->getTable()->getFieldType())
      ->getRowsMatching('child_id', $this->field_id);
  }

  public function getOption($option_id) {
    return Engine_Api::_()->fields()
      ->getFieldsOptions($this->getTable()->getFieldType())
      ->getRowMatching(array(
        'option_id' => $option_id,
        'field_id' => $this->field_id,
      ));
  }

  public function getOptions()
  {
    return Engine_Api::_()->fields()
      ->getFieldsOptions($this->getTable()->getFieldType())
      ->getRowsMatching('field_id', $this->field_id);
  }

  public function getOptionIds()
  {
    $ids = array();
    foreach( $this->getOptions() as $option ) {
      $ids[] = $option->option_id;
    }
    return $ids;
  }

  public function getValue($spec)
  {
    // spec must be an instance of Core_Model_Item_Abstract
    if( !($spec instanceof Core_Model_Item_Abstract) )
    {
      throw new Fields_Model_Exception('$spec must be an instance of Core_Model_Item_Abstract');
    }

    if( !$spec->getIdentity() )
    {
      return null;
    }

    $values = Engine_Api::_()->fields()
      ->getFieldsValues($spec);

    if( !$values )
    {
      return null;
    }

    if( in_array($this->type, array('multiselect', 'multi_checkbox', 'partner_gender', 'looking_for')) ) {
      return $values->getRowsMatching('field_id', $this->field_id);
    } else {
      return $values->getRowMatching('field_id', $this->field_id);
    }
  }

  public function getElementParams($spec, array $additionalParams = array())
  {
    $params = $this->_data;

    $info = Engine_Api::_()->fields()->getFieldInfo($this->type);

    $name = $this->getElementName();
    $type = Engine_Api::_()->fields()->inflectFieldType($params['type']);
    $config = $params['config'];

    unset($params['field_id']);
    unset($params['type']);
    unset($params['config']);
    unset($params['parent_option_id']);
    unset($params['parent_field_id']);
    //unset($params['alias']);
    unset($params['error']);
    unset($params['display']);
    unset($params['search']);

    $params['allowEmpty'] = ! @$params['required'];

    if( !is_array($config) ) $config = array();
    $options = array_merge($config, $params, $additionalParams);

    // Clean out null and "NULL" values
    foreach( $options as $index => $option )
    {
      // Note: Don't do empty() here, there may be false values
      if( is_null($options[$index]) )
      {
        unset($options[$index]);
      }
      if( is_string($option) && strtoupper($option) == 'NULL' )
      {
        unset($options[$index]);
      }
    }

    // Process multi options
    if( $this->canHaveDependents() )
    {
      $options['multiOptions'] = array();
      if( /*empty($config['required']) && */ empty($info['multi']) && $this->type != 'radio' ) {
        $options['multiOptions'][''] = '';
      }
      foreach( $this->getOptions() as $option ) {
        $options['multiOptions'][$option->option_id] = $option->label;
      }
    }

    // or just regular options
    else if( !empty($info['multiOptions']) ) {
      $options['multiOptions'] = $info['multiOptions'];
      if( /*empty($config['required']) &&*/ empty($info['multi']) ) {
        $options['multiOptions'] = array_merge(array('' => ''), $options['multiOptions']);
      }
    }

    // Process value
    if( $spec instanceof Core_Model_Item_Abstract )
    {
      $value = $this->getValue($spec);
      if( is_array($value) ) {
        $vals = array();
        foreach( $value as $singleValue ) {
          $vals[] = $singleValue->value;
        }
        $options['value'] = $vals;
      } else if( is_object($value) ) {
        $options['value'] = htmlspecialchars_decode($value->value);
      }
    }

    return array(
      'type' => $type,
      'name' => $name,
      'options' => $options
    );
  }

  public function setElementName($name)
  {
    $this->_elementName = $name;
    return $this;
  }

  public function getElementName()
  {
    if( is_null($this->_elementName) )
    {
      $this->_elementName = $this->field_id;
    }
    return $this->_elementName;
  }

  public function canHaveDependents()
  {
    return ( in_array($this->type, Engine_Api::_()->fields()->getFieldInfo('dependents')) );
  }

  public function formatValue($value)
  {
    return $value;
  }

  public function isHeading()
  {
    return ( $this->type == 'heading' );
  }
}