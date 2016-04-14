@extends("layout/always")


@push("title", "Мои поиски")
@push("app-title", "Мои поиски")


@push("head-css")
	<link rel="stylesheet" type="text/css" href="/css/bootstrap/bootstrap-social.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/css/search-you.css">
    <link rel="stylesheet" href="/css/sky-forms.css">
    <link rel="stylesheet" href="/css/custom-sky-forms.css">
    <link rel="stylesheet" href="/css/bootstrap/slider.min.css">

    <style type="text/css">

    </style>
@endpush


@push("nav-item")
<li class="active">
	<a href="{{ route('search-of') }}" style="padding-top:15px; padding-bottom:14px;">Мои поиски</a>
</li>
@endpush


@push("bottom-js")
	<script type="text/javascript" src="/js/include/knockout.js"></script>
	<script type="text/javascript" src="/js/search-you.js"></script>
	<script type="text/javascript" src="/js/search-you-client.js"></script>
	<script type="text/javascript" src="/js/include/jsts.js"></script>

	<script type="text/javascript">
	$(function() {
		rhjcjdjr.searchYou = new SearchYou();
	});
	</script>

	<script type="text/javascript">
	$(function() {
		$('#map-canvas').hide();
	});
	</script>

	<script type="text/javascript">
	$(function() {
		var searchYou = rhjcjdjr.searchYou;
		var googleMap = null;

		searchYou.mineSearches({}, function(response) {
			log(response.response.data.searches);
			if (response && response.status == 'ok') {
				var searches = response.response.data.searches;

				$.each(searches, function(i, searchItem) {
					searchItem.tempId = randomString(6);
					knockoutBinds.vmSearch.searches.push(searchItem);
				});
			}
		},
		function(response) {
			log('Error requesting "mineSearches"');
		});

		function vmSearchInit() {
			this._prev = null;

			//	get current elemtnt (clicked one)
			this.onClick = function(currentSearchItem) {
				loadGoogleMapScript(function(google) {
					var googleMap = new SearchYouMap();
						googleMap.createGoogleMap(google, 'map-canvas');
						googleMap.initGoogleMap();

					var polygon = new google.maps.Polygon({
						paths: JSON.parse(currentSearchItem.polygon_coords_serialized),
						strokeColor: '#FF0000',
						strokeOpacity: 0.8,
						strokeWeight: 2,
						fillColor: '#FF0000',
						fillOpacity: 0.35,
						draggable: false,
						geodesic: true
					});

					polygon.setMap(googleMap.getMap());


					$('#map-canvas').show();

					$('#' + this._prev).parent().css({backgroundColor: ''});
					this._prev = currentSearchItem.tempId;
					$('#' + currentSearchItem.tempId).parent().css({backgroundColor: '#ccc'});
				});
			},

			this.printComment = function(comment) {
				if (comment.length > 100) {
					return comment.substring(0, 100) + '...';
				}
				return comment;
			},
			this.printPublishDate = function(date) {
				return date;
			},

            this.searches = ko.observableArray([]);
		}

        function knockoutInit() {
            this.vmSearch = new vmSearchInit(googleMap);
        }

        window.knockoutBinds = new knockoutInit;
        ko.applyBindings(knockoutBinds);
	});
	</script>
@endpush


@section("content")
<!-- google/open street maps canvas -->
<div class="row">

	<div id="vk_api_transport"></div>
	<script type="text/javascript">
	window.vkAsyncInit = function() {
		VK.init({
			apiId: {{ $vk_app_id }}
		});
	};

	setTimeout(function() {
		var el = document.createElement("script");
		el.type = "text/javascript";
		el.src = "//vk.com/js/api/openapi.js";
		el.async = true;
		document.getElementById("vk_api_transport").appendChild(el);
	}, 0);
	</script>

	<div class="col-md-12">
		<div id="map-canvas"></div>
	</div>

	<div class="col-md-12" data-bind="foreach: {data: vmSearch.searches}">
		<div class="search-item" data-bind="click: knockoutBinds.vmSearch.onClick.bind($element)">
			<div class="row" data-bind="attr: {id: tempId}">
				<div class="col-md-1">
					<div data-bind="text: '# ' + id"></div>
				</div>
				<div class="col-md-3">
					<span class="search-item-title">Дата публикации:</span>
					<div data-bind="text: knockoutBinds.vmSearch.printPublishDate(created_at)"></div>
				</div>
				<div class="col-md-4">
					<span class="search-item-title">Совпадение:</span>
					<span class="search-item-matches-not">
						<div data-bind="">Пока что нет</div>
					</span>
				</div>
				<div class="col-md-4">
					<span class="search-item-title">Комментарий:</span>
					<div data-bind="text: knockoutBinds.vmSearch.printComment(comment)"></div>
				</div>
			</div>
		</div>
    </div>
</div>
@endsection


@section("under-content")
<div class="row">
	<div class="col-md-12">
		<p>Hi there.</p>
	</div>
</div>
@endsection