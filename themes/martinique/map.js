var vectorLayer;
var invisibleFeatureStyle = new ol.style.Style({
    image: new ol.style.Circle({
        radius: 0,
        fill: new ol.style.Fill({color: 'red'}),
        stroke: new ol.style.Stroke({color: 'yellow', width: 1})
    })
})
function init_osm_box(divName) {      
    var pageFeatures = [];

    for (var i = 0; i < page_locations.length ; i++) {
        var point = page_locations[i];
        var page = new ol.Feature({
            geometry: pointToLonlat(point),
            name: point.name,
            uid: point.uid,
            attributes: {"icon": point.main_type, "content":pointToContent(point)}
        })

        pageFeatures.push(page)
    }

    var vectorSource = new ol.source.Vector({features: pageFeatures})
    
    vectorLayer = new ol.layer.Vector({
        source: vectorSource,
        visible: true,
        style: function(feature, resolution) {
            var featureStyle = new ol.style.Style({
                image: new ol.style.Icon(/** @type {olx.style.IconOptions} */ ({
                    anchor: [0.5, 0.5],
                    anchorOrigin :'bottom-left',
                    /*size : [100,100],*/

                    scale: 0.05,
                    anchorXUnits: 'fraction',
                    anchorYUnits: 'pixels',
                    opacity: 1,
                    src: "/themes/martinique/picto/" + feature.get("attributes")["icon"] + ".png"
                }))
            });
            feature.get("attributes")["style"] = featureStyle;

            return [featureStyle];
        }
    });
    
    var map = new ol.Map({
        controls: ol.control.defaults({
            attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
                collapsible: false
            })
        }),
        target: document.getElementById(divName),
        view: new ol.View({
            center: [0, 0],
            zoom: 3
        }),
        layers: [
            new ol.layer.Group({
                'title': 'Base maps',
                layers: [
                    new ol.layer.Tile({
                        title: 'Water color',
                        type: 'base',
                        visible: true,
                        source: new ol.source.Stamen({
                            layer: 'watercolor'
                        })
                    }),
                    new ol.layer.Tile({
                        title: 'OSM',
                        type: 'base',
                        visible: false,
                        source: new ol.source.OSM()
                    }),

                ]
            }),
            vectorLayer
        ]
      });
    /* zoom to all dives */
    var extent = vectorLayer.getSource().getExtent();
    map.getView().fit(extent, map.getSize());

    /* add layer switch control */
    map.addControl(new ol.control.LayerSwitcher({
        tipLabel: 'Layer' // Optional label for button
    }));

    /* prepare popups*/
    var element = document.getElementById('popup');

    var popup = new ol.Overlay({
        element: element,
        positioning: 'bottom-center',
        stopEvent: false
    });
    map.addOverlay(popup);
    
    var visible_feature = null;
    // display popup on click
    map.on('click', function(evt) {
        var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');

        var feature = map.forEachFeatureAtPixel(evt.pixel,
            function(feature, layer) {
                return feature;
            });

        
        $(element).popover('destroy');

        if (feature && visible_feature != feature) {
            var geometry = feature.getGeometry();
            var coord = geometry.getCoordinates();
            
            popup.setPosition(coord);
            
            $(element).popover({
                'placement': 'bottom',
                'html': true,
                'offset': '50px 50px',
                'content': feature.get('attributes')["content"]
            });
            
            $(element).popover('show');
            $(".wall-post:not(.head_map)").addClass("map-hidden"); 
            $("#"+feature.get('uid')).removeClass("map-hidden");
            visible_feature = feature;
        } else {
            visible_feature = null;
            $(".wall-post:not(.head_map)").removeClass("map-hidden"); 
        }
        update_visible_posts_features(pageFeatures);
    });

    // change mouse cursor when over marker
    $(map.getViewport()).on('mousemove', function(e) {
        var pixel = map.getEventPixel(e.originalEvent);
        var hit = map.forEachFeatureAtPixel(pixel, function(feature, layer) {
            return true;
        });
        map.getTarget().style.cursor = hit ? 'pointer' : '';
    });

    map.on("moveend", function(e) {
        if (visible_feature) return;
        var extent = map.getView().calculateExtent(map.getSize());
        $(".wall-post:not(.head_map)").removeClass("map-visible");
        var show_all = true;
        for (var i = 0; i < page_locations.length; i++) {
            var point = page_locations[i];
            
            if (ol.extent.containsCoordinate(extent, pointToLonlat(point).getCoordinates())) {
                $("#"+point.uid).addClass("map-visible");
            } else {
                show_all = false;
            }
        }
        if (show_all) {
            $(".wall-post").addClass("map-visible");
        }
        update_visible_posts_features(pageFeatures);
    })

    return pageFeatures;
}
function pointToLonlat(point) {

    return new ol.geom.Point(ol.proj.transform([parseFloat(point.lat), 
                                                parseFloat(point.lon)], 'EPSG:4326', 'EPSG:3857'))
}

function pointToContent(point) {
    var ret = "<div class='map_content'>\n"
        +"  <h3>"+point.name+"</h3>\n";
    ret += "<p>";
    for (tp in point.types) {
        var name = point.types[tp]
        ret += "  <img width='25px' title='"+name+
            "' src='/themes/martinique/picto/"+name+".png' alt='"+name+"'/>" ;
    }
    ret += "</p>";
    ret += "<p>"+point.abstract+"</p>\n";
    ret += "</div>";
    return ret;

}

var tagSelected = null;
function init_tag_selectors(pageFeatures) {
    $(".wall-post").addClass("type-visible")

    $(".tag_selector").click(function() {
        var tagname = $(this).attr('alt');

        var showAll = tagname == tagSelected;
        
        $("img.cat").removeClass("type_selected")

        if (!showAll) {
            $("img.cat_"+tagname).addClass("type_selected")
        }
        $(".wall-post:not(.head_map)").each(function() {
            $(this).toggleClass("type-visible",
                                showAll || $(this).find("img.cat_"+tagname).length != 0);
        })
        
        update_visible_posts_features(pageFeatures);
        
        tagSelected = showAll ? null : tagname;
    });
}
function update_visible_posts_features(pageFeatures) {
    $(pageFeatures).each(function(pos, feature) {
        if ($("#"+feature.get("uid")).is(".type-visible.map-visible")) {
            feature.setStyle(feature.get("attributes")["style"]);
        } else {
            feature.setStyle(invisibleFeatureStyle);
        }
    });
    vectorLayer.changed()

    $(".wall-post").hide();
    $(".wall-post.type-visible.map-visible:not(.map-hidden)").show();
    
}

$(document).ready(function() {
    var pageFeatures = init_osm_box('page_map')
    init_tag_selectors(pageFeatures);
});
