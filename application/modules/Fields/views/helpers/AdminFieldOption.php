<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminFieldOption.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_View_Helper_AdminFieldOption extends Zend_View_Helper_Abstract
{
  public function adminFieldOption($option, $map)
  {
    $parentField = Engine_Api::_()->fields()->getFieldsMeta($option->getFieldType())->getRowMatching('field_id', $option->field_id);

    $parentFieldLabel = $parentField->label;
    $optionId = $option->option_id;
    $optionLabel = $this->view->translate($option->label);
    $key = $map->getKey();

    $translate = Zend_Registry::get('Zend_Translate');
    $translate_extra_questions = $this->view->translate('These extra questions appear when "%1$s" is selected for "%2$s".', array($optionLabel, $parentFieldLabel));

    $depWrapperClass = 'admin_field_dependent_field_wrapper ' .
      'admin_field_dependent_field_wrapper_' . $optionId . ' ' .
      $this->_generateClassNames($key, 'admin_field_dependent_field_wrapper_');

    $content = <<<EOF
  <div class="{$depWrapperClass}" id="admin_field_dependent_field_wrapper_{$key}_{$optionId}">
    <span class="field_extraoptions_explain">
      {$translate_extra_questions}
      <span>
      [ <a class="dep_add_field_link" href="javascript:void(0);">{$translate->_("add question")}</a> ]
      &nbsp;
      [ <a class="dep_hide_field_link" href="javascript:void(0);" onclick="void(0);" onmousedown="void(0);">{$translate->_("hide questions")}</a> ]
      </span>
    </span>
EOF;

    $dependentMaps = Engine_Api::_()->fields()->getFieldsMaps($option->getFieldType())->getRowsMatching('option_id', $option->option_id);
    //if( !empty($dependentFields) ) {
      $content .= '<ul class="admin_fields">';
      foreach( $dependentMaps as $map ) {
        $content .= $this->view->adminFieldMeta($map);
      }
      $content .= '</ul>';
    //}

    $content .= "</div>";

    return $content;
  }

  protected function _generateClassNames($key, $prefix = '')
  {
    list($parent_id, $option_id, $child_id) = explode('_', $key);
    return
      $prefix . 'parent_' . $parent_id . ' ' .
      $prefix . 'option_' . $option_id . ' ' .
      $prefix . 'child_' . $child_id
      ;
  }
}