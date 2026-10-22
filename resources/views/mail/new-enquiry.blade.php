<x-mail::message>
# New enquiry from {{ $enquiry['name'] }}

{{ $firstName }} sent this through the contact form on the website. Reply to this email to answer them directly.

<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 8px 0 24px; border-top: 1px solid #e8e5ef;">
@foreach ($details as $label => $value)
<tr><td style="padding: 9px 16px 9px 0; border-bottom: 1px solid #e8e5ef; color: #535353; white-space: nowrap; vertical-align: top; font-size: 14px;">{{ $label }}</td><td style="padding: 9px 0; border-bottom: 1px solid #e8e5ef; vertical-align: top; font-size: 14px;">{!! $value !!}</td></tr>
@endforeach
</table>

**About the project**

<x-mail::panel>
<div>{!! $messageHtml !!}</div>
</x-mail::panel>

@if ($adminUrl)
<x-mail::button :url="$adminUrl">
Open in the admin panel
</x-mail::button>
@endif
</x-mail::message>
