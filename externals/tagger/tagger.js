
var Tagger = new Class({

  Implements : [Events, Options],

  options : {
    // Local options
    'title' : false,
    'description' : false,
    'transImage' : 'application/modules/Core/externals/images/trans.gif',
    'existingTags' : [],
    'tagListElement' : false,
    'linkElement' : false,
    'noTextTagHref' : true,
    'guid' : false,
    'enableCreate' : false,
    'enableDelete' : false,

    // Create
    'createRequestOptions' : {
      'url' : '',
      'data' : {
        'format' : 'json'
      }
    },
    'deleteRequestOptions' : {
      'url' : '',
      'data' : {
        'format' : 'json'
      }
    },

    // Cropper options
    'cropOptions' : {
      'preset' : [10,10,58,58],
      'min' : [48,48],
      'max' : [128,128],
      'handleSize' : 8,
      'opacity' : .6,
      'color' : '#7389AE',
      'border' : 'externals/moolasso/crop.gif'
    },

    // Autosuggest options
    'suggestProto' : 'local',
    'suggestParam' : [
      
    ],
    'suggestOptions' : {
      'minLength': 0,
      'maxChoices' : 100,
      'delay' : 250,
      'selectMode': 'pick',
      //'autocompleteType': 'message',
      'multiple': false,
      'className': 'message-autosuggest',
      'filterSubset' : true,
      'tokenFormat' : 'object',
      'tokenValueKey' : 'label',
      'injectChoice': $empty,
      'onPush' : $empty,
      
      'prefetchOnInit' : true,
      'alwaysOpen' : true,
      'ignoreKeys' : true
    }
  },

  initialize : function(el, options) {
    el = $(el);

    if( el.get('tag') != 'img' ) {
      this.image = el.getElement('img');
    } else {
      this.image = el;
    }

    this.element = el;
    this.count = 0;
    
    this.setOptions(options);

    //this.element.addEvent('')

    this.options.existingTags.each(this.addTag.bind(this));
  },

  begin : function() {
    if( !this.options.enableCreate ) return;
    this.getCrop();
    this.getForm();
    this.getSuggest();
    this.fireEvent('onBegin');
  },

  end : function() {
    if( this.crop ) {
      this.crop.destroy();
      delete this.crop;
    }
    if( this.form ) {
      this.form.destroy();
      delete this.form;
    }
    if( this.suggest ) {
      delete this.suggest;
    }
    this.fireEvent('onEnd');
  },

  getCrop : function() {
    if( !this.crop ) {
      var options = $merge(this.options.cropOptions, {
        
      });
      this.crop = new Lasso.Crop(this.image, options);
      this.crop.addEvent('resize', this.onMove.bind(this));
      this.crop.refresh();
    }

    return this.crop;
  },

  getForm : function() {
    if( !this.form ) {
      this.form = new Element('div', {
        'id' : 'tagger_form',
        'class' : 'tagger_form',
        'styles' : {
          'position' : 'absolute',
          'z-index' : '100000',
          'width' : '150px'
          //'height' : '300px'
        }
      }).inject(this.element, 'after');

      // Title
      if( this.options.title ) {
        new Element('div', {
          'class' : 'media_photo_tagform_titlebar',
          'html' : this.options.title
        }).inject(this.form);
      }

      // Container
      this.formContainer = new Element('div', {
        'class' : 'media_photo_tagform_container'
      }).inject(this.form);

      // Description
      if( this.options.description ) {
        new Element('div', {
          'class' : 'media_photo_tagform_text',
          'html' : this.options.description
        }).inject(this.formContainer);
      }

      // Input
      this.input = new Element('input', {
        'id' : 'tagger_input',
        'class' : 'tagger_input',
        'type' : 'text',
        'styles' : {
          
        }
      }).inject(this.formContainer);

      // Choices
      this.choices = new Element('div', {
        'class' : 'tagger_list'
      }).inject(this.formContainer);

      // Submit container
      var submitContainer = new Element('div', {
        'class' : 'media_photo_tagform_submits'
      }).inject(this.formContainer);

      var self = this;
      new Element('a', {
        'id' : 'tag_save',
        'href' : 'javascript:void(0);',
        'html' : en4.core.language.translate('Save'),
        'events' : {
          'click' : function() {
            var data = {}; //JSON.decode(choice.getElement('input').value);
            data.label = self.input.value;
            if( $type(data.label) && data.label != '' ) {
              data.extra = self.coords;
              self.createTag(data);
            }
          }
        }
      }).inject(submitContainer);

      new Element('a', {
        'id' : 'tag_cancel',
        'href' : 'javascript:void(0);',
        'html' : en4.core.language.translate('Cancel'),
        'events' : {
          'click' : function() {
            this.end();
          }.bind(this)
        }
      }).inject(submitContainer);

      this.input.focus();
    }
    
    return this.form;
  },

  getSuggest : function() {
    if( !this.suggest ) {
      var self = this;
      var options = $merge(this.options.suggestOptions, {
        'overflow' : true,
        'maxChoices' : 4,
        'customChoices' : this.choices,
        'injectChoice' : function(token) {
          var choice = new Element('li', {
            'class': 'autocompleter-choices',
            //'value': token.id,
            'html': token.photo || '',
            'id': token.guid
          });
          new Element('div', {
            'html' : this.markQueryValue(token.label),
            'class' : 'autocompleter-choice'
          }).inject(choice);
          new Element('input', {
            'type' : 'hidden',
            'value' : JSON.encode(token)
          }).inject(choice);
          this.addChoiceEvents(choice).inject(this.choices);
          choice.store('autocompleteChoice', token);
        },
        'onChoiceSelect' : function(choice) {
          var data = JSON.decode(choice.getElement('input').value);
          data.extra = self.coords;
          self.createTag(data);
          //alert(choice.getElement('input').value);
        },
        'emptyChoices' : function() {
          this.fireEvent('onHide', [this.element, this.choices]);
        },
        'onCommand' : function(e) {
          switch (e.key) {
            case 'enter':
              self.createTag({
                label : self.input.value,
                extra : self.coords
              });
              break;
          }
        }
      });

      if( this.options.suggestProto == 'local' ) {
        this.suggest = new Autocompleter.Local(this.input, this.options.suggestParam, options);
      } else if( this.options.suggestProto == 'request.json' ) {
        this.suggest = new Autocompleter.Request.JSON(this.input, this.options.suggestParam, options);
      }
    }

    return this.suggest;
  },

  getTagList : function() {
    if( !this.tagList ) {
      if( !this.options.tagListElement ) {
        this.tagList = new Element('div', {
          'class' : 'tag_list'
        }).inject(this.element, 'after');
      } else {
        this.tagList = $(this.options.tagListElement);
      }
    }

    return this.tagList;
  },

  onMove : function(coords) {
    this.coords = coords;
    var pos = {x:0,y:0}; //this.element.getPosition();
    var form = this.getForm();
    form.setStyles({
      'top' : pos.y + coords.y + 20,
      'left' : pos.x + coords.x + coords.w + 20
    });
  },




  // Tagging stuff

  addTag : function(params) {
    // Required: id, text, x, y, w, h

    if ( 'object' != $type(params) ) {
      //alert('This entry has already been tagged.');
      return;
    }

    var baseX = 0, baseY = 0, baseW = 0, baseH = 0;
    
    ["x", "y", "w", "h"].each(function(key) {
      params.extra[key] = parseInt(params.extra[key]);
    });

    if( this.options.noTextTagHref && params.tag_type == 'core_tag' ) {
      delete params.href;
    }

    // Make tag
    var tag = new Element('div', {
      'id' : 'tag_' + params.id,
      'class' : 'tag_div',
      'html' : '<img src="'+this.options.transImage+'" width="100%" height="100%" />',
      'styles' : {
        'position' : 'absolute',
        'width' : params.extra.w,
        'height' : params.extra.h,
        'top' : baseY + params.extra.y,
        'left' : baseX + params.extra.x
      },
      'events' : {
        'mouseover' : function() {
          this.showTag(params.id);
        }.bind(this),
        'mouseout' : function() {
          this.hideTag(params.id);
        }.bind(this)
      }
    }).inject(this.element, 'after');

    // Make label
    // Note: we need to use visibility hidden to position correctly in IE
    var label = new Element("span", {
      'id' : 'tag_label_' + params.id,
      'class' : 'tag_label',
      'html' : params.text,
      'styles' : {
        'position' : 'absolute'
      }
    }).inject(this.element, 'after');

    var labelPos = {};
    labelPos.top = ( baseY + params.extra.y + tag.getSize().y );
    labelPos.left = Math.round( ( baseX + params.extra.x ) + ( tag.getSize().x / 2 ) - (label.getSize().x / 2) );

    if( this.element.getSize().y < labelPos.top.toInt() + 20 ){
      labelPos.top = baseY + params.extra.y - label.getSize().y;
    }

    label.setStyles(labelPos);

    this.hideTag(params.id);

    var isFirst = ( !$type(this.count) || this.count == 0 );
    this.getTagList().setStyle('display', '');

    // Make list
    if( !isFirst ) new Element('span', {
      'id' : 'tag_comma_' + params.id,
      'class' : 'tag_comma',
      'html' : ','
    }).inject(this.getTagList());

    // Make other thingy
    var info = new Element('span', {
      'id' : 'tag_info_' + params.id,
      'class' : 'tag_info media_tag_listcontainer'
    }).inject(this.getTagList());
    
    var activator = new Element('a', {
      'id' : 'tag_activator_' + params.id,
      'class' : 'tag_activator',
      'href' : params.href || null,
      'html' : params.text,
      'events' : {
        'mouseover' : function() {
          this.showTag(params.id);
        }.bind(this),
        'mouseout' : function() {
          this.hideTag(params.id);
        }.bind(this)
      }
    }).inject(info);

    // Delete
    if( this.checkCanRemove(params.id) )
    {
      info.appendText(' (');
      var destroyer = new Element('a', {
        'id' : 'tag_destroyer_' + params.id,
        'class' : 'tag_destroyer albums_tag_delete',
        'href' : 'javascript:void(0);',
        'html' : en4.core.language.translate('delete'),
        'events' : {
          'click' : function() {
            this.removeTag(params.id);
          }.bind(this)
        }
      }).inject(info);
      info.appendText(')');
    }
    
    this.count++;
  },

  createTag : function(params) {
    if( !this.options.enableCreate ) return;

    // Send request
    var requestOptions = $merge(this.options.createRequestOptions, {
      'data' : $merge(params, {
        
      }),
      'onComplete' : function(responseJSON) {
        this.addTag(responseJSON);
      }.bind(this)
    });
    var request = new Request.JSON(requestOptions);
    request.send();

    // End tagging
    this.end();
  },

  removeTag : function(id) {

    if( !this.checkCanRemove(id) ) return;

    // Remove from frontend
    var next = $('tag_info_' + id).getNext();
    if( next && next.get('html').trim() == ',' ) next.destroy();
    $('tag_' + id).destroy();
    $('tag_label_' + id).destroy();
    $('tag_info_' + id).destroy();
    this.count--;

    // Send request
    var requestOptions = $merge(this.options.deleteRequestOptions, {
      'data' : {
        'tagmap_id' : id
      },
      'onComplete' : function(responseJSON) {
        
      }.bind(this)
    });
    var request = new Request.JSON(requestOptions);
    request.send();
  },

  checkCanRemove : function(id) {

    // Check if can remove
    var tagData;
    this.options.existingTags.each(function(datum) {
      if( datum.tagmap_id == id ) {
        tagData = datum;
      }
    });

    if( this.options.enableDelete ) return true;

    if( tagData ) {
      if( tagData.tag_type + '_' + tagData.tag_id == this.options.guid ) return true;
      if( tagData.tagger_type + '_' + tagData.tagger_id == this.options.guid ) return true;
    }
    
    return false;
  },

  showTag : function(id) {
    $('tag_' + id)/*.addClass('tag_div')*/.removeClass('tag_div_hidden');
    $('tag_label_' + id)/*.addClass('tag_label')*/.removeClass('tag_label_hidden');
  },

  hideTag : function(id) {
    $('tag_' + id).addClass('tag_div_hidden')/*.removeClass('tag_div')*/;
    $('tag_label_' + id).addClass('tag_label_hidden')/*.removeClass('tag_label')*/;
  }

});