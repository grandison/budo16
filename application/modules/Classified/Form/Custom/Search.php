<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Search.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Classified_Form_Custom_Search extends Fields_Form_Search
{
  protected $_type;

  public function setType($type)
  {
    $this->_type = $type;
    return $this;
  }

  public function init()
  {
    //parent::init();
    
    $this->addDecorators(array(
      'FormElements'
      ));

    $fields = Engine_Api::_()->getApi('core', 'fields')->getFieldsMeta($this->_type);
    foreach( $fields as $field )
    {
      if( !$field->search || !$field->alias )
      {
        continue;
      }

      $key = $field->alias;

      // Hack for birthday type fields
      $params = $field->getElementParams($this->_type, array('required' => false));


      // Range type fields
      if( $field->type == 'date' || $field->type == 'birthdate' || $field->type == 'float' )
      {
        $subform = new Engine_Form(array(
          'description' => $params['options']['label'],
          'elementsBelongTo'=> $key,
          'decorators' => array(
            'FormElements',
            array('Description', array('placement' => 'PREPEND', 'tag' => 'label', 'class' => 'form-label')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'integer_field form-wrapper integer_field_unselected', 'id' =>'integer-wrapper'))
          )
        ));
        //Engine_Form::enableForm($subform);
        unset($params['options']['label']);
        $params['options']['decorators'] = array('ViewHelper', array('HtmlTag', array('tag'=>'div', 'class'=>'form-element')));

        $subform->addElement($params['type'], 'min', $params['options']);
        $subform->addElement($params['type'], 'max', $params['options']);
        $this->addSubForm($subform, $key);
      }
      else
      {
        $this->addElement($params['type'], $key, $params['options']);
      }

      $element = $this->getElement($key);
    }
    $this->addElement('Button', 'done', array(
      'label' => 'Search',
      'type' => 'submit',
    ));
  }

}