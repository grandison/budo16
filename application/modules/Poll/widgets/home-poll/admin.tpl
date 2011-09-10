<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Poll
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: admin.tpl 8221 2011-01-15 00:24:02Z john $
 * @author     John
 */
?>
<div style="padding: 10px;">

  <?php if( !$this->values ): ?>
    
    <script type="text/javascript">
      window.addEvent('domready', function() {
        var params = parent.pullWidgetParams();
        var info = parent.pullWidgetTypeInfo();
        
        // Populate params
        $H(params).each(function(value, key) {
          if( $type(value) == 'array' ) {
            value.each(function(svalue){
              if( $(key + '-' + svalue) ) {
                $(key + '-' + svalue).set('checked', true);
              }
            });
          } else if( $(key) ) {
            $(key).value = value;
          } else if( $(key + '-' + value) ) {
            $(key + '-' + value).set('checked', true);
          }
        });
        $$('.form-description').set('html', info.description);

        // Has a poll selected already
        if( 'poll_id' in params && params.poll_id ) {
          $('poll-home-poll-search').setStyle('display', 'none');
          selectPoll(params.poll_id);
        } else {
          
        }
        
      });

      var searchPoll = function(query) {
        var request = new Request.JSON({
          url : '<?php echo $this->url(array('module' => 'poll', 'controller' => 'manage', 'action' => 'suggest'), 'admin_default', true) ?>',
          data : {
            format : 'json',
            query : query
          },
          onComplete : function(responseJSON) {
            $('poll-home-poll-search-results').setStyle('display', '');
            $H(responseJSON.data).each(function(title, id) {
              (new Element('a', {
                'html' : title,
                'events' : {
                  'click' : function(event) {
                    selectPoll(id);
                  }
                }
              })).inject(
                (new Element('li', {
                })).inject(
                  $('poll-home-poll-search-results').getElement('ul')
                )
              );
            });
            parent.Smoothbox.instance.doAutoResize();
          }
        });
        request.send();
      }

      var deselectPoll = function() {
        $('poll_id').set('value', '');
        $('poll-home-poll-search').setStyle('display', '');
        $('poll-home-poll-search-results').setStyle('display', 'none');
        $('poll-home-poll-form').setStyle('display', 'none');
        $('poll-home-poll-selected').setStyle('display', 'none');
      }
      
      var selectPoll = function(poll_id, info) {
        if( !info ) {
          getPollInfo(poll_id, selectPoll);
          return;
        }
        $('poll_id').set('value', poll_id);
        $('title').set('value', info.title);
        $('poll-home-poll-search').setStyle('display', 'none');
        $('poll-home-poll-search-results').setStyle('display', 'none');
        $('poll-home-poll-form').setStyle('display', '');
        $('poll-home-poll-selected').setStyle('display', '');

        $('poll-home-poll-selected').empty();

        // Create photo
        if( 'photo' in info ) {
          (new Element('img', {
            src : info.photo
          })).inject(
            (new Element('div', {
              'class' : 'photo'
            })).inject(
              $('poll-home-poll-selected')
            )
          );
        }
        // Create info wrapper
        var infoWrapper = new Element('div', {
          'class' : 'info'
        });
        infoWrapper.inject($('poll-home-poll-selected'));

        // Create title
        (new Element('a', {
          html : info.title,
          href : info.href,
          target : '_blank'
        })).inject(
          (new Element('div', {
            'class' : 'title'
          })).inject(
            infoWrapper
          )
        );

        // Create description
        (new Element('div', {
          html : info.description,
          'class' : 'description'
        })).inject(
          infoWrapper
        );

        parent.Smoothbox.instance.doAutoResize();
      }

      var getPollInfo = function(poll_id, callback) {
        var request = new Request.JSON({
          url : '<?php echo $this->url(array('module' => 'poll', 'controller' => 'manage', 'action' => 'info'), 'admin_default', true) ?>',
          data : {
            format : 'json',
            poll_id : poll_id
          },
          onComplete : function(responseJSON) {
            if( 'status' in responseJSON && responseJSON.status ) {
              callback(poll_id, responseJSON);
            }
          }
        });
        request.send();
      }
    </script>

    <div id="poll-home-poll-form" style="display:none;">
      <?php echo $this->form->render($this) ?>
    </div>

    <div id="poll-home-poll-search">
      <h3>
        <?php echo $this->translate('Home Poll') ?>
      </h3>
      <p>
        <?php echo $this->translate('Search for a poll to display using the text box below.') ?>
      </p>
      <div>
        <input type="text" name="query" id="query" />
      </div>
      <div>
        <button onclick="searchPoll($('query').value)">Search</button>
      </div>
    </div>

    <div id="poll-home-poll-search-results" style="display:none;">
      <ul>

      </ul>
    </div>

    <div id="poll-home-poll-selected" style="display:none;">

    </div>

  <?php else: ?>

    <script type="text/javascript">
      parent.setWidgetParams(<?php echo Zend_Json::encode($this->values) ?>);
      parent.Smoothbox.close();
    </script>

  <?php endif; ?>



  <?php /*
  <?php if( $this->form ): ?>

    <script type="text/javascript">
      window.addEvent('domready', function() {
        var params = parent.pullWidgetParams();
        var info = parent.pullWidgetTypeInfo();
        $H(params).each(function(value, key) {
          if( $type(value) == 'array' ) {
            value.each(function(svalue){
              if( $(key + '-' + svalue) ) {
                $(key + '-' + svalue).set('checked', true);
              }
            });
          } else if( $(key) ) {
            $(key).value = value;
          } else if( $(key + '-' + value) ) {
            $(key + '-' + value).set('checked', true);
          }
        });
        $$('.form-description').set('html', info.description);
      })
    </script>

    <?php echo $this->form->render($this) ?>

  <?php elseif( $this->values ): ?>

    <script type="text/javascript">
      parent.setWidgetParams(<?php echo Zend_Json::encode($this->values) ?>);
      parent.Smoothbox.close();
    </script>

  <?php else: ?>

    <?php echo $this->translate("Error: no values") ?>

  <?php endif; ?>
   *
   */ ?>

</div>