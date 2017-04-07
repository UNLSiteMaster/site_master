ALTER TABLE scanned_page ADD daemon_name VARCHAR(256) NULL;
ALTER TABLE scanned_page ADD INDEX scanned_page_daeomon_name (daemon_name)