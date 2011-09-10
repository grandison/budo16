<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: view.tpl 8281 2011-01-20 22:09:59Z shaun $
 * @author     Jung
 */
?>

<?php if( !$this->blog || ($this->blog->draft==1 && !$this->blog->isOwner($this->viewer()))): ?>
<?php echo $this->translate('The blog you are looking for does not exist or has not been published yet.');?>
<?php return; // Do no render the rest of the script in this mode
endif; ?>

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

    <form id='filter_form' class="blog_search_form" method='GET' action="<?php echo $this->url(array('user_id' => $this->blog->owner_id), 'blog_view') ?>">
      <input type='text' class='text suggested' name='search' id='blog_search_field' size='20' maxlength='100' alt='<?php echo $this->translate('Search Blogs') ?>' value="<?php if( $this->search ) echo $this->search; ?>" />
      <input type="hidden" id="tag" name="tag" value=""/>
      <input type="hidden" id="category" name="category" value=""/>
      <input type="hidden" id="page" name="page" value=""/>
      <input type="hidden" id="start_date" name="start_date" value=""/>
      <input type="hidden" id="end_date" name="end_date" value=""/>
    </form>

    <?php if( count($this->userCategories) ): ?>
      <h4><?php echo $this->translate('Categories');?></h4>
      <ul>
          <li> <a href='javascript:void(0);' onclick='javascript:categoryAction(0);' <?php if ($this->category==0) echo " style='font-weight: bold;'";?>><?php echo $this->translate('All Categories');?></a></li>
          <?php foreach ($this->userCategories as $category): ?>
            <li><a href='javascript:void(0);' onclick='javascript:categoryAction(<?php echo $category->category_id?>);' <?php if( $this->category->category_id == $category->category_id ) echo " style='font-weight: bold;'";?>>
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

  <?php if ($this->blog->owner_id == $this->viewer->getIdentity()&&$this->blog->draft == 1):?>
    <div class="tip">
      <span>
        <?php echo $this->translate('This blog entry has not been published. You can publish it by %1$sediting the entry%2$s.', '<a href="'.$this->url(array('blog_id' => $this->blog->blog_id), 'blog_specific', true).'">', '</a>'); ?>
      </span>
    </div>
    <br/>
  <?php endif; ?>

  <h2>
    <?php echo $this->blog->getTitle() ?>
  </h2>
  <ul class='blogs_entrylist'>
    <li>
      <div class="blog_entrylist_entry_date">
        <?php echo $this->translate('Posted by');?> <?php echo $this->htmlLink($this->owner->getHref(), $this->owner->getTitle()) ?>
        <?php echo $this->timestamp($this->blog->creation_date) ?>
        <?php if( $this->category ): ?>
          -
          <?php echo $this->translate('Filed in') ?>
          <a href='javascript:void(0);' onclick='javascript:categoryAction(<?php echo $this->category->category_id?>);'><?php echo $this->category->category_name ?></a>
        <?php endif; ?>
        <?php if (count($this->blogTags )):?>
          -
          <?php foreach ($this->blogTags as $tag): ?>
            <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->getTag()->tag_id; ?>);'>#<?php echo $tag->getTag()->text?></a>&nbsp;
          <?php endforeach; ?>
        <?php endif; ?>
        -
        <?php echo $this->translate(array('%s view', '%s views', $this->blog->view_count), $this->locale()->toNumber($this->blog->view_count)) ?>
      </div>
      <div class="blog_entrylist_entry_body">
        <?php echo $this->blog->body ?>
      </div>
    </li>
  </ul>
  
  <?php echo $this->action("list", "comment", "core", array("type"=>"blog", "id"=>$this->blog->getIdentity())) ?>
</div>
