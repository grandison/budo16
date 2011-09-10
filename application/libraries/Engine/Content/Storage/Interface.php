<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Interface.php 7453 2010-09-23 03:59:38Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Content
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
interface Engine_Content_Storage_Interface
{
  public function loadMetaData(Engine_Content $contentAdapter, $name);
  
  public function loadContent(Engine_Content $contentAdapter, $name);
}