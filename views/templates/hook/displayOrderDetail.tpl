{if $trackAwb}
    <div class="box">
        <img style="width: 20px; height: 20px;" src="{$showImg}"> <strong>Cargus</strong>
        <br>
        <br>
        <div class="">
            <a href="https://www.cargus.ro/tracking-romanian/?t={$trackAwb}" target="_blank" class="btn btn-primary">Urmărește coletul</a>
        </div>
    </div>
{/if}

{if $showRetur}
<div class="box">
    <img style="width: 20px; height: 20px;" src="{$showImg}"> <strong>Cargus</strong>
    <div class="">
        {$showText}: {$showQr}
    </div>
    <div id="qrcode-container">
    </div>
</div>

<script type="text/javascript" src="{$smarty.const.__PS_BASE_URI__}modules/cargus/views/js/qrcode.js"></script>

<script>
    let opts = {
        errorCorrectionLevel: 'H',
        type: 'image/jpeg',
        quality: 0.3,
        margin: 2,
        scale: 4,
        width: 128,
        color: {
            dark:"#000000ff",
            light:"#ffffffff"
        }
    }

    QRCode.toDataURL('{$showQr}', opts, function (err, url) {
        if (err) {
            throw err
        }


        let img = document.createElement('img');
        img.alt = 'QRCode';
        img.src = url;

        document.getElementById('qrcode-container').appendChild(img);
    });

</script>
{/if}
