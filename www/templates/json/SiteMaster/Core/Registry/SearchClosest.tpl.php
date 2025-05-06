<?php

$site = $context->getSite();

if (!$site) {
    echo "{}";
} else {
    $result = array();

    $result['id'] = $site->id;
    $result['base_url'] = $site->base_url;
    $result['support_email']  = $site->support_email;
    $result['support_groups'] = $site->support_groups;
    $result['title']          = $site->title;
    $result['production_status'] = $site->production_status;
    $result['group_name'] = $site->group_name;
    $result['gpa'] = false;

    if ($scan = $site->getLatestScan()) {
        $result['gpa'] = $scan->gpa;
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


    $result['members'] = $members;

    echo json_encode($result, JSON_PRETTY_PRINT);
}
