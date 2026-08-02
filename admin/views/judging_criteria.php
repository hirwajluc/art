<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoring Criteria - GREATER Art Competition</title>
    <?php include 'views/includes/styles.php'; ?>
    <style>
        .criteria-form { background:#fff; border-radius:12px; box-shadow:0 3px 12px rgba(0,0,0,.08); padding:30px; }
        .criterion-row { display:grid; grid-template-columns:40px 1fr 2fr 100px 40px; gap:12px; align-items:start; padding:16px 0; border-bottom:1px solid #f0f0f0; }
        .criterion-row:last-of-type { border-bottom:none; }
        .drag-handle { cursor:grab; color:#ccc; font-size:18px; display:flex; align-items:center; justify-content:center; padding-top:8px; }
        .total-box { background:linear-gradient(135deg,var(--primary),var(--accent)); color:#fff; border-radius:10px; padding:20px 30px; display:flex; justify-content:space-between; align-items:center; margin-top:20px; }
        .total-box .total-num { font-size:36px; font-weight:800; }
        .total-box .total-lbl { font-size:14px; opacity:.8; }
        .add-row-btn { background:#f8f9fa; border:2px dashed #dee2e6; border-radius:8px; padding:14px; text-align:center; cursor:pointer; color:var(--gray); font-size:14px; transition:.2s; margin-top:12px; }
        .add-row-btn:hover { background:#e8f0fe; border-color:var(--primary); color:var(--primary); }
        .remove-btn { background:none; border:none; color:#dc3545; cursor:pointer; font-size:18px; padding:8px 0; }
        .remove-btn:hover { color:#b91c1c; }
        .hint-box { background:#fffbeb; border-left:4px solid var(--warning); border-radius:6px; padding:14px 18px; margin-bottom:24px; font-size:14px; color:#92400e; }
    </style>
</head>
<body>
<div class="admin-container">
    <?php include 'views/includes/sidebar.php'; ?>
    <main class="main-content">
        <?php include 'views/includes/topbar.php'; ?>

        <?php if ($flashSuccess): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <div class="hint-box">
            <strong><i class="fas fa-info-circle"></i> Important:</strong> Changing criteria after judges have started evaluating does not retroactively change existing scores. Consider finalising criteria before judging begins.
        </div>

        <div class="criteria-form">
            <form method="POST" action="?page=save_criteria" id="criteriaForm">
                <div style="display:grid; grid-template-columns:40px 1fr 2fr 100px 40px; gap:12px; padding-bottom:10px; border-bottom:2px solid var(--primary); margin-bottom:8px;">
                    <div></div>
                    <div style="font-weight:600; color:var(--dark); font-size:14px;">Criterion Name *</div>
                    <div style="font-weight:600; color:var(--dark); font-size:14px;">Description</div>
                    <div style="font-weight:600; color:var(--dark); font-size:14px;">Max Score *</div>
                    <div></div>
                </div>

                <div id="criteriaRows">
                <?php foreach ($criteria as $i => $c): ?>
                <div class="criterion-row" data-index="<?php echo $i; ?>">
                    <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                    <div>
                        <input type="hidden" name="criterion_id[]" value="<?php echo (int)$c['id']; ?>">
                        <input class="form-input" type="text" name="name[]" value="<?php echo htmlspecialchars($c['name']); ?>" required placeholder="e.g. Creativity">
                    </div>
                    <div>
                        <input class="form-input" type="text" name="description[]" value="<?php echo htmlspecialchars($c['description'] ?? ''); ?>" placeholder="Optional description">
                    </div>
                    <div>
                        <input class="form-input" type="number" name="max_score[]" value="<?php echo (int)$c['max_score']; ?>" min="1" max="100" required onchange="updateTotal()">
                    </div>
                    <div>
                        <button type="button" class="remove-btn" onclick="removeRow(this)" title="Remove criterion"><i class="fas fa-times-circle"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>

                <div class="add-row-btn" onclick="addRow()">
                    <i class="fas fa-plus"></i> Add Scoring Criterion
                </div>

                <div class="total-box">
                    <div>
                        <div class="total-lbl">Total Possible Score</div>
                        <div class="total-num" id="totalScore">
                            <?php echo array_sum(array_column($criteria, 'max_score')); ?>
                        </div>
                    </div>
                    <div style="text-align:right; font-size:13px; opacity:.8; max-width:240px;">
                        This is the maximum total score a judge can award to any artwork.
                    </div>
                </div>

                <div style="margin-top:24px; display:flex; gap:12px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Criteria
                    </button>
                    <a href="?page=judging_criteria" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
function updateTotal() {
    const scores = [...document.querySelectorAll('input[name="max_score[]"]')];
    const total  = scores.reduce((sum, i) => sum + (parseInt(i.value) || 0), 0);
    document.getElementById('totalScore').textContent = total;
}

function addRow() {
    const container = document.getElementById('criteriaRows');
    const idx = container.children.length;
    const div = document.createElement('div');
    div.className = 'criterion-row';
    div.dataset.index = idx;
    div.innerHTML = `
        <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
        <div>
            <input type="hidden" name="criterion_id[]" value="">
            <input class="form-input" type="text" name="name[]" required placeholder="e.g. Originality">
        </div>
        <div>
            <input class="form-input" type="text" name="description[]" placeholder="Optional description">
        </div>
        <div>
            <input class="form-input" type="number" name="max_score[]" value="20" min="1" max="100" required onchange="updateTotal()">
        </div>
        <div>
            <button type="button" class="remove-btn" onclick="removeRow(this)"><i class="fas fa-times-circle"></i></button>
        </div>
    `;
    container.appendChild(div);
    updateTotal();
    div.querySelector('input[name="name[]"]').focus();
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.criterion-row');
    if (rows.length <= 1) { alert('At least one scoring criterion is required.'); return; }
    btn.closest('.criterion-row').remove();
    updateTotal();
}
</script>
</body>
</html>
