<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8373 2011-02-01 22:59:13Z john $
 * @author     John
 */
?>

<ul class="generic_list_widget generic_list_widget_large_photo">
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <div class="photo">
        <?php echo $this->htmlLink($item->getHref(), $this->itemPhoto($item, 'thumb.normal'), array('class' => 'thumb')) ?>
      </div>
      <div class="info">
        <div class="title">
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
          <?php if( $item->closed ): ?>
            <img src='application/modules/Classified/externals/images/close.png' />
          <?php endif ?>
        </div>
        <div class="stats">
          <?php echo $this->timestamp(strtotime($item->creation_date)) ?>
          - <?php echo $this->translate('posted by %1$s',
              $this->htmlLink($item->getOwner()->getHref(), $item->getOwner()->getTitle())) ?>
        </div>
      </div>
      <div class="description">
        <?php $fieldStructure = Engine_Api::_()->fields()->getFieldsStructurePartial($item)?>
        <?php echo $this->fieldValueLoop($item, $fieldStructure) ?>
        <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 300) ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>