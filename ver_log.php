<?php
echo "<pre>";
if (file_exists("log.txt")) {
    echo htmlspecialchars(file_get_contents("log.txt"));
} else {
    echo "log.txt não encontrado.";
}
echo "</pre>";
?>
