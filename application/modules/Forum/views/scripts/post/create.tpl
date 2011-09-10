<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: create.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Sami
 */
?>
<script type="text/javascript">
function showUploader()
{
  $('photo').style.display = 'block';
  $('photo-label').style.display = 'none';
}
</script>
<h2>
<?php echo $this->htmlLink(array('route'=>'forum_general'), $this->translate("Forums"));?>
  &#187; <?php echo $this->htmlLink(array('route'=>'forum_forum', 'forum_id'=>$this->forum->getIdentity()), $this->forum->getTitle());?>
  &#187; <?php echo $this->htmlLink(array('route'=>'forum_topic', 'topic_id'=>$this->topic->getIdentity()), $this->topic->getTitle());?>
  &#187 <?php echo $this->translate('Post Reply');?>
</h2>
<?php echo $this->form->render($this) ?>
