<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/AdminAuth.php';
require_once __DIR__ . '/../core/CategoryService.php';
$auth = new AdminAuth();
$auth->requireAuth();
$pageTitle = 'Categories';
$categoryService = new CategoryService();
if (isPost()) {
    $action = input('action');
    if ($action === 'create') {
        $result = $categoryService->create($_POST);
        if ($result['success']) { setFlash('success', $result['message']); redirect(ADMIN_URL . '/categories.php'); }
        $error = $result['error'] ?? 'Failed';
    } elseif ($action === 'update') {
        $result = $categoryService->update(input('id'), $_POST);
        if ($result['success']) { setFlash('success', $result['message']); redirect(ADMIN_URL . '/categories.php'); }
        $error = $result['error'] ?? 'Failed';
    } elseif ($action === 'delete') {
        $result = $categoryService->delete(input('id'));
        if ($result['success']) { setFlash('success', $result['message']); } else { setFlash('error', $result['error']); }
        redirect(ADMIN_URL . '/categories.php');
    }
}
$categories = $categoryService->getAll();
$editCat = null;
if (input('edit')) { $editCat = $categoryService->getById(input('edit')); }
include __DIR__ . '/partials/header.php';
?>
<div class="content-header"><h2>Categories</h2><p>Manage booking categories</p></div>
<div class="card" style="margin-bottom:20px;"><div class="card-header">Add/Edit Category</div>
<form method="POST">
<input type="hidden" name="action" value="<?php echo $editCat ? 'update' : 'create'; ?>">
<?php if ($editCat): ?><input type="hidden" name="id" value="<?php echo $editCat['id']; ?>"><?php endif; ?>
<div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" value="<?php echo e($editCat['name'] ?? ''); ?>" required></div>
<div class="form-group"><label>Color *</label><input type="color" name="color" class="form-control" value="<?php echo e($editCat['color'] ?? '#3498db'); ?>" required></div>
<button type="submit" class="btn btn-primary"><?php echo $editCat ? 'Update' : 'Create'; ?></button>
<?php if ($editCat): ?><a href="<?php echo ADMIN_URL; ?>/categories.php" class="btn btn-secondary">Cancel</a><?php endif; ?>
</form></div>
<div class="card"><div class="card-header">All Categories</div><table class="table"><thead><tr><th>ID</th><th>Name</th><th>Color</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($categories as $cat): ?>
<tr><td><?php echo $cat['id']; ?></td><td><strong><?php echo e($cat['name']); ?></strong></td>
<td><span style="display:inline-block;width:40px;height:20px;background:<?php echo e($cat['color']); ?>;border-radius:4px;"></span> <?php echo e($cat['color']); ?></td>
<td><a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
<form method="POST" style="display:inline;" onsubmit="return confirm('Delete?');">
<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
<button type="submit" class="btn btn-sm btn-danger">Delete</button></form></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php include __DIR__ . '/partials/footer.php'; ?>
