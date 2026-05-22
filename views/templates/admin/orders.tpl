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

    .bootstrap .btn {
        display: flex;
        align-items: normal;
    }

    .bootstrap .btn.btn-primary i {
        display: block;
        width: 20px;
        height: 20px;
        margin-right: 2px;
        font-size: 16px;
    }
</style>

<div class="entry-edit">
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> AWB-uri in asteptare</div>
            <div class="table-responsive-row clearfix">
                <table class="table order">
                    <thead>
                    <tr class="nodrag nodrop">
                        <th><input style="position:absolute; margin-top:-6px;" type="checkbox"
                                   onclick="$('input[name*=\'selected\']').prop('checked', this.checked);"/></th>
                        <th><span class="title_box active">ID comanda</span></th>
                        <th><span class="title_box active">Punct de ridicare</span></th>
                        <th><span class="title_box active">Nume destinatar</span></th>
                        <th><span class="title_box active">Localitate destinatar</span></th>
                        <th><span class="title_box active">Cod postal</span></th>
                        <th><span class="title_box active">Plicuri</span></th>
                        <th><span class="title_box active">Colete</span></th>
                        <th><span class="title_box active">Greutate</span></th>
                        {*<th><span class="title_box active">Lungime</span></th>
                        <th><span class="title_box active">Latime</span></th>
                        <th><span class="title_box active">Inaltime</span></th>*}
                        <th><span class="title_box active">Ramburs numerar</span></th>
                        <th><span class="title_box active">Ramburs cont colector</span></th>
                        <th><span class="title_box active">Platitor expeditie</span></th>
                        <th><span class="title_box active">Locatie Ship & GO</span></th>
                        <th><span class="title_box active">Livrare Sambata</span></th>
                        <th><span class="title_box active">Livrare Matinala</span></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$lines item=line}
                        <tr>
                            <td class='{cycle name=color values="odd,even"}'>
                                <input style="position:absolute; margin-top:-6px;" type="checkbox" name="selected[]"
                                       value="{$line.order_id}"/>
                            </td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.order_id}</td>
                            <td class='{cycle name=color values="odd,even"}'>{($pickups[$line.pickup_id]) ? ($pickups[$line.pickup_id]) : '-/-'}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.name}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.locality_name|cat:(($line.county_name) ? ', ' : '')|cat:$line.county_name}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.postal_code}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.envelopes}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.parcels}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.weight} kg</td>
                            {*<td class='{cycle name=color values="odd,even"}'>{$line.length} cm</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.width} cm</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.height} cm</td>*}
                            <td class='{cycle name=color values="odd,even"}'>{$line.cash_repayment} lei</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.bank_repayment} lei</td>
                            <td class='{cycle name=color values="odd,even"}'>{($line.payer == 2) ? 'Destinatar' : 'Expeditor'}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.pudo_location_id}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.saturday_delivery}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$line.delivery_time}</td>

                            <td>
                                <div class="btn-group-action">
                                    <div class="btn-group pull-right">
                                        <a href="index.php?controller=CargusEditAwb&token={$token}&id={$line.order_id}"
                                           title="Editare" class="edit btn btn-default">
                                            <i class="icon-pencil"></i> Editare
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            <div class="btn-group bulk-actions dropup">
                <button type="submit" name="submit_valideaza" value="submit" class="btn btn-primary">
                    <i class="icon-plus-sign"></i> Valideaza AWB-urile selectate
                </button>
                <button type="submit" name="submit_sterge" value="submit" class="btn btn-default">
                    <i class="icon-trash"></i> Sterge AWB-urile selectate
                </button>
            </div>
            <script>
                // VALIDEAZA AWB-urile SELECTATE
                $('[name="submit_valideaza"]').click(function () {
                    var coduri = [];
                    $('input[name*=\'selected\']:checked').each(function () {
                        coduri.push($(this).val());
                    });
                    if (coduri.length > 0) {
                        return true;
                    } else {
                        alert('Nu ati selectat niciun AWB pentru validare!');
                    }
                    return false;
                });

                // STERGE AWB-urile SELECTATE
                $('[name="submit_sterge"]').click(function () {
                    var coduri = [];
                    $('input[name*=\'selected\']:checked').each(function () {
                        coduri.push($(this).val());
                    });
                    if (coduri.length > 0) {
                        return true;
                    } else {
                        alert('Nu ati selectat niciun AWB pentru stergere!');
                    }
                    return false;
                });
            </script>
        </div>

        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> AWB-uri validate</div>
            <div class="table-responsive-row clearfix">
                <table class="table order">
                    <thead>
                    <tr class="nodrag nodrop">
                        <th><input style="position:absolute; margin-top:-6px;" type="checkbox"
                                   onclick="$('input[name*=\'awbs\']').prop('checked', this.checked);"/></th>
                        <th><span class="title_box active">ID comanda</span></th>
                        <th><span class="title_box active">Serie AWB</span></th>
                        <th><span class="title_box active">Cost livrare</span></th>
                        <th><span class="title_box active">Nume destinatar</span></th>
                        <th><span class="title_box active">Localitate destinatar</span></th>
                        <th><span class="title_box active">Plicuri</span></th>
                        <th><span class="title_box active">Colete</span></th>
                        <th><span class="title_box active">Greutate</span></th>
                        <th><span class="title_box active">Ramburs numerar</span></th>
                        <th><span class="title_box active">Ramburs cont colector</span></th>
                        <th><span class="title_box active">Platitor expeditie</span></th>
                        <th><span class="title_box active">Status</span></th>
                    </tr>
                    <tr class="nodrag nodrop filter row_hover">
                        <th colspan="13">
                            <div style="float:left; height:31px; line-height:31px; padding-right:5px;">Punctul de
                                ridicare
                            </div>
                            {html_options name=CARGUS_PUNCT_RIDICARE options=$pickups selected=$pickupsSelect class='filter center' style='width:200px; float:left;'}
                            <button type="submit" name="submitPickup" value="submit" class="btn btn-default"
                                    style="float:left; margin-left:5px;">
                                <i class="icon-pencil"></i> Schimba punctul de ridicare implicit
                            </button>
                        </th>
                    </tr>
                    </thead>
                    <tbody>

                    {foreach from=$awbs item=awb}
                        <tr>
                            <td height="38">
                                <input style="position:absolute; margin-top:-6px;" type="checkbox" name="awbs[]"
                                       value="{$awb.BarCode}"/>
                            </td>
                            <td>{$awb.CustomString}</td>
                            <td>{$awb.BarCode}</td>
                            <td>{$awb.ShippingCost.GrandTotal} lei</td>
                            <td>{$awb.Recipient.Name}</td>
                            <td>{$awb.Recipient.LocalityName}</td>
                            <td>{$awb.Envelopes}</td>
                            <td>{$awb.Parcels}</td>
                            <td>{$awb.TotalWeight} kg</td>
                            <td>{$awb.CashRepayment} lei</td>
                            <td>{$awb.BankRepayment} lei</td>
                            <td>{($awb.BarCode == 2) ? 'Destinatar' : 'Expeditor'}</td>
                            <td>{$awb.Status}</td>
                        </tr>
                    {/foreach}

                    </tbody>
                </table>
            </div>
            <div class="btn-group bulk-actions dropup">
                {html_options id=PRINT_TYPE_ID name=PRINT_TYPE options=$printTypes selected=$printType class='filter center' style='width:100px; float:left;'}
                <button type="submit" name="submit_printeaza" value="submit" class="btn btn-primary">
                    <i class="icon-plus-sign"></i> Printeaza AWB-urile selectate
                </button>
                <button type="submit" name="submit_trimite" value="submit" class="btn btn-default">
                    <i class="icon-plus-sign"></i> Trimite comanda curenta
                </button>
                <button type="submit" name="submit_dezactiveaza" value="submit" class="btn btn-default">
                    <i class="icon-trash"></i> Dezactiveaza AWB-urile selectate
                </button>
            </div>
            <script>
                // PRINTEAZA AWB-urile SELECTATE
                $('[name="submit_printeaza"]').click(function () {
                    var coduri = [];
                    $('input[name*=\'awbs\']:checked').each(function () {
                        coduri.push($(this).val());
                    });
                    var format = $('#PRINT_TYPE_ID').val();

                    if (coduri.length > 0) {
                        window.open('index.php?controller=CargusAdmin&type=PRINTAWB&format=' + format + '&token={$tokenAdmin}&secret={$cookie}&codes=[' + coduri.join(',') + ']', '', 'width=900, height=600, left=50, top=50');
                    } else {
                        alert('Nu ati selectat niciun AWB pentru printare!');
                    }
                    return false;
                });

                // TRIMITE COMANDA CURENTA
                $('[name="submit_trimite"]').click(function () {
                    window.open('index.php?controller=CargusAdmin&type=SENDORDER&token={$tokenAdmin}&secret={$cookie}', '', 'width=900, height=600, left=50, top=50');
                    return false;
                });

                // DEZACTIVEAZA AWB-urile SELECTATE
                $('[name="submit_dezactiveaza"]').click(function () {
                    var coduri = [];
                    $('input[name*=\'awbs\']:checked').each(function () {
                        coduri.push($(this).val());
                    });
                    if (coduri.length > 0) {
                        return true;
                    } else {
                        alert('Nu ati selectat niciun AWB pentru dezactivare!');
                    }
                    return false;
                });
            </script>
        </div>
    </form>
</div>