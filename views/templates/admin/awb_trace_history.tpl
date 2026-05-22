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
            <div class="panel-heading"><i class="icon-align-justify"></i> Detalii AWB serie nr. {$BarCode}</div>
            <div class="table-responsive-row clearfix">
                <table class="table order">
                    <tbody>
                    <tr class="odd">
                        <td colspan="2"><strong>Istoric status-uri</strong></td>
                    </tr>
                    {foreach from=$awbTrace.Event item=event}
                        <tr>
                            <td class='{cycle name=color values="odd,even"}'>{$event.Description}</td>
                        </tr>
                    {foreachelse}
                        <td>
                            Nu exista niciun status pentru AWB-ul selectat!
                        </td>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>