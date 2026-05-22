<script>
var cargus_url = '{$smarty.const.__PS_BASE_URI__}';

var CARGUS_API_STREETS = '{$CARGUS_API_STREETS nofilter}';
var CARGUS_API_CITIES = '{$CARGUS_API_CITIES nofilter}';

</script>
<script type="text/javascript" src="{$smarty.const._PS_JS_DIR_}jquery/jquery-{$smarty.const._PS_JQUERY_VERSION_}.min.js"></script>

<script type="text/javascript" src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/cargus_postalcode.js"></script>

<script>
    $(document).ready(function () {
        {if isset($CARGUS_SHIP_GO_CARRIER_ID) }
            var CARGUS_SHIP_GO_CARRIER_ID = '{$CARGUS_SHIP_GO_CARRIER_ID}';

            // Add more modules values here
            let codArray = [
                'cashondelivery',
                'cod',
                'ramburs',
                'cash',
                'numerar',
                'livrare'
            ];

            for (const codName of codArray) {
                var inputId = $("input[data-module-name*= '" + codName + "']").attr("id");
                $("input[data-module-name*= '" + codName + "']").prop('disabled', true);
                $('label[for=' +  inputId  +'] > span').addClass('disabled');

            }
        {/if}
    });
</script>
