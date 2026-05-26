<?php
/**
 * @var array $submissions      The user's own switch submissions.
 * @var array $audioSubmissions The user's own audio (recording) submissions.
 */
?>
<div class="my-submissions-page">
    <h1>My Submissions</h1>

    <section class="my-submissions-page__section">
        <h2>Switch Submissions</h2>

        <?php if (empty($submissions)): ?>
            <p class="empty-state">
                You haven't submitted any switches yet.<br>
                <a href="<?= url('/submit') ?>">Submit one →</a>
            </p>
        <?php else: ?>
            <table class="submission-table">
                <thead>
                    <tr>
                        <th>Switch</th>
                        <th>Designer or Studio</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $s): ?>
                        <tr>
                            <td><?= e($s['name']) ?></td>
                            <td><?= e(or_unknown($s['designer_name'])) ?></td>
                            <td>
                                <span class="status status--<?= e(strtolower($s['status'])) ?>">
                                    <?= e($s['status']) ?>
                                </span>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($s['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <section class="my-submissions-page__section">
        <h2>Audio Submissions</h2>

        <?php if (empty($audioSubmissions)): ?>
            <p class="empty-state">
                You haven't submitted any recordings yet.<br>
                <a href="<?= url('/switches') ?>">Find a switch to record →</a>
            </p>
        <?php else: ?>
            <table class="submission-table">
                <thead>
                    <tr>
                        <th>Switch</th>
                        <th>Recording</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($audioSubmissions as $a): ?>
                        <tr>
                            <td>
                                <?php if (!empty($a['switch_slug'])): ?>
                                    <a href="<?= url('/switches/' . $a['switch_slug']) ?>">
                                        <?= e($a['switch_name']) ?>
                                    </a>
                                <?php else: ?>
                                    <?= e(or_unknown($a['switch_name'])) ?>
                                <?php endif; ?>
                            </td>
                            <td><audio controls src="<?= url($a['audio_url']) ?>"></audio></td>
                            <td>
                                <span class="status status--<?= e(strtolower($a['status'])) ?>">
                                    <?= e($a['status']) ?>
                                </span>
                            </td>
                            <td><?= e(date('M j, Y', strtotime($a['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
