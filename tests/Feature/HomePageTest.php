<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_the_landing_page_renders_every_section_in_order(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>STRICTMEDIA – Brand, web, marketing, software and printing</title>', false)
            ->assertSeeInOrder([
                'id="top"',
                'aria-label="What we do"',
                'data-scrub',
                'id="work"',
                'id="case-studies"',
                'id="platforms-title"',
                'id="services"',
                'id="print"',
                'id="process"',
                'id="contact"',
                '<footer class="px-3',
            ], false);
    }

    public function test_the_work_grid_links_every_project_to_its_case_study_or_its_site(): void
    {
        $studies = config('site.case_studies');
        $response = $this->get('/')->assertOk();

        foreach ($studies as $study) {
            $response->assertSee($study['client']);

            if ($study['featured'] ?? false) {
                $response->assertSee("href=\"#case-{$study['id']}\"", false)->assertSee("id=\"case-{$study['id']}\"", false);
            } else {
                $response->assertSee("href=\"{$study['url']}\"", false)->assertDontSee("id=\"case-{$study['id']}\"", false);
            }
        }

        $featured = count(array_filter($studies, fn ($study) => $study['featured'] ?? false));
        $response->assertSee("{$featured} projects");
    }

    public function test_every_project_has_its_screenshots(): void
    {
        foreach (config('site.case_studies') as $study) {
            $this->assertFileExists(public_path("images/work/{$study['id']}.webp"));

            if ($study['featured'] ?? false) {
                $this->assertFileExists(public_path("images/work/{$study['id']}-full.webp"));
            }
        }
    }

    public function test_the_page_copy_comes_from_the_site_config(): void
    {
        config(['site.hero.headline' => 'A brand new headline']);

        // Each word of a headline sits in its own span, so it reads as one only once the tags are gone
        $this->get('/')
            ->assertSeeText('A brand new headline')
            ->assertSee('Brand & design')
            ->assertSee('hello@strictmedia.co.uk')
            ->assertSee('Get a printing quote');
    }
}
