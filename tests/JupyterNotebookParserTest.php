<?php

namespace PixlMint\Test\CMS;

use Nacho\Helpers\JupyterNotebookHelper;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class JupyterNotebookParserTest extends TestCase
{
    private JupyterNotebookHelper $converter;

    const EXCLUDED = [
        "Line_breaks_in_LateX_305",
    ];

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->converter = new JupyterNotebookHelper($logger, true);
    }

    public function testNotebookParsing()
    {
        $basedir = "/var/www/html/pixl-cms/tests/data";
        $testFiles = [];
        foreach (scandir($basedir . "/inputs") as $file) {
            $in = $basedir . "/inputs/" . $file;
            if (is_file($in)) {
                $info = pathinfo($in);
                $out = $basedir . "/outputs/" . $info['filename'] . ".md";

                if (is_file($out) && !in_array($info['filename'], self::EXCLUDED)) {
                    $testFiles[$in] = $out;
                }
            }
        }

        foreach ($testFiles as $input => $expected) {
            $content = $this->converter->getContent($input);

            $this->assertEquals(file_get_contents($expected), $content, sprintf("%s did not parse correctly", pathinfo($input)['basename']));
        }
    }
}
