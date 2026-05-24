<?php

test('all returns all submissions with designer and submitter, newest first', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $u1 = insert_user($pdo, ['username' => 'Alice']);
    $u2 = insert_user($pdo, ['username' => 'Bob']);
    $did = insert_designer($pdo, ['name' => 'Gateron']);

    insert_submission($pdo, ['user_id' => $u1, 'name' => 'Older', 'designer_id' => $did, 'created_at' => '2024-01-01 00:00:00', 'status' => 'Pending']);
    insert_submission($pdo, ['user_id' => $u2, 'name' => 'Newer', 'designer_id' => $did, 'created_at' => '2024-06-01 00:00:00', 'status' => 'Approved']);

    $repo  = new SubmissionRepository($pdo);
    $rows  = $repo->all();
    $names = array_column($rows, 'name');

    assertSame(['Newer', 'Older'], $names);
    assertSame('Gateron', $rows[0]['designer_name']);
    assertSame('Bob', $rows[0]['submitter_username']);
});

test('filtered returns only submissions matching the given status', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    insert_submission($pdo, ['name' => 'A', 'status' => 'Pending']);
    insert_submission($pdo, ['name' => 'B', 'status' => 'Approved']);
    insert_submission($pdo, ['name' => 'C', 'status' => 'Pending']);

    $repo  = new SubmissionRepository($pdo);
    $names = array_column($repo->filtered('Pending'), 'name');

    assertSame(['C', 'A'], $names);
});

test('findById returns a submission with designer and submitter', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $uid = insert_user($pdo, ['username' => 'Charlie']);
    $did = insert_designer($pdo, ['name' => 'JWK']);
    $sid = insert_submission($pdo, ['user_id' => $uid, 'designer_id' => $did, 'name' => 'Oil King', 'switch_type' => 'Linear']);

    $repo = new SubmissionRepository($pdo);
    $sub  = $repo->findById($sid);

    assertSame('Oil King', $sub['name']);
    assertSame('JWK', $sub['designer_name']);
    assertSame('Charlie', $sub['submitter_username']);
    assertSame(null, $repo->findById(999));
});

test('update changes editable fields on a submission', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $sid  = insert_submission($pdo, ['name' => 'Old', 'switch_type' => 'Linear']);
    $repo = new SubmissionRepository($pdo);

    $repo->update($sid, ['name' => 'New Name', 'bottom_out_force' => '67']);

    $sub = $repo->findById($sid);
    assertSame('New Name', $sub['name']);
    assertSame('67.0', $sub['bottom_out_force']);
    assertSame('Linear', $sub['switch_type']); // unchanged
});

test('approve creates a switch from submission data and marks it Approved', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $uid     = insert_user($pdo, ['username' => 'Submitter']);
    $adminId = insert_user($pdo, ['username' => 'Admin', 'role' => 'admin']);
    $did     = insert_designer($pdo, ['name' => 'Gateron']);

    $repo  = new SubmissionRepository($pdo);
    $sid   = $repo->create($uid, ['name' => 'Oil King', 'designer_id' => $did, 'switch_type' => 'Linear', 'bottom_out_force' => '62', 'sound_profile' => 'Creamy']);

    $switchId = $repo->approve($sid, $adminId);
    $sub      = $repo->findById($sid);

    // Submission updated
    assertSame('Approved', $sub['status']);
    assertSame($adminId, (int) $sub['reviewed_by']);

    // Switch created
    $switchRepo = new SwitchRepository($pdo);
    $switch = $switchRepo->findById($switchId);
    assertSame('Oil King', $switch['name']);
    assertSame('oil-king', $switch['slug']);
    assertSame('Linear', $switch['switch_type']);
    assertSame('62.0', (string) $switch['bottom_out_force']);
    assertSame('approved', $switch['status']);
    assertSame($uid, (int) $switch['submitted_by']);
    assertSame($adminId, (int) $switch['approved_by']);
});

test('reject marks the submission as Rejected and sets reviewed_by', function () {
    $pdo = test_pdo();
    reset_db($pdo);

    $adminId = insert_user($pdo, ['role' => 'admin']);
    $sid     = insert_submission($pdo, ['name' => 'Bad Switch']);

    $repo = new SubmissionRepository($pdo);
    $repo->reject($sid, $adminId);

    $sub = $repo->findById($sid);
    assertSame('Rejected', $sub['status']);
    assertSame($adminId, (int) $sub['reviewed_by']);
});
