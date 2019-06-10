<?php
$sites = array();

foreach ($context as $site) {
    $sites[$site->base_url]['site_id']  = $site->id;
    $sites[$site->base_url]['support_email']  = $site->support_email;
    $sites[$site->base_url]['support_groups'] = $site->support_groups;
    $sites[$site->base_url]['title']          = $site->title;
    $sites[$site->base_url]['production_status'] = $site->production_status;
    $sites[$site->base_url]['group_name'] = $site->group_name;
    $sites[$site->base_url]['gpa'] = false;
    
    if ($scan = $site->getLatestScan()) {
        $sites[$site->base_url]['gpa'] = $scan->gpa;
    }
    
    $members = array();
    foreach ($site->getApprovedMembers() as $member) {
        $user = $member->getUser();
        
        $roles = array();
        foreach ($member->getRoles() as $role) {
            if (!$role->isApproved()) {
                //They might have individual unapproved roles
                continue;
            }
            $roles[] = $role->getRole()->role_name;
        }
        
        $members[$member->id] = array(
            'provider' => $user->provider,
            'uid' => $user->uid,
            'roles' => $roles
        );
    }
    
    
    $sites[$site->base_url]['members'] = $members;
}

echo json_encode($sites, JSON_PRETTY_PRINT);
