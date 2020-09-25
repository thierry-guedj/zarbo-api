<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
<img src="{{ $message->embed(public_path().'/images/logo192x192.png') }}" style="padding:0px; margin:0px" />
{{ $slot }}
@endif
</a>
</td>
</tr>
