<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8089 2010-12-21 01:38:39Z john $
 * @author     Jung
 */
?>

<ul class="blogs_browse">
  <?php foreach( $this->paginator as $item ): ?>
    <li>
      <div class='blogs_browse_info'>
        <p class='blogs_browse_info_title'>
          <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
        </p>
        <p class='blogs_browse_info_date'>
          <?php echo $this->translate('Posted');?> <?php echo $this->timestamp($item->creation_date) ?>
        </p>
        <p class='blogs_browse_info_blurb'>
          <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 300) ?>
        </p>
      </div>
    </li>
  <?php endforeach; ?>
</ul>

<?php
  // show view all link even if all are listed
  if( $this->paginator->count() > 0 ):
?>
  <?php echo $this->htmlLink($this->url(array('user_id' => Engine_Api::_()->core()->getSubject()->getIdentity()), 'blog_view'), $this->translate('View All Entries'), array('class' => 'buttonlink icon_blog_viewall')) ?>
<?php endif;?>