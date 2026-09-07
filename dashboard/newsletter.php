<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? '';
    $selectedIds = $_POST['subscriber_ids'] ?? [];

    if ($action === 'delete') {
        $message = newsletter_delete_subscribers($selectedIds) ? 'Selected subscribers deleted.' : 'No subscribers deleted.';
    }

    if ($action === 'send') {
        $subject = trim($_POST['subject'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $sendTo = $_POST['send_to'] ?? 'selected';
        $emails = $sendTo === 'all'
            ? array_column(newsletter_subscribers(true), 'email')
            : newsletter_emails_by_ids($selectedIds);

        if (!$emails) {
            $error = 'Please select at least one subscriber or choose Send to all.';
        } elseif ($subject === '' || $body === '') {
            $error = 'Subject and message are required.';
        } else {
            $sent = newsletter_send_promotional_email($emails, $subject, $body);
            $message = "Promotional email sent to {$sent} subscriber(s).";
            if ($sent === 0) {
                $error = 'Email sending failed. Check the SMTP configuration in Site Settings.';
            }
        }
    }
}

$subscribers = newsletter_subscribers();
$pageTitle = 'Newsletter - ' . $site['title'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100 text-slate-700">
<div class="flex min-h-screen">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="flex min-h-screen min-w-0 flex-1 flex-col">
        <?php require __DIR__ . '/topbar.php'; ?>
        <main class="px-5 pb-5 pt-0 lg:px-8 lg:pb-8 lg:pt-0">
            <?php if ($message): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-bold text-red-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]" method="post">
                <?php echo csrf_field(); ?>
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Subscribers</p>
                            <h2 class="mt-1 text-xl font-black text-slate-950"><?php echo count($subscribers); ?> Email(s)</h2>
                        </div>
                        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-black text-white hover:bg-red-700" type="submit" name="action" value="delete" onclick="return confirm('Delete selected subscribers?')">
                            <i class="fa-solid fa-trash mr-2"></i>Delete
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="w-12 px-4 py-3">
                                        <input class="h-4 w-4 accent-blue-700" type="checkbox" data-check-all>
                                    </th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Subscribed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($subscribers as $subscriber): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3">
                                            <input class="h-4 w-4 accent-blue-700" type="checkbox" name="subscriber_ids[]" value="<?php echo e((string) $subscriber['id']); ?>" data-subscriber-check>
                                        </td>
                                        <td class="px-4 py-3 font-black text-slate-800"><?php echo e($subscriber['email']); ?></td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700"><?php echo $subscriber['is_active'] ? 'Active' : 'Inactive'; ?></span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-500"><?php echo e((string) $subscriber['subscribed_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$subscribers): ?>
                                    <tr>
                                        <td class="px-4 py-10 text-center text-slate-500" colspan="4">No newsletter subscribers yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <aside class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Promotional Email</p>
                    <div class="mt-4 grid gap-4">
                        <label class="block text-sm font-black text-slate-600">Send To
                            <select class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="send_to">
                                <option value="selected">Checked subscribers</option>
                                <option value="all">All active subscribers</option>
                            </select>
                        </label>
                        <label class="block text-sm font-black text-slate-600">Subject
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="subject" placeholder="Promotional email subject">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Message
                            <textarea class="mt-2 min-h-60 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7" name="body" placeholder="Write promotional email message"></textarea>
                        </label>
                        <button class="inline-flex items-center justify-center rounded-xl bg-blue-700 px-5 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950" type="submit" name="action" value="send">
                            <i class="fa-solid fa-paper-plane mr-2"></i>Send Email
                        </button>
                    </div>
                </aside>
            </form>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const all = document.querySelector('[data-check-all]');
        const checks = Array.from(document.querySelectorAll('[data-subscriber-check]'));

        all?.addEventListener('change', () => {
            checks.forEach(check => check.checked = all.checked);
        });
    })();
</script>
</body>
</html>
