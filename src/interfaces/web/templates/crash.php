<?php
/**
 * @var array $exception
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error Page</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-weight: 200;
            font-size: 1.1rem;
        }

        h1, h2, h3, h4, h5, h6 {
            font-weight: 300 !important;
        }

        .stack-trace {
            white-space: pre-wrap;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="p-5 mb-4 bg-danger text-white rounded-3">
        <h1 class="display-6">⚠️ <?= htmlspecialchars(\Charcoal\OOP\OOP::baseClassName($exception['class'])) ?></h1>
        <p class="lead"><?= htmlspecialchars($exception['message']) ?></p>
        <hr>
        <p><strong>Exception Type:</strong> <?= htmlspecialchars($exception['class']) ?></p>
        <p class="mb-0"><strong>Error Code:</strong> <?= htmlspecialchars($exception['code']) ?></p>
    </div>

    <h4>Stack Trace:</h4>
    <div class="stack-trace px-4 py-3"><?= htmlspecialchars(implode("\n", $exception['trace'])) ?></div>
    <?php if (!empty($exception['previous'])): ?>
        <h4 class="mt-4">Previous Exception:</h4>
        <div class="stack-trace px-4 py-3 mb-4"><strong><?= htmlspecialchars($exception['previous']['class']) ?>
:</strong> <?= htmlspecialchars($exception['previous']['message']) ?>
<br><strong>Code:</strong> <?= htmlspecialchars($exception['previous']['code']) ?><br><strong>Stack Trace:</strong><br><?= htmlspecialchars(implode("\n", $exception['previous']['trace'])) ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>