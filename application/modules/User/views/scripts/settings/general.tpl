<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: general.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
?>

<div class="headline">
  <h2>
    <?php echo $this->translate('My Settings');?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>

<div class="global_form">
  <?php if ($this->form->saveSuccessful): ?>
    <h3><?php echo $this->translate('Settings were successfully saved.');?></h3>
  <?php endif; ?>
  <?php echo $this->form->render($this) ?>
</div>

<?php if( Zend_Controller_Front::getInstance()->getRequest()->getParam('format') == 'html' ): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function(){
      var req = new Form.Request($$('.global_form')[0], $('global_content'), {
        requestOptions : {
          url : '<?php echo $this->url(array()) ?>'
        },
        extraData : {
          format : 'html'
        }
      });
    });
  </script>
<?php endif; ?>