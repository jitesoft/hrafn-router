<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  PathExtractorTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests;

use Hrafn\Router\Contracts\PathExtractorInterface;
use Hrafn\Router\Parser\RegularExpressionExtractor;
use PHPUnit\Framework\TestCase;

/**
 * PathExtractorTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class PathExtractorTest extends TestCase {
    /** @var PathExtractorInterface */
    private $extractor;

    protected function setUp() {
        parent::setUp();

        $this->extractor = new RegularExpressionExtractor();
    }

    public function testExtractPath() {
        $path = '/test/path/without/parameters';
        $out  = $this->extractor->getUriParts($path);

        $this->assertEquals([
            'test',
            'path',
            'without',
            'parameters'
        ], $out->toArray());
    }

    public function testExtractPathWithParameter() {
        $path = '/test/path/with/{some}/parameters/{?right}';
        $out  = $this->extractor->getUriParts($path);

        $this->assertEquals([
            'test',
            'path',
            'with',
            '%some%',
            'parameters',
            '%right%'
        ], $out->toArray());
    }

    public function testTrailingSlash() {
        $path = '/test/path/without/parameters/';
        $out  = $this->extractor->getUriParts($path);

        $this->assertEquals([
            'test',
            'path',
            'without',
            'parameters'
        ], $out->toArray());
    }

    public function testTrailingSlashWithParameter() {
        $path = '/test/path/with/{some}/parameters/{?right}/';
        $out  = $this->extractor->getUriParts($path);

        $this->assertEquals([
            'test',
            'path',
            'with',
            '%some%',
            'parameters',
            '%right%'
        ], $out->toArray());

    }

    public function testOmittedSlash() {
        $path = 'test/path/without/parameters';
        $out  = $this->extractor->getUriParts($path);

        $this->assertEquals([
            'test',
            'path',
            'without',
            'parameters'
        ], $out->toArray());
    }

}
