/* -----------------------------------------------------------------

  Script:
    A mootools comet wrapper

    @script    Request.Comet
    @version  0.1
    @author    Benjamin Hutchins
    @date    May 29th, 2008

  Copyright:
    Copyright (c) 2008, Benjamin Hutchins <http://www.xvolter.com/>

  License:
    MIT license

   ----------------------------------------------------------------- */

Request.Comet = new Class({

  Implements: [Options, Events],

  type: 0,

  tunnel: null,

  lastResponse : false,

  options: {
    url: '',
    type: 0,
    name: "MooComet",
    mode: 'short',
    onPush: $empty,
    reconnect : 100
  },

  initialize: function(options) {
    this.setOptions(options);

    // Type detection
    if( this.mode == 'short' || this.mode == 'long' ) {
      this.type = 1;
    } else if( Browser.Engine.trident ) {
      this.type = 3;
    //} else if( Browser.Engine.webkit ) {
    //  this.type = 1;
    //} else if( Browser.Engine.presto ) {
    //  this.type = 1; //2;
    } else {
      this.type = 1;
    }

    window.addEvent('unload', this.cancel.bind(this));

    this.open();

    return this;
  },

  open : function() {

    this.options.type = this.type;

    switch( this.type ) {
      default:
        this.options.type = this.type = 1;
      case 1:
        this.tunnel = new Request.Comet.XHR(this, this.options)
        break;
      case 2:
        this.tunnel = new Request.Comet.EventSource(this, this.options)
        break;
      case 3:
        this.tunnel = new Request.Comet.ActiveX(this, this.options)
        break;
    }

    this.tunnel.addEvent('onRecvData', this.onChange.bind(this));
    
    return this;
  },

  cancel: function() {
    this.tunnel.cancel();
    delete this.tunnel;
    
    this.lastResponse = '';

    return this;
  },

  send: function() {
    this.tunnel.send();
    return this;
  },

  onChange: function(text) {
    this.fireEvent('onPush', text);
    
    // Auto reconnect
    if( this.options.reconnect ) {
      this.cancel();
      (function(){
        this.open();
        this.send();
      }).delay(this.options.reconnect, this);
    }
  }
});

Request.Comet.XHR = new Class({

  Extends: Request,

  comet : false,

  initialize : function(comet, options) {
    this.comet = comet;
    options.data = $merge(options.data, {
      'cometType' : options.type,
      'cometName' : options.name,
      'cometMode' : options.mode
    });
    this.parent(options);
  },

  cancel : function() {
    if( this.xhr.readyState != 4 ) {
      this.parent();
    }
    
    return this;
  },

  onStateChange: function(){
    if( this.options.mode != 'comet' && this.xhr.readyState != 4 ) {
      return;
    }
    this.response = {text: this.xhr.responseText, xml: this.xhr.responseXML};
    this.onRecvData(this.processScripts(this.response.text));
  },

  onRecvData : function(text) {
    var response = text.split("<end />");
    response = response[response.length-1];
    this.fireEvent('onRecvData', response);
  }
});

Request.Comet.ActiveX = new Class({

  Implements : [Options, Events],

  options : {
    url : false
  },
  
  state : 0,

  comet : false,

  element : false,

  initialize : function(comet, options) {
    this.comet = comet;
    this.setOptions(options);
    this.element = new ActiveXObject("htmlfile");
    this.options.url = this.options.url + (this.options.url.search("\\?")>-1?"&":"?") + "cometType=" + this.type + "&cometName=" + this.options.name + "&cometMode=" + this.options.mode;
  },

  send : function()
  {
    if( this.state != 0 ) {
      this.cancel();
    }

    this.element.open();
    this.element.write("<html><body></body></html>");
    this.element.close();
    this.element.parentWindow._cometObject = this;
    this.element.body.innerHTML = "<iframe src='" + this.options.url + "'></iframe>";

    return this;
  },

  cancel : function() {
    if( this.state != 0 )
    {
      delete this.element;
    }

    return this;
  },

  destroy : function() {
    this.cancel();
  },

  onRecvData : function(data) {
    this.fireEvent('onRecvData', data);
  }

});

Request.Comet.EventSource = new Class({

  Implements : [Options, Events],

  options : {
    url : false
  },

  comet : false,

  element : false,

  initialize : function(comet, options) {
    this.comet = comet;
    this.setOptions(options);
    this.element = document.createElement("event-source");
    this.options.url = this.options.url + (this.options.url.search("\\?")>-1?"&":"?") + "cometType=" + this.type + "&cometName=" + this.options.name + "&cometMode=" + this.options.mode;
  },

  send : function() {
    this.element.setAttribute("src", this.options.url);
    document.body.appendChild(this.element);
    this.element.addEventListener(this.options.name, this.onRecvData.bind(this), false);
    
    return this;
  },

  cancel : function() {
    document.removeChild(this.element);
    
    return this;
  },

  destroy : function() {
    this.cancel();
  },

  onRecvData : function() {
    var response = arguments[0].data;
    this.fire('onRecvData', response);
  }
  
});