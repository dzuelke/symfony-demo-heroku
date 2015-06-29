<?php
    $container->setParameter('database_url', getenv('DATABASE_URL')?:'postgres://localhost:5432/symfony_demo');
?>