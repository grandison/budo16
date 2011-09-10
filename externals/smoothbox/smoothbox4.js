
var Smoothbox = {

  instance : false,
  
  bind : function(selector)
  {
    // All children of element
    var elements;
    if( $type(selector) == 'element' ){
      elements = selector.getElements('a.smoothbox');
    } else if( $type(selector) == 'string' ){
      elements = $$(selector);
    } else {
      elements = $$("a.smoothbox");
    }
    
    elements.each(function(el)
    {
      if( el.get('tag') != 'a' || el.retrieve('smoothboxed', false) )
      {
        return;
      }
      
      var params = $try(function(){
        var ret = JSON.decode(el.title);
        if( $type(ret.title) )
        {
          el.title = ret.title;
        }
        else
        {
          el.title = '';
        }
        return ret;
      }, function(){
        return {};
      });
      
      params.url = el.href;
      el.store('smoothbox', params);
      el.store('smoothboxed', true);

      //el.href = 'javascript:void(0);';
      el.addEvent('click', function(event)
      {
        event.stop(); // Maybe move this to after next line when done debugging
        Smoothbox.open(el);
      });
    });
  },

  close : function()
  {
    if( this.instance )
    {
      this.instance.close();
    }
  },

  open : function(spec, options)
  {
    if( this.instance )
    {
      var bind = this;
      this.instance.addEvent('closeafter', function()
      {
        bind.open(spec);
      });
      this.instance.close();
      return;
    }

    // Check the options array
    if( $type(options) == 'object' ) {
      options = new Hash(options);
    } else if( $type(options) != 'hash' ) {
      options = new Hash();
    }

    // Check the arguments

    // Spec as element
    if( $type(spec) == 'element' ) {
      // This is a link
      if( spec.get('tag').toLowerCase() == 'a' ) {
        spec = new Hash({
          'mode' : 'Iframe',
          'link' : spec,
          'element' : spec,
          'url' : spec.get('href'),
          'title' : spec.get('title')
        });
      }
      // This is some other element
      else {
        spec = new Hash({
          'mode' : 'Inline',
          'title' : spec.get('title'),
          'element' : spec
        });
      }
    }

    // Spec as string
    else if( $type(spec) == 'string' ) {
      // Spec is url
      if( spec.length < 4000 && (spec.substring(0, 1) == '/' ||
          spec.substring(0, 1) == '.' ||
          spec.substring(0, 4) == 'http' ||
          !spec.match(/[ <>"'{}|^~\[\]`]/)
        )
      ) {
        spec = new Hash({
          'mode' : 'Iframe',
          'url' : spec
        });
      }
      // Spec is a string
      else {
        spec = new Hash({
          'mode' : 'String',
          'bodyText' : spec
        });
      }
    }

    // Spec as object or hash
    else if( $type(spec) == 'object' || $type(spec) == 'hash' ) {
      // Don't do anything?
    }

    // Unknown spec
    else {
      spec = new Hash();
    }




    // Now lets start the fun stuff
    spec.extend(options);

    var mode = spec.get('mode');
    spec.erase('mode');

    if( !mode ) {
      if( spec.has('url') ) {
        //if( spec.get('url').match(/\.(jpe?g|png|gif|bmp)/gi) ) {
          //mode = 'Image';
        //} else {
          mode = 'Iframe';
        //}
      }
      else if( spec.has('element') ) {
        mode = 'Inline';
      }
      else if( spec.has('bodyText') ) {
        mode = 'String';
      }
      else {
        return;
      }
    }

    if( !$type(Smoothbox.Modal[mode]) )
    {
      //mode = 'Iframe';
      return;
    }
    
    //console.log(spec);
    
    this.instance = new Smoothbox.Modal[mode](spec.getClean());
  }

};

Smoothbox.Modal = new Class({

  Implements : [Events, Options],

  options : {
    url : null,
    width : 480,
    height : 320,

    // Do or do not
    transitions : false,
    overlay : true,
    loading : true,
    
    noOverlayClose : false,

    autoResize : true,
    autoFormat : 'smoothbox'

    //useFixed : false
  },

  eventProto : {},

  overlay : false,

  window : false,

  content : false,

  loading : false,

  initialize : function(options)
  {
    this.setOptions(options);

    /*
    if( Browser.Engine.gecko )
    {
      // @todo figure out which browsers exactly use fixed
      this.options.useFixed = true;
    }
    */

    this.onOpen();

    this.load();
  },
  
  close : function()
  {
    this.onClose();

    window.removeEvent('scroll', this.eventProto.scroll);
    window.removeEvent('resize', this.eventProto.resize);

    if( this.options.transitions )
    {
      var bind = this;
      this.addEvent('closeafter', function()
      {
        if( bind.window ) bind.window.destroy();
        if( bind.overlay ) bind.overlay.destroy();
        if( bind.loading ) bind.loading.destroy();
      });
      bind.hide();
    }
    else
    {
      if( this.window ) this.window.destroy();
      if( this.overlay ) this.overlay.destroy();
      if( this.loading ) this.loading.destroy();
    }

    Smoothbox.instance = false;
  },

  load : function()
  {
    this.create();
    
    // Add Events
    var bind = this;
    this.eventProto.resize = function()
    {
      bind.positionOverlay();
      bind.positionWindow();
    }

    this.eventProto.scroll = function()
    {
      bind.positionOverlay();
      bind.positionWindow();
    };

    window.addEvent('resize', this.eventProto.resize);
    window.addEvent('scroll', this.eventProto.scroll);

    
    this.position();
    this.showOverlay();
    this.showLoading();
  },

  create : function()
  {
    this.createOverlay();
    this.createLoading();
    this.createWindow();
  },

  createLoading : function()
  {
    if( this.loading || !this.options.loading )
    {
      return;
    }

    var bind = this;
    
    this.loading = new Element('div', {
      id : 'TB_load'
    });
    this.loading.inject(document.body);

    var loadingImg = new Element('img', {
      src : 'externals/smoothbox/loading.gif' // @todo Move to CSS
    });
    loadingImg.inject(this.loading);
  },

  createOverlay : function()
  {
    if( this.overlay || !this.options.overlay )
    {
      return;
    }

    var bind = this;

    this.overlay = new Element('div', {
      'id' : 'TB_overlay',
      'styles' : {
        'position' : 'absolute',
        'top' : '0px',
        'left' : '0px'
      },
      'opacity' : 0
    });
    this.overlay.inject(document.body);

    if( !this.options.noOverlayClose )
    {
      this.overlay.addEvent('click', function()
      {
        bind.close();
      });
    }
  },

  createWindow : function()
  {
    if( this.window )
    {
      return;
    }

    var bind = this;
    
    this.window = new Element('div', {
      'id' : 'TB_window',
      'opacity' : 0
    });
    this.window.inject(document.body);

    var title = new Element('div', {
      id : 'TB_title'
    });
    title.inject(this.window);

    var titleText = new Element('div', {
      id : 'TB_ajaxWindowTitle',
      html : this.options.title
    });
    titleText.inject(title);

    var titleClose = new Element('div', {
      id : 'TB_closeAjaxWindow',
      events : {
        click : function() {
         bind.close();
        }
      }
    });
    titleClose.inject(title);

    var titleCloseLink = new Element('a', {
      id : 'TB_title',
      href : 'javascript:void(0);',
      title : 'close',
      html : 'close',
      events : {
        click : function() {
          bind.close();
        }
      }
    })
    titleCloseLink.inject(titleClose);
  },

  position : function()
  {
    this.positionOverlay();
    this.positionWindow();
    this.positionLoading();
  },

  positionLoading : function()
  {
    if( !this.loading )
    {
      return;
    }

    if( Browser.Engine.trident /*&& this.loading.style.display == 'none'*/ ){
      //this.loading.style.visibility = 'hidden';
      //this.loading.style.display = '';
      this.loading.style.display = '';
    }

    this.loading.setStyles({
        left: (window.getScroll().x + (window.getSize().x - 56) / 2) + 'px',
        top: (window.getScroll().y + ((window.getSize().y - 20) / 2)) + 'px',
        display: "block"
    });
  },

  positionOverlay : function()
  {
    if( !this.overlay )
    {
      return;
    }

    if( Browser.Engine.trident /*&& this.overlay.style.display == 'none'*/ ){
      //this.overlay.style.visibility = 'hidden';
      //this.overlay.style.display = '';
      this.overlay.style.display = '';
    }
    
    this.overlay.setStyles({
        "height" : '0px',
        "width" : '0px'
    });
    
    if( !this.options.noOverlay )
    {
      this.overlay.setStyles({
          "height" : window.getScrollHeight() + 'px',
          "width" : window.getScrollWidth() + 'px'
      });
    }
  },

  positionWindow : function()
  {
    if( !this.window )
    {
      return;
    }

    if( Browser.Engine.trident /*&& this.window.style.display == 'none'*/ ){
      //this.window.style.visibility = 'hidden';
      //this.window.style.display = '';
      this.window.style.display = '';
    }

    this.window.setStyles({
      "width" : this.options.width + 'px',
      "left" : (window.getScroll().x + (window.getSize().x - this.options.width) / 2) + 'px',
      "top" : (window.getScroll().y + (window.getSize().y - this.options.height) / 2) + 'px'
    });
  },

  show : function()
  {
    this.showOverlay();
    this.showLoading();
    this.showWindow();
  },

  showLoading : function()
  {
    if( !this.loading )
    {
      return;
    }

    if( Browser.Engine.trident /*&& this.loading.style.visibility == 'hidden'*/ ){
      //this.loading.style.visibility = 'visible';
      this.loading.style.display = '';
    }

    if( this.options.transitions )
    {
      this.loading.tween('opacity', 0, 1);
    }
    else
    {
      this.loading.setStyle('opacity', 1);
    }
  },
  
  showOverlay : function()
  {
    if( !this.overlay )
    {
      return;
    }

    if( Browser.Engine.trident /*&& this.overlay.style.visibility == 'hidden'*/ ){
      //this.overlay.style.visibility = 'visible';
      this.overlay.style.display = '';
    }

    if( this.options.transitions )
    {
      this.overlay.tween('opacity', 0, 0.6);
    }
    else
    {
      this.overlay.setStyle('opacity', 0.6);
    }
  },

  showWindow : function()
  {
    if( !this.window )
    {
      return;
    }

    if( Browser.Engine.trident /* && this.window.style.visibility == 'hidden'*/ ){
      //this.window.style.visibility = 'visible';
      this.window.style.display = '';
    }

    // Try to autoresize the window
    if( typeof(this.doAutoResize) == 'function' )
    {
      this.doAutoResize();
    }

    if( this.options.transitions )
    {
      this.window.tween('opacity', 0, 1);
    }
    else
    {
      this.window.setStyle('opacity', 1);
    }
  },

  hide : function()
  {
    this.hideLoading();
    this.hideOverlay();
    this.hideWindow();
  },

  hideLoading : function()
  {
    if( !this.loading )
    {
      return;
    }

    if( this.options.transitions )
    {
      this.loading.tween('opacity', 1, 0);
    }
    else
    {
      this.loading.setStyle('opacity', 0);
    }
  },

  hideOverlay : function()
  {
    if( !this.overlay )
    {
      return;
    }
    
    if( this.options.transitions )
    {
      this.overlay.tween('opacity', 0.6, 0);
    }
    else
    {
      this.overlay.setStyle('opacity', 0);
    }
  },

  hideWindow : function()
  {
    /*
    if( !this.window )
    {
      return;
    }
    */
    
    if( this.options.transitions )
    {
      var bind = this;
      this.window.tween('opacity', 1, 0);
      this.window.get('tween').addEvent('complete', function()
      {
        bind.fireEvent('closeafter');
      });
    }
    else
    {
      this.window.setStyle('opacity', 0);
    }
  },


  doAutoResize : function(element)
  {
    if( !element || !this.options.autoResize )
    {
      return;
    }

    var size = $try(function(){
      return element.getScrollSize();
    }, function(){
      return element.getSize();
    }, function(){
      return {
        x : element.scrollWidth,
        y : element.scrollHeight
      }
    });

    var winSize = window.getSize();
    if( size.x - 70 > winSize.x ) size.x = winSize.x - 70;
    if( size.y - 70 > winSize.y ) size.y = winSize.y - 70;

    this.content.setStyles({
      'width' : (size.x + 20) + 'px',
      'height' : (size.y + 20) + 'px'
    });

    this.options.width = (size.x + 20);
    this.options.height = (size.y + 20);

    this.positionWindow();
  },


  // events
  onLoad : function()
  {
    this.fireEvent('load', this);
  },

  onOpen : function()
  {
    this.fireEvent('open', this);
  },

  onClose : function()
  {
    this.fireEvent('close', this);
  },

  onCloseAfter : function()
  {
    this.fireEvent('closeafter', this);
  }

});

Smoothbox.Modal.Iframe = new Class({

  Extends : Smoothbox.Modal,

  load : function()
  {
    if( this.content )
    {
      return;
    }

    this.parent();

    var bind = this;
    var loadIsOkay = true;

    var uriSrc = new URI(this.options.url);
    if( this.options.autoFormat ) {
      uriSrc.setData({'format' : this.options.autoFormat}, true, 'query');
    }

    this.content = new IFrame({
      src : uriSrc,
      id : 'TB_iframeContent',
      name : 'TB_iframeContent',
      frameborder : '0',
      width : this.options.width,
      height : this.options.height,
      events : {
        load : function()
        {
          if( loadIsOkay )
          {
            loadIsOkay = false;
            bind.hideLoading();
            bind.showWindow();
            bind.onLoad();
          } else {
            bind.doAutoResize();
          }
        }
      }
    });

    this.content.inject(this.window);
  },

  doAutoResize : function()
  {
    if( !this.options.autoResize )
    {
      return;
    }

    // Check if from same host
    var iframe = this.content;
    var host = $try(function(){
      return iframe.contentWindow.location.host;
    });
    
    if( !host || host != window.location.host )
    {
      return;
    }

    // Try to get element
    if( this.options.autoResize == true )
    {
      var self = this;
      var element = $try(function(){
        return iframe.contentWindow.document.body.getChildren()[0];
      }, function(){
        return iframe.contentWindow.document.body;
      }, function(){
        return iframe.contentWindow.document.documentElement;
      });

      return this.parent( element );
    }

    else if( $type(this.options.autoResize) == 'element' )
    {
      return this.parent( iframe.contentWindow.$(this.options.autoResize) );
    }
  }

});

Smoothbox.Modal.Request = new Class({

  Extends : Smoothbox.Modal,

  load : function()
  {
    if( this.content )
    {
      return;
    }

    this.parent();

    var bind = this;
    var data = (this.options.requestData || {});

    if( this.autoFormat ) {
      data.format = this.autoFormat;
    }

    (new Request.HTML({
      url : this.options.url,
      method : (this.options.requestMethod || 'get'),
      data : data,
      onSuccess : function(responseTree, responseElements, responseHTML, responseJavaScript)
      {
        bind.content = new Element('div', {
          id : 'TB_ajaxContent',
          width : bind.options.width,
          height : bind.options.height,
          html : responseHTML
        });
        bind.content.inject(bind.window);
        //responseTree.inject(bind.content);

        bind.hideLoading();
        bind.showWindow();
        bind.onLoad();
      }
    })).send();

  }

});

Smoothbox.Modal.Inline = new Class({

  Extends : Smoothbox.Modal,

  element : false,

  load : function()
  {
    if( this.content )
    {
      return;
    }

    this.parent();

    this.content = new Element('div', {
      id : 'TB_ajaxContent',
      width : this.options.width,
      height : this.options.height
    });
    this.content.inject(this.window);

    this.element.clone().inject(this.content);
        
    this.hideLoading();
    this.showWindow();
    this.onLoad();
  },

  setOptions : function(options)
  {
    this.element = $(options.element);
    this.parent(options);
  }
  
});

Smoothbox.Modal.String = new Class({

  Extends : Smoothbox.Modal,

  load : function()
  {
    if( this.content )
    {
      return;
    }

    this.parent();
    
    this.content = new Element('div', {
      id : 'TB_ajaxContent',
      width : this.options.width,
      height : this.options.height,
      html : '<div>' + this.options.bodyText + '</div>'
    });
    this.content.inject(this.window);
    
    this.hideLoading();
    this.showWindow();
    this.onLoad();
  },

  doAutoResize : function()
  {
    if( !this.options.autoResize )
    {
      return;
    }

    var bind = this;
    var element = $try(function(){
      return bind.content.getChildren()[0];
    });

    return this.parent( element );
  }
  
});

Smoothbox.Modal.Image = new Class({

  Extends : Smoothbox.Modal

});

window.addEvent('domready', function()
{
  Smoothbox.bind();
})

window.addEvent('load', function()
{
  Smoothbox.bind();
})