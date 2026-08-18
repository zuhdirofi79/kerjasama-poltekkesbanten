<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  list<string>  $visible
     * @param  list<string>  $hidden
     */
    protected function assertPublicFolderCards(TestResponse $response, array $visible, array $hidden = []): void
    {
        $html = $response->getContent();

        $this->assertSame(
            count($visible),
            substr_count($html, 'class="repository-folder-title"'),
            'Unexpected number of public folder cards.',
        );

        foreach ($visible as $name) {
            $this->assertStringContainsString(
                'class="repository-folder-title">'.$name.'</h3>',
                $html,
                "Missing folder card: {$name}",
            );
        }

        foreach ($hidden as $name) {
            $this->assertStringNotContainsString(
                'class="repository-folder-title">'.$name.'</h3>',
                $html,
                "Folder card should be hidden: {$name}",
            );
        }
    }
}
