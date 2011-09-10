<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8223 2011-01-15 00:49:34Z john $
 * @author     John
 */
?>

<?php
  $this->form->setTitle('Activity Feed Settings');
  $this->form->setDescription($this->translate('ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_DESCRIPTION',
      $this->url(array('module' => 'activity','controller' => 'settings', 'action' => 'types'), 'admin_default')));
  $this->form->getDecorator('Description')->setOption('escape', false);
?>
<div class='settings'>
<?php echo $this->form->render($this); ?>
</div>