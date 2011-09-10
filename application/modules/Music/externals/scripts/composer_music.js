
/* $Id: composer_music.js 7924 2010-12-04 03:19:53Z john $ */


Composer.Plugin.Music = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'music',

  options : {
    title : 'Add Music',
    lang : {},
    requestOptions : false,
    fancyUploadEnabled : true,
    fancyUploadOptions : {}
  },

  initialize : function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.parent();
    this.makeActivator();
    return this;
  },

  detach : function() {
    this.parent();
    return this;
  },

  activate : function() {
    if( this.active ) return;
    this.parent();

    this.makeMenu();
    this.makeBody();

    // Generate form
    var fullUrl = this.options.requestOptions.url;
    this.elements.form = new Element('form', {
      'id' : 'compose-music-form',
      'class' : 'compose-form',
      'method' : 'post',
      'action' : fullUrl,
      'enctype' : 'multipart/form-data'
    }).inject(this.elements.body);

    this.elements.formInput = new Element('input', {
      'id' : 'compose-music-form-input',
      'class' : 'compose-form-input',
      'type' : 'file',
      'name' : 'Filedata',
      'events' : {
        'change' : this.doRequest.bind(this)
      }
    }).inject(this.elements.form);

    // Try to init fancyupload
    if( this.options.fancyUploadEnabled && this.options.fancyUploadOptions ) {
      this.elements.formFancyContainer = new Element('div', {
        'styles' : {
          //'display' : 'none',
          'visibility' : 'hidden'
        }
      }).inject(this.elements.body);

      // This is the browse button
      this.elements.formFancyFile = new Element('a', {
        'href' : 'javascript:void(0);',
        'id' : 'compose-music-form-fancy-file',
        'class' : 'buttonlink',
        'html' : this._lang('Select File')
      }).inject(this.elements.formFancyContainer);

      // This is the status
      this.elements.formFancyStatus = new Element('div', {
        'html' :
'<div style="display:none;">\n\
  <div class="demo-status-overall" id="demo-status-overall" style="display:none;">\n\
    <div class="overall-title"></div>\n\
    <img src="" class="progress overall-progress" />\n\
  </div>\n\
  <div class="demo-status-current" id="demo-status-current" style="display:none;">\n\
    <div class="current-title"></div>\n\
    <img src="" class="progress current-progress" />\n\
  </div>\n\
  <div class="current-text"></div>\n\
</div>'
      }).inject(this.elements.formFancyContainer);

      // This is the list
      this.elements.formFancyList = new Element('div', {
        'styles' : {
          'display' : 'none'
        }
      }).inject(this.elements.formFancyContainer);

      var self = this;
      var opts = $merge({
        url : fullUrl,
        appendCookieData: true,
        multiple : false,
        typeFilter: {
          'Music (*.mp3, *.m4a)': '*.mp3; *.m4a;'
        },
        target : this.elements.formFancyFile,
        container : self.elements.body,
        // Events
        onLoad : function() {
          self.elements.formFancyContainer.setStyle('display', '');
          self.elements.formFancyContainer.setStyle('visibility', 'visible');
          //self.elements.form.setStyle('display', 'none');
          self.elements.form.destroy();
          this.target.addEvents({
                  click: function() {
                          return false;
                  },
                  mouseenter: function() {
                          this.addClass('hover');
                  },
                  mouseleave: function() {
                          this.removeClass('hover');
                          this.blur();
                  },
                  mousedown: function() {
                          this.focus();
                  }
          });
        },
        onSelectSuccess : function() {
          self.makeLoading('invisible');
          //$('demo-status-overall').setStyle('display', '');
          this.start();
        },
        onFileSuccess : function(file, response) {
          var json = new Hash(JSON.decode(response, true) || {});
          self.doProcessResponse(json);
        }
      }, this.options.fancyUploadOptions);

      try {
        this.elements.formFancyUpload = new FancyUpload2(
          this.elements.formFancyStatus,
          this.elements.formFancyList,
          opts
        );
      } catch( e ) {
        //if( $type(console) ) console.log(e);
      }
    }

    /*
    this.elements.formSubmit = new Element('button', {
      'id' : 'compose-music-form-submit',
      'class' : 'compose-form-submit',
      'html' : 'Attach',
      'events' : {
        'click' : function(e) {
          e.stop();
          this.doAttach();
        }.bind(this)
      }
    }).inject(this.elements.body);
    */
  },

  deactivate : function() {
    if (this.params.song_id)
      new Request.JSON({
        url: en4.core.basePath + '/music/remove-song',
        data: {
          format: 'json',
          song_id: this.params.song_id
        }
      });
    if( !this.active ) return;
    this.parent();
  },

  doRequest : function() {
    this.elements.iframe = new IFrame({
      'name' : 'composeMusicFrame',
      'src' : 'javascript:false;',
      'styles' : {
        'display' : 'none'
      },
      'events' : {
        'load' : function() {
          this.doProcessResponse(window._composeMusicResponse);
          window._composeMusicResponse = false;
        }.bind(this)
      }
    }).inject(this.elements.body);

    window._composeMusicResponse = false;
    this.elements.form.set('target', 'composeMusicFrame');

    // Submit and then destroy form
    this.elements.form.submit();
    this.elements.form.destroy();

    // Start loading screen
    this.makeLoading();
  },

  makeLoading : function(action) {
    if( !this.elements.loading ) {
      if( action == 'empty' ) {
        this.elements.body.empty();
      } else if( action == 'hide' ) {
        this.elements.body.getChildren().each(function(element){ element.setStyle('display', 'none')});
      } else if( action == 'invisible' ) {
        this.elements.body.getChildren().each(function(element){ element.setStyle('height', '0px').setStyle('visibility', 'hidden')});
      }

      this.elements.loading = new Element('div', {
        'id' : 'compose-' + this.getName() + '-loading',
        'class' : 'compose-loading'
      }).inject(this.elements.body);

      var image = this.elements.loadingImage || (new Element('img', {
        'id' : 'compose-' + this.getName() + '-loading-image',
        'class' : 'compose-loading-image'
      }));

      image.inject(this.elements.loading);

      new Element('span', {
        'html' : this._lang('Loading song, please wait...')
      }).inject(this.elements.loading);
    }
  },

  doProcessResponse : function(responseJSON) {
    // An error occurred
    if ( ($type(responseJSON) != 'object' && $type(responseJSON) != 'hash' )) {
      if( this.elements.loading )
          this.elements.loading.destroy();
      this.makeError(this._lang('Unable to upload music. Please click cancel and try again'), 'empty');
      return;
    }

    if (  $type(parseInt(responseJSON.song_id)) != 'number' ) {
      if( this.elements.loading )
          this.elements.loading.destroy();
      //if ($type(console))
      //  console.log('responseJSON: %o', responseJSON);
      this.makeError(this._lang('Song got lost in the mail. Please click cancel and try again'), 'empty');
      return;
    }
    // Success
    /*
    this.params.set('title', responseJSON.title);
    this.params.set('description', responseJSON.description);
    this.params.set('photo_id', responseJSON.photo_id);
    this.params.set('video_id', responseJSON.video_id);
    this.elements.preview = Asset.image(responseJSON.src, {
      'id' : 'compose-video-preview-image',
      'class' : 'compose-preview-image',
      'onload' : this.doImageLoaded.bind(this)
    });
    */
    this.params.set('rawParams',  responseJSON);
    this.params.set('song_id',    responseJSON.song_id);
    this.params.set('song_title', responseJSON.song_title);
    this.params.set('song_url',   responseJSON.song_url);
    this.elements.preview = new Element('a', {
      'href': responseJSON.song_url,
      'text': responseJSON.song_title,
      'class': 'compose-music-link',
      'events' : {
        'click' : function(event) {
          event.stop();
          $(this).toggleClass('compose-music-link-playing');
          $(this).toggleClass('compose-music-link');
          var song = (responseJSON.song_url.match(/\.mp3$/)
            ? soundManager.createSound({id:'s'+responseJSON.song_id, url:responseJSON.song_url})
          : soundManager.createVideo({id:'s'+responseJSON.song_id, url:responseJSON.song_url}));
          song.togglePause();
          this.blur();
        }
      }
    });
    this.elements.preview.set('text', responseJSON.song_title);
    this.doSongLoaded();
  },

  doSongLoaded : function() {
    if( this.elements.loading )
        this.elements.loading.destroy();
    if( this.elements.formFancyContainer )
        this.elements.formFancyContainer.destroy();
    this.elements.preview.inject(this.elements.body);
    this.makeFormInputs();
  },

  makeFormInputs : function() {
    this.ready();
    this.parent({
      'song_id' : this.params.song_id
    });
  }

})