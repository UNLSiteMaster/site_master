
#Get the average GPA of all sites
SELECT AVG(gpa)
FROM
  (

    SELECT scans.gpa as gpa, max(scans.id) as id
    FROM scans
    GROUP BY scans.sites_id
  ) t