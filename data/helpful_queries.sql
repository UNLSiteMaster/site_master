
#Get the average GPA of all sites
SELECT AVG(gpa)
FROM
  (

    SELECT scans.gpa as gpa, max(scans.id) as id
    FROM scans
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