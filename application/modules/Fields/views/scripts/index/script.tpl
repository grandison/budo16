<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: script.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>

<script type="text/javascript">

var topLevelId = '0';

function changeFields(element, force)
{
  // We can call this without an argument to start with the top level fields
  if( !$type(element) )
  {
    $$('.parent_'+topLevelId).each(function(element)
    {
      changeFields(element);
    });
    return;
  }

  // Detect if this is an input or the container
  if( element.hasClass('field_container') )
  {
    element = element.getElement('.field_input');
  }

  // If this cannot have dependents, skip
  if( !$type(element) || !$type(element.onchange) )
  {
    return;
  }

  // Get the input and params
  var params = element.id.split(/[-_]/);
  if( params.length > 3 )
  {
    params.shift();
  }
  force = ( $type(force) ? force : false );

  // Now look and see
  var option_id = element.value;

  // Iterate over children
  $$('.parent_'+params[2]).each(function(childElement)
  {
    // Forcing hide
    var nextForce;
    if( force == 'hide' || force == 'show' )
    {
      childElement.style.display = ( force == 'hide' ? 'none' : '' );
      nextForce = force;
    }

    // Hide fields not tied to the current option (but propogate hiding)
    else if( !childElement.hasClass('option_'+option_id) )
    {
      childElement.style.display = 'none';
      nextForce = 'hide';
    }

    // Otherwise show field and propogate (nothing, show?)
    else
    {
      childElement.style.display = '';
      nextForce = undefined;
    }

    changeFields(childElement, nextForce);
  });
}

window.addEvent('load', function()
{
  changeFields();
});

</script>