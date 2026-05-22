
$(document).ready(function(){
    $.jsonToFormData = function(item) {
        let form_data = new FormData();

        Object.entries(item).forEach(([key, value]) => {
            form_data.append(key, value);
        });

        return form_data;
    };

    $.getJsonData = async function(url, data = {}, method = 'GET') {
        let ct = 'application/json';
        let headers = {
            'Content-Type': ct
        };

        let body = JSON.stringify(data);
        if (method === 'POST') {
            ct = 'multipart/form-data';
            body = $.jsonToFormData(data);
            headers = {};
        }

        const response = await fetch(url, {
            method: method, // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-store',
            credentials: 'same-origin',
            headers: headers,
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'no-referrer',
            body: body
        });

        if (!response.ok) {
            console.error('getJsonData failed: ', response);
            return false;
        }

        try {
            const text = await response.text();
            const data = JSON.parse(text);

            return data;
        } catch (err) {
            console.error('getJsonData error: ', err);
        }
        return false;
    };

    async function getCities(input) {
        //check required field state
        if (
            $(input).length > 0 &&
            $(input).find(':selected').val().length >= 2
        ) {
            let state = $(input).find(':selected').val();

            //get json data
            let result = await $.getJsonData(CARGUS_API_CITIES, {'state': state}, 'POST');

            if (result !== false) {
                return result;
            }
        }

        return [];
    }

    async function filterCities(request, input) {
        let data = await getCities(input);

        let search = request.term.toUpperCase();

        let result = [];
        let i = 0;

        $.each(data, function (key, obj) {
            let name = obj.Name.toUpperCase();

            let $extraKm = (!obj.ExtraKm ? 0 : obj.ExtraKm);
            let $km = (obj.InNetwork ? 0 : $extraKm);

            let extra = {
                'zip': obj.PostalCode,
                'cid': obj.LocalityId,
                'km': $km
            };

            if (name.indexOf(search) != -1) {
                i++;

                result.push({
                    label: obj.Name, // Label for Display
                    value: obj.Name, // Value
                    extra: extra
                });
            }

            if (i > 9) {
                return false;
            }
        });

        return result;
    }

    async function getStreets(input) {
        //check required field city
        if (
            $(input).data("cid") !== undefined &&
            $(input).data("zip").length != 6
        ) {
            let city = $(input).data("cid");

            //get json data
            let result = await $.getJsonData(CARGUS_API_STREETS, {'city': city}, 'POST');

            if (result !== false) {
                return result;
            }
        }

        return [];
    }

    async function filterStreets(request, input, strNr = null) {
        let data = await getStreets(input);

        let search = request.term.toUpperCase();
        let searchNr = 0;

        if (strNr != null) {
            searchNr = parseInt(search);
            search = $(strNr).val().toUpperCase().trim();
        }

        let result = [];
        let i = 0;

        $.each(data, function (key, obj) {
            let name = obj.Name.toUpperCase();

            let extra = null;

            if (obj.PostalNumbers.length == 1) {
                extra = obj.PostalNumbers[0].PostalCode;
            }

            if (name.indexOf(search) != -1) {
                if (strNr === null) {
                    i++;

                    result.push({
                        label: obj.Name, // Label for Display
                        value: obj.Name, // Value
                        extra: extra
                    });
                } else {
                    $.each(obj.PostalNumbers, function(j, objStr) {
                        if (
                            (
                                (parseInt(objStr.FromNo) <= searchNr && searchNr <= parseInt(objStr.ToNo)) ||
                                (parseInt(objStr.FromNo) == parseInt(objStr.ToNo) && parseInt(objStr.ToNo) == 0)
                            ) &&
                            objStr.PostalCode.length == 6
                        ) {
                            result.push({
                                label: obj.Name, // Label for Display
                                value: obj.Name, // Value
                                extra: objStr.PostalCode
                            });

                            i = 10;

                            return false;
                        }
                    });
                }
            }

            if (i > 9) {
                return false;
            }
        });

        return result;
    }


    $(document).on('change', 'input[name="city"]', function() {
        let zip = $(this).data("zip");


        if (zip !== undefined && zip.length == 6) {
            $('input[name="postcode"]').val(zip);
        } else {
            $('input[name="postcode"]').val("");
        }
    });

    $(document).on('keyup keypress', '[name="city"]', function(e) {
        if ($(this).data('ui-autocomplete') === undefined) {
            const that = $(this);

            $(this).autocomplete({
                delay: 350,
                minLength: 1,
                source: async function(request, response) {
                    let result = await filterCities(request, 'select[name="id_state"]');

                    response(result);
                },
                select: function(event, ui) {
                    that.data('cid', ui.item.extra.cid);
                    that.data('zip', ui.item.extra.zip);
                    that.attr('km', ui.item.extra.km);
                    that.trigger('change');
                }
            });

            $(this).autocomplete("search");
        }
    });

    $(document).on('keyup keypress', '[name="street_cargus"]', function(e) {
        if ($(this).data('ui-autocomplete') === undefined) {
            $(this).autocomplete({
                delay: 350,
                minLength: 2,
                source: async function(request, response) {
                    let result = await filterStreets(request, 'input[name="city"]');

                    response(result);
                },
                select: function(event, ui) {
                    if (ui.item.extra != null) {
                        $('input[name="postcode"]').val(ui.item.extra);
                    } else {
                        $('input[name="postcode"]').val("");
                    }
                }
            });

            $(this).autocomplete("search");
        }
    });

    $(document).on('blur', '[name="street_number_cargus"]', async function(){
        if ($('input[name="city"]').data('zip') !== undefined &&
            $('input[name="city"]').data('zip').length == 6
        ) {
            //city has postal code, no need for more searches
            return true;
        }

        let strNr = $(this).val();

        let result = await filterStreets({'term': strNr}, 'input[name="city"]', '[name="street_cargus"]');

        if (result[0] !== undefined && result[0].extra != null) {
            $('input[name="postcode"]').val(result[0].extra);
        } else {
            $('input[name="postcode"]').val("");
        }
    });

});