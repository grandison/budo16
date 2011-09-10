<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: PhpSapi.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_PhpSapi extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'badSapi' => 'Unsupported PHP SAPI type.',
  );

  protected $_messageVariables = array(
    'allowed' => '_allowed',
    'sapi_type' => '_sapiType',
  );
  
  protected $_types = array(
    'aolserver',
    'apache',
    'apache2filter',
    'apache2handler',
    'caudium',
    'cgi',
    'cgi-fcgi',
    'cli',
    'continuity',
    'embed',
    'isapi',
    'litespeed',
    'milter',
    'nsapi',
    'phttpd',
    'pi3web',
    'roxen',
    'thttpd',
    'tux',
    'webjame',
  );

  protected $_allowed;

  protected $_sapiType;

  public function setAllowed($allowed)
  {
    $this->_allowed = $allowed;
    return $this;
  }

  public function getAllowed()
  {
    return $this->_allowed;
  }

  public function execute()
  {
    $sapi = php_sapi_name();
    $allowed = $this->getAllowed();

    if( !empty($allowed) && is_array($allowed) ) {
      if( !in_array($sapi, $allowed) ) {
        return $this->_error('badSapi');
      }
    }
  }
}