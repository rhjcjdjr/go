/* chat-server.js */

function ChatServer() {
	//	last message id
	this.lastId = null;
	//	pool server url
	this.url = null;
	//	data sent to server on each request
	this.requestData = {};
	//	is js connecten to server at the time
	this.pending = false;
	//	last server response
	this.response = null;
	//	local ts
	this.lastTs = null;
	//	turn on/off server
	this.canListenFlag = false;
	//	ajax jq object
	this.jqAjax = null;

	//	lesten for server
	this.startListen = function() {

		if (true === this.isPending()) {
			log('trying to connect to pooling server multiple times [chatServer::startListen called 2 times]');
		} else {

			if ( ! this.canListen()) {
				log('chatServer can not start listening. [canListen == false]');
				return;
			}

			//	dev
			log(new Date().getTime() + ' starting listening pooling server....');
			//	prevent synchronous (at one time) listening
			this.setPending(true);
			//	prepare data to send

			var requestData = {};
				this.hasLastId() ? (requestData.last = this.getLastId()) : null;
				this.hasTs() ? (requestData.ts = this.getTs()) : null;

			this.setRequestData(requestData);

			var context = this, jqXHR;

				jqXHR = context.jqAjax = $.ajax({
					url:context.url,
					method:'post',
					data:context.getRequestData(),
					beforeSend: function(jqXHR) {
						$(context).trigger('before-listen', [context]);
					}
				});

				context.jqAjax.always(function(response) {
					context.onServerResponseAny(response);
				})
				.done(function(response) {
					context.onServerResponseDone(response);
				})
				.fail(function(response) {
					context.onServerResponseFail(response);
				});

			(function(context) {
				setTimeout(function() {

				}, 0);
			})(this);
		}
	}

	this.canListen = function() {
		return Boolean(this.canListenFlag);
	}

	this.setCanListen = function(flag) {
		$(this).trigger('status-change', [this]);
		this.canListenFlag = flag;
	}

	//	set if server can/not listen
	this.stopListen = function(canselCurrent) {
		if (canselCurrent && canselCurrent == true) {
			if (this.jqAjax) {
				this.jqAjax.abort();
			}
		}
		this.setCanListen(false);
	}

	//	must be called after creating
	this.init = function(arguments) {
		if ( ! arguments) {
			throw new Error('There must be Arguments while chat server initialization. [url at least]');
		}

		if (arguments.url) {
			this.url = arguments.url;
		}

		if (null == this.url) {
			log('Fatal error! No url is set while chat server initialization')
		}

		this.setCanListen(true);
	}

	//	 get data
	this.getRequestData = function() {
		return this.requestData;
	}
	//	set data
	this.setRequestData = function(data) {
		this.requestData = data;
	}


	this.getTs = function() {
		var ts = window.parseInt(this.lastTs);
		return is_int(ts) ? ts : null;
	}
	this.setTs = function(ts) {
		var ts = window.parseInt(ts);
		if (is_int(ts)) {
			this.lastTs = ts;
		} else {
			log('try to set invalid timestamp in chat server');
		}
	}
	this.hasTs = function() {
		return this.lastTs !== null;
	}

	//	after any response called first (before done or fail handlers)
	this.onServerResponseAny = function(response) {
		//	notify js that listening of server done
		this.setPending(false);

		//	here jq response object
		if (response.statusText == 'abort') {
			log('chatServer was canseled by client.');
		}

		if (response.status == 'fail') {
			log('chatServer error. Received status == fail');
		}

		if (response.status == 'ok') {
			log('chatServer response is finished. Received status == ok');

			if ( ! empty(response.output_ts)) {
				this.setTs(response.output_ts);
			}
		}
	}

	//	have response
	this.onServerResponseDone = function(response) {
		var self = this;

		if (this.formattedResponse(response)) {
			this.response = response;

			//	last message id in response
			if (response.response.last)
				this.setLastId(response.response.last);

			//!	deprecated
			if (this.hasEventType(response)) this.triggerEvent(this.getEventType(response));

			//	online people in response
			if (response && response.online) {
				self.fireOnline(response.online);
			}

			this.startListen();
		} else {
			log('response has broken format in chat server');
		}
	}

	this.setPending = function(state) {
		this.pending = Boolean(state);
	}

	this.isPending = function() {
		return this.pending;
	}

	this.onServerResponseFail = function() {
			var c = this, connectionFailedCb, connectionAlrightCb;

			connectionAlrightCb = function() {
				//	connection is ok, but server is unavailable
				//	fire global event that connection with pool server is lost
				$(rhjcjdjr).trigger('pooling:unavailable');
				//	try to reconnect to server
				c.connectionSeeker();
			}

			connectionFailedCb = function() {
				//	connection's lost
				$(rhjcjdjr).trigger('disconnected');
			}

			//	we got failed response
			//	the first step is to check if the connection with server is still available
			//	connectionAlright(connectionAlrightCb, connectionFailedCb);
			log('FATAL ERROR. No connection with pool server found.');
	}

	//	try to have connection with pool server back
	this.connectionSeeker = function seeker(connectionFoundCb) {
		var context = this, connectionFound, connectionFailed;

		seeker.delay = 2;
		seeker.counter = ++seeker.counter || 1;
		seeker.counter > 2 && (seeker.delay = 5);
		seeker.counter > 6 && (seeker.delay = 15);
		seeker.counter > 9 && (seeker.delay = 40);

		connectionFound = function() {
			if (connectionFoundCb)
				connectionFoundCb();
			else
				context.listen();
		}

		connectionFailed = function() {
			window.setTimeout(
				(function() {
					return function() {seeker.call(context)} })
				(), seeker.delay * 1000);
		}

		ajaxHead(this.url, connectionFound, connectionFailed);
	}



	//	like 'message' or 'like'
	this.triggerEvent = function(eventString) {
		$(this).trigger(eventString, [this.getResponse().response]);
	}

	this.setLastId = function(id) {
		var id = window.parseInt(id);
		if (is_int(id) && id >= 0) {
			this.lastId = id;
		} else {
			devError('try to set invalid last id [' + id + ']');
		}
	}
	this.getLastId = function() {
		var lid = window.parseInt(this.lastId);
		return is_int(lid) && lid >= 0 ? lid : null;
	}
	this.hasLastId = function() {
		return this.lastId !== null;
	}

	this.getResponse = function() {
		return this.response;
	}


	this.fireOnline = function(array) {
		$(this).trigger('online', [array]);
	}


	this.hasEventType = function(response) {
		return (response.response && response.response.type) ? true : false;
	}

	this.getEventType = function(response) {
		if (this.hasEventType(response))
			return response.response.type;
		return null;
	}

	//	check if given response obj can be accepted
	//	to be accepted response must follow local rules below
	this.formattedResponse = function(response) {
		if (response) {
			return (
				'status' in response &&
				(response.status === 'ok' || response.status === 'fail') &&
				'response' in response
			) ? 1 : 0;
		}
		return 0;
	}
}