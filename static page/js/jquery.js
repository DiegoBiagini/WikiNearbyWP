$(document).ready(function() {
    
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
                        var keyValue = Object.keys(result);
                        var title, image, coords;
                        var container, imageContainer;
                
                        if(Object.keys(result).length == 0) {
                           div.innerHTML += "The request retun 0 places.";
                        }

                        for(var i=0; i < Object.keys(result).length; i++) {
                            container = document.createElement("div");
                            imageContainer = document.createElement("div");
                            title = document.createElement("h3");
                            image = document.createElement("img");
                            coords = document.createElement("h4");
                            container.setAttribute("id","wkn-postid-" + keyValue[i]);
                            imageContainer.classList.add("wkn-img-container");
                            div.appendChild(container);
                            container.appendChild(imageContainer);
                            imageContainer.appendChild(image);
                            container.appendChild(title);
                            container.appendChild(coords);
                            title.innerHTML += result[keyValue[i]]["title"];
                            coords.innerHTML += "X : " + result[keyValue[i]]["coordinates"]["0"]["lat"];
                            coords.innerHTML += "  Y : " + result[keyValue[i]]["coordinates"]["0"]["lon"];
                            if(result[keyValue[i]].hasOwnProperty("thumbnail")){
                                image.setAttribute("src",result[keyValue[i]]["thumbnail"]["source"]);
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


