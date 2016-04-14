<!doctype html>
<html lang="en">
<head>
	<!-- meta -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>@yield("title")</title>


	<!-- certain for all pages css placed here -->
	<!-- include, lib, framework -->
	<link href="/css/bootstrap/bootstrap.min.css" rel="stylesheet">
	<link href="/css/bootstrap/font-awesome.min.css" rel="stylesheet">
	<!-- app -->
	<link href="/css/always.css" rel="stylesheet">


	<!-- shared head stack. js, css are pushed here -->
	@stack("head")


	<!-- head css stack -->
	@stack("head-css")


	<!-- head js stack -->
	@stack("head-js")
</head>
<body>
	<!-- main container -->
	<div class="container col-md-12 col-lg-12" id="content-outter">
        <div class="panel">
            <!-- heading -->
            <div id="header" class="panel-heading col-md-8 box">
                <div class="panel-control">

				<nav class="navbar navbar-default" style="border:none; background-color:inherit; min-height:40px;">
					<div class="container-fluid">
						<div class="navbar-header" id="nav-btn-dropdown">
							<button type="button" class="navbar-toggle collapsed" id="nav-btn-collapsed" style="margin-right:80px;">
								<span class="sr-only">Toggle navigation</span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>

							
							<div class="navbar-collapse collapse" id="nav-manu-content" style="margin-right:80px;">
								<ul class="nav navbar-nav">
									@if (Auth::check())
									@stack("nav-item")
									<li><a href="#" style="padding-top:15px; padding-bottom:14px;">Главная</a></li>
									<li>
										<a href="{{ route('search') }}" style="padding-top:15px; padding-bottom:14px;">Поиск</a>
									</li>
									<li><a href="{{ route('chat') }}" style="padding-top:15px; padding-bottom:14px;">Общение</a></li>
									@endif
								</ul>
							</div>
							

							<div style="position:absolute; right:0; top:0;">
								<button type="button" class="btn btn-default" data-toggle="dropdown">
									<i class="fa fa-gear"></i>
								</button>
								<button class="btn btn-default" type="button" id="page-trigger-btn">
									<i class="fa fa-chevron-down"></i>
								</button>
		    					<ul class="dropdown-menu dropdown-menu-right">
		    						<li>
		    							<a href="#" data-toggle="modal" data-target="#exampleModal" data-whatever="Сообщить о проблеме">Сообщить о проблеме</a>
		    						</li>
		    						<li>
		    							<a href="#" data-toggle="modal" data-target="#exampleModal" data-whatever="Предложить улучшение">Предложить улучшение</a>
		    						</li>
		    						<li><a href="#">Помощь</a></li>
									@if (Auth::check())
									<li class="divider"></li>
									<li><a href="{{ route('logout') }}">Выход</a></li>
									@endif
		    					</ul>
		    				</div>
						</div>
					</div>
				</nav>

                </div>
                <!-- logo, title -->
                <h3 class="panel-title" style="display:block; text-overflow: ellipsis; white-space: nowrap;">@yield("app-title")</h3>

            </div>
    
            <!--widget body-->
            <div id="demo-chat-body">
            	<div id="main-body" class="collapse in">
	                <div class="nano has-scrollbar">
	                    <div id="scrollable-content" class="nano-content pad-all" tabindex="0" style="right: -11px;">

	                    <div class="col-md-8 col-xs-12 box">
						<!-- layout content: before -->
						@yield("content")
						<!-- layout content: after -->
						</div>

	                    </div>
	                    <div class="nano-pane">
	                        <div class="nano-slider" style="height: 141px; transform: translate(0px, 0px);"></div>
	                    </div>
	                </div>
				</div>

            	<div id="under-body" style="display:none;">
	                <div class="nano has-scrollbar">
	                    <div class="nano-content pad-all" tabindex="0" style="right: -12px;">
	                    	
	                    	<div class="col-md-8 box">
	                    	@yield("under-content")
	                    	</div>

	                    </div>
	                    <div class="nano-pane">
	                        <div class="nano-slider" style="height: 141px; transform: translate(0px, 0px);"></div>
	                    </div>
	                </div>
				</div>

                <!--widget footer-->
                <div id="footer" class="panel-footer col-md-8 box">
                	<div class="footer-wrap">
                    	@section("footer")
                    	<div id="copyright">
                    		<div>
                    			Search-You is a trademark of Novikov Maxim. Copyright &copy; Novikov Maxim.
                    			<a href="#">Learn more...</a>
                    		</div>
                    	</div>
                    	@show
                	</div>
                </div>
            </div>
        </div>
	</div>

	<!-- certain for all pages js placed here -->
	<!-- unclude, lib, framework -->
	<script type="text/javascript" src="/js/include/jquery.min.js"></script>
	<script type="text/javascript" src="/js/bootstrap/bootstrap.min.js"></script>
	<!-- app -->
	<script type="text/javascript" src="/js/always.js"></script>

	<script type="text/javascript">
	/* fix page */
	$(function() {
		//	init bootstrap popovers
		$('[data-toggle="popover"]').popover();


		//	fix container height
		footerToBottom();
		//	on window resize fix content-box height
		$(window).resize(function() {
			footerToBottom();
			$('#nav-btn-collapsed').popover('hide');
		});


		//	page leverage
		$('#page-trigger-btn').on('click', function() {
			if ($('#main-body').is(':visible')) {
				$('#main-body').hide();
				$('#under-body').fadeIn(500);
			} else {
				$('#under-body').hide();
				$('#main-body').fadeIn(500);;
			}
		});
	});

	/* main */
	$(function() {
		$('#exampleModal').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget); // Button that triggered the modal
			var recipient = button.data('whatever'); // Extract info from data-* attributes
			// If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
			// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
			var modal = $(this);
			modal.find('.modal-title').text(recipient);
		});
	});

	/* main */
	$(function() {
		$('#nav-btn-collapsed').popover({
			//	title: "",
			content: function() {
				$('#nav-manu-content ul').addClass('via-popover');
				var cnt = $('#nav-manu-content').html();
				$('#nav-manu-content ul').removeClass('via-popover');
				return cnt;
			},
			animation: false,
			placement: 'bottom',
			html: true,
			trigger: 'click manual',
		});

		$('#nav-btn-collapsed').on('click', function() {
			
		});
	});

	/* main */
	$(function() {

	});
	</script>

	<div class="util">

	<!-- modals templates -->
	<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title" id="exampleModalLabel">Обращение</h4>
	      </div>
	      <div class="modal-body">
	        <form>
	          <div class="form-group">
	            <label for="recipient-name" class="control-label">Краткое описание:</label>
	            <input type="text" class="form-control" id="recipient-name">
	          </div>
	          <div class="form-group">
	            <label for="message-text" class="control-label">Подробное описание:</label>
	            <textarea class="form-control" id="message-text"></textarea>
	          </div>
	        </form>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
	        <button type="button" class="btn btn-primary">Создать обращение</button>
	      </div>
	    </div>
	  </div>
	</div>
	<!-- /modals templates -->

	<script type="text/javascript">
		/* current logged user */
		@if (Auth::check())
		//	yes. window.
		window.Owner = {
				id: '{{ Auth::user()->id }}',
				fname: '{{ Auth::user()->fname }}',
				lname: '{{ Auth::user()->lname }}',
				sex: '{{ Auth::user()->sex }}',
				pic: '{{ Auth::user()->pic }}',
				pic_small: '{{ Auth::user()->pic_small }}',
			};
		@else
			window.Owner = null;
		@endif

		/* main global object */
		window.rhjcjdjr = {
			DEBUG: true,
		};
	</script>
	</div>

	<!-- bottom js stack. not certain js is pushed here -->
	@stack("bottom-js")

</body>
</html>