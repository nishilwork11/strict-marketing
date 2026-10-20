<?php

// All page copy lives here: change it here and the views follow.

$services = [
    'statement' => 'Brand, web, marketing, software and printing. One team and one point of contact.',
    'body' => 'Most businesses juggle a designer, a web developer, an ads agency, a CRM consultant and a printer. We do all five, so your brand, website, campaigns and printed materials share the same data, the same look and the same deadline.',
    'items' => [
        [
            'name' => 'Brand & design',
            'points' => ['Logos and brand identity', 'Brand guidelines', 'Website and app design', 'Print and packaging design'],
        ],
        [
            'name' => 'Web development',
            'points' => ['Business websites', 'E-commerce stores', 'Web apps and portals', 'Hosting, speed and support'],
        ],
        [
            'name' => 'SEO',
            'points' => ['Technical SEO audits', 'Local SEO', 'Content and landing pages', 'Monthly reporting'],
        ],
        [
            'name' => 'PPC',
            'points' => ['Google Ads and Shopping', 'Meta and LinkedIn ads', 'Conversion tracking', 'Landing page testing'],
        ],
        [
            'name' => 'Custom software',
            'points' => ['Internal tools', 'Customer and trade portals', 'API integrations', 'Data sync and automation'],
        ],
        [
            'name' => 'CRM',
            'points' => ['Setup and migration', 'Pipelines and automation', 'Forms, calls and email in one place', 'Team training'],
        ],
        [
            'name' => 'Printing',
            'points' => ['Business cards and stationery', 'Flyers and brochures', 'Signs, banners and displays', 'Stickers, labels and packaging'],
        ],
    ],
];

return [

    'brand' => [
        'name' => 'STRICTMEDIA',
        // Shown large in the footer, and beside the logo in the admin panel
        'wordmark' => 'STRICTMEDIA',
        'email' => 'hello@strictmedia.co.uk',
        'phone' => '+44 7740 536355',
        'address' => ['Studio address line', 'City, Postcode'],
        'locations' => ['London', 'Remote, UK-wide'],
        'year' => 2026,
        'tagline' => 'Brand and design, web development, marketing, custom software and printing, from one team.',
        // Set in capitals under the wordmark in the footer and admin panel, split by dots
        'strapline' => ['Marketing', 'PPC', 'Web development', 'Printing'],
    ],

    'nav' => [
        ['label' => 'Work', 'href' => '#work'],
        ['label' => 'Services', 'href' => '#services'],
        ['label' => 'Printing', 'href' => '#print'],
        ['label' => 'Process', 'href' => '#process'],
    ],

    'hero' => [
        'headline' => 'STRICTMEDIA is a digital company working across brand and design, web development, marketing, custom software and printing.',
    ],

    'intro' => [
        'statement' => 'We develop brands that stand out, build websites that win customers, create software that works for your business and deliver print that brings your brand to life.',
        'cta' => ['label' => 'How we work', 'href' => '#process'],
    ],

    // Our work: every project below is a live site, described from its own pages.
    // Each one needs a screenshot in public/images/work/: {id}.webp (1080×675, the
    // top of the home page) and, for featured projects, {id}-full.webp (the page
    // scrolled, which plays when the case study's cover is hovered). Featured projects
    // get a full case study card; the rest link to their site from the work grid.
    'case_studies' => [
        [
            'id' => 'xtocastiq',
            'client' => 'Xtocastiq',
            'url' => 'https://xtocastiq.co.uk',
            'featured' => true,
            'summary' => 'A website for quantum-powered drug discovery',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#04060d', 'to' => '#1f2c72', 'dark' => true],
            'description' => 'Xtocastiq combines quantum algorithms, classical computing and its own methodology to make drug discovery and patient care more precise, faster and less resource-intensive. The site explains the science, its Xpectra platform and its drug discovery programmes.',
            'facts' => [
                ['value' => 'Healthcare', 'label' => 'Drug discovery and patient care'],
                ['value' => 'Quantum', 'label' => 'Hybrid quantum–classical computing'],
                ['value' => 'Xpectra', 'label' => 'Its research platform'],
            ],
        ],
        [
            'id' => 'apollo-brunswick',
            'client' => 'Apollo Restaurant',
            'url' => 'https://apollobrunswick.com',
            'featured' => true,
            'summary' => 'A website for a Greek Cypriot restaurant in London',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#2b1410', 'to' => '#a03d29', 'dark' => true],
            'description' => 'Apollo serves fine Greek Cypriot food from a charcoal grill at the Brunswick Centre, carrying on the original Apollo restaurant founded in 1981. The site shows the menu, gallery and opening times, and takes table and Christmas bookings.',
            'facts' => [
                ['value' => 'Since 1981', 'label' => 'The original Apollo opened'],
                ['value' => 'WC1N', 'label' => 'At the Brunswick Centre, London'],
                ['value' => 'Bookings', 'label' => 'Tables booked from the site'],
            ],
        ],
        [
            'id' => 'masterpiece-jewellers',
            'client' => 'Masterpiece Jewellers',
            'url' => 'https://masterpiecejewellers.com',
            'featured' => true,
            'summary' => 'A showroom website for luxury watches and fine jewellery',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#0b0b0b', 'to' => '#7d5f35', 'dark' => true],
            'description' => 'Masterpiece Jewellers buys, sources and sells sought-after watches from Rolex, Patek Philippe, Audemars Piguet and Richard Mille, and makes bespoke jewellery with certified diamonds. The site presents featured watches with their year and price, ahead of a visit to the London showroom.',
            'facts' => [
                ['value' => 'Buy, sell', 'label' => 'Watches bought, sourced and sold'],
                ['value' => 'Bespoke', 'label' => 'Jewellery with certified diamonds'],
                ['value' => 'London', 'label' => 'Showroom visits'],
            ],
        ],
        [
            'id' => 'bespoke-timber-windows',
            'client' => 'Bespoke Timber Windows',
            'url' => 'https://bespoketimberwindows.com',
            'featured' => true,
            'summary' => 'A website for a London timber window and door maker',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#1c2f26', 'to' => '#a8864f', 'dark' => true],
            'description' => 'Bespoke Timber Windows designs, makes and fits timber windows and doors in Harrow, for period homes and new builds across London and the Home Counties. The site sets out the range with prices from £595, and books surveys and quotes.',
            'facts' => [
                ['value' => 'Since 2017', 'label' => 'Handcrafted in Harrow'],
                ['value' => '10 years', 'label' => 'Guarantee on their work'],
                ['value' => 'FENSA', 'label' => 'Registered installer'],
            ],
        ],
        [
            'id' => 'southgate-timber',
            'client' => 'Southgate Timber',
            'url' => 'https://southgatetimber.co.uk',
            'featured' => true,
            'summary' => 'Google Ads for a timber merchant in Essex',
            'tags' => ['PPC', 'Google Ads'],
            'cover' => ['from' => '#2c4a12', 'to' => '#8cc63e', 'dark' => true],
            'description' => 'Southgate Timber supplies hardwood decking, cedar cladding, fencing, sleepers and oak mouldings from its warehouses in Moreton, Essex, and delivers across the UK mainland. Our Google Ads campaigns bring people searching for its timber to its online shop.',
            'facts' => [
                ['value' => 'Google Ads', 'label' => 'Search campaigns'],
                ['value' => 'FSC®', 'label' => 'Registered timber merchant'],
                ['value' => 'UK-wide', 'label' => 'Delivery across the mainland'],
            ],
        ],
        [
            'id' => 'workflair',
            'client' => 'Workflair',
            'url' => 'http://workflair.co.uk',
            'summary' => 'A website for a guaranteed-rent letting company',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#250709', 'to' => '#c41e2c', 'dark' => true],
        ],
        [
            'id' => 'brood-roastery',
            'client' => 'Brood Roastery',
            'url' => 'https://broodroastery.com',
            'summary' => 'An online shop for a London speciality coffee roaster',
            'tags' => ['Web development', 'E-commerce'],
            'cover' => ['from' => '#f1c3c6', 'to' => '#8fd1cb', 'dark' => false],
        ],
        [
            'id' => 'hamilton-foundation',
            'client' => 'Hamilton Development Foundation',
            'url' => 'https://hamilton-foundation.com',
            'summary' => 'A website for a blockchain foundation in Anguilla',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#0e2232', 'to' => '#7d9c33', 'dark' => true],
        ],
        [
            'id' => 'htl-nevis',
            'client' => 'Hamilton Trust Company',
            'url' => 'https://htlnevis.com',
            'summary' => 'A website for a trust company in Nevis',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#33270d', 'to' => '#c29a45', 'dark' => true],
        ],
        [
            'id' => 'tilson-and-spencer',
            'client' => 'Tilson & Spencer',
            'url' => 'https://tilsonandspencer.co.uk',
            'summary' => 'A property search website for a Tottenham estate agent',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#0e1a3c', 'to' => '#e0447f', 'dark' => true],
        ],
        [
            'id' => 'shaftesbury-garage',
            'client' => 'Shaftesbury Garage',
            'url' => 'https://shaftesburygarage.co.uk',
            'summary' => 'A website for an MOT and repair garage in Barnet',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#111111', 'to' => '#23479a', 'dark' => true],
        ],
        [
            'id' => 'spartan-pmj',
            'client' => 'The Spartan Group & PMJ',
            'url' => 'https://spartanpmj.com',
            'summary' => 'A website for a planning and land development group',
            'tags' => ['Web design', 'Web development'],
            'cover' => ['from' => '#262626', 'to' => '#9c8656', 'dark' => true],
        ],
    ],

    'platforms' => [
        'statement' => "We've worked with 150+ businesses across the UK, France & Cyprus, on everything they need to grow.",
        'items' => ['Brand development', 'Graphic design', 'Web development', 'SEO', 'Google Ads', 'Meta Ads', 'Custom CRM', 'Printing Services'],
    ],

    'services' => $services,

    // The big scrolling strip under the hero: Marketing, then each service by name
    'ticker' => ['Marketing', ...array_column($services['items'], 'name')],

    'print' => [
        'label' => 'Printing',
        'headline' => 'Designed here, printed here, delivered anywhere in the UK.',
        'body' => 'Thousands of printing products, from business cards to exhibition stands. Send us finished artwork, or let our designers make it to match your website.',
        'steps' => [
            ['title' => 'Send artwork or a brief', 'text' => 'Upload print-ready files, or tell us what you need and we design it.'],
            ['title' => 'Approve a proof', 'text' => 'We check sizes, bleed and colours, then send a digital proof to sign off.'],
            ['title' => 'Printed and delivered', 'text' => 'Your order goes to press and ships to your door with tracking.'],
        ],
        'rows' => [
            [
                ['name' => 'Business cards', 'detail' => 'Matt, silk, soft-touch, foil', 'size' => '85 × 55 mm'],
                ['name' => 'Flyers and leaflets', 'detail' => 'Flat or folded, 130–400gsm', 'size' => 'A7 to A3'],
                ['name' => 'Booklets and brochures', 'detail' => 'Stapled, wire or perfect bound', 'size' => 'A6 to A4'],
                ['name' => 'Posters', 'detail' => 'Gloss, satin or matt paper', 'size' => 'A3 to A0'],
                ['name' => 'Letterheads', 'detail' => 'Uncoated and laid papers', 'size' => 'A4 and A5'],
                ['name' => 'Calendars', 'detail' => 'Wall, desk and planners', 'size' => 'A5 to A3'],
            ],
            [
                ['name' => 'Roller banners', 'detail' => 'Stand and carry case included', 'size' => '850 × 2000 mm'],
                ['name' => 'Signs and boards', 'detail' => 'Foamex, Correx, Dibond', 'size' => 'Made to size'],
                ['name' => 'Stickers and labels', 'detail' => 'Sheets, rolls, die-cut', 'size' => 'Any shape'],
                ['name' => 'Packaging', 'detail' => 'Mailer boxes, bags, tags', 'size' => 'Made to size'],
                ['name' => 'Branded clothing', 'detail' => 'Tees, hoodies, workwear', 'size' => 'XS to 3XL'],
                ['name' => 'Exhibition displays', 'detail' => 'Pop-up walls and counters', 'size' => 'Kits and custom'],
            ],
        ],
        'cta' => ['label' => 'Get a printing quote', 'href' => '#contact'],
    ],

    'process' => [
        'title' => 'How we work',
        'steps' => [
            ['title' => 'Talk', 'text' => 'A 30-minute call to understand your business, goals and budget.'],
            ['title' => 'Plan', 'text' => "A fixed quote, timeline and the people who'll work on it."],
            ['title' => 'Build', 'text' => 'Weekly updates and a preview link so you see progress as it happens.'],
            ['title' => 'Grow', 'text' => "Monthly reports, support and improvements once you're live."],
        ],
    ],

    'contact' => [
        'headline' => "Got a project in mind? Let's talk it through.",
        'note' => "Tell us a little about it and we'll reply within one working day.",
        'form_title' => "Let's discuss your project",
        'services' => ['Brand & design', 'Website', 'SEO', 'PPC', 'Custom software', 'CRM', 'Printing'],
        'budgets' => ['Under £5k', '£5k–15k', '£15k–50k', '£50k+', 'Not sure yet'],
    ],

];
