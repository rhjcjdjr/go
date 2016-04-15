@extends("layout/always")


@push("title", "Общение")
@push("app-title", "Общение (<span data-bind=\"text: vmChat.online().length\"></span>)")


@push("menu-item")
<li class="divider"></li>
<li><a href="{{ route('logout') }}">Выход</a></li>
@endpush


@push("bottom-js")
    <script type="text/javascript" src="/js/include/autosize.min.js"></script>
    <script type="text/javascript" src="/js/include/knockout.js"></script>
    <script type="text/javascript" src="/js/include/jquery.formatDateTime.js"></script>
    <script type="text/javascript" src="/js/chat-server.js"></script>
    <script type="text/javascript" src="/js/chat.js"></script>
    <script type="text/javascript">
    /* fix page */
    $(function() {
        //  make chat input textarea height-resizable
        var resizeObject = autosize($('#content-outter .chat-input'));
        $(resizeObject).on('autosize:resized', function() {
            //  after textarea height changed, we need to fix content-box height
            footerToBottom();
        });
    });
    </script>

    <script type="text/javascript">
    /* main */
    $(function() {
        //  main view model
        function knockoutInit() {
            this.vmChat = new vmChatInit(),
            this.vmElse = new vmElseInit()
        }

        //  separate view models
        //  chat
        function vmChatInit() {
            var self = this;

            //  get last message
            this.last = function() {
                return self.messages().length ? self.messages()[self.messages().length - 1] : null;
            }
            //  get first message
            this.first = function() {
                return self.messages()[0] ? self.messages()[0] : null;
            }
            //  returns id of nearest message of given id
            this.near = function(id) {

                id = +id;

                var collectionId = [],
                    index = null,
                    indexId,
                    got;

                self.loop(function(mess) {
                    var messageId = +mess.id();

                    if (is_int(messageId)) {
                        collectionId.push(messageId);
                    } else {
                        ('temp message')
                        log('messages collection has NOT INT id: [' + mess.getAnyId() + ']');
                    }
                });

                if ($.inArray(id, collectionId) !== -1) {
                    log('warning! given to chat::near(ID) method ID is already exists.');
                    return null;
                }

                collectionId.push(id);

                //  stronger to leak
                collectionId.sort(function(a, b) { return b-a; });
                index = $.inArray(id, collectionId);

                if (collectionId[index + 1] !== undefined) {
                    indexId = collectionId[index + 1];
                    //  var rvl = self.index(self.get(indexId)) + 1;
                    var rvl = self.index(self.get(indexId)) + 1;

                    if (rvl == self.index(self.last())) {
                        log('push is better.');
                        return null;
                    } else {
                        return rvl;
                    }
                } else {
                    if (id < collectionId[0]) return 0;
                }

                return null;
            }
            //  get single message by id
            this.get = function(id) {
                var result = null;
                self.loop(function(message) {
                    if (message.id() == id) {
                        result = message;
                        return 0;
                    }
                });
                return result;
            }
            //  loop through the messages with cb.
            //  if cb returns something !== (undefined|null), the value will be returned
            this.loop = function(cb) {
                for (var i = self.messages().length - 1; i >= 0; i--) {
                    if ((result = cb(self.messages()[i])) !== undefined && result !== null) {
                        return result;
                    }
                };
            }
            //  check if message exists with given id.
            //  if so than the message returned (instead of bool[true])
            this.has = function(id) {
                var message;
                return  (message = self.get(id)) ? message : false;
            }
            //  remmove message by id or object of it
            this.remove = function(id) {
                var message;

                if (is_int(id) && id >= 0) {
                    if (message = self.get(id)) {
                        //  ... ok here
                    }
                } else if (typeof id === 'object') {
                    message = id;
                }

                self.messages.remove(message);
            }
            //  get index of given message
            this.index = function(message) {
                var index = $.inArray(message, this.messages());
                if (index === -1) {
                    return null;
                }
                return +index;
            };
            //  get previous messages (if any) to given
            this.prev = function(message) {
                var index;
                if ((index = this.index(message)) !== null) {
                    if (this.messages()[index - 1] !== undefined) {
                        return this.messages()[index - 1];
                    }
                }
                return null;
            };
            //  get next message (if any) to given
            this.next = function(message) {
                var index;
                if ((index = this.index(message)) !== null) {
                    if (this.messages()[index + 1] !== undefined) {
                        return this.messages()[index + 1];
                    }
                }
                return null;
            };


            self.online = ko.observableArray([]);
            self.messages = ko.observableArray([]);

            //  prevent out of memory stuff
            this.messages.subscribe(function(message) {

                if (self.messages().length >= chat.availableMessageslimitPerTime) {
                    self.remove(self.first());
                }

            }, null, "arrayChange");
        }
        //  something else
        function vmElseInit() {
            //  to do
        }

        //  yes. window.
        window.knockoutBinds = new knockoutInit;

        //  log(knockoutBinds);

        //  applied to all page,
        //  so on creating new view model it should be
        //  put to "knockoutInit" function above
        ko.applyBindings(knockoutBinds);
    });
    </script>
@endpush


@push("head-css")
<style type="text/css">
    .speech {
        position: relative;
        background: #D0E8FF;
        color: #2A6B79;
        display: inline-block;
        border-radius: 0;
        padding: 12px 20px;
    }
    .speech:before {
        content: "";
        display: block;
        position: absolute;
        width: 0;
        height: 0;
        left: 0;
        top: 0;
        border-top: 7px solid transparent;
        border-bottom: 7px solid transparent;
        border-right: 7px solid #D0E8FF;
        margin: 15px 0 0 -6px;
    }
    .speech-right>.speech:before {
        left: auto;
        right: 0;
        border-top: 7px solid transparent;
        border-bottom: 7px solid transparent;
        border-left: 7px solid #ffdc91;
        border-right: 0;
        margin: 15px -6px 0 0;
    }
    .speech .media-heading {
        font-size: 1.2em;
        color: #317787;
        display: block;
        border-bottom: 1px solid rgba(0,0,0,0.1);
        margin-bottom: 10px;
        padding-bottom: 5px;
        font-weight: 300;
    }
    .speech-time {
        margin-top: 20px;
        margin-bottom: 0;
        font-size: .8em;
        font-weight: 300;
    }
    .speech-right {
        text-align: right;
    }
    .speech-right>.speech {
        background: #ffda87;
        color: #a07617;
        text-align: right;
    }
    .speech-right>.speech .media-heading {
        color: #a07617;
    }
    .img-sm {
        width:40px;
        height:40px;
    }
    .chat-input {
        resize:none;
        font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
        font-size: 15px;
        line-height: 1.42857143;
        overflow-y:hidden !important;
    }
    .chat-btn-send {
        font-size:1em;
    }
    #footer .checkbox {
        height:20px;
        margin-top:0px !important;
        margin-bottom:0px !important;
        padding-top:10px;
        font-size:1em;
    }
    .speech .msg-pending-flag {
        background:url(/img/animate.gif) no-repeat;
        position:absolute;
        top:14px;
        left:-7px;
        width:12px;
        height:12px;
        -moz-background-size: 100%;
        -webkit-background-size:100%;
        -o-background-size:100%;
        background-size:100%;
    }
    .speech-right .msg-pending-flag {
        background:url(/img/animate.gif) no-repeat;
        position:absolute;
        top:14px;
        left:172px;
        width:12px;
        height:12px;
        -moz-background-size: 100%;
        -webkit-background-size:100%;
        -o-background-size:100%;
        background-size:100%;
    }

    /* media queries */
    @media (max-width: 490px) {
        /* fix chat typer box */
        #footer .row > div {
            padding-right:10px !important;
            padding-left:10px !important;
        }
    }
</style>
@endpush

@section("content")
<ul class="list-unstyled media-block" data-bind="foreach: {data: vmChat.messages}">

    <!-- template of message -->
    <li class="mar-btm" data-bind="attr: {id: getAnyId()}" style="position:relative;">
        <div data-bind="css: {'media-left': $data.user, 'media-right': !$data.user}">
            <!-- ko if:$data.user -->
            <img data-bind="attr: {src: user.pic_small}" class="img-circle img-sm" alt="" onerror="log('unable to load pic')">
            <!-- /ko -->
            <!-- ko ifnot:$data.user -->
            <img src="/img/hidden.jpg" class="img-sm" alt="" onerror="log('unable to load pic')">
            <!-- /ko -->
        </div>
        <div class="media-body pad-hor" data-bind="css: {'speech-right': !$data.user}">
            <div class="speech">
                <!-- pending -->
                <div class="msg-pending"></div>
                <!-- ko if:$data.user -->
                <a class="media-heading" data-bind="attr: {href: 'id' + user.id}, html: user.fname"></a>
                <!-- /ko -->
                <p data-bind="html: escapedText()"></p>
                <p class="speech-time">
                    <i class="fa fa-clock-o fa-fw"></i>
                    <span data-bind="text: created_at"></span>
                </p>
            </div>
        </div>
    </li>

</ul>
@endsection

@section("footer")
<div class="row">
    <div class="col-xs-8 col-md-9 col-lg-9">
        <textarea id="chat-inp-textarea" placeholder="Что происходит?" class="form-control chat-input"></textarea>
    </div>
    <div class="col-xs-4 col-md-3 col-lg-3">
        <button class="btn btn-primary btn-block chat-btn-send" type="submit">отправить</button>
        <div class="checkbox">
            <label><input id="chat-inp-checkbox" type="checkbox" value="1">анонимно</label>
        </div>
    </div>
</div>
@endsection

@push("bottom-js")
<script type="text/javascript">
/* add new message */
$(function() {
    //  yes. window.
    window.chat = new Chat;
        chat.init();

        $(chat).on('create-message', function(event, button) {
            ('create new message event fired')
            
            var grabber = new MessageAttributesGrabber();
            var message = chat.message(grabber.grab());

                knockoutBinds.vmChat.messages.push(message);
                    message.showPending();
                    message.toview();

            chat.send(message, function(response) {
                ('message sent successfully');

                if (response && response.message) {
                    var nearId = knockoutBinds.vmChat.near(Number(response.message.id));
                        message.update(response.message);
                    
                        message.hidePending();

                    if (null !== nearId) {
                        log('new message was spliced and updated');
                        knockoutBinds.vmChat.remove(message);
                        knockoutBinds.vmChat.messages.splice(nearId, 0, message);
                    } else {
                        log('new message was only updated');
                        //  knockoutBinds.vmChat.messages.push(message);
                    }
                }
            },
            function() {
                log('fail while sending message to server.');
            });
        });
});
</script>

<script type="text/javascript">
/* chat server initialization */
$(function() {
    //  yes. window.
    window.chatServer = new ChatServer;
        chatServer.init({url: 'chat/pool'});

    $(chatServer).on('message', function(event, response) {
        log('chat server Message event fired!');

        $.each(response.data, function(i, message)
        {
            var created = chat.message(message);
            var nearId = knockoutBinds.vmChat.near(created.id());
                //  message.update(response.message);

            if (null !== nearId) {
                log('received by chat server message was spliced');
                //  knockoutBinds.vmChat.remove(message);
                knockoutBinds.vmChat.messages.splice(nearId, 0, created);
            } else {
                log('received by chat server message was pushed');
                knockoutBinds.vmChat.messages.push(created);
            }
        });
    });
});
</script>

<script type="text/javascript">
/* get messages from server */
$(function() {
    $.ajax({
        url: 'ajax/chat/messages',
        method: 'get',
    })
    .done(function(response) {
        if (response.response && response.response.data) {
            $.each(response.response.data, function(i, message)
            {
                var created = chat.message(message);
                    knockoutBinds.vmChat.messages.push(created);
            });
        }
    })
    .fail(function() {
        log('fail while getting messages from server.');
    });
});
</script>

<script type="text/javascript">
/* junk */
$(function() {

    var onlineUsers = function selfFunction(e, server) {
        //  users online. Notice: you should check it AFTER listening pool server so
        //  database will be updated
        setTimeout(function() {
            $.ajax({
                url: 'ajax/chat/online',
                method: 'get',
            })
            .done(function(response) {
                $(chatServer).off('before-listen', selfFunction);

                if (response.response && response.response.data) {
                    chatServer.fireOnline(response.response.data);
                }
            });
        }, 800);
    };
    //  check online users after chatServer sent request (update current user in which)
    $(chatServer).on('before-listen', onlineUsers);

    //  online users
    $(chatServer).on('online', function(event, users) {
        ('chat server Online event fired!');

        knockoutBinds.vmChat.online([]);

        $.each(users, function(i, user) {
            knockoutBinds.vmChat.online.push(user);
        });
    });
});
</script>

<script type="text/javascript">
/* main */
$(function() {
    chatServer.startListen();
});
</script>
@endpush


@section("under-content")
<div class="row">
    <div class="col-md-12" data-bind="foreach: {data: vmChat.online}">
        <a href="#" style="display:inline-block; width: 60px; height: 60px; /*background: #eee;*/ overflow:hidden; padding: 4px 6px; margin: 0 10px 6px 0;" data-bind="attr: {href: '/id' + user.id}">
            <img data-bind="attr: {src: user.pic_small}" style="width:40px; height:40px; margin:0 auto; display:block;">
            <p style="overflow:hidden; text-align:center;" data-bind="html: user.fname"></p>
        </a>
    </div>
</div>
@endsection
