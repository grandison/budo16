
/* $Id: core.js 8375 2011-02-02 02:09:25Z john $ */


en4.activity = {

  load : function(next_id, subject_guid){
    if( en4.core.request.isRequestActive() ) return;

    $('feed_viewmore').style.display = 'none';
    $('feed_loading').style.display = '';

    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'activity/widget/feed',
      data : {
        //format : 'json',
        'maxid' : next_id,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : subject_guid
      }
      /*
      onSuccess : function(){
        $('feed_viewmore').style.display = '';
        $('feed_loading').style.display = 'none';
      }*/
    }), {
      'element' : $('activity-feed'),
      'updateHtmlMode' : 'append'
    });
  },

  like : function(action_id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/like',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  unlike : function(action_id, comment_id) {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/unlike',
      data : {
        format : 'json',
        action_id : action_id,
        comment_id : comment_id,
        subject : en4.core.subject.guid
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  comment : function(action_id, body) {
    if( body.trim() == '' )
    {
      return;
    }
    
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/comment',
      data : {
        format : 'json',
        action_id : action_id,
        body : body,
        subject : en4.core.subject.guid
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  attachComment : function(formElement){
    var bind = this;
    formElement.addEvent('submit', function(event){
      event.stop();
      bind.comment(formElement.action_id.value, formElement.body.value);
    });
  },

  viewComments : function(action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/viewComment',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  viewLikes : function(action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/index/viewLike',
      data : {
        format : 'json',
        action_id : action_id,
        nolist : true
      }
    }), {
      'element' : $('activity-item-'+action_id),
      'updateHtmlMode': 'comments'
    });
  },

  hideNotifications : function(reset_text) {
    en4.core.request.send(new Request.JSON({
      'url' : en4.core.baseUrl + 'activity/notifications/hide'
    }));
    $('updates_toggle').set('html', reset_text).removeClass('new_updates');

    /*
    var notify_link = $('core_menu_mini_menu_updates_count').clone();
    $('new_notification').destroy();
    notify_link.setAttribute('id', 'core_menu_mini_menu_updates_count');
    notify_link.innerHTML = "0 updates";
    notify_link.inject($('core_menu_mini_menu_updates'));
    $('core_menu_mini_menu_updates').setAttribute('id', '');
    */
    if($('notifications_main')){
      var notification_children = $('notifications_main').getChildren('li');
      notification_children.each(function(el){
          el.setAttribute('class', '');
      });
    }

    if($('notifications_menu')){
      var notification_children = $('notifications_menu').getChildren('li');
      notification_children.each(function(el){
          el.setAttribute('class', '');
      });
    }
    //$('core_menu_mini_menu_updates').setStyle('display', 'none');
  },

  updateNotifications : function() {
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/update',
      data : {
        format : 'json'
      },
      onSuccess : this.showNotifications.bind(this)
    }));
  },

  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      $('updates_toggle').set('html', responseJSON.text).addClass('new_updates');
    }
  },

  markRead : function (action_id){
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/test',
      data : {
        format     : 'json',
        'actionid' : action_id
      }
    }));
  },

  cometNotify : function(responseObject){
    //for( var x in responseObject ) alert(responseObject[x]);
    //if( $type(responseObject.text) ){
      $('core_menu_mini_menu_updates').style.display = '';
      $('core_menu_mini_menu_updates_count').innerHTML = responseObject.text;
    //}
  }

};

var NotificationUpdateHandler = new Class({

  Implements : [Events, Options],
  options : {
      debug : false,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      subject_guid : null
    },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,

  initialize : function(options) {
    this.setOptions(options);
  },

  start : function() {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this.activestate = 1;
        this.state= true;
      }.bind(this),
      'onStateIdle' : function() {
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });

    this.loop();
  },

  stop : function() {
    this.state = false;
  },

  updateNotifications : function() {
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'activity/notifications/update',
      data : {
        format : 'json'
      },
      onSuccess : this.showNotifications.bind(this)
    }));
  },

  showNotifications : function(responseJSON){
    if (responseJSON.notificationCount>0){
      $('updates_toggle').set('html', responseJSON.text).addClass('new_updates');
    }
  },
  
  loop : function() {
    if( !this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.updateNotifications().addEvent('complete', function() {
        this.loop.delay(this.options.delay, this);
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
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
  }
});

//(function(){

  en4.activity.compose = {

    composers : {},

    register : function(object){
      name = object.getName();
      this.composers[name] = object;
    },

    deactivate : function(){
      for( var x in this.composers ){
        this.composers[x].deactivate();
      }
      return this;
    }

  };


  en4.activity.compose.icompose = new Class({

    Implements: [Events, Options],

    name : false,

    element : false,

    options : {},

    initialize : function(element, options){
      this.element = $(element);
      this.setOptions(options);
    },

    getName : function(){
      return this.name;
    },

    activate : function(){
      en4.activity.compose.deactivate();
    },

    deactivate : function(){

    }
  });

//})();

var ActivityUpdateHandler = new Class({

  Implements : [Events, Options],
  options : {
      debug : false,
      baseUrl : '/',
      identity : false,
      delay : 5000,
      admin : false,
      idleTimeout : 600000,
      last_id : 0,
      subject_guid : null
    },

  state : true,

  activestate : 1,

  fresh : true,

  lastEventTime : false,

  title: document.title,
  
  initialize : function(options) {
    this.setOptions(options);
  },

  start : function() {
    this.state = true;

    // Do idle checking
    this.idleWatcher = new IdleWatcher(this, {timeout : this.options.idleTimeout});
    this.idleWatcher.register();
    this.addEvents({
      'onStateActive' : function() {
        this.activestate = 1;
        this.state= true;
      }.bind(this),
      'onStateIdle' : function() {
        this.activestate = 0;
        this.state = false;
      }.bind(this)
    });
    this.loop();
  },

  stop : function() {
    this.state = false;
  },

  checkFeedUpdate : function(action_id, subject_guid){
    if( en4.core.request.isRequestActive() ) return;
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/name/activity.feed',
      data : {
        'format' : 'html',
        'minid' : this.options.last_id+1,
        'feedOnly' : true,
        'nolayout' : true,
        'subject' : this.options.subject_guid,
        'checkUpdate' : true
      }
    }), {
      'element' : $('feed-update')
    });
  },

  getFeedUpdate : function(last_id){
    if( en4.core.request.isRequestActive() ) return;
    var min_id = this.options.last_id + 1;
    this.options.last_id = last_id;
    document.title = this.title;
    en4.core.request.send(new Request.HTML({
      url : en4.core.baseUrl + 'widget/index/name/activity.feed',
      data : {
        'format' : 'html',
        'minid' : min_id,
        'feedOnly' : true,
        'nolayout' : true,
        'getUpdate' : true,
        'subject' : this.options.subject_guid
      }
    }), {
      'element' : $('activity-feed'),
      'updateHtmlMode' : 'prepend'
    });
  },

  loop : function() {
    if( !this.state) {
      this.loop.delay(this.options.delay, this);
      return;
    }

    try {
      this.checkFeedUpdate().addEvent('complete', function() {
        this.loop.delay(1250, this);
      }.bind(this));
    } catch( e ) {
      this.loop.delay(this.options.delay, this);
      this._log(e);
    }
  },
  // Utility

  _log : function(object) {
    if( !this.options.debug ) {
      return;
    }

    // Firefox is dumb and causes problems sometimes with console
    try {
      //if( typeof(console) && $type(console) ) {
        //console.log(object);
      //}
    } catch( e ) {
      // Silence
    }
  }
})