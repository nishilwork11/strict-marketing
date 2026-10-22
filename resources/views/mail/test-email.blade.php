<x-mail::message>
# Email from the website is working

This test was sent from the admin panel on {{ $sentAt }}. It went out the same way as the enquiry emails, so they're being sent too.

<x-mail::button :url="$url">
Back to the email page
</x-mail::button>
</x-mail::message>
