<script>
    var cargus_url = '{$smarty.const.__PS_BASE_URI__}';

    var CARGUS_API_STREETS = '{$CARGUS_API_STREETS nofilter}';
    var CARGUS_API_CITIES = '{$CARGUS_API_CITIES nofilter}';

</script>
<script type="text/javascript" src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/cargus_postalcode.js"></script>
<script>
    $(function () {
        $('#content').removeClass('nobootstrap').addClass('bootstrap');


    });
</script>
<style>
    label {
        width: 220px;
        font-weight: normal;
        padding: 0.4em 0.3em 0 0;
    }

    input[type="text"] {
        margin-bottom: 3px;
        width: 250px;
    }

    #edit_form span {
        color: #666;
        font-size: 11px;
        line-height: 0;
    }

    #content.bootstrap .panel .panel-heading-nav {
        border-bottom: 0;
        padding: 10px 0 0;
    }

    .panel-heading-nav .nav {
        padding-left: 10px;
        padding-right: 10px;
    }

    .bootstrap .nav-tabs {
        border-bottom: 1px solid #ddd;
    }

    .bootstrap .panel .panel-footer .btn i {
        display: inherit;
    }

    .bootstrap .nav-tabs li a {
        text-transform: unset;
    }

    .bootstrap .btn-flex {
        display: flex;
        align-items: center;
    }
</style>


<div class="entry-edit">
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> Editare AWB comanda nr. {$data.order_id}</div>
            <br /><br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Expeditor</div>
            <div class="form-group">
                <input type="hidden" name="id_country" value="36" />

                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Punct de ridicare</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=pickup_id options=$pickups selected=$pickupsSelect class='filter center' style='width:200px; float:left;'}
                </div>
            </div>
            <br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Destinatar</div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Nume destinatar</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="name" value="{$data.name}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Judet</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=id_state options=$states selected=$statesSelect class='filter center' style='width:200px; float:left;'}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Localitate</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="city" autocomplete="off" value="{$data.locality_name}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Adresa de livrare</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="address">{$data.address}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Cod postal</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="postal_code">{$data.postal_code}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Persoana de contact</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="contact" value="{$data.contact}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Telefon</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="phone" value="{$data.phone}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Email</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="email" value="{$data.email}" />
                </div>
            </div>



            <br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Detalii AWB</div>
            {*<div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Plicuri</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="envelopes" value="{$data.envelopes}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Colete</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="parcels" value="{$data.parcels}" />
                </div>
            </div>*}
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare matinala</span>
                </label>
                <div class="col-lg-4">
                    <select name="delivery_time" id="delivery_time">
                        <option value="0" {if $data.delivery_time == 0}selected="selected"{/if}>Nu</option>
                        <option value="10" {if $data.delivery_time == 10}selected="selected"{/if}>10</option>
                        <option value="12" {if $data.delivery_time == 12}selected="selected"{/if}>12</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare sambata</span>
                </label>
                <div class="col-lg-4 saturday_delivery"> <!-- Add your custom class here -->
                    {html_options name=saturday_delivery options=$yesNo selected=$yesNoSambata}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Valoare declarata</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="value" value="{$data.value}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs numerar</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="cash_repayment" value="{$data.cash_repayment}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs cont colector</span>
                </label>
                <div class="col-lg-4">
                    <input type="text" name="bank_repayment" value="{$data.bank_repayment}" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Ramburs alt tip</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="other_repayment">{$data.other_repayment}</textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Platitor expeditie</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=payer options=$payers selected=$payerSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Deschidere colet</span>
                </label>
                <div class="col-lg-4">
                    {html_options name=openpackage options=$yesNo selected=$yesNoDeschidereColet}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Observatii</span>
                </label>
                <div class="col-lg-4">
                    <textarea name="observations">{$data.observations}</textarea>
                </div>
            </div>

            <br />
            <div class="panel-heading" style="padding-left: 16.66667%; margin-left: 0;">Detalii Colet(e)</div>

            <div class="panel panel-default">
                <div class="panel-heading panel-heading-nav">
                    <ul class="nav nav-tabs">
                        {foreach from=$dataparcels key=index item=row}
                            <li role="presentation" class="{if $index eq 0}active{/if}">
                                <a href="#tab{$index}" aria-controls="tab{$index}" role="tab" data-toggle="tab">{$index+1} - {if $row.expedition_type eq 'parcel'}Colet{else}Plic{/if}</a>
                            </li>
                        {/foreach}
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        {foreach from=$dataparcels key=index item=row}
                            <div role="tabpanel" class="tab-pane fade {if $index eq 0}in active{/if}" id="tab{$index}">
                                <input type="hidden" name="cid[{$index}]" value="{$row.id}" />
                                <input type="hidden" name="pudo_location_id[{$index}]" value="{$row.pudo_location_id}" />
                                <div class="form-group">
                                    <label class="control-label col-lg-2">
                                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip expeditie</span>
                                    </label>
                                    <div class="col-lg-4">
                                        {html_options name="expedition_type[$index]" options=$expeditie selected=$row.expedition_type}
                                    </div>
                                </div>
                                <div class="form-group">

                                    <label class="control-label col-lg-2">
                                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Greutate</span>
                                    </label>
                                    <div class="col-lg-4">
                                        <input type="text" name="weight[{$index}]" value="{$row.weight}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">
                                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Lungime</span>
                                    </label>
                                    <div class="col-lg-4">
                                        <input type="text" name="length[{$index}]" value="{$row.length}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">
                                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Latime</span>
                                    </label>
                                    <div class="col-lg-4">
                                        <input type="text" name="width[{$index}]" value="{$row.width}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">
                                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Inaltime</span>
                                    </label>
                                    <div class="col-lg-4">
                                        <input type="text" name="height[{$index}]" value="{$row.height}" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-lg-2">
                                        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Continut</span>
                                    </label>
                                    <div class="col-lg-4">
                                        <textarea name="contents[{$index}]">{$row.contents}</textarea>
                                    </div>
                                </div>
                                {if $index neq 0}
                                    <button
                                            type="submit"
                                            name="delete"
                                            value="{$row.id}"
                                            class="btn btn-danger btn-flex"
                                            onclick="return confirm('Sigur vrei sa stergi coletul?');">
                                        <i class="material-icons">remove_circle_outline</i>&nbsp; Sterge {if $row.expedition_type eq 'parcel'}colet{else}plic{/if}
                                    </button>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>

            <div class="panel-footer">

                <button type="submit" name="submit" value="submit" class="btn btn-default pull-right">
                    <i class="material-icons">save</i>&nbsp; Salveaza
                </button>
                {if $allowMultiPiece}
                    <button type="submit" name="add" value="add" class="btn btn-warning pull-right">
                        <i class="material-icons">add_circle_outline</i>&nbsp; Adauga colet
                    </button>
                {/if}
            </div>
        </div>
    </form>
</div>