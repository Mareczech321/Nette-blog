<?php

namespace App\Model;

use Nette\Caching\Cache;
use Nette\Caching\Storage;
use App\Model\Mapper\PostUrlMapper;
class FeedManager
{
    private Cache $cache;

    public function __construct(
        Storage $storage,
        private PostFacade $postFacade,
        private ImageManager $imageManager,
        private PostUrlMapper $postUrlMapper
    ){
        $this->cache = new Cache($storage, 'rss_feed');
    }

    /**
     * @param int $adminId
     */
    public function stealNewestPost(int $adminId): void
    {
        $url = 'https://zive.cz/rss';

        $data = $this->cache->load('newest_article', function (&$dependencies) use ($url) {
            $dependencies[Cache::EXPIRE] = '2 hours';

            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n"
                ]
            ]);

            $xmlString = @file_get_contents($url, false, $context);

            if ($xmlString === false) {
                throw new \Exception('Cizí web blokuje připojení nebo neexistuje.');
            }

            $xml = @simplexml_load_string($xmlString);
            if (!$xml) {
                throw new \Exception('Nepodařilo se přečíst XML feed.');
            }

            $newestItem = null;
            $maxTimestamp = 0;

            foreach ($xml->channel->item as $item) {
                $pubDate = (string) $item->pubDate;
                $time = strtotime($pubDate);

                if ($time !== false && $time > $maxTimestamp) {
                    $maxTimestamp = $time;
                    $newestItem = $item;
                }
            }

            if (!$newestItem) {
                throw new \Exception('Feed neobsahuje žádné články.');
            }

            return [
                'title' => (string) $newestItem->title,
                'description' => (string) $newestItem->description,
                'image_url' => isset($newestItem->enclosure) ? (string) $newestItem->enclosure['url'] : null,
            ];
        });

        if (!is_array($data)) {
            throw new \Exception('Chyba dat v cache.');
        }

        $imagePathForDb = null;
        $imageUrl = $data['image_url'] ?? null;

        if ($imageUrl !== null) {
            $imagePathForDb = $this->imageManager->saveFromUrl($imageUrl);
        }

        $postDto = $this->postUrlMapper->mapRssToDTO(
            rssData: $data,
            userId: $adminId,
            imageName: $imagePathForDb
        );

        $this->postFacade->savePost(null, $postDto);
    }
}