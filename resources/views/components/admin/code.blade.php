{{-- A setting or command in running text. PHP drops the newline after the closing tag below, so punctuation can follow the component directly --}}
<code {{ $attributes->class('rounded bg-ink/6 px-1 py-0.5 font-mono text-[0.88em]') }}>{{ $slot }}</code><?php /* no space after the code */ ?>
