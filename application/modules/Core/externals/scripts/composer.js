
/* $Id: composer.js 8850 2011-04-11 23:47:44Z john $ */


var Composer = new Class({
  
  Implements : [Events, Options],

  elements : {},

  plugins : {},

  options : {
    lang : {},
    overText : true,
    allowEmptyWithoutAttachment : false,
    allowEmptyWithAttachment : true,
    hideSubmitOnBlur : true,
    submitElement : false
  },

  initialize : function(element, options) {
    this.setOptions(options);
    this.elements = new Hash(this.elements);
    this.plugins = new Hash(this.plugins);
    
    this.elements.textarea = $(element);
    this.elements.textarea.store('Composer');

    this.attach();
    this.getTray();
    this.getMenu();

    this.pluginReady = false;

    this.getForm().addEvent('submit', function(e) {
      if( this.pluginReady ) {
        if( !this.options.allowEmptyWithAttachment && this.getContent() == '' ) {
          e.stop();
          return;
        }
      } else {
        if( !this.options.allowEmptyWithoutAttachment && this.getContent() == '' ) {
          e.stop();
          return;
        }
      }
      this.saveContent();
    }.bind(this));
  },

  getMenu : function() {
    if( !$type(this.elements.menu) ) {
      this.elements.menu = $try(function(){
        return $(this.options.menuElement);
      }.bind(this));

      if( !$type(this.elements.menu) ) {
        this.elements.menu = new Element('div',{
          'id' : 'compose-menu',
          'class' : 'compose-menu'
        }).inject(this.getForm(), 'after');
      }
    }
    return this.elements.menu;
  },

  getTray : function() {
    if( !$type(this.elements.tray) ) {
      this.elements.tray = $try(function(){
        return $(this.options.trayElement);
      }.bind(this));

      if( !$type(this.elements.tray) ) {
        this.elements.tray =  new Element('div',{
          'id' : 'compose-tray',
          'class' : 'compose-tray',
          'styles' : {
            'display' : 'none'
          }
        }).inject(this.getForm(), 'after');
      }
    }
    return this.elements.tray;
  },

  getInputArea : function() {
    if( !$type(this.elements.inputarea) ) {
      var form = this.elements.textarea.getParent('form');
      this.elements.inputarea = new Element('div', {
        'styles' : {
          'display' : 'none'
        }
      }).inject(form);
    }
    return this.elements.inputarea;
  },

  getForm : function() {
    return this.elements.textarea.getParent('form');
  },



  // Editor
  
  attach : function() {
    var size = this.elements.textarea.getSize();

    // Modify textarea
    this.elements.textarea.addClass('compose-textarea').setStyle('display', 'none');

    // Create container
    this.elements.container = new Element('div', {
      'id' : 'compose-container',
      'class' : 'compose-container',
      'styles' : {
        
      }
    });
    this.elements.container.wraps(this.elements.textarea);

    // Create body
    this.elements.body = new Element('div', {
      'class' : 'compose-content',
      'styles' : {
        'display' : 'block'
      },
      'events' : {
        'keypress' : function(event) {
          if( event.key == 'a' && event.control ) {
            // FF only
            if( Browser.Engine.gecko ) {
              fix_gecko_select_all_contenteditable_bug(this, event);
            }
          }
        }
      }
    }).inject(this.elements.textarea, 'before');

    var self = this;
    this.elements.body.addEvent('blur', function(e) {
      if( '' == this.get('html').replace(/\s/, '').replace(/<[^<>]+?>/ig, '') )
      {
        if( !Browser.Engine.trident ) {
          this.set('html', '<br />');
        }
        if( self.options.hideSubmitOnBlur ) {
          (function() {
            if( !self.hasActivePlugin() ) {
              self.getMenu().setStyle('display', 'none');
            }
          }).delay(250);
        }
      }
    });

    if( self.options.hideSubmitOnBlur ) {
      this.getMenu().setStyle('display', 'none');
      this.elements.body.addEvent('focus', function(e) {
        self.getMenu().setStyle('display', '');
      });
    }
    
    $(this.elements.body);
    this.elements.body.contentEditable = true;
    this.elements.body.designMode = 'On';
    
    ['MouseUp', 'MouseDown', 'ContextMenu', 'Click', 'Dblclick', 'KeyPress', 'KeyUp', 'KeyDown'].each(function(eventName) {
      var method = (this['editor' + eventName] || function(){}).bind(this);
      this.elements.body.addEvent(eventName.toLowerCase(), method);
    }.bind(this));

    this.setContent(this.elements.textarea.value);
    
    this.selection = new Composer.Selection(this.elements.body);

    if( this.options.overText ) {
      new Composer.OverText(this.elements.body, $merge({
        textOverride : this._lang('Post Something...'),
        poll : true,
        positionOptions: {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -4 : 4 ),
            y: 2
          }
        }
      }, this.options.overTextOptions));
    }
    
    this.fireEvent('attach', this);
    
    this.plugins.each(function(){

    });

  },

  detach : function() {
    this.saveContent();
    this.textarea.setStyle('display', '').removeClass('compose-textarea').inject(this.container, 'before');
    this.container.dispose();
    this.fireEvent('detach', this);
    return this;
  },

  focus: function(){
    // needs the delay to get focus working
    (function(){
      this.elements.body.focus();
      this.fireEvent('focus', this);
    }).bind(this).delay(10);
    return this;
  },



  // Content
  
  getContent: function(){
    return this.cleanup(this.elements.body.get('html'));
  },

  setContent: function(newContent){
    if( !newContent.trim() && !Browser.Engine.trident ) newContent = '<br />';
    this.elements.body.set('html', newContent);
    return this;
  },

  saveContent: function(){
    this.elements.textarea.set('value', this.getContent());
    return this;
  },

  cleanup : function(html) {
    // @todo
    return html
      .replace(/<(br|p|div)[^<>]*?>/ig, "\r\n")
      .replace(/<[^<>]+?>/ig, ' ')
      .replace(/(\r\n?|\n){3,}/ig, "\n\n")
      .trim();
  },



  // Plugins

  addPlugin : function(plugin) {
    var key = plugin.getName();
    this.plugins.set(key, plugin);
    plugin.setComposer(this);
    return this;
  },

  addPlugins : function(plugins) {
    plugins.each(function(plugin) {
      this.addPlugin(plugin);
    }.bind(this));
  },

  getPlugin : function(name) {
    return this.plugins.get(name);
  },

  activate : function(name) {
    this.deactivate();
    this.getMenu().setStyle();
    this.plugins.get(name).activate();
  },

  deactivate : function() {
    this.plugins.each(function(plugin) {
      plugin.deactivate();
    });
    this.getTray().empty();
  },

  signalPluginReady : function(state) {
    this.pluginReady = state;
  },

  hasActivePlugin : function() {
    var active = false;
    this.plugins.each(function(plugin) {
      active = active || plugin.active;
    });
    return active;
  },



  // Key events

  editorMouseUp: function(e){
    this.fireEvent('editorMouseUp', e);
  },

  editorMouseDown: function(e){
    this.fireEvent('editorMouseDown', e);
  },

  editorContextMenu: function(e){
    this.fireEvent('editorContextMenu', e);
  },

  editorClick: function(e){
    // make images selectable and draggable in Safari
    if (Browser.Engine.webkit){
      var el = e.target;
      if (el.get('tag') == 'img'){
        this.selection.selectNode(el);
      }
    }

    this.fireEvent('editorClick', e);
  },

  editorDoubleClick: function(e){
    this.fireEvent('editorDoubleClick', e);
  },

  editorKeyPress: function(e){
    this.keyListener(e);
    this.fireEvent('editorKeyPress', e);
  },

  editorKeyUp: function(e){
    this.fireEvent('editorKeyUp', e);
  },

  editorKeyDown: function(e){
    if (e.key == 'enter'){
      /*
      if (this.options.paragraphise && !e.shift){
        if (Browser.Engine.gecko || Browser.Engine.webkit){
          var node = this.selection.getNode();
          var blockEls = /^(H[1-6]|P|DIV|ADDRESS|PRE|FORM|TABLE|LI|OL|UL|TD|CAPTION|BLOCKQUOTE|CENTER|DL|DT|DD)$/;
          var isBlock = node.getParents().include(node).some(function(el){
            return el.nodeName.test(blockEls);
          });
          if (!isBlock) this.execute('insertparagraph');
        }
      } else {
        if (Browser.Engine.trident){
          var r = this.selection.getRange();
          var node = this.selection.getNode();
          if (node.get('tag') != 'li'){
            if (r){
              this.selection.insertContent('<br>');
              this.selection.collapse(false);
            }
          }
          e.preventDefault();
        }
      }
      */
    }

    this.fireEvent('editorKeyDown', e);
  },

  keyListener: function(e){
    
  },
  

  _lang : function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }

      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }

      if( arguments.length <= 1 ) {
        return string;
      }

      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }

      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  }
});



Composer.Selection = new Class({

  initialize: function(win){
    this.win = win;
  },

  getSelection: function(){
    //this.win.focus();
    return window.getSelection();
  },

  getRange: function(){
    var s = this.getSelection();

    if (!s) return null;

    try {
      return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
    } catch(e) {
      // IE bug when used in frameset
      return document.body.createTextRange();
    }
  },

  setRange: function(range){
    if (range.select){
      $try(function(){
        range.select();
      });
    } else {
      var s = this.getSelection();
      if (s.addRange){
        s.removeAllRanges();
        s.addRange(range);
      }
    }
  },

  selectNode: function(node, collapse){
    var r = this.getRange();
    var s = this.getSelection();

    if (r.moveToElementText){
      $try(function(){
        r.moveToElementText(node);
        r.select();
      });
    } else if (s.addRange){
      collapse ? r.selectNodeContents(node) : r.selectNode(node);
      s.removeAllRanges();
      s.addRange(r);
    } else {
      s.setBaseAndExtent(node, 0, node, 1);
    }

    return node;
  },

  isCollapsed: function(){
    var r = this.getRange();
    if (r.item) return false;
    return r.boundingWidth == 0 || this.getSelection().isCollapsed;
  },

  collapse: function(toStart){
    var r = this.getRange();
    var s = this.getSelection();

    if (r.select){
      r.collapse(toStart);
      r.select();
    } else {
      toStart ? s.collapseToStart() : s.collapseToEnd();
    }
  },

  getContent: function(){
    var r = this.getRange();
    var body = new Element('body');

    if (this.isCollapsed()) return '';

    if (r.cloneContents){
      body.appendChild(r.cloneContents());
    } else if ($defined(r.item) || $defined(r.htmlText)){
      body.set('html', r.item ? r.item(0).outerHTML : r.htmlText);
    } else {
      body.set('html', r.toString());
    }

    var content = body.get('html');
    return content;
  },

  getText : function(){
    var r = this.getRange();
    var s = this.getSelection();

    return this.isCollapsed() ? '' : r.text || s.toString();
  },

  getNode: function(){
    var r = this.getRange();

    if (!Browser.Engine.trident){
      var el = null;

      if (r){
        el = r.commonAncestorContainer;

        // Handle selection a image or other control like element such as anchors
        if (!r.collapsed)
          if (r.startContainer == r.endContainer)
            if (r.startOffset - r.endOffset < 2)
              if (r.startContainer.hasChildNodes())
                el = r.startContainer.childNodes[r.startOffset];

        while ($type(el) != 'element') el = el.parentNode;
      }

      return $(el);
    }

    return $(r.item ? r.item(0) : r.parentElement());
  },

  insertContent: function(content){
    var r = this.getRange();

    if (r.insertNode){
      r.deleteContents();
      r.insertNode(r.createContextualFragment(content));
    } else {
      // Handle text and control range
      (r.pasteHTML) ? r.pasteHTML(content) : r.item(0).outerHTML = content;
    }
  }

});


Composer.OverText = new Class({

  Extends : OverText,

  test : function() {
    var v;
    if( !$type(this.options.isPlainText) || !this.options.isPlainText ) {
      v = this.element.get('html').replace(/\s+/, '').replace(/<br.*?>/, '');
    } else {
      v = !this.parent();
    }
    return !v;
  },
  
  hide: function(suppressFocus, force){
    if (this.text && (this.text.isDisplayed() && (!this.element.get('disabled') || force))){
      this.text.hide();
      this.fireEvent('textHide', [this.text, this.element]);
      this.pollingPaused = true;
      try {
        this.element.fireEvent('focus');
        this.element.focus();
      } catch(e){} //IE barfs if you call focus on hidden elements
    }
    return this;
  }

})


Composer.Plugin = {};

Composer.Plugin.Interface = new Class({

  Implements : [Options, Events],

  name : 'interface',

  active : false,

  composer : false,

  options : {
    loadingImage : 'application/modules/Core/externals/images/loading.gif'
  },
  
  elements : {},

  persistentElements : ['activator', 'loadingImage'],

  params : {},

  initialize : function(options) {
    this.params = new Hash();
    this.elements = new Hash();
    this.reset();
    this.setOptions(options);
  },

  getName : function() {
    return this.name;
  },

  setComposer : function(composer) {
    this.composer = composer;
    this.attach();
    return this;
  },

  getComposer : function() {
    if( !this.composer ) throw "No composer defined";
    return this.composer;
  },

  attach : function() {
    this.reset();
  },

  detach : function() {
    this.reset();
    if( this.elements.activator ) {
      this.elements.activator.destroy();
      this.elements.erase('menu');
    }
  },

  reset : function() {
    this.elements.each(function(element, key) {
      if( $type(element) == 'element' && !this.persistentElements.contains(key) ) {
        element.destroy();
        this.elements.erase(key);
      }
    }.bind(this));
    this.params = new Hash();
    this.elements = new Hash();
  },

  activate : function() {
    if( this.active ) return;
    this.active = true;

    this.reset();

    this.getComposer().getTray().setStyle('display', '');
    this.getComposer().getMenu().setStyle('display', 'none');
    var submitButtonEl = $(this.getComposer().options.submitElement);
    if( submitButtonEl ) {
      submitButtonEl.setStyle('display', 'none');
    }

    this.getComposer().getMenu().setStyle('border', 'none');

    this.getComposer().getMenu().getElements('.compose-activator').each(function(element) {
      element.setStyle('display', 'none');
    });
   
    switch( $type(this.options.loadingImage) ) {
      case 'element':
        break;
      case 'string':
        this.elements.loadingImage = new Asset.image(this.options.loadingImage, {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image'
        });
        break;
      default:
        this.elements.loadingImage = new Asset.image('loading.gif', {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image'
        });
        break;
    }
  },

  deactivate : function() {
    if( !this.active ) return;
    this.active = false;

    this.reset();

    this.getComposer().getTray().setStyle('display', 'none');
    this.getComposer().getMenu().setStyle('display', '');
    var submitButtonEl = $(this.getComposer().options.submitElement);
    if( submitButtonEl ) {
      submitButtonEl.setStyle('display', '');
    }
    this.getComposer().getMenu().getElements('.compose-activator').each(function(element) {
      element.setStyle('display', '');
    });

    this.getComposer().getMenu().set('style', '');

    this.getComposer().signalPluginReady(false);
  },

  ready : function() {
    this.getComposer().signalPluginReady(true);
    this.getComposer().getMenu().setStyle('display', '');

    var submitEl = $(this.getComposer().options.submitElement);
    if( submitEl ) {
      submitEl.setStyle('display', '');
    }
  },


  // Utility

  makeActivator : function() {
    if( !this.elements.activator ) {
      this.elements.activator = new Element('a', {
        'id' : 'compose-' + this.getName() + '-activator',
        'class' : 'compose-activator buttonlink',
        'href' : 'javascript:void(0);',
        'html' : this._lang(this.options.title),
        'events' : {
          'click' : this.activate.bind(this)
        }
      }).inject(this.getComposer().getMenu());
    }
  },

  makeMenu : function() {
    if( !this.elements.menu ) {
      var tray = this.getComposer().getTray();

      this.elements.menu = new Element('div', {
        'id' : 'compose-' + this.getName() + '-menu',
        'class' : 'compose-menu'
      }).inject(tray);

      this.elements.menuTitle = new Element('span', {
        'html' : this._lang(this.options.title) + ' ('
      }).inject(this.elements.menu);

      this.elements.menuClose = new Element('a', {
        'href' : 'javascript:void(0);',
        'html' : this._lang('cancel'),
        'events' : {
          'click' : function(e) {
            e.stop();
            this.getComposer().deactivate();
          }.bind(this)
        }
      }).inject(this.elements.menuTitle);

      this.elements.menuTitle.appendText(')');
    }
  },
  
  makeBody : function() {
    if( !this.elements.body ) {
      var tray = this.getComposer().getTray();
      this.elements.body = new Element('div', {
        'id' : 'compose-' + this.getName() + '-body',
        'class' : 'compose-body'
      }).inject(tray);
    }
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
        'html' : this._lang('Loading...')
      }).inject(this.elements.loading);
    }
  },

  makeError : function(message, action) {
    if( !$type(action) ) action = 'empty';
    message = message || 'An error has occurred';
    message = this._lang(message);
    
    this.elements.error = new Element('div', {
      'id' : 'compose-' + this.getName() + '-error',
      'class' : 'compose-error',
      'html' : message
    }).inject(this.elements.body);
  },

  makeFormInputs : function(data) {
    this.ready();
    
    this.getComposer().getInputArea().empty();

    data.type = this.getName();

    $H(data).each(function(value, key) {
      this.setFormInputValue(key, value);
    }.bind(this));
  },

  setFormInputValue : function(key, value) {
    var elName = 'attachmentForm' + key.capitalize();
    if( !this.elements.has(elName) ) {
      this.elements.set(elName, new Element('input', {
        'type' : 'hidden',
        'name' : 'attachment[' + key + ']',
        'value' : value || ''
      }).inject(this.getComposer().getInputArea()));
    }
    this.elements.get(elName).value = value;
  },

  _lang : function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }

      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }

      if( arguments.length <= 1 ) {
        return string;
      }

      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }

      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  }
  
});