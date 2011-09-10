<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: manage.tpl 8368 2011-02-01 03:59:27Z john $
 * @author     Steve
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('Polls');?>
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
  var searchPolls = function() {
    $('filter_form').submit();
  }
</script>

<div class='layout_right'>
  <?php echo $this->form->render($this) ?>

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
  <?php if( 0 == count($this->paginator) ): ?>
    <div class="tip">
      <span>
        <?php echo $this->translate('There are no polls yet.') ?>
        <?php if( $this->canCreate): ?>
          <?php echo $this->translate('Why don\'t you %1$screate one%2$s?',
            '<a href="'.$this->url(array('action' => 'create'), 'poll_general').'">', '</a>') ?>
        <?php endif; ?>
      </span>
    </div>

  <?php else: // $this->polls is NOT empty ?>
  
    <ul class="polls_browse">
      <?php foreach( $this->paginator as $poll ): ?>
      <li id="poll-item-<?php echo $poll->poll_id ?>">
        <?php echo $this->htmlLink($poll->getHref(), $this->itemPhoto($this->owner, 'thumb.icon'), array('class' => 'polls_browse_photo')) ?>
        <div class="polls_browse_options">
          <?php echo $this->htmlLink(array(
            'route' => 'poll_specific',
            'action' => 'edit',
            'poll_id' => $poll->poll_id,
            'reset' => true,
          ), $this->translate('Edit Privacy'), array(
            'class' => 'buttonlink icon_poll_edit'
          )) ?>
          <?php
            echo $this->htmlLink(array('route' => 'default', 'module' => 'poll', 'controller' => 'index', 'action' => 'delete', 'poll_id' => $poll->poll_id, 'format' => 'smoothbox'), $this->translate('Delete Poll'), array(
              'class' => 'buttonlink smoothbox icon_poll_delete'
            ));
          ?>
        </div>
        <div class="polls_browse_info">
          <?php echo $this->htmlLink($poll->getHref(), $poll->getTitle()) ?>
          <div class="polls_browse_info_date">
              <?php echo $this->translate('Posted by %s', $this->htmlLink($this->owner, $this->owner->getTitle())) ?>
              <?php echo $this->timestamp($poll->creation_date) ?>
              -
              <?php echo $this->translate(array('%s vote', '%s votes', $poll->vote_count), $this->locale()->toNumber($poll->vote_count)) ?>
              -
              <?php echo $this->translate(array('%s view', '%s views', $poll->view_count), $this->locale()->toNumber($poll->view_count)) ?>
          </div>
          <?php if( '' != ($description = $poll->getDescription()) ): ?>
            <div class="polls_browse_info_desc">
              <?php echo $description ?>
            </div>
          <?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; // $this->polls is NOT empty ?>

  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'pageAsQuery' => true,
    'query' => $this->formValues,
    //'params' => $this->formValues,
  )); ?>
</div>