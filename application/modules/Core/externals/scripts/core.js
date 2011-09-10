
/* $Id: core.js 8375 2011-02-02 02:09:25Z john $ */


var en4 = {};


/**
 * Core methods
 */
en4.core = {

  baseUrl : false,

  basePath : false,

  loader : false,

  environment : 'production',

  setBaseUrl : function(url)
  {
    this.baseUrl = url;
    var m = this.baseUrl.match(/^(.+?)index[.]php/i);
    this.basePath = ( m ? m[1] : this.baseUrl );
  },

  subject : {
    type : '',
    id : 0,
    guid : ''
  },

  showError : function(text){
    Smoothbox.close();
    Smoothbox.instance = new Smoothbox.Modal.String({
      bodyText : text
    });
  }

};


/**
 * Run Once scripts
 */
en4.core.runonce = {

  executing : false,
  
  fns : [],

  add : function(fn){
    this.fns.push(fn);
  },

  trigger : function(){
    if( this.executing ) return;
    this.executing = true;
    var fn;
    while( fn = this.fns.shift() ){
      $try(function(){fn();});
    }
    //this.fns.each(function(fn){
    //  $try(function(){ fn(); });
    //});
    this.fns = [];
    this.executing = false;
  }
  
};

window.addEvent('load', function(){
  en4.core.runonce.trigger();
});

// This is experimental
window.addEvent('domready', function(){
  en4.core.runonce.trigger();
});


/**
 * Dynamic page loader
 */
en4.core.dloader = {

  loopId : false,

  currentHref : false,

  activeHref : false,

  xhr : false,

  frame : false,

  enabled : false,

  setEnabled : function(flag) {
    this.enabled = ( flag == true );
  },

  start : function(options) {
    if( this.frame || this.xhr ) return this;

    this.activeHref = options.url;

    // Use an iframe for get requests
    if( $type(options.conntype) && options.conntype == 'frame' )
    {
      options = $merge({
        data : {
          format : 'async',
          mode : 'frame'
        },
        styles : {
          'position' : 'absolute',
          'top' : '-200px',
          'left' : '-200px',
          'height' : '100px',
          'width' : '100px'
        },
        events : {
          //load : this.handleLoad.bind(this)
        }
      }, options);
      
      if( $type(options.url) ) {
        options.src = options.url;
        delete options.url;
      }
      // Add format as query string
      if( $type(options.data) ) {
        var separator = ( options.src.indexOf() > -1 ? '&' : '?' );
        options.src += separator + $H(options.data).toQueryString();
        delete options.data;
      }
      this.frame = new IFrame(options);
      this.frame.inject(document.body);
    }

    else
    {
      options = $merge({
        method : 'get',
        data : {
          'format' : 'html',
          'mode' : 'xhr'
        },
        onComplete : this.handleLoad.bind(this)
      }, options);
      this.xhr = new Request.HTML(options);
      this.xhr.send();
    }
    
    return this;
  },

  cancel : function() {
    if( this.frame ) {
      this.frame.destroy();
      this.frame = false;
    }
    if( this.xhr ) {
      this.xhr.cancel();
      this.xhr = false;
    }
    this.activeHref = false;
    return this;
  },

  attach : function(els)
  {
    var bind = this;

    if( !$type(els) )
    {
      els = $$('a');
    }

    // Attach to links
    //$$('a.ajaxable').each(function(element)
    els.each(function(element)
    {
      if( element.get('tag') != 'a' || element.onclick || !element.href || element.href.match(/^(javascript|[#])/) || element.hasEvents() )
      {
        return;
      }
      
      element.addEvent('click', function(event)
      {
        if( element.get('tag') != 'a' || element.onclick || !element.href || element.href.match(/^(javascript|[#])/) ) {
          return;
        }
        var events = element.getEvents('click');
        if( events && events.length > 1 ) {
          return;
        }
        
        // Cancel link click
        event.stopPropagation();

        bind.startRequest(element.href);

        return false;
      });
    });

    // Monitor location
    //window.addEvent('unload', this.monitorAddress.bind(this));
    this.currentHref = window.location.href;
    this.loopId = this.monitorAddress.periodical(200, this);
  },

  handleLoad : function(response1, response2, response3, response4) {
    var response;
    
    if( this.frame ) {
      response = $try(function() {
        return response1;
      }, function(){
        return this.frame.contentWindow.document.documentElement.innerHTML;
      }.bind(this));
    } else if( this.xhr ) {
      response = response3;
    }

    if( response ) {
      $('global_content').innerHTML = response;
      en4.core.request.evalScripts($('global_content'));
      this.attach($('global_content').getElements('a'));
      en4.core.runonce.trigger();
    }
    
    this.cancel();
    this.activeHref = false;
  },

  startRequest : function(url)
  {
    // Cancel current request if active
    if( this.activeHref )
    {
      // Ignore if equal
      if( this.activeHref == url )
      {
        return;
      }
      // Otherwise cancel an continue
      this.cancel();
    }

    this.start({
      url : url,
      conntype : 'frame'
    });
    
    // Set hash in url to page
    this.updateWindowLocation(url);
  },

  updateWindowLocation : function(url)
  {
    middlesegment = window.location.host + en4.core.baseUrl;
    tail = url.split(middlesegment)[1];
    window.location.hash = tail;
    this.currentHref = window.location.href;
  },

  monitorAddress : function()
  {
    if( this.currentHref == window.location.href )
    {
      return;
    }
    else
    {
      var fullUrl =
        window.location.protocol + '//' +
        window.location.host +
        en4.core.baseUrl +
        window.location.hash.replace('#', '');

      this.startRequest(fullUrl);
    }
  }
};


/**
 * Request pipeline
 */
en4.core.request = {

  activeRequests : [],

  isRequestActive : function(){
    return ( this.activeRequests.length > 0 );
  },

  send : function(req, options){
    options = options || {};
    if( !$type(options.force) ) options.force = false;
    
    // If there are currently active requests, ignore
    if( this.activeRequests.length > 0 && !options.force ){
      return this;
    }
    this.activeRequests.push(req);
    
    // Process options
    if( !$type(options.htmlJsonKey) ) options.htmlJsonKey = 'body';
    if( $type(options.element) ){
      options.updateHtmlElement   = options.element;
      options.evalsScriptsElement = options.element;
    }

    // OnComplete
    var bind = this;
    req.addEvent('success', function(response, response2, response3, response4){
      bind.activeRequests.erase(req);
      var htmlBody;
      var jsBody;
      //alert($type(response) + $type(response2) + $type(response3) + $type(response4));
      
      // Get response
      if( $type(response) == 'object' ){ // JSON response
        htmlBody = response[options.htmlJsonKey];
      } else if( $type(response3) == 'string' ){ // HTML response
        htmlBody = response3;
        jsBody = response4;
      }

      // An error probably occurred
      if( !response && !response3 && $type(options.updateHtmlElement) ){
        en4.core.showError('An error has occurred processing the request. The target may no longer exist.');
        return;
      }

      if( $type(response) == 'object' && $type(response.status) && response.status == false /* && $type(response.error) */ )
      {
        en4.core.showError('An error has occurred processing the request. The target may no longer exist.' + '<br /><br /><button onclick="Smoothbox.close()">Close</button>');
        return;
      }

      // Get scripts
      if( $type(options.evalsScriptsElement) || $type(options.evalsScripts) ){
        if( htmlBody ) htmlBody.stripScripts(true);
        if( jsBody ) eval(jsBody);
      }
      
      if( $type(options.updateHtmlElement) && htmlBody ){
        if( $type(options.updateHtmlMode) && options.updateHtmlMode == 'append' ){
          Elements.from(htmlBody).inject($(options.updateHtmlElement));
        } else if( $type(options.updateHtmlMode) && options.updateHtmlMode == 'prepend' ){
          Elements.from(htmlBody).reverse().inject($(options.updateHtmlElement), 'top');
        } else if ($type(options.updateHtmlMode) && options.updateHtmlMode == 'comments' && Elements.from(htmlBody) && Elements.from(htmlBody)[1] && Elements.from(htmlBody)[1].getElement('.comments')) {
            $(options.updateHtmlElement).getElement('.comments').destroy();
            $(options.updateHtmlElement).getElement('.feed_item_date').destroy();
            if (Elements.from(htmlBody)[1].getElement('.feed_item_date'))
                Elements.from(htmlBody)[1].getElement('.feed_item_date').inject($(options.updateHtmlElement.getElement('.feed_item_body')));
            Elements.from(htmlBody)[1].getElement('.comments').inject($(options.updateHtmlElement.getElement('.feed_item_body')));
        } else {
          $(options.updateHtmlElement).empty();
          Elements.from(htmlBody).inject($(options.updateHtmlElement));
        }
        Smoothbox.bind($(options.updateHtmlElement));
      }
      
      if( !$type(options.doRunOnce) || !options.doRunOnce ){
        en4.core.runonce.trigger();
      }
    });

    req.send();
    
    return this;
  },
  
  evalScripts : function(element) {
    element = $(element);
    if( !element ) return this;
    element.getElements('script').each(function(script){
      if( script.type != 'text/javascript' ) return;
      if( script.src ){
        Asset.javascript(script.src);
      }
      else if( script.innerHTML.trim() ) {
        eval(script.innerHTML);
      }
    });

    return this;
  }

};


/**
 * Comments
 */
en4.core.comments = {

  loadComments : function(type, id, page){
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'core/comment/list',
      data : {
        format : 'html',
        type : type,
        id : id,
        page : page
      }
    }), {
      'element' : $('comments')
    });
  },

  attachCreateComment : function(formElement){
    var bind = this;
    formElement.addEvent('submit', function(event){
      event.stop();
      var form_values  = formElement.toQueryString();
          form_values += '&format=json';
          form_values += '&id='+formElement.identity.value;
      en4.core.request.send(new Request.JSON({
        url : en4.core.baseUrl + 'core/comment/create',
        data : form_values
      }), {
        'element' : $('comments')
      });
      //bind.comment(formElement.type.value, formElement.identity.value, formElement.body.value);
    })
  },

  comment : function(type, id, body){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'core/comment/create',
      data : {
        format : 'json',
        type : type,
        id : id,
        body : body
      }
    }), {
      'element' : $('comments')
    });
  },

  like : function(type, id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'core/comment/like',
      data : {
        format : 'json',
        type : type,
        id : id,
        comment_id : comment_id
      }
    }), {
      'element' : $('comments')
    });
  },

  unlike : function(type, id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'core/comment/unlike',
      data : {
        format : 'json',
        type : type,
        id : id,
        comment_id : comment_id
      }
    }), {
      'element' : $('comments')
    });
  },

  showLikes : function(type, id){
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'core/comment/list',
      data : {
        format : 'html',
        type : type,
        id : id,
        viewAllLikes : true
      }
    }), {
      'element' : $('comments')
    });
  },

  deleteComment : function(type, id, comment_id) {
    if( !confirm(en4.core.language.translate('Are you sure you want to delete this?')) ) {
      return;
    }
    (new Request.JSON({
      url : en4.core.baseUrl + 'core/comment/delete',
      data : {
        format : 'json',
        type : type,
        id : id,
        comment_id : comment_id
      },
      onComplete: function() {
        if( $('comment-' + comment_id) ) {
          $('comment-' + comment_id).destroy();
        }
        try {
          var commentCount = $$('.comments_options span')[0];
          var m = commentCount.get('html').match(/\d+/);
          var newCount = ( parseInt(m[0]) != 'NaN' && parseInt(m[0]) > 1 ? parseInt(m[0]) - 1 : 0 );
          commentCount.set('html', commentCount.get('html').replace(m[0], newCount));
        } catch( e ) {}
      }
    })).send();
  }
};



en4.core.languageAbstract = new Class({

  Implements : [Options, Events],

  name : 'language',

  options : {
    locale : 'en',
    defaultLocale : 'en'
  },

  data : {

  },

  initialize : function(options, data) {
    // b/c
    if( $type(options) == 'object' ) {
      if( $type(options.lang) ) {
        this.addData(options.lang);
        delete options.lang;
      }
      if( $type(options.data) ) {
        this.addData(options.data);
        delete options.data;
      }
      this.setOptions(options);
    }
    if( $type(data) == 'object' ) {
      this.setData(data);
    }
  },

  getName : function() {
    return this.name;
  },

  setLocale : function(locale) {
    this.options.locale = locale;
    return this;
  },

  getLocale : function() {
    return this.options.locale;
  },

  translate: function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }

      // Process arguments
      var locale = this.options.locale;
      var messageId = arguments[0];
      var options = new Array();
      if( arguments.length > 1 ) {
        for( var i = 1, l = arguments.length; i < l; i++ ) {
          options.push(arguments[i]);
        }
      }

      // Check plural
      var plural = false;
      var number = 1;
      if( $type(messageId) == 'array' ) {
        if( messageId.length > 2 ) {
          number = messageId.pop();
          plural = messageId;
        }
        messageId = messageId[0];
      }

      // Get message
      var message;
      if( $type(this.data[messageId]) ) {
        message = this.data[messageId];
      } else if( plural ) {
        message = plural;
        locale = this.options.defaultLocale;
      } else {
        message = messageId;
      }
      
      // Get correct message from plural
      if( $type(message) == 'array' ) {
        var rule = this.getPlural(locale, number);
        if( $type(message[rule]) ) {
          message = message[rule];
        } else {
          message = message[0];
        }
      }

      if( options.length <= 0 ) {
        return message;
      }

      return message.vsprintf(options);
    } catch( e ) {
      alert(e);
    }
  },

  setData : function(data) {
    if( $type(data) != 'object' && $type(data) != 'hash' ) {
      return this;
    }
    this.data = data;
    return this;
  },

  addData : function(data) {
    if( $type(data) != 'object' && $type(data) != 'hash' ) {
      return this;
    }
    this.data = $merge(this.data, data);
    return this;
  },

  getData : function(data) {
    return this.data;
  },


  getPlural : function(locale, number) {

    if( $type(locale) != 'string' ) {
      return 0;
    }

    if( locale == "pt_BR" ) {
      locale = "xbr";
    }

    if( locale.length > 3 ) {
      locale = locale.substring(0, locale.indexOf('_'));
    }

    switch( locale ) {
      case 'bo': case 'dz': case 'id': case 'ja': case 'jv': case 'ka':
      case 'km': case 'kn': case 'ko': case 'ms': case 'th': case 'tr':
      case 'vi':
        return 0;
        break;

      case 'af': case 'az': case 'bn': case 'bg': case 'ca': case 'da':
      case 'de': case 'el': case 'en': case 'eo': case 'es': case 'et':
      case 'eu': case 'fa': case 'fi': case 'fo': case 'fur': case 'fy':
      case 'gl': case 'gu': case 'ha': case 'he': case 'hu': case 'is':
      case 'it': case 'ku': case 'lb': case 'ml': case 'mn': case 'mr':
      case 'nah': case 'nb': case 'ne': case 'nl': case 'nn': case 'no':
      case 'om': case 'or': case 'pa': case 'pap': case 'ps': case 'pt':
      case 'so': case 'sq': case 'sv': case 'sw': case 'ta': case 'te':
      case 'tk': case 'ur': case 'zh': case 'zu':
        return (number == 1) ? 0 : 1;
        break;

      case 'am': case 'bh': case 'fil': case 'fr': case 'gun': case 'hi':
      case 'ln': case 'mg': case 'nso': case 'xbr': case 'ti': case 'wa':
        return ((number == 0) || (number == 1)) ? 0 : 1;
        break;

      case 'be': case 'bs': case 'hr': case 'ru': case 'sr': case 'uk':
        return ((number % 10 == 1) && (number % 100 != 11)) ? 0 :
          (((number % 10 >= 2) && (number % 10 <= 4) && ((number % 100 < 10)
          || (number % 100 >= 20))) ? 1 : 2);

      case 'cs': case 'sk':
        return (number == 1) ? 0 : (((number >= 2) && (number <= 4)) ? 1 : 2);

      case 'ga':
        return (number == 1) ? 0 : ((number == 2) ? 1 : 2);

      case 'lt':
        return ((number % 10 == 1) && (number % 100 != 11)) ? 0 :
          (((number % 10 >= 2) && ((number % 100 < 10) ||
          (number % 100 >= 20))) ? 1 : 2);

      case 'sl':
        return (number % 100 == 1) ? 0 : ((number % 100 == 2) ? 1 :
          (((number % 100 == 3) || (number % 100 == 4)) ? 2 : 3));

      case 'mk':
        return (number % 10 == 1) ? 0 : 1;

      case 'mt':
        return (number == 1) ? 0 :
          (((number == 0) || ((number % 100 > 1) && (number % 100 < 11))) ? 1 :
          (((number % 100 > 10) && (number % 100 < 20)) ? 2 : 3));

      case 'lv':
        return (number == 0) ? 0 : (((number % 10 == 1) &&
          (number % 100 != 11)) ? 1 : 2);

      case 'pl':
        return (number == 1) ? 0 : (((number % 10 >= 2) && (number % 10 <= 4) &&
          ((number % 100 < 10) || (number % 100 > 29))) ? 1 : 2);

      case 'cy':
        return (number == 1) ? 0 : ((number == 2) ? 1 : (((number == 8) ||
          (number == 11)) ? 2 : 3));

      case 'ro':
        return (number == 1) ? 0 : (((number == 0) || ((number % 100 > 0) &&
          (number % 100 < 20))) ? 1 : 2);

      case 'ar':
        return (number == 0) ? 0 : ((number == 1) ? 1 : ((number == 2) ? 2 :
          (((number >= 3) && (number <= 10)) ? 3 : (((number >= 11) &&
          (number <= 99)) ? 4 : 5))));

      default:
        return 0;
    }
  }
  
});


en4.core.language = new en4.core.languageAbstract();