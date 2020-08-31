$(document).ready(function() {
    //Variables passed through WP
    var lat = data.latitude;
    var lon = data.longitude;
    var show_coord = data.show_coord;

    var my_coords = document.getElementById("wkn-my-coords");
    my_coords.innerHTML += "(" + lat + "," + lon + ")";


    var div = document.getElementById("wkn-nearby-wrap");

    // If show_coord is off hide coordinates
    if (show_coord == false) {
        $("#wkn-my-coords").css("display", "none");
    }

    $('#carousels').carousel({
        interval: false,
        wrap: false,
        keyboard: true
    });


    $('#carousels').on('slide.bs.carousel', function() {
        $holder = $(".carousel-indicators li.active");
        $holder.next("li").addClass("active");
        if ($holder.is(':last-child')) {
            $holder.removeClass("active");
            $(".carousel-indicators li:first").addClass("active");
        }
        $holder.removeClass("active");
    });
	

});


function calcDistance(lat1, lon1, lat2, lon2) {
    // Set all variables for the haversine forumla
    var rad = 6371e3; //Earth radius in meter
    var phi1 = lat1 * Math.PI / 180;
    var phi2 = lat2 * Math.PI / 180;
    var delta1 = (lat2 - lat1) * Math.PI / 180;
    var delta2 = (lon2 - lon1) * Math.PI / 180;

    // Calculate all constant that we need for the haversine formula
    var a = Math.sin(delta1 / 2) * Math.sin(delta1 / 2) + Math.cos(phi1) * Math.cos(phi2) * Math.sin(delta2 / 2) * Math.sin(delta2 / 2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    var res = rad * c;

    // Final value is on meter so we return res/1000 to have the result in km
    return res / 1000;
}


function callWikiApi(position) {
    //Variables passed through WP
    var km_range = data.km_range;
    var plugin_path = data.plugin_path
    var show_coord = data.show_coord;

    var load_button = document.getElementById("wkn-req-button");
    var places_div = document.getElementById("wkn-nearby-place");

    load_button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';


    var div = document.getElementById("wkn-nearby-wrap");

    call = false;
    lat = position.latitude;
    lon = position.longitude;


    var url = "https://en.wikipedia.org/w/api.php";

    $.ajax({
        url: url,
        type: "POST",
        dataType: "jsonp",

        data: {
            "action": "query",
            "list": "geosearch",
            "pithumbsize": 150,
            "gsradius": 1000 * km_range,
            "gslimit": 12,
            "gscoord": lat + "|" + lon,
            "format": "json"
        },


        success: function(response) {
            var result = response["query"].geosearch;
            var key_value = Object.keys(result);
            var title, image, coords, distance;
            var container, image_container, card_body, card_footer, carousel_active, carousel, divRow;
            var container_indicators, indicators_ol;
            var indicator = 0;

            indicators_ol = document.createElement("ol");
            indicators_ol.classList.add("carousel-indicators");
            indicators_ol.setAttribute("id", "wkn-carousel-indicators");

            container_indicators = document.getElementById("carousel-indexs");
            container_indicators.appendChild(indicators_ol);

            if (Object.keys(result).length == 0) {
                div.innerHTML += "The request retun 0 places.";
            }

            var i = 0
            for (var r in result) {
                var entry = result[i]

                if ((i % 6) == 0) {
                    carousel = document.createElement("div");
                    indicators = document.createElement("li");
                    divRow = document.createElement("div");

                    carousel.classList.add("carousel-item");
                    divRow.classList.add("row");
                    indicators.setAttribute("data-target", "#carousels");

                    carousel.appendChild(divRow);
                    div.appendChild(carousel);


                    indicators_ol.appendChild(indicators);
                    indicators.setAttribute("data-slide-to", "" + indicator);

                    indicator++;
                    indicators.setAttribute("id", "wkn-indicator");
                    if (indicator == 1) {
                        indicators.classList.add("active");
                        carousel.classList.add("carousel-item", "active");
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

                container.setAttribute("id", "wkn-postid");
                //container.classList.add("col-md-4","col-lg-4","col-sm-12"); //BOOTSTRAP
                container.classList.add("col-sm-4", "d-flex"); //BOOTSTRAP
                card_container.classList.add("card", "card-body", "flex-fill");
                card_container.setAttribute("id", "wkn-card-container");

                // BUILD CARD 
                image_container.setAttribute("id", "wkn-card-image");
                card_body.classList.add("card-body");
                card_body.setAttribute("id", "wkn-card-body");
                title.classList.add("card-title");
                title.setAttribute("id", "wkn-card-title");
                card_footer.classList.add("card-footer");
                card_footer.classList.add("card-footer");
                card_footer.setAttribute("id", "wkn-card-footer");
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

                title.innerHTML += "<a href='https://en.wikipedia.org/wiki?curid=" + entry["pageid"] + "'>" + entry["title"] + "</a>";
                if (show_coord != false) {
                    shortened_lat = ("" + entry["lat"]).substring(0, 7)
                    shortened_lon = ("" + entry["lon"]).substring(0, 7)
                    coords.innerHTML += "(" + shortened_lat + "," + shortened_lon + ")";
                    coords.setAttribute("id", "wkn-postid-coords");
                }


                distance.innerHTML += calcDistance(lat, lon, entry["lat"], entry["lon"]).toFixed(1) + " Km.";

                $.ajax({
                    url: url,
                    image_element: image,
                    type: "POST",
                    dataType: "jsonp",

                    data: {
                        "action": "query",
                        "pageids": entry["pageid"],
                        "pithumbsize": 150,
                        "prop": "pageimages",
                        "format": "json"
                    },
                    success: function(response) {
                        entry = response["query"]["pages"];
                        entry = entry[Object.keys(entry)[0]]
                        if (entry.hasOwnProperty("thumbnail")) {
                            thumbnail = entry["thumbnail"]["source"]
                            this.image_element.setAttribute("src", thumbnail);
                        } else
                            this.image_element.setAttribute("src", plugin_path + "/assets/image.png");
                    }
                });


                divRow.appendChild(container);

                i++;
            }
            load_button.style.display = "none";
            // Transition in
            var $places_div = $("#wkn-nearby-place");
            $places_div.addClass("wkn-block").outerWidth();
            $places_div.addClass("wkn-fadein");
			
			$("#carousels").collapse("show");
			
			$('#wkn-collapse-btn').on('click', function() {
				$fa_el = $(this).find("i");
				if ($fa_el.hasClass("fa-caret-down")){
					$fa_el.removeClass("fa-caret-down");
					$fa_el.addClass("fa-caret-up");
				}
				else{
					$fa_el.removeClass("fa-caret-up");
					$fa_el.addClass("fa-caret-down");
				}
			});
        },

        error: function(error) {
            console.log(error);
            load_button.innerHTML("Unknown error during the request.");
        }
    });
}