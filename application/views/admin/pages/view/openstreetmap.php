<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draw Lines on Map</title>
    <!-- Include Leaflet and Leaflet.draw -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <style>
        #map {
            height: 500px;
            /* Adjust map height */
        }
    </style>
</head>

<body>
    <div id="map"></div>
    <script>
        // Initialize the map
        var map = L.map('map').setView([51.505, -0.09], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Initialize the feature group to store drawn items
        var drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Add drawing toolbar
        var drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems
            },
            draw: {
                polyline: true, // Enable drawing lines
                polygon: false,
                circle: false,
                rectangle: false,
                marker: false,
                circlemarker: false
            }
        });
        map.addControl(drawControl);

        // Listen for the 'draw:created' event
        map.on(L.Draw.Event.CREATED, function(event) {
            var layer = event.layer; // The drawn layer
            var type = event.layerType; // The type of shape drawn (e.g., polyline)

            // Add the layer to the map
            drawnItems.addLayer(layer);

            if (type === 'polyline') {
                // Get the coordinates of the polyline
                var coordinates = layer.getLatLngs();

                // Log coordinates to the console
                console.log("Line coordinates:", coordinates);

                // Example: Send coordinates to server using AJAX
                // (Uncomment and update the URL for your server endpoint)
                /*
                $.post("http://your-server-endpoint", {
                    coordinates: coordinates
                }, function(response) {
                    console.log("Server Response:", response);
                });
                */
            }
        });
    </script>
</body>

</html>