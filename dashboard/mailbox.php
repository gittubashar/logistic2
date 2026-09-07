<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$message = (string) ($_SESSION['mailbox_message'] ?? '');
unset($_SESSION['mailbox_message']);
$error = (string) ($_SESSION['mailbox_error'] ?? '');
unset($_SESSION['mailbox_error']);
$selectedId = max(0, (int) ($_GET['id'] ?? $_POST['message_id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $contact = contact_message_by_id($selectedId);

    if (!$contact) {
        $error = 'The selected contact message was not found.';
    } elseif ($action === 'delete') {
        if (contact_message_delete($selectedId)) {
            $_SESSION['mailbox_message'] = 'Contact message deleted.';
            header('Location: ' . base_url('dashboard/mailbox.php'));
            exit;
        }
        $error = 'Unable to delete the contact message.';
    } elseif ($action === 'mark_unread') {
        contact_message_mark_unread($selectedId);
        $_SESSION['mailbox_message'] = 'Message marked as unread.';
        header('Location: ' . base_url('dashboard/mailbox.php') . '?id=' . $selectedId);
        exit;
    } elseif ($action === 'reply') {
        $replySubject = trim((string) ($_POST['reply_subject'] ?? ''));
        $replyBody = trim((string) ($_POST['reply_body'] ?? ''));

        if ($replySubject === '' || mb_strlen($replySubject) > 255) {
            $error = 'Enter a reply subject of up to 255 characters.';
        } elseif ($replyBody === '' || mb_strlen($replyBody) > 20000) {
            $error = 'Enter a reply message of up to 20,000 characters.';
        } else {
            $emailBody = '<div style="margin:0;background:#f1f5f9;padding:32px 16px;font-family:Arial,sans-serif;color:#334155">'
                . '<div style="max-width:680px;margin:auto;border:1px solid #e2e8f0;border-radius:16px;background:#ffffff;overflow:hidden">'
                . '<div style="background:#0f172a;padding:20px 24px;color:#ffffff;font-size:20px;font-weight:700">' . e((string) $site['title']) . '</div>'
                . '<div style="padding:24px;line-height:1.75">'
                . '<p style="margin-top:0">Hello ' . e((string) $contact['name']) . ',</p>'
                . '<div>' . nl2br(e($replyBody)) . '</div>'
                . '<p style="margin-bottom:0;margin-top:28px;color:#64748b">Regards,<br><strong>' . e((string) ($site['smtp']['from_name'] ?? $site['title'])) . '</strong></p>'
                . '</div></div></div>';
            $smtpError = '';

            if (smtp_send_html_email((string) $contact['email'], $replySubject, $emailBody, '', $smtpError)) {
                if (contact_message_record_reply($selectedId, $replySubject, $replyBody)) {
                    $_SESSION['mailbox_message'] = 'Reply sent successfully to ' . $contact['email'] . '.';
                } else {
                    $_SESSION['mailbox_error'] = 'Reply was sent to ' . $contact['email'] . ', but its history could not be saved. Please run the database migration.';
                }
                header('Location: ' . base_url('dashboard/mailbox.php') . '?id=' . $selectedId);
                exit;
            } else {
                $error = 'Reply not sent. ' . $smtpError;
            }
        }
    }
}

$messages = contact_messages();
if ($selectedId === 0 && $messages) {
    $selectedId = (int) $messages[0]['id'];
}
$selected = contact_message_by_id($selectedId);
if ($selected && $selected['status'] === 'unread') {
    contact_message_mark_read($selectedId);
    $selected['status'] = 'read';
    foreach ($messages as &$listedMessage) {
        if ((int) $listedMessage['id'] === $selectedId) {
            $listedMessage['status'] = 'read';
            break;
        }
    }
    unset($listedMessage);
}

$unreadCount = contact_unread_count();
$smtpReady = smtp_is_configured();
$pageTitle = 'Mailbox - ' . $site['title'];
$dashboardPageLabel = 'Mailbox';

function mailbox_date(string $value, string $format = 'M j, Y g:i A'): string
{
    $timestamp = strtotime($value);

    return $timestamp ? date($format, $timestamp) : $value;
}
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
        <main class="px-4 pb-5 pt-0 lg:px-6">
            <?php if ($message): ?>
                <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-800"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 font-bold text-red-700"><?php echo e($error); ?></div>
            <?php endif; ?>

            <section class="mb-4 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">All messages</p>
                    <strong class="mt-1 block text-3xl font-black text-slate-950"><?php echo count($messages); ?></strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-500">Unread</p>
                    <strong class="mt-1 block text-3xl font-black text-blue-700"><?php echo $unreadCount; ?></strong>
                </div>
                <div class="rounded-2xl border <?php echo $smtpReady ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50'; ?> p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider <?php echo $smtpReady ? 'text-emerald-700' : 'text-amber-700'; ?>">SMTP</p>
                    <strong class="mt-1 block text-lg font-black text-slate-950"><?php echo $smtpReady ? 'Configured' : 'Not configured'; ?></strong>
                    <?php if (!$smtpReady): ?>
                        <a class="text-xs font-black text-blue-700 hover:underline" href="<?php echo e(base_url('dashboard/site-settings.php')); ?>">Open Site Settings</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="grid min-h-[650px] overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm xl:grid-cols-[380px_minmax(0,1fr)]">
                <aside class="border-b border-slate-200 xl:border-b-0 xl:border-r">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Contact inbox</p>
                        <h1 class="mt-1 text-xl font-black text-slate-950">Mailbox</h1>
                    </div>
                    <div class="max-h-[720px] overflow-y-auto divide-y divide-slate-100">
                        <?php foreach ($messages as $contact): ?>
                            <?php $isSelected = (int) $contact['id'] === $selectedId; ?>
                            <a class="block px-5 py-4 transition <?php echo $isSelected ? 'bg-blue-50' : 'hover:bg-slate-50'; ?>" href="<?php echo e(base_url('dashboard/mailbox.php') . '?id=' . $contact['id']); ?>">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <?php if ($contact['status'] === 'unread'): ?><span class="h-2.5 w-2.5 shrink-0 rounded-full bg-blue-600"></span><?php endif; ?>
                                            <strong class="block truncate text-sm text-slate-950"><?php echo e($contact['name']); ?></strong>
                                        </div>
                                        <span class="mt-1 block truncate text-xs font-bold text-slate-500"><?php echo e($contact['email']); ?></span>
                                    </div>
                                    <time class="shrink-0 text-[11px] font-bold text-slate-400"><?php echo e(mailbox_date((string) $contact['created_at'], 'M j')); ?></time>
                                </div>
                                <p class="mt-2 truncate text-sm font-bold text-slate-700"><?php echo e($contact['service'] ?: 'General enquiry'); ?></p>
                                <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500"><?php echo e($contact['message']); ?></p>
                                <?php if ($contact['status'] === 'replied'): ?>
                                    <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700"><i class="fa-solid fa-reply mr-1.5"></i>Replied</span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if (!$messages): ?>
                            <div class="px-6 py-16 text-center text-slate-500">
                                <i class="fa-regular fa-envelope-open mb-3 text-4xl text-slate-300"></i>
                                <p class="font-black text-slate-700">No contact messages yet</p>
                                <p class="mt-1 text-sm">New website enquiries will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>

                <div class="min-w-0">
                    <?php if ($selected): ?>
                        <article class="border-b border-slate-200 p-5 lg:p-7">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700"><?php echo e($selected['service'] ?: 'General enquiry'); ?></p>
                                    <h2 class="mt-1 text-2xl font-black text-slate-950"><?php echo e($selected['name']); ?></h2>
                                    <a class="mt-1 inline-flex items-center text-sm font-bold text-blue-700 hover:underline" href="mailto:<?php echo e($selected['email']); ?>"><?php echo e($selected['email']); ?></a>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <time class="mr-2 text-xs font-bold text-slate-500"><?php echo e(mailbox_date((string) $selected['created_at'])); ?></time>
                                    <form method="post">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="message_id" value="<?php echo (int) $selected['id']; ?>">
                                        <button class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50" type="submit" name="action" value="mark_unread"><i class="fa-regular fa-envelope mr-1.5"></i>Unread</button>
                                    </form>
                                    <form method="post" onsubmit="return confirm('Delete this contact message?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="message_id" value="<?php echo (int) $selected['id']; ?>">
                                        <button class="rounded-lg border border-red-200 px-3 py-2 text-xs font-black text-red-600 hover:bg-red-50" type="submit" name="action" value="delete"><i class="fa-solid fa-trash mr-1.5"></i>Delete</button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-6 whitespace-pre-wrap rounded-2xl bg-slate-50 p-5 text-sm leading-7 text-slate-700"><?php echo e($selected['message']); ?></div>
                        </article>

                        <div class="p-5 lg:p-7">
                            <?php if (!empty($selected['replied_at'])): ?>
                                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <strong class="text-sm text-emerald-800"><i class="fa-solid fa-circle-check mr-2"></i>Last reply sent</strong>
                                        <time class="text-xs font-bold text-emerald-700"><?php echo e(mailbox_date((string) $selected['replied_at'])); ?></time>
                                    </div>
                                    <p class="mt-2 text-sm font-black text-slate-800"><?php echo e($selected['reply_subject']); ?></p>
                                    <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-600"><?php echo e((string) $selected['reply_message']); ?></p>
                                </div>
                            <?php endif; ?>

                            <form method="post">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="message_id" value="<?php echo (int) $selected['id']; ?>">
                                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Reply by email</p>
                                        <h3 class="text-lg font-black text-slate-950">To <?php echo e($selected['email']); ?></h3>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-black <?php echo $smtpReady ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'; ?>"><?php echo $smtpReady ? 'SMTP ready' : 'SMTP required'; ?></span>
                                </div>
                                <label class="block text-sm font-black text-slate-600">Subject
                                    <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal text-slate-800" name="reply_subject" maxlength="255" value="<?php echo e((string) ($_POST['reply_subject'] ?? ('Re: ' . ($selected['service'] ?: 'Your website enquiry')))); ?>" required>
                                </label>
                                <label class="mt-4 block text-sm font-black text-slate-600">Message
                                    <textarea class="mt-2 min-h-52 w-full rounded-xl border border-slate-300 px-4 py-3 font-normal leading-7 text-slate-800" name="reply_body" maxlength="20000" placeholder="Write your reply..." required><?php echo e((string) ($_POST['reply_body'] ?? '')); ?></textarea>
                                </label>
                                <button class="mt-4 inline-flex items-center rounded-xl bg-blue-700 px-5 py-3 font-black text-white shadow-lg shadow-blue-700/20 hover:bg-slate-950 disabled:cursor-not-allowed disabled:opacity-50" type="submit" name="action" value="reply" <?php echo $smtpReady ? '' : 'disabled'; ?>>
                                    <i class="fa-solid fa-paper-plane mr-2"></i>Send Reply
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="grid min-h-[650px] place-items-center p-8 text-center text-slate-500">
                            <div><i class="fa-regular fa-envelope mb-4 text-6xl text-slate-200"></i><p class="font-black text-slate-700">Select a message to read it</p></div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
</body>
</html>
