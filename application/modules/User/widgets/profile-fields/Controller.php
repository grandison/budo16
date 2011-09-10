<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Controller.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class User_Widget_ProfileFieldsController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Don't render this if not authorized
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !Engine_Api::_()->core()->hasSubject() ) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $subject = Engine_Api::_()->core()->getSubject('user');
    if( !$subject->authorization()->isAllowed($viewer, 'view') ) {
      return $this->setNoRender();
    }

    // Load fields view helpers
    $view = $this->view;
    $view->addHelperPath(APPLICATION_PATH . '/application/modules/Fields/View/Helper', 'Fields_View_Helper');

    // Values
    $this->view->fieldStructure = $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($subject);
    if( count($fieldStructure) <= 1 ) { // @todo figure out right logic
      return $this->setNoRender();
    }
    return;

    $valuesStructure = array();
    $valueCount = 0;
    foreach( $fieldStructure as $index => $field )
    {
      $value = $field->getValue($subject);
      if( !$field->display )
      {
        continue;
      }

      if( $field->isHeading() )
      {
        $valuesStructure[] = array(
          'alias' => null,
          'label' => $field->label,
          'value' => $field->label,
          'heading' => true,
          'type' => $field->type,
        );
      }

      else if( $value && !empty($value->value) )
      {
        $valueCount++;

        $label = Engine_Api::_()->fields()
                 ->getFieldsOptions($subject)
                 ->getRowMatching('option_id', $value->value);
        $label = $label
                 ? $label->label
                 : $value->value;

        $valuesStructure[] = array(
          'alias' => $field->alias,
          'label' => $field->label,
          'value' => $label,
          'heading' => false,
          'type' => $field->type,
        );
      }
    }
    $this->view->user   = $subject;
    $this->view->fields = $valuesStructure;
    $this->view->valueCount = $valueCount;


    // Do not render if nothing to show
    if( $valueCount <= 0 ) {
      return $this->setNoRender();
    }
  }
}