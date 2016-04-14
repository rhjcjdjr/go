/* chat.js */

/* message attributes grabber */
function MessageAttributesGrabber(user) {
//	finds all data about message (text, user, ...)
//	that is about to be sent
	
	user = user ? user : (window.Owner ? window.Owner : null);

	//	collect all attrs
	this.grab = function() {
		var attributes =
		{
			user: null,
			text: this.getText(),
			visible: this.getVisibility(),
			local_id: randomString(10),
			created_at: $.formatDateTime('hh:ii mm/dd/yy', new Date()),
		}

		user !== null && attributes.visible && (attributes.user = user);

		return attributes;
	}

	//	get message text
	this.getText = function() {

		var prepareInputText = function(txt) {
			return txt;
		}

		return prepareInputText( $('#chat-inp-textarea').val().trim() );
	}
	//	get author visibility (1: visible, 0: hidden)
	this.getVisibility = function() {
		return $('#chat-inp-checkbox').is(':checked') ? 0:1;
	}
}

/* message object */
function ChatMessage() {
//	here message creating before be sent to server
//	an HTML wrap attaching to page while message is pending
//	@see knockout js

	//	create message view model
	//	after 'go' button clicked grabber grabs data of the message
	//	and this class is extended with it (@see ChatConstructor:createMessage())
	//	and the last thing - register the message in a observable messages collection
	this.applyObservables = function() {
		//	binding observables
		//	not all of them, only really needed ones
		//	as soon as it costs much
		this.id = ko.observable(this.id);
	    this.text = ko.observable(this.text);
		this.visible = ko.observable(this.visible);
		this.local_id = ko.observable(this.local_id);
		this.created_at = ko.observable($.formatDateTime('hh:ii mm/dd/yy', new Date( parseInt(this.ts + '000') )));

		if (this.user) {
			this.user.online = ko.observable(false);
		}
	};

	this.toview = function() {
		var el;
		if (this.getAnyId()) {
			if (el = document.getElementById(this.getAnyId())) {
				el.scrollIntoView();
			}
		} else {
			log('Found element without ID: can not scroll into view of it.');
		}
	};

	this.hide = function() {
		$('#' + this.getAnyId()).hide();
	};
	this.show = function() {
		$('#' + this.getAnyId()).show();
	};
	this.escapedText = function() {
		return escape(this.text());
	};

    //	get message ID.
    //	if message is local (not sent to server yet) - local id is returned
    //	if message is not local - it's id returned
    this.getAnyId = function() {
    	var prefixRealId = 'mess_';
    	var prefixLocalId = 'mess_local_';

        if (this.id() !== undefined) {
        	return prefixRealId + this.id();
        }
        else if (this.local_id() !== undefined) {
        	return prefixLocalId + this.local_id();
        }
        else {
        	log('Fatal error! message have no neither INT id, nor LOCAL id.');
        }
        return false;
    };

    this.showPending = function() {
    	var self = this;

    	setTimeout(function() {
    		if (is_int(Number(self.id()))) {
    			//	already added
    		} else {
    			$('#'+self.getAnyId() + ' .msg-pending').addClass('msg-pending-flag');
    		}
    	}, 500);
    }
    this.hidePending = function() {
    	$('#'+this.getAnyId() + ' .msg-pending').removeClass('msg-pending-flag');
    }

	//	update local message
	//	after server respond the method called
	//	updates performed here
	this.update = function(attributes) {
		log('Updating the message: (id - ' + attributes.id + ')');

		this.id(attributes.id);
		this.created_at($.formatDateTime('hh:ii mm/dd/yy', new Date( parseInt(attributes.ts + '000') )));

        if (window.chatServer) {
        	log('setting last id [' + this.id() + '] to server from chat::update()');
        	chatServer.setLastId(this.id());
        }
	}
}

/* chat client */
function Chat() {

	//	button via new chat message are sent
	this.btnSendSelector = '.chat-btn-send';
	//	chat typer section id
	//	this.chatTyperId = '';
	//	url to send message to
	this.serverUrl = 'chat/new';
	//	url to get messages from
	this.serverMessagesUrl = 'ajax/chat/messages';
	//	'load more messages' helper
	this.pendingMessagesMore = false;
	//	prevent runnin out of memory
	this.availableMessageslimitPerTime = 30;
	//	the same as above
	this.messagesLoadChunkSize = 10;
	//	messages stack helper
	this.pendingSend = false;
	//	VM of messages
	//	@see knockout js
	//	this.vm;
	//	messages stack to be added
	//	adding strictly successively, one by one, to prevent
	//	big traffic (spam) also to
	//	reduce one time connections to server since my server is weak, coz
	//	I don't have enough resources to get powerfull one
	this.messagesStack = [];



	//	init knockout
	this.init = function() {
		var chatLocal = this;

		//	listen buttons being used to send message
		//	and rise "create-message" event after clicked
		$(chatLocal.btnSendSelector).on('click', function(btn) {
			$(chatLocal).trigger('create-message', [btn]);
		});
	}

	//	create message
	this.message = function(attributes) {
		var self = this;

			var message = $.extend(attributes, new ChatMessage);
				message.applyObservables();
		return  message;
	}

    this.load = function(from, limit, dir, done) {

		$.ajax({
			url: this.chatServerMessagesUrl,
			data: {
				dir: dir,
				from: from,
				limit: limit
			},
			method: 'get'
		})
		.always(function(response) {
			//	 to do
		})
		.done(function(response) {
			
			if (response.status == 'ok') {
				done && done(response.response.data);
			}

		})
		.fail(function(response) {
			devError('Failed load more messages');
		});

    }

    //	send new message to server
    this.send = function(msg, dCb, fCb) {
    	var self = this;

    	self.messagesStack.push({
    		msg: msg,
    		dCb: dCb,
    		fCb: fCb
    	});

    	if (self.pendingSend == true) {

    	} else {
    		self.trySend();
    	}
    }

    //	delegate send method
    this.trySend = function sender() {
    	var self = this;

    	var deferred = self.sendProcess();

    	if (deferred) {
    		deferred.done(function() {
    			if (self.messagesStack.length > 0) {
    				sender.call(self);
    			} else {
    				self.pendingSend = false;
    			}
    		});    		
    	} else {
    		self.pendingSend = false;
    	}
    }

    //	real message sending
    this.sendProcess = function() {
    	var self = this;

    	var messageWrap = chat.messagesStack.shift();

    	if ( ! messageWrap) {
    		return null;
    	}

    	self.pendingSend = true;

	    	var msg = messageWrap.msg;
	    	var dCb = messageWrap.dCb;
	    	var fCb = messageWrap.fCb;

			var data = {
				text: msg.text(),
				visible: msg.visible(),
				local_ts: msg.local_ts,
				local_id: msg.local_id(),
			};

			var deferred = $.Deferred();

			$.ajax({
				url: self.serverUrl,
				data: data,
				method: 'post'
			})
			.always(function() {
				deferred.resolve();
			})
			.done(function(response) {
				if (dCb)
					dCb(response, msg);
				else
					self.onMessageRecive(response, msg);
			})
			.fail(function(response) {
				if (fCb)
					fCb(response, msg);
				else
					self.onMessageReciveNot(response, msg);
			});

			return deferred.promise();
    }


	this.onMessageRecive = function(response, message) {
		if (response && response.status == 'ok') {
			message.update(response.message_raw);
		} else {

		}
	}

	this.onMessageReciveNot = function(response, message) {
		devError('given response object done with code != 200, message is not sent');
		message.not_delivered();
	}
}