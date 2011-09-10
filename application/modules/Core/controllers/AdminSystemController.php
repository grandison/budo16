<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminSystemController.php 8469 2011-02-15 23:34:09Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminSystemController extends Core_Controller_Action_Admin
{
  public function init()
  {
    if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
      return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
    }
  }

  public function indexAction()
  {
    return $this->_forward('php');
    /*
    return $this->_helper->redirector->gotoRoute(array(
      'controller' => 'log',
      'action' => 'index',
    ), null, false);*/
  }

  public function logAction()
  {
    return $this->_helper->redirector->gotoRoute(array(
      'controller' => 'log',
      'action' => 'index',
    ), null, false);
  }
  
  public function phpAction()
  {
    ob_start();
    phpinfo();
    $source = ob_get_clean();

    preg_match('~<style.+?>(.+?)</style>.+?(<table.+\/table>)~ims', $source, $matches);
    $css = $matches[1];
    $source = $matches[2];

    $css = preg_replace('/[\r\n](.+?{)/iu', "\n#phpinfo \$1", $css);

    //$regex = '/'.preg_quote('<a href="http://www.php.net/">', '/').'.+?'.preg_quote('</a>', '/').'/ims';
    //$source = preg_replace($regex, '', $source);

    // strip images from phpinfo()
    $regex = '/<img .+?>/ims';
    $source = preg_replace($regex, '', $source);
    
    $regex = '/'.preg_quote('<h2>PHP License</h2>', '/').'.+$/ims';
    $source = preg_replace($regex, '', $source);

    $source = str_replace("module_Zend Optimizer", "module_Zend_Optimizer", $source);

    $this->view->style = $css;
    $this->view->content = $source;
  }

  public function apcAction()
  {
    
  }
}