
<?php
  $this->headScript()
    ->appendFile('application/modules/Poll/externals/scripts/core.js');
  $this->headTranslate(array(
    'Show Questions', 'Show Results', '%1$s%%', '%1$s vote',
  ));
?>

<script type="text/javascript">
  //<![CDATA[
  en4.core.runonce.add(function() {
    var initializePoll = function() {
      en4.poll.urls.vote = '<?php echo $this->url(array('action' => 'vote'), 'poll_general') ?>';
      en4.poll.urls.login = '<?php echo $this->url(array(), 'user_login') ?>';
      en4.poll.addPollData(<?php echo $this->poll->getIdentity() ?>, {
        canVote : <?php echo $this->canVote ? 'true' : 'false' ?>,
        canChangeVote : <?php echo $this->canChangeVote ? 'true' : 'false' ?>,
        hasVoted : <?php echo $this->hasVoted ? 'true' : 'false' ?>
      });

      $$('#poll_form_<?php echo $this->poll->getIdentity() ?> .poll_radio input').removeEvents('click').addEvent('click', function(event) {
        en4.poll.vote(<?php echo $this->poll->getIdentity() ?>, event.target);
      });
    }

    // Dynamic loading for feed
    if( $type(en4) == 'object' && 'poll' in en4 ) {
      initializePoll();
    } else {
      new Asset.javascript('application/modules/Poll/externals/scripts/core.js', {
        onload: function() {
          initializePoll();
        }
      });
    }
  });
  //]]>
</script>

<span class="poll_view_single">
  <form id="poll_form_<?php echo $this->poll->getIdentity() ?>" action="<?php echo $this->url() ?>" method="POST" onsubmit="return false;">
    <ul id="poll_options_<?php echo $this->poll->getIdentity() ?>" class="poll_options">
      <?php foreach( $this->pollOptions as $i => $option ): ?>
      <li id="poll_item_option_<?php echo $option->poll_option_id ?>">
        <div class="poll_has_voted" <?php echo ( $this->hasVoted ? '' : 'style="display:none;"' ) ?>>
          <div class="poll_option">
            <?php echo $option->poll_option ?>
          </div>
          <?php $pct = $this->poll->vote_count
                     ? floor(100*($option->votes/$this->poll->vote_count))
                     : 0;
                if (!$pct)
                  $pct = 1;
                // NOTE: poll-answer graph & text is actually rendered via
                // javascript.  The following HTML is there as placeholders
                // and for javascript backwards compatibility (though
                // javascript is required for voting).
           ?>
          <div id="poll-answer-<?php echo $option->poll_option_id ?>" class='poll_answer poll-answer-<?php echo (($i%8)+1) ?>' style='width: <?php echo .7*$pct; // set width to 70% of its real size to as to fit text label too ?>%;'>
            &nbsp;
          </div>
          <div class="poll_answer_total">
            <?php echo $this->translate(array('%1$s vote', '%1$s votes', $option->votes), $this->locale()->toNumber($option->votes)) ?>
            (<?php echo $this->translate('%1$s%%', $this->locale()->toNumber($option->votes ? $pct : 0)) ?>)
          </div>
        </div>
        <div class="poll_not_voted" <?php echo ($this->hasVoted?'style="display:none;"':'') ?> >
          <div class="poll_radio" id="poll_radio_<?php echo $option->poll_option_id ?>">
            <input id="poll_option_<?php echo $option->poll_option_id ?>"
                   type="radio" name="poll_options" value="<?php echo $option->poll_option_id ?>"
                   <?php if ($this->hasVoted == $option->poll_option_id): ?>checked="true"<?php endif; ?>
                   <?php if ($this->hasVoted && !$this->canChangeVote): ?>disabled="true"<?php endif; ?>
                   />
          </div>
          <label for="poll_option_<?php echo $option->poll_option_id ?>">
            <?php echo $option->poll_option ?>
          </label>
        </div>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php if( empty($this->hideStats) ): ?>
    <div class="poll_stats">
      <a href='javascript:void(0);' onClick='en4.poll.toggleResults(<?php echo $this->poll->getIdentity() ?>); this.blur();' class="poll_toggleResultsLink">
        <?php echo $this->translate($this->hasVoted ? 'Show Questions' : 'Show Results' ) ?>
      </a>
      <?php if( empty($this->hideLinks) ): ?>
      &nbsp;|&nbsp;
      <?php echo $this->htmlLink(array(
        'module'=>'activity',
        'controller'=>'index',
        'action'=>'share',
        'route'=>'default',
        'type'=>'poll',
        'id' => $this->poll->getIdentity(),
        'format' => 'smoothbox'
      ), $this->translate("Share"), array('class' => 'smoothbox')); ?>
      &nbsp;|&nbsp;
      <?php echo $this->htmlLink(array(
        'module'=>'core',
        'controller'=>'report',
        'action'=>'create',
        'route'=>'default',
        'subject'=>$this->poll->getGuid(),
        'format' => 'smoothbox'
      ), $this->translate("Report"), array('class' => 'smoothbox')); ?>
      <?php endif; ?>
      &nbsp;|&nbsp;
      <span class="poll_vote_total">
        <?php echo $this->translate(array('%s vote', '%s votes', $this->poll->vote_count), $this->locale()->toNumber($this->poll->vote_count)) ?>
      </span>
      &nbsp;|&nbsp;
      <?php echo $this->translate(array('%s view', '%s views', $this->poll->view_count), $this->locale()->toNumber($this->poll->view_count)) ?>
    </div>
    <?php endif; ?>
  </form>
</span>