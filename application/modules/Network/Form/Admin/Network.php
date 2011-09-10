<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Network.php 8544 2011-03-02 00:15:57Z jung $
 * @author     Sami
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Network_Form_Admin_Network extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttrib('id', 'admin-form')
      ->setMethod('POST')
      ->setAction($_SERVER['REQUEST_URI'])
        ->setTitle('Create Network')
        ->setDescription('NETWORK_FORM_ADMIN_NETWORK_DESCRIPTION');

    // Set up form title/params
    //if( !$isCreate ) {
    //  $this->setTitle('Edit Network');
    //}
    
    // set up hidden param
    //if( !$isCreate ) {
    //  $network = Engine_Api::_()->network()->getNetwork($this->_networkIdentity);
    //} else {
    //  $network = null;
    //}

    // init name
    $this->addElement('Text', 'title', array(
      'label' => 'Name',
      'required' => true,
      'allowEmpty' => false,
    ));

    // init description
    $this->addElement('Text', 'description', array(
      'label' => 'Description',
      'validators' => array(
        array('StringLength', true, array(6, 32))
      ),
    ));

    // init assignment
    $this->addElement('Radio', 'assignment', array(
      'label' => 'Member Assignment',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        '0' => 'Members can choose to join this network at any time',
        '1' => 'Members are automatically assigned to this network',
        '2' => 'Members must be assigned to this network by an administrator'
      )
    ));
    
    // init field_id
    $this->addElement('Select', 'field_id', array(
      'label' => 'Related Profile Question',
      //'required' => true,
      //'allowEmpty' => false,
      'multiOptions' => array(
        '' => '',
      )
    ));

    // init fields
    $fieldsMeta = Engine_Api::_()->fields()->getFieldsMeta("user");
    $fieldTypes = array();
    foreach( $fieldsMeta as $meta ) {
      $info = Engine_Api::_()->fields()->getFieldInfo($meta->type);
      $genericType = $meta->type;
      if( !empty($info['base']) ) {
        $genericType = $info['base'];
      }
      $id = 'field_pattern_' . $meta->field_id;
      $pattern_type = null;

      switch( $genericType ) {
        // Select
        case 'select':
        case 'radio':
        case 'multiselect':
        case 'multi_checkbox':
          $pattern_type = 'select';

          // Get options
          $multiOptions = array();
          if( $meta->type == 'country' ) {
            $tmp = new Fields_Form_Element_Country('tmp');
            $multiOptions = $tmp->getMultiOptions();
          } else if( $meta->type != $genericType ) {
            //die('whoops');
            $elOpt = $meta->getElementParams(null);
            $multiOptions = $elOpt['options']['multiOptions'];
          } else {
            foreach( $meta->getOptions() as $option ) {
              $multiOptions[$option->option_id] = $option->label;
            }
          }
          if( empty($multiOptions) ) continue;
          
          $this->addElement('Multiselect', $id, array(
            'label' => 'Matching Value',
            'multiOptions' => $multiOptions,
            //'style' => 'display: none',
          ));
          break;
        
        // Text
        case 'text':
        case 'textarea':
          $pattern_type = 'text';
          $this->addElement('Textarea', $id, array(
            'label' => 'Matching Value',
            //'style' => 'display: none',
          ));
          break;

        // Range - birthday
        case 'date':
        case 'birthdate':
        case 'birthday':
          $pattern_type = 'date';
          $subform = new Zend_Form_SubForm(array(
            //'style' => 'display: none',
          ));
          Engine_Form::enableForm($subform);

          $subform->addElement('Date', 'min', array(
            'label' => 'From:',
          ));
          $subform->addElement('Date', 'max', array(
            'label' => 'To:',
          ));

          $this->addSubForm($subform, $id);
          //$this->$id->getDecorator();
          break;

        // Range
        case 'integer':
        case 'float':
          $pattern_type = 'range';
          $subform = new Zend_Form_SubForm(array(
            //'style' => 'display: none',
          ));
          Engine_Form::enableForm($subform);

          $subform->addElement($genericType, 'min', array(
            'label' => 'From:',
          ));
          $subform->addElement($genericType, 'max', array(
            'label' => 'To:',
          ));

          $this->addSubForm($subform, $id);
          break;

        // Unknown
        default:
          continue 2;
          break;
      }

      $el = $this->$id;
      if( $el instanceof Zend_Form_SubForm ) {
        $el->setDecorators(array(
          array('FormElements'),
          array('HtmlTag', array('tag' => 'div', 'class' => 'form-wrapper network_field_container', 'id' => $id . '-wrapper', 'style' => 'display:none')),
        ));
      } else if ( $el instanceof Zend_Form_Element ) {
        $el->getDecorator('HtmlTag2')->setOption('class', 'form-wrapper network_field_container')->setOption('style', 'display:none');
      } else {
        continue;
      }
      
      $fieldTypes[$meta->field_id] = $pattern_type;
      $this->field_id->addMultiOption($meta->field_id, $meta->label);
    }

    // Field types
    $this->addElement('Hidden', 'types', array(
      'value' => Zend_Json::encode($fieldTypes),
    ));

    // Add invisible
    $this->addElement('Checkbox', 'hide', array(
      'label' => 'Yes, hide membership for this network.',
      'description' => 'Invisible?',
    ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'ignore' => true,
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'network', 'controller' => 'manage', 'action' => 'index'), 'admin_default', true),
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}