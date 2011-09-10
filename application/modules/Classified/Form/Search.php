<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: WidgetController.php
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Classified_Form_Search extends Fields_Form_Search
{
  protected $_fieldType = 'classified';
  
  public function init()
  {
    parent::init();

    $this->loadDefaultDecorators();

    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'classifieds_browse_filters field_search_criteria',
      ))
      ->setAction($_SERVER['REQUEST_URI'])
      ->getDecorator('HtmlTag')
        ->setOption('class', 'browseclassifieds_criteria classifieds_browse_filters');

    // Generate
    //$this->generate();

    // Add custom elements
    $this->getAdditionalOptionsElement();


    /*
    foreach( $this->getFieldElements() as $fel ) {
      if( $fel instanceof Zend_Form_Element ) {
        $fel->clearDecorators();
        $fel->addDecorator('ViewHelper');
        Engine_Form::addDefaultDecorators($fel);
      } else if( $fel instanceof Zend_Form_SubForm ) {
        $fel->clearDecorators();
        $fel->setDescription('<label>' . $fel->getDescription() . '</label>');
        $fel->addDecorator('FormElements')
            ->addDecorator('HtmlTag', array('tag' => 'div', 'id'  => $fel->getName() . '-element', 'class' => 'form-element'))
            ->addDecorator('Description', array('tag' => 'div', 'class' => 'form-label', 'placement' => 'PREPEND', 'escape' => false))
            ->addDecorator('HtmlTag2', array('tag' => 'div', 'id'  => $fel->getName() . '-wrapper', 'class' => 'form-wrapper browse-range-wrapper'));
      }
    }
     * 
     */
  }

  public function getAdditionalOptionsElement()
  {
    $i = -5000;

    $this->addElement('Hidden', 'page', array(
      'order' => $i--,
    ));

    $this->addElement('Hidden', 'tag', array(
      'order' => $i--,
    ));

    $this->addElement('Hidden', 'start_date', array(
      'order' => $i--,
    ));

    $this->addElement('Hidden', 'end_date', array(
      'order' => $i--,
    ));

    $this->addElement('Text', 'search', array(
      'label' => 'Search Classifieds',
      'order' => $i--,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));

    $this->addElement('Select', 'orderby', array(
      'label' => 'Browse By',
      'multiOptions' => array(
        'creation_date' => 'Most Recent',
        'view_count' => 'Most Viewed',
      ),
      'onchange' => 'searchClassifieds();',
      'order' => $i--,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));

    $this->addElement('Select', 'show', array(
      'label' => 'Show',
      'multiOptions' => array(
        '1' => 'Everyone\'s Posts',
        '2' => 'Only My Friends\' Posts',
      ),
      'onchange' => 'searchClassifieds();',
      'order' => $i--,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));

    $this->addElement('Select', 'closed', array(
      'label' => 'Status',
      'multiOptions' => array(
        '' => 'All Listings',
        '0' => 'Only Open Listings',
        '1' => 'Only Closed Listings',
      ),
      'onchange' => 'searchClassifieds();',
      'order' => $i--,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));

    $this->addElement('Select', 'category', array(
      'label' => 'Category',
      'multiOptions' => array(
        '0' => 'All Categories',
      ),
      'onchange' => 'searchClassifieds();',
      'order' => $i--,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));



    
    $this->addElement('Checkbox', 'has_photo', array(
      'label' => 'Only Classifieds With Photos',
      'order' => 10000000,
      'decorators' => array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND', 'tag' => 'label')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));
    
    $this->addElement('Button', 'done', array(
      'label' => 'Search',
      'onclick' => 'searchClassifieds();',
      'ignore' => true,
      'order' => 10000001,
      'decorators' => array(
        'ViewHelper',
        //array('Label', array('tag' => 'span')),
        array('HtmlTag', array('tag' => 'li'))
      ),
    ));
  }
}