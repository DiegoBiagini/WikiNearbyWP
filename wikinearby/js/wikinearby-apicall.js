$(document).ready(function() {
	//Variables passed through WP
    var lat = data.latitude;
	var lon = data.longitude;
	var kmRange = data.km_range;
	var preLoad = data.pre_load;
	var showCoord = data.show_coord;
	
	var call=true;
	
    var div = document.getElementById("wkn-nearby-wrap");
    
	// If showcoord is off hide coordinates
	if(showCoord == false){
		$("#wkn-my-coords").css("display", "none"); 
	}

	callWikiApi({
		"latitude": lat, 
		"longitude": lon }
	);
    
    function showError(error) {
      switch(error.code) {
        case error.PERMISSION_DENIED:
            div.innerHTML = "User denied the request for Geolocation."
          break;
        case error.POSITION_UNAVAILABLE:
            div.innerHTML = "Location information is unavailable."
          break;
        case error.TIMEOUT:
            div.innerHTML = "The request to get user location timed out."
          break;
        case error.UNKNOWN_ERROR:
            div.innerHTML = "An unknown error occurred."
          break;
      }
    }
    
    function calcDistance(lat1, lon1, lat2, lon2){
        // Set all variables for the haversine forumla
        var rad = 6371e3; //Earth radius in meter
        var phi1 = lat1 * Math.PI/180;
        var phi2 = lat2 * Math.PI/180;
        var delta1 = (lat2-lat1) * Math.PI/180;
        var delta2 = (lon2-lon1) * Math.PI/180;
        
        // Calculate all constant that we need for the haversine formula
        var a = Math.sin(delta1/2) * Math.sin(delta1/2) + Math.cos(phi1) * Math.cos(phi2) * Math.sin(delta2/2) * Math.sin(delta2/2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var res = rad * c;
        
        // Final value is on meter so we return res/1000 to have the result in km
        return res/1000;
    }

    
    function callWikiApi(position){
        if(call){
            call = false;
            lat = position.latitude;
            lon = position.longitude;


            var my_coords = document.getElementById("wkn-my-coords");
            my_coords.innerHTML += "X : " + lat + "  Y : " + lon;
            var url = "https://en.wikipedia.org/w/api.php";

            $.ajax({
                url: url,
                type: "POST",
                dataType: "jsonp",
				
                data:
                    {
                        "action": "query",
						"list":"geosearch",
                        "pithumbsize": 150,
                        "gsradius": 10000,
                        "gslimit": 12,
                        "gscoord":  lat + "|" + lon,
                        "format": "json"
                    },
					

                success: function(response){
							var result = response["query"].geosearch;
							console.log(result)
                            var key_value = Object.keys(result);
                            var title, image, coords, distance;
                            var container, image_container, card_body, card_footer, carousel_active, carousel,divRow;
                            var container_indicators, indicators_ol;
                            var indicator = 0;

							indicators_ol = document.createElement("ol");
							indicators_ol.classList.add("carousel-indicators");
							indicators_ol.setAttribute("id","wkn-carousel-indicators");

                            container_indicators = document.getElementById("carousel-indexs");
                            container_indicators.appendChild(indicators_ol);
							
                            if(Object.keys(result).length == 0) {
                               div.innerHTML += "The request retun 0 places.";
                            }
							console.log(Object.keys(result).length)
							
							var i = 0
							for (var r in result){
								console.log( result[r] );
								var entry = result[i]
								
								if((i % 6) == 0){
									carousel = document.createElement("div");
									indicators = document.createElement("li");
									divRow = document.createElement("div");
									
									carousel.classList.add("carousel-item");
									divRow.classList.add("row");
									indicators.setAttribute("data-target","#carousels");
									
									carousel.appendChild(divRow);
									div.appendChild(carousel);
									
									
                                    indicators_ol.appendChild(indicators);
                                    indicators.setAttribute("data-slide-to",""+indicator);
									
									indicator++;
									indicators.setAttribute("id","wkn-indicator");
                                    if(indicator == 1){
                                        indicators.classList.add("active");
										carousel.classList.add("carousel-item","active");
                                    }
                                } 
								
								container = document.createElement("div");
                                card_container = document.createElement("div");
                                image_container = document.createElement("div");
                                card_body = document.createElement("div");
                                card_footer = document.createElement("div");
                                title = document.createElement("h3");
                                image = document.createElement("img");
                                coords = document.createElement("h5");
                                distance = document.createElement("h5");
								
								container.setAttribute("id","wkn-postid");
                                //container.classList.add("col-md-4","col-lg-4","col-sm-12"); //BOOTSTRAP
								container.classList.add("col-sm-4","d-flex"); //BOOTSTRAP
                                card_container.classList.add("card","card-body","flex-fill");
								card_container.setAttribute("id","wkn-card-container");
								
								// BUILD CARD 
                                image_container.setAttribute("id","wkn-card-image");
                                card_body.classList.add("card-body");
                                title.classList.add("card-title");
                                card_footer.classList.add("card-footer");
								card_footer.setAttribute("id","wkn-card-footer");
                                distance.classList.add("text-center");
                                // END CARD 
								
								container.appendChild(card_container);
                                image_container.appendChild(image);
                                card_body.appendChild(title);
                                card_body.appendChild(coords);
                                card_footer.appendChild(distance);
                                card_container.appendChild(image_container);
                                card_container.appendChild(card_body);
                                card_container.appendChild(card_footer);							
								
                                title.innerHTML += entry["title"];
								shortened_lat = ("" + entry["lat"]).substring(0,10)
								shortened_lon = ("" + entry["lon"]).substring(0,10)
                                coords.innerHTML += "(" + shortened_lat +"," + shortened_lon + ")";
                                coords.setAttribute("id","wkn-postid-coords");

                                distance.innerHTML += calcDistance(lat, lon, entry["lat"], entry["lon"]).toFixed(1) + " Km.";
								
								$.ajax({
									url: url,
									image_element: image,
									type: "POST",
									dataType: "jsonp",
									
									data:
										{
											"action": "query",
											"pageids":entry["pageid"],
											"pithumbsize": 150,
											"prop": "pageimages",

											"format": "json"
										},
									success: function(response){
										entry = response["query"]["pages"];
										entry = entry[Object.keys(entry)[0]]
										//console.log(entry[Object.keys(entry)[0]])
										if (entry.hasOwnProperty("thumbnail")){
											thumbnail = entry["thumbnail"]["source"]
											console.log(thumbnail)
											this.image_element.setAttribute("src",thumbnail);
										}
										else
											this.image_element.setAttribute("src","image/image.png");
									}
								});
								

								divRow.appendChild(container);
								
								i++;
							}

	
                        },

                error: function(error){
                            console.log(error);
                            div.innerHTML("Unknown error during the request.");
                        }
            });
        }
    }
    
	
    $('#carousels').carousel({
        interval : false,
        wrap : false,
        keyboard : false
    });
	
	
	$('#carousels').on('slide.bs.carousel', function () {
        $holder = $( ".carousel-indicators li.active" );
        $holder.next( "li" ).addClass("active");
            if($holder.is(':last-child')) {
                $holder.removeClass("active");
                $(".carousel-indicators li:first").addClass("active");
            }
        $holder.removeClass("active");
    });
    
});


