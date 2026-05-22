
<script>
    var CARGUS_SHIP_GO_CARRIER_ID       = '{$CARGUS_SHIP_GO_CARRIER_ID}';
    var UPDATE_SHIP_GO_LOCATION_LINK    = '{$UPDATE_SHIP_GO_LOCATION_LINK nofilter}';
    var CARGUS_API_PUDOPOINTS           = '{$CARGUS_API_PUDOPOINTS nofilter}';
    var cargus_url                      = '{$smarty.const.__PS_BASE_URI__}';
</script>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap">

<link href="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/css/widget/all.min.css" rel="stylesheet">
<link href="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/css/widget/CargusWidget.css" rel="stylesheet">
<link href="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/css/widget/leaflet.min.css" rel="stylesheet">

<div class="cargus-map-widget" id="shipgomap-modal" style="display: none;"></div>

<div class="position-relative mx-auto">
    <button type="button" class="btn btn-primary position-relative start-50 translate-middle-x" id="cargus-open-map-btn">Alege cel mai apropiat Ship&Go!</button>
</div>

<div class="alert alert-warning d-none location-alert mt-2 mr-2" role="alert"></div>
<div class="alert alert-danger d-none location-error mt-2 mr-2" role="alert">
    A aparut o problema la salvarea locatiei. Incercati din nou!
</div>

<button type="submit" class="btn btn-primary float-xs-right d-none continue-extra w-50 mb-2 m-auto" name="confirmDeliveryOption" value="1">
    Continue
</button>


<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/ship_and_go.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/fuse.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/bootstrap.bundle.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/hyperlist.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/leaflet.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/leaflet-canvas-markers.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/lodash.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/src/libraries/turf.min.js"></script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/cargusWidget.js"></script>