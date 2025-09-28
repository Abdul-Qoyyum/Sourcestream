<?php

namespace Tests\Unit\Services;

use App\Http\Services\NewsAPIService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class NewsAPIServiceTest extends TestCase
{
    private NewsAPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NewsAPIService();
    }

    public function test_it_fetches_articles_successfully()
    {
        Http::fake([
            'newsapi.org/v2/*' => Http::response(
                [
                    'status' => 'ok',
                    'totalResults' => 1,
                    'articles' => [
                        [
                            'source' => [
                                'id' => 'the-washington-post',
                                'name' => 'The Washington Post'
                            ],
                            'author' => 'Gene Park',
                            'title' => '‘Ghost of Yotei’ sharpens the samurai revenge formula with raw feeling - The Washington Post',
                            'description' => 'The open-world PlayStation epic excels thanks to a revenge story written with precision and heart, backed by a compelling lead performance.',
                            'url' => 'https://www.washingtonpost.com/entertainment/video-games/2025/09/25/ghost-of-yotei-review/',
                            'urlToImage' => 'https://www.washingtonpost.com/wp-apps/imrs.php?src=https://arc-anglerfish-washpost-prod-washpost.s3.amazonaws.com/public/7BPZVCHOKJDQXCQ3PEIHWI65XA.png&w=1440',
                            'publishedAt' => '2025-09-25T14:31:02Z',
                            'content' => 'The open-world game excels thanks to a revenge story written with precision and heart, backed by a compelling lead performance.
September 25, 2025 at 9:00 a.m. EDTJust now'
                        ]
                    ]
                ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertCount(1, $articles);
        $this->assertEquals('‘Ghost of Yotei’ sharpens the samurai revenge formula with raw feeling - The Washington Post', $articles[0]['title']);
    }

    public function test_it_handles_api_errors_gracefully()
    {
        Http::fake([
            'newsapi.org/v2/*' => Http::response([
                'status' => 'error',
                'message' => 'API Error'],
                Response::HTTP_INTERNAL_SERVER_ERROR)
        ]);

        Log::shouldReceive('error')->once();

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_returns_correct_source_identifier()
    {
        $source = $this->service->getSource();

        $this->assertEquals('newsapi', $source);
    }

    public function test_it_handles_http_errors_gracefully()
    {
        Http::fake([
            'newsapi.org/v2/*' => Http::response([], Response::HTTP_INTERNAL_SERVER_ERROR)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_network_errors_gracefully()
    {
        Http::fake([
            'newsapi.org/v2/*' => Http::response([], Response::HTTP_REQUEST_TIMEOUT)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_empty_response()
    {
        Http::fake([
            'newsapi.org/v2/*' => Http::response(   [
                'status' => 'ok',
                'totalResults' => 0,
                'articles' => []
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_missing_fields_gracefully()
    {
        Http::fake([
            'newsapi.org/v2/*' => Http::response(
                [
                    'status' => 'ok',
                    'totalResults' => 1,
                    'articles' => [
                        [
                            'publishedAt' => '2025-09-25T14:31:02Z',
                            //Assuming the source['id'] field is missing
                            //Assuming the url field is missing
                            //Assuming the title field is missing
                            //Assuming the description field is missing
                            //Assuming the content field is missing
                            //Assuming the url field is missing
                            //Assuming the urlToImage field is missing
                            //Assuming the author field is missing
                            //Assuming the publishedAt field is missing
                        ]
                    ]
                ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertCount(1, $articles);
        $this->assertEquals(null, $articles[0]['external_id']);
        $this->assertEquals('', $articles[0]['title']);
        $this->assertEquals('', $articles[0]['summary']);
        $this->assertEquals('', $articles[0]['url']);
        $this->assertEquals(null, $articles[0]['image_url']);
        $this->assertEquals(null, $articles[0]['author']);
    }
}
