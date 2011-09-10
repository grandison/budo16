<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: browse.tpl 8089 2010-12-21 01:38:39Z john $
 * @author     Sami
 */
?>

<script type="text/javascript">
//<![CDATA[
  window.addEvent('domready', function() {
    $('sort').addEvent('change', function(){
      $(this).getParent('form').submit();
    });
    
    var category_id = $('category_id');
    if( category_id != null ){
      category_id.addEvent('change', function(){
        $(this).getParent('form').submit();
      });
    }
  })
//]]>
</script>

<div class="headline">
  <h2>
    <?php echo $this->translate('Photo Albums');?>
  </h2>
  <?php if( count($this->navigation) > 0 ): ?>
    <div class="tabs">
      <?php
        // Render the menu
        echo $this->navigation()
          ->menu()
          ->setContainer($this->navigation)
          ->render();
      ?>
    </div>
  <?php endif; ?>
</div>

<div class='layout_right'>
  <?php echo $this->search_form->render($this) ?>

  <?php if( count($this->quickNavigation) > 0 ): ?>
    <div class="quicklinks">
      <?php
        // Render the menu
        echo $this->navigation()
          ->menu()
          ->setContainer($this->quickNavigation)
          ->render();
      ?>
    </div>
  <?php endif; ?>
</div>

<div class='layout_middle'>
  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>

    <ul class="thumbs">
      <?php foreach( $this->paginator as $album ): ?>
        <li>
          <a class="thumbs_photo" href="<?php echo $album->getHref(); ?>">
            <span style="background-image: url(<?php echo $album->getPhotoUrl('thumb.normal'); ?>);"></span>
          </a>
          <p class="thumbs_info">
            <span class="thumbs_title">
              <?php echo $this->htmlLink($album, $this->string()->chunk($this->string()->truncate($album->getTitle(), 45), 10)) ?>
            </span>
            <?php echo $this->translate('By');?>
            <?php echo $this->htmlLink($album->getOwner()->getHref(), $album->getOwner()->getTitle(), array('class' => 'thumbs_author')) ?>
            <br />
            <?php echo $this->translate(array('%s photo', '%s photos', $album->count()),$this->locale()->toNumber($album->count())) ?>
          </p>
        </li>
      <?php endforeach;?>
    </ul>

    <?php if( $this->paginator->count() > 1 ): ?>
      <br />
      <?php echo $this->paginationControl($this->paginator, null, null); ?>
    <?php endif; ?>
  <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate('Nobody has created an album yet.');?>
        <?php if( $this->canCreate ): ?>
          <?php echo $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action' => 'upload')).'">', '</a>'); ?>
        <?php endif; ?>
      </span>
    </div>
  <?php endif; ?>
</div>