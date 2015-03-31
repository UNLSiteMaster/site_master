ALTER TABLE sites ADD site_map_url VARCHAR(2100);
ALTER TABLE sites ADD crawl_method ENUM('CRAWL_ONLY', 'SITE_MAP_ONLY', 'HYBRID') NOT NULL DEFAULT 'HYBRID';
ALTER TABLE scanned_page ADD found_with ENUM('SITE_MAP', 'CRAWL') NOT NULL DEFAULT 'CRAWL';
ALTER TABLE scanned_page_links ADD INDEX scanned_page_links_last_uncached (date_created, scanned_page_id, original_url_hash, cached)