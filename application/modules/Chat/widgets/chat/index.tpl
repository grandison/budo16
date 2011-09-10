<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Chat
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 8427 2011-02-09 23:11:24Z john $
 * @author     John
 */
?>

<div id="<?php echo $this->tmpId ?>">
</div>

<?php echo $this->action('index', 'index', 'chat', array(
  'tmpId' => $this->tmpId,
)) ?>