
/* $Id: core.js 7244 2010-09-01 01:49:53Z john $ */


en4.user = {

  viewer : {
    type : false,
    id : false
  },

  attachEmailTaken : function(element, callback)
  {
    var bind = this;
    element.addEvent('blur', function(){
      bind.checkEmailTaken(element.value, callback);
    });

    /*
    var lastElementValue = element.value;
    (function(){
      if( element.value != lastElementValue )
      {

        lastElementValue = element.value;
      }
    }).periodical(500, this);
    */
  },

  attachUsernameTaken : function(element, callback)
  {
    var bind = this;
    element.addEvent('blur', function(){
      bind.checkUsernameTaken(element.value, callback);
    });
    
    /*
    var lastElementValue = element.value;
    (function(){
      if( element.value != lastElementValue )
      {
        bind.checkUsernameTaken(element.value, callback);
        lastElementValue = element.value;
      }
    }).periodical(500, this);
    */
  },

  checkEmailTaken : function(email, callback)
  {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'user/signup/taken',
      data : {
        format : 'json',
        email : email
      },
      onSuccess : function(responseObject)
      {
        if( $type(responseObject.taken) ){
          callback(responseObject.taken);
        }
      }
    }));
    
    return this;
  },

  checkUsernameTaken : function(username)
  {
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'user/signup/taken',
      data : {
        format : 'json',
        username : username
      },
      onSuccess : function(responseObject)
      {
        if( $type(responseObject.taken) ){
          callback(responseObject.taken);
        }
      }
    }));

    return this;
  },

  clearStatus : function() {
    var request = new Request.JSON({
      url : en4.core.baseUrl + 'user/edit/clear-status',
      method : 'post',
      data : {
        format : 'json'
      }
    });
    request.send();
    if( $('user_profile_status_container') ) {
      $('user_profile_status_container').empty();
    }
    return request;
  }
  
};

en4.user.friends = {

  refreshLists : function(){
    
  },
  
  addToList : function(list_id, user_id){
    var request = new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-add',
      data : {
        format : 'json',
        friend_id : user_id,
        list_id : list_id
      }
    });
    request.send();
    return request;

    /*
    $('profile_friends_lists_menu_' + user_id).style.display = 'none';

    var bind = this;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-add',
      data : {
        format : 'json',
        friend_id : user_id,
        list_id : list_id
      }
    }), {
      'element' : $('user_friend_' + user_id)
    });

    return this;
    */
  },

  removeFromList : function(list_id, user_id){
    var request = new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-remove',
      data : {
        format : 'json',
        friend_id : user_id,
        list_id : list_id
      }
    });
    request.send();
    return request;
    /*
    var bind = this;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-remove',
      data : {
        format : 'json',
        friend_id : user_id,
        list_id : list_id
      }
    }), {
      'element' : $('user_friend_' + user_id)
    });

    return this;
    */
  },

  createList : function(title, user_id){
    var request = new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-create',
      data : {
        format : 'json',
        friend_id : user_id,
        title : title
      }
    });
    request.send();
    return request;

    /*
    $('profile_friends_lists_menu_' + user_id).style.display = 'none';
    var bind = this;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-create',
      data : {
        format : 'json',
        friend_id : user_id,
        list_title : title
      }
    }), {
      'element' : $('user_friend_' + user_id)
    });

    return this;
    */
  },

  deleteList : function(list_id){

    var bind = this;
    en4.core.request.send(new Request.JSON({
      url : en4.core.baseUrl + 'user/friends/list-delete',
      data : {
        format : 'json',
        user_id : en4.user.viewer.id,
        list_id : list_id
      }
    }));

    return this;
  },


  showMenu : function(user_id){
    $('profile_friends_lists_menu_' + user_id).style.visibility = 'visible';
    $('friends_lists_menu_input_' + user_id).focus();
    $('friends_lists_menu_input_' + user_id).select();
  },

  hideMenu : function(user_id){
    $('profile_friends_lists_menu_' + user_id).style.visibility = 'hidden';
  },

  clearAddList : function(user_id){
    $('friends_lists_menu_input_' + user_id).value = "";
  }

}