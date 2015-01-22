import scrapy
from scrapy.contrib.spiders import CrawlSpider, Rule
from scrapy.contrib.linkextractors.sgml import SgmlLinkExtractor
from scrapy.contrib.linkextractors import LinkExtractor
from scrapy.contrib.linkextractors.regex import RegexLinkExtractor
from scrapy.selector import HtmlXPathSelector
from scrapy.utils.url import urlparse 
from scrapy.http import Request


class VisionSpider(CrawlSpider):
    name = "vision"
    start_urls = ["http://www.visions.ca/"]
rules = (
		Rule(RegexLinkExtractor(allow=('/Catalogue/Category',)) , follow=True, callback='parse_items'),
		Rule(RegexLinkExtractor() , callback='parse_items')
		)
def parse_items(self, response):	
	for sel in response.xpath('//div[@class="contentright"]'):
			title = sel.xpath('h2/a/text()').extract()
			link = sel.xpath('a/@href').extract()
			desc = sel.xpath('text()').extract()
			departments = sel.xpath('a/span/text()').extract()
			print departments, title, link, desc
			return title
			
def link_filtering(self, links):
        ret = []
        for link in links:
            parsed_url = urlparse.urljoin('http://www.visions.ca/' + link.url)
			
            ret.append(parsed_url)
        return ret
