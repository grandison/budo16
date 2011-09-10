<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Edit.php 8110 2010-12-22 21:03:38Z char $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Form_Edit extends Engine_Form
{
  public function init()
  {
    $this->setTitle('Edit Video')
      ->setAttrib('name', 'video_edit');
    $user = Engine_Api::_()->user()->getViewer();

    $this->addElement('Text', 'title', array(
      'label' => 'Video Title',
      'required' => true,
      'notEmpty' => true,
      'validators' => array(
        'NotEmpty',
      ),
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '100'))
      )
    ));
    $this->title->getValidator('NotEmpty')->setMessage("Please specify an video title");
    
    // init tag
    $this->addElement('Text', 'tags',array(
      'label'=>'Tags (Keywords)',
      'autocomplete' => 'off',
      'description' => 'Separate tags with commas.'
    ));
    $this->tags->getDecorator("Description")->setOption("placement", "append");

    $this->addElement('Textarea', 'description', array(
      'label' => 'Video Description',
      'rows' => 2,
      'maxlength' => '512',
      'filters' => array(
        'StripTags',
        //new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_Censor(),
        new Engine_Filter_EnableLinks(),
      )
    ));

    // prepare categories
    $categories = Engine_Api::_()->video()->getCategories();
    $categories_prepared[0]= "";
    foreach ($categories as $category){
      $categories_prepared[$category->category_id]= $category->category_name;
    }

    // category field
    $this->addElement('Select', 'category_id', array(
          'label' => 'Category',
          'multiOptions' => $categories_prepared
        ));
    
    $this->addElement('Checkbox', 'search', array(
      'label' => "Show this video in search results",
    ));


    // Privacy
    $availableLabels = array(
      'everyone'            => 'Everyone',
      'registered'          => 'All Registered Members',
      'owner_network'       => 'Friends and Networks',
      'owner_member_member' => 'Friends of Friends',
      'owner_member'        => 'Friends Only',
      'owner'               => 'Just Me'
    );

    
    // View
    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'auth_view');
    $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));
    if( empty($viewOptions) ) {
      $viewOptions = $availableLabels;
    }

    if( !empty($viewOptions) && count($viewOptions) >= 1 ) {
      // Make a hidden field
      if(count($viewOptions) == 1) {
        $this->addElement('hidden', 'auth_view', array('value' => key($viewOptions)));
      // Make select box
      } else {
        $this->addElement('Select', 'auth_view', array(
            'label' => 'Privacy',
            'description' => 'Who may see this video?',
            'multiOptions' => $viewOptions,
            'value' => key($viewOptions),
        ));
        $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
      }
    }
    
    // Comment
    $commentOptions =(array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'auth_comment');
    $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));
    if( empty($commentOptions) ) {
      $commentOptions = $availableLabels;
    }

    if( !empty($commentOptions) && count($commentOptions) >= 1 ) {
      // Make a hidden field
      if(count($commentOptions) == 1) {
        $this->addElement('hidden', 'auth_comment', array('value' => key($commentOptions)));
      // Make select box
      } else {
        $this->addElement('Select', 'auth_comment', array(
            'label' => 'Comment Privacy',
            'description' => 'Who may post comments on this video?',
            'multiOptions' => $commentOptions,
            'value' => key($commentOptions),
        ));
        $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
      }
    }

    
    // Element: execute
    $this->addElement('Button', 'execute', array(
      'label' => 'Save Video',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'video_general', true),
      'onclick' => '',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // DisplayGroup: buttons
    $this->addDisplayGroup(array(
      'execute',
      'cancel',
    ), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper'
      ),
    ));
  }
}
