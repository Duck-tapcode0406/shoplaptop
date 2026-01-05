<?php
// Thay thế echo $success_message và echo $error_message thành htmlspecialchars
$patterns = [
    '/<\\?php\\s+echo\\s+\\$success_message\\s*;\\s*\\?>/s' => '<?php echo htmlspecialchars($success_message, ENT_QUOTES, "UTF-8"); ?>',
    '/<\\?php\\s+echo\\s+\\$error_message\\s*;\\s*\\?>/s' => '<?php echo htmlspecialchars($error_message, ENT_QUOTES, "UTF-8"); ?>'
];
$files = glob("**/*.php");
foreach ($files as $f) {
    $content = file_get_contents($f);
    $new = $content;
    foreach ($patterns as $pat => $rep) {
        $new = preg_replace($pat, $rep, $new);
    }
    if ($new !== $content) {
        file_put_contents($f, $new);
        echo "Patched: $f\n";
    }
}
?>

