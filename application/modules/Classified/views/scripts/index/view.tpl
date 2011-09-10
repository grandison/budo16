<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: view.tpl 8387 2011-02-03 20:53:58Z john $
 * @author     Jung
 */
?>

<?php if( !$this->classified): ?>
<?php echo $this->translate('The classified you are looking for does not exist or has been deleted.');?>
<?php return; // Do no render the rest of the script in this mode
endif; ?>

<div class='layout_middle'>
  <h2>
    <?php echo $this->classified->getTitle(); ?>
    <?php if( $this->classified->closed == 1 ): ?>
      <img src='application/modules/Classified/externals/images/close.png'/>
    <?php endif;?>
  </h2>
  <ul class='classifieds_entrylist'>
    <li>
      <div class="classified_entrylist_entry_date">
        <?php echo $this->translate('Posted by');?> <?php echo $this->htmlLink($this->classified->getParent(), $this->classified->getParent()->getTitle()) ?>
        <?php echo $this->timestamp($this->classified->creation_date) ?>
        <?php if ($this->category):?>- <?php echo $this->translate('Filed in');?>
        <?php echo $this->translate($this->category->category_name); ?>
        <?php endif; ?>
        <?php if (count($this->classifiedTags )):?>
        -
          <?php foreach ($this->classifiedTags as $tag): ?>
          <?php if (!empty($tag->getTag()->text)):?>
            #<?php echo $tag->getTag()->text?>&nbsp;
          <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php echo $this->fieldValueLoop($this->classified, $this->fieldStructure) ?>

      </div>

      <?php if ($this->classified->closed == 1):?>
        <div class="tip">
          <span>
            <?php echo $this->translate('This classified listing has been closed by the poster.');?>
          </span>
        </div>
      <?php endif; ?>
      <div class="classified_entrylist_entry_body">
        <?php echo nl2br($this->classified->body) ?>
      </div>
        <ul class='classified_thumbs'>
          <?php if($this->main_photo):?>
            <li>
              <div class="classifieds_thumbs_description">
                <?php if( '' != $this->main_photo->getDescription() ): ?>
                  <?php echo $this->string()->chunk($this->main_photo->getDescription(), 100) ?>
                <?php endif; ?>
              </div>
              <?php echo $this->htmlImage($this->main_photo->getPhotoUrl(), $this->main_photo->getTitle(), array(
                'id' => 'media_photo'
              )); ?>
            </li>
          <?php endif; ?>

          <?php foreach( $this->paginator as $photo ): ?>
            <?php if($this->classified->photo_id != $photo->file_id):?>
              <li>
                <div class="classifieds_thumbs_description">
                  <?php if( '' != $photo->getDescription() ): ?>
                    <?php echo $this->string()->chunk($photo->getDescription(), 100) ?>
                  <?php endif; ?>
                </div>
                <?php echo $this->htmlImage($photo->getPhotoUrl(), $photo->getTitle(), array(
                  'id' => 'media_photo'
                )); ?>
              </li>
            <?php endif; ?>
          <?php endforeach;?>
        </ul>
    </li>
  </ul>

  <div class="classified_stats">
    <?php if( $this->canUpload ): ?>
      <?php echo $this->htmlLink(array(
        'route' => 'classified_extended',
        'controller' => 'photo',
        'action' => 'upload',
        'classified_id' => $this->classified->getIdentity(),
        ), $this->translate('Add Photos')) ?>
      &nbsp;|&nbsp;
    <?php endif; ?>
    <?php if( $this->canEdit ): ?>
      <?php echo $this->htmlLink(array(
        'route' => 'classified_specific',
        'action' => 'edit',
        'classified_id' => $this->classified->getIdentity(),
        //'format' => 'smoothbox'
      ), $this->translate("Edit")/*, array('class' => 'smoothbox')*/); ?>
      &nbsp;|&nbsp;
    <?php endif; ?>
    <?php if( $this->canDelete ): ?>
      <?php echo $this->htmlLink(array(
        'route' => 'classified_specific',
        'action' => 'delete',
        'classified_id' => $this->classified->getIdentity(),
        'format' => 'smoothbox'
      ), $this->translate("Delete"), array('class' => 'smoothbox')); ?>
      &nbsp;|&nbsp;
    <?php endif; ?>
    <?php if( $this->canEdit ): ?>
      <?php if( !$this->classified->closed ): ?>
        <?php echo $this->htmlLink(array(
          'route' => 'classified_specific',
          'action' => 'close',
          'classified_id' => $this->classified->getIdentity(),
          'closed' => 1,
          'QUERY' => array(
            'return_url' => $this->url(),
          ),
        ), $this->translate('Close')) ?>
      <?php else: ?>
        <?php echo $this->htmlLink(array(
          'route' => 'classified_specific',
          'action' => 'close',
          'classified_id' => $this->classified->getIdentity(),
          'closed' => 0,
          'QUERY' => array(
            'return_url' => $this->url(),
          ),
        ), $this->translate('Open')) ?>
      <?php endif; ?>
      &nbsp;|&nbsp;
    <?php endif; ?>
    <?php echo $this->htmlLink(array(
      'module' => 'activity',
      'controller' => 'index',
      'action' => 'share',
      'route' => 'default',
      'type' => 'classified',
      'id' => $this->classified->getIdentity(),
      'format' => 'smoothbox'
    ), $this->translate("Share"), array('class' => 'smoothbox')); ?>
    &nbsp;|&nbsp;
    <?php echo $this->htmlLink(array(
      'module' => 'core',
      'controller' => 'report',
      'action' => 'create',
      'route' => 'default',
      'subject' => $this->classified->getGuid(),
      'format' => 'smoothbox'
    ), $this->translate("Report"), array('class' => 'smoothbox')); ?>
    &nbsp;|&nbsp;
    <?php echo $this->translate(array('%s view', '%s views', $this->classified->view_count), $this->locale()->toNumber($this->classified->view_count)) ?>
  </div>

  <?php echo $this->action("list", "comment", "core", array("type"=>"classified", "id"=>$this->classified->getIdentity())) ?>
</div>