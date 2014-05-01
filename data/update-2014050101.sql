ALTER TABLE scans ADD date_updated DATETIME  COMMENT 'The date that this scan was last updated' AFTER date_created;
ALTER TABLE scanned_page ADD num_errors INT COMMENT 'The total number of errors found';
ALTER TABLE scanned_page ADD num_notices INT COMMENT 'The total number of notices found';