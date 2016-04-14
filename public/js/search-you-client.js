/* search-you-client.js  */

function SearchYou() {

	this.placeCoords = [],


	//	grabs input
	this.grab = function() {
		var created_polygons,
			growth_from,
			growth_to,
			date_from,
			date_to,
			age_from,
			age_to,
			sex;

		//	who to search
		sex = $("#search-who input:radio[name ='options']:checked").val();

		//	date of search
		date_from = $("#date-search-box div:first input").val();
		date_to = $("#date-search-box div:last input").val();

		//	person age
		var age_raw = $("#slider-search-age input[data-value]").val().split(',');
		
		if (age_raw.length == 2) {
			age_from = age_raw[0];
			age_to = age_raw[1];
		}

		//	person growth
		var growth_raw = $("#slider-search-growth input[data-value]").val().split(',');
		
		if (growth_raw.length == 2) {
			growth_from = growth_raw[0];
			growth_to = growth_raw[1];
		}
		
		//	created polygons (on Google map)
		created_polygons = rhjcjdjr.searchYouMap.getCreatedPolygonsCoords();

		//	comment
		var comment = $('#search-you-comment').val();

		return {
			sex: sex,
			age: {age_from: age_from, age_to: age_to},
			date: {date_from: date_from, date_to: date_to},
			growth: {growth_from: growth_from, growth_to: growth_to},
			comment: comment,
			polygons: JSON.stringify(created_polygons),
		};
	},


	//	search requests created by current user
	this.mineSearches = function(options, doneCb, failCb) {
		$.ajax({
			url: 'ajax/search/mine',
			data: {},
			method: 'post',
		})
		.done(function(response) {
			doneCb && doneCb(response);
		})
		.fail(function(response) {
			failCb && failCb(response);
		});
	}
};