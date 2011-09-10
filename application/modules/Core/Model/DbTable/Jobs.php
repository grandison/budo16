<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Jobs.php 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_Model_DbTable_Jobs extends Engine_Db_Table
{
  //protected $_serializedColumns = array('data');

  public function addJob($type, $params)
  {
    // Get job type
    $jobtype = Engine_Api::_()->getDbtable('jobTypes', 'core')->select()
      ->where('enabled = ?', 1)
      ->where('type = ?', $type)
      ->limit(1)
      ->query()
      ->fetch();

    // Missing job type
    if( !$jobtype ) {
      return false;
    }

    // Separate params from allowed columns
    $allowedColumns = array('priority');
    $data = array_intersect_key($params, array_flip($allowedColumns));
    $params = array_diff_key($params, array_flip($allowedColumns));

    // Add other data
    $data['jobtype_id'] = $jobtype['jobtype_id'];
    $data['creation_date'] = new Zend_Db_Expr('NOW()');
    $data['data'] = Zend_Json::encode($params);

    $job = $this->createRow();
    $job->setFromArray($data);
    $job->save();

    return $job;
  }
}