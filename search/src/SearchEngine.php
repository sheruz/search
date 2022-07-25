<?php

namespace Malick;

require_once __DIR__ . '/SingleRecord.php';

class SearchEngine
{
    private $engine;
    private $ranking;
    private $keyword;
    public $results;
    private $pagesCount;

    public function __construct($engine = 'google.com')
    {
        $this->engine = $engine;
        $this->results = new \ArrayIterator();
        $this->pagesCount = 5;
    }

    public function setEngine($engine)
    {
        try {
            $engineParts = explode('.', $engine);
            if ($engineParts[0] !== 'google') return;
            if (!in_array($engineParts[1], ['com', 'ae', 'sa'])) return;
            $this->engine = $engine;
        } catch (\Throwable $th) {
            //throw $th;
            print($th);
        }
        return $this;
    }

    public function setPagesCount($count)
    {
        $this->pagesCount = $count;
        return $this;
    }

    public function search($keywords)
    {
        $keywordsType = gettype($keywords);
        if (!in_array($keywordsType, ['string', 'array'])) return [];

        $keywords = $keywordsType == 'string' ? [$keywords] : $keywords;

        // repeat this 5 times to fetch 5 pages
        foreach ($keywords as $keyword) {
            $this->keyword = $keyword;
            $this->ranking = 0;

            for ($i = 0; $i < $this->pagesCount; $i++) {
                $link = $this->generateLink($i);
                $html = $this->getHtml($link);
                if (!$html) break;
                $this->processHtml($html);
            }
        }

        return $this->results;
    }

    private function generateLink($round)
    {
        $start = $round * 10;
        return "https://www.$this->engine/search?start=$start&q=" . str_replace(' ', '+', strval($this->keyword));
    }

    private function getHtml($link)
    {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                // 'user-agent: Mozilla/5.0',
                'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:102.0) Gecko/20100101 Firefox/102.0',
                "Content-Type: text/html; charset=UTF-8",
            ]);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            $response = curl_exec($ch);
            if (! $response) return '';
            return $response;
        } catch (\Throwable $th) {
            return '';
        }
    }

    private function processHtml($html)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        foreach ($xpath->query('//div[@id="tads"]/div[@class="uEierd"]') as $topAd) {
            $this->saveRow($topAd, true);
        }

        foreach ($dom->getElementsByTagName('h3') as $key => $item) {
            $parent = $item->parentNode;
            if ($parent->nodeName == 'a') {
                $grand = $parent->parentNode->parentNode;
                $this->saveRow($grand);
            }
        }

        foreach ($xpath->query('//div[@id="bottomads"]/div[@class="uEierd"]') as $bottomAd) {
            $this->saveRow($bottomAd, true);
        }
    }

    private function saveRow($item, $promoted = false)
    {
        $record = new SingleRecord(
            $this->keyword,
            $this->ranking,
            $promoted ? $this->getAdLink($item) : $this->getLink($item),
            $promoted ? $this->getAdTitle($item) : $this->getTitle($item),
            $promoted ? $this->getAdDescription($item) : $this->getDescription($item),
            $promoted,
        );
        $this->ranking++;
        $this->results->append($record);
    }

    private function getTitle($item)
    {
        $h3 = $item->getElementsByTagName('h3');
        return $h3[0]->textContent;
    }

    private function getAdLinkNode($item)
    {
        return $item->getElementsByTagName('div')[0]->getElementsByTagName('div')[0]->getElementsByTagName('div')[0]->getElementsByTagName('div')[0]->getElementsByTagName('a')[0];
    }

    private function getAdTitle($item)
    {
        $linkNode = $this->getAdLinkNode($item);
        return $linkNode->getElementsByTagName('div')[0]->getElementsByTagName('span')[0]->textContent;
    }

    private function getLink($item)
    {
        $link = $item->getElementsByTagName('a')[0]->getAttribute('href');
        $link = str_replace('/url?q=', '', $link);
        return $link;
    }

    private function getAdLink($item)
    {
        $linkNode = $this->getAdLinkNode($item);
        return $linkNode->getAttribute('href');
    }

    private function getDescription($item)
    {
        $childNodes = $item->childNodes;
        switch (count($childNodes)) {
            case 2:
                $description = $childNodes[1]->textContent;
                break;
            case 1:
                $description = $item->parentNode->childNodes[1]->textContent;
                break;
            default:
                $description = '';
                break;
        }
        return $description;
    }

    private function getAdDescription($item)
    {
        return $item->getElementsByTagName('div')->item(2)->childNodes->item(1)->textContent;
    }
}
