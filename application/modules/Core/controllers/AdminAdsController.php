<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Core_AdminAdsController extends Core_Controller_Action_Admin
{
  // Ad Campaign actions
  
  public function indexAction()
  {
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

    $table = Engine_Api::_()->getDbtable('adcampaigns', 'core');
    $select = $table->select()
      ->order('adcampaign_id DESC');
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    //$paginator->setItemCountPerPage(1);

    // Build a list of statuses for each ad
    $status = array();
    foreach( $paginator as $item ) {
      if( !$item->status ) {
        $status[$item->getIdentity()][] = $this->view->translate('Paused');
      }
      if( !$item->hasStarted() ) {
        $status[$item->getIdentity()][] = $this->view->translate('Not Started');
      }
      if( $item->hasExpired() ) {
        $status[$item->getIdentity()][] = $this->view->translate('Ended');
      }
      if( $item->hasReachedViewLimit() ) {
        $status[$item->getIdentity()][] = $this->view->translate('Reached View Limit');
      }
      if( $item->hasReachedClickLimit() ) {
        $status[$item->getIdentity()][] = $this->view->translate('Reached Click Limit');
      }
      if( $item->hasReachedCtrLimit() ) {
        $status[$item->getIdentity()][] = $this->view->translate('Reached CTR Limit');
      }
      if( empty($status[$item->getIdentity()]) ) {
        $status[$item->getIdentity()][] = $this->view->translate('Active');
      }
    }
    $this->view->status = $status;
  }

  public function createAction()
  {
    $this->view->navigation = $this->_helper->api()
      ->getApi('menus', 'core')
      ->getNavigation('core_admin_main');

    $this->view->form = $form = new Core_Form_Admin_Ads_Create();
   
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $params = $form->getValues();
      $campaign = $this->_helper->api()->getDbtable('Adcampaigns', 'core')->createRow();
      $campaign->setFromArray($params);
      $campaign->network = Zend_Json_Encoder::encode($params['ad_networks']);
      $campaign->level = Zend_Json_Encoder::encode($params['ad_levels']);
      $campaign->status = 1;
      $campaign->save();

      // redirect to manage page for now
      $this->_helper->redirector->gotoRoute(array('action' => 'manageads', 'id'=>$campaign->adcampaign_id));
    }

  }

  public function editAction()
  {
    $id = $this->_getParam('id', null);
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('adcampaign_admin_main', array('params'=>array('id'=>$id)), 'adcampaign_admin_main_edit');

    $this->view->form = $form = new Core_Form_Admin_Ads_Edit();
    $this->view->campaign = $campaign = Engine_Api::_()->getItem('core_adcampaign', $id);

    // Save values
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $params = $form->getValues();
      //$params['user_id'] = $this->_helper->api()->user()->getViewer()->getIdentity();
      //$announcement = $this->_helper->api()->getDbtable('announcements', 'announcement')->createRow();
      $campaign->setFromArray($params);

      $selected_levels = $this->_getParam('ad_levels');
      $selected_networks = $this->_getParam('ad_networks');

      $campaign->network = Zend_Json_Encoder::encode($selected_networks);
      $campaign->level = Zend_Json_Encoder::encode($selected_levels);

      $campaign->save();
      //$this->_helper->redirector->gotoRoute(array('action' => 'index'));
      //$form->addMessage('Changes Saved!');
    }

    // Initialize values
    else
    {
      $form->populate($campaign->toArray());

      // if end_time is not set make sure the form value is populated properly
      if( $campaign->end_settings == 0 ) {
        $form->end_time->setValue(0);
      }
      // set the networks & level values
      //$networks = Engine_Api::_()->network()->getNetwork(NULL, NULL);
      $networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll();
      if( $networks ) {
        if( ($ad_networks = $form->getElement('ad_networks')) ) {
          $ad_networks->setValue(Zend_Json_Decoder::decode($campaign->network));
        }
      }
      if( ($levels = $form->getElement('ad_levels')) ) {
        $levels->setValue(Zend_Json_Decoder::decode($campaign->level));
      }

      /*
       * $this->view->selected_levels = $selected_levels = Zend_Json_Decoder::decode($campaign->level);
       * $this->view->selected_networks = $selected_networks = Zend_Json_Decoder::decode($campaign->network);
       */
    }
  }

  public function deleteAction()
  {
    // delete associated advertisements

    // remove the ad campaing
    $this->view->form = $form = new Core_Form_Admin_Ads_Delete();
    $id = $this->_getParam('id', null);

    if( $id )
    {
      $form->adcampaign_id->setValue($id);
    }

    if( $this->getRequest()->isPost() )
    {
      $table = $this->_helper->api()->getDbtable('Adcampaigns', 'core');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        $campaign = Engine_Api::_()->getItem('core_adcampaign', $id);
        $ads = $campaign->getAds();
        foreach($ads as $ad){
          Engine_Api::_()->getApi('Ad', 'core')->deleteAd($ad);
        }
        $campaign->delete();
        
        $db->commit();

        $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array(Zend_Registry::get('Zend_Translate')->_("Ad Campaign Deleted"))
        ));
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
    }
  }
 
  public function statusAction()
  {
    $id = $this->_getParam('adcampaign_id', null);
    $table = $this->_helper->api()->getDbtable('Adcampaigns', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $campaign = Engine_Api::_()->getItem('core_adcampaign', $id);

      // make status paused or started depending on current setting
      if($campaign->status){
        $campaign->status =0;
      }
      else $campaign->status = 1;
      $campaign->save();

      $db->commit();

    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function manageadsAction()
  {
    $this->view->campaign_id = $id = $this->_getParam('id', null);
    $this->view->campaign = $campaign = Engine_Api::_()->getItem('core_adcampaign', $id);
    
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('adcampaign_admin_main',
          array('params' => array('id' => $campaign->adcampaign_id)),
          'adcampaign_admin_main_manageads');
    
    $table = Engine_Api::_()->getDbtable('ads', 'core');
    $select = $table->select()->where('ad_campaign = ?', $campaign->adcampaign_id);
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    //$paginator->setItemCountPerPage(1);
  }

  // Ad action actions
  public function createadAction()
  {
   // die('here');
    if( isset($_GET['ul']) || isset($_FILES['Filedata']) ) return $this->_forward('upload-photo', null, null, array('format' => 'json'));

    $id = $this->_getParam('id', null);
    $this->view->campaign = $campaign = Engine_Api::_()->getItem('core_adcampaign', $id);

    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('adcampaign_admin_main', array('params'=>array('id'=>$id)), 'adcampaign_admin_main_manageads');

    $this->view->form = $form = new Core_Form_Admin_Ads_Ad();

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $params = $form->getValues();
      $ad = $this->_helper->api()->getDbtable('Ads', 'core')->createRow();
      $ad->setFromArray($params);
      $ad->ad_campaign = $id;

      $ad->save();

      // redirect to manage page for now
      $this->_helper->redirector->gotoRoute(array('action' => 'manageads', 'id'=>$id));
    }
  }

  public function editadAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Ads_Adedit();
    $id = $this->_getParam('id', null);
    $campaign = Engine_Api::_()->getItem('core_ad', $id);

    // Save values
    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {
      $params = $form->getValues();
      //$params['user_id'] = $this->_helper->api()->user()->getViewer()->getIdentity();
      //$announcement = $this->_helper->api()->getDbtable('announcements', 'announcement')->createRow();
      $campaign->setFromArray($params);
      $campaign->save();

      $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format'=> 'smoothbox',
      'messages' => array(Zend_Registry::get('Zend_Translate')->_("Advertisement Edited."))
      ));
    }

    // Initialize values
    else
    {
      $form->populate($campaign->toArray());
    }
  }

  public function deleteadAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Ads_Addelete();
    $id = $this->_getParam('id', null);

    if( $id )
    {
      $form->ad_id->setValue($id);
    }

    if( $this->getRequest()->isPost() )
    {
      $table = $this->_helper->api()->getDbtable('Ads', 'core');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        $ad = Engine_Api::_()->getItem('core_ad', $id);
        Engine_Api::_()->getApi('Ad', 'core')->deleteAd($ad);
        $db->commit();

        $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array(Zend_Registry::get('Zend_Translate')->_("Advertisement Deleted."))
        ));
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
    }
  }

  // code to handle upload stuff

  public function uploadPhotoAction()
  {
    if( !$this->_helper->requireUser()->checkRequire() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Max file size limit exceeded (probably).");
      return;
    }

    if( !$this->getRequest()->isPost() )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Invalid request method");
      return;
    }

    $values = $this->getRequest()->getPost();
    if( empty($values['Filename']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("No file");
      return;
    }

    if( !isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) )
    {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_("Invalid Upload");
      return;
    }
    $table = $this->_helper->api()->getDbtable('Adphotos', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();

      $params = array(
        'owner_type' => 'user',
        'owner_id' => $viewer->getIdentity()
      );
      $photo_id = Engine_Api::_()->getApi('Ad', 'core')->createPhoto($params, $_FILES['Filedata'])->adphoto_id;

      $this->view->status = true;
      $this->view->name = $_FILES['Filedata']['name'];
      $this->view->photo_id = $photo_id;
      $this->view->photo_url = "<a href='' target='_blank'><img src='".Engine_Api::_()->getItem('core_adphoto', $photo_id)->getPhotoUrl()."'/></a>";


      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      $this->view->status = false;
      $this->view->error = 'An error occurred.'.$e;
      // throw $e;
      return;
    }
  }

  public function previewAction(){
    $id = $this->_getParam('id', null);
    $campaign = Engine_Api::_()->getItem('core_ad', $id);
    $photo_id = $campaign->photo_id;

    $preview = $campaign->html_code;

    $this->view->preview = $preview;

  }

  public function removephotoAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    
    $photo_id= (int) $this->_getParam('photo_id');
    $photo = Engine_Api::_()->getItem('core_adphoto', $photo_id);

    if( !$this->getRequest()->isPost() ) {
      return;
    }
    
    $db = $photo->getTable()->getAdapter();
    $db->beginTransaction();

    try
    {
      $photo->delete();
      // @todo need to delete it out of storage system
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
  }

  public function deleteselectedAction()
  {
    $this->view->ids = $ids = $this->_getParam('ids', null);
    $confirm = $this->_getParam('confirm', false);
    $this->view->count = count(explode(",", $ids));

    // Save values
    if( $this->getRequest()->isPost() && $confirm == true )
    {
      $ids_array = explode(",", $ids);
      foreach( $ids_array as $id ){
        $campaign = Engine_Api::_()->getItem('core_adcampaign', $id);
        $ads = $campaign->getAds();
        foreach($ads as $ad){
          Engine_Api::_()->getApi('Ad', 'core')->deleteAd($ad);
        }
        $campaign->delete();
      }

      $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }
}