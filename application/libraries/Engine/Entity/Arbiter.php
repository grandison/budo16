<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Entity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Arbiter.php 7904 2010-12-03 03:36:14Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Entity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Engine_Entity_Arbiter
{
  protected $_inflector = array(
    'Engine_Entity_Arbiter',
    'typeToClass'
  );

  protected $_deflector = array(
    'Engine_Entity_Arbiter',
    'classToType'
  );




  // Utiltiy

  static public function typeToClass($type)
  {
    if( !is_string($type) || empty($type) ) {
      throw new Engine_Entity_Exception('Malformed type passed to typeToClass');
    }
    
    $segments = explode('_', $type);

    // Single segments types are doubled up
    if( count($segments) == 1 ) {
      $segments[] = $segments[0];
    }

    // Inflect
    $prefix = array_shift($segments);
    array_map('ucfirst', $segments);
    $class = ucfirst($prefix) . '_Model_' . join('_', $segments);

    return $class;
  }

  static public function classToType($class)
  {
    if( !is_string($type) || empty($type) ) {
      throw new Engine_Entity_Exception('Malformed class passed to classToType');
    }

    $segments = explode('_', strtolower($class));

    if( count($segments) < 3 ) {
      throw new Engine_Entity_Exception('Malformed class passed to classToType');
    }

    $prefix = array_shift($segments);
    $slug = array_shift($segments);

    if( count($segments) == 1 && $segments[0] == $prefix ) {
      return $prefix;
    }

    return $prefix . join('_', $segments);
  }
}