<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yandex Harita Testi</title>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=4f7e8177-d95e-4f85-a6d9-3ba6a3c4edd5&lang=tr_TR" type="text/javascript"></script>
</head>
<body>
    <div id="map" style="width: 600px; height: 400px;"></div>

    <script type="text/javascript">
        ymaps.ready(init);
        
        function init() {
            var map = new ymaps.Map("map", {
                center: [41.0082, 28.9784], // İstanbul koordinatları
                zoom: 10
            });
            
            // Örnek bir marker ekleyelim
            var marker = new ymaps.Placemark([41.0082, 28.9784], {
                hintContent: 'İstanbul!'
            });
            map.geoObjects.add(marker);
        }
    </script>
</body>
</html>