<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: AdminStatsController.php 8838 2011-04-11 21:17:28Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminStatsController extends Core_Controller_Action_Admin
{
  protected $_periods = array(
    Zend_Date::DAY, //dd
    Zend_Date::WEEK, //ww
    Zend_Date::MONTH, //MM
    Zend_Date::YEAR, //y
  );

  protected $_allPeriods = array(
    Zend_Date::SECOND,
    Zend_Date::MINUTE,
    Zend_Date::HOUR,
    Zend_Date::DAY,
    Zend_Date::WEEK,
    Zend_Date::MONTH,
    Zend_Date::YEAR,
  );

  protected $_periodMap = array(
    Zend_Date::DAY => array(
      Zend_Date::SECOND => 0,
      Zend_Date::MINUTE => 0,
      Zend_Date::HOUR => 0,
    ),
    Zend_Date::WEEK => array(
      Zend_Date::SECOND => 0,
      Zend_Date::MINUTE => 0,
      Zend_Date::HOUR => 0,
      Zend_Date::WEEKDAY_8601 => 1,
    ),
    Zend_Date::MONTH => array(
      Zend_Date::SECOND => 0,
      Zend_Date::MINUTE => 0,
      Zend_Date::HOUR => 0,
      Zend_Date::DAY => 1,
    ),
    Zend_Date::YEAR => array(
      Zend_Date::SECOND => 0,
      Zend_Date::MINUTE => 0,
      Zend_Date::HOUR => 0,
      Zend_Date::DAY => 1,
      Zend_Date::MONTH => 1,
    ),
  );
  
  public function indexAction()
  {
    // Get types
    $statsTable = Engine_Api::_()->getDbtable('statistics', 'core');
    $select = new Zend_Db_Select($statsTable->getAdapter());
    $select
      ->from($statsTable->info('name'), 'type')
      ->distinct(true)
      ;

    $data = $select->query()->fetchAll();
    $types = array();
    foreach( $data as $datum ) {
      $type = $datum['type'];
      $fancyType = '_CORE_ADMIN_STATS_' . strtoupper(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $type), '_'));
      $types[$type] = $fancyType;
    }
    
    $this->view->filterForm = $filterForm = new Core_Form_Admin_Statistics_Filter();
    $filterForm->type->setMultiOptions($types);
  }

  public function referrersAction()
  {
    $table = Engine_Api::_()->getDbtable('referrers', 'core');
    $select = $table->select()
      ->order('value ASC')
      ->limit(100);
    $this->view->referrers = $table->fetchAll();
  }

  public function clearReferrersAction()
  {
    if( $this->getRequest()->isPost() ) {
      $table = Engine_Api::_()->getDbtable('referrers', 'core');
      $table->delete(array('1 = ?' => 1));
      //return $this->_helper->redirector->gotoRoute(array('action' => 'referrers'));
    }
  }

  public function chartDataAction()
  {
    // Disable layout and viewrenderer
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);

    // Get params
    $type   = $this->_getParam('type');
    $start  = $this->_getParam('start');
    $offset = $this->_getParam('offset', 0);
    $mode   = $this->_getParam('mode');
    $chunk  = $this->_getParam('chunk');
    $period = $this->_getParam('period');
    $periodCount = $this->_getParam('periodCount', 1);
    //$end = $this->_getParam('end');

    // Validate chunk/period
    if( !$chunk || !in_array($chunk, $this->_periods) ) {
      $chunk = Zend_Date::DAY;
    }
    if( !$period || !in_array($period, $this->_periods) ) {
      $period = Zend_Date::MONTH;
    }
    if( array_search($chunk, $this->_periods) >= array_search($period, $this->_periods) ) {
      die('whoops');
      return;
    }

    // Validate start
    if( $start && !is_numeric($start) ) {
      $start = strtotime($start);
    }
    if( !$start ) {
      $start = time();
    }

    // Fixes issues with month view
    Zend_Date::setOptions(array(
      'extend_month' => true,
    ));

    // Get timezone
    $timezone = Engine_Api::_()->getApi('settings', 'core')
        ->getSetting('core_locale_timezone', 'GMT');
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer && $viewer->getIdentity() && !empty($viewer->timezone) ) {
      $timezone = $viewer->timezone;
    }

    // Make start fit to period?
    $startObject = new Zend_Date($start);
    $startObject->setTimezone($timezone);
    
    $partMaps = $this->_periodMap[$period];
    foreach( $partMaps as $partType => $partValue ) {
      $startObject->set($partValue, $partType);
    }

    // Do offset
    if( $offset != 0 ) {
      $startObject->add($offset, $period);
    }
    
    // Get end time
    $endObject = new Zend_Date($startObject->getTimestamp());
    $endObject->setTimezone($timezone);
    $endObject->add($periodCount, $period);
    $endObject->sub(1, Zend_Date::SECOND); // Subtract one second

    // Get data
    $statsTable = Engine_Api::_()->getDbtable('statistics', 'core');
    $statsSelect = $statsTable->select()
      ->where('type = ?', $type)
      ->where('date >= ?', gmdate('Y-m-d H:i:s', $startObject->getTimestamp()))
      ->where('date < ?', gmdate('Y-m-d H:i:s', $endObject->getTimestamp()))
      ->order('date ASC')
      ;
    $rawData = $statsTable->fetchAll($statsSelect);
    
    // Now create data structure
    $currentObject = clone $startObject;
    $nextObject = clone $startObject;
    $data = array();
    $dataLabels = array();
    $cumulative = 0;
    $previous = 0;

    do {
      $nextObject->add(1, $chunk);
      
      $currentObjectTimestamp = $currentObject->getTimestamp();
      $nextObjectTimestamp = $nextObject->getTimestamp();

      $data[$currentObjectTimestamp] = $cumulative;

      // Get everything that matches
      $currentPeriodCount = 0;
      foreach( $rawData as $rawDatum ) {
        $rawDatumDate = strtotime($rawDatum->date);
        if( $rawDatumDate >= $currentObjectTimestamp && $rawDatumDate < $nextObjectTimestamp ) {
          $currentPeriodCount += $rawDatum->value;
        }
      }

      // Now do stuff with it
      switch( $mode ) {
        default:
        case 'normal':
          $data[$currentObjectTimestamp] = $currentPeriodCount;
          break;
        case 'cumulative':
          $cumulative += $currentPeriodCount;
          $data[$currentObjectTimestamp] = $cumulative;
          break;
        case 'delta':
          $data[$currentObjectTimestamp] = $currentPeriodCount - $previous;
          $previous = $currentPeriodCount;
          break;
      }
      
      $currentObject->add(1, $chunk);
    } while( $currentObject->getTimestamp() < $endObject->getTimestamp() );

    // Reprocess label
    $labelStrings = array();
    $labelDate = new Zend_Date();
    foreach( $data as $key => $value ) {
      $labelDate->set($key);
      $labelStrings[] = $this->view->locale()->toDate($labelDate, array('size' => 'short')); //date('D M d Y', $key);
    }

    // Let's expand them by 1.1 just for some nice spacing
    $minVal = min($data);
    $maxVal = max($data);
    $minVal = floor($minVal * ($minVal < 0 ? 1.1 : (1 / 1.1)) / 10) * 10;
    $maxVal = ceil($maxVal * ($maxVal > 0 ? 1.1 : (1 / 1.1)) / 10) * 10;

    // Remove some labels if there are too many
    $xlabelsteps = 1;
    if( count($data) > 10 ) {
      $xlabelsteps = ceil(count($data) / 10);
    }

    // Remove some grid lines if there are too many
    $xsteps = 1;
    if( count($data) > 100 ) {
      $xsteps = ceil(count($data) / 100);
    }

    // Create base chart
    require_once 'OFC/OFC_Chart.php';

    // Make x axis labels
    $x_axis_labels = new OFC_Elements_Axis_X_Label_Set();
    $x_axis_labels->set_steps( $xlabelsteps );
    $x_axis_labels->set_labels( $labelStrings );

    // Make x axis
    $labels = new OFC_Elements_Axis_X();
    $labels->set_labels( $x_axis_labels );
    $labels->set_colour("#416b86");
    $labels->set_grid_colour("#dddddd");
    $labels->set_steps($xsteps);

    // Make y axis
    $yaxis = new OFC_Elements_Axis_Y();
    $yaxis->set_range($minVal, $maxVal/*, $steps*/);
    $yaxis->set_colour("#416b86");
    $yaxis->set_grid_colour("#dddddd");
    
    // Make data
    $graph = new OFC_Charts_Line();
    $graph->set_values( array_values($data) );
    $graph->set_colour("#5ba1cd");

    // Make title
    $locale = Zend_Registry::get('Locale');
    $translate = Zend_Registry::get('Zend_Translate');
    $titleStr = $translate->_('_CORE_ADMIN_STATS_' . strtoupper(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $type), '_')));
    $title = new OFC_Elements_Title( $titleStr . ': '. $this->view->locale()->toDateTime($startObject) . ' to ' . $this->view->locale()->toDateTime($endObject) );
    $title->set_style( "{font-size: 14px;font-weight: bold;margin-bottom: 10px; color: #777777;}" );

    // Make full chart
    $chart = new OFC_Chart();
    $chart->set_bg_colour('#ffffff');

    $chart->set_x_axis($labels);
    $chart->add_y_axis($yaxis);
    $chart->add_element($graph);
    $chart->set_title( $title );
    
    // Send
    $this->getResponse()->setBody( $chart->toPrettyString() );
  }

  public function chartImageUploadAction()
  {
    // The flash callback is not working, so this is no good for now
    return;
    
    // Disable layout and viewrenderer
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);

    $upload_path = APPLICATION_PATH . '/public/temporary';
    $file_name = preg_replace('/[^a-z0-9_.-]/', '', $this->_getParam('file'));
    $response = '';

    if( $file_name && file_exists($upload_path) && is_writeable($upload_path) )
    {
      file_put_contents($upload_path . '/' . $file_name, $GLOBALS["HTTP_RAW_POST_DATA"]);
      $response = 'Saving your image to: ' . $this->view->baseUrl() . 'public/temporary/' . $file_name;
    }
    else
    {
      $response = 'Upload failed';
    }
    
    echo $response;
    
    $this->getResponse()->setBody( $response );
  }


  /*
  public function chartImageReflectAction()
  {
    // Disable layout and viewrenderer
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender(true);

    // Reflect the image data
    $name = $this->_getParam('name');
    $data = $this->_getParam('data');

    $this->getResponse()
      ->setHeader('Content-type', 'image/png', true)
      ->setHeader('Content-Disposition', 'attachment; filename="'.urlencode($name).'"', true)
      ->setBody($data)
      ;
  }
  */
}