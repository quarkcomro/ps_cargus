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
                        <td colspan="2"><strong>Expeditor</strong></td>
                    </tr>
                    <tr class="even">
                        <td style="width:250px">Nume</td>
                        <td>{($awb.Sender.Name|trim) ? ($awb.Sender.Name|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Judet</td>
                        <td>{($awb.Sender.CountyName|trim) ? ($awb.Sender.CountyName|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Localitate</td>
                        <td>{($awb.Sender.LocalityName|trim) ? ($awb.Sender.LocalityName|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Strada</td>
                        <td>{($awb.Sender.StreetName|trim) ? ($awb.Sender.StreetName|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Numar</td>
                        <td>{($awb.Sender.BuildingNumber|trim) ? ($awb.Sender.BuildingNumber|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Adresa</td>
                        <td>{($awb.Sender.AddressText|trim) ? ($awb.Sender.AddressText|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Contact</td>
                        <td>{($awb.Sender.ContactPerson|trim) ? ($awb.Sender.ContactPerson|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Telefon</td>
                        <td>{($awb.Sender.PhoneNumber|trim) ? ($awb.Sender.PhoneNumber|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Email</td>
                        <td>{($awb.Sender.Email|trim) ? ($awb.Sender.Email|trim) : '-'}</td>
                    </tr>

                    <tr class="odd">
                        <td colspan="2"><strong>Destinatar</strong></td>
                    </tr>
                    <tr class="even">
                        <td>Nume</td>
                        <td>{($awb.Recipient.Name|trim) ? ($awb.Recipient.Name|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Judet</td>
                        <td>{($awb.Recipient.CountyName|trim) ? ($awb.Recipient.CountyName|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Localitate</td>
                        <td>{($awb.Recipient.LocalityName|trim) ? ($awb.Recipient.LocalityName|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Strada</td>
                        <td>{($awb.Recipient.StreetName|trim) ? ($awb.Recipient.StreetName|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Numar</td>
                        <td>{($awb.Recipient.BuildingNumber|trim) ? ($awb.Recipient.BuildingNumber|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Adresa</td>
                        <td>{($awb.Recipient.AddressText|trim) ? ($awb.Recipient.AddressText|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Contact</td>
                        <td>{($awb.Recipient.ContactPerson|trim) ? ($awb.Recipient.ContactPerson|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Telefon</td>
                        <td>{($awb.Recipient.PhoneNumber|trim) ? ($awb.Recipient.PhoneNumber|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Email</td>
                        <td>{($awb.Recipient.Email|trim) ? ($awb.Recipient.Email|trim) : '-'}</td>
                    </tr>

                    <tr class="odd">
                        <td colspan="2"><strong>Detalii AWB</strong></td>
                    </tr>
                    <tr class="even">
                        <td>Serie</td>
                        <td>{$awb.BarCode|trim}</td>
                    </tr>
                    <tr class="even">
                        <td>Plicuri</td>
                        <td>{$awb.Envelopes|trim}</td>
                    </tr>
                    <tr class="even">
                        <td>Colete</td>
                        <td>{$awb.Parcels|trim}</td>
                    </tr>
                    <tr class="even">
                        <td>Greutate</td>
                        <td>{$awb.TotalWeight|trim}</td>
                    </tr>
                    <tr class="even">
                        <td>Valoare declarata</td>
                        <td>{$awb.DeclaredValue|trim} lei</td>
                    </tr>
                    <tr class="even">
                        <td>Ramburs numerar</td>
                        <td>{$awb.CashRepayment|trim} lei</td>
                    </tr>
                    <tr class="even">
                        <td>Ramburs cont colector</td>
                        <td>{$awb.BankRepayment|trim} lei</td>
                    </tr>
                    <tr class="even">
                        <td>Ramburs alt tip</td>
                        <td>{($awb.OtherRepayment|trim) ? ($awb.OtherRepayment|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Deschidere colet</td>
                        <td>{($awb.OpenPackage) ? 'Da' : 'Nu'}</td>
                    </tr>
                    <tr class="even">
                        <td>Livrare dimineata</td>
                        <td>{($awb.MorningDelivery) ? 'Da' : 'Nu'}</td>
                    </tr>
                    <tr class="even">
                        <td>Livrare sambata</td>
                        <td>{($awb.SaturdayDelivery) ? 'Da' : 'Nu'}</td>
                    </tr>
                    <tr class="even">
                        <td>Platitor expeditie</td>
                        <td>{($awb.ShipmentPayer == 1) ? 'Expeditor' : (($awb.ShipmentPayer == 2) ? 'Destinatar' : 'Tert')}</td>
                    </tr>
                    <tr class="even">
                        <td>Observatii</td>
                        <td>{($awb.Observations|trim) ? ($awb.Observations|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Continut</td>
                        <td>{($awb.PackageContent|trim) ? ($awb.PackageContent|trim) : '-'}</td>
                    </tr>
                    <tr class="even">
                        <td>Identificator</td>
                        <td>{$awb.CustomString|trim}</td>
                    </tr>
                    <tr class="even">
                        <td>Status</td>
                        <td>{$awb.Status|trim}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>