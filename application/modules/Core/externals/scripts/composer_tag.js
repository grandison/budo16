
/* $Id: composer_tag.js 7244 2010-09-01 01:49:53Z john $ */


Composer.Plugin.Tag = new Class({
  
  Extends : Composer.Plugin.Interface,

  name : 'tag',

  options : {
    'enabled' : false,
    requestOptions : {},
    'suggestOptions' : {
      'minLength': 0,
      'maxChoices' : 100,
      'delay' : 250,
      'selectMode': 'pick',
      'multiple': false,
      'filterSubset' : true,
      'tokenFormat' : 'object',
      'tokenValueKey' : 'label',
      'injectChoice': $empty,
      'onPush' : $empty,

      'prefetchOnInit' : true,
      'alwaysOpen' : false,
      'ignoreKeys' : true
    }
  },

  initialize : function(options) {
    this.params = new Hash(this.params);
    this.parent(options);
  },

  suggest : false,

  attach : function() {
    if( !this.options.enabled ) return;
    this.parent();

    // Poll for links
    /*
    this.interval = (function() {
      this.poll();
    }).periodical(250, this);
    */

    this.getComposer().addEvent('editorKeyPress', this.monitor.bind(this));
    this.getComposer().addEvent('editorClick', this.monitor.bind(this));
   
    /*
    this.monitorLastContent = '';
    this.monitorLastMatch = '';
    this.monitorLastKeyPress = $time();
    this.getComposer().addEvent('editorKeyPress', function() {
      this.monitorLastKeyPress = $time();
    }.bind(this));
    */
    return this;
  },

  detach : function() {
    if( !this.options.enabled ) return;
    this.parent();
    this.getComposer().removeEvent('editorKeyPress', this.monitor.bind(this));
    this.getComposer().removeEvent('editorClick', this.monitor.bind(this));
    if( this.interval ) $clear(this.interval);
    return this;
  },

  activate: $empty,

  deactivate : $empty,

  poll : function() {
    
  },

  monitor : function(e) {
    // seems like we have to do this stupid delay or otherwise the last key
    // doesn't get in the content
    (function() {
      
      var start = this.getComposer().selection.getRange().startOffset;
      var content = this.getComposer().getContent();
      var atIndexes = content.allIndexesOf('@');
      var currentIndex = false;
      var nextIndex = false;
      atIndexes.each(function(atIndex) {
        if( atIndex > start )
        {
          if( nextIndex == false ) nextIndex = atIndex;
          return;
        }
        currentIndex = atIndex;
      });
      if( currentIndex === false ) {
        this.endSuggest();
        return;
      }
      if( !nextIndex ) nextIndex = content.length;

      // Get the current at segment
      var segment = content.substring(currentIndex + 1, nextIndex);
      this.positions = {
        start : currentIndex,
        end : nextIndex
      };

      // Check next space
      var spaceIndex = segment.indexOf(' ');
      if( spaceIndex > 0 ) {
        if( currentIndex + spaceIndex < start ) {
          // If the space index is less than the cursor pos, return
          this.endSuggest();
          return;
        } else {
          // Otherwise remove after
          this.positions.end = spaceIndex;
          segment = segment.substring(0, spaceIndex);
        }
      }
      
      if( segment == '' ) {
        this.endSuggest();
        return;
      }

      this.doSuggest(segment);

    }).delay(5, this);
  },

  doSuggest : function(text) {
    //console.log(text);
    //console.log(this.positions);
    this.currentText = text;
    var suggest = this.getSuggest();
    var input = this.getHiddenInput();
    input.set('value', text);
    input.value = text;
    suggest.prefetch();
  },

  endSuggest : function() {
    this.currentText = '';
    this.positions = {};
    if( this.suggest ) {
      this.getSuggest().destroy();
      delete this.suggest;
    }
  },

  getHiddenInput : function() {
    if( !this.hiddenInput ) {
      this.hiddenInput = new Element('input', {
        'type' : 'text',
        'styles' : {
          'display' : 'none'
        }
      }).inject(document.body);
    }
    return this.hiddenInput;
  },

  getSuggest : function() {
    if( !this.suggest ) {
      var width = this.getComposer().elements.body.getSize().x;
      this.choices = new Element('div', {
        'styles' : {
          'position' : 'absolute',
          'background-color' : '#fff',
          'border' : '1px solid #aaa',
          'width' : width + 'px'
        }
      }).inject(this.getComposer().elements.body, 'after');
      
      var self = this;
      var options = $merge(this.options.suggestOptions, {
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

          //var body = self.getComposer().elements.body;


          //console.log(data);
          var replaceString = '@' + self.currentText;
          var newString = '<a href="'+data.url+'">'+data.label+'</a>&nbsp;';
          var content = self.getComposer().getContent();
          content = content.replace(replaceString, newString);
          self.getComposer().setContent(content);



          //console.log(self.positions);
          //self.createTag(data);
          //alert(choice.getElement('input').value);
        },
        'emptyChoices' : function() {
          this.fireEvent('onHide', [this.element, this.choices]);
        },
        'onCommand' : function(e) {
          switch (e.key) {
            case 'enter':
              break;
          }
        }
      });

      if( this.options.suggestProto == 'local' ) {
        this.suggest = new Autocompleter.Local(this.getHiddenInput(), this.options.suggestParam, options);
      } else if( this.options.suggestProto == 'request.json' ) {
        this.suggest = new Autocompleter.Request.JSON(this.getHiddenInput(), this.options.suggestParam, options);
      }
    }

    return this.suggest;
  }

});