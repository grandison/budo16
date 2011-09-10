<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<h2><?php echo $this->translate('Search') ?></h2>

<div id="searchform" class="global_form_box">
  <?php echo $this->form->setAttrib('class', '')->render($this) ?>
</div>

<br />
<br />

<?php if( empty($this->paginator) ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Please enter a search query.') ?>
    </span>
  </div>
<?php elseif( $this->paginator->getTotalItemCount() <= 0 ): ?>
  <div class="tip">
    <span>
      <?php echo $this->translate('No results were found.') ?>
    </span>
  </div>
<?php else: ?>
  <?php echo $this->translate(array('%s result found', '%s results found', $this->paginator->getTotalItemCount()),
                              $this->locale()->toNumber($this->paginator->getTotalItemCount()) ) ?>

  <?php foreach( $this->paginator as $item ):
    $item = $this->item($item->type, $item->id);
    if( !$item ) continue; ?>
    <div class="search_result">
      <div class="search_photo">
        <?php echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.icon')) ?>
      </div>
      <div class="search_info">
        <?php if( '' != $this->query ): ?>
          <?php echo $this->htmlLink($item->getHref(), $this->highlightText($item->getTitle(), $this->query), array('class' => 'search_title')) ?>
        <?php else: ?>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'search_title')) ?>
        <?php endif; ?>
        <p class="search_description">
          <?php if( '' != $this->query ): ?>
            <?php echo $this->highlightText($this->viewMore($item->getDescription()), $this->query); ?>
          <?php else: ?>
            <?php echo $this->viewMore($item->getDescription()); ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
  <?php /*
    <div class="search_result">
      <div class="search_icon">
        &nbsp;
      </div>
      <div class="search_info">
        <?php echo $this->htmlLink($item->getHref(), $item->getTitle(), array('class' => 'search_title')) ?>
        <p class="search_description">
          <?php echo $item->getDescription(); ?>
        </p>
      </div>
    </div>
   *
   */ ?>
  <?php endforeach; ?>

  <br />

  <div>
    <?php echo $this->paginationControl($this->paginator, null, null, array(
      //'params' => array(
      //  'query' => $this->query,
      //),
      'query' => array(
        'query' => $this->query,
        'type' => $this->type,
      ),
    )); ?>
  </div>

<?php endif; ?>


