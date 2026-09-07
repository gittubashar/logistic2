<?php
require_once __DIR__ . '/../includes/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . base_url('login.php'));
    exit;
}

$message = '';
$error = '';
$editPost = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $message = post_delete((int) ($_POST['id'] ?? 0)) ? 'Post deleted.' : 'Unable to delete post.';
    } else {
        $image = trim($_POST['image'] ?? '');
        $uploadedImage = upload_dashboard_file($_FILES['image_upload'] ?? [], 'posts');
        $image = $uploadedImage ?: $image;

        $payload = [
            'id' => (int) ($_POST['id'] ?? 0),
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'content_html' => page_clean_html(trim($_POST['content_html'] ?? '')),
            'image' => $image,
            'is_published' => isset($_POST['is_published']),
            'published_at' => trim($_POST['published_at'] ?? ''),
        ];

        if ($payload['title'] === '') {
            $error = 'Post title is required.';
        } else {
            $message = post_save($payload) ? 'Post saved successfully.' : 'Unable to save post. Check duplicate slug.';
        }
    }
}

if (isset($_GET['edit'])) {
    $editPost = post_find((int) $_GET['edit']);
}

$posts = posts_all();
$formPost = $editPost ?: [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content_html' => '',
    'image' => '',
    'is_published' => 1,
    'published_at' => date('Y-m-d H:i:s'),
];
$pageTitle = 'Post Manager - ' . $site['title'];
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

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_420px]">
                <form class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm" method="post" enctype="multipart/form-data" data-post-form>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id" value="<?php echo e((string) $formPost['id']); ?>">
                    <input type="hidden" name="content_html" data-post-editor-input value="<?php echo e(page_clean_html($formPost['content_html'] ?? '')); ?>">

                    <div class="mb-5 flex items-center justify-between border-b border-slate-200 pb-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700"><?php echo $formPost['id'] ? 'Edit Post' : 'New Post'; ?></p>
                            <h2 class="mt-1 text-xl font-black text-slate-950"><?php echo e($formPost['title'] ?: 'Blog Post'); ?></h2>
                        </div>
                        <label class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-sm font-black text-slate-700">
                            <input class="accent-blue-700" type="checkbox" name="is_published" <?php echo !empty($formPost['is_published']) ? 'checked' : ''; ?>>
                            Publish
                        </label>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <label class="block text-sm font-black text-slate-600">Title
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="title" value="<?php echo e($formPost['title'] ?? ''); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Slug
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="slug" value="<?php echo e($formPost['slug'] ?? ''); ?>" placeholder="post-slug">
                        </label>
                        <label class="block text-sm font-black text-slate-600 lg:col-span-2">Excerpt
                            <textarea class="mt-2 min-h-24 w-full rounded-xl border border-slate-300 px-4 py-3 leading-7" name="excerpt"><?php echo e($formPost['excerpt'] ?? ''); ?></textarea>
                        </label>
                        <label class="block text-sm font-black text-slate-600">Published At
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="published_at" value="<?php echo e((string) ($formPost['published_at'] ?? '')); ?>">
                        </label>
                        <label class="block text-sm font-black text-slate-600">Featured Image
                            <input class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3" name="image" value="<?php echo e($formPost['image'] ?? ''); ?>" placeholder="uploads/posts/image.jpg">
                        </label>
                        <label class="block text-sm font-black text-slate-600 lg:col-span-2">Pick image from computer
                            <input class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm" type="file" name="image_upload" accept="image/*">
                        </label>
                    </div>

                    <div class="mt-5">
                        <p class="mb-2 text-sm font-black text-slate-600">Content Editor</p>
                        <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white">
                            <div class="flex flex-wrap gap-2 border-b border-slate-200 bg-slate-50 p-3">
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="bold"><i class="fa-solid fa-bold"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="italic"><i class="fa-solid fa-italic"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="underline"><i class="fa-solid fa-underline"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="insertUnorderedList"><i class="fa-solid fa-list-ul"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="justifyLeft"><i class="fa-solid fa-align-left"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="justifyCenter"><i class="fa-solid fa-align-center"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="justifyRight"><i class="fa-solid fa-align-right"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-command="justifyFull"><i class="fa-solid fa-align-justify"></i></button>
                                <button class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-black text-slate-700" type="button" data-editor-link><i class="fa-solid fa-link"></i></button>
                            </div>
                            <div class="min-h-[420px] px-5 py-4 leading-8 outline-none prose max-w-none" contenteditable="true" data-post-editor><?php echo page_clean_html($formPost['content_html'] ?? ''); ?></div>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-3">
                        <a class="rounded-xl border border-slate-300 bg-white px-5 py-3 font-black text-slate-700" href="<?php echo e(base_url('dashboard/post-manager.php')); ?>">New</a>
                        <button class="rounded-xl bg-blue-700 px-6 py-3 font-black text-white" type="submit">Save Post</button>
                    </div>
                </form>

                <aside class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-700">Posts</p>
                    <div class="mt-4 grid gap-2">
                        <?php foreach ($posts as $post): ?>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                                <a class="font-black text-slate-950 hover:text-blue-700" href="<?php echo e(base_url('dashboard/post-manager.php?edit=' . $post['id'])); ?>"><?php echo e($post['title']); ?></a>
                                <p class="mt-1 text-xs font-bold text-slate-500"><?php echo e($post['slug']); ?></p>
                                <div class="mt-3 flex gap-2">
                                    <a class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700" href="<?php echo e(base_url('post.php?slug=' . $post['slug'])); ?>" target="_blank">View</a>
                                    <form method="post" onsubmit="return confirm('Delete this post?')">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo e((string) $post['id']); ?>">
                                        <button class="rounded-lg bg-red-600 px-3 py-2 text-xs font-black text-white" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$posts): ?>
                            <p class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm font-bold text-slate-500">No posts yet.</p>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </main>
        <?php require __DIR__ . '/footer.php'; ?>
    </div>
</div>
<script>
    (() => {
        const editor = document.querySelector('[data-post-editor]');
        const input = document.querySelector('[data-post-editor-input]');
        const form = document.querySelector('[data-post-form]');
        if (!editor || !input || !form) return;

        document.querySelectorAll('[data-editor-command]').forEach(button => {
            button.addEventListener('click', () => {
                document.execCommand(button.dataset.editorCommand, false, null);
                editor.focus();
            });
        });

        document.querySelector('[data-editor-link]')?.addEventListener('click', () => {
            const url = window.prompt('Enter link URL');
            if (!url) return;
            document.execCommand('createLink', false, url);
            editor.focus();
        });

        form.addEventListener('submit', () => {
            input.value = editor.innerHTML.trim();
        });
    })();
</script>
</body>
</html>
