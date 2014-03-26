
#Get the average GPA of all sites
SELECT AVG(gpa)
FROM
  (
    SELECT scans.gpa as gpa, max(scans.id) as id
    FROM scans
      WHERE status = 'COMPLETE'
    GROUP BY scans.sites_id
  ) t;
  
#Get all users with emails (who are not operators)
select users.uid, users.first_name, users.last_name, users.email
FROM users
  LEFT JOIN site_members ON site_members.users_id = users.id
  LEFT JOIN site_member_roles ON site_member_roles.site_members_id = site_members.id
  LEFT JOIN roles ON site_member_roles.roles_id = roles.id
WHERE roles.role_name NOT IN ('operator')
      AND users.email IS NOT NULL
GROUP BY users.uid;

#number of sites in 4.0
SELECT COUNT(*)
FROM
  (
    SELECT unl_scan_attributes.id, max(scans.id)
    FROM unl_scan_attributes
      JOIN scans on (unl_scan_attributes.scans_id = scans.id)
    WHERE scans.status = 'COMPLETE'
          AND unl_scan_attributes.html_version = '4.0'
          AND unl_scan_attributes.dep_version >= '4.0'
    GROUP by scans.sites_id
  ) as t;
  
#top marks
SELECT COUNT(marks_id) as count, page_marks.marks_id, marks.name
FROM page_marks
  JOIN scanned_page ON (scanned_page.id = page_marks.scanned_page_id)
  JOIN (SELECT MAX(scans.id) as id
        FROM scanned_page
          JOIN scans on (scanned_page.scans_id = scans.id)
        WHERE scans.status = 'COMPLETE'
        GROUP BY scans.sites_id
       ) as completed_scans ON completed_scans.id = scanned_page.scans_id
  JOIN marks ON (page_marks.marks_id = marks.id)
GROUP BY page_marks.marks_id
ORDER BY count DESC;