<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Create.php 8110 2010-12-22 21:03:38Z char $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Blog_Form_Create extends Engine_Form
{
  public $_error = array();

  public function init()
  {   
    $this->setTitle('Write New Entry')
      ->setDescription('Compose your new blog entry below, then click "Post Entry" to publish the entry to your blog.')
      ->setAttrib('name', 'blogs_create');
    $user = Engine_Api::_()->user()->getViewer();
    $user_level = Engine_Api::_()->user()->getViewer()->level_id;

    $this->addElement('Text', 'title', array(
      'label' => 'Title',
      'allowEmpty' => false,
      'required' => true,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '63'))
    )));

    // init to
    $this->addElement('Text', 'tags',array(
      'label'=>'Tags (Keywords)',
      'autocomplete' => 'off',
      'description' => 'Separate tags with commas.',
      'filters' => array(
        new Engine_Filter_Censor(),
      ),
    ));
    $this->tags->getDecorator("Description")->setOption("placement", "append");
    
    // prepare categories
    $categories = Engine_Api::_()->blog()->getCategories();
    if (count($categories)!=0){
      $categories_prepared[0]= "";
      foreach ($categories as $category){
        $categories_prepared[$category->category_id]= $category->category_name;
      }

      // category field
      $this->addElement('Select', 'category_id', array(
            'label' => 'Category',
            'multiOptions' => $categories_prepared
          ));
    }
    
    $this->addElement('Select', 'draft', array(
      'label' => 'Status',
      'multiOptions' => array("0"=>"Published", "1"=>"Saved As Draft"),
      'description' => 'If this entry is published, it cannot be switched back to draft mode.'
    ));
    $this->draft->getDecorator('Description')->setOption('placement', 'append');

    $allowed_html = Engine_Api::_()->authorization()->getPermission($user_level, 'blog', 'auth_html');
    
    $this->addElement('TinyMce', 'body', array(
      'disableLoadDefaultDecorators' => true,
      'required' => true,
      'allowEmpty' => false,
      'decorators' => array(
        'ViewHelper'
      ),
      'filters' => array(
        new Engine_Filter_Censor(),
        new Engine_Filter_Html(array('AllowedTags'=>$allowed_html))),
    ));

    $this->addElement('Checkbox', 'search', array(
      'label' => 'Show this blog entry in search results',
      'value' => 1,
    ));

    $availableLabels = array(
      'everyone'            => 'Everyone',
      'registered'          => 'All Registered Members',
      'owner_network'       => 'Friends and Networks',
      'owner_member_member' => 'Friends of Friends',
      'owner_member'        => 'Friends Only',
      'owner'               => 'Just Me'
    );

    // Element: auth_view
    $viewOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('blog', $user, 'auth_view');
    $viewOptions = array_intersect_key($availableLabels, array_flip($viewOptions));

    if( !empty($viewOptions) && count($viewOptions) >= 1 ) {
      // Make a hidden field
      if(count($viewOptions) == 1) {
        $this->addElement('hidden', 'auth_view', array('value' => key($viewOptions)));
      // Make select box
      } else {
        $this->addElement('Select', 'auth_view', array(
            'label' => 'Privacy',
            'description' => 'Who may see this blog entry?',
            'multiOptions' => $viewOptions,
            'value' => key($viewOptions),
        ));
        $this->auth_view->getDecorator('Description')->setOption('placement', 'append');
      }
    }

    // Element: auth_comment
    $commentOptions = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('blog', $user, 'auth_comment');
    $commentOptions = array_intersect_key($availableLabels, array_flip($commentOptions));

    if( !empty($commentOptions) && count($commentOptions) >= 1 ) {
      // Make a hidden field
      if(count($commentOptions) == 1) {
        $this->addElement('hidden', 'auth_comment', array('value' => key($commentOptions)));
      // Make select box
      } else {
        $this->addElement('Select', 'auth_comment', array(
            'label' => 'Comment Privacy',
            'description' => 'Who may post comments on this blog entry?',
            'multiOptions' => $commentOptions,
            'value' => key($commentOptions),
        ));
        $this->auth_comment->getDecorator('Description')->setOption('placement', 'append');
      }
    }

    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Post Entry',
      'type' => 'submit',
    ));
  }
  
  public function postEntry()
  {
    $values = $this->getValues();
    
    $user = Engine_Api::_()->user()->getViewer();
    $title = $values['title'];
    $body = $values['body'];
    $category_id = $values['category_id'];
    $tags = preg_split('/[,]+/', $values['tags']);

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try{
      // Transaction
      $table = Engine_Api::_()->getDbtable('blogs', 'blog');

      // insert the blog entry into the database
      $row = $table->createRow();
      $row->owner_id   =  $user->getIdentity();
      $row->owner_type = $user->getType();
      $row->category_id = $category_id;
      $row->creation_date = date('Y-m-d H:i:s');
      $row->modified_date   = date('Y-m-d H:i:s');
      $row->title   = $title;
      $row->body   = $body;
      //$row->category_id = $category_id;
      $row->save();

      $blog_id = $row->blog_id;

      if ($tags)
      {
        $this->handleTags($blog_id,$tags);
      }

      $attachment = Engine_Api::_()->getItem($row->getType(), $blog_id);
      $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $row, 'blog_new');
      Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
      $db->commit();
    }
    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }
    //      $action = $api->addActivity($viewer, $viewer, 'status', $body);
    //  $api->attachActivity($action, $attachment);
  }

  public function handleTags($blog_id, $tags){
      $tagTable = Engine_Api::_()->getDbtable('tags', 'blog');
      $tabMapTable = Engine_Api::_()->getDbtable('tagmaps', 'blog');
      $tagDup = array();
      foreach( $tags as $tag )
      {

        $tag = htmlspecialchars((trim($tag)));
        if (!in_array($tag, $tagDup) && $tag !="" && strlen($tag)< 20){
          $tag_id = $this->checkTag($tag);
          // check if it is new. if new, createnew tag. else, get the tag_id and insert
          if (!$tag_id){
            $tag_id = $this->createNewTag($tag, $blog_id, $tagTable);
          }

          $tabMapTable->insert(array(
            'blog_id' => $blog_id,
            'tag_id' => $tag_id
          ));
          $tagDup[] = $tag;
        }
        if (strlen($tag)>= 20){
          $this->_error[] = $tag;
        }
      }
   }
  /*    $table = Engine_Api::_()->getDbtable('tags', 'blog');

    $select = $table->select()->order('text ASC')->where('text like ?', "%$search%");
    $results = $table->fetchAll($select);*/

  public function checkTag($text){
    $table = Engine_Api::_()->getDbtable('tags', 'blog');
    $select = $table->select()->order('text ASC')->where('text = ?', $text);
    $results = $table->fetchRow($select);
    $tag_id = "";
    if($results) $tag_id = $results->tag_id;
    return $tag_id;
  }

  public function createNewTag($text, $blog_id, $tagTable){
    $row = $tagTable->createRow();
    $row->text =  $text;
    $row->save();
    $tag_id = $row->tag_id;

    return $tag_id;
  }

}