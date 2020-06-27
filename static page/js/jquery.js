$(function() {
    
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
                        console.log(JSON.stringify(response));
                    },
            error: function(error){
                        console.log(error);
                    }
        });
    }
    
});


