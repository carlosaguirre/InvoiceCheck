<?php
?>
<html>
<body>
    <form target="_self" action="/<?= $_project_name ?>" method="POST">
        <input type="submit" name="logout" value="LOGOUT">
    </form>
    <!-- 
        SESSION:
        <?= arr2str($_SESSION); ?>

        COOKIES:
        <?= arr2str($_COOKIE); ?>
    -->
</body>
</html>