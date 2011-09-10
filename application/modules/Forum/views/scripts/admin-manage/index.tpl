<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7904 2010-12-03 03:36:14Z john $
 * @author     Jung
 */
?>

<h2><?php echo $this->translate("Forums Plugin") ?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  var moveCategoryUp = function(category_id) {
    var url = '<?php echo $this->url(array('action' => 'move-category')) ?>';
    var request = new Request.JSON({
      url : url,
      data : {
        format : 'json',
        category_id : category_id
      },
      onComplete : function() {
        window.location.replace( window.location.href );
      }
    });
    request.send();
  }
  var moveForumUp = function(forum_id) {
    var url = '<?php echo $this->url(array('action' => 'move-forum')) ?>';
    var request = new Request.JSON({
      url : url,
      data : {
        format : 'json',
        forum_id : forum_id
      },
      onComplete : function() {
        window.location.replace( window.location.href );
      }
    });
    request.send();
  }
</script>

<div class="admin_forums_options">
  <a href="<?php echo $this->url(array('action'=>'add-category'));?>" class="buttonlink smoothbox admin_forums_createcategory"><?php echo $this->translate("Add Category") ?></a>
  <a href="<?php echo $this->url(array('action'=>'add-forum'));?>" class="buttonlink smoothbox admin_forums_create"><?php echo $this->translate("Add Forum") ?></a>
</div>

<br />

<ul class="admin_forum_categories">
  <?php foreach ($this->categories as $category):?>
  <li>
    <div class="admin_forum_categories_info">
      <div class="admin_forum_categories_options">
        <span class="admin_forums_moveup">      
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('move up'), array('onclick' => 'moveCategoryUp(' . $category->category_id .');'));?> |
        </span>
        <a href="<?php echo $this->url(array('action'=>'edit-category', 'category_id'=>$category->getIdentity()));?>" class="smoothbox"><?php echo $this->translate("edit") ?></a>
        | <a class="smoothbox" href="<?php echo $this->url(array('action'=>'delete-category', 'category_id'=>$category->getIdentity()));?>"><?php echo $this->translate("delete") ?></a>
      </div>
      <div class="admin_forum_categories_title">
        <?php echo $category->getTitle();?>
      </div>
    </div>
    <ul class="admin_forums">
      <?php foreach ($category->getChildren('forum_forum', array('order'=>'order')) as $forum):?>
      <li>
        <div class="admin_forums_options">
          <span class="admin_forums_moveup">
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('move up'), array('onclick' => 'moveForumUp(' . $forum->getIdentity() .');'));?> |
          </span>
          <a href="<?php echo $this->url(array('action'=>'edit-forum', 'forum_id'=>$forum->getIdentity()));?>" class="smoothbox"><?php echo $this->translate("edit") ?></a>
          | <a class="smoothbox" href="<?php echo $this->url(array('action'=>'delete-forum', 'forum_id'=>$forum->getIdentity()));?>"><?php echo $this->translate("delete") ?></a>
        </div>
        <div class="admin_forums_info">
          <span class="admin_forums_title">
            <?php echo $forum->getTitle();?>
          </span>
          <span class="admin_forums_description">
            <?php echo $forum->getDescription();?>
          </span>
          <span class="admin_forums_moderators">
            <span class="admin_forums_moderators_top">
              <?php echo $this->translate("Moderators") ?>
              (<a href="<?php echo $this->url(array('action'=>'add-moderator', 'format'=>'smoothbox', 'forum_id'=>$forum->getIdentity()));?>" class="smoothbox"><?php echo $this->translate("add") ?></a>)
            </span>
            <span>
              <?php
                $i = 0;
                foreach ($forum->getModeratorList()->getAllChildren() as $moderator)
                {
                  if ($i > 0)
                  {
                    echo ', ';
                  }
                  $i++;
                  echo $moderator->__toString() . ' (<a class="smoothbox" href="' . $this->url(array('action'=>'remove-moderator', 'forum_id'=>$forum->getIdentity(), 'user_id'=>$moderator->getIdentity())) . '">' . $this->translate("remove") . '</a>)';
                }
              ?>
            </span>
          </span>
        </div>
      </li>
<?php endforeach;?>
    </ul>
  </li>
  <?php endforeach;?>
</ul>

