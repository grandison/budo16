<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: list.tpl 8123 2010-12-24 01:54:12Z char $
 * @author     Jung
 */
?>

<script type="text/javascript">
  var pageAction = function(page){
    $('page').value = page;
    $('filter_form').submit();
  }
  var categoryAction = function(category){
    $('page').value = 1;
    $('blog_search_field').value = '';
    $('category').value = category;
    $('tag').value = '';
    $('start_date').value = '';
    $('end_date').value = '';
    $('filter_form').submit();
  }
  var tagAction = function(tag){
    $('page').value = 1;
    $('blog_search_field').value = '';
    $('tag').value = tag;
    $('category').value = '';
    $('start_date').value = '';
    $('end_date').value = '';
    $('filter_form').submit();
  }
  var dateAction = function(start_date, end_date){
    $('page').value = 1;
    $('blog_search_field').value = '';
    $('start_date').value = start_date;
    $('end_date').value = end_date;
    $('tag').value = '';
    $('category').value = '';
    $('filter_form').submit();
  }

  en4.core.runonce.add(function(){
    new OverText($('blog_search_field'), {
      poll: true,
      pollInterval: 500,
      positionOptions: {
        position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
        edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
        offset: {
          x: ( en4.orientation == 'rtl' ? -4 : 4 ),
          y: 2
        }
      }
    });
  });
</script>

<div class='layout_left'>
  <div class='blogs_gutter'>
    <?php echo $this->htmlLink($this->owner->getHref(), $this->itemPhoto($this->owner), array('class' => 'blogs_gutter_photo')) ?>
    <?php echo $this->htmlLink($this->owner->getHref(), $this->owner->getTitle(), array('class' => 'blogs_gutter_name')) ?>

    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->gutterNavigation)
        ->setUlClass('navigation blogs_gutter_options')
        ->render();
    ?>

    <form id='filter_form' class="blog_search_form" method='GET' action="<?php echo $this->escape($_SERVER['REQUEST_URI']) ?>">
      <input type='text' class='text suggested' name='search' id='blog_search_field' size='20' maxlength='100' alt='<?php echo $this->translate('Search Blogs') ?>' value="<?php if( $this->search ) echo $this->search; ?>" />
      <input type="hidden" id="tag" name="tag" value="<?php if( $this->tag ) echo $this->tag; ?>"/>
      <input type="hidden" id="category" name="category" value="<?php if( $this->category ) echo $this->category; ?>"/>
      <input type="hidden" id="page" name="page" value="<?php if( $this->page ) echo $this->page; ?>"/>
      <input type="hidden" id="start_date" name="start_date" value="<?php if( $this->start_date) echo $this->start_date; ?>"/>
      <input type="hidden" id="end_date" name="end_date" value="<?php if( $this->end_date) echo $this->end_date; ?>"/>
    </form>

    <?php if( count($this->userCategories) ): ?>
      <h4><?php echo $this->translate('Categories');?></h4>
      <ul>
          <li> <a href='javascript:void(0);' onclick='javascript:categoryAction(0);' <?php if ($this->category==0) echo " style='font-weight: bold;'";?>><?php echo $this->translate('All Categories');?></a></li>
          <?php foreach ($this->userCategories as $category): ?>
            <li><a href='javascript:void(0);' onclick='javascript:categoryAction(<?php echo $category->category_id?>);' <?php if( $this->category == $category->category_id ) echo " style='font-weight: bold;'";?>>
                  <?php echo $category->category_name?>
                </a>
            </li>
          <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if( count($this->userTags) ): ?>

      <h4><?php echo $this->translate('Tags'); ?></h4>
      <ul>
        <?php foreach ($this->userTags as $tag): ?>
          <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->tag_id; ?>);' <?php if ($this->tag==$tag->tag_id) echo " style='font-weight: bold;'";?>>#<?php echo $tag->text?></a>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if( count($this->archive_list) ):?>
      <h4><?php echo $this->translate('Archives');?></h4>
      <ul>
        <?php foreach ($this->archive_list as $archive): ?>
        <li>
          <a href='javascript:void(0);' onclick='javascript:dateAction(<?php echo $archive['date_start']?>, <?php echo $archive['date_end']?>);' <?php if ($this->start_date==$archive['date_start']) echo " style='font-weight: bold;'";?>><?php echo $archive['label']?></a>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  </div>
</div>

<div class='layout_middle'>
  <h2>
    <?php echo $this->translate('Recent Entries')?>
  </h2>

  <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
    <ul class='blogs_entrylist'>
    <?php foreach ($this->paginator as $item): ?>
      <li>
        <span>
          <h3>
            <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?>
          </h3>
          <div class="blog_entrylist_entry_date">
           <?php echo $this->translate('by');?> <?php echo $this->htmlLink($item->getParent(), $item->getParent()->getTitle()) ?>
            <?php echo $this->timestamp($item->creation_date) ?>
          </div>
          <div class="blog_entrylist_entry_body">
            <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 300) ?>
          </div>
          <?php if ($item->comment_count > 0) :?>
            <?php echo $this->htmlLink($item->getHref(), $item->comment_count . ' ' . ( $item->comment_count != 1 ? 'comments' : 'comment' ), array('class' => 'buttonlink icon_comments')) ?>
          <?php endif; ?>
        </span>
      </li>
    <?php endforeach; ?>
    </ul>

  <?php elseif( $this->category || $this->tag ): ?>
    <div class="tip">
      <span>
        <?php echo $this->translate('%1$s has not published a blog entry with that criteria.', $this->owner->getTitle()); ?>
      </span>
    </div>

  <?php else: ?>
    <div class="tip">
      <span>
        <?php echo $this->translate('%1$s has not written a blog entry yet.', $this->owner->getTitle()); ?>
      </span>
    </div>
  <?php endif; ?>

  <?php echo $this->paginationControl($this->paginator, null, null, array(
    'pageAsQuery' => true,
    'query' => $this->formValues,
    //'params' => $this->formValues,
  )); ?>
  
</div>