<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <title><?php echo $title; ?></title>
</head>
<body>
    <nav>
        <!--<ul>
            <li><a href="/">Home</a></li>
            <li><a href="/register">Register</a></li>
        </ul> -->
    </nav>

    <div class="">
        <?php include $child_view; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Wellcome. All rights reserved.</p>
    </footer>

    <?php
    foreach ($javascript as $script) {
        echo "<script src=\"$script\"></script>";
    }
    ?>
</body>
</html>