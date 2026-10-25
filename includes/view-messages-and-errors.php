<?php
declare(strict_types=1);
if (!empty($_SESSION["messages"])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php foreach ($_SESSION["messages"] as $message): ?>
            <p class="mb-0"><?php echo e($message); ?></p>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION["messages"]); ?>
<?php elseif (!empty($_SESSION["errors"])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php foreach ($_SESSION["errors"] as $error): ?>
            <p class="mb-0"><?php echo e($error); ?></p>
        <?php endforeach; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION["errors"]); ?>
<?php endif; ?>

