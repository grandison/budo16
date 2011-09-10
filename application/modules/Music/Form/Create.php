<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8110 2010-12-22 21:03:38Z char $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Music_Form_Create extends Engine_Form
{
  protected $_playlist;

  protected $_roles = array(
    'everyone'            => 'Everyone',
    'registered'          => 'All Registered Members',
    'owner_network'       => 'Friends and Networks',
    'owner_member_member' => 'Friends of Friends',
    'owner_member'        => 'Friends Only',
    'owner'               => 'Just Me'
  );

  public function init()
  {
    $auth = Engine_Api::_()->authorization()->context;
    $user = Engine_Api::_()->user()->getViewer();

    
    // Init form
    $this
      ->setTitle('Add New Songs')
      ->setDescription('Choose music from your computer to add to this playlist.')
      ->setAttrib('id',      'form-upload-music')
      ->setAttrib('name',    'playlist_create')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    // Init name
    $this->addElement('Text', 'title', array(
      'label' => 'Playlist Name',
      'maxlength' => '63',
      'filters' => array(
        //new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '63')),
      )
    ));

    // Init descriptions
    $this->addElement('Textarea', 'description', array(
      'label' => 'Playlist Description',
      'maxlength' => '300',
      'filters' => array(
        'StripTags',
        //new Engine_Filter_HtmlSpecialChars(),
        new Engine_Filter_Censor(),
        new Engine_Filter_StringLength(array('max' => '300')),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    // Init search checkbox
    $this->addElement('Checkbox', 'search', array(
      'label' => "Show this playlist in search results",
      'value' => 1,
      'checked' => true,
    ));


    // AUTHORIZATIONS
    $availableLabels = $this->_roles;

    // Element: auth_view
    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('music_playlist', $user, 'auth_view');
    $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));

    if( !empty($viewOptions) && count($viewOptions) >= 1 ) {
      // Make a hidden field
      if(count($viewOptions) == 1) {
        $this->addElement('hidden', 'auth_view', array('value' => key($viewOptions)));
      // Make select box
      } else {
        $this->addElement('Select', 'auth_view', array(
            'label'        => 'Privacy',
            'description'  => 'Who may see this playlist?',
            'multiOptions' => $viewOptions,
            'value'        => key($viewOptions),
        ));
        $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
      }
    }

    // Element: auth_comment
    $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('music_playlist', $user, 'auth_comment');
    $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));

    if( !empty($commentOptions) && count($commentOptions) >= 1 ) {
      // Make a hidden field
      if(count($commentOptions) == 1) {
        $this->addElement('hidden', 'auth_comment', array('value' => key($commentOptions)));
      // Make select box
      } else {
        $this->addElement('Select', 'auth_comment', array(
            'label'        => 'Comment Privacy',
            'description'  => 'Who may post comments on this playlist?',
            'multiOptions' => $commentOptions,
            'value'        => key($commentOptions),
        ));
        $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
      }
    }
    

    // Init playlist art
    $this->addElement('File', 'art', array(
      'label' => 'Playlist Artwork',
    ));
    $this->art->addValidator('Extension', false, 'jpg,png,gif,jpeg');

    // Init file uploader
    $fancyUpload = new Engine_Form_Element_FancyUpload('file');
    $fancyUpload->clearDecorators()
                ->addDecorator('FormFancyUpload')
                ->addDecorator('viewScript', array(
                  'viewScript' => '_FancyUpload.tpl',
                  'placement'  => '',
                  ));
    Engine_Form::addDefaultDecorators($fancyUpload);
    $this->addElement($fancyUpload);

    // Init hidden file IDs
    $this->addElement('Hidden', 'fancyuploadfileids');


    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Music to Playlist',
      'type'  => 'submit',
    ));
  }

  public function clearUploads()
  {
    $this->getElement('fancyuploadfileids')->setValue('');
  }

  public function saveValues()
  {
    $playlist = null;
    $values   = $this->getValues();
    $translate= Zend_Registry::get('Zend_Translate');
    
    if(!empty($values['playlist_id']))
      $playlist = Engine_Api::_()->getItem('music_playlist', $values['playlist_id']);
    else {
      $playlist = $this->_playlist = Engine_Api::_()->getDbtable('playlists', 'music')->createRow();
      $playlist->title = trim($values['title']);
      if (empty($playlist->title))
        $playlist->title = $translate->_('_MUSIC_UNTITLED_PLAYLIST');
      
      $playlist->owner_type    = 'user';
      $playlist->owner_id      = Engine_Api::_()->user()->getViewer()->getIdentity();
      $playlist->description   = trim($values['description']);
      $playlist->search        = $values['search'];
      $playlist->save();
      $values['playlist_id']   = $playlist->playlist_id;

      // Assign $playlist to a Core_Model_Item
      $playlist = $this->_playlist = Engine_Api::_()->getItem('music_playlist', $values['playlist_id']);

      // get file_id list
      $file_ids = array();
      foreach (explode(' ', $values['fancyuploadfileids']) as $file_id) {
        $file_id = trim($file_id);
        if (!empty($file_id))
          $file_ids[] = $file_id;
      }

      // Attach songs (file_ids) to playlist
      if (!empty($file_ids))
        foreach ($file_ids as $file_id)
          $playlist->addSong($file_id);

      // Only create activity feed item if "search" is checked
      if ($playlist->search) {
        $activity = Engine_Api::_()->getDbtable('actions', 'activity');
        $action   = $activity->addActivity(
            Engine_Api::_()->user()->getViewer(),
            $playlist,
            'music_playlist_new',
            null,
            array('count' => count($file_ids))
        );
        if (null !== $action)
          $activity->attachActivity($action, $playlist);
      }
    }
      



    // Authorizations
    $auth      = Engine_Api::_()->authorization()->context;
    $prev_allow_comment = $prev_allow_view = false;
    foreach ($this->_roles as $role => $role_label) {
      // allow viewers
      if ($values['auth_view'] == $role || $prev_allow_view) {
        $auth->setAllowed($playlist, $role, 'view', true);
        $prev_allow_view = true;
      } else
        $auth->setAllowed($playlist, $role, 'view', 0);

      // allow comments
      if ($values['auth_comment'] == $role || $prev_allow_comment) {
        $auth->setAllowed($playlist, $role, 'comment', true);
        $prev_allow_comment = true;
      } else
        $auth->setAllowed($playlist, $role, 'comment', 0);
    }

    // Rebuild privacy
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    foreach( $actionTable->getActionsByObject($playlist) as $action ) {
      $actionTable->resetActivityBindings($action);
    }



    if (!empty($values['art']))
      $playlist->setPhoto($this->art);

    return $playlist;
  }

}
