<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: _jsSwitch.tpl 8728 2011-03-28 17:22:05Z char $
 * @author     John
 */
?>

<script type="text/javascript">

var topLevelId = '<?php echo sprintf('%d', (int) @$this->topLevelId) ?>';
var topLevelValue = '<?php echo sprintf('%d', (int) @$this->topLevelValue) ?>';
var elementCache = {};

function getFieldsElements(selector)
{
  if( selector in elementCache || $type(elementCache[selector]) ) {
    return elementCache[selector];
  } else {
    return elementCache[selector] = $$(selector);
  }
}

function changeFields(element, force)
{
  element = $(element);
  
  // We can call this without an argument to start with the top level fields
  if( !$type(element) ) {
    getFieldsElements('.parent_' + topLevelId).each(function(element) {
      changeFields(element);
    });
    return;
  }

  // If this cannot have dependents, skip
  if( !$type(element) || !$type(element.onchange) ) {
    return;
  }

  // Get the input and params
  var field_id = element.get('class').match(/field_([\d]+)/i)[1];
  var parent_field_id = element.get('class').match(/parent_([\d]+)/i)[1];
  var parent_option_id = element.get('class').match(/option_([\d]+)/i)[1];

  //console.log(field_id, parent_field_id, parent_option_id);

  if( !field_id || !parent_option_id || !parent_field_id ) {
    return;
  }

  force = ( $type(force) ? force : false );

  // Now look and see
  // Check for multi values
  var option_id = [];
  if( element.name.indexOf('[]') > 0 ) {
    if( element.type == 'checkbox' ) { // MultiCheckbox
      getFieldsElements('.field_' + field_id).each(function(multiEl) {
        if( multiEl.checked ) {
          option_id.push(multiEl.value);
        }
      });
    } else if( element.get('tag') == 'select' && element.multiple ) { // Multiselect
      element.getChildren().each(function(multiEl) {
        if( multiEl.selected ) {
          option_id.push(multiEl.value);
        }
      });
    }
  } else if( element.type == 'radio' ) {
    if( element.checked ) {
      option_id = [element.value];
    }
  } else {
    option_id = [element.value];
  }

  //console.log(option_id, $$('.parent_'+field_id));

  // Iterate over children
  getFieldsElements('.parent_' + field_id).each(function(childElement) {
    //console.log(childElement);
    var childContainer;
    if( childElement.getParent('form').get('class') == 'field_search_criteria' ) {
      childContainer = $try(function(){ return childElement.getParent('li').getParent('li'); });
    }
    if( !childContainer ) {
       childContainer = childElement.getParent('div.form-wrapper');
    }
    if( !childContainer ) {
      childContainer = childElement.getParent('div.form-wrapper-heading');
    }
    if( !childContainer ) {
      childContainer = childElement.getParent('li');
    }
    //console.log(option_id);
    //var childLabel = childContainer.getElement('label');
    var childOptions = childElement.get('class').match(/option_([\d]+)/gi);
    for(var i = 0; i < childOptions.length; i++) {
      for(var j = 0; j < option_id.length; j++) {
        if(childOptions[i] == "option_" + option_id[j]) {
          var childOptionId = option_id[j];
          break;
        }
      }
    }

    //var childOptionId = childElement.get('class').match(/option_([\d]+)/i)[1];
    var childIsVisible = ( 'none' != childContainer.getStyle('display') );
    var skipPropagation = false;
    //var childFieldId = childElement.get('class').match(/field_([\d]+)/i)[1];

    // Forcing hide
    var nextForce;
    if( force == 'hide' && !option_id.contains(childOptionId)) {
      if( !childElement.hasClass('field_toggle_nohide') ) {
        childContainer.setStyle('display', 'none');
      }
      nextForce = force;
    } else if( force == 'show' ) {
      childContainer.setStyle('display', '');
      nextForce = force;
    } else if( !$type(option_id) == 'array' || !option_id.contains(childOptionId) ) {
      // Hide fields not tied to the current option (but propogate hiding)
      if( !childElement.hasClass('field_toggle_nohide') ) {
        childContainer.setStyle('display', 'none');
      }
      nextForce = 'hide';
      if( !childIsVisible ) {
        skipPropagation = true;
      }
    } else {
      // Otherwise show field and propogate (nothing, show?)
      childContainer.setStyle('display', '');
      nextForce = undefined;
      //if( childIsVisible ) {
      //  skipPropagation = true;
      //}
    }

    if( !skipPropagation ) {
      changeFields(childElement, nextForce);
    }
  });

  window.fireEvent('onChangeFields');
}

window.addEvent('load', function()
{
  changeFields();
});

</script>