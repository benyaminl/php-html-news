# HTML RSS AGREGATOR

This is a garage project that mimik like 68k.news, so old browser like IE8 or mosaic could access the pages

this project for now only focus on Indonesia News provided by Kompas and kontan based rss (as only them that have rss)

NO DB needed, only flat file cache. Hope this work well

## Folder Structure 

-- index.php (the home page, show latest rss that's fetched by server)
-- news (the cache folde of the news that's parsed into plain HTML)
-- news/index.php (the parser that generate html from the URL)
-- system/parser/kontan.php (for kontan)
-- system/parser/kompas.php (for kompas)
