<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Package
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Manifest.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Filter
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Package_Manifest extends Engine_Package_Manifest_Entity_Package
{
  public function addToArchive(Archive_Tar $archive)
  {
    // Add top level package manifest
    $archive->addString('package.json', $this->toString('json'));

    // Normal stuff
    parent::addToArchive($archive);
  }
}
