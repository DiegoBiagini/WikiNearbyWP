$(document).ready(function() {
	//Variables passed through WP
    var lat = locData.latitude;
	var lon = locData.longitude;
	var kmRange = locData.km_range;
	var preLoad = locData.pre_load;
	var showCoord = locData.show_coord;

	
    var div = document.getElementById("wkn-nearby-wrap");
	
	// If showcoord is off hide coordinates
	if(showCoord == false){
		console.log("hallo");
		$("#wkn-my-coords").css("display", "none"); 

	}

	callWikiApi({
		"latitude": lat, 
		"longitude": lon }
	);
    

	
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

        var my_coords = document.getElementById("wkn-my-coords");

        var url = "https://en.wikipedia.org/w/api.php";
        
        $.ajax({
            url: url,
            type: "POST",
            dataType: "jsonp",
            data:
                {
                    "action": "query",
                    "prop": "coordinates|pageimages|description",
					"exintro" : "1",
                    "inprop": "url",
                    "pithumbsize": 150,
                    "generator": "geosearch",
                    "ggsradius": 1000 * kmRange,
                    "ggslimit": 10,
                    "ggscoord":  position.latitude + "|" + position.longitude,
                    "format": "json"
                },
            
            success: function(response){
                        var result = response["query"].pages;

                        var key_value = Object.keys(result);
                        var title, image, coords, distance;
                        var container, image_container;
                
                        if(Object.keys(result).length == 0) {
                           div.innerHTML += "The request returned 0 places.";
                        }

                        for(var i=0; i < Object.keys(result).length; i++) {
							// Create container for a single near location
                            container = document.createElement("div");
                            image_container = document.createElement("div");
                            title = document.createElement("h3");
                            image = document.createElement("img");
                            coords = document.createElement("h4");
                            distance = document.createElement("h5");
							
							
                            container.setAttribute("id","wkn-postid-" + key_value[i]);
                            image_container.classList.add("wkn-img-container");
                            div.appendChild(container);
                            container.appendChild(image_container);
                            image_container.appendChild(image);
                            container.appendChild(title);
                            container.appendChild(coords);
                            container.appendChild(distance);
							
							
							// Add the queried values 
							
                            title.innerHTML += result[key_value[i]]["title"];
                            coords.innerHTML += "(" + result[key_value[i]]["coordinates"]["0"]["lat"] +"," + result[key_value[i]]["coordinates"]["0"]["lon"] + ")";
                            coords.classList.add("wkn-postid-coords");
                            
							if(showCoord == true)
								coords.style.display = "block";
							else
								coords.style.display = "none";
							
                            distance.innerHTML += calcDistance(lat, lon, result[key_value[i]]["coordinates"]["0"]["lat"], result[key_value[i]]["coordinates"]["0"]["lon"]).toFixed(1) + " Km.";
							
                            
                            if(result[key_value[i]].hasOwnProperty("thumbnail")){
                                image.setAttribute("src",result[key_value[i]]["thumbnail"]["source"]);
                            } else {
                                image.setAttribute("src","assets/empty.png");
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


