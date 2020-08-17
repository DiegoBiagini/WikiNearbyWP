$(function() {
    
    var lat, lon, max_dist;
    
    var call = true;
    
    var div = document.getElementById("wkn-nearby-wrap");
    
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(callWikiApi, showError);
    } else { 
        div.innerHTML = "Geolocation is not supported by this browser.";
    }
    
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
            var positions = 
                {
                    "latitude" : position.coords.latitude,
                    "longitude" : position.coords.longitude
                };   

            lat = position.coords.latitude;
            lon = position.coords.longitude;


            var my_coords = document.getElementById("wkn-my-coords");
            my_coords.innerHTML += "X : " + positions.latitude + "  Y : " + positions.longitude;
            var url = "https://en.wikipedia.org/w/api.php";

            $.ajax({
                url: url,
                type: "POST",
                dataType: "jsonp",
                data:
                    {
                        "action": "query",
                        "prop": "coordinates|pageimages|description",
                        "inprop": "url",
                        "pithumbsize": 150,
                        "generator": "geosearch",
                        "ggsradius": 10000,
                        "ggslimit": 10,
                        "ggscoord":  positions.latitude + "|" + positions.longitude,
                        "format": "json"
                    },

                success: function(response){
                            var result = response["query"].pages;
                            var key_value = Object.keys(result);
                            var title, image, coords, distance;
                            var container, image_container, card_body, card_footer, carousel_active, carousel;
                            var container_indicators, indicators_ol;
                            var indicator = 0;

                            carousel = document.createElement("div");
                            carousel_active = document.createElement("div");

                            carousel_active.classList.add("carousel-item","active");
                            carousel.classList.add("carousel-item");

                            div.appendChild(carousel_active);
                            div.appendChild(carousel);
                                 
                            container_indicators = document.getElementById("carousel-indexs");
                            
                            if(Object.keys(result).length == 0) {
                               div.innerHTML += "The request retun 0 places.";
                            }

                            for(var i=0; i < Object.keys(result).length; i++) {

                                container = document.createElement("div");
                                card_container = document.createElement("div");
                                image_container = document.createElement("div");
                                card_body = document.createElement("div");
                                card_footer = document.createElement("div");
                                title = document.createElement("h3");
                                image = document.createElement("img");
                                coords = document.createElement("h5");
                                distance = document.createElement("h5");
                                
                                indicators_ol = document.createElement("ol");
                                indicators = document.createElement("li");

                                container.setAttribute("id","wkn-postid" /*+ key_value[i]*/);
                                container.classList.add("col-md-4","col-lg-4","col-sm-12"); //BOOTSTRAP
                                card_container.classList.add("card");
                                image_container.classList.add("wkn-card-image");
                                card_body.classList.add("card-body");
                                title.classList.add("card-title");
                                card_footer.classList.add("card-footer");
                                distance.classList.add("text-center");
                                
                                indicators_ol.classList.add("carousel-indicators");
                                indicators.setAttribute("data-target","carousel");
                                
                                //div.appendChild(container);
                                container.appendChild(card_container);
                                image_container.appendChild(image);
                                card_body.appendChild(title);
                                card_body.appendChild(coords);
                                card_footer.appendChild(distance);
                                card_container.appendChild(image_container);
                                card_container.appendChild(card_body);
                                card_container.appendChild(card_footer);
                                if(i < 6){
                                    carousel_active.appendChild(container);
                                }else {
                                    carousel.appendChild(container);
                                }
                               
                                title.innerHTML += result[key_value[i]]["title"];
                                coords.innerHTML += "(" + result[key_value[i]]["coordinates"]["0"]["lat"] +"," + result[key_value[i]]["coordinates"]["0"]["lon"] + ")";
                                coords.classList.add("wkn-postid-coords");

                                distance.innerHTML +=calcDistance(lat, lon, result[key_value[i]]["coordinates"]["0"]["lat"], result[key_value[i]]["coordinates"]["0"]["lon"]).toFixed(1) + " Km.";

                                if(result[key_value[i]].hasOwnProperty("thumbnail")){
                                    image.setAttribute("src",result[key_value[i]]["thumbnail"]["source"]);
                                } else {
                                    image.setAttribute("src","image/image.png");
                                }
                                if((i % 6) == 0){
                                    container_indicators.appendChild(indicators_ol);
                                    indicators_ol.appendChild(indicators);
                                    indicators.setAttribute("data-slide-to",""+indicator);
                                    if(indicator == 0){
                                        indicators.classList.add("active");
                                    }
                                    indicator++;
                                }
                                    
                            }
                            container_indicators.appendChild(indicators_ol);
                        },

                error: function(error){
                            console.log(error);
                            div.innerHTML("Unknown error during the request.");
                        }
            });
        }
    }
    
    $('.carousel').carousel({
        interval : false,
        wrap : false,
        keyboard : false
    });
    
});



