ALTER TABLE sites ADD site_map_url VARCHAR(2100);
ALTER TABLE sites ADD crawl_method ENUM('CRAWL_ONLY', 'SITE_MAP_ONLY', 'HYBRID') NOT NULL DEFAULT 'HYBRID';
ALTER TABLE scanned_page ADD found_with ENUM('SITE_MAP', 'CRAWL') NOT NULL DEFAULT 'CRAWL';