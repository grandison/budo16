<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8379 2011-02-02 04:40:27Z john $
 * @author     Jung
 */
?>
<script type="text/javascript">
  var SortablesInstance;

  window.addEvent('load', function() {
    SortablesInstance = new Sortables('step_list', {
      clone: true,
      constrain: true,
      handle: 'sortable',
      onComplete: function(e) {
        reorder(e);
      }
    });
  });

  var reorder = function(e) {
     var steps = e.parentNode.childNodes;
     var ordering = {};
     var i = 1;
     for (var step in steps)
     {
       var child_id = steps[step].id;
       if ((child_id != undefined) && (child_id.substr(0, 5) == 'step_'))
       {
         ordering[child_id] = i;
         i++;
       }
     }
    ordering['format'] = 'json';
    // Send request
    var url = '<?php echo $this->url(array('action' => 'order')) ?>';
    var request = new Request.JSON({
      'url' : url,
      'data' : ordering,
      onSuccess : function(responseJSON) {
      }
    });

    request.send();
  }

  function ignoreDrag(event)
  {
    event.stopPropagation();
    return false;
  }
</script>

<h2>
  <?php echo $this->translate("Member Signup Process") ?>
</h2>
<p>
  <?php echo $this->translate("USER_VIEWS_SCRIPTS_ADMINSIGNUP_INDEX_DESCRIPTION") ?>
</p>

<br />

<div class='admin_signup_wrapper'>
  <div class='admin_signup_steps'>
    <ul id="step_list">
      <?php foreach( $this->steps as $step ): ?>
        <li class='sortable' id='step_<?php echo $step->signup_id ?>'>
          <a href='<?php echo $this->url(array('signup_id'=>$step->signup_id));?>' onmousedown="ignoreDrag(event)"><?php echo $this->translate("ADMIN_SIGNUP_STEP_" . strtoupper($step->class)) ?></a>
        </li>
      <?php endforeach;?>
    </ul>
  </div>
  <div class='admin_signup_settings'>
    <div class='form_elements'>
      <?php echo $this->partial($this->script[0], $this->script[1], array(
        'form' => $this->form,
        'current_step'=>$this->current_step
      )) ?>
    </div>
  </div>
</div>









<!--
<table class='admin_table'>
  <thead>
    <tr>
      <th class='admin_table_short'>ID</th>
      <th>Title</th>
      <th>Enabled</th>
      <th>Options</th>
    </tr>
  </thead>
  <tbody>
    <?php //if( count($this->paginator) ): ?>
      <?php foreach( $this->steps as $step ): ?>
        <tr>
          <td><?php echo $step->signup_id ?></td>
          <td><?php echo $step->class ?></td>
          <td><?php echo 'Yes' ?></td>
          <td>
            <?php if( !empty($step->class) ): ?>
              <?php echo $this->htmlLink($step->class, 'configure') ?>
            <?php endif; ?>

            <?php if( !empty($step->admin_route) ): ?>
              -
            <?php endif; ?>


          </td>
        </tr>
      <?php endforeach; ?>
    <?php //endif; ?>
  </tbody>
</table>
-->