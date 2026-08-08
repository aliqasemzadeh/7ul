<?php

namespace Tests\Unit;

use App\Actions\Links\GenerateLinkQrCode;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateLinkQrCodeTest extends TestCase
{
    #[Test]
    public function it_returns_an_svg_data_uri_for_a_url(): void
    {
        $dataUri = (new GenerateLinkQrCode)->handle('https://7ul.ir/abcdef12');

        $this->assertStringStartsWith('data:image/svg+xml', $dataUri);
        $this->assertStringContainsString('svg', rawurldecode($dataUri));
    }
}
