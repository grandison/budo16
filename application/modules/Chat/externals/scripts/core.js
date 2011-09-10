// preload chat sound
soundManager.url = 'externals/soundmanager/swf/';
soundManager.onready(function() {
  if( soundManager.supported() ) {
    soundManager.createSound({
      id: '_chat_ding',
      url: 'application/modules/Chat/externals/ding.mp3',
      autoLoad: true,
      volume: 50
    });
  }
});

var ChatHandler = new Class({

  Implements : [Events, Options],

  options : {
    debug : false,
    baseUrl : '/',
    identity : false,
    delay : 3000,
    requestTimeout: 10000,
    admin : false,
    idleTimeout : 60000,

    // Chat specific
    enableChat : true,
    chatOptions : {
      identity : 1,
      operator : false
    },

    // IM specific
    enableIM : true,
    imOptions : {
      container : false,
      memberIm : false
    }
  },

  state : false,

  chatstate : 0,

  imstate : 0,

  activestate : 1,

  fresh : true,

  rooms : {},

  lastEventTime : false,

  initialize : function(options) {
    this.setOptions(options);
    this.options.requestTimeout = Math.round(this.options.delay * 0.5);
    this.rooms = new Hash();
  },

  start : function() {
    this.state = true;
    
    if( this.options.enableIM ) {
      this.startIm(this.options.imOptions);
    }

    if( this.options.enableChat ) {
      this.startChat(this.options.chatOptions);
    }

    this.addEvent('onEvent_reconfigure', this.onReconfigure.bind(this));
    
    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this.activestate = 1;
        this.status(1);
      }.bind(this),
      'onStateIdle' : function() {
        this.activestate = 0;
        this.status(2);
      }.bind(this)
    });
    
    this.loop();
  },

  startIm : function(options) {
    if( $type(this.im) ) {
      return; // Maybe destroy it later
    }

    this.options.imOptions = $merge(this.options.imOptions, options);
    this.im = new ChatHandler_Whispers(this, this.options.imOptions);
    this.imstate = 1;

    var savedState = Cookie.read('en4_chat_imstate', {path:en4.core.basePath});
    if( savedState == 0 ) {
      this.im.items.settings.toggleOnline();
    }
  },

  startChat : function(options) {
    // If no id or already in, ignore
    var identity = options.identity || Cookie.read('en4_chat_room_last', {path:en4.core.basePath}) || 1;
    options.identity = identity;
    if( !identity || $type(this.rooms.get(identity)) ) return;

    // Close any open rooms (if any)
    if( this.rooms.getLength() > 0 ) {
      var count = 0;
      this.rooms.each(function(room) {
        count++;
        this.leave(room.options.identity).addEvent('complete', function() {
          room.destroy();
          this.rooms.erase(room.options.identity);
          count--;
          if( count <= 0 ) {
            this.options.chatOptions = $merge(this.options.chatOptions, options);
            this.rooms.set(identity, new ChatHandler_Room(this, this.options.chatOptions));
          }
        }.bind(this));
      }.bind(this));
      return;
    }

    // Just open
    this.options.chatOptions = $merge(this.options.chatOptions, options);
    this.rooms.set(identity, new ChatHandler_Room(this, this.options.chatOptions));
    this.chatstate = 1;
  },

  stop : function() {
    this.state = false;
  },

  loop : function() {
    if( !this.state || (!this.imstate && !this.chatstate) ) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      // send request
      var request = this.ping();
      var completed = false;
      request.addEvent('complete', function() {
        completed = true;
        this.loop.delay(this.options.delay, this);
      }.bind(this));

      // Start request timeout handler
      var timeoutFunc = function() {
        if( !completed ) {
          request.removeEvents('complete');
          request.cancel();
          this.loop();
        }
      }.bind(this);
      timeoutFunc.delay(this.options.requestTimeout, this);
      
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }
  },


  
  // Start requests

  ping : function() {
    this._log({'type' : 'cmd.ping'});

    var fresh = this.fresh;
    this.fresh = false;

    // Gather extra data
    var extraData = {};
    this.fireEvent('onPingBefore', extraData);

    var request = new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=ping',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/ping',
      data : $merge({
        'format' : 'json',
        'fresh' : fresh,
        'lastEventTime' : this.lastEventTime || null
      }, extraData),
      onSuccess : this.onPingResponse.bind(this)
    });

    request.send();
    return request;
  },
  
  join : function(room_id) {
    this._log({'type' : 'cmd.join', 'room_id' : room_id});

    var request = new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=join',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/join',
      data : {
        'format' : 'json',
        'room_id' : room_id
      },
      onSuccess : this.onJoinResponse.bindWithEvent(this, room_id)
    });

    request.send();
    return request;
  },

  leave : function(room_id) {
    this._log({'type' : 'cmd.leave', 'room_id' : room_id});

    var request = new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=leave',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/leave',
      data : {
        'format' : 'json',
        'room_id' : room_id
      },
      onSuccess : this.onLeaveResponse.bindWithEvent(this, room_id)
    });
    
    request.send();
    return request;
  },

  send : function(room_id, message, callback) {
    this._log({'type' : 'cmd.send', 'room_id' : room_id, 'message' : message});

    return (new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=send',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/send',
      data : {
        'format' : 'json',
        'room_id' : room_id,
        'message' : message
      },
      onSuccess : this.onSendResponse.bindWithEvent(this, [room_id, callback])
    })).send();
  },

  whisper : function(user_id, message, callback) {
    this._log({'type' : 'cmd.whisper', 'user_id' : user_id, 'message' : message});
    
    return (new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=whisper',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/whisper',
      data : {
        'format' : 'json',
        'user_id' : user_id,
        'message' : message
      },
      onSuccess : this.onWhisperResponse.bindWithEvent(this, [user_id, callback])
    })).send();
  },

  whisperClose : function(user_id) {
    this._log({'type' : 'cmd.whisperClose', 'user_id' : user_id});

    return (new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=whisper-close',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/whisper-close',
      data : {
        'format' : 'json',
        'user_id' : user_id
      },
      onSuccess : this.onWhisperCloseResponse.bind(this)
    })).send();
  },

  status : function(state, type) {
    this._log({'type' : 'cmd.status', 'state' : state});

    return (new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=status',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/status',
      data : {
        'format' : 'json',
        'status' : state,
        'type' : type
      },
      onSuccess : this.onStatusResponse.bind(this)
    })).send();
  },

  list : function() {
    this._log({'type' : 'cmd.list'});

    return (new Request.JSON({
      url : this.options.baseUrl + '?m=lite&module=chat&action=list',
      timeout : this.options.requestTimeout,
      noCache : true,
      //url : this.options.baseUrl + 'chat/ajax/list',
      data : {
        'format' : 'json'
      },
      onSuccess : this.onListResponse.bind(this)
    })).send();
  },


  // Handle requests

  onPingResponse : function(responseJSON) {
    this._log({'type' : 'resp.ping', 'data' : responseJSON});

    try {
      if( $type(responseJSON) == 'object' ) {

        if( $type(responseJSON.lastEventTime) && responseJSON.lastEventTime ) {
          this.lastEventTime = responseJSON.lastEventTime;
        }

        // Online friends
        if( $type(responseJSON.users) == 'object' ) {
          for( var x in responseJSON.users ) {
            var data = responseJSON.users[x];
            this.fireEvent('onEvent_presence', data);
          }
        }

        // Whispers
        if( $type(responseJSON.whispers) == 'object' ) {
          for( var x in responseJSON.whispers ) {
            this.fireEvent('onEvent_chat', responseJSON.whispers[x]);
          }
        }

        // Events
        if( $type(responseJSON.events) == 'object' ) {
          for( var x in responseJSON.events ) {
            var type      = responseJSON.events[x].type;
            var eventName = 'onEvent_' + type;
            if (type == 'chat' && responseJSON.events[x].sender_id != en4.user.viewer.id && 'object' == $type(soundManager)) {
              // ding!
              soundManager.getSoundById('_chat_ding').play();
            }
            this.fireEvent(eventName, responseJSON.events[x]);
          }
        }

      }
    }

    catch( e )
    {
      this._log({type:'error', data: e});
    }
    if( $type(responseJSON.status) && responseJSON.status ) {
      this.fireEvent('onPingSuccess', responseJSON);
    } else {
      this.fireEvent('onPingFailure', responseJSON);
    }
  },

  onJoinResponse : function(responseJSON, room_id) {
    this._log({'type' : 'resp.join', 'data' : responseJSON, 'room_id' : room_id});

    // We were sent back the data anyway (we probably already are in it)
    /*
    if( $type(responseJSON.room.room_id) ) {
      var room_id = responseJSON.room.room_id;
      if( !$type(this.rooms[room_id]) ) {
        this.rooms[room_id] = new ChatHandler_Room(this, $merge(this.options.chatOptions, {
          identity : room_id
        }), responseJSON);
      }
    }
    */

    responseJSON.room_id = room_id;
    this.fireEvent('onJoin', responseJSON);
    /*
    if( $type(responseJSON.status) && responseJSON.status ) {
      this.fireEvent('onJoinSuccess', responseJSON);
    } else {
      this.fireEvent('onJoinFailure', responseJSON);
    }
    */
  },

  onLeaveResponse : function(responseJSON, room_id) {
    this._log({'type' : 'resp.leave', 'data' : responseJSON, 'room_id' : room_id});

    responseJSON.room_id = room_id;
    this.fireEvent('onLeave', responseJSON);
    /*
    if( $type(responseJSON.status) && responseJSON.status ) {
      this.fireEvent('onLeaveSuccess', responseJSON);
    } else {
      this.fireEvent('onLeaveFailure', responseJSON);
    }
    */
  },

  onSendResponse : function(responseJSON, room_id, callback) {
    this._log({'type' : 'resp.send', 'data' : responseJSON});

    if( $type(responseJSON.status) && responseJSON.status ) {
      this.fireEvent('onSendSuccess', responseJSON);
    } else {
      this.fireEvent('onSendFailure', responseJSON);
    }
    
    if( $type(callback) == 'function' ) {
      callback(responseJSON, room_id);
    }
  },

  onWhisperResponse : function(responseJSON, user_id, callback) {
    this._log({'type' : 'resp.whisper', 'data' : responseJSON});
    
    if( $type(responseJSON.status) && responseJSON.status ) {
      this.fireEvent('onWhisperSuccess', responseJSON);
    } else {
      this.fireEvent('onWhisperFailure', responseJSON);
    }

    if( $type(callback) == 'function' ) {
      callback(responseJSON, user_id);
    }
  },

  onWhisperCloseResponse : function(responseJSON) {
    this._log({'type' : 'resp.whisperClose', 'data' : responseJSON});

    this.fireEvent('onWhisperClose', responseJSON);
  },

  onStatusResponse : function(responseJSON) {

  },

  onListResponse : function(responseJSON) {
    this.fireEvent('onListRooms', responseJSON);
  },



  // Other events

  onReconfigure : function(data) {
    if( $type(data.delay) ) {
      this.options.delay = data.delay;
    }
    if( $type(data.chat_enabled) ) {
      // Disabling chat
      if( parseInt(data.chat_enabled) == 0 ) {
        this.rooms.each(function(room) {
          room.destroy();
        });
        (new Element('div', {
          'html' : en4.core.language.translate('The chat room has been disabled by the site admin.')
        }).inject(this.container || $('global_content') || document.body));
      }
      // Enabling chat
      else
      {
        // dont do anything
      }
    }
    if( $type(data.im_enabled) ) {
      if( parseInt(data.im_enabled) == 0 ) {
        if( $type(this.im) ) {
          this.im.destroy();
        }
      }
    }
  },




  // Utility
  
  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }
    
    // Firefox is dumb and causes problems sometimes with console
    try {
      if( typeof(console) && $type(console) ) {
        console.log(object);
      }
    } catch( e ) {
      // Silence
    }
  },
  
  _supportsContentEditable : function() {
    return ( true == ('contentEditable' in document.body) );
    /*
    if( Browser.Engine.trident && Browser.Engine.version >= 4 ) { // Might support it even before 4, but mootools doesn't detect before that
      return true;
    }else if( Browser.Engine.gecko && Browser.Engine.version >= 19 ) {
      return true;
    }else if( Browser.Engine.webkit && Browser.Engine.version >= 419 ) { // Not enough information to confirm
      return true;
    }else if( Browser.Engine.presto && Browser.Engine.version >= 925 ) { // Not enough information to confirm
      return true;
    }
    return false;
    */
  }
});



var ChatHandler_Room = new Class({

  Implements : [Events, Options],

  options : {
    identity : false,
    rateMessages : 10,
    rateTimeout : 10000,
    maxLength : 1023
  },

  handler : false,

  data : {},

  elements : {},

  rate : [],

  initialize : function(handler, options) {
    this.handler = handler;
    this.room = new Hash();
    this.users = new Hash();
    this.elements = new Hash();
    this.setOptions(options);
    
    this.render();
    this.attach();
    
    this.handler.join(this.options.identity);
    Cookie.write('en4_chat_room_last', this.options.identity, {path:en4.core.basePath});
  },

  destroy : function() {
    this.detach();
    this.handler.chatstate = 0;
    this.elements.container.destroy();
  },

  attach : function() {
    this.handler.addEvent('onPingBefore', this.onPingBefore.bind(this));
    this.handler.addEvent('onJoin', this.onJoin.bind(this));
    this.handler.addEvent('onLeave', this.onLeave.bind(this));
    this.handler.addEvent('onEvent_grouppresence', this.onPresence.bind(this));
    this.handler.addEvent('onEvent_groupchat', this.onGroupChat.bind(this));
    this.handler.addEvent('onListRooms', this.onListRooms.bind(this));
  },

  detach : function() {
    this.handler.removeEvent('onPingBefore', this.onPingBefore.bind(this));
    this.handler.removeEvent('onJoin', this.onJoin.bind(this));
    this.handler.removeEvent('onLeave', this.onLeave.bind(this));
    this.handler.removeEvent('onEvent_grouppresence', this.onPresence.bind(this));
    this.handler.removeEvent('onEvent_groupchat', this.onGroupChat.bind(this));
    this.handler.removeEvent('onListRooms', this.onListRooms.bind(this));
  },

  render : function() {
    
    var identity = this.options.identity;

    var self = this;
    if( $('chat_container') ) {
      $('chat_container').destroy();
    }

    // Container
    this.elements.container = new Element('div', {
      'id' : 'chat_container_' + identity,
      'class' : 'chat_container'
    }).inject(this.container || $(this.options.container) || $('global_content') || document.body);

    // Header
    this.elements.header = new Element('div', {
      'class' : 'chat_header'
    }).inject(this.elements.container);

    // Title
    this.elements.headerTitle = new Element('div', {
      'class' : 'chat_header_title'
    }).inject(this.elements.header);

    // Menu
    /*
    this.elements.headerMenu = new Element('div', {
      'id' : 'chat_header_menu_' + identity,
      'class' : 'chat_header_menu'
    }).inject(this.elements.header);
    */
   
    // Rooms
    var roomList = new Hash(this.options.roomList);
    if( roomList.getKeys().length > 0 ) {

      this.elements.roomsButton = new Element('span', {
        'class' : 'pulldown',
        'events' : {
          'click' : function() {
            if( this.elements.roomsButton.hasClass('pulldown_active') ) {
              this.elements.roomsButton.removeClass('pulldown_active').addClass('pulldown');
              // Get rooms
              this.handler.list();
            } else {
              this.elements.roomsButton.removeClass('pulldown').addClass('pulldown_active');
            }
          }.bind(this)
        }
      }).inject(this.elements.header);
      //(new Element('span', 'Browse Chatrooms')).inject((new Element('div')).inject(this.elements.roomsButton));

      var pulldownWrapper = new Element('div', {'class' : 'pulldown_contents_wrapper'}).inject(this.elements.roomsButton);
      var pulldownContainer = new Element('div', {'class' : 'pulldown_contents'}).inject(pulldownWrapper);

      this.elements.roomsList = new Element('ul', {
        /*
        'events' : {
          'click' : function(event) {
            var element = event.target;

            // Change rooms
            if( element.tagName.toLowerCase() != 'ul' ) {
              if( element.tagName.toLowerCase() != 'li' ) {
                element = element.getParent();
                if( element.tagName.toLowerCase() != 'li' ) {
                  return;
                }
              }
              event.stop();
              var room_id = element.id.replace(/[^0-9]/g, '');
              alert(room_id);
              self.handler.startChat({identity:room_id});
            }
          }
        }
        */
      }).inject(pulldownContainer);

      new Element('a', {
        'href' : 'javascript:void(0);',
        'html' : en4.core.language.translate('Browse Chatrooms')
      }).inject(this.elements.roomsButton);

      this.onListRooms(roomList);
      /*
      roomList.each(function(data) {
        alert(data.identity);
        var tmpRoomLiEl = new Element('li', {
          'id' : 'chat_room_link_' + data.identity
        }).inject(this.elements.roomsList);
        (new Element('div', {'html' : data.title})).inject(tmpRoomLiEl);
        (new Element('div', {'html' : data.people})).inject(tmpRoomLiEl);
      }.bind(this));
      */
    }

    // Users
    this.elements.usersWrapper = new Element('div', {
      'id' : 'chat_users_wrapper_' + identity,
      'class' : 'chat_users_wrapper'
    }).inject(this.elements.container);
    
    this.elements.usersList = new Element('ul', {
      'id' : 'chat_users_' + identity,
      'class' : 'chat_users chat_users_list'
    }).inject(this.elements.usersWrapper);

    // Body
    this.elements.body = new Element('div', {
      'id' : 'chat_main_' + identity,
      'class' : 'chat_main chat_body'
    }).inject(this.elements.container);

    // Messages
    this.elements.messagesWrapper = new Element('div', {
      'id' : 'chat_messages_wrapper_' + identity,
      'class' : 'chat_messages_wrapper'
    }).inject(this.elements.body);

    this.elements.messagesList = new Element('ul', {
      'id' : 'chat_messages_' + identity,
      'class' : 'chat_messages chat_messages_list'
    }).inject(this.elements.messagesWrapper);

    // Input
    this.elements.inputWrapper = new Element('div', {
      'id' : 'chat_input_wrapper_' + identity,
      'class' : 'chat_input_wrapper'
    }).inject(this.elements.body);

    if( this.handler._supportsContentEditable() ) {
      // Div+ContentEditable
      this.elements.input = new Element('div', {
        'class' : 'chat_input',
        'html' : ( Browser.Engine.gecko ? '<p><br /></p>' : '' ),
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
      }).inject(this.elements.inputWrapper);
      this.elements.input.contentEditable = true;
    } else {
      // Input
      this.elements.input = new Element('input', {
        'id' : 'chat_input_' + identity,
        'class' : 'chat_input',
        'type' : 'text'
      }).inject(this.elements.inputWrapper);
    }

    this.elements.input.addEvents({
      'keypress' : function(event) {
        if( event.key == 'enter' ) {
          event.preventDefault();
          this.send();
        }
      }.bind(this)
    });
  },


  // Actions

  send : function() {

    var message;
    if( this.handler._supportsContentEditable() ) {
      message = this.elements.input.get('html');

      // Webkit, you're killing me!
      if( Browser.Engine.webkit ) {
        this.elements.input.destroy();
        delete this.elements.input;
        this.elements.input = new Element('div', {
          'id' : 'chat_input',
          'class' : 'chat_input',
          'html' : ( Browser.Engine.gecko ? '<p><br /></p>' : '' )
        }).inject(this.elements.inputWrapper);
        this.elements.input.contentEditable = true;
        this.elements.input.addEvents({
          'keypress' : function(event) {
            if( event.key == 'enter' ) {
              event.preventDefault();
              this.send();
            }
          }.bind(this)
        });
      // Everything else works fine
      } else {
        this.elements.input.empty();
        this.elements.input.set('html', '<p><br /></p>');
      }
      
      this.elements.input.focus();
      
      message = message.stripTags();
    } else {
      message = this.elements.input.get('value');
      this.elements.input.set('value', '');
    }

    message = message.trim();
    if( message == '' ) {
      return;
    }
    
    if( message.length > this.options.maxLength ) message = message.substring(0, this.options.maxLength);

    // Check rate
    this.rate = this.rate.filter(function(item) {
      return $time() < item + this.options.rateTimeout;
    }.bind(this));

    if( this.rate.length >= this.options.rateMessages ) {
      this.onGroupChat({
        'room_id' : this.options.identity,
        'system' : 1,
        'body' : en4.core.language.translate('You are sending messages too quickly - please wait a few seconds and try again.')
      });
      return;
    }

    this.rate.push($time());

    // Create el
    var ref = {};

    // For commands, don't display here
    if( message.indexOf('/') !== 0 ) {
      this.onGroupChat({
        room_id : this.options.identity,
        user_id : this.getSelf().identity,
        body : message
      }, ref);
    }
    
    this.handler.send(this.options.identity, message, function(responseJSON) {
      if( $type(ref.element) ) {
        ref.element.set('id', 'chat_message_'+responseJSON.message_id);
      }
    });
  },

  getSelf : function() {
    var self;
    this.users.each(function(data) {
      if( data.self ) {
        self = data;
      }
    });
    if( !$type(self) ) {
      self = {
        'identity' : 0,
        'title' : en4.core.language.translate('You')
      };
    }
    return self;
  },
  
  // Events

  onPingBefore : function(data) {
    data.rooms = [this.options.identity];
  },

  onJoin : function(data) {
    this.handler._log({type:'chat.join', data:data});
    // Remove existing messages and users (we are going to re-populate)
    this.elements.usersList.empty();
    this.elements.messagesList.empty();

    if( $type(data.room) == 'object' ) {
      this.room = $H(data.room);
    }

    if( $type(data.users) == 'object' ) {
      for( var x in data.users ) {
        this.onPresence(data.users[x]);
      }
    }

    this.elements.headerTitle.set('html', '<h3>' + en4.core.language.translate(data.room.title) + '</h3>');
  },

  onLeave : function(data) {
    if( data.room_id != this.options.identity ) return;
    this.destroy();
  },

  onPresence : function(data) {
    this.handler._log({type:'chat.presence', data:data, self:this.options.identity});
    if( data.room_id != this.options.identity ) return;

    // Update user info
    if( parseInt(data.state) > 0 || !this.users.has(data.identity) ) {
      this.users.set(data.identity, data);
    }

    // Get el
    var userElId = 'chat_room_' + this.options.identity + '_user_' + data.identity;
    var userEl = $(userElId);
    
    if( parseInt(data.state) >= 1 ) {
      if( !userEl ) {
        userEl = new Element('li', {
          'id' : userElId,
          'html' : '<span class="chat_user_photo"><img src="' + (data.photo || 'application/modules/User/externals/images/nophoto_user_thumb_icon.png') + '" /></span>' + '<span class="chat_user_name"><a href="' + data.href + '" target="_blank">' + data.title + '</a></span>'
        }).inject(this.elements.usersList);

        // Add system notice
        if( !$type(data.stale) || !data.stale ) {
          this.onGroupChat({
            'system' : 1,
            'body' : en4.core.language.translate('%1$s has joined the room.', '<a href="' + data.href + '" target="_blank">' + data.title + '</a>'),
            'room_id' : this.options.identity
          });
        }
        
        // Do admin stuff
        /*
        if( this.options.operator || this.handler.options.admin ) {
          new Element('a', {
            'href' : this.handler.options.baseUrl + 'chat/index/ban/format/smoothbox',
            'class' : 'smoothbox',
            'html' : 'x'
          }).inject(userEl);
        }
        */
      }
      
      ChatHandler_Utility.applyStateClass(userEl.getElement('.chat_user_name'), parseInt(data.state));

    } else if( parseInt(data.state) < 1 ) {
      if( userEl ) {
        userEl.destroy();
        
        // Add system notice
        if( !$type(data.stale) || parseInt(data.stale) != 1 ) {
          this.onGroupChat({
            'system' : 1,
            'body' : en4.core.language.translate('%1$s has left the room.', '<a href="' + data.href + '" target="_blank">' + data.title + '</a>'),
            'room_id' : this.options.identity
          });
        }
      }
    }
  },

  onGroupChat : function(data, ref) {
    this.handler._log({type:'chat.message', data:data});
    if( data.room_id != this.options.identity ) return;

    // Check to see if we already have recv this message
    if( $type(data.message_id) && $('chat_message_'+data.message_id) ) {
      return;
    }

    var body = data.body;
    if( body.length > this.options.maxLength ) body = body.substring(0, this.options.maxLength);
    body = ChatHandler_Utility.replaceSmilies(body);
    body = body.replaceLinks();

    var msgWrpr, tmpDivEl;

    // System message
    if( $type(data.system) && parseInt(data.system)==1 ) {
      msgWrpr = new Element('li').inject(this.elements.messagesList);

      tmpDivEl = new Element('div', {
        'class' : 'chat_message_info'
      }).inject(msgWrpr);

      (new Element('span', {'html' : body, 'class' : 'chat_message_info_body chat_message_info_body_system'})).inject(tmpDivEl);
    }
    // Normal message
    else
    {
      var user = this.users.get(data.user_id);

      // Add message
      msgWrpr = new Element('li').inject(this.elements.messagesList);

      /*
      tmpDivEl = (new Element('div', {
        'class' : 'chat_message_info'
      })).inject(msgWrpr);
      */
     
      (new Element('div', {
        'class' : 'chat_message_photo',
        'html' : '<a href="' + user.href + '" target="_blank"><img src="' + (user.photo || 'application/modules/User/externals/images/nophoto_user_thumb_icon.png') + '" alt="" /></a>'
      })).inject(msgWrpr);

      (new Element('div', {
        'class' : 'chat_message_info',
        'html' : '\n\
  <span class="chat_message_info_author"><a href="' + user.href + '" target="_blank">' + user.title + '</a></span>\n\
  <span class="chat_message_info_body">' + body + '</span>\n\
  '
      })).inject(msgWrpr);
    }

    if( $type(msgWrpr) && $type(data.message_id) ) {
      msgWrpr.set('id', 'chat_message_'+data.message_id);
    }

    if( $type(ref) == 'object' ) {
      ref.element = msgWrpr;
    }

    this.elements.messagesWrapper.scrollTo(0, this.elements.messagesWrapper.getScrollSize().y);
  },

  onListRooms : function(data) {
    if( $type(data.rooms) ) {
      data = data.rooms;
    }
    
    // Clear
    this.elements.roomsList.empty();

    // Rebuild
    var self = this;
    $H(data).each(function(room) {
      new Element('li', {
        'html' : en4.core.language.translate(room.title) + ' (' + en4.core.language.translate(['%1$s person', '%1$s people', room.people], room.people) + ')',
        //'html' : '<div>' + room.title + '</div><div>' + room.people +' people</div>',
        'events' : {
          'click' : function(event) {
            self.handler.startChat({identity:room.identity});
          }
        }
      }).inject(this.elements.roomsList);
    }.bind(this));
  },

  onReconfigure : function(data) {
    if( $type(data.chat_enabled) && parseInt(data.chat_enabled) == 0 ) {
      var container = this.container;
      this.destroy();
      (new Element('div', {
        'html' : en4.core.language.translate('The chat room has been disabled by the site admin.')
      }).inject(this.container || $('global_content') || document.body));
    }
  }

});




var ChatHandler_Whispers = new Class({

  Implements : [Events, Options],

  options : {
    identity : false,
    rateMessages : 10,
    rateTimeout : 10000,
    maxLength : 1023
  },

  handler : false,

  elements : {},

  itemOrder : [],

  rate : [],

  initialize : function(handler, options) {
    this.handler = handler;
    this.items = new Hash();
    this.users = new Hash();
    this.elements = new Hash();
    this.setOptions(options);
    
    this.render();
    this.attach();
  },

  destroy : function() {
    this.detach();
    this.elements.container.destroy();
    this.handler.imstate = 0;
  },

  attach : function() {
    this.handler.addEvent('onPingBefore', this.onPingBefore.bind(this));
    this.handler.addEvent('onEvent_presence', this.onPresence.bind(this));
    this.handler.addEvent('onEvent_chat', this.onChat.bind(this));
    window.addEvent('keypress', this.onCommand.bind(this));
  },

  detach : function() {
    this.handler.removeEvent('onPingBefore', this.onPingBefore.bind(this));
    this.handler.removeEvent('onEvent_presence', this.onPresence.bind(this));
    this.handler.removeEvent('onEvent_chat', this.onChat.bind(this));
    window.removeEvent('keypress', this.onCommand.bind(this));
  },
  
  // Informational

  getSelf : function() {
    var self;
    this.users.each(function(data) {
      if( data.self ) {
        self = data;
      }
    });
    if( !$type(self) ) {
      self = {
        'identity' : 0,
        'title' : en4.core.language.translate('You')
      };
    }
    return self;
  },

  // Rendering stuff

  render : function() {
    this.container = $(this.options.container) || $(document.body);

    if( $('im_container') ) {
      $('im_container').destroy();
    }

    // General
    this.elements.container = new Element('ul', {
      'id' : 'im_container'
    }).inject(this.container);

    // Settings
    this.items.settings = new ChatHandler_Whispers_UI_Settings(this, this.elements.container, {
      'name' : 'settings',
      'uid' : 'settings',
      'title' : en4.core.language.translate('Settings'),
      'showClose' : false
    });

    // Friends
    this.items.friends = new ChatHandler_Whispers_UI_Friends(this, this.elements.container, {
      'name' : 'friends',
      'uid' : 'friends',
      'title' : en4.core.language.translate('Friends Online'),
      'showClose' : false
    });
    this.itemOrder.push('friends');
  },


  open : function(identity, focus) {
    var uid = 'convo' + identity;
    var user = this.users.get(identity);

    if( !$type(this.items[uid]) ) {
      var name = 'convo';
      this.items[uid] = new ChatHandler_Whispers_UI_Conversation(this, this.elements.container, {
        'name' : name,
        'uid' : uid,
        'title' : user.title,
        'identity' : identity
      });
      this.itemOrder.push(uid);
    }

    var item = this.items[uid];
    item.state(user.state);
    if( focus ) {
      item.focus();
    }

    // Handle wrapping
    this.resize();
  },

  resize : function() {
    if( this.elements.container.getCoordinates().left < 250 ) {
      this.elements.container.addClass('im_container_crunched');
    } else {
      //this.elements.container.removeClass('im_container_crunched');
    }
  },


  // Event handlers

  onPingBefore : function(data) {
    data.im = ( this.handler.imstate == 1);
  },
  
  onPresence : function(data) {
    this.handler._log({type:'im.presence', data : data});
    var user_id = data.identity;
    var state = parseInt(data.state);

    if( parseInt(data.state) > 0 || !this.users.has(user_id) ) {
      this.users.set(user_id, data);
    }

    // Notify any open convos
    var uid = 'convo' + user_id;
    if( $type(this.items[uid]) ) {
      this.items[uid].onPresence(data);
    }
  },

  onChat : function(data) {
    this.handler._log({'type' : 'im.chat', 'data' : data});

    var uid = 'convo' + data.user_id;
    var name = 'convo';
    var user = this.users.get(data.sender_id);

    if( !$type(this.items[uid]) ) {
      // Only focus if not stale
      this.open(data.user_id);
    }
    
    var item = this.items[uid];

    item.onChat(data, user);
  },

  onCommand : function(event) {
    if( event.control && event.alt && event.key == 'right' ) {
      this.seekItem(-1);
    }

    if( event.control && event.alt && event.key == 'left' ) {
      this.seekItem(1);
    }

  },

  seekItem : function(count) {
    // Get current index
    var activeIndex = 0;
    this.itemOrder.each(function(uid, index) {
      var item = this.items.get(uid);
      if( item.isVisible() ) {
        activeIndex = index;
      }
    }.bind(this));
    activeIndex += count;
    if( activeIndex >= this.itemOrder.length ) activeIndex -= this.itemOrder.length
    if( activeIndex < 0 ) activeIndex += this.itemOrder.length

    var item = this.items.get(this.itemOrder[activeIndex]);
    item.focus();
  }

});







// GUI Classes

var ChatHandler_Whispers_UI_Abstract = new Class({

  Implements : [Events, Options],

  options : {
    name : 'generic',
    uid : false,
    title : 'Untitled',
    showClose : true,
    showHide : true,
    hiddenByDefault : true
  },

  handler : false,

  container : false,

  elements : {},
  
  initialize : function(handler, container, options) {
    this.handler = handler;
    this.container = container;
    this.setOptions(options);

    this.render();

    this.attach();
    this.reposition();
  },

  destroy : function() {
    this.handler.itemOrder.erase(this.options.uid);
    this.detach();
    this.elements.main.destroy();
  },

  attach: function() {
    this.handler.addEvent('onItemShow', this.onOtherItemShow.bind(this));
  },

  detach : function () {
    this.handler.removeEvent('onItemShow', this.onOtherItemShow.bind(this));
  },

  render : function() {

    var name = this.options.name;
    var uid = this.options.uid;

    // Main
    this.elements.main = new Element('li', {
      'class' : 'im_main im_main_' + name + ' im_main_inactive'
    }).inject(this.container);

    if( uid ) this.elements.main.set('id', 'im_main_' + uid);


    // Menu
    this.elements.menu = new Element('div', {
      'class' : 'im_menu_wrapper im_menu_'  + name + '_wrapper',
      'styles' : {
        // Can't position correctly if hidden
        //'display' : 'none'
      }
    }).inject(this.elements.main);

    if( uid ) this.elements.menu.set('id', 'im_menu_' + uid + '_wrapper');

    // Menu head
    this.elements.menuHead = new Element('div', {
      'class' : 'im_menu_head im_menu_'  + name + '_head'
    }).inject(this.elements.menu);

    if( uid ) this.elements.menuHead.set('id', 'im_menu_' + uid + '_head');

    // Menu title
    this.elements.menuTitle = new Element('div', {
      'class' : 'im_menu_title im_menu_'  + name + '_title',
      'html' : this.options.title
    }).inject(this.elements.menuHead);

    if( uid ) this.elements.menuTitle.set('id', 'im_menu_' + uid + '_title');

    // Menu hide
    if( this.options.showHide ) {
      this.elements.menuHide = new Element('div', {
        'class' : 'im_menu_hide im_menu_'  + name + '_hide'
      }).inject(this.elements.menuHead);

      if( uid ) this.elements.menuHide.set('id', 'im_item_' + uid + '_hide');

      this.elements.menuHideLink = new Element('a', {
        'href' : 'javascript:void(0);',
        'class' : 'im_menu_hidelink im_menu_'  + name + '_hidelink',
        /* @todo change to bgimage */
        'html' : '<img src="application/modules/Chat/externals/images/window_hide.png" />',
        'events' : {
          'click' : this.hide.bind(this)
        }
      }).inject(this.elements.menuHide);

      if( uid ) this.elements.menuHideLink.set('id', 'im_menu_' + uid + '_hidelink');
    }
    this.elements.menuHide = new Element('div', {

    });

    // Body
    this.elements.menuBody = new Element('ul', {
      'class' : 'im_menu_body im_menu_'  + name + '_body'
    }).inject(this.elements.menu);

    if( uid ) this.elements.menuBody.set('id', 'im_menu_' + uid + '_body');


    // Item
    this.elements.item = new Element('div', {
      'class' : 'im_item im_item_'  + name,
      'events' : {
        'click' : this.toggle.bind(this)
      }
    }).inject(this.elements.main);

    if( uid ) this.elements.item.set('id', 'im_item_' + uid);

    // Item wrapper
    this.elements.itemTitle = new Element('span', {
      'class' : 'im_item_title im_item_'  + name + '_title',
      'html' : this.options.title
    }).inject(this.elements.item);

    if( uid ) this.elements.itemTitle.set('id', 'im_item_' + uid + '_title');

    // Item close
    if( this.options.showClose ) {
      this.elements.itemClose = new Element('span', {
        'class' : 'im_item_close im_item_'  + name + '_close'
      }).inject(this.elements.item);

      if( uid ) this.elements.itemClose.set('id', 'im_item_' + uid + '_close');

      this.elements.itemCloseLink = new Element('a', {
        'href' : 'javascript:void(0);',
        'class' : 'im_item_closelink im_item_'  + name + '_closelink',
        /* @todo change to bgimage */
        'html' : '<img src="application/modules/Chat/externals/images/window_close.png" />',
        'events' : {
          'click' : this.close.bind(this)
        }
      }).inject(this.elements.itemClose);

      if( uid ) this.elements.itemCloseLink.set('id', 'im_item_' + uid + '_closelink');
    }

    if( Cookie.read('en4_chat_whispers_active', {path:en4.core.basePath}) == this.options.uid ) {
      this.show();
    } else if( this.options.hiddenByDefault ) {
      this.hide();
    }

    this.handler.resize();
  },

  reposition : function() {
    this.elements.menu.setStyle('margin-top', 0 - this.elements.menu.getSize().y);
  },

  getBody : function() {
    return this.elements.menuBody;
  },


  // Actions

  isVisible : function() {
    return !this.elements.main.hasClass('im_main_inactive');
    //return this.elements.menu.getStyle('display') != 'none';
  },

  show : function(e) {
    if( $type(e) ) {e.stop();}
    if( !this.isVisible() ) {
      this.handler.fireEvent('onItemShow', this);
      Cookie.write('en4_chat_whispers_active', this.options.uid, {path:en4.core.basePath});
      this.elements.main.addClass('im_main_active').removeClass('im_main_inactive');
      this.reposition();
    }
  },

  focus : function(e) {
    if( $type(e) ) {e.stop();}
    this.show();
  },

  hide : function(e) {
    if( $type(e) ) {e.stop();}
    if( this.isVisible() ) {
      if( Cookie.read('en4_chat_whispers_active', {path:en4.core.basePath}) == this.options.uid ) {
        Cookie.dispose('en4_chat_whispers_active', {path:en4.core.basePath});
      }
      this.elements.main.addClass('im_main_inactive').removeClass('im_main_active');
      //this.elements.menu.setStyle('display', 'none');
      this.handler.fireEvent('onItemHide', this);
    }
  },

  toggle : function(e) {
    if( $type(e) ) {e.stop();}
    if( !this.isVisible() ) {
      this.show();
    } else {
      this.hide();
    }
    this.handler.resize();
  },

  close : function(e) {
    if( $type(e) ) {e.stop();}
    if( this.isVisible() ) {
      if( Cookie.read('en4_chat_whispers_active', {path:en4.core.basePath}) == this.options.uid ) {
        Cookie.dispose('en4_chat_whispers_active', {path:en4.core.basePath});
      }
    }
    this.destroy();
    this.handler.resize();
    delete this.handler.items[this.options.uid];
  },



  // Events

  onOtherItemShow : function(item) {
    if( item.options.uid != this.options.uid ) {
      this.hide();
    }
  }
  
});



var ChatHandler_Whispers_UI_Settings = new Class({

  Extends : ChatHandler_Whispers_UI_Abstract,
  
  render : function() {
    var name = this.options.name;
    var uid = this.options.uid;

    // Main
    this.elements.main = new Element('li', {
      'class' : 'im_main im_main_' + name + ' im_main_settings_online'
    }).inject(this.container);

    if( uid ) this.elements.main.set('id', 'im_main_' + uid);

    // Tooltip
    this.elements.tooltip = new Element('span', {
      'class' : 'im_item_tooltip_settings',
      'html' : en4.core.language.translate('Go Offline')
    }).inject(this.elements.main);
    
    // Item
    this.elements.item = new Element('span', {
      'class' : 'im_item im_item_'  + name,
      'events' : {
        'click' : this.toggleOnline.bind(this)
      }
    }).inject(this.elements.main);

    if( uid ) this.elements.item.set('id', 'im_item_' + uid);

    // Item wrapper
    this.elements.itemTitle = new Element('span', {
      'class' : 'im_item_title im_item_'  + name + '_title',
      'html' : '&nbsp;'
    }).inject(this.elements.item);

    if( uid ) this.elements.itemTitle.set('id', 'im_item_' + uid + '_title');
  },

  toggleOnline : function(state) {
    if( typeof(state) == 'object' ) state = null; // For events
    state = state || ( 1 - this.handler.handler.imstate );
    if( state == 0 ) {
      this.elements.main.addClass('im_main_settings_offline').removeClass('im_main_settings_online');
      this.handler.handler.imstate = 0;
      this.handler.handler.status(0, 'im');
      this.elements.tooltip.set('text', en4.core.language.translate('Open Chat'));

      // Show hide the rest of the stuff
      this.handler.items.each(function(item) {
        if( !item.elements.main.hasClass('im_main_' + this.options.name) ) {
          item.elements.main.setStyle('display', 'none');
        }
      }.bind(this));
    } else {
      this.elements.main.addClass('im_main_settings_online').removeClass('im_main_settings_offline');
      this.handler.handler.imstate = 1;
      this.handler.handler.status(1, 'im');
      this.elements.tooltip.set('text', en4.core.language.translate('Go Offline'));

      // Show hide the rest of the stuff
      this.handler.items.each(function(item) {
        if( !item.elements.main.hasClass('im_main_' + this.options.name) ) {
          item.elements.main.setStyle('display', '');
          item.reposition();
        }
      }.bind(this));
    }
    Cookie.write('en4_chat_imstate', this.handler.handler.imstate, {path:en4.core.basePath});
  },

  reposition : $empty,
  show : $empty,
  hide : $empty,
  toggle : $empty,
  close : $empty,
  isVisible : $empty
});



var ChatHandler_Whispers_UI_Friends = new Class({
  
  Extends : ChatHandler_Whispers_UI_Abstract,

  attach : function() {
    this.parent();

    this.handler.handler.addEvent('onEvent_presence', this.onPresence.bind(this));
  },

  detach : function() {
    this.parent();
    
    this.handler.handler.removeEvent('onEvent_presence', this.onPresence.bind(this));
  },

  render : function() {
    this.parent();

    // Show friend counts
    this.elements.menuCount = new Element('span', {
      'html' : '(0)'
    }).inject(this.elements.menuTitle);
    
    this.elements.itemCount = new Element('span', {
      'html' : '(0)'
    }).inject(this.elements.itemTitle);

    // Show no friends online notice
    this.elements.menuBody.setStyle('display', 'none');
    new Element('div', {
      'class' : 'im_menu_' + this.options.name + '_none',
      'html' : this.handler.options.memberIm ? en4.core.language.translate('No members are online.') : en4.core.language.translate('None of your friends are online.')
    }).inject(this.elements.menu);
  },


  // Events

  onPresence : function(data) {
    var user_id = data.identity;
    var bodyEl = this.getBody();
    var userElId = 'im_user_' + user_id;
    var userEl = $(userElId);

    if( data.self == 1 ) {
      return;
    }

    if( parseInt(data.state) >= 1 ) {

      if( !$type(userEl) ) {
        userEl = new Element('li', {
          'id' : userElId,
          'events' : {
            'click' : function() {
              this.handler.open(user_id, true);
            }.bind(this)
          },
          'html' : '\n\
<span class="im_menu_friends_photo">\n\
  <img src="' + (data.photo || 'application/modules/User/externals/images/nophoto_user_thumb_icon.png') + '" alt="" />\n\
</span>\n\
<span class="im_menu_friends_name">\n\
  ' + data.title + '\n\
</span>\n\
'
        }).inject(bodyEl);
      }

      // Update online state
      var nameEl = userEl.getElement('.im_menu_friends_name');
      ChatHandler_Utility.applyStateClass(nameEl, parseInt(data.state));

    } else {
      if( userEl ) {
        userEl.destroy();
      }
    }



    // Update total
    this.elements.menuCount.set('html', '(' + bodyEl.getChildren().length + ')')
    this.elements.itemCount.set('html', '(' + bodyEl.getChildren().length + ')')

    // Show/hide no friends notice
    var childrenLength = bodyEl.getChildren().length;
    var noFriendsEl = this.elements.menu.getElement('.im_menu_' + this.options.name + '_none');
    if( childrenLength < 1 && !noFriendsEl ) {
      this.elements.menuBody.setStyle('display', 'none');
      new Element('div', {
        'class' : 'im_menu_' + this.options.name + '_none',
        'html' : this.handler.options.memberIm ? en4.core.language.translate('No members are online.') : en4.core.language.translate('None of your friends are online.')
      }).inject(this.elements.menu);
    } else if( childrenLength >= 1 && noFriendsEl ) {
      this.elements.menuBody.setStyle('display', '');
      noFriendsEl.destroy();
    }


    this.reposition();
  }

});





var ChatHandler_Whispers_UI_Conversation = new Class({

  Extends : ChatHandler_Whispers_UI_Abstract,

  flasher : false,

  unread : 0,

  initialize: function(handler, container, options) {
    this.parent(handler, container, options);
    this.unread = Cookie.read('en4_whispers_unread_'+this.options.uid, {path:en4.core.basePath}) || 0;
    this.checkFlasher();
  },

  attach : function() {
    this.parent();
  },

  detach : function() {
    this.parent();
    if( this.flasher ) $clear(this.flasher);
  },



  // Rendering
  
  render : function() {
    this.parent();

    // Footer
    this.elements.menuFooter = new Element('div', {
      'class' : 'im_menu_footer im_menu_'  + this.options.name + '_footer'
    }).inject(this.elements.menu);

    if( this.options.uid ) this.elements.menuFooter.set('id', 'im_menu_' + this.options.uid + '_footer');

    // Input
    if( this.handler.handler._supportsContentEditable() ) {

      this.elements.menuInput = new Element('div', {
        'class' : 'im_menu_input im_menu_'  + this.options.name + '_input',
        'html' : '<p><br /></p>',
        'contentEditable' : true,
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
      }).inject(this.elements.menuFooter);
      //this.elements.menuInput.focus();
    } else {
      this.elements.menuInput = new Element('input', {
        'class' : 'im_menu_input im_menu_'  + this.options.name + '_input'
      }).inject(this.elements.menuFooter);

      if( this.options.uid ) this.elements.menuInput.set('id', 'im_menu_' + this.options.uid + '_input');
    }

    this.elements.menuInput.addEvents({
      'keypress' : function(event) {
        if( event.key == 'enter' ) {
          event.preventDefault();
          this.send();
        }
      }.bind(this)
    });
  },



  // Actions

  focus : function() {
    this.parent();
    this.elements.menuInput.focus();
    this.elements.menuBody.scrollTo(0, this.elements.menuBody.getScrollSize().y);
  },

  show : function(e) {
    this.parent(e);

    // Reset unread count
    this.unread = 0;
    Cookie.dispose('en4_whispers_unread_'+this.options.uid, {path:en4.core.basePath});

    if( this.flasher ) {
      $clear(this.flasher);
      this.flasher = false;
      this.unread = 0;
      Cookie.write('en4_whispers_unread_'+this.options.uid, this.unread, {path:en4.core.basePath});
      this.elements.main.removeClass('im_main_unread');
    }

    if( $type(this.elements.menuInput) ) {
      this.elements.menuInput.focus();
    }
    if( $type(this.elements.menuBody) ) {
      this.elements.menuBody.scrollTo(0, this.elements.menuBody.getScrollSize().y);
    }
  },

  send : function() {

    var message;
    var data = this.handler.getSelf();

    // Get message
    if( this.handler.handler._supportsContentEditable() ) {
      message = this.elements.menuInput.get('html');

      // Webkit you're killing me!
      if( Browser.Engine.webkit ) {
        this.elements.menuInput.destroy();
        delete this.elements.menuInput;

        this.elements.menuInput = new Element('div', {
          'class' : 'im_menu_input im_menu_'  + this.options.name + '_input',
          'html' : '<p><br /></p>',
          'contentEditable' : true
        }).inject(this.elements.menuFooter);

        this.elements.menuInput.addEvents({
          'keypress' : function(event) {
            if( event.key == 'enter' ) {
              event.preventDefault();
              this.send();
            }
          }.bind(this)
        });
      // Everything else works great
      } else {
        this.elements.menuInput.empty();
        this.elements.menuInput.set('html', '<p><br /></p>');
      }
      this.elements.menuInput.focus();
     
      message = message.stripTags();
    } else {
      message = this.elements.menuInput.get('value');
      this.elements.menuInput.set('value', '');
    }

    message = message.trim();
    if( message == '' ) {
      return;
    }
    
    if( message.length > this.handler.options.maxLength ) message = message.substring(0, this.handler.options.maxLength);

    // Check rate
    this.handler.rate = this.handler.rate.filter(function(item) {
      return $time() < item + this.handler.options.rateTimeout;
    }.bind(this));

    if( this.handler.rate.length >= this.handler.options.rateMessages ) {
      this.onChat({
        'system' : true,
        'body' : en4.core.language.translate('You are sending messages too quickly - please wait a few seconds and try again.')
      });
      return;
    }

    this.handler.rate.push($time());

    // Send
    var ref = {};
    this.onChat({
      'body' : message
    }, data, ref);

    this.handler.handler.whisper(this.options.identity, message, function(responseJSON) {
      if( $type(ref.element) ) {
        ref.element.set('id', 'chat_whisper_'+responseJSON.whisper_id);
      }
    });
  },

  state : function(state) {
    ChatHandler_Utility.applyStateClass(this.elements.itemTitle, parseInt(state));
  },

  close : function(e, force) {
    if( $type(e) ) {e.stop();}
    if( force ) {
      this.parent();
    } else {
      this.handler.handler.whisperClose(this.options.identity).addEvent('complete', function() {
        this.close(null, true);
      }.bind(this));
    }
  },



  // Events
  
  onChat : function(data, user, ref) {
    this.handler.handler._log({'type' : 'ui.conov.chat', 'data' : data, 'user' : user});

    // Ignore if mesage already exists
    if( $type(data.whisper_id) && $('chat_whisper_'+data.whisper_id) ) {
      return;
    }

    var messageEl;

    // Process body
    var body = data.body;
    body = ChatHandler_Utility.replaceSmilies(body);
    body = body.replaceLinks();
    if( body.length > this.handler.options.maxLength ) body = body.substring(0, this.handler.options.maxLength);

    // System message
    if( $type(data.system) && data.system ) {
      messageEl = (new Element('li')).inject(this.elements.menuBody);
      (new Element('span', {'class' : 'im_convo_messages_body', 'html' : body}).inject(messageEl));
    }

    // Normal
    else
    {
      // If not visible, increment unread
      if( !this.isVisible() && !data.stale ) {
        this.unread++;
        Cookie.write('en4_whispers_unread_'+this.options.uid, this.unread, {path:en4.core.basePath});
        this.checkFlasher();
      }

      messageEl = (new Element('li')).inject(this.elements.menuBody);
      if( $type(data.whisper_id) ) {
        messageEl.set('id', 'chat_whisper_'+data.whisper_id);
      }
      (new Element('span', {'class' : 'im_convo_messages_author', 'html' : user.title}).inject(messageEl));
      (new Element('span', {'class' : 'im_convo_messages_body', 'html' : body}).inject(messageEl));
    }

    if( $type(messageEl) && $type(ref) == 'object' ) {
      ref.element = messageEl;
    }
    
    if( this.isVisible() ) {
      this.elements.menuBody.scrollTo(0, this.elements.menuBody.getScrollSize().y);
    }
  },

  onPresence : function(data) {
    this.state(data.state);
  },

  checkFlasher : function() {
    if( !this.flasher && this.unread > 0 ) {
      this.flasher = (function() {
        if( this.elements.main.hasClass('im_main_unread') ) {
          this.elements.main.removeClass('im_main_unread');
        } else {
          this.elements.main.addClass('im_main_unread');
        }
      }).periodical(500, this);
    }
  }

});


var ChatHandler_Utility = {

  // States
  
  states : $H({
    0 : 'offline',
    1 : 'online',
    2 : 'idle',
    3 : 'away'
  }),

  classPrefix : 'im_state_',

  getStateClass : function(state) {
    return this.classPrefix + this.states[state];
  },

  applyStateClass : function(element, state) {
    // Remove old states
    this.states.each(function(stateName) {
      element.removeClass(this.classPrefix + stateName);
    }.bind(this));

    // Add new state
    element.addClass(this.getStateClass(state));
  },

  // Smilies

  // Symbols from http://www.astro.umd.edu/~marshall/smileys.html
  imageSpec : 'application/modules/Chat/externals/images/smilies/:name:.png',
  
  smilies : $H({
    ':?' : 'confused',
    ':-?' : 'confused',
    
    'B)' : 'cool',
    '8)' : 'cool',
    'B-)' : 'cool',
    '8-)' : 'cool',
    
    ':\'(' : 'cry',
    '=\'(' : 'cry',
    ':"(' : 'cry',
    ':_(' : 'cry',

    ':#' : 'embarrassed',
    ':-#' : 'embarrassed',
    ':S' : 'embarrassed', // "Uncertainty"
    ':-S' : 'embarrassed', // "Uncertainty"
    ':$' : 'embarrassed', // "Uncertainty"
    ':-$' : 'embarrassed', // "Uncertainty"
    '^^;' : 'embarrassed',
    '^_^;' : 'embarrassed',
    '-_-;' : 'embarrassed',

    ':(' : 'frown',
    ':-(' : 'frown',
    '=(' : 'frown',
    '=-(' : 'frown',

    ':D' : 'grin',
    ':-D' : 'grin',
    '=D' : 'grin',

    'lol' : 'lol',
    '^o^' : 'lol',

    ':&' : 'mad',
    ':-&' : 'mad',
    '>(' : 'mad',
    '>:(' : 'mad',
    '>=(' : 'mad',
    ':&amp;' : 'mad',
    ':-&amp;' : 'mad',

    '*:|' : 'nervous', // very incorrect

    ':|' : 'neutral',
    '-_-' : 'neutral',
    '-.-' : 'neutral',

    ':)' : 'smile',
    ':-)' : 'smile',
    ':>' : 'smile',
    '=)' : 'smile',
    //'^o^' : 'smile', (lol?)
    '^_^' : 'smile',
    '^.^' : 'smile',

     // NYI ':-o' : 'surprise', other O_o o_o O_O O.O O.o o.O

    ':P' : 'tongue',
    ':-P' : 'tongue',

    ';)' : 'wink',
    ';-)' : 'wink',
    ';>' : 'wink'
    
  }),

  replaceSmilies : function(text) {
    this.smilies.each(function(name, val) {
      if( text.indexOf(val) < 0 ) return;
      var parts = text.split(val);
      var image = '<img src="' + this.imageSpec.replace(':name:', name).escapeQuotes() + '" alt="' + val.escapeQuotes() + '" />';
      text = parts.join(image);
    }.bind(this));
    return text;
  }
};
