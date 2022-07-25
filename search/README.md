# Search Engine

A search engine crawling tool that extracts metadata for a set of keywords.

## Requirements
PHP >= 7.2

## Installation

Install using composer.

```bash
composer require Malick/search-engine
```

## Usage

```php

// skip this if using Laravel
require __DIR__.'/vendor/autoload.php';

// instantiation
$searchEngine = new Malick\SearchEngine();

// searching & getting results
$results = $searchEngine->search('topic');

```

### Returned Data
search function will return the results as an instance of ArrayIterator.
Each record will be an object that has the following properties:

```php

foreach($results as $result) {
    print($result->keyword); // keyword being searched
    print($result->ranking); // result's ranking (the topmost result would be 0)
    print($result->url); // result's URL
    print($result->title); // title of the page (as it appears in google search)
    print($result->description); // result's description
    print($result->promoted); // true if the result is an ad (boolean)
}

```

## Options

#### Search for multiple keywords

```php

// get search results for an array of keywords
$searchEngine->search(['topic A', 'topic B']);

```

#### Change engine

Currently two engines are supported, 'google.com' and 'google.ae'.

Default engine is 'google.com'.

```php

// setting engine
$searchEngine->setEngine('google.ae');

```

#### Change the number of pages

You can set how many pages to search (for each keyword).

Default is 5 pages.

```php

// setting number of pages
$searchEngine->setPagesCount(1);

```

#### Using method chaining

You can simply use method chaining.

```php

// using method chaining
$results = $searchEngine->setEngine('google.ae')
             ->setPagesCount(1)
             ->search(['topic A', 'topic B']);


```

## License
[MIT](https://choosealicense.com/licenses/mit/)