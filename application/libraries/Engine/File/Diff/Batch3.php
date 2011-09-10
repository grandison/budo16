<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Batch3.php 7244 2010-09-01 01:49:53Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_File_Diff
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_File_Diff_Batch3 extends Engine_File_Diff_Batch
{
  protected $_originalFiles;
  
  public function __construct(array $leftFiles, array $rightFiles, array $originalFiles)
  {
    if( count($originalFiles) != count($leftFiles) ) {
      throw new Engine_File_Diff_Exception("Count of left, right, and original did not match");
    }
    $this->_originalFiles = $originalFiles;
    
    parent::__construct($leftFiles, $rightFiles);
  }

  public function execute()
  {
    reset($this->_leftFiles);
    reset($this->_rightFiles);
    reset($this->_originalFiles);

    $break = false;
    while( !$break )
    {
      $left = current($this->_leftFiles);
      $right = current($this->_rightFiles);
      $original = current($this->_originalFiles);

      if( !$left && !$right && !$original ) {
        $break = true;
        continue;
      } else {
        $diff = Engine_File_Diff::factory($left, $right, $original);
        $diff->execute();
        $this->_diffs[] = $diff;
      }
      
      next($this->_leftFiles);
      next($this->_rightFiles);
      next($this->_originalFiles);
    }
    return $this;
  }
}