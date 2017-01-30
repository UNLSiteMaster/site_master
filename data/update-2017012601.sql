ALTER TABLE sites ADD group_name VARCHAR(256) NOT NULL default 'default';
ALTER TABLE sites ADD group_is_overridden ENUM('YES', 'NO') NOT NULL DEFAULT 'NO';
