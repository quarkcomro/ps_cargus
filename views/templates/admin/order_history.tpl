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
        line-height: 0px;
    }
</style>

<div class="entry-edit">
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> Istoric AWB-uri pentru comanda nr. {$orderId}</div>
            <div class="table-responsive-row clearfix">
                <table class="table order">
                    <thead>
                    <tr class="nodrag nodrop">
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
                        <th><span class="title_box active">Tracking</span></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$awbs item=awb}
                        <tr>
                            <td height="38" class='{cycle name=color values="odd,even"}'>{$awb.CustomString}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.BarCode}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.ShippingCost.GrandTotal} lei</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.Recipient.Name}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.Recipient.LocalityName}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.Envelopes}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.Parcels}</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.TotalWeight} kg</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.CashRepayment} lei</td>
                            <td class='{cycle name=color values="odd,even"}'>{$awb.BankRepayment} lei</td>
                            <td class='{cycle name=color values="odd,even"}'>{($awb.ShipmentPayer == 2) ? 'Destinatar' : 'Expeditor'}</td>
                            <td>
                               {($awb.Status == 'Deleted') ? ('<span class="label color_field" style="background-color:#DC143C; color:white;">'|cat:$awb.Status|cat:'</span>') : $awb.Status}
                            </td>
                            <td>
                                <div class="btn-group-action">
                                    <div class="btn-group pull-right">
                                        <a href="index.php?controller=CargusAwbTrace&token={$tokenTrace}&BarCode={$awb.BarCode}" title="Vizualizare" class="edit btn btn-default">
                                            <i class="icon-search-plus"></i> Track
                                        </a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group-action">
                                    <div class="btn-group pull-right">
                                        <a href="index.php?controller=CargusAwbHistory&token={$token}&BarCode={$awb.BarCode}" title="Vizualizare" class="edit btn btn-default">
                                            <i class="icon-search-plus"></i> Vizualizare
                                        </a>
                                    </div>
                                </div>
                            </td>

                        </tr>

                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>