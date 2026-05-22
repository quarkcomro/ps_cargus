
const DEFAULT_COORDINATES = {
    latitude: 44.442137062756885,
    longitude: 26.09464970813823,
};
const KEY_MAPPING_VALUES = false
let data_endpoint = CARGUS_API_PUDOPOINTS;
var assets_path = '/modules/cargus/views/js/assets/icons';

function closeModal() {
    var shipgomap = document.getElementById("shipgomap-modal");
    if (shipgomap) {
        shipgomap.style.display = "none";     
        // Clear everything inside the modal
        shipgomap.innerHTML = "";
    }
    return true;
}

// What to do when choosing a location

function ChooseMarker(selectedPoint) {
    handleLocationChange(selectedPoint);
    closeModal();
    return false;
}

function handleLocationChange(location) {
    $.ajax({
        type: "POST",
        url: UPDATE_SHIP_GO_LOCATION_LINK,
        cache: false,
        data: JSON.stringify({location_id: location.Id}),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(data){
            checkAndRenderContinue(data, location);
        },
        error: function(data) {
            checkAndRenderContinue(data, location);
        }
    });
}

function checkAndRenderContinue(data, location) {
    var continueButton      = $('button.continue:visible');
    var continueButtonExtra = $('button.continue-extra');
    var locationInfoAlert = $('.location-alert');
    var locationErrorAlert = $('.location-error');
    if (!data.responseText.includes('ERROR')) {
        locationInfoAlert.html(createAlertHTML(location));
        locationInfoAlert.removeClass('d-none');
        continueButton.prop('disabled', false);
        continueButtonExtra.removeClass('d-none');
        locationErrorAlert.addClass('d-none');
        $('html, body').animate({
            scrollTop: $(".continue-extra").offset().top
        }, 200);
    } else {
        continueButton.prop('disabled', true);
        locationErrorAlert.removeClass('d-none');
    }
}

createAlertHTML = function (location) {
    var locationPaymentsString = 'Locatie aleasa : <b>' + location.Name + '</b><br/>';
    return locationPaymentsString;
}

const WidgetFnParams = {
    ChooseMarker,
    closeModal,
};

const WidgetVarParams = {
    assets_path,
    DEFAULT_COORDINATES,
    KEY_MAPPING_VALUES,
    data_endpoint,
};

function openCargusMap() {
    const modal = document.getElementById("shipgomap-modal");
    modal.style.display = "flex";
    initializeCargus('shipgomap-modal', WidgetFnParams, WidgetVarParams);
}

// Initialize by adding the required DOM elements for Ship & Go.

openCargusMapButton = document.querySelector('#cargus-open-map-btn');

openCargusMapButton.addEventListener("click", (event) => {
    event.preventDefault();
    openCargusMap();
});

$(window).keydown(function(event){
    if(event.keyCode == 13) {
        event.preventDefault();
        return false;
    }
});

$( document ).ready(function() {
    if ((parseInt($("#js-delivery input[type='radio']:checked").val()) == CARGUS_SHIP_GO_CARRIER_ID)) {
        $('button.continue').prop('disabled', true);
    }
});

