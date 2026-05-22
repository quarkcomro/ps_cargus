<script>
var cargus_url = '{$smarty.const.__PS_BASE_URI__}';
var adm = '{$smarty.const._PS_ADMIN_DIR_}';
var arr = adm.split('/');
var cargus_admindir = arr.slice(-1).pop();
var secret = '{$smarty.const._COOKIE_KEY_}';
var cragus_token = '{$CargusAdminToken}';
</script>
<script src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/admin_mods.js"></script>