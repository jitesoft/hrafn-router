<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
  ParameterExtractorTest.php - Part of the router project.

  © - Jitesoft 2018
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
namespace Hrafn\Router\Tests\Parser;

use Hrafn\Router\Contracts\ParameterExtractorInterface;
use Hrafn\Router\Parser\RegexParameterExtractor;
use Jitesoft\Exceptions\Logic\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * ParameterExtractorTest
 * @author Johannes Tegnér <johannes@jitesoft.com>
 * @version 1.0.0
 */
class ParameterExtractorTest extends TestCase {

    /** @var ParameterExtractorInterface */
    private $extractor;

    protected function setUp() {
        parent::setUp();

        $this->extractor = new RegexParameterExtractor(new NullLogger());
    }

    public function testExtractWithoutParameters() {
        $path    = '/path/without/parameter';
        $pattern = '/path/without/parameter';
        $output  = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEmpty($output);
    }

    public function testExtractWithSingleParameter() {
        $path    = '/path/with/abc123';
        $pattern = '/path/with/{parameter}';
        $output  = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals(['parameter' => 'abc123'],$output->toAssocArray());

    }

    public function testExtractWithSingleOptionalParameter() {

        $path    = '/path/with/abc123';
        $pattern = '/path/with/{parameter}';
        $output  = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals(['parameter' => 'abc123'],$output->toAssocArray());

    }

    public function testExtractWithMultipleParameters() {
        $path    = '/path/with/abc123/abc321/cde123';
        $pattern = '/path/with/{parameter}/{another}/{again}';
        $output  = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals([
            'parameter' => 'abc123',
            'another' => 'abc321',
            'again' => 'cde123'
        ],$output->toAssocArray());
    }

    public function testExtractWithMultipleOptionalParameters() {
        $path    = '/path/with/abc123/abc321/cde123';
        $pattern = '/path/with/{?parameter}/{?another}/{?again}';
        $output  = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals([
            'parameter' => 'abc123',
            'another' => 'abc321',
            'again' => 'cde123'
        ],$output->toAssocArray());

        $path   = '/path/with/abc123/abc321/';
        $output = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals([
            'parameter' => 'abc123',
            'another' => 'abc321',
            'again' => null
        ],$output->toAssocArray());

    }

    public function testExtractWithMixedParameters() {
        $path    = '/path/with/abc123/req/abc321/cde123';
        $pattern = '/path/with/{parameter}/{required}/{?another}/{?again}';
        $output  = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals([
            'parameter' => 'abc123',
            'required' => 'req',
            'another' => 'abc321',
            'again' => 'cde123'
        ],$output->toAssocArray());

        $path   = '/path/with/abc123/req/abc1444';
        $output = $this->extractor->getUriParameters($pattern, $path);

        $this->assertEquals([
            'parameter' => 'abc123',
            'required' => 'req',
            'another' => 'abc1444',
            'again' => null
        ],$output->toAssocArray());
    }

    public function testExtractFailure() {
        $path    = '/path/with/abc123';
        $pattern = '/path/with/{parameter}/{required}';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Error when trying to match pattern "/path/with/{parameter}/{required}" with path "/path/with/abc123", Could not match all parameters.'
        );
        $this->extractor->getUriParameters($pattern, $path);
    }

}
