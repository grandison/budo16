<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: error.tpl 7762 2010-11-11 01:14:03Z shaun $
 * @author     John
 */
?>

<script type="text/javascript">
  var goToContactPageAfterError = function() {
    var url = '<?php echo $this->url(array('controller' => 'help', 'action' => 'contact'), 'default', true) ?>';
    var name = '<?php echo urlencode(base64_encode($this->errorName)) ?>';
    var loc = '<?php echo urlencode(base64_encode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])) ?>';
    var time = '<?php echo urlencode(base64_encode(time())) ?>';
    window.location.href = url + '?name=' + name + '&loc=' + loc + '&time=' + time;
  }
</script>

<div>

  <h2>
    <?php echo $this->translate('We\'re sorry!') ?>
  </h2>

  <p>
  <?php echo $this->translate('We are currently experiencing some technical ' .
      'issues. Please try again or report this to your site administrator ' .
      'using the %1$scontact%2$s form.',
      '<a href="javascript:void(0);" onclick="goToContactPageAfterError();return false;">',
      '</a>'
      ) ?>
  </p>
  <br />

  <p>
    <?php echo $this->translate('Administrator: Please check the error log in ' .
        'your admin panel for more information regarding this error.') ?>
    <?php //echo $this->translate('Some information is available below:') ?>
  </p>
  <br />
  
  <p>
    <?php printf($this->translate('Error Code: %s'), $this->error_code); ?>
  </p>
  <br />

  <?php /*
  <p class="small">
    Type: <?php echo $this->errorName ?>
    <br />
    Location: <?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>
  </p>
   */ ?>

  <?php if( isset($this->error) && 'development' == APPLICATION_ENV ): ?>
    <br />
    <br />
    <pre><?php echo $this->error; ?></pre>
  <?php endif; ?>

</div>