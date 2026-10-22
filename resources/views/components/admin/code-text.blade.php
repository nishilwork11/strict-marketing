@props(['text'])
{{-- Text from app/Support/MailCheck.php, where `backticks` mark settings and commands, shown as x-admin.code --}}
{!! preg_replace('/`([^`]+)`/', '<code class="rounded bg-ink/6 px-1 py-0.5 font-mono text-[0.88em]">$1</code>', e($text)) !!}
