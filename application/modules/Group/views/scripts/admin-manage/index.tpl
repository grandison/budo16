<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7991 2010-12-08 18:17:43Z char $
 * @author     Jung
 */
?>


<script type="text/javascript">

function multiDelete()
{
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected groups?") ?>");
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
</script>

<h2>
  <?php echo $this->translate("Groups Plugin") ?>
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
  <?php echo $this->translate("GROUP_VIEWS_SCRIPTS_ADMINMANAGE_INDEX_DESCRIPTION") ?>
</p>

<br />

<?php if( count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
        <th class='admin_table_short'><?php echo $this->translate("ID") ?></th>
        <th><?php echo $this->translate("Title") ?></th>
        <th><?php echo $this->translate("Owner") ?></th>
        <th><?php echo $this->translate("Views") ?></th>
        <th><?php echo $this->translate("Date") ?></th>
        <th><?php echo $this->translate("Options") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->group_id;?>' value='<?php echo $item->group_id ?>' /></td>
          <td><?php echo $item->group_id ?></td>
          <td><?php echo $item->title ?></td>
          <td><?php echo $this->user($item->user_id)->getTitle(); ?></td>
          <td><?php echo $item->view_count;?></td>
          <td><?php echo $this->locale()->toDateTime($item->creation_date) ?></td>
          <td>
            <a href="<?php echo $this->url(array('id' => $item->group_id), 'group_profile') ?>">
              <?php echo $this->translate("view") ?>
            </a>
            |
            <?php echo $this->htmlLink(
                array('route' => 'default', 'module' => 'group', 'controller' => 'admin-manage', 'action' => 'delete', 'id' => $item->group_id),
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
      <?php echo $this->translate("There are no groups posted by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>
