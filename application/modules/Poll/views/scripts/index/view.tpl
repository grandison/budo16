<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: view.tpl 7486 2010-09-28 03:00:23Z john $
 * @author     Steve
 */
?>

<h2>
  <?php echo $this->translate('%s\'s Polls', $this->htmlLink($this->owner, $this->owner->getTitle())) ?>
</h2>

<div class="layout_middle">
  <div class='polls_view'>

    <h3>
      <?php echo $this->poll->title ?>
    </h3>
    <div class="poll_desc">
      <?php echo $this->poll->description ?>
    </div>
    
    <?php
      // poll, pollOptions, canVote, canChangeVote, hasVoted, showPieChart
      echo $this->render('_poll.tpl')
    ?>

    <?php echo $this->action("list", "comment", "core", array("type"=>"poll", "id"=>$this->poll->poll_id)) ?>

  </div>
</div>
