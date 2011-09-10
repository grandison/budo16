
if( !('en4' in window) ) {
  en4 = {};
}
if( !('music' in en4) ) {
  en4.music = {};
}

en4.core.runonce.add(function() {

  // preload pause button element as defined in CSS class '.music_player_button_pause'
  new Element('div', {
    'id': 'pause_preloader',
    'class': 'music_player_button_pause',
    'style': 'position: absolute; top: -9999px; left: -9999px;'
  }).inject(document.body).destroy();
  
  // ADD TO PLAYLIST
  $$('a.music_add_to_playlist').addEvent('click', function(){
    $('song_id').value = this.id.substring(5);
    Smoothbox.open( $('music_add_to_playlist'), {mode: 'Inline'} );
    var pl = $$('#TB_ajaxContent > div')[0];
    pl.show();
  });

  // PLAY ON MY PROFILE
  $$('a.music_set_profile_playlist').addEvent('click', function() {
    var url_part    = this.href.split('/');
    var playlist_id = 0;
    $each(url_part, function(val, i) {
      if (val == 'playlist_id')
        playlist_id = url_part[i+1];
    });
    new Request.JSON({
      method: 'post',
      url: this.href,
      noCache: true,
      data: {
        'playlist_id': playlist_id,
        'format': 'json'
      },
      onSuccess: function(json){
        var link = $$('#music_playlist_item_' + json.playlist_id + ' a.music_set_profile_playlist')[0];
        if (json && json.success) {
          $$('a.music_set_profile_playlist')
            .set('text', en4.core.language.translate('Play on my Profile'))
            .addClass('icon_music_playonprofile')
            .removeClass('icon_music_disableonprofile')
            ;
          if( json.enabled && link ) {
            link
              .set('text', en4.core.language.translate('Disable Profile Playlist'))
              .addClass('icon_music_disableonprofile')
              .removeClass('icon_music_playonprofile')
              ;
          }
        }
      }
    }).send();
    return false;
  });

  // enable players automatically?
  var players = $$('.music_player_wrapper');
  //if( players.length > 0 ) {
    // Initialize sound manager?
    en4.music.player.getSoundManager();
  //}
  players.each(function(el) {
    var matches = el.get('id').match(/music_player_([\w\d]+)/i);
    if( matches && matches.length >= 2 ) {
      en4.music.player.createPlayer(matches[1]);
    }
  });
});

en4.music.player = {

  playlists : [],

  mute : ( Cookie.read('en4_music_mute') == 1 ? true : false ),

  volume : ( Cookie.read('en4_music_volume') ? Cookie.read('en4_music_volume') : 85 ),
  
  getSoundManager : function() {

    if( !('soundManager' in en4.music) && 'soundManager' in window ) {
      en4.music.soundManager = soundManager;
    }

    return en4.music.soundManager;
  },

  getPlaylists : function() {
    return this.playlists;
  },

  getVolume : function() {
    if( this.mute ) {
      return 0;
    } else {
      return this.volume;
    }
  },

  setVolume : function(volume) {
    if( 0 == volume ) {
      this.mute = true;
    } else {
      this.mute = false;
      this.volume = volume;
    }
    this._writeCookies();
    this._updatePlaylists();
  },

  toggleMute : function(flag) {
    if( $type(flag) ) {
      this.mute = ( true == flag );
    } else {
      this.mute = !this.mute;
    }
    this._writeCookies();
    this._updatePlaylists();
  },
  
  createPlayer : function(id) {

    var par = $('music_player_' + id);
    var el  = par.getElement('div.music_player');
    
    en4.music.player.getSoundManager().onready(function() {
      // show the entire player
      if( !par.getElement('div.playlist_short_player') ) {
        if( !el.hasClass('playlist_player_loaded') ) {
          var playlist = new en4.music.playlistAbstract(el);
          en4.music.player.playlists.push(playlist);
          el.addClass('playlist_player_loaded');
        }

      // show the short player first
      } else {
        par.getElement('div.music_player:not(div.playlist_short_player)').hide();
        par.getElement('div.playlist_short_player').addEvent('click', function(){
          var par = $('music_player_' + id);
          var el = par.getElement('div.music_player');
          el.show();
          par.getElement('div.playlist_short_player').hide();

          if( !el.hasClass('playlist_player_loaded') ) {
            var playlist = new en4.music.playlistAbstract(el);
            en4.music.player.playlists.push(playlist);
            playlist.play();
            el.addClass('playlist_player_loaded');
          }
        });
      }
    });

    return this;
  },

  _writeCookies : function() {
    var tmpUri = new URI($$('head base[href]')[0]);
    Cookie.write('en4_music_volume', this.volume, {
      duration: 7, // days
      path: tmpUri.get('directory'),
      domain: tmpUri.get('domain')
    });
    Cookie.write('en4_music_mute', ( this.mute ? 1 : 0 ), {
      duration: 7, // days
      path: tmpUri.get('directory'),
      domain: tmpUri.get('domain')
    });
  },

  _updatePlaylists : function() {
    this.playlists.each(function(playlist) {
      playlist._updateScrub();
      playlist._updateVolume();
    });
  }
  
};