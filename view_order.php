<?php
// Redirect to the actual view_order.php in the pages directory
if (isset($_GET['id'])) {
    header("Location: pages/view_order.php?id=" . $_GET['id']);
    exit;
} else {
    header("Location: pages/index.php");
    exit;
}
?>