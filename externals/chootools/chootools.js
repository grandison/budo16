
// Chootools. Choo-Choo as in Engine. Get it?

// no more undefined object "console" errors:
if(typeof(console) === 'undefined') {
    var console = {}
    console.log = console.error = console.info = console.debug = console.warn = console.trace = console.dir = console.dirxml = console.group = console.groupEnd = console.time = console.timeEnd = console.assert = console.profile = function() {};
}

/**
 * Event hacking methods
 */
Events.implement({

  getEvents : function(type) {
		type = Events.removeOn(type);
    var events;
    if( !type ) events = this.$events;
    if( $type(this.$events[type]) ) events = this.$events[type];
    if( !events || events.length < 1 ) events = false;
    return events;
  },

  hasEvents : function(type) {
    return ( this.getEvents(type) != false );
  }

});


Native.implement([Element, Window, Document], {
  
  getEvents : function(type) {
    var events = this.retrieve('events');
    if( type && !$type(events[type]) ) return false;
    if( type ) events = events[type];
    if( !events || events.length < 1 ) return false;
    return events;
  },

  hasEvents : function(type) {
    return ( this.getEvents(type) != false );
  }

  /*
  addEventByPriority : function(type, fn, priority) {
    priority = priority || this.getDefaultEventPriority();
		var events = this.retrieve('events', {});
		events[type] = events[type] || {'keys': [], 'values': [], 'priorities': []};
    if( !$type(events[type].priorities) ) events[type].priorities = [];


    this.retrieve('events').each(function())
  },

  getDefaultEventPriority : function() {
    return this.get('defaultEventPriority') || 10;
  }
  */
  
});

/**
 * This is used to generate fluent timestamps
 */
(function(){

  var serverOffset = 0;

  Date.setServerOffset = function(ts){
    var server = new Date(ts);
    var client = new Date();
    serverOffset = server - client;
  };

  Date.getServerOffset = function() {
    return serverOffset;
  };

  Date.implement({

    getISODay : function()
    {
      var day = this.get('day') - 1;
      if( day < 0 ) day += 7;
      return day;
    },

    getISOWeek : function()
    {
      var compare = this.clone().set({
        month : 1,
        date : 4
      });
      var startOfWeekYear = compare.get('dayofyear') - compare.getISODay() - 1;
      return ( (this.get('dayofyear') - startOfWeekYear) / 7 ).ceil();
    },

    getFluentTimeSince : function(now)
    {
      var ref = this;
      var val;
      if( !now ) now = new Date();
      var deltaNormal = (ref - now - serverOffset) / 1000;
      //var deltaNormal = (now - ref + serverOffset) / 1000;
      var delta = Math.abs(deltaNormal);
      var isPlus = (deltaNormal > 0);

      if( delta < 1 ) {
        if( isPlus ) {
          return en4.core.language.translate('now');
        } else {
          return en4.core.language.translate('now');
        }
      }

      // Less than a minute
      else if( delta < 60 ) {
        if( isPlus ) {
          return en4.core.language.translate('in few seconds');
        } else {
          return en4.core.language.translate('a few seconds ago');
        }
      }

      // Less than an hour
      else if( delta < 60 * 60 ) {
        val = Math.floor(delta / 60);
        if( isPlus ) {
          return en4.core.language.translate(['in %s minute', 'in %s minutes', val], val);
        } else {
          return en4.core.language.translate(['%s minute ago', '%s minutes ago', val], val);
        }
      }

      // less than 12 hours ago, or less than a day ago and same day
      else if( delta < (60 * 60 * 12) || (delta < 60 * 60 * 24 && ref.get('day') == now.get('day')) )
      {
        val = Math.floor(delta / (60 * 60));
        if( isPlus ) {
          return en4.core.language.translate(['in %s hour', 'in %s hours', val], val);
        } else {
          return en4.core.language.translate(['%s hour ago', '%s hours ago', val], val);
      	}
      }

      // less than a week and same week
      else if( delta < 60 * 60 * 24 * 7 && ref.getISOWeek() == now.getISOWeek() )
      {
        return en4.core.language.translate(
          '%s at %s',
          ref.format('%A'),
          ref.format('%I %p')
        );
      }

      // less than a year and same year
      else if( delta < 60 * 60 * 60 * 24 * 366 && ref.getYear() == now.getYear() )
      {
        return ref.format('%B %d%o').replace(' 0', ' ');
      }

      // Otherwise use the full date
      else
      {
        return ref.format('%B %d%o %Y');
      }
    }

  });

  window.addEvent('load', function()
  {
    (function(){
      var now = new Date();
      $$('.timestamp-update').each(function(element){
        var ref = new Date(element.title);
        var newStamp = ref.getFluentTimeSince(now);
        if( element.innerHTML != newStamp )
        {
          element.innerHTML = newStamp;
        }
      });
    }).periodical(1000);
  });






  /**
   * Autogrowing textareas
   */
  var AutoGrow = new Class({

    Implements: [Options, Events],

    options: {
      useNullHeightShrink : true,
      interval : false
    },

    _resizing : false,

    _interval : false,

    _debug : false,

    initialize: function(textarea, options) {
      this.textarea = $(textarea);
      this.setOptions(options);

      if( this._debug ){
        this._debug = new Element('div', {
          styles : {
            position : 'absolute',
            top : '30px',
            left : '30px'
          }
        });
        this._debug.inject(document.body);
      }

      // Bind input events
      this.textarea.setStyles({
        'overflow-x' : 'auto',
        'overflow-y' : 'hidden',
        '-mox-box-sizing' : 'border-box',
        '-ms-box-sizing' : 'border-box',
        'resize' : 'none',
        'padding-bottom' : '0px',
        'padding-top' : '4px',
        'padding-left' : '4px'
      });
      this.textarea.store('AutoGrowInstance', this);

      this.textarea.addEvent('focus', this.handle);
      //this.textarea.addEvent('keydown', this.handle);
      this.textarea.addEvent('keyup', this.handle);
      this.textarea.addEvent('paste', this.handle);
      this.textarea.addEvent('cut', this.handle);
      if( Browser.Engine.webkit || Browser.Engine.trident ) this.textarea.addEvent('scroll', this.handle);
      //this.textarea.addEvent('resize', this.handle);

      // Initial resize
      if( this.options.interval )
      {
        this._interval = this.handle.periodical(this.options.interval, this.textarea);
      }
      else
      {
        this.resize();
      }
    },

    handle: function(){
      var instance = this.retrieve('AutoGrowInstance');
      if( instance ) instance.resize();
    },

    resize: function() {
      if( this._resizing ) return;

      this._resizing = true;

      this._resize();

      if( Browser.Engine.gecko || Browser.Engine.webkit )
      {
        this._shrink();
      }

      this._resizing = false;
    },

    _resize: function() {
      var scrollHeight = this.textarea.getScrollSize().y; //this.textarea.scrollHeight
      if( scrollHeight )
      {
        var newHeight = this._getHeight();
        var oldHeight = this.textarea.getSize().y;

        if( this._debug ){
          this._debug.innerHTML =
            'Old: ' + oldHeight + '<br />' +
            'Scroll: ' + scrollHeight + '<br />' +
            'New: ' + newHeight + '<br />' +
            '';
        }

        if( newHeight != oldHeight )
        {
          this.textarea.style.maxHeight = this.textarea.style.height = newHeight + 'px';
        }
      }
      else
      {
        this._estimate();
      }
    },

    _getHeight: function()
    {
      var height = this.textarea.getScrollSize().y;
      if( Browser.Engine.gecko ){
        height += this.textarea.offsetHeight - this.textarea.clientHeight;
      } else if( Browser.Engine.trident ) {
        height += this.textarea.offsetHeight - this.textarea.clientHeight;
      } else if( Browser.Engine.webkit ) {
        height += this.textarea.getStyle('border-top-width').toInt() + this.textarea.getStyle('border-bottom-width').toInt();
      } else if( Browser.Engine.presto ) { // Maybe need for safari < 4
        height += this.textarea.getStyle('padding-bottom').toInt();
      }
      return height;
    },

    _shrink: function()
    {
      if( this.options.useNullHeightShrink ){
        this.textarea.style.height = '0px';
        this._resize();
      } else {
        var scrollHeight = this.textarea.getScrollSize().y;
        var paddingBottom = this.textarea.getStyle('padding-bottom').toInt();

        // tweak padding to see if height can be reduced
        this.textarea.style.paddingBottom = paddingBottom + 1 + "px";
        // see if the height changed by the 1px added
        var newHeight = this._getHeight() - 1;
        // if can be reduced, so now try a big chunk
        if( this.textarea.getStyle('max-height').toInt() != newHeight )
        {
          this.textarea.style.paddingBottom = paddingBottom + scrollHeight + "px";
          this.textarea.scrollTop = 0;
          this.textarea.style.maxHeight = this._getHeight() - scrollHeight + "px";
        }

        this.textarea.style.paddingBottom = paddingBottom + 'px';
      }
    },

    _estimate: function()
    {
      this.textarea.style.maxHeight = "";
      this.textarea.style.height = "auto";
      this.textarea.rows = (this.textarea.value.match(/(\r\n?|\n)/g) || []).length + 1;
    },

    clear: function() {
      this.textarea.removeEvent('focus', this.handle);
      //this.textarea.removeEvent('keydown', this.handle);
      this.textarea.removeEvent('keyup', this.handle);
      this.textarea.removeEvent('paste', this.handle);
      this.textarea.removeEvent('cut', this.handle);
      if( Brower.Engine.webkit || Browser.Engine.trident ) this.textarea.removeEvent('scroll', this.handle);
      //this.textarea.removeEvent('resize', this.handle);
      $clear(this._interval);
    }

  });

  Element.implement({

    autogrow : function(options)
    {
      if( !options )
        options = this.retrieve('AutoGrowOptions');
      else
        this.store('AutoGrowOptions', options);

      if( this.retrieve('AutoGrowInstance') )
        this.retrieve('AutoGrowInstance').clear();

      var tmp = new AutoGrow(this, options);

      return this;
    }

  });
  
  
})();


/**
 * String stuff
 */
String.implement({

  replaceLinks : function(){
    var tmp = this.replace(/http\:\/\/\w[^\s<>'"]+/ig, function(match){
    //var tmp = this.replace(/http\:\/\/\w[\d.?&=\/-]+/ig, function(match){
      return '<a href="' + encodeURI(match) + '" target="_blank">' + match.escapeQuotes() + '</a>'
    }.bind(this));
    return tmp;
  },

  cTrim : function(chars) {
    if( !chars ) return this;
    var reg = new RegExp("^["+chars+"]+|["+chars+"]+$", "g");
    return this.replace(reg, '');
  },

  allIndexesOf : function(searchValue)
  {
    var posArr = [];
    var pos = -1;
    var loop = 100;
    do {
      pos = this.indexOf(searchValue, pos + 1);
      if( pos != -1 ) posArr.push(pos);
      loop--;
    } while( pos != -1 && loop > 0 );
    return posArr;
  },

  stripTags : function() {
    return this.replace(/<[^<>]+?>/g)
  },
  
  escapeHTML : function()
  {
     var div = document.createElement('div');
     var text = document.createTextNode(this);
     div.appendChild(text);
     return div.innerHTML;
  },

  htmlSpecialChars : function() {
    var tmp = this.replace('&', '&amp;')
      .replace('<', '&lt;')
      .replace('>', '&gt;')
      .replace('"', '&quot;')
      .replace('\'', '&#039;')
      ;
    return tmp;
  },

  escapeQuotes : function() {
    return this.replace('"', '\"').replace('\'', '\\\'');
  }
 
});

Element.implement({

  enableViewMore : function(){
    if( this.retrieve('ChooViewMore', false) ) return this;
    this.store('ChooViewMore', true);

    var innerHTML = this.get('html');
    
    // Less than min, don't bother
    if( innerHTML.length < 255 ){
      return this;
    }

    // More than max, truncate
    if( innerHTML.length > 1027 ){
      innerHTML = innerHTML.substring(0, 1027) + ' ...';
    }
    
    // @todo make sure this doesn't foobar HTML
    var lessEl = new Element('span', {
      'html' : innerHTML.substring(0, 255)
    });
    var moreEl = new Element('span', {
      'html' : innerHTML,
      'styles' : {
        'display' : 'none'
      }
    });

    var moreLink = new Element('a', {
      'href' : 'javascript:void(0);',
      'html' : ' more',
      'events' : {
        'click' : function(){
          lessEl.style.display = 'none';
          moreEl.style.display = '';
        }
      }
    });

    this.empty();
    lessEl.inject(this);
    moreEl.inject(this);
    moreLink.inject(lessEl);

    return this;
  },

  enableLinks : function() {
    try {
      // alpha: \\w
      // digit: \\d
      // safe: $_.+-
      // extra: !*'(),
      // national: {}|\^~[]`
      // punctuation: <>#%"
      // reserved: ;/?:@&=
      // hex: \\dabcdefABCDEF or \\da-fA-F
      // escape: %\da-fA-F
      // unreservered: \\w\\d$_.+-!*'(),
      // uchar: \\w\\d$_.+-!*'(),%\da-fA-F
      // xchar: \\w\\d$_.+-!*'(),%\da-fA-F;/?:@&=
      var urlRegexp = new RegExp(
        '(([\\w]{3,}:)(\\/\\/)?)' + // Schema
        '(' + // Userinfo
          '[\\w\\d$_.+!*\'(),-]+' + // User
          '(:[\\w\\d$_.+!*\'(),-]+)?' + // Password
        '@)?' + // Userinfo
        '(' + // Host
          '(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}(1[0-9]{2}|2[0-4][0-9]|25[0-5]|[1-9][0-9]|[0-9])|' + // IP
          '(([a-zA-Z0-9][a-zA-Z0-9\\-]*[a-zA-Z0-9])\\.|[a-zA-Z0-9])*([A-Za-z]|[A-Za-z][A-Za-z0-9\\-]*[A-Za-z0-9])' + // Hostname
        ')' + // Host
        '(:\\d+)?' + // Port
        '(/[\\w\\d$_.+!*\'(),;/:@&=-]*)?' + // Path
        '(\\?[\\w\\d$_.+!*\'(),;/:@&=-]*)?' + // Query
        '(#[\\w\\d$_.+!*\'(),;/:@&=-]*)?', // Fragment
      'ig');
      //var urlRegexp = new RegExp('http\\:\\/\\/\\w[^\\s<>\'"]+');
    } catch(e) {
      return this;
    }

    [this].combine(this.getElements('*')).each(function(el) {
      try {
        // Already inside a link
        if( $type(el.getParent('a')) == 'element' ) { // Ignore if we are inside a link already
          return;
        }
        
        // Index nodes
        var nodeIndex = [], i, l;
        for( i = 0, l = el.childNodes.length; i < l; i++ ) {
          if( el.childNodes[i].nodeType != 3 ) { // Not a text node
            continue;
          }
          if( $type(el.childNodes[i].nodeValue) != 'string' ) { // Node value not a string
            continue;
          }
          nodeIndex.push(el.childNodes[i]);
        }

        // Nothing to do
        if( nodeIndex.length <= 0 ) {
          return;
        }

        // Replace
        for( i = 0, l = nodeIndex.length; i < l; i++ ) {
          var currentNode = nodeIndex[i];
          var value = currentNode.nodeValue;
          var replacements;
          try {
            replacements = value.match(urlRegexp);
          } catch( e ) {
            continue;
          }
          
          // Nothing to do
          if( $type(replacements) != 'array' || replacements.length <= 0 ) {
            continue;
          }

          // Actually replace
          var lastPos = 0;
          for( var j = 0, k = replacements.length; j < k; j++ ) {
            var currentReplacement = replacements[j];
            // Remove punctuation
            var punctuation = '.,;:?!';
            var last = currentReplacement.substring(currentReplacement.length - 1);
            while( -1 !== punctuation.indexOf(last) ) {
              // remove
              currentReplacement = currentReplacement.substring(0, currentReplacement.length - 1);
              last = currentReplacement.substring(currentReplacement.length - 1);
            }

            var curPos = value.indexOf(currentReplacement, lastPos);
            if( -1 == curPos ) {
              continue;
            }
            var curText = value.substring(lastPos, curPos);

            // Add text node
            currentNode.parentNode.insertBefore(document.createTextNode(curText), currentNode);
            currentNode.parentNode.insertBefore(new Element('a', {
              'href' : currentReplacement,
              'target' : '_blank',
              'html' : currentReplacement
            }), currentNode);

            // Update last pos
            lastPos = curPos + currentReplacement.length;

            // Last one
            if( j == k - 1 ) {
              curText = value.substring(lastPos);
              currentNode.parentNode.insertBefore(document.createTextNode(curText), currentNode);
            }
          }

          // Remove
          currentNode.parentNode.removeChild(currentNode);
        }
      } catch( e ) { if( 'console' in window && 'log' in console ) console.log(e) }
    });
    
    return this;
  }
});





/**
 * Idle
 */
(function() {
  var IdleWatcherInstance;
  
  var IdleWatcher = window.IdleWatcher = new Class({

    Implements : [Options, Events],

    options : {
      timeout : 20000,
      poll : 500
    },

    time : 0,

    state : 1,

    interval : false,
    
    initialize : function(observer, options) {
      this.observer = observer || window;
      this.setOptions(options);
      this.time = $time();
    },

    register : function() {
      window.addEvents({
        'scroll' : this.onActive.bind(this),
        'resize' : this.onActive.bind(this)
      });
      document.addEvents({
        'mousemove' : this.onActive.bind(this),
        'keydown' : this.onActive.bind(this)
      });
      this.interval = this.poll.periodical(this.options.poll, this);
    },

    unregister : function() {
      window.removeEvents({
        'scroll' : this.onActive.bind(this),
        'resize' : this.onActive.bind(this)
      });
      document.removeEvents({
        'mousemove' : this.onActive.bind(this),
        'keydown' : this.onActive.bind(this)
      });
    },

    onActive : function(e) {
      if( this.state == 0 ) {
        this.state = 1;
        var delta = $time() - this.time;
        this.observer.fireEvent('onStateActive', {'time' : delta});
      }
      this.time = $time();
    },

    poll : function() {
      if( this.state == 1 ) {
        if( $time() - this.time > this.options.timeout )
        {
          this.state = 0;
          this.observer.fireEvent('onStateIdle');
        }
      }
    }
  });

  IdleWatcherInstance = window._IdleWatcher = new IdleWatcher();
  IdleWatcherInstance.register();

})();





function fix_gecko_select_all_contenteditable_bug(ed, event) {
	// thanks to Bodo Schulze for this fix:
	// http://blog.cms-schulze.de/2009/07/09/work-around-gecko-contenteditable-bug-436703/trackback/

    if( !Browser.Engine.gecko ) return;

    // get selection object
    var sel = window.getSelection();
    if (!sel) return;

    // focus the editor
    ed.focus();

    // the fix: append an empty textnode
    ed.appendChild(document.createTextNode(''));

    // get a range object and fix its boundaries
    var range = sel.getRangeAt(0);
    range.setStart(ed.firstChild, 0);
    range.setEnd(ed.lastChild, ed.lastChild.length);

    // put the fixed range into the visual selection
    sel.removeAllRanges();
    sel.addRange(range);
    event.stop();
}


function htmlspecialchars_decode (string, quote_style) {
    // Convert special HTML entities back to characters
    //
    // version: 1004.2314
    // discuss at: http://phpjs.org/functions/htmlspecialchars_decode
    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Mateusz "loonquawl" Zalega
    // +      input by: ReverseSyntax
    // +      input by: Slawomir Kaniecki
    // +      input by: Scott Cariss
    // +      input by: Francois
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
    // *     returns 1: '<p>this -> &quot;</p>'
    // *     example 2: htmlspecialchars_decode("&amp;quot;");
    // *     returns 2: '&quot;'
    var optTemp = 0, i = 0, noquotes= false;
    if (typeof quote_style === 'undefined') {
        quote_style = 2;
    }
    string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE' : 1,
        'ENT_HTML_QUOTE_DOUBLE' : 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE' : 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i=0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
        // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"');
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');

    return string;
}


// Sprintf

String.implement({

  vsprintf : function(args) {
    str = this;
    // Check for no params
    if( !args || !args.length )
    {
      return str;
    }

    // Replace params
    var out = '';
    var m;
    var masterIndex = 0;
    var currentIndex;
    var arg;
    var instr;
    var meth;
    var sign;
    while( str.length > 0 )
    {
      // Check for no more expressions
      if( !str.match(/[%]/) )
      {
        out += str;
        break;
      }

      // Remove any preceeding non-expressions
      m = str.match(/^([^%]+?)([%].+)?$/)
      if( m )
      {
        out += m[1];
        str = typeof(m[2]) ? m[2] : '';
        if( str == '' )
        {
          break;
        }
      }

      // Check for escaped %
      if( str.substring(0, 2) == '%%' )
      {
        str = str.substring(2);
        out += '%';
        continue;
      }

      // Proc next params
      m = str.match(/^[%](?:([0-9]+)\x24)?(\x2B)?(\x30|\x27[^$])?(\x2D)?([0-9]+)?(?:\x2E([0-9]+))?([bcdeEfosuxX])/)
      if( m )
      {
        instr = m[7];
        meth = m[6] || false;
        sign = m[2] || false;
        currentIndex = ( m[1] ? m[1] - 1 : masterIndex++ );
        if( $type(args[currentIndex]) )
        {
          arg = args[currentIndex];
        }
        else
        {
          throw('Undefined argument for index ' + currentIndex);
        }

        // Make sure passed sane argument type
        switch( typeof(arg) )
        {
          case 'number':
          case 'string':
          case 'boolean':
            // Okay
            break;

          case 'undefined':
            if( arg == null )
            {
              arg = '';
              break;
            }
          default:
            throw('Unknown argument type: ' + typeof(arg));
            break;
        }

        // Now proc instr
        switch( instr )
        {
          // Binary
          case 'b':
            if( typeof(arg) != 'number' ) arg = parseInt(arg);
            arg = arg.toString(2);
            break;

          // Char
          case 'c':
            arg = String.fromCharCode(arg);
            break;

          // Integer
          case 'd':
            arg = parseInt(arg);
            break;

          // Scientific notation
          case 'E':
          case 'e':
            if( typeof(arg) != 'number' ) arg = parseFloat(arg);
            if( meth )
            {
              arg = arg.toExponential(meth);
            }
            else
            {
              arg = arg.toExponential();
            }
            if( instr == 'E' ) arg = arg.toUpperCase();
            break;

          // Unsigned integer
          case 'u':
            arg = Math.abs(parseInt(arg));
            break;

          // Float
          case 'f':
            if( meth )
            {
              arg = parseFloat(arg).toFixed(meth)
            }
            else
            {
              arg = parseFloat(arg);
            }
            break;

          // Octal
          case 'o':
            if( typeof(arg) != 'number' ) arg = parseInt(arg);
            arg = arg.toString(8);
            break;

          // String
          case 's':
            if( typeof(arg) != 'string' ) arg = String(arg);
            if( meth )
            {
              arg = arg.substring(0, meth);
            }
            break;

          // Hex
          case 'x':
          case 'X':
            if( typeof(arg) != 'number' ) arg = parseInt(arg);
            arg = arg.toString(8);
            if( instr == 'X' ) arg = arg.toUpperCase();
            break;
        }

        // Add a sign if requested
        if( (instr == 'd' || instr == 'e' || instr == 'f') && sign && arg > 0 )
        {
          arg = '+' + arg;
        }

        // Do repeating if necessary
        var repeatChar, repeatCount;
        if( m[3] )
        {
          repeatChar = m[3];
        }
        else
        {
          repeatChar = ' ';
        }
        if( m[5] )
        {
          repeatCount = m[5];
        }
        else
        {
          repeatCount = 0;
        }
        repeatCount -= arg.length;

        // Do the repeating
        if( repeatCount > 0 )
        {
          var paddedness = function(str, count)
          {
            var ret = '';
            while( count > 0 )
            {
              ret += str;
              count--;
            }
            return ret;
          }(repeatChar, repeatCount);

          if( m[4] )
          {
            out += arg + paddedness;
          }
          else
          {
            out += paddedness + arg;
          }
        }

        // Just add the string
        else
        {
          out += arg;
        }

        // Remove from str
        str = str.substring(m[0].length);
      }
      else
      {
        throw('Malformed expression in string: ' + str);
      }
    }

    return out;
  },

  sprintf : function() {
    return this.vsprintf(arguments);
  }

});



Request.Post = new Class({

  url : false,

  data : false,

  options : {
    formOptions : {
      target : null,
      enctype : null,
      method : 'post'
    }
  },

  form : false,

  initialize : function(options) {
    // Example:
    // (new Request.Post({url:'http://www.google.com',data:{'test': 'test', 'arr' : ['a','b'], 'obj':{'a' : 'b', 'x' : 'y'}}})).send();

    // Get url
    if( !('url' in options) ) {
      throw "no url given";
    }
    this.url = options.url;
    delete options.url;
    
    // get data
    if( 'data' in options ) {
      this.data = options.data;
      delete options.data;
    }
    
    // save the rest of the options for now
    this.options = $merge(this.options, options);

    // render now?
    this.render();

  },

  send : function() {
    this.render();
    this.form.submit();
  },

  render : function() {
    if( this.form ) {
      return this;
    }
    
    this.form = new Element('form', $merge(this.options.formOptions, {
      'action' : this.url,
      'styles' : {
        'display' : 'none'
      }
    }));

    this.form.inject(document.body);

    this._recursiveRender(this.data);
    
    return this;
  },

  _recursiveRender : function(data, path) {
    if( $type(path) != 'array' ) {
      path = [];
    }
    //var childPath = path.clone();
    var childPath = [].extend(path);
    switch( $type(data) ) {
      // Collections
      case 'collection':
      case 'array':
        $A(data).each(function(value, index) {
          childPath = [].extend(path);
          childPath.push(index);
          this._recursiveRender(value, childPath);
        }.bind(this));
        break;
      case 'object':
        $H(data).each(function(value, index) {
          childPath = [].extend(path);
          childPath.push(index);
          this._recursiveRender(value, childPath);
        }.bind(this));
        break;

      // Weird
      case 'element':
        if( data.get('value') ) {
          this._recursiveRender(data.get('value'), childPath);
        }
        break;
      case 'event':
        this._recursiveRender($(data.target), childPath);
        break;
      case 'textnode':
        this._recursiveRender(data.nodeValue, childPath);
        break;
      case 'function':
        this._recursiveRender(data(), childPath);
        break;

      // Normal
      case 'string':
      case 'number':
      case 'date':
      case 'boolean':
        this._renderOne(data, childPath);
        break;

      // Skip
      case 'regexp':
      case 'class':
      case 'window':
      case 'document':
      default:
        break;
    }
  },

  _renderOne : function(data, path) {
    (new Element('input', {
      'type' : 'hidden',
      'name' : this._buildElementName(path),
      'value' : data
    })).inject(this.form);
    return this;
  },

  _buildElementName : function(path) {
    var name = '';
    path.each(function(value, index) {
      if( '' == name ) {
        name = value;
      } else {
        name += '[' + value + ']';
      }
    });
    return name;
  }

})