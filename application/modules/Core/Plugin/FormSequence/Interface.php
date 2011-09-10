<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Interface.php 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
interface Core_Plugin_FormSequence_Interface
{
  public function setRegistry($registry);
  
  public function getName();
  
  public function getForm();

  public function setForm(Zend_Form $form);

  public function getScript();

  public function setScript($script);
  
  public function getSession();

  public function setSession(Zend_Session_Namespace $session);

  /**
   * This is called to check if the plugin needs to be executed
   *
   * @return bool
   */
  public function isActive();

  /**
   * Set the active status of the plugin
   * @param bool $flag
   */
  public function setActive($flag = false);
  
  /**
   * This is called when it is this plugin's turn to be rendered
   */
  public function onView();

  /**
   * This is called when the plugin's form is posted
   * Return true when plugin is done, false if failed validation/more pages/etc
   */
  public function onSubmit(Zend_Controller_Request_Abstract $request);

  /**
   * This is called when all processing is done and the data should be saved
   */
  public function onProcess();
}