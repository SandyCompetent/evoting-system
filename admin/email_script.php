<?php
require_once __DIR__ . "/../includes/bootstrap.php";
require_role("admin");

if (isset($_POST["sendMailBtn"])) {
    csrf_verify();

    // Fixed, configured sender — never taken from user input (prevents spoofing/relay).
    $fromEmail = env("MAIL_FROM", "no-reply@evoting.local");

    $toEmail = trim($_POST["toEmail"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $body    = trim($_POST["message"] ?? "");

    // Reject anything with CR/LF (header-injection) and invalid addresses.
    $hasNewline = static fn(string $s): bool => preg_match('/[\r\n]/', $s) === 1;

    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL) || $hasNewline($toEmail)
        || $subject === "" || $hasNewline($subject)) {
        $_SESSION["errors"][] = "Invalid recipient or subject.";
        redirect("checkingmail.php");
    }

    $headers = implode("\r\n", [
        "MIME-Version: 1.0",
        "Content-type: text/html; charset=UTF-8",
        "From: " . $fromEmail,
        "Reply-To: " . $fromEmail,
        "X-Mailer: PHP/" . phpversion(),
    ]);

    $safeBody = nl2br(e($body));
    $html = '<!doctype html><html lang="en"><head><meta charset="UTF-8"></head><body>'
          . '<div class="container">' . $safeBody . '<br/>Regards<br/>' . e($fromEmail) . '</div>'
          . '</body></html>';

    if (@mail($toEmail, $subject, $html, $headers)) {
        $_SESSION["messages"][] = "Email sent successfully.";
    } else {
        $_SESSION["errors"][] = "Email could not be sent (mail transport not configured).";
    }
    redirect("checkingmail.php");
}

// No direct GET access.
redirect("checkingmail.php");
