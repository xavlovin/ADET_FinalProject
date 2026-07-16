<?php
include 'includes/config.php';

$updates = [
    1 => "\u{1F6E1}",
    2 => "\u{1F48A}",
    3 => "\u{1F9B4}",
    4 => "\u{2764}\u{FE0F}",
    5 => "\u{1F331}",
    6 => "\u{1F9E0}",
    7 => "\u{26A1}",
    8 => "\u{1F4A4}",
    9 => "\u{2728}",
    12 => "\u{1F3CB}"
];

foreach ($updates as $id => $icon) {
    mysqli_query($conn, "UPDATE categories SET icon = '" . mysqli_real_escape_string($conn, $icon) . "' WHERE id = $id");
}
echo "Emojis successfully restored!";
?>
