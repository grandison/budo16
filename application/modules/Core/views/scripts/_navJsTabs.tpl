<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _navJsTabs.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<script type='text/javascript'>
  var containerPrefix = '<?php echo ( !empty($this->container->containerPrefix) ? $this->container->containerPrefix : 'user_profile_index-main-middle-' ) ?>';
  function switchTab(identity)
  {
    var container = $('global_content').getElement('.layout_' + identity);
    
    $$('.tab_links').each(function(element)
    {
      var localIdentity = element.id.replace('tab_link_', '');
      var localContainer = $('global_content').getElement('.layout_' + localIdentity)

      // If missing container
      if( !$type(localContainer) ) {
        localContainer.setStyle('display', 'none');
        element.setStyle('display', 'none');
        return;
      }
      
      // Show
      if( element.id == 'tab_link_' + identity )
      {
        if( !localContainer.hasClass('tab_container_active') )
        {
          localContainer.addClass('tab_container_active');
          localContainer.removeClass('tab_container_inactive');
        }
        if( !element.hasClass('tab_active') )
        {
          element.addClass('tab_active');
          element.removeClass('tab_inactive');
        }
      }

      // Hide
      else
      {
        if( !localContainer.hasClass('tab_container_inactive') )
        {
          localContainer.addClass('tab_container_inactive');
          localContainer.removeClass('tab_container_active');
        }
        if( !element.hasClass('tab_inactive') )
        {
          element.addClass('tab_inactive');
          element.removeClass('tab_active');
        }
      }
    });
    
    /*
    $$('.tab_links').each(function(element)
    {
      var container = document.getElementById('tab_container_'+identity);

      // Show
      if( element.id == 'tab_link_' + identity )
      {
        if( !container.hasClass('tab_container_active') )
        {
          container.addClass('tab_container_active');
          container.removeClass('tab_container_inactive');
        }
        if( !element.hasClass('tab_link_active') )
        {
          element.addClass('tab_link_active');
          element.removeClass('tab_link_inactive');
        }
      }

      // Hide
      else
      {
        if( container.hasClass('tab_container_active') )
        {
          container.addClass('tab_container_inactive');
          container.removeClass('tab_container_active');
        }
        if( element.hasClass('tab_link_active') )
        {
          element.addClass('tab_link_inactive');
          element.removeClass('tab_link_active');
        }
      }
    });
    */
   
    // THIS NEEDS TO BE ABSTRACTED
    /*
    document.getElementById('profile_updates').style.display = 'none';
    document.getElementById('profile_tabs_updates').className = '';
    document.getElementById('profile_info').style.display = 'none';
    document.getElementById('profile_tabs_info').className = '';

    var visibleBox = itemId.parentNode.id.replace('_tabs', '');
    document.getElementById(visibleBox).style.display = 'block';
    document.getElementById(itemId.parentNode.id).className = 'tab_active';
    document.getElementById(itemId.parentNode.id).blur();
    */
  }

</script>

<div class='tabs'>
  <ul>
    <?php foreach( $this->container as $link ): ?>
      <li id="tab_link_<?php echo $link->getClass() ?>" class="tab_links tab_link_<?php echo $link->getClass().( $link->isActive() ? ' tab_active' : ' tab_inactive' ) ?>"><a href="<?php echo $link->getHref() ?>" onclick="switchTab('<?php echo $link->getClass()?>'); return false;"><?php echo $link->getLabel() ?></a></li>
    <?php endforeach; ?>
  </ul>
</div>