
en4.poll = {

  urls : {
    vote : 'polls/vote/',
    login : 'login'
  },

  data : {},

  addPollData : function(identity, data) {
    if( $type(data) != 'object' ) {
      data = {};
    }
    data = $H(data);
    this.data[identity] = data;
    return this;
  },

  getPollDatum : function(identity, key, defaultValue) {
    if( !defaultValue ) {
      defaultValue = false;
    }
    if( !(identity in this.data) ) {
      return defaultValue;
    }
    if( !(key in this.data[identity]) ) {
      return defaultValue;
    }
    return this.data[identity][key];
  },

  toggleResults : function(identity) {
    var pollContainer = $('poll_form_' + identity);
    if( 'none' == pollContainer.getElement('.poll_options div.poll_has_voted').getStyle('display') ) {
      pollContainer.getElements('.poll_options div.poll_has_voted').show();
      pollContainer.getElements('.poll_options div.poll_not_voted').hide();
      pollContainer.getElement('.poll_toggleResultsLink').set('text', en4.core.language.translate('Show Questions'));
    } else {
      pollContainer.getElements('.poll_options div.poll_has_voted').hide();
      pollContainer.getElements('.poll_options div.poll_not_voted').show();
      pollContainer.getElement('.poll_toggleResultsLink').set('text', en4.core.language.translate('Show Results'));
    }
  },

  renderResults : function(identity, answers, votes) {
    if( !answers || 'array' != $type(answers) ) {
      return;
    }
    var pollContainer = $('poll_form_' + identity);
    answers.each(function(option) {
      var div = $('poll-answer-' + option.poll_option_id);
      var pct = votes > 0
              ? Math.floor(100*(option.votes / votes))
              : 1;
      if (pct < 1)
          pct = 1;
      // set width to 70% of actual width to fit text on same line
      div.style.width = (.7*pct)+'%';
      div.getNext('div.poll_answer_total')
         .set('text',  option.votesTranslated + ' (' + en4.core.language.translate('%1$s%%', (option.votes ? pct : '0')) + ')');
      if( !this.getPollDatum(identity, 'canVote') ||
        (!this.getPollDatum(identity, 'canChangeVote') || this.getPollDatum(identity, 'hasVoted')) ) {
        pollContainer.getElement('.poll_radio input').set('disabled', true);
      }
    }.bind(this));
  },

  vote: function(identity, option) {
    if( !en4.user.viewer.id ) {
      window.location.href = this.urls.login + '?return_url=' + encodeURIComponent(window.location.href);
      return;
    }
    //if( en4.core.subject.type != 'poll' ) {
    //  return;
    //}
    if( $type(option) != 'element' ) {
      return;
    }
    option = $(option);

    var pollContainer = $('poll_form_' + identity);
    var value = option.value;

    $('poll_radio_' + option.value).toggleClass('poll_radio_loading');

    var request = new Request.JSON({
      url: this.urls.vote,
      method: 'post',
      data : {
        'format' : 'json',
        'poll_id' : identity,
        'option_id' : value
      },
      onComplete: function(responseJSON) {
        $('poll_radio_' + option.value).toggleClass('poll_radio_loading');
        if( $type(responseJSON) == 'object' && responseJSON.error ) {
          Smoothbox.open(new Element('div', {
            'html' : responseJSON.error
              + '<br /><br /><button onclick="parent.Smoothbox.close()">'
              + en4.core.language.translate('Close')
              + '</button>'
          }));
        } else {
          pollContainer.getElement('.poll_vote_total')
            .set('text', en4.core.language.translate(['%1$s vote', '%1$s votes', responseJSON.votes_total], responseJSON.votes_total));
          this.renderResults(identity, responseJSON.pollOptions, responseJSON.votes_total);
          this.toggleResults(identity);
        }
        if( !this.getPollDatum(identity, 'canChangeVote') ) {
          pollContainer.getElements('.poll_radio input').set('disabled', true);
        }
      }.bind(this)
    });
    
    request.send()
  }

};