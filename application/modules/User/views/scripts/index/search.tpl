<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: search.tpl 7864 2010-11-30 03:25:41Z steve $
 * @author     Jung
 */
?>
  <?php if( !empty($this->ajax) ): // Simple feed only for AJAX ?>
    <?php foreach( $this->users as $user ): ?>
      <li>
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
        <?php if( $this->viewer()->getIdentity() ): ?>
          <div class='browsemembers_results_links'>
            <?php echo $this->userFriendship($user) ?>
          </div>
        <?php endif; ?>

          <div class='browsemembers_results_info'>
            <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
            <?php echo $user->status; ?>
            <?php if( $user->status != "" ): ?>
              <div>
                <?php echo $this->timestamp($user->status_date) ?>
              </div>
            <?php endif; ?>
          </div>
      </li>
    <?php endforeach; ?>

    <script type="text/javascript">
      $('form_lastrow').value = <?php echo $this->lastrow; ?>;
      var lastrow = <?php echo $this->lastrow; ?>;
      var userCount = <?php echo $this->userCount; ?>;
    </script>
<?php return; // Do no render the rest of the script in this mode
endif; ?>

<div>
    <h3>
      <?php echo $this->translate(array('%s member found.', '%s members found.', $this->totalUsers),$this->locale()->toNumber($this->totalUsers)) ?>
    </h3>
  </div>
  <ul id="browsemembers_ul">
    <?php foreach( $this->users as $user ): ?>
      <li>
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon')) ?>
        
        <?php if( $this->viewer()->getIdentity() ): ?>
          <div class='browsemembers_results_links'>
            <?php echo $this->userFriendship($user) ?>
          </div>
        <?php endif; ?>

          <div class='browsemembers_results_info'>
            <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
            <?php echo $user->status; ?>
            <?php if( $user->status != "" ): ?>
              <div>
                <?php echo $this->timestamp($user->status_date) ?>
              </div>
            <?php endif; ?>
          </div>
      </li>
    <?php endforeach; ?>
  </ul>

<script type="text/javascript">
    function disableEnterKey(e)
    {
         var key;
         if(window.event)
              key = window.event.keyCode; //IE
         else
              key = e.which; //firefox

         return (key != 13);
    }

    var requestActive = false;
    $('form_lastrow').value = <?php echo $this->lastrow; ?>;
    var totalusers = <?php echo $this->totalUsers; ?>;
    var lastrow = <?php echo $this->lastrow; ?>;
    var userCount = <?php echo $this->userCount; ?>;
    var loadNextSearchMembers = function()
    {
      $('browsemembers_viewmore').innerHTML = "<div><img src='application/modules/Core/externals/images/loading.gif' style='float:left;margin-right: 5px;'/><?php echo $this->translate('Loading...');?></div>";
      $('form_ajax').value = "true";
      if( requestActive ) return;

      (new Request.HTML({
        'format': 'html',
        'url' : '<?php echo $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>',
        'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
        {
          requestActive = false;
          $('browsemembers_ul').innerHTML += responseHTML;
          responseJavaScript;
          if(userCount >= 10  && lastrow < totalusers){
            $('browsemembers_viewmore').innerHTML = '<a id="more_link" class="buttonlink icon_viewmore" href="javascript:loadNextSearchMembers();"><?php echo $this->translate('View More');?></a>';
          }
          else{
            $('browsemembers_viewmore').innerHTML = "";
          }
          Smoothbox.bind();
        }
      })).post($('myForm'));
    }

    var searchMembers = function()
    {
      $('browsemembers_results').innerHTML = "<div><img src='application/modules/Core/externals/images/loading.gif' style='float:left;margin-right: 5px;'/><?php echo $this->translate('Loading...');?></div>";
      $('form_ajax').value = "";
      $('form_lastrow').value = '0';
      if( requestActive ) return;
      (new Request.HTML({
        'format': 'html',
        'url' : '<?php echo $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>',
        'onSuccess' : function(responseTree, responseElements, responseHTML, responseJavaScript)
        {
          requestActive = false;
          $('browsemembers_results').innerHTML = responseHTML;
          responseJavaScript;
          Smoothbox.bind();
        }
      })).post($('myForm'));
    }
  </script>

  <?php if( $this->lastrow < $this->totalUsers ): ?>
  

  <div class='browsemembers_viewmore' id="browsemembers_viewmore">
    <a id="more_link" class="buttonlink icon_viewmore" href="javascript:loadNextSearchMembers();"><?php echo $this->translate('View More');?></a>
  </div>
<?php endif; ?>