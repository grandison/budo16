<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author		 John
 */
?>

<div class="group_album_options">
  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
    <?php echo $this->htmlLink(array(
        'route' => 'group_extended',
        'controller' => 'photo',
        'action' => 'list',
        'subject' => $this->subject()->getGuid(),
      ), $this->translate('View All Photos'), array(
        'class' => 'buttonlink icon_group_photo_view'
    )) ?>
  <?php endif; ?>

  <?php if( $this->canUpload ): ?>
    <?php echo $this->htmlLink(array(
        'route' => 'group_extended',
        'controller' => 'photo',
        'action' => 'upload',
        'subject' => $this->subject()->getGuid(),
      ), $this->translate('Upload Photos'), array(
        'class' => 'buttonlink icon_group_photo_new'
    )) ?>
  <?php endif; ?>
</div>

<br />

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

  <ul class="thumbs">
    <?php foreach( $this->paginator as $photo ): ?>
      <li>
        <a class="thumbs_photo" href="<?php echo $photo->getHref(); ?>">
          <span style="background-image: url(<?php echo $photo->getPhotoUrl('thumb.normal'); ?>);"></span>
        </a>
        <p class="thumbs_info">
          <?php echo $this->translate('By');?>
          <?php echo $this->htmlLink($photo->getOwner()->getHref(), $photo->getOwner()->getTitle(), array('class' => 'thumbs_author')) ?>
          <br />
          <?php echo $this->timestamp($photo->creation_date) ?>
        </p>
      </li>
    <?php endforeach;?>
  </ul>

<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('No photos have been uploaded to this group yet.');?>
    </span>
  </div>

<?php endif; ?>
