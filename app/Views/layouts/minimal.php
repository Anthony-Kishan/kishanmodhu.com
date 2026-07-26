<?php
/**
 * Standalone layout for error pages.
 *
 * Deliberately self-contained — no database reads, no external stylesheets — so
 * it still renders when the thing that caused the error is the database itself.
 *
 * @var string $content
 * @var string $pageTitle
 */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= e($pageTitle ?? 'Error') ?></title>
    <style>
        *{ margin:0; padding:0; box-sizing:border-box; }
        body {
            min-height:100vh; display:flex; align-items:center; justify-content:center;
            background:#000; color:#fff; text-align:center; padding:2rem;
            font-family:"Inter", system-ui, -apple-system, sans-serif;
        }
        h1 { font-size:clamp(4rem, 20vw, 12rem); font-weight:700; line-height:1; }
        p  { margin:1rem 0 2rem; text-transform:uppercase; letter-spacing:0.05em; opacity:0.7; }
        a  {
            display:inline-block; padding:0.5rem 1.25rem; background:#fff; color:#000;
            border-radius:3px; font-weight:600; text-decoration:none; transition:background 0.3s ease;
        }
        a:hover { background:#0099ff; }
    </style>
</head>

<body>
    <main><?= $content ?></main>
</body>

</html>
