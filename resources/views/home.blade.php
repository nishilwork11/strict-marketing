<x-layouts.app
    :title="config('site.brand.name').' – Brand, web, marketing, software and printing'"
    description="Brand and design, web development, SEO, PPC, custom software, CRM and printing from one team."
>
    <x-site.header />
    <main>
        <x-site.hero />
        <x-site.ticker />
        <div class="wash">
            <x-site.work />
            <x-site.case-studies />
            <x-site.platforms />
        </div>
        <x-site.services />
        <x-site.print />
        <x-site.process />
        <x-site.contact />
    </main>
    <x-site.footer />
</x-layouts.app>
