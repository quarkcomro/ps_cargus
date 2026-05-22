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
</style>

<div class="entry-edit">
    <form id="edit_form" class="form-horizontal" name="edit_form" method="post">
        <div class="panel">
            <div class="panel-heading"><i class="icon-align-justify"></i> Istoric livrari Cargus</div>
            <div class="table-responsive-row clearfix">
                <table class="table order">
                    <thead>
                    <tr class="nodrag nodrop">
                        <th><span class="title_box active">Nr. comanda</span></th>
                        <th><span class="title_box active">Data validare</span></th>
                        <th><span class="title_box active">Data ridicare</span></th>
                        <th><span class="title_box active">Data procesare</span></th>
                        <th><span class="title_box active">Nr. AWB-uri</span></th>
                        <th><span class="title_box active">Plicuri</span></th>
                        <th><span class="title_box active">Colete</span></th>
                        <th><span class="title_box active">Greutate</span></th>
                        <th><span class="title_box active">Status</span></th>
                        <th></th>
                    </tr>
                    <tr class="nodrag nodrop filter row_hover">
                        <th colspan="10">
                            <div style="float:left; height:31px; line-height:31px; padding-right:5px;">Punctul de ridicare
                            </div>
                            {html_options name=CARGUS_PUNCT_RIDICARE options=$pickups selected=$pickupsSelect class='filter center' style='width:200px; float:left;'}
                            <button type="submit" name="submitPickup" value="submit" class="btn btn-default" style="float:left; margin-left:5px;">
                                <i class="icon-pencil"></i> Schimba punctul de ridicare implicit
                            </button>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach from=$orders item=order}
                            <tr>
                                <td class='{cycle name=color values="odd,even"}'>{$order.OrderId}</td>
                                <td class='{cycle name=color values="odd,even"}'>{($order.ValidationDate) ? ($order.ValidationDate|date_format:"%e.%m.%Y") : '-'}</td>
                                <td class='{cycle name=color values="odd,even"}'>{($order.PickupStartDate) ? (($order.PickupStartDate|date_format:"%e.%m.%Y %k:%M")|cat:' - '|cat:($order.PickupEndDate|date_format:"%e.%m.%Y %k:%M") ): '-'}</td>
                                <td class='{cycle name=color values="odd,even"}'>{($order.ProcessedDate) ? ($order.ProcessedDate|date_format:"%e.%m.%Y") : '-'}</td>
                                <td class='{cycle name=color values="odd,even"}'>{$order.NoAwb}</td>
                                <td class='{cycle name=color values="odd,even"}'>{$order.NoEnvelop}</td>
                                <td class='{cycle name=color values="odd,even"}'>{$order.NoParcel}</td>
                                <td class='{cycle name=color values="odd,even"}'>{$order.TotalWeight}</td>
                                <td class='{cycle name=color values="odd,even"}'>{$order.OrdStatus}</td>
                                <td>
                                    <div class="btn-group-action">
                                        <div class="btn-group pull-right">
                                            <a href="index.php?controller=CargusOrderHistory&token={$token}&orderId={$order.OrderId}" title="Vizualizare" class="edit btn btn-default">
                                                <i class="icon-search-plus"></i> Vizualizare
                                            </a>
                                        </div>
                                    </div>
                                </td>

                            </tr>

                        {/foreach}
                    </tbody>
                </table>
                <div style="color:#999; font-size:11px; padding:10px 0;">Sunt afisate ultimele 100 de comenzi efectuate
                    pentru punctul curent de ridicare. Pentru comenzile anterioare, va rugam sa consultati pagina Cargus
                </div>
            </div>
        </div>
    </form>
</div>