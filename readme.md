# HTML RSS AGREGATOR

This is a garage project that mimik like 68k.news, so old browser like IE8 or mosaic or old Firefox (v0.9-pre XUL) could access the pages

this project for now only focus on Indonesia News provided by kontan based rss (as only them that have rss)

NO DB needed, only flat file cache HTML

working/online app http://news.benyamin.xyz

## Folder Structure 

```
-- index.php (the home page, show latest rss that's fetched by server)
-- news (the cache folde of the news that's parsed into plain HTML)
-- news/index.php (the parser that generate html from the URL)
-- system/parser/kontan.php (for kontan)
-- system/parser/jagatreview.php (for JGR)
```

## Clean and Update News List and Cache

For cleaning cache of html and rss periodically, you need to run 
```bash
*/5 * * * * rm /location/of/the/app/rss/*xml

0 0 * * 2,4,6 rm /location/of/the/app/news/*html
```

You could put this on cron or task scheduler (based on the OS, I use Cron on Linux)
*/5 means every minutes

2,3,4 means every tuesday, thursday, and saturday

## Thanks to 
Kontan.co.id (do support them! only them now that still support rss, other way, kompas.com and other need extensive page crawl, I will add it later)

JagatReview.com (BEST INDONESIAN SITE FOR REVIEW!)
