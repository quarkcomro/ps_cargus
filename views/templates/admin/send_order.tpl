<link rel="stylesheet" href="themes/default/css/admin-theme.css" />
<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css" />
<script type="text/javascript" src="{$smarty.const._PS_JS_DIR_}jquery/jquery-{$smarty.const._PS_JQUERY_VERSION_}.min.js"></script>
<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<script>
    $(function() {
        $('#datepicker').datepicker({
            minDate: 0,
            firstDay: 1,
            dateFormat: 'dd.mm.yy',
            beforeShowDay: function(date) {
                var day = date.getDay();
                return [(day != 0), ''];
            }
        });

        $('#datepicker').change(function(){
            window.location = "index.php?controller=CargusAdmin&type=SENDORDER&token={$token}&secret={$cookie}" + "&date=" + $(this).val();
        });

        $('select[name="hour_from"]').change(function(){
            window.location = "index.php?controller=CargusAdmin&type=SENDORDER&token={$token}&secret={$cookie}" + "&date=" + $('#datepicker').val() + "&hour=" + $(this).val();
        });
    });
</script>
<style>
    .page-sidebar #content {
        margin-left: 0px !important;
    }
    .bootstrap .page-head h2.page-title {
        padding-left: 20px !important;
    }
</style>
<body class="ps_back-office page-sidebar admincarguslivrari">
<div id="main">
    <div id="content" class="bootstrap">
        <div class="bootstrap">
            <div class="page-head">
                <h2 class="page-title">
                    Intervalul de ridicare
                </h2>
            </div>
        </div>
        <div class="entry-edit">
            <div class="panel">
                <div class="panel-heading"><i class="icon-align-justify"></i> Va rugam sa alegeti data si intervalul orar pentru ridicarea comenzii</div>
                <form action="index.php?controller=CargusAdmin&type=COMPLETEORDER&token={$token}&secret={$cookie}" method="post" enctype="multipart/form-data" id="form">
                    <input class="form-control" name="date" type="text" id="datepicker" value="{$date}" style="width:200px; float:left; margin-right:10px;" />
                    <select class="form-control" name="hour_from" style="width:90px; float:left; margin-right:10px;">
                        {$h_dela}
                    </select>
                    <select class="form-control" name="hour_to" style="width:90px; float:left; margin-right:10px;">
                        {$h_panala}
                    </select>
                    <button type="submit" value="submit" class="btn btn-primary">
                        <i class="icon-plus-sign"></i> Trimite comanda
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>