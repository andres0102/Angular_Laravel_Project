<tr>
    <td class="header logo" width="50%">
        <a href="{{ $url }}">
            {{ $slot }}
        </a>
    </td>
    <td class="header" width="50%">
      <p>
        <span class="co_name">{{ env('CO_NAME') }}</span><br>
        <span class="co_info">{{ env('CO_ADDRESS') }}</span><br>
        <span class="co_info">Tel: {{ env('CO_TEL') }} &nbsp; Fax: {{ env('CO_FAX') }}</span>
      </p>
    </td>
</tr>
