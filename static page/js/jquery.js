$(document).ready(function() {
    
    var lat, lon, max_dist;
    
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
    
    function callWikiApi(position){
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
                        var title, image, coords;
                        var container, image_container;
                
                        if(Object.keys(result).length == 0) {
                           div.innerHTML += "The request retun 0 places.";
                        }

                        for(var i=0; i < Object.keys(result).length; i++) {
                            container = document.createElement("div");
                            image_container = document.createElement("div");
                            title = document.createElement("h3");
                            image = document.createElement("img");
                            coords = document.createElement("h4");
                            container.setAttribute("id","wkn-postid-" + key_value[i]);
                            image_container.classList.add("wkn-img-container");
                            div.appendChild(container);
                            container.appendChild(image_container);
                            image_container.appendChild(image);
                            container.appendChild(title);
                            container.appendChild(coords);
                            title.innerHTML += result[key_value[i]]["title"];
                            coords.innerHTML += "X : " + result[key_value[i]]["coordinates"]["0"]["lat"];
                            coords.innerHTML += "  Y : " + result[key_value[i]]["coordinates"]["0"]["lon"];
                            coords.classList.add("wkn-postid-coords");
                            if(result[key_value[i]].hasOwnProperty("thumbnail")){
                                image.setAttribute("src",result[key_value[i]]["thumbnail"]["source"]);
                            } else {
                                image.setAttribute("src","image/image.png");
                            }
                        }
                    },
            
            error: function(error){
                        console.log(error);
                        div.innerHTML("Unknown error during the request.");
                    }
        });
    }
    
});


