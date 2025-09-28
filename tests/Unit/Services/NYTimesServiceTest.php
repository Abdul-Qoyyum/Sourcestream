<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Http\Services\NYTimesService;
use Illuminate\Support\Facades\Http;

class NYTimesServiceTest extends TestCase
{
    private NYTimesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.nytimes.key', 'test-api-key');
        $this->service = new NYTimesService();
    }

    public function test_it_fetches_articles_successfully()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([
                    'status' => 'OK',
                    'copyright' => 'Copyright (c) 2025 The New York Times Company. All Rights Reserved.',
                    'num_results' => 1,
                    'results' => [
                        [
                            'byline' => 'By Lauren Hirsch, Tripp Mickle and Emmett Lindner',
                            'source' => 'New York Times',
                            'slug_name' => '25biz-trump-tiktok',
                            'url' => 'https://www.nytimes.com/2025/09/25/technology/trump-tiktok-ban-deal.html',
                            'title' => 'Trump Clears Way for American-Owned TikTok Valued at $14 Billion',
                            'abstract' => 'The administration has been working for months to find non-Chinese investors for a U.S. version of the app.',
                            'published_date' => '2025-09-25T17:44:30-04:00',
                            'multimedia' => [
                                [
                                    'url' => 'https://static01.nyt.com/images/2025/09/25/multimedia/25biz-trump-tiktok-cmvk/25biz-trump-tiktok-cmvk-thumbStandard-v3.jpg',
                                    'format' => 'Standard Thumbnail',
                                    'height' => 75,
                                    'width' => 75,
                                    'type' => 'image',
                                    'subtype' => 'photo',
                                    'caption' => 'President Trump signed the executive order to clear the path for a TikTok deal on Thursday.',
                                    'copyright' => 'Haiyun Jiang/The New York Times'
                                ],
                                [
                                    'url' => 'https://static01.nyt.com/images/2025/09/25/multimedia/25biz-trump-tiktok-cmvk/25biz-trump-tiktok-cmvk-mediumThreeByTwo210-v2.jpg',
                                    'format' => 'mediumThreeByTwo210',
                                    'height' => 140,
                                    'width' => 210,
                                    'type' => 'image',
                                    'subtype' => 'photo',
                                    'caption' => 'President Trump signed the executive order to clear the path for a TikTok deal on Thursday.',
                                    'copyright' => 'Haiyun Jiang/The New York Times'
                                ]
                            ],
                        ]
                    ]
                ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertCount(1, $articles);
        $this->assertEquals('Trump Clears Way for American-Owned TikTok Valued at $14 Billion', $articles[0]['title']);
        $this->assertEquals('Lauren Hirsch, Tripp Mickle and Emmett Lindner', $articles[0]['author']);
    }


    public function test_it_extracts_author_from_byline()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([
                'status' => 'OK',
                'copyright' => 'Copyright (c) 2025 The New York Times Company. All Rights Reserved.',
                'num_results' => 1,
                'results' => [
                    [
                        'byline' => 'By Lauren Hirsch, Tripp Mickle and Emmett Lindner',
                        'source' => 'New York Times',
                        'slug_name' => '25biz-trump-tiktok',
                        'url' => 'https://www.nytimes.com/2025/09/25/technology/trump-tiktok-ban-deal.html',
                        'title' => 'Trump Clears Way for American-Owned TikTok Valued at $14 Billion',
                        'abstract' => 'The administration has been working for months to find non-Chinese investors for a U.S. version of the app.',
                        'published_date' => '2025-09-25T17:44:30-04:00',
                        'multimedia' => [],
                    ]
                ]
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertCount(1, $articles);
        $this->assertEquals('Lauren Hirsch, Tripp Mickle and Emmett Lindner', $articles[0]['author']);
    }

    public function test_it_uses_default_author_when_no_author_information()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([
                    'status' => 'OK',
                    'copyright' => 'Copyright (c) 2025 The New York Times Company. All Rights Reserved.',
                    'num_results' => 50,
                    'results' => [
                        [
                            'byline' => '', //Assuming byline is empty
                            'source' => 'New York Times',
                            'slug_name' => '26evening-web',
                            'url' => 'https://www.nytimes.com/2025/09/26/briefing/netanyahu-un-comey-trump-indicted.html',
                            'title' => 'Netanyahu Was Defiant at the U.N.',
                            'abstract' => 'Also, what’s next after James Comey’s indictment. Here’s the latest at the end of Friday.',
                            'published_date' => '2025-09-26T18:00:16-04:00',
                            'multimedia' => [
                                [
                                    'url' => 'https://static01.nyt.com/images/2025/09/26/multimedia/26evening-Netanyahu-zptk/26evening-Netanyahu-zptk-thumbStandard.jpg',
                                    'format' => 'Standard Thumbnail',
                                    'height' => 75,
                                    'width' => 75,
                                    'type' => 'image',
                                    'subtype' => 'photo',
                                    'caption' => 'Benjamin Netanyahu at the United Nations General Assembly today.',
                                    'copyright' => 'Dave Sanders for The New York Times'
                                ]
                            ]
                        ]
                    ]
                ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('environment');
        $this->assertCount(1, $articles);
        $this->assertEquals('The New York Times', $articles[0]['author']);
    }

    public function test_it_handles_api_errors_gracefully()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response(
                [
                    'fault' => [
                        'faultstring' => 'Invalid ApiKey',
                        'detail' => [
                            'errorcode' => 'oauth.v2.InvalidApiKey'
                        ]
                    ]
                ], Response::HTTP_UNAUTHORIZED)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_http_errors_gracefully()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([], Response::HTTP_INTERNAL_SERVER_ERROR)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_network_errors_gracefully()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([], Response::HTTP_REQUEST_TIMEOUT)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_maps_categories_correctly()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([
                'status' => 'OK',
                'copyright' => 'Copyright (c) 2025 The New York Times Company. All Rights Reserved.',
                'num_results' => 0,
                'results' => []
            ], Response::HTTP_OK)
        ]);


        $this->service->fetchArticles('technology');
        $this->service->fetchArticles('business');
        $this->service->fetchArticles('sports');
        $this->service->fetchArticles('entertainment');
        $this->service->fetchArticles('health');
        $this->service->fetchArticles('science');
        $this->service->fetchArticles('general');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/technology.json?limit=50&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/business.json?limit=50&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/sports.json?limit=50&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/arts.json?limit=50&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/health.json?limit=50&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/science.json?limit=50&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.nytimes.com/svc/news/v3/content/nyt/world.json?limit=50&api-key=test-api-key';
        });
    }

    public function test_it_returns_correct_source_identifier()
    {
        $source = $this->service->getSource();

        $this->assertEquals('nytimes', $source);
    }

    public function test_it_handles_empty_response()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([
                'status' => 'OK',
                'copyright' => 'Copyright (c) 2025 The New York Times Company. All Rights Reserved.',
                'num_results' => 0,
                'results' => []
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_missing_fields_gracefully()
    {
        Http::fake([
            'api.nytimes.com/svc/*' => Http::response([
                'status' => 'OK',
                'copyright' => 'Copyright (c) 2025 The New York Times Company. All Rights Reserved.',
                'num_results' => 50,
                'results' => [
                    [
                        'slug_name' => '',
                        'url' => '',
                        'abstract' => '',
                        'multimedia' => [],
                        'published_date' => '2025-09-26T18:00:16-04:00',
                    ]
                ]
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertCount(1, $articles);
        $this->assertEquals('', $articles[0]['external_id']);
        $this->assertEquals('', $articles[0]['title']);
        $this->assertEquals('', $articles[0]['summary']);
        $this->assertEquals('', $articles[0]['content']);
        $this->assertEquals('', $articles[0]['url']);
        $this->assertEquals(null, $articles[0]['image_url']);
        $this->assertEquals('The New York Times', $articles[0]['author']);
    }

}
