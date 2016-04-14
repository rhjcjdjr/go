@extends("layout/always")


@push("title", "Поиск")
@push("app-title", "Поиск")


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
<li><a href="{{ route('search-of') }}" style="padding-top:15px; padding-bottom:14px;">Мои поиски</a></li>
@endpush


@push("bottom-js")
	<script type="text/javascript" src="/js/include/knockout.js"></script>
	<script type="text/javascript" src="/js/search-you.js"></script>
	<script type="text/javascript" src="/js/search-you-client.js"></script>
	<script type="text/javascript" src="/js/include/jquery.maskedinput.js"></script>
	<script type="text/javascript" src="/js/include/jsts.js"></script>
	<script type="text/javascript" src="/js/bootstrap/slider.min.js"></script>

	<script type="text/javascript">
		function vmSearchYouInit() {
            this.vk = {};
            this.vk.availableCountries = ko.observableArray([]);
            this.vk.availableCities = ko.observableArray([]);
            this.vk._currentCountryId = null;
            this.vk._currentCityId = null;
            
            this.vk.onClickCountry = function(root) {
            	//	enable city list
            	$('#search-region-city-inp').prop('disabled', false);

            	//	reset list
            	root.vmSearchYou.vk.availableCities([]);
            	$('#search-region-city-inp').val('');

            	root.vmSearchYou.vk._currentCountryId = this.cid;
            	$('#search-region-country-btn span:first').text(this.title);

		        VK.Api.call('database.getCities', {country_id: this.cid}, function(response) {
		        	if (response && response.response && response.response.length > 0) {
		        		knockoutBinds.vmSearchYou.vk.availableCities(response.response);

		        		if ( ! $('#search-city-lists').is(':visible')) {
		        			$('#search-city-lists').toggle();
		        		}
		        	}
		        });
            };
            this.vk.onClickCity = function(root) {
            	var region = '';

            	root.vmSearchYou.vk._currentCityId = this.cid;

            	if (this.region) {
            		region = ' (' + this.region.trim() + ')';
            	}

            	$('#search-region-city-inp').val(this.title.trim() + region);
            	$('#search-city-lists').toggle();
            	
            	log(this.title);
            	log(root.vmSearchYou.vk._currentCityId);
            	log(root.vmSearchYou.vk._currentCountryId);
            };
            this.vk.onTypingCity = function(el, root) {
            	var q = $(el).val();
            	var curr_country_id = root.vmSearchYou.vk._currentCountryId;

            	var requestOptions = {
            		q: q
            	};

            	if (curr_country_id != null) {
            		requestOptions.country_id = curr_country_id;
            	}

		        VK.Api.call('database.getCities', requestOptions, function(response) {
		        	if (response && response.response && response.response.length > 0) {
		        		knockoutBinds.vmSearchYou.vk.availableCities(response.response);

		        		if ( ! $('#search-city-lists').is(':visible')) {
		        			$('#search-city-lists').toggle();
		        		}
		        	}
		        });
            };
		}

        function knockoutInit() {
            this.vmSearchYou = new vmSearchYouInit();
        }

        window.knockoutBinds = new knockoutInit;
        ko.applyBindings(knockoutBinds);

        VK.Api.call('database.getCountries', {}, function(response) {
        	knockoutBinds.vmSearchYou.vk.availableCountries(response.response);
        });
	</script>
	
	<script type="text/javascript">
		//	init input mask
	    jQuery(document).ready(function() {
        	$(".date-search").mask('99:99 99/99/9999', {placeholder:'X'});
	    });

	    //	init slider
	   	$(".slider-search").slider({
	   		scale: 'logarithmic',
	   	});

	   	//	update slider value on drag/click events
		$("#slider-search-age").on("slide slideStop", function(slideEvt) {
			$("#slider-search-age .slider-search-age-from").text(slideEvt.value[0]);
			$("#slider-search-age .slider-search-age-to").text(slideEvt.value[1]);
		});
		$("#slider-search-growth").on("slide slideStop", function(slideEvt) {
			$("#slider-search-growth .slider-search-growth-from").text(slideEvt.value[0]);
			$("#slider-search-growth .slider-search-growth-to").text(slideEvt.value[1]);
		});

		$('#search-city-lists-btn').on('click', function() {
			$('#search-city-lists').toggle();
		});
	</script>

	<script type="text/javascript">
	$(function() {
		rhjcjdjr.searchYou = new SearchYou();
		rhjcjdjr.searchYouMap = new SearchYouMap();
	});
	</script>

	<script type="text/javascript">
	$(function() {
		var searchYou = rhjcjdjr.searchYou;
		var searchYouMap = rhjcjdjr.searchYouMap;

		loadGoogleMapScript(function(google) {
			var googleMap = searchYouMap.createGoogleMap(google, 'map-canvas');
							searchYouMap.initGoogleMap(googleMap);
							searchYouMap.displayDrawTools(googleMap);
		});
	});
	</script>

	<script type="text/javascript">
	$(function() {
		$('#search-you-btn-send').on('click', function(event) {
			event.preventDefault();

			var searchYou = rhjcjdjr.searchYou;
			var searchYouMap = rhjcjdjr.searchYouMap;

			var data = searchYou.grab();

			$.ajax({
				url: 'ajax/search/new',
				data: data,
				method: 'post',
			})
			.done(function(response) {
				log(response);
			})
			.fail(function(response) {
				log('Error while requesting "ajax/search/new"');
			});
		});
	});
	</script>
@endpush


@section("content")

<script src="//vk.com/js/api/openapi.js" type="text/javascript"></script>
<script type="text/javascript">
VK.init({
	apiId: {{ $vk_app_id }}
});
</script>

<!-- google/open street maps canvas -->
<div class="row">
	<div class="col-md-12">
		<div id="map-canvas" style=""></div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
    <form id="" class="sky-form">
        <header>Уточнение: [на карте тык на треугольничек и выделяй область и потом жми "Отправить" и потом выделенную область можно увидеть на "Мои поиски"]</header>

        <fieldset>
            <section>
            	<label class="label">Промежуток времени (когда заметил(а) девушку/парня):</label>
				<div class="row" id="date-search-box">
					<div class="col-md-6 col-sm-6 col-xs-12">
		                <label class="input">
		                    <i class="icon-append fa fa-calendar"></i>
		                    <input type="text" name="date" class="date-search" placeholder="Примерно с">
		                </label>
					</div>
					<div class="col-md-6 col-sm-6 col-xs-12">
		                <label class="input">
		                    <i class="icon-append fa fa-calendar"></i>
		                    <input type="text" name="date" class="date-search" placeholder="До">
		                </label>
					</div>
				</div>
            </section>

            <section>
        	<div class="row">
        		<div class="col-md-3">
					<div class="dropdown">
						<label class="label">Регион:</label>
						<button style="padding:9px;" class="btn btn-default dropdown-toggle" type="button" id="search-region-country-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
						<span style="padding-right:4px;">Выберите страну...</span>
						<span class="caret"></span>
						</button>
						<ul class="dropdown-menu search-select-region" data-bind="foreach: {data: vmSearchYou.vk.availableCountries}">
							<li>
								<a href="#" data-bind="attr: {value: cid}, text: title, click: knockoutBinds.vmSearchYou.vk.onClickCountry.bind($data, $root);"></a>
							</li>
						</ul>
					</div>
        		</div>
				<div class="col-lg-5">
					<label class="label">Город:</label>
					<div class="input-group">
						
		                <label class="input">
		                    <input disabled id="search-region-city-inp" type="text" name="date" class="" placeholder="Начните вводить название города..." data-bind="textInput: knockoutBinds.vmSearchYou.vk.onTypingCity($element, $data);">
		                </label>

						<ul id="search-city-lists" class="dropdown-menu dropdown-menu-right search-select-region" data-bind="foreach: {data: vmSearchYou.vk.availableCities, as: 'city'}">
							<li>
								<a href="#" data-bind="attr: {value: cid}, click: knockoutBinds.vmSearchYou.vk.onClickCity.bind($data, $root);">
									<!-- ko if: city.important -->
									<span style="font-weight:bold;" data-bind="attr: {value: cid}, text: city.title"></span>
									<!-- /ko -->
									<!-- ko ifnot: city.important -->
									<span data-bind="attr: {value: cid}, text: city.title"></span>
									<!-- /ko -->
									<div class="search-region-title" data-bind="text: city.region"></div>
								</a>
							</li>
						</ul>

						<div class="input-group-btn">
							<button style="height:34px; margin-top:-4px;" type="button" id="search-city-lists-btn" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
								<span class="caret"></span>
							</button>
						</div>

					</div>
				</div>
        		<div class="col-md-4">
        		</div>
        	</div>
            </section>

            <section>
                <label class="label">Кого ищу:</label>
                <div class="btn-group" data-toggle="buttons" id="search-who">
					<label class="btn btn-default">
						<input type="radio" name="options" id="" value="1" autocomplete="off">Девушку
					</label>
					<label class="btn btn-default">
						<input type="radio" name="options" id="" value="2" autocomplete="off">Парня
					</label>
				</div>
            </section>

            <section id="slider-search-age">
                <label class="label">Возраст - от: <span class="slider-search-age-from">20</span>, до: <span class="slider-search-age-to">24</span></label>
					
				<input id="" class="slider-search" type="text" value="" data-slider-min="14" data-slider-max="101" data-slider-step="1" data-slider-value="[20,24]"/>
            </section>

            <section id="slider-search-growth">
                <label class="label">Рост - от: <span class="slider-search-growth-from">168</span>, до: <span class="slider-search-growth-to">172</span></label>
					
				<input id="" class="slider-search" type="text" value="" data-slider-min="160" data-slider-max="190" data-slider-step="1" data-slider-value="[168,172]"/>
            </section>

            <section class="textarea">
                <label class="label">Комментарий:</label>
                <textarea id="search-you-comment" name="options" placeholder="Подробности...." class="form-control chat-input" style="font-size:14px; resize:none;"></textarea>
            </section>

        </fieldset>
        <!-- /fieldset -->

        <footer>
            <button type="submit" class="btn btn-primary" id="search-you-btn-send">Отправить</button>
            <button type="button" class="btn btn-secondary" onclick="/*window.history.back();*/">Отмена</button>
        </footer>

    </form>
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
