<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: requireuser.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<?php
  if( $this->form ):
    echo $this->form->render($this);
  else:
    echo $this->translate('Please sign in to continue.');
  endif;
?>