<?php

namespace Javaabu\MobileVerification\Tests\Unit;

use Javaabu\MobileVerification\Policies\MobileNumberPolicy;
use Javaabu\MobileVerification\Support\Strings;
use Javaabu\MobileVerification\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TranslationsTest extends TestCase
{
    #[Test]
    public function every_string_has_a_dhivehi_translation(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);
        $en = json_decode(file_get_contents(__DIR__ . '/../../lang/en.json'), true);

        foreach ($this->strings() as $string) {
            $this->assertArrayHasKey($string, $dv, "Missing Dhivehi for: {$string}");
            $this->assertArrayHasKey($string, $en, "Missing from en.json: {$string}");
            $this->assertMatchesRegularExpression('/\p{Thaana}/u', $dv[$string], "Not Dhivehi: {$string}");
        }
    }

    #[Test]
    public function the_dhivehi_keeps_the_placeholders(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);

        foreach ($dv as $english => $dhivehi) {
            preg_match_all('/:[a-z_]+/', $english, $expected);
            preg_match_all('/:[a-z_]+/', $dhivehi, $actual);

            $this->assertEqualsCanonicalizing(array_unique($expected[0]), array_unique($actual[0]), "Placeholders differ in: {$english}");
        }
    }

    /** @return array<int, string> */
    protected function strings(): array
    {
        $strings = [...array_values(Strings::ALL), ...array_values(MobileNumberPolicy::DESCRIPTIONS)];

        foreach (['src', 'resources/views'] as $folder) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . "/../../{$folder}"));

            foreach ($files as $file) {
                if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.php')) {
                    continue;
                }

                preg_match_all("/(?:__|trans_choice)\\(\\s*'((?:[^'\\\\]|\\\\.)*)'/", file_get_contents($file->getPathname()), $matches);

                foreach ($matches[1] as $match) {
                    $strings[] = stripslashes($match);
                }

                preg_match_all('/(?:__|trans_choice)\(\s*"((?:[^"\\\\]|\\\\.)*)"/', file_get_contents($file->getPathname()), $matches);

                foreach ($matches[1] as $match) {
                    $strings[] = stripslashes($match);
                }
            }
        }

        return array_values(array_unique($strings));
    }
}
