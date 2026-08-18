<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContactPageTest extends TestCase
{
    public function test_contact_page_shows_unit_email_and_contact_persons(): void
    {
        $response = $this->get(route('pages.kontak'));

        $response->assertOk();
        $response->assertSee('Kontak / Helpdesk');
        $response->assertSee('Silakan hubungi Unit Kerjasama Poltekkes Kemenkes Banten.');
        $response->assertSee('kerjasama@poltekkesbanten.ac.id');
        $response->assertSee('Rifaldi Tri Pamungkas');
        $response->assertSee('082184024334');
        $response->assertSee('Diyas Arliandini');
        $response->assertSee('089508394483');
        $response->assertSee('tel:+6282184024334', false);
        $response->assertSee('https://wa.me/6282184024334', false);
        $response->assertSee('tel:+6289508394483', false);
        $response->assertSee('https://wa.me/6289508394483', false);
        $response->assertSee('Ajukan Kerjasama');
    }
}
