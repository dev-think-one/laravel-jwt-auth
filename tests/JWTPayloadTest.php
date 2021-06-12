<?php


namespace JWTAuth\Tests;

use Carbon\Carbon;
use JWTAuth\JWTPayload;

class JWTPayloadTest extends TestCase
{

    /** @test */
    public function payload_implement_contract()
    {
        $payload = new JWTPayload();

        $this->assertTrue($payload->isPast());
        $this->assertFalse($payload->isValid());
        $this->assertFalse($payload->can('some'));
        $this->assertTrue($payload->cant('some'));
        $this->assertIsArray($payload->abilities());
        $this->assertCount(0, $payload->abilities());
        $this->assertEquals(0, $payload->exp());
        $this->assertIsArray($payload->toArray());
        $this->assertCount(0, $payload->toArray());
        $this->assertIsArray($payload->jsonSerialize());
        $this->assertCount(0, $payload->jsonSerialize());
        $this->assertEquals(null, $payload->get('example'));
        $this->assertEquals('bla', $payload->get('example', 'bla'));
        $this->assertEquals('[]', $payload->toJson());
        $this->assertEquals('bla2', $payload->add('example', 'bla2')->get('example'));
        $this->assertEquals('bla3', $payload->add([ 'example2' => 'bla3' ])->get('example2'));
    }

    /** @test */
    public function pass_payload_in_constructor()
    {
        $exp     = Carbon::now()->addHour()->timestamp;
        $payload = new JWTPayload([
            'abilities' => [ 'ab_1', 'ab_2' ],
            'exp'       => $exp,
        ]);

        $this->assertFalse($payload->isPast());
        $this->assertTrue($payload->isValid());
        $this->assertFalse($payload->can('some'));
        $this->assertTrue($payload->cant('some'));
        $this->assertTrue($payload->can('ab_1'));
        $this->assertFalse($payload->cant('ab_1'));
        $this->assertIsArray($payload->abilities());
        $this->assertCount(2, $payload->abilities());
        $this->assertEquals($exp, $payload->exp());
        $this->assertIsArray($payload->toArray());
        $this->assertCount(2, $payload->toArray());
        $this->assertIsArray($payload->jsonSerialize());
        $this->assertCount(2, $payload->jsonSerialize());
        $this->assertEquals(null, $payload->get('example'));
        $this->assertEquals($exp, $payload->get('exp'));
    }
}
