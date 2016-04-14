/* search-you.js */

function loadGoogleMapScript(cb) {
	//	if already exists
	if (window.google && google.maps) {
		log('Google map script attempted to be loaded twice. Cancel loading, return loaded.');
		cb(window.google);
		return;
	}
	$.getScript('https://www.google.com/jsapi', function() {
	    google.load('maps', '3', { other_params: 'sensor=false&libraries=drawing', callback: function() {
	    	log('Google map script has been loaded.');
	    	cb(google);
	    }});
	});
}

function drawPolygonsFromArray(map, array) {
	$.each(array, function(i, coordsOfCurrentPolygon) {
		new google.maps.Polygon({
			map: map,
			paths: coordsOfCurrentPolygon,
			strokeColor: '#FF0000',
			strokeOpacity: 0.8,
			strokeWeight: 2,
			fillColor: '#FF0000',
			fillOpacity: 0.35,
			draggable: false,
			geodesic: true
		});
	});
}

function getPolygonCoords(polygon) {
	var coordinates = (polygon.getPath().getArray());
	var coordinatesCollection = [];

	for(var coord in coordinates)
	{
		var latitude = coordinates[coord].lat();
		var longitude = coordinates[coord].lng();

		coordinatesCollection.push({
			lat: latitude,
			lng: longitude,
		});
	}

	return coordinatesCollection;
}

function googleMaps2JTS(boundaries) {
	var coordinates = [];
	for (var i = 0; i < boundaries.getLength(); i++) {
		coordinates.push(
			new jsts.geom.Coordinate(
				boundaries.getAt(i).lat(),
				boundaries.getAt(i).lng()
			)
		);
	}
	return coordinates;
}

function jts2googleMaps(geometry) {
	var coordArray = geometry.getCoordinates();
		var returnRes = [];
		for (var i = 0; i < coordArray.length; i++) {
		returnRes.push({
			lat: coordArray[i].x,
			lng: coordArray[i].y
		});
	}
	return returnRes;
}



function SearchYouMap(canvas) {

	this.googleMap = null,
	this.drawingManager = null,
	this.createdPolygons = [],


	this.getCreatedPolygons = function() {
		return this.createdPolygons;
	},

	this.getCreatedPolygonsCoords = function() {
		created_polygons = this.getCreatedPolygons();
		created_polygons_coords = [];

		$.each(created_polygons, function(i, polygon) {
			var coords = getPolygonCoords(polygon);
			created_polygons_coords.push(coords);
		});

		return created_polygons_coords;
	},

	//	display map
	this.displayDrawTools = function() {
		this.drawingManager.setMap(this.googleMap);
	},

	//	init google map
	this.initGoogleMap = function() {
		var self = this;

		//	google artist
		var drawingManager = new google.maps.drawing.DrawingManager({
			//	drawingMode: google.maps.drawing.OverlayType.POLYGON,
			drawingControl: true,
			drawingControlOptions: {
			position: google.maps.ControlPosition.TOP_CENTER,
			drawingModes: [
				//	google.maps.drawing.OverlayType.MARKER,
				//	google.maps.drawing.OverlayType.RECTANGLE
				//	google.maps.drawing.OverlayType.CIRCLE,
				google.maps.drawing.OverlayType.POLYGON,
			]},
			polygonOptions: {
				fillColor: '#D8E4E8',
				fillOpacity: .38,
				strokeWeight: 3,
				clickable: true,
				editable: true,
				zIndex: 1
			}
		});

		//	polygon done (drawn)
		google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon)
		{
			self.createdPolygons.push(polygon);

			//	add closing coord (closed polygon)
			polygon.getPath().push(polygon.getPath().getAt(0));

			var coordinates = (polygon.getPath().getArray());
			
			google.maps.event.addListener(polygon.getPath(), 'set_at', function() {
				log('Google maps "set_at" event fired. (Polygon has been changed.)');
			});

			google.maps.event.addListener(polygon.getPath(), 'insert_at', function() {
				log('Google maps "insert_at" event fired. (Polygon has been changed.)');
			});

			for(var coord in coordinates)
			{
				var latitude = coordinates[coord].lat();
				var longitude = coordinates[coord].lng();

				log('{' + latitude + ',' + longitude + '},');
			}
		});

		self.drawingManager = drawingManager;
	},

	//	create google map
	this.createGoogleMap = function(google, selectorId)
	{
		var self = this;

		if ( ! google) {
			log('Google object expected in "createGoogleMap"'); return;
		}

		if ( ! selectorId) {
			log('DOM Selector ID expected in "createGoogleMap"'); return;
		}

		//	to where load map
		var element = document.getElementById(selectorId);

		if ( ! element) {
			log('DOM Selector ID (' + selectorId + ') was not found in "createGoogleMap"'); return;
		}

		var map = new google.maps.Map(element, {
			//	zhukovka (32 reg)
			center: new google.maps.LatLng(53.5342118, 33.72796297),
			zoom: 14,
			mapTypeId: "OSM",
			mapTypeControl: false,
			streetViewControl: false
		});

		//	define OSM map type pointing at the OpenStreetMap tile server
		map.mapTypes.set("OSM", new google.maps.ImageMapType({
			getTileUrl: function(coord, zoom) {
				var tilesPerGlobe = 1 << zoom;
				var x = coord.x % tilesPerGlobe;
				if (x < 0) {
					x = tilesPerGlobe+x;
				}
				
				return "http://tile.openstreetmap.org/" + zoom + "/" + x + "/" + coord.y + ".png";
			},
			tileSize: new google.maps.Size(256, 256),
			name: "OpenStreetMap",
			maxZoom: 20
		}));
		  
		//	latitude: 53.54025638597097;  longitude: 33.72480869293213
		//	latitude: 53.535104510968736; longitude: 33.71785640716553
		//	latitude: 53.536736856043184; longitude: 33.75047206878662
		  
		  
		  
		/*
			var coords = [
				{lat: 53.536736856043184, lng: 33.75047206878662},
				{lat: 53.535104510968736, lng: 33.71785640716553},
				{lat: 53.54025638597097,  lng: 33.72480869293213},
			];
			
			new google.maps.Polygon({
				map: searchMapGoogle,
				paths: coords,
				strokeColor: '#FF0000',
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: '#FF0000',
				fillOpacity: 0.35,
				draggable: false,
				geodesic: true
			});
		*/

		self.googleMap = map;
	},

	this.getMap = function() {
		return this.googleMap;
	}
}

$(document).ready(function() {

	var googleMap = {
		
		polygonCollection: [],


		addPolygon: function(p) {
			this.polygonCollection.push(p);
		},

		showPolygonDebug: function() {
			if (this.polygonCollection.length < 1) return;
				var polygon = this.polygonCollection[0];

			log(googleMaps2JTS(polygon.getPath()));

			var geometryFactory = new jsts.geom.GeometryFactory();
			var tritoCoor = googleMaps2JTS(polygon.getPath());
			var shell = geometryFactory.createLinearRing(tritoCoor);
			var jstsPolygon = geometryFactory.createPolygon(shell);
		},

		showPolygon: function() {

			$.each(this.polygonCollection, function(i, polygon) {
				log('Current polygon: ');
				var coordinates = polygon.getPath().getArray();
				for(var coord in coordinates)
				{
					var latitude = coordinates[coord].lat();
					var longitude = coordinates[coord].lng();

					console.log(latitude, longitude);
				}
			});

			if (this.polygonCollection.length < 2) return;

			var geometryFactory = new jsts.geom.GeometryFactory();

			var trito = this.polygonCollection[0].getPath();
			var tritoCoor = googleMaps2JTS(trito);
			var shell = geometryFactory.createLinearRing(tritoCoor);

			var trito2 = this.polygonCollection[1].getPath();
			var tritoCoor2 = googleMaps2JTS(trito2);
			var shell2 = geometryFactory.createLinearRing(tritoCoor2);



			var jstsPolygon = geometryFactory.createPolygon(shell);
			var jstsPolygon2 = geometryFactory.createPolygon(shell2);

			var intersection = jstsPolygon.intersection(jstsPolygon2);
			var intersection2 = jstsPolygon2.intersection(jstsPolygon);

			log('Intersections of those two are:');
			log(intersection);
			log(intersection2);

			var intersectionCoords = jts2googleMaps(intersection2);

			new google.maps.Polygon({
				map: searchMapGoogle,
				paths: intersectionCoords,
				strokeColor: '#FF0000',
				strokeOpacity: 0.8,
				strokeWeight: 2,
				fillColor: '#FF0000',
				fillOpacity: 0.35,
				draggable: false,
				geodesic: true
			});

		}
	};

});
