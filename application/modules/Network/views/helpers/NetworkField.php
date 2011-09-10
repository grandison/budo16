<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: NetworkField.php 8544 2011-03-02 00:15:57Z jung $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Network_View_Helper_NetworkField extends Zend_View_Helper_Abstract
{
  public function networkField($network, $fields)
  {
    if( !($network instanceof Network_Model_Network) ||
        !$network->field_id || !$fields ||
        false == ($field = $fields->getRowMatching('field_id', $network->field_id)) ||
        empty($network->pattern['type']) ||
        empty($network->pattern['value']) ) {
      return $this->view->translate('n/a');
    }

    $type = $network->pattern['type'];
    $value = $network->pattern['value'];

    $info = Engine_Api::_()->fields()->getFieldInfo($field->type);
    $genericType = $field->type;
    if( !empty($info['base']) ) {
      $genericType = $info['base'];
    }

    $content = $this->view->translate($field->label);
    switch( $type ) {
      case 'text':
      case 'exact':
        $content .= ' (' . $value . ')';
        break;

      case 'select':
      case 'multiselect':
        if( $field->type == 'country' ) {
          $tmp = new Fields_Form_Element_Country('tmp');
          $multiOptions = $tmp->getMultiOptions();
          $option = $multiOptions[$value];
        } else if( $field->type != $genericType ) {
          $elOpt = $field->getElementParams(null);
          $option = $elOpt['options']['multiOptions'][$value];
        } else {
          $option = $field->getOption($value);
          if( is_object($option) ) {
            $option = $option->label;
          }
        }

        if(is_array($value) ) {
          return $content .= ' (' . $this->view->translate('Multiple Options') . ')';
        }

        if( !$option ) {
          return $this->view->translate('n/a');
        }
        
        $content .= ' (' . $this->view->translate($option) . ')';
        break;

      case 'range':
      case 'date':
        $arr = array();
        if( !empty($value['min']) && !empty($value['max']) ) {
          $content .= ' (' . $value['min'] . ' - ' . $value['max'] . ')';
        } else if( !empty($value['min']) &&  empty($value['max']) ) {
          $content .= ' (>' . $value['min'] . ')';
        } else if(  empty($value['min']) && !empty($value['max']) ) {
          $content .= ' (<' . $value['max'] . ')';
        } else {
          return $this->view->translate('n/a');
        }
        break;
      default:
        return $this->view->translate('n/a');
        break;
    }

    return $content;
  }
}