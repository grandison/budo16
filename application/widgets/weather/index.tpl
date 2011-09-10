<?php
/**
 * SocialEngine
 *
 * @category   Application_Widget
 * @package    Weather
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: index.tpl 7562 2010-10-05 22:17:24Z john $
 * @author     John
 */
?>

<div>
  <?php if( !empty($this->location) ): ?>
    Location:
    <?php echo $this->location->city ?>,
    <?php echo $this->location->state ?>,
    <?php echo $this->location->country ?>
  <?php endif; ?>
  <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core',
      'controller' => 'widget', 'action' => 'index',
      'content_id' => $this->identity, 'view' => 'choose',
      'format' => 'smoothbox'), 'Change Location', array('class' => 'smoothbox')) ?>
      <br />
</div>



<?php if( !empty($this->forecast) ): //echo '<pre>'.htmlspecialchars($this->forecast->asXml()).'</pre>'; ?>

  <?php foreach( $this->forecast->txt_forecast->forecastday as $key => $value ): ?>
    <div>
      <img src="<?php echo $value->icons->icon_set[0]->icon_url ?>" alt="<?php $value->icon ?>" />
      <span>
        <?php echo $value->title ?>
      </span>
      <p>
        <?php echo $value->fcttext ?>
      </p>
    </div>
    <br />
  <?php endforeach; ?>

<?php endif; ?>