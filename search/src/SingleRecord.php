<?php

namespace Malick;

class SingleRecord {
    public function __construct($keyword, $ranking, $url, $title, $description, $promoted = false)
    {
        $this->keyword = $keyword;
        $this->ranking = $ranking;
        $this->url = $url;
        $this->title = $title;
        $this->description = $description;
        $this->promoted = $promoted;
    }
}