<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8934 2011-05-12 21:13:19Z jung $
 * @author     John
 */
?>

<ul class="profile_links">
  <?php foreach( $this->paginator as $link ): ?>
    <li>
      <?php if($link->photo_id != 0):?>
      <div class="profile_links_photo">
        <?php echo $this->htmlLink($link->getHref(), $this->itemPhoto($link)) ?>
      </div>
      <?php endif;?>
      <div class="profile_links_info">
        <div class="profile_links_title">
          <?php echo $this->htmlLink($link->getHref(), $link->getTitle()) ?>
        </div>
        <div class="profile_links_description">
          <?php echo $this->htmlLink($link->getHref(), $link->getDescription()) ?>
        </div>
        <?php if( !$link->getOwner()->isSelf($link->getParent()) ): ?>
        <div class="profile_links_author">
          <?php echo $this->translate('Posted by %s', $link->getOwner()->__toString()) ?>
          <?php echo $this->timestamp($link->creation_date) ?>
        </div>
        <?php endif; ?>
      </div>

      <?php
      if ($link->isDeletable()){
        echo "<br/>".$this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'link', 'action' => 'delete', 'link_id' => $link->link_id, 'format' => 'smoothbox'), $this->translate('Delete Link'), array(
          'class' => 'buttonlink smoothbox icon_delete'
        ));
      }
      ?>
    </li>
  <?php endforeach; ?>
</ul>