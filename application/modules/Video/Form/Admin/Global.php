<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Global.php 8064 2010-12-15 23:10:34Z char $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Video_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $this->addElement('Text', 'video_ffmpeg_path', array(
      'label' => 'Path to FFMPEG',
      'description' => 'Please enter the full path to your FFMPEG installation. (Environment variables are not present)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ffmpeg.path', ''),
    ));
    
    $this->addElement('Text', 'video_jobs', array(
      'label' => 'Encoding Jobs',
      'description' => 'How many jobs do you want to allow to run at the same time?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.jobs', 2),
    ));

    $this->addElement('Text', 'video_page', array(
      'label' => 'Listings Per Page',
      'description' => 'How many videos will be shown per page? (Enter a number between 1 and 999)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 10),
    ));


    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}