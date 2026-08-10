<?php

use App\Models\DiningTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exports dining tables with qr image links', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    DiningTable::create(['name' => 'A01', 'code' => 'table-a01', 'capacity' => 4, 'is_active' => true]);

    $response = $this->actingAs($admin)->get(route('admin.dining-tables.export'));
    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $zip = new \ZipArchive();
    expect($zip->open($response->baseResponse->getFile()->getPathname()))->toBeTrue()
        ->and($zip->locateName('xl/media/qr-1.png'))->toBeFalse()
        ->and($zip->getFromName('xl/worksheets/sheet1.xml'))->toContain('api.qrserver.com');
    $zip->close();
});

it('prevents order staff from exporting dining tables', function () {
    $staff = User::factory()->create(['role' => 'orders_viewer', 'is_admin' => false]);

    $this->actingAs($staff)
        ->get(route('admin.dining-tables.export'))
        ->assertForbidden();
});
