/**
 * Autocompleter
 *
 * http://digitarald.de/project/autocompleter/
 *
 * @version		1.1.2
 *
 * @license		MIT-style license
 * @author		Harald Kirschner <mail [at] digitarald.de>
 * @copyright	Author
 */

var Autocompleter = new Class({

	Implements: [Options, Events],

	options: {/*
		onOver: $empty,
		onSelect: $empty,
		onSelection: $empty,
		onShow: $empty,
		onHide: $empty,
		onBlur: $empty,
		onFocus: $empty,*/
		minLength: 1,
		markQuery: true,
		width: 'inherit',
		maxChoices: 10,
		injectChoice: null,
		customChoices: null,
		emptyChoices: null,
		visibleChoices: true,
		className: 'autocompleter-choices',
		zIndex: 42,
		delay: 1,
		observerOptions: {},
		fxOptions: {},

		autoSubmit: false,
		overflow: false,
		overflowMargin: 25,
		selectFirst: true,
		filter: null,
		filterCase: false,
		filterSubset: false,
		forceSelect: false,
		selectMode: true,
		choicesMatch: null,
		multiple: false,
		separator: ', ',
		separatorSplit: /\s*[,;]\s*/,
		autoTrim: false,
		allowDupes: false,

		cache: true,
		relative: true,
                //autocompleteType: null
                tokenFormat: 'object',
                tokenIdKey: 'id',
                tokenValueKey: 'label',
                prefetchOnInit : false,
                alwaysOpen : false,
                ignoreKeys : false,
                ignoreOverlayFix : false
	},

	initialize: function(element, options) {
		this.element = $(element);
		this.setOptions(options);
		this.build();
		this.observer = new Observer(this.element, this.prefetch.bind(this), $merge({
			'delay': this.options.delay
		}, this.options.observerOptions));
		this.queryValue = null;
		if (this.options.filter) this.filter = this.options.filter.bind(this);
		var mode = this.options.selectMode;
		this.typeAhead = (mode == 'type-ahead');
		this.selectMode = (mode === true) ? 'selection' : mode;
		this.cached = [];
                
                if( this.options.prefetchOnInit ) this.prefetch.delay(this.options.delay + 50, this);
	},

	/**
	 * build - Initialize DOM
	 *
	 * Builds the html structure for choices and appends the events to the element.
	 * Override this function to modify the html generation.
	 */
	build: function() {
		if ($(this.options.customChoices)) {
			this.choices = this.options.customChoices;
		} else {
			this.choices = new Element('ul', {
				'class': this.options.className,
				'styles': {
					'zIndex': this.options.zIndex
				}
			}).inject(document.body);
			this.relative = false;
			if (this.options.relative) {
				this.choices.inject(this.element, 'after');
				this.relative = this.element.getOffsetParent();
			}
			if(!this.options.ignoreOverlayFix) this.fix = new OverlayFix(this.choices);
		}
		if (!this.options.separator.test(this.options.separatorSplit)) {
			this.options.separatorSplit = this.options.separator;
		}
                if( !this.options.alwaysOpen ) {
                  this.fx = (!this.options.fxOptions) ? null : new Fx.Tween(this.choices, $merge({
                          'property': 'opacity',
                          'link': 'cancel',
                          'duration': 200
                  }, this.options.fxOptions)).addEvent('onStart', Chain.prototype.clearChain).set(0);
                }
		this.element.setProperty('autocomplete', 'off')
			.addEvent((Browser.Engine.trident || Browser.Engine.webkit) ? 'keydown' : 'keypress', this.onCommand.bind(this))
			.addEvent('click', this.onCommand.bind(this, [false]));

                if( !this.options.alwaysOpen ) {
                  this.element
			.addEvent('focus', this.toggleFocus.create({bind: this, arguments: true, delay: 100}))
			.addEvent('blur', this.toggleFocus.create({bind: this, arguments: false, delay: 100}));
                }
	},

	destroy: function() {
		if (this.fix) this.fix.destroy();
		this.choices = this.selected = this.choices.destroy();
	},

	toggleFocus: function(state) {
		this.focussed = state;
		if (!state) this.hideChoices(true);
		this.fireEvent((state) ? 'onFocus' : 'onBlur', [this.element]);
	},

	onCommand: function(e) {
		if (!e && this.focussed) return this.prefetch();
		if (e && e.key && !e.shift && !this.options.ignoreKeys) {
			switch (e.key) {
				case 'enter':
                                        e.stop();
					if( !this.selected ) {
                                          if( !this.options.customChoices ) {
                                            // @todo support multiple
                                            this.element.value = '';
                                          }
                                          return true;
                                        }
					if (this.selected && this.visible) {
						this.choiceSelect(this.selected);
						return !!(this.options.autoSubmit);
					}
					break;
				case 'up': case 'down':
                                        var value = this.element.value;
					if (!this.prefetch() && this.queryValue !== null) {
						var up = (e.key == 'up');
						this.choiceOver((this.selected || this.choices)[
							(this.selected) ? ((up) ? 'getPrevious' : 'getNext') : ((up) ? 'getLast' : 'getFirst')
						](this.options.choicesMatch), true);
                                                this.element.value = value;
					}
					return false;
				case 'esc':
					this.hideChoices(true);
                                        if( !this.options.customChoices ) this.element.value = '';
                                        //if (this.options.autocompleteType=='message') this.element.value="";
					break;
                                case 'tab':
                                        if (this.selected && this.visible) {
                                          this.choiceSelect(this.selected);
                                          return !!(this.options.autoSubmit);
                                        } else {
                                          this.hideChoices(true);
                                          if( !this.options.customChoices ) this.element.value = '';
                                          //if (this.options.autocompleteType=='message') this.element.value="";
                                          break;
                                        }
                                          
			}
		}
                this.fireEvent('onCommand', e);
		return true;
	},

	setSelection: function(finish) {
		//var input = this.selected.inputValue[0], value = input;
                var tokenInfo = this.selected.retrieve('autocompleteChoice');
                //console.log(this.selected);
                var input = ( this.options.tokenFormat == 'object' ? tokenInfo[this.options.tokenValueKey] : tokenInfo );
                var value = input;
		var start = this.queryValue.length, end = input.length;
		if ( (input.substr(0, start) || '').toLowerCase() != (this.queryValue || '').toLowerCase()) start = 0;
		if (this.options.multiple) {
			var split = this.options.separatorSplit;
			value = this.element.value;
			start += this.queryIndex;
			end += this.queryIndex;
			var old = value.substr(this.queryIndex).split(split, 1)[0];
			value = value.substr(0, this.queryIndex) + input + value.substr(this.queryIndex + old.length);
			if (finish) {
				var tokens = value.split(this.options.separatorSplit).filter(function(entry) {
					return this.test(entry);
				}, /[^\s,]+/);
				if (!this.options.allowDupes) tokens = [].combine(tokens);
				var sep = this.options.separator;
				value = tokens.join(sep) + sep;
				end = value.length;
			}
		}
                // @todo figure what this is for
		if( this.options.autocompleteType == 'tag' ) this.observer.setValue(value);
		this.opted = value;
		if (finish || this.selectMode == 'pick') start = end;
		$try(function() { this.element.selectRange(start, end) }.bind(this)); // This seems to be throwing an error sometimes
		this.fireEvent('onSelection', [this.element, this.selected, value, input]);
	},

	showChoices: function() {
		var match = this.options.choicesMatch, first = this.choices.getFirst(match);
		this.selected = this.selectedValue = null;
		if (this.fix) {
			var pos = this.element.getCoordinates(this.relative), width = this.options.width || 'auto';
			this.choices.setStyles({
				//'left': pos.left,
				//'top': pos.bottom,
				'width': (width === true || width == 'inherit') ? pos.width : width
			});
		}
		if (!first) return;
		if (!this.visible) {
			this.visible = true;
			this.choices.setStyle('display', '');
			if (this.fx) this.fx.start(1);
			this.fireEvent('onShow', [this.element, this.choices]);
		}
		if (this.options.selectFirst || this.typeAhead || first.inputValue == this.queryValue) this.choiceOver(first, this.typeAhead);
		var items = this.choices.getChildren(match), max = this.options.maxChoices;
		var styles = {'overflowY': 'hidden', 'height': ''};
		this.overflown = false;
		if (items.length > max) {
			var item = items[max - 1];
			styles.overflowY = 'scroll';
			styles.height = item.getCoordinates(this.choices).bottom;
			this.overflown = true;
		};
		this.choices.setStyles(styles);
		if (this.fix) this.fix.show();
		if (this.options.visibleChoices) {
			var scroll = document.getScroll(),
			size = document.getSize(),
			coords = this.choices.getCoordinates();
			if (coords.right > scroll.x + size.x) scroll.x = coords.right - size.x;
			if (coords.bottom > scroll.y + size.y) scroll.y = coords.bottom - size.y;
			window.scrollTo(Math.min(scroll.x, coords.left), Math.min(scroll.y, coords.top));
		}
	},

	hideChoices: function(clear) {
		if (clear) {
			var value = this.element.value;
			if (this.options.forceSelect) value = this.opted;
			if (this.options.autoTrim) {
				value = value.split(this.options.separatorSplit).filter($arguments(0)).join(this.options.separator);
			}
			this.observer.setValue(value);
		}
		if (!this.visible) return;
		this.visible = false;
		if (this.selected) this.selected.removeClass('autocompleter-selected');
		this.observer.clear();
		var hide = function(){
			this.choices.setStyle('display', 'none');
			if (this.fix) this.fix.hide();
		}.bind(this);
		if (this.fx) this.fx.start(0).chain(hide);
		else hide();
		this.fireEvent('onHide', [this.element, this.choices]);
	},

	prefetch: function() {
		var value = this.element.value, query = value;
		if (this.options.multiple) {
			var split = this.options.separatorSplit;
			var values = value.split(split);
			var index = this.element.getSelectedRange().start;
			var toIndex = value.substr(0, index).split(split);
			var last = toIndex.length - 1;
			index -= toIndex[last].length;
			query = values[last];
		}
		if (query.length < this.options.minLength) {
			this.hideChoices();
		} else {
			if (query === this.queryValue || (this.visible && query == this.selectedValue)) {
				if (this.visible) return false;
				this.showChoices();
			} else {
				this.queryValue = query;
				this.queryIndex = index;
				if (!this.fetchCached()) this.query();
			}
		}
		return true;
	},

	fetchCached: function() {
                //console.log('pt1');
                switch( true ) {
                    // Not enabled or no data
                    case ( !this.options.cache ):
                    //case ( !this.cached ):
                    //case ( !this.cached.length ):
                    // Query value became less specific
                    case ( !this.cachedQueryValue || this.queryValue.length < this.cachedQueryValue.length ):
                    // Query value became completely different
                    case ( this.queryValue.indexOf(this.cachedQueryValue) == -1 ):
                    // Choices left are less than max choices
                      return false;
                      break;
                }
                //console.log('pt2');

                // If choices left are less than max choices, filter and return
                if( this.cached.length < this.options.maxChoices )
                {
                  this.update(this.filter(this.cached));
                  return true;
                }

                // If choices left are greater than or equal to maxChoices, but all match new query
                var newChoices = this.filter(this.cached, this.queryValue);
                if( newChoices.length >= this.cached.length )
                {
                  this.update(this.filter(this.cached));
                  return true;
                }

                // This means strange things?
                return false;
	},

	update: function(tokens) {
		$(this.choices).empty();
		this.cached = tokens;
                this.cachedQueryValue = this.queryValue;
		var type = tokens && $type(tokens);
		if (!type || (type == 'array' && !tokens.length) || (type == 'hash' && !tokens.getLength())) {
			(this.options.emptyChoices || this.hideChoices).call(this);
		} else {
			if (this.options.maxChoices < tokens.length && !this.options.overflow) tokens.length = this.options.maxChoices;
			tokens.each(this.options.injectChoice || function(token){
                                tokenValue = ( this.options.tokenFormat == 'object' ? token[this.options.tokenValueKey] : token );
				var choice = new Element('li', {'html': this.markQueryValue(tokenValue)});
				//choice.inputValue = tokenValue;
				this.addChoiceEvents(choice).inject(this.choices);
                                choice.store('autocompleteChoice', token);
                                /*
                                //var profile_img = "";
				if (this.options.autocompleteType=='message') profile_img = token[2];
                                var choice = new Element('li', {'html': profile_img+"<div class='autocompleter-choice'>"+this.markQueryValue(token[0])+"</div>",'id':token[0], value:token[1],'class': 'autocompleter-choices'});
				choice.inputValue = token[0];
				this.addChoiceEvents(choice).inject(this.choices);
                                */
			}, this);
			this.showChoices();
		}
	},

	choiceOver: function(choice, selection) {
		if (!choice || choice == this.selected) return;
		if (this.selected) this.selected.removeClass('autocompleter-selected');
		this.selected = choice.addClass('autocompleter-selected');
		this.fireEvent('onSelect', [this.element, this.selected, selection]);
		if (!this.selectMode) this.opted = this.element.value;
		if (!selection) return;
		this.selectedValue = this.selected.retrieve('autocompleteChoice');
		if (this.overflown) {
			var coords = this.selected.getCoordinates(this.choices), margin = this.options.overflowMargin,
				top = this.choices.scrollTop, height = this.choices.offsetHeight, bottom = top + height;
			if (coords.top - margin < top && top) this.choices.scrollTop = Math.max(coords.top - margin, 0);
			else if (coords.bottom + margin > bottom) this.choices.scrollTop = Math.min(coords.bottom - height + margin, bottom);
		}
		if (this.selectMode) this.setSelection();
	},

	choiceSelect: function(choice) {
		if (choice) this.choiceOver(choice);
		this.setSelection(true);
		this.queryValue = false;

		if( !this.options.alwaysOpen ) {
                  this.hideChoices();
                } else {
                  this.observer.setValue('');
                  this.prefetch.delay(this.options.delay, this);
                }

                this.fireEvent('onChoiceSelect', choice);
                
                if (this.options.autocompleteType=='message') {
                  this.element.value = '';
                  var token = choice.retrieve('autocompleteChoice');

                  //checking if the choice is al ist of friends
                  if (token.friends){
                    var friend_ids = "";
                    for (var id in token.friends) {
                      if(friend_ids == "") friend_ids = id;
                      else friend_ids = friend_ids+","+id;
                    }
                    this.doAddValueToHidden(token.label, friend_ids, 'toValues', true, ' tag_friend');
                    // code to expend out the suggestion list
                    /*
                    for (var id in token.friends) {
                      this.doAddValueToHidden(token.friends[id], id, 'toValues', true);
                    }*/
                  }
                 
                  else this.doAddValueToHidden(choice.id, token.id, 'toValues', true);
                }
                
	},
        
        doAddValueToHidden: function (name, toID, hideLoc, newItem, list){
          // This is code for the invisible values
          var hiddenInputField = document.getElementById(hideLoc);
          var previousToValues = hiddenInputField.value;

          if (this.checkSpanExists(name, toID)){
            if (previousToValues==''){
              document.getElementById(hideLoc).value = toID;
            }
            else {
              document.getElementById(hideLoc).value = previousToValues+","+toID;
            }
            this.doPushSpan(name, toID, newItem, hideLoc, list);
          }
        },

        doPushSpan: function(name, toID, newItem, hideLoc, list){
          // summary:
          //		This is called to create a new span item with the id.

          var myElement = new Element("span");
          if (newItem){
            myElement.id = "tospan_"+name+"_"+toID;
            myElement.innerHTML = name+" <a href='javascript:void(0);' onclick='this.parentNode.destroy();removeFromToValue(\""+toID+"\", \""+hideLoc+"\");'>x</a>";
          }
          else{
            myElement.id = "tospan_"+name+"_"+toID;
            myElement.innerHTML = name+" <a href='javascript:void(0);' onclick='this.parentNode.destroy();removeFromToValue(\""+toID+"\", \""+hideLoc+"\");'>x</a>";
          }
          $('toValues-wrapper').setStyle('height', 'auto');


          if (list == null) list = "";
          myElement.addClass("tag"+list);

          document.getElementById('toValues-element').appendChild(myElement);
          this.fireEvent('push');
        },
        
        checkToValue: function (toID, toValues){
          // summary:
          // This returns a boolean "true" if the user is NOT already added ToValue
          var checkValue = true;
          
          var checkMulti = toID.search(/,/);

          var x;
          var toValueArray = toValues.split(",");

          
          // check if we are removing multiple recipients
          if (checkMulti!=-1){
            var recipientsArray = toID.split(",");
            var multiBool = false;
            for (var i = 0; i < recipientsArray.length; i++){
              var tempBool = true;
              for (var x = 0; x < toValueArray.length; x++){
                if (toValueArray[x]==recipientsArray[i]) {
                  tempBool =false;
                }
              }
              multiBool = multiBool || tempBool;
            }
            checkValue = multiBool;
          }
          else{
            for (x in toValueArray)
            {
              if (toValueArray[x]==toID) {
                checkValue =false;
                //alert("duplicate");
              }
            }
          }

          return checkValue;
        },

        checkSpanExists: function (name, toID){
          var span_id = "tospan_"+name+"_"+toID;
          if ($(span_id)){
            return false;
          }
          else return true;
        },

	filter: function(tokens, queryValue) {
                //this.lastFilterDelta = this.currentFilterDelta || 0;
                //this.currentFilterDelta = 0;
                //var delta = 0;
                queryValue = queryValue || this.queryValue;
                tokens = tokens || this.tokens;
                //console.log(tokens);
                var regex = new RegExp(((this.options.filterSubset) ? '' : '^') + queryValue.escapeRegExp(), (this.options.filterCase) ? '' : 'i');
                if( this.options.tokenFormat == 'object' ) {
                  var key = this.options.tokenValueKey;
                  return tokens.filter(function(token){
                    return regex.test(token[key]);
                  });
                } else {
                  return tokens.filter(function(token){
                    return regex.test(token);
                  });
                }
                //this.currentFilterDelta = delta;
                return tokens;
                /*
		return (tokens || this.tokens).filter(function(token) {
			return this.test(token[0]);
		}, new RegExp(((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp(), (this.options.filterCase) ? '' : 'i'));
                */
	},

	/**
	 * markQueryValue
	 *
	 * Marks the queried word in the given string with <span class="autocompleter-queried">*</span>
	 * Call this i.e. from your custom parseChoices, same for addChoiceEvents
	 *
	 * @param		{String} Text
	 * @return		{String} Text
	 */
	markQueryValue: function(str) {
		return (!this.options.markQuery || !this.queryValue) ? str
			: str.replace(new RegExp('(' + ((this.options.filterSubset) ? '' : '^') + this.queryValue.escapeRegExp() + ')', (this.options.filterCase) ? '' : 'i'), '<span class="autocompleter-queried">$1</span>');
	},

	/**
	 * addChoiceEvents
	 *
	 * Appends the needed event handlers for a choice-entry to the given element.
	 *
	 * @param		{Element} Choice entry
	 * @return		{Element} Choice entry
	 */
	addChoiceEvents: function(el) {
		return el.addEvents({
			'mouseover': this.choiceOver.bind(this, el),
			'click': this.choiceSelect.bind(this, el)
		});
	}
});

var OverlayFix = new Class({

	initialize: function(el) {
		if (Browser.Engine.trident) {
			this.element = $(el);
			this.relative = this.element.getOffsetParent();
			this.fix = new Element('iframe', {
				'frameborder': '0',
				'scrolling': 'no',
				'src': 'javascript:false;',
				'styles': {
					'position': 'absolute',
					'border': 'none',
					'display': 'none',
					'filter': 'progid:DXImageTransform.Microsoft.Alpha(opacity=0)'
				}
			}).inject(this.element, 'after');
		}
	},

	show: function() {
		if (this.fix) {
			var coords = this.element.getCoordinates(this.relative);
			delete coords.right;
			delete coords.bottom;
			this.fix.setStyles($extend(coords, {
				'display': '',
				'zIndex': (this.element.getStyle('zIndex') || 1) - 1
			}));
		}
		return this;
	},

	hide: function() {
		if (this.fix) this.fix.setStyle('display', 'none');
		return this;
	},

	destroy: function() {
		if (this.fix) this.fix = this.fix.destroy();
	}

});

Element.implement({

	getSelectedRange: function() {
		if (!Browser.Engine.trident) return {start: this.selectionStart, end: this.selectionEnd};
		var pos = {start: 0, end: 0};
		var range = this.getDocument().selection.createRange();
		if (!range || range.parentElement() != this) return pos;
		var dup = range.duplicate();
		if (this.type == 'text') {
			pos.start = 0 - dup.moveStart('character', -100000);
			pos.end = pos.start + range.text.length;
		} else {
			var value = this.value;
			var offset = value.length - value.match(/[\n\r]*$/)[0].length;
			dup.moveToElementText(this);
			dup.setEndPoint('StartToEnd', range);
			pos.end = offset - dup.text.length;
			dup.setEndPoint('StartToStart', range);
			pos.start = offset - dup.text.length;
		}
		return pos;
	},

	selectRange: function(start, end) {
		if (Browser.Engine.trident) {
			var diff = this.value.substr(start, end - start).replace(/\r/g, '').length;
			start = this.value.substr(0, start).replace(/\r/g, '').length;
			var range = this.createTextRange();
			range.collapse(true);
			range.moveEnd('character', start + diff);
			range.moveStart('character', start);
			range.select();
		} else {
			this.focus();
			this.setSelectionRange(start, end);
		}
		return this;
	}

});

/* compatibility */

Autocompleter.Base = Autocompleter;