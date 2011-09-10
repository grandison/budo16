<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7991 2010-12-08 18:17:43Z char $
 * @author     Jung
 */
?>


<script type="text/javascript">

function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected videos?") ?>");
}

function selectAll()
{
  var i;
  var multidelete_form = $('multidelete_form');
  var inputs = multidelete_form.elements;
  for (i = 1; i < inputs.length; i++) {
    if (!inputs[i].disabled) {
      inputs[i].checked = inputs[0].checked;
    }
  }
}

 function killProcess(video_id) {
    (new Request.JSON({
      'format': 'json',
      'url' : '<?php echo $this->url(array('module' => 'video', 'controller' => 'admin-manage', 'action' => 'kill'), 'default', true) ?>',
      'data' : {
        'format' : 'json',
        'video_id' : video_id
      },
      'onRequest' : function(){
        $$('input[type=radio]').set('disabled', true);
      },
      'onSuccess' : function(responseJSON, responseText)
      {
        window.location.reload();
      }
    })).send();

  }
</script>

<h2>
  <?php echo $this->translate("Videos Plugin") ?>
</h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<p>
  <?php echo $this->translate("VIDEO_VIEWS_SCRIPTS_ADMINMANAGE_INDEX_DESCRIPTION") ?>
</p>

<br />

<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
        <th class='admin_table_short'>ID</th>
        <th><?php echo $this->translate("Title") ?></th>
        <th><?php echo $this->translate("Owner") ?></th>
        <th><?php echo $this->translate("Views") ?></th>
        <th><?php echo $this->translate("State") ?></th>
        <th><?php echo $this->translate("Date") ?></th>
        <th><?php echo $this->translate("Options") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->video_id;?>' value='<?php echo $item->video_id ?>' /></td>
          <td><?php echo $item->video_id ?></td>
          <td><?php echo $item->title ?></td>
          <td><?php echo $this->user($item->owner_id)->getTitle() ?></td>
          <td><?php echo $this->locale()->toNumber($item->view_count) ?></td>
          <td>
            
            <?php
              switch ($item->status){
                case 0:
                  $status = $this->translate("queued");
                  break;
                case 1:
                  $status = $this->translate("ready");
                  break;
                case 2:
                  $status = $this->translate("processing");
                  break;
                default:
                  $status = $this->translate("failed");
              }
              echo $status;
            ?>
            <?php if($item->status == 2):?>
            (<a href="javascript:void(0);" onclick="javascript:killProcess('<?php echo $item->video_id?>');">
              <?php echo $this->translate("end"); ?>
            </a>)
            <?php endif;?>
          </td>
          <td><?php echo $this->locale()->toDateTime($item->creation_date) ?></td>
          <td>
            <a href="<?php echo $this->url(array('user_id' => $item->owner_id, 'video_id' => $item->video_id), 'video_view') ?>">
              <?php echo $this->translate("view") ?>
            </a>
            |
            <?php echo $this->htmlLink(
                array('route' => 'default', 'module' => 'video', 'controller' => 'admin-manage', 'action' => 'delete', 'id' => $item->video_id),
                $this->translate("delete"),
                array('class' => 'smoothbox')) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <br />

  <div class='buttons'>
    <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
  </div>
  </form>

  <br />

  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>

<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no videos posted by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>
