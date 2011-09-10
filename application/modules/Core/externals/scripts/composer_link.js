
/* $Id: composer_link.js 7904 2010-12-03 03:36:14Z john $ */


Composer.Plugin.Link = new Class({

  Extends : Composer.Plugin.Interface,

  name : 'link',

  options : {
    title : 'Add Link',
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    // Various image filtering options
    imageMaxAspect : ( 10 / 3 ),
    imageMinAspect : ( 3 / 10 ),
    imageMinSize : 48,
    imageMaxSize : 5000,
    imageMinPixels : 2304,
    imageMaxPixels : 1000000,
    imageTimeout : 5000,
    // Delay to detect links in input
    monitorDelay : 600,
    debug : false
  },

  initialize : function(options) {
    this.params = new Hash(this.params);
    this.parent(options);
  },

  attach : function() {
    this.parent();
    this.makeActivator();

    // Poll for links
    //this.interval = (function() {
    //  this.poll();
    //}).periodical(250, this);
    this.monitorLastContent = '';
    this.monitorLastMatch = '';
    this.monitorLastKeyPress = $time();
    this.getComposer().addEvent('editorKeyPress', function() {
      this.monitorLastKeyPress = $time();
    }.bind(this));
    

    return this;
  },

  detach : function() {
    this.parent();
    if( this.interval ) $clear(this.interval);
    return this;
  },

  activate : function() {
    if( this.active ) return;
    this.parent();

    this.makeMenu();
    this.makeBody();
    
    // Generate body contents
    // Generate form
    this.elements.formInput = new Element('input', {
      'id' : 'compose-link-form-input',
      'class' : 'compose-form-input',
      'type' : 'text'
    }).inject(this.elements.body);

    this.elements.formSubmit = new Element('button', {
      'id' : 'compose-link-form-submit',
      'class' : 'compose-form-submit',
      'html' : this._lang('Attach'),
      'events' : {
        'click' : function(e) {
          e.stop();
          this.doAttach();
        }.bind(this)
      }
    }).inject(this.elements.body);

    this.elements.formInput.focus();
  },

  deactivate : function() {
    if( !this.active ) return;
    this.parent();
    
    this.request = false;
  },

  poll : function() {
    // Active plugin, ignore
    if( this.getComposer().hasActivePlugin() ) return;
    // Recent key press, ignore
    if( $time() < this.monitorLastKeyPress + this.options.monitorDelay ) return;
    // Get content and look for links
    var content = this.getComposer().getContent();
    // Same as last body
    if( content == this.monitorLastContent ) return;
    this.monitorLastContent = content;
    // Check for match
    var m = content.match(/http:\/\/([-\w\.]+)+(:\d+)?(\/([-#:\w/_\.]*(\?\S+)?)?)?/);
    if( $type(m) && $type(m[0]) && this.monitorLastMatch != m[0] )
    {
      this.monitorLastMatch = m[0];
      this.activate();
      this.elements.formInput.value = this.monitorLastMatch;
      this.doAttach();
    }
  },



  // Getting into the core stuff now

  doAttach : function() {
    var val = this.elements.formInput.value;
    if( !val ) {
      return;
    }
    if( !val.match(/^[a-zA-Z]{1,5}:\/\//) )
    {
      val = 'http://' + val;
    }
    this.params.set('uri', val)
    // Input is empty, ignore attachment
    if( val == '' ) {
      e.stop();
      return;
    }

    // Send request to get attachment
    var options = $merge({
      'data' : {
        'format' : 'json',
        'uri' : val
      },
      'onComplete' : this.doProcessResponse.bind(this)
    }, this.options.requestOptions);

    // Inject loading
    this.makeLoading('empty');

    // Send request
    this.request = new Request.JSON(options);
    this.request.send();
  },

  doProcessResponse : function(responseJSON, responseText) {
    // Handle error
    if( $type(responseJSON) != 'object' ) {
      responseJSON = {
        'status' : false
      };
    }
    this.params.set('uri', responseJSON.url);

    var title = responseJSON.title || responseJSON.url;
    var description = responseJSON.description || responseJSON.title || responseJSON.url;
    var images = responseJSON.images || [];

    this.params.set('title', title);
    this.params.set('description', description);
    this.params.set('images', images);
    this.params.set('loadedImages', []);
    this.params.set('thumb', '');

    if( images.length > 0 ) {
      this.doLoadImages();
    } else {
      this.doShowPreview();
    }
  },


  
  // Image loading
  
  doLoadImages : function() {
    // Start image load timeout
    var interval = this.doShowPreview.delay(this.options.imageTimeout, this);

    // Load them images
    this.params.loadedImages = [];

    this.params.set('assets', new Asset.images(this.params.get('images'), {
      'properties' : {
        'class' : 'compose-link-image'
      },
      'onProgress' : function(counter, index) {
        this.params.loadedImages[index] = this.params.images[index];
        // Debugging
        if( this.options.debug ) {
          console.log('Loaded - ', this.params.images[index]);
        }
      }.bind(this),
      'onError' : function(counter, index) {
        delete this.params.images[index];
      }.bind(this),
      'onComplete' : function() {
        $clear(interval);
        this.doShowPreview();
      }.bind(this)
    }));
  },


  // Preview generation
  
  doShowPreview : function() {
    var self = this;
    this.elements.body.empty();
    this.makeFormInputs();
    
    // Generate image thingy
    if( this.params.loadedImages.length > 0 ) {
      var tmp = new Array();
      this.elements.previewImages = new Element('div', {
        'id' : 'compose-link-preview-images',
        'class' : 'compose-preview-images'
      }).inject(this.elements.body);

      this.params.assets.each(function(element, index) {
        if( !$type(this.params.loadedImages[index]) ) return;
        element.addClass('compose-preview-image-invisible').inject(this.elements.previewImages);
        if( !this.checkImageValid(element) ) {
          delete this.params.images[index];
          delete this.params.loadedImages[index];
          element.destroy();
        } else {
          element.removeClass('compose-preview-image-invisible').addClass('compose-preview-image-hidden');
          tmp.push(this.params.loadedImages[index]);
          element.erase('height');
          element.erase('width');
        }
      }.bind(this));

      this.params.loadedImages = tmp;

      if( this.params.loadedImages.length <= 0 ) {
        this.elements.previewImages.destroy();
      }
    }

    this.elements.previewInfo = new Element('div', {
      'id' : 'compose-link-preview-info',
      'class' : 'compose-preview-info'
    }).inject(this.elements.body);
    
    // Generate title and description
    this.elements.previewTitle = new Element('div', {
      'id' : 'compose-link-preview-title',
      'class' : 'compose-preview-title'
    }).inject(this.elements.previewInfo);

    this.elements.previewTitleLink = new Element('a', {
      'href' : this.params.uri,
      'html' : this.params.title,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditTitle(this);
        }
      }
    }).inject(this.elements.previewTitle);

    this.elements.previewDescription = new Element('div', {
      'id' : 'compose-link-preview-description',
      'class' : 'compose-preview-description',
      'html' : this.params.description,
      'events' : {
        'click' : function(e) {
          e.stop();
          self.handleEditDescription(this);
        }
      }
    }).inject(this.elements.previewInfo);

    // Generate image selector thingy
    if( this.params.loadedImages.length > 0 ) {
      this.elements.previewOptions = new Element('div', {
        'id' : 'compose-link-preview-options',
        'class' : 'compose-preview-options'
      }).inject(this.elements.previewInfo);

      if( this.params.loadedImages.length > 1 ) {
        this.elements.previewChoose = new Element('div', {
          'id' : 'compose-link-preview-options-choose',
          'class' : 'compose-preview-options-choose',
          'html' : '<span>' + this._lang('Choose Image:') + '</span>'
        }).inject(this.elements.previewOptions);

        this.elements.previewPrevious = new Element('a', {
          'id' : 'compose-link-preview-options-previous',
          'class' : 'compose-preview-options-previous',
          'href' : 'javascript:void(0);',
          'html' : '&#171; ' + this._lang('Last'),
          'events' : {
            'click' : this.doSelectImagePrevious.bind(this)
          }
        }).inject(this.elements.previewChoose);

        this.elements.previewCount = new Element('span', {
          'id' : 'compose-link-preview-options-count',
          'class' : 'compose-preview-options-count'
        }).inject(this.elements.previewChoose);


        this.elements.previewPrevious = new Element('a', {
          'id' : 'compose-link-preview-options-next',
          'class' : 'compose-preview-options-next',
          'href' : 'javascript:void(0);',
          'html' : this._lang('Next') + ' &#187;',
          'events' : {
            'click' : this.doSelectImageNext.bind(this)
          }
        }).inject(this.elements.previewChoose);
      }

      this.elements.previewNoImage = new Element('div', {
        'id' : 'compose-link-preview-options-none',
        'class' : 'compose-preview-options-none'
      }).inject(this.elements.previewOptions);

      this.elements.previewNoImageInput = new Element('input', {
        'id' : 'compose-link-preview-options-none-input',
        'class' : 'compose-preview-options-none-input',
        'type' : 'checkbox',
        'events' : {
          'click' : this.doToggleNoImage.bind(this)
        }
      }).inject(this.elements.previewNoImage);

      this.elements.previewNoImageLabel = new Element('label', {
        'for' : 'compose-link-preview-options-none-input',
        'html' : this._lang('Don\'t show an image'),
        'events' : {
          //'click' : this.doToggleNoImage.bind(this)
        }
      }).inject(this.elements.previewNoImage);
      
      // Show first image
      this.setImageThumb(this.elements.previewImages.getChildren()[0]);
    }
  },

  checkImageValid : function(element) {
    var size = element.getSize();
    var sizeAlt = {x:element.get('width'),y:element.get('height')};
    var width = sizeAlt.x || size.x;
    var height = sizeAlt.y || size.y;
    var pixels = width * height;
    var aspect = width / height;
    
    // Debugging
    if( this.options.debug ) {
      console.log(element.get('src'), sizeAlt, size, width, height, pixels, aspect);
    }

    // Check aspect
    if( aspect > this.options.imageMaxAspect ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Aspect greater than max - ', element.get('src'), aspect, this.options.imageMaxAspect);
      }
      return false;
    } else if( aspect < this.options.imageMinAspect ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Aspect less than min - ', element.get('src'), aspect, this.options.imageMinAspect);
      }
      return false;
    }
    // Check min size
    if( width < this.options.imageMinSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Width less than min - ', element.get('src'), width, this.options.imageMinSize);
      }
      return false;
    } else if( height < this.options.imageMinSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Height less than min - ', element.get('src'), height, this.options.imageMinSize);
      }
      return false;
    }
    // Check max size
    if( width > this.options.imageMaxSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Width greater than max - ', element.get('src'), width, this.options.imageMaxSize);
      }
      return false;
    } else if( height > this.options.imageMaxSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Height greater than max - ', element.get('src'), height, this.options.imageMaxSize);
      }
      return false;
    }
    // Check  pixels
    if( pixels < this.options.imageMinPixels ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Pixel count less than min - ', element.get('src'), pixels, this.options.imageMinPixels);
      }
      return false;
    } else if( pixels > this.options.imageMaxPixels ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Pixel count greater than max - ', element.get('src'), pixels, this.options.imageMaxPixels);
      }
      return false;
    }

    return true;
  },

  doSelectImagePrevious : function() {
    if( this.elements.imageThumb && this.elements.imageThumb.getPrevious() ) {
      this.setImageThumb(this.elements.imageThumb.getPrevious());
    }
  },

  doSelectImageNext : function() {
    if( this.elements.imageThumb && this.elements.imageThumb.getNext() ) {
      this.setImageThumb(this.elements.imageThumb.getNext());
    }
  },

  doToggleNoImage : function() {
    if( !$type(this.params.thumb) ) {
      this.params.thumb = this.elements.imageThumb.src;
      this.setFormInputValue('thumb', this.params.thumb);
      this.elements.previewImages.setStyle('display', '');
      if( this.elements.previewChoose ) this.elements.previewChoose.setStyle('display', '');
    } else {
      delete this.params.thumb;
      this.setFormInputValue('thumb', '');
      this.elements.previewImages.setStyle('display', 'none');
      if( this.elements.previewChoose ) this.elements.previewChoose.setStyle('display', 'none');
    }
  },

  setImageThumb : function(element) {
    // Hide old thumb
    if( this.elements.imageThumb ) {
      this.elements.imageThumb.addClass('compose-preview-image-hidden');
    }
    if( element ) {
      element.removeClass('compose-preview-image-hidden');
      this.elements.imageThumb = element;
      this.params.thumb = element.src;
      this.setFormInputValue('thumb', element.src);
      if( this.elements.previewCount ) {
        var index = this.params.loadedImages.indexOf(element.src);
        //this.elements.previewCount.set('html', ' | ' + (index + 1) + ' of ' + this.params.loadedImages.length + ' | ');
        this.elements.previewCount.set('html', ' | ' + this._lang('%d of %d', index + 1, this.params.loadedImages.length) + ' | ');
      }
    } else {
      this.elements.imageThumb = false;
      delete this.params.thumb;
    }
  },

  makeFormInputs : function() {
    this.ready();
    this.parent({
      'uri' : this.params.uri,
      'title' : this.params.title,
      'description' : this.params.description,
      'thumb' : this.params.thumb
    });
  },

  handleEditTitle : function(element) {
    element.setStyle('display', 'none');
    var input = new Element('input', {
      'type' : 'text',
      'value' : element.get('html').trim(),
      'events' : {
        'blur' : function() {
          if( input.value.trim() != '' ) {
            this.params.title = input.value;
            element.set('html', this.params.title);
            this.setFormInputValue('title', this.params.title);
          }
          element.setStyle('display', '');
          input.destroy();
        }.bind(this)
      }
    }).inject(element, 'after');
    input.focus();
  },

  handleEditDescription : function(element) {
    element.setStyle('display', 'none');
    var input = new Element('textarea', {
      'html' : element.get('html').trim(),
      'events' : {
        'blur' : function() {
          if( input.value.trim() != '' ) {
            this.params.description = input.value;
            element.set('html', this.params.description);
            this.setFormInputValue('description', this.params.description);
          }
          element.setStyle('display', '');
          input.destroy();
        }.bind(this)
      }
    }).inject(element, 'after');
    input.focus();
  }

});