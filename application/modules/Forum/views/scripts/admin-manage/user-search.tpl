<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: user-search.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */
?>
<?php if (count($this->paginator) > 1):?>
<?php echo $this->translate("Your search returned too many results; only displaying the first 20.") ?>
<?php endif;?>
<?php foreach ($this->paginator as $user):?>
<?php if (!$this->forum->isModerator($user)):?>
  <li>
    <a href='javascript:addModerator(<?php echo $user->getIdentity();?>);'><?php echo $user->getTitle();?></a>
  </li>
<?php endif;?>
<?php endforeach;?>