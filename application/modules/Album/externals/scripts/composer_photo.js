
/* $Id: composer_photo.js 7244 2010-09-01 01:49:53Z john $ */


Composer.Plugin.Photo = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'photo',

  options : {
    title : 'Add Photo',
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
      'id' : 'compose-photo-form',
      'class' : 'compose-form',
      'method' : 'post',
      'action' : fullUrl,
      'enctype' : 'multipart/form-data'
    }).inject(this.elements.body);
    
    this.elements.formInput = new Element('input', {
      'id' : 'compose-photo-form-input',
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
        'id' : 'compose-photo-form-fancy-file',
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
          'Images (*.jpg, *.jpeg, *.gif, *.png)': '*.jpg; *.jpeg; *.gif; *.png'
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
        this.elements.formFancyUpload = new FancyUpload2(this.elements.formFancyStatus, this.elements.formFancyList, opts);
      } catch( e ) {
        //if( $type(console) ) console.log(e);
      }
    }

    /*
    this.elements.formSubmit = new Element('button', {
      'id' : 'compose-photo-form-submit',
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
    if( !this.active ) return;
    this.parent();
  },

  doRequest : function() {
    this.elements.iframe = new IFrame({
      'name' : 'composePhotoFrame',
      'src' : 'javascript:false;',
      'styles' : {
        'display' : 'none'
      },
      'events' : {
        'load' : function() {
          this.doProcessResponse(window._composePhotoResponse);
          window._composePhotoResponse = false;
        }.bind(this)
      }
    }).inject(this.elements.body);

    window._composePhotoResponse = false;
    this.elements.form.set('target', 'composePhotoFrame');

    // Submit and then destroy form
    this.elements.form.submit();
    this.elements.form.destroy();

    // Start loading screen
    this.makeLoading();
  },

  doProcessResponse : function(responseJSON) {
    // An error occurred
    if( ($type(responseJSON) != 'hash' && $type(responseJSON) != 'object') || $type(responseJSON.src) != 'string' || $type(parseInt(responseJSON.photo_id)) != 'number' ) {
      //this.elements.body.empty();
      this.makeError(this._lang('Unable to upload photo. Please click cancel and try again'), 'empty');
      return;
      //throw "unable to upload image";
    }

    // Success
    this.params.set('rawParams', responseJSON);
    this.params.set('photo_id', responseJSON.photo_id);
    this.elements.preview = Asset.image(responseJSON.src, {
      'id' : 'compose-photo-preview-image',
      'class' : 'compose-preview-image',
      'onload' : this.doImageLoaded.bind(this)
    });
  },

  doImageLoaded : function() {
    if( this.elements.loading ) this.elements.loading.destroy();
    if( this.elements.formFancyContainer ) this.elements.formFancyContainer.destroy();
    this.elements.preview.erase('width');
    this.elements.preview.erase('height');
    this.elements.preview.inject(this.elements.body);
    this.makeFormInputs();
  },

  makeFormInputs : function() {
    this.ready();
    this.parent({
      'photo_id' : this.params.photo_id
    });
  }

})