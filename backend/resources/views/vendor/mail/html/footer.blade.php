<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0">
            <tr>
                <td class="content-cell" align="center">
                    <img src="{{ env('CO_ICON') }}" alt="{{ env('CO_NAME') }}" height="50"><!-- public_path('logo_icon.png') -->
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                </td>
            </tr>
        </table>
    </td>
</tr>
