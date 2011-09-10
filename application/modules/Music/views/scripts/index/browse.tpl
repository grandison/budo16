<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: browse.tpl 8300 2011-01-25 06:42:26Z john $
 * @author     Steve
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Music');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<script type="text/javascript">
  //<![CDATA[
    en4.core.runonce.add(function() {
      $$('#sort, #show').addEvent('change', function(){
        $(this).getParent('form').submit();
      });
    });
  //]]>
</script>

<div class='layout_right'>
  <?php echo $this->formFilter->render($this) ?>

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
  <?php if (0 == count($this->paginator) ): ?>

    <div class="tip">
      <span>
        <?php echo $this->translate('There is no music uploaded yet.') ?>
        <?php if( $this->canCreate ): ?>
          <?php echo $this->htmlLink(array(
            'route' => 'music_general',
            'action' => 'create'
          ), $this->translate('Why don\'t you add some?')) ?>
        <?php endif; ?>
      </span>
    </div>
  <?php else: ?>
  
    <ul class="music_browse">
      <?php foreach ($this->paginator as $playlist): ?>
        <li id="music_playlist_item_<?php echo $playlist->getIdentity() ?>">
          <div class="music_browse_author_photo">
            <?php echo $this->htmlLink($playlist->getOwner(),
                       $this->itemPhoto($playlist->getOwner(), 'thumb.icon') ) ?>
          </div>
          <div class="music_browse_options">
            <?php if ($playlist->isDeletable() || $playlist->isEditable()): ?>
              <ul>
                <?php if ($playlist->isEditable()): ?>
                  <li>
                    <?php echo $this->htmlLink($playlist->getHref(array('route' => 'music_playlist_specific', 'action' => 'edit')),
                      $this->translate('Edit Playlist'),
                      array('class'=>'buttonlink icon_music_edit'))
                    ?>
                  </li>
                <?php endif; ?>
                <?php if ($playlist->isDeletable()): ?>
                  <li>
                    <?php echo $this->htmlLink($playlist->getHref(array('route' => 'music_playlist_specific', 'action' => 'delete')),
                      $this->translate('Delete Playlist'),
                      array('class'=>'buttonlink smoothbox icon_music_delete'))
                    ?>
                  </li>
                <?php endif; ?>
                <?php if ($playlist->getOwner()->isSelf(Engine_Api::_()->user()->getViewer())): ?>
                  <li>
                    <?php echo $this->htmlLink($playlist->getHref(array('route' => 'music_playlist_specific', 'action' => 'set-profile')),
                      $this->translate($playlist->profile ? 'Disable Profile Playlist' : 'Play on my Profile'),
                      array(
                        'class' => 'buttonlink music_set_profile_playlist ' . ( $playlist->profile ? 'icon_music_disableonprofile' : 'icon_music_playonprofile' )
                      )
                    ) ?>
                  </li>
                <?php endif; ?>
              </ul>
            <?php endif; ?>
          </div>
          <div class="music_browse_info">
            <div class="music_browse_info_title">
              <h3>
                <?php echo $this->htmlLink($playlist->getHref(), $playlist->getTitle()) ?>
              </h3>
            </div>
            <div class="music_browse_info_date">
              <?php echo $this->translate('Created %s by ', $this->timestamp($playlist->creation_date)) ?>
              <?php echo $this->htmlLink($playlist->getOwner(), $playlist->getOwner()->getTitle()) ?>
              -
              <?php echo $this->htmlLink($playlist->getHref(),  $this->translate(array('%s comment', '%s comments', $playlist->getCommentCount()), $this->locale()->toNumber($playlist->getCommentCount()))) ?>
            </div>
            <div class="music_browse_info_desc">
              <?php echo $playlist->description ?>
            </div>
            <?php echo $this->partial('_Player.tpl', array('playlist' => $playlist, 'hideStats' => true)) ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php echo $this->paginationControl($this->paginator, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
      //'params' => $this->formValues,
    )); ?>
  
  <?php endif; ?>
</div>
