<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Birthdate.php 8481 2011-02-16 20:07:58Z steve $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John
 */
class Fields_Form_Element_Birthdate extends Engine_Form_Element_Birthdate
{
  protected $_metaObject;

  public function init()
  {
    parent::init();

    // Set min age
    if( !is_null($this->min_age) ) {

      // Set max year
      $date = new Zend_Date();
      $this->_yearMax = (int) $date->get(Zend_Date::YEAR) - (int) $this->min_age;

      // Add validator
      $validator = new Engine_Validate_Callback(array($this, 'validateAge'));
      $validator->setMessage('The minimum age is ' . $this->min_age . '.', 'invalid');
      $this->addValidator($validator);
    }
  }

  public function setMetaObject($meta)
  {
    $this->_metaObject = $meta;
    return $this;
  }

  public function validateAge($value)
  {
    $parts = @explode('-', $value);

    // Error if not filled out
    if( count($parts) < 3 || count(array_filter($parts)) < 3 ) {
      //$this->addError('Please fill in your birthday.');
      return false;
    }

    $value = mktime(0, 0, 0, $parts[1], $parts[2], $parts[0]);

    // Error if too low
    $date = new Zend_Date($value);
    $age = - $date->sub(time())  / 365 / 86400;

    if( $age < $this->min_age ) {
      //$this->addError('You are not old enough.');
      return false;
    }
    
    return true;
  }
}