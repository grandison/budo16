<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: referrers.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<h2><?php echo $this->translate("Top Referring Sites") ?></h2>
<p>
  <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINSTATS_REFERRERS_DESCRIPTION") ?>
</p>

<br />

<script type="text/javascript">
  var clearReferrers = function() {
    if( !confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to clear the referrers?")) ?>') ) {
      return;
    }
    var url = '<?php echo $this->url(array('action' => 'clear-referrers')) ?>';
    var request = new Request.JSON({
      url : url,
      data : {
        format : 'json'
      },
      onComplete : function() {
        window.location.replace( window.location.href );
      }
    });
    request.send();
  }
</script>

<?php if( count($this->referrers) > 0 ): ?>

  <div>
    <?php echo $this->htmlLink('javascript:void(0);', 'Clear Referrer List', array(
      'class' => 'buttonlink admin_referrers_clear',
      'onclick' => "clearReferrers();",
    )) ?>
  </div>

  <br />

  <table class='admin_table'>
    <thead>
      <tr>
        <th><?php echo $this->translate("Hits") ?></th>
        <th><?php echo $this->translate("Referring URL") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->referrers as $referrer ): ?>
        <tr>
          <td>
            <?php echo $this->locale()->toNumber($referrer->value) ?>
          </td>
          <td>
            <?php
              $href = $referrer->host . $referrer->path . ( $referrer->query ? '?' . $referrer->query : '' );
              echo $this->htmlLink('http://' . $href, 'http://' . $href, array('target' => '_blank'))
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php else: ?>

  <div class="tip">
    <span>
      <?php echo $this->translate("There have not been any referrers logged yet.") ?>
    </span>
  </div>

<?php endif; ?>