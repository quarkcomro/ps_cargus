<script>
    $(function () {
        $('#content').removeClass('nobootstrap').addClass('bootstrap');
    });
</script>

<style>
    .icon-AdminUrgentCargus:before {
        content: "\f0d1";
    }

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
</style>

<div class="entry-edit">
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post"
          action="">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> Preferinte modul Cargus</div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_PUNCT_RIDICARE">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Punctul de ridicare</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_PUNCT_RIDICARE name=CARGUS_PUNCT_RIDICARE options=$pickups selected=$pickupsSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_EXPEDITOR_LOCATION_ID">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Latime"
                          data-html="true">LocationID Expeditor</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_EXPEDITOR_LOCATION_ID" name="CARGUS_EXPEDITOR_LOCATION_ID" value="{$CARGUS_EXPEDITOR_LOCATION_ID}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_SM_CARRIER_ID">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="CARGUS_SM_CARRIER_ID"
                          data-html="true">SM Carrier ID</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_SM_CARRIER_ID" name="CARGUS_SM_CARRIER_ID" value="{$CARGUS_SM_CARRIER_ID}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_ASIGURARE">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Asigurare expeditie</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_ASIGURARE name=CARGUS_ASIGURARE options=$yesNo selected=$yesNoAsiguare}
                </div>
            </div>
{*            <div class="form-group">*}
{*                <label class="control-label col-lg-2" for="CARGUS_SAMBATA">*}
{*                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare sambata</span>*}
{*                </label>*}
{*                <div class="col-lg-10">*}
{*                    {html_options id=CARGUS_SAMBATA name=CARGUS_SAMBATA options=$yesNo selected=$yesNoSambata}*}
{*                </div>*}
{*            </div>*}
{*            <div class="form-group">*}
{*                <label class="control-label col-lg-2" for="CARGUS_DIMINEATA">*}
{*                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Livrare dimineata</span>*}
{*                </label>*}
{*                <div class="col-lg-10">*}
{*                    {html_options id=CARGUS_DIMINEATA name=CARGUS_DIMINEATA options=$yesNo selected=$yesNoDimineata}*}
{*                </div>*}
{*            </div>*}
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_DESCHIDERE_COLET">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Deschidere colet</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_DESCHIDERE_COLET name=CARGUS_DESCHIDERE_COLET options=$yesNo selected=$yesNoDeschidereColet}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_TIP_RAMBURS">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip ramburs</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_TIP_RAMBURS name=CARGUS_TIP_RAMBURS options=$ramburs selected=$rambursSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_PLATITOR">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Alegeti cine plateste costul serviciului de transport catre Cargus"
                          data-html="true">Platitor expeditie</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_PLATITOR name=CARGUS_PLATITOR options=$platitor selected=$platitorSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_TIP_EXPEDITIE">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="" data-html="true">Tip expeditie</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_TIP_EXPEDITIE name=CARGUS_TIP_EXPEDITIE options=$expeditie selected=$expeditieSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_TRANSPORT_GRATUIT">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Daca totalul cosului depaseste suma in lei introdusa, transportul va fi gratuit"
                          data-html="true">Limita transport gratuit</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_TRANSPORT_GRATUIT" name="CARGUS_TRANSPORT_GRATUIT" value="{$transport}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_COST_FIX">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Modulul nu va mai calcula dinamic costul transportului si va fi afisata suma in lei introdusa"
                          data-html="true">Cost fix transport</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_COST_FIX" name="CARGUS_COST_FIX" value="{$cost}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_SERVICIU">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Serviciu"
                          data-html="true">Serviciu</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_SERVICIU name=CARGUS_SERVICIU options=$serviciuOptions selected=$serviciuSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_ID_TARIF">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Id tarif"
                          data-html="true">Id tarif</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_ID_TARIF" name="CARGUS_ID_TARIF" value="{$id_tarif}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_GREUTATE">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Greutate"
                          data-html="true">Greutate</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_GREUTATE" name="CARGUS_GREUTATE" value="{$greutate}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_LUNGIME">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Lungime"
                          data-html="true">Lungime</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_LUNGIME" name="CARGUS_LUNGIME" value="{$lungime}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_LATIME">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Latime"
                          data-html="true">Latime</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_LATIME" name="CARGUS_LATIME" value="{$latime}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_INALTIME">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Inaltime"
                          data-html="true">Inaltime</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_INALTIME" name="CARGUS_INALTIME" value="{$inaltime}"/>
                </div>
            </div>
            {if $canUseAutocomplete}
            <div class="form-group">
                <label class="control-label col-lg-2" for="cargus_preferinte_postal_codes">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Completeaza automat codul postal in baza adresei introduse"
                          data-html="true">Nomenclator strazi/coduri postale</span>
                </label>
                <div class="col-lg-10">
                    <div>
                        <label>
                            <input
                                    type="checkbox"
                                    name="CARGUS_POSTALCODE"
                                    id="cargus_preferinte_postal_codes"
                                    value="1"
                                    {if $postal_code} checked="checked" {/if}
                                    >
                        </label>
                    </div>
                </div>
            </div>
            {/if}
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_PACKAGE_CONTENT_TEXT">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Daca acest camp este gol atunci continutul cosului de cumparaturi este trimis ca text in continutul pachetului.<br>Daca clientul are nevoie de confidentialitate, introduceti un text personalizat aici."
                          data-html="true"
                    >Text predefinit pentru continutul pachetului</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_PACKAGE_CONTENT_TEXT" name="CARGUS_PACKAGE_CONTENT_TEXT" value="{$package_content_text}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_AWB_RETUR">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title=""
                          data-html="true">Retur cumparator</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_AWB_RETUR name=CARGUS_AWB_RETUR options=$awbReturOptions selected=$awbReturSelect}
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_AWB_RETUR_VALIDITATE">
                    <span>
                    Validitate (zile)
                    </span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_AWB_RETUR_VALIDITATE" name="CARGUS_AWB_RETUR_VALIDITATE" value="{$awb_retur_validitate}"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_PRINT_AWB_RETUR">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title=""
                          data-html="true">Print AWB retur</span>
                </label>
                <div class="col-lg-10">
                    {html_options id=CARGUS_PRINT_AWB_RETUR name=CARGUS_PRINT_AWB_RETUR options=$printAwbReturOptions selected=$printAwbRetur}
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_SATURDAY_DELIVERY">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title="Optiune curier livrare sambata"
                          data-html="true">Curier Ship on Saturday</span>
                </label>
                <div class="col-lg-10">
                    <div>
                        <label>
                            <input
                                    type="checkbox"
                                    name="CARGUS_SATURDAY_DELIVERY"
                                    id="CARGUS_SATURDAY_DELIVERY"
                                    value="1"
                                    {if $CARGUS_SATURDAY_DELIVERY} checked="checked" {/if}
                                    >
                        </label>
                    </div>
                </div>
            </div>
            {if $CARGUS_SATURDAY_DELIVERY}
            <div class="form-group">
                <label class="control-label col-lg-2" for="CARGUS_SATURDAY_DELIVERY_TARIF">
                    <span title="" data-toggle="tooltip" class="label-tooltip"
                          data-original-title=""
                          data-html="true"
                    >Pret curier Ship on Saturday</span>
                </label>
                <div class="col-lg-10">
                    <input type="text" id="CARGUS_SATURDAY_DELIVERY_TARIF" name="CARGUS_SATURDAY_DELIVERY_TARIF" value="{$CARGUS_SATURDAY_DELIVERY_TARIF}"/>
                </div>
            </div>
{*                <div class="form-group">*}
{*                    <label class="control-label col-lg-2" for="CARGUS_SATURDAY_MESAJ">*}
{*                        Mesaj afisare curier*}
{*                    </label>*}
{*                    <div class="col-lg-10">*}
{*                        <input type="text" id="CARGUS_SATURDAY_MESAJ" name="CARGUS_SATURDAY_MESAJ" value="{$CARGUS_SATURDAY_MESAJ}" class="form-control"/>*}
{*                    </div>*}
{*                </div>*}
            {/if}
            
            <div class="form-group">
            <label class="control-label col-lg-2" for="CARGUS_PRE10_DELIVERY">
            <span title="" data-toggle="tooltip" class="label-tooltip"
                data-original-title="Optiune curier livrare pre10"
                data-html="true">Curier pre10 Shipment</span>
            </label>
            <div class="col-lg-10">
                <div>
                    <label>
                        <input
                                type="checkbox"
                                name="CARGUS_PRE10_DELIVERY"
                                id="CARGUS_PRE10_DELIVERY"
                                value="1"
                                {if $CARGUS_PRE10_DELIVERY} checked="checked" {/if}
                                >
                    </label>
                </div>
            </div>
        </div>

            {if $CARGUS_PRE10_DELIVERY}
                <div class="form-group">
                    <label class="control-label col-lg-2" for="CARGUS_PRE10_DELIVERY_TARIF">
        <span title="" data-toggle="tooltip" class="label-tooltip"
              data-original-title=""
              data-html="true"
        >Pret curier pre10</span>
                    </label>
                    <div class="col-lg-10">
                        <input type="text" id="CARGUS_PRE10_DELIVERY_TARIF" name="CARGUS_PRE10_DELIVERY_TARIF" value="{$CARGUS_PRE10_DELIVERY_TARIF}" class="form-control"/>
                    </div>
                </div>
{*                <div class="form-group">*}
{*                    <label class="control-label col-lg-2" for="CARGUS_PRE10_MESAJ">*}
{*                        Mesaj afisare curier*}
{*                    </label>*}
{*                    <div class="col-lg-10">*}
{*                        <input type="text" id="CARGUS_PRE10_MESAJ" name="CARGUS_PRE10_MESAJ" value="{$CARGUS_PRE10_MESAJ}" class="form-control"/>*}
{*                    </div>*}
{*                </div>*}
            {/if}

        <div class="form-group">
            <label class="control-label col-lg-2" for="CARGUS_PRE12_DELIVERY">
            <span title="" data-toggle="tooltip" class="label-tooltip"
                data-original-title="Optiune curier livrare pre12"
                data-html="true">Curier pre12 Shipment</span>
            </label>
            <div class="col-lg-10">
                <div>
                    <label>
                        <input
                                type="checkbox"
                                name="CARGUS_PRE12_DELIVERY"
                                id="CARGUS_PRE12_DELIVERY"
                                value="1"
                                {if $CARGUS_PRE12_DELIVERY} checked="checked" {/if}
                                >
                    </label>
                </div>
            </div>
        </div>

        {if $CARGUS_PRE12_DELIVERY}
        <div class="form-group">
            <label class="control-label col-lg-2" for="CARGUS_PRE12_DELIVERY">
                <span title="" data-toggle="tooltip" class="label-tooltip"
                        data-original-title=""
                        data-html="true"
                >Pret curier pre12</span>
            </label>
            <div class="col-lg-10">
                <input type="text" id="CARGUS_PRE12_DELIVERY" name="CARGUS_PRE12_DELIVERY_TARIF" value="{$CARGUS_PRE12_DELIVERY_TARIF}"/>
            </div>
        </div>
{*        <div class="form-group">*}
{*            <label class="control-label col-lg-2" for="CARGUS_PRE12_MESAJ">*}
{*                Mesaj afisare curier*}
{*            </label>*}
{*            <div class="col-lg-10">*}
{*                <input type="text" id="CARGUS_PRE12_MESAJ" name="CARGUS_PRE12_MESAJ" value="{$CARGUS_PRE12_MESAJ}" class="form-control"/>*}
{*            </div>*}
{*        </div>*}
        {/if}

        <div class="form-group">
            <label class="control-label col-lg-2" for="CARGUS_SHIPGO_DELIVERY">
            <span title="" data-toggle="tooltip" class="label-tooltip"
                data-original-title="Optiune curier livrare pre12"
                data-html="true">Curier SHIP&GO</span>
            </label>
            <div class="col-lg-10">
                <div>
                    <label>
                        <input
                                type="checkbox"
                                name="CARGUS_SHIPGO_DELIVERY"
                                id="CARGUS_SHIPGO_DELIVERY"
                                value="1"
                                {if $CARGUS_SHIPGO_DELIVERY} checked="checked" {/if}
                                >
                    </label>
                </div>
            </div>
        </div>

        {if $CARGUS_SHIPGO_DELIVERY}
        <div class="form-group">
            <label class="control-label col-lg-2" for="CARGUS_SHIPGO_DELIVERY">
                <span title="" data-toggle="tooltip" class="label-tooltip"
                        data-original-title=""
                        data-html="true"
                >Pret SHIP&GO </span>
            </label>
            <div class="col-lg-10">
                <input type="text" id="CARGUS_SHIPGO_DELIVERY_TARIF" name="CARGUS_SHIPGO_DELIVERY_TARIF" value="{$CARGUS_SHIPGO_DELIVERY_TARIF}"/>
            </div>
        </div>
{*            <div class="form-group">*}
{*                <label class="control-label col-lg-2" for="CARGUS_SHIPGO_MESAJ">*}
{*                    Mesaj afisare curier*}
{*                </label>*}
{*                <div class="col-lg-10">*}
{*                    <input type="text" id="CARGUS_SHIPGO_MESAJ" name="CARGUS_SHIPGO_MESAJ" value="{$CARGUS_PRE10_MESAJ}" class="form-control"/>*}
{*                </div>*}
{*            </div>*}
        {/if}
        
            <div class="panel-footer">
                <button type="submit" name="submit" value="submit" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> Salveaza
                </button>
            </div>
        </div>
    </form>
</div>