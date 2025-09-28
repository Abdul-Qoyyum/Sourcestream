<?php

namespace Tests\Unit\Services;

use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Http\Services\GuardianService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class GuardianServiceTest extends TestCase
{
    private GuardianService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.guardian.key', 'test-api-key');
        $this->service = new GuardianService();
    }

    public function test_it_fetches_articles_successfully()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response(
                [
                    'response' => [
                        'status' => 'ok',
                        'userTier' => 'developer',
                        'total' => 1,
                        'startIndex' => 1,
                        'pageSize' => 50,
                        'currentPage' => 1,
                        'pages' => 1,
                        'orderBy' => 'newest',
                        'results' => [
                            [
                                'id' => 'tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                                'webTitle' => '‘A broken system full of criminality and death’: the podcast lifting the lid on what happens to the UK’s rubbish',
                                'webUrl' => 'https://www.theguardian.com/tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                                'webPublicationDate' => '2025-09-23T08:55:47Z',
                                'fields' => [
                                    'trailText' => 'Turkey’s recycling centres treat vast amounts of the UK’s waste – and rely on refugees who work in conditions so unsafe that hundreds have died. A new podcast uncovers the sinister side of what happens when Brits throw things away',
                                    'bodyText' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                                    'thumbnail' => 'https://media.guim.co.uk/fe7528e16e28d6361e6683b9058db93f42e8b665/912_0_4560_3648/500.jpg',
                                    'byline' => 'Daniel Dylan Wray',
                                ],
                                'tags' => [
                                    [
                                        'id' => 'tv-and-radio/podcasts',
                                        'type' => 'keyword',
                                        'sectionId' => 'tv-and-radio',
                                        'sectionName' => 'Television & radio',
                                        'webTitle' => 'Podcasts',
                                        'webUrl' => 'https://www.theguardian.com/tv-and-radio/podcasts',
                                        'apiUrl' => 'https://content.guardianapis.com/tv-and-radio/podcasts',
                                        'references' => []
                                    ],
                                    [
                                        'id' => 'tv-and-radio/tv-and-radio',
                                        'type' => 'keyword',
                                        'sectionId' => 'tv-and-radio',
                                        'sectionName' => 'Television & radio',
                                        'webTitle' => 'Television & radio',
                                        'webUrl' => 'https://www.theguardian.com/tv-and-radio/tv-and-radio',
                                        'apiUrl' => 'https://content.guardianapis.com/tv-and-radio/tv-and-radio',
                                        'references' => []
                                    ]
                                ],
                                'isHosted' => false,
                                'pillarId' => 'pillar/arts',
                                'pillarName' => 'Arts'
                            ]
                        ]
                    ]
                ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('environment');
        $this->assertCount(1, $articles);
        $this->assertEquals('‘A broken system full of criminality and death’: the podcast lifting the lid on what happens to the UK’s rubbish', $articles[0]['title']);
        $this->assertEquals('Turkey’s recycling centres treat vast amounts of the UK’s waste – and rely on refugees who work in conditions so unsafe that hundreds have died. A new podcast uncovers the sinister side of what happens when Brits throw things away', $articles[0]['summary']);
        $this->assertEquals('Daniel Dylan Wray', $articles[0]['author']);
    }

    public function test_it_extracts_author_from_byline()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'status' => 'ok',
                    'userTier' => 'developer',
                    'total' => 1,
                    'startIndex' => 1,
                    'pageSize' => 50,
                    'currentPage' => 1,
                    'pages' => 1,
                    'orderBy' => 'newest',
                    'results' => [
                        [
                            'id' => 'tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                            'webTitle' => '‘A broken system full of criminality and death’: the podcast lifting the lid on what happens to the UK’s rubbish',
                            'webUrl' => 'https://www.theguardian.com/tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                            'webPublicationDate' => '2025-09-23T08:55:47Z',
                            'fields' => [
                                'trailText' => 'Turkey’s recycling centres treat vast amounts of the UK’s waste – and rely on refugees who work in conditions so unsafe that hundreds have died. A new podcast uncovers the sinister side of what happens when Brits throw things away',
                                'bodyText' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                                'thumbnail' => 'https://media.guim.co.uk/fe7528e16e28d6361e6683b9058db93f42e8b665/912_0_4560_3648/500.jpg',
                                'byline' => 'Daniel Dylan Wray',
                            ],
                            'tags' => [],
                            'isHosted' => false,
                            'pillarId' => 'pillar/arts',
                            'pillarName' => 'Arts'
                        ]
                    ]
                ]
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('environment');

        $this->assertCount(1, $articles);
        $this->assertEquals('Daniel Dylan Wray', $articles[0]['author']);
    }

    public function test_it_uses_default_author_when_no_author_information()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'status' => 'ok',
                    'userTier' => 'developer',
                    'total' => 1,
                    'startIndex' => 1,
                    'pageSize' => 50,
                    'currentPage' => 1,
                    'pages' => 1,
                    'orderBy' => 'newest',
                    'results' => [
                        [
                            'id' => 'tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                            'webTitle' => '‘A broken system full of criminality and death’: the podcast lifting the lid on what happens to the UK’s rubbish',
                            'webUrl' => 'https://www.theguardian.com/tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                            'webPublicationDate' => '2025-09-23T08:55:47Z',
                            'fields' => [
                                'trailText' => 'Turkey’s recycling centres treat vast amounts of the UK’s waste – and rely on refugees who work in conditions so unsafe that hundreds have died. A new podcast uncovers the sinister side of what happens when Brits throw things away',
                                'bodyText' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.',
                                'thumbnail' => 'https://media.guim.co.uk/fe7528e16e28d6361e6683b9058db93f42e8b665/912_0_4560_3648/500.jpg',
                                'byline' => null, // No byline
                            ],
                            'tags' => [],
                            'isHosted' => false,
                            'pillarId' => 'pillar/arts',
                            'pillarName' => 'Arts'
                        ]
                    ]
                ]
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('environment');
        $this->assertCount(1, $articles);
        $this->assertEquals('The Guardian', $articles[0]['author']);
    }

    public function test_it_handles_api_errors_gracefully()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([
                    'message' => 'Unauthorized'
                ], Response::HTTP_UNAUTHORIZED)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_http_errors_gracefully()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([], Response::HTTP_INTERNAL_SERVER_ERROR)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_network_errors_gracefully()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([], Response::HTTP_REQUEST_TIMEOUT) // Timeout
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_maps_categories_correctly()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'status' => 'ok',
                    'results' => []
                ]
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
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=technology&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=business&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=sport&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=culture&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=society&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=science&api-key=test-api-key';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://content.guardianapis.com/search?show-fields=all&show-tags=all&page-size=50&order-by=newest&section=world&api-key=test-api-key';
        });

    }

    public function test_it_returns_correct_source_identifier()
    {
        $source = $this->service->getSource();

        $this->assertEquals('guardian', $source);
    }

    public function test_it_handles_empty_response()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'status' => 'ok',
                    'results' => []
                ]
            ], Response::HTTP_OK)
        ]);

        $articles = $this->service->fetchArticles('technology');

        $this->assertEmpty($articles);
    }

    public function test_it_handles_missing_fields_gracefully()
    {
        Http::fake([
            'content.guardianapis.com/search*' => Http::response([
                'response' => [
                    'status' => 'ok',
                    'results' => [
                        [
                            'id' => 'tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                            'webTitle' => 'A realistic system',
                            'webUrl' => 'https://www.theguardian.com/tv-and-radio/2025/sep/23/boy-wasted-podcast-uk-rubbish-turkey-recycling-centres',
                            'webPublicationDate' => '2025-09-23T08:55:47Z',
                            //Assuming the fields array was missing
                            'tags' => [],
                            'isHosted' => false,
                            'pillarId' => 'pillar/arts',
                            'pillarName' => 'Arts'
                        ]
                    ]
                ]
            ], Response::HTTP_OK)
        ]);


        $articles = $this->service->fetchArticles('technology');

        $this->assertCount(1, $articles);
        $this->assertEquals('A realistic system', $articles[0]['title']);
        $this->assertEquals('', $articles[0]['summary']); // Empty when fields missing
        $this->assertEquals('', $articles[0]['content']); // Empty when fields missing
        $this->assertEquals(null, $articles[0]['image_url']); // Null when fields missing
        $this->assertEquals('The Guardian', $articles[0]['author']); // Default author
    }
}
