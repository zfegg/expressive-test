<?php

declare(strict_types=1);

namespace ZfeggTest\ExpressiveTest;

use Dflydev\FigCookies\SetCookie;
use Dflydev\FigCookies\SetCookies;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Laminas\Diactoros\Response;
use Zfegg\ExpressiveTest\TestResponse;

class TestResponseTest extends TestCase
{
    public function testAssertStatus()
    {
        (new TestResponse(new Response()))->assertStatus(200);
    }

    public function testAssertHeaderMissing()
    {
        (new TestResponse(new Response()))->assertHeaderMissing('test');
    }

    public function testAssertJsonCount()
    {
        $response = (new TestResponse(new Response\JsonResponse([
            'data' => [1,2,3],
            'data2' => ['data3' => 'a', 'data4' => 'b']
        ])));
        $response->assertJsonCount(2);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonCount(2, 'data2.*');
    }

    public function testAssertNoContent()
    {
        (new TestResponse(new Response\EmptyResponse()))
            ->assertNoContent();
    }

    public function testJson()
    {
        $this->assertEquals(
            [1,2,3],
            (new TestResponse(new Response\JsonResponse(['data' => [1,2,3]])))->json('data')
        );

        $this->expectException(AssertionFailedError::class);

        (new TestResponse(new Response\EmptyResponse()))->json();
    }

    public function testAssertOkAndSuccessful()
    {
        (new TestResponse(new Response()))
            ->assertOk()
            ->assertSuccessful();
    }

    public function testAssertNotFound()
    {
        (new TestResponse(new Response\EmptyResponse(404)))->assertNotFound();
    }

    public function testAssertUnauthorized()
    {
        (new TestResponse(new Response\EmptyResponse(401)))->assertUnauthorized();
    }

    public function testAssertDontSeeText()
    {
        (new TestResponse(new Response\HtmlResponse('<p>test</p>')))
            ->assertDontSeeText('hello');
    }

    public function testAssertJsonMissingExact()
    {
        (new TestResponse(new Response\JsonResponse(['test'])))
            ->assertJsonMissingExact(['test1']);
    }

    public function testAssertHeader()
    {
        (new TestResponse(new Response\JsonResponse(['test'])))
            ->assertHeader('Content-Type', 'application/json');
    }

    public function testAssertCookieAndNotExpiredAndMissing()
    {
        $orgResponse = new Response\JsonResponse(['test']);
        $setCookies = new SetCookies([SetCookie::create('test', '123')->withExpires(time() + 3600)]);
        $orgResponse = $setCookies->renderIntoSetCookieHeader($orgResponse);

        (new TestResponse($orgResponse))
            ->assertCookie('test', '123')
            ->assertCookieNotExpired('test')
            ->assertCookieMissing('notfound');
    }

    public function testAssertExactJson()
    {
        (new TestResponse(new Response\JsonResponse(['test'])))
            ->assertExactJson(['test']);
    }

    public function testAssertRedirect()
    {
        (new TestResponse(new Response\RedirectResponse('/')))
            ->assertRedirect('/')
            ->assertLocation('/');
    }

    public function testAssertCreated()
    {
        (new TestResponse(new Response\EmptyResponse(201)))
            ->assertCreated();
    }

    public function testAssertDontSee()
    {
        (new TestResponse(new Response\HtmlResponse('<p>test</p>')))
            ->assertDontSee('hello');
    }

    public function testAssertForbidden()
    {
        (new TestResponse(new Response\EmptyResponse(403)))
            ->assertForbidden();
    }


    public function testAssertJsonPath()
    {
        $response = (new TestResponse(new Response\JsonResponse([
            'data' => [1,2,3],
            'data2' => ['data3' => 'a', 'data4' => 'b']
        ])));
        $response->assertJsonPath('data2.*', ['a', 'b']);
        $response->assertJsonPath('data2.*', ['a', 'b'], true);
    }

    public function testAssertJsonMissing()
    {
        $response = (new TestResponse(new Response\JsonResponse([
            'data' => [1,2,3],
            'data2' => ['data3' => 'a', 'data4' => 'data']
        ])));
        $response->assertJsonMissing(['data3']);
        $response->assertJsonMissing(['data3'], true);
    }

    public function testAssertJsonStructure()
    {
        $response = (new TestResponse(new Response\JsonResponse([
            'data' => ['data3' => 'a', 'data4' => 'data'],
            'data2' => ['data3' => 'b', 'data4' => 'data']
        ])));
        $response->assertJsonStructure(['data']);
        $response->assertJsonStructure(['*' => ['data3']]);
    }

    public function testAssertSee()
    {
        (new TestResponse(new Response\HtmlResponse('<p>test</p>')))
            ->assertSee('test');
    }

    public function testAssertSeeText()
    {
        (new TestResponse(new Response\HtmlResponse('<p><span>t</span>est</p>')))
            ->assertSeeText('test');
    }

    public function testAssertCookieExpired()
    {
        $orgResponse = new Response\JsonResponse(['test']);
        $setCookies = new SetCookies([SetCookie::create('test', '123')]);
        $orgResponse = $setCookies->renderIntoSetCookieHeader($orgResponse);

        (new TestResponse($orgResponse))
            ->assertCookieExpired('test');
    }

    public function testAssertJson()
    {
        $response = (new TestResponse(new Response\JsonResponse([
            'data' => [1,2,3],
            'data2' => ['data3' => 'a', 'data4' => 'data']
        ])));
        $response->assertJson(['data' => [1,2,3]]);
    }
}
