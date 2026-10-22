<?php
// Included from voter/index.php within the active-voting branch.
// Expects $category_id and $category_name in scope.
$candidates = fetchAll("SELECT * FROM candidate WHERE category_id = ? ORDER BY full_name", [$category_id]);
?>
<div class="row">
    <div class="col-sm-12">
        <?php require_once("../includes/view_messages_and_errors.php"); ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="title">Vote Your Candidate for Category ( <?php echo e($category_name); ?> )</div>
            </div>
            <div class="panel-body">
                <p class="text-muted">You may vote for <strong>one</strong> candidate. Your vote is final and cannot be changed.</p>
                <form class="form-horizontal" action="<?php echo e($_SERVER['PHP_SELF']); ?>" method="post" onsubmit="return confirm('Submit your vote? This cannot be undone.');">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th width="30%">Name</th>
                                    <th width="20%">Photo</th>
                                    <th width="20%">Symbol</th>
                                    <th width="25%">Select</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $value): ?>
                                    <tr>
                                        <td><?php echo e($value['full_name']); ?></td>
                                        <td><img src="../upload/photo/<?php echo e($value['photo']); ?>" alt="Photo" style="width:100px;height:100px;object-fit:cover;"></td>
                                        <td><img src="../upload/symbol/<?php echo e($value['symbol']); ?>" alt="Symbol" style="width:100px;height:100px;object-fit:cover;"></td>
                                        <td>
                                            <label class="radio">
                                                <input type="radio" name="vote" value="<?php echo (int) $value['id']; ?>" required>
                                                Vote
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td colspan="4"><button class="btn btn-primary btn-lg navbar-right" type="submit" name="submit">Submit Your Vote</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
