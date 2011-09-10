<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Music
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

        // Has a playlist selected already
        if( 'playlist_id' in params && params.playlist_id ) {
          $('music-home-playlist-search').setStyle('display', 'none');
          selectPlaylist(params.playlist_id);
        } else {
          
        }
        
      });

      var searchPlaylist = function(query) {
        var request = new Request.JSON({
          url : '<?php echo $this->url(array('module' => 'music', 'controller' => 'manage', 'action' => 'suggest'), 'admin_default', true) ?>',
          data : {
            format : 'json',
            query : query
          },
          onComplete : function(responseJSON) {
            $('music-home-playlist-search-results').setStyle('display', '');
            $H(responseJSON.data).each(function(title, id) {
              (new Element('a', {
                'html' : title,
                'events' : {
                  'click' : function(event) {
                    selectPlaylist(id);
                  }
                }
              })).inject(
                (new Element('li', {
                })).inject(
                  $('music-home-playlist-search-results').getElement('ul')
                )
              );
            });
            parent.Smoothbox.instance.doAutoResize();
          }
        });
        request.send();
      }

      var deselectPlaylist = function() {
        $('playlist_id').set('value', '');
        $('music-home-playlist-search').setStyle('display', '');
        $('music-home-playlist-search-results').setStyle('display', 'none');
        $('music-home-playlist-form').setStyle('display', 'none');
        $('music-home-playlist-selected').setStyle('display', 'none');
      }
      
      var selectPlaylist = function(playlist_id, info) {
        if( !info ) {
          getPlaylistInfo(playlist_id, selectPlaylist);
          return;
        }
        $('playlist_id').set('value', playlist_id);
        $('title').set('value', info.title);
        $('music-home-playlist-search').setStyle('display', 'none');
        $('music-home-playlist-search-results').setStyle('display', 'none');
        $('music-home-playlist-form').setStyle('display', '');
        $('music-home-playlist-selected').setStyle('display', '');

        $('music-home-playlist-selected').empty();

        // Create photo
        if( 'photo' in info ) {
          (new Element('img', {
            src : info.photo
          })).inject(
            (new Element('div', {
              'class' : 'photo'
            })).inject(
              $('music-home-playlist-selected')
            )
          );
        }
        // Create info wrapper
        var infoWrapper = new Element('div', {
          'class' : 'info'
        });
        infoWrapper.inject($('music-home-playlist-selected'));

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

      var getPlaylistInfo = function(playlist_id, callback) {
        var request = new Request.JSON({
          url : '<?php echo $this->url(array('module' => 'music', 'controller' => 'manage', 'action' => 'info'), 'admin_default', true) ?>',
          data : {
            format : 'json',
            playlist_id : playlist_id
          },
          onComplete : function(responseJSON) {
            if( 'status' in responseJSON && responseJSON.status ) {
              callback(playlist_id, responseJSON);
            }
          }
        });
        request.send();
      }
    </script>

    <div id="music-home-playlist-form" style="display:none;">
      <?php echo $this->form->render($this) ?>
    </div>

    <div id="music-home-playlist-search">
      <h3>
        <?php echo $this->translate('Home Playlist') ?>
      </h3>
      <p>
        <?php echo $this->translate('Search for a playlist to display using the text box below.') ?>
      </p>
      <div>
        <input type="text" name="query" id="query" />
      </div>
      <div>
        <button onclick="searchPlaylist($('query').value)">Search</button>
      </div>
    </div>

    <div id="music-home-playlist-search-results" style="display:none;">
      <ul>

      </ul>
    </div>

    <div id="music-home-playlist-selected" style="display:none;">

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