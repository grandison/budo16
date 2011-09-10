
/* $Id: core.js 7244 2010-09-01 01:49:53Z john $ */


en4.album = {

  composer : false,

  getComposer : function(){
    if( !this.composer ){
      this.composer = new en4.album.acompose();
    }

    return this.composer;
  }

};


/*
var test1 = new Class({

  Implements: [Events, Options]

});

var test2 = new Class({

  Extends: test1

})
*/


en4.album.acompose = new Class({

  Extends : en4.activity.compose.icompose,

  name : 'photo',

  active : false,

  options : {},

  frame : false,

  photo_id : false,

  initialize : function(element, options){
    if( !element ) element = $('activity-compose-photo');
    this.parent(element, options);
  },
  
  activate : function(){
    this.parent();
    this.element.style.display = '';
    $('activity-compose-photo-input').style.display = '';
    $('activity-compose-photo-loading').style.display = 'none';
    $('activity-compose-photo-preview').style.display = 'none';
    $('activity-form').addEvent('beforesubmit', this.checkSubmit.bind(this));
    this.active = true;

    // @todo this is a hack
    $('activity-post-submit').style.display = 'none';
  },

  deactivate : function(){
    if( !this.active ) return;
    this.active = false
    this.photo_id = false;
    if( this.frame ) this.frame.destroy();
    this.frame = false;
    $('activity-compose-photo-preview').empty();
    $('activity-compose-photo-input').style.display = '';
    this.element.style.display = 'none';
    $('activity-form').removeEvent('submit', this.checkSubmit.bind(this));;

    // @todo this is a hack
    $('activity-post-submit').style.display = 'block';
    $('activity-compose-photo-activate').style.display = '';
    $('activity-compose-link-activate').style.display = '';
  },

  process : function(){
    if( this.photo_id ) return;
    
    if( !this.frame ){
      this.frame = new IFrame({
        src : 'about:blank',
        name : 'albumComposeFrame',
        styles : {
          display : 'none'
        }
      });
      this.frame.inject(this.element);
    }

    $('activity-compose-photo-input').style.display = 'none';
    $('activity-compose-photo-loading').style.display = '';
    $('activity-compose-photo-form').target = 'albumComposeFrame';
    $('activity-compose-photo-form').submit();
  },

  processResponse : function(responseObject){
    if( this.photo_id ) return;
    
    (new Element('img', {
      src : responseObject.src,
      styles : {
        //'max-width' : '100px'
      }
    })).inject($('activity-compose-photo-preview'));
    $('activity-compose-photo-loading').style.display = 'none';
    $('activity-compose-photo-preview').style.display = '';
    this.photo_id = responseObject.photo_id;

    // @todo this is a hack
    $('activity-post-submit').style.display = 'block';
    $('activity-compose-photo-activate').style.display = 'none';
    $('activity-compose-link-activate').style.display = 'none';
  },

  checkSubmit : function(event)
  {
    if( this.active && this.photo_id )
    {
      //event.stop();
      $('activity-form').attachment_type.value = 'album_photo';
      $('activity-form').attachment_id.value = this.photo_id;
    }
  }
  
});