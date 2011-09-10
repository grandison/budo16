<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Group
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: categories.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Jung
 */
?>

<h2><?php echo $this->translate("Groups Plugin") ?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

  <div class='clear'>
    <div class='settings'>
    <form class="global_form">
      <div>
        <h3> <?php echo $this->translate("Group Categories") ?> </h3>
        <p class="description">
          <?php echo $this->translate("GROUP_VIEWS_SCRIPTS_ADMINSETTINGS_CATEGORIES_DESCRIPTION") ?>
        </p>
          <?php if(count($this->categories)>0):?>

         <table class='admin_table'>
          <thead>
            <tr>
              <th><?php echo $this->translate("Category Name") ?></th>
<?php //              <th># of Times Used</th>?>
              <th><?php echo $this->translate("Options") ?></th>
            </tr>

          </thead>
          <tbody>
            <?php foreach ($this->categories as $category): ?>
                    <tr>
                      <td><?php echo $category->title?></td>
                      <td>
                        <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'group', 'controller' => 'admin-settings', 'action' => 'edit-category', 'id' =>$category->category_id), $this->translate("edit"), array(
                          'class' => 'smoothbox',
                        )) ?>
                        |
                        <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'group', 'controller' => 'admin-settings', 'action' => 'delete-category', 'id' =>$category->category_id), $this->translate("delete"), array(
                          'class' => 'smoothbox',
                        )) ?>

                      </td>
                    </tr>

            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else:?>
      <br/>
      <div class="tip">
      <span><?php echo $this->translate("There are currently no categories.") ?></span>
      </div>
      <?php endif;?>
        <br/>


      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'group', 'controller' => 'settings', 'action' => 'add-category'), $this->translate('Add New Category'), array(
      'class' => 'smoothbox buttonlink',
      'style' => 'background-image: url(application/modules/Core/externals/images/admin/new_category.png);')) ?>
    </div>
    </form>
    </div>
  </div>
     